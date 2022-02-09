<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Model\Behavior;

use ArrayObject;
use Cake\Collection\Collection;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Josegonzalez\Upload\File\Path\ProcessorInterface;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;
use Josegonzalez\Upload\File\Transformer\TransformerInterface;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Josegonzalez\Upload\File\Writer\WriterInterface;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

class UploadBehavior extends Behavior
{
    /**
     * Protected file names
     *
     * @var array
     */
    private $protectedFieldNames = [
        'priority',
    ];

    /**
     * Initialize hook
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config): void
    {
        $configs = [];
        foreach ($config as $field => $settings) {
            if (is_int($field)) {
                $configs[$settings] = [];
            } else {
                $configs[$field] = $settings;
            }
        }

        $this->setConfig($configs);
        $this->setConfig('className', null);

        $schema = $this->_table->getSchema();
        /** @var string $field */
        foreach (array_keys($this->getConfig()) as $field) {
            $schema->setColumnType($field, 'upload.file');
        }
        $this->_table->setSchema($schema);
    }

    /**
     * Modifies the data being marshalled to ensure invalid upload data is not inserted
     *
     * @param \Cake\Event\EventInterface $event an event instance
     * @param \ArrayObject $data data being marshalled
     * @param \ArrayObject $options options for the current event
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $validator = $this->_table->getValidator();
        $dataArray = $data->getArrayCopy();
        /** @var string $field */
        foreach (array_keys($this->getConfig(null, [])) as $field) {
            if (!$validator->isEmptyAllowed($field, false)) {
                continue;
            }
            if (!empty($dataArray[$field]) && $dataArray[$field]->getError() !== UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
    }

    /**
     * Modifies the entity before it is saved so that uploaded file data is persisted
     * in the database too.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options the options passed to the save method
     * @return void|false
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        foreach ($this->getConfig(null, []) as $field => $settings) {
            if (
                in_array($field, $this->protectedFieldNames, true)
                || !$entity->isDirty($field)
            ) {
                continue;
            }

            $data = $entity->get($field);
            if (!$data instanceof UploadedFileInterface) {
                continue;
            }

            if ($entity->get($field)->getError() !== UPLOAD_ERR_OK) {
                if (Hash::get($settings, 'restoreValueOnFailure', true)) {
                    $entity->set($field, $entity->getOriginal($field));
                    $entity->setDirty($field, false);
                }
                continue;
            }

            $path = $this->getPathProcessor($entity, $data, $field, $settings);
            $basepath = $path->basepath();
            $filename = $path->filename();
            $pathinfo = [
                'basepath' => $basepath,
                'filename' => $filename,
            ];

            $files = $this->constructFiles($entity, $data, $field, $settings, $pathinfo);

            $writer = $this->getWriter($entity, $data, $field, $settings);
            $success = $writer->write($files);
            if ((new Collection($success))->contains(false)) {
                return false;
            }

            $entity->set($field, $filename);
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $basepath);
            $entity->set(Hash::get($settings, 'fields.size', 'size'), $data->getSize());
            $entity->set(Hash::get($settings, 'fields.type', 'type'), $data->getClientMediaType());
            $entity->set(Hash::get($settings, 'fields.ext', 'ext'), pathinfo($filename, PATHINFO_EXTENSION));
        }
    }

    /**
     * Deletes the files after the entity is deleted
     *
     * @param \Cake\Event\EventInterface $event The afterDelete event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
     * @param \ArrayObject $options the options passed to the delete method
     * @return bool
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $result = true;

        foreach ($this->getConfig(null, []) as $field => $settings) {
            if (in_array($field, $this->protectedFieldNames) || Hash::get($settings, 'keepFilesOnDelete', true)) {
                continue;
            }

            $dirField = Hash::get($settings, 'fields.dir', 'dir');
            if ($entity->has($dirField)) {
                $path = $entity->get($dirField);
            } else {
                $path = $this->getPathProcessor($entity, $entity->get($field), $field, $settings)->basepath();
            }

            $callback = Hash::get($settings, 'deleteCallback', null);
            if ($callback && is_callable($callback)) {
                $files = $callback($path, $entity, $field, $settings);
            } else {
                $files = [$path . $entity->get($field)];
            }

            $writer = $this->getWriter($entity, null, $field, $settings);
            $success = $writer->delete($files);

            if ($result && (new Collection($success))->contains(false)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Retrieves an instance of a path processor which knows how to build paths
     * for a given file upload
     *
     * @param \Cake\Datasource\EntityInterface $entity an entity
     * @param \Psr\Http\Message\UploadedFileInterface|string $data the data being submitted for a save or the filename
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return \Josegonzalez\Upload\File\Path\ProcessorInterface
     */
    public function getPathProcessor(EntityInterface $entity, $data, string $field, array $settings): ProcessorInterface
    {
        /** @var class-string<\Josegonzalez\Upload\File\Path\ProcessorInterface> $processorClass */
        $processorClass = Hash::get($settings, 'pathProcessor', DefaultProcessor::class);

        return new $processorClass($this->_table, $entity, $data, $field, $settings);
    }

    /**
     * Retrieves an instance of a file writer which knows how to write files to disk
     *
     * @param \Cake\Datasource\EntityInterface $entity an entity
     * @param \Psr\Http\Message\UploadedFileInterface|null $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return \Josegonzalez\Upload\File\Writer\WriterInterface
     */
    public function getWriter(
        EntityInterface $entity,
        ?UploadedFileInterface $data,
        string $field,
        array $settings
    ): WriterInterface {
        /** @var class-string<\Josegonzalez\Upload\File\Writer\WriterInterface> $writerClass */
        $writerClass = Hash::get($settings, 'writer', DefaultWriter::class);

        return new $writerClass($this->_table, $entity, $data, $field, $settings);
    }

    /**
     * Creates a set of files from the initial data and returns them as key/value
     * pairs, where the path on disk maps to name which each file should have.
     * This is done through an intermediate transformer, which should return
     * said array. Example:
     *
     *   [
     *     '/tmp/path/to/file/on/disk' => 'file.pdf',
     *     '/tmp/path/to/file/on/disk-2' => 'file-preview.png',
     *   ]
     *
     * A user can specify a callable in the `transformer` setting, which can be
     * used to construct this key/value array. This processor can be used to
     * create the source files.
     *
     * @param \Cake\Datasource\EntityInterface $entity an entity
     * @param \Psr\Http\Message\UploadedFileInterface $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @param array $pathinfo Path info.
     * @return array key/value pairs of temp files mapping to their names
     */
    public function constructFiles(
        EntityInterface $entity,
        UploadedFileInterface $data,
        string $field,
        array $settings,
        array $pathinfo
    ): array {
        $basepath = $pathinfo['basepath'];
        $filename = $pathinfo['filename'];

        $basepath = substr($basepath, -1) == DS ? $basepath : $basepath . DS;
        $transformerClass = Hash::get($settings, 'transformer', DefaultTransformer::class);
        $results = [];
        if (is_subclass_of($transformerClass, TransformerInterface::class)) {
            $transformer = new $transformerClass($this->_table, $entity, $data, $field, $settings);
            $results = $transformer->transform($filename);
            foreach ($results as $key => $value) {
                $results[$key] = $basepath . $value;
            }
        } elseif (is_callable($transformerClass)) {
            $results = $transformerClass($this->_table, $entity, $data, $field, $settings, $filename);
            foreach ($results as $key => $value) {
                $results[$key] = $basepath . $value;
            }
        } else {
            throw new UnexpectedValueException(sprintf(
                "'transformer' not set to instance of TransformerInterface: %s",
                $transformerClass
            ));
        }

        return $results;
    }
}

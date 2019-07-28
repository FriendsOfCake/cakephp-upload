<?php
namespace Josegonzalez\Upload\Model\Behavior;

use ArrayObject;
use Cake\Collection\Collection;
use Cake\Database\Type;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Exception;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use UnexpectedValueException;

class UploadBehavior extends Behavior
{
    private $protectedFieldNames = [
        'priority',
    ];

    /**
     * Initialize hook
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config)
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

        Type::map('upload.file', 'Josegonzalez\Upload\Database\Type\FileType');
        $schema = $this->_table->getSchema();
        foreach (array_keys($this->getConfig()) as $field) {
            $schema->setColumnType($field, 'upload.file');
        }
        $this->_table->setSchema($schema);
    }

    /**
     * Modifies the data being marshalled to ensure invalid upload data is not inserted
     *
     * @param \Cake\Event\Event $event an event instance
     * @param \ArrayObject $data data being marshalled
     * @param \ArrayObject $options options for the current event
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $validator = $this->_table->getValidator();
        $dataArray = $data->getArrayCopy();
        foreach (array_keys($this->getConfig(null, [])) as $field) {
            if (!$validator->isEmptyAllowed($field, false)) {
                continue;
            }
            if (Hash::get($dataArray, $field . '.error') !== UPLOAD_ERR_NO_FILE) {
                continue;
            }
            unset($data[$field]);
        }
    }

    /**
     * Modifies the entity before it is saved so that uploaded file data is persisted
     * in the database too.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved
     * @param \ArrayObject $options the options passed to the save method
     * @return void|false
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        foreach ($this->getConfig(null, []) as $field => $settings) {
            if (in_array($field, $this->protectedFieldNames)) {
                continue;
            }

            if (Hash::get((array)$entity->get($field), 'error') !== UPLOAD_ERR_OK) {
                if (Hash::get($settings, 'restoreValueOnFailure', true)) {
                    $entity->set($field, $entity->getOriginal($field));
                    $entity->setDirty($field, false);
                }
                continue;
            }

            $data = $entity->get($field);
            $path = $this->getPathProcessor($entity, $data, $field, $settings);
            $basepath = $path->basepath();
            $filename = $path->filename();
            $data['name'] = $filename;
            $files = $this->constructFiles($entity, $data, $field, $settings, $basepath);

            $writer = $this->getWriter($entity, $data, $field, $settings);
            $success = $writer->write($files);

            if ((new Collection($success))->contains(false)) {
                return false;
            }

            $entity->set($field, $filename);
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $basepath);
            $entity->set(Hash::get($settings, 'fields.size', 'size'), $data['size']);
            $entity->set(Hash::get($settings, 'fields.type', 'type'), $data['type']);
            $entity->set(Hash::get($settings, 'fields.ext', 'ext'), pathinfo($data['name'], PATHINFO_EXTENSION));
        }
    }

    /**
     * Deletes the files after the entity is deleted
     *
     * @param \Cake\Event\Event $event The afterDelete event that was fired
     * @param \Cake\ORM\Entity $entity The entity that was deleted
     * @param \ArrayObject $options the options passed to the delete method
     * @return bool
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
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

            $writer = $this->getWriter($entity, [], $field, $settings);
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
     * @param \Cake\ORM\Entity $entity an entity
     * @param array $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return \Josegonzalez\Upload\File\Path\ProcessorInterface
     */
    public function getPathProcessor(Entity $entity, $data, $field, $settings)
    {
        $default = 'Josegonzalez\Upload\File\Path\DefaultProcessor';
        $processorClass = Hash::get($settings, 'pathProcessor', $default);
        if (is_subclass_of($processorClass, 'Josegonzalez\Upload\File\Path\ProcessorInterface')) {
            return new $processorClass($this->_table, $entity, $data, $field, $settings);
        }

        throw new UnexpectedValueException(sprintf(
            "'pathProcessor' not set to instance of ProcessorInterface: %s",
            $processorClass
        ));
    }

    /**
     * Retrieves an instance of a file writer which knows how to write files to disk
     *
     * @param \Cake\ORM\Entity $entity an entity
     * @param array $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return \Josegonzalez\Upload\File\Writer\WriterInterface
     */
    public function getWriter(Entity $entity, $data, $field, $settings)
    {
        $default = 'Josegonzalez\Upload\File\Writer\DefaultWriter';
        $writerClass = Hash::get($settings, 'writer', $default);
        if (is_subclass_of($writerClass, 'Josegonzalez\Upload\File\Writer\WriterInterface')) {
            return new $writerClass($this->_table, $entity, $data, $field, $settings);
        }

        throw new UnexpectedValueException(sprintf(
            "'writer' not set to instance of WriterInterface: %s",
            $writerClass
        ));
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
     * @param \Cake\ORM\Entity $entity an entity
     * @param array $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @param string $basepath a basepath where the files are written to
     * @return array key/value pairs of temp files mapping to their names
     */
    public function constructFiles(Entity $entity, $data, $field, $settings, $basepath)
    {
        $basepath = (substr($basepath, -1) == DS ? $basepath : $basepath . DS);
        $default = 'Josegonzalez\Upload\File\Transformer\DefaultTransformer';
        $transformerClass = Hash::get($settings, 'transformer', $default);
        $results = [];
        if (is_subclass_of($transformerClass, 'Josegonzalez\Upload\File\Transformer\TransformerInterface')) {
            $transformer = new $transformerClass($this->_table, $entity, $data, $field, $settings);
            $results = $transformer->transform();
            foreach ($results as $key => $value) {
                $results[$key] = $basepath . $value;
            }
        } elseif (is_callable($transformerClass)) {
            $results = $transformerClass($this->_table, $entity, $data, $field, $settings);
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

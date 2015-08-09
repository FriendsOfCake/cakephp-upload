<?php
namespace Josegonzalez\Upload\Model\Behavior;

use ArrayObject;
use Cake\Collection\Collection;
use Cake\Database\Type;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Exception;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;
use Josegonzalez\Upload\File\Writer\DefaultWriter;

class UploadBehavior extends Behavior
{

    /**
     * Initialize hook
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->_config = [];
        $this->config(Hash::normalize($config));

        Type::map('upload.file', 'Josegonzalez\Upload\Database\Type\FileType');
        $schema = $this->_table->schema();
        foreach (array_keys($this->config()) as $field) {
            $schema->columnType($field, 'upload.file');
        }
        $this->_table->schema($schema);
    }

    /**
     * Modifies the data being marshalled to ensure invalid upload data is not inserted
     *
     * @param Event       $event an event instance
     * @param ArrayObject $data data being marshalled
     * @param ArrayObject $options options for the current event
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $validator = $this->_table->validator();
        $collection = new Collection(array_keys($this->config()));
        $collection = $collection->filter(function ($field, $key) use ($validator, $data) {
            return $validator->isEmptyAllowed($field, false) && Hash::get($data, $field . '.error') === UPLOAD_ERR_NO_FILE;
        });

        foreach ($collection->toList() as $field) {
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
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        foreach ($this->config() as $field => $settings) {
            if (Hash::get((array)$entity->get($field), 'error') !== UPLOAD_ERR_OK) {
                continue;
            }

            $data = $entity->get($field);
            $path = $this->getPathProcessor($entity, $data, $field, $settings);
            $files = $this->constructFiles($entity, $data, $field, $settings, $path->basepath());

            $writerClass = Hash::get($settings, 'writer', 'Josegonzalez\Upload\File\Writer\DefaultWriter');
            $writer = new $writerClass($this->_table, $entity, $data, $field, $settings);
            $success = $writer->write($files);

            $entity->set($field, $path->filename());
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $path->basepath());
            $entity->set(Hash::get($settings, 'fields.size', 'size'), $data['size']);
            $entity->set(Hash::get($settings, 'fields.type', 'type'), $data['type']);
        }
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
     * @param array  $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array  $settings the settings for the current field
     * @param string $basepath a basepath where the files are written to
     * @return array key/value pairs of temp files mapping to their names
     */
    public function constructFiles($entity, $data, $field, $settings, $basepath)
    {
        $default = 'Josegonzalez\Upload\File\Transformer\DefaultTransformer';
        $transformerClass = Hash::get($settings, 'transformer', $default);
        $transformer = new $transformerClass($this->_table, $entity, $data, $field, $settings);
        $results = $transformer->transform();
        foreach ($results as $key => $value) {
            $results[$key] = $basepath . '/' . $value;
        }
        return $results;
    }

    /**
     * Retrieves an instance of a path processor which knows how to build paths
     * for a given file upload
     *
     * @param \Cake\ORM\Entity $entity an entity
     * @param array  $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array  $settings the settings for the current field
     * @return Josegonzalez\Upload\File\Path\AbstractProcessor
     */
    public function getPathProcessor($entity, $data, $field, $settings)
    {
        $default = 'Josegonzalez\Upload\File\Path\DefaultProcessor';
        $processorClass = Hash::get($settings, 'pathProcessor', $default);
        return new $processorClass($this->_table, $entity, $data, $field, $settings);
    }
}

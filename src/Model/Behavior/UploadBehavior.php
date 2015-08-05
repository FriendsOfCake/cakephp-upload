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
use Josegonzalez\Upload\Path\DefaultPathProcessor;
use Josegonzalez\Upload\Writer\DefaultWriter;

class UploadBehavior extends Behavior
{
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

    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        foreach ($this->config() as $field => $settings) {
            if (Hash::get((array)$entity->get($field), 'error') !== UPLOAD_ERR_OK) {
                continue;
            }

            $data = $entity->get($field);
            $basepath = $this->getBasepath($entity, $field, $settings);
            $writer = Hash::get($settings, 'writer', new DefaultWriter);
            $files = $this->constructFiles($data, $field, $settings, $basepath);
            $success = $writer($files, $field, $settings);

            $entity->set($field, $this->getFilename($data, $settings));
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $basepath);
        }

        return true;
    }

    public function constructFiles($data, $field, $settings, $basepath)
    {
        $processor = Hash::get($settings, 'processor', null);
        if (is_callable($processor)) {
            return $processor($data, $field, $settings, $basepath);
        }
        return [$data['tmp_name'] => $basepath . '/' . $data['name']];
    }

    public function getFilename($data, $settings)
    {
        $processor = Hash::get($settings, 'filename', null);
        if (is_callable($processor)) {
            return $processor($data, $settings);
        }
        return $data['name'];
    }

    public function getBasepath($entity, $field, $settings)
    {
        $defaultProcessor = new DefaultPathProcessor;
        $processor = Hash::get($settings, 'pathProcessor', $defaultProcessor);
        return $processor($this->_table, $entity, $field, $settings);
    }
}

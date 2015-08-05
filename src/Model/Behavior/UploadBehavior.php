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
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;

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
            $filesystem = $this->getFilesystem($field, $settings);

            $success = [];
            $files = $this->constructFiles($data, $field, $settings, $basepath);
            foreach ($files as $file => $path) {
                $success[] = $this->writeFile($filesystem, $file, $path);
            }

            $entity->set($field, $this->getFilename($data, $settings));
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $basepath);
        }

        return true;
    }

    public function writeFile($filesystem, $file, $path)
    {
        $success = false;
        $stream = fopen($file, 'r+');
        $tempPath = $path . '.temp';
        $this->deletePath($filesystem, $tempPath);
        if ($filesystem->writeStream($tempPath, $stream)) {
            $this->deletePath($filesystem, $path);
            $success = $filesystem->rename($tempPath, $path);
        }
        $this->deletePath($filesystem, $tempPath);
        fclose($stream);
        return $success;
    }

    public function deletePath($filesystem, $path)
    {
        try {
            $filesystem->delete($path);
        } catch (FileNotFoundException $e) {
            // TODO: log this?
        }
    }

    public function getFilesystem($field, array $settings = [])
    {
        $adapter = new Local(Hash::get($settings, 'rootDir', ROOT . DS));
        $adapter = Hash::get($settings, 'adapter', $adapter);
        if (is_callable($adapter)) {
            $adapter = $adapter();
        }

        if ($adapter instanceof AdapterInterface) {
            return new Filesystem($adapter, Hash::get($settings, 'filesystemOptions', [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]));
        }

        throw new Exception(sprintf("Invalid Adapter for field %s", $field));
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
        $defaultPath = 'webroot{DS}files{DS}{model}{DS}{field}{DS}';
        $defaultProcessor = new DefaultPathProcessor;
        $path = Hash::get($settings, 'path', $defaultPath);
        $processor = Hash::get($settings, 'processor', $defaultProcessor);
        return $processor($this->_table, $entity, $field, $settings);
    }
}

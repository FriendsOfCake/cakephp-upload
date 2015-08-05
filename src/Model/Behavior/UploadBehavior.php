<?php
namespace Josegonzalez\Upload\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Collection\Collection;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;

class UploadBehavior extends Behavior
{
    public function initialize(array $config)
    {
        $this->_config = [];
        $this->config($this->normalizeArray($config));

        // overwrite that pesky schema LIKE A BAWS
        \Cake\Database\Type::map('file', 'Josegonzalez\Upload\Database\Type\FileType');
        $schema = $this->_table->schema();
        foreach (array_keys($this->config()) as $field) {
            $schema->columnType($field, 'file');
        }
        $this->_table->schema($schema);
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $validator = $this->_table->validator();
        $collection = new Collection(array_keys($this->config()));
        $collection = $collection->filter(function ($field, $key) use ($validator) {
            return $validator->isEmptyAllowed($field, false);
        });
        $collection = $collection->filter(function ($field, $key) use ($data) {
            return Hash::get($data, $field . '.error') === UPLOAD_ERR_NO_FILE;
        });

        foreach ($collection->toList() as $field) {
            unset($data[$field]);
        }
    }

    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        foreach ($this->config() as $field => $settings) {
            if (!Hash::get((array)$entity->get($field), 'error') === UPLOAD_ERR_OK) {
                continue;
            }

            $data = $entity->get($field);
            $basepath = $this->getBasepath($entity, $field, $settings);
            $filesystem = $this->getFilesystem($field, $settings);
            $name = $this->getFilename($data, $settings);

            $success = [];
            $files = $this->constructFiles($data, $field, $settings, $basepath);
            foreach ($files as $file => $path) {
                $success[] = $this->writeFile($filesystem, $file, $path);
            }

            $entity->set($field, $data['name']);
            $entity->set(Hash::get($settings, 'fields.dir', 'dir'), $basepath);
        }

        return true;
    }

    public function writeFile($filesystem, $file, $path)
    {
        // TODO: Handle race conditions?
        $success = false;
        $stream = fopen($file, 'r+');
        if ($filesystem->has($path)) {
            $tempPath = $path . '.temp';
            $this->deletePath($filesystem, $tempPath);
            if ($filesystem->writeStream($tempPath, $stream)) {
                $this->deletePath($filesystem, $path);
                $success = $filesystem->rename($tempPath, $path);
            }
            $this->deletePath($filesystem, $tempPath);
        } else {
            $success = $filesystem->writeStream($path, $stream);
        }
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

    public function normalizeArray(array $objects)
    {
        $normal = [];
        foreach ($objects as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = [];
            }

            $normal[$name] = $config;
        }
        return $normal;
    }

    public function getFilesystem($field, array $settings = [])
    {
        $adapter = Hash::get($settings, 'adapter');
        if ($adapter === null) {
            $adapter = new Local(Hash::get($settings, 'rootDir', ROOT . DS));
        } elseif (is_callable($adapter)) {
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
        $path = Hash::get($settings, 'path', $defaultPath);
        if (is_callable($path)) {
            return $path($field, $settings);
        }

        $replacements = array(
            '{primaryKey}' => $entity->get($this->_table->primaryKey()),
            '{model}' => Inflector::underscore($this->_table->alias()),
            '{field}' => $field,
            '{time}' => time(),
            '{microtime}' => microtime(),
            '{DS}' => DIRECTORY_SEPARATOR,
        );
        return str_replace(array_keys($replacements), array_values($replacements), $path);
    }
}

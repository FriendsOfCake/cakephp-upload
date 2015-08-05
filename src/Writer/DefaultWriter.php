<?php
namespace Josegonzalez\Upload\Writer;

use Cake\Utility\Hash;
use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;

class DefaultWriter
{
    public function __invoke($files, $field, $settings)
    {
        $filesystem = $this->getFilesystem($field, $settings);
        $success = [];
        foreach ($files as $file => $path) {
            $success[] = $this->writeFile($filesystem, $file, $path);
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

}

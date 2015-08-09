<?php
namespace Josegonzalez\Upload\File\Writer;

use Cake\Utility\Hash;
use Josegonzalez\Upload\File\Writer\WriterInterface;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use UnexpectedValueException;

class DefaultWriter implements WriterInterface
{
    /**
     * Writes a set of files to an output
     *
     * @param array $files the files being written out
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     */
    public function __invoke($files = [], $field, $settings)
    {
        $filesystem = $this->getFilesystem($field, $settings);
        $results = [];
        foreach ($files as $file => $path) {
            $results[] = $this->writeFile($filesystem, $file, $path);
        }

        return $results;
    }

    /**
     * Writes a set of files to an output
     *
     * @param League\Flysystem\FilesystemInterface $filesystem a filesystem wrapper
     * @param string $file a full path to a temp file
     * @param string $path that path to which the file should be written
     * @return bool
     */
    public function writeFile(FilesystemInterface $filesystem, $file, $path)
    {
        $stream = @fopen($file, 'r');
        if ($stream === false) {
            return false;
        }

        $success = false;
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

    /**
     * Deletes a path from a filesystem
     *
     * @param League\Flysystem\FilesystemInterface $filesystem a filesystem writer
     * @param string $path the path that should be deleted
     * @return boolean
     */
    public function deletePath(FilesystemInterface $filesystem, $path)
    {
        $success = false;
        try {
            $success = $filesystem->delete($path);
        } catch (FileNotFoundException $e) {
            // TODO: log this?
        }
        return $success;
    }

    /**
     * Retrieves a configured filesystem for the given field
     *
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return League\Flysystem\FilesystemInterface
     */
    public function getFilesystem($field, array $settings = [])
    {
        $adapter = new Local(Hash::get($settings, 'filesystem.root', ROOT . DS));
        $adapter = Hash::get($settings, 'filesystem.adapter', $adapter);
        if (is_callable($adapter)) {
            $adapter = $adapter();
        }

        if ($adapter instanceof AdapterInterface) {
            return new Filesystem($adapter, Hash::get($settings, 'filesystem.options', [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]));
        }

        throw new UnexpectedValueException(sprintf("Invalid Adapter for field %s", $field));
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Writer;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Visibility;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

class DefaultWriter implements WriterInterface
{
    /**
     * Constructs a writer
     *
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param \Psr\Http\Message\UploadedFileInterface|null $data the data being submitted for a save
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __construct(
        protected Table $table,
        protected EntityInterface $entity,
        protected ?UploadedFileInterface $data,
        protected string $field,
        protected array $settings
    ) {
    }

    /**
     * Writes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function write(array $files): array
    {
        $filesystem = $this->getFilesystem($this->field, $this->settings);
        $results = [];
        foreach ($files as $file => $path) {
            $results[] = $this->writeFile($filesystem, $file, $path);
        }

        return $results;
    }

    /**
     * Deletes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function delete(array $files): array
    {
        $filesystem = $this->getFilesystem($this->field, $this->settings);
        $results = [];
        foreach ($files as $path) {
            $results[] = $this->deletePath($filesystem, $path);
        }

        return $results;
    }

    /**
     * Writes a set of files to an output
     *
     * @param \League\Flysystem\FilesystemOperator $filesystem a filesystem wrapper
     * @param string $file a full path to a temp file
     * @param string $path that path to which the file should be written
     * @return bool
     */
    public function writeFile(FilesystemOperator $filesystem, string $file, string $path): bool
    {
        // phpcs:ignore
        $stream = @fopen($file, 'r');
        if ($stream === false) {
            return false;
        }

        $success = false;
        $tempPath = $path . '.temp';
        $this->deletePath($filesystem, $tempPath);
        try {
            $filesystem->writeStream($tempPath, $stream);
            $this->deletePath($filesystem, $path);
            try {
                $filesystem->move($tempPath, $path);
                $success = true;
            } catch (FilesystemException) {
                // noop
            }
        } catch (FilesystemException) {
            // noop
        }

        $this->deletePath($filesystem, $tempPath);
        is_resource($stream) && fclose($stream);

        return $success;
    }

    /**
     * Deletes a path from a filesystem
     *
     * @param \League\Flysystem\FilesystemOperator $filesystem a filesystem writer
     * @param string $path the path that should be deleted
     * @return bool
     */
    public function deletePath(FilesystemOperator $filesystem, string $path): bool
    {
        $success = true;
        try {
            $filesystem->delete($path);
        } catch (FilesystemException) {
            $success = false;
            // TODO: log this?
        }

        return $success;
    }

    /**
     * Retrieves a configured filesystem for the given field
     *
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     * @return \League\Flysystem\FilesystemOperator
     */
    public function getFilesystem(string $field, array $settings = []): FilesystemOperator
    {
        $adapter = new LocalFilesystemAdapter(Hash::get($settings, 'filesystem.root', ROOT . DS));
        $adapter = Hash::get($settings, 'filesystem.adapter', $adapter);
        if (is_callable($adapter)) {
            $adapter = $adapter();
        }

        if ($adapter instanceof FilesystemAdapter) {
            return new Filesystem($adapter, Hash::get($settings, 'filesystem.options', [
                'visibility' => Visibility::PUBLIC,
            ]));
        }

        throw new UnexpectedValueException(sprintf('Invalid Adapter for field %s', $field));
    }
}

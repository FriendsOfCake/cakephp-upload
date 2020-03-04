<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Writer;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Psr\Http\Message\UploadedFileInterface;

interface WriterInterface
{
    /**
     * Constructor.
     *
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param \Psr\Http\Message\UploadedFileInterface|null $data the data being submitted for a save
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __construct(Table $table, EntityInterface $entity, ?UploadedFileInterface $data = null, string $field, array $settings);

    /**
     * Writes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function write(array $files): array;

    /**
     * Deletes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function delete(array $files): array;
}

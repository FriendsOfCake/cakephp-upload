<?php
namespace Josegonzalez\Upload\File\Writer;

use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Entity;

interface WriterInterface
{
    /**
     * Constructor.
     *
     * @param \Cake\Datasource\RepositoryInterface $repository the instance managing the entity
     * @param \Cake\ORM\Entity                     $entity the entity to construct a path for.
     * @param array                                $data the data being submitted for a save
     * @param string                               $field the field for which data will be saved
     * @param array                                $settings the settings for the current field
     */
    public function __construct(RepositoryInterface $repository, Entity $entity, $data, $field, $settings);

    /**
     * Writes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function write(array $files);

    /**
     * Deletes a set of files to an output
     *
     * @param array $files the files being written out
     * @return array array of results
     */
    public function delete(array $files);
}

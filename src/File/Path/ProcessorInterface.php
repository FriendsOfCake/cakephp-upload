<?php
namespace Josegonzalez\Upload\File\Path;

use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Entity;

interface ProcessorInterface
{
    /**
     * Constructor
     *
     * @param \Cake\Datasource\RepositoryInterface $repository the instance managing the entity
     * @param \Cake\ORM\Entity                     $entity the entity to construct a path for.
     * @param array                                $data the data being submitted for a save
     * @param string                               $field the field for which data will be saved
     * @param array                                $settings the settings for the current field
     */
    public function __construct(RepositoryInterface $repository, Entity $entity, $data, $field, $settings);

    /**
     * Returns the basepath for the current field/data combination
     *
     * @return string
     */
    public function basepath();

    /**
     * Returns the filename for the current field/data combination
     *
     * @return string
     */
    public function filename();
}

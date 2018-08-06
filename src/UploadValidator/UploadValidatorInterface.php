<?php
namespace Josegonzalez\Upload\UploadValidator;

use Cake\ORM\Entity;

interface UploadValidatorInterface
{
    /**
     * Constructor.
     *
     * @param \Cake\ORM\Entity $entity the entity to construct a path for.
     * @param string           $field the field for which data will be saved
     */
    public function __construct(Entity $entity, $field);

    /**
     * Check's data for any upload errors.
     * pairs, where the path on disk maps to name which each file should have.
     *
     * @return boolean `true` if upload failed
     */
    public function hasUploadFailed();
}

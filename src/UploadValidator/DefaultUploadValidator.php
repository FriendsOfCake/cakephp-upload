<?php
namespace Josegonzalez\Upload\UploadValidator;

use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Josegonzalez\Upload\UploadValidator\UploadValidatorInterface;

class DefaultUploadValidator implements UploadValidatorInterface
{
    /**
     * Entity instance.
     *
     * @var \Cake\ORM\Entity
     */
    protected $entity;

    /**
     * Name of field
     *
     * @var string
     */
    protected $field;

    /**
     * Constructor
     *
     * @param \Cake\ORM\Entity $entity the entity to construct a path for.
     * @param string           $field the field for which data will be saved
     */
    public function __construct(Entity $entity, $field)
    {
        $this->entity = $entity;
        $this->field = $field;
    }

    /**
     * Check's data for any upload errors.
     * pairs, where the path on disk maps to name which each file should have.
     *
     * @return bool `true` if upload failed
     */
    public function hasUploadFailed()
    {
        return Hash::get((array)$this->entity->get($this->field), 'error') !== UPLOAD_ERR_OK;
    }
}

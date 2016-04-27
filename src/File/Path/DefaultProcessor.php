<?php
namespace Josegonzalez\Upload\File\Path;

use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Entity;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait as BasepathTrait;
use Josegonzalez\Upload\File\Path\Filename\DefaultTrait as FilenameTrait;
use Josegonzalez\Upload\File\Path\ProcessorInterface;

class DefaultProcessor implements ProcessorInterface
{
    /**
     * RepositoryInterface instance.
     *
     * @var \Cake\Datasource\RepositoryInterface
     */
    protected $repository;

    /**
     * Entity instance.
     *
     * @var \Cake\ORM\Entity
     */
    protected $entity;

    /**
     * Array of uploaded data for this field
     *
     * @var array
     */
    protected $data;

    /**
     * Name of field
     *
     * @var string
     */
    protected $field;

    /**
     * Settings for processing a path
     *
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param \Cake\Datasource\RepositoryInterface $repository the instance managing the entity
     * @param \Cake\ORM\Entity                     $entity the entity to construct a path for.
     * @param array                                $data the data being submitted for a save
     * @param string                               $field the field for which data will be saved
     * @param array                                $settings the settings for the current field
     */
    public function __construct(RepositoryInterface $repository, Entity $entity, $data, $field, $settings)
    {
        $this->repository = $repository;
        $this->entity = $entity;
        $this->data = $data;
        $this->field = $field;
        $this->settings = $settings;
    }

    use BasepathTrait;
    use FilenameTrait;
}

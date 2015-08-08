<?php
namespace Josegonzalez\Upload\Path;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\Hash;

abstract class AbstractPathProcessor
{
    /**
     * Table instance.
     *
     * @var \Cake\ORM\Table
     */
    protected $table;

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
     * Constructor.
     *
     * @param \Cake\ORM\Table        $table
     * @param \Cake\ORM\Entity       $entity
     * @param array                  $data
     * @param string                 $field
     * @param array                  $settings
     */
    public function __construct(Table $table, Entity $entity, $data, $field, $settings)
    {
        $this->table = $table;
        $this->entity = $entity;
        $this->data = $data;
        $this->field = $field;
        $this->settings = $settings;
    }

    /**
     * Returns the basepath for the current field/data combination
     *
     * @return string
     */
    abstract public function basepath();

    /**
     * Returns the filename for the current field/data combination
     *
     * @return string
     */
    abstract public function filename();
}

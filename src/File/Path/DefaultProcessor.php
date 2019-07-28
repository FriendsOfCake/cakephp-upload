<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Path;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait as BasepathTrait;
use Josegonzalez\Upload\File\Path\Filename\DefaultTrait as FilenameTrait;

class DefaultProcessor implements ProcessorInterface
{
    use BasepathTrait;
    use FilenameTrait;

    /**
     * Table instance.
     *
     * @var \Cake\ORM\Table
     */
    protected $table;

    /**
     * Entity instance.
     *
     * @var \Cake\Datasource\EntityInterface
     */
    protected $entity;

    /**
     * Array of uploaded data for this field or filename stored in db
     *
     * @var array|string
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
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param array|string     $data the data being submitted for a save or filename stored in db
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __construct(Table $table, EntityInterface $entity, $data, string $field, array $settings)
    {
        $this->table = $table;
        $this->entity = $entity;
        $this->data = $data;
        $this->field = $field;
        $this->settings = $settings;
    }
}

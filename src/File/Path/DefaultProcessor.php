<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Path;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait as BasepathTrait;
use Josegonzalez\Upload\File\Path\Filename\DefaultTrait as FilenameTrait;
use Psr\Http\Message\UploadedFileInterface;

class DefaultProcessor implements ProcessorInterface
{
    use BasepathTrait;
    use FilenameTrait;

    /**
     * Table instance.
     *
     * @var \Cake\ORM\Table
     */
    protected Table $table;

    /**
     * Entity instance.
     *
     * @var \Cake\Datasource\EntityInterface
     */
    protected EntityInterface $entity;

    /**
     * Instance of \Psr\Http\Message\UploadedFileInterface conaining the meta info from the file.
     *
     * @var \Psr\Http\Message\UploadedFileInterface|string
     */
    protected UploadedFileInterface|string $data;

    /**
     * Name of field
     *
     * @var string
     */
    protected string $field;

    /**
     * Settings for processing a path
     *
     * @var array
     */
    protected array $settings;

    /**
     * Constructor
     *
     * @param \Cake\ORM\Table $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param string|\Psr\Http\Message\UploadedFileInterface|array $data the data being submitted for a save or filename stored in db
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     */
    public function __construct(Table $table, EntityInterface $entity, string|UploadedFileInterface|array $data, string $field, array $settings)
    {
        $this->table = $table;
        $this->entity = $entity;
        $this->data = $data;
        $this->field = $field;
        $this->settings = $settings;
    }
}

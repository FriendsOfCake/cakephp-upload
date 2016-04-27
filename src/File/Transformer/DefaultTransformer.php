<?php
namespace Josegonzalez\Upload\File\Transformer;

use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Entity;
use Josegonzalez\Upload\File\Transformer\TransformerInterface;

class DefaultTransformer implements TransformerInterface
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

    /**
     * Creates a set of files from the initial data and returns them as key/value
     * pairs, where the path on disk maps to name which each file should have.
     * Example:
     *
     *   [
     *     '/tmp/path/to/file/on/disk' => 'file.pdf',
     *     '/tmp/path/to/file/on/disk-2' => 'file-preview.png',
     *   ]
     *
     * @return array key/value pairs of temp files mapping to their names
     */
    public function transform()
    {
        return [$this->data['tmp_name'] => $this->data['name']];
    }
}

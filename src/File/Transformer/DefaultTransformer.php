<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Transformer;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Psr\Http\Message\UploadedFileInterface;

class DefaultTransformer implements TransformerInterface
{
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
     * Array of uploaded data for this field
     *
     * @var \Psr\Http\Message\UploadedFileInterface
     */
    protected UploadedFileInterface $data;

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
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param \Psr\Http\Message\UploadedFileInterface $data the data being submitted for a save
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __construct(
        Table $table,
        EntityInterface $entity,
        UploadedFileInterface $data,
        string $field,
        array $settings
    ) {
        $this->table = $table;
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
     * @param string $filename Filename.
     * @return array key/value pairs of temp files mapping to their names
     */
    public function transform(string $filename): array
    {
        return [$this->data->getStream()->getMetadata('uri') => $filename];
    }
}

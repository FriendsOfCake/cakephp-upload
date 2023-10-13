<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Transformer;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Psr\Http\Message\UploadedFileInterface;

class DefaultTransformer implements TransformerInterface
{
    /**
     * Constructor
     *
     * @param \Cake\ORM\Table $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param \Psr\Http\Message\UploadedFileInterface $data the data being submitted for a save
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     */
    public function __construct(
        protected Table $table,
        protected EntityInterface $entity,
        protected UploadedFileInterface $data,
        protected string $field,
        protected array $settings
    ) {
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

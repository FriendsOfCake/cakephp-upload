<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Path;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Psr\Http\Message\UploadedFileInterface;

interface ProcessorInterface
{
    /**
     * Constructor
     *
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\Datasource\EntityInterface $entity the entity to construct a path for.
     * @param string|\Psr\Http\Message\UploadedFileInterface $data the data being submitted for a save or filename stored in db
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __construct(Table $table, EntityInterface $entity, string|UploadedFileInterface $data, string $field, array $settings);

    /**
     * Returns the basepath for the current field/data combination
     *
     * @return string
     */
    public function basepath(): string;

    /**
     * Returns the filename for the current field/data combination
     *
     * @return string
     */
    public function filename(): string;
}

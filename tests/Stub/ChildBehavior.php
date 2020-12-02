<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\Stub;

use Cake\Datasource\EntityInterface;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;
use Psr\Http\Message\UploadedFileInterface;

class ChildBehavior extends UploadBehavior
{
    protected $_defaultConfig = ['key' => 'value'];

    public function constructFiles(
        EntityInterface $entity,
        UploadedFileInterface $data,
        string $field,
        array $settings,
        array $pathinfo
    ): array {
        $files = parent::constructFiles($entity, $data, $field, $settings, $pathinfo);
        $this->constructedFiles = $files;

        return $files;
    }
}

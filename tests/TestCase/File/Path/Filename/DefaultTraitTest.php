<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Path\Filename;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\Filename\DefaultTrait;
use Laminas\Diactoros\UploadedFile;

class DefaultTraitTest extends TestCase
{
    private function createTraitMock()
    {
        return new class {
            use DefaultTrait;

            public $entity;
            public $table;
            public $settings;
            public $data;
            public $field;
        };
    }

    public function testFilename()
    {
        $mock = $this->createTraitMock();
        $mock->settings = [];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());

        $mock = $this->createTraitMock();
        $mock->settings = [
            'nameCallback' => 'not_callable',
        ];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());

        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->field = 'field';
        $mock->settings = [
            'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                return $data->getClientFilename();
            },
        ];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Path\Filename;

use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;

class DefaultTraitTest extends TestCase
{
    public function testFilename()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Filename\DefaultTrait');
        $mock->settings = [];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Filename\DefaultTrait');
        $mock->settings = [
            'nameCallback' => 'not_callable',
        ];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Filename\DefaultTrait');
        $mock->settings = [
            'nameCallback' => function ($data, $settings) {
                return $data->getClientFilename();
            },
        ];
        $mock->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'filename');
        $this->assertEquals('filename', $mock->filename());

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Filename\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
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

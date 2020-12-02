<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Path;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Laminas\Diactoros\UploadedFile;

class DefaultProcessorTest extends TestCase
{
    public function testIsProcessorInterface()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK);
        $field = 'field';
        $settings = [];
        $processor = new DefaultProcessor($table, $entity, $data, $field, $settings);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }
}

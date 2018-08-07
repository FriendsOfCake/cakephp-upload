<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Path;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\Base64Processor;

class Base64ProcessorTest extends TestCase
{
    public function testIsProcessorInterface()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['name' => 'filename'];
        $field = 'field';
        $settings = [];
        $processor = new Base64Processor($table, $entity, $data, $field, $settings);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }

    public function testRandomFileNameDefaultExtension()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['name' => 'filename'];
        $field = 'field';
        $settings = [];
        $processor = new Base64Processor($table, $entity, $data, $field, $settings);
        $fileName = $processor->filename();
        $found = strpos($fileName, '.png');
        $this->assertNotFalse($found);
    }

    public function testRandomFileNameCustomExtension()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['name' => 'filename'];
        $field = 'field';
        $settings = ['base64_extension' => '.cake'];
        $processor = new Base64Processor($table, $entity, $data, $field, $settings);
        $fileName = $processor->filename();
        $found = strpos($fileName, '.cake');
        $this->assertNotFalse($found);
    }
}

<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Path;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Josegonzalez\Upload\File\Path\ProcessorInterface;

class DefaultProcessorTest extends TestCase
{
    public function testIsProcessorInterface()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['name' => 'filename'];
        $field = 'field';
        $settings = [];
        $processor = new DefaultProcessor($table, $entity, $data, $field, $settings);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }
}

<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Path;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Josegonzalez\Upload\File\Path\ProcessorInterface;

class DefaultProcessorTest extends TestCase
{
    public function testIsProcessorInterface()
    {
        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $entity = $this->getMock('Cake\ORM\Entity');
        $data = ['name' => 'filename'];
        $field = 'field';
        $settings = [];
        $processor = new DefaultProcessor($repository, $entity, $data, $field, $settings);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }
}

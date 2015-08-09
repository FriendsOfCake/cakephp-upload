<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;
use Josegonzalez\Upload\File\Transformer\TransformerInterface;

class DefaultTransformerTest extends TestCase
{
    public function setup()
    {
        $entity = $this->getMock('Cake\ORM\Entity');
        $table = $this->getMock('Cake\ORM\Table');
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo.txt'];
        $field = 'field';
        $settings = [];
        $this->transformer = new DefaultTransformer($table, $entity, $data, $field, $settings);
    }

    public function teardown()
    {
        unset($this->transformer);
    }

    public function testIsProcessorInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Transformer\TransformerInterface', $this->transformer);
    }

    public function testTransform()
    {
        $this->assertEquals(['path/to/file' => 'foo.txt'], $this->transformer->transform());
    }
}

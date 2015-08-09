<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;

class DefaultTransformerTest extends TestCase
{
    public function testInvoke()
    {
        $entity = $this->getMock('Cake\ORM\Entity');
        $table = $this->getMock('Cake\ORM\Table');
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo.txt'];
        $field = 'field';
        $settings = [];

        $expected = ['path/to/file' => 'foo.txt'];

        $transformer = new DefaultTransformer;
        $this->assertEquals($expected, $transformer->__invoke($table, $entity, $data, $field, $settings));
        $this->assertEquals($expected, $transformer($table, $entity, $data, $field, $settings));
    }
}

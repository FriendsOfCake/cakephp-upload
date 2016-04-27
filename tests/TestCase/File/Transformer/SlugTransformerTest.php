<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\SlugTransformer;
use Josegonzalez\Upload\File\Transformer\TransformerInterface;

class SlugTransformerTest extends TestCase
{
    public function setup()
    {
        $entity = $this->getMock('Cake\ORM\Entity');
        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo é À.TXT'];
        $field = 'field';
        $settings = [];
        $this->transformer = new SlugTransformer($repository, $entity, $data, $field, $settings);
    }

    public function teardown()
    {
        unset($this->transformer);
    }

    public function testTransform()
    {
        $this->assertEquals(['path/to/file' => 'foo-e-a.txt'], $this->transformer->transform());
    }

    public function testTransformWithNoFileExt()
    {
        $entity = $this->getMock('Cake\ORM\Entity');
        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo é À'];
        $transformer = new SlugTransformer($repository, $entity, $data, 'field', []);
        $this->assertEquals(['path/to/file' => 'foo-e-a'], $transformer->transform());
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\SlugTransformer;

class SlugTransformerTest extends TestCase
{
    public function setUp(): void
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo é À.TXT'];
        $field = 'field';
        $settings = [];
        $this->transformer = new SlugTransformer($table, $entity, $data, $field, $settings);
    }

    public function testTransform()
    {
        $this->assertEquals(['path/to/file' => 'foo-e-a.txt'], $this->transformer->transform());
    }

    public function testTransformWithNoFileExt()
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo é À'];
        $transformer = new SlugTransformer($table, $entity, $data, 'field', []);
        $this->assertEquals(['path/to/file' => 'foo-e-a'], $transformer->transform());
    }
}

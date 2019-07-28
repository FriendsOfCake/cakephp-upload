<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;

class DefaultTransformerTest extends TestCase
{
    public function setUp(): void
    {
        $entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $data = ['tmp_name' => 'path/to/file', 'name' => 'foo.txt'];
        $field = 'field';
        $settings = [];
        $this->transformer = new DefaultTransformer($table, $entity, $data, $field, $settings);
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

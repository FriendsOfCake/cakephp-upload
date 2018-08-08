<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\Base64Transformer;
use VirtualFileSystem\FileSystem as Vfs;

class Base64TransformerTest extends TestCase
{
    public function setup()
    {
        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $this->data = ['data' => 'Y2FrZXBocA==', 'name' => '5a2e69ff-c2c0-44c1-94a7-d791202f0067.txt'];
        $this->field = 'field';
        $this->settings = [];
        $this->transformer = new Base64Transformer(
            $this->table,
            $this->entity,
            $this->data,
            $this->field,
            $this->settings
        );

        $this->vfs = new Vfs;
        mkdir($this->vfs->path('/tmp'));
        file_put_contents($this->vfs->path('/tmp/tempfile'), $this->data['data']);
    }

    public function teardown()
    {
        unset($this->transformer);
    }

    public function testTransform()
    {
        $this->transformer->setPath($this->vfs->path('/tmp/tempfile'));
        $expected = [$this->vfs->path('/tmp/tempfile') => '5a2e69ff-c2c0-44c1-94a7-d791202f0067.txt'];
        $this->assertEquals($expected, $this->transformer->transform());
    }

    public function testIsTransformerInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Transformer\TransformerInterface', $this->transformer);
    }
}

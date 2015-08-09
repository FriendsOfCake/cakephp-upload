<?php
namespace Josegonzalez\Upload\Test\TestCase\Database\Type;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Database\Type\FileType;

class FileTypeTest extends TestCase
{
    public function testMarshal()
    {
        $type = new FileType('field');
        $this->assertEquals('expected', $type->marshal('expected'));
        $this->assertEquals([], $type->marshal([]));
        $this->assertEquals(['key'], $type->marshal(['key']));
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\Database\Type;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Database\Type\FileType;

class FileTypeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->type = new FileType('field');
    }

    public function tearDown(): void
    {
        unset($this->type);
        parent::tearDown();
    }

    public function testMarshal()
    {
        $this->assertEquals('expected', $this->type->marshal('expected'));
        $this->assertEquals([], $this->type->marshal([]));
        $this->assertEquals(['key'], $this->type->marshal(['key']));
    }

    public function testToDatabase()
    {
        $driver = $this->getMockBuilder('Cake\Database\DriverInterface')->getMock();
        $this->assertEquals('expected', $this->type->toDatabase('expected', $driver));
        $this->assertEquals([], $this->type->toDatabase([], $driver));
        $this->assertEquals(['key'], $this->type->toDatabase(['key'], $driver));
    }

    public function testToPHP()
    {
        $driver = $this->getMockBuilder('Cake\Database\DriverInterface')->getMock();
        $this->assertEquals('expected', $this->type->toPHP('expected', $driver));
        $this->assertEquals([], $this->type->toPHP([], $driver));
        $this->assertEquals(['key'], $this->type->toPHP(['key'], $driver));
    }
}

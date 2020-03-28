<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase;

use Cake\Database\TypeFactory;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Plugin;

class PluginTest extends TestCase
{
    public function testBootstrap()
    {
        $mock = $this->getMockBuilder('Cake\Core\PluginApplicationInterface')->getMock();
        $plugin = new Plugin([]);
        $plugin->bootstrap($mock);
        $this->assertEquals('Josegonzalez\Upload\Database\Type\FileType', TypeFactory::getMap('upload.file'));
    }
}

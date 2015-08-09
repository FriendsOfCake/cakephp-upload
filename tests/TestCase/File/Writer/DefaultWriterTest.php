<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Writer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\FilesystemInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;

class DefaultWriterTest extends TestCase
{
    private $root;

    public function setup()
    {
        vfsStreamWrapper::register();
        $this->root = vfsStream::setup('root', null, ['file.txt' => 'content']);
    }

    public function testInvoke()
    {
        $writer = new DefaultWriter;
        $this->assertEquals([], $writer->__invoke([], 'field', []));
        $this->assertEquals([true], $writer->__invoke([
            vfsStream::url('root/file.txt') => 'file.txt'
        ], 'field', []));

        $this->assertEquals([false], $writer->__invoke([
            vfsStream::url('root/invalid.txt') => 'file.txt'
        ], 'field', ['filesystem.adapter' => new NullAdapter]));

        $this->assertEquals([], $writer([], 'field', []));
        $this->assertEquals([true], $writer([
            vfsStream::url('root/file.txt') => 'file.txt'
        ], 'field', []));

        $this->assertEquals([false], $writer([
            vfsStream::url('root/invalid.txt') => 'file.txt'
        ], 'field', ['filesystem.adapter' => new NullAdapter]));
    }

    public function testWriteFile()
    {
        $writer = new DefaultWriter;
        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('rename')->will($this->returnValue(true));
        $this->assertTrue($writer->writeFile($filesystem, vfsStream::url('root/file.txt'), 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(false));
        $filesystem->expects($this->exactly(2))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->never())->method('rename');
        $this->assertFalse($writer->writeFile($filesystem, vfsStream::url('root/file.txt'), 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('rename')->will($this->returnValue(false));
        $this->assertFalse($writer->writeFile($filesystem, vfsStream::url('root/file.txt'), 'path'));
    }

    public function testDeletePath()
    {
        $writer = new DefaultWriter;

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(true));
        $this->assertTrue($writer->deletePath($filesystem, 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(false));
        $this->assertFalse($writer->deletePath($filesystem, 'path'));
    }

    public function testGetFilesystem()
    {
        $writer = new DefaultWriter;
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $writer->getFilesystem('field', []));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $writer->getFilesystem('field', [
            'key' => 'value'
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => new NullAdapter
            ]
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => function () {
                    return new NullAdapter;
                },
            ]
        ]));
    }

    public function testGetFilesystemUnexpectedValueException()
    {
        $this->setExpectedException('UnexpectedValueException', 'Invalid Adapter for field field');

        $writer = new DefaultWriter;
        $writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => 'invalid_adapter'
            ]
        ]);
    }
}

<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Writer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem as Vfs;

class DefaultWriterTest extends TestCase
{
    protected $vfs;
    protected $writer;
    protected $entity;
    protected $repository;
    protected $data;
    protected $field;
    protected $settings;

    public function setup()
    {
        $this->entity = $this->getMock('Cake\ORM\Entity');
        $this->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $this->data = ['tmp_name' => 'path/to/file', 'name' => 'foo.txt'];
        $this->field = 'field';
        $this->settings = [
            'filesystem' => [
                'adapter' => function () {
                    return new VfsAdapter(new Vfs);
                }
            ]
        ];
        $this->writer = new DefaultWriter(
            $this->repository,
            $this->entity,
            $this->data,
            $this->field,
            $this->settings
        );

        $this->vfs = new Vfs;
        mkdir($this->vfs->path('/tmp'));
        file_put_contents($this->vfs->path('/tmp/tempfile'), 'content');
    }

    public function testIsWriterInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $this->writer);
    }

    public function testInvoke()
    {
        $this->assertEquals([], $this->writer->write([]));
        $this->assertEquals([true], $this->writer->write([
            $this->vfs->path('/tmp/tempfile') => 'file.txt'
        ], 'field', []));

        $this->assertEquals([false], $this->writer->write([
            $this->vfs->path('/tmp/invalid.txt') => 'file.txt'
        ], 'field', []));
    }

    public function testDelete()
    {
        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->at(0))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->at(1))->method('delete')->will($this->returnValue(false));
        $writer = $this->getMock('Josegonzalez\Upload\File\Writer\DefaultWriter', ['getFilesystem'], [$this->repository, $this->entity, $this->data, $this->field, $this->settings]);
        $writer->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $this->assertEquals([], $writer->delete([]));
        $this->assertEquals([true], $writer->delete([
            $this->vfs->path('/tmp/tempfile')
        ]));

        $this->assertEquals([false], $writer->delete([
            $this->vfs->path('/tmp/invalid.txt')
        ]));
    }

    public function testWriteFile()
    {
        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('rename')->will($this->returnValue(true));
        $this->assertTrue($this->writer->writeFile($filesystem, $this->vfs->path('/tmp/tempfile'), 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(false));
        $filesystem->expects($this->exactly(2))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->never())->method('rename');
        $this->assertFalse($this->writer->writeFile($filesystem, $this->vfs->path('/tmp/tempfile'), 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('rename')->will($this->returnValue(false));
        $this->assertFalse($this->writer->writeFile($filesystem, $this->vfs->path('/tmp/tempfile'), 'path'));
    }

    public function testDeletePath()
    {
        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(true));
        $this->assertTrue($this->writer->deletePath($filesystem, 'path'));

        $filesystem = $this->getMock('League\Flysystem\FilesystemInterface');
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(false));
        $this->assertFalse($this->writer->deletePath($filesystem, 'path'));
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $this->writer->getFilesystem('field', []));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $this->writer->getFilesystem('field', [
            'key' => 'value'
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $this->writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => new NullAdapter
            ]
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $this->writer->getFilesystem('field', [
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

        $this->writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => 'invalid_adapter'
            ]
        ]);
    }
}

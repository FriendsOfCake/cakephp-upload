<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Writer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Laminas\Diactoros\UploadedFile;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

class DefaultWriterTest extends TestCase
{
    protected $vfs;
    protected $writer;
    protected $entity;
    protected $table;
    protected $data;
    protected $field;
    protected $settings;

    public function setUp(): void
    {
        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $this->data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'foo.txt');
        $this->field = 'field';
        $this->settings = [
            'filesystem' => [
                'adapter' => function () {
                    return new InMemoryFilesystemAdapter();
                },
            ],
        ];
        $this->writer = new DefaultWriter(
            $this->table,
            $this->entity,
            $this->data,
            $this->field,
            $this->settings
        );
    }

    public function testIsWriterInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $this->writer);
    }

    public function testInvoke()
    {
        $this->assertEquals([], $this->writer->write([]));
        $this->assertEquals([true], $this->writer->write([
            '/tmp/tempfile' => 'file.txt',
        ], 'field', []));

        $this->assertEquals([false], $this->writer->write([
            '/tmp/invalid.txt' => 'file.txt',
        ], 'field', []));
    }

    public function testDelete()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->at(0))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->at(1))->method('delete')->will($this->returnValue(false));
        $writer = $this->getMockBuilder('Josegonzalez\Upload\File\Writer\DefaultWriter')
            ->setMethods(['getFilesystem'])
            ->setConstructorArgs([$this->table, $this->entity, $this->data, $this->field, $this->settings])
            ->getMock();
        $writer->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $this->assertEquals([], $writer->delete([]));
        $this->assertEquals([true], $writer->delete([
            '/tmp/tempfile',
        ]));

        $this->assertEquals([false], $writer->delete([
            '/tmp/invalid.txt',
        ]));
    }

    public function testWriteFile()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('move')->will($this->returnValue(true));
        $this->assertTrue($this->writer->writeFile($filesystem, '/tmp/tempfile', 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(false));
        $filesystem->expects($this->exactly(2))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->never())->method('move');
        $this->assertFalse($this->writer->writeFile($filesystem, '/tmp/tempfile', 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream')->will($this->returnValue(true));
        $filesystem->expects($this->exactly(3))->method('delete')->will($this->returnValue(true));
        $filesystem->expects($this->once())->method('move')->will($this->returnValue(false));
        $this->assertFalse($this->writer->writeFile($filesystem, '/tmp/tempfile', 'path'));
    }

    public function testDeletePath()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(true));
        $this->assertTrue($this->writer->deletePath($filesystem, 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->any())->method('delete')->will($this->returnValue(false));
        $this->assertFalse($this->writer->deletePath($filesystem, 'path'));
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf('League\Flysystem\FilesystemOperator', $this->writer->getFilesystem('field', []));
        $this->assertInstanceOf('League\Flysystem\FilesystemOperator', $this->writer->getFilesystem('field', [
            'key' => 'value',
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemOperator', $this->writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => new InMemoryFilesystemAdapter(),
            ],
        ]));
        $this->assertInstanceOf('League\Flysystem\FilesystemOperator', $this->writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => function () {
                    return new InMemoryFilesystemAdapter();
                },
            ],
        ]));
    }

    public function testGetFilesystemUnexpectedValueException()
    {
        $this->expectException('UnexpectedValueException', 'Invalid Adapter for field field');

        $this->writer->getFilesystem('field', [
            'filesystem' => [
                'adapter' => 'invalid_adapter',
            ],
        ]);
    }
}

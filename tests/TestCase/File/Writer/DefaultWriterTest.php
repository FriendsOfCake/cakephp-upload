<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Writer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Laminas\Diactoros\UploadedFile;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToWriteFile;
use org\bovigo\vfs\vfsStream as Vfs;

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
        $this->entity = $this->createStub('Cake\ORM\Entity');
        $this->table = $this->createStub('Cake\ORM\Table');
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
            $this->settings,
        );

        $this->vfs = Vfs::setup('tmp');
        file_put_contents($this->vfs->url() . '/tempfile', 'content');
    }

    public function testIsWriterInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $this->writer);
    }

    public function testInvoke()
    {
        $this->assertEquals([], $this->writer->write([]));
        $this->assertEquals([true], $this->writer->write([
            $this->vfs->url() . '/tempfile' => 'file.txt',
        ], 'field', []));

        $this->assertEquals([false], $this->writer->write([
            $this->vfs->url() . '/invalid.txt' => 'file.txt',
        ], 'field', []));
    }

    public function testDeleteSucess()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->once())->method('delete');

        $writer = $this->getMockBuilder('Josegonzalez\Upload\File\Writer\DefaultWriter')
            ->onlyMethods(['getFilesystem'])
            ->setConstructorArgs([$this->table, $this->entity, $this->data, $this->field, $this->settings])
            ->getMock();
        $writer->expects($this->atLeastOnce())->method('getFilesystem')->willReturn($filesystem);

        $this->assertEquals([], $writer->delete([]));
        $this->assertEquals([true], $writer->delete([
            $this->vfs->url() . '/tempfile',
        ]));
    }

    public function testDeleteFailure()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->once())->method('delete')->willThrowException(new UnableToDeleteFile());

        $writer = $this->getMockBuilder('Josegonzalez\Upload\File\Writer\DefaultWriter')
            ->onlyMethods(['getFilesystem'])
            ->setConstructorArgs([$this->table, $this->entity, $this->data, $this->field, $this->settings])
            ->getMock();
        $writer->expects($this->atLeastOnce())->method('getFilesystem')->willReturn($filesystem);

        $this->assertEquals([], $writer->delete([]));

        $this->assertEquals([false], $writer->delete([
            $this->vfs->url() . '/invalid.txt',
        ]));
    }

    public function testWriteFile()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream');
        $filesystem->expects($this->exactly(3))->method('delete');
        $filesystem->expects($this->once())->method('move');
        $this->assertTrue($this->writer->writeFile($filesystem, $this->vfs->url() . '/tempfile', 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream')->willThrowException(new UnableToWriteFile());
        $filesystem->expects($this->exactly(2))->method('delete');
        $filesystem->expects($this->never())->method('move');
        $this->assertFalse($this->writer->writeFile($filesystem, $this->vfs->url() . '/tempfile', 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')->getMock();
        $filesystem->expects($this->once())->method('writeStream');
        $filesystem->expects($this->exactly(3))->method('delete');
        $filesystem->expects($this->once())->method('move')->willThrowException(new UnableToMoveFile());
        $this->assertFalse($this->writer->writeFile($filesystem, $this->vfs->url() . '/tempfile', 'path'));
    }

    public function testDeletePath()
    {
        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->once())->method('delete');
        $this->assertTrue($this->writer->deletePath($filesystem, 'path'));

        $filesystem = $this->getMockBuilder('League\Flysystem\FilesystemOperator')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->once())->method('delete')->willThrowException(new UnableToDeleteFile());
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

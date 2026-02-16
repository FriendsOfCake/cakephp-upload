<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\DefaultProcessor;
use Josegonzalez\Upload\File\Transformer\SlugTransformer;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;
use Josegonzalez\Upload\Test\Stub\ChildBehavior;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

class UploadBehaviorTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Josegonzalez/Upload.Files',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $dataOk;

    /**
     * @var array<string, mixed>
     */
    protected array $configOk;

    /**
     * @var array<string, mixed>
     */
    protected array $dataError;

    /**
     * @var array<string, mixed>
     */
    protected array $configError;

    protected string $field;

    /**
     * @var array<string, mixed>
     */
    protected array $settings;

    /**
     * @var array<int, string>
     */
    protected array $behaviorMethods;

    public function setUp(): void
    {
        $this->dataOk = [
            'field' => new UploadedFile(
                fopen('php://temp', 'wb+'),
                1,
                UPLOAD_ERR_OK,
                'derp',
                'text/plain',
            ),
        ];

        $this->configOk = [
            'field' => [
                'keepFilesOnDelete' => false,
                'deleteCallback' => null,
            ],
        ];
        $this->dataError = [
            'field' => new UploadedFile(
                fopen('php://temp', 'wb+'),
                0,
                UPLOAD_ERR_NO_FILE,
                'derp',
            ),
        ];
        $this->configError = [
            'field' => [],
        ];
        $this->field = 'field';
        $this->settings = ['field' => []];

        $this->behaviorMethods = get_class_methods(UploadBehavior::class);
    }

    public function testInitialize()
    {
        $table = $this->createStub(Table::class);
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->onlyMethods(['setColumnType', 'hasColumn'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
            ->method('hasColumn')
            ->with('field')
            ->willReturn(true);
        $schema->expects($this->once())
                    ->method('setColumnType')
                    ->with('field', 'upload.file');
        $table->method('getSchema')
              ->willReturn($schema);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);

        $behavior->expects($this->exactly(1))
                 ->method('getConfig')
                 ->willReturn($this->settings);

        $behavior->initialize($this->settings);
    }

    public function testInheritedConfig()
    {
        $table = $this->getTableLocator()->get('Josegonzales/Upload.Files');
        $behavior = new ChildBehavior($table, []);

        $result = $behavior->getConfig();
        $expected = ['key' => 'value'];
        $this->assertEquals($expected, $result);
    }

    public function testInitializeIndexedConfig()
    {
        $settings = ['field'];
        $table = $this->createStub(Table::class);
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->onlyMethods(['setColumnType', 'hasColumn'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
            ->method('hasColumn')
            ->with('field')
            ->willReturn(true);
        $schema->expects($this->once())
               ->method('setColumnType')
               ->with('field', 'upload.file');
        $table->method('getSchema')
              ->willReturn($schema);

        $behavior = new UploadBehavior($table, []);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->getConfig());
    }

    public function testInitializeAddBehaviorOptionsInterfaceConfig()
    {
        $settings = [
            'className' => UploadBehavior::class,
            'field' => [],
        ];
        $table = $this->createStub(Table::class);
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->onlyMethods(['setColumnType', 'hasColumn'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
            ->method('hasColumn')
            ->with('field')
            ->willReturn(true);
        $schema->expects($this->once())
            ->method('setColumnType')
            ->with('field', 'upload.file');
        $table->method('getSchema')
              ->willReturn($schema);

        $behavior = new UploadBehavior($table, []);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->getConfig());
    }

    public function testBeforeMarshalOk()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->atLeastOnce())
                  ->method('isEmptyAllowed')
                  ->willReturn(true);

        $table = $this->getMockBuilder(Table::class)->getMock();
        $table->expects($this->atLeastOnce())
                    ->method('getValidator')
                    ->willReturn($validator);

        $behavior = new UploadBehavior($table, $this->settings);

        $data = new ArrayObject($this->dataOk);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject($this->dataOk), $data);

        $data = new ArrayObject();
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject([]), $data);
    }

    public function testBeforeMarshalError()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->willReturn(true);

        $table = $this->getMockBuilder(Table::class)->getMock();
        $table->expects($this->once())
                    ->method('getValidator')
                    ->willReturn($validator);

        $behavior = new UploadBehavior($table, $this->settings);

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject(), $data);
    }

    public function testBeforeMarshalEmptyAllowed()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->willReturn(false);

        $table = $this->getMockBuilder(Table::class)->getMock();
        $table->expects($this->once())
                    ->method('getValidator')
                    ->willReturn($validator);

        $behavior = new UploadBehavior($table, $this->settings);

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject($this->dataError), $data);
    }

    public function testBeforeMarshalDataAsArray()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->atLeastOnce())
                  ->method('isEmptyAllowed')
                  ->willReturn(true);

        $table = $this->getMockBuilder(Table::class)->getMock();
        $table->expects($this->atLeastOnce())
                    ->method('getValidator')
                    ->willReturn($validator);

        $behavior = new UploadBehavior($table, $this->settings);

        $data = new ArrayObject(
            $this->transformUploadedFilesToArray($this->dataOk),
        );
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject($this->transformUploadedFilesToArray($this->dataOk)), $data);

        $data = new ArrayObject(
            $this->transformUploadedFilesToArray($this->dataError),
        );
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject([]), $data);
    }

    public function testBeforeSaveNoUpload()
    {
        $originalValue = rand(1000, 9999);

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);

        $behavior = new UploadBehavior($table, $this->settings);
        $behavior->setConfig($this->settings);
        $entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->willReturn($this->dataError['field']);
        $entity->expects($this->any())
            ->method('getOriginal')
            ->with('field')
            ->willReturn($originalValue);
        $entity->expects($this->never())
            ->method('set')
            ->with('field', $originalValue);
        $entity->expects($this->never())
            ->method('setDirty')
            ->with('field', false);
        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject()));
    }

    public function testBeforeSaveNoWrite()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $processor = $this->createStub(DefaultProcessor::class);
        $writer = $this->createStub(DefaultWriter::class);

        $entity->method('isDirty')
               ->willReturn(true);
        $entity->method('get')
               ->willReturn($this->dataOk['field']);
        $writer->method('write')
               ->willReturn([false]);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter', 'constructFiles'])
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->settings);
        $behavior->expects($this->atLeastOnce())
                 ->method('getPathProcessor')
                 ->willReturn($processor);
        $behavior->expects($this->atLeastOnce())
                 ->method('getWriter')
                 ->willReturn($writer);
        $behavior->expects($this->atLeastOnce())
                 ->method('constructFiles')
                 ->willReturn([]);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject()));
    }

    public function testBeforeSaveOk()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $processor = $this->createStub(DefaultProcessor::class);
        $writer = $this->createStub(DefaultWriter::class);

        $entity->method('isDirty')
               ->willReturn(true);
        $entity->method('get')
               ->willReturn($this->dataOk['field']);
        $processor->method('filename')
                  ->willReturn('derp');
        $writer->method('write')
               ->willReturn([true]);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter', 'constructFiles'])
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->settings);
        $behavior->expects($this->atLeastOnce())
                 ->method('getPathProcessor')
                 ->willReturn($processor);
        $behavior->expects($this->atLeastOnce())
                 ->method('getWriter')
                 ->willReturn($writer);
        $behavior->expects($this->atLeastOnce())
                 ->method('constructFiles')
                 ->willReturn([]);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject()));
    }

    public function testBeforeSaveDoesNotRestoreOriginalValue()
    {
        $settings = $this->settings;
        $settings['field']['restoreValueOnFailure'] = false;

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);

        $behavior = new UploadBehavior($table, $this->settings);
        $behavior->setConfig($settings);
        $entity->expects($this->never())->method('getOriginal');
        $entity->expects($this->never())->method('set');

        $behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject());
    }

    public function testBeforeSaveWithProtectedFieldName()
    {
        $settings = $this->settings;
        $settings['priority'] = 11;

        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);

        $behavior = new UploadBehavior($table, $this->settings);
        $behavior->setConfig($settings);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject()));
    }

    public function testBeforeSaveWithFieldValueAsString()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);

        $behavior = new UploadBehavior($table, $this->settings);

        $entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->willReturn('file.jpg');
        $entity->expects($this->any())
            ->method('isDirty')
            ->with('field')
            ->willReturn(true);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $entity, new ArrayObject()));
    }

    public function testAfterDeleteOk()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $processor = $this->createStub(DefaultProcessor::class);
        $writer = $this->createStub(DefaultWriter::class);

        $entity->method('get')
               ->willReturn('file.txt');
        $writer->method('delete')
               ->willReturn([true]);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);
        $behavior->expects($this->atLeastOnce())
            ->method('getPathProcessor')
            ->willReturn($processor);
        $behavior->expects($this->atLeastOnce())
                 ->method('getWriter')
                 ->willReturn($writer);
        $event = new Event('fake.event');
        $behavior->afterDelete($event, $entity, new ArrayObject());
        $this->assertTrue($event->getResult());
    }

    public function testAfterDeleteFail()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $processor = $this->createStub(DefaultProcessor::class);
        $writer = $this->createStub(DefaultWriter::class);

        $entity->method('get')
               ->willReturn('file.txt');
        $writer->method('delete')
               ->willReturn([false]);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);
        $behavior->expects($this->atLeastOnce())
            ->method('getPathProcessor')
            ->willReturn($processor);
        $behavior->expects($this->atLeastOnce())
                 ->method('getWriter')
                 ->willReturn($writer);
        $event = new Event('fake.event');
        $behavior->afterDelete($event, $entity, new ArrayObject());
        $this->assertFalse($event->getResult());
    }

    public function testAfterDeleteSkip()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);

        // With configError (keepFilesOnDelete defaults to true), files are not deleted
        $behavior = new UploadBehavior($table, $this->dataError);
        $behavior->setConfig($this->configError);

        $event = new Event('fake.event');
        $behavior->afterDelete($event, $entity, new ArrayObject());
        $this->assertTrue($event->getResult());
    }

    public function testAfterDeleteUsesPathProcessorToDetectPathToTheFile()
    {
        $dir = '/some/path/';
        $field = 'file.txt';

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);
        $processor = $this->createMock(DefaultProcessor::class);
        $writer = $this->createMock(DefaultWriter::class);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $entity->expects($this->once())
            ->method('has')
            ->with('dir')
            ->willReturn(false);

        $entity->expects($this->exactly(3))
            ->method('get')
            ->with('field')
            ->willReturn($field);

        // expecting getPathProcessor to be called with right arguments for dataOk
        $behavior->expects($this->once())
            ->method('getPathProcessor')
            ->with($entity, $field, 'field', $this->configOk['field'])
            ->willReturn($processor);
        // basepath of processor should return our fake path
        $processor->expects($this->once())
            ->method('basepath')
            ->willReturn($dir);
        // expecting getWriter to be called with right arguments for dataOk
        $behavior->expects($this->once())
            ->method('getWriter')
            ->with($entity, null, 'field', $this->configOk['field'])
            ->willReturn($writer);
        // and here we check that file with right path will be deleted
        $writer->expects($this->once())
            ->method('delete')
            ->with([$dir . $field])
            ->willReturn([true]);

        $behavior->afterDelete(new Event('fake.event'), $entity, new ArrayObject());
    }

    public function testAfterDeletePrefersStoredPathOverPathProcessor()
    {
        $dir = '/some/path/';
        $field = 'file.txt';

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);
        $writer = $this->createMock(DefaultWriter::class);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $entity->expects($this->once())
            ->method('has')
            ->with('dir')
            ->willReturn(true);

        $entity->method('get')
            ->willReturnMap([
                ['dir', $dir],
                ['field', $field],
            ]);
        $behavior->expects($this->never())
            ->method('getPathProcessor');
        $behavior->expects($this->once())
            ->method('getWriter')
            ->willReturn($writer);

        $writer->expects($this->once())
            ->method('delete')
            ->with([$dir . $field])
            ->willReturn([true]);

        $event = new Event('fake.event');
        $behavior->afterDelete($event, $entity, new ArrayObject());
        $this->assertTrue($event->getResult());
    }

    public function testAfterDeleteNoDeleteCallback()
    {
        $field = (string)rand(1000, 9999);
        $path = rand(1000, 9999) . DIRECTORY_SEPARATOR;

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);
        $processor = $this->createMock(DefaultProcessor::class);
        $writer = $this->createMock(DefaultWriter::class);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();

        $this->configOk['field']['deleteCallback'] = null;

        $behavior->setConfig($this->configOk);
        $entity->expects($this->exactly(3))
            ->method('get')
            ->with('field')
            ->willReturn($field);
        $behavior->expects($this->once())->method('getPathProcessor')
            ->with($entity, $field, 'field', $this->configOk['field'])
            ->willReturn($processor);
        $processor->expects($this->once())->method('basepath')
            ->willReturn($path);
        $behavior->expects($this->once())->method('getWriter')
            ->with($entity, null, 'field', $this->configOk['field'])
            ->willReturn($writer);
        $writer->expects($this->once())
            ->method('delete')
            ->with([
                $path . $field,
            ])
            ->willReturn([true, true, true]);

        $behavior->afterDelete(new Event('fake.event'), $entity, new ArrayObject());
    }

    public function testAfterDeleteUsesDeleteCallback()
    {
        $field = (string)rand(1000, 9999);
        $path = rand(1000, 9999) . DIRECTORY_SEPARATOR;

        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);
        $processor = $this->createMock(DefaultProcessor::class);
        $writer = $this->createMock(DefaultWriter::class);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor', 'getWriter'])
            ->setConstructorArgs([$table, $this->dataOk])
            ->getMock();

        $this->configOk['field']['deleteCallback'] = function ($path, $entity, $field, $settings) {
            return [
                $path . $entity->get($field),
                $path . 'sm-' . $entity->get($field),
                $path . 'lg-' . $entity->get($field),
            ];
        };

        $behavior->setConfig($this->configOk);
        $entity->expects($this->exactly(5))
            ->method('get')
            ->with('field')
            ->willReturn($field);
        $behavior->expects($this->once())->method('getPathProcessor')
            ->with($entity, $field, 'field', $this->configOk['field'])
            ->willReturn($processor);
        $processor->expects($this->once())->method('basepath')
            ->willReturn($path);
        $behavior->expects($this->once())->method('getWriter')
            ->with($entity, null, 'field', $this->configOk['field'])
            ->willReturn($writer);
        $writer->expects($this->once())
            ->method('delete')
            ->with([
                $path . $field,
                $path . 'sm-' . $field,
                $path . 'lg-' . $field,
            ])
            ->willReturn([true, true, true]);

        $behavior->afterDelete(new Event('fake.event'), $entity, new ArrayObject());
    }

    public function testAfterDeleteWithProtectedFieldName()
    {
        $settings = $this->settings;
        $settings['priority'] = 11;

        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);

        // 'priority' is a protected field name, so it's skipped
        $behavior = new UploadBehavior($table, $settings);

        $event = new Event('fake.event');
        $behavior->afterDelete($event, $entity, new ArrayObject());
        $this->assertTrue($event->getResult());
    }

    public function testAfterDeleteWithNullableFileField()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createMock(Entity::class);

        $behavior = $this->getMockBuilder(UploadBehavior::class)
            ->onlyMethods(['getPathProcessor'])
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $entity->expects($this->once())
            ->method('get')
            ->with('field')
            ->willReturn(null);

        $behavior->expects($this->never())
            ->method('getPathProcessor');

        $behavior->afterDelete(new Event('fake.event'), $entity, new ArrayObject());
    }

    public function testGetWriter()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $processor = $behavior->getWriter($entity, new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'), 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $processor);
    }

    public function testConstructFiles()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            ['basepath' => 'path', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'path/file.txt'], $files);

        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            ['basepath' => 'some/path', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithBasePathEndingDS()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            ['basepath' => 'path/', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'path/file.txt'], $files);

        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            ['basepath' => 'some/path/', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithCallable()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $callable = function () {
            return ['php://temp' => 'file.text'];
        };
        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            ['transformer' => $callable],
            ['basepath' => 'some/path', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesWithCallableAndBasePathEndingDS()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $callable = function () {
            return ['php://temp' => 'file.text'];
        };
        $files = $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK),
            'field',
            ['transformer' => $callable],
            ['basepath' => 'some/path', 'filename' => 'file.txt'],
        );
        $this->assertEquals(['php://temp' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesException()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $this->expectException('UnexpectedValueException', "'transformer' not set to instance of TransformerInterface: UnexpectedValueException");
        $behavior->constructFiles(
            $entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            ['transformer' => 'UnexpectedValueException'],
            ['basepath' => 'path', 'filename' => 'file.txt'],
        );
    }

    public function testGetPathProcessor()
    {
        $table = $this->createStub(Table::class);
        $entity = $this->createStub(Entity::class);
        $behavior = new UploadBehavior($table, []);

        $processor = $behavior->getPathProcessor($entity, new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK), 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }

    public function testNameCallback()
    {
        $table = $this->getTableLocator()->get('Files');
        $behavior = new ChildBehavior($table, [
            'filename' => [
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return 'Awesome Filename.png';
                },
                'transformer' => SlugTransformer::class,
            ],
        ]);

        $event = new Event('Model.beforeSave', $table);
        $entity = new Entity([
            'filename' => $this->dataOk['field'],
        ]);

        $behavior->beforeSave($event, $entity, new ArrayObject());

        $expected = [
            'php://temp' => 'webroot/files/Files/filename/awesome-filename.png',
        ];

        $this->assertEquals($expected, $behavior->constructedFiles);
    }

    private function transformUploadedFilesToArray(array $data): array
    {
        return array_map(
            function (UploadedFileInterface $file) {
                return [
                    'tmp_name' => '',
                    'error' => $file->getError(),
                    'name' => $file->getClientFilename(),
                    'type' => $file->getClientMediaType(),
                    'size' => $file->getSize(),
                ];
            },
            $data,
        );
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;
use Josegonzalez\Upload\Test\Stub\ChildBehavior;
use Laminas\Diactoros\UploadedFile;
use ReflectionClass;

class UploadBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Josegonzalez/Upload.Files',
    ];

    public function setUp(): void
    {
        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $this->dataOk = [
            'field' => new UploadedFile(
                fopen('php://temp', 'wb+'),
                1,
                UPLOAD_ERR_OK,
                'derp',
                'text/plain'
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
                'derp'
            ),
        ];
        $this->configError = [
            'field' => [],
        ];
        $this->field = 'field';
        $this->settings = ['field' => []];

        $this->behavior = new UploadBehavior($this->table, []);
        $this->processor = $this->getMockBuilder('Josegonzalez\Upload\File\Path\DefaultProcessor')
            ->setMethods([])
            ->setConstructorArgs([$this->table, $this->entity, $this->dataOk[$this->field], $this->field, $this->settings])
            ->getMock();
        $this->writer = $this->getMockBuilder('Josegonzalez\Upload\File\Writer\DefaultWriter')
            ->setMethods([])
            ->setConstructorArgs([$this->table, $this->entity, $this->dataOk[$this->field], $this->field, $this->settings])
            ->getMock();
        $this->behaviorMethods = get_class_methods('Josegonzalez\Upload\Model\Behavior\UploadBehavior');
    }

    public function testInitialize()
    {
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->setMethods(['setColumnType', 'getSchema', 'setSchema'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
                    ->method('setColumnType')
                    ->with('field', 'upload.file');
        $table->expects($this->at(0))
                    ->method('getSchema')
                    ->will($this->returnValue($schema));
        $table->expects($this->at(1))
                    ->method('setSchema')
                    ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);

        $behavior->expects($this->exactly(1))
                 ->method('getConfig')
                 ->will($this->returnValue($this->settings));

        $behavior->initialize($this->settings);
    }

    public function testInheritedConfig()
    {
        $table = TableRegistry::get('Josegonzales/Upload.Files');
        $behavior = new ChildBehavior($table, []);

        $result = $behavior->getConfig();
        $expected = ['key' => 'value'];
        $this->assertEquals($expected, $result);
    }

    public function testInitializeIndexedConfig()
    {
        $settings = ['field'];
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->setMethods(['setColumnType', 'getSchema', 'setSchema'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
               ->method('setColumnType')
               ->with('field', 'upload.file');
        $table->expects($this->at(0))
              ->method('getSchema')
              ->will($this->returnValue($schema));
        $table->expects($this->at(1))
              ->method('setSchema')
              ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->getConfig());
    }

    public function testInitializeAddBehaviorOptionsInterfaceConfig()
    {
        $settings = [
            'className' => 'Josegonzalez\Upload\Model\Behavior\UploadBehavior',
            'field' => [],
        ];
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $schema = $this->getMockBuilder('Cake\Database\Schema\TableSchema')
            ->setMethods(['setColumnType', 'getSchema', 'setSchema'])
            ->disableOriginalConstructor()
            ->getMock();
        $schema->expects($this->once())
            ->method('setColumnType')
            ->with('field', 'upload.file');
        $table->expects($this->at(0))
            ->method('getSchema')
            ->will($this->returnValue($schema));
        $table->expects($this->at(1))
            ->method('setSchema')
            ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize', 'setConfig', 'getConfig']);
        //$behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $settings], '', false);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->getConfig());
    }

    public function testBeforeMarshalOk()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(true));

        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $table->expects($this->once())
                    ->method('getValidator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->expects($this->any())
                 ->method('getConfig')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataOk);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject($this->dataOk), $data);
    }

    public function testBeforeMarshalError()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(true));

        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $table->expects($this->once())
                    ->method('getValidator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->expects($this->any())
                 ->method('getConfig')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject(), $data);
    }

    public function testBeforeMarshalEmptyAllowed()
    {
        $validator = $this->getMockBuilder('Cake\Validation\Validator')->getMock();
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(false));

        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $table->expects($this->once())
                    ->method('getValidator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$table, $this->settings])
            ->getMock();
        $behavior->expects($this->any())
                 ->method('getConfig')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject());
        $this->assertEquals(new ArrayObject($this->dataError), $data);
    }

    public function testBeforeSaveNoUpload()
    {
        $originalValue = rand(1000, 9999);

        $methods = array_diff($this->behaviorMethods, ['beforeSave', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->settings);
        $this->entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->will($this->returnValue($this->dataError['field']));
        $this->entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->will($this->returnValue($this->dataError['field']));
        $this->entity->expects($this->any())
            ->method('getOriginal')
            ->with('field')
            ->will($this->returnValue($originalValue));
        $this->entity->expects($this->never())
            ->method('set')
            ->with('field', $originalValue);
        $this->entity->expects($this->never())
            ->method('setDirty')
            ->with('field', false);
        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testBeforeSaveNoWrite()
    {
        $methods = array_diff($this->behaviorMethods, ['beforeSave', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->settings);
        $this->entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->will($this->returnValue($this->dataOk['field']));
        $behavior->expects($this->any())
                 ->method('getPathProcessor')
                 ->will($this->returnValue($this->processor));
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $behavior->expects($this->any())
                 ->method('constructFiles')
                 ->will($this->returnValue([]));
        $this->writer->expects($this->any())
                     ->method('write')
                     ->will($this->returnValue([false]));

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testBeforeSaveOk()
    {
        $methods = array_diff($this->behaviorMethods, ['beforeSave', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->settings])
            ->getMock();
        $behavior->setConfig($this->settings);
        $this->entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->will($this->returnValue($this->dataOk['field']));
        $behavior->expects($this->any())
                 ->method('getPathProcessor')
                 ->will($this->returnValue($this->processor));
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $behavior->expects($this->any())
                 ->method('constructFiles')
                 ->will($this->returnValue([]));
        $this->processor->expects($this->any())
                ->method('filename')
                ->will($this->returnValue('derp'));
        $this->writer->expects($this->any())
                     ->method('write')
                     ->will($this->returnValue([true]));

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testBeforeSaveDoesNotRestoreOriginalValue()
    {
        $settings = $this->settings;
        $settings['field']['restoreValueOnFailure'] = false;

        $methods = array_diff($this->behaviorMethods, ['beforeSave', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->settings])
            ->getMock();
        $behavior->setConfig($settings);
        $this->entity->expects($this->never())->method('getOriginal');
        $this->entity->expects($this->never())->method('set');

        $behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject());
    }

    public function testBeforeSaveWithProtectedFieldName()
    {
        $settings = $this->settings;
        $settings['priority'] = 11;

        $methods = array_diff($this->behaviorMethods, ['beforeSave', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->settings])
            ->getMock();
        $behavior->setConfig($settings);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testAfterDeleteOk()
    {
        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $behavior->expects($this->any())
            ->method('getPathProcessor')
            ->willReturn($this->processor);
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
                     ->method('delete')
                     ->will($this->returnValue([true]));

        $this->assertTrue($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testAfterDeleteFail()
    {
        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $behavior->expects($this->any())
            ->method('getPathProcessor')
            ->willReturn($this->processor);
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
                     ->method('delete')
                     ->will($this->returnValue([false]));

        $this->assertFalse($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testAfterDeleteSkip()
    {
        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataError])
            ->getMock();
        $behavior->setConfig($this->configError);

        $behavior->expects($this->any())
            ->method('getWriter')
            ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
            ->method('delete')
            ->will($this->returnValue([true]));

        $this->assertTrue($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testAfterDeleteUsesPathProcessorToDetectPathToTheFile()
    {
        $dir = '/some/path/';
        $field = 'file.txt';

        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $this->entity->expects($this->at(0))
            ->method('has')
            ->with('dir')
            ->will($this->returnValue(false));
        $this->entity->expects($this->at(1))
            ->method('get')
            ->with('field')
            ->will($this->returnValue($field));
        $this->entity->expects($this->at(2))
            ->method('get')
            ->with('field')
            ->will($this->returnValue($field));

        // expecting getPathProcessor to be called with right arguments for dataOk
        $behavior->expects($this->once())
            ->method('getPathProcessor')
            ->with($this->entity, $field, 'field', $this->configOk['field'])
            ->willReturn($this->processor);
        // basepath of processor should return our fake path
        $this->processor->expects($this->once())
            ->method('basepath')
            ->willReturn($dir);
        // expecting getWriter to be called with right arguments for dataOk
        $behavior->expects($this->once())
            ->method('getWriter')
            ->with($this->entity, null, 'field', $this->configOk['field'])
            ->willReturn($this->writer);
        // and here we check that file with right path will be deleted
        $this->writer->expects($this->once())
            ->method('delete')
            ->with([$dir . $field])
            ->willReturn([true]);

        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject());
    }

    public function testAfterDeletePrefersStoredPathOverPathProcessor()
    {
        $dir = '/some/path/';
        $field = 'file.txt';

        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();
        $behavior->setConfig($this->configOk);

        $this->entity->expects($this->at(0))
            ->method('has')
            ->with('dir')
            ->will($this->returnValue(true));
        $this->entity->expects($this->at(1))
            ->method('get')
            ->with('dir')
            ->will($this->returnValue($dir));
        $this->entity->expects($this->at(2))
            ->method('get')
            ->with('field')
            ->will($this->returnValue($field));

        $behavior->expects($this->never())
            ->method('getPathProcessor');
        $behavior->expects($this->once())
            ->method('getWriter')
            ->will($this->returnValue($this->writer));

        $this->writer->expects($this->once())
            ->method('delete')
            ->with([$dir . $field])
            ->will($this->returnValue([true]));

        $this->assertTrue($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testAfterDeleteNoDeleteCallback()
    {
        $this->entity->field = rand(1000, 9999);
        $path = rand(1000, 9999) . DIRECTORY_SEPARATOR;
        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();

        $this->configOk['field']['deleteCallback'] = null;

        $behavior->setConfig($this->configOk);
        $behavior->expects($this->once())->method('getPathProcessor')
            ->with($this->entity, $this->entity->field, 'field', $this->configOk['field'])
            ->willReturn($this->processor);
        $this->processor->expects($this->once())->method('basepath')
            ->willReturn($path);
        $behavior->expects($this->once())->method('getWriter')
            ->with($this->entity, null, 'field', $this->configOk['field'])
            ->willReturn($this->writer);
        $this->writer->expects($this->once())
            ->method('delete')
            ->with([
                $path . $this->entity->field,
            ])
            ->willReturn([true, true, true]);

        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject());
    }

    public function testAfterDeleteUsesDeleteCallback()
    {
        $this->entity->field = rand(1000, 9999);
        $path = rand(1000, 9999) . DIRECTORY_SEPARATOR;
        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $this->dataOk])
            ->getMock();

        $this->configOk['field']['deleteCallback'] = function ($path, $entity, $field, $settings) {
            return [
                $path . $entity->{$field},
                $path . 'sm-' . $entity->{$field},
                $path . 'lg-' . $entity->{$field},
            ];
        };

        $behavior->setConfig($this->configOk);
        $behavior->expects($this->once())->method('getPathProcessor')
            ->with($this->entity, $this->entity->field, 'field', $this->configOk['field'])
            ->willReturn($this->processor);
        $this->processor->expects($this->once())->method('basepath')
            ->willReturn($path);
        $behavior->expects($this->once())->method('getWriter')
            ->with($this->entity, null, 'field', $this->configOk['field'])
            ->willReturn($this->writer);
        $this->writer->expects($this->once())
            ->method('delete')
            ->with([
                $path . $this->entity->field,
                $path . 'sm-' . $this->entity->field,
                $path . 'lg-' . $this->entity->field,
            ])
            ->willReturn([true, true, true]);

        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject());
    }

    public function testAfterDeleteWithProtectedFieldName()
    {
        $settings = $this->settings;
        $settings['priority'] = 11;

        $methods = array_diff($this->behaviorMethods, ['afterDelete', 'config', 'setConfig', 'getConfig']);
        $behavior = $this->getMockBuilder('Josegonzalez\Upload\Model\Behavior\UploadBehavior')
            ->setMethods($methods)
            ->setConstructorArgs([$this->table, $settings])
            ->getMock();

        $behavior->expects($this->any())
            ->method('getWriter')
            ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
            ->method('delete')
            ->will($this->returnValue([true]));

        $this->assertTrue($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject()));
    }

    public function testGetWriter()
    {
        $processor = $this->behavior->getWriter($this->entity, new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'), 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $processor);
    }

    public function testConstructFiles()
    {
        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            'path'
        );
        $this->assertEquals(['php://temp' => 'path/file.txt'], $files);

        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            'some/path'
        );
        $this->assertEquals(['php://temp' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithBasePathEndingDS()
    {
        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            'path/'
        );
        $this->assertEquals(['php://temp' => 'path/file.txt'], $files);

        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            [],
            'some/path/'
        );
        $this->assertEquals(['php://temp' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithCallable()
    {
        $callable = function () {
            return ['php://temp' => 'file.text'];
        };
        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            ['transformer' => $callable],
            'some/path'
        );
        $this->assertEquals(['php://temp' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesWithCallableAndBasePathEndingDS()
    {
        $callable = function () {
            return ['php://temp' => 'file.text'];
        };
        $files = $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK),
            'field',
            ['transformer' => $callable],
            'some/path/'
        );
        $this->assertEquals(['php://temp' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesException()
    {
        $this->expectException('UnexpectedValueException', "'transformer' not set to instance of TransformerInterface: UnexpectedValueException");
        $this->behavior->constructFiles(
            $this->entity,
            new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK, 'file.txt'),
            'field',
            ['transformer' => 'UnexpectedValueException'],
            'path'
        );
    }

    public function testGetPathProcessor()
    {
        $processor = $this->behavior->getPathProcessor($this->entity, new UploadedFile(fopen('php://temp', 'rw+'), 1, UPLOAD_ERR_OK), 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }
}

<?php
namespace Josegonzalez\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;
use Josegonzalez\Upload\Test\Stub\ChildBehavior;
use ReflectionClass;

class UploadBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Josegonzalez/Upload.Files',
    ];

    public function setup()
    {
        $this->entity = $this->getMock('Cake\ORM\Entity');
        $this->table = $this->getMock('Cake\ORM\Table');
        $this->dataOk = [
            'field' => [
                'tmp_name' => 'path/to/file',
                'name' => 'derp',
                'error' => UPLOAD_ERR_OK,
                'size' => 1,
                'type' => 'text',
                'keepFilesOnDelete' => false
            ]
        ];
        $this->dataError = [
            'field' => [
                'tmp_name' => 'path/to/file',
                'name' => 'derp',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
                'type' => '',
            ]
        ];
        $this->field = 'field';
        $this->settings = ['field' => []];

        $this->behavior = new UploadBehavior($this->table, []);
        $this->processor = $this->getMock('Josegonzalez\Upload\File\Path\DefaultProcessor', [], [$this->table, $this->entity, $this->dataOk, $this->field, $this->settings]);
        $this->writer = $this->getMock('Josegonzalez\Upload\File\Writer\DefaultWriter', [], [$this->table, $this->entity, $this->dataOk, $this->field, $this->settings]);
        $this->behaviorMethods = get_class_methods('Josegonzalez\Upload\Model\Behavior\UploadBehavior');
    }

    public function testInitialize()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $schema = $this->getMock('Cake\Database\Schema\Table', [], [$table, []]);
        $schema->expects($this->once())
                    ->method('columnType')
                    ->with('field', 'upload.file');
        $table->expects($this->at(0))
                    ->method('schema')
                    ->will($this->returnValue($schema));
        $table->expects($this->at(1))
                    ->method('schema')
                    ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $this->settings], '', false);
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);

        $behavior->expects($this->exactly(2))
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $behavior->initialize($this->settings);
    }

    public function testInheritedConfig()
    {
        $table = TableRegistry::get('Josegonzales/Upload.Files');
        $behavior = new ChildBehavior($table, []);

        $result = $behavior->config();
        $expected = ['key' => 'value'];
        $this->assertEquals($expected, $result);
    }

    public function testInitializeIndexedConfig()
    {
        $settings = ['field'];
        $table = $this->getMock('Cake\ORM\Table');
        $schema = $this->getMock('Cake\Database\Schema\Table', [], [$table, []]);
        $schema->expects($this->once())
               ->method('columnType')
               ->with('field', 'upload.file');
        $table->expects($this->at(0))
              ->method('schema')
              ->will($this->returnValue($schema));
        $table->expects($this->at(1))
              ->method('schema')
              ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize', 'config']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $settings], '', false);
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $table);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->config());
    }

    public function testBeforeMarshalOk()
    {
        $validator = $this->getMock('Cake\Validation\Validator');
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(true));

        $table = $this->getMock('Cake\ORM\Table');
        $table->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $this->settings]);
        $behavior->expects($this->any())
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataOk);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject($this->dataOk), $data);
    }

    public function testBeforeMarshalError()
    {
        $validator = $this->getMock('Cake\Validation\Validator');
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(true));

        $table = $this->getMock('Cake\ORM\Table');
        $table->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $this->settings]);
        $behavior->expects($this->any())
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject, $data);
    }

    public function testBeforeMarshalEmptyAllowed()
    {
        $validator = $this->getMock('Cake\Validation\Validator');
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(false));

        $table = $this->getMock('Cake\ORM\Table');
        $table->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$table, $this->settings]);
        $behavior->expects($this->any())
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject($this->dataError), $data);
    }

    public function testBeforeSaveUploadError()
    {
        $originalValue = rand(1000, 9999);

        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->config($this->settings);
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
        $this->entity->expects($this->once())
            ->method('set')
            ->with('field', $originalValue);
        $this->entity->expects($this->once())
            ->method('dirty')
            ->with('field', false);
        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveWriteFail()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->config($this->settings);
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

        $this->assertFalse($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveOk()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->config($this->settings);
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
                     ->will($this->returnValue([true]));

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveDoesNotRestoreOriginalValue()
    {
        $settings = $this->settings;
        $settings['field']['restoreValueOnFailure'] = false;

        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->config($settings);
        $this->entity->expects($this->never())->method('getOriginal');
        $this->entity->expects($this->never())->method('set');
        $behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject);
    }

    public function testAfterDeleteOk()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->dataOk]);
        $behavior->config($this->dataOk);

        $behavior->expects($this->any())
            ->method('getPathProcessor')
            ->willReturn($this->processor);
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
                     ->method('delete')
                     ->will($this->returnValue([true]));

        $this->assertNull($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testAfterDeleteFail()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->dataOk]);
        $behavior->config($this->dataOk);

        $behavior->expects($this->any())
            ->method('getPathProcessor')
            ->willReturn($this->processor);
        $behavior->expects($this->any())
                 ->method('getWriter')
                 ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
                     ->method('delete')
                     ->will($this->returnValue([false]));

        $this->assertFalse($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testAfterDeleteSkip()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->dataError]);
        $behavior->config($this->dataError);

        $behavior->expects($this->any())
            ->method('getWriter')
            ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
            ->method('delete')
            ->will($this->returnValue([true]));

        $this->assertNull($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testAfterDeleteUsesPathProcessorToDetectPathToTheFile()
    {
        $this->entity->field = rand(1000, 9999);
        $path = rand(1000, 9999) . DIRECTORY_SEPARATOR;
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->dataOk]);
        $behavior->config($this->dataOk);

        // expecting getPathProcessor to be called with right arguments for dataOk
        $behavior->expects($this->once())->method('getPathProcessor')
            ->with($this->entity, $this->entity->field, 'field', $this->dataOk['field'])
            ->willReturn($this->processor);
        // basepath of processor should return our fake path
        $this->processor->expects($this->once())->method('basepath')
            ->willReturn($path);
        // expecting getWriter to be called with right arguments for dataOk
        $behavior->expects($this->once())->method('getWriter')
            ->with($this->entity, [], 'field', $this->dataOk['field'])
            ->willReturn($this->writer);
        // and here we check that file with right path will be deleted
        $this->writer->expects($this->once())->method('delete')
            ->with([$path . $this->entity->field])
            ->willReturn([true]);

        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject);
    }

    public function testGetWriter()
    {
        $processor = $this->behavior->getWriter($this->entity, [], 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Writer\WriterInterface', $processor);
    }

    public function testGetWriterException()
    {
        $this->setExpectedException('UnexpectedValueException', "'writer' not set to instance of WriterInterface: UnexpectedValueException");
        $this->behavior->getWriter($this->entity, [], 'field', ['writer' => 'UnexpectedValueException']);
    }

    public function testConstructFiles()
    {
        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            [],
            'path'
        );
        $this->assertEquals(['path/to/file/on/disk' => 'path/file.txt'], $files);

        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            [],
            'some/path'
        );
        $this->assertEquals(['path/to/file/on/disk' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithBasePathEndingDS()
    {
        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            [],
            'path/'
        );
        $this->assertEquals(['path/to/file/on/disk' => 'path/file.txt'], $files);

        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            [],
            'some/path/'
        );
        $this->assertEquals(['path/to/file/on/disk' => 'some/path/file.txt'], $files);
    }

    public function testConstructFilesWithCallable()
    {
        $callable = function () {
            return ['path/to/callable/file/on/disk' => 'file.text'];
        };
        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            ['transformer' => $callable],
            'some/path'
        );
        $this->assertEquals(['path/to/callable/file/on/disk' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesWithCallableAndBasePathEndingDS()
    {
        $callable = function () {
            return ['path/to/callable/file/on/disk' => 'file.text'];
        };
        $files = $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            ['transformer' => $callable],
            'some/path/'
        );
        $this->assertEquals(['path/to/callable/file/on/disk' => 'some/path/file.text'], $files);
    }

    public function testConstructFilesException()
    {
        $this->setExpectedException('UnexpectedValueException', "'transformer' not set to instance of TransformerInterface: UnexpectedValueException");
        $this->behavior->constructFiles(
            $this->entity,
            ['tmp_name' => 'path/to/file/on/disk', 'name' => 'file.txt'],
            'field',
            ['transformer' => 'UnexpectedValueException'],
            'path'
        );
    }

    public function testGetPathProcessor()
    {
        $processor = $this->behavior->getPathProcessor($this->entity, [], 'field', []);
        $this->assertInstanceOf('Josegonzalez\Upload\File\Path\ProcessorInterface', $processor);
    }

    public function testGetPathProcessorException()
    {
        $this->setExpectedException('UnexpectedValueException', "'pathProcessor' not set to instance of ProcessorInterface: UnexpectedValueException");
        $this->behavior->getPathProcessor($this->entity, [], 'field', ['pathProcessor' => 'UnexpectedValueException']);
    }
}

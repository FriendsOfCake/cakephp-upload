<?php
namespace Josegonzalez\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;
use ReflectionClass;

class UploadBehaviorTest extends TestCase
{
    public function setup()
    {
        $this->entity = $this->getMock('Cake\ORM\Entity');
        $this->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
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

        $this->behavior = new UploadBehavior($this->repository, []);
        $this->processor = $this->getMock('Josegonzalez\Upload\File\Path\DefaultProcessor', [], [$this->repository, $this->entity, $this->dataOk, $this->field, $this->settings]);
        $this->writer = $this->getMock('Josegonzalez\Upload\File\Writer\DefaultWriter', [], [$this->repository, $this->entity, $this->dataOk, $this->field, $this->settings]);
        $this->behaviorMethods = get_class_methods('Josegonzalez\Upload\Model\Behavior\UploadBehavior');
    }

    public function testInitialize()
    {
        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $schema = $this->getMock('Cake\Database\Schema\Table', [], [$repository, []]);
        $schema->expects($this->once())
                    ->method('columnType')
                    ->with('field', 'upload.file');
        $repository->expects($this->at(0))
                    ->method('schema')
                    ->will($this->returnValue($schema));
        $repository->expects($this->at(1))
                    ->method('schema')
                    ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$repository, $this->settings], '', false);
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $repository);

        $behavior->expects($this->exactly(2))
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $behavior->initialize($this->settings);
    }

    public function testInitializeIndexedConfig()
    {
        $settings = ['field'];
        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $schema = $this->getMock('Cake\Database\Schema\Table', [], [$repository, []]);
        $schema->expects($this->once())
               ->method('columnType')
               ->with('field', 'upload.file');
        $repository->expects($this->at(0))
              ->method('schema')
              ->will($this->returnValue($schema));
        $repository->expects($this->at(1))
              ->method('schema')
              ->will($this->returnValue($schema));

        $methods = array_diff($this->behaviorMethods, ['initialize', 'config']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$repository, $settings], '', false);
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('_table');
        $property->setAccessible(true);
        $property->setValue($behavior, $repository);
        $behavior->initialize($settings);

        $this->assertEquals(['field' => []], $behavior->config());
    }

    public function testBeforeMarshalOk()
    {
        $validator = $this->getMock('Cake\Validation\Validator');
        $validator->expects($this->once())
                  ->method('isEmptyAllowed')
                  ->will($this->returnValue(true));

        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $repository->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$repository, $this->settings]);
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

        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $repository->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$repository, $this->settings]);
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

        $repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $repository->expects($this->once())
                    ->method('validator')
                    ->will($this->returnValue($validator));

        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$repository, $this->settings]);
        $behavior->expects($this->any())
                 ->method('config')
                 ->will($this->returnValue($this->settings));

        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        $this->assertEquals(new ArrayObject($this->dataError), $data);
    }

    public function testBeforeSaveUploadError()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->settings]);
        $behavior->config($this->settings);
        $this->entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->will($this->returnValue($this->dataError['field']));
        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveWriteFail()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->settings]);
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
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->settings]);
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

    public function testAfterDeleteOk()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->dataOk]);
        $behavior->config($this->dataOk);

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
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->dataOk]);
        $behavior->config($this->dataOk);

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
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->repository, $this->dataError]);
        $behavior->config($this->dataError);

        $behavior->expects($this->any())
            ->method('getWriter')
            ->will($this->returnValue($this->writer));
        $this->writer->expects($this->any())
            ->method('delete')
            ->will($this->returnValue([true]));

        $this->assertNull($behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject));
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

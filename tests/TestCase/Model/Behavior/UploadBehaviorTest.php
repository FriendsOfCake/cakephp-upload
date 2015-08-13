<?php
namespace Josegonzalez\Upload\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Model\Behavior\UploadBehavior;

class UploadBehaviorTest extends TestCase
{
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
    }

    public function testBeforeMarshal()
    {
        $methods = array_diff(get_class_methods('Cake\Validation\Validator'), ['isEmptyAllowed', 'field']);
        $validator = $this->getMock('Cake\Validation\Validator', $methods);
        $validatorSet = $this->getMock('Cake\Validation\ValidationSet');
        $validator->expects($this->any())
                  ->method('isEmptyAllowed')
                  ->with('field', false)
                  ->will($this->returnValue(true));
        $validator->expects($this->any())
                  ->method('field')
                  ->will($this->returnValue($validatorSet));
        $this->table->expects($this->any())
                    ->method('validator')
                    ->will($this->returnValue($validator));
        var_dump($validator);
        $methods = array_diff($this->behaviorMethods, ['beforeMarshal']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->expects($this->any())
                 ->method('config')
                 ->will($this->returnValue($this->settings));
        $data = new ArrayObject($this->dataError);
        $behavior->beforeMarshal(new Event('fake.event'), $data, new ArrayObject);
        var_dump($data);
        die;
    }

    public function testBeforeSaveUploadError()
    {
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('Josegonzalez\Upload\Model\Behavior\UploadBehavior', $methods, [$this->table, $this->settings]);
        $behavior->config($this->settings);
        $this->entity->expects($this->any())
                     ->method('get')
                     ->with('field')
                     ->will($this->returnValue($this->dataError['field']));
         $this->assertTrue($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
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
                     ->will($this->returnValue(false));

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
                     ->will($this->returnValue(true));

        $this->assertTrue($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
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

    public function testConstructFilesException()
    {
        $this->setExpectedException('UnexpectedValueException', "'transformer' not set to instance of TransformerInterface: UnexpectedValueException");
        $files = $this->behavior->constructFiles(
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

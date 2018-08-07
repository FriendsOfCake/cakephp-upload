<?php
namespace Josegonzalez\Upload\Test\TestCase\UploadValidator;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\UploadValidator\DefaultUploadValidator;

class DefaultUploadValidatorTest extends TestCase
{
    public function setup()
    {
        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->dataOk = [
            'field' => [
                'tmp_name' => 'path/to/file',
                'name' => 'derp',
                'error' => UPLOAD_ERR_OK,
                'size' => 1,
                'type' => 'text',
                'keepFilesOnDelete' => false,
                'deleteCallback' => null
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

        $this->defaultUploadValidator = new DefaultUploadValidator($this->entity, $this->field);
    }

    public function testOK() {
        $this->entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->will($this->returnValue($this->dataOk['field']));
        $this->assertFalse($this->defaultUploadValidator->hasUploadFailed());
    }

    public function testFail() {
        $this->entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->will($this->returnValue($this->dataError['field']));
        $this->assertTrue($this->defaultUploadValidator->hasUploadFailed());
    }

    public function testIsUploadValidatorInterface()
    {
        $interface = 'Josegonzalez\Upload\UploadValidator\UploadValidatorInterface';
        $this->assertInstanceOf($interface, $this->defaultUploadValidator);
    }
}
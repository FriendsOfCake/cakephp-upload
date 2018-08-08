<?php
namespace Josegonzalez\Upload\Test\TestCase\UploadValidator;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\UploadValidator\Base64UploadValidator;

class Base64UploadValidatorTest extends TestCase
{
    public function setup()
    {
        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->dataOk = "Y2FrZXBocA==";
        $this->dataError = ' ';
        $this->field = 'field';
        $this->base64UploadValidator = new Base64UploadValidator($this->entity, $this->field);
    }

    public function testOK()
    {
        $this->entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->will($this->returnValue($this->dataOk));
        $this->assertFalse($this->base64UploadValidator->hasUploadFailed());
    }

    public function testFail()
    {
        $this->entity->expects($this->any())
            ->method('get')
            ->with('field')
            ->will($this->returnValue($this->dataError));
        $this->assertTrue($this->base64UploadValidator->hasUploadFailed());
    }

    public function testIsUploadValidatorInterface()
    {
        $interface = 'Josegonzalez\Upload\UploadValidator\UploadValidatorInterface';
        $this->assertInstanceOf($interface, $this->base64UploadValidator);
    }
}

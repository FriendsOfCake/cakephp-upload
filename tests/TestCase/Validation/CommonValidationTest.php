<?php

namespace Josegonzalez\Upload\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Validation\CommonValidation;

class CommonValidationTest extends TestCase
{
    private $data;

    public function setup()
    {
        parent::setUp();
        $this->data = [
            'name' => 'sample.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/tmpfile',
            'size' => 200
        ];
    }

    public function teardown()
    {
        parent::tearDown();
    }

    public function testIsUnderPhpSizeLimit()
    {
        $this->assertTrue(CommonValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(CommonValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_INI_SIZE]));
    }

    public function testIsUnderFormSizeLimit()
    {
        $this->assertTrue(CommonValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(CommonValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_FORM_SIZE]));
    }

    public function testIsCompletedUpload()
    {
        $this->assertTrue(CommonValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(CommonValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_PARTIAL]));
    }

    public function testIsFileUpload()
    {
        $this->assertTrue(CommonValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(CommonValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_NO_FILE]));
    }

    public function testIsSuccessfulWrite()
    {
        $this->assertTrue(CommonValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(CommonValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_CANT_WRITE]));
    }
}

<?php

namespace Josegonzalez\Upload\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Validation\UploadValidation;

class UploadValidationTest extends TestCase
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
        $this->assertTrue(UploadValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_INI_SIZE]));
    }

    public function testIsUnderFormSizeLimit()
    {
        $this->assertTrue(UploadValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_FORM_SIZE]));
    }

    public function testIsCompletedUpload()
    {
        $this->assertTrue(UploadValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_PARTIAL]));
    }

    public function testIsFileUpload()
    {
        $this->assertTrue(UploadValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_NO_FILE]));
    }

    public function testIsSuccessfulWrite()
    {
        $this->assertTrue(UploadValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_CANT_WRITE]));
    }

    public function testIsAboveMinSize()
    {
        $this->assertTrue(UploadValidation::isAboveMinSize($this->data, 200));
        $this->assertFalse(UploadValidation::isAboveMinSize($this->data, 250));

        // Test if no size is set or specified
        $this->data['size'] = '';
        $this->assertFalse(UploadValidation::isAboveMinSize($this->data, 200));

        unset($this->data['size']);
        $this->assertFalse(UploadValidation::isAboveMinSize($this->data, 200));
    }

    public function testIsBelowMaxSize()
    {
        $this->assertTrue(UploadValidation::isBelowMaxSize($this->data, 200));
        $this->assertFalse(UploadValidation::isBelowMaxSize($this->data, 150));

        // Test if no size is set or specified
        $this->data['size'] = '';
        $this->assertFalse(UploadValidation::isBelowMaxSize($this->data, 200));

        unset($this->data['size']);
        $this->assertFalse(UploadValidation::isBelowMaxSize($this->data, 200));
    }
}

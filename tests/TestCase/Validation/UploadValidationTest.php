<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Validation\UploadValidation;
use Laminas\Diactoros\UploadedFile;

class UploadValidationTest extends TestCase
{
    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'name' => 'sample.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/tmpfile',
            'size' => 200,
        ];
    }

    public function testIsUnderPhpSizeLimit()
    {
        $this->assertTrue(UploadValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isUnderPhpSizeLimit($this->data + ['error' => UPLOAD_ERR_INI_SIZE]));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isUnderPhpSizeLimit($file));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_INI_SIZE, 'sample.txt', 'text/plain');
        $this->assertFalse(UploadValidation::isUnderPhpSizeLimit($file));
    }

    public function testIsUnderFormSizeLimit()
    {
        $this->assertTrue(UploadValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isUnderFormSizeLimit($this->data + ['error' => UPLOAD_ERR_FORM_SIZE]));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isUnderFormSizeLimit($file));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_FORM_SIZE, 'sample.txt', 'text/plain');
        $this->assertFalse(UploadValidation::isUnderFormSizeLimit($file));
    }

    public function testIsCompletedUpload()
    {
        $this->assertTrue(UploadValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isCompletedUpload($this->data + ['error' => UPLOAD_ERR_PARTIAL]));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isCompletedUpload($file));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_PARTIAL, 'sample.txt', 'text/plain');
        $this->assertFalse(UploadValidation::isCompletedUpload($file));
    }

    public function testIsFileUpload()
    {
        $this->assertTrue(UploadValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isFileUpload($this->data + ['error' => UPLOAD_ERR_NO_FILE]));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isFileUpload($file));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_NO_FILE, 'sample.txt', 'text/plain');
        $this->assertFalse(UploadValidation::isFileUpload($file));
    }

    public function testIsSuccessfulWrite()
    {
        $this->assertTrue(UploadValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_OK]));
        $this->assertFalse(UploadValidation::isSuccessfulWrite($this->data + ['error' => UPLOAD_ERR_CANT_WRITE]));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isSuccessfulWrite($file));

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_CANT_WRITE, 'sample.txt', 'text/plain');
        $this->assertFalse(UploadValidation::isSuccessfulWrite($file));
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

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isAboveMinSize($file, 200));
        $this->assertFalse(UploadValidation::isAboveMinSize($file, 250));
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

        $file = new UploadedFile(fopen('php://temp', 'rw+'), 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(UploadValidation::isBelowMaxSize($file, 200));
        $this->assertFalse(UploadValidation::isBelowMaxSize($file, 150));
    }
}

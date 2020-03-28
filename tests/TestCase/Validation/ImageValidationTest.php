<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\Validation\ImageValidation;
use Laminas\Diactoros\UploadedFile;
use VirtualFileSystem\FileSystem as Vfs;

class ImageValidationTest extends TestCase
{
    private $data;
    private $vfs;

    public function setUp(): void
    {
        parent::setUp();

        $this->vfs = new Vfs();
        mkdir($this->vfs->path('/tmp'));

        // Write sample image with dimensions: 20x20
        $img = fopen($this->vfs->path('/tmp/tmpimage'), "wb");
        fwrite($img, base64_decode('iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7DAAAOwwHHb6hkAAAAB3RJTUUH4AMUECwX5I9GIwAAACFJREFUOMtj/P//PwM1ARMDlcGogaMGjho4auCogUPFQABpCwMlgqgSYAAAAABJRU5ErkJggg=='));
        fclose($img);

        $this->data = [
            'name' => 'sample.txt',
            'type' => 'text/plain',
            'tmp_name' => $this->vfs->path('/tmp/tmpimage'),
            'size' => 200,
            'error' => UPLOAD_ERR_OK,
        ];
    }

    public function testIsAboveMinWidth()
    {
        $file = new UploadedFile($this->data['tmp_name'], 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(ImageValidation::isAboveMinWidth($file, 10));
        $this->assertFalse(ImageValidation::isAboveMinWidth($file, 30));

        $this->assertTrue(ImageValidation::isAboveMinWidth($this->data, 10));
        $this->assertFalse(ImageValidation::isAboveMinWidth($this->data, 30));

        // Test if no tmp_name is set or specified
        $this->data['tmp_name'] = '';
        $this->assertFalse(ImageValidation::isAboveMinWidth($this->data, 10));

        unset($this->data['tmp_name']);
        $this->assertFalse(ImageValidation::isAboveMinWidth($this->data, 10));
    }

    public function testIsBelowMaxWidth()
    {
        $file = new UploadedFile($this->data['tmp_name'], 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(ImageValidation::isBelowMaxWidth($file, 30));
        $this->assertFalse(ImageValidation::isBelowMaxWidth($file, 10));

        $this->assertTrue(ImageValidation::isBelowMaxWidth($this->data, 30));
        $this->assertFalse(ImageValidation::isBelowMaxWidth($this->data, 10));

        // Test if no tmp_name is set or specified
        $this->data['tmp_name'] = '';
        $this->assertFalse(ImageValidation::isBelowMaxWidth($this->data, 10));

        unset($this->data['tmp_name']);
        $this->assertFalse(ImageValidation::isBelowMaxWidth($this->data, 10));
    }

    public function testIsAboveMinHeight()
    {
        $file = new UploadedFile($this->data['tmp_name'], 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(ImageValidation::isAboveMinHeight($file, 10));
        $this->assertFalse(ImageValidation::isAboveMinHeight($file, 30));

        $this->assertTrue(ImageValidation::isAboveMinHeight($this->data, 10));
        $this->assertFalse(ImageValidation::isAboveMinHeight($this->data, 30));

        // Test if no tmp_name is set or specified
        $this->data['tmp_name'] = '';
        $this->assertFalse(ImageValidation::isAboveMinHeight($this->data, 10));

        unset($this->data['tmp_name']);
        $this->assertFalse(ImageValidation::isAboveMinHeight($this->data, 10));
    }

    public function testIsBelowMaxHeight()
    {
        $file = new UploadedFile($this->data['tmp_name'], 200, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');
        $this->assertTrue(ImageValidation::isBelowMaxHeight($file, 30));
        $this->assertFalse(ImageValidation::isBelowMaxHeight($file, 10));

        $this->assertTrue(ImageValidation::isBelowMaxHeight($this->data, 30));
        $this->assertFalse(ImageValidation::isBelowMaxHeight($this->data, 10));

        // Test if no tmp_name is set or specified
        $this->data['tmp_name'] = '';
        $this->assertFalse(ImageValidation::isBelowMaxHeight($this->data, 10));

        unset($this->data['tmp_name']);
        $this->assertFalse(ImageValidation::isBelowMaxHeight($this->data, 10));
    }
}

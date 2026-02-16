<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;
use Laminas\Diactoros\UploadedFile;

class DefaultTransformerTest extends TestCase
{
    protected UploadedFile $uploadedFile;
    protected DefaultTransformer $transformer;

    public function setUp(): void
    {
        $entity = $this->createStub('Cake\ORM\Entity');
        $table = $this->createStub('Cake\ORM\Table');
        $this->uploadedFile = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'foo.txt');

        $field = 'field';
        $settings = [];
        $this->transformer = new DefaultTransformer($table, $entity, $this->uploadedFile, $field, $settings);
    }

    public function testIsProcessorInterface()
    {
        $this->assertInstanceOf('Josegonzalez\Upload\File\Transformer\TransformerInterface', $this->transformer);
    }

    public function testTransform()
    {
        $this->assertEquals(
            [$this->uploadedFile->getStream()->getMetadata('uri') => 'foo.txt'],
            $this->transformer->transform('foo.txt'),
        );
    }
}

<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Transformer;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Transformer\SlugTransformer;
use Laminas\Diactoros\UploadedFile;

class SlugTransformerTest extends TestCase
{
    protected SlugTransformer $transformer;

    public function setUp(): void
    {
        $entity = $this->createStub('Cake\ORM\Entity');
        $table = $this->createStub('Cake\ORM\Table');
        $data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'foo é À.TXT');
        $field = 'field';
        $settings = [];
        $this->transformer = new SlugTransformer($table, $entity, $data, $field, $settings);
    }

    public function testTransform()
    {
        $this->assertEquals(['php://temp' => 'foo-e-a.txt'], $this->transformer->transform('foo é À.TXT'));
    }

    public function testTransformWithNoFileExt()
    {
        $entity = $this->createStub('Cake\ORM\Entity');
        $table = $this->createStub('Cake\ORM\Table');
        $data = new UploadedFile(fopen('php://temp', 'wb+'), 150, UPLOAD_ERR_OK, 'foo é À');
        $transformer = new SlugTransformer($table, $entity, $data, 'field', []);
        $this->assertEquals(['php://temp' => 'foo-e-a'], $transformer->transform('foo é À'));
    }
}

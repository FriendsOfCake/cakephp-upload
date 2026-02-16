<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Test\TestCase\File\Path\Basepath;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait;

class DefaultTraitTest extends TestCase
{
    private function createTraitMock()
    {
        return new class {
            use DefaultTrait;

            public $entity;
            public $table;
            public $settings;
            public $data;
            public $field;
        };
    }

    public function testNoSpecialConfiguration()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = [];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->table->expects($this->once())->method('getAlias')->willReturn('Table');
        $this->assertEquals('webroot/files/Table/field/', $mock->basepath());
    }

    public function testCustomPath()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->table->expects($this->once())->method('getAlias')->willReturn('Table');
        $this->assertEquals('webroot/files/Table-field/', $mock->basepath());
    }

    public function testExistingEntityWithPrimaryKey()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('get')->willReturn(1);
        $mock->table->expects($this->once())->method('getAlias')->willReturn('Table');
        $mock->table->expects($this->exactly(2))->method('getPrimaryKey')->willReturn('id');
        $this->assertEquals('webroot/files/Table-field/1/', $mock->basepath());
    }

    public function testNewEntity()
    {
        $this->expectException('LogicException', '{primaryKey} substitution not allowed for new entities');

        $mock = $this->createTraitMock();
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->willReturn(true);
        $mock->basepath();
    }

    public function testExitingEntityWithCompositePrimaryKey()
    {
        $this->expectException('LogicException', '{primaryKey} substitution not valid for composite primary keys');

        $mock = $this->createTraitMock();
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->willReturn(false);
        $mock->table->expects($this->once())->method('getPrimaryKey')->willReturn(['id', 'other_id']);
        $mock->basepath();
    }

    /**
     * test Path Without PrimaryKey when Entity has Composite PrimaryKey
     */
    public function testPathWithoutPrimaryKey()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->table->expects($this->once())->method('getAlias')->willReturn('Table');
        $this->assertEquals('webroot/files/Table-field/', $mock->basepath());
    }

    public function testYearWithMonthPath()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{year}{DS}{month}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';

        $this->assertEquals('webroot/files/' . date('Y') . '/' . date('m') . '/', $mock->basepath());
    }

    public function testYearWithMonthAndDayPath()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{year}{DS}{month}{DS}{day}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';

        $this->assertEquals('webroot/files/' . date('Y') . '/' . date('m') . '/' . date('d') . '/', $mock->basepath());
    }

    public function testModelFieldYearWithMonthAndDayPath()
    {
        $mock = $this->createTraitMock();
        $mock->entity = $this->createStub('Cake\ORM\Entity');
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field}{DS}{year}{DS}{month}{DS}{day}{DS}'];

        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->table->expects($this->once())->method('getAlias')->willReturn('Table');

        $this->assertEquals('webroot/files/Table/field/' . date('Y') . '/' . date('m') . '/' . date('d') . '/', $mock->basepath());
    }

    public function testFieldValueMissing()
    {
        $this->expectException('LogicException', 'Field value for substitution is missing: field');

        $mock = $this->createTraitMock();
        $entity = $this->createStub('Cake\ORM\Entity');
        $entity->method('get')->willReturn(null);
        $mock->entity = $entity;
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->basepath();
    }

    public function testFieldValueNonScalar()
    {
        $this->expectException('LogicException', 'Field value for substitution must be a integer, float, string or boolean: field');

        $mock = $this->createTraitMock();
        $entity = $this->createStub('Cake\ORM\Entity');
        $entity->method('get')->willReturn([]);
        $mock->entity = $entity;
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->basepath();
    }

    public function testFieldValueZeroLength()
    {
        $this->expectException('LogicException', 'Field value for substitution must be non-zero in length: field');

        $mock = $this->createTraitMock();
        $entity = $this->createStub('Cake\ORM\Entity');
        $entity->method('get')->willReturn('');
        $mock->entity = $entity;
        $mock->table = $this->createStub('Cake\ORM\Table');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->basepath();
    }

    public function testFieldValue()
    {
        $mock = $this->createTraitMock();
        $entity = $this->createStub('Cake\ORM\Entity');
        $entity->method('get')->willReturn('value');
        $table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $table->expects($this->once())->method('getAlias')->willReturn('Table');
        $mock->entity = $entity;
        $mock->table = $table;
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $this->assertEquals('webroot/files/Table/value/', $mock->basepath());
    }
}

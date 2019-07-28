<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Path\Basepath;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait;

class DefaultTraitTest extends TestCase
{
    public function testNoSpecialConfiguration()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = [];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->exactly(0))->method('get')->will($this->returnValue(1));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));
        $mock->table->expects($this->exactly(0))->method('getPrimaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table/field/', $mock->basepath());
    }

    public function testCustomPath()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->exactly(0))->method('get')->will($this->returnValue(1));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));
        $mock->table->expects($this->exactly(0))->method('getPrimaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table-field/', $mock->basepath());
    }

    public function testExistingEntityWithPrimaryKey()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('get')->will($this->returnValue(1));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));
        $mock->table->expects($this->exactly(2))->method('getPrimaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table-field/1/', $mock->basepath());
    }

    public function testNewEntity()
    {
        $this->expectException('LogicException', '{primaryKey} substitution not allowed for new entities');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->will($this->returnValue(true));
        $mock->basepath();
    }

    public function testExitingEntityWithCompositePrimaryKey()
    {
        $this->expectException('LogicException', '{primaryKey} substitution not valid for composite primary keys');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->will($this->returnValue(false));
        $mock->table->expects($this->once())->method('getPrimaryKey')->will($this->returnValue(['id', 'other_id']));
        $mock->basepath();
    }

    /**
     * test Path Without PrimaryKey when Entity has Composite PrimaryKey
     */
    public function testPathWithoutPrimaryKey()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->table->expects($this->exactly(0))->method('getPrimaryKey')->will($this->returnValue(['id', 'other_id']));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));
        $this->assertEquals('webroot/files/Table-field/', $mock->basepath());
    }

    public function testYearWithMonthPath()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{year}{DS}{month}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';

        $this->assertEquals('webroot/files/' . date("Y") . '/' . date("m") . '/', $mock->basepath());
    }

    public function testYearWithMonthAndDayPath()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{year}{DS}{month}{DS}{day}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';

        $this->assertEquals('webroot/files/' . date("Y") . '/' . date("m") . '/' . date("d") . '/', $mock->basepath());
    }

    public function testModelFieldYearWithMonthAndDayPath()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field}{DS}{year}{DS}{month}{DS}{day}{DS}'];

        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->exactly(0))->method('get')->will($this->returnValue(1));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));

        $this->assertEquals('webroot/files/Table/field/' . date("Y") . '/' . date("m") . '/' . date("d") . '/', $mock->basepath());
    }

    public function testFieldValueMissing()
    {
        $this->expectException('LogicException', 'Field value for substitution is missing: field');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->any())->method('get')->will($this->returnValue(null));
        $mock->basepath();
    }

    public function testFieldValueNonScalar()
    {
        $this->expectException('LogicException', 'Field value for substitution must be a integer, float, string or boolean: field');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->any())->method('get')->will($this->returnValue([]));
        $mock->basepath();
    }

    public function testFieldValueZeroLength()
    {
        $this->expectException('LogicException', 'Field value for substitution must be non-zero in length: field');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->any())->method('get')->will($this->returnValue(''));
        $mock->basepath();
    }

    public function testFieldValue()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $mock->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}{DS}{field-value:field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->any())->method('get')->will($this->returnValue('value'));
        $mock->table->expects($this->once())->method('getAlias')->will($this->returnValue('Table'));
        $this->assertEquals('webroot/files/Table/value/', $mock->basepath());
    }
}

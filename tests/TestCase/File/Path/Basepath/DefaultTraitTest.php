<?php
namespace Josegonzalez\Upload\Test\TestCase\File\Path\Basepath;

use Cake\TestSuite\TestCase;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait;

class DefaultTraitTest extends TestCase
{
    public function testNoSpecialConfiguration()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMock('Cake\ORM\Entity');
        $mock->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $mock->settings = [];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('get')->will($this->returnValue(1));
        $mock->repository->expects($this->once())->method('alias')->will($this->returnValue('Table'));
        $mock->repository->expects($this->once())->method('primaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table/field/', $mock->basepath());
    }

    public function testCustomPath()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMock('Cake\ORM\Entity');
        $mock->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('get')->will($this->returnValue(1));
        $mock->repository->expects($this->once())->method('alias')->will($this->returnValue('Table'));
        $mock->repository->expects($this->once())->method('primaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table-field/', $mock->basepath());
    }

    public function testExistingEntityWithPrimaryKey()
    {
        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMock('Cake\ORM\Entity');
        $mock->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('get')->will($this->returnValue(1));
        $mock->repository->expects($this->once())->method('alias')->will($this->returnValue('Table'));
        $mock->repository->expects($this->exactly(2))->method('primaryKey')->will($this->returnValue('id'));
        $this->assertEquals('webroot/files/Table-field/1/', $mock->basepath());
    }

    public function testNewEntity()
    {
        $this->setExpectedException('LogicException', '{primaryKey} substitution not allowed for new entities');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMock('Cake\ORM\Entity');
        $mock->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->will($this->returnValue(true));
        $this->assertEquals('webroot/files/Table-field/1/', $mock->basepath());
    }

    public function testExitingEntityWithCompositePrimaryKey()
    {
        $this->setExpectedException('LogicException', '{primaryKey} substitution not valid for composite primary keys');

        $mock = $this->getMockForTrait('Josegonzalez\Upload\File\Path\Basepath\DefaultTrait');
        $mock->entity = $this->getMock('Cake\ORM\Entity');
        $mock->repository = $this->getMock('Cake\Datasource\RepositoryInterface');
        $mock->settings = ['path' => 'webroot{DS}files{DS}{model}-{field}{DS}{primaryKey}/'];
        $mock->data = ['name' => 'filename'];
        $mock->field = 'field';
        $mock->entity->expects($this->once())->method('isNew')->will($this->returnValue(false));
        $mock->repository->expects($this->once())->method('primaryKey')->will($this->returnValue(['id', 'other_id']));
        $this->assertEquals('webroot/files/Table-field/1/', $mock->basepath());
    }
}

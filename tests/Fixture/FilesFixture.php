<?php
namespace Josegonzalez\Upload\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FilesFixture extends TestFixture
{
    public $table = 'files';

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'filename' => ['type' => 'integer'],
        'created' => ['type' => 'datetime', 'null' => true],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['filename' => 'FileOne'],
        ['filename' => 'FileTwo'],
        ['filename' => 'FileThree'],
    ];

    public function init()
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created');
        });
        parent::init();
    }
}

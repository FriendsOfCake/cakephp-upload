<?php
namespace Josegonzalez\Upload\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FilesFixture extends TestFixture
{
    /**
     * records property
     *
     * @var array
     */
    public array $records = [
        ['filename' => 'FileOne'],
        ['filename' => 'FileTwo'],
        ['filename' => 'FileThree'],
    ];

    public function init(): void
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created');
        });
        parent::init();
    }
}

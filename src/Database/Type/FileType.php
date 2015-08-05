<?php
namespace Josegonzalez\Upload\Database\Type;

use Cake\Database\Type;

class FileType extends Type
{
    public function marshal($value)
    {
        return $value;
    }
}

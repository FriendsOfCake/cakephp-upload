<?php
namespace Josegonzalez\Upload\Path;

use Cake\Utility\Hash;

class DefaultPathProcessor
{
    public function __invoke($table, $entity, $field, $settings)
    {
        $defaultPath = 'webroot{DS}files{DS}{model}{DS}{field}{DS}';
        $path = Hash::get($settings, 'path', $defaultPath);
        $replacements = array(
            '{primaryKey}' => $entity->get($table->primaryKey()),
            '{model}' => $table->alias(),
            '{field}' => $field,
            '{time}' => time(),
            '{microtime}' => microtime(),
            '{DS}' => DIRECTORY_SEPARATOR,
        );
        return str_replace(
            array_keys($replacements),
            array_values($replacements), 
            $path
        );
    }
}

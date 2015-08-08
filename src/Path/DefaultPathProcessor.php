<?php
namespace Josegonzalez\Upload\Path;

use Cake\Utility\Hash;
use LogicException;

class DefaultPathProcessor
{
    public function __invoke($table, $entity, $field, $settings)
    {
        $defaultPath = 'webroot{DS}files{DS}{model}{DS}{field}{DS}';
        $path = Hash::get($settings, 'path', $defaultPath);
        if (strpos($path, '{primaryKey}') !== false) {
            if ($entity->isNew()) {
                throw new LogicException('{primaryKey} substitution not allowed for new entities');
            }
            if (is_array($table->primaryKey())) {
                throw new LogicException('{primaryKey} substitution not valid for composite primary keys');
            }
        }

        $replacements = [
            '{primaryKey}' => $entity->get($table->primaryKey()),
            '{model}' => $table->alias(),
            '{field}' => $field,
            '{time}' => time(),
            '{microtime}' => microtime(),
            '{DS}' => DIRECTORY_SEPARATOR,
        ];
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $path
        );
    }
}

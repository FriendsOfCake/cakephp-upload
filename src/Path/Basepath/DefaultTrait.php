<?php
namespace Josegonzalez\Upload\Path\Basepath;

use Cake\Utility\Hash;
use LogicException;

trait DefaultTrait
{
    /**
     * Returns the basepath for the current field/data combination.
     * If a `path` is specified in settings, then that will be used as
     * the replacement pattern
     *
     * @return string
     * @throws LogicException if a replacement is not valid for the current dataset
     */
    public function basepath()
    {
        $defaultPath = 'webroot{DS}files{DS}{model}{DS}{field}{DS}';
        $path = Hash::get($this->settings, 'path', $defaultPath);
        if (strpos($path, '{primaryKey}') !== false) {
            if ($this->entity->isNew()) {
                throw new LogicException('{primaryKey} substitution not allowed for new entities');
            }
            if (is_array($this->table->primaryKey())) {
                throw new LogicException('{primaryKey} substitution not valid for composite primary keys');
            }
        }

        $replacements = [
            '{primaryKey}' => $this->entity->get($this->table->primaryKey()),
            '{model}' => $this->table->alias(),
            '{field}' => $this->field,
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

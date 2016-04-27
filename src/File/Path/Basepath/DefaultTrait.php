<?php
namespace Josegonzalez\Upload\File\Path\Basepath;

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
            if (!method_exists($this->repository, 'primaryKey')) {
                throw new LogicException('{primaryKey} substitution not valid for non-Table classes');
            }
            if (is_array($this->repository->primaryKey())) {
                throw new LogicException('{primaryKey} substitution not valid for composite primary keys');
            }
        }

        $replacements = [
            '{model}' => $this->repository->alias(),
            '{field}' => $this->field,
            '{time}' => time(),
            '{microtime}' => microtime(),
            '{DS}' => DIRECTORY_SEPARATOR,
        ];

        if (method_exists($this->repository, 'table')) {
            $replacements['{table}'] = $this->repository->table();
        }

        if (method_exists($this->repository, 'primaryKey')) {
            $replacements['{primaryKey}'] = $this->entity->get($this->repository->primaryKey());
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $path
        );
    }
}

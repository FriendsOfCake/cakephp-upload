<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Path\Basepath;

use Cake\Utility\Hash;
use LogicException;

/**
 * @property \Cake\ORM\Table $table
 */
trait DefaultTrait
{
    /**
     * Returns the basepath for the current field/data combination.
     * If a `path` is specified in settings, then that will be used as
     * the replacement pattern
     *
     * @return string
     * @throws \LogicException if a replacement is not valid for the current dataset
     */
    public function basepath(): string
    {
        $defaultPath = 'webroot{DS}files{DS}{model}{DS}{field}{DS}';
        $path = Hash::get($this->settings, 'path', $defaultPath);
        if (str_contains($path, '{primaryKey}')) {
            if ($this->entity->isNew()) {
                throw new LogicException('{primaryKey} substitution not allowed for new entities');
            }
            if (is_array($this->table->getPrimaryKey())) {
                throw new LogicException('{primaryKey} substitution not valid for composite primary keys');
            }
        }

        $replacements = [
            '{model}' => $this->table->getAlias(),
            '{table}' => $this->table->getTable(),
            '{field}' => $this->field,
            '{year}' => date('Y'),
            '{month}' => date('m'),
            '{day}' => date('d'),
            '{time}' => time(),
            '{microtime}' => microtime(true),
            '{DS}' => DIRECTORY_SEPARATOR,
        ];
        if (str_contains($path, '{primaryKey}')) {
            $replacements['{primaryKey}'] = $this->entity->get($this->table->getPrimaryKey());
        }

        if (preg_match_all("/{field-value:(\w+)}/", $path, $matches)) {
            foreach ($matches[1] as $field) {
                $value = $this->entity->get($field);
                if ($value === null) {
                    throw new LogicException(sprintf(
                        'Field value for substitution is missing: %s',
                        $field
                    ));
                } elseif (!is_scalar($value)) {
                    throw new LogicException(sprintf(
                        'Field value for substitution must be a integer, float, string or boolean: %s',
                        $field
                    ));
                } elseif (strlen((string)$value) < 1) {
                    throw new LogicException(sprintf(
                        'Field value for substitution must be non-zero in length: %s',
                        $field
                    ));
                }

                $replacements[sprintf('{field-value:%s}', $field)] = $value;
            }
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $path
        );
    }
}

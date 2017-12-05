<?php
namespace Josegonzalez\Upload\File\Path\Filename;

use Cake\Utility\Hash;

trait DefaultTrait
{
    /**
     * Returns the filename for the current field/data combination.
     * If a `nameCallback` is specified in settings, then that callable
     * will be invoked with the current upload data.
     *
     * @return string
     */
    public function filename()
    {
        $processor = Hash::get($this->settings, 'nameCallback', null);
        if (is_callable($processor)) {
            $numberOfParameters = (new \ReflectionFunction($processor))->getNumberOfParameters();
            if ($numberOfParameters == 2) {
                return $processor($this->data, $this->settings);
            }

            return $processor($this->table, $this->entity, $this->data, $this->field, $this->settings);
        }

        return $this->data['name'];
    }
}

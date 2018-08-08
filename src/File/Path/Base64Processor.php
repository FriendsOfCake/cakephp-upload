<?php
namespace Josegonzalez\Upload\File\Path;

use Cake\Utility\Hash;
use Cake\Utility\Text;

class Base64Processor extends DefaultProcessor
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
        $extension = Hash::get($this->settings, 'base64_extension', '.png');
        if (is_callable($processor)) {
            $numberOfParameters = (new \ReflectionFunction($processor))->getNumberOfParameters();
            if ($numberOfParameters == 2) {
                return $processor($this->data, $this->settings);
            }

            return $processor($this->table, $this->entity, $this->data, $this->field, $this->settings);
        }

        return Text::uuid() . "$extension";
    }
}

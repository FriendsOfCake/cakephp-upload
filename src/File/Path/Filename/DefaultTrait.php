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
            return $processor($this->data, $this->settings);
        }

        return $this->data['name'];
    }
}

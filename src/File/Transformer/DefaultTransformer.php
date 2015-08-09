<?php
namespace Josegonzalez\Upload\File\Transformer;

use Cake\ORM\Entity;
use Cake\ORM\Table;

class DefaultTransformer
{
    /**
     * Simply returns the data array with no extra modifications
     *
     * @param \Cake\ORM\Table  $table the instance managing the entity
     * @param \Cake\ORM\Entity $entity the entity to construct a path for.
     * @param array            $data the data being submitted for a save
     * @param string           $field the field for which data will be saved
     * @param array            $settings the settings for the current field
     */
    public function __invoke(Table $table, Entity $entity, $data, $field, $settings)
    {
        return [$data['tmp_name'] => $data['name']];
    }
}

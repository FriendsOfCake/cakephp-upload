<?php
namespace Josegonzalez\Upload\UploadValidator;

use Josegonzalez\Upload\UploadValidator\DefaultUploadValidator;

class Base64UploadValidator extends DefaultUploadValidator
{

    /**
     * Check's data for any upload errors.
     * pairs, where the path on disk maps to name which each file should have.
     *
     * @return bool `true` if upload failed
     */
    public function hasUploadFailed()
    {
        return !base64_decode($this->entity->get($this->field), true);
    }
}

<?php

namespace Josegonzalez\Upload\Validation;

use Josegonzalez\Upload\Validation\Traits\UploadValidationTrait;
use Josegonzalez\Upload\Validation\Traits\ImageValidationTrait;

class DefaultValidation
{
    use UploadValidationTrait;
    use ImageValidationTrait;
}

<?php

namespace Josegonzalez\Upload\Validation;

use Josegonzalez\Upload\Validation\Traits\Base64ValidationTrait;
use Josegonzalez\Upload\Validation\Traits\ImageValidationTrait;
use Josegonzalez\Upload\Validation\Traits\UploadValidationTrait;

class DefaultValidation
{
    use Base64ValidationTrait;
    use ImageValidationTrait;
    use UploadValidationTrait;
}

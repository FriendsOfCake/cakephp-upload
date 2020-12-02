<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\Validation;

use Josegonzalez\Upload\Validation\Traits\ImageValidationTrait;
use Josegonzalez\Upload\Validation\Traits\UploadValidationTrait;

class DefaultValidation
{
    use ImageValidationTrait;
    use UploadValidationTrait;
}

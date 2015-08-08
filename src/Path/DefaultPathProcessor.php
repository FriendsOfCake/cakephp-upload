<?php
namespace Josegonzalez\Upload\Path;

use Josegonzalez\Upload\Path\AbstractPathProcessor;
use Josegonzalez\Upload\Path\Basepath\DefaultTrait as BasepathTrait;
use Josegonzalez\Upload\Path\Filename\DefaultTrait as FilenameTrait;

class DefaultPathProcessor extends AbstractPathProcessor
{
    use BasepathTrait;
    use FilenameTrait;
}

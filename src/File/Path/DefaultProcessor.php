<?php
namespace Josegonzalez\Upload\File\Path;

use Josegonzalez\Upload\File\Path\AbstractProcessor;
use Josegonzalez\Upload\File\Path\Basepath\DefaultTrait as BasepathTrait;
use Josegonzalez\Upload\File\Path\Filename\DefaultTrait as FilenameTrait;

class DefaultProcessor extends AbstractProcessor
{
    use BasepathTrait;
    use FilenameTrait;
}

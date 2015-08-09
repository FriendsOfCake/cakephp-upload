<?php
namespace Josegonzalez\Upload\File\Writer;

interface WriterInterface
{
    public function __invoke($files = [], $field, $settings);
}

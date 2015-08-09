<?php
namespace Josegonzalez\Upload\File\Writer;

interface WriterInterface
{
    /**
     * Writes a set of files to an output
     *
     * @param array $files the files being written out
     * @param string $field the field for which data will be saved
     * @param array $settings the settings for the current field
     */
    public function __invoke(array $files, $field, $settings);
}

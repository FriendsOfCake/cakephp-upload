<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 8/6/2018
 * Time: 7:30 PM
 */

namespace Josegonzalez\Upload\File\Transformer;


class Base64Transformer extends DefaultTransformer
{

    /**
     * Creates a set of files from the initial data and returns them as key/value
     * pairs, where the path on disk maps to name which each file should have.
     * Example:
     *
     *   [
     *     '/tmp/path/to/file/on/disk' => 'file.pdf',
     *     '/tmp/path/to/file/on/disk-2' => 'file-preview.png',
     *   ]
     *
     * @return array key/value pairs of temp files mapping to their names
     */
    public function transform()
    {
        $decoded = base64_decode($this->data['data']);
        $tmp = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tmp,$decoded);
        return [
            $tmp => $this->data['name'],
        ];
    }
}
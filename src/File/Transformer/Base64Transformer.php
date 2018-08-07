<?php
namespace Josegonzalez\Upload\File\Transformer;


class Base64Transformer extends DefaultTransformer
{

    private $path;

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
        file_put_contents($this->getPath(),$decoded);
        return [
            $this->getPath() => $this->data['name'],
        ];
    }

    public function setPath($path = '') {
        if (empty($path)) {
            $this->path = tempnam(sys_get_temp_dir(), 'upload');
        }
        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }
}

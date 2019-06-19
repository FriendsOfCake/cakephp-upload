<?php
namespace Josegonzalez\Upload\File\Transformer;

class Base64Transformer extends DefaultTransformer
{
    /**
     * Path where the file will be writen
     *
     * @var string
     */
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
        file_put_contents($this->getPath(), $decoded);

        return [
            $this->getPath() => $this->data['name'],
        ];
    }

    /**
     * Sets the path for the file to be written
     *
     * @param string $path Path to write the file
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns the path where the file will be written
     *
     * @return string|empty
     */
    public function getPath()
    {
        if (empty($this->path)) {
            return $this->path = tempnam(sys_get_temp_dir(), 'upload');
        }

        return $this->path;
    }
}

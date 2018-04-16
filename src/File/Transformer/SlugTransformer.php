<?php
namespace Josegonzalez\Upload\File\Transformer;

use Cake\Utility\Text;
use Josegonzalez\Upload\File\Transformer\DefaultTransformer;

class SlugTransformer extends DefaultTransformer
{
    /**
     * Creates a set of files from the initial data and returns them as key/value
     * pairs, where the path on disk maps to name which each file should have.
     * The file name will be sluggified (using Text::slug()) and lowercased.
     *
     * Example:
     *
     * ```
     *   [
     *     '/tmp/path/to/file/on/disk' => 'file.pdf',
     *     '/tmp/path/to/file/on/disk-2' => 'file-preview.png',
     *   ]
     * ```
     *
     * @return array key/value pairs of temp files mapping to their names
     */
    public function transform()
    {
        $filename = pathinfo($this->data['name'], PATHINFO_FILENAME);
        $filename = Text::slug($filename, '-');

        $ext = pathinfo($this->data['name'], PATHINFO_EXTENSION);
        if (!empty($ext)) {
            $filename = $filename . '.' . $ext;
        }

        return [$this->data['tmp_name'] => strtolower($filename)];
    }
}

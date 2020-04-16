<?php
declare(strict_types=1);

namespace Josegonzalez\Upload\File\Transformer;

use Cake\Utility\Text;

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
     * @param string $filename Filename.
     * @return array key/value pairs of temp files mapping to their names
     */
    public function transform(string $filename): array
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        $filename = Text::slug($filename, '-');
        if (!empty($ext)) {
            $filename = $filename . '.' . $ext;
        }

        return [$this->data->getStream()->getMetadata('uri') => strtolower($filename)];
    }
}

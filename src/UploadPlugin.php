<?php
declare(strict_types=1);

namespace Josegonzalez\Upload;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Database\TypeFactory;
use Josegonzalez\Upload\Database\Type\FileType;

/**
 * Plugin class.
 */
class UploadPlugin extends BasePlugin
{
    /**
     * Plugin bootstrap.
     *
     * @param \Cake\Core\PluginApplicationInterface $app Application instance.
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        TypeFactory::map('upload.file', FileType::class);
    }
}

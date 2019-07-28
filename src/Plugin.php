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
class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app): void
    {
        TypeFactory::map('upload.file', FileType::class);
    }
}

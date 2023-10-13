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
     * The name of this plugin
     *
     * @var string|null
     */
    protected ?string $name = 'Upload';

    /**
     * Console middleware
     *
     * @var bool
     */
    protected bool $consoleEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;

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

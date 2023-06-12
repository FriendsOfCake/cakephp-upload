<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use function Cake\Core\env;

/**
 * Test suite bootstrap.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);

require_once 'vendor/autoload.php';

define('ROOT', $root . DS . 'tests' . DS . 'test_app' . DS);
define('APP', ROOT . 'App' . DS);
define('TMP', sys_get_temp_dir() . DS);
define('CONFIG', ROOT . DS . 'config' . DS);

if (!getenv('DB_URL')) {
    putenv('DB_URL=sqlite:///:memory:');
}
ConnectionManager::setConfig('test', ['url' => getenv('DB_URL')]);

Configure::write('App.namespace', 'Josegonzalez\Upload\Test\TestApp');

// Create test database schema
if (env('FIXTURE_SCHEMA_METADATA')) {
    $loader = new SchemaLoader();
    $loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}

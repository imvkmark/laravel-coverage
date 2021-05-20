<?php

namespace Poppy\Framework\Poppy;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Poppy\Framework\Poppy\Contracts\Repository;

/**
 * @method bool optimize()
 * @method all()
 * @method slugs()
 * @method where($key, $value)
 * @method sortBy($key)
 * @method sortByDesc($key)
 * @method exists($slug)
 * @method count()
 * @method getManifest($slug)
 * @method get($property, $default = null)
 * @method set($property, $value)
 * @method Collection enabled()
 * @method disabled()
 * @method isEnabled($slug)
 * @method isDisabled($slug)
 * @method isPoppy($slug)
 * @method enable(string $slug)
 * @method disable(string $slug)
 */
class Poppy
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Create a new Poppy Modules instance.
     * @param Application $app
     * @param Repository  $repository
     */
    public function __construct(Application $app, Repository $repository)
    {
        $this->app        = $app;
        $this->repository = $repository;
    }

    /**
     * Register the module service provider file from all modules.
     * @return void
     */
    public function register(): void
    {
        $modules = $this->repository->enabled();

        $modules->each(function ($module) {
            $this->registerServiceProvider($module);
            $this->autoloadFiles($module);
        });
    }

    /**
     * @return Repository
     */
    public function repository(): Repository
    {
        return $this->repository;
    }

    /**
     * Oh sweet sweet magical method.
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return call_user_func_array([$this->repository, $method], $arguments);
    }

    /**
     * Register the module service provider.
     * @param array $module module
     * @return void
     */
    private function registerServiceProvider($module): void
    {
        $serviceProvider = poppy_class($module['slug'], 'ServiceProvider');

        if (class_exists($serviceProvider)) {
            $this->app->register($serviceProvider);
        }
    }

    /**
     * Autoload custom module files.
     * @param array $module module
     * @return void
     */
    private function autoloadFiles($module)
    {
        if (isset($module['autoload'])) {
            foreach ($module['autoload'] as $file) {
                $path = poppy_path($module['slug'], $file);
                if (file_exists($path)) {
                    include $path;
                }
            }
        }
    }
}
<?php

namespace Poppy\Core\Module;

use Illuminate\Support\ServiceProvider;

/**
 * Class ModuleServiceProvider.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Register for service provider.
     */
    public function register()
    {
        $this->app->singleton('poppy.core.module', function () {
            return new ModuleManager();
        });
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return ['poppy.core.module'];
    }
}

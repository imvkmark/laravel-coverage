<?php

namespace Poppy\Core\Rbac;

/**
 * Copyright (C) Update For IDE
 */

use Blade;
use Illuminate\Support\ServiceProvider;
use Poppy\Core\Rbac\Permission\PermissionManager;


class RbacServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     */
    public function boot()
    {
        // rbac
        $this->bootRbacBladeDirectives();
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register()
    {
        $this->registerRbac();
        $this->registerPermission();
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'poppy.core.rbac',
            'poppy.core.permission',
        ];
    }

    /**
     * register rbac and alias
     */
    private function registerRbac()
    {
        $this->app->bind('poppy.core.rbac', function ($app) {
            return new Rbac($app);
        });
        $this->app->alias('poppy.core.rbac', Rbac::class);
    }

    private function registerPermission()
    {
        $this->app->singleton('poppy.core.permission', function ($app) {
            return new PermissionManager();
        });
    }

    /**
     * Register the blade directives
     * @return void
     */
    private function bootRbacBladeDirectives()
    {
        // Call to Entrust::hasRole
        Blade::directive('role', function ($expression) {
            return "<?php if (\\Rbac::hasRole({$expression})) : ?>";
        });

        Blade::directive('endrole', function ($expression) {
            return '<?php endif; // Rbac::hasRole ?>';
        });

        // Call to Entrust::capable
        Blade::directive('permission', function ($expression) {
            return "<?php if (\\Rbac::capable({$expression})) : ?>";
        });

        Blade::directive('endpermission', function ($expression) {
            return '<?php endif; // Rbac::capable ?>';
        });

        // Call to Entrust::ability
        Blade::directive('ability', function ($expression) {
            return "<?php if (\\Rbac::ability({$expression})) : ?>";
        });

        Blade::directive('endability', function ($expression) {
            return '<?php endif; // Rbac::ability ?>';
        });
    }
}

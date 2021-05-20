<?php namespace Site\Http;

/**
 * Copyright (C) Update For IDE
 */

use Illuminate\Routing\Router;
use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'Site\Request';

    /**
     * Define your route model bindings, pattern filters, etc.
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            // todo auth
            //			'prefix' => 'site',
            'middleware' => 'web',
        ], function (Router $route) {
            require_once poppy_path('site', 'src/http/routes/web.php');
        });
    }

}

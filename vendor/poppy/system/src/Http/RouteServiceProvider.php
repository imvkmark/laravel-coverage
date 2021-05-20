<?php

namespace Poppy\System\Http;

/**
 * Copyright (C) Update For IDE
 */

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the module.
     * @return void
     */
    public function map(): void
    {
        $this->mapApiRoutes();
    }


    /**
     * Define the "api" routes for the module.
     * These routes are typically stateless.
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        // Api V1 版本
        Route::group([
            'prefix' => 'api_v1/system',
        ], function () {
            require_once __DIR__ . '/Routes/api_v1_web.php';
        });
    }
}
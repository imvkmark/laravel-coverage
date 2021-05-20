<?php

namespace Poppy\Framework;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Poppy\Framework\Helper\UtilHelper;

/**
 * FrameworkServiceProvider
 */
class FrameworkServiceProvider extends ServiceProvider
{

    protected static $registered = false;

    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot(): void
    {
        // 注册 api 文档配置
        $this->publishes([
            framework_path('config/poppy.php') => config_path('poppy.php'),
        ], 'poppy');

        // framework register
        if (!self::$registered) {
            app('poppy')->register();
            self::$registered = true;
        }

        // views an lang
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'poppy');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'poppy');

        $this->bootValidation();

        // Carbon
        Carbon::setLocale(config('app.locale'));
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            framework_path('config/framework.php'),
            'poppy.framework'
        );

        $this->app->register(Console\ConsoleServiceProvider::class);
        $this->app->register(Console\GeneratorServiceProvider::class);
        $this->app->register(Http\BladeServiceProvider::class);
        $this->app->register(Poppy\PoppyServiceProvider::class);
        $this->app->register(Parse\ParseServiceProvider::class);
        $this->app->register(Translation\TranslationServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides(): array
    {
        return [
            'path.framework',
            'path.poppy',
            'path.module',
        ];
    }

    private function bootValidation(): void
    {
        app('validator')->extend('mobile', function ($attribute, $value, $parameters) {
            return UtilHelper::isMobile($value);
        });
        app('validator')->extend('json', function ($attribute, $value, $parameters) {
            return UtilHelper::isJson($value);
        });
        app('validator')->extend('date', function ($attribute, $value, $parameters) {
            return UtilHelper::isDate($value);
        });
        app('validator')->extend('chid', function ($attribute, $value, $parameters) {
            return UtilHelper::isChId($value);
        });
        app('validator')->extend('simple_pwd', function ($attribute, $value, $parameters) {
            return UtilHelper::isPwd($value);
        });
        app('validator')->extend('username', function ($attribute, $value, $parameters) {
            $first = Arr::first($parameters);
            return UtilHelper::isUsername($value, $first === 'sub');
        });
    }
}
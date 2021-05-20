<?php

namespace Poppy\System\Setting;

use Illuminate\Support\ServiceProvider;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\System\Setting\Repository\SettingRepository;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @return array
     */
    public function provides(): array
    {
        return ['poppy.system.setting'];
    }

    /**
     * Register for service provider.
     */
    public function register()
    {
        $this->app->singleton('poppy.system.setting', function () {
            return new SettingRepository();
        });
        $this->app->bind(SettingContract::class, SettingRepository::class);
    }
}

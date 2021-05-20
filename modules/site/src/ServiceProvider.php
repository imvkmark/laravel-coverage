<?php namespace Site;

/**
 * Copyright (C) Update For IDE
 */

use Illuminate\Console\Scheduling\Schedule;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider as ModuleServiceProviderBase;
use Poppy\System\Events\LoginBannedEvent;
use Site\Http\RouteServiceProvider;
use Site\Listeners\LoginBanned\BackendLoginIpBannedListener;
use Site\Models\FrontActivity;
use Site\Models\PamAccount;
use Site\Models\Policies\FrontActivityPolicy;
use Site\Models\Policies\PamAccountPolicy;

class ServiceProvider extends ModuleServiceProviderBase
{
    protected $policies = [
        FrontActivity::class => FrontActivityPolicy::class,

        PamAccount::class => PamAccountPolicy::class,
    ];
    protected $listens  = [
        LoginBannedEvent::class   => [
            BackendLoginIpBannedListener::class,
        ],
    ];
    /**
     * @var string the poppy name slug
     */
    private $name = 'site';

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot($this->name);
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->registerCommand();
        $this->registerSchedule();
        $this->registerDev();

    }

    public function provides(): array
    {
        return [
            'site.form',
        ];
    }

    private function registerCommand()
    {

    }

    private function registerSchedule()
    {
        $this->app['events']->listen('console.schedule', function (Schedule $schedule) {
            //定时检查活动是否结束
            $schedule->command('site:activity-over')
                ->everyThirtyMinutes();

            // 统计客服工作
            $schedule->command('site:op-kf')
                ->dailyAt('04:00')->appendOutputTo($this->consoleLog());
        });
    }

    /**
     * 注册开发环境的命令行执行
     */
    private function registerDev()
    {
        $this->app['events']->listen('console.schedule', function (Schedule $schedule) {
            /* 30 分钟 清除clockwork文件
             -------------------------------------------- */
            if (!is_production()) {
                $schedule->command('clockwork:clean')
                    ->everyTenMinutes();
            }
        });
    }
}

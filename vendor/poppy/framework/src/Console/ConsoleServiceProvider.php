<?php

namespace Poppy\Framework\Console;

use Illuminate\Support\ServiceProvider;
use Poppy\Framework\Console\Commands\PoppyDisableCommand;
use Poppy\Framework\Console\Commands\PoppyEnableCommand;
use Poppy\Framework\Console\Commands\PoppyListCommand;
use Poppy\Framework\Console\Commands\PoppyMigrateCommand;
use Poppy\Framework\Console\Commands\PoppyMigrateRefreshCommand;
use Poppy\Framework\Console\Commands\PoppyMigrateResetCommand;
use Poppy\Framework\Console\Commands\PoppyMigrateRollbackCommand;
use Poppy\Framework\Console\Commands\PoppyOptimizeCommand;
use Poppy\Framework\Console\Commands\PoppySeedCommand;
use Poppy\Framework\Database\Migrations\Migrator;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerDisableCommand();
        $this->registerEnableCommand();
        $this->registerListCommand();
        $this->registerMigrateCommand();
        $this->registerMigrateRefreshCommand();
        $this->registerMigrateResetCommand();
        $this->registerMigrateRollbackCommand();
        $this->registerOptimizeCommand();
        $this->registerSeedCommand();
    }

    /**
     * Register the module:disable command.
     */
    protected function registerDisableCommand()
    {
        $this->app->singleton('command.poppy.disable', function () {
            return new PoppyDisableCommand();
        });

        $this->commands('command.poppy.disable');
    }

    /**
     * Register the module:enable command.
     */
    protected function registerEnableCommand()
    {
        $this->app->singleton('command.poppy.enable', function () {
            return new PoppyEnableCommand();
        });

        $this->commands('command.poppy.enable');
    }

    /**
     * Register the module:list command.
     */
    protected function registerListCommand()
    {
        $this->app->singleton('command.poppy.list', function ($app) {
            return new PoppyListCommand($app['poppy']);
        });

        $this->commands('command.poppy.list');
    }

    /**
     * Register the module:migrate command.
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.poppy.migrate', function ($app) {
            return new PoppyMigrateCommand($app['migrator'], $app['poppy']);
        });

        $this->commands('command.poppy.migrate');
    }

    /**
     * Register the module:migrate:refresh command.
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.poppy.migrate.refresh', function () {
            return new PoppyMigrateRefreshCommand();
        });

        $this->commands('command.poppy.migrate.refresh');
    }

    /**
     * Register the module:migrate:reset command.
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.poppy.migrate.reset', function ($app) {
            return new PoppyMigrateResetCommand($app['poppy'], $app['files'], $app['migrator']);
        });

        $this->commands('command.poppy.migrate.reset');
    }

    /**
     * Register the module:migrate:rollback command.
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.poppy.migrate.rollback', function ($app) {
            $repository = $app['migration.repository'];
            $table      = $app['config']['database.migrations'];

            $migrator = new Migrator($table, $repository, $app['db'], $app['files']);

            return new PoppyMigrateRollbackCommand($migrator, $app['poppy']);
        });

        $this->commands('command.poppy.migrate.rollback');
    }

    /**
     * Register the module:optimize command.
     */
    protected function registerOptimizeCommand()
    {
        $this->app->singleton('command.poppy.optimize', function () {
            return new PoppyOptimizeCommand();
        });

        $this->commands('command.poppy.optimize');
    }

    /**
     * Register the module:seed command.
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.poppy.seed', function ($app) {
            return new PoppySeedCommand($app['poppy']);
        });

        $this->commands('command.poppy.seed');
    }
}

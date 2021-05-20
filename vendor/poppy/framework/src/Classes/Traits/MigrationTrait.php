<?php

namespace Poppy\Framework\Classes\Traits;

use Illuminate\Support\Str;

/**
 * MigrationTrait
 */
trait MigrationTrait
{
    /**
     * Require (once) all migration files for the supplied module.
     *
     * @param string $module module
     */
    protected function requireMigrations(string $module)
    {
        $path = $this->getMigrationPath($module);

        $migrations = $this->laravel['files']->glob($path . '*_*.php');

        foreach ($migrations as $migration) {
            $this->laravel['files']->requireOnce($migration);
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $module module
     *
     * @return string
     */
    protected function getMigrationPath(string $module)
    {
        if (Str::startsWith($module, 'poppy.')) {
            return poppy_path($module, 'src/Database/Migrations');
        }
        return poppy_path($module, 'src/database/migrations');
    }
}


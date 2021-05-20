<?php

namespace Poppy\Core\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Rbac Facade
 */
class PermissionFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'poppy.core.permission';
    }
}


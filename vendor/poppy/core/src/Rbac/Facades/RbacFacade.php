<?php

namespace Poppy\Core\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Rbac Facade
 */
class RbacFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'poppy.core.rbac';
    }
}


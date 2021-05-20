<?php

namespace Poppy\Framework\Facade;

use Illuminate\Support\Facades\Facade;
use Poppy\Framework\Parse\Ini;

/**
 * @see Ini
 */
class IniFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'poppy.ini';
    }
}

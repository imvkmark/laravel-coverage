<?php

namespace Poppy\Framework\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see YamlFacade
 */
class YamlFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'poppy.yaml';
    }
}

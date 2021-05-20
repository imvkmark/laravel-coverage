<?php

namespace Poppy\Framework\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see XmlFacade
 */
class XmlFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'poppy.xml';
    }
}

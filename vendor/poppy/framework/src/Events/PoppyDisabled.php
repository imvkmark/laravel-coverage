<?php

namespace Poppy\Framework\Events;

use Illuminate\Support\Collection;
use Poppy\Framework\Application\Event;

/**
 * 禁用一个模块
 */
class PoppyDisabled extends Event
{

    /**
     * @var Collection 模块
     */
    public $module;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }
}
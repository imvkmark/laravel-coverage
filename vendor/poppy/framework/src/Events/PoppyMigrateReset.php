<?php

namespace Poppy\Framework\Events;

use Poppy\Framework\Application\Event;
use Poppy\Framework\Poppy\Poppy;

/**
 * Migrate Refresh
 */
class PoppyMigrateReset extends Event
{

    /**
     * @var Poppy 模块
     */
    public $poppy;

    /**
     * @var array|mixed
     */
    private $option;

    /**
     * @param Poppy $poppy
     * @param array $option
     */
    public function __construct(Poppy $poppy, $option = [])
    {
        $this->poppy  = $poppy;
        $this->option = $option;
    }
}
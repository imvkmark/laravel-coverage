<?php

namespace Poppy\Framework\Events;

use Illuminate\Support\Collection;
use Poppy\Framework\Application\Event;

/**
 * PoppyOptimized
 */
class PoppyOptimized extends Event
{
    /**
     * Optimized module collection
     * @var Collection $modules
     */
    private $modules;

    /**
     * PoppyOptimized constructor.
     * @param Collection $modules
     */
    public function __construct(Collection $modules)
    {
        $this->modules = $modules;
    }

    /**
     * @return Collection
     */
    public function modules()
    {
        return $this->modules;
    }
}
<?php

namespace Poppy\Core\Events;

use Illuminate\Support\Collection;
use Poppy\Framework\Application\Event;

class PermissionInitEvent extends Event
{
    /**
     * @var Collection
     */
    public $permissions;

    public function __construct($permissions)
    {
        $this->permissions = $permissions;
    }
}

<?php

namespace Poppy\Core\Rbac\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 权限约束
 */
interface RbacPermissionContract
{
    /**
     * Many-to-Many relations with role model.
     * @return BelongsToMany
     */
    public function roles();
}


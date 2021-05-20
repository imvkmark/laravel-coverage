<?php

namespace Poppy\Core\Rbac\Helper;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class RbacHelper
 */
class RbacHelper
{
    /**
     * 获取权限以及分组
     * @param string $type 账号类型
     * @return Collection
     */
    public static function permission(string $type): Collection
    {
        $permissionClass = config('poppy.core.rbac.permission');
        $permission      = (new $permissionClass)->where('type', $type)->get();
        $collection      = new Collection($permission);

        return $collection->groupBy('group');
    }
}


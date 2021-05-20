<?php

namespace Poppy\Core\Rbac\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 权限 trait
 */
trait RbacPermissionTrait
{
    /**
     * Many-to-Many relations with role model.
     * @return BelongsToMany
     */
    public function roles()
    {
        $roleModel           = config('poppy.core.rbac.role');
        $rolePermissionModel = config('poppy.core.rbac.role_permission');
        return $this->belongsToMany(
            $roleModel,
            (new $rolePermissionModel)->getTable()
        );
    }

    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        $permissionModel = config('poppy.core.rbac.permission');
        static::deleting(function ($permission) use ($permissionModel) {
            if (!method_exists(new $permissionModel, 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }

            return true;
        });
    }
}


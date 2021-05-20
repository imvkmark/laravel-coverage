<?php

namespace Poppy\Core\Rbac\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Core\Rbac\Permission\Permission;

/**
 * 角色 Trait
 */
trait RbacRoleTrait
{
    //Big block of caching functionality.

    /**
     * @return Collection|mixed
     */
    public function cachedPermissions()
    {
        static $cache;
        $rolePrimaryKey = $this->primaryKey;
        $cacheKey       = PyCoreDef::rbacCkRolePermissions($this->$rolePrimaryKey);
        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = sys_cache('py-core-rbac')->remember($cacheKey, config('cache.ttl'), function () {
                return $this->perms()->get();
            });
        }

        return $cache[$cacheKey];
    }

    /**
     * @param array $options 选项
     * @return bool
     */
    public function save(array $options = []): bool
    {   //both inserts and updates
        if (!parent::save($options)) {
            return false;
        }
        $this->flushPermissionRole();

        return true;
    }

    /**
     * @param array $options 选项
     * @return bool
     */
    public function delete(array $options = []): bool
    {   //soft or hard
        if (!parent::delete($options)) {
            return false;
        }
        $this->flushPermissionRole();

        return true;
    }

    /**
     * Boot the role model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the role model uses soft deletes.
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        $roleModel = config('poppy.core.rbac.role');
        static::deleting(function ($role) use ($roleModel) {
            if (!method_exists((new $roleModel), 'bootSoftDeletes')) {
                $role->users()->sync([]);
                $role->perms()->sync([]);
            }
            return true;
        });
    }

    /**
     * @return bool
     */
    public function restore(): bool
    {   //soft delete undo's
        if (!parent::restore()) {
            return false;
        }
        $this->flushPermissionRole();

        return true;
    }

    /**
     * 清理权限
     */
    public function flushPermissionRole()
    {
        sys_cache('py-core-rbac')->flush();
    }

    /**
     * Many-to-Many relations with the user model.
     * @return BelongsToMany
     */
    public function users()
    {
        $accountClass     = config('poppy.core.rbac.account');
        $roleAccountClass = config('poppy.core.rbac.role_account');
        $roleFk           = config('poppy.core.rbac.role_fk');
        $accountFk        = config('poppy.core.rbac.account_fk');
        return $this->belongsToMany(
            $accountClass,
            (new $roleAccountClass)->getTable(),
            $roleFk,
            $accountFk
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     * @return BelongsToMany
     */
    public function perms()
    {
        $permissionClass = config('poppy.core.rbac.permission');
        $roleFk          = config('poppy.core.rbac.role_fk');
        $permissionFk    = config('poppy.core.rbac.permission_fk');
        return $this->belongsToMany(
            $permissionClass,
            $this->getPermissionRoleTable(),
            $roleFk,
            $permissionFk
        );
    }

    /**
     * Save the inputted permissions.
     * @param mixed $inputPermissions 需要保存的权限
     * @return void
     */
    public function savePermissions($inputPermissions)
    {
        if (!empty($inputPermissions)) {
            $this->perms()->sync($inputPermissions);
        }
        else {
            $this->perms()->detach();
        }
    }

    /**
     * Attach permission to current role.
     * @param object|array|Permission $permission 权限
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->perms()->attach($permission);
    }

    /**
     * Detach permission from current role.
     * @param object|array $permission 权限
     * @return void
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->perms()->detach($permission);
    }

    /**
     * Attach multiple permissions to current role.
     * @param array $permissions 权限
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from current role
     * @param array $permissions 权限
     * @return void
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }

    /**
     * Checks if the role has a permission by its name.
     * @param string|array $name       permission name or array of permission names
     * @param bool         $requireAll all permissions in the array are required
     * @return bool
     */
    public function hasPermission($name, $requireAll = false): bool
    {
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                }

                if (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll;
            return $requireAll;
        }

        foreach ($this->cachedPermissions() as $permission) {
            if ($permission->name === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    private function getPermissionRoleTable(): string
    {
        $permissionRole = config('poppy.core.rbac.role_permission');
        return (new $permissionRole)->getTable();
    }
}
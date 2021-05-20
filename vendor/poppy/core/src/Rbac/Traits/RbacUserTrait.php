<?php

namespace Poppy\Core\Rbac\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Poppy\Core\Classes\PyCoreDef;

/**
 * 用户 trait
 */
trait RbacUserTrait
{
    //Big block of caching functionality.

    /**
     * @return Collection
     */
    public function cachedRoles()
    {
        static $cache;
        $userPrimaryKey = $this->primaryKey;
        $cacheKey       = PyCoreDef::rbacCkUserRoles($this->$userPrimaryKey);
        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = sys_cache('py-core-rbac')->remember($cacheKey, config('cache.ttl'), function () {
                return $this->roles()->get();
            });
        }

        return $cache[$cacheKey];
    }

    /**
     * 保存
     * @param array $options 选项
     */
    public function save(array $options = [])
    {   //both inserts and updates
        parent::save($options);
        sys_cache('py-core-rbac')->flush();
    }

    /**
     * 删除
     * @param array $options 选项
     */
    public function delete(array $options = [])
    {   //soft or hard
        parent::delete($options);
        sys_cache('py-core-rbac')->flush();
    }

    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        $accountModel = config('poppy.core.rbac.account');
        static::deleting(function ($user) use ($accountModel) {
            if (!method_exists((new $accountModel), 'bootSoftDeletes')) {
                $user->roles()->sync([]);
            }

            return true;
        });
    }

    /**
     * 清空
     */
    public function restore()
    {   //soft delete undo's
        parent::restore();
        sys_cache('py-core-rbac')->flush();
    }

    /**
     * Many-to-Many relations with Role.
     * @return BelongsToMany
     */
    public function roles()
    {
        $roleModel = config('poppy.core.rbac.role');
        $accountFk = config('poppy.core.rbac.account_fk');
        $roleFk    = config('poppy.core.rbac.role_fk');
        return $this->belongsToMany(
            $roleModel,
            $this->getRoleUserTable(),
            $accountFk,
            $roleFk
        );
    }

    /**
     * Checks if the user has a role by its name.
     * @param string|array $name       role name or array of role names
     * @param bool         $requireAll all roles in the array are required
     * @return bool
     */
    public function hasRole($name, $requireAll = false): bool
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                }

                if (!$hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        }

        foreach ($this->cachedRoles() as $role) {
            if ($role->name === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name.
     * @param string|array $permission permission string or array of permissions
     * @param bool         $requireAll all permissions in the array are required
     * @return bool
     */
    public function capable($permission, $requireAll = false): bool
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->capable($permName);
                if ($hasPerm && !$requireAll) {
                    return true;
                }
                if (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        }
        foreach ($this->cachedRoles() as $role) {
            // Validate against the Permission table
            foreach ($role->cachedPermissions() as $perm) {
                if (Str::is($permission, $perm->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks role(s) and permission(s).
     * @param string|array $roles       Array of roles or comma separated string
     * @param string|array $permissions array of permissions or comma separated string
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function ability($roles, $permissions, $options = [])
    {
        // Convert string to array if that's what is passed in.
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if (!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        }
        else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if (!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        }
        else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both'
            ) {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles       = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->capable($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))
        ) {
            $validateAll = true;
        }
        else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        }
        elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        }

        return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     * @param mixed $role 角色
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     * @param mixed $role 角色
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    /**
     * Attach multiple roles to a user
     * @param array $roles 多个角色
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from a user
     * @param array $roles 多个角色
     */
    public function detachRoles($roles = null)
    {
        if (!$roles) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    /**
     * @return string
     */
    private function getRoleUserTable(): string
    {
        $roleAccountModel = config('poppy.core.rbac.role_account');
        return (new $roleAccountModel)->getTable();
    }
}
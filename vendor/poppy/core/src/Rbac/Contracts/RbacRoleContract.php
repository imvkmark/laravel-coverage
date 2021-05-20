<?php

namespace Poppy\Core\Rbac\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 角色约束
 */
interface RbacRoleContract
{
    /**
     * Many-to-Many relations with the user model.
     * @return BelongsToMany
     */
    public function users();

    /**
     * Many-to-Many relations with the permission model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     * @return BelongsToMany
     */
    public function perms();

    /**
     * Save the inputted permissions.
     * @param mixed $inputPermissions 需要保存的权限
     * @return void
     */
    public function savePermissions($inputPermissions);

    /**
     * Attach permission to current role.
     * @param object|array $permission 权限
     * @return void
     */
    public function attachPermission($permission);

    /**
     * Detach permission form current role.
     * @param object|array $permission 权限
     * @return void
     */
    public function detachPermission($permission);

    /**
     * Attach multiple permissions to current role.
     * @param array $permissions 多个权限
     * @return void
     */
    public function attachPermissions($permissions);

    /**
     * Detach multiple permissions from current role
     * @param array $permissions 多个权限
     * @return void
     */
    public function detachPermissions($permissions);
}

<?php

namespace Poppy\System\Models\Policies;

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * 用户角色策略
 */
class PamRolePolicy
{

    /**
     * 编辑
     * @param PamAccount $pam 账号
     * @return bool
     */
    public function create(PamAccount $pam): bool
    {
        return true;
    }

    /**
     * 编辑
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     * @return bool
     */
    public function edit(PamAccount $pam, PamRole $role): bool
    {
        return true;
    }

    /**
     * 保存权限
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     * @return bool
     */
    public function permission(PamAccount $pam, PamRole $role): bool
    {
        return !($role->name === PamRole::BE_ROOT);
    }

    /**
     * 删除
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     * @return bool
     */
    public function delete(PamAccount $pam, PamRole $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return true;
    }
}
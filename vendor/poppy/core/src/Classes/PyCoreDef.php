<?php

namespace Poppy\Core\Classes;


class PyCoreDef
{
    public const MIN_DEBUG     = 0;
    public const MIN_ONE_HOUR  = 60;
    public const MIN_SIX_HOUR  = 360;
    public const MIN_HALF_DAY  = 720;
    public const MIN_ONE_DAY   = 1440;
    public const MIN_HALF_WEEK = 5040;
    public const MIN_ONE_WEEK  = 10080;
    public const MIN_ONE_MONTH = 43200;

    /**
     * 模型注释
     * @return string
     */
    public static function ckLangModels(): string
    {
        return 'lang-models';
    }

    /**
     * 模块注释
     * @param string $type
     * @return string
     */
    public static function ckModule(string $type): string
    {
        return 'module' . ($type ? '-' . $type : '');
    }

    /**
     * 权限
     * @return string
     */
    public static function ckPermissions(): string
    {
        return 'permissions';
    }

    /**
     * 缓存器
     * @param string $key 标识KEY
     * @return string
     */
    public static function ckCacher(string $key): string
    {
        return 'cacher-' . $key;
    }

    /**
     * Rbac 角色缓存
     * @param int $id
     * @return string
     */
    public static function rbacCkRolePermissions(int $id): string
    {
        return 'permission-role-' . $id;
    }

    /**
     * 用户角色缓存
     * @param int $id
     * @return string
     */
    public static function rbacCkUserRoles(int $id): string
    {
        return 'roles-user-' . $id;
    }

    /**
     * 过期的KEY/Field
     * @return string
     */
    public static function ckRdsKeyFieldExpired(): string
    {
        return 'rds-key-field-expired';
    }
}
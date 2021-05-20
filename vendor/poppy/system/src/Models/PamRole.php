<?php

namespace Poppy\System\Models;

use Eloquent;
use Illuminate\Support\Collection;
use Poppy\Core\Rbac\Contracts\RbacRoleContract;
use Poppy\Core\Rbac\Traits\RbacRoleTrait;
use Poppy\Framework\Helper\ArrayHelper;

/**
 * 用户角色
 * @property int                             $id
 * @property string                          $name
 * @property string                          $title
 * @property string                          $description
 * @property string                          $type
 * @property bool                            $is_system
 * @property int                             $is_enable 是否可用
 * @property-read Collection|PamPermission[] $perms
 * @property-read Collection|PamAccount[]    $users
 * @mixin Eloquent
 */
class PamRole extends Eloquent implements RbacRoleContract
{
    use RbacRoleTrait;

    const BE_ROOT  = 'root';      // admin user
    const FE_USER  = 'user';      // web user
    const DEV_USER = 'develop';   // developer

    protected $table = 'pam_role';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description',
        'type',
        'is_system',
    ];

    /**
     * 通过角色来获取账户类型, 由于角色在单条处理中不会存在变化, 故而可以进行静态缓存
     * @param int $role_id 角色id
     * @return mixed
     */
    public static function getAccountTypeByRoleId($role_id)
    {
        static $_cache;
        if (!isset($_cache[$role_id])) {
            $_cache[$role_id] = self::where('role_id', $role_id)->value('account_type');
        }

        return $_cache[$role_id];
    }

    /**
     * 返回一维的角色对应
     * @param null|string $type 类型
     * @param string      $key  key
     * @return Collection
     */
    public static function getLinear($type = null, $key = 'id'): Collection
    {
        return self::where('type', $type)->pluck('title', $key);
    }

    /**
     * 根据账户类型获取角色
     * @param string|null $accountType 账户类型
     * @param bool        $cache       是否缓存
     * @return array
     */
    public static function getAll($accountType = null, $cache = true)
    {
        static $roles = null;
        if (empty($roles) || !$cache) {
            if ($accountType) {
                $items = self::where('account_type', $accountType)->get()->toArray();
            }
            else {
                $items = self::all()->toArray();
            }
            $roles = ArrayHelper::pluck($items, 'id');
        }

        return $roles;
    }

    /**
     * 获取角色信息
     * @param int  $id    角色id
     * @param null $key   key
     * @param bool $cache 是否缓存
     * @return null
     */
    public static function info($id, $key = null, $cache = true)
    {
        $roles = self::getAll(null, $cache);

        return $key
            ? $roles[$id][$key] ?? null
            : $roles[$id];
    }
}
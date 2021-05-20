<?php

namespace Poppy\System\Action;


use Exception;
use Illuminate\Support\Arr;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Rbac\Permission\Permission;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Events\RolePermissionUpdatedEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamPermissionRole;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\PamRoleAccount;
use Validator;
use View;

/**
 * 角色action
 */
class Role
{
    use AppTrait, PamTrait, CoreTrait;

    /**
     * @var PamRole
     */
    protected $role;

    /**
     * @var int Role id
     */
    protected $roleId;

    /**
     * @var string
     */
    protected $roleTable;

    public function __construct()
    {
        $this->roleTable = (new PamRole())->getTable();
    }

    /**
     * 创建需求
     * @param array    $data 创建数据
     * @param null|int $id   角色id
     * @return bool
     */
    public function establish(array $data, $id = null)
    {
        if (!$this->checkPam()) {
            return false;
        }

        $initDb = [
            'title'       => (string) Arr::get($data, 'title', ''),
            'type'        => (string) Arr::get($data, 'type', ''),
            'description' => (string) Arr::get($data, 'description', ''),
        ];

        $rule = [
            'title' => [
                Rule::required(),
                Rule::unique($this->roleTable, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'type'  => [
                Rule::required(),
                Rule::in([
                    PamAccount::TYPE_BACKEND,
                    PamAccount::TYPE_DEVELOP,
                    PamAccount::TYPE_USER,
                ]),
            ],
        ];
        if ($id) {
            unset($rule['name'], $rule['type']);
        }
        $validator = Validator::make($initDb, $rule, [], [
            'name'  => '角色用户名',
            'title' => '角色名称',
            'type'  => '角色类型',
        ]);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        // init
        if ($id && !$this->init($id)) {
            return false;
        }

        if ($this->roleId) {
            if (!$this->pam->can('edit', $this->role)) {
                return $this->setError(trans('py-system::action.role.no_policy_to_update'));
            }
            // 编辑时候类型和名称不允许编辑
            unset($initDb['type'], $initDb['name']);
            $this->role->update($initDb);
        }
        else {
            if (!$this->pam->can('create', PamRole::class)) {
                return $this->setError(trans('py-system::action.role.no_policy_to_create'));
            }
            $this->role = PamRole::create($initDb);
        }

        return true;
    }

    /**
     * 保存权限
     * @param array $permission_ids 所有的权限列表
     * @param int   $role_id        角色ID
     * @return bool
     */
    public function savePermission($role_id, $permission_ids)
    {
        if (!$this->checkPam()) {
            return false;
        }

        if (!$this->init($role_id)) {
            return false;
        }

        if ($this->pam->can('savePermission', PamRole::class)) {
            return $this->setError(trans('py-system::action.role.no_policy_to_save_permission'));
        }

        if ($permission_ids) {
            $objPermissions = PamPermission::whereIn('id', $permission_ids)->get();
            if (!$objPermissions->count()) {
                return $this->setError(trans('py-system::action.role.permission_error'));
            }
            $this->role->savePermissions($objPermissions);
        }
        else {
            $this->role->savePermissions([]);
        }

        $this->role->flushPermissionRole();

        event(new RolePermissionUpdatedEvent($this->role));

        return true;
    }

    /**
     * @param int $id 角色id
     * @return bool
     */
    public function init($id)
    {
        try {
            $this->role   = PamRole::findOrFail($id);
            $this->roleId = $this->role->id;

            return true;
        } catch (Exception $e) {
            return $this->setError(trans('py-system::action.role.role_not_exists'));
        }
    }

    /**
     * 分配视图数据
     */
    public function share()
    {
        View::share(['item' => $this->role]);
    }


    public function getRole(): PamRole
    {
        return $this->role;
    }

    /**
     * 获取所有权限以及默认值
     * @param int  $id      角色id
     * @param bool $has_key 是否有值
     * @return array|mixed|Permission
     */
    public function permissions($id, $has_key = true)
    {
        $role = PamRole::find($id);
        if (!$role) {
            return $this->setError('角色不存在');
        }
        $permissions = $this->corePermission()->permissions();
        $type        = $role->type;

        // 权限映射
        if ($map = config('poppy.system.role_type_map')) {
            $type = isset($map[$type]) ? $map[$type] : $type;
        }

        $keys              = $permissions->keys();
        $match             = PamPermission::where('type', $type)->whereIn('name', $keys)->pluck('id', 'name');
        $collectPermission = collect();
        foreach ($permissions as $key => $permission) {
            $tmp = $permission->toArray();
            $id  = $match->get($tmp['key']);
            // 去掉本用户组不可控制的权限
            if (!$id) {
                continue;
            }
            $tmp['id'] = $match->get($tmp['key']);
            $collectPermission->put($key, $tmp);
        }

        $permission = [];
        $collectPermission->each(function ($item, $key) use (&$permission, $role) {
            $root    = [
                'title'  => $item['root_title'],
                'root'   => $item['root'],
                'groups' => [],
            ];
            $rootKey = $item['root'];
            if (!isset($permission[$rootKey])) {
                $permission[$rootKey] = $root;
            }
            $groupKey = $item['group'];
            $group    = [
                'group'       => $item['group'],
                'title'       => $item['group_title'],
                'permissions' => [],
            ];
            if (!isset($permission[$rootKey]['groups'][$groupKey])) {
                $permission[$rootKey]['groups'][$groupKey] = $group;
            }

            $item['value'] = (int) $role->hasPermission($key);

            unset(
                $item['is_default'],
                $item['root'],
                $item['group'],
                $item['module'],
                $item['key'],
                $item['root_title'],
                $item['type'],
                $item['group_title']
            );

            $permission[$rootKey]['groups'][$groupKey]['permissions'][] = $item;
        });

        if (!$has_key) {
            $p = [];
            foreach ($permission as $sp) {
                $pg = $sp;
                unset($pg['groups']);
                foreach ($sp['groups'] as $spg) {
                    $pg['groups'][] = $spg;
                }
                $p[] = $pg;
            }
            $permission = $p;
        }

        return $permission;
    }

    /**
     * 删除数据
     * @param int $id 角色id
     * @return bool
     */
    public function delete($id)
    {
        if (!$this->checkPam()) {
            return false;
        }

        if ($id && !$this->init($id)) {
            return false;
        }

        if (!$this->pam->can('delete', $this->role)) {
            return $this->setError(trans('py-system::action.role.no_policy_to_delete'));
        }

        if (PamRoleAccount::where('role_id', $this->roleId)->exists()) {
            return $this->setError(trans('py-system::action.role.role_has_account'));
        }

        // 删除权限
        try {
            PamPermissionRole::where('role_id', $this->roleId)->delete();
            // 删除角色
            $this->role->delete();

            return true;
        } catch (Exception $e) {
            return $this->setError($e->getMessage());
        }
    }
}
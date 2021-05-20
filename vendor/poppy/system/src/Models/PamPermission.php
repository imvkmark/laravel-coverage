<?php

namespace Poppy\System\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Poppy\Core\Rbac\Contracts\RbacPermissionContract;
use Poppy\Core\Rbac\Traits\RbacPermissionTrait;

/**
 * 用户权限
 *
 * @property int                       $id
 * @property string                    $name
 * @property string                    $title
 * @property string                    $description
 * @property string                    $group
 * @property string                    $root
 * @property string                    $module
 * @property string                    $type
 * @property-read Collection|PamRole[] $roles
 * @method static Builder|PamPermission newModelQuery()
 * @method static Builder|PamPermission newQuery()
 * @method static Builder|PamPermission query()
 * @mixin Eloquent
 */
class PamPermission extends Eloquent implements RbacPermissionContract
{
    use RbacPermissionTrait;

    protected $table = 'pam_permission';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description',
        'group',
        'root',
        'module',
        'type',
    ];
}
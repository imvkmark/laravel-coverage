<?php
/**
 * Core 模块的使用配置
 */

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamPermissionRole;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\PamRoleAccount;

return [

    /*
    |--------------------------------------------------------------------------
    | 接口文档的定义
    |--------------------------------------------------------------------------
    | 需要运行 `php artisan core:doc apidoc` 来生成技术文档
    */
    'apidoc'     => [
        'web' => [
            'title'       => '前台接口',
            'method'      => 'post',
            'default_url' => 'api_v1/system/auth/login',
        ],
    ],

    /* 维护邮箱地址
     * ---------------------------------------- */
    'op_mail'    => env('CORE_OP_MAIL', ''),

    /* Rbac 模型和外键设定
     * ---------------------------------------- */
    'rbac'       => [
        'role'            => PamRole::class,
        'account'         => PamAccount::class,
        'role_account'    => PamRoleAccount::class,
        'permission'      => PamPermission::class,
        'role_permission' => PamPermissionRole::class,
        'role_fk'         => 'role_id',
        'account_fk'      => 'account_id',
        'permission_fk'   => 'permission_id',
    ],

];
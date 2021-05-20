<?php

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamPermissionRole;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\PamRoleAccount;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Num
    |--------------------------------------------------------------------------
    |
    */
    'pagesize' => 20,

    'framework' => [
        'message_template' => [
            'backend' => 'py-mgr-page::tpl.message',
            'app'     => 'site::web.app.message',
        ],
        'json_format'      => JSON_UNESCAPED_SLASHES,
    ],

    'system' => [
        'user_location' => '/user/login',

        'secret'        => env('PY_SYS_SECRET', ''),

        //允许的Header
        'cross_headers' => 'X-APP-OS,X-APP-VERSION,X-APP-CHANNEL,X-APP-ID,X-APP-NAME',

        /* 角色-权限 类型映射
        * ---------------------------------------- */
        'role_type_map' => [
            'desktop' => 'backend',
            'front'   => 'user',
        ],
    ],

    'core' => [
        'route_hide' => [
            'py-mgr-page:backend.role.index',
            'py-mgr-page:backend.pam.index',
            'py-mgr-page:backend.pam.log',
        ],

        /*
        |--------------------------------------------------------------------------
        | 接口文档的定义
        |--------------------------------------------------------------------------
        | 需要运行 `php artisan py-core:doc api` 来生成技术文档
        */
        'apidoc'     => [
        ],

        /*
        |--------------------------------------------------------------------------
        | Rbac 权限映射
        |--------------------------------------------------------------------------
        |
        */
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
    ],
];
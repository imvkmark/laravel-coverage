<?php

return [
    /* 地区
     * ---------------------------------------- */
    'area'    => [
        'same_error'      => '父级元素不能和子集元素相同',
        'exist_error'     => '该类别下存在子元素, 不得删除',
        'undefined_error' => '条目不存在, 不得操作',
    ],

    /* 验证码
     * ---------------------------------------- */
    'captcha' => [
        'send_passport_format_error' => '无法发送验证码, 格式不正确',
        'account_miss'               => '指定账号不存在, 无法发送',
        'account_exists'             => '指定手机号已经存在, 不能绑定, 请更换',
        'account_no_password'        => '账户未设置密码',
    ],

    /* 帮助中心
    * ---------------------------------------- */
    'help'    => [
        'item_not_exist'       => '条目不存在, 不得操作',
        'parent_error'         => '分类级别错误',
        'parent_id_not_exists' => '分类级别不存在',
    ],

    'category' => [
        'item_not_exist' => '条目不存在, 不得操作',
    ],

    'image_captcha' => [
        'check_not_input' => '请填写验证码',
        'check_not_exist' => '验证码已经过期',
        'check_error'     => '验证码不正确',
    ],
    'o_auth'        => [
        'bind_type'       => '第三方绑定账号类型',
        'bind_type_error' => '第三方绑定账号类型错误',
    ],
    'oss_uploader'  => [
        'return_url_error' => '保存 AliOss 时候返回的域名地址不能为空',
    ],

    /* 用户
    * ---------------------------------------- */
    'pam'           => [
        'check_permission_need_login' => '用户需要登录',
        'not_set_name_prefix'         => '尚未设置用户名默认前缀, 无法注册, 请联系管理员',
        'account_disable_not_login'   => '本账户被禁用, 不得登入',
        'login_fail_again'            => '您输入的账号或密码有误！',
        'user_name_not_space'         => '用户名中不得包含空格',
        'role_not_exists'             => '给定的用户角色不存在',
        'mobile_already_registered'   => '该手机号已经注册过',
        'account_disabled'            => '当前用户已禁用',
        'account_enabled'             => '当前用户为启用状态',
        'disable_reason'              => '禁用原因',
        'disable_to'                  => '禁用时间',
        'account_not_exist'           => '该账户不存在',
        'sub_user_account_need_colon' => '子用户账户必须包含 :',
        'pam_error'                   => '用户不存在',
    ],
    'role'          => [
        'permissions'                  => '权限ID',
        'permission_error'             => '权限错误',
        'no_policy_to_delete'          => '无权删除此角色',
        'no_policy_to_create'          => '无权创建角色',
        'no_policy_to_update'          => '无权更改角色',
        'no_policy_to_save_permission' => '无权保存权限',
        'role_not_exists'              => '角色不存在',
        'role_has_account'             => '当前角色下存在用户, 请先清除用户的这类角色信息, 再行删除',
    ],
    'verification'  => [
        'send_passport_format_error' => '无法发送验证码, 格式不正确',
        'check_captcha_error'        => '验证码错误',
        'verify_code_expired'        => '验证码已过期, 请重新发送',
        'verify_code_error'          => '非法请求',
    ],
];
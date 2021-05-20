<?php

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

/**
 * 登录受限事件
 * 用于登录过程中拦截用户/设备等信息
 */
class LoginBannedEvent
{
    /**
     * @var PamAccount 用户账户
     */
    public $pam;

    /**
     * @var string
     */
    public $guard;

    public function __construct(PamAccount $pam, $guard)
    {
        $this->pam   = $pam;
        $this->guard = $guard;
    }
}
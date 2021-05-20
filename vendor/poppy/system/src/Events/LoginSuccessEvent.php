<?php

namespace Poppy\System\Events;

use Illuminate\Auth\SessionGuard;
use Poppy\System\Models\PamAccount;

/**
 * 登录成功事件
 */
class LoginSuccessEvent
{
    /**
     * @var PamAccount 用户账户
     */
    public $pam;

    /**
     * @var string 平台
     */
    public $platform;

    /**
     * @var SessionGuard|null
     */
    public $guard;

    public function __construct(PamAccount $pam, $platform, $guard = null)
    {
        $this->pam      = $pam;
        $this->platform = $platform;
        $this->guard    = $guard;
    }
}
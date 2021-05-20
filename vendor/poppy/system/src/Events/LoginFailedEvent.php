<?php

namespace Poppy\System\Events;

/**
 * 登录失败事件
 */
class LoginFailedEvent
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $passport;

    /**
     * @var string
     */
    public $password;

    public function __construct(array $credentials)
    {
        $this->type     = $credentials['type'] ?? '';
        $this->passport = $credentials['passport'] ?? '';
        $this->password = $credentials['password'] ?? '';
    }
}
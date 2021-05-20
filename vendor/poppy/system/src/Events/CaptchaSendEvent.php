<?php

namespace Poppy\System\Events;

/**
 * 发送验证码
 */
class CaptchaSendEvent
{

    /**
     * 通行证
     * @var string
     */
    public $passport;

    /**
     * 验证码
     * @var string
     */
    public $captcha;

    public function __construct($passport, $captcha)
    {
        $this->passport = $passport;
        $this->captcha  = $captcha;
    }
}
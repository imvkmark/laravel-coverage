<?php

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

/**
 * 用户颁发token 成功
 */
class LoginTokenPassedEvent
{
    /**
     * @var PamAccount 用户账户
     */
    public $pam;

    /**
     * @var string
     */
    public $token;

    /**
     * 设备ID
     * @var string
     */
    public $deviceId;

    /**
     * 设备类型
     * @var string
     */
    public $deviceType;

    public function __construct(PamAccount $pam, string $token, $device_id = '', $device_type = '')
    {
        $this->pam        = $pam;
        $this->token      = $token;
        $this->deviceId   = $device_id;
        $this->deviceType = $device_type;
    }
}
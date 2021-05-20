<?php

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

/**
 * 用户注册事件
 */
class PamRegisteredEvent
{
    /**
     * @var PamAccount
     */
    public $pam;

    /**
     * 设备ID
     * @var string $device_id
     */
    public $device_id;

    /**
     * PamRegisteredEvent constructor.
     * @param PamAccount $pam
     * @param string     $device_id 设备ID
     */
    public function __construct(PamAccount $pam, $device_id = '')
    {
        $this->pam       = $pam;
        $this->device_id = $device_id;
    }
}
<?php

namespace Poppy\System\Listeners\LoginTokenPassed;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Sso;
use Poppy\System\Events\LoginTokenPassedEvent;

/*
|--------------------------------------------------------------------------
| 单点登录监听
|--------------------------------------------------------------------------
| 单点登录必须传递 DeviceId/DeviceType
| HeaderOfDeviceId   : X-APP-ID (device_id)
| HeaderOfDeviceTYPE : X-APP-OS (device_type)
*/

class SsoListener
{
    /**
     * Handle the event.
     * @param LoginTokenPassedEvent $event 用户账号
     * @return void
     * @throws ApplicationException |\Exception
     */
    public function handle(LoginTokenPassedEvent $event)
    {
        $Sso = new Sso();
        if (!$Sso->handle($event->pam, $event->deviceId, $event->deviceType, $event->token)) {
            throw new ApplicationException($Sso->getError());
        }
    }
}

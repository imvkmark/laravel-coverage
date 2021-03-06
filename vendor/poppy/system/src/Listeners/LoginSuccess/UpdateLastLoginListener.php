<?php

namespace Poppy\System\Listeners\LoginSuccess;

use Carbon\Carbon;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\System\Events\LoginSuccessEvent;

/**
 * 登录成功更新登录次数 + 最后登录时间
 */
class UpdateLastLoginListener
{
	/**
	 * @param LoginSuccessEvent $event 登录成功
	 */
	public function handle(LoginSuccessEvent $event)
	{
		$pam              = $event->pam;
		$pam->logined_at  = Carbon::now();
		$pam->login_times += 1;
		$pam->login_ip    = EnvHelper::ip();
		$pam->save();
	}
}


<?php

namespace Poppy\System\Listeners\LoginSuccess;

use Poppy\Framework\Helper\EnvHelper;
use Poppy\System\Events\LoginSuccessEvent;
use Poppy\System\Models\PamLog;
use Throwable;

/**
 * 记录登录日志
 */
class LogListener
{
	/**
	 * @param LoginSuccessEvent $event 登录成功
	 */
	public function handle(LoginSuccessEvent $event)
	{
		$pam = $event->pam;

		$ip = EnvHelper::ip();

		try {
			$areaText = class_exists('Poppy\Extension\IpStore\Support\Facade')
				? app('poppy.ext.ip_store')->area($ip)
				: '';
		} catch (Throwable $e) {
			$areaText = '';
		}

		if (is_array($areaText)) {
			$areaText = implode(' ', $areaText);
		}

		PamLog::create([
			'account_id'   => $pam->id,
			'account_type' => $pam->type,
			'type'         => 'success',
			'parent_id'    => $pam->parent_id,
			'ip'           => $ip,
			'area_text'    => $areaText,
			'area_name'    => '',
		]);
	}
}


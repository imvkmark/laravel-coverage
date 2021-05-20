<?php

namespace Poppy\System\Classes\Traits;

use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\System\Setting\Repository\SettingRepository;

/**
 * Db Trait Db 工具
 */
trait SystemTrait
{

	/**
	 * 检查当前是否是在事务中
	 * @return SettingRepository
	 */
	protected function sysSetting(): SettingRepository
	{
		return app(SettingContract::class);
	}
}
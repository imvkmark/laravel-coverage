<?php

namespace Poppy\System\Setting\Facades;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * 设置 Facade
 */
class SettingFacade extends IlluminateFacade
{
	/**
	 * 获取组件的注册名称
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'poppy.system.setting';
	}
}
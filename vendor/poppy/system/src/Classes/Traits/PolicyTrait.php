<?php

namespace Poppy\System\Classes\Traits;

use Poppy\System\Models\PamAccount;

/**
 * 策略映射
 */
trait PolicyTrait
{
	/**
	 * @param PamAccount $pam     账号
	 * @param string     $ability 能力
	 * @return bool|null
	 */
	public function before(PamAccount $pam, $ability)
	{
		$permission = self::$permissionMap[$ability] ?? '';

		return $permission ? $pam->capable($permission) : null;
	}

	/**
	 * 策略映射
	 * @return mixed
	 */
	public static function getPermissionMap()
	{
		return self::$permissionMap;
	}
}
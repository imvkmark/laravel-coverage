<?php

namespace Poppy\System\Classes\Traits;


/**
 * 用户设置和获取
 */
trait UserSettingTrait
{
	/**
	 * @param string $key       key
	 * @param array  $values    值
	 * @param array  $available 数组
	 * @return bool
	 */
	public function userSettingSet($key, $values, array $available = []): bool
	{
		if (!$this->checkPam()) {
			return false;
		}

		if (!is_array($values)) {
			return $this->setError('输入数据不是正确的格式');
		}
		$data = [];
		foreach ($values as $k => $v) {
			if ($available && !in_array($k, $available, false)) {
				continue;
			}

			$data[$k] = $v;
		}

		if (!app('poppy.system.setting')->set([
			'user::pam-' . $this->pam->id . '.' . $key => $data,
		])) {
			return $this->setError(app('poppy.system.setting')->getError());
		}

		return true;
	}

	/**
	 * 获取用户配置
	 * @param string $group 组
	 * @return array
	 */
	public function userSettingGet($group): array
	{
		if (!$this->checkPam()) {
			return [];
		}

		return sys_setting('user::pam-' . $this->pam->id . '.' . $group) ?: [];
	}
}
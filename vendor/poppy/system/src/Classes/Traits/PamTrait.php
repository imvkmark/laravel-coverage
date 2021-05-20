<?php

namespace Poppy\System\Classes\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Poppy\System\Models\PamAccount;

/**
 * Pam Trait
 * Pam 的验证, 设置, 获取
 */
trait PamTrait
{

	/**
	 * @var PamAccount;
	 */
	protected $pam;

	/**
	 * @return PamAccount
	 */
	public function getPam(): PamAccount
	{
		return $this->pam;
	}

	/**
	 * 检查 pam 用户
	 * @return bool
	 */
	public function checkPam(): bool
	{
		if (!$this->pam) {
			return $this->setError(trans('py-system::action.pam.check_permission_need_login'));
		}

		return true;
	}

	/**
	 * Set Pam Account.
	 * @param PamAccount|Authenticatable|int $pam 用户
	 * @return self
	 */
	public function setPam($pam): self
	{
		if (is_numeric($pam)) {
			$this->pam = PamAccount::find($pam);
		}
		else {
			$this->pam = $pam;
		}

		return $this;
	}

	/**
	 * 检测当前用户是否存在权限
	 * @param string $permission_key 权限KEY
	 * @return bool
	 */
	public function checkPermission($permission_key): bool
	{
		if (!$this->checkPam()) {
			return false;
		}

		$corePermission = app('poppy.core.permission');
		$permission     = $corePermission->permissions()->offsetGet($permission_key);

		if ($permission) {
			if ($this->pam->capable($permission->key())) {
				return true;
			}

			return $this->setError("没有 '{$permission->description()} '权限, 无权操作");
		}

		return $this->setError("需检测的权限 '{$permission_key}' 不存在!");
	}
}
<?php

namespace Poppy\System\Classes\Contracts;

/**
 * 数据库更新数据
 */
interface ProgressContract
{
	/**
	 * 业务逻辑执行
	 */
	public function handle():array;
}
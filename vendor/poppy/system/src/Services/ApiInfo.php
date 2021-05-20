<?php

namespace Poppy\System\Services;

use Poppy\Core\Services\Contracts\ServiceArray;

class ApiInfo implements ServiceArray
{

	public function key():string
	{
		return 'py-system';
	}

	public function data()
	{
		return [
			'title' => '系统',
		];
	}
}
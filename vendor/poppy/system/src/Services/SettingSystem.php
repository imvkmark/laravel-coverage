<?php

namespace Poppy\System\Services;

use Poppy\Core\Services\Contracts\ServiceArray;
use Poppy\System\Http\Forms\Settings\FormSettingPam;
use Poppy\System\Http\Forms\Settings\FormSettingSite;

class SettingSystem implements ServiceArray
{

	public function key():string
	{
		return 'poppy.system';
	}

	public function data()
	{
		return [
			'title' => '系统',
			'forms' => [
				FormSettingSite::class,
				FormSettingPam::class,
			],
		];
	}
}
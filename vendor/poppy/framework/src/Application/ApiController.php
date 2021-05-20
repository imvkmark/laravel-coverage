<?php

namespace Poppy\Framework\Application;

/**
 * Api Controller
 */
class ApiController extends Controller
{
	/**
	 * ApiController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		py_container()->setExecutionContext('api');
	}
}
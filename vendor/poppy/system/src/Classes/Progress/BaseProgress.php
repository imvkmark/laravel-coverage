<?php

namespace Poppy\System\Classes\Progress;

use Poppy\System\Classes\Contracts\ProgressContract;
use Poppy\System\Classes\Traits\FixTrait;

/**
 * 数据库更新读取
 */
abstract class BaseProgress implements ProgressContract
{

    use FixTrait;


    public function __construct()
    {
        $this->fixInit();
    }
}

<?php

namespace Poppy\Core\Services\Contracts;

/**
 * 数组
 */
interface ServiceArray
{
    /**
     * key
     * @return string
     */
    public function key();

    /**
     * 返回的数据
     * @return mixed
     */
    public function data();
}
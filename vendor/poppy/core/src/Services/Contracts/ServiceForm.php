<?php

namespace Poppy\Core\Services\Contracts;

/**
 * 返回表单项目
 */
interface ServiceForm
{
    /**
     * 构造器
     * @param array $params 参数
     * @return mixed
     */
    public function builder(array $params = []);
}
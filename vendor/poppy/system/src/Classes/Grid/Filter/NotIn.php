<?php

namespace Poppy\System\Classes\Grid\Filter;

class NotIn extends In
{
    /**
     * @inheritDoc
     */
    protected $query = 'whereNotIn';
}

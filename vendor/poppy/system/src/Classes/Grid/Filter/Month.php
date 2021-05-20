<?php

namespace Poppy\System\Classes\Grid\Filter;

class Month extends Date
{
    /**
     * @inheritDoc
     */
    protected $query = 'whereMonth';

    /**
     * @var string
     */
    protected $fieldName = 'month';
}

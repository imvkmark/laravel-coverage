<?php

namespace Poppy\System\Classes\Grid\Filter;

class Day extends Date
{
    /**
     * @inheritDoc
     */
    protected $query = 'whereDay';

    /**
     * @var string
     */
    protected $fieldName = 'day';
}

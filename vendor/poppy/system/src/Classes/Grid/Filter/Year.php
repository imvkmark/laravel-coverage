<?php

namespace Poppy\System\Classes\Grid\Filter;

class Year extends Date
{
    /**
     * @inheritDoc
     */
    protected $query = 'whereYear';

    /**
     * @var string
     */
    protected $fieldName = 'year';
}

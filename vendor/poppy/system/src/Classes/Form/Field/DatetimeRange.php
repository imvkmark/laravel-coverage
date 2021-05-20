<?php

namespace Poppy\System\Classes\Form\Field;

class DatetimeRange extends Date
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->options([
            'layui-range' => true,
            'layui-type'  => 'datetime',
        ]);
        return parent::render();
    }
}

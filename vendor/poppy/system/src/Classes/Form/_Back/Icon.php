<?php

namespace Poppy\System\Classes\Form\Field;

class Icon extends Text
{
    protected $default = 'fa-pencil';

    public function render()
    {

        $this->prepend('<i class="fa fa-pencil fa-fw"></i>')
            ->defaultAttribute('style', 'width: 140px');

        return parent::render();
    }
}

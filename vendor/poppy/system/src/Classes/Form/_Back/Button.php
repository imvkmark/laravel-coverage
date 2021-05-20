<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\System\Classes\Form\Field;

class Button extends Field
{
    protected $class = ' layui-btn-primary';


    public function __construct($label = '')
    {
        $this->label = $label;
    }

    public function info()
    {
        $this->class = ' layui-btn-info';

        return $this;
    }

    public function small()
    {
        $this->class .= ' layui-btn-sm';

        return $this;
    }

    public function render()
    {
        $this->attribute([
            'class' => 'layui-btn' . $this->class,
        ]);
        return parent::render();
    }
}

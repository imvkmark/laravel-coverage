<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\System\Classes\Form\Field;
use Poppy\System\Classes\Form\Traits\PlainInput;

class Date extends Field
{
    use PlainInput;

    protected $options = [
        'type' => 'date',
    ];

    protected $attributes = [
        'style' => 'width: 110px',
    ];

    protected $view = 'py-system::tpl.form.date';

    public function render()
    {

        $this->prepend('<i class="fa fa-calendar fa-fw"></i>');
        $this->addVariables([
            'prepend' => $this->prepend,
            'options' => $this->options,
        ]);
        return parent::render();
    }
}

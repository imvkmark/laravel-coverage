<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\System\Classes\Form\Field;

class Slider extends Field
{


    protected $options = [
        'type'     => 'single',
        'prettify' => false,
        'hasGrid'  => true,
    ];

    public function render()
    {
        return parent::render();
    }
}

<?php

namespace Poppy\System\Classes\Form\Field;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class Color extends Text
{

    protected $view = 'py-system::tpl.form.color';

    /**
     * Render this filed.
     *
     * @return Factory|View
     */
    public function render()
    {
        $this->prepend('<i class="fa fa-palette"></i>')
            ->defaultAttribute('style', 'width: 140px');

        return parent::render();
    }
}

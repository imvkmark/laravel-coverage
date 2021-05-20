<?php

namespace Poppy\System\Classes\Contracts;

use Illuminate\Contracts\Support\Renderable;

/**
 * 界面渲染
 * @deprecated
 * @see     Renderable
 * @removed 4.0
 */
interface UiContract
{
    public function render();
}
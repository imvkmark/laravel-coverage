<?php

namespace Poppy\System\Classes\Grid\Concerns;

use Closure;
use Poppy\System\Classes\Grid;
use Poppy\System\Classes\Grid\Tools;

trait HasTools
{
    use HasQuickSearch;

    /**
     * Header tools.
     *
     * @var Tools
     */
    public $tools;

    /**
     * Setup grid tools.
     *
     * @param Closure $callback
     *
     * @return void
     */
    public function tools(Closure $callback)
    {
        call_user_func($callback, $this->tools);
    }

    /**
     * Render custom tools.
     *
     * @return string
     */
    public function renderHeaderTools(): string
    {
        return $this->tools->render();
    }

    /**
     * Setup grid tools.
     *
     * @param Grid $grid
     * @return $this
     */
    protected function initTools(Grid $grid): self
    {
        $this->tools = new Tools($grid);
        return $this;
    }
}

<?php

namespace Poppy\System\Classes\Grid\Tools;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Poppy\System\Classes\Grid\Concerns\HasQuickSearch;

class QuickSearch extends AbstractTool
{
    /**
     * @var string
     */
    protected $view = 'py-system::tpl.grid.quick-search';

    /**
     * @var string
     */
    protected $placeholder;

    /**
     * Set placeholder.
     *
     * @param string $text
     *
     * @return $this
     */
    public function placeholder($text = '')
    {
        $this->placeholder = $text;

        return $this;
    }

    /**
     * @return Factory|View
     */
    public function render()
    {
        $query = request()->query();

        Arr::forget($query, HasQuickSearch::$searchKey);

        $vars = [
            'action'      => request()->url() . '?' . http_build_query($query),
            'key'         => HasQuickSearch::$searchKey,
            'value'       => request(HasQuickSearch::$searchKey),
            'placeholder' => $this->placeholder,
        ];

        return view($this->view, $vars);
    }
}

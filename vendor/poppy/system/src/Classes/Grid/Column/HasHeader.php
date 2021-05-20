<?php
/*
 * This is NOT a Free software.
 * When you have some Question or Advice can contact Me.
 * @author     Duoli <zhaody901@126.com>
 * @copyright  Copyright (c) 2013-2021 Poppy Team
 */
namespace Poppy\System\Classes\Grid\Column;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Poppy\System\Classes\Grid\Model;

trait HasHeader
{
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Add contents to column header.
     *
     * @param string|Renderable|Htmlable $header
     *
     * @return $this
     */
    public function addHeader($header)
    {
        if ($header instanceof Filter) {
            $header->setParent($this);
            $this->filter = $header;
        }

        $this->headers[] = $header;
        return $this;
    }

    /**
     * Add a binding based on filter to the model query.
     *
     * @param Model $model
     */
    public function bindFilterQuery(Model $model)
    {
        if ($this->filter) {
            $this->filter->addBinding(request($this->name), $model);
        }
    }

    /**
     * Render Column header.
     *
     * @return string
     */
    public function renderHeader()
    {
        return collect($this->headers)->map(function ($item) {
            if ($item instanceof Renderable) {
                return $item->render();
            }

            if ($item instanceof Htmlable) {
                return $item->toHtml();
            }

            return (string) $item;
        })->implode('');
    }

    public function getHeader()
    {
        return $this->headers;
    }

    /**
     * Add a filter to column header.
     *
     * @return $this
     */
    protected function addFilter($type = null, $formal = null)
    {
        if (is_array($type)) {
            return $this->addHeader(new CheckFilter($type));
        }

        if (is_null($type)) {
            $type = 'equal';
        }

        if (in_array($type, ['equal', 'like', 'date', 'time', 'datetime'])) {
            return $this->addHeader(new InputFilter($type));
        }

        if ($type === 'range') {
            if (is_null($formal)) {
                $formal = 'equal';
            }

            return $this->addHeader(new RangeFilter($formal));
        }

        return $this;
    }
}

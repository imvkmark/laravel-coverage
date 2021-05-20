<?php

namespace Poppy\System\Classes\Form\Field;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Checkbox extends MultipleSelect
{
    protected $inline = true;

    protected $canCheckAll = false;


    /**
     * @inheritDoc
     */
    public function fill($data)
    {
        $this->checked = (array) Arr::get($data, $this->column);
    }

    /**
     * Set options.
     *
     * @param array|callable|string $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        if (is_callable($options)) {
            $this->options = $options;
        }
        else {
            $this->options = (array) $options;
        }

        return $this;
    }

    /**
     * Set checked.
     *
     * @param array|callable|string $checked
     *
     * @return $this
     */
    public function checked($checked = [])
    {
        if ($checked instanceof Arrayable) {
            $checked = $checked->toArray();
        }

        $this->checked = (array) $checked;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->addVariables([
            'checked'     => $this->checked,
            'inline'      => $this->inline,
            'canCheckAll' => $this->canCheckAll,
        ]);

        if ($this->canCheckAll) {
            $checkAllClass = uniqid('check-all-');
            $this->addVariables(['checkAllClass' => $checkAllClass]);
        }

        $this->attribute('lay-skin', 'primary');

        return parent::render();
    }

    /**
     * Add a checkbox above this component, so you can select all checkboxes by click on it.
     *
     * @return $this
     */
    public function canCheckAll()
    {
        $this->canCheckAll = true;

        return $this;
    }

    /**
     * Draw inline checkboxes.
     *
     * @return $this
     */
    public function inline()
    {
        $this->inline = true;

        return $this;
    }

    /**
     * Draw stacked checkboxes.
     *
     * @return $this
     */
    public function stacked()
    {
        $this->inline = false;

        return $this;
    }
}

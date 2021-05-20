<?php

namespace Poppy\System\Classes\Form\Field;

use Illuminate\Support\Arr;
use Poppy\System\Classes\Form\Field;

class ListField extends Field
{
    /**
     * Max list size.
     *
     * @var int
     */
    protected $max;

    /**
     * Minimum list size.
     *
     * @var int
     */
    protected $min = 0;

    /**
     * @var array
     */
    protected $value = [''];

    /**
     * Set Max list size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function max(int $size)
    {
        $this->max = $size;

        return $this;
    }

    /**
     * Set Minimum list size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function min(int $size)
    {
        $this->min = $size;

        return $this;
    }

    /**
     * Fill data to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function fill($data)
    {
        $this->data = $data;

        $this->value = Arr::get($data, $this->column, $this->value);

        $this->formatValue();
    }

    /**
     * @inheritDoc
     */
    public function getValidator(array $input)
    {
        if ($this->validator) {
            return $this->validator->call($this, $input);
        }

        if (!is_string($this->column)) {
            return false;
        }

        $rules = $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        if (!Arr::has($input, $this->column)) {
            return false;
        }

        $rules["{$this->column}.values.*"]      = $fieldRules;
        $attributes["{$this->column}.values.*"] = __('Value');

        $rules["{$this->column}.values"][] = 'array';

        if (!is_null($this->max)) {
            $rules["{$this->column}.values"][] = "max:$this->max";
        }

        if (!is_null($this->min)) {
            $rules["{$this->column}.values"][] = "min:$this->min";
        }

        $attributes["{$this->column}.values"] = $this->label;

        return validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * @inheritDoc
     */
    public function prepare($value)
    {
        return array_values($value['values']);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->setupScript();


        return parent::render();
    }
}

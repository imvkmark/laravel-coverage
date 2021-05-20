<?php

namespace Poppy\System\Classes\Form\Field;

/**
 * Class ListBox.
 *
 * @see https://github.com/istvan-ujjmeszaros/bootstrap-duallistbox
 */
class Listbox extends MultipleSelect
{
    protected $settings = [];


    public function settings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Set listbox height.
     *
     * @param int $height
     *
     * @return Listbox
     */
    public function height($height = 200)
    {
        return $this->settings(['selectorMinimalHeight' => $height]);
    }

    /**
     * @inheritDoc
     */
    protected function loadRemoteOptions($url, $parameters = [], $options = [])
    {
        return $this;
    }

    public function render()
    {
        $this->attribute('data-value', implode(',', (array) $this->value()));

        return parent::render();
    }
}

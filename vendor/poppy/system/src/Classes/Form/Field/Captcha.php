<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Classes\Form;

class Captcha extends Text
{
    protected $rules = ['captcha'];

    protected $view = 'py-system::tpl.form.captcha';

    /**
     * Captcha constructor.
     * @param       $column
     * @param array $arguments
     * @throws ApplicationException
     */
    public function __construct($column, $arguments = [])
    {
        if (!class_exists('\Mews\Captcha\Captcha')) {
            throw new ApplicationException('To use captcha field, please install [mews/captcha] first.');
        }

        parent::__construct($column, $arguments);
    }

    public function setForm(Form $form = null)
    {
        $this->form = $form;

        $this->form->ignore($this->column);

        return $this;
    }
}

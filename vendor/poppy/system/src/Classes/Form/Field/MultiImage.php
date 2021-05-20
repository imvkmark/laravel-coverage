<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\System\Classes\Form\Field;

final class MultiImage extends Field
{

    /**
     * @inheritDoc
     */
    protected $view = 'py-system::tpl.form.multi_image';

    /**
     * Token
     * @var string
     */
    private $token;

    public function token($token): self
    {
        $this->token = $token;
        return $this;
    }


    public function render()
    {
        $this->attribute([
            'token' => $this->token,
        ]);
        return parent::render();
    }
}

<?php

namespace Poppy\System\Classes\Form\Field;

use Poppy\System\Classes\Form\Field;

class File extends Field
{
    public function image()
    {
        $this->options['type'] = 'images';
    }

    public function file()
    {
        $this->options['type'] = 'file';
    }

    public function audio()
    {
        $this->options['type'] = 'audio';
    }

    public function video()
    {
        $this->options['type'] = 'video';
    }
}

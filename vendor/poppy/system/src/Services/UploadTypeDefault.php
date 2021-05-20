<?php

namespace Poppy\System\Services;

use Poppy\Core\Services\Contracts\ServiceArray;
use Poppy\System\Classes\Uploader\DefaultUploadProvider;

class UploadTypeDefault implements ServiceArray
{

    public function key(): string
    {
        return 'default';
    }

    public function data()
    {
        return [
            'title'    => '默认(uploads 目录下)',
            'provider' => DefaultUploadProvider::class,
        ];
    }
}
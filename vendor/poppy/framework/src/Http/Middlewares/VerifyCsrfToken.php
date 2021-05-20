<?php

namespace Poppy\Framework\Http\Middlewares;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

/**
 * csrf 校验, 暂时均未开启
 */
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * @var array
     */
    protected $except = [];

    /**
     * Create a new middleware instance.
     *
     * @param Application $app
     * @param Encrypter   $encrypter
     * @return void
     */
    public function __construct(Application $app, Encrypter $encrypter)
    {
        parent::__construct($app, $encrypter);
        $this->except = (array) config('poppy.framework.csrf_except');
    }
}
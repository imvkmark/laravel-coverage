<?php

namespace Poppy\System\Http\Middlewares;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * Jwt 校验, 验证Token 存在以及Token 是否有效
 */
class JwtAuthenticate extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $this->auth->setRequest($request)->getToken();

        if (!$token || !$this->auth->check(true)) {
            return response('Unauthorized Jwt.', 401);
        }

        return $next($request);
    }
}
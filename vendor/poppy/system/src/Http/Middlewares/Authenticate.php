<?php

namespace Poppy\System\Http\Middlewares;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as IlluminateAuthenticate;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Models\PamAccount;

/**
 * Class Authenticate.
 */
class Authenticate extends IlluminateAuthenticate
{
    /**
     * 检测跳转地址
     * @param $guards
     * @return string
     */
    public static function detectLocation($guards): string
    {
        $location = '';
        // develop
        if (in_array(PamAccount::GUARD_DEVELOP, $guards, true) && $devLogin = config('poppy.system.prefix') . '/develop/login') {
            $location = $devLogin;
        }
        if (in_array(PamAccount::GUARD_BACKEND, $guards, true) && $backendLogin = config('poppy.system.prefix') . '/login') {
            $location = $backendLogin;
        }
        if (in_array(PamAccount::GUARD_WEB, $guards, true) && $userLogin = config('poppy.system.user_location')) {
            $location = $userLogin;
        }
        return $location;
    }

    /**
     * @inheritDoc
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            return app('auth')->authenticate();
        }
        foreach ($guards as $guard) {
            if (app('auth')->guard($guard)->check()) {
                return app('auth')->shouldUse($guard);
            }
        }
        throw new AuthenticationException('Unauthenticated.', $guards);
    }

    /**
     * @inheritDoc
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);

        } catch (AuthenticationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 401,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $append = [];
            if ($location = self::detectLocation($guards)) {
                $append['_location'] = $location;
                return Resp::error('无权限访问', $append);
            }

            return response('Unauthorized.', 401);
        }
        return $next($request);
    }
}
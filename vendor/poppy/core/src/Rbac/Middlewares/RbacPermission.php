<?php

namespace Poppy\Core\Rbac\Middlewares;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Exceptions\PermissionException;
use Poppy\Core\Rbac\Traits\RbacUserTrait;
use Poppy\Framework\Classes\Resp;
use Route;


/*
|--------------------------------------------------------------------------
| 用户权限中间件
|--------------------------------------------------------------------------
| 如果用户需要有额外需要通过的用户, 放到 passed 中来忽略此权限验证
*/

/**
 * 用户权限
 */
class RbacPermission
{
    use CoreTrait;

    /**
     * Handle an incoming request.
     * @param Request $request 请求
     * @param Closure $next    后续处理
     * @return mixed
     * @throws PermissionException
     */
    public function handle($request, Closure $next)
    {
        /** @var RbacUserTrait $user */
        $user = $request->user();

        if (!method_exists($user, 'capable')) {
            throw new PermissionException('用户没有检测权限的方法, 无法使用此中间件');
        }

        $controller = Route::current()->controller;

        /* 未定义权限, 通过
         * ---------------------------------------- */
        if (!($controller::$permission ?? '')) {
            return $next($request);
        }

        /* 超级管理员通过
         * ---------------------------------------- */
        if (method_exists($this, 'passed')) {
            if ($this->passed($user)) {
                return $next($request);
            }
        }

        $permissions = $controller::$permission;

        /* 存在方法权限, 不验证 global
         * ---------------------------------------- */
        $method           = Str::after(Route::currentRouteAction(), '@');
        $methodPermission = $permissions[$method] ?? '';
        if ($methodPermission && $this->corePermission()->has($methodPermission)) {
            if ($user->capable($methodPermission)) {
                return $next($request);
            }

            return Resp::error('用户方法权限访问受限');
        }

        /* 全局权限
         * ---------------------------------------- */
        $globalPermission = $permissions['global'] ?? '';
        if ($globalPermission && $this->corePermission()->has($globalPermission)) {
            if ($user->capable($globalPermission)) {
                return $next($request);
            }
            return Resp::error('用户权限访问受限');
        }
        return $next($request);
    }
}

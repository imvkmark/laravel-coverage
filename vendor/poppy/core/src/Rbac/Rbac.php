<?php

namespace Poppy\Core\Rbac;

use Illuminate\Foundation\Application;
use Poppy\Core\Rbac\Contracts\RbacUserContract;

/**
 * This class is the main entry point of rbac. Usually this the interaction
 * with this class will be done through the Entrust Facade
 */
class Rbac
{
    /**
     * Laravel application
     * @var Application
     */
    public $app;

    /**
     * Create a new confide instance.
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Checks if the current user has a role by its name
     * @param string       $guard      防护器
     * @param string|array $role       角色数组/字串
     * @param bool         $requireAll 是否要求全部匹配
     * @return bool
     */
    public function hasRole($guard, $role, $requireAll = false)
    {
        if ($user = $this->user($guard)) {
            return $user->hasRole($role, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a permission by its name
     * 检测当前用户是否有权限
     * @param string $guard      防护器
     * @param string $permission permission string
     * @param bool   $requireAll 是否要求全部匹配
     * @return bool
     */
    public function capable($guard, $permission, $requireAll = false)
    {
        if ($user = $this->user($guard)) {
            return $user->capable($permission, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a role or permission by its name
     * @param array|string $roles       the role(s) needed
     * @param array|string $permissions the permission(s) needed
     * @param array        $options     the Options
     * @return bool
     */
    public function ability($guard, $roles, $permissions, $options = [])
    {
        if ($user = $this->user($guard)) {
            return $user->ability($roles, $permissions, $options);
        }

        return false;
    }

    /**
     * Get the currently authenticated user or null.
     * @param string $guard 获取用户信息
     * @return RbacUserContract
     */
    public function user($guard)
    {
        return $this->app->auth->guard($guard)->user();
    }

    /**
     * Filters a route for a role or set of roles.
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     * @param string       $route      Route pattern. i.e: "admin/*"
     * @param array|string $roles      The role(s) needed
     * @param mixed        $result     i.e: Redirect::to('/')
     * @param bool         $requireAll User must have all roles
     * @return mixed
     */
    public function routeNeedsRole($route, $roles, $result = null, $requireAll = true)
    {
        $filterName = is_array($roles) ? implode('_', $roles) : $roles;
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($roles, $result, $requireAll) {
            $hasRole = $this->hasRole($roles, $requireAll);

            if (!$hasRole) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }

    /**
     * Filters a route for a permission or set of permissions.
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $permissions The permission(s) needed
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $requireAll  User must have all permissions
     * @return mixed
     */
    public function routeNeedsPermission($route, $permissions, $result = null, $requireAll = true)
    {
        $filterName = is_array($permissions) ? implode('_', $permissions) : $permissions;
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($permissions, $result, $requireAll) {
            $hasPerm = $this->capable($permissions, $requireAll);

            if (!$hasPerm) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }

    /**
     * Filters a route for role(s) and/or permission(s).
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $roles       The role(s) needed
     * @param array|string $permissions The permission(s) needed
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $requireAll  User must have all roles and permissions
     * @return void
     */
    public function routeNeedsRoleOrPermission($route, $roles, $permissions, $result = null, $requireAll = false)
    {
        $filterName = is_array($roles) ? implode('_', $roles) : $roles;
        $filterName .= '_' . (is_array($permissions) ? implode('_', $permissions) : $permissions);
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($roles, $permissions, $result, $requireAll) {
            $hasRole  = $this->hasRole($roles, $requireAll);
            $hasPerms = $this->capable($permissions, $requireAll);

            if ($requireAll) {
                $hasRolePerm = $hasRole && $hasPerms;
            }
            else {
                $hasRolePerm = $hasRole || $hasPerms;
            }

            if (!$hasRolePerm) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }
}
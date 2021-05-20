<?php namespace Site\Http\Foundation;

/**
 * poppy http kernel
 */
class Kernel extends \Poppy\Framework\Foundation\Http\Kernel
{
    /**
     * The application's global HTTP middleware stack.
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Foundation\Http\Middleware\ValidatePostSize',
    ];
}
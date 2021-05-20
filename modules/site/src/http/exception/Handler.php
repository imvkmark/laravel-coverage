<?php namespace Site\Http\Exception;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Exceptions\HintException;
use Poppy\Framework\Exceptions\PolicyException;
use Poppy\Framework\Foundation\Exception\Handler as ExceptionHandler;
use ReflectionException;

class Handler extends ExceptionHandler
{
    /**
     * All of the register exception handlers.
     * @var array
     */
    protected $handlers = [];


    protected $dontReport = [
        HintException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     * @param Request   $request
     * @param Exception $exception
     * @return Response
     * @throws ReflectionException
     */
    public function render($request, Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            /* 启用 Sentry 进行异常报警
            * ---------------------------------------- */
            if (app()->bound('sentry')) {
                app('sentry')->captureException($exception);
            }
        }

        if ($exception instanceof ApplicationException) {
            if ($exception->getCode()) {
                return Resp::web($exception->getCode(), $exception->getMessage());
            }
            return Resp::error($exception->getMessage());
        }

        // 之前的策略异常
        if ($exception instanceof PolicyException) {
            return Resp::web(Resp::NO_AUTH, $exception->getMessage());
        }

        if ($exception instanceof TokenMismatchException) {
            return Resp::error('凭证不匹配');
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return Resp::error('无权限访问', [
            'location' => url('/'),
        ]);
    }
}

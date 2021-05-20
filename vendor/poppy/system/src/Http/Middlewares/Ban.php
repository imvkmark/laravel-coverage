<?php

namespace Poppy\System\Http\Middlewares;

use Closure;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Models\PamToken;

class Ban
{

    /**
     * @var PamToken
     */
    public static $init;

    /**
     * @param         $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //获取ip
        $ip = EnvHelper::ip();

        $rds = RdsDb::instance();
        $md5 = md5($ip);

        // 初始化这个KEY, 这个初始化可能会放到系统中
        if (!$rds->exists(PySystemDef::ckBan())) {
            (new \Poppy\System\Action\Ban())->init();
        }

        if ($rds->hExists(PySystemDef::ckBan(), $md5)) {
            return Resp::error('当前ip被封禁，请联系客服处理');
        }

        $deviceId = request()->header('X-APP-ID') ?: input('device_id');
        if ($deviceId) {
            $md5 = md5($deviceId);
            if ($rds->hExists(PySystemDef::ckBan(), $md5)) {
                return Resp::error('当前设备被封禁，请联系客服处理');
            }
        }

        return $next($request);
    }
}
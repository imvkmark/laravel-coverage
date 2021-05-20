<?php

namespace Poppy\System\Classes\Api\Sign;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Poppy\Framework\Helper\ArrayHelper;

/**
 * 后台用户认证
 */
class DefaultApiSignProvider extends DefaultBaseApiSign
{

    /**
     * @var Request 请求内容
     */
    private $request;

    public function __construct()
    {
        $this->request = app('request');
    }

    /**
     * 获取Sign
     * @param array $params 请求参数
     * @return string
     */
    public function sign(array $params): string
    {
        $dirtyParams = $params;
        $params      = $this->except($params);
        $token       = function ($params) {
            $token = $this->request->header('Authorization');
            if ($token && Str::startsWith($token, 'Bearer')) {
                $token = substr($token, 7);
            }
            if (!$token) {
                $token = $this->request->input('token');
            }

            if (!$token) {
                $token = $params['token'] ?? '';
            }
            return $token;
        };
        ksort($params);
        $kvStr    = ArrayHelper::toKvStr($params);
        $signLong = md5(md5($kvStr) . $token($dirtyParams));
        return $signLong[1] . $signLong[3] . $signLong[15] . $signLong[31];
    }
}
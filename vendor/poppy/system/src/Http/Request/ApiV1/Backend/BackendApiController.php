<?php

namespace Poppy\System\Http\Request\ApiV1\Backend;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Poppy\Framework\Application\ApiController;
use Poppy\System\Models\PamAccount;

/**
 * Backend Api 控制器
 */
abstract class BackendApiController extends ApiController
{
    /**
     * 返回 Jwt 用户
     * @return Authenticatable|PamAccount
     */
    protected function jwtPam()
    {
        return Auth::guard(PamAccount::GUARD_JWT_BACKEND)->user();
    }
}
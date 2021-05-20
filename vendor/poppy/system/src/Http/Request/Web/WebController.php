<?php

namespace Poppy\System\Http\Request\Web;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Poppy\Framework\Application\Controller;
use Poppy\System\Models\PamAccount;

/**
 * 网页入口
 */
abstract class WebController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->withViews();
    }

    /**
     * 当前用户
     * @return Authenticatable|PamAccount
     */
    public function pam()
    {
        return Auth::guard(PamAccount::GUARD_WEB)->user();
    }

    /**
     * 当前用户
     * @return Authenticatable|PamAccount
     */
    public function jwtPam()
    {
        return Auth::guard(PamAccount::GUARD_JWT_WEB)->user();
    }
}

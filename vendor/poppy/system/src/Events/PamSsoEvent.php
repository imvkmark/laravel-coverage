<?php

namespace Poppy\System\Events;

use Illuminate\Support\Collection;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamToken;

/**
 * 用户单点登录事件触发
 */
class PamSsoEvent
{
    /**
     * @var PamAccount
     */
    public $pam;

    /**
     * @var string 单点登录被踢下去的用户的数据
     */
    public $tokens;


    /**
     * PamDisableEvent constructor.
     * @param PamAccount            $pam
     * @param Collection|PamToken[] $tokens
     */
    public function __construct(PamAccount $pam, $tokens)
    {
        $this->pam    = $pam;
        $this->tokens = $tokens;
    }
}
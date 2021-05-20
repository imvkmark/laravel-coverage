<?php

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

/**
 * 用户启用
 */
class PamEnableEvent
{
    /**
     * @var PamAccount
     */
    public $pam;

    /**
     * @var PamAccount 操作账号
     */
    public $editor;

    /**
     * @var string 禁用原因
     */
    public $reason;


    /**
     * PamDisableEvent constructor.
     * @param PamAccount $pam
     * @param PamAccount $editor
     * @param string     $reason
     */
    public function __construct(PamAccount $pam, PamAccount $editor, $reason = '')
    {
        $this->pam    = $pam;
        $this->editor = $editor;
        $this->reason = $reason;
    }
}
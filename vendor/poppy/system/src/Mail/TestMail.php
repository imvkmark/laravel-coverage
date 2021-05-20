<?php

namespace Poppy\System\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * 测试发送
 */
class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string 发送内容
     */
    public $content;

    /**
     * Create a new message instance.
     *
     * @param $content
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('py-system::mail.test');
    }
}

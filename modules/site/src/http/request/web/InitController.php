<?php namespace Site\Http\Request\Web;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\View\View;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Traits\ViewTrait;

/**
 * 初始化文件
 */
class InitController extends Controller
{

    use DispatchesJobs, ViewTrait;

    /**
     * 页面留言
     * @param string $message 信息
     * @return Factory|View
     */
    public function deny($message = '')
    {
        if (!$message) {
            $message = '您无权访问本页面';
        }
        return view('site::web.inc.deny', [
            'message' => $message,
        ]);
    }
}
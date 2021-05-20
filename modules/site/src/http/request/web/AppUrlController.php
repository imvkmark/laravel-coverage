<?php namespace Site\Http\Request\Web;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Resp;
use Site\Models\PluginCategory;
use Site\Models\PluginHelp;
use View;


/**
 * App 链接访问页面, 不支持微信小程序访问 用户封禁问题
 */
class AppUrlController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        Container::getInstance()->setExecutionContext('app');
    }

    /**
     *
     * @return Factory|\Illuminate\View\View
     */
    public function rule()
    {
        return view('site::web.app.rule');
    }

    public function rule_new()
    {
        return view('site::web.app.rule_new');
    }

    /**
     * 帮助页面
     * [id]     文章ID, 存在文章ID 的时候列表不可用
     * [page]   分页
     * [cat_id] 分类
     */
    public function help()
    {
        $id = input('id', 0);
        if ($id) {
            $item = PluginHelp::find($id);
            if (!$item) {
                return Resp::error('文章不存在');
            }
            View::share([
                'item'   => $item,
                '_title' => $item->help_title,
            ]);

            return view('site::web.app.help_show');
        }

        /* 按照分类分组展示
         * 如果当前分类, 有子分类,展示下级子分类
         * 如果没有, 展示文章列表
         * ---------------------------------------- */

        // 前台公告分类/问题帮助中心分类/首页顶部/活动帮助分类不显示
        $except_id = [
            ydl_setting('site.order_help_category') ?: 0,
            ydl_setting('site.order_question_category') ?: 0,
            ydl_setting('site.order_up_category') ?: 0,
            ydl_setting('site.activity_category') ?: 0,
        ];

        $except_id = array_filter($except_id);

        /** @var Builder $Db */
        $Db = PluginCategory::select(['id', 'cat_title', 'thumb'])
            ->where('is_enable', 1)
            ->where('parent_id', 0)
            ->whereNotIn('id', $except_id)
            ->orderBy('id');

        $Db->with('child:parent_id,id,cat_title')
            ->with('article:cat_id,id,help_title');

        $data = $Db->get()->toArray();

        View::share([
            'items'  => $data,
            '_title' => '帮助中心',
        ]);

        return view('site::web.app.help', [
            'page_type' => $id ? 'show' : 'list',
        ]);
    }
}

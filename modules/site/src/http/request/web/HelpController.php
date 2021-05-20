<?php namespace Site\Http\Request\Web;

use DB;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Poppy\Framework\Classes\Resp;
use User\Action\ActionAccount;
use Site\Models\PluginCategory;
use Site\Models\PluginHelp;
use Illuminate\Http\Request;
use View;

/**
 * 帮助中心
 */
class HelpController extends InitController
{

    public function __construct()
    {
        parent::__construct();
        $nums = PluginHelp::select(['cat_id', DB::raw('count(*) as total')])->groupBy('cat_id')->pluck('total', 'cat_id');

        $cat_id = request()->get('cat_id');
        $id     = request()->route('id');
        if (!$cat_id && $id) {
            $cat_id = PluginHelp::where('id', $id)->value('cat_id');
        }
        $cat_pid = PluginCategory::where('id', $cat_id)->value('parent_id');
        //公告中心的两个栏目
        $site_order_help_category = ydl_setting('site.order_help_category');
        $site_activity_category   = ydl_setting('site.activity_category');
        $proclamation             = [$site_order_help_category, $site_activity_category];
        if (in_array($cat_id, $proclamation) || in_array($cat_pid, $proclamation)) {
            //当前为公告中心的栏目
            $cats = PluginCategory::enable()->whereIn('id', $proclamation)->orWhereIn('parent_id', $proclamation)->select('cat_title', 'id', 'parent_id', 'parent_ids')->get()->toArray();
            $tree = $this->generateTree($cats, $cid = 'id', $pid = 'parent_id', $child = 'children');
            $cats = $tree;
            $curr = 'proclamation';
        }
        else {
            //当前为帮助中心
            $cats = PluginCategory::enable()->whereNotIn('id', $proclamation)->whereNotIn('parent_id', $proclamation)->select('cat_title', 'id')->get()->toArray();
            $curr = 'help';
        }


        View::share([
            'parent_id' => $cat_pid ?? '',
            'nums'      => $nums,
            'cats'      => $cats,
            'curr'      => $curr ?? '',
        ]);
        self::$permission = [
            'feedback' => 'backend:site.help.feedback',  //意见反馈
            'show'     => 'backend:site.help.show',      //文章页面
            'index'    => 'backend:site.help.index',     //文章列表
        ];
    }

    /**
     * 引用算法生成树型结构
     * @param array  $array 原始数组
     * @param string $cid   原始id字段名
     * @param string $pid   原始pid字段名
     * @param string $child 分配给子集的字段名
     * @return array
     */
    public function generateTree($array, $cid = 'cid', $pid = 'pid', $child = 'children')
    {
        //第一步 构造数据
        $items = [];
        foreach ($array as $value) {
            $items[$value["$cid"]] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = [];
        foreach ($items as $key => $value) {
            if (isset($items[$value["$pid"]])) {
                $items[$value["$pid"]]["$child"][] = &$items[$key];
            }
            else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    /**
     * 帮助列表
     * @param Request $request request
     * @return Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $cat_id = $request->input('cat_id');

        $Db = PluginHelp::whereRaw('1');
        if ($cat_id) {
            $Db->where('cat_id', $cat_id);
        }
        $items = $Db->orderBy('created_at', 'desc')
            ->paginate($this->pagesize)
            ->appends($request->input());

        View::share([
            'cat_id' => $cat_id,
            'action' => 'help',
            'items'  => $items,
        ]);

        return view('site::web.help.index');

    }

    /**
     * 查看详情
     * @param integer $id ID
     * @return Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        /** @var PluginHelp $item */
        $item      = PluginHelp::findOrFail($id);
        $parent_id = PluginCategory::where('id', $item->cat_id)->value('parent_id');
        View::share([
            'item'      => $item,
            'action'    => 'help',
            'cat_id'    => $item->cat_id,
            'parent_id' => $parent_id,
        ]);
        return view('site::web.help.show');
    }

    /**
     * 留言反馈
     * @param Request $request request
     * @return Factory|JsonResponse|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function feedback(Request $request)
    {
        if (!$this->owner) {
            return Resp::error('请先登录', 'location|' . route('user:web.user.login'));
        }

        if (is_post()) {
            $actAccount         = new ActionAccount();
            $data               = $request->all();
            $data['account_id'] = $this->accountId;
            if ($actAccount->feedback($data)) {
                return Resp::success('反馈成功', 'location|' . route('help.feedback'));
            }

            return Resp::error($actAccount->getError());
        }

        View::share('action', 'feedback');
        return view('site::web.help.feedback');
    }

    /**
     * 订单优质打手页面
     * @return Factory|\Illuminate\View\View
     */
    public function orderGreatTitle()
    {
        return view('site::web.help.order_great_title');
    }

    /**
     * 隐私政策
     * @return Factory|\Illuminate\View\View
     */
    public function privateRule()
    {
        return view('site::web.help.private');
    }

    /**
     * 服务条款
     * @return Factory|\Illuminate\View\View
     */
    public function rule()
    {
        return view('site::web.help.rule');
    }

    /**
     * 关于我们
     * @return Factory|\Illuminate\View\View
     */
    public function aboutUs()
    {
        return view('site::web.help.about_us');
    }

    /**
     * 联系我们
     * @return Factory|\Illuminate\View\View
     */
    public function contactUs()
    {
        return view('site::web.help.contact_us');
    }

    /**
     * app下载页面
     * @return Factory|\Illuminate\View\View
     */
    public function appDownload()
    {
        View::share([
            '_title' => 'App下载 - ' . site('site_name'),
        ]);
        return view('site::web.help.app_download');
    }

}

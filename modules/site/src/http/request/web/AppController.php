<?php namespace Site\Http\Request\Web;

use Exception;
use Hashids\Hashids;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Order\Models\DailianOrder;
use Poppy\Framework\Classes\Resp;
use Site\Models\PluginCategory;
use Site\Models\PluginHelp;
use User\Action\ActionUser;
use User\Models\AccountFront;
use View;


/**
 * App 链接访问页面, 不支持微信小程序访问
 */
class AppController extends InitController
{

    public function __construct()
    {
        parent::__construct();
        Container::getInstance()->setExecutionContext('app');
    }

    /**
     * app html
     * @return Factory|\Illuminate\View\View
     */
    public function appHtml()
    {
        $agent  = strtolower($_SERVER['HTTP_USER_AGENT']);
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $ipad   = (strpos($agent, 'ipad')) ? true : false;
        if ($ipad || $iphone) {
            $is_iphone = true;
        }
        else {
            $is_iphone = false;
        }

        View::share([
            'apk_url'   => 'http://a.app.qq.com/o/simple.jsp?pkgname=com.yidailian.elephant',
            'is_iphone' => $is_iphone,
            'h5_url'    => config('app.url_mobile'),
        ]);

        /* 这个地址是应用宝的下载地址
         -------------------------------------------- */
        return view('site::web.app.app_html');
    }

    /**
     * 邀请页面
     * @return Factory|\Illuminate\View\View|string
     */
    public function invite()
    {
        if (!$this->user) {
            return 'no access token passed';
        }
        if (!$this->user->invite_code) {
            $hashids    = new Hashids('dailian-invite', 6);
            $inviteCode = $hashids->encode($this->pam->account_id);
            $this->user->update([
                'invite_code' => $inviteCode,
            ]);
        }
        else {
            $inviteCode = $this->user->invite_code;
        }
        $url = route_url('site:web.home.invite', [$inviteCode]);

        $qrcodeUrl = route_url('site:web.support_util.qrcode', null, [
            't' => $url,
        ]);

        $items = AccountFront::where('invite_account_id', $this->user->account_id)->with('pam')
            ->paginate($this->pagesize);
        $items->appends(input());

        $bg = ydl_setting('invite.invite_bg');
        return view('site::web.app.invite', [
            'qrcode_url' => $qrcodeUrl,
            'items'      => $items,
            'url'        => $url,
            'bg'         => $bg,
        ]);
    }

    /**
     * 帮助页面
     * [id]     文章ID, 存在文章ID 的时候列表不可用
     * [page]   分页
     * [cat_id] 分类
     */
    public function help()
    {
        $id = input('id');
        if ($id) {
            $item = PluginHelp::find($id);
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

    /**
     * 更多帮助
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function helpMore()
    {
        $cat_id = sys_get(input(), 'cat_id', 0);
        if (!$cat_id) {
            return Resp::error('请选择分类');
        }

        $type = sys_get(input(), 'type', 'article');

        $items = [];
        /* article, 展示文章列表, cate: 按照分类分组,展示下面的文章
         * ---------------------------------------- */
        if ($type === 'article') {
            $data = PluginHelp::orderBy('id', 'desc')->where('cat_id', $cat_id)->get()->toArray();
            foreach ($data as $value) {
                $items[] = [
                    'id'    => $value['id'],
                    'title' => $value['help_title'],
                ];
            }
        }
        else {
            $data = PluginCategory::where('parent_id', $cat_id)
                ->select(['id'])
                ->with('article:cat_id,id,help_title')->get()->toArray();

            foreach ($data as $cate) {
                if (!isset($cate['article'])) {
                    continue;
                }
                foreach ($cate['article'] as $article) {
                    $items[$cate['id']][] = [
                        'id'    => $article['id'] ?? 0,
                        'title' => $article['help_title'] ?? '',
                    ];
                }
            }
        }

        View::share([
            'items'  => $items,
            '_title' => '帮助中心',
            'type'   => $type,
            'cat_id' => $cat_id,
        ]);

        return view('site::web.app.help_more');
    }


    /**
     * 关于我们
     * @return Factory|\Illuminate\View\View
     */
    public function aboutUs()
    {
        return view('site::web.app.about_us');
    }

    /**
     * 关于我们
     * @return Factory|\Illuminate\View\View
     */
    public function wxWithdraw()
    {
        return view('site::web.app.wx_withdraw');
    }

    /**
     * 规则
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
     * 活动规则
     * @return Factory|\Illuminate\View\View
     */
    public function activityRule()
    {
        return view('site::web.app.activity_rule');
    }

    /**
     * 活动规则
     * @param $type
     * @return Factory|\Illuminate\View\View
     */
    public function activityImg($type)
    {
        return view('site::web.app.activity_img', [
            'type' => $type,
        ]);
    }

    /**
     * 分享
     * @param string $key   key
     * @param null   $value value
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function share($key, $value = null)
    {
        try {
            $orderId = (new Hashids(config('app.key')))->decode($key);
            $orderNo = DailianOrder::where('id', $orderId)->value('order_no');
            return redirect(config('app.url_mobile') . '/order/' . $orderNo);
        } catch (Exception $e) {
            return Resp::error('无法解析指定链接');
        }
    }

    /**
     * 同意
     * @return Factory|\Illuminate\View\View
     */
    public function transfer()
    {
        View::share([
            'account_name' => $this->pam ? $this->pam->account_name : '',
        ]);
        return view('site::web.app.transfer');
    }


    /**
     * 芝麻实名认证跳转回来的界面
     * 'biz_content' => '{"biz_no":"ZM201902233000000808000332694843","passed":"true"}',
     * 'sign' => 'IQP7/1D280qCnDo/a0VTJYaAV2qYTycGbvcQ=='
     * @param string $platform 平台
     * @param string $hash_id
     * @return array|JsonResponse|RedirectResponse|Response|Redirector
     */
    public function zhima($platform = 'h5', $hash_id)
    {
        $hashId = (new Hashids('ZhimaUserVerify'))->decode($hash_id);
        if (!$hashId) {
            \Log::error('芝麻实名解析用户ID错误:' . $hash_id . '平台' . $platform);
            return Resp::error('参数错误，请联系客服');
        }
        $User = new ActionUser();
        /** @var AccountFront $UserInfo */
        $UserInfo = $User->getUserZhimaInfo($hashId[0]);
        if ($User->verifyZhima($UserInfo)) {
            if ($platform === 'h5') {
                $buttonUrl  = config('app.url_mobile') . '/personal_data';
                $buttonText = '返回 H5';
            }
            elseif ($platform === 'android') {
                $buttonUrl  = 'ydl://user/chid_verify';
                $buttonText = '返回 App';
            }
            else {
                $buttonUrl  = '#';
                $buttonText = '返回应用继续操作';
            }
            View::share([
                'user'        => $User->getUser(),
                'button_url'  => $buttonUrl,
                'button_text' => $buttonText,
            ]);
            return Resp::success('认证成功');
        }
        return Resp::error('支付宝校验失败');
    }

}

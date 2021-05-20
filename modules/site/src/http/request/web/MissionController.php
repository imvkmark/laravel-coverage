<?php namespace Site\Http\Request\Web;

use Carbon\Carbon;
use Finance\Models\FinanceRebate;
use Finance\Models\RewardRecord;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Poppy\Core\Redis\RdsStore;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\TimeHelper;
use Request;
use Site\Action\ActionMission;
use Site\Models\FrontActivity;
use Site\Models\PluginHelp;
use Throwable;
use View;

/**
 * 前台活动页面
 * Class ActivityController
 */
class MissionController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('pam_front.auth', [
            'except' => [
                'index',
                'display',
                'application',
                'show',
            ],
        ]);
    }

    /**
     * 活动公告列表
     * @return Factory|\Illuminate\View\View
     */
    public function index()
    {
        //活动公告
        $activity = FrontActivity::orderBy('created_at', 'desc')->where('is_open', 1);
        $group    = input('group');
        if ($group) {
            switch ($group) {
                case 'all':
                    break;
                case 'ing':
                    $activity->where('start_at', '<=', Carbon::now())->where('end_at', '>=', Carbon::now());
                    break;
                case 'over':
                    $activity->where('end_at', '<', Carbon::now());
                    break;
                /*case 'long':
                    $activity->where('group', FrontActivity::GROUP_USER_FIRST);
                    break;*/
            }
        }
        $data = $activity->paginate(8)->appends(Request::all());

        return view('site::web.activity.index', [
            'activity' => $data,
        ]);
    }

    /**
     * 活动返利
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function application()
    {
        $group   = Request::input('group');
        $game_id = sys_get(input(), 'game', '');

        $activityDb = FrontActivity::where('group', $group)
            ->where('is_open', '1');
        if ($game_id) {
            $activityDb->where('game_id', $game_id);
        }

        $type = $activityDb->pluck('title', 'id')->toArray();

        if (!$this->owner) {
            return Resp::error('请先登录', 'location|' . route('user:web.user.login'));
        }

        //可以申请的列表
        switch ($group) {
            case 'have':
                $Db = FinanceRebate::where('can_apply', '1')
                    ->where('sd_account_id', $this->owner->account_id)
                    ->with('activity')
                    ->with('order')
                    ->orderBy('over_at', 'desc');
                break;
            default:
                $Db = FinanceRebate::where('can_apply', '1')
                    ->where('sd_account_id', $this->owner->account_id)
                    ->where('is_rebate', 0)
                    ->where('activity_type', $group)
                    ->with('activity')
                    ->with('order')
                    ->orderBy('over_at', 'desc');
                break;
        }
        /*$Db = FinanceRebate::where('can_apply', '1')
            ->where('sd_account_id', $this->owner->account_id)
            ->with('activity')
            ->with('order')
            ->orderBy('over_at', 'desc');*/


        $start_time = input('start_date');
        if ($start_time) {
            $Db->where('created_at', '>=', TimeHelper::dayStart($start_time));
        }
        $end_time = input('end_date');
        if ($end_time) {
            $Db->where('created_at', '<=', TimeHelper::dayEnd($end_time));
        }
        $activity_id = input('activity_id');
        if ($activity_id) {
            $Db->where('activity_id', $activity_id);
        }
        $have = input('have');
        if ($have) {
            $Db->where('is_rebate', 1);
        }
        $limit_time = input('limit_time');
        if ($limit_time) {
            $Db->where('is_rebate', '0');
        }

        $kw = trim(input('kw'));
        if ($kw) {
            $Db->where(function (Builder $q) use ($kw) {
                $q->orWhere('order_no', 'like', '%' . $kw . '%');
            });
        }
        $items = $Db->paginate($this->pagesize)
            ->appends(Request::all());

        View::share('items', $items);


        //获取活动帮助列表
        $article = PluginHelp::find(site('activity_category'));
        View::share([
            'type'     => $type,
            'articles' => $article ? $article->content : '',
        ]);

        return view('site::web.activity.application');
    }

    /**
     * 申请
     * @param integer $id ID
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function apply($id)
    {
        $activity = new ActionMission();
        $activity->setUser($this->owner);
        if ($activity->apply($id)) {
            return Resp::success('申请成功,请等待工作人员发放', '_reload|1');
        }

        return Resp::error($activity->getError());
    }

    /**
     * 活动详情
     * @param string $type 类型
     * @return Factory|JsonResponse|RedirectResponse|Response|Redirector|\Illuminate\View\View
     */
    public function show($type)
    {
        if ($type !== 'prize_summer') {
            return Resp::error('类型不正确, 非法访问');
        }

        $method = Str::camel($type);
        if (is_callable([$this, $method])) {
            if (is_post()) {
                return $this->$method();
            }
            $this->$method();
        }

        return view('site::web.activity.show.' . $type);
    }

    /**
     * prize summer
     * @return JsonResponse|RedirectResponse|Response|Redirector
     * @throws Throwable
     */
    private function prizeSummer()
    {
        $prize   = 0;
        $isClose = !ydl_setting('prize.summer_is_open');
        if (!$isClose) {
            $isIng = ydl_setting('prize.summer_start_datetime') <= Carbon::now() && Carbon::now() <= ydl_setting('prize.summer_end_datetime');
        }
        else {
            $isIng = true;
        }

        //	查询用户可抽数量
        if (is_post()) {
            //判断有无登录
            if (!$this->user) {
                return Resp::error('请先登录后再进行操作');
            }

            if ($isClose || !$isIng) {
                return Resp::error('活动已关闭或者不在活动范围内, 无法进行抽奖!');
            }
            // 判断今天抽了多少
            $num = RewardRecord::where('account_id', $this->ownerId)
                ->where('created_at', '>=', Carbon::now()->startOfDay())
                ->count();
            //判断是否在活动时间内
            //订单是否是在活动期间接的
            if (
                Carbon::now() < ydl_setting('prize.summer_start_datetime')
                ||
                Carbon::now() > ydl_setting('prize.summer_end_datetime')
            ) {
                return Resp::error('已超出活动期限');
            }

            $max_num = ydl_setting('prize.summer_times_per_day') ?: 0;
            if ($max_num && $num > $max_num) {
                return Resp::error('每天最高可以抽' . $max_num . '次');
            }
            //去抽奖

            if (RdsStore::inLock('user_prize_' . $this->pam->account_id, 5)) {
                return Resp::error('操作频繁, 请5秒后重试');
            }

            $activity = new ActionMission();
            $activity->setFront($this->front);
            if (!$activity->pumping()) {
                return Resp::error($activity->getError());
            }

            // 获取抽取的奖励
            $item = $activity->getPrize();

            return Resp::success('抽取到奖品', [
                'rotate' => $item['rotate'],
                'desc'   => '恭喜您抽中了' . $item['title'],
            ]);

        }


        $items = RewardRecord::orderBy('created_at', 'desc')
            ->with('pam')->take(30)->get();

        $data = [];
        foreach ($items as $item) {
            $account_name = mb_substr($item->pam->account_name, 0, 2) . '*****' . mb_substr($item->pam->account_name, -2);
            $data[]       = [
                'account_name' => $account_name ?: '******',
                'award_type'   => RewardRecord::kvType($item->award_type)['title'],
                'created_at'   => substr($item->created_at, 5),
            ];
        }

        View::share([
                'start_at' => substr(ydl_setting('prize.summer_start_datetime'), 2, -3),
                'end_at'   => substr(ydl_setting('prize.summer_end_datetime'), 2, -3),
                'items'    => $data,
                'prize'    => $prize,
                'is_close' => $isClose,
                'is_over'  => !$isIng,
            ]
        );
    }

    /**
     * 游戏活动
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function gameActivity()
    {
        $game_id = sys_get(input(), 'game_id', 0);
        $group   = sys_get(input(), 'group', '');
        if ($game_id) {
            $list = FrontActivity::getOpenActivity($game_id, $group);
            return Resp::success('', $list);
        }
    }

}

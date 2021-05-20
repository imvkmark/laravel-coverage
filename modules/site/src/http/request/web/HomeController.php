<?php namespace Site\Http\Request\Web;

use Cache;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Order\Action\ActionPublisher;
use Order\Models\DailianOrder;
use Order\Models\GameDan;
use Order\Models\GameName;
use Order\Models\GameServer;
use Poppy\Framework\Classes\Resp;
use Response;
use Site\Action\ActGateway;
use Site\Models\FrontActivity;
use Site\Models\PluginHelp;
use Site\Classes\Hash;
use User\Action\ActionSpread;
use User\Models\AccountFront;
use View;

/**
 * 主页
 */
class HomeController extends InitController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('pam_front.auth', [
            'except' => [
                'homePage',
                'index',
                'test',
                'showActivity',
                'invite',
                'js',
                'getDanType',
                'getGameServerListByAreaId',
                'calculate',
                'countUp',
            ],
        ]);
    }


    /**
     * 用户分页
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function test()
    {

        $gateWay = new ActGateway();

        $value  = [
            'status'  => 1,
            'message' => 'haha',
            'test'    => '123',
        ];
        $result = $gateWay->send('10009', $value);
        var_dump($result);
    }

    /**
     * 用户分页
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function homepage()
    {

        if (is_mobile()) {
            return redirect(route('site:web.app.app'));
        }

        //公告滚动
        $notice = PluginHelp::cat(site('order_up_category'))->orderBy('list_order', 'asc')->take(5)->get();

        //获取王者荣耀区服列表
        $wzGameServerList = $this->getGameServerList(2);
        //获取英雄联盟区服信息, lol区服信息需要二级联动
        //获取所有区
        $lolAreas = GameServer::gameAreas(1);
        //dump($lolAreas);
        //代练类型
        $lolTypes = GameDan::kvDansType();
        $wzTypes  = [
            GameDan::DAN_TYPE_RANK => GameDan::kvDansType(GameDan::DAN_TYPE_RANK),
        ];
        //dump($lolTypes);
        //dump($wzTypes);

        //获取英雄联盟段位信息
        //$lolDanType = $this->getDanType($lolTypes, 'lol', 1, 'upgrade');
        //获取王者荣耀段位信息
        //$wzDanType = $this->getDanType($wzTypes, 'wz', 2);


        $imgs = [];
        $urls = [];
        if (site('spread_index_ad')) {
            $images = ad_images(site('spread_index_ad'));

            if ($images->count()) {
                foreach ($images as $img) {
                    $urls[] = $img['image_src'];
                    $imgs[] = [
                        'url'  => $img['image_src'],
                        'alt'  => $img['image_alt'],
                        'link' => $img['image_url'],
                    ];
                }
            }
        }
        View::share([
            'images'           => $imgs,
            'urls'             => json_encode($urls),
            'notices'          => $notice,
            'wzGameServerList' => $wzGameServerList,
            'lolAreas'         => $lolAreas,
            'lolTypes'         => $lolTypes,
        ]);

        return view('site::web.home.home');
    }


    /**
     * 展示活动
     * @param integer $id ID
     * @return JsonResponse|RedirectResponse|\Illuminate\Http\Response|Redirector
     */
    public function showActivity($id)
    {
        $id   = (new Hash())->decode($id)[0];
        $data = FrontActivity::find($id);
        if (!$data) {
            return Resp::error('找不到此活動');
        }
        if ($data->is_link === 'Y') {
            return redirect($data->link_url);
        }
        return Response::make($data->html);
    }

    /**
     * 处理跳转
     * @param string $inviteCode 邀请码
     * @return RedirectResponse|Redirector
     */
    public function invite($inviteCode)
    {
        if (is_mobile()) {
            $url = config('app.url_mobile') . '/login?invite=' . $inviteCode;
        }
        else {
            $url = route_url('user:web.user.register', null, ['invite' => $inviteCode]);
        }

        $this->linkClickStatistics($inviteCode);
        return redirect($url);
    }

    /**
     * 推广链接点击
     * @param string $invite_code 推广码
     */
    public function linkClickStatistics($invite_code): void
    {
        if (!$invite_code) {
            return;
        }

        if (!$user = AccountFront::where('invite_code', $invite_code)->first()) {
            return;
        }

        $Statistics = new ActionSpread();
        $Statistics->linkClickStatistics($user);
    }


    /**
     * 主控制面板
     * @return Application|Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function cp()
    {
        return view('site::web.home.cp');
    }

    /**
     * 通过游戏区id获取游戏服列表
     * @param $request
     * @return mixed
     */
    public function getGameServerListByAreaId(Request $request)
    {

        if ($request->ajax()) {
            $areaId         = $request->get('areaId');
            $gameServerList = GameServer::where('is_enable', 'Y')->where('parent_id', $areaId)->get();
            return $gameServerList;
        }

    }

    /**
     * 根据代练类型获取段位信息
     * @return array
     */
    public function getDanType()
    {
        $input    = input();
        $game     = sys_get($input, 'game');
        $game_id  = (int) sys_get($input, 'game_id');
        $dan_type = sys_get($input, 'dan_type');

        if (!$game || !$game_id) {
            return Resp::error('请先填写游戏');
        }

        $names = GameName::kvLinear();
        if (!array_key_exists($game_id, $names)) {
            return Resp::error('请选择支持的游戏');
        }
        if (!in_array($game, [GameName::NAME_WZ, GameName::NAME_LOL], true)) {
            return Resp::error('请选择支持的游戏');
        }
        $names = array_flip($names);
        if ($game === GameName::NAME_WZ && $names['王者农药'] !== $game_id) {
            return Resp::error('请选择支持的游戏');
        }
        if ($game === GameName::NAME_LOL && $names['撸啊撸'] !== $game_id) {
            return Resp::error('请选择支持的游戏');
        }

        //$types 全部代练类型：排位(rank) ..
        $types     = GameDan::kvDansType();
        $dan_types = [];
        /* 段位
         * ---------------------------------------- */
        foreach ($types as $type => $title) {
            $data = $this->dans($game, $type, $game_id);
            $dans = [
                'type'  => $type,
                'title' => $title,
                'dans'  => $data['dans'] ?? [],
            ];

            if (isset($data['num'])) {
                $dans['nums'] = $data['num'] ?? [];
            }
            if (isset($data['end_dans'])) {
                $dans['end_dans'] = $data['end_dans'] ?? [];
            }
            $dan_types[] = $dans;
        }
        foreach ($dan_types as $type) {
            if ($dan_type && $type['type'] === $dan_type) {
                return $type;
            }
        }
        return $dan_types;
    }

    public function calculate()
    {
        $Publisher = (new ActionPublisher())->setFront($this->front);
        if (!$Publisher->calculate(input())) {
            return Resp::error($Publisher->getError());
        }
        return Resp::success('计算成功', $Publisher->getPriceInfo());
    }

    /**
     * 首页 已为XXX人提供服务
     */
    public function countUp()
    {
        $minutes = 10;  //缓存10分钟
        if (Cache::has('order_last_id')) {
            return Cache::get('order_last_id');
        }
        $lastId = (new DailianOrder())->orderBy('id', 'desc')->value('id');
        Cache::put('order_last_id', $lastId, $minutes * 60);
        return $lastId;
    }

    //首页计算价格

    /**
     * 根据游戏id获取游戏区服列表信息
     * @param $game_id
     * @return mixed
     */
    protected function getGameServerList($game_id)
    {
        $areas   = GameServer::gameAreas($game_id);
        $servers = collect(GameServer::getAllChildServer($game_id))->map(function ($item) use ($areas) {
            if (isset($areas[$item['parent_id']])) {
                $area_title = ($areas[$item['parent_id']]['server_title'] ?? '') . '-';
            }
            return [
                'title' => ($area_title ?? '') . $item['server_title'],
                'code'  => $item['server_code'],
            ];
        })->values()->toArray();
        return $servers;
    }

    /**
     * 获取段位
     * @param string $game    游戏
     * @param string $type    类型
     * @param int    $game_id 游戏id
     * @return array
     */
    private function dans($game, $type, $game_id): array
    {
        if (!array_key_exists($type, GameDan::kvDansType())) {
            return [];
        }

        switch ($type) {
            /* 排位赛
            * ---------------------------------------- */
            // wz只有排位赛
            case GameDan::DAN_TYPE_RANK:
                /* wz
                 * ---------------------------------------- */
                if ($game === GameName::NAME_WZ) {
                    $star_func = function ($id) {
                        $star = [];
                        if (Gamedan::isMaster($id)) {
                            for ($i = 0; $i <= 1000; $i++) {
                                $star[] = [
                                    'id'    => $id . '-' . $i,
                                    'title' => $i . '星',
                                ];
                            }
                        }
                        else {
                            $count = GameDan::danStarCount($id);
                            for ($i = 0; $i <= $count; $i++) {
                                $star[] = [
                                    'id'    => $id . '-' . $i,   // 与rank的子段位key保持一致
                                    'title' => $i . '星',
                                ];
                            }
                        }
                        return $star;
                    };

                    $star_end_func = function ($id) {
                        if (Gamedan::isMaster($id)) {
                            for ($i = 1; $i <= 1000; $i++) {
                                $star[] = [
                                    'id'    => $id . '-' . $i,
                                    'title' => $i . '星',
                                ];
                            }
                        }
                        else {
                            $count = GameDan::danStarCount($id);
                            for ($i = 1; $i <= $count; $i++) {
                                $star[] = [
                                    'id'    => $id . '-' . $i,   // 与rank的子段位key保持一致
                                    'title' => $i . '星',
                                ];
                            }
                        }
                        return $star;
                    };

                    $data['dans'] = collect(GameDan::gameChildDans($game_id))->sortBy('level')
                        ->map(function ($item) use ($star_func) {
                            $dan = [
                                'id'    => (string) $item['id'],
                                'title' => $item['title'],
                                'child' => $star_func($item['id']),
                            ];
                            unset($item['id']);
                            return $dan;
                        })->toArray();

                    $data['end_dans'] = collect(GameDan::gameChildDans($game_id))->sortBy('level')
                        ->map(function ($item) use ($star_end_func) {
                            $dan = [
                                'id'    => (string) $item['id'],
                                'title' => $item['title'],
                                'child' => $star_end_func($item['id']),
                            ];
                            unset($item['id']);
                            return $dan;
                        })->toArray();
                }
                /* lol
                 * ---------------------------------------- */
                if ($game === GameName::NAME_LOL) {
                    $data['dans'] = collect(GameDan::gameGroupDans($game_id))->filter(function ($item) {
                        return !Str::contains($item['title'], '新号');
                    })->values()
                        ->map(function ($item) {
                            $childs = [];
                            foreach ($item['child'] as $child) {
                                $title    = (string) GameDan::title2Num($child['title']);
                                $childs[] = [
                                    'id'    => (string) $child['id'],
                                    'title' => !GameDan::isMaster($child['id']) ? $title : '请在当前胜点处输入胜点',
                                ];
                            }

                            $item['child'] = $childs;
                            unset($item['id']);
                            return $item;
                        })->toArray();

                    /* 大师段位胜点
                     * ---------------------------------------- */
                    $master_points = function ($id) {
                        $count = ceil(GameDan::POINT_MAX / 10);
                        for ($i = 0; $i <= $count; $i++) {
                            $childs[] = [
                                'id'    => $id . '-' . $i * 10,
                                'title' => (string) ($i * 10),
                            ];
                        }
                        return $childs;
                    };

                    $data['end_dans'] = collect(GameDan::gameGroupDans($game_id))->filter(function ($item) {
                        return !Str::contains($item['title'], '新号');
                    })->values()->map(function ($item) use ($master_points) {
                        $childs = [];

                        foreach ($item['child'] as $child) {
                            if (!GameDan::isMaster($item['id'])) {
                                $title    = GameDan::title2Num($child['title']);
                                $childs[] = [
                                    'id'    => (string) $child['id'],
                                    'title' => (string) $title,
                                ];
                            }
                            else {
                                $childs = $master_points($child['id']);
                            }
                        }

                        $item['child'] = $childs;
                        unset($item['id']);
                        return $item;
                    })->toArray();
                }

                break;

            /* 定位赛
            * ---------------------------------------- */
            case GameDan::DAN_TYPE_POSITION:
                if ($game !== GameName::NAME_LOL) {
                    return [];
                }

                $data['dans'] = collect(GameDan::gameParentDans($game_id))->sortBy('level')
                    ->map(function ($item) {
                        $dan = [
                            'id'    => $item['id'],
                            'title' => $item['title'],
                        ];
                        return $dan;
                    })->values()->toArray();

                // 需要打的场次
                for ($i = 1; $i <= 10; $i++) {
                    $num[] = [
                        'num'   => $i,
                        'title' => $i . '场',
                    ];
                }
                $data['num'] = $num;

                break;
            default:
                /* 晋级赛
                * ---------------------------------------- */
            case GameDan::DAN_TYPE_UPGRADE:
                if ($game !== GameName::NAME_LOL) {
                    return [];
                }

                // 场次
                for ($i = 0; $i <= 2; $i++) {
                    $num[] = [
                        'num'   => $i,
                        'title' => $i . '场',
                    ];
                }

                $dans = collect(GameDan::gameGroupDans($game_id))->filter(function ($item) {
                    $regex = '/(新号|大师|宗师|王者)/u';
                    return !preg_match($regex, $item['title'], $match);
                })->values()
                    ->map(function ($item) use ($num) {

                        $childs = [];
                        foreach ($item['child'] as $child) {
                            $title    = GameDan::title2Num($child['title']);
                            $childs[] = [
                                'id'    => $child['id'],
                                'title' => $title,
                                'num'   => $title <= 1 ? array_slice($num, 0, 2) : $num,
                            ];
                        }

                        $item['child'] = $childs;
                        unset($item['id']);
                        return $item;
                    })->toArray();

                $data['dans'] = $dans;
                break;
        }
        return $data;
    }

}

<?php namespace Site\Http\Request\Web;

use Cache;
use Carbon\Carbon;
use Finance\Action\ActionCash;
use GatewayClient\Gateway;
use Hashids\Hashids;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;
use Order\Action\ActionPublisher;
use Order\Classes\Front;
use Order\Classes\PriceFactory;
use Order\Models\DailianOrder;
use Order\Models\GameDan;
use Order\Models\GameName;
use Poppy\AliyunPush\Classes\AliPush;
use Poppy\Framework\Classes\Number;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\StrHelper;
use Poppy\System\Classes\Contracts\UploadContract;
use Poppy\System\Classes\Uploader\DefaultUploadProvider;
use Site\Classes\SiteDef;
use Site\Models\PamAccount;
use Site\Models\PluginHelp;
use User\Action\ActionApprove;
use User\Events\ZhimaApproveEvent;
use User\Models\AccountFront;
use User\Models\AppInit;
use User\Models\GoldenHunter;
use User\Models\GoldenStatistics;

/**
 * 主页
 */
class TestController extends InitController
{
    /**
     * 实名验证
     */
    public function index()
    {
        /* 通知用户
         * ---------------------------------------- */
        $Cash = new ActionCash();

        $pam    = PamAccount::find(10375);
        $amount = 2.00;

        $Cash->setFront(Front::instance()->init($pam));
        if (!$Cash->create(1179, $amount, $pam, '123456', 'plain')) {
            return Resp::error($Cash->getError());
        }

        return Resp::success('success');
    }

    public function closeClient()
    {
        $clientIds = AppInit::select('client_id')
            ->where('client_id', '!=', '')
            ->get()
            ->pluck('client_id')
            ->toArray();

        if (!$clientIds) {
            die('无在线用户');
        }
        foreach ($clientIds as $clientId) {
            Gateway::closeClient($clientId);
        }
        die('执行完成');
    }

    /**
     * 清除猎手
     */
    public function clearHunter()
    {
        $ruleOverNum  = new Number(ydl_setting('beater_rule.golden_over_num') ?: 0);
        $ruleOverRate = new Number(ydl_setting('beater_rule.golden_over_rate') ?: 0);
        $hunters      = GoldenHunter::where('status', GoldenHunter::STATUS_SUCCESS)->get();
        $pam          = PamAccount::where('account_type', PamAccount::TYPE_DESKTOP)->first();
        $ActApprove   = (new ActionApprove())->setPam($pam);
        foreach ($hunters as $hunter) {
            /** @var GoldenStatistics $statics */
            $statics      = GoldenStatistics::where('type', 'month')->where('account_id', $hunter->account_id)->first();
            $trueOverNum  = new Number($statics->over_num ?? 0);
            $trueOverRate = new Number($statics->over_rate ?? 0);
            $input        = [
                'reason' => '[金牌打手自动清除]不满足每月规定完单数或完单率后台自动清除',
                'money'  => 0,
            ];
            if ($ruleOverNum->subtract($trueOverNum)->isGreaterThan(0) || $ruleOverRate->subtract($trueOverRate)->isGreaterThan(0)) {
                if (!$ActApprove->quit($hunter->id, $input)) {

                }
            }
        }
    }

    /**
     * socket-21
     * @return Factory|View
     */
    public function socket($uid = 0)
    {
        $input        = input();
        $token        = AppInit::where('account_id', $uid)->orderBy('created_at', 'desc')->first();
        $access_token = AppInit::where('access_token', $input['access_token'])->first();
        dump($token, $access_token);
        return view('site::web.test.index', [
            'token' => $token->access_token,
        ]);
    }

    /**
     * 缓存名称
     * @return string
     */
    private function cacheName()
    {
        return SiteDef::ckTestOrderTitle();
    }

    /**
     * 解析段位
     */
    public function parseOrderTitle()
    {
        $title_list = include 'title.php';


        $cache_data = Cache::get($this->cacheName());
        $time       = $cache_data['time'] ?? 1;
        $list       = collect($title_list)->forPage($time - 1, 200);

        $count_title = count($title_list);

        // dump($count_title);
        $Pub    = new ActionPublisher();
        $count  = 0;
        $result = [];
        foreach ($list as $title_info) {
            $game  = $title_info[0];
            $title = $title_info[1] ?? '';
            if (!$title) {
                continue;
            }

            if (Str::contains($game, '王者农药')) {
                $game_name = 'wz';
                $game_id   = 2;
            }
            elseif (Str::contains($game, '撸啊撸')) {
                $game_name = 'lol';
                $game_id   = 1;
            }
            else {
                continue;
            }

            $game_name = 'lol';
            $game_id   = 1;

            $title = '定位赛   钻石段   单双排';
            dump($title);
            if ($Price = PriceFactory::make($title)) {

                // dump($Price);
                $Price->parse();
                $data = $Price->data();

                dump($data);
                if ($data['type'] === GameDan::DAN_TYPE_UPGRADE) {
                    $next_dan_key = $Price->nextDanKey($game_name, ($data['start'] ?? '') . '_' . ($data['start_num'] ?? 0));
                }
                $price = $Pub->calcPrice($game_id, $data, $next_dan_key ?? []);


                if ((new Number($price))->isLessThanOrEqualTo(0.00)) {
                    // dump($title);
                }
                else {
                    ++$count;
                    dump($price);
                }

                $Price = new Number($price ?? 0);
                if ($Price->isLessThanOrEqualTo(0)) {
                    $diff_price = 0;
                }
                else {
                    $diff_price = (new Number($title_info[2] ?? '0'))->subtract($price)->getValue();
                }

                $result[] = [
                    'order_title' => $title,
                    'price'       => $title_info[2] ?? 0,
                    'diff_price'  => $diff_price,
                    'reference'   => $price ?? 0,
                ];
            }
            else {
                // dump($title);
            }
            die;
        }

        Cache::forget($this->cacheName());
        $cache_data = Cache::remember($this->cacheName(), 30 * 60, function () use ($time, $result, $cache_data) {

            $import      = $cache_data['import'] ?? [];
            $import_data = array_merge($import, $result);

            return [
                'time'   => ++$time,
                'import' => $import_data,
            ];
        });

        if ($time == 19) {
            $result = $cache_data['import'] ?? [];
            // return (new TitleExport(collect($result)))->download('订单标题.xlsx');
        }
        else {
            echo 'total:' . $count_title . ';';
            echo 'time:' . $time . ';';

            return redirect('test/parse_order');
        }
        die;

    }


    /**
     * 二维码
     * @return ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function qrcode()
    {
        $names = GameName::getCache();
        $games = [];
        foreach ($names as $id => $name) {
            $games[$name->over_hour][] = $id;
        }
        dd($games);
        // \Artisan::call('order:dailian-over_new_golden_order');
        dd(round(2, 2));
        $result = '2019-03-15 14:56:21' < '2019-03-15 14:30:08' ? true : false;
        dd($result);
        $games = GameName::whereIn('game_name', ['撸啊撸', '王者农药'])->get();

        $lolId = 0;
        $wzId  = 0;
        $lol   = $games->where('game_name', '撸啊撸')->first();
        if ($lol) {
            $lolId = $lol->id;
        }
        $wz = $games->where('game_name', '王者农药')->first();
        if ($wz) {
            $wzId = $wz->id;
        }

        $order = DailianOrder::where('order_status', DailianOrder::ORDER_STATUS_EXAMINE)
            ->where(function ($query) use ($wz, $lol) {
                $goldenIds = GoldenHunter::where('status', 'pass')->pluck('id');
                if ($goldenIds->count()) {
                    if ($lol) {
                        $lolHour = round($lol->golden_over_hour, 2);
                        if ($lolHour) {
                            $query->orWhere(function ($query) use ($goldenIds, $lolHour, $lol) {
                                $minutes     = $lolHour * 60;
                                $orderOverAt = Carbon::now()->subMinutes($minutes);
                                //可以急速验收
                                $query->where('overed_at', '<', $orderOverAt);
                                $query->where('game_id', $lol->id);

                                //接单者是金牌打手
                                $query->whereIn('sd_account_id', $goldenIds);
                            });
                        }
                    }

                    if ($wz) {
                        $wzHour = round($wz->golden_over_hour, 2);
                        if ($wzHour) {
                            $query->orWhere(function ($query) use ($goldenIds, $wzHour, $wz) {
                                $minutes     = $wzHour * 60;
                                $orderOverAt = Carbon::now()->subMinutes($minutes);
                                //可以急速验收
                                $query->where('overed_at', '<', $orderOverAt);
                                $query->where('game_id', $wz->id);

                                //接单者是金牌打手
                                $query->whereIn('sd_account_id', $goldenIds);
                            });
                        }
                    }
                }
            })->with('pam')
            ->get();
        dump($order);
        // die;
        if ($order->count()) {
            dump(Carbon::now() . ' [Cron-over order] current over ' . $order->count() . ' num');
            foreach ($order as $od) {

                if (!$od->pam) {
                    dump(Carbon::now() . ' [Cron-over order] no pam ! order_no : ' . $od->order_no);
                    continue;
                }
                $goldenAt = null;
                if (in_array($od->game_id, [$lolId, $wzId])) {
                    // $goldenAt = $od->golden_hunter->success_at;
                }

                //成为金牌打手之前的订单不能急速验收
                if ($od->assigned_at < $goldenAt) {
                    continue;
                }

                // 初始化新用户
                $front     = (new Front())->init($od->pam);
                $Publisher = (new ActionPublisher())->setFront($front)->setOrder($od);
                // 取消支付密码校验
                $Publisher->setCancelPwdVerify(true);
                if (!$Publisher->over('password verify', 'plain', true)) {
                    $this->error(
                        Carbon::now() . ' [Cron-over order] error : ' . $Publisher->getError() .
                        ', account_id : ' . $front->getOwner()->account_id .
                        ', order_no : ' . $Publisher->getOrder()->order_no
                    );
                }
                else {
                    dump(Carbon::now() . ' [Cron-over order] over ! order_no : ' . $od->order_no);
                }
            }
        }

        die;
        $directory = 'tuiguang/BQcACQsIVlQGDg';
        $file      = $directory . '/style_0.jpg';
        $file      = 'q0aw3vnvixv.png';


        $image = image2base64($file);
        $index = strpos($image, ',');
        $image = substr_replace($image, 0, $index + 1);


        /** @var DefaultUploadProvider $Image */
        $Image = app(UploadContract::class);

        $Image->setDestination($file);


        $content = base64_decode($image);

        if ($Image->saveInput($content)) {
            dd($Image->getUrl());
        }

        dd($Image->getUrl());
        dd($Image->getError());


        $spread_words = ydl_setting('spread.spread_words') ?: '';

        $spread_words = explode("\r\n", $spread_words);

        $user = AccountFront::find(10334);
        if (!$user->invite_code) {
            $hashids    = new Hashids('dailian-invite', 6);
            $inviteCode = $hashids->encode($this->pam->account_id);
            $user->update([
                'invite_code' => $inviteCode,
            ]);
        }
        else {
            $inviteCode = $user->invite_code;
        }
        $url = route_url('site:web.home.invite', [$inviteCode]);

        $spread_words = array_map(function ($words) use ($url) {
            return str_replace('{$link}', $url, $words);
        }, $spread_words);


        $pictures = ydl_setting('spread.spread_pictures') ?? '';

        if (strpos($pictures, 'array_') === 0) {
            $pictures = unserialize(str_replace('array_', $pictures));
        }

        /* 二维码
         * ---------------------------------------- */
        $user = AccountFront::find(10334);
        if (!$user->invite_code) {
            $hashids    = new Hashids('dailian-invite', 6);
            $inviteCode = $hashids->encode($this->pam->account_id);
            $user->update([
                'invite_code' => $inviteCode,
            ]);
        }
        else {
            $inviteCode = $user->invite_code;
        }
        $url = route_url('site:web.home.invite', [$inviteCode]);

        $qrcodeUrl = route_url('site:web.support_util.qrcode', null, [
            't' => $url,
            's' => 'xs',
        ]);

        $files = [];
        foreach ($pictures as $picture) {
            /* 背景图
            * ---------------------------------------- */
            $image = Image::make($picture);

            /* 填充二维码
             * ---------------------------------------- */
            $image->insert($qrcodeUrl, 'bottom-left', 140, 160);

            $file_name = StrHelper::uniqueId('qr_code');
            $files[]   = $image->response('jpg', 100);
        }
    }

    /**
     * @param integer $account_id 用户ID
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function aliPush($account_id)
    {
        $avatar        = avatar('');
        $nickname      = '管理员';
        $order_help_id = site('order_help_category');
        $activity_id   = site('activity_category');
        $items         = PluginHelp::where(function ($query) use ($order_help_id, $activity_id) {
            $query->orWhere('cat_id', $order_help_id);
            $query->orWhere('cat_id', $activity_id);
        })->get();
        /** @var PluginHelp $item */
        $item = $items->random();

        $extras = [
            'type'         => 'system',
            'title'        => $item->help_title,
            'url'          => route_url('site:web.app.help', ['id' => $item->id]),
            'id'           => $item->id,
            'cat_id'       => $item->cat_id,
            'time'         => Carbon::now()->toDateTimeString(),
            'is_can_click' => true,
            'avatar'       => $avatar,
            'nickname'     => $nickname,
        ];


        $notify_account_id = [$account_id];

        // 获取 ali push 注册码
        /** @var Collection $aliPushIds */
        $aliPushIds = AppInit::whereIn('account_id', $notify_account_id)
            ->where('ali_push_id', '!=', '')
            ->pluck('ali_push_id');
        $ids        = $aliPushIds->toArray();

        $param = [
            // 本地使用 tag, 则进行测试
            'broadcast_type'   => 'DEVICE',
            'registration_ids' => $ids,
            'title'            => $item->help_title,
            'content'          => substr_cn($item->content, 30),
            'extras'           => $extras,
            'offline'          => 'Y',
            'device_type'      => 'android|notice',
        ];

        $Push = new AliPush();
        if (!$Push->send($param)) {
            return Resp::error($Push->getError());
        }
        return Resp::success('成功');
    }

    /**
     * 配合PC测试 通过实名认证socket发送
     * @param integer $account_id 用户ID
     * @return JsonResponse|RedirectResponse|Response|Redirector
     */
    public function zhima($account_id)
    {
        /** @var AccountFront $Front */
        $Front = AccountFront::where('account_id', $account_id)->first();
        if (!$Front) {
            return Resp::error('查无此用户!');
        }
        if ($Front->truename_status !== AccountFront::TRUENAME_STATUS_PASSED) {
            return Resp::error('该账号实名认证未通过，可以通过前台申请，后台审核通过形式进行通过申请或联系后端人员！');
        }
        event(new ZhimaApproveEvent($Front));
        return Resp::success('执行成功，但是socket有可能未发送如socket未发送请联系后端管理人员！');
    }
}

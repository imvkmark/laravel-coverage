<?php namespace Site\Http\Request\Web;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Mobile_Detect;
use Response;
use Site\Classes\Hash;
use Poppy\Framework\Classes\Resp;
use Site\Models\FrontActivity;

/**
 * 活动页面
 */
class ActivityController extends InitController
{

    /***
     * 展示活动
     * @param integer $id 活动ID
     * @return JsonResponse|RedirectResponse|\Illuminate\Http\Response|Redirector
     */
    public function show($id)
    {
        $id = (new Hash())->decode($id)[0] ?? 0;
        if (!$id) {
            return Resp::error('找不到此活動');
        }
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
     * 19.1.24 后台活动设定
     * @return Factory|View
     */
    public function s9At2019()
    {

        if ((new Mobile_Detect())->isMobile()) {
            return view('site::web.activity.2019.s9_mobile');
        }
        return view('site::web.activity.2019.s9_pc');
    }

    /**
     * 19.4.16 后台活动设定
     * @return Factory|View
     */
    public function s9At20190416()
    {

        if ((new Mobile_Detect())->isMobile()) {
            return view('site::web.activity.20190416.glory_mobile');
        }
        return view('site::web.activity.20190416.glory_pc');
    }

    /**
     * 接单福利
     * @return Factory|\Illuminate\Foundation\Application|View
     */
    public function welfare()
    {
        return view('site::web.activity.2020.welfare');
    }

    public function welfare_new()
    {
        return view('site::web.activity.2020.welfare_new');
    }

    public function welfare_new_10()
    {
        return view('site::web.activity.2020.welfare_new_10');
    }

    public function welfare_2020_09_24_wz()
    {
        return view('site::web.activity.2020.walfare_2020_09_24_wz');
    }

    public function welfare_2020_09_24_lol()
    {
        return view('site::web.activity.2020.walfare_2020_09_24_lol');
    }
}

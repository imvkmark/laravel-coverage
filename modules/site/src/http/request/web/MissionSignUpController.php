<?php namespace Site\Http\Request\Web;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Poppy\Framework\Classes\Resp;
use Site\Action\ActionSignUp;
use Illuminate\Http\Request;
use Site\Models\FrontActivity;
use User\Models\ActivitySignUp;

class MissionSignUpController extends InitController
{
    /**
     * @param Request $request request
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function signUp(Request $request)
    {
        $id = $request->input('id');
        //判断用户是否登录
        if (!$this->owner) {
            return Resp::error('请先登录');
        }
        //判断是否报名 Y 前台将报名按钮改为 灰色 已报名
        $sign_up = ActivitySignUp::where('account_id', $this->owner->account_id)
            ->where('activity_id', $id)
            ->count();

        if (!empty($sign_up)) {
            return Resp::error('已报名过该活动', '');
        }

        //获取活动类型
        $activity = FrontActivity::find($id);
        //判断哪种活动类型 不符合报名 不符合 将报名按钮 改为灰色
        switch ($activity->group) {
            case FrontActivity::GROUP_LIMIT_TIME:
                break;
            case FrontActivity::GROUP_PLATFORM:
                break;
        }

        //符合条件 记录信息
        $info   = [
            'account_id'  => $this->owner->account_id,
            'activity_id' => $id,
            'game_id'     => $activity->game_id,
            'mobile'      => $this->owner->mobile,
            'qq'          => $this->owner->qq,
        ];
        $acSign = new ActionSignUp();
        if ($acSign->establish($info)) {
            return Resp::success('报名成功', '_reload|1');
        }

    }

}

<?php namespace Site\Http\Request\Web;

use Session;
use Site\Models\PluginAllowip;
use Site\Models\PamAccount;
use Order\Models\GameName;
use Site\Action\ActionValidate;
use User\Models\AccountFront;
use Order\Models\DailianOrder;
use Illuminate\Http\Request;

/**
 * 用于 js 验证
 */
class ValidateController extends InitController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('pam_front.auth');
    }


    /**
     * 检查用户回答的重复密保问题是否正确
     * @param Request $request request
     */
    public function answerRepeat(Request $request)
    {
        if ($request->input('answer_1')) {
            $sessionKey = 'answer_1';
            $value      = $request->input('answer_1');
        }
        elseif ($request->input('answer_2')) {
            $sessionKey = 'answer_2';
            $value      = $request->input('answer_2');
        }
        else {
            $sessionKey = 'answer_3';
            $value      = $request->input('answer_3');
        }
        $repeatAnswer = Session::get('site#user_answer');

        if (isset($repeatAnswer[$sessionKey]) && $repeatAnswer[$sessionKey] === $value) {
            echo 'true';
        }
        else {
            echo 'false';
        }
    }

    /**
     * 检测支付密码是否正确
     * @param Request $request request
     *                         payword : 支付密码
     */
    public function payword(Request $request)
    {
        $payword = $request->input('payword');
        if (AccountFront::checkPayword($this->accountId, $payword)) {
            echo 'true';
        }
        else {
            echo 'false';
        }
    }

    /**
     * 检测余额是否充足够能够支付订单
     * @param Request $request  request
     * @param integer $order_id 订单id
     */
    public function moneyEnough(Request $request, $order_id = null)
    {
        $price = $request->input('order_price');
        if (isset($order_id)) {
            $order         = DailianOrder::find($order_id);
            $order_money   = $order->order_price;
            $surplus_money = $this->owner->money - $this->owner->lock;
            $money         = $order_money + $surplus_money;
        }
        else {
            $money = $this->owner->money - $this->owner->lock;
        }

        if (bccomp($price, $money, 2) > 0) {
            echo 'false';
        }
        else {
            echo 'true';
        }
    }

    /**
     * account_name
     * @param Request $request request
     */
    public function accountNameAvailable(Request $request)
    {
        $account_name = $request->input('account_name');
        if (PamAccount::accountNameExists($account_name)) {
            exit('false');
        }
        else {
            exit('true');
        }
    }

    /**
     * 账户存在
     * @param Request $request request
     */
    public function accountNameExists(Request $request)
    {
        $account_name = $request->input('account_name');
        if (PamAccount::accountNameExists($account_name)) {
            exit('true');
        }
        else {
            exit('false');
        }
    }

    /**
     * 验证码可用
     * mobile
     * mobile_captcha
     * @param Request $request request
     */
    public function mobileCodeValid(Request $request)
    {
        $mobile_code = $request->input('mobile_captcha');
        $subject     = $request->input('mobile');
        $Validate    = new ActionValidate();
        if ($Validate->checkMobileCodeValid($subject, $mobile_code)) {
            echo 'true';
        }
        else {
            echo 'false';
        }
    }


    /**
     * 检查是否存在游戏名称
     * @param Request $request request
     * @param null    $id      ID
     */
    public function gameNameAvailable(Request $request, $id = null)
    {
        $GameName = GameName::where('game_name', $request->input('game_name'));
        if ($id) {
            $GameName->where('game_id', '!=', $id);
        }
        if ($GameName->exists()) {
            exit('false');
        }
        else {
            exit('true');
        }
    }

    /**
     * 检查是否存在ip
     * @param Request $request request
     */
    public function allowIpAvailable(Request $request)
    {
        $Ip = PluginAllowip::where('ip_addr', $request->input('ip_addr'));
        if ($Ip->exists()) {
            exit('false');
        }
        else {
            exit('true');
        }
    }
}

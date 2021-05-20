<?php namespace Site\Http\Request\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Intervention\Image\Facades\Image;
use Poppy\Framework\Classes\Resp;
use QrCode;
use Site\Action\ActionSpreadSet;
use Site\Classes\YunKf\VisitorCard;

/**
 * 推广图片展示
 */
class SpreadImgController extends InitController
{
    /**
     * @return array|JsonResponse|RedirectResponse|Response|Redirector|mixed|Resp|\Response
     */
    public function show()
    {
        $input = input();

        $url = sys_get($input, 'url');
        $key = sys_get($input, 'key');
        if (!$url) {
            return Resp::error('参数错误');
        }
        $qrcodeUrl = $this->qrcode($url, 264);
        $pictures  = ActionSpreadSet::getBg();

        $image = Image::make($pictures[$key]);
        /* 填充二维码
         * ---------------------------------------- */
        $image->insert($qrcodeUrl, 'bottom-left', 244, 288);
        return $image->response('jpg', 100);
    }

    /**
     * 云客服查询用户信息
     */
    public function yunKf()
    {
        $YunKf = new VisitorCard();
        $input = input();

        $params = sys_get($input, 'params');
        $key    = sys_get($input, 'key');
        if ($data = $YunKf->customerInfo($params, $key)) {
            return $data;
        }

        return [
            'message' => '查询失败',
            'success' => false,
        ];
    }

    /**
     * @param        $text
     * @param int    $width
     * @param string $size
     * @return string|void
     */
    public function qrcode($text, $width = 0, $size = 'm')
    {
        $len = strlen($text);
        $map = [
            'xs'  => 100,
            's'   => 200,
            'm'   => 300,
            'l'   => 400,
            'xl'  => 500,
            'xxl' => 600,
        ];

        $px = $width;
        if (!$width) {
            $px = $map[$size] ?? $map['m'];
        }

        $Qr = QrCode::format('png')->size($px)->margin(1);
        if ($len < 50) {
            $Qr->errorCorrection('H');
        }
        return $Qr->generate($text);
    }
}

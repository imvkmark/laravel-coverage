<?php

namespace Poppy\Framework\Application;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\Framework\Http\Pagination\PageInfo;
use Route;
use View;

/**
 * poppy controller
 */
abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * @var string 权限(中间件中可以读取, 使用 public 模式)
     */
    public static $permission;

    /**
     * pagesize
     * @var int $pagesize
     */
    protected $pagesize = 15;

    /**
     * ip
     * @var string $ip
     */
    protected $ip;

    /**
     * now
     * @var Carbon $now
     */
    protected $now;

    /**
     * route
     * @var string $route
     */
    protected $route;

    /**
     * title
     * @var string $title
     */
    protected $title;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->route    = Route::currentRouteName();
        $this->pagesize = PageInfo::pagesize();
        $this->ip       = EnvHelper::ip();
        $this->now      = Carbon::now();
    }

    protected function withViews()
    {
        View::share([
            '_ip'       => $this->ip,
            '_now'      => $this->now,
            '_pagesize' => $this->pagesize,
            '_route'    => $this->route,
        ]);
        // 自动计算seo
        // 根据路由名称来转换 seo key
        // system:web.user.index  => system::web_nav_index
        $seoKey = str_replace([':', '.'], ['::', '_'], $this->route);
        if ($seoKey) {
            $seoKey = str_replace('::', '::seo.', $seoKey);
            $this->seo(trans($seoKey));
        }
        else {
            $this->seo();
        }
    }

    /**
     * seo
     * @param mixed ...$args args
     */
    protected function seo(...$args)
    {
        [$title, $description] = parse_seo($args);
        $title       = $title ? $title . '-' . config('poppy.framework.title') : config('poppy.framework.title');
        $description = $description ?: config('poppy.framework.description');

        $this->title = $title;

        View::share([
            '_title'       => $title,
            '_description' => $description,
        ]);
    }
}
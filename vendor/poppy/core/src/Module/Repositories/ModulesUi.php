<?php namespace Poppy\Core\Module\Repositories;

use Illuminate\Support\Collection;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Framework\Support\Abstracts\Repository;

/**
 * Ui Repos
 * @deprecated
 */
class ModulesUi extends Repository
{

    /**
     * @var Collection
     */
    protected $structures;

    /**
     * Initialize.
     * @param Collection $uis 集合
     */
    public function initialize(Collection $uis)
    {
        // check serve setting
        $this->items = sys_cache('py-core')->remember(
            PyCoreDef::ckModule('ui'),
            PyCoreDef::MIN_ONE_DAY,
            function () use ($uis) {
                $ui = collect();
                $uis->each(function ($moduleUis) use ($ui) {
                    collect($moduleUis)->each(function ($def, $key) use ($ui) {
                        $ui->put($key, $def);
                    })->toArray();
                });
                return $ui->all();
            }
        );
    }

    /**
     * 显示UI, 减少传值
     * @param string $key          UI Key
     * @param array  $route_params 路由参数
     * @param bool   $display      是否显示
     * @return string
     */
    public function render($key, $route_params = [], $display = true): string
    {
        if (!$display) {
            return '';
        }
        if ($this->offsetExists($key)) {
            $def  = $this->offsetGet($key);
            $type = $def['type'] ?? 'link';
            if ($type === 'link') {
                return $this->parseLink($def, $route_params);
            }
            if ($type === 'iframe') {
                return $this->parseIframe($def, $route_params);
            }
            if ($type === 'request') {
                return $this->parseRequest($def, $route_params);
            }
            if ($type === 'javascript') {
                return $this->parseJavascript($def, $route_params);
            }
            return '';
        }
        return '';
    }

    /**
     * @param array $ui
     * @param null  $route_params
     * @param array $params
     * @return string
     */
    private function parseLink($ui, $route_params = null, $params = []): string
    {
        $title   = $this->defTitle($ui);
        $element = $this->defElement($ui);
        $url     = $this->defUrl($ui, $route_params, $params);
        return <<<Link
    <a href="{$url}" class="J_tooltip" title="{$title}">
        {$element}
    </a>
Link;
    }


    /**
     * @param array $ui     UI 标识
     * @param null  $params 参数
     * @return string
     */
    private function parseJavascript($ui, $params = null): string
    {
        $title   = $this->defTitle($ui);
        $element = $this->defElement($ui);
        return <<<Link
    <a href="javascript:{$params}" class="J_tooltip" title="{$title}">
        {$element}
    </a>
Link;
    }

    /**
     * 解析 iframe 弹窗
     * @param array $ui           UI 标识
     * @param null  $route_params 路由参数
     * @param array $params       参数
     * @return string
     */
    private function parseIframe($ui, $route_params = null, $params = []): string
    {
        $title   = $this->defTitle($ui);
        $element = $this->defElement($ui);
        $url     = $this->defUrl($ui, $route_params, $params);
        return <<<Link
    <a href="{$url}" class="J_tooltip J_iframe" title="{$title}">
        {$element}
    </a>
Link;
    }

    /**
     * 解析请求
     * @param array $ui           UI 标识
     * @param null  $route_params 路由参数
     * @param array $params       参数
     * @return string
     */
    private function parseRequest($ui, $route_params = null, $params = []): string
    {
        $title   = $this->defTitle($ui);
        $element = $this->defElement($ui);
        $url     = $this->defUrl($ui, $route_params, $params);
        return <<<Link
    <a href="{$url}" class="J_request J_iframe" title="{$title}">
        {$element}
    </a>
Link;
    }

    /**
     * 解析标题
     * @param array $ui 定义
     * @return string
     */
    private function defTitle($ui): string
    {
        return $ui['title'] ?? '';
    }

    /**
     * 解析元素
     * @param array $ui 定义
     * @return string
     */
    private function defElement($ui): string
    {
        if ($ui['icon'] ?? '') {
            return <<<Element
	<i class="{$ui['icon']}"></i>
Element;
        }
        return $this->defTitle($ui);
    }

    /**
     * 解析URL
     * @param array        $ui           定义
     * @param array|string $route_params 路由参数
     * @param array|string $params       参数
     * @return string
     */
    private function defUrl($ui, $route_params, $params): string
    {
        $route       = $ui['route'] ?? '';
        $routeParams = $ui['route_params'] ?? [];
        $defParams   = $ui['params'] ?? [];
        $url         = $ui['url'] ?? '';
        if ($route) {
            $routeParams = array_merge($routeParams, $route_params);
            $defParams   = array_merge($defParams, $params);
            $url         = route_url($route, $routeParams, $defParams);
        }
        return $url;
    }

}

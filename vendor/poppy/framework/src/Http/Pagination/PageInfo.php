<?php

namespace Poppy\Framework\Http\Pagination;

/**
 * 分页信息
 */
class PageInfo
{
    /**
     * @var int 页码
     */
    private $page;

    /**
     * @var int 每页的分页数
     */
    private $size;

    /**
     * 分页构造器
     * @param array $page_info 分页信息
     */
    public function __construct(array $page_info)
    {
        $sizeConfig = abs(config('poppy.framework.page_size')) ?: 15;
        $page       = abs($page_info['page'] ?? 1);
        $size       = abs($page_info['size'] ?? $sizeConfig);
        $this->page = $page ?: 1;
        $this->size = $size ?: $sizeConfig;
    }

    /**
     * 分页大小
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * 页码
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * 返回分页的大小
     * @return int
     */
    public static function pagesize(): int
    {
        // pagesize
        $size        = config('poppy.framework.page_size', 15);
        $maxPagesize = config('poppy.framework.page_max');
        if (input('pagesize')) {
            $pagesize = abs((int) input('pagesize'));
            $pagesize = ($pagesize <= $maxPagesize) ? $pagesize : $maxPagesize;
            if ($pagesize > 0) {
                $size = $pagesize;
            }
        }
        return $size;
    }
}
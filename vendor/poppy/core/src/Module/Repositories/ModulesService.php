<?php

namespace Poppy\Core\Module\Repositories;

use Illuminate\Support\Collection;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Framework\Support\Abstracts\Repository;

/**
 * 定义的服务项
 */
class ModulesService extends Repository
{

    /**
     * Initialize.
     * @param Collection $data 集合
     */
    public function initialize(Collection $data)
    {
        $this->items = sys_cache('py-core')->remember(
            PyCoreDef::ckModule('service'),
            PyCoreDef::MIN_HALF_DAY,
            function () use ($data) {
                $collection = collect();
                $data->each(function ($items) use ($collection) {
                    $items = collect($items);
                    $items->each(function ($item, $key) use ($collection) {
                        $collection->put($key, $item);
                    });
                });

                return $collection->all();
            }
        );
    }
}

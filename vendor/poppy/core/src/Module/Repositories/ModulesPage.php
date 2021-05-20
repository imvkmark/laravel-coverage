<?php

namespace Poppy\Core\Module\Repositories;

use Illuminate\Support\Collection;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Framework\Support\Abstracts\Repository;

/**
 * 为了兼容而存在
 * @deprecated 3.1
 * @removed    4.0
 */
class ModulesPage extends Repository
{
    /**
     * Initialize.
     * @param Collection $slugs 集合
     */
    public function initialize(Collection $slugs)
    {
        $this->items = sys_cache('py-core')->remember(
            PyCoreDef::ckModule('page'),
            PyCoreDef::MIN_ONE_DAY,
            function () use ($slugs) {
                $collection = collect();
                $slugs->each(function ($items, $slug) use ($collection) {
                    if ($items) {
                        $collection->put($slug, $items);
                    }
                });
                $collection->transform(function ($definition) {
                    data_set($definition, 'tabs', collect($definition['tabs'])->map(function ($definition) {
                        data_set($definition, 'fields', collect($definition['fields'])->map(function ($definition) {
                            // 兼容函数不存在情况
                            // 仅仅是针对ydl
                            $setting = '';
                            if (function_exists('ydl_setting')) {
                                $setting = ydl_setting($definition['key'], '');
                            }
                            elseif (function_exists('sys_setting')) {
                                $setting = sys_setting($definition['key'], '');
                            }
                            if (isset($definition['format'])) {
                                switch ($definition['format']) {
                                    case 'boolean':
                                        $definition['value'] = (bool) $setting;
                                        break;
                                    default:
                                        $definition['value'] = $setting;
                                        break;
                                }
                            }
                            else {
                                $definition['value'] = $setting;
                            }

                            return $definition;
                        }));

                        return $definition;
                    }));

                    return $definition;
                });

                return $collection->all();
            }
        );
    }
}

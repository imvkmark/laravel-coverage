<?php

namespace Poppy\Core\Rbac\Repositories;

use Illuminate\Support\Collection;
use Poppy\Framework\Support\Abstracts\Repository;

/**
 * Class PermissionRepository.
 */
class PermissionRepository extends Repository
{
    /**
     * Initialize.
     *
     * @param Collection $collection 需要初始化的权限
     */
    public function initialize(Collection $collection)
    {
        $collection->each(function ($definition, $identification) {
            $this->items[$identification] = $definition;
        });
    }
}

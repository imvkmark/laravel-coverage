<?php

namespace Poppy\Core\Listeners\PoppyOptimized;

use Poppy\Framework\Events\PoppyOptimized;
use Storage;

/**
 * 清除缓存
 */
class ClearCacheListener
{

    /**
     * @param PoppyOptimized $event 框架优化
     */
    public function handle(PoppyOptimized $event)
    {
        sys_cache('py-core')->flush();

        // cache files
        $disk = Storage::disk('storage');
        collect([
            'framework/classes.php',
            'framework/packages.php',
            'framework/services.php',
        ])->each(function ($item) use ($disk) {
            if ($disk->exists($item)) {
                $disk->delete($item);
            }
        });

        // clear console logs
        $logs  = glob('storage/logs/console-*.log');
        $count = count($logs);
        collect($logs)->each(function ($file, $idx) use ($disk, $count) {
            if ($idx + 5 < $count) {
                $disk->delete($file);
            }
        });
    }
}


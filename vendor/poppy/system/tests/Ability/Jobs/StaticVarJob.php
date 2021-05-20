<?php

namespace Poppy\System\Tests\Ability\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Poppy\Framework\Application\Job;
use Poppy\Framework\Helper\ArrayHelper;

/**
 * 队列
 */
class StaticVarJob extends Job implements ShouldQueue
{
    use Queueable;

    /**
     * 脚本目录
     * @var int $shellPath
     */
    private $var;

    /**
     * Create a new job instance.
     * @param int $appendVar 追加的变量
     */
    public function __construct(int $appendVar)
    {
        $this->var = $appendVar;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        static $vars;
        $vars[] = $this->var;
        sys_info('testing', self::class, 'vars:' . ArrayHelper::toKvStr($vars));
        if ($this->var < 100) {
            dispatch(new self($this->var + 1))->delay(1);
        }
    }
}

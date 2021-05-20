<?php

namespace Poppy\Core\Commands;

use Illuminate\Console\Command;
use Mail;
use Poppy\System\Mail\MaintainMail;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

/**
 * User
 */
class OpCommand extends Command
{
    /**
     * @var string 名称
     */
    protected $name = 'py-core:op';

    /**
     * @var string 描述
     */
    protected $description = 'Maintain Tool.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $do = $this->argument('do');
        switch ($do) {
            case 'mail':
                $title   = $this->option('title') ?: 'No Title';
                $content = $this->option('content') ?: 'No Content';
                $file    = $this->option('file');
                if (!config('poppy.core.op_mail')) {
                    $this->error(sys_mark('poppy.core', self::class, 'Config `poppy.core.op_mail` not set. Can not send Op Mail'));
                    return;
                }
                try {
                    Mail::to(config('poppy.core.op_mail'))->send(new MaintainMail($title, $content, $file));
                } catch (Throwable $e) {
                    $this->error(sys_mark('poppy.core', self::class, $e->getMessage()));
                }
                break;
            case 'clear':
                sys_cache('py-core')->flush();
                $this->info(sys_mark('poppy.core', self::class, 'Clear Core Cache'));
                break;
            default:
                $this->warn('Error type in maintain tool.');
                break;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['do', InputArgument::REQUIRED, 'Maintain type.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['title', null, InputOption::VALUE_OPTIONAL, 'Mail Title'],
            ['content', null, InputOption::VALUE_OPTIONAL, 'Mail Content'],
            ['file', null, InputOption::VALUE_OPTIONAL, 'Mail Content'],
            ['log', null, InputOption::VALUE_NONE, 'Need Log'],
            ['type', null, InputOption::VALUE_OPTIONAL, 'Request Type'],
            ['url', null, InputOption::VALUE_OPTIONAL, 'Request Url'],
        ];
    }
}
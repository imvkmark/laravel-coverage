<?php

namespace Poppy\System\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Poppy\Core\Redis\RdsDb;
use Poppy\System\Action\Pam;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\SysConfig;
use Throwable;

/**
 * User
 */
class UserCommand extends Command
{
    /**
     * 前端部署.
     * @var string
     */
    protected $signature = 'py-system:user 
		{do : actions in "reset_pwd"}
		{--account= : Account Name}
		{--pwd= : Account password}
		';

    /**
     * 描述
     * @var string
     */
    protected $description = 'user handler.';

    /**
     * Execute the console command.
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        $do = $this->argument('do');
        switch ($do) {
            case 'reset_pwd':
                $passport = $this->ask('Your passport?');

                if ($pam = PamAccount::passport($passport)) {
                    $pwd = trim($this->ask('Your aim password'));
                    $Pam = new Pam();
                    $Pam->setPassword($pam, $pwd);
                    $this->info('Reset user password success');
                }
                else {
                    $this->error('Your account not exists');
                }
                break;
            case 'create_user':
                $passport = $this->ask('Please input passport!');
                $password = $this->ask('Please input password!');
                $role     = $this->ask('Please input role name!');
                if (!PamAccount::passport($passport)) {
                    $Pam = new Pam();
                    if ($Pam->register($passport, $password, $role)) {
                        $this->info('User ' . $passport . ' created');
                    }
                    else {
                        $this->error($Pam->getError());
                    }
                }
                else {
                    $this->error('user ' . $passport . ' exists');
                }
                break;
            case 'auto_fill':
                $user = PamAccount::whereIn('type', [PamAccount::TYPE_BACKEND, PamAccount::TYPE_DEVELOP])->pluck('id', 'username');
                if (!$user) {
                    return;
                }
                collect($user)->map(function ($id) {
                    PamAccount::where('id', $id)->update([
                        'mobile' => '33023-' . sprintf("%s%'.07d", '', $id),
                    ]);
                });
                $this->info(sys_mark('py-system', self::class, 'Fill Mobile Over'));
                break;
            case 'clear_expired':
                // 移除过期的 Jwt Token
                $Rds    = RdsDb::instance();
                $endTtl = Carbon::now()->timestamp;
                $items  = $Rds->zRangeByScore(PySystemDef::ckSso('expired'), 0, $endTtl);
                $num    = 0;
                if (is_array($items) && $num = count($items)) {
                    $Rds->hDel(PySystemDef::ckSso('valid'), $items);
                    $Rds->zRemRangeByScore(PySystemDef::ckSso('expired'), 0, $endTtl);
                }
                $this->info(sys_mark('py-system', self::class, 'Delete Expired Token, Num : ' . $num));
                break;
            case 'init_role':
                $roles = [
                    [
                        'name'      => PamRole::FE_USER,
                        'title'     => '用户',
                        'type'      => PamAccount::TYPE_USER,
                        'is_system' => SysConfig::YES,
                    ],
                    [
                        'name'      => PamRole::BE_ROOT,
                        'title'     => '超级管理员',
                        'type'      => PamAccount::TYPE_BACKEND,
                        'is_system' => SysConfig::YES,
                    ],
                    [
                        'name'      => PamRole::DEV_USER,
                        'title'     => '开发者',
                        'type'      => PamAccount::TYPE_DEVELOP,
                        'is_system' => SysConfig::YES,
                    ],
                ];
                foreach ($roles as $role) {
                    if (!PamRole::where('name', $role['name'])->exists()) {
                        PamRole::create($role);
                    }
                }
                $this->info('Init Role success');
                break;
            case 'auto_enable':
                (new Pam())->autoEnable();
                $this->info(sys_mark('py-system', __CLASS__, 'auto enable pam!'));
                break;
            case 'clear_log':
                (new Pam())->clearLog();
                $this->info(sys_mark('py-system', __CLASS__, 'auto clear log!'));
                break;
            default:
                $this->error('Please type right action![reset_pwd, init_role, create_user, auto_enable, clear_log, auto_fill]');
                break;
        }
    }
}
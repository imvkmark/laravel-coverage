<?php

namespace Poppy\System\Classes\Auth\Password;

use Poppy\System\Classes\Contracts\PasswordContract;
use Poppy\System\Models\PamAccount;

/**
 * 用户认证
 */
class DefaultPasswordProvider implements PasswordContract
{
    /**
     * @inheritDoc
     */
    public function check(PamAccount $pam, string $password, $type = 'plain'): bool
    {
        return $this->genPassword($password, $pam->created_at, $pam->password_key) === $pam->password;
    }

    /**
     * @inheritDoc
     */
    public function genPassword(string $password, string $reg_datetime, string $password_key): string
    {
        return md5(sha1($password . $reg_datetime) . $password_key);
    }
}
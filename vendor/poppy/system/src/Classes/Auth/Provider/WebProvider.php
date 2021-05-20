<?php

namespace Poppy\System\Classes\Auth\Provider;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Poppy\System\Models\PamAccount;

/**
 * 前台用户认证
 */
class WebProvider extends PamProvider
{
    /**
     * Retrieve a user by the given credentials.
     * DO NOT TEST PASSWORD HERE!
     * @param array $credentials 凭证
     * @return Builder|Model
     */
    public function retrieveByCredentials(array $credentials)
    {
        $credentials['type'] = PamAccount::TYPE_USER;

        return parent::retrieveByCredentials($credentials);
    }
}
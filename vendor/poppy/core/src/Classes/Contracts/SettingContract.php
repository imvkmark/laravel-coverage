<?php

namespace Poppy\Core\Classes\Contracts;

/**
 * Interface SettingsRepository.
 */
interface SettingContract
{
    /**
     * Delete a setting value.
     * @param string $key key need delete
     */
    public function delete(string $key);

    /**
     * Get a setting value by key.
     * @param string $key     获取设置key
     * @param null   $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a setting value from key and value.
     * @param string|array $key   获取设置key
     * @param mixed        $value 需要设置的值
     */
    public function set(string $key, $value = '');
}

<?php

namespace Poppy\System\Classes;


class PySystemDef
{
    /**
     * 模型注释
     * @return string
     */
    public static function ckModelComment(): string
    {
        return 'model-comment';
    }

    /**
     * 设置
     * @return string
     */
    public static function ckSetting(): string
    {
        return 'setting';
    }

    /**
     * 设置
     * @return string
     */
    public static function ckPamRelParent(): string
    {
        return 'pam-rel-parent';
    }

    /**
     * 一次验证码
     * @return string
     */
    public static function ckVerificationOnce(): string
    {
        return 'py-system:verification-once_code';
    }

    /**
     * 验证码
     * @return string
     */
    public static function ckVerificationCaptcha(): string
    {
        return 'py-system:verification-captcha';
    }

    /**
     * 单点登录的Hash(允许访问的)
     * @param string $type 类型
     * @return string
     */
    public static function ckSso(string $type): string
    {
        return 'py-system:sso-' . $type;
    }

    /**
     * 用户禁用
     * @return string
     */
    public static function ckBan(): string
    {
        return 'py-system:ban';
    }
}
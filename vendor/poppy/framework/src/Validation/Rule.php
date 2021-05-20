<?php

namespace Poppy\Framework\Validation;

use Illuminate\Validation\Rule as IlluminateRule;

/**
 * Class Rule.
 */
class Rule extends IlluminateRule
{
    /**
     * @return string
     */
    public static function array(): string
    {
        return 'array';
    }

    /**
     * 验证的字段必须完全是字母的字符
     * @return string
     */
    public static function alpha(): string
    {
        return 'alpha';
    }

    /**
     * 验证的字段可能具有字母、数字、破折号（ - ）以及下划线（ _ ）
     * @return string
     */
    public static function alphaDash(): string
    {
        return 'alpha_dash';
    }

    /**
     * string rule
     * @return string
     */
    public static function string(): string
    {
        return 'string';
    }

    /**
     * 身份证号
     * string rule
     * @return string
     */
    public static function chid(): string
    {
        return 'chid';
    }

    /**
     * size
     * @param int $length length
     * @return string
     */
    public static function size(int $length): string
    {
        return 'size:' . $length;
    }

    /**
     * max
     * @param int $length length
     * @return string
     */
    public static function max(int $length): string
    {
        return 'max:' . $length;
    }

    /**
     * @return string
     */
    public static function boolean(): string
    {
        return 'boolean';
    }

    /**
     * date format
     * @param string $format format
     * @return string
     */
    public static function dateFormat($format): string
    {
        return 'date_format:' . $format;
    }

    /**
     * @return string
     */
    public static function date(): string
    {
        return 'date';
    }

    /**
     * @return string
     */
    public static function nullable(): string
    {
        return 'nullable';
    }

    /**
     * @return string
     */
    public static function email(): string
    {
        return 'email';
    }

    /**
     * 用户名验证, 支持子用户
     * @param bool $sub 是否是子用户
     * @return string
     */
    public static function username($sub = false): string
    {
        if ($sub) {
            return 'username:sub';
        }
        return 'username:normal';
    }

    /**
     * @return string
     */
    public static function file(): string
    {
        return 'file';
    }

    /**
     * @return string
     */
    public static function image(): string
    {
        return 'image';
    }

    /**
     * mimetypes
     * @param array $mimeTypes $mimeTypes
     * @return string
     */
    public static function mimetypes(array $mimeTypes): string
    {
        return 'mimetypes:' . implode(',', $mimeTypes);
    }

    /**
     * @return string
     */
    public static function numeric(): string
    {
        return 'numeric';
    }

    /**
     * regex
     * @param string $regex regex
     * @return string
     */
    public static function regex(string $regex): string
    {
        return 'regex:' . $regex;
    }

    /**
     * @return string
     */
    public static function required(): string
    {
        return 'required';
    }

    /**
     * @return string
     */
    public static function confirmed(): string
    {
        return 'confirmed';
    }

    /**
     * @return string
     */
    public static function mobile(): string
    {
        return 'mobile';
    }

    /**
     * @return string
     */
    public static function password(): string
    {
        return 'password';
    }

    /**
     * @return string
     */
    public static function simplePwd(): string
    {
        return 'simple_pwd';
    }

    /**
     * @return string
     */
    public static function url(): string
    {
        return 'url';
    }

    /**
     * Between String
     * @param int $start start
     * @param int $end   end
     * @return string
     */
    public static function between(int $start, int $end): string
    {
        return 'between:' . $start . ',' . $end;
    }

    /**
     * 最小数
     * @param string $value 最小值
     * @return string
     */
    public static function min(string $value): string
    {
        return 'min:' . $value;
    }

    /**
     * @return string
     */
    public static function integer(): string
    {
        return 'integer';
    }

    /**
     * @return string
     */
    public static function json(): string
    {
        return 'json';
    }

    /**
     * @return string
     */
    public static function ip(): string
    {
        return 'ip';
    }
}

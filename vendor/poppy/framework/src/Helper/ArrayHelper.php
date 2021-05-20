<?php

namespace Poppy\Framework\Helper;


use Illuminate\Support\Arr;

/**
 * 数组相关操作
 */
class ArrayHelper
{
    /**
     * 拼合数组, 支持多维拼合
     * @param array  $array 输入的数组
     * @param string $join  间隔字串
     * @return string
     */
    public static function combine(array $array, $join = ','): string
    {
        $arr = Arr::flatten($array);

        return implode($join, $arr);
    }

    /**
     * 根据数组生成自定义key序列, | 作为 kv 分隔, ; 作为 kv 之间的分隔
     * ['name'=>'mark Zhao'] 转化为 name|mark Zhao
     * @param array $array 输入的数组
     * @return string 返回的字串
     */
    public static function genKey(array $array): string
    {
        if ($array) {
            $str = '';
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    continue;
                }
                if (!$value) {
                    $value = 0;
                }
                $str .= $key . '|' . $value . ';';
            }

            return rtrim($str, ';');
        }

        return '';
    }

    /**
     * 返回kv结构字串
     * @param array|string $array array
     * @param string       $join  join
     * @return string
     */
    public static function toKvStr($array, $join = ','): string
    {
        $return = '';

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    ksort($value);
                    $return .= $key . '=' . json_encode($value) . $join;
                }
                else {
                    $return .= $key . '=' . $value . $join;
                }
            }
        }
        else {
            $return .= $array . $join;
        }

        return rtrim($return, $join);
    }

    /**
     * 根据当前值, 获取下一个值
     * @param array      $array 请求的数组
     * @param string|int $value 匹配的值
     * @return mixed
     */
    public static function next(array $array, $value)
    {
        reset($array);
        while ($_val = current($array)) {
            if ($_val === $value) {
                break;
            }
            next($array);
        }

        return next($array);
    }


    /**
     * 根据值删除数组中的元素
     * @param array        $array
     * @param string|array $value
     * @return array
     */
    public static function delete(array $array, $value): array
    {
        $value = Arr::wrap($value);

        $return = [];
        foreach ($array as $index => $item) {
            if (!in_array($item, $value)) {
                $return[$index] = $item;
            }
        }
        return $return;
    }

    /**
     * 映射 Null 到 Empty
     * @param array $arr
     * @return array
     */
    public static function mapNull(array $arr): array
    {
        $return = [];
        foreach ($arr as $index => $item) {
            if (is_array($item)) {
                $return[$index] = self::mapNull($item);
            }
            elseif (is_null($item)) {
                $return[$index] = '';
            }
            else {
                $return[$index] = $item;
            }
        }
        return $return;
    }

    /**
     * 查找值在数组中的位置, 用于 Excel 导入的时候查找下一组键值
     * @param array        $arr
     * @param string|array $values
     * @return false|int|string
     */
    public static function findKey(array $arr, $values)
    {
        if (is_array($values)) {
            foreach ($values as $v) {
                if (($value = self::findKey($arr, $v)) !== false) {
                    return $value;
                }
            }
            return false;
        }
        return array_search($values, $arr);
    }
}
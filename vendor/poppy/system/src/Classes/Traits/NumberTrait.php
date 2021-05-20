<?php

namespace Poppy\System\Classes\Traits;

use Poppy\Framework\Classes\Number;
use Poppy\Framework\Exceptions\ArithmeticException;
use Throwable;

/**
 * Numbers Helpers
 */
trait NumberTrait
{
	/**
	 * 数值相加
	 * @param string $a 需要叠加的数据
	 * @param string $b 需要叠加的数据
	 * @return string
	 */
	public function numberAdd($a, $b): string
	{
		return (new Number($a))->add($b)->getValue();
	}

	/**
	 * 减法
	 * @param string $a 需要叠加的数据
	 * @param string $b 需要叠加的数据
	 * @return string
	 */
	public function numberSubtract($a, $b): string
	{
		return (new Number($a))->subtract($b)->getValue();
	}

	/**
	 * 乘法计算
	 * @param string $a 需要叠加的数据
	 * @param string $b 需要叠加的数据
	 * @return string
	 */
	public function numberMultiply($a, $b): string
	{
		return (new Number($a))->multiply($b)->getValue();
	}

	/**
	 * 除法
	 * @param float|string $a     除数
	 * @param float|string $b     被除数
	 * @param int          $scale 精度
	 * @return string
	 */
	public function numberDivide($a, $b, $scale = 2)
	{
		try {
			return (new Number($a, $scale))->divide($b)->getValue();
		} catch (ArithmeticException $e) {
			return '0.00';
		}
	}

	/**
	 * 计算费率
	 * @param string $amount   金额
	 * @param float  $fee_rate 费率
	 * @return string
	 */
	public function numberFee($amount, $fee_rate = 0.0): string
	{
		try {
			return (new Number($amount))->multiply($fee_rate)->divide(100)->getValue();
		} catch (Throwable $e) {
			return '0.00';
		}
	}

	/**
	 * 数值比较
	 *
	 * 返回值 0: 相等 , 1: a大于b; -1: a小于b
	 *
	 * @param float|int|string $a 数值a
	 * @param float|int|string $b 数值b
	 * @return int
	 */
	public function numberCompare($a, $b): int
	{
		return (new Number($a))->compareTo($b);
	}

	/**
	 * 获取字四舍五入串值
	 * Returns the current raw value of this BigNumber
	 *
	 * @param float|string|int $a 数值a
	 * @param int              $precision
	 * @return string String representation of the number in base 10
	 */
	public function numberRound($a, $precision = 0)
	{
		return (new Number($a))->round($precision)->getValue();
	}
}
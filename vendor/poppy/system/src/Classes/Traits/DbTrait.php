<?php

namespace Poppy\System\Classes\Traits;

use DB;
use Illuminate\Support\Str;

/**
 * Db Trait Db 工具
 */
trait DbTrait
{

	/**
	 * 更新数据库字段值
	 * @param string $table 数据表名称
	 * @param int    $id    ID
	 * @param string $field 更新字段
	 * @param string $val   更新值
	 * @return bool
	 */
	public function fieldVal(string $table, int $id, string $field, string $val)
	{
		$item = DB::table($table)->where('id', $id)->first();
		if (!$item) {
			return $this->setError('查无此数据');
		}
		DB::table($table)->where('id', $id)->update([
			$field => $val,
		]);

		return true;
	}

	/**
	 * 检查当前是否是在事务中
	 * @return bool
	 */
	protected function inTransaction(): bool
	{
		if (DB::transactionLevel() <= 0) {
			return $this->setError('当前操作未在事务中');
		}

		return true;
	}

	/**
	 * 启用 查询日志
	 */
	protected function enableQueryLog(): void
	{
		DB::enableQueryLog();
	}

	/**
	 * 获取SqlLog
	 * @return array
	 */
	protected function fetchQueryLog(): array
	{
		$logs = DB::getQueryLog();
		if (count($logs)) {
			$formats = [];
			foreach ($logs as $log) {
				$query = $log['query'];
				if (count($log['bindings'] ?? [])) {
					foreach ($log['bindings'] as $binding) {
						if (is_string($binding)) {
							$binding = '"' . $binding . '"';
						}
						$query = Str::replaceFirst('?', $binding, $query);
					}
				}
				$time      = $log['time'] ?? 0;
				$formats[] = [
					$query, $time,
				];
			}
			return $formats;
		}
		return $logs;
	}
}
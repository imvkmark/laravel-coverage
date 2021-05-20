<?php

namespace Poppy\System\Setting\Repository;

use DB;
use Exception;
use Illuminate\Support\Str;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Classes\Traits\KeyParserTrait;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Models\SysConfig;
use Throwable;

/**
 * system config
 * Setting Repository
 */
class SettingRepository implements SettingContract
{
    use KeyParserTrait, AppTrait, CoreTrait;

    /**
     * @var bool 检查是否存在这个数据表
     */
    private $hasTable = false;

    /**
     * @var bool 是否重新读取
     */
    private $reRead = false;

    /**
     * @var array 查询缓存
     */
    private static $cache;

    public function __construct()
    {
        $tableName = (new SysConfig())->getTable();

        static::$cache = (array) sys_cache('py-system')->get(PySystemDef::ckSetting());
        if (static::$cache) {
            $this->hasTable = true;
        }
        else {
            $hasDb   = py_container()->hasDatabase();
            $builder = DB::getSchemaBuilder();
            if ($hasDb && $builder->hasTable($tableName)) {
                $this->hasTable = true;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        if (!$this->keyParserMatch($key)) {
            return $this->setError(trans('py-system::util.setting.key_not_match', [
                'key' => $key,
            ]));
        }
        $record = $this->findRecord($key);
        if (!$record) {
            return false;
        }

        try {
            $record->delete();
        } catch (Exception $e) {
            return false;
        }

        unset(static::$cache[$key]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = '')
    {
        if ($this->reRead) {
            static::$cache = (array) sys_cache('py-system')->get(PySystemDef::ckSetting());
        }

        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        if (!$this->keyParserMatch($key)) {
            return $this->setError(trans('py-system::util.setting.key_not_match', [
                'key' => $key,
            ]));
        }

        if (!$this->hasTable) {
            return '';
        }

        $record = $this->findRecord($key);
        if (!$record) {
            // get default by setting.yaml
            $settingItem = $this->coreModule()->settings()->get($key);
            if ($settingItem) {
                $type           = $settingItem['type'] ?? 'string';
                $defaultSetting = $settingItem['default'] ?? '';
                switch ($type) {
                    case 'string':
                    default:
                        $default = $defaultSetting;
                        break;
                    case 'int':
                        $default = (int) $defaultSetting;
                        break;
                    case 'bool':
                    case 'boolean':
                        $default = (bool) $defaultSetting;
                        break;
                }
            }

            static::$cache[$key] = $default;

            // save to record
            $this->set($key, $default);

            return static::$cache[$key];
        }

        static::$cache[$key] = unserialize($record->value);

        $this->save();

        return static::$cache[$key];
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value = ''): bool
    {
        if (is_array($key)) {
            foreach ($key as $_key => $_value) {
                $this->set($_key, $_value);
            }

            return true;
        }

        if (!$this->keyParserMatch($key)) {
            return $this->setError(trans('py-system::util.setting.key_not_match', [
                'key' => $key,
            ]));
        }

        $record = $this->findRecord($key);
        if (!$record) {
            $record = new SysConfig();
            [$namespace, $group, $item] = $this->parseKey($key);
            $record->namespace = $namespace;
            $record->group     = $group;
            $record->item      = $item;
        }
        $record->value = serialize($value);
        $record->save();

        static::$cache[$key] = $value;
        // 写入缓存
        $this->save();

        return true;
    }

    /**
     * 根据命名空间从数据库中获取数据
     * @param string $ng 命名空间和分组
     * @return array
     */
    public function getNG(string $ng): array
    {
        [$ns, $group] = explode('::', $ng);
        if (!$ns || !$group) {
            return [];
        }
        $values = SysConfig::where('namespace', $ns)->where('group', $group)->select(['item', 'value'])->get();
        $data   = collect();
        $values->each(function ($item) use ($data) {
            $data->put($item['item'], unserialize($item['value']));
        });

        return $data->toArray();
    }

    /**
     * 删除命名空间以及分组
     * @param string $ng
     * @return bool
     */
    public function removeNG(string $ng): bool
    {
        if (!Str::contains($ng, '::')) {
            return false;
        }
        [$ns, $group] = explode('::', $ng);
        if (!$ns && !$group) {
            return false;
        }
        $Db     = SysConfig::where('namespace', $ns)->where('group', $group);
        $values = (clone $Db)->pluck('item');
        if ($values->count()) {
            $values->each(function ($item) use ($ns, $group) {
                unset(static::$cache["{$ns}::{$group}.{$item}"]);
            });
            $this->save();
            try {
                $Db->delete();
                return true;
            } catch (Throwable $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 保存配置
     */
    public function save(): void
    {
        sys_cache('py-system')->forever(PySystemDef::ckSetting(), static::$cache);
    }

    /**
     * 设置是否重新读取缓存
     * @param bool $reRead 标识
     */
    public function setReRead(bool $reRead): void
    {
        $this->reRead = $reRead;
    }

    /**
     * Returns a record (cached)
     * @param string $key 获取的key
     * @return SysConfig|null
     */
    private function findRecord(string $key): ?SysConfig
    {
        /** @var SysConfig $record */
        $record = SysConfig::query();

        return $record->applyKey($key)->first();
    }
}

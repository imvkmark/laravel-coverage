<?php

namespace Poppy\Framework\Application;

use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel;
use Poppy\Framework\Foundation\Application;
use Poppy\Framework\Helper\UtilHelper;

/**
 * Main Test Case
 */
class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        static $app;
        if (!$app) {
            $file         = __DIR__ . '/../../../../storage/bootstrap/app.php';
            $fileInVendor = __DIR__ . '/../../../../../storage/bootstrap/app.php';
            if (file_exists($file)) {
                $app = require_once $file;
            }
            elseif (file_exists($fileInVendor)) {
                $app = require_once $fileInVendor;
            }

            if ($app !== null) {
                $app->make(Kernel::class)->bootstrap();

                return $app;
            }
        }
        return $app;

    }

    /**
     * Run Vendor Test
     * @param array $vendors test here is must class
     */
    public function poppyTestVendor(array $vendors = []): void
    {
        collect($vendors)->each(function ($class, $package) {
            $this->assertTrue(class_exists($class), "Class `{$class}` is not exist, run `composer require {$package}` to install");
        });
    }

    /**
     * 返回当前容器
     * @return Container|Application
     * @deprecated 3.1
     * @removed    4.0
     */
    protected function poppyContainer()
    {
        return Container::getInstance();
    }

    /**
     * 输出变量
     * @param array|string $vars 需要输出的内容
     * @param string       $description
     */
    protected function outputVariables($vars, $description = '')
    {
        if ($description) {
            echo $description . ':' . PHP_EOL;
        }
        if (is_array($vars)) {
            var_export(json_encode($vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            echo PHP_EOL;
        }
        else {
            echo $vars . PHP_EOL;
        }
    }


    /**
     * 读取模块 Json 文件
     * @param $module
     * @param $path
     * @return array
     */
    protected function readJson($module, $path): array
    {
        $filePath = poppy_path($module, $path);
        if (file_exists($filePath)) {
            $config = file_get_contents($filePath);
            if (UtilHelper::isJson($config)) {
                return json_decode($config, true);
            }
            return [];
        }
        return [];
    }


    protected function faker(): Generator
    {
        return app(Generator::class);
    }
}
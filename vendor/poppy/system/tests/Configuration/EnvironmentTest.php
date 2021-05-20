<?php

namespace Poppy\System\Tests\Configuration;

/**
 * Copyright (C) Update For IDE
 */

use Poppy\System\Tests\Base\SystemTestCase;

class EnvironmentTest extends SystemTestCase
{
    /**
     * 配置项检测
     */
    public function testEnv()
    {
        $env = [
            'JWT_SECRET',
        ];

        foreach ($env as $_env) {
            if (!env($_env)) {
                $this->assertTrue(false, "Env {$_env} need to set");
            }
            else {
                $this->assertTrue(true);
            }
        }
    }

    public function testCommands()
    {
        $env = [
            'node',
            'apidoc',
        ];

        foreach ($env as $_env) {
            if (!command_exist($_env)) {
                $this->assertTrue(false, "Command {$_env} need to install");
            }
            else {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * 检查PHP 扩展
     */
    public function testPhp()
    {
        $env = [
            'gd',
            'json',
            'iconv',
            'mysqlnd',
            'mbstring',
            'bcmath',
        ];

        foreach ($env as $_env) {
            if (!extension_loaded($_env)) {
                $this->assertTrue(false, "Php extension {$_env} need to load");
            }
            else {
                $this->assertTrue(true);
            }
        }
    }
}

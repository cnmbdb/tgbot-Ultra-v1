#!/usr/bin/env php
<?php

/**
 * Hyperf Watch Hot Reload Scripts
 * From: https://github.com/ha-ni-cc/hyperf-watch
 * Author: hanicc@qq.com
 * Usage:
 * Open the terminal console in the project root directory and enter：php watch
 * 在项目根目录下打开终端控制台，输入：php watch
 * If you want to clean the /runtime/container cache, enter: php watch -c
 * 如果你想要清除/runtime/container缓存，则输入：php watch -c
 */

# PHP Bin File PHP程序所在路径（默认自动获取）
const PHP_BIN_FILE = 'which php';
# Watch Dir 监听目录（默认监听脚本所在的根目录）
const WATCH_DIR = __DIR__ . '/';
# Watch Ext 监听扩展名（多个可用英文逗号隔开）
const WATCH_EXT = 'php,env';
# Exclude Dir 排除目录（不监听的目录，数组形式)
const EXCLUDE_DIR = ['vendor', 'runtime', 'public'];
# Entry Point File 入口文件
const ENTRY_POINT_FILE = __DIR__ . '/bin/hyperf.php';
# Start Command 启动命令
const START_COMMAND = [ENTRY_POINT_FILE, 'start'];
# PID File Path PID文件路径
const PID_FILE_PATH = __DIR__ . '/runtime/hyperf.pid';
# Scan Interval 扫描间隔（毫秒，默认2000）
const SCAN_INTERVAL = 2000;
# Console Color 控制台颜色
const CONSOLE_COLOR_DEFAULT = "\033[0m";
const CONSOLE_COLOR_RED = "\033[0;31m";
const CONSOLE_COLOR_GREEN = "\033[0;32m";
const CONSOLE_COLOR_YELLOW = "\033[0;33m";
const CONSOLE_COLOR_BLUE = "\033[0;34m";

if (!function_exists('exec')) {
    echo '[x] 请在php.ini配置中取消禁用exec方法' . PHP_EOL;
    exit(1);
}

define('PHP', PHP_BIN_FILE == 'which php' ? exec('which php') : PHP_BIN_FILE);

if (!file_exists(PHP) || !is_executable(PHP)) {
    echo '[x] PHP bin (" ' . PHP . ' ") 路径没有找到或无法执行，请确认路径正确?' . PHP_EOL;
    exit(1);
}

if (!file_exists(ENTRY_POINT_FILE)) {
    echo '[x] 入口文件 ("' . ENTRY_POINT_FILE . '") 没有找到，请确认文件存在?' . PHP_EOL;
    exit(1);
}

# 加载env
$content = @file_get_contents('.env');
$values = array_filter(preg_split("/(\r\n|\n|\r)/", $content));
foreach ($values as $val) {
    if (substr($val, 0, 1) === '#') {
        continue;
    }
    list($name, $value) = explode('=', $val);
    $_ENV[$name] = $value;
}

use Swoole\Process;
use Swoole\Timer;
use Swoole\Event;

swoole_async_set(['enable_coroutine' => false, 'log_level' => SWOOLE_LOG_INFO]);
$hashes = [];
$serve = null;

echo CONSOLE_COLOR_YELLOW . "🚀 Start @ " . date('Y-m-d H:i:s') . PHP_EOL;
start();
state();
Timer::tick(SCAN_INTERVAL, 'watch');

function killOldProcess()
{
    // pid存在则关闭存在的进程
    if (file_exists(PID_FILE_PATH) && $pid = @file_get_contents(PID_FILE_PATH)) {
        if (!@posix_kill($pid)) forceKill();
    } else forceKill();
}

function forceKill($match = '')
{
    if (!$match) {
        $match = @$_ENV['APP_NAME'] . '.Master';
    }
    // 适配MacOS
    if (PHP_OS == 'Darwin') $match = ENTRY_POINT_FILE;
    $command = "ps -ef | grep '$match' | grep -v grep | awk '{print $2}' | xargs kill -9 2>&1";
    // 找不到pid，强杀进程
    exec($command);
}

function start()
{
    // 杀旧进程
    killOldProcess();
    global $serve;
    $serve = new Process('serve', true);
    $serve->start();
    if (false === $serve->pid) {
        echo swoole_strerror(swoole_errno()) . PHP_EOL;
        exit(1);
    }
    addEvent($serve);
}

function addEvent($serve)
{
    Event::add($serve->pipe, function () use (&$serve) {
        $message = @$serve->read();
        if (!empty($message)) {
            $debug = strpos($message, '[DEBUG]') !== false;
            $info = strpos($message, '[INFO]') !== false;
            $warn = strpos($message, '[WARNING]') !== false;
            $error = strpos($message, '[ERROR]') !== false;
            if ($debug) {
                echo CONSOLE_COLOR_BLUE . $message;
            } elseif ($info) {
                echo CONSOLE_COLOR_GREEN . $message;
            } elseif ($warn) {
                echo CONSOLE_COLOR_YELLOW . $message;
            } elseif ($error) {
                echo CONSOLE_COLOR_RED . $message;
            } else echo CONSOLE_COLOR_DEFAULT . $message;
            echo CONSOLE_COLOR_DEFAULT;
        }
    });
}

function watch()
{
    global $hashes;
    foreach ($hashes as $pathName => $currentHash) {
        if (!file_exists($pathName)) {
            unset($hashes[$pathName]);
            continue;
        }
        $newHash = fileHash($pathName);
        if ($newHash != $currentHash) {
            change();
            state();
            break;
        }
    }
}

function state()
{
    global $hashes;
    $files = phpFiles(WATCH_DIR);
    $hashes = array_combine($files, array_map('fileHash', $files));
    $count = count($hashes);
    echo CONSOLE_COLOR_YELLOW . "📡 Watching $count files..." . PHP_EOL;
}

function change()
{
    global $serve;
    echo CONSOLE_COLOR_YELLOW . "🔄 Restart @ " . date('Y-m-d H:i:s') . PHP_EOL;
    Process::kill($serve->pid);
    start();
}

function serve(Process $serve)
{
    $opt = getopt('c');
    # if (isset($opt['c'])) echo exec(PHP . ' ' . ENTRY_POINT_FILE . ' di:init-proxy') . '..' . PHP_EOL;
    if (isset($opt['c'])) delDir('./runtime/container');
    $serve->exec(PHP, START_COMMAND);
}

function fileHash(string $pathname): string
{
    $contents = file_get_contents($pathname);
    if (false === $contents) {
        return 'deleted';
    }
    return md5($contents);
}

function phpFiles(string $dirname): array
{
    $directory = new RecursiveDirectoryIterator($dirname);
    $filter = new Filter($directory);
    $iterator = new RecursiveIteratorIterator($filter);
    return array_map(function ($fileInfo) {
        return $fileInfo->getPathname();
    }, iterator_to_array($iterator));
}

function delDir($path)
{
    if (is_dir($path)) {
        //扫描一个目录内的所有目录和文件并返回数组
        $dirs = scandir($path);
        foreach ($dirs as $dir) {
            //排除目录中的当前目录(.)和上一级目录(..)
            if ($dir != '.' && $dir != '..') {
                //如果是目录则递归子目录，继续操作
                $sonDir = $path . '/' . $dir;
                if (is_dir($sonDir)) {
                    //递归删除
                    delDir($sonDir);
                    //目录内的子目录和文件删除后删除空目录
                    @rmdir($sonDir);
                } else {
                    //如果是文件直接删除
                    @unlink($sonDir);
                }
            }
        }
        @rmdir($path);
    }
}

class Filter extends RecursiveFilterIterator
{
    public function accept()
    {
        if ($this->current()->isDir()) {
            if (preg_match('/^\./', $this->current()->getFilename())) {
                return false;
            }
            return !in_array($this->current()->getFilename(), EXCLUDE_DIR);
        }
        $list = array_map(function (string $item): string {
            return "\.$item";
        }, explode(',', WATCH_EXT));
        $list = implode('|', $list);
        return preg_match("/($list)$/", $this->current()->getFilename());
    }
}
<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ClearPlugin extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('clearPlugin')
            ->addArgument('plugin', Argument::OPTIONAL, 'plugin name .')
            ->addOption('cache', 'c', Option::VALUE_NONE, 'clear cache file')
            ->addOption('log', 'l', Option::VALUE_NONE, 'clear log file')
            ->addOption('dir', 'r', Option::VALUE_NONE, 'clear empty dir')
            ->setDescription('Clear runtime file');
    }

    protected function execute(Input $input, Output $output)
    {
        $plugin       = $input->getArgument('plugin') ?: '';
        $runtimePath  = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . ($plugin ? $plugin . DIRECTORY_SEPARATOR : '');

        if ($input->getOption('cache')) {
            $path = $runtimePath . 'cache';
        } elseif ($input->getOption('log')) {
            $path = $runtimePath . 'log';
        } else {
            $path = $runtimePath;
        }

        $rmdir = (bool)$input->getOption('dir');
        $this->clear(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $rmdir);

        $output->writeln("<info>Clear Successed</info>");
    }

    protected function clear(string $path, bool $rmdir): void
    {
        $files = is_dir($path) ? scandir($path) : [];
        foreach ($files as $file) {
            if ('.' != $file && '..' != $file && is_dir($path . $file)) {
                array_map('unlink', glob($path . $file . DIRECTORY_SEPARATOR . '*.*'));
                if ($rmdir) {
                    rmdir($path . $file);
                }
            } elseif ('.gitignore' != $file && is_file($path . $file)) {
                unlink($path . $file);
            }
        }
    }
}
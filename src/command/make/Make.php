<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

abstract class Make extends Command
{

    protected $plugin;

    protected $type;

    abstract protected function getStub();

    protected function configure()
    {
        $this->addArgument('plugin', Argument::REQUIRED, "The name of the plugin");
        $this->addArgument('name',Argument::REQUIRED,'The name of the class');
    }

    protected function execute(Input $input, Output $output)
    {
        $plugin = trim($input->getArgument('plugin'));

        $this->plugin = $plugin;

        $name = trim($input->getArgument('name'));

        $classname = $this->getClassName($name);

        $pathname = $this->getPathName($classname);

        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ':' . $classname . ' already exists!</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));

        $output->writeln('<info>' . $this->type . ':' . $classname . ' created successfully.</info>');
    }

    protected function buildClass(string $name)
    {
        $stub = file_get_contents($this->getStub());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->getNamespace(),
        ], $stub);
    }

    protected function getPathName(string $name): string
    {
        $name = substr($name, 7);

        return $this->app->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . ltrim(str_replace('\\', '/', $name), '/') . '.php';
    }

    protected function getClassName(string $name): string
    {
        if (strpos($name, '\\') !== false) {
            return $name;
        }

        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->getNamespace() . '\\' . $name;
    }

    protected function getNamespace(): string
    {
        return 'plugin' . '\\' . $this->plugin;
    }
}
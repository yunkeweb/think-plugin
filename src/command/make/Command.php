<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

use think\console\input\Argument;

class Command extends Make
{
    protected $type = "Command";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:command')
            ->addArgument('commandName', Argument::OPTIONAL, "The name of the command")
            ->setDescription('Create a new command class');
    }

    protected function buildClass(string $name): string
    {
        $commandName = $this->input->getArgument('commandName') ?: strtolower(basename($name));
        $namespace   = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $class = str_replace($namespace . '\\', '', $name);
        $stub  = file_get_contents($this->getStub());

        return str_replace(['{%commandName%}', '{%className%}', '{%namespace%}', '{%app_namespace%}'], [
            $commandName,
            $class,
            $namespace,
            $this->getNamespace(),
        ], $stub);
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'command.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\command';
    }
}
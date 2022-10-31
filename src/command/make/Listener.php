<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Listener extends Make
{
    protected $type = "Listener";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:listener')
            ->setDescription('Create a new listener class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'listener.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\listener';
    }
}
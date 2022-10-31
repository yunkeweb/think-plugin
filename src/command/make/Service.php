<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Service extends Make
{
    protected $type = "Service";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:service')
            ->setDescription('Create a new Service class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'service.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\service';
    }
}
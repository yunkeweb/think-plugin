<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Middleware extends Make
{
    protected $type = "Middleware";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:middleware')
            ->setDescription('Create a new middleware class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'middleware.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\middleware';
    }
}
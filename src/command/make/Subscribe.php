<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Subscribe extends Make
{
    protected $type = "Subscribe";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:subscribe')
            ->setDescription('Create a new subscribe class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'subscribe.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\subscribe';
    }
}
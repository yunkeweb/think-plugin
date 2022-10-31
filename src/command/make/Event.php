<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Event extends Make
{
    protected $type = "Event";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:event')
            ->setDescription('Create a new event class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'event.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\event';
    }
}
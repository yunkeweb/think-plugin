<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Model extends Make
{
    protected $type = "Model";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:model')
            ->setDescription('Create a new model class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\model';
    }
}
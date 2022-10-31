<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command\make;

class Validate extends Make
{
    protected $type = "Validate";

    protected function configure()
    {
        parent::configure();
        $this->setName('plugin:validate')
            ->setDescription('Create a validate class');
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        return $stubPath . 'validate.stub';
    }

    protected function getNamespace(): string
    {
        return parent::getNamespace() . '\\validate';
    }
}
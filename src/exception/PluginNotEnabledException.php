<?php
declare (strict_types=1);

namespace yunkeweb\plugin\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

class PluginNotEnabledException extends RuntimeException implements NotFoundExceptionInterface
{
    protected $pluginName;

    public function __construct(string $message, string $pluginName = '', Throwable $previous = null)
    {
        $this->message = $message;
        $this->pluginName   = $pluginName;

        parent::__construct($message, 0, $previous);
    }

    /**
     * 获取插件名
     * @access public
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }
}
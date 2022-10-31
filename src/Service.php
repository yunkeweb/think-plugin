<?php
declare (strict_types=1);

namespace yunkeweb\plugin;

use think\Service as BaseService;
use yunkeweb\plugin\command\Build;
use yunkeweb\plugin\command\Clear;
use yunkeweb\plugin\command\make\Command;
use yunkeweb\plugin\command\make\Controller;
use yunkeweb\plugin\command\make\Event;
use yunkeweb\plugin\command\make\Listener;
use yunkeweb\plugin\command\make\Middleware;
use yunkeweb\plugin\command\make\Model;
use yunkeweb\plugin\command\make\Service as ServiceCommand;
use yunkeweb\plugin\command\make\Subscribe;
use yunkeweb\plugin\command\make\Validate;

class Service extends BaseService
{
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            $this->app->middleware->add(Plugin::class);
        });

        $this->commands([
            'plugin:build'   => Build::class,
            'plugin:clear'   => Clear::class,
            'plugin:command' => Command::class,
            'plugin:controller' => Controller::class,
            'plugin:event' => Event::class,
            'plugin:listener' => Listener::class,
            'plugin:middleware' => Middleware::class,
            'plugin:model' => Model::class,
            'plugin:service' => ServiceCommand::class,
            'plugin:subscribe' => Subscribe::class,
            'plugin:validate' => Validate::class,
        ]);
    }
}
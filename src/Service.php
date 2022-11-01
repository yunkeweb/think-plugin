<?php
declare (strict_types=1);

namespace yunkeweb\plugin;

use think\facade\Cache;
use think\facade\Config;
use think\Service as BaseService;
use yunkeweb\plugin\command\Build;
use yunkeweb\plugin\command\Clear;
use yunkeweb\plugin\command\make\Command;
use yunkeweb\plugin\command\make\Controller;
use yunkeweb\plugin\command\make\Event as EventCommand;
use yunkeweb\plugin\command\make\Listener;
use yunkeweb\plugin\command\make\Middleware;
use yunkeweb\plugin\command\make\Model;
use yunkeweb\plugin\command\make\Service as ServiceCommand;
use yunkeweb\plugin\command\make\Subscribe;
use yunkeweb\plugin\command\make\Validate;
use think\facade\Event;

class Service extends BaseService
{
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            $this->app->middleware->add(Plugin::class);
            if (Config::get('plugin.autoload',true)){
                LoadPlugin::loadEvent($this->app);
                LoadPlugin::bindProvider($this->app);
            }
        });
        $this->commands([
            'plugin:build'   => Build::class,
            'plugin:clear'   => Clear::class,
            'plugin:command' => Command::class,
            'plugin:controller' => Controller::class,
            'plugin:event' => EventCommand::class,
            'plugin:listener' => Listener::class,
            'plugin:middleware' => Middleware::class,
            'plugin:model' => Model::class,
            'plugin:service' => ServiceCommand::class,
            'plugin:subscribe' => Subscribe::class,
            'plugin:validate' => Validate::class,
        ]);
    }
}
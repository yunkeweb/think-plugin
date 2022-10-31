<?php
declare (strict_types=1);

namespace yunkeweb\plugin;

use think\facade\Cache;
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

        // 加载插件事件
        $this->loadEvent();
    }

    protected function loadEvent()
    {
        $hooks = $this->app->isDebug() ? [] : Cache::get('hooks', []);

        if (empty($hooks)){
            $hooks = [];
            $path = plugin_base_path();
            $plugins = glob($path . '*' , GLOB_ONLYDIR|GLOB_MARK);
            foreach ($plugins as $plugin){
                $pluginName = pathinfo($plugin)['basename'];
                // 过滤掉未启用的插件
                if (!isset(plugin_info($pluginName)['enabled']) || plugin_info($pluginName)['enabled'] !== true){
                    continue;
                }
                $deny = $this->app->config->get('plugin.deny_plugin_list', []);
                // 注册事件
                $file = $plugin . 'hook.php';
                if (is_file($file)){
                    $hook = include $file;
                    foreach ($hook as $key=>$listen){
                        if (is_array($listen)){
                            $hooks[$key] = array_merge($hooks[$key] ?? [],$listen);
                        }else{
                            $hooks[$key][] =  $listen;
                        }
                    }
                }
            }
            Cache::set('hooks',$hooks);
        }
        Event::listenEvents($hooks);
    }
}
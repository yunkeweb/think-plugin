<?php
declare (strict_types=1);

namespace yunkeweb\plugin;

use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Event;

class LoadPlugin
{
    public static function bindProvider(App $app)
    {
        $prefix = Config::get('plugin.cache_prefix','plugin_');

        $providers = $app->isDebug() ? [] : Cache::get($prefix.'providers', []);
        if (empty($providers)){
            $path = plugin_base_path();
            $plugins = glob($path . '*' , GLOB_ONLYDIR|GLOB_MARK);
            foreach ($plugins as $plugin){
                // 过滤掉无用目录
                $deny = $app->config->get('plugin.deny_plugin_list', []);
                if (in_array($plugin,$deny)){
                    continue;
                }
                $pluginName = pathinfo($plugin)['basename'];
                // 过滤掉未启用的插件
                if (!isset(plugin_info($pluginName)['enabled']) || plugin_info($pluginName)['enabled'] !== true){
                    continue;
                }
                // 加载服务
                $file = $plugin . 'global_provider.php';
                if (is_file($file)){
                    $providers = array_merge($providers,(array)include $file);
                }
            }
            Cache::set($prefix.'providers',$providers);
        }
        $app->bind($providers);
    }

    public static function loadEvent(App $app)
    {
        $prefix = Config::get('plugin.cache_prefix','plugin_');

        $hooks = $app->isDebug() ? [] : Cache::get($prefix.'hooks', []);

        if (empty($hooks)){
            $path = plugin_base_path();
            $plugins = glob($path . '*' , GLOB_ONLYDIR|GLOB_MARK);
            foreach ($plugins as $plugin){
                $pluginName = pathinfo($plugin)['basename'];
                // 过滤掉无用目录
                $deny = $app->config->get('plugin.deny_plugin_list', []);
                if (in_array($pluginName,$deny)){
                    continue;
                }
                // 过滤掉未启用的插件
                if (!isset(plugin_info($pluginName)['enabled']) || plugin_info($pluginName)['enabled'] !== true){
                    continue;
                }
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
            Cache::set($prefix.'hooks',$hooks);
        }
        Event::listenEvents($hooks);
    }
}
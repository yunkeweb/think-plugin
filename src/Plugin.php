<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace yunkeweb\plugin;

use Closure;
use think\App;
use think\facade\Event;
use think\Request;
use think\Response;

/**
 * 多应用模式支持
 */
class Plugin
{

    /** @var App */
    protected $app;

    /**
     * 插件名称
     * @var string
     */
    protected $name;

    /**
     * 插件名称
     * @var string
     */
    protected $pluginName;

    /**
     * 插件路径
     * @var string
     */
    protected $pluginPath;

    /**
     * 插件根目录
     * @var string
     */
    protected $basePath;

    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->basePath = $app->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR;
    }

    /**
     * 插件解析
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->parsePlugin()) {
            return $next($request);
        }

        return $this->app->middleware->pipeline('plugin')
            ->send($request)
            ->then(function ($request) use ($next) {
                return $next($request);
            });
    }

    /**
     * 解析插件
     * @return bool
     */
    protected function parsePlugin(): bool
    {
        $path = $this->app->request->pathinfo();

        $pathArr = explode('/', $path);

        $pluginDir = current($pathArr);

        if ($pluginDir != "plugin"){
            return false;
        }

        $pluginName = next($pathArr);

        if ($pluginName) {
            // 检测插件是否禁止访问
            $deny = $this->app->config->get('plugin.deny_plugin_list', []);
            if (in_array($pluginName,$deny)){
                return false;
            }

            // 检测插件是否启用
            if (!isset(plugin_info($pluginName)['enabled']) || plugin_info($pluginName)['enabled'] !== true){
                return false;
            }
            // 设置插件路径
            $this->pluginPath = $this->getBasePath() . $pluginName . DIRECTORY_SEPARATOR;
            $this->pluginName = $pluginName;

            // 检测插件是否存在
            if (!is_dir($this->pluginPath)){
                return false;
            }
            $this->app->request->pluginName = $pluginName;
            // 插件开始
            Event::trigger('PluginBegin',$pluginName);
            $this->app->request->setRoot('/plugin/' . $pluginName);
            $path = strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '';
            $this->app->request->setPathinfo(strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '');
        }else{
            return false;
        }
        $this->setPlugin();
        Event::trigger('PluginEnd',$pluginName);
        return true;
    }

    protected function getPluginPath(): string
    {
        return $this->pluginPath;
    }

    /**
     * 获取插件路由目录
     * @return string
     */
    protected function getRoutePath(): string
    {
        return $this->getPluginPath() . 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取插件根目录
     * @return string
     */

    protected function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * 获取解析的插件名
     * @return string
     */
    protected function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * 设置应用
     */
    protected function setPlugin(): void
    {
        $pluginName = $this->getPluginName();
        // 设置应用命名空间
        $this->app->setNamespace('plugin\\' . $pluginName);
        $this->app->setRuntimePath($this->app->getRuntimePath() . 'plugin' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR);
        $this->app->http->setRoutePath($this->getRoutePath());
        //加载插件
        $this->loadPlugin();
    }

    /**
     * 加载应用文件
     * @param string $pluginPath
     * @return void
     */
    protected function loadPlugin(): void
    {
        $pluginPath = $this->getPluginPath();

        if (is_file($pluginPath . 'common.php')) {
            include_once $pluginPath . 'common.php';
        }

        $files = [];

        $files = array_merge($files, glob($pluginPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));

        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }
        // 加载插件级事件
        if (is_file($pluginPath . 'event.php')) {
            $this->app->loadEvent(include $pluginPath . 'event.php');
        }
        // 加载插件级中间件
        if (is_file($pluginPath . 'middleware.php')) {
            $this->app->middleware->import(include $pluginPath . 'middleware.php', 'plugin');
        }
        // 加载插件级服务者
        if (is_file($pluginPath . 'provider.php')) {
            $this->app->bind(include $pluginPath . 'provider.php');
        }

        // 加载应用默认语言包
        $this->app->loadLangPack();
    }

}

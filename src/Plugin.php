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
use think\Exception;
use think\exception\HttpException;
use think\Request;
use think\Response;
use yunkeweb\plugin\exception\PluginNotFoundException;
use yunkeweb\plugin\exception\PluginNotEnabledException;

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
     * 应用路径
     * @var string
     */
    protected $path;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    /**
     * 多应用解析
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

        return $this->app->middleware->pipeline('app')
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

        $name = next($pathArr);

        if ($name) {
            $pluginName = $name;

            // 检测插件是否存在
            if (!$this->checkPlugin($pluginName)){
               throw new PluginNotFoundException('plugin not found',$pluginName);
            }

            // 检测插件是否禁止访问
            $deny = $this->app->config->get('plugin.deny_plugin_list', []);
            if (in_array($pluginName,$deny)){
                throw new HttpException(404, 'plugin not exists:' . $name);
            }

            // 检测插件是否启用
            if (!isset(plugin_info($pluginName)['enabled']) || plugin_info($pluginName)['enabled'] !== true){
                throw new PluginNotEnabledException('plugin not enabled',$pluginName);
            }

            $this->app->request->setRoot('/' . $name);
            $path = strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '';
            $this->app->request->setPathinfo(strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '');
        }else{
            return false;
        }
        $this->setApp($pluginName);
        return true;
    }

    protected function getPluginPath($pluginName): string
    {
        return $this->getBasePath() . $pluginName . DIRECTORY_SEPARATOR;
    }

    //检测插件是否存在
    protected function checkPlugin($name): bool
    {
        return is_dir($this->getBasePath() . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * 获取路由目录
     * @access protected
     * @return string
     */
    protected function getRoutePath(): string
    {
        return $this->app->getAppPath() . 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取根目录
     * @return string
     */

    public function getBasePath(): string
    {
        return $this->app->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR;
    }

    /**
     * 设置应用
     * @param string $pluginName
     */
    protected function setApp(string $pluginName): void
    {
        $this->appName = $pluginName;
        $this->app->http->name($pluginName);

        $pluginPath = $this->getBasePath() . $pluginName . DIRECTORY_SEPARATOR;

        $this->app->setAppPath($pluginPath);
        // 设置应用命名空间
        $this->app->setNamespace('plugin\\' . $pluginName);

        if (is_dir($pluginPath)) {
            $this->app->setRuntimePath($this->app->getRuntimePath() . 'plugin' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR);
            $this->app->http->setRoutePath($this->getRoutePath());

            //加载应用
            $this->loadApp($pluginName, $pluginPath);
        }
    }

    /**
     * 加载应用文件
     * @param string $pluginName 插件名
     * @return void
     */
    protected function loadApp(string $pluginName, string $pluginPath): void
    {
        if (is_file($pluginPath . 'common.php')) {
            include_once $pluginPath . 'common.php';
        }

        $files = [];

        $files = array_merge($files, glob($pluginPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));

        foreach ($files as $file) {
            $this->app->config->load($file, 'plugin_'.$pluginName.'_'.pathinfo($file, PATHINFO_FILENAME));
        }

        if (is_file($pluginPath . 'event.php')) {
            $this->app->loadEvent(include $pluginPath . 'event.php');
        }

        if (is_file($pluginPath . 'middleware.php')) {
            $this->app->middleware->import(include $pluginPath . 'middleware.php', 'app');
        }

        if (is_file($pluginPath . 'provider.php')) {
            $this->app->bind(include $pluginPath . 'provider.php');
        }

        // 加载应用默认语言包
        $this->app->loadLangPack($this->app->lang->defaultLangSet());
    }

}

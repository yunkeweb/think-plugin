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
use think\exception\HttpException;
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
     * 应用名称
     * @var string
     */
    protected $name;

    /**
     * 应用名称
     * @var string
     */
    protected $appName;

    /**
     * 应用路径
     * @var string
     */
    protected $path;

    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->name = $this->app->http->getName();
        $this->path = $this->app->http->getPath();
    }

    /**
     * 多应用解析
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
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
     * 解析多应用
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
            $appName = $name;
            $this->app->request->setRoot('/' . $name);
            $path = strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '';
            $this->app->request->setPathinfo(strpos($path, '/') ? ltrim(strstr($path, '/'), '/') : '');
        }else{
            return false;
        }
        $this->setApp($appName);
        return true;
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

    public function getBasePath()
    {
        return $this->app->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR;
    }

    /**
     * 设置应用
     * @param string $appName
     */
    protected function setApp(string $appName): void
    {
        $this->appName = $appName;
        $this->app->http->name($appName);

        $appPath = $this->path ?: $this->getBasePath() . $appName . DIRECTORY_SEPARATOR;

        $this->app->setAppPath($appPath);
        // 设置应用命名空间
        $this->app->setNamespace('plugin\\' . $appName);

        if (is_dir($appPath)) {
            $this->app->setRuntimePath($this->app->getRuntimePath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR);
            $this->app->http->setRoutePath($this->getRoutePath());

            //加载应用
            $this->loadApp($appName, $appPath);
        }
    }

    /**
     * 加载应用文件
     * @param string $appName 应用名
     * @return void
     */
    protected function loadApp(string $appName, string $appPath): void
    {
        if (is_file($appPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }

        $files = [];

        $files = array_merge($files, glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));

        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }

        if (is_file($appPath . 'event.php')) {
            $this->app->loadEvent(include $appPath . 'event.php');
        }

        if (is_file($appPath . 'middleware.php')) {
            $this->app->middleware->import(include $appPath . 'middleware.php', 'app');
        }

        if (is_file($appPath . 'provider.php')) {
            $this->app->bind(include $appPath . 'provider.php');
        }

        // 加载应用默认语言包
        $this->app->loadLangPack($this->app->lang->defaultLangSet());
    }

}

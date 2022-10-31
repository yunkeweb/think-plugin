<?php
declare (strict_types=1);

if (!function_exists('plugin_config')){
    /**
     * 获取插件配置信息
     * @param $fileName
     * @param $appName
     * @return array|mixed
     */
    function plugin_config($fileName,$appName = null)
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->config->get('plugin_' . $appName . '_' . $fileName);
    }
}

if (!function_exists('plugin_path')){
    /**
     * 获取插件目录
     * @param $appName
     * @return string
     */
    function plugin_path($appName = null): string
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_info')){
    /**
     * 获取插件信息
     * @param $appName
     * @return mixed|null
     */
    function plugin_info($appName = null)
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        $info = app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .'info' . app()->getConfigExt();
        if (is_file($info)){
            return include $info;
        }
        return null;
    }
}

if (!function_exists('plugin_base_path')){
    /**
     * 获取插件基础目录
     * @return string
     */
    function plugin_base_path(): string
    {
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_config_path')){
    /**
     * 获取插件配置目录
     * @param null $appName
     * @return string
     */
    function plugin_config_path($appName = null): string
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_runtime_path')){
    /**
     * 获取插件运行时目录
     * @param $appName
     * @return string
     */
    function plugin_runtime_path($appName = null): string
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR;
    }
}

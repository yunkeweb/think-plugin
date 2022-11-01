<?php
declare (strict_types=1);

if (!function_exists('plugin_path')){
    /**
     * 获取插件目录
     * @param string $appName
     * @return string
     */
    function plugin_path(string $appName): string
    {
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_info')){
    /**
     * 获取插件信息
     * @param string $appName
     * @return array
     */
    function plugin_info(string $appName) : array
    {
        $info = app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config.json';
        if (is_file($info)){
            return json_decode(file_get_contents($info),true);
        }
        return [];
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
     * @param string $appName
     * @return string
     */
    function plugin_config_path(string $appName): string
    {
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_runtime_path')){
    /**
     * 获取插件运行时目录
     * @param string $appName
     * @return string
     */
    function plugin_runtime_path(string $appName): string
    {
        return app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR;
    }
}

<?php
declare (strict_types=1);

if (!function_exists('plugin_config')){
    function plugin_config($fileName,$appName = null)
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->config->get('plugin_' . $appName . '_' . $fileName);
    }
}

if (!function_exists('plugin_path')){
    function plugin_path($appName = null)
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
        return app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('plugin_info')){
    function plugin_info($appName = null)
    {
        if ($appName === null){
            $appName = app('http')->getName();
        }
       return include app()->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .'info.php';
    }
}

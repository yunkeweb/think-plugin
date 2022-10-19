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

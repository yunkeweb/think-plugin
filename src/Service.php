<?php
declare (strict_types=1);

namespace yunkeweb\plugin;

use think\App;
use think\Request;
use think\Service as BaseService;

class Service extends BaseService
{
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            $this->app->middleware->add(Plugin::class);
        });
    }
}
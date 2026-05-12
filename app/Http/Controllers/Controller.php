<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    public function getServiceObj($serviceName, $module = null )
    {
        $module = $module ? ucfirst($module) : app('current.module');
        $serviceName = ucfirst($serviceName);
        $class = "\Modules\\{$module}\Services\\{$serviceName}Service";
        return new $class();
    }
}

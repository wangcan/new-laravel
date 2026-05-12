<?php

namespace Modules\Scraper\Services;

class DealHtmlService
{
    public function createTable($bigSort, $sort)
    {
        $path = module_path(app('current.module'), 'database/table-datas/' . $bigSort . '.php');
        $datas = require($path);
        $data = $datas[$sort] ?? [];
        print_r($data);
        var_dump($path);
        exit();

    }
}

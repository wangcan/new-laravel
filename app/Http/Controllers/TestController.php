<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    use \App\Http\Controllers\Traits\TestMongoDB;

    public function test(Request $request)
    {
        $inTest = config('app.inTest');
        if (empty($inTest)) {
            //return $this->error(400, '非法请求');
        }
        $method = ucfirst($request->input('method', ''));
        $method = "_test{$method}";
        return $this->$method($request);
        exit();
    }
}

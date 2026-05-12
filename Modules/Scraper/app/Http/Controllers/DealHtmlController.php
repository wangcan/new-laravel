<?php

namespace Modules\Scraper\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealHtmlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createTable(Request $request)
    {
        $dealHtml = $this->getServiceObj('dealHtml');
        $bigSort = $request->input('big-sort');
        $sort = $request->input('sort');
        $dealHtml->createTable($bigSort, $sort);
        return view('scraper::index');
    }
}

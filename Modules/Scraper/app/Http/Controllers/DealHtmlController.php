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
        return view('scraper::index');
    }
}

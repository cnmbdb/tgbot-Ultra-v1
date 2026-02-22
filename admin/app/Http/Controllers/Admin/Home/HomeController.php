<?php

namespace App\Http\Controllers\Admin\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Test;

class HomeController extends Controller
{
    public function index()
    {
        return view('admin.home.index');
    }
}

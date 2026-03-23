<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

class ApiDocController extends Controller
{
    public function index()
    {
        return view('pages.system.api-docs');
    }
}

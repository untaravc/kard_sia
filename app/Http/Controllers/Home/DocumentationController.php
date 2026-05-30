<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;

class DocumentationController extends Controller
{
    public function index()
    {
        return view('docs.index');
    }
}

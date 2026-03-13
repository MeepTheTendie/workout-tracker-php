<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrsController extends Controller
{
    public function __construct()
    {
        // Middleware applied in routes
    }

    public function index()
    {
        return view('prs.index');
    }
}

<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function home(Request $request)
    {
        return view('admin/home');
    }
}
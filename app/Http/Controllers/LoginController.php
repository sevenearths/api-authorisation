<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function login(Request $request)
    {
        if($request->session()->has('user_logged_in')) {
            return redirect()->route('home');
        }
        return view('admin/login');
    }

    public function loginPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:10',
            'password' => 'required|min:10',
        ]);

        if ($validator->fails()) {

            return view('admin/login')->withErrors($validator);

        } else {

            if(
                Input::get('username') != env('ADMIN_USER') ||
                Hash::check(Input::get('password'), env('ADMIN_PASS')) == false
            ) {
                $validator->errors()->add('username', 'The Username or Password is Incorrect');
                return view('admin/login')->withErrors($validator);
            }
        }

        $request->session()->set('user_logged_in', true);
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_logged_in');
        return redirect()->route('login');
    }
}
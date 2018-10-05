<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

class UserLoggedInCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->session()->has('user_logged_in')) {
            return $next($request);
        } else {
            $validator = Validator::make($request->all(),[]);
            $validator->errors()->add('username', 'You need to login first');
            return redirect()->route('login')->withErrors($validator);
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

// This class needs to be created
use ApiResponse\ApiResponseClass;

class UserLoggedInAjaxCheck
{
    private $response;

    public function __construct(ApiResponseClass $apiResponseClass)
    {
        $this->response = $apiResponseClass;
    }

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
            return $this->response->unauthorised('not logged in');
        }
    }
}

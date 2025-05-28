<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class Localization
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
        // $availableLangs  = array('en', 'hu', 'pt', 'ro', 'sv');
        // $userLangs = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

    if (\Session::has('locale'))
    {
        \App::setlocale(\Session::get('locale'));
    }
    // else if (in_array($userLangs, $availableLangs))
    // {
    //     \App::setLocale($userLangs);
    //   // Session::push('locale', $userLangs);
    // }
    // else{
    //     \App::setLocale($userLangs);
    // }
    return $next($request);
    }
}

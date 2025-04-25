<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->segment(3));

//        if($request->secure()) {
//            return redirect($request->path());
//        }
        // чтобы не передавать locale при каждом испоьзовании route()
        // URL::defaults(['locale' => $request->segment(3)]);

        return $next($request);
    }
}

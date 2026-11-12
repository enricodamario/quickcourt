<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const BAHASA = ['en', 'id'];

    public function handle(Request $request, Closure $next): Response
    {
        $bahasa = $request->session()->get('locale', config('app.locale'));

        if (in_array($bahasa, self::BAHASA, true)) {
            App::setLocale($bahasa);
            Carbon::setLocale($bahasa);
        }

        return $next($request);
    }
}
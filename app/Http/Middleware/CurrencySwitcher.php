<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class CurrencySwitcher
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableCurrencies = array_keys(config('app.currencies', []));
        $currency = Session::get('currency', config('app.default_currency'));

        if (in_array($currency, $availableCurrencies)) {
            session(['currency' => $currency]);
        } else {
            session(['currency' => config('app.default_currency')]);
        }

        return $next($request);
    }
}

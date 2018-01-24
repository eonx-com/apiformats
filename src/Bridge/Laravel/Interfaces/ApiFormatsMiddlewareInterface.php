<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Interfaces;

use Illuminate\Http\Request;

interface ApiFormatsMiddlewareInterface
{
    /**
     * Handle incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next);
}

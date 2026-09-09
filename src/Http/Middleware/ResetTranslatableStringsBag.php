<?php

namespace Wotz\FilamentAdminBar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Wotz\FilamentAdminBar\Translator;

/**
 * Starts each page view with an empty list of resolved strings.
 *
 * The bag has to accumulate across one page view *and every sub-request that
 * page makes* — a lazy Livewire component, a deferred island, an ajax fragment
 * — because those are exactly the strings that used to go missing. So it
 * cannot be cleared per request.
 *
 * A full document GET is the boundary that means "a new page": not a Livewire
 * update, not a JSON fetch, not a POST that redirects back.
 */
class ResetTranslatableStringsBag
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->startsAPageView($request)) {
            session()->forget(Translator::BAG);
        }

        return $next($request);
    }

    protected function startsAPageView(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->hasHeader('X-Livewire') || $request->ajax() || $request->wantsJson()) {
            return false;
        }

        return $request->acceptsHtml();
    }
}

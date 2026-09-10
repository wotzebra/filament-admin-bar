<?php

namespace Wotz\FilamentAdminBar\Support;

/**
 * Where one page view begins.
 *
 * A full document GET. Not a Livewire update, not a JSON fetch, not a POST
 * that redirects back — those are all the *same* page still happening, and the
 * bar's whole job is to describe a page rather than a request.
 */
class PageView
{
    public static function isStarting(): bool
    {
        $request = request();

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->hasHeader('X-Livewire') || $request->ajax() || $request->wantsJson()) {
            return false;
        }

        return $request->acceptsHtml();
    }
}

<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;
use Wotz\FilamentRedirects\Models\Redirect;

/**
 * Only on a 404, where the offer is worth making.
 *
 * The moment you find a broken link is the moment you want to fix it, and it is
 * the only moment you have the old URL in front of you. On every other page
 * this tab would be a permanent reminder of a job nobody is doing.
 */
class RedirectsTab extends Tab
{
    public string $name = 'Redirect';

    /**
     * Set from the error view, before the layout renders the bar.
     */
    protected static bool $notFound = false;

    public static function markNotFound(): void
    {
        static::$notFound = true;
    }

    public static function isNotFound(): bool
    {
        return static::$notFound;
    }

    public function canSee(): bool
    {
        return static::$notFound && class_exists(Redirect::class);
    }

    public function render(): View
    {
        return view('filament-admin-bar::tabs.redirects', [
            'from' => '/' . ltrim(request()->getRequestUri(), '/'),
            'existing' => class_exists(Redirect::class)
                ? Redirect::query()->where('from', request()->path())->first()
                : null,
        ]);
    }
}

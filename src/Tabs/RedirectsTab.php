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

    /**
     * Whether the page *was* a 404 has to be remembered, like everything else
     * here: the request that returned 404 is over by the time the tab renders.
     */
    public function capture(): void
    {
        $this->remember([
            'not_found' => static::$notFound,
            'from' => '/' . ltrim(request()->getRequestUri(), '/'),
            'path' => request()->path(),
        ]);
    }

    public function canSee(): bool
    {
        return ($this->recall(['not_found' => false])['not_found'] ?? false)
            && class_exists(Redirect::class);
    }

    public function render(): View
    {
        $captured = $this->recall(['from' => '', 'path' => '']);

        return view('filament-admin-bar::tabs.redirects', [
            'from' => $captured['from'] ?? '',
            'existing' => class_exists(Redirect::class)
                ? Redirect::query()->where('from', $captured['path'] ?? '')->first()
                : null,
        ]);
    }
}

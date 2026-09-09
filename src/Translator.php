<?php

namespace Wotz\FilamentAdminBar;

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator
{
    /**
     * Where the keys resolved during this page view are collected.
     */
    public const BAG = 'translatable-strings';

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $translation = parent::get($key, $replace, $locale, $fallback);

        $this->record($key);

        return $translation;
    }

    /**
     * Remember that this key was asked for.
     *
     * Three things this deliberately does not do.
     *
     * It does not record the translation. The tab looks the value up from the
     * database anyway, and writing every string on the page into the session
     * on every request — for every visitor — is a lot of session for nothing.
     *
     * It does not run for a visitor who is not signed in to the panel. Only an
     * admin ever sees the bar, so only an admin's page view is worth
     * collecting; without this the session of every anonymous visitor grows
     * for a tab they will never open.
     *
     * And it stores the key as a literal array key rather than through dot
     * notation. `session()->put("bag.{$key}", …)` nests on every dot in the
     * key, so `foo` and `foo.bar` collide: the first becomes a string where
     * the second needs an array, and one of them is lost.
     */
    protected function record(string $key): void
    {
        if (! $this->panelUserIsSignedIn()) {
            return;
        }

        $excluded = config('filament-admin-bar.translatable-strings-tab.excluded', []);

        if (collect($excluded)->contains(fn ($exclude) => Str::is($exclude, $key))) {
            return;
        }

        $bag = session()->get(self::BAG, []);

        if (array_key_exists($key, $bag)) {
            return;
        }

        $bag[$key] = true;

        session()->put(self::BAG, $bag);
    }

    /**
     * Asking Filament who is signed in throws when no panel is registered, and
     * this runs on every translation lookup there is — including the ones in a
     * console command or a queued job, where there is no panel and no session
     * either. Anything other than a definite yes means "do not record".
     */
    protected function panelUserIsSignedIn(): bool
    {
        try {
            return Filament::auth()->check();
        } catch (\Throwable) {
            return false;
        }
    }
}

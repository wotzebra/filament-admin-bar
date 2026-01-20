<?php

namespace Wotz\FilamentAdminBar;

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator
{
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $translation = parent::get($key, $replace, $locale, $fallback);
        $excluded = config('filament-admin-bar.translatable-strings-tab.excluded', []);

        // Don't show Filament strings in the admin bar when returning to the front-end
        if (! collect($excluded)->contains(fn ($exclude) => Str::is($exclude, $key))) {
            session()->put("translatable-strings.$key", $translation);
        }

        return $translation;
    }
}

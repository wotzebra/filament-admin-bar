<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;
use Wotz\TranslatableStrings\Models\TranslatableString;

class TranslatableStringsTab extends Tab
{
    public string $name = 'Translatable strings';

    public function canSee(): bool
    {
        return class_exists(TranslatableString::class);
    }

    public function render(): View
    {
        return view('filament-admin-bar::tabs.translatable-strings');
    }
}

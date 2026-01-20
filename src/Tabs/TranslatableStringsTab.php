<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;

class TranslatableStringsTab extends Tab
{
    public string $name = 'Translatable strings';

    public function render(): View
    {
        return view('filament-admin-bar::tabs.translatable-strings');
    }
}

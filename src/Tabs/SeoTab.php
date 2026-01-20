<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;
use Wotz\Seo\Facades\SeoBuilder;

class SeoTab extends Tab
{
    public string $name = 'SEO';

    public function render(): View
    {
        return view('filament-admin-bar::tabs.seo', [
            'data' => SeoBuilder::contents(),
        ]);
    }
}

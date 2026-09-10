<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;
use Wotz\Seo\Facades\SeoBuilder;

class SeoTab extends Tab
{
    public string $name = 'SEO';

    /**
     * The SEO builder is filled by the frontend route's middleware, so it holds
     * this page's tags only while this page's request is running.
     */
    public function capture(): void
    {
        $this->remember(SeoBuilder::contents());
    }

    public function render(): View
    {
        return view('filament-admin-bar::tabs.seo', [
            'data' => $this->recall([]),
        ]);
    }
}

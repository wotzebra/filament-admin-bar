<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;
use Wotz\FilamentAdminBar\Support\PageMedia;

/**
 * The images rendered on this page, with the one thing about them an editor
 * cannot see by looking: whether they have alt text.
 */
class MediaTab extends Tab
{
    public string $name = 'Media';

    public function canSee(): bool
    {
        return app(PageMedia::class)->all()->isNotEmpty();
    }

    public function render(): View
    {
        return view('filament-admin-bar::tabs.media', [
            'attachments' => app(PageMedia::class)->all(),
        ]);
    }
}

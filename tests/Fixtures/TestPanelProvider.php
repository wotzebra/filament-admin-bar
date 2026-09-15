<?php

namespace Wotz\FilamentAdminBar\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;

/**
 * A panel for the bar to belong to. Half of what this package does is only
 * true for somebody signed in to one.
 */
class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin');
    }
}

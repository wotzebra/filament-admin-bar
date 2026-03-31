<?php

use Wotz\FilamentAdminBar\Tabs\SeoTab;
use Wotz\FilamentAdminBar\Tabs\TranslatableStringsTab;

return [
    'tabs' => [
        SeoTab::class,
        TranslatableStringsTab::class,
    ],
    'translatable-strings-tab' => [
        'excluded' => [
            'filament-admin-bar::*',
            'routes.*',
        ],
    ],
    'filament-guard' => null,
];

<?php

return [
    'tabs' => [
        Wotz\FilamentAdminBar\Tabs\SeoTab::class,
        Wotz\FilamentAdminBar\Tabs\TranslatableStringsTab::class,
    ],
    'translatable-strings-tab' => [
        'excluded' => [
            'filament-admin-bar::*',
            'routes.*',
        ],
    ],
    'filament-guard' => null,
];

# Admin bar for filament

## Introduction

This package will add an admin bar to the frontend, only visible for logged in Filament users.

![img.png](img.png)

## Installation

You can install the package via composer:

```bash
composer require wotz/filament-admin-bar
```

DO NOT forget to publish the CSS files, or the bar will not be visible:

```bash
php artisan vendor:publish --force --tag=filament-admin-bar-assets
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-admin-bar-config"
```

This is the contents of the published config file:

```php
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
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-admin-bar-views"
```

## Usage

Load our TranslationLoader in your `config/app.php`. This way we can show translations in the admin bar.

```php
return [
    // ...
    'providers' => ServiceProvider::defaultProviders()->merge([
        // ...
        Wotz\FilamentAdminBar\TranslationLoader::class,
        // ...
    ])->toArray(),
    // ...
];
```

After that load the admin bar at the bottom of `<body>` your layout:

```blade
<livewire:admin-bar />
```

That's all

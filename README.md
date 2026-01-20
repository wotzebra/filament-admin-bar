# Admin bar for filament

This package will add an admin bar to the frontend, only visible for logged in Filament users.

![img.png](docs/img.png)

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

```blade
<livewire:admin-bar />
```

## Documentation

For the full documentation, check [here](./docs/index.md).

## Testing

```bash
vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Upgrading

Please see [UPGRADING](UPGRADING.md) for more information on how to upgrade to a new version.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email info@whoownsthezebra.be instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

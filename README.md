# Admin bar for filament

This package will add an admin bar to the frontend, only visible for logged in Filament users.

![img.png](docs/img.png)

## Tabs

Five tabs ship with the package, and each decides for itself whether it has
anything to show:

| Tab | Shows |
|---|---|
| SEO | What the page reports to search engines |
| Translatable strings | The copy on this page, editable in place |
| Media | The images on this page, and which of them have no alt text |
| Records | The CMS records behind this page, linked to their edit screens |
| Redirect | Only on a 404: create a redirect from the URL that just failed |

Only the active tab is rendered. Listing a tab therefore costs what it costs
when somebody opens it, not on every frontend request an admin makes.

## Telling the bar which record a page is

Detail pages need no wiring: anything bound to the route is picked up, so the
"Edit page in CMS" action and the Records tab work on them out of the box.

Index and home pages resolve their record some other way, so they say so —
usually from the one place an application already funnels them through:

```php
admin_bar_record($staticPage);          // this page contains it
admin_bar_record($vacancy, primary: true); // this page *is* it
```

The header action opens the primary record. Everything else is a click away in
the Records tab.

## Sitting above something else

The bar pins itself to the bottom edge, closed and open — and so does
everything else that wants to be permanent: a dev toolbar, a cookie strip, a
sticky basket. It measures that edge and climbs over whatever it finds there,
re-measuring when it changes, so nothing needs configuring for the usual case.
Laravel Debugbar is a full-width strip in one state and a corner button in
another; both are handled.

`offset_bottom` sets a floor, in pixels, for something measurement cannot see.

## Telling the bar which images a page draws

The Media tab lists the images the page loaded and flags the ones with no alt
text. A page draws them from a great many templates and from one model, so the
model is where to say it:

```php
Attachment::retrieved(fn (Attachment $attachment) => admin_bar_media($attachment));
```

Nothing is collected outside a page view, so a queued job that walks the whole
library costs nothing.

## The redirect tab

It only appears on a 404, which is the one moment the old URL is in front of
you. Your error view has to say so before the layout renders the bar:

```blade
@php
    \Wotz\FilamentAdminBar\Tabs\RedirectsTab::markNotFound();
@endphp
```

## Editable strings

The package ships a `Translator` that records which keys a page resolved, so
the Translatable strings tab knows what to offer. `TranslationLoader` swaps it
in for Laravel's own — register it in `bootstrap/providers.php`, before
anything that resolves a translation:

```php
return [
    // …
    Wotz\FilamentAdminBar\TranslationLoader::class,
];
```

Keys are collected for the whole page view, including the lazy Livewire
components and deferred islands it renders afterwards, and the list starts empty
on the next page. Nothing is recorded for a visitor who is not signed in to the
panel.

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

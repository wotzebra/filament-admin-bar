<?php

namespace Wotz\FilamentAdminBar\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\FilamentAdminBar\Livewire as Components;
use Wotz\FilamentAdminBar\Support\PageMedia;
use Wotz\FilamentAdminBar\Support\PageRecords;
use Wotz\TranslatableStrings\Models\TranslatableString;

use function Livewire\on;

class FilamentAdminBarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-admin-bar')
            ->setBasePath(__DIR__ . '/../')
            ->hasViews()
            ->hasConfigFile();
    }

    public function registeringPackage(): void
    {
        // `hasHelpers()` is not in this version of laravel-package-tools, and
        // requiring the file directly is what it would have done anyway.
        require_once __DIR__ . '/../helpers.php';

        // One answer per page view to "which record is this page?", so the
        // collector is scoped to the request rather than resolved fresh.
        $this->app->scoped(PageRecords::class);
        $this->app->scoped(PageMedia::class);
    }

    public function bootingPackage()
    {
        Livewire::component('admin-bar', Components\AdminBar::class);

        if (class_exists(TranslatableString::class)) {
            Livewire::component('translatable-strings-tab', Components\TranslatableStringsTab::class);
        }

        $this->detachFromThePageMiddleware();

        FilamentAsset::register([
            Css::make('filament-admin-bar', __DIR__ . '/../../resources/dist/assets/filament-admin-bar.css'),
        ], 'wotz/filament-admin-bar');
    }

    /**
     * Keep the bar working on a page that failed.
     *
     * Livewire remembers which path a component was drawn on and replays that
     * page's route middleware on every update, route model bindings included.
     * On a 404 the binding it replays is the one that could not be resolved in
     * the first place, so it throws again and the update endpoint answers 404.
     * No tab switches — on the one page the Redirect tab exists for.
     *
     * The bar is not part of the page it sits on. It binds no route models and
     * checks the panel guard itself on every render, so it has nothing to gain
     * from that replay and a whole page's worth of failures to inherit.
     * Dehydrating it against the root leaves nothing to replay: a path with no
     * segments cannot carry a binding, and where the host has no root route at
     * all Livewire finds no middleware and skips the step.
     *
     * Registered once everything else has booted, so this runs after the
     * listener it is correcting.
     */
    protected function detachFromThePageMiddleware(): void
    {
        $this->app->booted(function (): void {
            on('dehydrate', function ($component, $context): void {
                if (! str_starts_with($component::class, 'Wotz\\FilamentAdminBar\\')) {
                    return;
                }

                $context->addMemo('path', '');
            });
        });
    }
}

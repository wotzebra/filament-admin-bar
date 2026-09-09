<?php

namespace Wotz\FilamentAdminBar\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Http\Kernel;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\FilamentAdminBar\Http\Middleware\ResetTranslatableStringsBag;
use Wotz\FilamentAdminBar\Livewire as Components;
use Wotz\FilamentAdminBar\Support\PageMedia;
use Wotz\FilamentAdminBar\Support\PageRecords;
use Wotz\TranslatableStrings\Models\TranslatableString;

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
        /*
         * The bag of resolved strings has to survive every sub-request a page
         * makes — a lazy component, a deferred island — and start empty on the
         * next page. Pushed onto the `web` group rather than left to the host
         * application to remember, because a bar that silently shows the
         * previous page's strings is worse than one that shows none.
         */
        $this->app->booted(function (): void {
            $this->app->make(Kernel::class)->appendMiddlewareToGroup('web', ResetTranslatableStringsBag::class);
        });

        Livewire::component('admin-bar', Components\AdminBar::class);

        if (class_exists(TranslatableString::class)) {
            Livewire::component('translatable-strings-tab', Components\TranslatableStringsTab::class);
        }

        FilamentAsset::register([
            Css::make('filament-admin-bar', __DIR__ . '/../../resources/dist/assets/filament-admin-bar.css'),
        ], 'wotz/filament-admin-bar');
    }
}

<?php

namespace Wotz\FilamentAdminBar\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\FilamentAdminBar\Livewire as Components;

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

    public function bootingPackage()
    {
        Livewire::component('admin-bar', Components\AdminBar::class);
        Livewire::component('translatable-strings-tab', Components\TranslatableStringsTab::class);

        FilamentAsset::register([
            Css::make('filament-admin-bar', __DIR__ . '/../../resources/dist/assets/filament-admin-bar.css'),
        ], 'wotz/filament-admin-bar');
    }
}

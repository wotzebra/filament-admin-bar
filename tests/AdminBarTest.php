<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Livewire\Livewire;
use Wotz\FilamentAdminBar\Livewire\AdminBar;
use Wotz\FilamentAdminBar\Tabs\Tab;

class FirstTab extends Tab
{
    public string $name = 'First';

    public function render(): View
    {
        return view('admin-bar-tests::tab', ['name' => $this->name]);
    }
}

class SecondTab extends FirstTab
{
    public string $name = 'Second';
}

/**
 * Only a panel page is offered on a 404, so which tab is open cannot simply be
 * whatever was open last.
 */
class OnlyOnSomePagesTab extends FirstTab
{
    public string $name = 'Sometimes';

    public function canSee(): bool
    {
        return false;
    }
}

function signInToPanel(): void
{
    ViewFacade::addNamespace('admin-bar-tests', __DIR__ . '/Fixtures/views');

    Filament::auth()->setUser(new class extends User
    {
        protected $table = 'users';

        public $id = 1;
    });

    config()->set('filament-admin-bar.tabs', [FirstTab::class, SecondTab::class]);
}

it('opens on a tab this page actually has', function () {
    signInToPanel();

    // Left over from a 404, where the Redirect tab was open. It does not exist
    // here, and the bar used to open on an empty body with nothing selected.
    session(['filament-admin-bar.current' => (new OnlyOnSomePagesTab)->key()]);

    Livewire::test(AdminBar::class)->assertSet('current', (new FirstTab)->key());
});

it('keeps the tab that was open when the page offers it', function () {
    signInToPanel();

    session(['filament-admin-bar.current' => (new SecondTab)->key()]);

    Livewire::test(AdminBar::class)->assertSet('current', (new SecondTab)->key());
});

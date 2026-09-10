<?php

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Livewire\Livewire;
use Wotz\FilamentAdminBar\Livewire\AdminBar;
use Wotz\FilamentAdminBar\Support\PageRecords;
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

it('keeps the edit action after a tab is clicked', function () {
    signInToPanel();

    app(PageRecords::class)->register(new class extends Model
    {
        protected $table = 'things';

        public $exists = true;

        public function getKey()
        {
            return 1;
        }
    }, primary: true);

    config()->set('filament-admin-bar.edit_page_url', fn (): string => '/admin/static-pages/31/edit');

    $component = Livewire::test(AdminBar::class)
        ->assertSet('editUrl', '/admin/static-pages/31/edit');

    // Clicking a tab is a different request, with an empty collector. The
    // action used to disappear from the header on the first click.
    app()->forgetInstance(PageRecords::class);

    $component->call('changeTab', (new SecondTab)->key())
        ->assertSet('editUrl', '/admin/static-pages/31/edit');
});

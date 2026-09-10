<?php

namespace Wotz\FilamentAdminBar\Livewire;

use Filament\Facades\Filament;
use Livewire\Component;
use Wotz\FilamentAdminBar\Support\PageRecords;
use Wotz\FilamentAdminBar\Support\PageView;
use Wotz\FilamentAdminBar\Tabs\Tab;

class AdminBar extends Component
{
    public ?string $current = null;

    public function render()
    {
        if (config('filament-admin-bar.filament-guard') && ! auth()->guard(config('filament-admin-bar.filament-guard'))->check()) {
            return '<div></div>';
        } elseif (! Filament::auth()->check()) {
            return '<div></div>';
        }

        $tabs = collect(config('filament-admin-bar.tabs', []))
            ->map(fn (string $tab) => new $tab);

        /*
         * Tabs render one at a time, and a tab renders when somebody clicks it
         * — a Livewire POST, a different request from the one that drew the
         * page. Anything a tab reads out of the request itself is gone by then.
         * So every tab, active or not, takes its copy of the page here, while
         * the page is still the thing happening.
         */
        if (PageView::isStarting()) {
            $tabs->each(fn (Tab $tab) => $tab->capture());
        }

        $tabs = $tabs->filter(fn (Tab $tab) => $tab->canSee())->values();

        if ($tabs->isEmpty()) {
            return '';
        }

        $this->current ??= session('filament-admin-bar.current');

        /*
         * Which tab is open is remembered across pages, and not every page
         * offers the same tabs — the Redirect tab only exists on a 404. Coming
         * off one of those, the remembered tab is not here, and the bar opened
         * on an empty body with nothing selected.
         */
        if (! $tabs->contains(fn (Tab $tab): bool => $tab->key() === $this->current)) {
            $this->current = $tabs->first()->key();
        }

        return view('filament-admin-bar::livewire.admin-bar', [
            'tabs' => $tabs,
            'activeTab' => $tabs->first(fn (Tab $tab) => $tab->key() === $this->current),
            'editUrl' => $this->editUrl(),
        ]);
    }

    /**
     * Where to edit the record this page *is*.
     *
     * The header has room for one action, and a detail page resolves two
     * records — the item and the index page it sits under. The item wins:
     * somebody who followed a link to a vacancy and pressed edit means the
     * vacancy. The index page is a click away in the Records tab.
     */
    protected function editUrl(): ?string
    {
        $closure = config('filament-admin-bar.edit_page_url');

        $records = app(PageRecords::class);
        $record = $records->primary();

        if ($closure instanceof \Closure) {
            return $closure($record);
        }

        return $record === null ? null : $records->editUrl($record);
    }

    public function changeTab(string $tab): void
    {
        $this->current = $tab;
        session(['filament-admin-bar.current' => $this->current]);
    }
}

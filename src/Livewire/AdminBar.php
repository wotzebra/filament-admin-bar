<?php

namespace Wotz\FilamentAdminBar\Livewire;

use Filament\Facades\Filament;
use Livewire\Component;
use Wotz\FilamentAdminBar\Support\PageRecords;
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
            ->map(fn (string $tab) => new $tab)
            ->filter(fn (Tab $tab) => $tab->canSee());

        if ($tabs->isEmpty()) {
            return '';
        }

        if (! $this->current) {
            $this->current = session(
                'filament-admin-bar.current',
                $tabs->first(default: null)?->key()
            );
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

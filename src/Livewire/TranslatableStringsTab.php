<?php

namespace Wotz\FilamentAdminBar\Livewire;

use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Wotz\FilamentAdminBar\Translator;
use Wotz\TranslatableStrings\ExportToLang;
use Wotz\TranslatableStrings\Models\TranslatableString;

class TranslatableStringsTab extends Component
{
    public ?string $message = null;

    public array $fields = [];

    public string $query = '';

    public function mount(): void
    {
        $this->setFields();
    }

    public function render()
    {
        return view('filament-admin-bar::livewire.translatable-strings-tab', [
            'strings' => $this->strings(),
        ]);
    }

    /**
     * Save, and make the result true on the next render rather than in a
     * minute.
     *
     * `TranslatableString::booted()` dispatches a *queued* export per updated
     * row, and that job writes the `lang/` files the translator actually
     * reads — which is where "please wait a minute" came from. On an
     * application whose default queue also carries other work, the wait was
     * real and unbounded.
     *
     * Exporting once per scope, synchronously, is a handful of file writes.
     * The page is then re-rendered from the server, so the new value is
     * correct by construction instead of patched into the DOM.
     */
    public function submit(ExportToLang $exporter): void
    {
        $data = collect($this->fields)
            ->reject(fn ($value) => $value === '_HTML_')
            ->dot();

        $saved = TranslatableString::query()
            ->whereIn('id', $data->keys())
            ->get();

        $saved->each(fn ($string) => $string->update([
            'value' => $data->get($string->id),
        ]));

        $saved->pluck('scope')
            ->unique()
            ->filter()
            ->each(fn (string $scope) => $exporter->export($scope));

        $this->message = null;

        // A re-render rather than a reload: the bar keeps its open state and
        // its height from localStorage, and the server renders the new value.
        $this->dispatch('admin-bar-strings-saved');
    }

    public function updatedQuery(): void
    {
        $this->setFields();
    }

    /**
     * The strings this page has resolved, asked again on every render.
     *
     * It used to be read once in `mount()` with `session()->pull()` — read
     * *and forget*. Three things followed. Copy inside a lazy component or a
     * deferred island resolved after mount and was missing. The pull emptied
     * the bag, so a string translated late on page A surfaced in the bar on
     * page B, one navigation later. And the key list was frozen in component
     * state, so nothing that arrived afterwards could ever join it.
     */
    #[On('admin-bar-tab-opened')]
    public function setFields(): void
    {
        $this->fields = $this->strings()
            // Don't send HTML to the frontend: link-picker will break other
            // Livewire components on the page.
            ->mapWithKeys(fn ($string) => [$string->id => ($string->is_html ? '_HTML_' : $string->value)])
            ->toArray();
    }

    /**
     * @return Collection<int, TranslatableString>
     */
    protected function strings(): Collection
    {
        $keys = array_keys(session()->get(Translator::BAG, []));

        if ($keys === []) {
            return collect();
        }

        return TranslatableString::query()
            ->whereIn('key', $keys)
            ->when($this->query, fn ($query) => $query
                ->where(fn ($query) => $query
                    ->where('key', 'like', "%{$this->query}%")
                    ->orWhere('value', 'like', "%{$this->query}%")
                )
            )
            ->get();
    }
}

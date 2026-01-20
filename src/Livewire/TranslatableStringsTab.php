<?php

namespace Wotz\FilamentAdminBar\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Wotz\TranslatableStrings\Models\TranslatableString;

class TranslatableStringsTab extends Component
{
    public Collection $session;

    public ?string $message = null;

    public array $fields = [];

    public string $query = '';

    public function mount()
    {
        $this->session = Collection::wrap(
            session()->pull('translatable-strings', [])
        )->dot()->keys();

        $this->setFields();
    }

    public function render()
    {
        return view('filament-admin-bar::livewire.translatable-strings-tab', [
            'strings' => $this->strings(),
        ]);
    }

    public function submit()
    {
        $data = collect($this->fields)
            ->reject(fn ($value) => $value === '_HTML_')
            ->dot();

        TranslatableString::query()
            ->whereIn('id', $data->keys())
            ->get()
            ->each(fn ($string) => $string->update([
                'value' => $data->get($string->id),
            ]));

        $this->message = 'The new strings are being saved, please wait a minute for them to be available.';
    }

    public function updatedQuery()
    {
        $this->setFields();
    }

    private function strings()
    {
        return TranslatableString::query()
            ->whereIn('key', $this->session)
            ->when($this->query, fn ($query) => $query
                ->where('key', 'like', "%{$this->query}%")
                ->orWhere('value', 'like', "%{$this->query}%")
            )
            ->get();
    }

    private function setFields()
    {
        // Don't send HTML to the frontend, link-picker will break other Livewire components on the page
        $this->fields = $this->strings()
            ->mapWithKeys(fn ($string) => [$string->id => ($string->is_html ? '_HTML_' : $string->value)])
            ->toArray();
    }
}

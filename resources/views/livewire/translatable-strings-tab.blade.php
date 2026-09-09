<div
    x-data
    @admin-bar-strings-saved.window="Livewire.navigate(window.location.href)"
>
    <div class="flex items-center gap-3 mb-3 flex-wrap">
        <label data-admin-bar-search>
            <x-heroicon-o-magnifying-glass class="w-4.5 h-4.5 shrink-0" />
            <input
                type="text"
                placeholder="Search strings on this page"
                wire:model.live.debounce.1000ms="query"
            >
        </label>

        <span style="font-size: 13px; color: var(--faint)">
            {{ trans_choice('{0}No strings on this page|{1}1 string on this page|[2,*]:count strings on this page', $strings->count(), ['count' => $strings->count()]) }}
        </span>

        {{-- Copy that renders after this tab mounted — a lazy component, a
             deferred island — joins the list on the next look. --}}
        <button type="button" data-admin-bar-link wire:click="setFields">
            <x-heroicon-o-arrow-path class="w-4 h-4" />
            Refresh
        </button>
    </div>

    <div wire:loading>
        <div data-admin-bar-card>
            <div class="flex items-center gap-3 p-4">
                <x-heroicon-o-arrow-path class="w-4.5 h-4.5" data-admin-bar-spinner />
                <span style="font-size: 15px; color: var(--muted)">Loading translatable strings…</span>
            </div>

            <div data-admin-bar-skeleton>
                @for ($row = 0; $row < 4; $row++)
                    <span></span><span></span>
                @endfor
            </div>
        </div>
    </div>

    <div wire:loading.remove>
        @if ($message)
            <div data-admin-bar-notice>
                <x-heroicon-o-check-circle class="w-4.5 h-4.5 shrink-0" />
                {{ $message }}
            </div>
        @endif

        @if ($strings->count())
            <div data-admin-bar-card>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 300px">Name</th>
                            <th>Translation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($strings as $string)
                            <tr wire:key="{{ $string->id }}_string_row">
                                <td data-key style="vertical-align: middle; padding: 10px 16px">
                                    {{ $string->key }}
                                </td>

                                <td style="vertical-align: middle; padding: 10px 16px">
                                    @if ($string->is_html)
                                        <a
                                            data-admin-bar-link
                                            href="/admin/translatable-strings/{{ $string->id }}/edit?locale=-{{ app()->getLocale() }}-tab"
                                            target="_blank"
                                        >
                                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                            Edit rich text in CMS
                                        </a>
                                    @else
                                        <input type="text" wire:model.defer="fields.{{ $string->id }}">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="button" data-admin-bar-submit wire:click="submit">
                    Save translations
                </button>

                <span style="font-size: 13px; color: var(--faint)">
                    Editing {{ strtoupper(app()->getLocale()) }}
                </span>
            </div>
        @else
            <div data-admin-bar-card>
                <div data-admin-bar-empty>
                    <h3>No translatable strings found</h3>
                    <p>
                        @if ($query)
                            Nothing on this page matches “{{ $query }}”. Clear the search to see
                            everything this page resolved.
                        @else
                            Nothing on this page is editable as a string yet.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

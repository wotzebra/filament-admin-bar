<div>
    <div class="mb-3">
        <label class="group relative flex items-center md:w-1/2">
            <span
                class="
                    absolute inset-y-0 left-3 m-auto
                    flex items-center justify-center h-9 w-9
                    text-gray-400
                    group-focus-within:text-teal-500
                "
            >
                <x-heroicon-o-magnifying-glass class="w-7 h-7" />
            </span>
            <input
                type="text"
                wire:model.live.debounce.1000ms="query"
                class="w-full py-1 px-4.5 pl-14"
            >
        </label>
    </div>

    <div wire:loading>
        <div class="flex items-center gap-2 w-full">
            <x-heroicon-o-arrow-path class="w-8 h-8 animate-spin" />
            Loading...
        </div>
    </div>

    <div wire:loading.remove>
        @if ($message)
            <div class="flex items-center gap-2 w-full mb-3">
                <x-heroicon-o-check-circle class="w-8 h-8 text-green-400" />
                {{ $message }}
            </div>
        @endif

        @if ($strings->count() ?? false)
            <div class="rounded-2xl border border-gray-300 bg-white overflow-auto">
                <table class="w-full">
                    <tr class="bg-gray-100">
                        <th class="py-1.5 px-3 text-left font-medium text-gray-600 min-w-104">Name</th>
                        <th class="py-1.5 px-3 text-left font-medium text-gray-600 min-w-104">Translation</th>
                    </tr>
                    @foreach ($strings as $string)
                        <tr class="border-t border-gray-300">
                            <td class="py-1.5 px-3">
                                {{ $string->key }}
                            </td>

                            <td class="py-1.5 px-3">
                                @if ($string->is_html)
                                    <a
                                        class="
                                            filament-link filament-tables-link-action
                                            inline-flex items-center justify-center gap-1
                                            font-medium text-teal-600
                                            outline-hidden no-underline focus:underline
                                            hover:underline hover:text-teal-500
                                        "
                                        href="/admin/translatable-strings/{{ $string->id }}/edit?locale=-{{ app()->getLocale() }}-tab"
                                        target="_blank"
                                    >
                                        <x-heroicon-o-pencil-square class="filament-link-icon w-8 h-8"/>
                                        Edit in CMS
                                    </a>
                                @else
                                    <input
                                        class="w-full"
                                        type="text"
                                        wire:model.defer="fields.{{ $string->id }}"
                                    >
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <button
                class="
                    inline-flex items-center justify-center gap-1
                    py-1 px-4 min-h-9 mt-4
                    font-medium text-white
                    rounded-xl border shadow border-transparent bg-teal-600
                    transition-colors
                    outline-hidden focus:ring-offset-2 focus:ring-2 focus:ring-inset focus:ring-white
                    focus:bg-teal-700 focus:ring-offset-teal-700
                    hover:bg-teal-500
                "
                wire:click="submit"
            >
                Save translations
            </button>
        @else
            <div class="flex items-center gap-2 w-full">
                <x-heroicon-o-x-circle class="w-8 h-8" />
                No translatable strings found
            </div>
        @endif
    </div>
</div>

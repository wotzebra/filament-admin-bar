@php
    use Wotz\FilamentAdminBar\Support\PageMedia;
@endphp

<div data-admin-bar-card>
    <table>
        <thead>
            <tr>
                <th style="width: 120px"></th>
                <th style="width: 260px">Name</th>
                <th>Alt text</th>
                <th style="width: 120px">Size</th>
                <th style="width: 90px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attachments as $attachment)
                <tr>
                    <td>
                        @if (filled($attachment->url ?? null))
                            <img data-admin-bar-thumb src="{{ $attachment->url }}" alt="" />
                        @else
                            <div data-admin-bar-thumb data-admin-bar-thumb-empty></div>
                        @endif
                    </td>
                    <td data-key>{{ $attachment->name }}</td>
                    <td>
                        @if (PageMedia::lacksAltText($attachment))
                            {{-- The one thing about an image an editor cannot
                                 see by looking at the page. --}}
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; color: color-mix(in oklab, var(--hue-warning) 70%, #181614)">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                Missing
                            </span>
                        @else
                            {{ $attachment->alt }}
                        @endif
                    </td>
                    <td style="color: var(--muted)">
                        {{ $attachment->width ? "{$attachment->width}×{$attachment->height}" : '—' }}
                    </td>
                    <td style="text-align: right">
                        @if ($url = $editUrl($attachment))
                            {{-- Where the alt text is written. Missing alt is the whole
                                 point of this tab, so the fix is one click from it. --}}
                            <a data-admin-bar-link href="{{ $url }}" target="_blank">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                Edit
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

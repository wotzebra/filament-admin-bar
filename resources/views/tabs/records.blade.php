@if ($records->isEmpty())
    <div data-admin-bar-card>
        <div data-admin-bar-empty>
            <h3>Nothing on this page has a CMS record</h3>
            <p>This page is built from something other than content you can edit here.</p>
        </div>
    </div>
@else
    <div data-admin-bar-card>
        <table>
            <thead>
                <tr>
                    <th style="width: 300px">What</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record['label'] }}</td>
                        <td style="color: var(--muted)">{{ $record['type'] }}</td>
                        <td style="text-align: right">
                            @if ($record['url'])
                                <a data-admin-bar-link href="{{ $record['url'] }}" target="_blank">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                    Edit
                                </a>
                            @else
                                {{-- No link rather than a link that will refuse them. --}}
                                <span style="color: var(--faint)">No access</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($total > $limit)
        <p style="margin-top: 12px; font-size: 13px; color: var(--faint)">
            Showing {{ $limit }} of {{ $total }}.
        </p>
    @endif
@endif

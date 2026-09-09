<div data-admin-bar-card>
    <table>
        <thead>
            <tr>
                <th style="width: 220px">Name</th>
                <th>Content</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $value)
                <tr>
                    <td data-key>{{ $key }}</td>

                    @if (Str::contains($key, 'image'))
                        <td>
                            @if (filled($value))
                                <div class="flex items-center gap-3">
                                    <img data-admin-bar-thumb src="{{ $value }}" alt="" />
                                    <span style="font-family: var(--mono); font-size: 12px; color: var(--faint)">
                                        {{ basename(parse_url($value, PHP_URL_PATH) ?? '') }}
                                    </span>
                                </div>
                            @else
                                {{-- A striped placeholder rather than a broken image. --}}
                                <div data-admin-bar-thumb data-admin-bar-thumb-empty></div>
                            @endif
                        </td>
                    @else
                        <td>{{ $value }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

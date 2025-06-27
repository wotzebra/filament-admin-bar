<div class="rounded-2xl border border-gray-300 bg-white overflow-auto">
    <table class="w-full">
        <tr class="bg-gray-100">
            <th class="py-1.5 px-3 text-left font-medium text-gray-600 min-w-104">Name</th>
            <th class="py-1.5 px-3 text-left font-medium text-gray-600 min-w-104">Content</th>
        </tr>
        @foreach ($data as $key => $value)
            <tr class="border-t border-gray-300">
                <td class="py-1.5 px-3">{{ $key }}</td>
                @if (Str::contains($key, 'image'))
                    <td class="py-1.5 px-3">
                        <img
                            class="
                                min-w-40 max-w-40 min-h-40 max-h-40 object-cover
                                rounded-lg shadow-slate-300/50
                            "
                            src="{{ $value }}"
                        />
                    </td>
                @else
                    <td class="py-1.5 px-3 font-light">{{ $value }}</td>
                @endif
            </tr>
        @endforeach
    </table>
</div>

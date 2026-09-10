<div data-admin-bar-card>
    @if ($existing)
        <div style="display: flex; align-items: flex-start; gap: 10px; padding: 16px">
            <x-heroicon-o-exclamation-triangle class="w-4.5 h-4.5 shrink-0" style="color: var(--hue-warning)" />
            <span style="font-size: 15px; line-height: 1.5">
                A redirect from <code>{{ $from }}</code> already exists,
                pointing at <code>{{ $existing->to }}</code> — and this
                page still 404s, so something downstream of it is broken.
            </span>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 14px; padding: 16px">
            <div style="display: flex; align-items: flex-start; gap: 10px">
                <x-heroicon-o-link-slash class="w-4.5 h-4.5 shrink-0" style="color: var(--faint)" />
                <span style="font-size: 15px; line-height: 1.5">
                    Nothing lives at <code>{{ $from }}</code>.
                    If it used to, send visitors somewhere sensible — the closest page, not the homepage.
                </span>
            </div>

            <a
                data-admin-bar-submit
                style="align-self: flex-start; text-decoration: none; display: inline-flex; align-items: center; gap: 8px"
                href="{{ config('filament-admin-bar.redirects-tab.create-url', '/admin/redirects') }}"
                target="_blank"
            >
                <x-heroicon-o-plus class="w-4 h-4" />
                Create a redirect
            </a>
        </div>
    @endif
</div>

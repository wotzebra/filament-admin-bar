<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;

abstract class Tab
{
    public string $name;

    abstract public function render(): View;

    public function name(): string
    {
        return $this->name;
    }

    public function key(): string
    {
        return md5(get_class($this));
    }

    public function canSee(): bool
    {
        return true;
    }

    /**
     * Take whatever this tab needs from the page request, while it is still
     * happening.
     *
     * Tabs render one at a time, and a tab is rendered when somebody clicks it
     * — which is a Livewire POST, a different request from the one that drew
     * the page. Anything the tab reads out of the request itself is gone by
     * then: the SEO builder is filled by the frontend route's middleware, and
     * the media and record collectors are filled while the page renders.
     *
     * So a tab that describes the page takes its copy here, on the page view,
     * and renders from that afterwards. Called for every tab, active or not,
     * so it must stay cheap — store ids, not objects.
     */
    public function capture(): void
    {
        //
    }

    /**
     * Where this tab keeps what it captured.
     */
    protected function snapshotKey(): string
    {
        return 'filament-admin-bar.snapshot.' . $this->key();
    }

    protected function remember(mixed $value): void
    {
        session()->put($this->snapshotKey(), $value);
    }

    protected function recall(mixed $default = null): mixed
    {
        return session()->get($this->snapshotKey(), $default);
    }
}

<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Wotz\FilamentAdminBar\Support\PageMedia;

/**
 * The images rendered on this page, with the one thing about them an editor
 * cannot see by looking: whether they have alt text.
 */
class MediaTab extends Tab
{
    public string $name = 'Media';

    /**
     * Ids rather than models: this runs on every page view whether the tab is
     * opened or not, and a serialised Eloquent model in the session is a lot to
     * carry for a tab nobody may click.
     */
    public function capture(): void
    {
        $this->remember(
            app(PageMedia::class)->all()
                ->map(fn (Model $attachment): array => [
                    'type' => $attachment->getMorphClass(),
                    'id' => (string) $attachment->getKey(),
                ])
                ->all(),
        );
    }

    public function canSee(): bool
    {
        return filled($this->recall([]));
    }

    public function render(): View
    {
        return view('filament-admin-bar::tabs.media', [
            'attachments' => $this->attachments(),
        ]);
    }

    /**
     * @return Collection<int, Model>
     */
    protected function attachments(): Collection
    {
        return collect($this->recall([]))
            ->groupBy('type')
            ->flatMap(function ($rows, string $type) {
                if (! class_exists($type)) {
                    return [];
                }

                return $type::query()->whereIn((new $type)->getKeyName(), collect($rows)->pluck('id'))->get();
            })
            ->values();
    }
}

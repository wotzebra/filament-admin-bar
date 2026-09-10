<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Wotz\FilamentAdminBar\Support\PageRecords;

/**
 * The CMS records behind the page being looked at.
 *
 * On a detail page that is one record and the index it sits under; on an index
 * page it is the list of everything shown, which turns a category page into a
 * jump list of the records on it.
 */
class RecordsTab extends Tab
{
    public string $name = 'Records';

    /**
     * A long index page can show a hundred records, and a hundred links is not
     * a jump list, it is the page again.
     */
    public const LIMIT = 25;

    /**
     * The collector is filled while the page renders, so the copy is taken then
     * — by the time somebody clicks this tab, the request that knew is over.
     */
    public function capture(): void
    {
        $this->remember(
            app(PageRecords::class)->all()
                ->take(self::LIMIT * 2)
                ->map(fn (Model $record): array => [
                    'type' => $record->getMorphClass(),
                    'id' => (string) $record->getKey(),
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
        $collector = app(PageRecords::class);
        $records = $this->records();

        return view('filament-admin-bar::tabs.records', [
            'records' => $records
                ->take(self::LIMIT)
                ->map(fn (Model $record): array => [
                    'label' => static::label($record),
                    'type' => class_basename($record),
                    'url' => $collector->editUrl($record),
                ]),
            'total' => $records->count(),
            'limit' => self::LIMIT,
        ]);
    }

    /**
     * @return Collection<int, Model>
     */
    protected function records(): Collection
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

    protected static function label(Model $record): string
    {
        foreach (['working_title', 'name', 'title', 'label'] as $attribute) {
            $value = $record->{$attribute} ?? null;

            if (is_string($value) && filled($value)) {
                return $value;
            }
        }

        return '#' . $record->getKey();
    }
}

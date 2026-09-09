<?php

namespace Wotz\FilamentAdminBar\Tabs;

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

    public function canSee(): bool
    {
        return app(PageRecords::class)->all()->isNotEmpty();
    }

    public function render(): View
    {
        $collector = app(PageRecords::class);

        return view('filament-admin-bar::tabs.records', [
            'records' => $collector->all()
                ->map(fn ($record): array => [
                    'label' => static::label($record),
                    'type' => class_basename($record),
                    'url' => $collector->editUrl($record),
                ])
                ->take(self::LIMIT),
            'total' => $collector->all()->count(),
            'limit' => self::LIMIT,
        ]);
    }

    protected static function label(mixed $record): string
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

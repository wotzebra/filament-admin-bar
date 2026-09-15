<?php

namespace Wotz\FilamentAdminBar\Support;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The CMS records behind the page currently being rendered.
 *
 * Request-scoped, because that is the only scope in which the question makes
 * sense: "which record is this page?" has one answer per page view.
 *
 * Two sources feed it, and between them they cover the site. Anything bound to
 * the current route registers itself — which is every detail page, for free.
 * And an application registers the record behind an index or a home page
 * itself, from wherever it already resolves one.
 */
class PageRecords
{
    /** @var Collection<string, array{record: Model, primary: bool}> */
    protected Collection $records;

    public function __construct()
    {
        $this->records = collect();
    }

    /**
     * Register a record this page is showing.
     *
     * `primary` marks the record the page *is*, as opposed to a record it
     * merely contains. A detail page's own item is primary; the index page it
     * lives under is not.
     */
    /**
     * At most this many. A host wires registration to a model event, so a page that
     * lists everything — or a feed, or a sitemap — would otherwise hold every row it
     * touched for a tab that shows twenty-five of them.
     */
    public const LIMIT = 200;

    public function register(?Model $record, bool $primary = false): void
    {
        if ($record === null || ! $record->exists) {
            return;
        }

        /*
         * Nothing to describe outside a page view, and a queued job walking a table
         * would collect the lot.
         */
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $key = $record::class . ':' . $record->getKey();

        // A record registered as primary stays primary.
        if ($this->records->has($key) && ! $primary) {
            return;
        }

        // The page's own record always fits; the list it sits above is what gets capped.
        if (! $primary && $this->records->count() >= self::LIMIT) {
            return;
        }

        $this->records->put($key, ['record' => $record, 'primary' => $primary]);
    }

    /**
     * Every model bound to the current route, which is every detail page.
     */
    public function registerRouteBindings(): void
    {
        foreach (request()->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                $this->register($parameter, primary: true);
            }
        }
    }

    /**
     * The one record this page is, if there is one.
     */
    public function primary(): ?Model
    {
        $this->registerRouteBindings();

        return $this->records->first(fn (array $entry): bool => $entry['primary'])['record'] ?? null;
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection
    {
        $this->registerRouteBindings();

        return $this->records->map(fn (array $entry): Model => $entry['record'])->values();
    }

    /**
     * Where to edit a record in the panel, or null when the signed-in user may
     * not — a link to a page that will refuse them is worse than no link.
     */
    public function editUrl(Model $record): ?string
    {
        $resource = static::resourceFor($record);

        if ($resource === null) {
            return null;
        }

        try {
            if (! $resource::canEdit($record)) {
                return null;
            }

            return $resource::getUrl('edit', ['record' => $record], panel: static::panel());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return class-string<resource>|null
     */
    public static function resourceFor(Model $record): ?string
    {
        return static::resourceMap()[$record::class] ?? null;
    }

    /**
     * Model to resource, memoised: this is asked once per record on a page
     * that may list a hundred of them.
     *
     * @return array<class-string<Model>, class-string<resource>>
     */
    protected static function resourceMap(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        // The facade's docblock says `Panel`, but a non-strict lookup of a
        // panel that isn't registered returns null.
        /** @var Panel|null $panel */
        $panel = Filament::getPanel(static::panel(), isStrict: false);

        if ($panel === null) {
            return $map = [];
        }

        $map = [];

        foreach ($panel->getResources() as $resource) {
            try {
                $map[$resource::getModel()] = $resource;
            } catch (\Throwable) {
                continue;
            }
        }

        return $map;
    }

    protected static function panel(): ?string
    {
        return config('filament-admin-bar.panel', 'admin');
    }
}

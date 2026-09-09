<?php

namespace Wotz\FilamentAdminBar\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The attachments rendered during this page view.
 *
 * Request-scoped and registered rather than scanned: the page has already
 * resolved every image it renders, so asking it to say so costs one call,
 * where working it out again means parsing the content a second time.
 */
class PageMedia
{
    /** @var Collection<string, Model> */
    protected Collection $attachments;

    public function __construct()
    {
        $this->attachments = collect();
    }

    public function register(?Model $attachment): void
    {
        if ($attachment === null || ! $attachment->exists) {
            return;
        }

        $this->attachments->put((string) $attachment->getKey(), $attachment);
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection
    {
        return $this->attachments->values();
    }

    /**
     * Whether an attachment is missing alt text in every locale it has.
     *
     * `alt` is usually translatable, and a model asked for `->alt` answers in
     * whichever locale the request happens to be in — which on a panel that
     * runs in one language and a site that runs in others is the wrong
     * question. Any locale with text in it counts as described.
     */
    public static function lacksAltText(Model $attachment): bool
    {
        $values = method_exists($attachment, 'getTranslations')
            ? $attachment->getTranslations('alt')
            : [$attachment->alt ?? null];

        return collect($values)->first(fn ($value): bool => filled($value)) === null;
    }
}

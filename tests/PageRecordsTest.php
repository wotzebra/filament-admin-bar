<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Wotz\FilamentAdminBar\Support\PageMedia;
use Wotz\FilamentAdminBar\Support\PageRecords;
use Wotz\FilamentAdminBar\Tabs\RecordsTab;
use Wotz\FilamentAdminBar\Tabs\RedirectsTab;
use Wotz\FilamentRedirects\Models\Redirect;

/**
 * Which CMS record is behind the page being looked at.
 *
 * Two sources, because between them they cover a site: anything bound to the
 * route registers itself, which is every detail page for free, and an index or
 * a home page registers its own record from wherever it already resolves one.
 */
class Thing extends Model
{
    protected $table = 'things';

    protected $guarded = [];

    public $exists = true;
}

beforeEach(function () {
    app()->forgetInstance(PageRecords::class);
    app()->forgetInstance(PageMedia::class);
});

it('takes the record a page registers for itself', function () {
    $records = app(PageRecords::class);
    $records->register(new Thing(['id' => 1, 'working_title' => 'News']), primary: true);

    expect($records->primary()?->getKey())->toBe(1);
});

it('picks up a record bound to the route', function () {
    $thing = new Thing(['id' => 7, 'working_title' => 'A vacancy']);

    Route::get('/vacancies/{thing}', fn () => '')->name('vacancies.show');

    $request = Request::create('/vacancies/7', 'GET');
    $route = Route::getRoutes()->match($request);
    $route->setParameter('thing', $thing);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);

    // Every detail page, without the application saying anything.
    expect(app(PageRecords::class)->primary()?->getKey())->toBe(7);
});

it('keeps the record the page is above the ones it merely contains', function () {
    $records = app(PageRecords::class);

    $records->register(new Thing(['id' => 2, 'working_title' => 'The index']));
    $records->register(new Thing(['id' => 3, 'working_title' => 'The item']), primary: true);

    // The header has room for one action. Somebody who followed a link to a
    // vacancy and pressed edit means the vacancy.
    expect($records->primary()?->getKey())->toBe(3)
        ->and($records->all())->toHaveCount(2);
});

it('does not demote a record that was already primary', function () {
    $records = app(PageRecords::class);
    $thing = new Thing(['id' => 4]);

    $records->register($thing, primary: true);
    $records->register($thing);

    expect($records->primary()?->getKey())->toBe(4);
});

it('ignores a record that does not exist', function () {
    $records = app(PageRecords::class);

    $unsaved = new Thing(['id' => null]);
    $unsaved->exists = false;

    $records->register($unsaved, primary: true);
    $records->register(null, primary: true);

    expect($records->primary())->toBeNull();
});

it('offers no edit link for a model with no resource', function () {
    // A link to a page that will refuse them is worse than no link.
    expect(app(PageRecords::class)->editUrl(new Thing(['id' => 5])))->toBeNull();
});

it('hides the records tab when the page has none', function () {
    $tab = new RecordsTab;
    $tab->capture();

    expect($tab->canSee())->toBeFalse();
});

it('shows the records tab once the page has one', function () {
    app(PageRecords::class)->register(new Thing(['id' => 6, 'working_title' => 'Something']));

    $tab = new RecordsTab;
    $tab->capture();

    expect($tab->canSee())->toBeTrue();
});

it('still knows what was on the page after the request that drew it is over', function () {
    app(PageRecords::class)->register(new Thing(['id' => 9, 'working_title' => 'Something']));

    $tab = new RecordsTab;
    $tab->capture();

    // Clicking a tab is a Livewire POST — a different request, with an empty
    // collector. Without the capture the tab renders blank, which is exactly
    // what rendering one tab at a time broke.
    app()->forgetInstance(PageRecords::class);

    expect($tab->canSee())->toBeTrue();
});

it('has nothing to show when the page never captured anything', function () {
    // No capture() call at all: a tab that was never on a page view.
    expect((new RecordsTab)->canSee())->toBeFalse();
});

it('shows the redirects tab on a 404 and on nothing else', function () {
    $tab = new RedirectsTab;

    expect($tab->canSee())->toBeFalse();

    RedirectsTab::markNotFound();

    // The moment you find a broken link is the moment you want to fix it, and
    // the only moment you have the old URL in front of you.
    expect($tab->canSee())->toBe(class_exists(Redirect::class));
});

it('counts an image with no alt text in any locale as undescribed', function () {
    $described = new class extends Model
    {
        public $exists = true;

        public function getTranslations(string $key): array
        {
            return ['nl' => 'Het magazijn', 'fr' => null];
        }
    };

    $bare = new class extends Model
    {
        public $exists = true;

        public function getTranslations(string $key): array
        {
            return ['nl' => null, 'fr' => null];
        }
    };

    // `alt` is translatable, and asking a model for `->alt` answers in whichever
    // locale the request is in — which on a panel that runs in one language and
    // a site that runs in others is the wrong question.
    expect(PageMedia::lacksAltText($described))->toBeFalse()
        ->and(PageMedia::lacksAltText($bare))->toBeTrue();
});

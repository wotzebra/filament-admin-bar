<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Wotz\FilamentAdminBar\Http\Middleware\ResetTranslatableStringsBag;
use Wotz\FilamentAdminBar\Translator;

/**
 * Which strings this page resolved.
 *
 * The bag has to accumulate across one page view *and every sub-request that
 * page makes* — a lazy Livewire component, a deferred island — because those
 * are exactly the strings that used to go missing. It used to be read with
 * `session()->pull()`: read and forget, once, at mount. Three things followed,
 * and all three are tested here.
 */
/**
 * The package does not bind this; a host application swaps it in for Laravel's
 * own. Built directly here so the test exercises the class rather than whatever
 * the test app happens to have bound.
 */
function translator(): Translator
{
    return new Translator(app('translation.loader'), app()->getLocale());
}

function signIn(): void
{
    $user = new class extends User
    {
        protected $table = 'users';

        public $id = 1;
    };

    Filament::auth()->setUser($user);
}

beforeEach(function () {
    session()->flush();
});

it('records a key that was asked for', function () {
    signIn();

    translator()->get('some.key');

    expect(array_keys(session()->get(Translator::BAG, [])))->toContain('some.key');
});

it('records nothing for a visitor who is not signed in to the panel', function () {
    // Only an admin ever sees the bar. Without this the session of every
    // anonymous visitor grows for a tab they will never open.
    translator()->get('some.key');

    expect(session()->get(Translator::BAG, []))->toBe([]);
});

it('records the key without the translation', function () {
    signIn();

    translator()->get('some.key');

    // The tab looks the value up from the database anyway, and writing every
    // string on the page into the session on every request is a lot of session
    // for nothing.
    expect(session()->get(Translator::BAG)['some.key'])->toBeTrue();
});

it('keeps a key and a key that is a prefix of it apart', function () {
    signIn();

    translator()->get('foo');
    translator()->get('foo.bar');

    // Stored through dot notation these collide: `foo` becomes a string where
    // `foo.bar` needs an array, and one of them is lost.
    expect(array_keys(session()->get(Translator::BAG, [])))
        ->toEqualCanonicalizing(['foo', 'foo.bar']);
});

it('skips the keys the config excludes', function () {
    signIn();

    config()->set('filament-admin-bar.translatable-strings-tab.excluded', ['routes.*']);

    translator()->get('routes.home');
    translator()->get('pages.home');

    expect(array_keys(session()->get(Translator::BAG, [])))->toBe(['pages.home']);
});

it('accumulates across the sub-requests one page makes', function () {
    signIn();

    // The document itself…
    translator()->get('first.key');

    // …then a lazy Livewire component on the same page.
    request()->headers->set('X-Livewire', 'true');
    app(ResetTranslatableStringsBag::class)->handle(request(), fn ($request) => response(''));
    translator()->get('second.key');

    // A string resolved late is exactly the string that used to go missing.
    expect(array_keys(session()->get(Translator::BAG, [])))
        ->toEqualCanonicalizing(['first.key', 'second.key']);
});

it('starts empty on the next page', function () {
    signIn();

    translator()->get('page.one');

    $request = Request::create('/somewhere-else', 'GET');
    $request->headers->set('Accept', 'text/html');
    $request->setLaravelSession(session()->driver());

    app(ResetTranslatableStringsBag::class)->handle($request, fn ($request) => response(''));

    // Without this a string translated late on page A surfaced in the bar on
    // page B, one navigation later.
    expect(session()->get(Translator::BAG, []))->toBe([]);
});

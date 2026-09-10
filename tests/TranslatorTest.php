<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
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
    // A singleton in the application, so one instance per test here too: the
    // "is this a new page view?" flag lives on the instance.
    if (! app()->bound('admin-bar-test-translator')) {
        app()->instance('admin-bar-test-translator', new Translator(app('translation.loader'), app()->getLocale()));
    }

    return app('admin-bar-test-translator');
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
    app()->forgetInstance('admin-bar-test-translator');

    $request = Request::create('/a-page', 'GET');
    $request->headers->set('Accept', 'text/html');
    app()->instance('request', $request);
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
    $document = Request::create('/a-page', 'GET');
    $document->headers->set('Accept', 'text/html');
    app()->instance('request', $document);
    translator()->get('first.key');

    // …then a lazy Livewire component on the same page, which is a POST and
    // therefore not a new page view.
    $update = Request::create('/livewire/update', 'POST');
    $update->headers->set('X-Livewire', 'true');
    app()->instance('request', $update);
    translator()->get('second.key');

    // A string resolved late is exactly the string that used to go missing.
    expect(array_keys(session()->get(Translator::BAG, [])))
        ->toEqualCanonicalizing(['first.key', 'second.key']);
});

it('starts empty on the next page', function () {
    signIn();

    $first = Request::create('/page-one', 'GET');
    $first->headers->set('Accept', 'text/html');
    app()->instance('request', $first);
    translator()->get('page.one');

    expect(array_keys(session()->get(Translator::BAG, [])))->toBe(['page.one']);

    // A new document GET is a new page view. The translator is rebuilt because
    // a real request rebuilds the container.
    app()->forgetInstance('admin-bar-test-translator');
    $second = Request::create('/page-two', 'GET');
    $second->headers->set('Accept', 'text/html');
    app()->instance('request', $second);
    translator()->get('page.two');

    // Without this a string translated late on page A surfaced in the bar on
    // page B, one navigation later.
    expect(array_keys(session()->get(Translator::BAG, [])))->toBe(['page.two']);
});

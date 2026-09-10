<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Wotz\FilamentAdminBar\Livewire\AdminBar;

/**
 * The bar has to keep working on a page that failed.
 *
 * Livewire remembers which path a component was drawn on and replays that
 * page's route middleware on every update, route model bindings included. On a
 * 404 the binding it replays is the one that could not be resolved in the first
 * place, so it throws again and the update endpoint answers 404 — on the one
 * page the Redirect tab exists for.
 */
it('does not carry the page it was drawn on into its updates', function () {
    Route::get('/vacancies/{missing}', fn () => '')->middleware('web');

    $this->get('/vacancies/7');

    // A path with no segments cannot carry a binding, so there is nothing left
    // for Livewire to replay.
    expect(Livewire::test(AdminBar::class)->snapshot['memo']['path'])->toBe('');
});

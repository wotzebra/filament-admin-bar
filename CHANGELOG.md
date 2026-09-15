# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v2.2.0 - 2026-09-15

**The bar is the route from the live site back into the CMS, and it was only
half of one.** Restyled to the Brigada theme, three bugs closed, three tabs
added.

### Upgrading

An application on the default config and views only needs the first step.

* **Re-publish the assets.** The stylesheet is new; without it the bar renders
  unstyled.

  ```bash
  php artisan vendor:publish --force --tag=filament-admin-bar-assets
  ```

* **Published config: add the new tabs.** A published config keeps working —
  every new key has a default — but it only lists the tabs it was published
  with. Add `MediaTab::class`, `RecordsTab::class` and `RedirectsTab::class` to
  `tabs` to get them, and consider widening `translatable-strings-tab.excluded`
  from `filament-admin-bar::*` to `filament*::*`, the new default, so Filament's
  own strings stay out of the tab. The new keys (`panel`, `corner`,
  `inset_inline`, `default_palette`, `edit_page_url`, `redirects-tab.create-url`)
  are in the package's config file.
* **Published views: re-publish or delete them.** `admin-bar.blade.php` and
  every tab view were rewritten, and the stylesheet targets the new markup. An
  old published copy renders the old markup against the new styles.
* **Custom tabs: read the page in `capture()`, not `render()`.** Only the active
  tab renders now, and switching tabs renders it in a Livewire request, where
  the page it sits on is gone. A tab that reads page state in `render()` shows
  up empty after a switch. Read it in `capture()` — called once, on the page
  view itself — store it with `$this->remember()`, and read it back in
  `render()` with `$this->recall()`. `SeoTab` is the example.
* **The `translatable-strings` session value changed shape**, from nested
  key → value to a flat key → `true`. Only relevant to code outside the package
  that read it.

### Fixed

* **Strings that render late now appear.** Copy inside a lazy Livewire
  component, a deferred island or any ajax response was missing from the
  Translatable strings tab. The tab read the bag once in `mount()` with
  `session()->pull()` — read *and* forget — and froze the key list in component
  state. So the list was a snapshot of the initial render, the read emptied the
  bag, and nothing reset it on a new page load: a string translated late on
  page A surfaced in the bar on page B, one navigation later. The bag is now
  read rather than pulled, the list derived per render, and middleware clears it
  on full document GETs only — so it accumulates across one page view and every
  sub-request that page makes.
* **The translator stopped writing the session for every visitor.** It recorded
  every resolved key *and its value*, on every lookup, for anyone at all —
  while importing `Filament` and never using it, which is the auth guard it was
  supposed to have. It now records keys only, only for somebody signed in to the
  panel, and cannot throw where no panel is registered. Keys are stored as
  literal array keys rather than through dot notation, so `foo` and `foo.bar`
  no longer collide.
* **Saved strings appear on the next request, not in a minute.** `submit()`
  leant on a queued export job per updated row, which is where "please wait a
  minute" came from — and on an application whose default queue carries other
  work, the wait was unbounded. Saving now exports once per scope
  synchronously and re-renders through `Livewire.navigate`, so the result is
  correct by construction and the bar keeps its open state.

### Added

* **Media tab.** The images on this page, with the one thing about them an
  editor cannot see by looking: whether they have alt text. Any locale with
  text in it counts as described, because `alt` is translatable and asking for
  `->alt` answers in whichever locale the request happens to be in.
* **Records tab.** The CMS records behind the page, linked to their edit
  screens — which turns an index page into a jump list of what it shows.
* **Redirect tab, on 404s only.** The moment you find a broken link is the
  moment you want to fix it, and the only moment the old URL is in front of you.
* **"Edit page in CMS" in the header,** resolving the record the page *is*.
  Detail pages need no wiring: anything bound to the route is picked up.
  Index and home pages register their own with `admin_bar_record()`.

### Changed

* **Restyled to the Brigada theme**, painting from the palette the visitor
  chose in the CMS (`localStorage.brigadaPalette`, falling back to `brio-05`
  when storage is empty, blocked or unrecognised). Colour is plain custom
  properties rather than Tailwind colour utilities, because no utility class can
  follow a palette chosen at runtime.
* **One tab is rendered, not all of them.** Every tab used to render on every
  page load with the inactive ones hidden by `display: none` — a tab's worth of
  queries per tab, on every frontend request an admin makes. At two tabs that
  was tolerable; the media and records tabs each walk the page's content, so it
  is not.
* **The bar renders in the browser's top layer** via `popover="manual"` rather
  than bidding its `z-index` up against a chat launcher sitting on 999999, and
  publishes `--admin-bar-height` on the document so a host site can move its
  widget clear of the open sheet.
* Closed state is a bottom-**left** edge tab, out of the corner those widgets
  default to. New config: `corner`, `inset_inline`, `default_palette`,
  `edit_page_url`, `panel`, `redirects-tab.create-url`.

**Full Changelog**: https://github.com/wotzebra/filament-admin-bar/compare/v2.1.2...v2.2.0

## v2.1.1 - 2026-04-27

### What's Changed

* Type-hint `changeTab` parameter as `string` to prevent a TypeError when `$current` receives a non-string value from a malformed Livewire payload

**Full Changelog**: https://github.com/wotzebra/filament-admin-bar/compare/v2.1.0...v2.1.1

## v2.1.0 - 2026-04-16

### What's Changed

* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/wotzebra/filament-admin-bar/pull/35
* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/wotzebra/filament-admin-bar/pull/31
* Add PHP 8.5 / Laravel 13 support by @jyrkidn in https://github.com/wotzebra/filament-admin-bar/pull/33
* Fix missing borders on input fields since Tailwind v4 upgrade by @jyrkidn in https://github.com/wotzebra/filament-admin-bar/pull/34

**Full Changelog**: https://github.com/wotzebra/filament-admin-bar/compare/v2.0.1...v2.1.0

## v2.0.1 - 2026-02-25

### What's Changed

* Add wire:key on translatable string row, so search and saving works properly by @jyrkidn in https://github.com/wotzebra/filament-admin-bar/pull/30

**Full Changelog**: https://github.com/wotzebra/filament-admin-bar/compare/v2.0.0...v2.0.1

## v2.0.0 - 2026-01-20

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/codedor/filament-admin-bar/pull/20
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/codedor/filament-admin-bar/pull/23
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/codedor/filament-admin-bar/pull/28
* Bump actions/checkout from 4 to 6 by @dependabot[bot] in https://github.com/codedor/filament-admin-bar/pull/27
* Bump stefanzweifel/git-auto-commit-action from 5 to 7 by @dependabot[bot] in https://github.com/codedor/filament-admin-bar/pull/25
* Upgrade to Filament v4 by @jyrkidn in https://github.com/codedor/filament-admin-bar/pull/21
* Upgrade to Filament v4 by @jyrkidn in https://github.com/codedor/filament-admin-bar/pull/26

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.4.0...v2.0.0

## v1.4.0 - 2025-02-28

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/17
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/18
* Upgrade to L12 by @jyrkidn in https://github.com/codedor/filament-admin-bar/pull/19

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.3.0...v1.4.0

## v1.3.0 - 2024-10-04

### What's Changed

* Upgrade to L11 by @jyrkidn in https://github.com/codedor/filament-admin-bar/pull/12

### New Contributors

* @jyrkidn made their first contribution in https://github.com/codedor/filament-admin-bar/pull/12

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.2.2...v1.3.0

## v1.2.2 - 2024-10-04

### What's Changed

* Fix hidden admin-bar on Windows
* Remember the height when switching pages or refreshing

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.2.1...v1.2.2

## v1.2.1 - 2024-09-27

### What's Changed

* set limit to the height of the bar by @thibautdeg in https://github.com/codedor/filament-admin-bar/pull/15

### New Contributors

* @thibautdeg made their first contribution in https://github.com/codedor/filament-admin-bar/pull/15

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.2.0...v1.2.1

## v1.2.0 - 2024-08-29

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.3.0 to 2.3.1 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/7
* Bump aglipanci/laravel-pint-action from 2.3.1 to 2.4 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/10
* Bump dependabot/fetch-metadata from 1.6.0 to 2.1.0 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/11
* Bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/8
* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/codedor/filament-admin-bar/pull/13
* Make admin bar resizable, refs #FIL001-133 by @Thomva in https://github.com/codedor/filament-admin-bar/pull/14

### New Contributors

* @Thomva made their first contribution in https://github.com/codedor/filament-admin-bar/pull/14

**Full Changelog**: https://github.com/codedor/filament-admin-bar/compare/v1.1.1...v1.2.0

## v1.1.1 - 2023-12-27

https://github.com/codedor/filament-admin-bar/commit/9ea68328d94c5be364f70473ae3540175052f667

## v1.1.0 - 2023-12-27

### What's changed

#### Fixed

- CSS for clean projects now publishes correctly

## v0.1.2 - 2023-11-08

### What's changed?

#### Fixed

- Fixed link-picker not working properly

## [Unreleased]

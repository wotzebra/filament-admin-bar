<?php

use Wotz\FilamentAdminBar\Tabs\MediaTab;
use Wotz\FilamentAdminBar\Tabs\RecordsTab;
use Wotz\FilamentAdminBar\Tabs\RedirectsTab;
use Wotz\FilamentAdminBar\Tabs\SeoTab;
use Wotz\FilamentAdminBar\Tabs\TranslatableStringsTab;

return [
    /*
     * Tabs are rendered one at a time — only the active one runs its queries —
     * so the cost of listing one here is what it costs when somebody opens it.
     * Each decides for itself whether it has anything to show.
     */
    'tabs' => [
        SeoTab::class,
        TranslatableStringsTab::class,
        MediaTab::class,
        RecordsTab::class,
        RedirectsTab::class,
    ],

    'translatable-strings-tab' => [
        /*
         * Keys the tab has no business offering. Filament's own strings resolve
         * on a frontend page because the bar asks who is signed in, which boots
         * the panel and translates its navigation — and the panel's wording is
         * not what somebody standing on the front end came to edit.
         */
        'excluded' => [
            'filament*::*',
            'routes.*',
        ],
    ],

    'redirects-tab' => [
        // Where "create a redirect" sends the editor.
        'create-url' => '/admin/redirects',
    ],

    /*
     * The panel the bar links into, used to find the resource behind a record.
     */
    'panel' => 'admin',

    /*
     * Where the bar sits when closed. 'left' or 'right'.
     */
    'corner' => 'left',

    /*
     * How many pixels above the bottom edge the bar sits, closed and open.
     * Zero, because that edge is where it belongs — raise it on a site that
     * already pins something full-width down there and would otherwise be
     * covered. Laravel Debugbar's minimised strip is the usual one, at 33.
     */
    'offset_bottom' => 0,

    /*
     * The palette the bar paints itself in when the visitor has never chosen
     * one in the CMS. brio-01 … brio-06.
     */
    'default_palette' => 'brio-05',

    /*
     * Given the record this page is about, where to edit it. Null uses the
     * panel's own resource URL, which is right unless an application has a
     * reason it is not.
     */
    'edit_page_url' => null,

    'filament-guard' => null,
];

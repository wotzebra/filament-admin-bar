<?php

use Illuminate\Database\Eloquent\Model;
use Wotz\FilamentAdminBar\Support\PageMedia;
use Wotz\FilamentAdminBar\Support\PageRecords;

if (! function_exists('admin_bar_record')) {
    /**
     * Tell the admin bar which CMS record this page is showing.
     *
     * Detail pages need no call: anything bound to the route registers itself.
     * This is for the pages that resolve their record some other way — an
     * index or a home page, which an application usually funnels through one
     * place of its own.
     *
     *     admin_bar_record($staticPage);
     */
    function admin_bar_record(?Model $record, bool $primary = false): void
    {
        app(PageRecords::class)->register($record, $primary);
    }
}

if (! function_exists('admin_bar_media')) {
    /**
     * Tell the admin bar about an image this page renders.
     *
     * Usually wired once, on the attachment model itself, rather than at every
     * place an image is drawn:
     *
     *     Attachment::retrieved(fn (Attachment $a) => admin_bar_media($a));
     */
    function admin_bar_media(?Model $attachment): void
    {
        app(PageMedia::class)->register($attachment);
    }
}

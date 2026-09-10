@php
    $palettes = ['brio-01', 'brio-02', 'brio-03', 'brio-04', 'brio-05', 'brio-06'];
    $fallbackPalette = config('filament-admin-bar.default_palette', 'brio-05');
    $corner = config('filament-admin-bar.corner') === 'right' ? 'bottom-right' : 'bottom-left';

    /*
     * How far along the bottom edge the closed trigger starts. It steps
     * further in on its own if it finds something already parked there.
     */
    $inset = (int) config('filament-admin-bar.inset_inline', 20);
@endphp

<div
    class="filament-admin-bar"
    x-bind:class="palette"
    data-corner="{{ $corner }}"
    x-bind:style="`--ab-inset-inline-start: ${inlineStart}px`"
    popover="manual"
    x-data="{
        open: false,
        palette: null,
        inlineStart: @js($inset),
        defaultInlineStart: @js($inset),
        triggerBlocked: false,
        remeasuring: false,
        listenForResize: false,
        adminBarHeight: '400px',
        palettes: @js($palettes),
        toggle () {
            if (! this.open && window.localStorage.getItem('filament-admin-bar-height')) {
                this.adminBarHeight = window.localStorage.getItem('filament-admin-bar-height')
            }

            this.open = ! this.open
            window.localStorage.setItem('filament-admin-bar-open', this.open)
            window.localStorage.setItem('filament-admin-bar-height', this.adminBarHeight)
            this.publishHeight()
        },
        drag (event) {
            if (! this.listenForResize) return

            const $tabs = this.$refs.tabs
            const newHeight = window.innerHeight - $tabs.clientHeight - (event.clientY || event.touches[0]?.clientY)

            if (newHeight > 40 && newHeight < window.innerHeight - $tabs.clientHeight - 92) {
                this.adminBarHeight = newHeight + 'px'
                window.localStorage.setItem('filament-admin-bar-height', this.adminBarHeight)
                this.publishHeight()
            }
        },
        /*
         * The open sheet covers the bottom of the viewport, which is where a
         * chat widget opens. Publishing the height lets the host site move its
         * widget out of the way rather than have it end up underneath.
         */
        publishHeight () {
            document.documentElement.style.setProperty(
                '--admin-bar-height',
                this.open ? this.adminBarHeight : '0px',
            )
        },
        /*
         * The palette the visitor chose in the CMS. Storage can be empty,
         * blocked, or hold something we do not recognise; all three fall back.
         *
         * Held in state and bound with `x-bind:class`, not added to
         * `classList` — every Livewire update morphs this element and restores
         * its `class` attribute from the server, which cannot know the
         * palette. Adding the class imperatively meant it survived until the
         * first tab switch and then vanished, taking every `var(--…)` in the
         * stylesheet with it: no sheet background, no accent on the links.
         */
        resolvePalette () {
            try {
                const stored = window.localStorage.getItem('brigadaPalette')

                if (stored && this.palettes.includes(stored)) {
                    return stored
                }
            } catch (error) {
                // Storage blocked entirely. The fallback stands.
            }

            return @js($fallbackPalette)
        },
        /*
         * What else is sitting in the corner the closed trigger wants.
         *
         * The bar belongs against the bottom edge, and so does everything else
         * that wants to be permanent — a dev toolbar, a cookie strip. Laravel
         * Debugbar is the usual one, and it is a small corner button in one
         * state and a bar across the whole edge in another.
         *
         * Small enough to step around, the trigger steps around it. Spanning
         * the edge, there is nowhere to step to and the trigger gets out of
         * the way until it is closed again — anybody reading a toolbar is not
         * reaching for the admin bar at that moment.
         */
        measureBottomEdge () {
            const toRight = this.$el.dataset.corner === 'bottom-right'
            let inlineStart = this.defaultInlineStart
            let blocked = false

            for (const element of document.body.children) {
                if (element === this.$el || this.$el.contains(element)) continue

                const styles = window.getComputedStyle(element)

                if (styles.position !== 'fixed') continue
                if (styles.display === 'none' || styles.visibility === 'hidden') continue

                const box = element.getBoundingClientRect()

                if (box.width === 0 || box.height === 0) continue

                // On the bottom edge, rather than merely somewhere low.
                if (Math.abs(box.bottom - window.innerHeight) > 2) continue

                // Tall enough to be a modal, a takeover or a full-page
                // overlay, which reaches the bottom edge without being
                // anything that lives there.
                if (box.height > window.innerHeight / 2) continue

                if (box.width > window.innerWidth / 2) {
                    blocked = true

                    continue
                }

                // Only what is in this corner: the far one is not in the way.
                const reach = toRight
                    ? window.innerWidth - box.left
                    : box.right

                if (reach > window.innerWidth / 2) continue

                inlineStart = Math.max(inlineStart, Math.round(reach) + 12)
            }

            return { inlineStart, blocked }
        },
        watchBottomEdge () {
            const remeasure = () => {
                if (this.remeasuring) return

                this.remeasuring = true

                window.requestAnimationFrame(() => {
                    this.remeasuring = false

                    const edge = this.measureBottomEdge()

                    this.inlineStart = edge.inlineStart
                    this.triggerBlocked = edge.blocked
                })
            }

            remeasure()

            window.addEventListener('resize', remeasure)

            // A toolbar folding itself away is an attribute change on an
            // element the bar does not own, so there is nothing else to hear.
            new MutationObserver(remeasure).observe(document.body, {
                attributes: true,
                childList: true,
                subtree: false,
                attributeFilter: ['class', 'style'],
            })
        },
        init () {
            this.palette = this.resolvePalette()
            this.open = window.localStorage.getItem('filament-admin-bar-open') === 'true'
            this.adminBarHeight = window.localStorage.getItem('filament-admin-bar-height') || '400px'
            this.publishHeight()

            /*
             * The top layer is what keeps this above a chat launcher sitting on
             * z-index 999999. Where it is unavailable the CSS fallback z-index
             * carries it.
             */
            if (typeof this.$el.showPopover === 'function') {
                try {
                    this.$el.showPopover()
                } catch (error) {
                    // Already shown, or unsupported. The fallback stands.
                }
            }

            this.$nextTick(() => this.watchBottomEdge())
        }
    }"
>
    <link rel="stylesheet" href="{{ asset('css/wotz/filament-admin-bar/filament-admin-bar.css') }}">

    {{-- Closed: the only way back in. --}}
    <button
        type="button"
        x-show="! open && ! triggerBlocked"
        x-cloak
        x-on:click="toggle()"
        data-admin-bar-trigger
        title="Open the admin bar"
    >
        Admin
        <x-heroicon-o-chevron-up class="w-3.5 h-3.5" />
    </button>

    <div
        x-show="open"
        x-collapse.duration.500ms
        x-cloak
        data-admin-bar
    >
        <div
            data-admin-bar-resizer
            @mousedown="listenForResize = true"
            @touchstart.passive="listenForResize = true"
            @mouseup.document="listenForResize = false"
            @touchend.document.passive="listenForResize = false"
            @mousemove.document="drag($event)"
            @touchmove.document="drag($event)"
        ></div>

        <div data-admin-bar-header>
            <ul data-admin-bar-tabs x-ref="tabs">
                @foreach ($tabs as $tab)
                    <li>
                        <button
                            type="button"
                            data-admin-bar-tab
                            aria-selected="{{ $tab->key() === $current ? 'true' : 'false' }}"
                            wire:click="changeTab('{{ $tab->key() }}')"
                        >
                            {{ $tab->name() }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div data-admin-bar-actions>
                @if ($editUrl)
                    {{-- The record this page *is*. A detail page also sits under
                         an index; that one is in the Records tab. --}}
                    <a href="{{ $editUrl }}" target="_blank" data-admin-bar-action>
                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                        Edit page in CMS
                    </a>
                @endif

                <button
                    type="button"
                    x-on:click="toggle()"
                    data-admin-bar-close
                    title="Close the admin bar"
                >
                    <x-heroicon-o-x-mark class="w-4.5 h-4.5" />
                </button>
            </div>
        </div>

        {{--
            One tab is rendered, not all of them.

            Every tab used to render on every page load with the inactive ones
            hidden by `display: none`, which is a tab's worth of queries per tab
            on every frontend request an admin makes. At two tabs that was
            tolerable; the media and records tabs each walk the page's content,
            so it is not.

            Keyed on the active tab so Livewire replaces the node rather than
            patching inside it — which is what the old `wire:ignore` was
            protecting, and what a nested Livewire component needs.
        --}}
        <div
            data-admin-bar-body
            wire:key="admin-bar-tab-{{ $current }}"
            :style="{ height: adminBarHeight }"
        >
            {{ $activeTab?->render() }}
        </div>
    </div>
</div>

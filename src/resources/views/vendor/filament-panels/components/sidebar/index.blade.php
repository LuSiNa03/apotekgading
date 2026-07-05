@props([
    'navigation',
])

@php
    $openSidebarClasses = 'fi-sidebar-open w-[--sidebar-width] translate-x-0 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 rtl:-translate-x-0';
    $isRtl = __('filament-panels::layout.direction') === 'rtl';
@endphp

<style>
    /* ═══════════════════════════════════════════════════
       APOTEK GADING — Custom Sidebar Gradient & Styling
    ═══════════════════════════════════════════════════ */

    /* Gradient/Solid background for the entire sidebar — Dynamic Panel Colors */
    .fi-sidebar {
        @if (filament()->getCurrentPanel()?->getId() === 'admin')
            background: #1e3a8a !important; /* Biru Solid (Navy/Royal) untuk Admin */
        @elseif (filament()->getCurrentPanel()?->getId() === 'pemilik')
            background: #b91c1c !important; /* Merah Solid untuk Pemilik */
        @else
            background: #16a34a !important; /* Hijau Solid untuk Petugas/Kasir */
        @endif
        border-right: 1px solid rgba(255,255,255,0.12);
        box-shadow: 4px 0 24px rgba(22,163,74,0.15);
    }

    /* Header area inside sidebar */
    .fi-sidebar-header {
        background: transparent !important;
        border-bottom: 1px solid rgba(255,255,255,0.15) !important;
        box-shadow: none !important;
    }

    /* Toggle collapse/expand button */
    .fi-sidebar-header .fi-icon-btn {
        color: rgba(255,255,255,0.80) !important;
        background: rgba(255,255,255,0.10) !important;
        border-radius: 8px !important;
        transition: background 0.2s, color 0.2s;
    }
    .fi-sidebar-header .fi-icon-btn:hover {
        background: rgba(255,255,255,0.22) !important;
        color: #ffffff !important;
    }

    /* Chevron rotation transition inside navigation groups */
    .fi-sidebar-group-collapse-button,
    .fi-sidebar-group-collapse-button button,
    .fi-sidebar-group-collapse-button svg,
    .fi-sidebar-group-collapse-button span {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Rotate saat di-collapse — pastikan semua elemen ikut berputar */
    .-rotate-180 .fi-sidebar-group-collapse-button,
    .-rotate-180.fi-sidebar-group-collapse-button,
    .fi-sidebar-group-collapse-button.-rotate-180,
    .fi-sidebar-group-collapse-button.-rotate-180 button,
    .fi-sidebar-group-collapse-button.-rotate-180 svg {
        transform: rotate(-180deg) !important;
    }

    /* Animasi slide untuk items yang muncul/hilang */
    .fi-sidebar-group-items {
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.3s ease !important;
    }

    /* Nav items text — white on gradient bg */
    .fi-sidebar-item-label {
        color: rgba(255,255,255,0.88) !important;
        font-weight: 500 !important;
    }
    .fi-sidebar-item.fi-active > a.fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active > div.fi-sidebar-item-button .fi-sidebar-item-label {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* Nav item icons */
    .fi-sidebar-item-icon {
        color: rgba(255,255,255,0.70) !important;
    }
    .fi-sidebar-item.fi-active > a.fi-sidebar-item-button .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active > div.fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* Semua item navbar — transparan secara default (tidak ada background putih di awal) */
    .fi-sidebar-item-button {
        background: transparent !important;
        border-radius: 10px !important;
        margin-bottom: 2px;
        transition: background 0.2s ease;
    }

    /* Active nav item — hanya muncul background putih kecerahan dikit SAAT AKTIF/DIKLIK (tanpa garis putih kiri) */
    .fi-sidebar-item.fi-active > a.fi-sidebar-item-button,
    .fi-sidebar-item.fi-active > div.fi-sidebar-item-button {
        background: rgba(255,255,255,0.18) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        border-radius: 10px !important;
        border-left: none !important;
    }

    /* Hover state — sedikit lebih terang saat diarahkan cursor */
    .fi-sidebar-item-button:hover {
        background: rgba(255,255,255,0.10) !important;
        border-radius: 10px !important;
    }

    /* Group label text */
    .fi-sidebar-group-label {
        color: rgba(255,255,255,0.55) !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.70rem !important;
    }

    /* Scrollbar on sidebar nav */
    .fi-sidebar-nav::-webkit-scrollbar { width: 4px; }
    .fi-sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 4px; }

    /* ── Divider/separator between sidebar and main content ── */
    .fi-main-ctn {
        border-left: 2px solid #e5e7eb !important;
    }
    @media (prefers-color-scheme: dark) {
        .fi-main-ctn { border-left-color: #374151 !important; }
    }

    /* Brand name colour */
    .fi-logo {
        color: #ffffff !important;
    }
    .fi-logo img { filter: brightness(0) invert(1); }
</style>

{{-- format-ignore-start --}}
<aside
    x-data="{}"
    @if (filament()->isSidebarCollapsibleOnDesktop() && (! filament()->hasTopNavigation()))
        x-cloak
        x-bind:class="
            $store.sidebar.isOpen
                ? @js($openSidebarClasses . ' ' . 'lg:sticky')
                : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0'
        "
    @else
        @if (filament()->hasTopNavigation())
            x-cloak
            x-bind:class="$store.sidebar.isOpen ? @js($openSidebarClasses) : '-translate-x-full rtl:translate-x-full'"
        @elseif (filament()->isSidebarFullyCollapsibleOnDesktop())
            x-cloak
            x-bind:class="$store.sidebar.isOpen ? @js($openSidebarClasses . ' ' . 'lg:sticky') : '-translate-x-full rtl:translate-x-full'"
        @else
            x-cloak="-lg"
            x-bind:class="
                $store.sidebar.isOpen
                    ? @js($openSidebarClasses . ' ' . 'lg:sticky')
                    : 'w-[--sidebar-width] -translate-x-full rtl:translate-x-full lg:sticky'
            "
        @endif
    @endif
    {{
        $attributes->class([
            'fi-sidebar fixed inset-y-0 start-0 z-30 flex flex-col h-screen content-start transition-all dark:bg-gray-900 lg:z-0 lg:shadow-none lg:ring-0 lg:transition-none',
            'lg:translate-x-0 rtl:lg:-translate-x-0' => ! (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop() || filament()->hasTopNavigation()),
            'lg:-translate-x-full rtl:lg:translate-x-full' => filament()->hasTopNavigation(),
        ])
    }}
>
    <div class="overflow-x-clip">
        <header
            class="fi-sidebar-header flex h-16 items-center px-6 ring-1 ring-white/10 lg:shadow-sm"
        >
            <div
                @if (filament()->isSidebarCollapsibleOnDesktop())
                    x-show="$store.sidebar.isOpen"
                    x-transition:enter="lg:transition lg:delay-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                @endif
            >
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <x-filament-panels::logo />
                    </a>
                @else
                    <x-filament-panels::logo />
                @endif
            </div>

            {{-- Compact logo shown when sidebar is collapsed: image only, centered --}}
            <div
                x-cloak
                x-show="!$store.sidebar.isOpen"
                class="w-full flex items-center justify-center lg:hidden"
            >
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <x-filament-panels::logo class="!flex !items-center !justify-center" />
                    </a>
                @else
                    <x-filament-panels::logo class="!flex !items-center !justify-center" />
                @endif
            </div>

            @if (filament()->isSidebarCollapsibleOnDesktop())
                {{-- ► EXPAND button (shown when collapsed) --}}
                <x-filament::icon-button
                    color="gray"
                    :icon="$isRtl ? 'heroicon-o-chevron-left' : 'heroicon-o-chevron-right'"
                    :icon-alias="$isRtl ? ['panels::sidebar.expand-button.rtl', 'panels::sidebar.expand-button'] : 'panels::sidebar.expand-button'"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.open()"
                    x-show="!$store.sidebar.isOpen"
                    class="mx-auto"
                />
            @endif

            @if (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop())
                {{-- ◄ COLLAPSE button (shown when expanded) --}}
                <x-filament::icon-button
                    color="gray"
                    :icon="$isRtl ? 'heroicon-o-chevron-right' : 'heroicon-o-chevron-left'"
                    :icon-alias="$isRtl ? ['panels::sidebar.collapse-button.rtl', 'panels::sidebar.collapse-button'] : 'panels::sidebar.collapse-button'"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.close()"
                    x-show="$store.sidebar.isOpen"
                    class="ms-auto hidden lg:flex"
                />
            @endif
        </header>
    </div>

    <nav
        class="fi-sidebar-nav flex-grow flex flex-col gap-y-7 overflow-y-auto overflow-x-hidden px-6 py-8"
        style="scrollbar-gutter: stable"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}

        @if (filament()->hasTenancy() && filament()->hasTenantMenu())
            <div
                @class([
                    'fi-sidebar-nav-tenant-menu-ctn',
                    '-mx-2' => ! filament()->isSidebarCollapsibleOnDesktop(),
                ])
                @if (filament()->isSidebarCollapsibleOnDesktop())
                    x-bind:class="$store.sidebar.isOpen ? '-mx-2' : '-mx-4'"
                @endif
            >
                <x-filament-panels::tenant-menu />
            </div>
        @endif

        <ul class="fi-sidebar-nav-groups -mx-2 flex flex-col gap-y-7">
            @foreach ($navigation as $group)
                <x-filament-panels::sidebar.group
                    :active="$group->isActive()"
                    :collapsible="$group->isCollapsible()"
                    :icon="$group->getIcon()"
                    :items="$group->getItems()"
                    :label="$group->getLabel()"
                    :attributes="\Filament\Support\prepare_inherited_attributes($group->getExtraSidebarAttributeBag())"
                />
            @endforeach
        </ul>

        <script>
            var collapsedGroups = JSON.parse(
                localStorage.getItem('collapsedGroups'),
            )

            if (collapsedGroups === null || collapsedGroups === 'null') {
                localStorage.setItem(
                    'collapsedGroups',
                    JSON.stringify(@js(
                        collect($navigation)
                            ->filter(fn (\Filament\Navigation\NavigationGroup $group): bool => $group->isCollapsed())
                            ->map(fn (\Filament\Navigation\NavigationGroup $group): string => $group->getLabel())
                            ->values()
                            ->all()
                    )),
                )
            }

            collapsedGroups = JSON.parse(
                localStorage.getItem('collapsedGroups'),
            )

            document
                .querySelectorAll('.fi-sidebar-group')
                .forEach((group) => {
                    if (
                        !collapsedGroups.includes(group.dataset.groupLabel)
                    ) {
                        return
                    }

                    // Alpine.js loads too slow, so attempt to hide a
                    // collapsed sidebar group earlier.
                    group.querySelector(
                        '.fi-sidebar-group-items',
                    ).style.display = 'none'
                    group
                        .querySelector('.fi-sidebar-group-collapse-button')
                        .classList.add('rotate-180')
                })
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_END) }}
    </nav>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_FOOTER) }}
</aside>
{{-- format-ignore-end --}}

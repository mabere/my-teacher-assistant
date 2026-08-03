@php
    $routeName = $routeName ?? request()->route()?->getName();
    $pageLabel = match (true) {
        $routeName === 'dashboard' => 'Dashboard',
        is_string($routeName) && str_starts_with($routeName, 'class-profiles.') => 'Profil kelas',
        is_string($routeName) && str_starts_with($routeName, 'teaching-projects.') => 'Proyek pembelajaran',
        is_string($routeName) && str_starts_with($routeName, 'admin.') => 'Pengaturan AI',
        is_string($routeName) && str_starts_with($routeName, 'help.') => 'Panduan',
        default => 'Ruang kerja guru',
    };
@endphp

<header class="sticky top-0 z-30 flex min-h-[68px] items-center justify-between gap-4 border-b border-slate-200/90 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" data-sidebar-toggle class="grid size-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 lg:hidden" aria-label="Buka navigasi" aria-expanded="false">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
        </button>
        <div class="min-w-0 truncate text-sm text-slate-500"><a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Beranda</a><span class="mx-2 text-slate-300">/</span><span class="font-semibold text-slate-900">{{ $pageLabel }}</span></div>
    </div>
    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        <span class="hidden rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 sm:inline-flex">AI menghasilkan draf</span>
        @if (Route::has('help.index'))
            <a href="{{ route('help.index') }}" class="hidden rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 sm:inline-flex">Panduan</a>
        @endif
    </div>
</header>

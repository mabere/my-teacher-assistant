@php
    $routeName = $routeName ?? request()->route()?->getName();
    $user = $user ?? auth()->user();
    $isActive = static function (string|array $patterns) use ($routeName): bool {
        foreach ((array) $patterns as $pattern) {
            if ($routeName && \Illuminate\Support\Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    };
@endphp

<aside id="app-sidebar" data-app-sidebar class="fixed inset-y-0 left-0 z-40 flex w-[260px] -translate-x-full flex-col overflow-y-auto bg-slate-950 px-4 py-5 text-slate-100 shadow-2xl transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:min-h-screen lg:shrink-0 lg:translate-x-0 lg:shadow-none">
    <div class="flex items-start justify-between gap-3 px-2">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-xl p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-700 text-sm font-extrabold shadow-lg shadow-indigo-950/40">AI</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold tracking-tight">AI Teaching Assistant</span>
                <span class="mt-0.5 block truncate text-[11px] text-slate-400">Ruang kerja guru</span>
            </span>
        </a>
        <button type="button" data-sidebar-close class="grid size-9 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 lg:hidden" aria-label="Tutup navigasi">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
        </button>
    </div>

    <div class="mx-2 mt-4 rounded-xl border border-white/10 bg-white/[.06] px-3 py-2.5 text-[11px] leading-5 text-slate-300">
        <span class="font-semibold text-slate-100">AI membantu sebagai draf.</span><br>
        Guru tetap memeriksa dan mengambil keputusan akhir.
    </div>

    <nav class="mt-5 grid gap-1.5" aria-label="Navigasi utama">
        <a href="{{ route('dashboard') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('dashboard'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('dashboard')]) @if($isActive('dashboard')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5v7a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1v-7Z" stroke-linejoin="round"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('teaching-projects.index') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('teaching-projects.*'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('teaching-projects.*')]) @if($isActive('teaching-projects.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h4" stroke-linecap="round"/></svg>
            <span>Proyek pembelajaran</span>
        </a>
        <a href="{{ route('class-profiles.index') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('class-profiles.*'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('class-profiles.*')]) @if($isActive('class-profiles.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 19c.7-3 3.1-5 7-5s6.3 2 7 5" stroke-linecap="round"/></svg>
            <span>Profil kelas</span>
        </a>
        @if (Route::has('project-documents.index'))
            <a href="{{ route('project-documents.index') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('project-documents.*'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('project-documents.*')]) @if($isActive('project-documents.*')) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M6 4h9l3 3v13H6V4Z"/><path d="M14 4v4h4M9 12h6M9 16h6" stroke-linecap="round"/></svg>
                <span>Dokumen sumber</span>
            </a>
        @endif
        @if (Route::has('help.index'))
            <a href="{{ route('help.index') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('help.*'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('help.*')]) @if($isActive('help.*')) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M9.8 9.2a2.3 2.3 0 1 1 3.7 1.8c-.9.7-1.5 1-1.5 2.1M12 16.5h.01" stroke-linecap="round"/></svg>
                <span>Panduan</span>
            </a>
        @endif
        @if ($user?->isAdmin())
            <a href="{{ route('admin.ai-provider.edit') }}" @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => $isActive('admin.*'), 'text-slate-300 hover:bg-white/[.07] hover:text-white' => ! $isActive('admin.*')]) @if($isActive('admin.*')) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 3.8 14 5l2.3-.2.9 2.2 1.9 1.3-.8 2.2.8 2.2-1.9 1.3-.9 2.2L14 16l-2 1.2L10 16l-2.3.2-.9-2.2-1.9-1.3.8-2.2-.8-2.2 1.9-1.3.9-2.2L10 5l2-1.2Z"/><circle cx="12" cy="11.2" r="2.4"/></svg>
                <span>Pengaturan AI</span>
            </a>
        @endif
    </nav>

    <div class="mt-auto border-t border-white/10 pt-4">
        <div class="flex items-center gap-3 px-2 py-2">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-700 text-xs font-bold text-white">{{ strtoupper(substr($user?->name ?? 'G', 0, 2)) }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $user?->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ $user?->isAdmin() ? 'Administrator' : 'Guru' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-300 transition hover:bg-white/[.07] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4M14 8l4 4-4 4M18 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

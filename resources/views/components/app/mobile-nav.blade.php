<nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-slate-200 bg-white/95 px-2 pb-[max(0.35rem,env(safe-area-inset-bottom))] pt-1.5 shadow-[0_-8px_24px_rgba(15,23,42,.06)] backdrop-blur lg:hidden" aria-label="Navigasi mobile">
    <a href="{{ route('dashboard') }}" @class(['flex flex-col items-center gap-1 rounded-xl px-2 py-1.5 text-[10px] font-semibold', 'text-indigo-600' => request()->routeIs('dashboard'), 'text-slate-500' => ! request()->routeIs('dashboard')])>
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5v7a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1v-7Z" stroke-linejoin="round"/></svg><span>Beranda</span>
    </a>
    <a href="{{ route('teaching-projects.index') }}" @class(['flex flex-col items-center gap-1 rounded-xl px-2 py-1.5 text-[10px] font-semibold', 'text-indigo-600' => request()->routeIs('teaching-projects.*'), 'text-slate-500' => ! request()->routeIs('teaching-projects.*')])>
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h4" stroke-linecap="round"/></svg><span>Proyek</span>
    </a>
    <a href="{{ route('teaching-projects.create') }}" class="-mt-4 flex flex-col items-center gap-1 text-[10px] font-semibold text-indigo-600">
        <span class="grid size-11 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg></span><span>Buat</span>
    </a>
    <a href="{{ route('class-profiles.index') }}" @class(['flex flex-col items-center gap-1 rounded-xl px-2 py-1.5 text-[10px] font-semibold', 'text-indigo-600' => request()->routeIs('class-profiles.*'), 'text-slate-500' => ! request()->routeIs('class-profiles.*')])>
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 19c.7-3 3.1-5 7-5s6.3 2 7 5" stroke-linecap="round"/></svg><span>Profil</span>
    </a>
</nav>

<nav class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Bagian workspace proyek" role="tablist">
    <div class="flex min-w-max items-stretch p-1.5">
        @foreach ($tabs as $key => $item)
            @php($isActive = $activeTab === $key)
            <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab={{ $key }}" role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}" @if($isActive) aria-current="page" @endif class="flex min-h-12 items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $isActive ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                <span>{{ $item['label'] }}</span>
                @if (! $item['available'])
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em] {{ $isActive ? 'bg-white/15 text-indigo-50' : 'bg-amber-50 text-amber-700' }}">Nanti</span>
                @endif
            </a>
        @endforeach
    </div>
</nav>

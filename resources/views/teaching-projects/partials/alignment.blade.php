<section class="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 shadow-sm sm:p-8" aria-labelledby="alignment-title">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Alignment checker</p>
            <h2 id="alignment-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Periksa keselarasan RPP</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Pemeriksaan deterministik membandingkan konteks proyek dengan tujuan, aktivitas inti, identitas, dan asesmen pada versi RPP terbaru.</p>
        </div>
        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $alignmentSummary['classes'] }}">{{ $alignmentSummary['label'] }}</span>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-indigo-200 bg-white/75 p-4">
        <div>
            <p class="text-sm font-semibold text-indigo-950">{{ $latestRppVersion ? 'Versi RPP '.$latestRppVersion->version_number : 'Belum ada versi RPP' }}</p>
            <p class="mt-1 text-xs leading-5 text-indigo-900/75">{{ $alignmentSummary['description'] }}</p>
        </div>
        @if ($latestRppVersion)
            <form method="POST" action="{{ route('teaching-projects.alignment.run', $teachingProject) }}">
                @csrf
                <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $alignmentFindings->isNotEmpty() ? 'Jalankan ulang pemeriksaan' : 'Jalankan pemeriksaan' }}</button>
            </form>
        @else
            <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=rpp" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buka tab RPP</a>
        @endif
    </div>

    @if ($alignmentFindings->isNotEmpty())
        <div class="mt-6 space-y-3">
            @foreach ($alignmentFindings as $finding)
                @php
                    $findingClasses = match ($finding->severity) {
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
                        'error' => 'border-red-200 bg-red-50 text-red-950',
                        default => 'border-emerald-200 bg-emerald-50 text-emerald-950',
                    };
                @endphp
                <article class="rounded-2xl border p-5 {{ $findingClasses }}">
                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">{{ $finding->category }}</p>
                            <h3 class="mt-1 font-semibold">{{ $finding->title }}</h3>
                        </div>
                        <span class="rounded-full bg-white/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em]">{{ $finding->severity === 'info' ? 'Informasi' : 'Perlu diperiksa' }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 opacity-85">{{ $finding->detail }}</p>
                    @if ($finding->recommendation)
                        <p class="mt-3 border-t border-current/10 pt-3 text-xs leading-5 opacity-75"><strong>Saran:</strong> {{ $finding->recommendation }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-indigo-200 bg-white/70 p-5 text-sm leading-6 text-slate-600">Belum ada hasil pemeriksaan untuk versi RPP terbaru. Jalankan checker setelah draft siap.</div>
    @endif
</section>

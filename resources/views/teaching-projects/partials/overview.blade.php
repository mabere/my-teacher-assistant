@php($latestLkpd = $artifactDrafts->get('lkpd'))

<div class="grid gap-5">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Paket pembelajaran</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Perangkat yang tersedia</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Setiap perangkat memakai konteks proyek yang sama. Status di bawah hanya berasal dari data yang sudah tersimpan.</p>
            </div>
            <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">{{ ($latestRppDraft ? 1 : 0) + ($latestLkpd?->status === 'ready' ? 1 : 0) }} perangkat siap</span>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-indigo-700">RPP / Modul</p>
                        <h3 class="mt-2 font-semibold text-slate-950">Draft pembelajaran</h3>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $rppState['classes'] }}">{{ $rppState['label'] }}</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $rppState['description'] }}</p>
                <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=rpp" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $latestRppDraft?->status === 'ready' ? 'Buka dan tinjau' : 'Lihat status RPP' }}</a>
            </article>

            <article class="rounded-2xl border border-violet-100 bg-violet-50/60 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-violet-700">LKPD</p>
                        <h3 class="mt-2 font-semibold text-slate-950">Lembar kerja peserta didik</h3>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $latestLkpd?->status === 'ready' ? 'bg-emerald-50 text-emerald-700' : 'bg-violet-100 text-violet-700' }}">{{ $latestLkpd?->status === 'ready' ? 'Siap ditinjau' : ($latestLkpd ? 'Diproses' : 'Belum dibuat') }}</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">Artifact pertama berbasis versi RPP terbaru dan tetap dapat diedit secara manual setelah selesai.</p>
                <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=artifacts" class="mt-5 inline-flex rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">Buka perangkat</a>
            </article>

            @foreach ([['title' => 'Materi ajar', 'description' => 'Generator materi belum tersedia pada fase ini.'], ['title' => 'Soal & rubrik', 'description' => 'Asesmen dan rubrik belum menjadi kontrak backend.']] as $futurePackage)
                <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Perangkat lanjutan</p>
                            <h3 class="mt-2 font-semibold text-slate-800">{{ $futurePackage['title'] }}</h3>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Nanti</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ $futurePackage['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Alur yang disarankan</p>
        <h2 class="mt-2 text-xl font-semibold text-slate-950">Dari konteks ke draft yang dapat ditinjau</h2>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="flex gap-3 rounded-2xl bg-slate-50 p-4"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-indigo-600 text-xs font-bold text-white">1</span><div><strong class="text-sm text-slate-900">Pastikan sumber siap</strong><p class="mt-1 text-sm leading-5 text-slate-600">Dokumen ready menjadi konteks tambahan untuk generator.</p></div></div>
            <div class="flex gap-3 rounded-2xl bg-slate-50 p-4"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-indigo-600 text-xs font-bold text-white">2</span><div><strong class="text-sm text-slate-900">Hasilkan dan tinjau RPP</strong><p class="mt-1 text-sm leading-5 text-slate-600">AI menghasilkan draft; guru tetap memeriksa dan mengedit.</p></div></div>
            <div class="flex gap-3 rounded-2xl bg-indigo-50 p-4"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-indigo-600 text-xs font-bold text-white">3</span><div><strong class="text-sm text-indigo-950">Periksa keselarasan</strong><p class="mt-1 text-sm leading-5 text-indigo-900/80">{{ $alignmentSummary['description'] }}</p></div></div>
            <div class="flex gap-3 rounded-2xl bg-emerald-50 p-4"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">4</span><div><strong class="text-sm text-emerald-950">Approval & export</strong><p class="mt-1 text-sm leading-5 text-emerald-900/80">{{ $latestApproval ? 'Versi approved siap diexport.' : 'Tinjau alignment lalu setujui versi RPP terbaru.' }}</p></div></div>
        </div>
        @if ($readyDocumentCount === 0)
            <div class="mt-5 flex flex-col justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 sm:flex-row sm:items-center"><span>Belum ada sumber siap. Anda tetap dapat membuat draft dari konteks proyek.</span><a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=sources" class="font-semibold text-amber-800 underline underline-offset-4">Kelola sumber</a></div>
        @endif
    </section>
</div>

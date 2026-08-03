<section class="rounded-3xl border border-violet-100 bg-violet-50/60 p-6 shadow-sm sm:p-8" aria-labelledby="artifact-title">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-violet-600">Generator artifact</p>
            <h2 id="artifact-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">LKPD berbantuan AI</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">LKPD dibuat dari versi RPP terbaru dan tetap perlu diperiksa serta disesuaikan guru sebelum digunakan.</p>
        </div>
        @php($latestArtifact = $artifactDrafts->get('lkpd'))
        @if ($artifactStale)
            <span class="rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">Macet</span>
        @elseif ($latestArtifact?->status === 'ready')
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Siap ditinjau</span>
        @elseif ($latestArtifact?->status === 'failed')
            <span class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">Gagal</span>
        @else
            <span class="rounded-full bg-violet-100 px-3 py-1.5 text-xs font-bold text-violet-700">{{ $latestArtifact ? 'Diproses' : 'Belum dibuat' }}</span>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-violet-200 bg-white/75 p-4">
        <p class="text-sm leading-6 text-violet-950">{{ $latestRppVersion ? 'Gunakan versi RPP '.$latestRppVersion->version_number.' sebagai dasar LKPD.' : 'Buat draft RPP terlebih dahulu.' }}</p>
        @if ($artifactStale)
            <form method="POST" action="{{ route('teaching-projects.artifacts.recover', [$teachingProject, $latestArtifact]) }}">
                @csrf
                <button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Pulihkan proses LKPD</button>
            </form>
            <form method="POST" action="{{ route('teaching-projects.artifacts.retry', [$teachingProject, $latestArtifact]) }}">
                @csrf
                <button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">Coba lagi LKPD</button>
            </form>
        @elseif ($latestRppVersion && ! in_array($latestArtifact?->status, ['queued', 'processing'], true))
            <form method="POST" action="{{ route('teaching-projects.artifacts.store', $teachingProject) }}">
                @csrf
                <input type="hidden" name="artifact_type" value="lkpd">
                <button class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ $latestArtifact?->status === 'ready' ? 'Generate ulang LKPD' : 'Generate LKPD' }}</button>
            </form>
        @elseif ($latestArtifact?->status === 'queued' || $latestArtifact?->status === 'processing')
            <span class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">Menunggu worker</span>
        @else
            <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=rpp" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buka tab RPP</a>
        @endif
    </div>

    @if ($artifactStale)
        <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm leading-6 text-amber-950"><strong>Proses tampak macet.</strong> Status belum berubah sejak {{ ($latestArtifact->started_at ?: $latestArtifact->created_at)?->format('d M Y H:i') }}. Gunakan tombol pemulihan untuk menjadwalkan ulang proses ini.</div>
    @elseif ($latestArtifact?->status === 'queued' || $latestArtifact?->status === 'processing')
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-950">LKPD sedang diproses oleh queue worker. Segarkan halaman setelah worker selesai.</div>
    @elseif ($latestArtifact?->status === 'failed')
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm leading-6 text-red-900"><strong>Pemrosesan gagal:</strong> {{ $latestArtifact->error_message ?: 'Tidak ada detail kesalahan.' }}@if ($latestArtifact->completed_at)<span class="mt-2 block text-xs font-semibold text-red-800">Terakhir diproses {{ $latestArtifact->completed_at->format('d M Y H:i') }}</span>@endif</div>
    @elseif ($latestArtifact?->status === 'ready')
        @php($artifactOutput = $latestArtifact->output_payload ?? [])
        <div class="mt-6 rounded-2xl border border-white bg-white p-5">
            <h3 class="text-lg font-semibold text-slate-950">{{ data_get($artifactOutput, 'title', 'LKPD') }}</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                @foreach (data_get($artifactOutput, 'sections', []) as $key => $items)
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-violet-600">{{ str_replace('_', ' ', $key) }}</p>
                        <ul class="mt-2 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-700">
                            @foreach ($items as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-violet-200 bg-white/70 p-5 text-sm leading-6 text-slate-600">Belum ada LKPD pada proyek ini. Hasilkan setelah draft RPP siap ditinjau.</div>
    @endif
</section>

<section class="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 shadow-sm sm:p-8" aria-labelledby="rpp-card-title">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">RPP / Modul Ajar</p>
            <h2 id="rpp-card-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Draft RPP terstruktur</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">AI membantu menyusun draft dari konteks proyek dan sumber yang siap. Guru tetap memeriksa sebelum digunakan.</p>
        </div>
        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $rppState['classes'] }}">{{ $rppState['label'] }}</span>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-indigo-100 bg-white/75 p-4">
        <p class="text-sm text-indigo-950">{{ $rppState['description'] }}</p>
        @if ($rppStale)
            <form method="POST" action="{{ route('teaching-projects.rpp-drafts.recover', [$teachingProject, $latestRppDraft]) }}">
                @csrf
                <button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Pulihkan proses RPP</button>
            </form>
            <form method="POST" action="{{ route('teaching-projects.rpp-drafts.retry', [$teachingProject, $latestRppDraft]) }}">
                @csrf
                <button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">Coba lagi draft RPP</button>
            </form>
        @elseif ($rppGenerationActive)
            <span class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">Menunggu worker</span>
        @else
            <form method="POST" action="{{ route('teaching-projects.generate-rpp', $teachingProject) }}">
                @csrf
                <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $latestRppDraft?->status === 'ready' ? 'Generate ulang draft' : 'Generate draft RPP' }}</button>
            </form>
        @endif
    </div>

    @if ($rppStale)
        <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950"><strong>Proses tampak macet.</strong> Status belum berubah sejak {{ ($latestRppDraft->started_at ?: $latestRppDraft->created_at)?->format('d M Y H:i') }}. Gunakan tombol pemulihan untuk menjadwalkan ulang proses ini.</div>
    @elseif ($latestRppDraft?->status === 'queued' || $latestRppDraft?->status === 'processing')
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Draft sedang diproses oleh queue worker. Segarkan halaman setelah worker selesai. <strong>{{ $latestRppDraft->status === 'queued' ? 'Status: menunggu antrean.' : 'Status: sedang diproses.' }}</strong></div>
    @elseif ($latestRppDraft?->status === 'failed')
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800"><strong>Pemrosesan gagal:</strong> {{ $latestRppDraft->error_message ?: 'Tidak ada detail kesalahan.' }}@if ($latestRppDraft->completed_at)<span class="mt-2 block text-xs font-semibold text-red-700">Terakhir diproses {{ $latestRppDraft->completed_at->format('d M Y H:i') }}</span>@endif</div>
    @elseif ($latestRppDraft?->status === 'ready')
        @php($output = $latestRppDraft->output_payload ?? [])
        <div class="mt-6 grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
            <div class="rounded-2xl border border-white bg-white p-5">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Identitas draft</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-slate-500">Mata pelajaran</dt><dd class="font-semibold text-slate-800">{{ data_get($output, 'identity.subject', $teachingProject->classProfile->subject) }}</dd></div>
                    <div><dt class="text-slate-500">Kelas</dt><dd class="font-semibold text-slate-800">{{ data_get($output, 'identity.grade', $teachingProject->classProfile->grade) }}</dd></div>
                    <div><dt class="text-slate-500">Topik</dt><dd class="font-semibold text-slate-800">{{ data_get($output, 'identity.topic', $teachingProject->topic) }}</dd></div>
                </dl>
                <p class="mt-5 text-xs text-slate-500">Model: {{ $latestRppDraft->model ?: 'Gemini' }} &middot; selesai {{ $latestRppDraft->completed_at?->diffForHumans() }}</p>
            </div>
            <div class="space-y-5">
                <div class="rounded-2xl border border-white bg-white p-5"><h3 class="font-semibold text-slate-900">Tujuan pembelajaran</h3><ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-700">@foreach (data_get($output, 'learning_objectives', []) as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                <div class="rounded-2xl border border-white bg-white p-5"><h3 class="font-semibold text-slate-900">Aktivitas pembelajaran</h3><div class="mt-4 grid gap-4 sm:grid-cols-3">@foreach (['opening' => 'Pembuka', 'core' => 'Inti', 'closing' => 'Penutup'] as $key => $label)<div><p class="text-xs font-bold uppercase tracking-[0.12em] text-indigo-600">{{ $label }}</p><ul class="mt-2 list-disc space-y-1 pl-4 text-sm leading-5 text-slate-700">@foreach (data_get($output, 'learning_activities.'.$key, []) as $item)<li>{{ $item }}</li>@endforeach</ul></div>@endforeach</div></div>
                <div class="rounded-2xl border border-white bg-white p-5"><h3 class="font-semibold text-slate-900">Asesmen</h3><div class="mt-4 grid gap-4 sm:grid-cols-3">@foreach (['diagnostic' => 'Diagnostik', 'formative' => 'Formatif', 'summative' => 'Sumatif'] as $key => $label)<div><p class="text-xs font-bold uppercase tracking-[0.12em] text-indigo-600">{{ $label }}</p><ul class="mt-2 list-disc space-y-1 pl-4 text-sm leading-5 text-slate-700">@foreach (data_get($output, 'assessment.'.$key, []) as $item)<li>{{ $item }}</li>@endforeach</ul></div>@endforeach</div></div>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-indigo-200 bg-white/70 p-4 text-sm text-indigo-900"><div><p>Ini masih draft AI. Guru tetap perlu memeriksa kesesuaian kurikulum, konteks kelas, aktivitas, dan asesmen sebelum digunakan.</p><p class="mt-2 text-xs font-semibold {{ $latestApproval ? 'text-emerald-700' : 'text-amber-700' }}">{{ $latestApproval ? 'Versi ini sudah disetujui guru.' : 'Belum disetujui; lanjutkan ke alignment dan approval.' }}</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=alignment" class="shrink-0 rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Periksa alignment</a><a href="{{ route('teaching-projects.rpp-drafts.edit', [$teachingProject, $latestRppDraft]) }}" class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Edit draft</a></div></div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-indigo-200 bg-white/70 p-5 text-sm leading-6 text-slate-600">Belum ada draft RPP. Pastikan konteks proyek sudah benar, lalu gunakan tombol generate ketika siap.</div>
    @endif
</section>

@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $hasProfile = $classProfileCount > 0;
    $hasProject = $teachingProjectCount > 0;
    $hasReadyRpp = $readyRppCount > 0;
    $primaryProject = $activeProject ?: $recentProjects->first();
    $draftStatus = match ($latestRppDraft?->status) {
        'ready' => ['Siap ditinjau', 'bg-emerald-50 text-emerald-700', 'Draft RPP dapat diedit dan ditinjau.'],
        'processing' => ['Sedang diproses', 'bg-amber-50 text-amber-700', 'Queue worker sedang memproses draft.'],
        'queued' => ['Menunggu antrean', 'bg-amber-50 text-amber-700', 'Draft menunggu queue worker.'],
        'failed' => ['Gagal diproses', 'bg-red-50 text-red-700', 'Periksa project untuk mencoba kembali.'],
        default => ['Belum ada draft', 'bg-slate-100 text-slate-600', 'Buat project dan hasilkan draft RPP pertama.'],
    };
@endphp

@section('content')
<div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Ruang kerja guru</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Siapkan pembelajaran dengan konteks yang terhubung.</h1>
        <p class="mt-3 max-w-2xl text-slate-600">Selamat datang, {{ auth()->user()->name }}. Mulai dari profil kelas, kembangkan menjadi proyek, lalu tinjau draft AI sebelum digunakan.</p>
    </div>
    <a href="{{ $primaryProject ? route('teaching-projects.show', $primaryProject) : route('teaching-projects.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
        {{ $primaryProject ? 'Lanjutkan proyek' : 'Buat proyek pertama' }}
    </a>
</div>

<section class="mt-8 rounded-3xl border border-indigo-200 bg-gradient-to-br from-white to-indigo-50 p-5 shadow-sm sm:p-7" aria-labelledby="start-here-title">
    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Mulai di sini</p>
            <h2 id="start-here-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Siapkan satu proyek pembelajaran pertama.</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Ikuti tiga langkah awal. Informasi profil dan sumber akan digunakan kembali agar guru tidak mengisi konteks yang sama berulang kali.</p>
        </div>
        <a href="{{ $primaryProject ? route('teaching-projects.show', $primaryProject) : route('teaching-projects.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            {{ $primaryProject ? 'Buka ruang kerja' : 'Buat proyek pembelajaran' }}
        </a>
    </div>
    <div class="mt-6 grid gap-3 md:grid-cols-3">
        @foreach ([
            ['1', 'Profil kelas', 'Simpan jenjang, kelas, kemampuan siswa, dan fasilitas.', $hasProfile],
            ['2', 'Proyek pembelajaran', 'Masukkan topik, tujuan, durasi, dan sumber.', $hasProject],
            ['3', 'Draft RPP', 'Hasilkan, periksa, edit, dan simpan versi draft.', $hasReadyRpp],
        ] as [$number, $title, $description, $complete])
            <div class="rounded-2xl border {{ $complete ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-white' }} p-4">
                <div class="flex items-start gap-3">
                    <span class="grid size-8 shrink-0 place-items-center rounded-full {{ $complete ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-50 text-indigo-600' }} text-xs font-bold">{{ $complete ? '✓' : $number }}</span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_.6fr]">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="relative z-10">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Ruang kerja persiapan pembelajaran</p>
            <h2 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-slate-950">Satu konteks, satu paket pembelajaran yang terhubung.</h2>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">Bangun perangkat secara bertahap. AI menghasilkan draf, sedangkan guru tetap memeriksa, merevisi, dan mengambil keputusan akhir.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ $primaryProject ? route('teaching-projects.show', $primaryProject) : route('teaching-projects.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">{{ $primaryProject ? 'Lanjutkan proyek' : 'Buat proyek pembelajaran' }}</a>
                <a href="{{ route('teaching-projects.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat semua proyek</a>
            </div>
        </div>
        <div class="pointer-events-none absolute -bottom-24 -right-20 size-64 rounded-full bg-indigo-100/80"></div>
    </section>

    <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Prinsip penggunaan</p>
        <h2 class="mt-3 text-xl font-semibold text-slate-950">Guru tetap memegang kendali.</h2>
        <div class="mt-5 space-y-3">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900"><strong>AI menghasilkan draf.</strong><br>Periksa konsep, konteks, dan asesmen.</div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700"><strong class="block text-xs uppercase tracking-[0.12em] text-slate-500">Konteks tersimpan</strong><span class="mt-1 block">Profil kelas dan sumber dipakai kembali.</span></div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700"><strong class="block text-xs uppercase tracking-[0.12em] text-slate-500">Hasil dapat ditelusuri</strong><span class="mt-1 block">Sumber dan status pemrosesan ditampilkan.</span></div>
        </div>
    </aside>
</div>

<section class="mt-8" aria-labelledby="summary-title">
    <div class="flex items-end justify-between gap-4">
        <div><h2 id="summary-title" class="text-2xl font-semibold tracking-tight text-slate-950">Ringkasan hari ini</h2><p class="mt-1 text-sm text-slate-500">Status pekerjaan berdasarkan data akun Anda.</p></div>
    </div>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('class-profiles.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md"><p class="text-sm text-slate-500">Profil kelas</p><p class="mt-2 text-3xl font-semibold text-slate-950">{{ $classProfileCount }}</p><p class="mt-2 text-xs font-semibold text-emerald-600">{{ $classProfileCount ? 'Siap digunakan kembali' : 'Belum ada profil' }}</p></a>
        <a href="{{ route('teaching-projects.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md"><p class="text-sm text-slate-500">Proyek pembelajaran</p><p class="mt-2 text-3xl font-semibold text-slate-950">{{ $teachingProjectCount }}</p><p class="mt-2 text-xs font-semibold text-emerald-600">{{ $activeProject ? 'Ada proyek untuk dilanjutkan' : 'Buat proyek pertama' }}</p></a>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Dokumen siap</p><p class="mt-2 text-3xl font-semibold text-slate-950">{{ $readyDocumentCount }}</p><p class="mt-2 text-xs font-semibold {{ $processingDocumentCount ? 'text-amber-600' : 'text-emerald-600' }}">{{ $processingDocumentCount ? $processingDocumentCount.' sedang diproses' : 'Tidak ada antrean aktif' }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Draft RPP terbaru</p><p class="mt-2 text-lg font-semibold text-slate-950">{{ $draftStatus[0] }}</p><p class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $draftStatus[1] }}">{{ $draftStatus[2] }}</p></div>
    </div>
</section>

<section class="mt-8" aria-labelledby="recent-projects-title">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end"><div><h2 id="recent-projects-title" class="text-2xl font-semibold tracking-tight text-slate-950">Proyek terbaru</h2><p class="mt-1 text-sm text-slate-500">Lanjutkan dari konteks dan versi terakhir.</p></div><a href="{{ route('teaching-projects.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Lihat semua</a></div>
    <div class="mt-4 space-y-3">
        @forelse ($recentProjects as $project)
            @php
                $statusClasses = match ($project->status->value) {
                    'active' => 'bg-emerald-50 text-emerald-700',
                    'completed' => 'bg-blue-50 text-blue-700',
                    'archived' => 'bg-slate-100 text-slate-600',
                    default => 'bg-amber-50 text-amber-700',
                };
                $readiness = $project->ready_rpp_count > 0
                    ? ['RPP siap ditinjau', 'bg-emerald-50 text-emerald-700']
                    : ($project->ready_documents_count > 0
                        ? ['Sumber siap digunakan', 'bg-blue-50 text-blue-700']
                        : ['Konteks belum lengkap', 'bg-amber-50 text-amber-700']);
            @endphp
            <a href="{{ route('teaching-projects.show', $project) }}" class="group flex flex-col justify-between gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:flex-row sm:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2"><span class="text-xs font-bold uppercase tracking-[0.14em] text-indigo-600">{{ $project->classProfile->subject }} · {{ $project->classProfile->grade }}</span><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $project->status->label() }}</span></div>
                    <h3 class="mt-2 truncate text-lg font-semibold text-slate-900 group-hover:text-indigo-700">{{ $project->title }}</h3>
                    <p class="mt-1 truncate text-sm text-slate-500">{{ $project->topic }} · {{ $project->duration_minutes }} menit · {{ $project->documents_count }} dokumen sumber</p>
                </div>
                <div class="w-full shrink-0 sm:w-52"><div class="flex justify-end text-xs text-slate-500"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $readiness[1] }}">{{ $readiness[0] }}</span></div><p class="mt-2 text-right text-xs text-slate-500">{{ $readiness[0] === 'RPP siap ditinjau' ? 'Buka proyek untuk meninjau draft.' : 'Lengkapi sumber dan konteks untuk melanjutkan.' }}</p></div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center"><p class="text-3xl">＋</p><h3 class="mt-3 font-semibold text-slate-900">Belum ada proyek pembelajaran.</h3><p class="mt-1 text-sm text-slate-500">Mulai dari profil kelas dan buat proyek pertama Anda.</p><a href="{{ route('teaching-projects.create') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Buat proyek pertama</a></div>
        @endforelse
    </div>
</section>
@endsection

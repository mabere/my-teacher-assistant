@extends('layouts.app')

@section('title', 'Edit draft RPP - '.$teachingProject->title)

@php
    $payload = $draft->output_payload ?? [];
    $latestVersion = $draft->versions->max('version_number') ?: 1;
    $workspaceUrl = route('teaching-projects.show', $teachingProject).'?tab=rpp';
    $sourceCount = $teachingProject->documents->count();
    $text = static fn (string $path): string => implode(PHP_EOL, data_get($payload, $path, []));
@endphp

@section('content')
<div class="mx-auto max-w-[1380px]">
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div class="min-w-0">
            <a href="{{ $workspaceUrl }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">&larr; Kembali ke workspace proyek</a>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">RPP / Modul Ajar &middot; Editor manual</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Edit draft RPP</h1>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Draft siap</span>
                <span class="rounded-full bg-indigo-100 px-3 py-1.5 text-xs font-bold text-indigo-800">Versi {{ $latestVersion }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $teachingProject->title }} &middot; {{ $teachingProject->classProfile->name }} &middot; {{ $teachingProject->topic }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
        <strong>Draft AI, keputusan tetap pada guru.</strong> Periksa konteks kelas, kurikulum, aktivitas, dan asesmen sebelum menyimpan versi baru.
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="min-w-0">
<form method="POST" action="{{ route('teaching-projects.rpp-drafts.update', [$teachingProject, $draft]) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Identitas</p>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
            @foreach ([['identity_subject', 'Mata pelajaran'], ['identity_grade', 'Kelas'], ['identity_topic', 'Topik']] as [$name, $label])
                <label class="block text-sm font-medium text-slate-700">{{ $label }}
                    <input name="{{ $name }}" value="{{ old($name, data_get($payload, str_replace('identity_', 'identity.', $name), '')) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error($name)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            @endforeach
        </div>
    </section>

    @php
        $sections = [
            'learning_objectives' => ['Tujuan pembelajaran', 'Satu butir per baris', 5, true],
            'opening' => ['Aktivitas pembuka', 'Satu aktivitas per baris', 5, true],
            'core' => ['Aktivitas inti', 'Satu aktivitas per baris', 8, true],
            'closing' => ['Aktivitas penutup', 'Satu aktivitas per baris', 5, true],
            'diagnostic' => ['Asesmen diagnostik', 'Satu butir per baris', 4, false],
            'formative' => ['Asesmen formatif', 'Satu butir per baris', 5, false],
            'summative' => ['Asesmen sumatif', 'Satu butir per baris', 5, false],
            'differentiation' => ['Diferensiasi', 'Satu strategi per baris', 5, false],
            'remedial' => ['Remedial', 'Satu strategi per baris', 4, false],
            'enrichment' => ['Pengayaan', 'Satu strategi per baris', 4, false],
            'references' => ['Referensi', 'Satu sumber per baris', 4, false],
        ];
    @endphp

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Isi draft</p>
        <p class="mt-2 text-sm text-slate-600">Edit bagian yang diperlukan. Setiap baris akan disimpan sebagai satu butir terstruktur.</p>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ($sections as $name => [$label, $help, $rows, $required])
                @php
                    $path = match ($name) {
                        'opening', 'core', 'closing' => 'learning_activities.'.$name,
                        'diagnostic', 'formative', 'summative' => 'assessment.'.$name,
                        default => $name,
                    };
                @endphp
                <label class="block text-sm font-medium text-slate-700">{{ $label }}
                    <span class="font-normal text-slate-500">({{ $help }})</span>
                    <textarea name="{{ $name }}" rows="{{ $rows }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm leading-6 focus:border-indigo-500 focus:ring-indigo-500" @required($required)>{{ old($name, $text($path)) }}</textarea>
                    @error($name)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            @endforeach
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ $workspaceUrl }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Simpan versi baru</button>
    </div>
</form>

<section class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Riwayat versi</p>
    <p class="mt-2 text-sm leading-6 text-slate-600">Setiap penyimpanan manual membuat versi baru. Versi terbaru menjadi isi aktif pada workspace.</p>
    <div class="mt-5 divide-y divide-slate-100">
        @forelse ($draft->versions as $version)
            <div class="flex flex-col gap-2 py-4 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-slate-800">Versi {{ $version->version_number }} <span class="font-normal text-slate-500">&middot; {{ $version->change_summary }}</span></p>
                    <p class="mt-1 text-xs text-slate-500">{{ $version->creator?->name ?: 'Guru' }} &middot; {{ $version->created_at?->format('d M Y H:i') }}</p>
                </div>
                @if ((int) $version->version_number === (int) $latestVersion)
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Versi aktif</span>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Tersimpan</span>
                @endif
            </div>
        @empty
            <p class="py-4 text-sm text-slate-500">Belum ada riwayat versi.</p>
        @endforelse
    </div>
</section>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24 lg:self-start" aria-label="Konteks editor RPP">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Context panel</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-950">Konteks proyek</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan konteks ini sebagai pemeriksaan cepat sebelum menyimpan revisi.</p>

            <dl class="mt-6 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Proyek</dt>
                    <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $teachingProject->title }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Topik</dt>
                    <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $teachingProject->topic }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $teachingProject->classProfile->name }}</dd>
                    <dd class="mt-1 text-slate-600">{{ $teachingProject->classProfile->subject }} &middot; {{ $teachingProject->classProfile->grade }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Durasi</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $teachingProject->duration_minutes }} menit</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Sumber tersimpan</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $sourceCount }} dokumen</dd>
                </div>
            </dl>

            <div class="mt-6 grid gap-2 border-t border-slate-100 pt-5">
                <a href="{{ $workspaceUrl }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-200">Kembali ke RPP workspace</a>
                <a href="{{ route('teaching-projects.edit', $teachingProject) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit konteks proyek</a>
            </div>
        </aside>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Buat proyek pembelajaran')

@section('content')
@php
    $wizardData = $wizardData ?? [];
    $hasProfile = $selectedProfile !== null;
@endphp

<div class="mx-auto max-w-5xl">
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
        <div>
            <a href="{{ route('teaching-projects.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">&larr; Kembali ke proyek</a>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Buat proyek pembelajaran</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $steps[$step - 1]['title'] }}</h1>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">Masukkan konteks secara bertahap. Setiap langkah tersimpan di sesi sampai proyek selesai dibuat.</p>
        </div>
        <form method="POST" action="{{ route('teaching-projects.wizard.cancel') }}">
            @csrf
            <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
        </form>
    </div>

    <div class="mt-8">
        @include('components.ui.stepper', ['steps' => $steps, 'current' => $step])
    </div>

    @if ($classProfiles->isEmpty())
        <div class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-950">
            <p class="font-semibold">Profil kelas diperlukan sebelum membuat proyek.</p>
            <p class="mt-2 text-sm leading-6 text-amber-900/80">Profil menyimpan konteks yang akan digunakan kembali pada proyek dan draft RPP.</p>
            <a href="{{ route('class-profiles.create') }}" class="mt-5 inline-flex rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Buat profil kelas</a>
        </div>
    @elseif ($step === 1)
        <form method="POST" action="{{ route('teaching-projects.wizard.basics') }}" class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4 text-sm leading-6 text-indigo-950">
                Pilih profil kelas terlebih dahulu. Mata pelajaran, jenjang, dan konteks siswa akan digunakan kembali pada langkah berikutnya.
            </div>
            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="class_profile_id" class="mb-2 block text-sm font-medium text-slate-700">Profil kelas</label>
                    <select id="class_profile_id" name="class_profile_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">Pilih profil kelas</option>
                        @foreach ($classProfiles as $profile)
                            <option value="{{ $profile->id }}" @selected((string) old('class_profile_id', $wizardData['class_profile_id'] ?? '') === (string) $profile->id)>{{ $profile->name }} &middot; {{ $profile->subject }} &middot; Kelas {{ $profile->grade }}</option>
                        @endforeach
                    </select>
                    @error('class_profile_id')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="title" class="mb-2 block text-sm font-medium text-slate-700">Judul proyek</label>
                    <input id="title" name="title" value="{{ old('title', $wizardData['title'] ?? '') }}" required maxlength="160" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" placeholder="Contoh: Descriptive Text - Pertemuan 1">
                    @error('title')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="topic" class="mb-2 block text-sm font-medium text-slate-700">Topik pembelajaran</label>
                    <input id="topic" name="topic" value="{{ old('topic', $wizardData['topic'] ?? '') }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" placeholder="Contoh: Descriptive Text">
                    @error('topic')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="learning_outcomes" class="mb-2 block text-sm font-medium text-slate-700">Capaian pembelajaran <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="learning_outcomes" name="learning_outcomes" rows="4" maxlength="10000" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">{{ old('learning_outcomes', $wizardData['learning_outcomes'] ?? '') }}</textarea>
                    @error('learning_outcomes')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="learning_objectives" class="mb-2 block text-sm font-medium text-slate-700">Tujuan pembelajaran</label>
                    <textarea id="learning_objectives" name="learning_objectives" rows="5" required maxlength="10000" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" placeholder="Tuliskan tujuan utama yang ingin dicapai siswa.">{{ old('learning_objectives', $wizardData['learning_objectives'] ?? '') }}</textarea>
                    @error('learning_objectives')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-8 flex justify-end border-t border-slate-100 pt-6">
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Simpan dan lanjut <span aria-hidden="true">&rarr;</span></button>
            </div>
        </form>
    @elseif ($step === 2)
        <form method="POST" action="{{ route('teaching-projects.wizard.execution') }}" class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="duration_minutes" class="mb-2 block text-sm font-medium text-slate-700">Alokasi waktu (menit)</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="10" max="10000" value="{{ old('duration_minutes', $wizardData['duration_minutes'] ?? 80) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <p class="mt-2 text-xs text-slate-500">Gunakan total durasi yang akan dipakai pada konteks generasi.</p>
                    @error('duration_minutes')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="teaching_model" class="mb-2 block text-sm font-medium text-slate-700">Model / metode <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input id="teaching_model" name="teaching_model" value="{{ old('teaching_model', $wizardData['teaching_model'] ?? '') }}" maxlength="120" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100" placeholder="Contoh: Project Based Learning">
                    @error('teaching_model')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:grid-cols-3">
                <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Profil</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedProfile?->name }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Topik</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $wizardData['topic'] }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tujuan</p><p class="mt-2 line-clamp-2 text-sm text-slate-700">{{ $wizardData['learning_objectives'] }}</p></div>
            </div>

            <div class="mt-8 flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                <a href="{{ route('teaching-projects.create', ['step' => 1]) }}" class="rounded-xl px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-100">&larr; Kembali</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Simpan dan lanjut <span aria-hidden="true">&rarr;</span></button>
            </div>
        </form>
    @elseif ($step === 3)
        <form method="POST" action="{{ route('teaching-projects.wizard.context') }}" class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4 text-sm leading-6 text-indigo-950">
                Data berikut diambil dari profil kelas yang Anda pilih. Periksa konteksnya sebelum digunakan untuk proyek dan draft RPP.
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jenjang dan kelas</p><p class="mt-2 font-semibold text-slate-900">{{ $selectedProfile->education_level->label() }} &middot; Kelas {{ $selectedProfile->grade }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Mata pelajaran</p><p class="mt-2 font-semibold text-slate-900">{{ $selectedProfile->subject }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jumlah dan kemampuan</p><p class="mt-2 font-semibold text-slate-900">{{ $selectedProfile->student_count ?: 'Jumlah siswa belum diisi' }} &middot; {{ $selectedProfile->ability_profile->label() }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Fasilitas</p><p class="mt-2 text-sm leading-6 text-slate-700">{{ $selectedProfile->facilities ?: 'Belum diisi' }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4 sm:col-span-2"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kebutuhan diferensiasi / konteks lokal</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $selectedProfile->differentiation_needs ?: ($selectedProfile->local_context ?: 'Belum diisi') }}</p></div>
            </div>
            <label class="mt-6 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                <input type="checkbox" name="context_confirmed" value="1" required class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>Saya sudah memeriksa konteks kelas dan memahami bahwa guru tetap bertanggung jawab menyesuaikan hasil dengan kondisi nyata siswa.</span>
            </label>
            @error('context_confirmed')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            <div class="mt-8 flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                <a href="{{ route('teaching-projects.create', ['step' => 2]) }}" class="rounded-xl px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-100">&larr; Kembali</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Simpan konteks <span aria-hidden="true">&rarr;</span></button>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('teaching-projects.wizard.complete') }}" class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-950">
                <strong>Konteks minimum sudah lengkap.</strong> Simpan proyek terlebih dahulu, lalu unggah sumber PDF/DOCX pada halaman proyek. Hanya dokumen berstatus siap yang akan digunakan untuk generate RPP.
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4 sm:col-span-2"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Dasar proyek</p><p class="mt-2 font-semibold text-slate-900">{{ $wizardData['title'] }}</p><p class="mt-1 text-sm text-slate-600">{{ $wizardData['topic'] }}</p><p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $wizardData['learning_objectives'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Profil kelas</p><p class="mt-2 font-semibold text-slate-900">{{ $selectedProfile->name }}</p><p class="mt-1 text-sm text-slate-600">{{ $selectedProfile->subject }} &middot; Kelas {{ $selectedProfile->grade }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Pelaksanaan</p><p class="mt-2 font-semibold text-slate-900">{{ $wizardData['duration_minutes'] }} menit</p><p class="mt-1 text-sm text-slate-600">{{ $wizardData['teaching_model'] ?: 'Model belum ditentukan' }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Sumber</p><p class="mt-2 text-sm leading-6 text-slate-700">Belum ada sumber pada proyek baru. Setelah proyek dibuat, unggah dokumen pada bagian Sumber proyek dan tunggu sampai statusnya siap.</p></div>
            </div>
            <label class="mt-6 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                <input type="checkbox" name="review_confirmed" value="1" required class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>Saya sudah meninjau ringkasan konteks ini dan siap menyimpan proyek sebagai draft yang masih dapat diedit.</span>
            </label>
            @error('review_confirmed')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            <div class="mt-8 flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                <a href="{{ route('teaching-projects.create', ['step' => 3]) }}" class="rounded-xl px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-100">&larr; Kembali</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Simpan proyek dan buka ruang kerja <span aria-hidden="true">&rarr;</span></button>
            </div>
        </form>
    @endif
</div>
@endsection

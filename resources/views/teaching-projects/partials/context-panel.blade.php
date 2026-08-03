@php($classProfile = $teachingProject->classProfile)

<aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24 lg:self-start" aria-label="Konteks proyek">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Context panel</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-950">Konteks proyek</h2>
        </div>
        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">v1 siap</span>
    </div>

    <dl class="mt-6 space-y-4 text-sm">
        <div>
            <dt class="text-slate-500">Topik</dt>
            <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $teachingProject->topic }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Tujuan</dt>
            <dd class="mt-1 whitespace-pre-line leading-6 text-slate-800">{{ $teachingProject->learning_objectives }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Kelas</dt>
            <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $classProfile->name }} &middot; {{ $classProfile->student_count ?: 'Jumlah siswa belum diisi' }} siswa</dd>
            <dd class="mt-1 text-slate-600">{{ $classProfile->education_level?->label() }} &middot; {{ $classProfile->subject }} &middot; Kemampuan {{ $classProfile->ability_profile?->label() }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Waktu dan model</dt>
            <dd class="mt-1 font-semibold leading-6 text-slate-900">{{ $teachingProject->duration_minutes }} menit</dd>
            <dd class="mt-1 text-slate-600">{{ $teachingProject->teaching_model ?: 'Model belum ditentukan' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Sumber siap digunakan</dt>
            @if ($readyDocuments->isNotEmpty())
                <dd class="mt-2 space-y-2">
                    @foreach ($readyDocuments as $document)
                        <span class="block truncate rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">{{ $document->original_filename }}</span>
                    @endforeach
                </dd>
            @else
                <dd class="mt-1 leading-6 text-slate-600">Belum ada dokumen berstatus siap.</dd>
            @endif
        </div>
    </dl>

    <div class="mt-6 grid gap-2 border-t border-slate-100 pt-5">
        <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=sources" class="rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-200">Kelola sumber</a>
        <a href="{{ route('teaching-projects.edit', $teachingProject) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit konteks</a>
    </div>
</aside>

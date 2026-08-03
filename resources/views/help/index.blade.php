@extends('layouts.app')

@section('title', 'Panduan ruang kerja guru')

@php
    $stages = [
        [
            'number' => '01',
            'label' => 'Mulai dari konteks',
            'title' => 'Buat profil kelas',
            'description' => 'Simpan jenjang, kelas, mata pelajaran, jumlah siswa, kemampuan, fasilitas, dan kebutuhan diferensiasi.',
            'detail' => 'Profil kelas dapat digunakan kembali pada beberapa proyek pembelajaran.',
            'available' => true,
            'href' => route('class-profiles.index'),
            'cta' => 'Kelola profil kelas',
            'tone' => 'bg-indigo-100 text-indigo-700',
        ],
        [
            'number' => '02',
            'label' => 'Susun proyek',
            'title' => 'Buat proyek pembelajaran',
            'description' => 'Masukkan topik, capaian, tujuan, alokasi waktu, dan model pembelajaran melalui wizard bertahap.',
            'detail' => 'Periksa ringkasan konteks sebelum proyek disimpan sebagai draft.',
            'available' => true,
            'href' => route('teaching-projects.create'),
            'cta' => 'Buat proyek',
            'tone' => 'bg-blue-100 text-blue-700',
        ],
        [
            'number' => '03',
            'label' => 'Siapkan sumber',
            'title' => 'Tambahkan dokumen sumber',
            'description' => 'Unggah PDF atau DOCX pada workspace proyek, lalu tunggu sampai statusnya siap digunakan.',
            'detail' => 'Hanya teks dari dokumen berstatus siap yang dapat masuk ke konteks generasi RPP.',
            'available' => true,
            'href' => route('teaching-projects.index'),
            'cta' => 'Buka proyek',
            'tone' => 'bg-cyan-100 text-cyan-700',
        ],
        [
            'number' => '04',
            'label' => 'Buat draf',
            'title' => 'Generate draft RPP',
            'description' => 'Jalankan generate dari tab RPP pada workspace setelah konteks dan sumber siap.',
            'detail' => 'Pekerjaan diproses melalui queue. Status yang tersedia: menunggu antrean, sedang diproses, siap, atau gagal.',
            'available' => true,
            'href' => route('teaching-projects.index'),
            'cta' => 'Buka workspace',
            'tone' => 'bg-emerald-100 text-emerald-700',
        ],
        [
            'number' => '05',
            'label' => 'Tinjau bersama guru',
            'title' => 'Periksa dan edit RPP',
            'description' => 'Buka draft siap dari workspace, periksa setiap bagian, lalu simpan revisi sebagai versi baru.',
            'detail' => 'AI hanya membantu membuat draf. Kesesuaian akademik dan keputusan akhir tetap berada pada guru.',
            'available' => true,
            'href' => route('teaching-projects.index'),
            'cta' => 'Lanjutkan ke proyek',
            'tone' => 'bg-violet-100 text-violet-700',
        ],
        [
            'number' => '06',
            'label' => 'Review final',
            'title' => 'Alignment, approval, dan export',
            'description' => 'Periksa keselarasan, setujui versi RPP terbaru, lalu unduh export DOCX/PDF yang sudah approved.',
            'detail' => 'Approval mengikat versi tertentu; ketika RPP berubah, alignment dan approval harus diulang untuk versi baru.',
            'available' => true,
            'href' => route('teaching-projects.index'),
            'cta' => 'Buka proyek',
            'tone' => 'bg-emerald-100 text-emerald-700',
        ],
    ];
@endphp

@section('content')
<div class="mx-auto max-w-[1380px]">
    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Panduan ruang kerja</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Dari konteks kelas menuju draft RPP</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">Ikuti alur berikut untuk menyiapkan pembelajaran. Setiap status berasal dari data proyek yang nyata, sedangkan AI digunakan sebagai bantuan penyusunan draf.</p>
        </div>
        <a href="{{ route('teaching-projects.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Buka proyek pembelajaran <span class="ml-2" aria-hidden="true">&rarr;</span></a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="workflow-title">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Workflow produk</p>
                    <h2 id="workflow-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Enam tahap yang saling terhubung</h2>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">6 tahap tersedia</span>
            </div>

            <ol class="mt-6 space-y-4">
                @foreach ($stages as $stage)
                    <li class="rounded-2xl border {{ $stage['available'] ? 'border-slate-200 bg-slate-50/70' : 'border-amber-200 bg-amber-50/60' }} p-4 sm:p-5">
                        <div class="flex items-start gap-4">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl {{ $stage['tone'] }} text-xs font-extrabold">{{ $stage['number'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] {{ $stage['available'] ? 'text-slate-500' : 'text-amber-700' }}">{{ $stage['label'] }}</p>
                                    @if ($stage['available'])
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em] text-emerald-700">Tersedia</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em] text-amber-800">Belum aktif</span>
                                    @endif
                                </div>
                                <h3 class="mt-1 text-lg font-semibold text-slate-950">{{ $stage['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $stage['description'] }}</p>
                                <p class="mt-2 text-xs leading-5 {{ $stage['available'] ? 'text-slate-500' : 'text-amber-900/80' }}">{{ $stage['detail'] }}</p>
                                @if ($stage['available'])
                                    <a href="{{ $stage['href'] }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ $stage['cta'] }} <span class="ml-1" aria-hidden="true">&rarr;</span></a>
                                @else
                                    <span class="mt-3 inline-flex text-sm font-semibold text-amber-800">Menunggu fase backend berikutnya</span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-indigo-100 bg-indigo-50/70 p-6" aria-labelledby="principles-title">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Prinsip penggunaan</p>
                <h2 id="principles-title" class="mt-2 text-xl font-semibold text-slate-950">Guru tetap memegang kendali</h2>
                <div class="mt-5 space-y-4 text-sm leading-6 text-indigo-950">
                    <div>
                        <p class="font-semibold">AI menghasilkan draf</p>
                        <p class="mt-1 text-indigo-900/75">Output AI bukan keputusan akademik final dan selalu dapat diperiksa serta diedit.</p>
                    </div>
                    <div>
                        <p class="font-semibold">Konteks berasal dari proyek</p>
                        <p class="mt-1 text-indigo-900/75">Profil kelas, tujuan, durasi, dan sumber yang siap menjadi dasar alur RPP.</p>
                    </div>
                    <div>
                        <p class="font-semibold">Versi tersimpan dengan jelas</p>
                        <p class="mt-1 text-indigo-900/75">Setiap revisi manual disimpan sebagai versi baru agar perubahan tetap terlacak.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="status-title">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Membaca status</p>
                <h2 id="status-title" class="mt-2 text-xl font-semibold text-slate-950">Gunakan status sebagai petunjuk</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-amber-700">Menunggu / diproses</dt>
                        <dd class="mt-1 leading-6 text-slate-600">Queue worker belum selesai. Segarkan workspace setelah proses berjalan.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-emerald-700">Siap</dt>
                        <dd class="mt-1 leading-6 text-slate-600">Dokumen dapat menjadi sumber atau draft RPP dapat dibuka untuk diedit.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-red-700">Gagal</dt>
                        <dd class="mt-1 leading-6 text-slate-600">Baca detail kesalahan lalu gunakan tindakan retry atau generate kembali yang tersedia.</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-3xl border border-amber-200 bg-amber-50 p-6" aria-labelledby="scope-title">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Batas baseline</p>
                <h2 id="scope-title" class="mt-2 text-xl font-semibold text-amber-950">Fitur yang belum aktif</h2>
                <p class="mt-3 text-sm leading-6 text-amber-900/80">Retrieval embedding/pgvector, OCR, generator artifact selain LKPD, dan collaboration belum aktif pada versi ini. Alignment, approval, export, dan LKPD sudah tersedia pada workspace sesuai status proyek.</p>
            </section>
        </aside>
    </div>
</div>
@endsection

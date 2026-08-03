<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>AI Teaching Assistant · Ruang kerja guru</title>
    <meta name="description" content="Ruang kerja guru untuk menghubungkan profil kelas, proyek pembelajaran, sumber, dan draft RPP berbantuan AI.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased">
    <div class="relative isolate">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[620px] overflow-hidden">
            <div class="absolute left-1/2 top-[-280px] h-[620px] w-[900px] -translate-x-1/2 rounded-full bg-indigo-200/45 blur-3xl"></div>
            <div class="absolute right-[-120px] top-28 size-72 rounded-full bg-cyan-100/70 blur-3xl"></div>
            <div class="absolute left-[-160px] top-72 size-80 rounded-full bg-violet-100/60 blur-3xl"></div>
        </div>

        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-700 text-sm font-extrabold text-white shadow-lg shadow-indigo-200">AI</span>
                <span>
                    <span class="block text-sm font-bold tracking-tight text-slate-950">AI Teaching Assistant</span>
                    <span class="mt-0.5 block text-xs text-slate-500">Ruang kerja guru</span>
                </span>
            </a>

            <nav class="flex items-center gap-2 sm:gap-3" aria-label="Navigasi publik">
                <a href="#cara-kerja" class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-white hover:text-indigo-600 sm:inline-flex">Cara kerja</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">Buka dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white/80 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:text-indigo-600">Masuk</a>
                @endauth
            </nav>
        </header>

        <main>
            <section class="mx-auto grid w-full max-w-7xl items-center gap-12 px-5 pb-16 pt-10 sm:px-8 sm:pt-16 lg:grid-cols-[1.02fr_.98fr] lg:gap-16 lg:px-10 lg:pb-24 lg:pt-20">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/80 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-indigo-700 shadow-sm">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        Ruang kerja persiapan pembelajaran
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-5xl lg:text-6xl lg:leading-[1.05]">Satu konteks untuk seluruh persiapan pembelajaran.</h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">Hubungkan profil kelas, proyek, dan sumber pembelajaran dalam satu alur kerja. AI membantu menyiapkan draf RPP, sementara guru tetap memeriksa, merevisi, dan mengambil keputusan akhir.</p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-500">Lanjutkan ke dashboard <span class="ml-2" aria-hidden="true">→</span></a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-500">Masuk ke ruang guru <span class="ml-2" aria-hidden="true">→</span></a>
                        @endauth
                        <a href="#cara-kerja" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white/80 px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-white">Lihat cara kerja</a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-600">✓</span> Konteks tersimpan</span>
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-600">✓</span> Sumber tetap privat</span>
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-600">✓</span> Guru memegang kendali</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl lg:mr-0">
                    <div class="absolute -inset-5 rounded-[2.5rem] bg-gradient-to-br from-indigo-200/70 via-white/10 to-cyan-100/70 blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200/90 bg-white p-3 shadow-2xl shadow-indigo-100/70">
                        <div class="rounded-[1.5rem] bg-slate-950 p-5 text-white sm:p-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-300">Contoh ruang kerja</p>
                                    <h2 class="mt-2 text-xl font-semibold tracking-tight">Descriptive Text</h2>
                                </div>
                                <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300">Draft aktif</span>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/10 bg-white/[.07] p-3">
                                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-400">Profil kelas</p>
                                    <p class="mt-2 text-sm font-semibold">VIII · Bahasa Inggris</p>
                                    <p class="mt-1 text-xs text-slate-400">28 siswa</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[.07] p-3">
                                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-400">Sumber</p>
                                    <p class="mt-2 text-sm font-semibold">2 dokumen</p>
                                    <p class="mt-1 text-xs text-emerald-300">Siap digunakan</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[.07] p-3">
                                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-400">Perangkat</p>
                                    <p class="mt-2 text-sm font-semibold">Draft RPP</p>
                                    <p class="mt-1 text-xs text-amber-300">Siap ditinjau</p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-indigo-400/20 bg-gradient-to-br from-indigo-500/25 to-violet-500/10 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 place-items-center rounded-xl bg-indigo-400/20 text-indigo-200">✦</span>
                                        <div>
                                            <p class="text-sm font-semibold">RPP / Modul ajar</p>
                                            <p class="mt-0.5 text-xs text-slate-400">Draft terstruktur berbantuan AI</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-indigo-200">01</span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div class="h-2 w-11/12 rounded-full bg-white/15"></div>
                                    <div class="h-2 w-9/12 rounded-full bg-white/10"></div>
                                    <div class="h-2 w-10/12 rounded-full bg-white/10"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-3 pb-1 pt-4 text-xs text-slate-500">
                            <span>Contoh alur, bukan data produksi</span>
                            <span class="font-semibold text-indigo-600">Guru tetap meninjau</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="cara-kerja" class="border-y border-slate-200/80 bg-white/75">
                <div class="mx-auto w-full max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-20">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Cara kerja</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Mulai dari konteks, bukan dari halaman kosong.</h2>
                        <p class="mt-4 leading-7 text-slate-600">Informasi yang guru siapkan di awal menjadi fondasi untuk perangkat pembelajaran berikutnya.</p>
                    </div>
                    <div class="mt-10 grid gap-4 md:grid-cols-3">
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                            <span class="grid size-10 place-items-center rounded-2xl bg-indigo-100 text-sm font-bold text-indigo-700">01</span>
                            <h3 class="mt-5 text-lg font-semibold text-slate-950">Bangun profil kelas</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Simpan jenjang, mata pelajaran, karakteristik siswa, fasilitas, dan konteks lokal.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                            <span class="grid size-10 place-items-center rounded-2xl bg-violet-100 text-sm font-bold text-violet-700">02</span>
                            <h3 class="mt-5 text-lg font-semibold text-slate-950">Kembangkan proyek</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Tentukan topik, tujuan, durasi, model pembelajaran, dan sumber yang relevan.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                            <span class="grid size-10 place-items-center rounded-2xl bg-cyan-100 text-sm font-bold text-cyan-700">03</span>
                            <h3 class="mt-5 text-lg font-semibold text-slate-950">Tinjau draft RPP</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Biarkan AI membantu menyusun draf, lalu periksa dan sesuaikan dengan kebutuhan kelas.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="mx-auto grid w-full max-w-7xl gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[.85fr_1.15fr] lg:items-center lg:px-10 lg:py-24">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Prinsip penggunaan</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">AI membantu pekerjaan guru, bukan menggantikan penilaiannya.</h2>
                    <p class="mt-4 leading-7 text-slate-600">Setiap hasil tetap diposisikan sebagai draft yang perlu diperiksa sebelum digunakan di kelas.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                        <p class="text-sm font-semibold text-amber-950">Draf, bukan keputusan</p>
                        <p class="mt-2 text-sm leading-6 text-amber-900/75">Guru memegang keputusan akademik dan konteks akhir.</p>
                    </div>
                    <div class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5">
                        <p class="text-sm font-semibold text-indigo-950">Konteks terhubung</p>
                        <p class="mt-2 text-sm leading-6 text-indigo-900/75">Profil, proyek, dan sumber tidak perlu diulang dari awal.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-950">Sumber privat</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Dokumen kerja disimpan dalam ruang privat aplikasi.</p>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-7xl px-5 pb-16 sm:px-8 lg:px-10 lg:pb-24">
                <div class="overflow-hidden rounded-[2rem] bg-indigo-700 px-6 py-10 text-white shadow-xl shadow-indigo-200 sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:px-14">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-200">Siap mulai?</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Susun persiapan pembelajaran dengan alur yang lebih tenang.</h2>
                        <p class="mt-4 leading-7 text-indigo-100">Mulai dari satu profil kelas dan kembangkan menjadi proyek pembelajaran yang dapat ditinjau.</p>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="mt-7 inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 lg:mt-0">Buka dashboard <span class="ml-2" aria-hidden="true">→</span></a>
                    @else
                        <a href="{{ route('login') }}" class="mt-7 inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 lg:mt-0">Masuk ke ruang guru <span class="ml-2" aria-hidden="true">→</span></a>
                    @endauth
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200/80 bg-white/70">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-3 px-5 py-7 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
                <p><span class="font-semibold text-slate-700">AI Teaching Assistant</span> · Ruang kerja guru</p>
                <p>Functional Alpha · AI menghasilkan draf, guru mengambil keputusan.</p>
            </div>
        </footer>
    </div>
</body>
</html>
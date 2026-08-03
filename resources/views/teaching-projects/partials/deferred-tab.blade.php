<section class="rounded-3xl border border-dashed border-amber-300 bg-amber-50 p-6 shadow-sm sm:p-8">
    <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Fase lanjutan</p>
    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-amber-950">{{ $title }}</h2>
    <p class="mt-3 max-w-2xl text-sm leading-6 text-amber-900/80">{{ $description }}</p>
    <div class="mt-6 rounded-2xl border border-amber-200 bg-white/70 p-5 text-sm leading-6 text-amber-950">
        Fitur ini sengaja belum diberi aksi palsu. Implementasinya membutuhkan model data, policy, dan service backend tersendiri agar hasilnya dapat dipertanggungjawabkan.
    </div>
    <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=overview" class="mt-6 inline-flex rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">Kembali ke ringkasan</a>
</section>

<section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Status proyek">
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
        <span class="text-xs font-bold uppercase tracking-[0.12em] text-emerald-700">Konteks</span>
        <strong class="mt-2 block text-sm text-emerald-950">Konteks siap</strong>
        <span class="mt-1 block text-xs leading-5 text-emerald-800/80">Profil kelas dan tujuan tersedia.</span>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <span class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Sumber</span>
        <strong class="mt-2 block text-sm text-slate-950">{{ $readyDocumentCount > 0 ? $readyDocumentCount.' siap digunakan' : 'Belum ada yang siap' }}</strong>
        <span class="mt-1 block text-xs leading-5 text-slate-500">@if($processingDocumentCount > 0){{ $processingDocumentCount }} sedang diproses @else Semua status tersimpan nyata @endif</span>
    </div>
    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
        <span class="text-xs font-bold uppercase tracking-[0.12em] text-indigo-700">RPP</span>
        <strong class="mt-2 block text-sm text-indigo-950">{{ $rppState['label'] }}</strong>
        <span class="mt-1 block text-xs leading-5 text-indigo-900/75">{{ $rppState['description'] }}</span>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
        <span class="text-xs font-bold uppercase tracking-[0.12em] text-amber-700">Keselarasan & approval</span>
        <strong class="mt-2 block text-sm text-amber-950">{{ $latestApproval ? 'Disetujui v'.$latestRppVersion->version_number : $alignmentSummary['label'] }}</strong>
        <span class="mt-1 block text-xs leading-5 text-amber-900/80">{{ $latestApproval ? 'Export DOCX/PDF tersedia.' : $alignmentSummary['description'] }}</span>
    </div>
</section>

<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="approval-title">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Approval guru</p>
            <h2 id="approval-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Tinjau dan setujui versi RPP</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Approval mengikat versi tertentu. Ketika RPP diedit dan versi baru dibuat, versi baru harus diperiksa serta disetujui kembali.</p>
        </div>
        @if ($latestApproval)
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Disetujui</span>
        @else
            <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Belum disetujui</span>
        @endif
    </div>

    @if (! $latestRppVersion)
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm leading-6 text-slate-600">Belum ada versi RPP yang dapat ditinjau. Hasilkan draft RPP terlebih dahulu.</div>
    @elseif (! $alignmentFindings->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-950">Jalankan alignment checker sebelum approval agar temuan keselarasan dapat menjadi bagian dari pemeriksaan guru.</div>
        <a href="{{ route('teaching-projects.show', $teachingProject) }}?tab=alignment" class="mt-5 inline-flex rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">Buka alignment checker</a>
    @elseif ($latestApproval)
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm leading-6 text-emerald-950">
            <p><strong>Versi {{ $latestRppVersion->version_number }} disetujui.</strong> {{ $latestApproval->approver?->name ?: 'Guru' }} menyetujui pada {{ $latestApproval->approved_at?->format('d M Y H:i') }}.</p>
            @if ($latestApproval->notes)
                <p class="mt-2">Catatan: {{ $latestApproval->notes }}</p>
            @endif
        </div>
        <div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5">
            <p class="text-sm font-semibold text-indigo-950">Export versi approved</p>
            <p class="mt-1 text-sm leading-6 text-indigo-900/75">Berkas hanya tersedia untuk versi terbaru yang sudah disetujui.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('teaching-projects.exports.download', [$teachingProject, $latestRppDraft, $latestRppVersion, 'docx']) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Unduh DOCX</a>
                <a href="{{ route('teaching-projects.exports.download', [$teachingProject, $latestRppDraft, $latestRppVersion, 'pdf']) }}" class="rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Unduh PDF</a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('teaching-projects.approvals.store', [$teachingProject, $latestRppDraft, $latestRppVersion]) }}" class="mt-6 space-y-5">
            @csrf
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                <p class="text-sm font-semibold text-indigo-950">Checklist pemeriksaan guru</p>
                <div class="mt-4 space-y-3 text-sm leading-6 text-indigo-950">
                    <label class="flex items-start gap-3"><input type="checkbox" name="context_verified" value="1" required class="mt-1 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500"><span>Saya sudah memeriksa konteks kelas, topik, tujuan, dan sumber yang digunakan.</span></label>
                    <label class="flex items-start gap-3"><input type="checkbox" name="alignment_reviewed" value="1" required class="mt-1 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500"><span>Saya sudah membaca hasil alignment checker dan menindaklanjuti temuan yang relevan.</span></label>
                    <label class="flex items-start gap-3"><input type="checkbox" name="content_reviewed" value="1" required class="mt-1 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500"><span>Saya sudah meninjau isi RPP dan bertanggung jawab atas keputusan akademik finalnya.</span></label>
                </div>
            </div>
            <label class="block text-sm font-medium text-slate-700">Catatan approval <span class="font-normal text-slate-400">(opsional)</span>
                <textarea name="notes" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm leading-6 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Catatan pemeriksaan atau penyesuaian guru"></textarea>
            </label>
            <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">Setujui versi {{ $latestRppVersion->version_number }}</button>
        </form>
    @endif
</section>

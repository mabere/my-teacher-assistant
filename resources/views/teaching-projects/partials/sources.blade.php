<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="sources-title">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Sumber proyek</p>
            <h2 id="sources-title" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Dokumen kurikulum atau buku</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">PDF dan DOCX disimpan di private storage dan diproses melalui queue. Hanya dokumen berstatus siap yang masuk ke konteks generator RPP.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ $readyDocumentCount }} siap</span>
    </div>

    <form method="POST" action="{{ route('teaching-projects.documents.store', $teachingProject) }}" enctype="multipart/form-data" class="mt-6 flex flex-col gap-3 rounded-2xl border border-dashed border-indigo-200 bg-indigo-50/50 p-4 sm:flex-row sm:items-center">
        @csrf
        <input type="file" name="document" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
        <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Unggah sumber</button>
    </form>
    @if ($errors->has('document'))<div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('document') }}</div>@endif

    <div class="mt-6 divide-y divide-slate-100">
        @forelse ($teachingProject->documents as $document)
            @php($isStale = in_array($document->id, $staleDocumentIds, true))
            @php($statusClasses = $isStale ? 'bg-amber-100 text-amber-800' : match ($document->processing_status->value) { 'ready' => 'bg-emerald-50 text-emerald-700', 'failed' => 'bg-red-50 text-red-700', 'processing' => 'bg-amber-50 text-amber-700', default => 'bg-slate-100 text-slate-700' })
            <div class="py-4 first:pt-0">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0"><p class="truncate font-medium text-slate-800">{{ $document->original_filename }}</p><div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500"><span>{{ number_format($document->size_bytes / 1024, 1) }} KB</span><span>&middot;</span><span class="rounded-full px-2 py-1 font-semibold {{ $statusClasses }}">{{ $isStale ? 'Macet' : $document->processing_status->label() }}</span>@if ($document->processed_at)<span>&middot; selesai {{ $document->processed_at->diffForHumans() }}</span>@endif</div></div>
                    <div class="flex items-center gap-3">@if (in_array($document->id, $staleDocumentIds, true))<form method="POST" action="{{ route('teaching-projects.documents.recover', [$teachingProject, $document]) }}">@csrf<button class="text-sm font-semibold text-amber-700 hover:text-amber-800">Pulihkan</button></form>@elseif ($document->processing_status->value === 'failed')<form method="POST" action="{{ route('teaching-projects.documents.retry', [$teachingProject, $document]) }}">@csrf<button class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Coba lagi</button></form>@endif<form method="POST" action="{{ route('teaching-projects.documents.destroy', [$teachingProject, $document]) }}" onsubmit="return confirm('Hapus dokumen ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button></form></div>
                </div>
                @if (in_array($document->id, $staleDocumentIds, true))<div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950"><strong>Proses tampak macet.</strong> Mulai {{ ($document->processing_started_at ?: $document->created_at)?->format('d M Y H:i') }}. Pulihkan untuk memasukkannya kembali ke antrean.</div>@endif
                @if ($document->processing_status->value === 'failed' && $document->processing_error)<div class="mt-3 rounded-2xl border border-red-100 bg-red-50 p-3 text-sm text-red-800"><strong>Pemrosesan gagal:</strong> {{ $document->processing_error }}</div>@endif
                @if ($document->processing_status->value === 'ready' && $document->extracted_text)<details class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700"><summary class="cursor-pointer font-semibold">Lihat ringkasan teks hasil ekstraksi</summary><p class="mt-2 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($document->extracted_text, 800) }}</p></details>@endif
            </div>
        @empty
            <p class="py-6 text-sm text-slate-500">Belum ada dokumen sumber pada proyek ini.</p>
        @endforelse
    </div>
</section>

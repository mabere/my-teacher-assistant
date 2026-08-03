@php
    $alerts = [
        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'Berhasil'],
        'error' => ['border-red-200 bg-red-50 text-red-800', 'Perlu perhatian'],
        'info' => ['border-blue-200 bg-blue-50 text-blue-800', 'Informasi'],
    ];
@endphp

@foreach ($alerts as $type => [$classes, $label])
    @if (session($type))
        <div class="mb-6 flex items-start gap-3 rounded-2xl border p-4 text-sm leading-6 {{ $classes }}" role="status">
            <span class="font-semibold">{{ $label }}:</span>
            <span>{{ session($type) }}</span>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800" role="alert">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

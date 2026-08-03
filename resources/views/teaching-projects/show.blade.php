@extends('layouts.app')

@section('title', $teachingProject->title)

@section('content')
@php
    $projectStatusClasses = match ($teachingProject->status->value) {
        'active' => 'bg-emerald-50 text-emerald-700',
        'completed' => 'bg-blue-50 text-blue-700',
        'archived' => 'bg-slate-100 text-slate-600',
        default => 'bg-amber-50 text-amber-700',
    };
@endphp

<div class="mx-auto max-w-[1380px]">
    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
        <div class="min-w-0">
            <a href="{{ route('teaching-projects.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">&larr; Kembali ke proyek</a>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Ruang kerja proyek</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $teachingProject->title }}</h1>
                <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $projectStatusClasses }}">{{ $teachingProject->status->label() }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $teachingProject->classProfile->subject }} &middot; {{ $teachingProject->classProfile->name }} &middot; {{ $teachingProject->topic }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Konteks siap</span>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $readyDocumentCount }} sumber siap</span>
                <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $rppState['classes'] }}">RPP: {{ $rppState['label'] }}</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 sm:justify-end">
            <a href="{{ route('teaching-projects.edit', $teachingProject) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit konteks</a>
            @if ($rppStale)
                <form method="POST" action="{{ route('teaching-projects.rpp-drafts.recover', [$teachingProject, $latestRppDraft]) }}">@csrf<button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Pulihkan proses RPP</button></form>
            @elseif ($latestRppDraft?->status === 'failed')
                <form method="POST" action="{{ route('teaching-projects.rpp-drafts.retry', [$teachingProject, $latestRppDraft]) }}">@csrf<button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">Coba lagi RPP</button></form>
            @elseif ($rppGenerationActive)
                <span class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">RPP diproses</span>
            @else
                <form method="POST" action="{{ route('teaching-projects.generate-rpp', $teachingProject) }}">@csrf<button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $latestRppDraft?->status === 'ready' ? 'Generate ulang RPP' : 'Generate draft RPP' }}</button></form>
            @endif
            <form method="POST" action="{{ route('teaching-projects.destroy', $teachingProject) }}" onsubmit="return confirm('Hapus proyek ini?')">@csrf @method('DELETE')<button class="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Hapus</button></form>
        </div>
    </div>

    @include('teaching-projects.partials.status-strip')
    @include('teaching-projects.partials.workspace-tabs')

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0">
            @if ($activeTab === 'overview')
                @include('teaching-projects.partials.overview')
            @elseif ($activeTab === 'rpp')
                @include('teaching-projects.partials.rpp-card')
            @elseif ($activeTab === 'sources')
                @include('teaching-projects.partials.sources')
            @elseif ($activeTab === 'artifacts')
                @include('teaching-projects.partials.artifacts')
            @elseif ($activeTab === 'alignment')
                @include('teaching-projects.partials.alignment')
            @else
                @include('teaching-projects.partials.approval')
            @endif
        </div>
        @include('teaching-projects.partials.context-panel')
    </div>
</div>
@endsection


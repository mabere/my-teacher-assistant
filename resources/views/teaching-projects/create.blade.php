@extends('layouts.app')

@section('title', 'Buat proyek pembelajaran')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('teaching-projects.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">← Kembali ke proyek</a>
    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Buat proyek pembelajaran</h1>
    <p class="mt-2 text-slate-600">Masukkan konteks inti satu kali. Generator AI akan menggunakan kontrak ini pada tahap berikutnya.</p>
    @if ($classProfiles->isEmpty())
        <div class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-900"><p class="font-semibold">Profil kelas diperlukan.</p><p class="mt-1 text-sm">Buat profil kelas sebelum memulai proyek pembelajaran.</p><a href="{{ route('class-profiles.create') }}" class="mt-4 inline-flex rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Buat profil kelas</a></div>
    @else
        <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">@include('teaching-projects._form', ['action' => route('teaching-projects.store'), 'method' => 'POST'])</div>
    @endif
</div>
@endsection

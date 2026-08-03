@extends('layouts.app')

@section('title', 'Tambah profil kelas')

@section('content')
<div class="max-w-3xl"><a href="{{ route('class-profiles.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">← Kembali ke profil kelas</a><h1 class="mt-4 text-3xl font-semibold tracking-tight">Tambah profil kelas</h1><p class="mt-2 text-slate-600">Informasi ini menjadi konteks yang digunakan kembali dalam setiap proyek pembelajaran.</p><div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">@include('class-profiles._form', ['action' => route('class-profiles.store'), 'method' => 'POST'])</div></div>
@endsection

@extends('layouts.app')

@section('title', 'Edit proyek pembelajaran')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('teaching-projects.show', $teachingProject) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">← Kembali ke proyek</a>
    <h1 class="mt-4 text-3xl font-semibold tracking-tight">Edit proyek pembelajaran</h1>
    <p class="mt-2 text-slate-600">Perubahan konteks akan digunakan untuk langkah pengembangan berikutnya.</p>
    <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">@include('teaching-projects._form', ['action' => route('teaching-projects.update', $teachingProject), 'method' => 'PUT'])</div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Pengaturan AI Provider')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">&larr; Kembali ke dashboard</a>
    <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Admin</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Pengaturan AI Provider</h1>
            <p class="mt-2 text-slate-600">Konfigurasi Gemini untuk generator RPP. API key disimpan terenkripsi dan tidak pernah ditampilkan kembali.</p>
        </div>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Admin only</span>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.ai-provider.update') }}" class="mt-8 space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="text-sm font-semibold text-slate-800">Provider</label>
                <select name="provider" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required>
                    <option value="gemini" @selected(old('provider', $setting->provider) === 'gemini')>Google Gemini</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-800">Model</label>
                <input name="model" value="{{ old('model', $setting->model) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required>
            </div>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">Base URL</label>
            <input name="base_url" type="url" value="{{ old('base_url', $setting->base_url) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">Gemini API key</label>
            <input name="api_key" type="password" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" placeholder="Kosongkan untuk mempertahankan key tersimpan">
            <p class="mt-2 text-xs text-slate-500">Status: <strong>{{ $setting->api_key_hint ? 'Tersimpan '.$setting->api_key_hint : 'Belum dikonfigurasi' }}</strong>. Nilai asli tidak dapat dilihat kembali.</p>
        </div>
        <label class="flex items-center gap-3 text-sm text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $setting->is_active)) class="rounded border-slate-300 text-indigo-600"> Aktifkan provider ini</label>
        <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Simpan pengaturan</button>
    </form>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-800">Uji koneksi</p>
            <p class="mt-2 text-sm text-slate-600">Memeriksa API key dan model tanpa menjalankan generasi RPP.</p>
            <form method="POST" action="{{ route('admin.ai-provider.test') }}" class="mt-4">@csrf<button class="rounded-xl border border-indigo-200 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Uji koneksi Gemini</button></form>
            @if ($setting->last_tested_at)<p class="mt-3 text-xs text-slate-500">Terakhir diuji {{ $setting->last_tested_at->diffForHumans() }} - {{ $setting->last_test_status === 'success' ? 'berhasil' : 'gagal' }}</p>@endif
            @if ($setting->last_test_status === 'failed' && $setting->last_test_error)<p class="mt-2 text-xs text-red-700">{{ $setting->last_test_error }}</p>@endif
        </div>
        <div class="rounded-3xl border border-red-100 bg-red-50 p-6">
            <p class="text-sm font-semibold text-red-900">Hapus API key</p>
            <p class="mt-2 text-sm text-red-800">Menghapus key akan membuat generator berhenti sampai key baru disimpan.</p>
            <form method="POST" action="{{ route('admin.ai-provider.key.destroy') }}" class="mt-4" onsubmit="return confirm('Hapus API key Gemini?')">@csrf @method('DELETE')<button class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-white">Hapus key</button></form>
        </div>
    </div>
</div>
@endsection

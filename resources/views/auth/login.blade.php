@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Ruang guru</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Masuk ke ruang guru</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan akun Anda untuk melanjutkan ke profil kelas dan proyek pembelajaran.</p>
    </div>
    @if (session('status'))<div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    @if ($errors->any())<div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><p class="font-semibold">Masuk belum berhasil.</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf
        <div><label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
        <div><label for="password" class="mb-2 block text-sm font-medium text-slate-700">Kata sandi</label><input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></div>
        <label class="flex items-center gap-3 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Ingat saya</label>
        <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Masuk</button>
    </form>
@endsection

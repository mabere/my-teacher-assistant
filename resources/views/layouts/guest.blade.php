<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'My Teacher Assistant' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
        <section class="hidden bg-indigo-700 p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-200">My Teacher Assistant</p><h1 class="mt-8 max-w-lg text-5xl font-semibold leading-tight">Satu konteks untuk seluruh persiapan pembelajaran.</h1><p class="mt-6 max-w-lg text-lg leading-8 text-indigo-100">Ruang kerja guru untuk menyusun perangkat pembelajaran secara terintegrasi, dapat diperiksa, dan tetap berada di bawah kendali profesional guru.</p></div>
            <p class="text-sm text-indigo-200">Functional Alpha · Fondasi Sprint 0</p>
        </section>
        <section class="flex items-center justify-center bg-slate-50 px-6 py-12 sm:px-10">
            <div class="w-full max-w-md">{{ $slot ?? '' }} @yield('content')</div>
        </section>
    </main>
</body>
</html>

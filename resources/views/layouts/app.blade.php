<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'My Teacher Assistant')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    @php($currentRoute = request()->route()?->getName())
    <div class="min-h-screen lg:flex">
        <div data-sidebar-backdrop class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-[2px] lg:hidden" aria-hidden="true"></div>
        @include('components.app.sidebar', ['user' => auth()->user(), 'routeName' => $currentRoute])

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            @include('components.app.topbar', ['routeName' => $currentRoute])
            <main id="main-content" tabindex="-1" class="min-w-0 flex-1 px-4 py-6 pb-24 sm:px-6 sm:py-8 lg:px-8 lg:pb-10">
                <div class="mx-auto w-full max-w-[1500px]">
                    @include('components.ui.flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('components.app.mobile-nav')
</body>
</html>

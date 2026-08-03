@props([
    'steps' => [],
    'current' => 1,
])

<nav aria-label="Progres pembuatan proyek">
    <ol class="grid gap-2 sm:grid-cols-4">
        @foreach ($steps as $item)
            @php
                $number = $item['number'];
                $isCurrent = $number === $current;
                $isComplete = $number < $current;
            @endphp
            <li class="relative rounded-2xl border px-3 py-3 {{ $isCurrent ? 'border-indigo-300 bg-indigo-50' : ($isComplete ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50') }}" @if($isCurrent) aria-current="step" @endif>
                <div class="flex items-start gap-3">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full text-xs font-bold {{ $isCurrent ? 'bg-indigo-600 text-white' : ($isComplete ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600') }}">
                        @if ($isComplete)
                            <span aria-hidden="true">&check;</span>
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xs font-bold uppercase tracking-[0.12em] {{ $isCurrent ? 'text-indigo-700' : ($isComplete ? 'text-emerald-700' : 'text-slate-500') }}">Langkah {{ $number }}</span>
                        <span class="mt-1 block text-sm font-semibold {{ $isCurrent ? 'text-indigo-950' : 'text-slate-800' }}">{{ $item['title'] }}</span>
                        <span class="mt-1 hidden text-xs leading-5 text-slate-500 sm:block">{{ $item['description'] }}</span>
                    </span>
                </div>
            </li>
        @endforeach
    </ol>
</nav>

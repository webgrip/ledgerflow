<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dev Dashboard' }} – LedgerFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 font-sans antialiased">

    <div class="sticky top-0 z-50 border-b border-amber-500/40 bg-amber-500/10 backdrop-blur px-4 py-2 flex items-center gap-3">
        <span class="text-amber-400 text-lg">⚠</span>
        <span class="text-amber-300 text-xs font-semibold uppercase tracking-widest">Development Dashboard — Not for production use</span>
        <span class="ml-auto text-amber-500/60 text-xs font-mono">{{ config('app.env') }} · {{ config('app.url') }}</span>
    </div>

    {{ $slot }}

    @fluxScripts
    @livewireScripts
</body>
</html>

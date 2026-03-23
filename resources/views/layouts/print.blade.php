<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Print') &mdash; {{ config('app.name', 'School Finance ERP') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            @page { margin: 0.75in; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-white antialiased text-secondary-900">

    {{-- Print toolbar (hidden on print) --}}
    <div class="no-print sticky top-0 z-50 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="btn-secondary text-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn-primary text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                Print
            </button>
            @stack('print-actions')
        </div>
    </div>

    {{-- Printable content --}}
    <div class="max-w-4xl mx-auto px-6 py-8">

        {{-- School Letterhead --}}
        <header class="text-center mb-8 pb-6 border-b-2 border-gray-800">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="flex items-center justify-center w-10 h-10 bg-primary-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15v-3.75m0 0 5.25 3 5.25-3" />
                    </svg>
                </div>
            </div>
            <h1 class="text-xl font-bold text-secondary-900 uppercase tracking-wide">OrangeApps Academy</h1>
            <p class="text-sm text-secondary-600 mt-1">123 Education Avenue, Makati City, Metro Manila, Philippines 1200</p>
            <p class="text-sm text-secondary-600">Tel: (02) 8888-1234 &bull; Email: finance@orangeapps.edu.ph</p>
        </header>

        {{-- Report Title --}}
        @hasSection('report-title')
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold text-secondary-900">@yield('report-title')</h2>
                @hasSection('report-subtitle')
                    <p class="text-sm text-secondary-500 mt-1">@yield('report-subtitle')</p>
                @endif
            </div>
        @endif

        {{-- Main content --}}
        @yield('content')

        {{-- Footer --}}
        <footer class="mt-12 pt-4 border-t border-gray-300 text-xs text-secondary-400">
            <div class="flex justify-between">
                <span>Generated: {{ now()->format('M d, Y h:i A') }}</span>
                <span>OrangeApps School Finance ERP</span>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

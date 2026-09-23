<!DOCTYPE html>
<html lang="id" class="w-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>RESCUE-LOG</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('img/Rescue-log.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/Rescue-log.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/Rescue-log.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        html,
        body {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-100 font-sans antialiased min-h-screen flex flex-col w-full text-slate-800">

    @include('layouts.navbar-lapangan')

    <main class="flex-1 w-full">
        <div class="w-full px-4 sm:px-6 lg:px-10 py-6">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. REGISTRASI SERVICE WORKER PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => {
                        console.log('[PWA SW Lapangan] Registered successfully:', reg.scope);
                    })
                    .catch(err => {
                        console.error('[PWA SW Lapangan] Registration failed:', err);
                    });
            });
        }

        // 2. GLOBAL TOAST NOTIFICATION HELPER
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-2xl shadow-lg border border-slate-100'
                }
            });

            @if (session('success'))
                Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
            @endif
            @if (session('error'))
                Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
            @endif
            @if (session('warning'))
                Toast.fire({ icon: 'warning', title: "{{ session('warning') }}" });
            @endif
            @if (session('info'))
                Toast.fire({ icon: 'info', title: "{{ session('info') }}" });
            @endif
        });

        // 3. HANDLER NATIVE PWA INSTALL PROMPT
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA SW] Native install prompt ready.');
        });
    </script>

    @stack('scripts')
</body>

</html>
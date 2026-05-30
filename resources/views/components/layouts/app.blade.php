<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php use App\Services\BrandingService; $brand = BrandingService::get(); @endphp
    <title>{{ $title ?? ($brand['app_full_name'] ?? 'LogStack Central') }}</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function hideLoader() {
            const loader = document.getElementById('preloader');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => { loader.style.display = 'none'; }, 600);
            }
        }
        window.addEventListener('load', hideLoader);
        setTimeout(hideLoader, 1200);

        // Dark mode init sebelum Alpine load
        (function() {
            const isDark = localStorage.getItem('theme') === 'dark' ||
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>
</head>
<body x-data="{ isDark: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="
        $watch('isDark', val => {
            localStorage.setItem('theme', val ? 'dark' : 'light');
            if (val) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })
      "
      class="bg-white dark:bg-[#0b0e14] text-slate-900 dark:text-white min-h-screen transition-colors duration-300 flex flex-col">

    <div id="preloader">
        <div class="loader-ring mb-4"></div>
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] animate-pulse">
            {{ $loaderText ?? ($brand['loader_text'] ?? 'LogStack') }}
        </div>
    </div>

    {{ $slot }}

    <x-toast />

</body>
</html>

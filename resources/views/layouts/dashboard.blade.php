<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'UPNVJ Study Tracer System')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('head', '')
    <title>@yield('title', 'Dashboard - UPNVJ Study Tracer System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <x-dashboard.sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm z-10">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button type="button" class="md:hidden text-gray-500 hover:text-gray-900 focus:outline-none"
                                x-data @click="$dispatch('toggle-sidebar')">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                            <h1 class="ml-3 text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        </div>

                        <div class="flex items-center">
                            <div class="relative" x-data="{ isOpen: false }">
                                <button @click="isOpen = !isOpen"
                                    class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                    <span class="mr-2">{{ Auth::user()->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="isOpen" @click.away="isOpen = false"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                                    style="display: none;">
                                    <a href="#"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                                    <a href="#"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-4 sm:px-6 lg:px-8 py-2">
                <x-flash-messages />
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto py-4 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t mt-auto">
                <div class="mx-auto px-4 sm:px-6 lg:px-8 py-3">
                    <div class="text-center text-xs text-gray-500">
                        &copy; {{ date('Y') }} UPN Veteran Jakarta - Study Tracer System | Tim CDE UPN Veteran Jakarta
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @stack('scripts')
    <script>
        function copyShareLink(link) {
            navigator.clipboard.writeText(link).then(() => {
                // Extract questionnaire ID from link
                const questionnaireParts = link.split('/');
                const questionnaireSlug = questionnaireParts[questionnaireParts.length - 1];
                
                // Get all toasts and find one that contains our slug
                const toasts = document.querySelectorAll('[id^="toast-"]');
                let toastElement = null;
                
                toasts.forEach(toast => {
                    if (toast.innerHTML.includes(questionnaireSlug)) {
                        toastElement = toast;
                    }
                });
                
                if (toastElement) {
                    toastElement.classList.remove('hidden');
                    setTimeout(() => {
                        hideToast(toastElement.id);
                    }, 3000);
                }
            });
        }
        
        function hideToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('hidden');
            }
        }
        
        function handlePublish(event, questionnaireId, formUrl) {
            // Prevent default form submission
            event.preventDefault();
            
            // Get the form
            const form = document.getElementById(`publish-form-${questionnaireId}`);
            const formData = new FormData(form);
            
            // Add X-Requested-With header for Laravel to detect AJAX
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Publikasi gagal');
                }
                return response.json();
            })
            .then(data => {
                // Show success toast
                const toast = document.getElementById(`toast-publish-${questionnaireId}`);
                toast.classList.remove('hidden');
                
                // After 2.5 seconds
                setTimeout(() => {
                    // Hide toast
                    hideToast(`toast-publish-${questionnaireId}`);
                    
                    // Open new tab with form URL (use URL from response if available)
                    const url = data && data.url ? data.url : formUrl;
                    window.open(url, '_blank');
                    
                    // Refresh current page after 0.2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 200);
                }, 2500);
            })
            .catch(error => {
                console.error('Error:', error);
                // Submit form normally if fetch fails
                form.submit();
            });
        }
    </script>
    @yield('scripts')
</body>

</html>
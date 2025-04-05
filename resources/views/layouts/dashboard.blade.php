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

    <!-- Global Toast Container -->
    <div id="global-toast-container" class="fixed top-4 right-4 z-50 pointer-events-none w-full">
        <!-- Toast notifications will be injected here -->
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

            showGlobalToast("Berhasil!", "Tautan berhasil disalin ke clipboard.");
        });
    }

    function hideToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('hidden');
        }
    }

    function showGlobalToast(title, message, duration = 3000) {
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastHTML = `
                <div id="${toastId}" class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden mb-3 transform transition-all duration-300 ease-out">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900">${title}</p>
                                <p class="mt-1 text-sm text-gray-500">${message}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button onclick="document.getElementById('${toastId}').remove()"
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        // Append to container
        const container = document.getElementById('global-toast-container');
        container.insertAdjacentHTML('beforeend', toastHTML);

        // Get the toast element
        const toast = document.getElementById(toastId);

        // Add entrance animation
        setTimeout(() => {
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);

        return toastId;
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
                // Show success toast using the global toast function
                showGlobalToast("Berhasil!", "Kuesioner berhasil dipublikasikan.", 2500);

                // After 2.5 seconds
                setTimeout(() => {
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
                // Show error toast
                showGlobalToast("Gagal!", "Terjadi kesalahan saat mempublikasikan kuesioner.", 3000);
                // Submit form normally if fetch fails
                form.submit();
            });
    }

    function handleClose(event, questionnaireId) {
        // Prevent default form submission
        event.preventDefault();

        // Get the form
        const form = document.getElementById(`close-form-${questionnaireId}`);
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
                    throw new Error('Penutupan kuesioner gagal');
                }
                return response.json();
            })
            .then(data => {
                // Show success toast using the global toast function
                showGlobalToast("Berhasil!", "Kuesioner berhasil ditutup.", 2000);

                // After 2 seconds, refresh the page
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error toast
                showGlobalToast("Gagal!", "Terjadi kesalahan saat menutup kuesioner.", 3000);
                // Submit form normally if fetch fails
                form.submit();
            });
    }
    </script>
    @yield('scripts')
</body>

</html>
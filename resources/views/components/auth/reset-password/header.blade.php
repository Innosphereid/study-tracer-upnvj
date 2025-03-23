<div class="flex flex-col items-center mb-8 group transition-all duration-300">
    <img src="{{ asset('logo-upnvj.png') }}" alt="UPNVJ Logo"
        class="h-24 mb-6 transform transition-transform duration-500 group-hover:scale-105">
    <h1 class="text-2xl md:text-3xl font-semibold font-display text-center text-gray-800 mb-2">
        {{ $title ?? 'Reset Password' }}
    </h1>
    <p class="text-gray-500 text-center max-w-sm">
        {{ $description ?? 'Recover your account access securely' }}
    </p>
</div>
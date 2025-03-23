<div class="text-center">
    <div class="mb-6 flex justify-center">
        <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
    </div>

    <h2 class="text-2xl font-semibold text-gray-800 mb-2">
        {{ __('Password Reset Successfully') }}
    </h2>

    <p class="text-gray-600 mb-8">
        {{ __('Your password has been reset successfully. You can now login with your new password.') }}
    </p>

    <a href="{{ route('login') }}"
        class="inline-flex justify-center items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 transition-all duration-300 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]">
        {{ __('Back to Login') }}
    </a>
</div>
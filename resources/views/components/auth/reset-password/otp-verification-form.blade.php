<div>
    @if (session('status'))
    <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-600 border border-green-200">
        {{ session('status') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-600 border border-red-200">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mb-6 text-center">
        <p class="text-gray-600">
            {{ __('We\'ve sent a 6-digit verification code to your email') }}
        </p>
        <p class="text-gray-600 font-medium">
            {{ $email ?? 'your email' }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.verify-otp') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? '' }}">

        <div class="input-group">
            <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Enter Verification Code') }}
            </label>
            <x-form.otp-input />
            <x-form.input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition-all duration-300 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]"
            x-data="{ loading: false }" x-bind:class="{ 'opacity-75 cursor-wait': loading }" @click="loading = true">
            <template x-if="loading">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </template>
            {{ __('Verify Code') }}
        </button>

        <div class="text-center">
            <div x-data="{ canResend: false }" x-init="setTimeout(() => { canResend = true }, 60000)" class="text-sm">
                <span class="text-gray-600">
                    {{ __('Didn\'t receive a code?') }}
                </span>
                <button type="button"
                    x-bind:class="canResend ? 'text-indigo-600 hover:text-indigo-800 cursor-pointer' : 'text-gray-400 cursor-not-allowed'"
                    x-bind:disabled="!canResend"
                    @click="if (canResend) { $el.closest('form').action = '{{ route('password.resend-otp') }}'; $el.closest('form').submit(); }">
                    {{ __('Resend Code') }}
                </button>
            </div>
        </div>
    </form>
</div>
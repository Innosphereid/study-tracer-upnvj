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

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <div class="input-group">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Email Address') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <x-form.text-input id="email" name="email" type="email" value="{{ old('email') }}"
                    placeholder="Enter your email address" required autofocus />
            </div>
            <x-form.input-error :messages="$errors->get('email')" class="mt-2" />
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
            {{ __('Send OTP Code') }}
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 transition-colors duration-200">
                {{ __('Back to Login') }}
            </a>
        </div>
    </form>
</div>
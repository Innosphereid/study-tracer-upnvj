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

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6"
        x-data="{ password: '', password_confirmation: '' }">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? '' }}">
        <input type="hidden" name="token" value="{{ $token ?? '' }}">

        <div class="input-group">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('New Password') }}
            </label>
            <x-form.password-input id="password" name="password" x-model="password"
                placeholder="Enter your new password" required autofocus />
            <x-form.input-error :messages="$errors->get('password')" class="mt-2" />
            <x-auth.password-strength-meter />
        </div>

        <div class="input-group">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Confirm Password') }}
            </label>
            <x-form.password-input id="password_confirmation" name="password_confirmation"
                x-model="password_confirmation" placeholder="Confirm your new password" required />
            <div class="mt-1 text-xs" x-show="password && password_confirmation">
                <span x-show="password === password_confirmation" class="text-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-3 w-3 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Passwords match
                </span>
                <span x-show="password !== password_confirmation" class="text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-3 w-3 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Passwords do not match
                </span>
            </div>
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
            {{ __('Set New Password') }}
        </button>
    </form>
</div>
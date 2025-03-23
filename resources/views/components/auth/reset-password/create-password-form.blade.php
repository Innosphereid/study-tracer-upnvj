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

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" x-data="{ 
            password: '', 
            password_confirmation: '',
            passwordsMatch: true,
            checkPasswordsMatch() {
                this.passwordsMatch = (this.password === this.password_confirmation) || !this.password_confirmation;
            }
        }" x-on:input.debounce.300ms="checkPasswordsMatch()">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? '' }}">
        <input type="hidden" name="token" value="{{ $token ?? '' }}">

        <div class="input-group">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('New Password') }}
            </label>
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 flex items-center pl-6 pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" name="password" x-model="password" x-bind:type="show ? 'text' : 'password'"
                    @keydown.window.escape="show = false"
                    class="w-full rounded-lg border-gray-300 pl-14 pr-12 py-3 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-300 ease-in-out text-base"
                    placeholder="Enter your new password" required autofocus />
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400 hover:text-gray-600 transition-colors"
                    @click="show = !show">
                    <template x-if="!show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>
                    <template x-if="show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </template>
                </button>
            </div>
            <x-form.input-error :messages="$errors->get('password')" class="mt-2" />

            <!-- Pass the password directly to the password strength meter -->
            <div x-data="passwordStrength()" x-init="watchPassword" x-effect="checkPasswordStrength(password)"
                class="mt-2">
                <div class="flex items-center space-x-1">
                    <div :class="{
                            'bg-red-300': passwordScore < 2,
                            'bg-yellow-300': passwordScore >= 2 && passwordScore < 4,
                            'bg-green-400': passwordScore >= 4
                        }" class="h-1 w-1/4 rounded-full transition-colors duration-300">
                    </div>
                    <div :class="{
                            'bg-gray-200': passwordScore < 2,
                            'bg-yellow-300': passwordScore >= 2 && passwordScore < 4,
                            'bg-green-400': passwordScore >= 4
                        }" class="h-1 w-1/4 rounded-full transition-colors duration-300">
                    </div>
                    <div :class="{
                            'bg-gray-200': passwordScore < 3,
                            'bg-yellow-300': passwordScore >= 3 && passwordScore < 4,
                            'bg-green-400': passwordScore >= 4
                        }" class="h-1 w-1/4 rounded-full transition-colors duration-300">
                    </div>
                    <div :class="{
                            'bg-gray-200': passwordScore < 4,
                            'bg-green-400': passwordScore >= 4
                        }" class="h-1 w-1/4 rounded-full transition-colors duration-300">
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-1">Password must contain:</p>
                    <div class="grid grid-cols-2 gap-1">
                        <div class="flex items-center">
                            <svg :class="{ 'text-green-500': lengthValid, 'text-gray-300': !lengthValid }"
                                class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span :class="{ 'text-green-500': lengthValid, 'text-gray-500': !lengthValid }"
                                class="text-xs">
                                At least 8 characters
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg :class="{ 'text-green-500': numberValid, 'text-gray-300': !numberValid }"
                                class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span :class="{ 'text-green-500': numberValid, 'text-gray-500': !numberValid }"
                                class="text-xs">
                                At least 1 number
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg :class="{ 'text-green-500': uppercaseValid, 'text-gray-300': !uppercaseValid }"
                                class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span :class="{ 'text-green-500': uppercaseValid, 'text-gray-500': !uppercaseValid }"
                                class="text-xs">
                                At least 1 uppercase
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg :class="{ 'text-green-500': symbolValid, 'text-gray-300': !symbolValid }"
                                class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span :class="{ 'text-green-500': symbolValid, 'text-gray-500': !symbolValid }"
                                class="text-xs">
                                At least 1 special character
                            </span>
                        </div>
                    </div>
                </div>

                <script>
                function passwordStrength() {
                    return {
                        passwordScore: 0,
                        lengthValid: false,
                        uppercaseValid: false,
                        numberValid: false,
                        symbolValid: false,

                        watchPassword() {
                            // We'll use x-effect to watch the password value
                        },

                        checkPasswordStrength(password) {
                            if (!password) {
                                this.resetValidation();
                                return;
                            }

                            // Reset score
                            let score = 0;

                            // Check length
                            this.lengthValid = password.length >= 8;
                            if (this.lengthValid) score++;

                            // Check for uppercase
                            this.uppercaseValid = /[A-Z]/.test(password);
                            if (this.uppercaseValid) score++;

                            // Check for numbers
                            this.numberValid = /\d/.test(password);
                            if (this.numberValid) score++;

                            // Check for special characters
                            this.symbolValid = /[^A-Za-z0-9]/.test(password);
                            if (this.symbolValid) score++;

                            this.passwordScore = score;
                        },

                        resetValidation() {
                            this.passwordScore = 0;
                            this.lengthValid = false;
                            this.uppercaseValid = false;
                            this.numberValid = false;
                            this.symbolValid = false;
                        }
                    };
                }
                </script>
            </div>
        </div>

        <div class="input-group">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Confirm Password') }}
            </label>
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 flex items-center pl-6 pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password_confirmation" name="password_confirmation" x-model="password_confirmation"
                    x-bind:type="show ? 'text' : 'password'" @keydown.window.escape="show = false"
                    class="w-full rounded-lg border-gray-300 pl-14 pr-12 py-3 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-300 ease-in-out text-base"
                    placeholder="Confirm your new password" required />
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400 hover:text-gray-600 transition-colors"
                    @click="show = !show">
                    <template x-if="!show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>
                    <template x-if="show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </template>
                </button>
            </div>
            <div class="mt-1 text-xs" x-show="password && password_confirmation">
                <div x-show="passwordsMatch" class="text-green-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Passwords match</span>
                </div>
                <div x-show="!passwordsMatch" class="text-red-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Passwords do not match</span>
                </div>
            </div>
        </div>

        <button type="submit" x-bind:disabled="!passwordsMatch"
            class="w-full flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition-all duration-300 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]"
            x-data="{ loading: false }" x-bind:class="{ 'opacity-75 cursor-wait': loading }"
            @click="if (passwordsMatch) loading = true">
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
<form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ loading: false }"
    @submit="loading = true">
    @csrf

    @if($errors->any())
    <x-alert type="error" dismissible>
        {{ __('Invalid credentials. Please try again.') }}
    </x-alert>
    @endif

    <x-input-field type="text" name="username" label="Username or Email" placeholder="Enter your username or email"
        :value="old('username')" required autofocus :error="$errors->first('username')" icon="fas fa-user" />

    <x-password-field :error="$errors->first('password')" />

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember_me" name="remember" type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                {{ __('Remember me') }}
            </label>
        </div>
    </div>

    <div>
        <x-button type="submit" variant="primary" size="lg" class="w-full" :loading="true" loadingText="Signing in..."
            x-bind:loading="loading">
            {{ __('Sign in') }}
        </x-button>
    </div>
</form>
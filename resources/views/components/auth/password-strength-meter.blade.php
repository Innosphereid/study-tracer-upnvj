<div x-data="passwordStrength()" class="mt-2">
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
                <svg :class="{ 'text-green-500': lengthValid, 'text-gray-300': !lengthValid }" class="h-3 w-3 mr-1"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span :class="{ 'text-green-500': lengthValid, 'text-gray-500': !lengthValid }" class="text-xs">
                    At least 8 characters
                </span>
            </div>
            <div class="flex items-center">
                <svg :class="{ 'text-green-500': numberValid, 'text-gray-300': !numberValid }" class="h-3 w-3 mr-1"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span :class="{ 'text-green-500': numberValid, 'text-gray-500': !numberValid }" class="text-xs">
                    At least 1 number
                </span>
            </div>
            <div class="flex items-center">
                <svg :class="{ 'text-green-500': uppercaseValid, 'text-gray-300': !uppercaseValid }"
                    class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span :class="{ 'text-green-500': uppercaseValid, 'text-gray-500': !uppercaseValid }" class="text-xs">
                    At least 1 uppercase
                </span>
            </div>
            <div class="flex items-center">
                <svg :class="{ 'text-green-500': symbolValid, 'text-gray-300': !symbolValid }" class="h-3 w-3 mr-1"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span :class="{ 'text-green-500': symbolValid, 'text-gray-500': !symbolValid }" class="text-xs">
                    At least 1 special character
                </span>
            </div>
        </div>
    </div>

    <script>
    function passwordStrength() {
        return {
            password: '',
            passwordScore: 0,
            lengthValid: false,
            uppercaseValid: false,
            numberValid: false,
            symbolValid: false,

            init() {
                this.$watch('password', (value) => {
                    this.checkPasswordStrength(value);
                });
            },

            checkPasswordStrength(password) {
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
            }
        };
    }
    </script>
</div>
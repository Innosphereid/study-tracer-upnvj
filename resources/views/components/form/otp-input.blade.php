<div class="flex flex-col space-y-4" x-data="otpInput()">
    <div class="flex justify-center space-x-3">
        <template x-for="(field, index) in fields" :key="index">
            <input type="text" inputmode="numeric" maxlength="1" x-ref="otp-field" x-model="field.value"
                @input="handleInput(index, $event)" @keydown="handleKeyDown(index, $event)" @paste="handlePaste($event)"
                class="w-12 h-14 text-center text-xl font-bold rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-300 ease-in-out shadow-sm">
        </template>
    </div>

    <input type="hidden" id="otp" name="otp" x-model="otp" />

    <div x-data="{ remainingTime: 600, formatTime() { const minutes = Math.floor(this.remainingTime / 60); const seconds = this.remainingTime % 60; return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`; } }"
        x-init="setInterval(() => { if (remainingTime > 0) remainingTime--; }, 1000)" class="text-center">
        <span class="text-sm text-gray-500">
            Code expires in <span x-text="formatTime()" class="font-medium"></span>
        </span>
    </div>

    <script>
    function otpInput() {
        return {
            fields: Array(6).fill().map(() => ({
                value: ''
            })),
            otp: '',

            handleInput(index, event) {
                const input = event.target;

                // Only allow numbers
                this.fields[index].value = input.value.replace(/[^0-9]/g, '');

                // Update the hidden field with the complete OTP
                this.otp = this.fields.map(field => field.value).join('');

                // Move to next field if a digit was entered
                if (this.fields[index].value && index < 5) {
                    this.$refs['otp-field'][index + 1].focus();
                }
            },

            handleKeyDown(index, event) {
                // Handle backspace
                if (event.key === 'Backspace') {
                    if (!this.fields[index].value && index > 0) {
                        this.fields[index - 1].value = '';
                        this.$refs['otp-field'][index - 1].focus();
                    }
                }

                // Handle left arrow
                if (event.key === 'ArrowLeft' && index > 0) {
                    this.$refs['otp-field'][index - 1].focus();
                }

                // Handle right arrow
                if (event.key === 'ArrowRight' && index < 5) {
                    this.$refs['otp-field'][index + 1].focus();
                }
            },

            handlePaste(event) {
                event.preventDefault();

                const pasted = (event.clipboardData || window.clipboardData).getData('text');
                const digits = pasted.replace(/\D/g, '').split('').slice(0, 6);

                digits.forEach((digit, i) => {
                    if (i < 6) {
                        this.fields[i].value = digit;
                    }
                });

                this.otp = this.fields.map(field => field.value).join('');

                // Focus on the next empty field or the last field
                const nextEmptyIndex = this.fields.findIndex(field => !field.value);
                const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
                this.$refs['otp-field'][focusIndex].focus();
            }
        };
    }
    </script>
</div>
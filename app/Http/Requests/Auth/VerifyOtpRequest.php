<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Ensure the OTP is formatted correctly by joining the request data if needed
        if (!$this->has('otp') && $this->has('otp_1') && $this->has('otp_2') && $this->has('otp_3') && 
            $this->has('otp_4') && $this->has('otp_5') && $this->has('otp_6')) {
            
            $otpDigits = [
                $this->input('otp_1'),
                $this->input('otp_2'),
                $this->input('otp_3'),
                $this->input('otp_4'),
                $this->input('otp_5'),
                $this->input('otp_6'),
            ];
            
            $this->merge(['otp' => implode('', $otpDigits)]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'alamat email',
            'otp' => 'kode OTP',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'otp.size' => 'Kode OTP harus terdiri dari 6 digit.',
            'otp.regex' => 'Kode OTP hanya boleh terdiri dari angka.',
        ];
    }

    /**
     * Handle rate limiting for OTP verification.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts('verify-otp:'.$this->input('email').'|'.$this->ip(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn('verify-otp:'.$this->input('email').'|'.$this->ip());

        throw ValidationException::withMessages([
            'otp' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Hit the rate limiter.
     *
     * @return void
     */
    public function hitRateLimiter(): void
    {
        RateLimiter::hit('verify-otp:'.$this->input('email').'|'.$this->ip());
    }

    /**
     * Get the rate limiting throttle key.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::transliterate('verify-otp|'.$this->input('email').'|'.$this->ip());
    }
}
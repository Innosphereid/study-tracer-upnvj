<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendResetLinkRequest extends FormRequest
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
        ];
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
        ];
    }

    /**
     * Handle rate limiting for password reset requests.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts('password-reset:'.$this->input('email').'|'.$this->ip(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn('password-reset:'.$this->input('email').'|'.$this->ip());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
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
        RateLimiter::hit('password-reset:'.$this->input('email').'|'.$this->ip());
    }

    /**
     * Get the rate limiting throttle key.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::transliterate('password-reset|'.$this->input('email').'|'.$this->ip());
    }
}
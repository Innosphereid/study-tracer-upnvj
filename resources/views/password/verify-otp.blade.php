@extends('layouts.auth')

@section('title', 'Verify OTP | UPNVJ Study Tracer System')
@section('description', 'Verify your OTP code to reset your password for the UPNVJ Study Tracer System.')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="py-12 px-8 md:px-12">
        <x-auth.reset-password.header title="Verify OTP" description="Enter the verification code sent to your email" />
        <x-auth.reset-password.otp-verification-form :email="$email ?? ''" />
    </div>
</div>
@endsection
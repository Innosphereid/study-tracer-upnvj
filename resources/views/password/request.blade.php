@extends('layouts.auth')

@section('title', 'Forgot Password | UPNVJ Study Tracer System')
@section('description', 'Reset your password for the UPNVJ Study Tracer System.')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="py-12 px-8 md:px-12">
        <x-auth.reset-password.header title="Forgot Password"
            description="Enter your email to receive a verification code" />
        <x-auth.reset-password.request-form />
    </div>
</div>
@endsection
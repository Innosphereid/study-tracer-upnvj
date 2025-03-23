@extends('layouts.auth')

@section('title', 'Reset Password | UPNVJ Study Tracer System')
@section('description', 'Create a new password for your account in the UPNVJ Study Tracer System.')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="py-12 px-8 md:px-12">
        <x-auth.reset-password.header title="Create New Password"
            description="Please create a strong password for your account" />
        <x-auth.reset-password.create-password-form :email="$email ?? ''" :token="$token ?? ''" />
    </div>
</div>
@endsection
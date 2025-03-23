@extends('layouts.auth')

@section('title', 'Password Reset Successfully | UPNVJ Study Tracer System')
@section('description', 'Your password has been reset successfully for the UPNVJ Study Tracer System.')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="py-12 px-8 md:px-12">
        <x-auth.reset-password.header title="Password Reset" description="Your password has been reset successfully" />
        <x-auth.reset-password.success-message />
    </div>
</div>
@endsection
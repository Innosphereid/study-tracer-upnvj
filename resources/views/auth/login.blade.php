@extends('layouts.auth')

@section('content')
@section('title', 'Login | UPNVJ Study Tracer System')
@section('description', 'Secure login portal for UPNVJ Study Tracer System. Access alumni tracking and management
tools.')

<div class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
    <!-- Login Form Section -->
    <div class="w-full md:w-1/2 py-12 px-8 md:px-12">
        <x-auth.login-header />
        <x-auth.login-form />
    </div>

    <!-- Illustration Section -->
    <div class="hidden md:block md:w-1/2 bg-gradient-to-br from-blue-500 to-indigo-600">
        <x-auth.login-illustration />
    </div>
</div>
@endsection
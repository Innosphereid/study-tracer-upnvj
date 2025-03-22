@extends('layouts.auth')

@section('title', 'Login - UPNVJ Study Tracer | Portal Admin Tim CDE')
@section('meta_description', 'Portal login resmi untuk sistem pengelolaan Study Tracer UPN Veteran Jakarta. Dikhususkan
bagi admin dan superadmin Tim CDE.')

@section('content')
<div class="bg-white shadow-xl rounded-lg overflow-hidden transform transition-all animate-fade-in">
    <!-- Header with Logo -->
    <div class="px-6 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 text-center">
        <x-logo size="lg" class="mx-auto mb-4" />
        <h1 class="text-2xl font-bold text-gray-800">Study Tracer Management System</h1>
        <p class="text-gray-600 mt-1">Tim CDE UPN Veteran Jakarta</p>
    </div>

    <!-- Login Form Section -->
    <div class="p-6 md:p-8 bg-white">
        <h2 class="text-xl font-semibold text-gray-700 mb-6">Admin Login</h2>

        @include('auth.partials.login-form')
    </div>

    <!-- Additional Info -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
        <div class="text-sm text-center text-gray-600">
            <p>This portal is exclusively for authorized<br>administration personnel only.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Additional micro-animations for the login page
document.addEventListener('DOMContentLoaded', function() {
    // Sequential fade-in animation for form elements
    const elements = document.querySelectorAll('.animate-fade-in');
    elements.forEach((el, index) => {
        el.style.animationDelay = `${0.1 + (index * 0.1)}s`;
    });
});
</script>
@endpush
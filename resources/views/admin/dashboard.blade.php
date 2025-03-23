@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-100 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Questionnaire Management</h2>
                <p class="text-gray-700 mb-4">Distribute and manage study tracer questionnaires to alumni.</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Manage Questionnaires →</a>
            </div>

            <div class="bg-purple-100 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Response Analytics</h2>
                <p class="text-gray-700 mb-4">View and analyze questionnaire responses.</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium">View Analytics →</a>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>

            <div class="bg-gray-50 p-6 rounded-lg shadow">
                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-white rounded shadow">
                        <div class="rounded-full w-10 h-10 bg-blue-200 flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-700" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">New questionnaire created: "Graduate Employment Survey 2025"</p>
                            <p class="text-sm text-gray-500">2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-center p-3 bg-white rounded shadow">
                        <div class="rounded-full w-10 h-10 bg-green-200 flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-700" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">125 questionnaire invitations sent to Computer Science alumni</p>
                            <p class="text-sm text-gray-500">Yesterday at 3:45 PM</p>
                        </div>
                    </div>

                    <div class="flex items-center p-3 bg-white rounded shadow">
                        <div class="rounded-full w-10 h-10 bg-yellow-200 flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-700" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Monthly report generated: "February 2025 Response Analysis"</p>
                            <p class="text-sm text-gray-500">Mar 1, 2025</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">SuperAdmin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-indigo-100 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">User Management</h2>
                <p class="text-gray-700 mb-4">Manage administrators and user accounts.</p>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Manage Users →</a>
            </div>

            <div class="bg-blue-100 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Questionnaire Management</h2>
                <p class="text-gray-700 mb-4">Create and manage study tracer questionnaires.</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Manage Questionnaires →</a>
            </div>

            <div class="bg-green-100 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Activity Logs</h2>
                <p class="text-gray-700 mb-4">View system and user activity logs.</p>
                <a href="#" class="text-green-600 hover:text-green-800 font-medium">View Logs →</a>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">System Overview</h2>

            <div class="bg-gray-50 p-6 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded shadow">
                        <span class="text-gray-500 text-sm">Total Alumni</span>
                        <p class="text-2xl font-bold">2,458</p>
                    </div>

                    <div class="bg-white p-4 rounded shadow">
                        <span class="text-gray-500 text-sm">Response Rate</span>
                        <p class="text-2xl font-bold">68%</p>
                    </div>

                    <div class="bg-white p-4 rounded shadow">
                        <span class="text-gray-500 text-sm">Active Questionnaires</span>
                        <p class="text-2xl font-bold">3</p>
                    </div>

                    <div class="bg-white p-4 rounded shadow">
                        <span class="text-gray-500 text-sm">Administrators</span>
                        <p class="text-2xl font-bold">12</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
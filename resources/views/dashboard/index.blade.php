@extends('layouts.dashboard')

@section('title', 'Dashboard - UPNVJ Study Tracer System')
@section('page-title', 'Dashboard')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-5">
        Selamat Datang di Dashboard Tracer Study UPNVJ
    </h2>

    <!-- Statistik Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-dashboard.stat-card title="Alumni Terdaftar" value="2,458" icon="academic-cap" trend="up" trendValue="3.5%"
            iconBackground="bg-indigo-500" />

        <x-dashboard.stat-card title="Kuesioner Aktif" value="12" icon="clipboard-list" trend="up" trendValue="16.7%"
            iconBackground="bg-green-500" />

        <x-dashboard.stat-card title="Tingkat Respons" value="68.2%" icon="chart-bar" trend="up" trendValue="5.1%"
            iconBackground="bg-blue-500" />

        @if(auth()->user()->role === 'superadmin')
        <x-dashboard.stat-card title="Admin Aktif" value="8" icon="shield-check" trend="up" trendValue="14.2%"
            iconBackground="bg-purple-500" />
        @else
        <x-dashboard.stat-card title="Alumni Sudah Bekerja" value="76.4%" icon="users" trend="up" trendValue="2.3%"
            iconBackground="bg-yellow-500" />
        @endif
    </div>

    <!-- Grafik dan Aktivitas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Grafik Respons Kuesioner -->
        <div class="lg:col-span-2">
            <x-dashboard.chart-card title="Tren Respons Kuesioner" subtitle="Data 6 bulan terakhir">
                <canvas id="response-chart"></canvas>

                @push('scripts')
                <script>
                const ctx = document.getElementById('response-chart').getContext('2d');
                const responseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Respons Kuesioner',
                            data: [42, 55, 49, 65, 73, 68],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
                </script>
                @endpush
            </x-dashboard.chart-card>
        </div>

        <!-- Aktivitas Terbaru -->
        <div>
            <x-dashboard.activity-log :activities="$activities ?? []" title="Aktivitas Terbaru" />
        </div>
    </div>

    <!-- Distribusi Alumni -->
    <div class="mb-6">
        <x-dashboard.chart-card title="Distribusi Alumni per Fakultas"
            subtitle="Berdasarkan data tracer study terakhir">
            <canvas id="faculty-distribution-chart"></canvas>

            @push('scripts')
            <script>
            const facultyCtx = document.getElementById('faculty-distribution-chart').getContext('2d');
            const facultyChart = new Chart(facultyCtx, {
                type: 'bar',
                data: {
                    labels: ['FEB', 'FISIP', 'FH', 'FK', 'FT', 'FIKES', 'FIK'],
                    datasets: [{
                        label: 'Jumlah Alumni',
                        data: [452, 386, 325, 298, 412, 275, 310],
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(139, 92, 246, 0.7)',
                            'rgba(236, 72, 153, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            </script>
            @endpush
        </x-dashboard.chart-card>
    </div>

    <!-- Quick Links -->
    <h3 class="text-lg font-medium text-gray-900 mb-4">Akses Cepat</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <x-dashboard.quick-link title="Buat Kuesioner Baru"
            description="Buat dan publikasikan kuesioner tracer study baru" icon="document-add" route="#"
            buttonText="Buat Kuesioner" color="indigo" />

        <x-dashboard.quick-link title="Lihat Hasil Tracer" description="Analisis dan ekspor hasil dari tracer study"
            icon="chart-bar" route="#" buttonText="Lihat Hasil" color="blue" />

        @if(auth()->user()->role === 'superadmin')
        <x-dashboard.quick-link title="Kelola Admin" description="Tambah, edit, dan kelola akun admin" icon="user-add"
            route="#" buttonText="Kelola Admin" color="purple" />
        @else
        <x-dashboard.quick-link title="Data Alumni" description="Lihat dan kelola data alumni terdaftar"
            icon="academic-cap" route="#" buttonText="Akses Data" color="green" />
        @endif
    </div>

    <!-- Status Pekerjaan Alumni atau Log Admin (berdasarkan role) -->
    <div class="mb-6">
        @if(auth()->user()->role === 'superadmin')
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Log Aktivitas Admin</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Admin
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aktivitas
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Detail
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Budi Santoso
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Membuat kuesioner baru
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Tracer Study Angkatan 2020
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    2 jam yang lalu
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Dian Pratiwi
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Mengupdate kuesioner
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Tracer Study Angkatan 2019
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    4 jam yang lalu
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Ahmad Rizki
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Mengekspor data
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Laporan Tracer Study 2023
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    6 jam yang lalu
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-right">
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Lihat semua log aktivitas &rarr;
                    </a>
                </div>
            </div>
        </div>
        @else
        <x-dashboard.chart-card title="Status Pekerjaan Alumni"
            subtitle="Distribusi status pekerjaan alumni berdasarkan data tracer study">
            <canvas id="employment-status-chart"></canvas>

            @push('scripts')
            <script>
            const employmentCtx = document.getElementById('employment-status-chart').getContext('2d');
            const employmentChart = new Chart(employmentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Bekerja (Swasta)', 'Bekerja (Pemerintah)', 'Wirausaha', 'Lanjut Studi',
                        'Belum Bekerja'
                    ],
                    datasets: [{
                        data: [45, 25, 15, 10, 5],
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
            </script>
            @endpush
        </x-dashboard.chart-card>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@endsection
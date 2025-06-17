@extends('layouts.app')

@section('title', 'CEO Dashboard')
@section('page-title', 'CEO Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Selamat Datang, {{ auth()->user()->first_name }}!</h3>
                        <p class="mb-0">Berikut adalah ringkasan kinerja perusahaan hari ini</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-crown fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($total_employees ?? 0) }}</h3>
                        <p class="text-muted mb-0">Total Karyawan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-building fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($total_departments ?? 0) }}</h3>
                        <p class="text-muted mb-0">Departemen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">Rp {{ number_format($monthly_payroll ?? 0, 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0">Payroll Bulanan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($attendance_rate ?? 0, 1) }}%</h3>
                        <p class="text-muted mb-0">Tingkat Kehadiran</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Tren Kinerja Bulanan</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi Departemen</h5>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Pengajuan Cuti Pending</h5>
                <span class="badge bg-warning">{{ count($recent_leaves ?? []) }}</span>
            </div>
            <div class="card-body">
                @if(isset($recent_leaves) && count($recent_leaves) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recent_leaves as $leave)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $leave->user->full_name ?? 'N/A' }}</h6>
                                    <p class="mb-1 text-muted">{{ $leave->leaveType->name ?? 'N/A' }}</p>
                                    <small class="text-muted">{{ $leave->start_date ?? 'N/A' }} - {{ $leave->end_date ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-warning">Pending</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Tidak ada pengajuan cuti yang pending</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Statistik Departemen</h5>
            </div>
            <div class="card-body">
                @if(isset($department_stats) && count($department_stats) > 0)
                    @foreach($department_stats as $dept)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">{{ $dept->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $dept->employees_count ?? 0 }} karyawan</small>
                        </div>
                        <div class="text-end">
                            <div class="progress" style="width: 100px; height: 8px;">
                                @php
                                    $maxEmployees = $department_stats && $department_stats->count() > 0 ? $department_stats->max('employees_count') : 1;
                                    $percentage = $maxEmployees > 0 ? (($dept->employees_count ?? 0) / $maxEmployees * 100) : 0;
                                @endphp
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $percentage }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data departemen</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Trends Chart
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyTrendsData = @json($monthly_trends ?? []);

new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: monthlyTrendsData.map(item => item.month),
        datasets: [{
            label: 'Total Karyawan',
            data: monthlyTrendsData.map(item => item.employees),
            borderColor: 'rgb(54, 162, 235)', // Blue
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            tension: 0.1
        }, {
            label: 'Total Kehadiran',
            data: monthlyTrendsData.map(item => item.attendance),
            borderColor: 'rgb(75, 192, 192)', // Teal
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Department Chart
const deptCtx = document.getElementById('departmentChart').getContext('2d');
new Chart(deptCtx, {
    type: 'doughnut',
    data: {
        labels: @json($department_stats ? $department_stats->pluck('name')->toArray() : []),
        datasets: [{
            data: @json($department_stats ? $department_stats->pluck('employees_count')->toArray() : []),
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endpush

<!-- Akses Laporan Lengkap Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2 text-primary"></i>Akses Laporan Lengkap</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('reports.hr') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-users me-2 text-info"></i>
                            Laporan Sumber Daya Manusia (HR)
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('reports.attendance') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-clock me-2 text-success"></i>
                            Laporan Absensi Karyawan
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('reports.financial') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-invoice-dollar me-2 text-danger"></i>
                            Laporan Keuangan
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('payroll.reports') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-money-check-alt me-2 text-warning"></i>
                            Laporan Penggajian
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('reports.leaves') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-alt me-2 text-purple"></i>
                            Laporan Cuti Karyawan
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

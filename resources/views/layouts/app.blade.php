<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sistem Penggajian RHI</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
        }
        
        .content-wrapper {
            padding: 30px;
            flex: 1;
        }

        .footer {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 30px;
            margin-top: auto;
            text-align: center;
        }

        .footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer .company-info {
            font-size: 0.9rem;
        }

        .footer .footer-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer .footer-links a:hover {
            color: white;
        }

        .footer .copyright {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Pagination improvements */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-color: var(--primary-color);
            color: white;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            cursor: pointer; /* Indicate it's clickable */
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Improve mobile toggle button */
        #sidebarToggle {
            padding: 8px 12px;
            font-size: 1.2rem;
            color: #667eea;
            transition: all 0.2s ease;
        }

        #sidebarToggle:hover {
            color: #764ba2;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1001; /* Higher than overlay */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .footer .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .footer .footer-links {
                justify-content: center;
                gap: 15px;
            }

            .footer .company-info {
                font-size: 0.8rem;
            }

            .footer .footer-links a {
                font-size: 0.8rem;
            }
        }

        /* Enhanced notification styles */
        #flash-messages {
            position: fixed !important;
            top: 80px !important;
            right: 20px !important;
            z-index: 1060 !important;
            width: 350px !important;
            max-width: 90vw !important;
        }

        #flash-messages .alert {
            margin-bottom: 10px !important;
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            animation: slideInRight 0.3s ease-out !important;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loading button state */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Ensure notifications are above modals */
        .modal {
            z-index: 1050;
        }

        #flash-messages {
            z-index: 1070 !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-building me-2"></i>RHI</h4>
            <small>Sistem Penggajian</small>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                
                @auth
                    @unless(Auth::user()->hasRole('ceo'))
                        @if(auth()->user()->hasPermission('users.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="fas fa-users me-2"></i>Manajemen User
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasPermission('employees.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employees*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                                <i class="fas fa-user-tie me-2"></i>Data Karyawan
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasPermission('departments.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('departments*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                <i class="fas fa-sitemap me-2"></i>Departemen
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasPermission('positions.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('positions*') ? 'active' : '' }}" href="{{ route('positions.index') }}">
                                <i class="fas fa-briefcase me-2"></i>Posisi Jabatan
                            </a>
                        </li>
                        @endif
                    @endunless

                    {{-- Absensi link - Visible to CEO and others --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance*') ? 'active' : '' }}" href="{{ route('attendance.index') }}">
                            <i class="fas fa-clock me-2"></i>Absensi
                        </a>
                    </li>

                    @unless(Auth::user()->hasRole('ceo'))
                        <!-- Jadwal Kerja Menu - Karyawan hanya bisa lihat, Admin/HR bisa kelola -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schedules*') ? 'active' : '' }}" href="{{ route('schedules.index') }}">
                                <i class="fas fa-calendar-alt me-2"></i>
                                @if(auth()->user()->hasRole('karyawan'))
                                    Jadwal Saya
                                @else
                                    Jadwal Kerja
                                @endif
                            </a>
                        </li>

                        <!-- Shift Kerja Menu - Hanya untuk Admin/HR -->
                        @if(auth()->user()->hasPermission('shifts.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('shifts*') ? 'active' : '' }}" href="{{ route('shifts.index') }}">
                                <i class="fas fa-business-time me-2"></i>Shift Kerja
                            </a>
                        </li>
                        @endif

                        <!-- Kantor Menu - Hanya untuk Admin/HR -->
                        @if(auth()->user()->hasPermission('offices.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('offices*') ? 'active' : '' }}" href="{{ route('offices.index') }}">
                                <i class="fas fa-building me-2"></i>Kantor
                            </a>
                        </li>
                        @endif
                    @endunless

                    {{-- Penggajian & Slip Gaji - Visible to CEO (for reports) and others based on permissions --}}
                    @if(Auth::user()->hasRole('ceo') && Auth::user()->hasPermission('reports.view')) {{-- CEO specific link to reports index --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.index') || request()->routeIs('reports.financial') || request()->routeIs('reports.hr') || request()->routeIs('reports.leaves') || request()->routeIs('payroll.reports') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>Laporan
                            </a>
                        </li>
                    @elseif(!Auth::user()->hasRole('ceo'))
                        @if(auth()->user()->hasPermission('payroll.view_all'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll.index') || request()->routeIs('payroll.reports') ? 'active' : '' }}" href="{{ route('payroll.index') }}">
                                <i class="fas fa-money-bill-wave me-2"></i>Penggajian
                            </a>
                        </li>
                        @elseif(auth()->user()->hasPermission('payroll.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll.slip*') ? 'active' : '' }}" href="{{ route('payroll.slip') }}">
                                <i class="fas fa-receipt me-2"></i>Slip Gaji
                            </a>
                        </li>
                        @endif
                    @endif

                    @unless(Auth::user()->hasRole('ceo'))
                        @if(auth()->user()->hasPermission('salary_components.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('salary-components*') ? 'active' : '' }}" href="{{ route('salary-components.index') }}">
                                <i class="fas fa-coins me-2"></i>Komponen Gaji
                            </a>
                        </li>
                        @endif
                    @endunless

                    {{-- Izin & Cuti - Visible to CEO and others --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('permits*') ? 'active' : '' }}" href="{{ route('permits.index') }}">
                            <i class="fas fa-file-alt me-2"></i>Izin & Cuti
                        </a>
                    </li>

                    @unless(Auth::user()->hasRole('ceo'))
                    <!-- Manajemen Lembur - Hanya untuk Admin/HRD -->
                    @if(auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR']) || auth()->user()->hasPermission('overtime.manage'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('permits.overtime.pending', 'permits.overtime.management', 'permits.overtime.reports') ? 'active' : '' }}"
                           href="#" id="overtimeManagementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-clock me-2"></i>Manajemen Lembur
                            @php
                                $pendingCount = \App\Models\OvertimeRequest::where('status', 'pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="overtimeManagementDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('permits.overtime.pending') ? 'active' : '' }}" href="{{ route('permits.overtime.pending') }}">
                                <i class="fas fa-hourglass-half me-2"></i>Persetujuan Lembur
                                @if($pendingCount > 0)
                                    <span class="badge bg-warning ms-2">{{ $pendingCount }}</span>
                                @endif
                            </a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('permits.overtime.management') ? 'active' : '' }}" href="{{ route('permits.overtime.management') }}">
                                <i class="fas fa-cogs me-2"></i>Kelola Semua Lembur
                            </a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('permits.overtime.reports') ? 'active' : '' }}" href="{{ route('permits.overtime.reports') }}">
                                <i class="fas fa-chart-bar me-2"></i>Laporan Lembur
                            </a></li>
                        </ul>
                    </li>
                    @elseif(auth()->user()->hasAnyRole(['Manager']) || auth()->user()->hasPermission('overtime.approve'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('permits.overtime.pending') ? 'active' : '' }}" href="{{ route('permits.overtime.pending') }}">
                            <i class="fas fa-clock me-2"></i>Persetujuan Lembur
                            @php
                                $pendingCount = \App\Models\OvertimeRequest::where('status', 'pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                    @endif

                    <!-- Manajemen Cuti - Hanya untuk Admin/HRD -->
                    @if(auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR']) || auth()->user()->hasPermission('leave.manage'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('permits.leave.pending', 'permits.leave.management', 'permits.leave.reports') ? 'active' : '' }}"
                           href="#" id="leaveManagementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-calendar-times me-2"></i>Manajemen Cuti
                            @php
                                $pendingLeaveCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
                            @endphp
                            @if($pendingLeaveCount > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingLeaveCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="leaveManagementDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('permits.leave.pending') ? 'active' : '' }}" href="{{ route('permits.leave.pending') }}">
                                <i class="fas fa-hourglass-half me-2"></i>Persetujuan Cuti
                                @if($pendingLeaveCount > 0)
                                    <span class="badge bg-warning ms-2">{{ $pendingLeaveCount }}</span>
                                @endif
                            </a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('permits.leave.management') ? 'active' : '' }}" href="{{ route('permits.leave.management') }}">
                                <i class="fas fa-cogs me-2"></i>Kelola Semua Cuti
                            </a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('permits.leave.reports') ? 'active' : '' }}" href="{{ route('permits.leave.reports') }}">
                                <i class="fas fa-chart-bar me-2"></i>Laporan Cuti
                            </a></li>
                        </ul>
                    </li>
                    @endif
                    @endunless

                    @unless(Auth::user()->hasRole('ceo'))
                    <!-- Demo Menu -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('demo*') ? 'active' : '' }}" href="{{ route('demo.features') }}">
                            <i class="fas fa-rocket me-2"></i>Demo Fitur
                        </a>
                    </li>

                    <!-- Debug Menu - Temporary -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('debug*') ? 'active' : '' }}" href="{{ route('debug.permissions') }}">
                            <i class="fas fa-bug me-2"></i>Debug Permissions
                        </a>
                    </li>
                    @endunless
                @endauth
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                    <button class="btn btn-link d-md-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="page-title d-none d-md-block">@yield('page-title', 'Dashboard')</h5>
                </div>
                
                @auth
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>{{ auth()->user()->first_name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="fas fa-user me-2"></i>Profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content-wrapper">
            <!-- Flash Messages -->
            <div id="flash-messages" class="position-fixed" style="top: 80px; right: 20px; z-index: 9999; width: 350px;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="company-info">
                    <strong><i class="fas fa-building me-2"></i>PT. RHI (Rumah Halal Indonesia)</strong>
                    <div class="mt-1">
                        <i class="fas fa-map-marker-alt me-2"></i>Jakarta, Indonesia
                        <span class="mx-2">|</span>
                        <i class="fas fa-phone me-2"></i>+62 21 1234 5678
                        <span class="mx-2">|</span>
                        <i class="fas fa-envelope me-2"></i>info@rhi.co.id
                    </div>
                </div>
                <div class="footer-links">
                    <a href="#"><i class="fas fa-question-circle me-1"></i>Bantuan</a>
                    <a href="#"><i class="fas fa-shield-alt me-1"></i>Kebijakan Privasi</a>
                    <a href="#"><i class="fas fa-file-contract me-1"></i>Syarat & Ketentuan</a>
                </div>
            </div>
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <i class="fas fa-copyright me-1"></i>{{ date('Y') }} PT. RHI (Rumah Halal Indonesia). Semua hak dilindungi.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <small>Sistem Absensi & Penggajian Karyawan v1.0</small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Sidebar functionality for mobile
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');

        // Function to open sidebar
        function openSidebar() {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling

            // Change toggle button icon to close icon
            const toggleIcon = sidebarToggle?.querySelector('i');
            if (toggleIcon) {
                toggleIcon.className = 'fas fa-times';
            }
        }

        // Function to close sidebar
        function closeSidebar() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling

            // Change toggle button icon back to hamburger
            const toggleIcon = sidebarToggle?.querySelector('i');
            if (toggleIcon) {
                toggleIcon.className = 'fas fa-bars';
            }
        }

        // Toggle sidebar when hamburger button is clicked
        sidebarToggle?.addEventListener('click', function() {
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        // Close sidebar when overlay is clicked
        sidebarOverlay?.addEventListener('click', function() {
            closeSidebar();
        });

        // Close sidebar when escape key is pressed
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Close sidebar when clicking on sidebar links (for navigation)
        sidebar?.addEventListener('click', function(e) {
            if (e.target.classList.contains('nav-link') && window.innerWidth <= 768) {
                closeSidebar();
            }
        });

        // Handle window resize - close sidebar if screen becomes larger
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Touch gesture support for closing sidebar
        let touchStartX = 0;
        let touchStartY = 0;

        sidebar?.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });

        sidebar?.addEventListener('touchmove', function(e) {
            if (!sidebar.classList.contains('show')) return;

            const touchCurrentX = e.touches[0].clientX;
            const touchCurrentY = e.touches[0].clientY;
            const deltaX = touchCurrentX - touchStartX;
            const deltaY = touchCurrentY - touchStartY;

            // If swipe left is detected and it's more horizontal than vertical
            if (deltaX < -50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                closeSidebar();
            }
        });

        // Enhanced notification system
        function showNotification(message, type = 'success') {
            console.log('showNotification called:', message, type); // Debug log

            const alertTypes = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };

            const icons = {
                'success': 'fas fa-check-circle',
                'error': 'fas fa-exclamation-triangle',
                'warning': 'fas fa-exclamation-triangle',
                'info': 'fas fa-info-circle'
            };

            const alertHtml = `
                <div class="alert ${alertTypes[type]} alert-dismissible fade show shadow" role="alert">
                    <i class="${icons[type]} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            const flashContainer = document.getElementById('flash-messages');
            if (flashContainer) {
                console.log('Flash container found, adding notification'); // Debug log
                flashContainer.insertAdjacentHTML('beforeend', alertHtml);

                // Auto-hide after 5 seconds
                setTimeout(function() {
                    const alerts = flashContainer.querySelectorAll('.alert');
                    if (alerts.length > 0) {
                        alerts[alerts.length - 1].classList.remove('show');
                        setTimeout(() => {
                            if (alerts[alerts.length - 1].parentNode) {
                                alerts[alerts.length - 1].remove();
                            }
                        }, 150);
                    }
                }, 5000);
            } else {
                console.error('Flash container not found!'); // Debug log
            }
        }

        // Auto-hide existing alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('#flash-messages .alert');
            console.log('Auto-hiding alerts:', alerts.length); // Debug log
            alerts.forEach(alert => {
                alert.classList.remove('show');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 150);
            });
        }, 5000);

        // Debug: Check if flash messages exist on page load
        document.addEventListener('DOMContentLoaded', function() {
            const flashContainer = document.getElementById('flash-messages');
            const alerts = flashContainer ? flashContainer.querySelectorAll('.alert') : [];
            console.log('Flash messages on page load:', alerts.length);
            if (alerts.length > 0) {
                console.log('Flash message types:', Array.from(alerts).map(a => a.className));
            }

            // Check for URL parameters for notifications
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('approved') === '1') {
                const message = urlParams.get('message') || 'Pengajuan berhasil disetujui!';
                console.log('Showing approval notification from URL:', message);

                // Always show notification, try multiple methods
                setTimeout(() => {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(message, 'success');
                    } else {
                        // Fallback: create notification directly
                        const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                                <i class="fas fa-check-circle me-2"></i>${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        if (flashContainer) {
                            flashContainer.insertAdjacentHTML('beforeend', alertHtml);
                        } else {
                            // Last resort: show simple toast
                            window.showSimpleToast(message, 'success');
                        }
                    }
                }, 100);

                // Clean URL by removing the parameters
                const cleanUrl = window.location.pathname + (window.location.search.replace(/[?&](approved|message)=[^&]*/g, '').replace(/^&/, '?').replace(/^\?$/, ''));
                window.history.replaceState({}, document.title, cleanUrl);
            }

            if (urlParams.get('rejected') === '1') {
                const message = urlParams.get('message') || 'Pengajuan berhasil ditolak!';
                console.log('Showing rejection notification from URL:', message);

                // Always show notification, try multiple methods
                setTimeout(() => {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(message, 'success');
                    } else {
                        // Fallback: create notification directly
                        const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="animation: slideInRight 0.3s ease-out;">
                                <i class="fas fa-check-circle me-2"></i>${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        if (flashContainer) {
                            flashContainer.insertAdjacentHTML('beforeend', alertHtml);
                        } else {
                            // Last resort: show simple toast
                            window.showSimpleToast(message, 'success');
                        }
                    }
                }, 100);

                // Clean URL by removing the parameters
                const cleanUrl = window.location.pathname + (window.location.search.replace(/[?&](rejected|message)=[^&]*/g, '').replace(/^&/, '?').replace(/^\?$/, ''));
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });

        // Global function for AJAX success notifications
        window.showNotification = showNotification;

        // Simple toast notification as ultimate fallback
        window.showSimpleToast = function(message, type = 'success') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                font-family: Arial, sans-serif;
                max-width: 300px;
                word-wrap: break-word;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        };
    </script>
    
    @stack('scripts')
</body>
</html>

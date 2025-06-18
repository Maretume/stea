<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;

use App\Models\Leave;
use App\Models\Department;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first();

        if (!$role) {
            return view('dashboard.default');
        }

        switch ($role->nama_kunci) { // name -> nama_kunci
            case 'ceo':
                return $this->ceoDashboard();
            case 'cfo':
                return $this->cfoDashboard();
            case 'hrd':
                return $this->hrdDashboard();
            case 'personalia':
                return $this->personaliaDashboard();
            case 'karyawan':
                return $this->karyawanDashboard();
            default:
                return view('dashboard.default');
        }
    }

    private function ceoDashboard()
    {
        $data = [
            'total_employees' => Employee::where('status_kepegawaian', 'aktif')->count(), // employment_status -> status_kepegawaian, active -> aktif
            'total_departments' => Department::where('aktif', true)->count(), // is_active -> aktif

            'attendance_rate' => $this->getAttendanceRate(),
            'recent_leaves' => LeaveRequest::with(['user', 'leaveType']) // Leave -> LeaveRequest (new model)
                ->where('status', 'menunggu') // pending -> menunggu
                ->latest('dibuat_pada') // created_at -> dibuat_pada
                ->take(5)
                ->get(),
            'department_stats' => $this->getDepartmentStats(),
            'monthly_trends' => $this->getMonthlyTrends(),
        ];

        return view('dashboard.ceo', $data);
    }

    private function cfoDashboard()
    {
        $data = [
            'total_employees' => Employee::where('status_kepegawaian', 'aktif')->count(),
            'total_departments' => Department::where('aktif', true)->count(),
            'attendance_today' => Attendance::where('tanggal', today())->count(), // date -> tanggal
            'pending_leaves' => LeaveRequest::where('status', 'menunggu')->count(), // Leave -> LeaveRequest, pending -> menunggu
        ];

        return view('dashboard.cfo', $data);
    }

    private function hrdDashboard()
    {
        $data = [
            'total_employees' => Employee::where('status_kepegawaian', 'aktif')->count(),
            'new_employees_this_month' => Employee::whereMonth('tanggal_rekrut', now()->month) // hire_date -> tanggal_rekrut
                ->whereYear('tanggal_rekrut', now()->year) // hire_date -> tanggal_rekrut
                ->count(),
            'pending_leaves' => LeaveRequest::where('status', 'menunggu')->count(), // Leave -> LeaveRequest, pending -> menunggu
            'attendance_today' => Attendance::where('tanggal', today()) // date -> tanggal
                ->where('status', 'hadir') // present -> hadir
                ->count(),
            'recent_employees' => Employee::with(['user', 'department', 'position']) // relations use translated keys
                ->latest('dibuat_pada') // created_at -> dibuat_pada
                ->take(5)
                ->get(),
            'leave_requests' => LeaveRequest::with(['user', 'leaveType']) // Leave -> LeaveRequest
                ->where('status', 'menunggu') // pending -> menunggu
                ->latest('dibuat_pada') // created_at -> dibuat_pada
                ->take(10)
                ->get(),
            'attendance_summary' => $this->getAttendanceSummary(),
        ];

        return view('dashboard.hrd', $data);
    }

    private function personaliaDashboard()
    {
        $data = [
            'employees_count' => Employee::where('status_kepegawaian', 'aktif')->count(),
            'attendance_today' => Attendance::where('tanggal', today())->count(), // date -> tanggal
            'absent_today' => $this->getAbsentToday(),
            'late_today' => Attendance::where('tanggal', today()) // date -> tanggal
                ->where('menit_terlambat', '>', 0) // late_minutes -> menit_terlambat
                ->count(),
            'recent_attendance' => Attendance::with('user') // User relation uses id_pengguna
                ->where('tanggal', today()) // date -> tanggal
                ->latest('dibuat_pada') // created_at -> dibuat_pada (assuming Attendance has this, or use 'jam_masuk')
                ->take(10)
                ->get(),
            'upcoming_leaves' => LeaveRequest::with(['user', 'leaveType']) // Leave -> LeaveRequest
                ->where('status', 'disetujui') // approved -> disetujui
                ->where('tanggal_mulai', '>=', today()) // start_date -> tanggal_mulai
                ->orderBy('tanggal_mulai') // start_date -> tanggal_mulai
                ->take(10)
                ->get(),
        ];

        return view('dashboard.personalia', $data);
    }

    private function karyawanDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'today_attendance' => Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                ->where('tanggal', today()) // date -> tanggal
                ->first(),
            'monthly_attendance' => Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                ->whereMonth('tanggal', now()->month) // date -> tanggal
                ->whereYear('tanggal', now()->year)   // date -> tanggal
                ->get()
                ->map(function($attendance) {
                    return [
                        'date' => $attendance->tanggal->format('Y-m-d'), // date -> tanggal
                        'total_work_minutes' => $attendance->total_menit_kerja ?? 0, // total_work_minutes -> total_menit_kerja
                    ];
                })
                ->toArray(),

            'leave_balance' => $this->getLeaveBalance($user->id),
            'recent_leaves' => LeaveRequest::where('id_pengguna', $user->id) // Leave -> LeaveRequest, user_id -> id_pengguna
                ->with('leaveType') // leaveType relation uses id_jenis_cuti
                ->latest('dibuat_pada') // Assuming created_at is 'dibuat_pada'
                ->take(5)
                ->get(),
            'attendance_stats' => $this->getUserAttendanceStats($user->id),
        ];

        return view('dashboard.karyawan', $data);
    }

    private function getAttendanceRate()
    {
        $totalWorkingDays = now()->day; // This logic might need review for accuracy
        $totalEmployees = Employee::where('status_kepegawaian', 'aktif')->count(); // employment_status -> status_kepegawaian, active -> aktif
        $totalExpectedAttendance = $totalWorkingDays * $totalEmployees;
        
        $actualAttendance = Attendance::whereMonth('tanggal', now()->month) // date -> tanggal
            ->whereYear('tanggal', now()->year)   // date -> tanggal
            ->where('status', 'hadir')          // present -> hadir
            ->count();
            
        return $totalExpectedAttendance > 0 ? round(($actualAttendance / $totalExpectedAttendance) * 100, 2) : 0;
    }

    private function getDepartmentStats()
    {
        return Department::withCount(['employees' => function($q) { // employees relation uses id_departemen
            $q->whereHas('user', function($q2) { // user relation uses id_pengguna
                $q2->where('status', 'aktif'); // active -> aktif
            });
        }])->where('aktif', true)->get(); // is_active -> aktif
    }

    private function getMonthlyTrends()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'), // Consider localizing month name if displayed directly
                'employees' => Employee::whereMonth('tanggal_rekrut', '<=', $date->month) // hire_date -> tanggal_rekrut
                    ->whereYear('tanggal_rekrut', '<=', $date->year) // hire_date -> tanggal_rekrut
                    ->where('status_kepegawaian', 'aktif') // employment_status -> status_kepegawaian, active -> aktif
                    ->count(),
                'attendance' => Attendance::whereMonth('tanggal', $date->month) // date -> tanggal
                    ->whereYear('tanggal', $date->year)   // date -> tanggal
                    ->count(),
            ];
        }
        return $months;
    }



    private function getAttendanceSummary()
    {
        $today = today();
        return [
            'present' => Attendance::where('tanggal', $today)->where('status', 'hadir')->count(), // date -> tanggal, present -> hadir
            'late' => Attendance::where('tanggal', $today)->where('status', 'terlambat')->count(), // date -> tanggal, late -> terlambat
            'absent' => $this->getAbsentToday(),
            'sick' => Attendance::where('tanggal', $today)->where('status', 'sakit')->count(), // date -> tanggal, sick -> sakit
            'leave' => Attendance::where('tanggal', $today)->where('status', 'cuti')->count(), // date -> tanggal, leave -> cuti
        ];
    }

    private function getAbsentToday()
    {
        $totalEmployees = Employee::where('status_kepegawaian', 'aktif')->count(); // employment_status -> status_kepegawaian, active -> aktif
        $presentToday = Attendance::where('tanggal', today())->count(); // date -> tanggal
        return $totalEmployees - $presentToday;
    }

    private function getLeaveBalance($userId)
    {
        // Simplified leave balance calculation - return annual leave balance as number
        // Leave -> LeaveRequest
        $usedAnnualLeave = LeaveRequest::where('id_pengguna', $userId) // user_id -> id_pengguna
            ->whereYear('tanggal_mulai', now()->year) // start_date -> tanggal_mulai
            ->where('status', 'disetujui') // approved -> disetujui
            ->sum('total_hari'); // total_days -> total_hari

        // Assuming default 12 days, should ideally come from LeaveType->maks_hari_per_tahun
        // This might need joining with leave_types or a more complex query if different leave types have different max days.
        // For simplicity, keeping 12 as a placeholder.
        return max(0, 12 - $usedAnnualLeave);
    }

    private function getUserAttendanceStats($userId)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        return [
            'present' => Attendance::where('id_pengguna', $userId) // user_id -> id_pengguna
                ->whereMonth('tanggal', $currentMonth) // date -> tanggal
                ->whereYear('tanggal', $currentYear)   // date -> tanggal
                ->where('status', 'hadir')           // present -> hadir
                ->count(),
            'late' => Attendance::where('id_pengguna', $userId) // user_id -> id_pengguna
                ->whereMonth('tanggal', $currentMonth) // date -> tanggal
                ->whereYear('tanggal', $currentYear)   // date -> tanggal
                ->where('menit_terlambat', '>', 0)    // late_minutes -> menit_terlambat
                ->count(),
            'absent' => Attendance::where('id_pengguna', $userId) // user_id -> id_pengguna
                ->whereMonth('tanggal', $currentMonth) // date -> tanggal
                ->whereYear('tanggal', $currentYear)   // date -> tanggal
                ->where('status', 'absen')           // absent -> absen
                ->count(),
        ];
    }
}

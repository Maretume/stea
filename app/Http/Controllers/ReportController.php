<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function hr(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $employeeCount = Employee::where('status_kepegawaian', 'aktif')->count(); // employment_status -> status_kepegawaian, active -> aktif
        $newEmployees = Employee::whereBetween('tanggal_rekrut', [$startDate, $endDate])->count(); // hire_date -> tanggal_rekrut
        $resignedEmployees = Employee::where('status_kepegawaian', 'mengundurkan_diri') // employment_status -> status_kepegawaian, resigned -> mengundurkan_diri
                                   ->whereBetween('diperbarui_pada', [$startDate, $endDate]) // updated_at -> diperbarui_pada
                                   ->count();
        
        // Using LeaveRequest as the current model for leave data
        $leaveRequests = LeaveRequest::whereBetween('dibuat_pada', [$startDate, $endDate]) // created_at -> dibuat_pada
                             ->selectRaw('status, COUNT(*) as count') // status values are already Indonesian in pengajuan_cuti
                             ->groupBy('status')
                             ->pluck('count', 'status')
                             ->toArray();
        
        $departmentStats = Employee::join('departemen', 'karyawan.id_departemen', '=', 'departemen.id') // departments -> departemen, employees.department_id -> karyawan.id_departemen
                                 ->where('karyawan.status_kepegawaian', 'aktif') // employees.employment_status -> karyawan.status_kepegawaian, active -> aktif
                                 ->selectRaw('departemen.nama, COUNT(*) as count') // departments.name -> departemen.nama
                                 ->groupBy('departemen.id', 'departemen.nama') // departments.id, departments.name -> departemen.id, departemen.nama
                                 ->get();
        
        return view('reports.hr', compact(
            'employeeCount', 'newEmployees', 'resignedEmployees', 
            'leaveRequests', 'departmentStats', 'startDate', 'endDate'
        ));
    }

    public function attendance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        
        // Assuming relations user, employee, department are correctly set up in respective models
        $query = Attendance::with('user.employee.department')
                          ->whereBetween('tanggal', [$startDate, $endDate]); // date -> tanggal
        
        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('id_departemen', $departmentId); // department_id -> id_departemen
            });
        }
        
        $attendances = $query->paginate(20); // Original query modified by count() calls below, need to clone or re-query for pagination
        
        // Re-query for stats to avoid pagination issues on counts
        $statsQuery = Attendance::with('user.employee.department')
                                ->whereBetween('tanggal', [$startDate, $endDate]);
        if ($departmentId) {
            $statsQuery->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('id_departemen', $departmentId);
            });
        }

        $stats = [
            'total_present' => (clone $statsQuery)->where('status', 'hadir')->count(),    // present -> hadir
            'total_late' => (clone $statsQuery)->where('status', 'terlambat')->count(), // late -> terlambat
            'total_absent' => (clone $statsQuery)->where('status', 'absen')->count(),    // absent -> absen
            'total_leave' => (clone $statsQuery)->where('status', 'cuti')->count(),     // leave -> cuti
        ];
        
        $departments = \App\Models\Department::where('aktif', true)->get(); // is_active -> aktif
        
        return view('reports.attendance', compact(
            'attendances', 'stats', 'departments', 'startDate', 'endDate', 'departmentId'
        ));
    }

    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $payrollStats = Payroll::whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                                    // Assuming payrollPeriod relation exists on Payroll model and PayrollPeriod model has tanggal_mulai
                                    $q->whereBetween('tanggal_mulai', [$startDate, $endDate]);
                                 })
                              ->selectRaw('
                                  SUM(gaji_kotor) as total_gross,           // gross_salary -> gaji_kotor
                                  SUM(total_tunjangan) as total_allowances, // total_allowances -> total_tunjangan
                                  SUM(total_potongan) as total_deductions,  // total_deductions -> total_potongan
                                  SUM(gaji_bersih) as total_net,            // net_salary -> gaji_bersih
                                  COUNT(DISTINCT id_pengguna) as total_employees_in_payroll // Count distinct users in payroll
                              ')
                              ->first();
        
        $departmentPayroll = Payroll::join('pengguna', 'penggajian.id_pengguna', '=', 'pengguna.id') // users -> pengguna, payrolls.user_id -> penggajian.id_pengguna
                                   ->join('karyawan', 'pengguna.id', '=', 'karyawan.id_pengguna') // employees -> karyawan, users.id -> pengguna.id, employees.user_id -> karyawan.id_pengguna
                                   ->join('departemen', 'karyawan.id_departemen', '=', 'departemen.id') // departments -> departemen, employees.department_id -> karyawan.id_departemen
                                   ->join('periode_penggajian', 'penggajian.id_periode_penggajian', '=', 'periode_penggajian.id') // Join with payroll_periods table
                                   ->whereBetween('periode_penggajian.tanggal_mulai', [$startDate, $endDate]) // Filter by period start date from joined table
                                   ->selectRaw('
                                       departemen.nama as department_name,      // departments.name -> departemen.nama
                                       SUM(penggajian.gaji_bersih) as total_salary, // payrolls.net_salary -> penggajian.gaji_bersih
                                       COUNT(DISTINCT penggajian.id_pengguna) as employee_count // Count distinct users
                                   ')
                                   ->groupBy('departemen.id', 'departemen.nama') // departments.id, departments.name -> departemen.id, departemen.nama
                                   ->get();
        
        return view('reports.financial', compact(
            'payrollStats', 'departmentPayroll', 'startDate', 'endDate'
        ));
    }

    public function export(Request $request, $type)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        switch ($type) {
            case 'attendance':
                return $this->exportAttendance($startDate, $endDate);
            case 'payroll':
                return $this->exportPayroll($startDate, $endDate);
            case 'employees':
                return $this->exportEmployees();
            default:
                return redirect()->back()->with('error', 'Jenis ekspor tidak valid.'); // Invalid export type.
        }
    }

    private function exportAttendance($startDate, $endDate)
    {
        // Relations user, employee, department are assumed to be translated in their respective models
        $attendances = Attendance::with('user.employee.department')
                                ->whereBetween('tanggal', [$startDate, $endDate]) // date -> tanggal
                                ->get();
        
        $filename = "laporan_absensi_{$startDate}_sampai_{$endDate}.csv"; // attendance_report -> laporan_absensi, to -> sampai
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            // CSV Headers translated
            fputcsv($file, ['Tanggal', 'ID Karyawan', 'Nama', 'Departemen', 'Jam Masuk', 'Jam Keluar', 'Status']);
            
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->tanggal, // date -> tanggal
                    $attendance->user->id_karyawan, // employee_id -> id_karyawan
                    $attendance->user->nama_depan . ' ' . $attendance->user->nama_belakang, // full_name -> nama_depan, nama_belakang
                    $attendance->user->employee->department->nama ?? '', // department.name -> department.nama
                    $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i:s') : '', // clock_in -> jam_masuk
                    $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i:s') : '', // clock_out -> jam_keluar
                    $attendance->status, // status is already Indonesian from model
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportPayroll($startDate, $endDate)
    {
        // Relations user, employee, department, payrollPeriod are assumed to be translated
        $payrolls = Payroll::with('user.employee.department', 'payrollPeriod')
                          ->whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                              $q->whereBetween('tanggal_mulai', [$startDate, $endDate]); // payrollPeriod.start_date -> payrollPeriod.tanggal_mulai
                          })
                          ->get();
        
        $filename = "laporan_penggajian_{$startDate}_sampai_{$endDate}.csv"; // payroll_report -> laporan_penggajian
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            // CSV Headers translated
            fputcsv($file, ['ID Karyawan', 'Nama', 'Departemen', 'Periode', 'Gaji Kotor', 'Total Tunjangan', 'Total Potongan', 'Gaji Bersih']);
            
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->id_karyawan, // employee_id -> id_karyawan
                    $payroll->user->nama_depan . ' ' . $payroll->user->nama_belakang,
                    $payroll->user->employee->department->nama ?? '', // department.name -> department.nama
                    // payrollPeriod relation and its attributes are translated
                    $payroll->payrollPeriod->tanggal_mulai->format('Y-m-d') . ' - ' . $payroll->payrollPeriod->tanggal_selesai->format('Y-m-d'),
                    $payroll->gaji_kotor,         // gross_salary -> gaji_kotor
                    $payroll->total_tunjangan,    // total_allowances -> total_tunjangan
                    $payroll->total_potongan,     // total_deductions -> total_potongan
                    $payroll->gaji_bersih,        // net_salary -> gaji_bersih
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportEmployees()
    {
        // Relations user, department, position are assumed to be translated
        $employees = Employee::with('user', 'department', 'position')->get();
        
        $filename = "laporan_karyawan_" . now()->format('Y-m_d') . ".csv"; // employees_report -> laporan_karyawan
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            // CSV Headers translated
            fputcsv($file, ['ID Karyawan', 'Nama', 'Email', 'Departemen', 'Jabatan', 'Tanggal Rekrut', 'Status', 'Gaji Pokok']);
            
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->user->id_karyawan, // employee_id -> id_karyawan
                    $employee->user->nama_depan . ' ' . $employee->user->nama_belakang,
                    $employee->user->surel,       // email -> surel
                    $employee->department->nama,  // department.name -> department.nama
                    $employee->position->nama,    // position.name -> position.nama
                    $employee->tanggal_rekrut,    // hire_date -> tanggal_rekrut
                    $employee->status_kepegawaian, // employment_status -> status_kepegawaian
                    $employee->gaji_pokok,        // basic_salary -> gaji_pokok
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function hr(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $employeeCount = Employee::where('employment_status', 'active')->count();
        $newEmployees = Employee::whereBetween('hire_date', [$startDate, $endDate])->count();
        $resignedEmployees = Employee::where('employment_status', 'resigned')
                                   ->whereBetween('updated_at', [$startDate, $endDate])
                                   ->count();
        
        $leaveRequests = Leave::whereBetween('created_at', [$startDate, $endDate])
                             ->selectRaw('status, COUNT(*) as count')
                             ->groupBy('status')
                             ->pluck('count', 'status')
                             ->toArray();
        
        $departmentStats = Employee::join('departments', 'employees.department_id', '=', 'departments.id')
                                 ->where('employees.employment_status', 'active')
                                 ->selectRaw('departments.name, COUNT(*) as count')
                                 ->groupBy('departments.id', 'departments.name')
                                 ->get();
        
        return view('reports.hr', compact(
            'employeeCount', 'newEmployees', 'resignedEmployees', 
            'leaveRequests', 'departmentStats', 'startDate', 'endDate'
        ));
    }

    public function leavesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->input('department_id');
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        $query = Leave::with(['user.employee.department', 'leaveType'])
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user.employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(20)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        $statuses = ['pending', 'approved', 'rejected', 'cancelled']; // Or fetch from a config/model if dynamic

        return view('reports.leaves', compact(
            'leaves',
            'startDate',
            'endDate',
            'departmentId',
            'leaveTypeId',
            'status',
            'departments',
            'leaveTypes',
            'statuses'
        ));
    }

    public function attendance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        
        $query = Attendance::with('user.employee.department')
                          ->whereBetween('date', [$startDate, $endDate]);
        
        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $attendances = $query->paginate(20);
        
        $stats = [
            'total_present' => $query->where('status', 'present')->count(),
            'total_late' => $query->where('status', 'late')->count(),
            'total_absent' => $query->where('status', 'absent')->count(),
            'total_leave' => $query->where('status', 'leave')->count(),
        ];
        
        $departments = \App\Models\Department::where('is_active', true)->get();
        
        return view('reports.attendance', compact(
            'attendances', 'stats', 'departments', 'startDate', 'endDate', 'departmentId'
        ));
    }

    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $payrollStats = Payroll::whereBetween('period_start', [$startDate, $endDate])
                              ->selectRaw('
                                  SUM(gross_salary) as total_gross,
                                  SUM(total_allowances) as total_allowances,
                                  SUM(total_deductions) as total_deductions,
                                  SUM(net_salary) as total_net,
                                  COUNT(*) as total_employees
                              ')
                              ->first();
        
        $departmentPayroll = Payroll::join('users', 'payrolls.user_id', '=', 'users.id')
                                   ->join('employees', 'users.id', '=', 'employees.user_id')
                                   ->join('departments', 'employees.department_id', '=', 'departments.id')
                                   ->whereBetween('payrolls.period_start', [$startDate, $endDate])
                                   ->selectRaw('
                                       departments.name as department_name,
                                       SUM(payrolls.net_salary) as total_salary,
                                       COUNT(*) as employee_count
                                   ')
                                   ->groupBy('departments.id', 'departments.name')
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
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    private function exportAttendance($startDate, $endDate)
    {
        $attendances = Attendance::with('user.employee.department')
                                ->whereBetween('date', [$startDate, $endDate])
                                ->get();
        
        $filename = "attendance_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Employee ID', 'Name', 'Department', 'Clock In', 'Clock Out', 'Status']);
            
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date,
                    $attendance->user->employee_id,
                    $attendance->user->full_name,
                    $attendance->user->employee->department->name ?? '',
                    $attendance->clock_in_time,
                    $attendance->clock_out_time,
                    $attendance->status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportPayroll($startDate, $endDate)
    {
        $payrolls = Payroll::with('user.employee.department')
                          ->whereBetween('period_start', [$startDate, $endDate])
                          ->get();
        
        $filename = "payroll_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Department', 'Period', 'Gross Salary', 'Allowances', 'Deductions', 'Net Salary']);
            
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->employee_id,
                    $payroll->user->full_name,
                    $payroll->user->employee->department->name ?? '',
                    $payroll->period_start . ' - ' . $payroll->period_end,
                    $payroll->gross_salary,
                    $payroll->total_allowances,
                    $payroll->total_deductions,
                    $payroll->net_salary,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportEmployees()
    {
        $employees = Employee::with('user', 'department', 'position')->get();
        
        $filename = "employees_report_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Email', 'Department', 'Position', 'Hire Date', 'Status', 'Basic Salary']);
            
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->user->employee_id,
                    $employee->user->full_name,
                    $employee->user->email,
                    $employee->department->name,
                    $employee->position->name,
                    $employee->hire_date,
                    $employee->employment_status,
                    $employee->basic_salary,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}

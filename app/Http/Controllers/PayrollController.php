<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        // Assuming relations 'user', 'payrollPeriod', 'approvedBy' are correctly translated in Payroll model
        $query = Payroll::with(['user', 'payrollPeriod', 'approvedBy']);

        if ($request->filled('period_id')) {
            $query->where('id_periode_penggajian', $request->period_id); // payroll_period_id -> id_periode_penggajian
        }

        if ($request->filled('status')) {
            // Assuming $request->status provides translated ENUM value
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                // Assuming User model uses nama_depan, nama_belakang, id_karyawan
                $q->where('nama_depan', 'like', "%{$search}%")
                  ->orWhere('nama_belakang', 'like', "%{$search}%")
                  ->orWhere('id_karyawan', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('dibuat_pada', 'desc')->paginate(20); // created_at -> dibuat_pada
        $periods = PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get(); // start_date -> tanggal_mulai

        // Calculate summary data
        $total_payroll = $payrolls->sum('gaji_bersih'); // net_salary -> gaji_bersih
        // status ENUMs: 'konsep', 'terhitung', 'disetujui', 'dibayar'
        $processed_count = $payrolls->where('status', 'disetujui')->count() + $payrolls->where('status', 'dibayar')->count(); // approved -> disetujui, paid -> dibayar
        $pending_count = $payrolls->where('status', 'menunggu_persetujuan')->count() + $payrolls->where('status', 'konsep')->count(); // pending -> menunggu_persetujuan (new state?), draft -> konsep
        $total_employees = $payrolls->count(); // This counts payroll records, not unique employees

        return view('payroll.index', compact('payrolls', 'periods', 'total_payroll', 'processed_count', 'pending_count', 'total_employees'));
    }

    public function slip()
    {
        $user = Auth::user();
        // Assuming relations are correctly set up with translated keys
        $payrolls = Payroll::where('id_pengguna', $user->id) // user_id -> id_pengguna
                          ->with('payrollPeriod', 'details.salaryComponent')
                          ->orderBy('dibuat_pada', 'desc') // created_at -> dibuat_pada
                          ->paginate(12);

        return view('payroll.slip', compact('payrolls'));
    }

    public function show(Payroll $payroll)
    {
        // Assuming relations are correctly set up
        $payroll->load(['user', 'payrollPeriod', 'details.salaryComponent']);
        return view('payroll.show', compact('payroll'));
    }

    public function periods()
    {
        // Assuming relations 'createdBy', 'approvedBy' use translated foreign keys
        $periods = PayrollPeriod::with('createdBy', 'approvedBy')
                               ->orderBy('tanggal_mulai', 'desc') // start_date -> tanggal_mulai
                               ->paginate(20);

        return view('payroll.periods.index', compact('periods'));
    }

    public function createPeriod()
    {
        return view('payroll.periods.create');
    }

    public function storePeriod(Request $request)
    {
        // Assuming request field names are still English
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'nullable|date',
        ]);

        // Check for overlapping periods
        $overlapping = PayrollPeriod::where(function($query) use ($request) {
            $query->whereBetween('tanggal_mulai', [$request->start_date, $request->end_date]) // start_date -> tanggal_mulai
                  ->orWhereBetween('tanggal_selesai', [$request->start_date, $request->end_date]) // end_date -> tanggal_selesai
                  ->orWhere(function($q) use ($request) {
                      $q->where('tanggal_mulai', '<=', $request->start_date) // start_date -> tanggal_mulai
                        ->where('tanggal_selesai', '>=', $request->end_date); // end_date -> tanggal_selesai
                  });
        })->exists();

        if ($overlapping) {
            return back()->withErrors(['start_date' => 'Periode ini bertumpang tindih dengan periode yang sudah ada.'])
                        ->withInput();
        }

        PayrollPeriod::create([
            'nama' => $request->name,                   // name -> nama
            'tanggal_mulai' => $request->start_date,    // start_date -> tanggal_mulai
            'tanggal_selesai' => $request->end_date,  // end_date -> tanggal_selesai
            'tanggal_bayar' => $request->pay_date,    // pay_date -> tanggal_bayar
            'status' => 'konsep',                     // draft -> konsep
            'dibuat_oleh' => Auth::id(),              // created_by -> dibuat_oleh
        ]);

        return redirect()->route('payroll.periods.index')
                        ->with('success', 'Periode penggajian berhasil dibuat.'); // Payroll period -> Periode penggajian
    }



    public function calculate(PayrollPeriod $period)
    {
        if ($period->status !== 'konsep') { // draft -> konsep
            return back()->with('error', 'Periode penggajian sudah diproses.'); // Payroll period -> Periode penggajian
        }

        $employees = Employee::where('status_kepegawaian', 'aktif') // employment_status -> status_kepegawaian, active -> aktif
                           ->with(['user.salaryComponents' => function($query) { // salaryComponents relation uses translated keys
                               $query->where('komponen_gaji.aktif', true); // salary_components.is_active -> komponen_gaji.aktif
                           }])
                           ->get();

        return view('payroll.calculate', compact('period', 'employees'));
    }

    public function process(Request $request, PayrollPeriod $period)
    {
        if ($period->status !== 'konsep') { // draft -> konsep
            return back()->with('error', 'Periode penggajian sudah diproses.');
        }

        $employees = Employee::where('status_kepegawaian', 'aktif')->get(); // employment_status -> status_kepegawaian, active -> aktif

        foreach ($employees as $employee) {
            $this->calculateEmployeePayroll($employee, $period);
        }

        $period->update(['status' => 'terhitung']); // calculated -> terhitung

        return redirect()->route('payroll.periods.index')
                        ->with('success', 'Penggajian berhasil dihitung untuk semua karyawan.'); // Payroll -> Penggajian
    }

    private function calculateEmployeePayroll(Employee $employee, PayrollPeriod $period)
    {
        // Get attendance data for the period
        // user_id -> id_pengguna, date -> tanggal, start_date -> tanggal_mulai, end_date -> tanggal_selesai
        $attendances = Attendance::where('id_pengguna', $employee->user->id) // Assuming Employee model has user relation, and User has id
                                ->whereBetween('tanggal', [$period->tanggal_mulai, $period->tanggal_selesai])
                                ->get();

        // ENUM values: ['hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur']
        $totalWorkingDays = $period->tanggal_mulai->diffInDaysFiltered(function(Carbon $date) {
            return !$date->isWeekend();
        }, $period->tanggal_selesai) + 1; // More accurate working days

        $totalPresentDays = $attendances->whereIn('status', ['hadir', 'terlambat', 'pulang_awal', 'setengah_hari'])->count();
        $totalAbsentDays = $attendances->where('status', 'absen')->count();
        $totalLateDays = $attendances->where('status', 'terlambat')->count();
        $totalOvertimeHours = $attendances->sum('menit_lembur') / 60; // overtime_minutes -> menit_lembur

        // Calculate basic salary (prorated if absent)
        $basicSalary = $employee->gaji_pokok; // basic_salary -> gaji_pokok
        // Proration logic might need adjustment based on company policy (e.g., only for 'absen' status)
        $effectivePresentDays = $totalWorkingDays - $totalAbsentDays; // Example: only count non-absent days for proration
        if ($totalWorkingDays > 0 && $effectivePresentDays < $totalWorkingDays) {
             $basicSalary = ($basicSalary / $totalWorkingDays) * $effectivePresentDays;
        }


        // Get salary components
        // User model's salaryComponents relation and SalaryComponent model itself are translated
        $salaryComponents = $employee->user->salaryComponents()->where('komponen_gaji.aktif', true)->get();
        
        $totalAllowances = 0;
        $totalDeductions = 0;
        $overtimeAmount = 0;

        $payrollDetails = [];

        foreach ($salaryComponents as $component) {
            // Use pivot amount if available, otherwise use default calculation
            // Pivot attribute is 'jumlah'
            $customAmount = isset($component->pivot) ? $component->pivot->jumlah : null;
            // calculateAmount in SalaryComponent model uses translated attributes
            $amount = $component->calculateAmount($basicSalary, $customAmount);

            if ($component->tipe === 'tunjangan') { // type 'allowance' -> 'tunjangan'
                $totalAllowances += $amount;
            } elseif ($component->tipe === 'potongan') { // type 'deduction' -> 'potongan'
                $totalDeductions += $amount;
            }
            // Benefits are typically not added to gross/net directly but are company costs.
            // If they affect payslip, logic might differ.

            $payrollDetails[] = [
                'id_komponen_gaji' => $component->id, // salary_component_id -> id_komponen_gaji
                'jumlah' => $amount,                 // amount -> jumlah
                'catatan_perhitungan' => "Dihitung untuk periode {$period->nama}", // calculation_notes -> catatan_perhitungan, period->name -> period->nama
            ];
        }

        // Calculate overtime
        if ($totalOvertimeHours > 0) {
            // Assuming a default overtime rate calculation for now
            // This might come from AttendanceRule or a specific overtime component setting
            $defaultAttendanceRule = AttendanceRule::where('standar', true)->first();
            $overtimeMultiplier = $defaultAttendanceRule ? $defaultAttendanceRule->pengali_lembur : 1.5;
            $overtimeRate = ($employee->gaji_pokok / 173) * $overtimeMultiplier;
            $overtimeAmount = $overtimeRate * $totalOvertimeHours;
        }

        // Calculate gross and net salary
        $grossSalary = $basicSalary + $totalAllowances + $overtimeAmount;
        $taxAmount = $this->calculateTax($grossSalary); // Assuming tax logic remains
        $netSalary = $grossSalary - $totalDeductions - $taxAmount;

        // Create or update payroll record
        $payroll = Payroll::updateOrCreate(
            [
                'id_periode_penggajian' => $period->id, // payroll_period_id -> id_periode_penggajian
                'id_pengguna' => $employee->user->id,       // user_id -> id_pengguna
            ],
            [
                'gaji_pokok' => $basicSalary,               // basic_salary -> gaji_pokok
                'total_tunjangan' => $totalAllowances,      // total_allowances -> total_tunjangan
                'total_potongan' => $totalDeductions,       // total_deductions -> total_potongan
                'jumlah_lembur' => $overtimeAmount,         // overtime_amount -> jumlah_lembur
                'gaji_kotor' => $grossSalary,               // gross_salary -> gaji_kotor
                'jumlah_pajak' => $taxAmount,               // tax_amount -> jumlah_pajak
                'gaji_bersih' => $netSalary,                // net_salary -> gaji_bersih
                'total_hari_kerja' => $totalWorkingDays,    // total_working_days -> total_hari_kerja
                'total_hari_hadir' => $totalPresentDays,    // total_present_days -> total_hari_hadir
                'total_hari_absen' => $totalAbsentDays,     // total_absent_days -> total_hari_absen
                'total_hari_terlambat' => $totalLateDays,   // total_late_days -> total_hari_terlambat
                'total_jam_lembur' => $totalOvertimeHours,  // total_overtime_hours -> total_jam_lembur
                'status' => 'konsep',                       // draft -> konsep
            ]
        );

        // Save payroll details
        $payroll->details()->delete(); // Assumes 'details' relation is correct
        foreach ($payrollDetails as $detail) {
            $payroll->details()->create($detail);
        }
    }

    private function calculateTax($grossSalary)
    {
        // Simplified tax calculation (PPh 21)
        // This should be more complex in real implementation
        $taxableIncome = max(0, $grossSalary - 4500000); // PTKP per month
        
        if ($taxableIncome <= 5000000) {
            return $taxableIncome * 0.05;
        } elseif ($taxableIncome <= 25000000) {
            return 250000 + (($taxableIncome - 5000000) * 0.15);
        } elseif ($taxableIncome <= 50000000) {
            return 3250000 + (($taxableIncome - 25000000) * 0.25);
        } else {
            return 9500000 + (($taxableIncome - 50000000) * 0.30);
        }
    }

    public function approve(Payroll $payroll)
    {
        // Check if payroll is in a state that can be approved
        // Assuming 'draft' -> 'konsep', 'pending' -> 'menunggu_persetujuan' (or similar translated states)
        if (!in_array($payroll->status, ['konsep', 'menunggu_persetujuan'])) {
            return back()->with('error', 'Penggajian tidak dapat disetujui karena status saat ini: ' . $payroll->status);
        }

        $payroll->update([
            'status' => 'disetujui', // approved -> disetujui
            'disetujui_oleh_payroll' => Auth::id(), // approved_by -> disetujui_oleh_payroll
            'disetujui_pada_payroll' => now(),    // approved_at -> disetujui_pada_payroll
        ]);

        return back()->with('success', 'Penggajian berhasil disetujui.');
    }

    public function approvePeriod(PayrollPeriod $period)
    {
        if ($period->status !== 'terhitung') { // calculated -> terhitung
            return back()->with('error', 'Periode penggajian belum dihitung.');
        }

        $period->update([
            'status' => 'disetujui', // approved -> disetujui
            'disetujui_oleh' => Auth::id(), // approved_by -> disetujui_oleh
            'disetujui_pada' => now(),    // approved_at -> disetujui_pada
        ]);

        // Update all payrolls in this period
        $period->payrolls()->update(['status' => 'disetujui']); // approved -> disetujui

        return back()->with('success', 'Periode penggajian berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:penggajian,id' // payrolls -> penggajian
        ]);

        $payrollIds = $request->payroll_ids;

        // Get payrolls that can be approved
        // Assuming 'draft' -> 'konsep', 'pending' -> 'menunggu_persetujuan'
        $payrolls = Payroll::whereIn('id', $payrollIds)
                          ->whereIn('status', ['konsep', 'menunggu_persetujuan'])
                          ->get();

        if ($payrolls->isEmpty()) {
            return back()->with('error', 'Tidak ada penggajian yang dapat disetujui.');
        }

        $approvedCount = 0;
        foreach ($payrolls as $payroll) {
            $payroll->update([
                'status' => 'disetujui', // approved -> disetujui
                'disetujui_oleh_payroll' => Auth::id(), // approved_by -> disetujui_oleh_payroll
                'disetujui_pada_payroll' => now(),    // approved_at -> disetujui_pada_payroll
            ]);
            $approvedCount++;
        }

        return back()->with('success', "Berhasil menyetujui {$approvedCount} penggajian.");
    }

    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfYear()->format('Y-m-d'));

        $payrolls = Payroll::with(['user', 'payrollPeriod']) // Relations use translated keys
                          ->whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                              // payrollPeriod relation uses 'id_periode_penggajian', PayrollPeriod model uses 'tanggal_mulai'
                              $q->whereBetween('tanggal_mulai', [$startDate, $endDate]);
                          })
                          ->get();

        $summary = [
            'total_employees' => $payrolls->groupBy('id_pengguna')->count(), // user_id -> id_pengguna
            'total_gross_salary' => $payrolls->sum('gaji_kotor'), // gross_salary -> gaji_kotor
            'total_net_salary' => $payrolls->sum('gaji_bersih'),   // net_salary -> gaji_bersih
            'total_tax' => $payrolls->sum('jumlah_pajak'),      // tax_amount -> jumlah_pajak
            'total_deductions' => $payrolls->sum('total_potongan'), // total_deductions -> total_potongan
        ];

        return view('payroll.reports', compact('payrolls', 'summary', 'startDate', 'endDate'));
    }

    public function downloadSlip(Payroll $payroll)
    {
        // Relations use translated keys
        $payroll->load(['user', 'payrollPeriod', 'details.salaryComponent']);

        // This would generate a PDF slip
        // For now, return a view
        return view('payroll.slip-pdf', compact('payroll'));
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfYear()->format('Y-m-d'));

        $payrolls = Payroll::with(['user', 'payrollPeriod']) // Relations use translated keys
                          ->whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                              $q->whereBetween('tanggal_mulai', [$startDate, $endDate]); // start_date -> tanggal_mulai
                          })
                          ->get();

        // For now, return JSON data
        // In a real implementation, this would generate Excel/CSV
        return response()->json([
            'data' => $payrolls,
            'summary' => [
                'total_employees' => $payrolls->groupBy('id_pengguna')->count(), // user_id -> id_pengguna
                'total_gross_salary' => $payrolls->sum('gaji_kotor'), // gross_salary -> gaji_kotor
                'total_net_salary' => $payrolls->sum('gaji_bersih'),   // net_salary -> gaji_bersih
                'total_tax' => $payrolls->sum('jumlah_pajak'),      // tax_amount -> jumlah_pajak
                'total_deductions' => $payrolls->sum('total_potongan'), // total_deductions -> total_potongan
            ]
        ]);
    }
}

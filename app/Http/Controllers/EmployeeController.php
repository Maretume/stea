<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\SalaryComponent;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'position', 'supervisor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nama_depan', 'like', "%{$search}%")    // first_name -> nama_depan
                  ->orWhere('nama_belakang', 'like', "%{$search}%") // last_name -> nama_belakang
                  ->orWhere('id_karyawan', 'like', "%{$search}%"); // employee_id -> id_karyawan
            });
        }

        if ($request->filled('department_id')) {
            $query->where('id_departemen', $request->department_id); // department_id -> id_departemen
        }

        if ($request->filled('status')) {
            // Assuming $request->status provides translated values like 'aktif'
            $query->where('status_kepegawaian', $request->status); // employment_status -> status_kepegawaian
        }

        $employees = $query->orderBy('dibuat_pada', 'desc')->paginate(20); // created_at -> dibuat_pada
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        $users = User::whereDoesntHave('employee')->get(); // Assuming 'employee' relation on User is correctly translated
        $supervisors = User::whereHas('employee')->get();  // Assuming 'employee' relation on User is correctly translated
        $shifts = \App\Models\Shift::active()->get();
        $offices = \App\Models\Office::active()->get();

        return view('employees.create', compact('departments', 'users', 'supervisors', 'shifts', 'offices'));
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English
        $request->validate([
            'user_id' => 'required|exists:pengguna,id|unique:karyawan,id_pengguna', // users -> pengguna, employees -> karyawan, user_id -> id_pengguna
            'department_id' => 'required|exists:departemen,id', // departments -> departemen
            'position_id' => 'required|exists:jabatan,id',     // positions -> jabatan
            'supervisor_id' => 'nullable|exists:pengguna,id',   // users -> pengguna
            'hire_date' => 'required|date',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after:contract_start',
            'employment_type' => 'required|in:tetap,kontrak,magang,paruh_waktu', // permanent,contract,internship,freelance -> tetap,kontrak,magang,paruh_waktu
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'bank_account_name' => 'nullable|string|max:100',
            'default_shift_id' => 'required|exists:shift,id', // shifts -> shift
            'default_office_id' => 'nullable|exists:kantor,id', // offices -> kantor
            'default_work_type' => 'required|in:WFO,WFA',
        ]);

        // Validate office_id for WFO
        if ($request->default_work_type === 'WFO' && !$request->default_office_id) {
            return back()->withErrors(['default_office_id' => 'Kantor wajib diisi untuk tipe kerja WFO.']); // Office -> Kantor
        }

        Employee::create([
            'id_pengguna' => $request->user_id,         // user_id -> id_pengguna
            'id_departemen' => $request->department_id, // department_id -> id_departemen
            'id_jabatan' => $request->position_id,       // position_id -> id_jabatan
            'id_atasan' => $request->supervisor_id,     // supervisor_id -> id_atasan
            'tanggal_rekrut' => $request->hire_date,    // hire_date -> tanggal_rekrut
            'mulai_kontrak' => $request->contract_start, // contract_start -> mulai_kontrak
            'akhir_kontrak' => $request->contract_end,   // contract_end -> akhir_kontrak
            'jenis_kepegawaian' => $request->employment_type, // employment_type -> jenis_kepegawaian (value already translated from validation)
            'status_kepegawaian' => 'aktif',            // employment_status -> status_kepegawaian, active -> aktif
            'gaji_pokok' => $request->basic_salary,      // basic_salary -> gaji_pokok
            'nama_bank' => $request->bank_name,         // bank_name -> nama_bank
            'rekening_bank' => $request->bank_account,   // bank_account -> rekening_bank
            'nama_rekening_bank' => $request->bank_account_name, // bank_account_name -> nama_rekening_bank
            'id_shift_standar' => $request->default_shift_id, // default_shift_id -> id_shift_standar
            'id_kantor_standar' => $request->default_work_type === 'WFO' ? $request->default_office_id : null, // default_office_id -> id_kantor_standar
            'tipe_kerja_standar' => $request->default_work_type, // default_work_type -> tipe_kerja_standar
        ]);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil dibuat.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'supervisor']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        $positions = Position::where('aktif', true)->get();     // is_active -> aktif
        $supervisors = Employee::with(['user', 'position'])
                              ->where('id', '!=', $employee->id)
                              ->where('status_kepegawaian', 'aktif') // employment_status -> status_kepegawaian, active -> aktif
                              ->get();
        $shifts = \App\Models\Shift::active()->get(); // Assumes Shift model has scopeActive using 'aktif'
        $offices = \App\Models\Office::active()->get(); // Assumes Office model has scopeActive using 'aktif'

        return view('employees.edit', compact('employee', 'departments', 'positions', 'supervisors', 'shifts', 'offices'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Assuming request field names are still in English
        $request->validate([
            'department_id' => 'required|exists:departemen,id', // departments -> departemen
            'position_id' => 'required|exists:jabatan,id',     // positions -> jabatan
            'supervisor_id' => 'nullable|exists:pengguna,id',   // users -> pengguna
            'hire_date' => 'required|date',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after:contract_start',
            'employment_type' => 'required|in:tetap,kontrak,magang,paruh_waktu', // permanent,contract,internship,freelance -> tetap,kontrak,magang,paruh_waktu
            'employment_status' => 'required|in:aktif,mengundurkan_diri,diberhentikan,pensiun', // active,resigned,terminated,retired -> aktif,mengundurkan_diri,diberhentikan,pensiun
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'bank_account_name' => 'nullable|string|max:100',
            'default_shift_id' => 'required|exists:shift,id', // shifts -> shift
            'default_office_id' => 'nullable|exists:kantor,id', // offices -> kantor
            'default_work_type' => 'required|in:WFO,WFA',
        ]);

        // Validate office_id for WFO
        if ($request->default_work_type === 'WFO' && !$request->default_office_id) {
            return back()->withErrors(['default_office_id' => 'Kantor wajib diisi untuk tipe kerja WFO.']); // Office -> Kantor
        }

        // Map English request keys to Indonesian model attributes for update
        $dataToUpdate = [
            'id_departemen' => $request->department_id,
            'id_jabatan' => $request->position_id,
            'id_atasan' => $request->supervisor_id,
            'tanggal_rekrut' => $request->hire_date,
            'mulai_kontrak' => $request->contract_start,
            'akhir_kontrak' => $request->contract_end,
            'jenis_kepegawaian' => $request->employment_type,
            'status_kepegawaian' => $request->employment_status,
            'gaji_pokok' => $request->basic_salary,
            'nama_bank' => $request->bank_name,
            'rekening_bank' => $request->bank_account,
            'nama_rekening_bank' => $request->bank_account_name,
            'id_shift_standar' => $request->default_shift_id,
            'id_kantor_standar' => $request->default_work_type === 'WFO' ? $request->default_office_id : null,
            'tipe_kerja_standar' => $request->default_work_type,
        ];
        $employee->update($dataToUpdate);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil dihapus.');
    }

    public function salary(Employee $employee)
    {
        $employee->load(['user.salaryComponents' => function($query) {
             // salary_components.is_active -> komponen_gaji.aktif
            $query->where('komponen_gaji.aktif', true);
        }]);

        $availableComponents = SalaryComponent::where('aktif', true) // is_active -> aktif
                                            ->whereNotIn('id', $employee->user->salaryComponents->pluck('id'))
                                            ->get();

        return view('employees.salary', compact('employee', 'availableComponents'));
    }

    public function updateSalary(Request $request, Employee $employee)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'components' => 'array',
            'components.*.component_id' => 'required|exists:komponen_gaji,id', // salary_components -> komponen_gaji
            'components.*.amount' => 'required|numeric|min:0',
            'components.*.effective_date' => 'required|date',
        ]);

        // Update basic salary
        $employee->update(['gaji_pokok' => $request->basic_salary]); // basic_salary -> gaji_pokok

        // Update salary components
        if ($request->has('components')) {
            foreach ($request->components as $component) {
                // User model's salaryComponents relation uses translated pivot keys
                $employee->user->salaryComponents()->syncWithoutDetaching([
                    $component['component_id'] => [
                        'jumlah' => $component['amount'],           // amount -> jumlah
                        'tanggal_efektif' => $component['effective_date'], // effective_date -> tanggal_efektif
                        'aktif' => true,                           // is_active -> aktif
                        'dibuat_pada' => now(),                    // created_at -> dibuat_pada
                        'diperbarui_pada' => now(),                // updated_at -> diperbarui_pada
                    ]
                ]);
            }
        }

        return back()->with('success', 'Komponen gaji berhasil diperbarui.');
    }
}

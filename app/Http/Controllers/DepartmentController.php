<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function __construct()
    {
        // Apply permission middleware to specific methods
        // TEMPORARILY COMMENTED OUT FOR DEBUGGING - UNCOMMENT AFTER FIXING PERMISSIONS
        // $this->middleware('permission:departments.view')->only(['index', 'show']);
        // $this->middleware('permission:departments.create')->only(['create', 'store']);
        // $this->middleware('permission:departments.edit')->only(['edit', 'update']);
        // $this->middleware('permission:departments.delete')->only(['destroy']);
    }
    public function index(Request $request)
    {
        $query = Department::with(['employees', 'positions']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $departments = $query->withCount(['employees', 'positions'])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        // Calculate statistics for the summary cards
        $total_employees = \App\Models\Employee::where('employment_status', 'active')->count();
        $total_positions = \App\Models\Position::where('is_active', true)->count();
        $active_departments = Department::where('is_active', true)->count();

        return view('departments.index', compact('departments', 'total_employees', 'total_positions', 'active_departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10|unique:departments,code',
            'name' => 'required|max:100',
            'description' => 'nullable',
            // 'is_active' will be handled below as it's a checkbox
        ]);

        try {
            $department = new Department();
            $department->code = $request->code;
            $department->name = $request->name;
            $department->description = $request->description;
            $department->is_active = $request->has('is_active'); // Correctly handle boolean for checkbox
            $department->save();

            return redirect()->route('departments.index')
                            ->with('success', 'Departemen berhasil dibuat.');

        } catch (\Exception $e) {
            // Log the error or handle it more gracefully
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal membuat departemen: ' . $e->getMessage());
        }
    }

    public function show(Department $department)
    {
        $department->load(['positions', 'employees.user']);
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'code' => 'required|max:10|unique:departments,code,' . $department->id,
            'name' => 'required|max:100',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['code', 'name', 'description']);
        $data['is_active'] = $request->has('is_active');

        $department->update($data);

        return redirect()->route('departments.index')
                        ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')
                           ->with('error', 'Tidak dapat menghapus departemen yang masih memiliki karyawan.');
        }

        // Check if department has positions
        if ($department->positions()->count() > 0) {
            return redirect()->route('departments.index')
                           ->with('error', 'Tidak dapat menghapus departemen yang masih memiliki posisi.');
        }

        $department->delete();

        return redirect()->route('departments.index')
                        ->with('success', 'Departemen berhasil dihapus.');
    }
}

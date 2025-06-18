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
                $q->where('nama', 'like', "%{$search}%") // name -> nama
                  ->orWhere('kode', 'like', "%{$search}%") // code -> kode
                  ->orWhere('deskripsi', 'like', "%{$search}%"); // description -> deskripsi
            });
        }

        // Status filter
        if ($request->filled('status')) {
            // Assuming $request->status provides 'true' or 'false' as string, or 1/0
            $isActive = filter_var($request->status, FILTER_VALIDATE_BOOLEAN);
            $query->where('aktif', $isActive); // is_active -> aktif
        }

        $departments = $query->withCount(['employees', 'positions']) // Assuming relations are correctly set up in Department model
                           ->orderBy('dibuat_pada', 'desc') // created_at -> dibuat_pada
                           ->paginate(10);

        // Calculate statistics for the summary cards
        // Assuming Employee, Position, Department models use translated 'status_kepegawaian', 'aktif' fields
        $total_employees = \App\Models\Employee::where('status_kepegawaian', 'aktif')->count();
        $total_positions = \App\Models\Position::where('aktif', true)->count();
        $active_departments = Department::where('aktif', true)->count();

        return view('departments.index', compact('departments', 'total_employees', 'total_positions', 'active_departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        // SIMPLE VERSION FOR DEBUGGING - REMOVE AFTER FIXING
        try {
            // Basic validation only
            if (empty($request->code) || empty($request->name)) {
                return redirect()->back()
                                ->withInput()
                                ->with('error', 'Kode dan Nama departemen harus diisi.');
            }

            // Create department with minimal data
            $department = new Department();
            $department->kode = $request->code; // code -> kode
            $department->nama = $request->name; // name -> nama
            $department->deskripsi = $request->description; // description -> deskripsi
            $department->aktif = $request->has('is_active') ? 1 : 0; // is_active -> aktif
            $department->save();

            // Force redirect with success message
            session()->flash('success', 'Departemen berhasil dibuat!');
            return redirect('/departments'); // Assuming route name is still /departments

        } catch (\Exception $e) {
            // Show detailed error
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(Department $department)
    {
        // Assuming relations 'positions', 'employees.user' are correctly updated in their respective models
        $department->load(['positions', 'employees.user']);
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        // Assuming request field names are still in English for validation keys
        $request->validate([
            'code' => 'required|max:10|unique:departemen,kode,' . $department->id, // departments -> departemen, code -> kode
            'name' => 'required|max:100',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        // Map English request keys to Indonesian model attributes
        $data = [
            'kode' => $request->code,
            'nama' => $request->name,
            'deskripsi' => $request->description,
            'aktif' => $request->has('is_active'), // is_active -> aktif
        ];

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

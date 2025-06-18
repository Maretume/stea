<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::with(['department', 'employees']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%") // name -> nama
                  ->orWhere('kode', 'like', "%{$search}%") // code -> kode
                  ->orWhereHas('department', function($dq) use ($search) { // Assuming department relation is correctly set up
                      $dq->where('nama', 'like', "%{$search}%"); // department.name -> department.nama
                  });
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('id_departemen', $request->department_id); // department_id -> id_departemen
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('tingkat', $request->level); // level -> tingkat
        }

        // Status filter
        if ($request->filled('status')) {
            // Assuming $request->status is boolean or 'true'/'false' string
            $isActive = filter_var($request->status, FILTER_VALIDATE_BOOLEAN);
            $query->where('aktif', $isActive); // is_active -> aktif
        }

        $positions = $query->withCount('employees') // Assuming employees relation is correct
                          ->orderBy('dibuat_pada', 'desc') // created_at -> dibuat_pada
                          ->paginate(10);

        // Additional data for the view
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        $total_employees = $positions->sum('employees_count');
        $average_salary = $positions->avg('gaji_pokok'); // base_salary -> gaji_pokok

        return view('positions.index', compact('positions', 'departments', 'total_employees', 'average_salary'));
    }

    public function create()
    {
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        return view('positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English
        $request->validate([
            'code' => 'required|unique:jabatan,kode|max:10', // positions -> jabatan, code -> kode
            'name' => 'required|max:100',
            'department_id' => 'required|exists:departemen,id', // departments -> departemen
            'base_salary' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable',
            'is_active' => 'boolean', // Added for consistency, assuming it comes from form
        ]);

        Position::create([
            'kode' => $request->code,
            'nama' => $request->name,
            'id_departemen' => $request->department_id,
            'gaji_pokok' => $request->base_salary,
            'tingkat' => $request->level,
            'deskripsi' => $request->description,
            'aktif' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('positions.index')
                        ->with('success', 'Jabatan berhasil dibuat.'); // Position -> Jabatan
    }

    public function show(Position $position)
    {
        $position->load('department', 'employees'); // Assuming these relations are correctly defined in Position model
        return view('positions.show', compact('position'));
    }

    public function edit(Position $position)
    {
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        return view('positions.edit', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        // Assuming request field names are still in English
        $request->validate([
            'code' => 'required|max:10|unique:jabatan,kode,' . $position->id, // positions -> jabatan, code -> kode
            'name' => 'required|max:100',
            'department_id' => 'required|exists:departemen,id', // departments -> departemen
            'base_salary' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable',
            'is_active' => 'boolean', // Added for consistency
        ]);

        $dataToUpdate = [
            'kode' => $request->code,
            'nama' => $request->name,
            'id_departemen' => $request->department_id,
            'gaji_pokok' => $request->base_salary,
            'tingkat' => $request->level,
            'deskripsi' => $request->description,
            'aktif' => $request->has('is_active') ? $request->is_active : $position->aktif, // Retain old value if not provided
        ];
        $position->update($dataToUpdate);

        return redirect()->route('positions.index')
                        ->with('success', 'Jabatan berhasil diperbarui.'); // Position -> Jabatan
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions.index')
                        ->with('success', 'Jabatan berhasil dihapus.'); // Position -> Jabatan
    }
}

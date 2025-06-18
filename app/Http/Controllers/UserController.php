<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'employee.department', 'employee.position']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_depan', 'like', "%{$search}%")    // first_name -> nama_depan
                  ->orWhere('nama_belakang', 'like', "%{$search}%") // last_name -> nama_belakang
                  ->orWhere('surel', 'like', "%{$search}%")       // email -> surel
                  ->orWhere('id_karyawan', 'like', "%{$search}%"); // employee_id -> id_karyawan
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('nama_kunci', $request->role); // name -> nama_kunci
            });
        }

        if ($request->filled('status')) {
            // Assuming $request->status provides translated values like 'aktif', 'tidak_aktif'
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('dibuat_pada', 'desc')->paginate(20); // created_at -> dibuat_pada
        $roles = Role::where('aktif', true)->get(); // is_active -> aktif

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::where('aktif', true)->get(); // is_active -> aktif
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        
        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English for validation keys
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:pengguna,id_karyawan', // users -> pengguna, employee_id -> id_karyawan
            'username' => 'required|string|max:50|unique:pengguna,nama_pengguna', // users -> pengguna, username -> nama_pengguna
            'email' => 'required|email|max:100|unique:pengguna,surel',          // users -> pengguna, email -> surel
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:pria,wanita', // male,female -> pria,wanita
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'role_id' => 'required|exists:peran,id', // roles -> peran
        ]);

        $user = User::create([
            'id_karyawan' => $request->employee_id,     // employee_id -> id_karyawan
            'nama_pengguna' => $request->username,    // username -> nama_pengguna
            'surel' => $request->email,               // email -> surel
            'kata_sandi' => Hash::make($request->password), // password -> kata_sandi
            'nama_depan' => $request->first_name,       // first_name -> nama_depan
            'nama_belakang' => $request->last_name,      // last_name -> nama_belakang
            'telepon' => $request->phone,             // phone -> telepon
            'jenis_kelamin' => $request->gender,          // gender -> jenis_kelamin (values already pria/wanita from validation)
            'tanggal_lahir' => $request->birth_date,    // birth_date -> tanggal_lahir
            'alamat' => $request->address,            // address -> alamat
            'status' => 'aktif',                      // active -> aktif
        ]);

        // Assign role
        $user->roles()->attach($request->role_id, [
            'ditetapkan_pada' => now(), // assigned_at -> ditetapkan_pada
            'aktif' => true,           // is_active -> aktif
        ]);

        return redirect()->route('users.index')
                        ->with('success', 'Pengguna berhasil dibuat.'); // User -> Pengguna
    }

    public function show(User $user)
    {
        $user->load(['roles', 'employee.department', 'employee.position']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('aktif', true)->get(); // is_active -> aktif
        $departments = Department::where('aktif', true)->get(); // is_active -> aktif
        $user->load('roles');
        
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        // Assuming request field names are still in English for validation keys
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:pengguna,id_karyawan,' . $user->id, // users -> pengguna, employee_id -> id_karyawan
            'username' => 'required|string|max:50|unique:pengguna,nama_pengguna,' . $user->id, // users -> pengguna, username -> nama_pengguna
            'email' => 'required|email|max:100|unique:pengguna,surel,' . $user->id,          // users -> pengguna, email -> surel
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:pria,wanita', // male,female -> pria,wanita
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'status' => 'required|in:aktif,tidak_aktif,ditangguhkan', // active,inactive,suspended -> aktif,tidak_aktif,ditangguhkan
        ]);

        // Map English request keys to Indonesian model attributes
        $data = [
            'id_karyawan' => $request->employee_id,
            'nama_pengguna' => $request->username,
            'surel' => $request->email,
            'nama_depan' => $request->first_name,
            'nama_belakang' => $request->last_name,
            'telepon' => $request->phone,
            'jenis_kelamin' => $request->gender,
            'tanggal_lahir' => $request->birth_date,
            'alamat' => $request->address,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $data['kata_sandi'] = Hash::make($request->password); // password -> kata_sandi
        }

        $user->update($data);

        return redirect()->route('users.index')
                        ->with('success', 'Pengguna berhasil diperbarui.'); // User -> Pengguna
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'Pengguna berhasil dihapus.'); // User -> Pengguna
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:peran,id', // roles -> peran
        ]);

        $role = Role::findOrFail($request->role_id);

        // Assuming roles() relation on User correctly uses 'id_peran' for relatedPivotKey
        if ($user->roles()->where('id_peran', $role->id)->exists()) {
            return back()->with('error', 'Pengguna sudah memiliki peran ini.'); // User -> Pengguna, role -> peran
        }

        $user->roles()->attach($role->id, [
            'ditetapkan_pada' => now(), // assigned_at -> ditetapkan_pada
            'aktif' => true,           // is_active -> aktif
        ]);

        return back()->with('success', "Peran {$role->nama_tampilan} berhasil ditambahkan."); // Role -> Peran, display_name -> nama_tampilan
    }

    public function removeRole(User $user, Role $role)
    {
        if ($user->roles()->count() <= 1) {
            return back()->with('error', 'Pengguna harus memiliki minimal satu peran.'); // User -> Pengguna, role -> peran
        }

        $user->roles()->detach($role->id);

        return back()->with('success', "Peran {$role->nama_tampilan} berhasil dihapus."); // Role -> Peran, display_name -> nama_tampilan
    }
}

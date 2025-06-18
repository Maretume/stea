<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Update last login info
            $user->update([
                'login_terakhir_pada' => now(), // last_login_at -> login_terakhir_pada
                'ip_login_terakhir' => $request->ip(), // last_login_ip -> ip_login_terakhir
            ]);

            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'Nama pengguna atau kata sandi salah.', // username -> nama_pengguna (in message)
        ])->onlyInput('username'); // Assuming request input name is still 'username'
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectBasedOnRole($user)
    {
        $role = $user->roles->first();
        
        if (!$role) {
            return redirect()->route('dashboard');
        }

        switch ($role->nama_kunci) { // name -> nama_kunci
            case 'ceo':
                return redirect()->route('dashboard.ceo');
            case 'cfo':
                return redirect()->route('dashboard.cfo');
            case 'hrd':
                return redirect()->route('dashboard.hrd');
            case 'personalia':
                return redirect()->route('dashboard.personalia');
            case 'karyawan':
                return redirect()->route('dashboard.karyawan');
            default:
                return redirect()->route('dashboard');
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Assuming request field name is still 'profile_photo'
        ]);

        // Assuming request field names match these English keys
        $data = $request->only(['first_name', 'last_name', 'phone', 'address']);

        // Translate keys for User model update
        $translatedData = [
            'nama_depan' => $data['first_name'],
            'nama_belakang' => $data['last_name'],
            'telepon' => $data['phone'] ?? null,
            'alamat' => $data['address'] ?? null,
        ];

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profiles'), $filename);
            $translatedData['foto_profil'] = 'uploads/profiles/' . $filename; // profile_photo -> foto_profil
        }

        $user->update($translatedData);

        return back()->with('success', 'Profil berhasil diperbarui.'); // Profile -> Profil
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required', // Assuming request field name
            'password' => 'required|string|min:8|confirmed', // Assuming request field name
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->kata_sandi)) { // password -> kata_sandi
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']); // Password -> Kata sandi
        }

        $user->update([
            'kata_sandi' => Hash::make($request->password), // password -> kata_sandi
            'paksa_ganti_kata_sandi' => false, // force_password_change -> paksa_ganti_kata_sandi
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.'); // Password -> Kata sandi
    }
}

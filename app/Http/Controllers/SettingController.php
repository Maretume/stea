<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRule;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => config('app.name'),
            'company_address' => 'Jl. Contoh No. 123, Jakarta',
            'company_phone' => '021-12345678',
            'company_email' => 'info@company.com',
            'working_hours_start' => '08:00',
            'working_hours_end' => '17:00',
            'late_tolerance_minutes' => 15,
            'overtime_rate' => 1.5,
        ];
        
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'working_hours_start' => 'required|date_format:H:i',
            'working_hours_end' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0|max:60',
            'overtime_rate' => 'required|numeric|min:1|max:5',
        ]);

        // Update default attendance rule
        // Assuming AttendanceRule model uses 'standar' for is_default
        $defaultRule = AttendanceRule::where('standar', true)->first();
        if ($defaultRule) {
            // Assuming request field names like 'working_hours_start' are still English
            $defaultRule->update([
                'jam_mulai_kerja' => $request->working_hours_start,     // work_start_time -> jam_mulai_kerja
                'jam_selesai_kerja' => $request->working_hours_end,   // work_end_time -> jam_selesai_kerja
                'toleransi_keterlambatan_menit' => $request->late_tolerance_minutes, // late_tolerance_minutes -> toleransi_keterlambatan_menit
                'pengali_lembur' => $request->overtime_rate,         // overtime_multiplier -> pengali_lembur
            ]);
        }

        // Cache settings for quick access
        Cache::put('company_settings', $request->only([
            'company_name', 'company_address', 'company_phone', 'company_email'
        ]), now()->addDays(30)); // These are app-level settings, not directly from a translated DB table here.

        return redirect()->route('settings.index')
                        ->with('success', 'Pengaturan berhasil diperbarui.'); // Settings updated successfully.
    }

    public function attendanceRules()
    {
        $rules = AttendanceRule::paginate(10);
        return view('settings.attendance-rules', compact('rules'));
    }

    public function leaveTypes()
    {
        $leaveTypes = LeaveType::paginate(10);
        return view('settings.leave-types', compact('leaveTypes'));
    }
}

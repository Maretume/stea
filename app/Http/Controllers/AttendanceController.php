<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Office;
use App\Models\Shift;
use App\Services\GeofencingService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $geofencingService;

    public function __construct(GeofencingService $geofencingService)
    {
        $this->geofencingService = $geofencingService;
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with('user'); // User relation uses id_pengguna

        // Filter based on user role
        if ($user->hasRole('karyawan')) { // hasRole uses nama_kunci
            $query->where('id_pengguna', $user->id); // user_id -> id_pengguna
        }

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('tanggal', '>=', $request->date_from); // date -> tanggal
        }

        if ($request->filled('date_to')) {
            $query->where('tanggal', '<=', $request->date_to); // date -> tanggal
        }

        if ($request->filled('user_id') && !$user->hasRole('karyawan')) {
            $query->where('id_pengguna', $request->user_id); // user_id -> id_pengguna
        }

        if ($request->filled('status')) {
            // Assuming $request->status provides translated ENUM value
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('tanggal', 'desc') // date -> tanggal
                           ->orderBy('jam_masuk', 'desc') // clock_in -> jam_masuk
                           ->paginate(20);

        $users = $user->hasRole('karyawan') ? collect() : User::whereHas('employee')->get(); // Assuming employee relation is correct

        return view('attendance.index', compact('attendances', 'users'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = today();

        // Check if already clocked in today
        $attendance = Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                                ->where('tanggal', $today)        // date -> tanggal
                                ->first();

        if ($attendance && $attendance->jam_masuk) { // clock_in -> jam_masuk
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock in hari ini.'
            ]);
        }

        // Get or create schedule for today
        $todaySchedule = $user->getTodaySchedule(); // Uses translated attributes internally
        if (!$todaySchedule) {
            // Auto-create schedule using employee's default settings
            $todaySchedule = $user->getOrCreateTodaySchedule(); // Uses translated attributes internally
            if (!$todaySchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membuat jadwal kerja. Pastikan Anda memiliki shift dan kantor default. Silakan hubungi HR.' // office -> kantor
                ]);
            }
        }

        // Enhanced location validation with geofencing
        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi GPS diperlukan untuk absensi.'
            ]);
        }

        $locationValidation = $this->geofencingService->validateScheduleLocation(
            $request->latitude,
            $request->longitude,
            $user->id,
            today()
        );

        if (!$locationValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $locationValidation['message'],
                'location_data' => [
                    'distance' => $locationValidation['distance'] ?? null,
                    'required_radius' => $locationValidation['required_radius'] ?? null,
                    'office' => $locationValidation['office'] ?? null
                ]
            ]);
        }

        // Track location for audit
        $this->geofencingService->trackLocationHistory(
            $user->id,
            $request->latitude,
            $request->longitude,
            'clock_in' // This is an action type, likely should remain English or be mapped if stored
        );

        $clockInTime = now();

        // Use shift-specific timing to determine status
        $shift = $todaySchedule->shift; // Assumes shift relation on Schedule is correctly translated
        $attendanceStatus = $shift->calculateAttendanceStatus($clockInTime); // Uses translated attributes internally

        // Calculate minutes based on status
        $lateMinutes = 0;
        $earlyMinutes = 0;
        // ENUM values: ['hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur']
        $status = 'hadir'; // Default status: present -> hadir

        switch ($attendanceStatus) {
            case 'early': // This is a return from calculateAttendanceStatus, not a DB ENUM
                $earlyMinutes = $shift->calculateEarlyMinutes($clockInTime); // Uses translated attributes internally
                $status = 'hadir'; // Still 'hadir', early is a flag via menit_masuk_awal
                break;
            case 'on_time':
                $status = 'hadir';
                break;
            case 'late':
                $lateMinutes = $shift->calculateLateMinutes($clockInTime); // Uses translated attributes internally
                $status = 'terlambat'; // late -> terlambat
                break;
        }

        $attendanceData = [
            'id_pengguna' => $user->id,          // user_id -> id_pengguna
            'tanggal' => $today,                // date -> tanggal
            'jam_masuk' => $clockInTime,        // clock_in -> jam_masuk
            'menit_terlambat' => $lateMinutes,    // late_minutes -> menit_terlambat
            'menit_masuk_awal' => $earlyMinutes, // early_minutes -> menit_masuk_awal
            'status' => $status,
            'ip_jam_masuk' => $request->ip(),   // clock_in_ip -> ip_jam_masuk
            'id_kantor' => $todaySchedule->id_kantor, // office_id -> id_kantor
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $attendanceData['lat_jam_masuk'] = $request->latitude; // clock_in_lat -> lat_jam_masuk
            $attendanceData['lng_jam_masuk'] = $request->longitude; // clock_in_lng -> lng_jam_masuk
        }

        if ($attendance) {
            $attendance->update($attendanceData);
        } else {
            $attendance = Attendance::create($attendanceData);
        }

        // Generate status message based on attendance status
        $statusMessage = '';
        // Using the calculated status for message, not the internal 'early'/'late' from shift calculation directly
        if ($status === 'terlambat') {
            $statusMessage = "Clock in berhasil - Terlambat {$lateMinutes} menit";
        } elseif ($earlyMinutes > 0 && $status === 'hadir') { // If early and status is 'hadir'
             $statusMessage = "Clock in berhasil - Masuk awal {$earlyMinutes} menit";
        } else { // present
            $statusMessage = "Clock in berhasil - Tepat waktu";
        }


        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'data' => [
                'attendance' => $attendance,
                'schedule' => $todaySchedule,
                'work_type' => $todaySchedule->tipe_kerja, // work_type -> tipe_kerja
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'early_minutes' => $earlyMinutes
            ]
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = today();

        $attendance = Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                                ->where('tanggal', $today)        // date -> tanggal
                                ->first();

        if (!$attendance || !$attendance->jam_masuk) { // clock_in -> jam_masuk
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock in hari ini.'
            ]);
        }

        if ($attendance->jam_keluar) { // clock_out -> jam_keluar
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock out hari ini.'
            ]);
        }

        // Get or create today's schedule to use shift timing
        $todaySchedule = $user->getTodaySchedule(); // Uses translated attributes
        if (!$todaySchedule) {
            // Auto-create schedule using employee's default settings
            $todaySchedule = $user->getOrCreateTodaySchedule(); // Uses translated attributes
            if (!$todaySchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membuat jadwal kerja. Pastikan Anda memiliki shift dan kantor default. Silakan hubungi HR.'
                ]);
            }
        }

        // Validate location for WFO clock out
        if ($todaySchedule->isWFO()) { // isWFO uses tipe_kerja
            if ($request->filled('latitude') && $request->filled('longitude')) {
                if (!$todaySchedule->canClockInAtLocation($request->latitude, $request->longitude)) { // uses translated office relation
                    $distance = $todaySchedule->getDistanceFromOffice($request->latitude, $request->longitude); // uses translated office relation
                    return response()->json([
                        'success' => false,
                        'message' => "Anda berada di luar radius kantor untuk clock out. Jarak Anda: " . round($distance) . " meter dari kantor " . $todaySchedule->office->nama . "." // office->name -> office->nama
                    ]);
                }
            }
        }

        $clockOutTime = now();
        $shift = $todaySchedule->shift; // Assumes shift relation is correct

        $earlyLeaveMinutes = $shift->calculateEarlyLeaveMinutes($clockOutTime); // Uses translated attributes
        $overtimeMinutes = $shift->calculateOvertimeMinutes($clockOutTime);   // Uses translated attributes

        // Calculate total work minutes
        $clockIn = Carbon::parse($attendance->jam_masuk); // clock_in -> jam_masuk
        $clockOut = Carbon::parse($clockOutTime);
        $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

        // Determine final status based on early leave and existing status
        $finalStatus = $attendance->status; // Original status (e.g., 'hadir', 'terlambat')
        if ($earlyLeaveMinutes > 0) {
            $finalStatus = 'pulang_awal'; // early_leave -> pulang_awal
        }
        // If already 'terlambat' and also 'pulang_awal', 'pulang_awal' might take precedence or combine.
        // For simplicity, if pulang_awal, status becomes pulang_awal.
        // If they were 'terlambat' and not 'pulang_awal', status remains 'terlambat'.
        // If they were 'hadir' and not 'pulang_awal', status remains 'hadir'.

        $updateData = [
            'jam_keluar' => $clockOutTime,               // clock_out -> jam_keluar
            'menit_pulang_awal' => $earlyLeaveMinutes,  // early_leave_minutes -> menit_pulang_awal
            'menit_lembur' => $overtimeMinutes,         // overtime_minutes -> menit_lembur
            'total_menit_kerja' => $totalWorkMinutes,   // total_work_minutes -> total_menit_kerja
            'status' => $finalStatus,
            'ip_jam_keluar' => $request->ip(),          // clock_out_ip -> ip_jam_keluar
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $updateData['lat_jam_keluar'] = $request->latitude;   // clock_out_lat -> lat_jam_keluar
            $updateData['lng_jam_keluar'] = $request->longitude; // clock_out_lng -> lng_jam_keluar
        }

        $attendance->update($updateData);

        // Generate status message
        $statusMessage = 'Clock out berhasil';
        if ($overtimeMinutes > 0) {
            $overtimeHours = floor($overtimeMinutes / 60);
            $overtimeRemainingMinutes = $overtimeMinutes % 60;
            if ($overtimeHours > 0) {
                $statusMessage .= " - Lembur {$overtimeHours} jam";
                if ($overtimeRemainingMinutes > 0) {
                    $statusMessage .= " {$overtimeRemainingMinutes} menit";
                }
            } else {
                $statusMessage .= " - Lembur {$overtimeRemainingMinutes} menit";
            }
        } elseif ($earlyLeaveMinutes > 0) {
            $statusMessage .= " - Pulang awal {$earlyLeaveMinutes} menit";
        } else {
            $statusMessage .= " - Tepat waktu";
        }

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'data' => [
                'attendance' => $attendance->fresh(),
                'schedule' => $todaySchedule,
                'work_type' => $todaySchedule->tipe_kerja, // work_type -> tipe_kerja
                'status' => $finalStatus,
                'overtime_minutes' => $overtimeMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'total_work_minutes' => $totalWorkMinutes
            ]
        ]);
    }



    public function getTodayAttendance()
    {
        try {
            $user = Auth::user();
            $attendance = Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                                    ->where('tanggal', today())     // date -> tanggal
                                    ->first();

            $todaySchedule = $user->getTodaySchedule(); // Uses translated attributes

            // Debug logging
            \Log::info('Today schedule data:', [
                'user_id' => $user->id,
                'schedule' => $todaySchedule ? $todaySchedule->toArray() : null,
                'shift' => $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->toArray() : null
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendance,
                    'schedule' => $todaySchedule,
                    'can_clock_in' => $user->canClockInToday(), // Uses translated attributes
                    'work_type' => $user->getTodayWorkType(), // Uses translated attributes
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading today attendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data absensi. Silakan coba lagi.',
                'data' => [
                    'attendance' => null,
                    'schedule' => null,
                    'can_clock_in' => false,
                    'work_type' => null,
                ]
            ], 500);
        }
    }

    public function getRecentAttendance()
    {
        try {
            $user = Auth::user();
            $recentAttendances = Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                                          ->with(['user']) // User relation uses id_pengguna
                                          ->orderBy('tanggal', 'desc') // date -> tanggal
                                          ->limit(7)
                                          ->get();

            return response()->json([
                'success' => true,
                'data' => $recentAttendances
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading recent attendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat absensi.',
                'data' => []
            ], 500);
        }
    }

    public function attempt(Request $request)
    {
        $user = Auth::user();
        $today = today();

        // Check if already has attendance today
        $attendance = Attendance::where('id_pengguna', $user->id) // user_id -> id_pengguna
                                ->where('tanggal', $today)        // date -> tanggal
                                ->first();

        if (!$attendance || !$attendance->jam_masuk) { // clock_in -> jam_masuk
            // Perform clock in
            return $this->clockIn($request);
        } elseif (!$attendance->jam_keluar) { // clock_out -> jam_keluar
            // Perform clock out
            return $this->clockOut($request);
        } else {
            // Already completed
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini sudah lengkap.'
            ]);
        }
    }

    /**
     * Validate current location for attendance
     */
    public function validateCurrentLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $todaySchedule = $user->getTodaySchedule(); // Uses translated attributes

        if (!$todaySchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal kerja untuk hari ini',
                'data' => [
                    'valid' => false,
                    'work_type' => null,
                    'office' => null,
                    'distance' => null,
                    'required_radius' => null
                ]
            ]);
        }

        // For WFA, location is always valid
        if ($todaySchedule->tipe_kerja === 'WFA') { // work_type -> tipe_kerja
            return response()->json([
                'success' => true,
                'message' => 'Kerja Dari Mana Saja (WFA) - lokasi valid', // Work From Anywhere -> Kerja Dari Mana Saja
                'data' => [
                    'valid' => true,
                    'work_type' => 'WFA',
                    'office' => null,
                    'distance' => null,
                    'required_radius' => null
                ]
            ]);
        }

        // For WFO, validate against office location
        $locationValidation = $this->geofencingService->validateScheduleLocation(
            $request->latitude,
            $request->longitude,
            $user->id,
            today()
        );

        return response()->json([
            'success' => true,
            'message' => $locationValidation['message'],
            'data' => [
                'valid' => $locationValidation['valid'],
                'work_type' => $locationValidation['work_type'] ?? 'WFO',
                'office' => $locationValidation['office'] ?? null,
                'distance' => $locationValidation['distance'] ?? null,
                'required_radius' => $locationValidation['required_radius'] ?? null
            ]
        ]);
    }

    public function edit($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if (!$user->hasPermission('attendance.edit')) { // hasPermission uses nama_kunci
            abort(403);
        }

        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        // Assuming request field names are still in English
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'late_minutes' => 'nullable|integer|min:0',
            'early_minutes' => 'nullable|integer|min:0', // This is 'menit_masuk_awal'
            'early_leave_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            // ENUM values: ['hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur']
            'status' => 'required|in:hadir,absen,terlambat,pulang_awal,setengah_hari,sakit,cuti,libur',
        ]);

        // Map English request keys to Indonesian model attributes
        $data = [
            'jam_masuk' => $request->clock_in,
            'jam_keluar' => $request->clock_out,
            'menit_terlambat' => $request->late_minutes,
            'menit_masuk_awal' => $request->early_minutes, // early_minutes (request) -> menit_masuk_awal (db)
            'menit_pulang_awal' => $request->early_leave_minutes,
            'menit_lembur' => $request->overtime_minutes,
            'status' => $request->status, // Value already translated from validation
        ];

        // Recalculate work minutes if times are provided
        if ($data['jam_masuk'] && $data['jam_keluar']) {
            $clockIn = Carbon::parse($data['jam_masuk']);
            $clockOut = Carbon::parse($data['jam_keluar']);
            $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

            $data['total_menit_kerja'] = $totalWorkMinutes; // total_work_minutes -> total_menit_kerja

            // Recalculate late and overtime
            // Assuming AttendanceRule model uses 'standar'
            $rule = AttendanceRule::where('standar', true)->first();
            if ($rule) {
                // These methods in AttendanceRule should use translated attribute names
                $data['menit_terlambat'] = $rule->calculateLateMinutes($data['jam_masuk']);
                $data['menit_lembur'] = $rule->calculateOvertimeMinutes($data['jam_keluar']);
            }
        }

        $attendance->update($data);

        return redirect()->route('attendance.index')
                        ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = Attendance::with(['user.employee.department', 'user.employee.position'])
                          ->whereBetween('tanggal', [$startDate, $endDate]); // date -> tanggal

        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('id_departemen', $departmentId); // department_id -> id_departemen
            });
        }

        $attendances = $query->orderBy('tanggal', 'desc')->get(); // date -> tanggal

        // Using translated ENUM values for summary
        $summary = [
            'total_present' => $attendances->where('status', 'hadir')->count(),
            'total_late' => $attendances->where('status', 'terlambat')->count(),
            'total_absent' => $attendances->where('status', 'absen')->count(),
            'total_sick' => $attendances->where('status', 'sakit')->count(),
            'total_leave' => $attendances->where('status', 'cuti')->count(),
        ];

        return view('attendance.report', compact('attendances', 'summary', 'startDate', 'endDate'));
    }
}

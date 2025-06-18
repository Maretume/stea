<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('waktu_mulai')->paginate(20); // start_time -> waktu_mulai
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English
        $request->validate([
            'name' => 'required|string|max:50|unique:shift,nama', // shifts -> shift, name -> nama
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        Shift::create([
            'nama' => $request->name,                 // name -> nama
            'waktu_mulai' => $request->start_time,   // start_time -> waktu_mulai
            'waktu_selesai' => $request->end_time,   // end_time -> waktu_selesai
            'aktif' => $request->has('is_active') ? $request->is_active : true, // is_active -> aktif
        ]);

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift berhasil dibuat.');
    }

    public function show($id)
    {
        $shift = Shift::with(['schedules.user'])->findOrFail($id);
        return view('shifts.show', compact('shift'));
    }

    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        // Assuming request field names are still in English
        $request->validate([
            'name' => 'required|string|max:50|unique:shift,nama,' . $id, // shifts -> shift, name -> nama
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        // Check if deactivating shift with active schedules
        if ($request->has('is_active') && !$request->is_active) {
            $activeSchedulesCount = $shift->schedules() // Assuming schedules relation is correct
                                         ->where('status', 'disetujui') // approved -> disetujui
                                         ->where('tanggal_jadwal', '>=', today()) // schedule_date -> tanggal_jadwal
                                         ->count();

            if ($activeSchedulesCount > 0) {
                return back()->withErrors([
                    'is_active' => "Tidak dapat menonaktifkan shift yang memiliki {$activeSchedulesCount} jadwal aktif."
                ]);
            }
        }

        $shift->update([
            'nama' => $request->name,               // name -> nama
            'waktu_mulai' => $request->start_time, // start_time -> waktu_mulai
            'waktu_selesai' => $request->end_time, // end_time -> waktu_selesai
            'aktif' => $request->has('is_active') ? $request->is_active : $shift->aktif, // is_active -> aktif
        ]);

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        
        // Check if shift has active schedules
        // Assuming schedules relation is correct
        if ($shift->schedules()->where('status', 'disetujui')->exists()) { // approved -> disetujui
            return redirect()->back()
                            ->with('error', 'Tidak dapat menghapus shift dengan jadwal aktif.'); // Cannot delete shift with active schedules.
        }

        $shift->delete();

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift berhasil dihapus.'); // Shift deleted successfully.
    }
}

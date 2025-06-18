<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::orderBy('nama')->paginate(20); // name -> nama
        return view('offices.index', compact('offices'));
    }

    public function create()
    {
        return view('offices.create');
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English
        $request->validate([
            'name' => 'required|string|max:100|unique:kantor,nama', // offices -> kantor, name -> nama
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        Office::create([
            'nama' => $request->name,         // name -> nama
            'lintang' => $request->latitude,  // latitude -> lintang
            'bujur' => $request->longitude, // longitude -> bujur
            'radius' => $request->radius,
            'aktif' => $request->has('is_active') ? $request->is_active : true, // is_active -> aktif
        ]);

        return redirect()->route('offices.index')
                        ->with('success', 'Kantor berhasil dibuat.');
    }

    public function show($id)
    {
        $office = Office::with(['schedules.user', 'attendances.user'])->findOrFail($id);
        return view('offices.show', compact('office'));
    }

    public function edit($id)
    {
        $office = Office::findOrFail($id);
        return view('offices.edit', compact('office'));
    }

    public function update(Request $request, $id)
    {
        $office = Office::findOrFail($id);

        // Assuming request field names are still in English
        $request->validate([
            'name' => 'required|string|max:100|unique:kantor,nama,' . $id, // offices -> kantor, name -> nama
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $office->update([
            'nama' => $request->name,         // name -> nama
            'lintang' => $request->latitude,  // latitude -> lintang
            'bujur' => $request->longitude, // longitude -> bujur
            'radius' => $request->radius,
            'aktif' => $request->has('is_active') ? $request->is_active : $office->aktif, // is_active -> aktif
        ]);

        return redirect()->route('offices.index')
                        ->with('success', 'Kantor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $office = Office::findOrFail($id);
        
        // Check if office has active schedules
        // Assuming schedules relation is correct and Schedule model uses 'status' and 'disetujui'
        if ($office->schedules()->where('status', 'disetujui')->exists()) { // approved -> disetujui
            return redirect()->back()
                            ->with('error', 'Tidak dapat menghapus kantor dengan jadwal aktif.'); // Cannot delete office with active schedules.
        }

        $office->delete();

        return redirect()->route('offices.index')
                        ->with('success', 'Kantor berhasil dihapus.'); // Office deleted successfully. -> Kantor berhasil dihapus.
    }
}

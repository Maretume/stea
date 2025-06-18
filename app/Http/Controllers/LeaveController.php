<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with('user', 'leaveType', 'approvedBy') // Relations use translated foreign keys
                      ->when(!Auth::user()->hasPermission('leaves.view_all'), function($query) { // hasPermission uses nama_kunci
                          return $query->where('id_pengguna', Auth::id()); // user_id -> id_pengguna
                      })
                      ->latest('dibuat_pada') // Assuming created_at is translated to dibuat_pada
                      ->paginate(10);
        
        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('aktif', true)->get(); // is_active -> aktif
        return view('leaves.create', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English
        $request->validate([
            'leave_type_id' => 'required|exists:jenis_cuti,id', // leave_types -> jenis_cuti
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $totalDays = $startDate->diff($endDate)->days + 1;

        Leave::create([
            'id_pengguna' => Auth::id(),             // user_id -> id_pengguna
            'id_jenis_cuti' => $request->leave_type_id, // leave_type_id -> id_jenis_cuti
            'tanggal_mulai' => $request->start_date,  // start_date -> tanggal_mulai
            'tanggal_selesai' => $request->end_date,    // end_date -> tanggal_selesai
            'total_hari' => $totalDays,             // total_days -> total_hari
            'alasan' => $request->reason,              // reason -> alasan
            'status' => 'menunggu',                   // pending -> menunggu
        ]);

        return redirect()->route('leaves.index')
                        ->with('success', 'Permintaan cuti berhasil diajukan.'); // Leave request submitted successfully.
    }

    public function show(Leave $leave)
    {
        $leave->load('user', 'leaveType', 'approvedBy'); // Relations use translated foreign keys
        return view('leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        if ($leave->status !== 'menunggu') { // pending -> menunggu
            return redirect()->route('leaves.index')
                           ->with('error', 'Tidak dapat mengubah cuti yang sudah disetujui/ditoLak.'); // Cannot edit approved/rejected leave.
        }

        $leaveTypes = LeaveType::where('aktif', true)->get(); // is_active -> aktif
        return view('leaves.edit', compact('leave', 'leaveTypes'));
    }

    public function update(Request $request, Leave $leave)
    {
        if ($leave->status !== 'menunggu') { // pending -> menunggu
            return redirect()->route('leaves.index')
                           ->with('error', 'Tidak dapat memperbarui cuti yang sudah disetujui/ditoLak.'); // Cannot update approved/rejected leave.
        }

        // Assuming request field names are still in English
        $request->validate([
            'leave_type_id' => 'required|exists:jenis_cuti,id', // leave_types -> jenis_cuti
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $totalDays = $startDate->diff($endDate)->days + 1;

        $leave->update([
            'id_jenis_cuti' => $request->leave_type_id, // leave_type_id -> id_jenis_cuti
            'tanggal_mulai' => $request->start_date,  // start_date -> tanggal_mulai
            'tanggal_selesai' => $request->end_date,    // end_date -> tanggal_selesai
            'total_hari' => $totalDays,             // total_days -> total_hari
            'alasan' => $request->reason,              // reason -> alasan
        ]);

        return redirect()->route('leaves.index')
                        ->with('success', 'Permintaan cuti berhasil diperbarui.'); // Leave request updated successfully.
    }

    public function pending()
    {
        $leaves = Leave::with('user', 'leaveType') // Relations use translated foreign keys
                      ->where('status', 'menunggu') // pending -> menunggu
                      ->latest('dibuat_pada') // Assuming created_at is 'dibuat_pada'
                      ->paginate(10);
        
        return view('leaves.pending', compact('leaves'));
    }

    public function approve(Request $request, Leave $leave)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $leave->update([
            'status' => 'disetujui', // approved -> disetujui
            'disetujui_oleh' => Auth::id(), // approved_by -> disetujui_oleh
            'disetujui_pada' => now(),    // approved_at -> disetujui_pada
            'catatan_persetujuan' => $request->approval_notes, // approval_notes -> catatan_persetujuan
        ]);

        return redirect()->route('leaves.pending')
                        ->with('success', 'Permintaan cuti berhasil disetujui.'); // Leave request approved successfully.
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $leave->update([
            'status' => 'ditoLak', // rejected -> ditoLak
            'disetujui_oleh' => Auth::id(), // approved_by -> disetujui_oleh (or consider a 'rejected_by' field)
            'disetujui_pada' => now(),    // approved_at -> disetujui_pada (or 'rejected_at')
            'catatan_persetujuan' => $request->approval_notes, // approval_notes -> catatan_persetujuan
        ]);

        return redirect()->route('leaves.pending')
                        ->with('success', 'Permintaan cuti berhasil ditoLak.'); // Leave request rejected.
    }
}

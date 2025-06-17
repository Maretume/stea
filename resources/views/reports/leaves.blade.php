@extends('layouts.app')

@section('title', 'Laporan Cuti Karyawan')
@section('page-title', 'Laporan Cuti')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Filter Laporan Cuti</h4>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.leaves') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate ?? old('start_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate ?? old('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="department_id">Departemen</label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ ($departmentId == $department->id) ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="leave_type_id">Jenis Cuti</label>
                        <select class="form-control" id="leave_type_id" name="leave_type_id">
                            <option value="">Semua Jenis Cuti</option>
                            @foreach($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}" {{ ($leaveTypeId == $leaveType->id) ? 'selected' : '' }}>
                                    {{ $leaveType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $statusValue)
                                <option value="{{ $statusValue }}" {{ ($status == $statusValue) ? 'selected' : '' }}>
                                    {{ ucfirst($statusValue) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('reports.leaves') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4 class="card-title">Data Laporan Cuti</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Departemen</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Total Hari</th>
                        <th>Status</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $index => $leave)
                    <tr>
                        <td>{{ $leaves->firstItem() + $index }}</td>
                        <td>{{ $leave->user->full_name ?? '-' }}</td>
                        <td>{{ $leave->user->employee->department->name ?? '-' }}</td>
                        <td>{{ $leave->leaveType->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                        <td>{{ $leave->total_days }}</td>
                        <td>
                            <span class="badge badge-{{
                                $leave->status == 'approved' ? 'success' :
                                ($leave->status == 'rejected' ? 'danger' :
                                ($leave->status == 'pending' ? 'warning' : 'secondary'))
                            }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td>{{ $leave->reason }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data cuti yang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection

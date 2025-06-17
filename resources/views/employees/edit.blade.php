@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Data Karyawan - ' . $employee->user->full_name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Karyawan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- User Information (Read Only) -->
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi User</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama:</strong> {{ $employee->user->full_name }}</p>
                                    <p><strong>Email:</strong> {{ $employee->user->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Employee ID:</strong> {{ $employee->user->employee_id }}</p>
                                    <p><strong>Phone:</strong> {{ $employee->user->phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Department and Position -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Departemen <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" name="department_id" required>
                                <option value="">Pilih Departemen</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="position_id" class="form-label">Posisi <span class="text-danger">*</span></label>
                            <select class="form-select @error('position_id') is-invalid @enderror" 
                                    id="position_id" name="position_id" required>
                                <option value="">Pilih Posisi</option>
                                @if($employee->position)
                                    <option value="{{ $employee->position->id }}" selected>
                                        {{ $employee->position->name }} - Rp {{ number_format($employee->position->base_salary) }}
                                    </option>
                                @endif
                            </select>
                            @error('position_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hire_date" class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                   id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" required>
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="employment_type" class="form-label">Tipe Karyawan <span class="text-danger">*</span></label>
                            <select class="form-select @error('employment_type') is-invalid @enderror" 
                                    id="employment_type" name="employment_type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="permanent" {{ old('employment_type', $employee->employment_type) == 'permanent' ? 'selected' : '' }}>Tetap</option>
                                <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Kontrak</option>
                                <option value="internship" {{ old('employment_type', $employee->employment_type) == 'internship' ? 'selected' : '' }}>Magang</option>
                                <option value="freelance" {{ old('employment_type', $employee->employment_type) == 'freelance' ? 'selected' : '' }}>Freelance</option>
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contract Dates -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contract_start" class="form-label">Tanggal Mulai Kontrak</label>
                            <input type="date" class="form-control @error('contract_start') is-invalid @enderror" 
                                   id="contract_start" name="contract_start" 
                                   value="{{ old('contract_start', $employee->contract_start ? $employee->contract_start->format('Y-m-d') : '') }}">
                            @error('contract_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contract_end" class="form-label">Tanggal Berakhir Kontrak</label>
                            <input type="date" class="form-control @error('contract_end') is-invalid @enderror" 
                                   id="contract_end" name="contract_end" 
                                   value="{{ old('contract_end', $employee->contract_end ? $employee->contract_end->format('Y-m-d') : '') }}">
                            @error('contract_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Employment Status -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employment_status" class="form-label">Status Karyawan <span class="text-danger">*</span></label>
                            <select class="form-select @error('employment_status') is-invalid @enderror" 
                                    id="employment_status" name="employment_status" required>
                                <option value="">Pilih Status</option>
                                <option value="active" {{ old('employment_status', $employee->employment_status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="resigned" {{ old('employment_status', $employee->employment_status) == 'resigned' ? 'selected' : '' }}>Resign</option>
                                <option value="terminated" {{ old('employment_status', $employee->employment_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                <option value="retired" {{ old('employment_status', $employee->employment_status) == 'retired' ? 'selected' : '' }}>Pensiun</option>
                            </select>
                            @error('employment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supervisor_id" class="form-label">Supervisor</label>
                            <select class="form-select @error('supervisor_id') is-invalid @enderror" 
                                    id="supervisor_id" name="supervisor_id">
                                <option value="">Pilih Supervisor (Opsional)</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" 
                                            {{ old('supervisor_id', $employee->supervisor_id) == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->user->full_name }} - {{ $supervisor->position->name ?? 'No Position' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supervisor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Salary Information -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Gaji</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="basic_salary" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" 
                                               id="basic_salary" name="basic_salary" 
                                               value="{{ old('basic_salary', $employee->basic_salary) }}" 
                                               placeholder="0" required>
                                    </div>
                                    <small class="text-muted">Akan diisi otomatis berdasarkan posisi, bisa diedit manual</small>
                                    @error('basic_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Default Schedule Settings -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Pengaturan Jadwal Default</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="default_shift_id" class="form-label">Shift Default <span class="text-danger">*</span></label>
                                    <select class="form-select @error('default_shift_id') is-invalid @enderror"
                                            id="default_shift_id" name="default_shift_id" required>
                                        <option value="">Pilih Shift</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}"
                                                {{ old('default_shift_id', $employee->default_shift_id) == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="default_work_type" class="form-label">Tipe Kerja Default <span class="text-danger">*</span></label>
                                    <select class="form-select @error('default_work_type') is-invalid @enderror"
                                            id="default_work_type" name="default_work_type" required>
                                        <option value="">Pilih Tipe Kerja</option>
                                        <option value="WFO" {{ old('default_work_type', $employee->default_work_type) == 'WFO' ? 'selected' : '' }}>Work From Office (WFO)</option>
                                        <option value="WFA" {{ old('default_work_type', $employee->default_work_type) == 'WFA' ? 'selected' : '' }}>Work From Anywhere (WFA)</option>
                                    </select>
                                    @error('default_work_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="default_office_id" class="form-label">Office Default</label>
                                    <select class="form-select @error('default_office_id') is-invalid @enderror"
                                            id="default_office_id" name="default_office_id">
                                        <option value="">Pilih Office</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}"
                                                {{ old('default_office_id', $employee->default_office_id) == $office->id ? 'selected' : '' }}>
                                                {{ $office->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_office_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Wajib diisi untuk tipe kerja WFO</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Information -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Bank</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="bank_name" class="form-label">Nama Bank</label>
                                    <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                                           id="bank_name" name="bank_name"
                                           value="{{ old('bank_name', $employee->bank_name) }}"
                                           placeholder="Contoh: BCA, Mandiri" maxlength="50">
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bank_account" class="form-label">Nomor Rekening</label>
                                    <input type="text" class="form-control @error('bank_account') is-invalid @enderror"
                                           id="bank_account" name="bank_account"
                                           value="{{ old('bank_account', $employee->bank_account) }}"
                                           placeholder="1234567890" maxlength="30">
                                    @error('bank_account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bank_account_name" class="form-label">Nama Pemilik Rekening</label>
                                    <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror"
                                           id="bank_account_name" name="bank_account_name"
                                           value="{{ old('bank_account_name', $employee->bank_account_name) }}"
                                           placeholder="Nama sesuai rekening" maxlength="100">
                                    @error('bank_account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Karyawan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Load positions when department changes
    $('#department_id').change(function() {
        const departmentId = $(this).val();
        const positionSelect = $('#position_id');
        
        // Clear current options
        positionSelect.html('<option value="">Pilih Posisi</option>');
        $('#basic_salary').val('');
        
        if (departmentId) {
            $.get(`/api/departments/${departmentId}/positions`)
                .done(function(positions) {
                    positions.forEach(function(position) {
                        positionSelect.append(
                            `<option value="${position.id}" data-salary="${position.base_salary}">
                                ${position.name} - Rp ${parseInt(position.base_salary).toLocaleString('id-ID')}
                            </option>`
                        );
                    });
                })
                .fail(function() {
                    alert('Gagal memuat data posisi');
                });
        }
    });

    // Update basic salary when position changes
    $('#position_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const baseSalary = selectedOption.data('salary');
        
        if (baseSalary) {
            $('#basic_salary').val(baseSalary);
        } else {
            $('#basic_salary').val('');
        }
    });

    // Set contract dates based on employment type
    $('#employment_type').change(function() {
        const type = $(this).val();
        const contractStart = $('#contract_start');
        const contractEnd = $('#contract_end');
        
        if (type === 'contract' || type === 'internship') {
            contractStart.prop('required', true);
            contractEnd.prop('required', true);
        } else {
            contractStart.prop('required', false);
            contractEnd.prop('required', false);
        }
    });

    // Format number inputs
    $('#basic_salary').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(value);
    });

    // Handle work type change for office requirement
    $('#default_work_type').change(function() {
        const workType = $(this).val();
        const officeSelect = $('#default_office_id');

        if (workType === 'WFO') {
            officeSelect.prop('required', true);
            officeSelect.closest('.mb-3').find('label').html('Office Default <span class="text-danger">*</span>');
        } else {
            officeSelect.prop('required', false);
            officeSelect.closest('.mb-3').find('label').html('Office Default');
            officeSelect.val(''); // Clear selection for WFA
        }
    });

    // Trigger work type change on page load if value exists
    if ($('#default_work_type').val()) {
        $('#default_work_type').trigger('change');
    }

    // Form validation
    $('form').on('submit', function(e) {
        const departmentId = $('#department_id').val();
        const positionId = $('#position_id').val();
        const hireDate = $('#hire_date').val();
        const employmentType = $('#employment_type').val();
        const employmentStatus = $('#employment_status').val();
        const basicSalary = $('#basic_salary').val();
        const defaultShiftId = $('#default_shift_id').val();
        const defaultWorkType = $('#default_work_type').val();

        if (!departmentId || !positionId || !hireDate || !employmentType || !employmentStatus || !basicSalary || !defaultShiftId || !defaultWorkType) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }

        // Check office requirement for WFO
        if (defaultWorkType === 'WFO' && !$('#default_office_id').val()) {
            e.preventDefault();
            alert('Office wajib diisi untuk tipe kerja WFO!');
            return false;
        }
    });
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Form Tambah Karyawan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.store') }}">
                    @csrf
                    
                    <!-- User Selection -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pilih User <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Hanya user yang belum memiliki data karyawan yang ditampilkan</small>
                    </div>
                    
                    <!-- Department and Position -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Departemen <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" name="department_id" required>
                                <option value="">Pilih Departemen</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                                <!-- Options will be populated by JavaScript -->
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
                                   id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="employment_type" class="form-label">Tipe Karyawan <span class="text-danger">*</span></label>
                            <select class="form-select @error('employment_type') is-invalid @enderror"
                                    id="employment_type" name="employment_type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="permanent" {{ old('employment_type') == 'permanent' ? 'selected' : '' }}>Tetap</option>
                                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                                <option value="internship" {{ old('employment_type') == 'internship' ? 'selected' : '' }}>Magang</option>
                                <option value="freelance" {{ old('employment_type') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Contract Details -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contract_start" class="form-label">Mulai Kontrak</label>
                            <input type="date" class="form-control @error('contract_start') is-invalid @enderror" 
                                   id="contract_start" name="contract_start" value="{{ old('contract_start') }}">
                            @error('contract_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contract_end" class="form-label">Selesai Kontrak</label>
                            <input type="date" class="form-control @error('contract_end') is-invalid @enderror" 
                                   id="contract_end" name="contract_end" value="{{ old('contract_end') }}">
                            @error('contract_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Supervisor -->
                    <div class="mb-3">
                        <label for="supervisor_id" class="form-label">Supervisor</label>
                        <select class="form-select @error('supervisor_id') is-invalid @enderror" 
                                id="supervisor_id" name="supervisor_id">
                            <option value="">Pilih Supervisor</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                    {{ $supervisor->full_name }} - {{ $supervisor->employee->position->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                               id="basic_salary" name="basic_salary" value="{{ old('basic_salary') }}"
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
                                            <option value="{{ $shift->id }}" {{ old('default_shift_id') == $shift->id ? 'selected' : '' }}>
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
                                        <option value="WFO" {{ old('default_work_type') == 'WFO' ? 'selected' : '' }}>Work From Office (WFO)</option>
                                        <option value="WFA" {{ old('default_work_type') == 'WFA' ? 'selected' : '' }}>Work From Anywhere (WFA)</option>
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
                                            <option value="{{ $office->id }}" {{ old('default_office_id') == $office->id ? 'selected' : '' }}>
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
                                           id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                                           placeholder="Contoh: BCA, Mandiri" maxlength="50">
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bank_account" class="form-label">Nomor Rekening</label>
                                    <input type="text" class="form-control @error('bank_account') is-invalid @enderror"
                                           id="bank_account" name="bank_account" value="{{ old('bank_account') }}"
                                           placeholder="1234567890" maxlength="30">
                                    @error('bank_account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bank_account_name" class="form-label">Nama Pemilik Rekening</label>
                                    <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror"
                                           id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}"
                                           placeholder="Nama sesuai rekening" maxlength="100">
                                    @error('bank_account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
            // Show loading
            positionSelect.html('<option value="">Loading...</option>');
            
            // Fetch positions
            $.get(`/api/departments/${departmentId}/positions`)
                .done(function(positions) {
                    positionSelect.html('<option value="">Pilih Posisi</option>');
                    
                    positions.forEach(function(position) {
                        positionSelect.append(
                            `<option value="${position.id}" data-salary="${position.base_salary}">
                                ${position.name} - Rp ${parseInt(position.base_salary).toLocaleString('id-ID')}
                            </option>`
                        );
                    });
                })
                .fail(function() {
                    positionSelect.html('<option value="">Error loading positions</option>');
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

            // Set default dates
            if (!contractStart.val()) {
                contractStart.val($('#hire_date').val());
            }

            if (!contractEnd.val() && type === 'internship') {
                // Set 6 months for internship
                const hireDate = new Date($('#hire_date').val());
                hireDate.setMonth(hireDate.getMonth() + 6);
                contractEnd.val(hireDate.toISOString().split('T')[0]);
            }
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
});
</script>
@endpush

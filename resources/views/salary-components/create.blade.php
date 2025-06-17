@extends('layouts.app')

@section('title', 'Tambah Komponen Gaji')
@section('page-title', 'Tambah Komponen Gaji')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Komponen Gaji</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('salary-components.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required maxlength="100">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Kode Komponen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" required maxlength="20">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="allowance" {{ old('type') == 'allowance' ? 'selected' : '' }}>Tunjangan</option>
                                <option value="deduction" {{ old('type') == 'deduction' ? 'selected' : '' }}>Potongan</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="calculation_type" class="form-label">Tipe Perhitungan <span class="text-danger">*</span></label>
                            <select class="form-select @error('calculation_type') is-invalid @enderror" 
                                    id="calculation_type" name="calculation_type" required>
                                <option value="">Pilih Tipe Perhitungan</option>
                                <option value="fixed" {{ old('calculation_type') == 'fixed' ? 'selected' : '' }}>Nominal Tetap</option>
                                <option value="percentage" {{ old('calculation_type') == 'percentage' ? 'selected' : '' }}>Persentase</option>
                            </select>
                            @error('calculation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="default_amount" class="form-label">Nominal Default <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('default_amount') is-invalid @enderror" 
                                   id="default_amount" name="default_amount" value="{{ old('default_amount') }}" 
                                   step="0.01" min="0" required>
                            @error('default_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3" id="percentage_field" style="display: none;">
                            <label for="percentage" class="form-label">Persentase (%)</label>
                            <input type="number" class="form-control @error('percentage') is-invalid @enderror" 
                                   id="percentage" name="percentage" value="{{ old('percentage') }}" 
                                   step="0.01" min="0" max="100">
                            @error('percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_taxable" name="is_taxable" 
                                       value="1" {{ old('is_taxable') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_taxable">
                                    Kena Pajak
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Urutan</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" min="1">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan
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
    $('#calculation_type').change(function() {
        if ($(this).val() === 'percentage') {
            $('#percentage_field').show();
            $('#percentage').attr('required', true);
        } else {
            $('#percentage_field').hide();
            $('#percentage').attr('required', false);
        }
    });
    
    // Trigger change event on page load
    $('#calculation_type').trigger('change');
});
</script>
@endpush

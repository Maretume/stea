<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $salaryComponents = SalaryComponent::orderBy('urutan')->orderBy('nama')->paginate(10); // sort_order -> urutan, name -> nama

        // Calculate summary data
        // type -> tipe, is_active -> aktif
        // ENUM values: allowance -> tunjangan, deduction -> potongan, benefit -> manfaat
        $allowances = SalaryComponent::where('tipe', 'tunjangan')->where('aktif', true)->count();
        $deductions = SalaryComponent::where('tipe', 'potongan')->where('aktif', true)->count();
        $benefits = SalaryComponent::where('tipe', 'manfaat')->where('aktif', true)->count();
        $total = SalaryComponent::where('aktif', true)->count();

        return view('salary-components.index', compact('salaryComponents', 'allowances', 'deductions', 'benefits', 'total'));
    }

    public function create()
    {
        return view('salary-components.create');
    }

    public function store(Request $request)
    {
        // Assuming request field names are still in English for validation keys
        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|unique:komponen_gaji,kode|max:20', // salary_components -> komponen_gaji, code -> kode
            'type' => 'required|in:tunjangan,potongan,manfaat', // allowance,deduction,benefit -> tunjangan,potongan,manfaat
            'calculation_type' => 'required|in:tetap,persentase,rumus', // fixed,percentage,formula -> tetap,persentase,rumus
            'default_amount' => 'required|numeric',
            'is_taxable' => 'boolean',
            'description' => 'nullable',
            'sort_order' => 'integer', // Added for consistency
            'is_active' => 'boolean',  // Added for consistency
        ]);

        SalaryComponent::create([
            'nama' => $request->name,
            'kode' => $request->code,
            'tipe' => $request->type,
            'tipe_perhitungan' => $request->calculation_type,
            'jumlah_standar' => $request->default_amount,
            'persentase' => $request->percentage, // Keep if present in request
            'rumus' => $request->formula,         // Keep if present in request
            'kena_pajak' => $request->is_taxable,
            'deskripsi' => $request->description,
            'urutan' => $request->sort_order ?? 0,
            'aktif' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('salary-components.index')
                        ->with('success', 'Komponen gaji berhasil dibuat.'); // Salary component -> Komponen gaji
    }

    public function show(SalaryComponent $salaryComponent)
    {
        return view('salary-components.show', compact('salaryComponent'));
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return view('salary-components.edit', compact('salaryComponent'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        // Assuming request field names are still in English
        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|max:20|unique:komponen_gaji,kode,' . $salaryComponent->id, // salary_components -> komponen_gaji, code -> kode
            'type' => 'required|in:tunjangan,potongan,manfaat', // allowance,deduction,benefit -> tunjangan,potongan,manfaat
            'calculation_type' => 'required|in:tetap,persentase,rumus', // fixed,percentage,formula -> tetap,persentase,rumus
            'default_amount' => 'required|numeric',
            'is_taxable' => 'boolean',
            'description' => 'nullable',
            'sort_order' => 'integer', // Added for consistency
            'is_active' => 'boolean',  // Added for consistency
        ]);

        $salaryComponent->update([
            'nama' => $request->name,
            'kode' => $request->code,
            'tipe' => $request->type,
            'tipe_perhitungan' => $request->calculation_type,
            'jumlah_standar' => $request->default_amount,
            'persentase' => $request->percentage,
            'rumus' => $request->formula,
            'kena_pajak' => $request->is_taxable,
            'deskripsi' => $request->description,
            'urutan' => $request->sort_order ?? $salaryComponent->urutan,
            'aktif' => $request->has('is_active') ? $request->is_active : $salaryComponent->aktif,
        ]);

        return redirect()->route('salary-components.index')
                        ->with('success', 'Komponen gaji berhasil diperbarui.'); // Salary component -> Komponen gaji
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();

        return redirect()->route('salary-components.index')
                        ->with('success', 'Komponen gaji berhasil dihapus.'); // Salary component -> Komponen gaji
    }
}

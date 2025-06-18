<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;

class CFOPayrollApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $cfoUser;
    protected $payroll;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles and permissions
        $this->createRolesAndPermissions();
        
        // Create CFO user
        $this->cfoUser = $this->createCFOUser();
        
        // Create test payroll
        $this->payroll = $this->createTestPayroll();
    }

    private function createRolesAndPermissions()
    {
        // Create CFO role
        $cfoRole = Role::create([
            'nama_kunci' => 'cfo', // name -> nama_kunci
            'nama_tampilan' => 'Direktur Keuangan', // display_name -> nama_tampilan
            'deskripsi' => 'Peran CFO dengan izin persetujuan penggajian', // description -> deskripsi
            'aktif' => true, // is_active -> aktif
        ]);

        // Create payroll.approve permission
        $approvePermission = Permission::create([
            'nama_kunci' => 'payroll.approve', // name -> nama_kunci
            'nama_tampilan' => 'Setujui Penggajian', // display_name -> nama_tampilan
            'modul' => 'penggajian', // module 'payroll' -> 'penggajian'
            'deskripsi' => 'Izin untuk menyetujui penggajian' // description -> deskripsi
        ]);

        // Create payroll.view_all permission
        $viewPermission = Permission::create([
            'nama_kunci' => 'payroll.view_all',
            'nama_tampilan' => 'Lihat Semua Penggajian',
            'modul' => 'penggajian', // module 'payroll' -> 'penggajian'
            'deskripsi' => 'Izin untuk melihat semua penggajian'
        ]);

        // Assign permissions to CFO role
        // Assuming Role model permissions() relation uses peran_izin pivot
        $cfoRole->permissions()->attach([$approvePermission->id, $viewPermission->id]);
    }

    private function createCFOUser()
    {
        $user = User::create([
            'id_karyawan' => 'CFO001',         // employee_id -> id_karyawan
            'nama_pengguna' => 'cfo_test',    // username -> nama_pengguna
            'surel' => 'cfo.test@example.com', // email -> surel
            'kata_sandi' => bcrypt('password'), // password -> kata_sandi
            'nama_depan' => 'Test',           // first_name -> nama_depan
            'nama_belakang' => 'CFO',         // last_name -> nama_belakang
            'status' => 'aktif',              // active -> aktif
        ]);

        // Assign CFO role
        $cfoRole = Role::where('nama_kunci', 'cfo')->first(); // name -> nama_kunci
        $user->roles()->attach($cfoRole->id, [
            'ditetapkan_pada' => now(), // assigned_at -> ditetapkan_pada
            'aktif' => true,           // is_active -> aktif
        ]);

        return $user;
    }

    private function createTestPayroll()
    {
        // Create a test employee
        $employeeUser = User::create([
            'id_karyawan' => 'EMP001',
            'nama_pengguna' => 'employee_test',
            'surel' => 'employee.test@example.com',
            'kata_sandi' => bcrypt('password'),
            'nama_depan' => 'Test',
            'nama_belakang' => 'Karyawan', // Employee -> Karyawan
            'status' => 'aktif',
        ]);

        // Create payroll period
        $period = PayrollPeriod::create([
            'nama' => 'Periode Tes', // Test Period -> Periode Tes
            'tanggal_mulai' => now()->startOfMonth(), // start_date -> tanggal_mulai
            'tanggal_selesai' => now()->endOfMonth(), // end_date -> tanggal_selesai
            'status' => 'terhitung',                 // calculated -> terhitung
            'dibuat_oleh' => $this->cfoUser->id,    // created_by -> dibuat_oleh
        ]);

        // Create payroll
        return Payroll::create([
            'id_periode_penggajian' => $period->id, // payroll_period_id -> id_periode_penggajian
            'id_pengguna' => $employeeUser->id,   // user_id -> id_pengguna
            'gaji_pokok' => 5000000,             // basic_salary -> gaji_pokok
            'total_tunjangan' => 1000000,        // total_allowances -> total_tunjangan
            'total_potongan' => 500000,         // total_deductions -> total_potongan
            'gaji_kotor' => 6000000,             // gross_salary -> gaji_kotor
            'jumlah_pajak' => 300000,            // tax_amount -> jumlah_pajak
            'gaji_bersih' => 5700000,            // net_salary -> gaji_bersih
            'status' => 'menunggu',               // pending -> menunggu (or 'konsep' if that's the initial state)
        ]);
    }

    public function test_cfo_has_payroll_approve_permission()
    {
        $this->assertTrue($this->cfoUser->hasPermission('payroll.approve')); // Permission name is key
    }

    public function test_cfo_can_approve_individual_payroll()
    {
        $this->actingAs($this->cfoUser);

        $response = $this->post(route('payroll.approve', $this->payroll->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Penggajian berhasil disetujui.'); // Payroll -> Penggajian

        $this->payroll->refresh();
        $this->assertEquals('disetujui', $this->payroll->status); // approved -> disetujui
        $this->assertEquals($this->cfoUser->id, $this->payroll->disetujui_oleh_payroll); // approved_by -> disetujui_oleh_payroll
        $this->assertNotNull($this->payroll->disetujui_pada_payroll); // approved_at -> disetujui_pada_payroll
    }

    public function test_cfo_can_bulk_approve_payrolls()
    {
        // Create another payroll
        $payroll2 = Payroll::create([
            'id_periode_penggajian' => $this->payroll->id_periode_penggajian, // payroll_period_id -> id_periode_penggajian
            'id_pengguna' => $this->payroll->id_pengguna, // user_id -> id_pengguna
            'gaji_pokok' => 4000000,
            'total_tunjangan' => 800000,
            'total_potongan' => 400000,
            'gaji_kotor' => 4800000,
            'jumlah_pajak' => 240000,
            'gaji_bersih' => 4560000,
            'status' => 'konsep', // draft -> konsep
        ]);

        $this->actingAs($this->cfoUser);

        $response = $this->post(route('payroll.bulk.approve'), [
            'payroll_ids' => [$this->payroll->id, $payroll2->id]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Berhasil menyetujui 2 penggajian.'); // payroll -> penggajian

        $this->payroll->refresh();
        $payroll2->refresh();

        $this->assertEquals('disetujui', $this->payroll->status); // approved -> disetujui
        $this->assertEquals('disetujui', $payroll2->status);    // approved -> disetujui
        $this->assertEquals($this->cfoUser->id, $this->payroll->disetujui_oleh_payroll); // approved_by -> disetujui_oleh_payroll
        $this->assertEquals($this->cfoUser->id, $payroll2->disetujui_oleh_payroll);   // approved_by -> disetujui_oleh_payroll
    }

    public function test_non_cfo_cannot_approve_payroll()
    {
        // Create regular employee user
        $regularUser = User::create([
            'id_karyawan' => 'REG001',
            'nama_pengguna' => 'regular_user',
            'surel' => 'regular@example.com',
            'kata_sandi' => bcrypt('password'),
            'nama_depan' => 'Regular',
            'nama_belakang' => 'User',
            'status' => 'aktif',
        ]);

        $this->actingAs($regularUser);

        $response = $this->post(route('payroll.approve', $this->payroll->id));

        $response->assertStatus(403);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ShiftController;

use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/ceo', [DashboardController::class, 'index'])->name('dashboard.ceo');
    Route::get('/dashboard/cfo', [DashboardController::class, 'index'])->name('dashboard.cfo');
    Route::get('/dashboard/hrd', [DashboardController::class, 'index'])->name('dashboard.hrd');
    Route::get('/dashboard/personalia', [DashboardController::class, 'index'])->name('dashboard.personalia');
    Route::get('/dashboard/karyawan', [DashboardController::class, 'index'])->name('dashboard.karyawan');
    
    // Profile routes
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/password', function() {
        return view('profile.password');
    })->name('profile.password');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password.update');
    
    // User Management
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/roles', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.remove-role');
    });
    
    // Employee Management
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->middleware('permission:employees.view')->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->middleware('permission:employees.create')->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view')->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employees.edit')->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.edit')->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('destroy');
        Route::get('/{employee}/salary', [EmployeeController::class, 'salary'])->middleware('permission:employees.edit')->name('salary');
        Route::post('/{employee}/salary', [EmployeeController::class, 'updateSalary'])->middleware('permission:employees.edit')->name('salary.update');
    });
    
    // Department Management - TEMPORARILY REMOVED PERMISSION MIDDLEWARE FOR DEBUGGING
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });
    
    // Position Management
    Route::prefix('positions')->name('positions.')->group(function () {
        Route::get('/', [PositionController::class, 'index'])->middleware('permission:positions.view')->name('index');
        Route::get('/create', [PositionController::class, 'create'])->middleware('permission:positions.create')->name('create');
        Route::post('/', [PositionController::class, 'store'])->middleware('permission:positions.create')->name('store');
        Route::get('/{position}', [PositionController::class, 'show'])->middleware('permission:positions.view')->name('show');
        Route::get('/{position}/edit', [PositionController::class, 'edit'])->middleware('permission:positions.edit')->name('edit');
        Route::put('/{position}', [PositionController::class, 'update'])->middleware('permission:positions.edit')->name('update');
        Route::delete('/{position}', [PositionController::class, 'destroy'])->middleware('permission:positions.delete')->name('destroy');
    });
    
    // Attendance Management
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/clock', function() {
            return view('attendance.clock');
        })->name('clock');
        Route::post('/attempt', [AttendanceController::class, 'attempt'])->name('attempt');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');

        Route::get('/today', [AttendanceController::class, 'getTodayAttendance'])->name('today');
        Route::get('/recent', [AttendanceController::class, 'getRecentAttendance'])->name('recent');
        Route::post('/validate-location', [AttendanceController::class, 'validateCurrentLocation'])->name('validate-location');

        Route::middleware('permission:attendance.edit')->group(function () {
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        });

        Route::middleware('permission:attendance.reports')->group(function () {
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        });
    });

    // Schedule Management
    Route::prefix('schedules')->name('schedules.')->group(function () {
        // Routes accessible by all authenticated users (including karyawan for viewing their own schedules)
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::get('/calendar', [ScheduleController::class, 'calendar'])->name('calendar');
        Route::get('/{schedule}', [ScheduleController::class, 'show'])->name('show');

        // Routes only for Admin/HR/Manager (schedule management)
        Route::middleware('permission:schedules.create')->group(function () {
            Route::get('/create', [ScheduleController::class, 'create'])->name('create');
            Route::post('/', [ScheduleController::class, 'store'])->name('store');
            Route::get('/bulk-create', [ScheduleController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-create', [ScheduleController::class, 'bulkStore'])->name('bulk-store');
        });

        Route::middleware('permission:schedules.edit')->group(function () {
            Route::get('/{schedule}/edit', [ScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [ScheduleController::class, 'update'])->name('update');
            Route::post('/bulk-edit', [ScheduleController::class, 'bulkEdit'])->name('bulk-edit');
            Route::put('/bulk-update', [ScheduleController::class, 'bulkUpdate'])->name('bulk-update');
        });

        Route::middleware('permission:schedules.delete')->group(function () {
            Route::delete('/{schedule}', [ScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/{schedule}/cancel', [ScheduleController::class, 'cancel'])->name('cancel');
        });
    });

    // Office Management - Only for Admin/HR
    Route::prefix('offices')->name('offices.')->middleware('permission:offices.view')->group(function () {
        Route::get('/', [OfficeController::class, 'index'])->name('index');
        Route::get('/{office}', [OfficeController::class, 'show'])->name('show');

        Route::middleware('permission:offices.create')->group(function () {
            Route::get('/create', [OfficeController::class, 'create'])->name('create');
            Route::post('/', [OfficeController::class, 'store'])->name('store');
        });

        Route::middleware('permission:offices.edit')->group(function () {
            Route::get('/{office}/edit', [OfficeController::class, 'edit'])->name('edit');
            Route::put('/{office}', [OfficeController::class, 'update'])->name('update');
        });

        Route::middleware('permission:offices.delete')->group(function () {
            Route::delete('/{office}', [OfficeController::class, 'destroy'])->name('destroy');
        });
    });

    // Shift Management - Only for Admin/HR
    Route::prefix('shifts')->name('shifts.')->middleware('permission:shifts.view')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');

        Route::middleware('permission:shifts.create')->group(function () {
            Route::get('/create', [ShiftController::class, 'create'])->name('create');
            Route::post('/', [ShiftController::class, 'store'])->name('store');
        });

        Route::middleware('permission:shifts.edit')->group(function () {
            Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit');
            Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
        });

        Route::middleware('permission:shifts.delete')->group(function () {
            Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
        });
    });

    // Debug route for permissions
    Route::get('/debug/permissions', function () {
        return view('debug.permissions');
    })->name('debug.permissions');

    // Demo route for features
    Route::get('/demo/features', function () {
        return view('demo.features');
    })->name('demo.features');

    // Test route for attendance system
    Route::get('/test/attendance', function () {
        $output = [];

        try {
            // Check data counts
            $output[] = 'Users: ' . \App\Models\User::count();
            $output[] = 'Employees: ' . \App\Models\Employee::count();
            $output[] = 'Shifts: ' . \App\Models\Shift::count();
            $output[] = 'Offices: ' . \App\Models\Office::count();

            // Get first employee
            $employee = \App\Models\Employee::with('user')->first();
            if ($employee) {
                $output[] = "Testing with employee: {$employee->user->full_name}";
                $output[] = "Default shift ID: " . ($employee->default_shift_id ?? 'NULL');
                $output[] = "Default office ID: " . ($employee->default_office_id ?? 'NULL');
                $output[] = "Default work type: " . ($employee->default_work_type ?? 'NULL');

                // Test getOrCreateTodaySchedule method
                $schedule = $employee->user->getOrCreateTodaySchedule();
                if ($schedule) {
                    $output[] = "Schedule created/found successfully!";
                    $output[] = "Schedule ID: {$schedule->id}";
                    $output[] = "Work type: {$schedule->work_type}";
                    $output[] = "Shift: {$schedule->shift->name}";
                    $output[] = "Office: " . ($schedule->office->name ?? 'N/A');
                } else {
                    $output[] = "Failed to create/find schedule";
                }
            } else {
                $output[] = "No employees found";
            }
        } catch (\Exception $e) {
            $output[] = "Error: " . $e->getMessage();
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');
    })->name('test.attendance');

    // Setup payroll data
    Route::get('/setup/payroll', function () {
        $output = [];

        try {
            // 1. Check admin user
            $admin = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->first();

            if (!$admin) {
                $output[] = "❌ Admin user not found";
                return response('<pre>' . implode("\n", $output) . '</pre>');
            }

            $output[] = "✅ Admin user found: {$admin->full_name}";

            // 2. Create payroll period
            $period = \App\Models\PayrollPeriod::firstOrCreate([
                'name' => 'Gaji Bulan ' . \Carbon\Carbon::now()->format('F Y'),
                'start_date' => \Carbon\Carbon::now()->startOfMonth(),
            ], [
                'end_date' => \Carbon\Carbon::now()->endOfMonth(),
                'pay_date' => \Carbon\Carbon::now()->endOfMonth()->addDays(5),
                'status' => 'draft',
                'created_by' => $admin->id,
            ]);

            $output[] = "✅ Payroll period: {$period->name}";

            // 3. Check employees and set basic salary
            $employees = \App\Models\Employee::where('employment_status', 'active')->with('user')->get();
            $output[] = "✅ Found {$employees->count()} active employees";

            $updated = 0;
            foreach ($employees as $employee) {
                if (!$employee->basic_salary || $employee->basic_salary == 0) {
                    $employee->update(['basic_salary' => 5000000]);
                    $updated++;
                }
                $output[] = "- {$employee->user->full_name}: Rp " . number_format($employee->basic_salary, 0, ',', '.');
            }

            if ($updated > 0) {
                $output[] = "✅ Updated {$updated} employees with basic salary";
            }

            // 4. Create default salary components
            $components = [
                [
                    'name' => 'Tunjangan Transport',
                    'code' => 'TRANSPORT_NEW',
                    'type' => 'allowance',
                    'calculation_type' => 'fixed',
                    'default_amount' => 500000,
                    'is_taxable' => false,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Tunjangan Makan',
                    'code' => 'MEAL_NEW',
                    'type' => 'allowance',
                    'calculation_type' => 'fixed',
                    'default_amount' => 300000,
                    'is_taxable' => false,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
            ];

            foreach ($components as $componentData) {
                try {
                    $component = \App\Models\SalaryComponent::firstOrCreate(
                        ['code' => $componentData['code']],
                        $componentData
                    );
                    $output[] = "✅ Component: {$component->name}";
                } catch (Exception $e) {
                    $output[] = "⚠️ Component error: " . $e->getMessage();
                }
            }

            $output[] = "";
            $output[] = "🎉 Setup completed! You can now:";
            $output[] = "1. Go to 'Penggajian' menu";
            $output[] = "2. Click 'Kelola Periode'";
            $output[] = "3. Click 'Calculate' on the draft period";

        } catch (\Exception $e) {
            $output[] = "Error: " . $e->getMessage();
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');
    })->name('setup.payroll');

    // Test payroll permissions
    Route::get('/test/payroll-permissions', function () {
        $output = [];

        try {
            $user = auth()->user();
            if (!$user) {
                $output[] = "❌ User not logged in";
                return response('<pre>' . implode("\n", $output) . '</pre>');
            }

            $output[] = "✅ User: {$user->full_name}";
            $output[] = "✅ Roles: " . $user->roles->pluck('name')->implode(', ');

            $payrollPermissions = [
                'payroll.view',
                'payroll.view_all',
                'payroll.create',
                'payroll.approve',
                'salary_components.view'
            ];

            $output[] = "\n📋 Payroll Permissions:";
            foreach ($payrollPermissions as $permission) {
                $hasPermission = $user->hasPermission($permission);
                $status = $hasPermission ? '✅' : '❌';
                $output[] = "{$status} {$permission}";
            }

            $output[] = "\n🔗 Available Routes:";
            if ($user->hasPermission('payroll.view_all')) {
                $output[] = "✅ /payroll (Main payroll page)";
                $output[] = "✅ /payroll/periods (Manage periods)";
            } elseif ($user->hasPermission('payroll.view')) {
                $output[] = "✅ /payroll/slip (Employee slip)";
            } else {
                $output[] = "❌ No payroll access";
            }

        } catch (\Exception $e) {
            $output[] = "Error: " . $e->getMessage();
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');
    })->name('test.payroll.permissions');

    // Test payroll controller
    Route::get('/test/payroll-controller', function () {
        try {
            $controller = new \App\Http\Controllers\PayrollController();
            return "✅ PayrollController loaded successfully";
        } catch (\Exception $e) {
            return "❌ Error: " . $e->getMessage();
        }
    })->name('test.payroll.controller');

    // Debug payroll index
    Route::get('/debug/payroll', function () {
        try {
            $controller = new \App\Http\Controllers\PayrollController();
            $request = new \Illuminate\Http\Request();

            // Call the index method directly
            $response = $controller->index($request);

            return $response;
        } catch (\Exception $e) {
            return response('<pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
        }
    })->name('debug.payroll');

    // Simple test routes
    Route::get('/test-payroll', function () {
        return 'Payroll route test works!';
    });

    Route::get('/test-payroll-periods', function () {
        return 'Payroll periods route test works!';
    });

    // Direct payroll routes without middleware
    Route::get('/payroll-direct', [PayrollController::class, 'index']);
    Route::get('/payroll-periods-direct', [PayrollController::class, 'periods']);
    Route::get('/payroll-periods-create-direct', [PayrollController::class, 'createPeriod']);

    // Test simple route
    Route::get('/test-periods', function() {
        return 'Test periods route works!';
    });

    // Test overtime pending route
    Route::get('/test-overtime-pending', function() {
        return 'Test overtime pending route works!';
    });

    // Direct test for overtime pending
    Route::get('/permits/overtime/pending-test', [App\Http\Controllers\PermitController::class, 'overtimePending']);

    // Alternative routes if main routes don't work
    Route::get('/periods', [PayrollController::class, 'periods'])->name('periods.index');
    Route::get('/periods/create', [PayrollController::class, 'createPeriod'])->name('periods.create');
    Route::post('/periods', [PayrollController::class, 'storePeriod'])->name('periods.store');

    // Test salary components query
    Route::get('/test/salary-components-query', function() {
        try {
            $user = \App\Models\User::first();
            $components = $user->salaryComponents()->where('salary_components.is_active', true)->get();
            return response()->json([
                'success' => true,
                'user' => $user->full_name,
                'components_count' => $components->count(),
                'components' => $components->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Test overtime functionality
    Route::get('/test/overtime', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not logged in']);
            }

            $overtimes = \App\Models\OvertimeRequest::byUser($user->id)->latest()->take(5)->get();

            return response()->json([
                'success' => true,
                'user' => $user->full_name,
                'overtime_count' => $overtimes->count(),
                'overtimes' => $overtimes->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Test create overtime
    Route::get('/test/create-overtime', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not logged in']);
            }

            $overtime = new \App\Models\OvertimeRequest([
                'user_id' => $user->id,
                'overtime_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'work_description' => 'Test overtime work description',
                'reason' => 'Test overtime reason',
            ]);

            $overtime->planned_hours = $overtime->calculatePlannedHours();
            $overtime->save();

            return response()->json([
                'success' => true,
                'message' => 'Overtime request created successfully',
                'overtime' => $overtime->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Test leave types
    Route::get('/test/leave-types', function() {
        try {
            $leaveTypes = \App\Models\LeaveType::all();

            if ($leaveTypes->isEmpty()) {
                // Create default leave types
                $defaultTypes = [
                    ['name' => 'Cuti Tahunan', 'code' => 'ANNUAL', 'max_days_per_year' => 12, 'is_paid' => true, 'is_active' => true],
                    ['name' => 'Cuti Sakit', 'code' => 'SICK', 'max_days_per_year' => 30, 'is_paid' => true, 'is_active' => true],
                    ['name' => 'Cuti Melahirkan', 'code' => 'MATERNITY', 'max_days_per_year' => 90, 'is_paid' => true, 'is_active' => true],
                    ['name' => 'Cuti Khusus', 'code' => 'SPECIAL', 'max_days_per_year' => 5, 'is_paid' => true, 'is_active' => true],
                ];

                foreach ($defaultTypes as $type) {
                    \App\Models\LeaveType::create($type);
                }

                $leaveTypes = \App\Models\LeaveType::all();
            }

            return response()->json([
                'success' => true,
                'leave_types_count' => $leaveTypes->count(),
                'leave_types' => $leaveTypes->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Test create leave
    Route::get('/test/create-leave', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not logged in']);
            }

            // Get first leave type
            $leaveType = \App\Models\LeaveType::first();
            if (!$leaveType) {
                return response()->json(['error' => 'No leave types found. Run /test/leave-types first']);
            }

            $leave = new \App\Models\LeaveRequest([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDay()->format('Y-m-d'),
                'end_date' => now()->addDays(2)->format('Y-m-d'),
                'reason' => 'Test leave request',
                'notes' => 'This is a test leave request',
            ]);

            $leave->total_days = $leave->calculateTotalDays();
            $leave->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave request created successfully',
                'leave' => $leave->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Debug leave form submission
    Route::post('/test/submit-leave', function(\Illuminate\Http\Request $request) {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Form data received successfully',
                'data' => $request->all(),
                'user' => auth()->user() ? auth()->user()->full_name : 'Not logged in'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Simple leave create without complex validation
    Route::get('/simple-leave-create', function() {
        $user = auth()->user();
        $leaveTypes = \App\Models\LeaveType::all();
        return view('permits.leave.simple-create', compact('leaveTypes'));
    });

    Route::post('/simple-leave-store', function(\Illuminate\Http\Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return back()->with('error', 'User not logged in');
            }

            $leave = new \App\Models\LeaveRequest([
                'user_id' => $user->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $leave->total_days = $leave->calculateTotalDays();
            $leave->save();

            return redirect('/permits/leave')->with('success', 'Leave request created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    });

    // Test notification system
    Route::get('/test-notification', function() {
        return view('test-notification');
    });

    Route::post('/test-ajax-notification', function() {
        return response()->json([
            'success' => true,
            'message' => 'AJAX notification test successful!'
        ]);
    });

    // Test leave approval with URL parameter approach
    Route::post('/test-leave-approval', function() {
        \Log::info('Test leave approval called');

        // Return redirect with success parameter in URL
        $currentUrl = url()->previous();
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        $redirectUrl = $currentUrl . $separator . 'approved=1&message=' . urlencode('Test pengajuan cuti berhasil disetujui!');

        return redirect($redirectUrl);
    });

    // Test flash message
    Route::get('/test-flash', function() {
        return redirect()->back()->with('success', 'Test flash message berhasil!');
    });

    Route::get('/test-flash-page', function() {
        return view('test-notification')->with('success', 'Test flash message dari view!');
    });

    // Debug session
    Route::get('/debug-session', function() {
        $sessionData = [
            'all_session' => session()->all(),
            'success_message' => session('success'),
            'error_message' => session('error'),
            'flash_data' => session()->getFlashBag()->all(),
        ];

        return response('<pre>' . print_r($sessionData, true) . '</pre>');
    });

    // Test leave data
    Route::get('/test/leave-data', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not logged in']);
            }

            $leaves = \App\Models\LeaveRequest::byUser($user->id)
                                             ->with(['leaveType', 'user'])
                                             ->latest()
                                             ->get();

            $leaveTypes = \App\Models\LeaveType::all();

            $result = [];
            foreach ($leaves as $leave) {
                $result[] = [
                    'id' => $leave->id,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => $leave->total_days,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'leave_type_id' => $leave->leave_type_id,
                    'leave_type_name' => $leave->leaveType ? $leave->leaveType->name : 'NULL',
                    'leave_type_exists' => $leave->leaveType ? 'YES' : 'NO',
                ];
            }

            return response()->json([
                'success' => true,
                'user' => $user->full_name,
                'leave_requests_count' => $leaves->count(),
                'leave_requests' => $result,
                'available_leave_types' => $leaveTypes->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Fix broken leave data
    Route::get('/fix/leave-data', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not logged in']);
            }

            // Get first available leave type
            $defaultLeaveType = \App\Models\LeaveType::first();
            if (!$defaultLeaveType) {
                return response()->json(['error' => 'No leave types found. Run /test/leave-types first']);
            }

            // Find leave requests with invalid leave_type_id
            $brokenLeaves = \App\Models\LeaveRequest::byUser($user->id)
                                                   ->whereDoesntHave('leaveType')
                                                   ->get();

            $fixed = 0;
            foreach ($brokenLeaves as $leave) {
                $leave->leave_type_id = $defaultLeaveType->id;
                $leave->save();
                $fixed++;
            }

            return response()->json([
                'success' => true,
                'message' => "Fixed {$fixed} broken leave requests",
                'default_leave_type' => $defaultLeaveType->name,
                'fixed_count' => $fixed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Setup overtime permissions for HRD and Admin
    Route::get('/setup/overtime-permissions-hrd', function() {
        try {
            $permissions = [
                'overtime.view' => 'View overtime requests',
                'overtime.create' => 'Create overtime requests',
                'overtime.edit' => 'Edit overtime requests',
                'overtime.delete' => 'Delete overtime requests',
                'overtime.approve' => 'Approve/reject overtime requests',
                'overtime.reports' => 'View overtime reports',
                'overtime.view_all' => 'View all overtime requests',
                'overtime.manage' => 'Full overtime management access',
            ];

            $output = [];
            $output[] = "🚀 Setting up Overtime Permissions for HRD and Admin...";

            // Create permissions
            $created = 0;
            foreach ($permissions as $name => $description) {
                $permission = \App\Models\Permission::firstOrCreate(
                    ['name' => $name],
                    ['description' => $description, 'module' => 'overtime']
                );
                if ($permission->wasRecentlyCreated) {
                    $created++;
                }
            }
            $output[] = "✅ Created/Updated {$created} permissions";

            // Setup Admin role
            $adminRole = \App\Models\Role::firstOrCreate(
                ['name' => 'Admin'],
                ['display_name' => 'Administrator', 'description' => 'Full system access', 'is_active' => true]
            );
            $allPermissions = \App\Models\Permission::whereIn('name', array_keys($permissions))->get();
            $adminRole->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
            $output[] = "✅ Admin role granted all overtime permissions";

            // Setup HRD role
            $hrdRole = \App\Models\Role::firstOrCreate(
                ['name' => 'HRD'],
                ['display_name' => 'Human Resources Department', 'description' => 'HR management access', 'is_active' => true]
            );
            $hrdPermissions = \App\Models\Permission::whereIn('name', [
                'overtime.view', 'overtime.view_all', 'overtime.create', 'overtime.edit',
                'overtime.delete', 'overtime.approve', 'overtime.reports', 'overtime.manage'
            ])->get();
            $hrdRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
            $output[] = "✅ HRD role granted overtime approval permissions";

            // Check HR role
            $hrRole = \App\Models\Role::where('name', 'HR')->first();
            if ($hrRole) {
                $hrRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
                $output[] = "✅ HR role granted overtime approval permissions";
            }

            // Setup Manager role
            $managerRole = \App\Models\Role::firstOrCreate(
                ['name' => 'Manager'],
                ['display_name' => 'Manager', 'description' => 'Department management access', 'is_active' => true]
            );
            $managerPermissions = \App\Models\Permission::whereIn('name', [
                'overtime.view', 'overtime.approve'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions->pluck('id'));
            $output[] = "✅ Manager role granted overtime approval permissions";

            $output[] = "";
            $output[] = "📊 Users with Overtime Approval Access:";
            $approvalUsers = \App\Models\User::whereHas('roles', function($query) {
                $query->whereIn('name', ['Admin', 'HRD', 'HR', 'Manager']);
            })->with('roles')->get();

            if ($approvalUsers->count() > 0) {
                foreach ($approvalUsers as $user) {
                    $roles = $user->roles->pluck('name')->implode(', ');
                    $output[] = "👤 {$user->full_name} ({$user->username}) - Roles: {$roles}";
                }
            } else {
                $output[] = "⚠️ No users found with approval roles";
            }

            $output[] = "";
            $output[] = "✅ Setup completed! HRD and Admin can now approve overtime requests.";
            $output[] = "🔗 Access pending overtime at: /permits/overtime/pending";

            return response('<pre>' . implode("\n", $output) . '</pre>');

        } catch (\Exception $e) {
            return response('<pre>❌ Error: ' . $e->getMessage() . '</pre>');
        }
    });

    // Setup leave permissions for HRD and Admin
    Route::get('/setup/leave-permissions-hrd', function() {
        try {
            $permissions = [
                'leave.view' => 'View leave requests',
                'leave.create' => 'Create leave requests',
                'leave.edit' => 'Edit leave requests',
                'leave.delete' => 'Delete leave requests',
                'leave.approve' => 'Approve/reject leave requests',
                'leave.reports' => 'View leave reports',
                'leave.view_all' => 'View all leave requests',
                'leave.manage' => 'Full leave management access',
            ];

            $output = [];
            $output[] = "🚀 Setting up Leave Permissions for HRD and Admin...";

            // Create permissions
            $created = 0;
            foreach ($permissions as $name => $description) {
                $permission = \App\Models\Permission::firstOrCreate(
                    ['name' => $name],
                    ['description' => $description, 'module' => 'leave']
                );
                if ($permission->wasRecentlyCreated) {
                    $created++;
                }
            }
            $output[] = "✅ Created/Updated {$created} permissions";

            // Setup Admin role
            $adminRole = \App\Models\Role::firstOrCreate(
                ['name' => 'Admin'],
                ['display_name' => 'Administrator', 'description' => 'Full system access', 'is_active' => true]
            );
            $allPermissions = \App\Models\Permission::whereIn('name', array_keys($permissions))->get();
            $adminRole->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
            $output[] = "✅ Admin role granted all leave permissions";

            // Setup HRD role
            $hrdRole = \App\Models\Role::firstOrCreate(
                ['name' => 'HRD'],
                ['display_name' => 'Human Resources Department', 'description' => 'HR management access', 'is_active' => true]
            );
            $hrdPermissions = \App\Models\Permission::whereIn('name', [
                'leave.view', 'leave.view_all', 'leave.create', 'leave.edit',
                'leave.delete', 'leave.approve', 'leave.reports', 'leave.manage'
            ])->get();
            $hrdRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
            $output[] = "✅ HRD role granted full leave permissions";

            // Check HR role
            $hrRole = \App\Models\Role::where('name', 'HR')->first();
            if ($hrRole) {
                $hrRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
                $output[] = "✅ HR role granted full leave permissions";
            }

            // Setup Manager role
            $managerRole = \App\Models\Role::firstOrCreate(
                ['name' => 'Manager'],
                ['display_name' => 'Manager', 'description' => 'Department management access', 'is_active' => true]
            );
            $managerPermissions = \App\Models\Permission::whereIn('name', [
                'leave.view', 'leave.approve'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions->pluck('id'));
            $output[] = "✅ Manager role granted leave approval permissions";

            return response('<pre>' . implode("\n", $output) . '</pre>');

        } catch (\Exception $e) {
            return response('<pre>❌ Error: ' . $e->getMessage() . '</pre>');
        }
    });

    // Setup overtime permissions
    Route::get('/setup/overtime-permissions', function() {
        try {
            $permissions = [
                'overtime.view' => 'View overtime requests',
                'overtime.create' => 'Create overtime requests',
                'overtime.edit' => 'Edit overtime requests',
                'overtime.delete' => 'Delete overtime requests',
                'overtime.approve' => 'Approve/reject overtime requests',
                'overtime.reports' => 'View overtime reports',
            ];

            $created = 0;
            foreach ($permissions as $name => $description) {
                $permission = \App\Models\Permission::firstOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
                if ($permission->wasRecentlyCreated) {
                    $created++;
                }
            }

            // Assign permissions to Admin role
            $adminRole = \App\Models\Role::where('name', 'Admin')->first();
            if ($adminRole) {
                $adminRole->permissions()->syncWithoutDetaching(
                    \App\Models\Permission::whereIn('name', array_keys($permissions))->pluck('id')
                );
            }

            // Assign basic permissions to Employee role
            $employeeRole = \App\Models\Role::where('name', 'Employee')->first();
            if ($employeeRole) {
                $basicPermissions = ['overtime.view', 'overtime.create', 'overtime.edit'];
                $employeeRole->permissions()->syncWithoutDetaching(
                    \App\Models\Permission::whereIn('name', $basicPermissions)->pluck('id')
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Setup overtime permissions completed",
                'created_permissions' => $created,
                'total_permissions' => count($permissions),
                'admin_role_updated' => $adminRole ? 'YES' : 'NO',
                'employee_role_updated' => $employeeRole ? 'YES' : 'NO'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Test route for permissions
    Route::get('/test/permissions', function () {
        $output = [];

        try {
            $user = auth()->user();
            if ($user) {
                $output[] = "Current user: {$user->full_name}";
                $output[] = "User roles: " . $user->roles->pluck('name')->implode(', ');

                // Check specific permissions
                $permissions = [
                    'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete',
                    'offices.view', 'shifts.view', 'leave.approve', 'leave.view', 'leave.manage'
                ];

                foreach ($permissions as $permission) {
                    $hasPermission = $user->hasPermission($permission) ? 'YES' : 'NO';
                    $output[] = "Permission '{$permission}': {$hasPermission}";
                }

                // Check roles
                $roles = ['Admin', 'HRD', 'HR', 'Manager', 'karyawan'];
                foreach ($roles as $role) {
                    $hasRole = $user->hasRole($role) ? 'YES' : 'NO';
                    $output[] = "Role '{$role}': {$hasRole}";
                }

                // Check hasAnyRole
                $hasAnyRole = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ? 'YES' : 'NO';
                $output[] = "Has any management role: {$hasAnyRole}";

            } else {
                $output[] = "No authenticated user";
            }
        } catch (\Exception $e) {
            $output[] = "Error: " . $e->getMessage();
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');
    })->name('test.permissions');

    // Test route for leave reject functionality
    Route::get('/test/leave-reject/{leave}', function (\App\Models\LeaveRequest $leave) {
        $output = [];
        $user = auth()->user();

        if (!$user) {
            return response('<pre>No authenticated user</pre>');
        }

        $output[] = "Testing leave reject functionality";
        $output[] = "Leave ID: {$leave->id}";
        $output[] = "Leave Status: {$leave->status}";
        $output[] = "Leave User ID: {$leave->user_id}";
        $output[] = "Current User ID: {$user->id}";
        $output[] = "";

        // Check permissions
        $hasAnyRole = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']);
        $hasPermission = $user->hasPermission('leave.approve');
        $canReject = $hasAnyRole || $hasPermission;

        $output[] = "Has any management role: " . ($hasAnyRole ? 'YES' : 'NO');
        $output[] = "Has leave.approve permission: " . ($hasPermission ? 'YES' : 'NO');
        $output[] = "Can reject: " . ($canReject ? 'YES' : 'NO');
        $output[] = "";

        // Check conditions
        $isOwnRequest = $leave->user_id === $user->id;
        $isPending = $leave->status === 'pending';

        $output[] = "Is own request: " . ($isOwnRequest ? 'YES' : 'NO');
        $output[] = "Is pending: " . ($isPending ? 'YES' : 'NO');
        $output[] = "";

        if ($canReject && !$isOwnRequest && $isPending) {
            $output[] = "✅ User CAN reject this leave request";
        } else {
            $output[] = "❌ User CANNOT reject this leave request";
            if (!$canReject) $output[] = "  - No permission";
            if ($isOwnRequest) $output[] = "  - Cannot reject own request";
            if (!$isPending) $output[] = "  - Request is not pending";
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');
    })->name('test.leave-reject');

    // Route to fix permissions
    Route::get('/fix/permissions', function () {
        try {
            Artisan::call('db:seed', ['--class' => 'FixSchedulePermissionsSeeder']);
            return response('<pre>Permissions fixed successfully!</pre>');
        } catch (\Exception $e) {
            return response('<pre>Error: ' . $e->getMessage() . '</pre>');
        }
    })->name('fix.permissions');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('update-settings');
    });
    
    // Leave Management
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        
        Route::middleware('permission:leaves.create')->group(function () {
            Route::get('/create', [LeaveController::class, 'create'])->name('create');
            Route::post('/', [LeaveController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:leaves.edit')->group(function () {
            Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
            Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        });
        
        Route::middleware('permission:leaves.approve')->group(function () {
            Route::get('/pending', [LeaveController::class, 'pending'])->name('pending');
            Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
        });
        
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
    });
    
    // Payroll Management - Individual routes to avoid grouping issues
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/slip', [PayrollController::class, 'slip'])->name('payroll.slip');
    Route::get('/payroll/slip/{payroll}/download', [PayrollController::class, 'downloadSlip'])->name('payroll.slip.download');
    Route::get('/payroll/reports', [PayrollController::class, 'reports'])->name('payroll.reports');
    Route::get('/payroll/reports/export', [PayrollController::class, 'exportReport'])->name('payroll.reports.export');

    // Payroll Periods
    Route::get('/payroll/periods', [PayrollController::class, 'periods'])->name('payroll.periods.index');
    Route::get('/payroll/periods/create', [PayrollController::class, 'createPeriod'])->name('payroll.periods.create');
    Route::post('/payroll/periods', [PayrollController::class, 'storePeriod'])->name('payroll.periods.store');
    Route::get('/payroll/periods/{period}/calculate', [PayrollController::class, 'calculate'])->name('payroll.periods.calculate');
    Route::post('/payroll/periods/{period}/process', [PayrollController::class, 'process'])->name('payroll.periods.process');

    // Payroll Actions
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('/payroll/period/{period}/approve', [PayrollController::class, 'approvePeriod'])->name('payroll.period.approve');
    Route::post('/payroll/bulk-approve', [PayrollController::class, 'bulkApprove'])->name('payroll.bulk.approve');
    
    // Salary Components - Simplified without middleware for now
    Route::resource('salary-components', SalaryComponentController::class);
    
    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/hr', [ReportController::class, 'hr'])->name('hr');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/leaves', [ReportController::class, 'leavesReport'])->name('leaves')->middleware('permission:reports.leaves');
        
        Route::middleware('permission:reports.financial')->group(function () {
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        });
        
        Route::middleware('permission:reports.export')->group(function () {
            Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        });
    });
    
    // Permit Management (Izin)
    Route::prefix('permits')->name('permits.')->group(function () {
        Route::get('/', [App\Http\Controllers\PermitController::class, 'index'])->name('index');



        // Overtime (Lembur)
        Route::prefix('overtime')->name('overtime.')->group(function () {
            Route::get('/', [App\Http\Controllers\PermitController::class, 'overtimeIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\PermitController::class, 'overtimeCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\PermitController::class, 'overtimeStore'])->name('store');

            // Management routes for HRD/Admin - moved before parameterized routes
            Route::get('/pending', [App\Http\Controllers\PermitController::class, 'overtimePending'])->name('pending');
            Route::get('/management', [App\Http\Controllers\PermitController::class, 'overtimeManagement'])->name('management');
            Route::get('/reports', [App\Http\Controllers\PermitController::class, 'overtimeReports'])->name('reports');
            Route::post('/bulk-approve', [App\Http\Controllers\PermitController::class, 'overtimeBulkApprove'])->name('bulk-approve');

            // Approval routes
            Route::post('/{overtime}/approve', [App\Http\Controllers\PermitController::class, 'overtimeApprove'])->name('approve');
            Route::post('/{overtime}/reject', [App\Http\Controllers\PermitController::class, 'overtimeReject'])->name('reject');

            // Parameterized routes - moved to end to avoid conflicts
            Route::get('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeShow'])->name('show');
            Route::get('/{overtime}/edit', [App\Http\Controllers\PermitController::class, 'overtimeEdit'])->name('edit');
            Route::put('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeUpdate'])->name('update');
            Route::delete('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeDestroy'])->name('destroy');
            Route::get('/{overtime}/slip', [App\Http\Controllers\PermitController::class, 'overtimeSlip'])->name('slip');
        });

        // Leave (Cuti)
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [App\Http\Controllers\PermitController::class, 'leaveIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\PermitController::class, 'leaveCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\PermitController::class, 'leaveStore'])->name('store');

            // Management routes for HRD/Admin - moved before parameterized routes
            Route::get('/pending', [App\Http\Controllers\PermitController::class, 'leavePending'])->name('pending');
            Route::get('/management', [App\Http\Controllers\PermitController::class, 'leaveManagement'])->name('management');
            Route::get('/reports', [App\Http\Controllers\PermitController::class, 'leaveReports'])->name('reports');
            Route::post('/bulk-approve', [App\Http\Controllers\PermitController::class, 'leaveBulkApprove'])->name('bulk-approve');
            Route::post('/{leave}/approve', [App\Http\Controllers\PermitController::class, 'leaveApprove'])->name('approve');
            Route::post('/{leave}/reject', [App\Http\Controllers\PermitController::class, 'leaveReject'])->name('reject');

            Route::get('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveShow'])->name('show');
            Route::get('/{leave}/edit', [App\Http\Controllers\PermitController::class, 'leaveEdit'])->name('edit');
            Route::put('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveUpdate'])->name('update');
            Route::delete('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveDestroy'])->name('destroy');
            Route::get('/{leave}/slip', [App\Http\Controllers\PermitController::class, 'leaveSlip'])->name('slip');
        });
    });

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/attendance-rules', [SettingController::class, 'attendanceRules'])->name('settings.attendance-rules');
        Route::get('/settings/leave-types', [SettingController::class, 'leaveTypes'])->name('settings.leave-types');
    });
});

// API Routes for AJAX calls
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/departments/{department}/positions', function($departmentId) {
        return \App\Models\Position::where('department_id', $departmentId)
                                  ->where('is_active', true)
                                  ->get(['id', 'name', 'base_salary']);
    })->middleware('permission:departments.view');
    
    Route::get('/users/search', function(\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        return \App\Models\User::where('first_name', 'like', "%{$query}%")
                              ->orWhere('last_name', 'like', "%{$query}%")
                              ->orWhere('employee_id', 'like', "%{$query}%")
                              ->with('employee.department', 'employee.position')
                              ->limit(10)
                              ->get();
    });
});

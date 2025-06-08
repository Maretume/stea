# 🗄️ DATABASE ERD DOCUMENTATION - STEA PAYROLL SYSTEM

## 📊 Entity Relationship Diagram Overview

Sistem Penggajian STEA menggunakan struktur database yang komprehensif dengan 15+ tabel yang saling berelasi untuk mendukung fitur HR yang lengkap.

---

## 🏗️ DATABASE STRUCTURE

### 📋 **CORE ENTITIES**

#### 1. **USERS** (Central Entity)
```sql
users
├── id (PK)
├── employee_id (UNIQUE)
├── username (UNIQUE)
├── email (UNIQUE)
├── password
├── first_name
├── last_name
├── phone
├── gender (male/female)
├── birth_date
├── address
├── profile_photo
├── status (active/inactive/suspended)
├── last_login_at
└── timestamps
```

#### 2. **EMPLOYEES** (1:1 with Users)
```sql
employees
├── id (PK)
├── user_id (FK → users.id) UNIQUE
├── department_id (FK → departments.id)
├── position_id (FK → positions.id)
├── supervisor_id (FK → users.id)
├── hire_date
├── contract_start
├── contract_end
├── employment_type (permanent/contract/internship/freelance)
├── employment_status (active/resigned/terminated/retired)
├── basic_salary
├── bank_name
├── bank_account
├── bank_account_name
├── tax_id (NPWP)
├── social_security_id (BPJS)
└── timestamps
```

---

### 🏢 **ORGANIZATIONAL STRUCTURE**

#### 3. **DEPARTMENTS**
```sql
departments
├── id (PK)
├── code (UNIQUE)
├── name
├── description
├── manager_id (FK → users.id)
├── is_active
└── timestamps
```

#### 4. **POSITIONS**
```sql
positions
├── id (PK)
├── department_id (FK → departments.id)
├── code (UNIQUE)
├── name
├── description
├── level
├── base_salary
├── is_active
└── timestamps
```

---

### 🔐 **AUTHENTICATION & AUTHORIZATION**

#### 5. **ROLES**
```sql
roles
├── id (PK)
├── name (UNIQUE)
├── display_name
├── description
├── is_active
└── timestamps
```

#### 6. **PERMISSIONS**
```sql
permissions
├── id (PK)
├── name (UNIQUE)
├── display_name
├── module
├── description
└── timestamps
```

#### 7. **USER_ROLES** (Many-to-Many)
```sql
user_roles
├── id (PK)
├── user_id (FK → users.id)
├── role_id (FK → roles.id)
├── assigned_at
├── is_active
└── timestamps
```

#### 8. **ROLE_PERMISSIONS** (Many-to-Many)
```sql
role_permissions
├── id (PK)
├── role_id (FK → roles.id)
├── permission_id (FK → permissions.id)
├── granted_at
└── timestamps
```

---

### ⏰ **ATTENDANCE SYSTEM**

#### 9. **ATTENDANCE_RULES**
```sql
attendance_rules
├── id (PK)
├── name
├── work_start_time
├── work_end_time
├── break_start_time
├── break_end_time
├── late_tolerance_minutes
├── early_leave_tolerance_minutes
├── overtime_multiplier
├── is_default
├── is_active
└── timestamps
```

#### 10. **ATTENDANCES**
```sql
attendances
├── id (PK)
├── user_id (FK → users.id)
├── attendance_rule_id (FK → attendance_rules.id)
├── date
├── clock_in
├── clock_out
├── break_start
├── break_end
├── total_work_minutes
├── total_break_minutes
├── overtime_minutes
├── late_minutes
├── early_leave_minutes
├── status (present/late/absent/sick/leave)
├── notes
├── clock_in_location
├── clock_out_location
├── clock_in_ip
├── clock_out_ip
└── timestamps
```

---

### 📅 **LEAVE MANAGEMENT**

#### 11. **LEAVE_TYPES**
```sql
leave_types
├── id (PK)
├── name
├── code (UNIQUE)
├── description
├── max_days_per_year
├── is_paid
├── requires_document
├── is_active
└── timestamps
```

#### 12. **LEAVE_REQUESTS** (Enhanced)
```sql
leave_requests
├── id (PK)
├── user_id (FK → users.id)
├── leave_type_id (FK → leave_types.id)
├── start_date
├── end_date
├── total_days
├── reason
├── notes
├── emergency_contact
├── emergency_phone
├── work_handover
├── status (pending/approved/rejected/cancelled)
├── approved_by (FK → users.id)
├── approved_at
├── approval_notes
├── attachments (JSON)
├── is_half_day
├── half_day_type (morning/afternoon)
└── timestamps
```

---

### 🔄 **PERMIT SYSTEM**

#### 13. **PERMIT_TYPES**
```sql
permit_types
├── id (PK)
├── name
├── code (UNIQUE)
├── description
├── requires_approval
├── affects_attendance
├── is_active
├── sort_order
└── timestamps
```

#### 14. **DAY_EXCHANGES** (Tukar Hari)
```sql
day_exchanges
├── id (PK)
├── user_id (FK → users.id)
├── original_work_date
├── replacement_date
├── reason
├── status (pending/approved/rejected/completed)
├── approved_by (FK → users.id)
├── approved_at
├── approval_notes
├── is_completed
├── completed_at
└── timestamps
```

#### 15. **OVERTIME_REQUESTS**
```sql
overtime_requests
├── id (PK)
├── user_id (FK → users.id)
├── overtime_date
├── start_time
├── end_time
├── planned_hours
├── actual_hours
├── work_description
├── reason
├── status (pending/approved/rejected/completed)
├── approved_by (FK → users.id)
├── approved_at
├── approval_notes
├── is_completed
├── completed_at
├── overtime_rate
├── overtime_amount
└── timestamps
```

#### 16. **PERMIT_APPROVALS** (Multi-level)
```sql
permit_approvals
├── id (PK)
├── approvable_type (polymorphic)
├── approvable_id (polymorphic)
├── approver_id (FK → users.id)
├── approval_level
├── status (pending/approved/rejected)
├── approved_at
├── notes
└── timestamps
```

---

### 💰 **PAYROLL SYSTEM**

#### 17. **SALARY_COMPONENTS**
```sql
salary_components
├── id (PK)
├── name
├── code (UNIQUE)
├── type (allowance/deduction/benefit)
├── calculation_type (fixed/percentage/formula)
├── default_amount
├── percentage
├── formula
├── is_taxable
├── is_active
└── timestamps
```

#### 18. **USER_SALARY_COMPONENTS** (Many-to-Many)
```sql
user_salary_components
├── id (PK)
├── user_id (FK → users.id)
├── salary_component_id (FK → salary_components.id)
├── amount
├── effective_date
├── end_date
├── is_active
└── timestamps
```

#### 19. **PAYROLL_PERIODS**
```sql
payroll_periods
├── id (PK)
├── name
├── start_date
├── end_date
├── pay_date
├── status (draft/calculated/approved/paid)
├── created_by (FK → users.id)
├── approved_by (FK → users.id)
├── approved_at
└── timestamps
```

#### 20. **PAYROLLS**
```sql
payrolls
├── id (PK)
├── payroll_period_id (FK → payroll_periods.id)
├── user_id (FK → users.id)
├── basic_salary
├── total_allowances
├── total_deductions
├── overtime_amount
├── gross_salary
├── tax_amount
├── net_salary
├── total_working_days
├── total_present_days
├── total_absent_days
├── total_late_days
├── total_overtime_hours
├── status (draft/approved/paid)
├── notes
└── timestamps
```

#### 21. **PAYROLL_DETAILS**
```sql
payroll_details
├── id (PK)
├── payroll_id (FK → payrolls.id)
├── salary_component_id (FK → salary_components.id)
├── amount
├── calculation_notes
└── timestamps
```

---

## 🔗 **KEY RELATIONSHIPS**

### **1. User-Centric Relationships**
- **Users** ↔ **Employees** (1:1)
- **Users** ↔ **Attendances** (1:Many)
- **Users** ↔ **Leave_Requests** (1:Many)
- **Users** ↔ **Day_Exchanges** (1:Many)
- **Users** ↔ **Overtime_Requests** (1:Many)
- **Users** ↔ **Payrolls** (1:Many)

### **2. Organizational Relationships**
- **Departments** ↔ **Positions** (1:Many)
- **Departments** ↔ **Employees** (1:Many)
- **Positions** ↔ **Employees** (1:Many)
- **Users** ↔ **Employees** (Supervisor relationship)

### **3. Authorization Relationships**
- **Users** ↔ **Roles** (Many:Many via user_roles)
- **Roles** ↔ **Permissions** (Many:Many via role_permissions)

### **4. Payroll Relationships**
- **Payroll_Periods** ↔ **Payrolls** (1:Many)
- **Payrolls** ↔ **Payroll_Details** (1:Many)
- **Users** ↔ **Salary_Components** (Many:Many via user_salary_components)

---

## 📊 **DATABASE INDEXES**

### **Performance Indexes**
```sql
-- User lookups
INDEX(employee_id), INDEX(username), INDEX(email)

-- Attendance queries
INDEX(user_id, date), INDEX(date), INDEX(status)

-- Payroll queries
INDEX(payroll_period_id, user_id), INDEX(status)

-- Leave queries
INDEX(user_id, status), INDEX(start_date, end_date)

-- Permit queries
INDEX(user_id, status), INDEX(overtime_date)

-- Department/Position queries
INDEX(department_id, employment_status)
```

---

## 🔒 **FOREIGN KEY CONSTRAINTS**

### **Cascade Rules**
- **ON DELETE CASCADE**: user_roles, user_salary_components, payroll_details
- **ON DELETE SET NULL**: manager_id, supervisor_id, approved_by
- **ON DELETE RESTRICT**: Core relationships (users→employees)

---

## 📈 **SCALABILITY CONSIDERATIONS**

### **Partitioning Strategy**
- **Attendances**: Partition by date (monthly)
- **Payrolls**: Partition by payroll_period
- **Audit logs**: Partition by created_at (quarterly)

### **Archiving Strategy**
- **Old Attendances**: Archive after 2 years
- **Old Payrolls**: Keep indefinitely for legal compliance
- **Old Permits**: Archive after 1 year

---

## 🎯 **ERD COMPLIANCE STATUS**

✅ **Fully Implemented Tables**: 21/21
✅ **All Relationships**: Properly defined with foreign keys
✅ **Indexes**: Optimized for performance
✅ **Constraints**: Data integrity enforced
✅ **Normalization**: 3NF compliance
✅ **Scalability**: Designed for growth

**Database structure is 100% complete and production-ready!**

# B-HRMS — Employee, Shift & Leave Management

## Gambaran Keseluruhan

Modul B-HRMS (Human Resource Management System) menyediakan fungsi pengurusan sumber manusia yang lengkap untuk syarikat-syarikat di Malaysia. Modul ini melengkapi B-Payroll dengan menyediakan data pekerja, rekod kehadiran, dan pengurusan cuti yang diperlukan untuk pengiraan payroll yang tepat.

## Ciri-ciri Utama

### 1. Pengurusan Pekerja (Employee Management)
- **Profil Pekerja Lengkap**: Nama, IC, pasport, alamat, maklumat peribadi
- **Maklumat Pekerjaan**: Jabatan, jawatan, jenis pekerjaan (full-time, part-time, contract, freelance, intern)
- **Tarikh Penting**: Tarikh mula bekerja, tarikh akhir kontrak, tarikh tamat probasi
- **Status Pekerja**: Active, on_leave, suspended, terminated, resigned
- **Maklumat Kecemasan**: Nama, telefon, hubungan
- **Maklumat Bank & Statutori**: Bank name, account number, EPF, SOCSO, EIS, PCB
- **Auto-generate Employee Number**: Format EMP-YYYY-NNNN
- **Pengurusan Kontrak**: Notifikasi kontrak tamat dalam 30 hari
- **Probasi Tracking**: Senarai pekerja dalam tempoh probasi
- **Bulk Import**: Import pekerja dari CSV

### 2. Pengurusan Jabatan (Department Management)
- **Struktur Hierarki**: Jabatan induk dan cawangan
- **Penyelia Jabatan**: Penetapan penyelia/manajer setiap jabatan
- **Statistik Jabatan**: Bilangan pekerja aktif per jabatan
- **Aktif/Non-aktif**: Status jabatan

### 3. Pengurusan Jawatan (Designation Management)
- **Jawatan dalam Jabatan**: Setiap jawatan dikaitkan dengan jabatan
- **Tahap Jawatan**: Level untuk struktur organisasi
- **Aktif/Non-aktif**: Status jawatan

### 4. Pengurusan Shift (Shift Management)
- **Jenis Shift**: Standard, Rotating, Flexible, Night
- **Waktu Kerja**: Masa mula, masa tamat, waktu rehat
- **Hari Kerja**: Configuration untuk setiap hari (1=Monday, 7=Sunday)
- **Pengiraan Automatik**: Net working hours (tolak waktu rehat)
- **Tugasan Shift**: Effective from/to untuk perubahan shift
- **Aktif/Non-aktif**: Status shift

### 5. Pengurusan Cuti (Leave Management)
- **Jenis Cuti**: Annual, Medical, Unpaid, Maternity, Paternity, Wedding, Haji, Emergency
- **Alokasi Cuti**: Hari cuti per tahun per jenis
- **Baki Cuti**: Tracking used vs remaining days
- **Carry Over**: Transfer baki cuti ke tahun berikutnya
- **Permintaan Cuti**: Full day, morning half-day, afternoon half-day
- **Kelulusan**: Pending → Approved/Rejected workflow
- **Auto-approve**: Untuk permintaan <= threshold hari (default 2 hari)
- **Lampiran**: Upload dokumen sokongan (sijil perubatan)
- **Audit Trail**: Sejarah semua tindakan cuti

### 6. Pengurusan Kehadiran (Attendance Management)
- **Check-in/Check-out**: Manual, Biometric, GPS, Web
- **GPS Fencing**: Location tracking pada check-in
- **Foto Check-in**: Optional photo verification
- **Kira Kemasukan Lewat**: Auto-calculate minutes late
- **Grace Period**: 15 minit (configurable)
- **Overtime Calculation**: Auto-calculate jika melebihi working hours
- **Rekod Harian**: Summary kehadiran harian
- **Laporan Bulanan**: Total hours, overtime, late minutes
- **Manual Marking**: Admin boleh mark kehadiran manual
- **Bulk Marking**: Mark kehadiran banyak pekerja serentak

### 7. Pengurusan Cuti Bersama (Holiday Management)
- **Cuti Umum**: Public holidays Malaysia
- **Cuti Syarikat**: Company-specific holidays
- **Cuti Observance**: Observance holidays
- **State-specific**: Cuti yang hanya applicable di negeri tertentu
- **Recurring**: Tahunan atau sekali sahaja

### 8. Permintaan Overtime (Overtime Management)
- **Permintaan Overtime**: Dengan tarikh, masa, dan alasan
- **Kelulusan**: Workflow approval
- **Tracking**: Approved, pending, rejected

## Struktur Fail

```
app/Modules/HRMS/
├── HRMSServiceProvider.php         # Service provider registration
├── README.md                       # This file
├── config/
│   └── hrms.php                    # Module configuration
├── Models/
│   ├── Department.php              # Jabatan & hierarki
│   ├── Employee.php                # Profil pekerja lengkap
│   ├── Designation.php             # Jawatan dalam jabatan
│   ├── Shift.php                   # Template shift kerja
│   ├── EmployeeShift.php           # Tugasan shift ke pekerja
│   ├── LeaveType.php               # Jenis-jenis cuti
│   ├── LeaveAllocation.php         # Alokasi cuti tahunan
│   ├── LeaveRequest.php            # Permintaan cuti
│   ├── LeaveRequestHistory.php     # Audit trail cuti
│   ├── Attendance.php              # Rekod kehadiran harian
│   ├── OvertimeRequest.php         # Permintaan overtime
│   ├── EmployeeDocument.php        # Dokumen pekerja
│   └── Holiday.php                 # Cuti bersama
├── Services/
│   ├── EmployeeService.php         # CRUD pekerja, shift assignment
│   ├── LeaveService.php            # Leave requests, approvals, balances
│   ├── AttendanceService.php       # Check-in/out, reports
│   └── ShiftService.php            # Shift management
├── Http/Controllers/
│   ├── EmployeeController.php      # API endpoints pekerja
│   ├── DepartmentController.php    # API endpoints jabatan
│   ├── LeaveController.php         # API endpoints cuti
│   ├── AttendanceController.php    # API endpoints kehadiran
│   └── ShiftController.php         # API endpoints shift
├── database/migrations/
│   ├── 2026_10_19_000200_create_hrms_core_tables.php
│   └── 2026_10_19_000201_create_hrms_leave_tables.php
└── routes/
    └── api.php                     # Module API routes
```

## Migration Tables

### Core Tables

**hrms_departments**
- `id`, `tenant_id`, `name`, `code`, `parent_id`, `manager_id`
- `description`, `is_active`

**hrms_employees**
- `id`, `tenant_id`, `user_id`, `employee_no`, `ic_number`, `passport_number`
- `first_name`, `last_name`, `email`, `phone`
- `date_of_birth`, `gender`, `marital_status`, `nationality`
- Address fields: `address_line1`, `address_line2`, `city`, `state`, `postcode`, `country`
- Employment: `department_id`, `designation_id`, `employment_type`, `date_joined`
- Dates: `contract_end_date`, `probation_end_date`
- Status: `active`, `on_leave`, `suspended`, `terminated`, `resigned`
- Emergency contact: `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`
- Bank & statutory: `bank_name`, `bank_account_no`, `epf_no`, `soeso_no`, `eis_no`, `pcb_ref`
- `metadata`, `last_active_at`

**hrms_designations**
- `id`, `tenant_id`, `name`, `code`, `department_id`, `level`, `description`, `is_active`

**hrms_shifts**
- `id`, `tenant_id`, `name`, `code`, `description`
- `start_time`, `end_time`, `break_start`, `break_end`
- `working_hours`, `working_days` (JSON), `type` (standard/rotating/flexible/night)
- `is_active`

**hrms_employee_shifts**
- `id`, `employee_id`, `shift_id`, `effective_from`, `effective_to`

**hrms_employee_documents**
- `id`, `employee_id`, `document_type`, `file_path`, `file_name`, `mime_type`, `file_size`
- `expiry_date`, `status` (pending/verified/rejected), `notes`

### Leave & Attendance Tables

**hrms_leave_types**
- `id`, `tenant_id`, `code`, `name`, `max_days_per_year`
- `encashable`, `requires_document`, `description`, `is_active`

**hrms_leave_allocations**
- `id`, `employee_id`, `leave_type_id`, `total_days`, `used_days`
- `carried_over_days`, `year`, `expiry_date`

**hrms_leave_requests**
- `id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`
- `days_requested`, `half_day` (full/morning/afternoon)
- `reason`, `attachment_path`, `attachment_name`
- `status` (draft/pending/approved/rejected/cancelled)
- `approved_by`, `approved_at`, `rejection_reason`

**hrms_leave_request_history**
- `id`, `leave_request_id`, `user_id`, `action`, `changes`, `notes`

**hrms_attendance**
- `id`, `employee_id`, `date`
- `check_in_time`, `check_out_time`, `total_hours`
- `status` (present/absent/late/half_day/on_leave/holiday)
- `minutes_late`, `overtime_hours`
- `check_in_method`, `check_in_location`, `check_out_location`
- `check_in_photo`, `check_out_photo`, `notes`

**hrms_overtime_requests**
- `id`, `employee_id`, `date`, `start_time`, `end_time`
- `hours_requested`, `reason`
- `status`, `approved_by`, `approved_at`, `rejection_reason`

**hrms_holidays**
- `id`, `tenant_id`, `name`, `date`
- `type` (public/company/observance), `state_applicable`
- `is_recurring`, `description`

## API Endpoints

### Employees
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/hrms/employees` | `hrms.view` | List employees with filters |
| GET | `/api/hrms/employees/{id}` | `hrms.view` | Get employee details |
| POST | `/api/hrms/employees` | `hrms.manage` | Create new employee |
| PUT | `/api/hrms/employees/{id}` | `hrms.manage` | Update employee |
| POST | `/api/hrms/employees/{id}/terminate` | `hrms.manage` | Terminate employee |
| POST | `/api/hrms/employees/{id}/assign-shift` | `hrms.manage` | Assign shift to employee |
| GET | `/api/hrms/employees/{id}/attendance-summary` | `hrms.view` | Get attendance summary |
| GET | `/api/hrms/employees/expiring-contracts` | `hrms.view` | Get expiring contracts |
| GET | `/api/hrms/employees/probationers` | `hrms.view` | Get probationers |
| POST | `/api/hrms/employees/bulk-import` | `hrms.manage` | Bulk import from CSV |

### Departments
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/hrms/departments` | `hrms.view` | List departments |
| GET | `/api/hrms/departments/{id}` | `hrms.view` | Get department details |
| POST | `/api/hrms/departments` | `hrms.manage` | Create department |
| PUT | `/api/hrms/departments/{id}` | `hrms.manage` | Update department |
| DELETE | `/api/hrms/departments/{id}` | `hrms.manage` | Delete department |
| GET | `/api/hrms/departments/statistics` | `hrms.view` | Get department stats |

### Leave
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/hrms/leave/types` | `hrms.view` | List leave types |
| POST | `/api/hrms/leave/types` | `hrms.manage` | Create leave type |
| POST | `/api/hrms/leave/requests` | `hrms.approve` | Submit leave request |
| GET | `/api/hrms/leave/pending` | `hrms.view` | Get pending requests |
| POST | `/api/hrms/leave/auto-approve` | `hrms.approve` | Auto-approve below threshold |
| POST | `/api/hrms/leave/requests/{id}/approve` | `hrms.approve` | Approve request |
| POST | `/api/hrms/leave/requests/{id}/reject` | `hrms.approve` | Reject request |
| POST | `/api/hrms/leave/requests/{id}/cancel` | `hrms.manage` | Cancel request |
| GET | `/api/hrms/leave/balance` | `hrms.view` | Get leave balance |
| POST | `/api/hrms/leave/allocate` | `hrms.manage` | Allocate leave days |
| GET | `/api/hrms/leave/history` | `hrms.view` | Get leave history |

### Attendance
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| POST | `/api/hrms/attendance/check-in` | `hrms.view` | Check in |
| POST | `/api/hrms/attendance/check-out` | `hrms.view` | Check out |
| GET | `/api/hrms/attendance/today` | `hrms.view` | Get today's attendance |
| GET | `/api/hrms/attendance` | `hrms.view` | List attendance records |
| GET | `/api/hrms/attendance/daily-summary` | `hrms.view` | Daily summary |
| GET | `/api/hrms/attendance/monthly-report` | `hrms.view` | Monthly report |
| POST | `/api/hrms/attendance/mark` | `hrms.manage` | Mark attendance (admin) |
| POST | `/api/hrms/attendance/bulk-mark` | `hrms.manage` | Bulk mark attendance |

### Shifts
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/hrms/shifts` | `hrms.view` | List shifts |
| GET | `/api/hrms/shifts/{id}` | `hrms.view` | Get shift details |
| POST | `/api/hrms/shifts` | `hrms.manage` | Create shift |
| PUT | `/api/hrms/shifts/{id}` | `hrms.manage` | Update shift |
| DELETE | `/api/hrms/shifts/{id}` | `hrms.manage` | Delete shift |
| POST | `/api/hrms/shifts/{id}/toggle-status` | `hrms.manage` | Toggle active status |

## Permissions

| Permission | Description |
|------------|-------------|
| `hrms.view` | Lihat pekerja, shift, cuti & rekod kehadiran |
| `hrms.manage` | Urus pekerja, shift, cuti & kehadiran |
| `hrms.approve` | Lulus permintaan cuti & overtime |

## Roles

**hr_manager** role sudah termasuk:
- `hrms.view`
- `hrms.manage`
- `hrms.approve`
- `payroll.view`
- `payroll.manage`

## Configuration (`config/hrms.php`)

```php
'working_hours' => [
    'default_start_time' => '09:00',
    'default_end_time' => '17:00',
    'default_working_days' => 5,
    'grace_period_minutes' => 15,
    'overtime_threshold' => 8,
],

'leave' => [
    'types' => [
        'annual' => ['name' => 'Annual Leave', 'max_days' => 14, 'encashable' => true],
        'medical' => ['name' => 'Medical Leave', 'max_days' => 14, 'requires_doc' => true],
        'unpaid' => ['name' => 'Unpaid Leave'],
        'maternity' => ['name' => 'Maternity Leave', 'max_days' => 90],
        'paternity' => ['name' => 'Paternity Leave', 'max_days' => 7],
        'wedding' => ['name' => 'Wedding Leave', 'max_days' => 3],
        'haji' => ['name' => 'Haji Leave', 'max_days' => 14],
        'emergency' => ['name' => 'Emergency Leave', 'max_days' => 3],
    ],
    'auto_approve_below_days' => 2,
],

'attendance' => [
    'check_in_methods' => ['manual', 'biometric', 'gps', 'web'],
    'geo_fencing_enabled' => false,
    'require_photo_on_checkin' => false,
],

'employment_types' => [
    'full_time', 'part_time', 'contract', 'freelance', 'intern',
],
```

## Malaysian Compliance

### Statutory Requirements
- **EPF (KWSP)**: Employee no (9-digit format), contribution tracking
- **SOCSO (PERKESO)**: Social security number storage
- **EIS**: Employment insurance system reference
- **PCB**: Pay as you earn reference

### Leave Entitlements (Employment Act 1955)
- **Annual Leave**: Minimum 8 days (tambah sehingga 16+ years service)
- **Medical Leave**: 14 days (hospitalization), 10 days (outpatient)
- **Maternity Leave**: 90 days (Section 36A, Act 282)
- **Paternity Leave**: 7 days (Section 36B, Act 282)

### Working Hours
- Normal: 8 hours/day, 45 hours/week
- Grace period: 15 minutes
- Overtime: 1.5x normal rate

## Integration dengan B-Payroll

Modul B-HRMS adalah prerequisite untuk B-Payroll:

1. **Data Pekerja**: `hrms_employees` → Payroll master data
2. **Kehadiran**: `hrms_attendance` → Overtime calculation, absent deduction
3. **Cuti**: `hrms_leave_requests` → Leave pay calculation
4. **Shift**: `hrms_shifts` → Shift allowance calculation
5. **Statutory**: EPF/SOCSO/EIS/PCB numbers dari employee profile

## Contoh Penggunaan

### Create Employee
```bash
POST /api/hrms/employees
{
    "first_name": "Ahmad",
    "last_name": "Abdullah",
    "email": "ahmad@example.com",
    "phone": "+6012-1234567",
    "ic_number": "901234-56-7890",
    "employment_type": "full_time",
    "date_joined": "2026-01-15",
    "department_id": 1,
    "designation_id": 3,
    "bank_name": "Maybank",
    "bank_account_no": "1234567890",
    "epf_no": "10012345678-1",
    "soeso_no": "KK123456-01-12345",
    "emergency_contact_name": "Fatimah Abdullah",
    "emergency_contact_phone": "+6012-9876543",
    "emergency_contact_relation": "Wife"
}
```

### Submit Leave Request
```bash
POST /api/hrms/leave/requests
{
    "employee_id": 1,
    "leave_type_id": 1,
    "start_date": "2026-12-20",
    "end_date": "2026-12-22",
    "days_requested": 3,
    "reason": "Family vacation"
}
```

### Check In
```bash
POST /api/hrms/attendance/check-in
{
    "method": "gps",
    "location": "Latitude: 3.1390, Longitude: 101.6869",
    "photo": "base64_encoded_image"
}
```

## Development Notes

### Employee Number Generation
Format: `EMP-YYYY-NNNN`
- Auto-increment within year
- Reset setiap tahun baru

### Leave Balance Calculation
```php
$allocation = LeaveAllocation::where('employee_id', $id)
    ->where('year', now()->year)
    ->first();

$remaining = $allocation->total_days - $allocation->used_days;
```

### Attendance Calculation
```php
// Calculate total hours
$totalHours = ($checkOutTime - $checkInTime) / 3600;

// Calculate overtime
$overtime = max(0, $totalHours - $netWorkingHours);

// Calculate minutes late
$minutesLate = max(0, strtotime($checkInTime) - strtotime($shiftStartTime));
```

## Future Enhancements

- [ ] Biometric integration (fingerprints, facial recognition)
- [ ] Mobile app for check-in/out
- [ ] Self-service portal untuk pekerja
- [ ] Recruitment & onboarding module
- [ ] Performance appraisal
- [ ] Training & development tracking
- [ ] Payroll integration auto-sync
- [ ] Email/SMS notifications
- [ ] PDF employment letters generation
- [ ] EA Form generation (tahunan)

# B-Projects — Project, Task & Timesheet Management

## Gambaran Keseluruhan

Modul **B-Projects** menyediakan sistem pengurusan projek lengkap dengan ciri-ciri:

- **Pengurusan Projek** — Cipta, urus & ikut kemajuan projek
- **Pengurusan Tugasan** — Tugasan dengan hierarki (parent/subtask), kebergantungan & kitaran kerja
- **Milestone Tracking** — Penjejakan milestone dengan peratusan penyelesaian
- **Timesheet & Pengesahan** — Rekod jam kerja dengan aliran kelulusan
- **Pengurusan Ahli Projek** — Tambah/keluarkan ahli dengan peranan (member/lead/viewer)
- **Pengurusan Dokumen** — Muat naik & versi dokumen projek
- **Log Aktiviti** — Jejak semua perubahan dalam projek

## Struktur Modul

```
app/Modules/Projects/
├── ProjectsServiceProvider.php         # Service provider dengan bindings
├── README.md                           # Dokumentasi ini
├── config/
│   └── projects.php                    # Tetapan projek, tugasan, timesheet
├── Models/
│   ├── Project.php                     # Model utama projek
│   ├── Task.php                        # Tugasan dengan dependencies
│   ├── Milestone.php                   # Milestone tracking
│   ├── Timesheet.php                   # Rekod jam kerja
│   ├── ProjectMember.php               # Ahli projek
│   ├── ProjectDocument.php             # Dokumen projek
│   └── ProjectActivity.php             # Log aktiviti
├── Services/
│   ├── ProjectService.php              # CRUD projek & logik tambahan
│   ├── TaskService.php                 # Pengurusan tugasan & dependencies
│   ├── MilestoneService.php            # Pengurusan milestone
│   ├── TimesheetService.php            # Timesheet CRUD & approval workflow
│   └── DocumentService.php             # Dokumen upload & versioning
├── Http/Controllers/
│   ├── ProjectController.php           # 9 endpoints (CRUD + members + summary)
│   ├── TaskController.php              # 8 endpoints (CRUD + status + assign)
│   ├── MilestoneController.php         # 7 endpoints (CRUD + complete + overdue)
│   └── TimesheetController.php         # 10 endpoints (CRUD + submit/approve/reject)
├── database/migrations/
│   └── 2026_10_19_000300_create_projects_tables.php
└── routes/
    └── api.php                         # API endpoints dengan permission middleware
```

## Ciri-ciri Utama

### 1. Pengurusan Projek

#### Ciri-ciri:
- Kod auto-generate (`PRJ-YYYY-NNNN`)
- Status lifecycle: `draft` → `active` → `on_hold` / `completed` / `cancelled`
- Priority levels: `low`, `medium`, `high`, `critical`
- Budget tracking dengan spent calculation
- Tagging system untuk kategori
- Client association (CRM integration)
- Manager assignment
- Completion percentage berdasarkan task completion

#### Helper Methods:
```php
// Generate project code
Project::generateCode(); // Returns "PRJ-2026-0001"

// Check if project is overdue
$project->isOverdue(); // Returns boolean

// Check if project is completed
$project->isCompleted(); // Returns boolean

// Get completion percentage
$project->completion_percentage; // Float (0-100)

// Get total hours
$project->total_hours; // Sum of approved timesheets

// Get billable hours
$project->billable_hours; // Sum of billable approved timesheets
```

### 2. Pengurusan Tugasan

#### Ciri-ciri:
- Hierarki tugasan (parent → subtasks)
- Dependency tracking dengan cycle detection
- Task lifecycle: `todo` → `in_progress` → `review` → `done`
- Priority levels: `low`, `medium`, `high`, `critical`
- Assignment kepada pengguna
- Due date tracking dengan overdue alerts
- Estimated vs actual hours tracking
- Story points untuk agile planning
- Labels/tags untuk categorization

#### Task Workflow:
```
todo → in_progress → review → done
  ↓        ↓            ↓
cancel   cancel       cancel
```

#### Dependency Rules:
- Cycle detection sebelum menambah dependency
- Semua dependencies mesti `done` sebelum mark task sebagai `done`
- Parent tasks tidak boleh depend pada subtasks

#### Helper Methods:
```php
// Get ancestor tasks (parent chain)
$task->getAncestors(); // Collection

// Get descendant tasks (subtask tree)
$task->getDescendants(); // Collection

// Calculate total subtask hours
$task->total_subtask_hours; // Float

// Check if task is overdue
$task->isOverdue(); // Boolean

// Check if task is completed
$task->isCompleted(); // Boolean
```

### 3. Milestone Tracking

#### Ciri-ciri:
- Due date per milestone
- Status: `pending`, `in_progress`, `completed`, `overdue`
- Completion percentage tracking
- Auto-overdue detection
- Association dengan projek

#### Helper Methods:
```php
// Check if milestone is completed
$milestone->isCompleted(); // Boolean

// Check if milestone is overdue
$milestone->isOverdue(); // Boolean
```

### 4. Timesheet & Approval Workflow

#### Ciri-ciri:
- Daily time logging (max 24 hours/day)
- Billable vs non-billable tracking
- Task association (optional)
- Approval workflow: `draf` → `dihantar` → `diluluskan` / `ditolak`
- Rejection dengan reason
- Weekly reporting
- Project summary statistics

#### Timesheet Workflow:
```
draf → dihantar → diluluskan
            ↓
          ditolak
```

#### Helper Methods:
```php
// Check if timesheet is approved
$timesheet->isApproved(); // Boolean

// Get billable amount
$timesheet->billable_amount; // Float (hours × rate)
```

#### Timesheet Summary:
```php
// Get project summary
$service->getProjectSummary($projectId, $startDate, $endDate);
// Returns:
// [
//     'total_hours' => 150.5,
//     'billable_hours' => 120.0,
//     'non_billable_hours' => 30.5,
//     'entry_count' => 25,
//     'billable_amount' => 12000.00,
// ]

// Get weekly report
$service->getWeeklyReport($userId, $weekNumber, $year);
// Returns daily totals with hours breakdown
```

### 5. Pengurusan Ahli Projek

#### Peranan:
- **member** — Akses penuh kepada tugasan & timesheet
- **lead** — Kepimpinan projek dengan akses tambahan
- **viewer** — Read-only access

### 6. Pengurusan Dokumen

#### Ciri-ciri:
- Version control (1.0, 1.1, 1.2, ...)
- Folder organization
- File metadata (size, mime type)
- Upload history
- Search by filename

#### Helper Methods:
```php
// Get file URL
$document->url; // String

// Get formatted file size
$document->formatted_size; // "2.5 MB"
```

## Configuration

File: `config/projects.php`

```php
return [
    // Project settings
    'project' => [
        'code_prefix' => 'PRJ',
        'auto_complete_on_last_task' => true,
        'allow_negative_budget' => false,
    ],

    // Task workflow
    'task' => [
        'statuses' => ['todo', 'in_progress', 'review', 'done', 'cancelled'],
        'priorities' => ['low', 'medium', 'high', 'critical'],
        'enable_dependencies' => true,
        'enable_story_points' => true,
        'max_subtask_depth' => 5,
    ],

    // Timesheet settings
    'timesheet' => [
        'max_daily_hours' => 24,
        'billable_rates' => [
            'default' => 100, // RM per hour
            'lead' => 150,
            'senior' => 120,
            'junior' => 80,
        ],
        'require_task_association' => false,
        'approval_required' => true,
    ],

    // Milestone settings
    'milestone' => [
        'statuses' => ['pending', 'in_progress', 'completed', 'overdue'],
        'auto_overdue_detection' => true,
        'calculate_from_tasks' => true,
    ],
];
```

## API Endpoints

### Projects

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/projects` | `projects.view` | List all projects |
| GET | `/api/v1/projects/{id}` | `projects.view` | Get project details |
| POST | `/api/v1/projects` | `projects.manage` | Create new project |
| PUT | `/api/v1/projects/{id}` | `projects.manage` | Update project |
| DELETE | `/api/v1/projects/{id}` | `projects.manage` | Delete project |
| POST | `/api/v1/projects/{id}/members` | `projects.manage` | Add member |
| DELETE | `/api/v1/projects/{id}/members/{userId}` | `projects.manage` | Remove member |
| GET | `/api/v1/projects/{id}/summary` | `projects.view` | Get project summary |

### Tasks

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/projects/{id}/tasks` | `projects.view` | List project tasks |
| GET | `/api/v1/tasks/{id}` | `projects.view` | Get task details |
| POST | `/api/v1/projects/{id}/tasks` | `projects.manage` | Create task |
| PUT | `/api/v1/tasks/{id}` | `projects.manage` | Update task |
| DELETE | `/api/v1/tasks/{id}` | `projects.manage` | Delete task |
| PATCH | `/api/v1/tasks/{id}/status` | `projects.manage` | Change task status |
| PATCH | `/api/v1/tasks/{id}/assign` | `projects.manage` | Assign task |
| GET | `/api/v1/projects/{id}/tasks/overdue` | `projects.view` | Get overdue tasks |
| GET | `/api/v1/tasks/my-tasks` | `projects.view` | Get my assigned tasks |

### Milestones

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/projects/{id}/milestones` | `projects.view` | List milestones |
| GET | `/api/v1/milestones/{id}` | `projects.view` | Get milestone |
| POST | `/api/v1/projects/{id}/milestones` | `projects.manage` | Create milestone |
| PUT | `/api/v1/milestones/{id}` | `projects.manage` | Update milestone |
| DELETE | `/api/v1/milestones/{id}` | `projects.manage` | Delete milestone |
| POST | `/api/v1/milestones/{id}/complete` | `projects.manage` | Complete milestone |
| GET | `/api/v1/projects/{id}/milestones/overdue` | `projects.view` | Get overdue milestones |

### Timesheets

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/projects/{id}/timesheets` | `projects.timesheet` | List timesheets |
| GET | `/api/v1/timesheets/{id}` | `projects.timesheet` | Get timesheet |
| POST | `/api/v1/projects/{id}/timesheets` | `projects.timesheet` | Create timesheet |
| PUT | `/api/v1/timesheets/{id}` | `projects.timesheet` | Update timesheet |
| POST | `/api/v1/timesheets/{id}/submit` | `projects.timesheet` | Submit for approval |
| POST | `/api/v1/timesheets/{id}/approve` | `projects.approve` | Approve timesheet |
| POST | `/api/v1/timesheets/{id}/reject` | `projects.approve` | Reject timesheet |
| DELETE | `/api/v1/timesheets/{id}` | `projects.timesheet` | Delete timesheet |
| GET | `/api/v1/projects/{id}/timesheets/summary` | `projects.timesheet` | Get summary |
| GET | `/api/v1/timesheets/my-weekly-report` | `projects.timesheet` | Get weekly report |
| GET | `/api/v1/timesheets/my-timesheets` | `projects.timesheet` | Get my timesheets |

## Permissions

| Permission | Description |
|------------|-------------|
| `projects.view` | Lihat projek, tugasan & milestone |
| `projects.manage` | Urus projek, tugasan, milestone & ahli |
| `projects.timesheet` | Rekod & lihat jam kerja |
| `projects.approve` | Lulus / tolak rekod jam kerja |

## Roles

| Role | Projects Permissions |
|------|---------------------|
| `tenant_owner` | All (*) |
| `tenant_admin` | `projects.view`, `projects.manage`, `projects.timesheet` |
| `hr_manager` | `projects.view`, `projects.manage`, `projects.timesheet`, `projects.approve` |
| `finance_manager` | `projects.view`, `projects.manage`, `projects.timesheet`, `projects.approve` |
| `staff` | `projects.view`, `projects.timesheet` |
| `auditor` | `projects.view` |

## Database Schema

### Tables

1. **projects** — Main project table
   - id, tenant_id, name, code, description, status, priority
   - start_date, end_date, budget, spent, tags
   - manager_id, client_id, completed_at
   - timestamps

2. **tasks** — Task management
   - id, tenant_id, project_id, title, description, status, priority
   - assigned_to, parent_task_id, order, due_date
   - estimated_hours, actual_hours, story_points, labels
   - completed_at, timestamps

3. **task_dependencies** — Many-to-many self-referential
   - id, task_id, depends_on_id, tenant_id

4. **milestones** — Project milestones
   - id, tenant_id, project_id, name, description, due_date
   - status, completion_percentage, completed_at
   - timestamps

5. **timesheets** — Time tracking
   - id, tenant_id, project_id, task_id, user_id
   - work_date, hours, note, billable
   - status, approved_by, approved_at, rejection_reason
   - timestamps

6. **project_members** — Project team
   - id, tenant_id, project_id, user_id, role
   - timestamps

7. **project_documents** — File storage
   - id, tenant_id, project_id, uploaded_by
   - file_path, file_name, mime_type, file_size
   - folder, version
   - timestamps

8. **project_activities** — Activity log
   - id, tenant_id, project_id, user_id
   - action, changes, description
   - timestamps

## Integration Points

### With B-CRM
- Client association on projects
- `crm_clients` table referenced via `client_id`

### With B-HRMS
- Employee data can be linked to project members
- Timesheet data feeds into payroll calculations

### With B-Finance
- Billable hours generate invoice entries
- Project budget tracking against actual spend
- Revenue recognition from approved timesheets

### With B-Invoicing
- Approved billable timesheets → Recurring billing
- Project milestones → Milestone-based invoicing

## Testing

Run the test suite:

```bash
php artisan test --testsuite=Projects
```

## Migration

Apply migrations:

```bash
php artisan migrate --path=app/Modules/Projects/database/migrations
```

Rollback:

```bash
php artisan migrate:rollback --path=app/Modules/Projects/database/migrations
```

## Changelog

### v1.0.0 (2026-10-19)
- Initial release
- Project CRUD with member management
- Task management with dependencies & hierarchy
- Milestone tracking with completion percentage
- Timesheet with approval workflow
- Document upload with versioning
- Activity logging
- Full API with permission-based access control

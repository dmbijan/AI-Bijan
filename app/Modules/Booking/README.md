# B-Booking — Service Booking & Appointments Module

## Overview

AI BIJAN Booking module for service-based businesses (salons, clinics, spas, consultancies). Features service catalog, time slot management, appointment scheduling, reminders, and status tracking.

**Priority:** P1  
**Status:** ✅ Complete  
**Blueprint Reference:** BAB 12, Kumpulan 2 (Jualan, Saluran & Pertumbuhan)

---

## Architecture

```
app/Modules/Booking/
├── BookingServiceProvider.php          # Service provider registration
├── config/
│   └── booking.php                     # Configuration (services, slots, reminders)
├── Models/
│   ├── Service.php                     # Service catalog entity
│   ├── ServiceProvider.php             # Staff/providers offering services
│   ├── Appointment.php                 # Booked appointments
│   ├── BookingSlot.php                 # Time slots
│   └── BookingReminder.php             # Reminder notifications
├── Services/
│   ├── ServiceCatalogService.php       # Service CRUD operations
│   ├── SlotService.php                 # Slot generation & management
│   └── AppointmentService.php          # Appointment lifecycle
├── Http/Controllers/
│   ├── ServiceController.php           # Service API endpoints
│   ├── AppointmentController.php       # Appointment API endpoints
│   └── SlotController.php              # Slot API endpoints
├── database/migrations/
│   └── 2026_10_19_000600_create_booking_tables.php
└── routes/
    └── api.php                         # Module-specific routes
```

---

## Database Schema

### Tables

#### `booking_services`
Service catalog items available for booking.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| name | VARCHAR(255) | Service name |
| slug | VARCHAR(255) | Unique URL-friendly identifier |
| category | VARCHAR(100) | Category (haircut, facial, massage, etc.) |
| description | TEXT | Service description |
| duration_minutes | INT | Standard duration |
| price | DECIMAL(15,2) | Service price |
| cost | DECIMAL(15,2) | Internal cost |
| provider_id | BIGINT UNSIGNED | Default provider FK → booking_service_providers |
| is_active | BOOLEAN | Active/inactive status |
| image_url | VARCHAR(500) | Service image |
| max_capacity | INT | Max concurrent customers |
| requires_prep | BOOLEAN | Requires preparation time |
| prep_time_minutes | INT | Preparation time before service |
| cancellation_policy | TEXT | Cancellation terms |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `booking_service_providers`
Staff or providers who deliver services.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| user_id | BIGINT UNSIGNED | Linked user account FK → users |
| name | VARCHAR(255) | Provider display name |
| slug | VARCHAR(255) | Unique identifier |
| specialization | VARCHAR(255) | Area of expertise |
| bio | TEXT | Provider biography |
| phone | VARCHAR(50) | Contact phone |
| email | VARCHAR(255) | Contact email |
| avatar_url | VARCHAR(500) | Profile photo |
| is_active | BOOLEAN | Active/inactive |
| max_concurrent_appointments | INT | Max simultaneous bookings |
| break_time_minutes | INT | Break between appointments |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `booking_slots`
Time slots for booking availability.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| service_id | BIGINT UNSIGNED | FK → booking_services |
| provider_id | BIGINT UNSIGNED | FK → booking_service_providers |
| slot_date | DATE | Date of slot |
| start_time | TIME | Start time |
| end_time | TIME | End time |
| status | ENUM | available, booked, blocked, maintenance |
| capacity | INT | Max bookings for this slot |
| booked_count | INT | Current number of bookings |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `bookings_appointments`
Booked appointments.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| appointment_no | VARCHAR(50) | Unique number (BK-YYYY-NNNNNN) |
| service_id | BIGINT UNSIGNED | FK → booking_services |
| provider_id | BIGINT UNSIGNED | FK → booking_service_providers |
| slot_id | BIGINT UNSIGNED | FK → booking_slots |
| customer_id | BIGINT UNSIGNED | FK → crm_clients |
| user_id | BIGINT UNSIGNED | Creator/operator FK → users |
| status | ENUM | draft, pending, confirmed, in_progress, completed, cancelled, no_show, rescheduled |
| start_time | DATETIME | Scheduled start |
| end_time | DATETIME | Scheduled end |
| actual_start_time | DATETIME | Actual start |
| actual_end_time | DATETIME | Actual end |
| notes | TEXT | Customer notes |
| internal_notes | TEXT | Staff-only notes |
| reminder_sent | BOOLEAN | Reminder notification sent |
| reminder_sent_at | DATETIME | When reminder sent |
| cancelled_at | DATETIME | Cancellation timestamp |
| cancelled_by | BIGINT UNSIGNED | Who cancelled FK → users |
| cancellation_reason | TEXT | Reason for cancellation |
| rescheduled_from | INT | Original appointment ID if rescheduled |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

#### `booking_reminders`
Reminder notification queue.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| appointment_id | BIGINT UNSIGNED | FK → bookings_appointments |
| channel | ENUM | sms, whatsapp, email, push |
| status | ENUM | pending, sent, failed |
| scheduled_at | DATETIME | When to send |
| sent_at | DATETIME | Actual send time |
| template | VARCHAR(100) | Template used |
| payload | JSON | Notification data |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

---

## API Endpoints

Base URL: `/api/v1/booking/`

### Services

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/services` | `booking.view` | List all services |
| GET | `/services/{id}` | `booking.view` | Show service details |
| POST | `/services` | `booking.manage` | Create new service |
| PUT | `/services/{id}` | `booking.manage` | Update service |
| DELETE | `/services/{id}` | `booking.manage` | Delete service |
| POST | `/services/{id}/toggle` | `booking.manage` | Toggle active status |

### Slots

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/slots` | `booking.view` | List available slots |
| GET | `/slots/daterange` | `booking.view` | Get slots by date range |
| POST | `/slots/generate` | `booking.manage` | Generate slots for date |
| POST | `/slots/{id}/book` | `booking.manage` | Book a slot |
| POST | `/slots/{id}/cancel` | `booking.manage` | Cancel slot booking |
| POST | `/slots/{id}/block` | `booking.manage` | Block a slot |

### Appointments

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/appointments` | `booking.view` | List appointments |
| GET | `/appointments/{id}` | `booking.view` | Show appointment details |
| GET | `/appointments/summary` | `booking.view` | Get summary statistics |
| POST | `/appointments` | `booking.manage` | Create appointment |
| PUT | `/appointments/{id}` | `booking.manage` | Update appointment |
| POST | `/appointments/{id}/confirm` | `booking.manage` | Confirm appointment |
| POST | `/appointments/{id}/start` | `booking.manage` | Start appointment |
| POST | `/appointments/{id}/complete` | `booking.manage` | Complete appointment |
| POST | `/appointments/{id}/cancel` | `booking.manage` | Cancel appointment |
| POST | `/appointments/{id}/no-show` | `booking.manage` | Mark no-show |
| POST | `/appointments/{id}/reschedule` | `booking.manage` | Reschedule appointment |

---

## Numbering Format

| Entity | Format | Example |
|--------|--------|---------|
| Appointment No | BK-YYYY-NNNNNN | BK-2026-000001 |

---

## Service Categories

| Key | Label (Malay) | Description |
|-----|---------------|-------------|
| haircut | Potongan Rambut | Hair cutting services |
| coloring | Pewarnaan Rambut | Hair coloring |
| facial | Rawatan Muka | Facial treatments |
| massage | Urutan | Massage therapy |
| nails | Kuku | Manicure/Pedicure |
| makeup | Solekan | Makeup application |
| consultation | Perundingan | Consultation sessions |
| medical | Perubatan | Medical services |
| dental | Gigi | Dental services |
| other | Lain-lain | Other services |

---

## Appointment Statuses

| Status | Malay Label | Description |
|--------|-------------|-------------|
| draft | Rangka | Appointment being created |
| pending | Menunggu | Awaiting confirmation |
| confirmed | Disahkan | Confirmed by customer/staff |
| in_progress | Sedang Berlangsung | Currently happening |
| completed | Selesai | Successfully completed |
| cancelled | Dibatalkan | Cancelled by customer/staff |
| no_show | Tidak Hadir | Customer didn't show up |
| rescheduled | Dijadual Semula | Moved to new time |

---

## Permissions

| Permission | Description | Roles |
|------------|-------------|-------|
| `booking.view` | View services, slots, appointments | tenant_owner, tenant_admin, finance_manager, hr_manager, cashier, staff |
| `booking.manage` | Manage services, slots, appointments | tenant_owner, tenant_admin |

---

## Business Logic

### Service Catalog Management

1. **Create Service** → Define service with name, duration, price, category
2. **Assign Provider** → Optional default provider assignment
3. **Set Capacity** → Max concurrent customers
4. **Configure Prep Time** → Buffer before service starts

### Slot Generation

1. **Generate Slots** → Based on business hours, duration, buffer time
2. **Auto-create Daily Slots** → Can be scheduled via cron/job
3. **Block Slots** → For breaks, meetings, maintenance
4. **Track Availability** → Real-time capacity tracking

### Appointment Lifecycle

1. **Create Draft** → Select service, provider, slot, customer
2. **Confirm** → Send confirmation notification
3. **Start** → Mark as in-progress, record actual start time
4. **Complete** → Record actual end time, update status
5. **Cancel** → Free up slot, send cancellation notice
6. **No-Show** → Mark customer absent
7. **Reschedule** → Create new appointment, free old slot

### Reminder System

- **Channels**: SMS, WhatsApp, Email, Push notification
- **Schedule**: 24h before, 2h before, 30m before (configurable)
- **Templates**: Customizable message templates
- **Tracking**: Sent/failed status per reminder

### Integration Points

- **B-CRM**: Customer linking for history & preferences
- **B-POS**: Service delivery billing after completion
- **B-Finance**: Revenue recognition on completion
- **B-Loyalty**: Points earning on completed appointments
- **B-Virtual Office**: Staff schedule coordination

---

## Services

### ServiceCatalogService

```php
// List services
$services = $service->listServices($tenantId, $filters);

// Get single service
$service = $service->getService($serviceId, $tenantId);

// Create service
$newService = $service->createService($data, $tenantId, $userId);

// Update service
$result = $service->updateService($serviceId, $data, $tenantId);

// Toggle active
$result = $service->toggleActive($serviceId, $tenantId);
```

### SlotService

```php
// Generate slots for date
$slots = $service->generateSlots($tenantId, $serviceId, $providerId, $date, $businessHours, $bufferMinutes);

// Get available slots
$available = $service->getAvailableSlots($tenantId, $serviceId, $providerId, $date);

// Book a slot
$booked = $service->bookSlot($slotId, $tenantId);

// Cancel slot booking
$canceled = $service->cancelSlot($slotId, $tenantId);

// Block a slot
$blocked = $service->blockSlot($slotId, $tenantId);
```

### AppointmentService

```php
// List appointments
$appointments = $service->listAppointments($tenantId, $filters);

// Create appointment
$appointment = $service->createAppointment($data, $tenantId, $userId);

// Confirm appointment
$confirmed = $service->confirmAppointment($appointmentId, $tenantId);

// Start appointment
$start = $service->startAppointment($appointmentId, $tenantId);

// Complete appointment
$completed = $service->completeAppointment($appointmentId, $tenantId);

// Cancel appointment
$canceled = $service->cancelAppointment($appointmentId, $data, $tenantId, $userId);

// Mark no-show
$noShow = $service->markNoShow($appointmentId, $tenantId);

// Reschedule appointment
$rescheduled = $service->rescheduleAppointment($appointmentId, $data, $tenantId, $userId);

// Get summary
$summary = $service->getSummary($tenantId, $startDate, $endDate);
```

---

## Configuration

File: `config/booking.php`

```php
'service_categories' => [ ... ],     // Category labels
'appointment_statuses' => [ ... ],   // Status labels
'slot_durations' => [ ... ],         // Available durations
'booking' => [
    'advance_booking_days' => 30,    // Max advance booking
    'min_booking_notice_hours' => 2, // Min notice required
    'cancellation_window_hours' => 24, // Free cancel window
    'buffer_time_minutes' => 15,     // Between appointments
],
'reminders' => [
    'enabled' => true,
    'channels' => ['sms', 'whatsapp', 'email'],
    'schedule' => [ ... ],
],
'timezone' => 'Asia/Kuala_Lumpur',
'business_hours' => [ ... ],         // Weekly schedule
```

---

## Events

| Event | Trigger | Payload |
|-------|---------|---------|
| `AppointmentCreated` | New appointment | appointment_id, service_id, customer_id |
| `AppointmentConfirmed` | Confirmation sent | appointment_id, reminder_id |
| `AppointmentStarted` | In-progress | appointment_id, actual_start_time |
| `AppointmentCompleted` | Finished | appointment_id, actual_duration |
| `AppointmentCancelled` | Cancellation | appointment_id, reason, slot_freed |
| `AppointmentRescheduled` | Moved to new time | new_id, old_id, new_slot |
| `ReminderSent` | Notification delivered | reminder_id, channel |

---

## Testing

Run Booking module tests:

```bash
php artisan test --filter=BookingModuleTest
```

---

## Future Enhancements

- [ ] Online booking widget for website
- [ ] Customer self-service portal
- [ ] Waitlist management
- [ ] Group bookings
- [ ] Package/prepaid bookings
- [ ] Loyalty point integration
- [ ] Automated reviews post-completion
- [ ] Provider performance analytics
- [ ] Peak pricing/dynamic pricing
- [ ] Multi-location support
- [ ] Recurring appointments
- [ ] Insurance/billing integration

---

## License

Part of AI BIJAN SaaS Platform. Proprietary license.

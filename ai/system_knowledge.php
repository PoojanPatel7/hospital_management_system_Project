<?php
// ai/system_knowledge.php
// Comprehensive Knowledge Base of all Pages, Features, and Workflows of the Hospital Management System

function getHospitalSystemKnowledge() {
    return <<<KNOWLEDGE
=== HOSPITAL MANAGEMENT SYSTEM - ARCHITECTURE & PAGE SPECIFICATIONS ===

1. DASHBOARD (dashboard.php)
- Purpose: Executive & operational command center for hospital administration and clinical staff.
- Core Metrics: Today's registered patients, today's appointments, real-time active queue count, bed occupancy (available vs occupied), on-duty doctors, staff attendance rate.
- Features: Live KPI summary cards, quick navigation shortcuts, today's appointment roster, emergency intake trigger.
- Tables: patients, appointments, beds, doctors, staff, staff_attendance.

2. PATIENTS & PROFILES (patients.php & patient_profile.php)
- Purpose: Comprehensive patient identity, medical dossier, and registration lifecycle.
- Patient ID / MRN Format: Unique Medical Record Number (e.g., 'CP-2026-001').
- Fields: id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone.
- Duplicate Detection: Checked against LOWER(name) + LOWER(surname) + LOWER(father_name).
- Patient Profile (patient_profile.php): Full clinical timeline, vital signs, appointment history, past diagnoses, active prescriptions, attached diagnostic reports (patient_files), chronological events (timeline_events).
- Tables: patients, patient_files, timeline_events, appointments, diagnoses, prescriptions.

3. DOCTORS & SCHEDULING (doctors.php & doctor_slots.php)
- Purpose: Medical specialist directory, credentialing, and schedule/slot management.
- Doctor ID Format: Unique identifier (e.g., 'doc-6a9a8604e31bd').
- Fields: id, name, phone, department_id, experience, degree, hospital_id (Note: NO created_at column).
- Departments & Specialties: Linked via `doctor_categories` and `departments` (Cardiology, Orthopedics, Pediatrics, General Medicine, Neurology, ICU).
- Weekly Scheduling (doctor_slots.php): Configured in `doctor_day_schedules` (available days, start/end times, slot duration in mins, max patients per slot, break times). Specific booking slots in `doctor_slots`.
- Tables: doctors, departments, doctor_categories, doctor_day_schedules, doctor_slots.

4. APPOINTMENT BOOKING (book.php & appointments.php)
- Purpose: Scheduling outpatient (OPD) and emergency consultations.
- Types: 'General Consultation', 'Follow-up', 'Emergency Case', 'Health Checkup'.
- Stages:
  • Stage 0: Pre-Booked (advance appointment for future date/time)
  • Stage 1: Checked-In (patient arrived at front desk)
  • Stage 2: Available at Hospital / Triage
  • Stage 3: In Consultation (with doctor)
  • Stage 4: Pharmacy / Billing (post-consultation)
  • Stage 5: Inpatient Admission / Bed / Discharged
- Statuses: 'Pre-Booked', 'Checked-In', 'Available at Hospital', 'In Consultation', 'Pharmacy / Billing', 'Discharged (Normal Medicine)', 'Discharged from Bed', 'Cancelled'.
- Key Column: `date` (NOT `appointment_date`).
- Tables: appointments, patients, doctors, timeline_events.

5. QUEUE MANAGEMENT (queue.php)
- Purpose: Live real-time outpatient flow Kanban & token queue.
- Token System: Sequential daily token numbers (1, 2, 3...).
- Pipeline: 5 stages (Waiting/Checked-In -> Triage -> Doctor Consultation -> Pharmacy -> Billing/Done).
- Walk-In Intake: Instant registration directly into Stage 2 ('Available at Hospital').
- Advance Check-In: Converts Stage 0 ('Pre-Booked') to Stage 1 ('Checked-In') when patient arrives.
- System State: Line pause/resume tracked in `system_state.line_running`.
- Tables: appointments, patients, doctors, system_state, timeline_events.

6. BED & INPATIENT MANAGEMENT (beds.php)
- Purpose: Ward, room, and bed admission, occupancy tracking, and discharge.
- Wards/Wings: 'ICU', 'General Ward Floor', 'Semi-Private', 'Deluxe Private', 'Emergency Trauma'.
- Bed Statuses: Strictly 'Available' or 'Occupied'.
- Allotment: Bed assigned to `patient_id` in `beds`, synchronized with `appointments.bed_number` and `stage = 5`.
- Discharge Workflow:
  1. Set `beds.status = 'Available'` and `beds.patient_id = NULL` for the bed number.
  2. Set `appointments.status = 'Discharged from Bed'` and `appointments.bed_number = NULL`.
  3. Insert discharge entry into `timeline_events`.
- Tables: beds, patients, appointments, timeline_events.

7. STAFF & ATTENDANCE MANAGEMENT (staff.php & api/staff.php)
- Purpose: Human resources roster and daily/monthly employee attendance tracking.
- Staff Fields: id, hospital_id, staff_code (e.g., 'STF-101'), first_name, last_name, role, department, shift, status ('Active'/'Inactive'), phone, email, qualification, salary.
- Roles: Nurse, Doctor, Receptionist, Lab Tech, RMO, Admin, Housekeeping, Security.
- Daily Attendance Table (`staff_attendance`):
  • MUST use table `staff_attendance` (NEVER update a non-existent column in `staff`!).
  • Fields: hospital_id, staff_id, date, status, check_in_time, check_out_time, working_hours, notes, marked_by.
  • Valid Statuses: Strictly 'Present', 'Absent', 'Late', 'Half Day', 'On Leave'.
  • Standard Working Hours: Present = 8.0 hrs (08:00 check-in), Late = 7.5 hrs, Half Day = 4.0 hrs, Absent/Leave = 0 hrs.
  • Constraint: UNIQUE key on (hospital_id, staff_id, date) -> Use `ON DUPLICATE KEY UPDATE`.
- Bulk Operations: Mark all active staff for today/date in 1 click.
- Monthly Sheet: Computes total days, present days, late days, half days, total hours, and attendance rate %.
- Tables: staff, staff_attendance.

8. MEDICAL HISTORY & CONSULTATIONS (history.php & consultation.php)
- Purpose: Longitudinal patient electronic health records (EHR).
- Doctor Notes: Saved in `appointments.doctor_notes`.
- Prescriptions (`prescriptions`): medicine_name, dosage, frequency, duration, instructions, appointment_id.
- Diagnoses (`diagnoses`): description, appointment_id.
- Reports (`patient_files`): title, file_path, file_name, mime_type, file_size, record_date, appointment_id.
- Tables: appointments, prescriptions, diagnoses, patient_files, patients, doctors.

9. BACKUP & RESTORE (backup.php & restore.php)
- Purpose: Automated and manual SQL database dumps and system restoration.
- Features: Generate .sql backup, secure download, auto-backup intervals, database restore.
- Directory: /backups with .htaccess protection.

10. HOSPITAL SETTINGS (about.php & register_hospital.php)
- Purpose: Multi-hospital multi-tenancy configuration and facility profile.
- Fields: hospital name, code, address, contact numbers, emergency hotline, bed capacity.
- Table: hospitals.
KNOWLEDGE;
}

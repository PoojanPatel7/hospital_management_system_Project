<?php
// ai/system_knowledge.php
// Master Knowledge Base: Complete Relational Map, All 21 Tables, Foreign Keys, UI Terminology vs DB Columns, and Page Mappings

function getHospitalSystemKnowledge() {
    return <<<KNOWLEDGE
=== BHOOMA HOSPITAL MANAGEMENT SYSTEM - COMPLETE MASTER RELATIONAL ARCHITECTURE ===

--- 1. DATABASE ENTITY-RELATIONSHIP MAP (ALL 21 TABLES & CONNECTIONS) ---

1. HOSPITALS (hospitals)
   - Primary Key: `id` (int)
   - Fields: `name`, `username` (unique), `password` (hashed), `created_at`.
   - Relationships & Connections:
     • Master multi-tenant parent table.
     • Every operational table references `hospital_id` to enforce strict hospital isolation:
       - patients.hospital_id -> hospitals.id
       - doctors.hospital_id -> hospitals.id
       - staff.hospital_id -> hospitals.id
       - beds.hospital_id -> hospitals.id
       - appointments.hospital_id -> hospitals.id
       - staff_attendance.hospital_id -> hospitals.id
       - ai_conversations.hospital_id -> hospitals.id
       - ai_action_log.hospital_id -> hospitals.id
   - Used In: login.php, register_hospital.php, about.php, all API endpoints.

2. PATIENTS (patients)
   - Primary Key: `id` (varchar, format: 'CP-YYYY-XXX', e.g. 'CP-2026-001') -> Called 'MRN' / 'Patient ID' in UI.
   - Fields: `name`, `surname`, `father_name`, `phone`, `demographics` (e.g. '35 Y, Male, O+'), `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`, `created_at`.
   - Relationships & Connections:
     • One-to-Many with appointments: appointments.patient_id = patients.id
     • One-to-Many with timeline_events: timeline_events.patient_id = patients.id
     • One-to-Many with patient_files: patient_files.patient_id = patients.id
     • One-to-One with beds: beds.patient_id = patients.id (when patient is admitted)
   - Used In: patients.php, patient_profile.php, queue.php, book.php, beds.php, history.php.

3. DOCTORS (doctors)
   - Primary Key: `id` (varchar, format: 'doc-XXXXXX', e.g. 'doc-6a9a8604e31bd') -> Doctor ID.
   - Fields: `name`, `department_id` (optional legacy FK), `experience`, `degree`, `phone`, `hospital_id`.
   - CRITICAL CAVEAT: Does NOT have `created_at` or `specialty` directly on doctors table.
   - Relationships & Connections:
     • Many-to-Many with departments via `doctor_categories`:
       doctors.id = doctor_categories.doctor_id AND doctor_categories.department_id = departments.id
     • One-to-Many with doctor_day_schedules: doctor_day_schedules.doctor_id = doctors.id
     • One-to-Many with doctor_slots: doctor_slots.doctor_id = doctors.id
     • One-to-Many with appointments: appointments.doctor_id = doctors.id
   - Used In: doctors.php, doctor_slots.php, book.php, queue.php, dashboard.php.

4. DEPARTMENTS (departments)
   - Primary Key: `id` (varchar, e.g. 'dep-cardio', 'dep-ortho')
   - Fields: `name` (e.g. 'Cardiology', 'Neurology', 'General Medicine'), `icon` (FontAwesome class), `hospital_id`.
   - Relationships:
     • Linked to doctors through junction table `doctor_categories`.
   - Used In: doctors.php, book.php, about.php.

5. DOCTOR CATEGORIES (doctor_categories)
   - Composite Primary Key: (`doctor_id`, `department_id`)
   - Purpose: Junction table linking doctors to medical specialties and departments.
   - Used In: doctors.php, book.php, queue.php (subquery for doctor's dept).

6. DOCTOR DAY SCHEDULES (doctor_day_schedules)
   - Primary Key: `id` (int)
   - Fields: `doctor_id`, `day_of_week` ('Monday'..'Sunday'), `is_available` (1/0), `start_time` (e.g. '09:00'), `end_time` (e.g. '17:00'), `duration_minutes` (e.g. 15), `break_start`, `break_end`, `custom_slots`.
   - Used In: doctor_slots.php, book.php (to calculate available consultation slots).

7. DOCTOR SLOTS (doctor_slots)
   - Primary Key: `id` (int)
   - Fields: `doctor_id`, `time_slot` (e.g. '09:00 AM', '10:30 AM').
   - Used In: doctor_slots.php, book.php.

8. APPOINTMENTS (appointments)
   - Primary Key: `id` (int, auto_increment) -> Displayed as 'APP-0001' in UI.
   - CRITICAL CAVEAT: Column for appointment date is `date` (NOT `appointment_date`).
   - Fields: `patient_id` (FK to patients.id), `doctor_id` (FK to doctors.id), `type` ('General Consultation', 'Follow-up', 'Emergency Case', 'Health Checkup'), `date` (YYYY-MM-DD), `slot` (e.g. '09:30 AM', 'Immediate Walk-In'), `symptoms`, `allergies`, `status`, `stage` (0 to 5), `bed_number` (FK to beds.bed_number), `doctor_notes`, `hospital_id`, `created_at`.
   - Stages Lifecycle:
     • Stage 0: Pre-Booked (advance appointment)
     • Stage 1: Checked-In (patient arrived at hospital desk)
     • Stage 2: Available at Hospital / Triage
     • Stage 3: In Consultation (with doctor)
     • Stage 4: Pharmacy / Billing
     • Stage 5: Inpatient Bed Admission / Discharged
   - Relationships:
     • appointments.patient_id = patients.id
     • appointments.doctor_id = doctors.id
     • One-to-Many with prescriptions: prescriptions.appointment_id = appointments.id
     • One-to-Many with diagnoses: diagnoses.appointment_id = appointments.id
     • One-to-Many with timeline_events: timeline_events.appointment_id = appointments.id
     • One-to-Many with patient_files: patient_files.appointment_id = appointments.id
   - Used In: appointments.php, book.php, queue.php, consultation.php, history.php, dashboard.php.

9. BEDS (beds)
   - Primary Key: `id` (varchar, e.g. 'bed-1718293041')
   - Fields: `bed_number` (varchar, unique, e.g. 'Bed 101', 'ICU-01'), `type` ('ICU', 'General', 'Private', 'Semi-Private'), `wing` ('General Ward Floor', 'ICU Wing', 'Deluxe Wing'), `status` ('Available' or 'Occupied'), `patient_id` (FK to patients.id), `hospital_id`.
   - Relationships:
     • beds.patient_id = patients.id
     • Synchronized with appointments.bed_number at Stage 5.
   - Discharge Flow: Sets `status = 'Available'`, `patient_id = NULL`, and sets `appointments.status = 'Discharged from Bed'`.
   - Used In: beds.php, queue.php, dashboard.php.

10. STAFF (staff)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `hospital_id`, `staff_code` (e.g. 'STF-101'), `first_name`, `last_name`, `email`, `phone`, `role` ('Senior Staff Nurse', 'Head Pharmacist', 'Chief Medical Lab Technician', 'Front Desk Officer / Receptionist', 'Resident Medical Officer (RMO)', 'Housekeeping', 'Security'), `department`, `gender`, `date_of_birth`, `joining_date`, `shift` ('Morning (08:00 - 16:00)', 'Evening (16:00 - 00:00)', 'Night (00:00 - 08:00)'), `status` ('Active'/'Inactive'), `qualification`, `salary`, `blood_group`, `emergency_contact`, `address`, `is_user`, `username`, `password`, `created_at`.
    - Relationships:
      • One-to-Many with staff_attendance: staff_attendance.staff_id = staff.id
    - CRITICAL CAVEAT: Attendance is NEVER stored as a column in `staff`. It is ALWAYS in `staff_attendance`.
    - Used In: staff.php, dashboard.php.

11. STAFF ATTENDANCE (staff_attendance)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `hospital_id`, `staff_id` (FK to staff.id), `date` (YYYY-MM-DD), `status` (ENUM: 'Present', 'Absent', 'Late', 'Half Day', 'On Leave'), `check_in_time` (time), `check_out_time` (time), `working_hours` (decimal, e.g. 8.00 for Present, 7.50 for Late, 4.00 for Half Day, 0.00 for Absent/Leave), `notes`, `marked_by`, `created_at`, `updated_at`.
    - Unique Constraint: (`hospital_id`, `staff_id`, `date`) -> Use `ON DUPLICATE KEY UPDATE`.
    - Used In: staff.php, dashboard.php.

12. PRESCRIPTIONS (prescriptions)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `appointment_id` (FK to appointments.id), `medicine_name` (e.g. 'Paracetamol 500mg', 'Amoxicillin'), `dosage` (e.g. '1 tablet'), `frequency` (e.g. 'TDS (3 times a day)', 'BD (2 times a day)'), `duration` (e.g. '5 days'), `instructions` (e.g. 'Take after food').
    - Relationships: prescriptions.appointment_id = appointments.id.
    - Used In: consultation.php, history.php, patient_profile.php.

13. DIAGNOSES (diagnoses)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `appointment_id` (FK to appointments.id), `description` (e.g. 'Acute Bronchitis', 'Type 2 Diabetes Mellitus').
    - Used In: consultation.php, history.php, patient_profile.php.

14. PATIENT FILES (patient_files)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `patient_id` (FK to patients.id), `appointment_id` (FK to appointments.id), `title` (e.g. 'Chest X-Ray PA View', 'Complete Blood Count Report'), `file_path`, `record_date`, `file_data` (longblob), `file_name`, `mime_type`, `file_size`, `created_at`.
    - Used In: consultation.php, patient_profile.php.

15. TIMELINE EVENTS (timeline_events)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `appointment_id` (FK to appointments.id), `patient_id` (FK to patients.id), `event_time` (e.g. '10:30 AM'), `event_description`, `created_at`.
    - Purpose: Immutable chronological audit trail of patient milestones (Check-in, Triage, Doctor Consultation, Bed Admission, Discharge).
    - Used In: queue.php, patient_profile.php, beds.php.

16. SYSTEM STATE (system_state)
    - Primary Key: `id` (int) -> Single row configuration.
    - Fields: `line_running` (tinyint: 1 = Queue Running, 0 = Queue Paused).
    - Used In: queue.php (allows front desk to pause/resume ticket intake).

17. AI CONVERSATIONS (ai_conversations)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `hospital_id`, `user_type` ('admin'/'staff'), `user_id` (username or staff code), `user_role`, `title`, `model_used`, `page_context` (e.g. 'dashboard.php', 'beds.php'), `created_at`, `updated_at`.
    - Used In: api/chatbot.php, ai_logs.php.

18. AI CHAT MESSAGES (ai_chat_messages)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `conversation_id` (FK to ai_conversations.id), `role` ('user'/'assistant'/'system'), `content`, `tokens_used`, `sql_executed`, `response_time_ms`, `created_at`.
    - Used In: api/chatbot.php, ai_logs.php.

19. AI PENDING ACTIONS (ai_pending_actions)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `conversation_id`, `message_id`, `hospital_id`, `action_type` ('INSERT'/'UPDATE'/'DELETE'), `target_table`, `description`, `sql_query`, `sql_params`, `validation_data`, `status` ('pending', 'confirmed', 'cancelled', 'expired', 'executed', 'failed'), `confirmed_at`, `executed_at`, `error_message`, `expires_at`, `created_at`.
    - Used In: api/chatbot.php, includes/chatbot_widget.php, ai_logs.php.

20. AI ACTION LOG (ai_action_log)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `hospital_id`, `user_id`, `user_role`, `action_id`, `action_type`, `target_table`, `sql_executed`, `rows_affected`, `success` (1/0), `error_message`, `ip_address`, `created_at`.
    - Purpose: Immutable security audit log of every database write performed by the AI.
    - Used In: api/chatbot.php, ai_logs.php.

21. AI CONFIG (ai_config)
    - Primary Key: `id` (int, auto_increment)
    - Fields: `hospital_id` (unique), `enabled`, `model_name`, `ollama_url`, `max_tokens`, `temperature`, `context_window`, `rate_limit_per_minute`, `allowed_tables`, `system_prompt_override`.
    - Used In: ai/config.php, ai_logs.php.

--- 2. UI TERMINOLOGY TO DATABASE FIELD TRANSLATION ---
• UI "Patient MRN" / "Patient Code" -> patients.id
• UI "Appointment Date" -> appointments.date (NOT appointment_date)
• UI "Specialty" / "Department" -> departments.name (JOIN doctor_categories ON departments.id = doctor_categories.department_id)
• UI "Doctor Experience" -> doctors.experience
• UI "Doctor Degree / Qualifications" -> doctors.degree
• UI "Doctor Contact" -> doctors.phone
• UI "Bed Status" -> beds.status ('Available' or 'Occupied')
• UI "Bed Wing / Ward" -> beds.wing / beds.type
• UI "Staff Attendance" -> staff_attendance.status (NEVER staff table)
• UI "Hours Worked" -> staff_attendance.working_hours
• UI "Check-In Time" -> staff_attendance.check_in_time
• UI "Queue Ticket" / "Token Number" -> Computed dynamically by appointment sequence today in queue.php
• UI "Consultation Notes" -> appointments.doctor_notes
• UI "Lab Reports / Documents" -> patient_files
• UI "Prescribed Medicines" -> prescriptions.medicine_name, dosage, frequency, duration
• UI "Clinical Diagnosis" -> diagnoses.description
KNOWLEDGE;
}

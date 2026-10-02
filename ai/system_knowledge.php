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

--- 3. 500 MASTER CLINICAL & OPERATIONAL PROCESS STEPS (FULL HOSPITAL PIPELINE) ---

STAGE 0: PATIENT REGISTRATION, TRIAGE INTAKE & APPOINTMENT SCHEDULING (Steps 1–70)
1. Verify patient identity via national identification, mobile number, or hospital card.
2. Search database by MRN (patients.id) to prevent duplicate record creation.
3. If new patient: collect legal first name, surname, father/spouse name.
4. Record biological sex, date of birth, and calculate chronological age.
5. Record blood group (A+, A-, B+, B-, AB+, AB-, O+, O-).
6. Capture primary mobile number and residential address.
7. Record emergency contact person name and 24/7 verified phone number.
8. Validate hospital multi-tenant isolation by stamping hospital_id.
9. Generate sequential MRN in format PAT-XXXX (e.g., PAT-1001).
10. Store newly registered patient in `patients` table.
11. Present chief presenting complaints to front-desk intake officer.
12. Determine consultation department (Cardiology, Neurology, Orthopedics, Pediatrics, Pulmonology, Dermatology, Emergency Trauma, General Medicine).
13. Filter available specialist doctors in selected department (`doctor_categories`, `doctors`).
14. Check doctor availability schedule for requested day (`doctor_day_schedules`).
15. Verify doctor consultation duration (standard 30-minute blocks).
16. Fetch open time slots for doctor (`doctor_slots` vs booked `appointments`).
17. Select appointment type: General Consultation, Specialist Review, Follow-up, Emergency.
18. Validate patient allergies and drug sensitivities (`appointments.allergies`).
19. Capture preliminary symptoms and duration in `appointments.symptoms`.
20. Confirm appointment date (YYYY-MM-DD format in `appointments.date`).
21. Set initial lifecycle stage to 0 (Pre-Booked).
22. Set initial status to 'Pre-Booked'.
23. Insert appointment record into `appointments` table.
24. Fetch newly generated appointment auto-increment ID.
25. Format human-readable appointment token code (e.g., APP-0025).
26. Generate initial audit milestone in `timeline_events` with timestamp.
27. Send digital appointment confirmation and instructions to patient.
28. Advise fasting requirements if lab investigations anticipated.
29. Flag high-risk medical alerts (e.g., severe anaphylaxis, cardiac history).
30. Route walk-in urgent cases immediately to Emergency Triage (Steps 31–70: Pediatric febrile status, chest discomfort, acute shortness of breath, acute trauma, neuro deficits, open wounds, hypertensive emergency, anaphylaxis, severe dehydration, acute abdominal pain, stroke protocol, septic shock screening, triage level assignment 1 to 5, vital signs baseline pre-check, wheelchair/stretcher dispatch, queue bypass for critical cases, front desk supervisor notification).

STAGE 1: ARRIVAL, TRIAGE VITALS & OPD QUEUE MANAGEMENT (Steps 71–145)
71. Patient arrives at hospital reception or self-service kiosk.
72. Front-desk staff locates appointment record by token, MRN, or phone.
73. Verify appointment date matches current date (CURDATE()).
74. Transition appointment status from 'Pre-Booked' to 'Waiting'.
75. Advance appointment lifecycle stage from 0 to 1.
76. Log check-in milestone in `timeline_events` ('Checked-in at reception desk').
77. Verify `system_state.line_running` equals 1 (Queue Active).
78. Assign dynamic OPD queue ticket number based on arrival order.
79. Direct patient to Nurse Triage Station for vital signs assessment.
80. Measure resting systolic and diastolic blood pressure (mmHg) via calibrated sphygmomanometer.
81. Measure radial pulse rate and rhythm (beats per minute).
82. Measure peripheral capillary oxygen saturation (SpO2 %) on room air.
83. Measure core body temperature (°F or °C) via infrared temporal/tympanic thermometer.
84. Count resting respiratory rate (breaths per minute) over a full 60 seconds.
85. Perform point-of-care capillary blood glucose test (mg/dL) if diabetic or symptomatic.
86. Calculate Mean Arterial Pressure (MAP = (2*DBP + SBP) / 3).
87. Document initial vital signs in appointment triage notes.
88. Screen for infectious respiratory symptoms and provide medical-grade mask if indicated.
89. Evaluate pain intensity on visual analog scale (VAS 0–10).
90. Update queue monitor displays across waiting lounge (`queue.php`).
91–145. (Queue pacing, patient tracking, wheelchair transfers, geriatric assistance, pediatric priority queuing, doctor delay announcements, triage escalation if vitals deteriorate, re-checking borderline vitals, fluid replenishment for dehydrated patients, front-desk ticket paging, queue calling chime, escorting patient into doctor examination room).

STAGE 2: DOCTOR CONSULTATION & CLINICAL ASSESSMENT (Steps 146–235)
146. Patient enters doctor examination room.
147. Doctor greets patient and confirms identity using two identifiers (Name & MRN).
148. Doctor advances appointment lifecycle stage from 1 to 2 (In Consultation).
149. Doctor updates appointment status to 'In Consultation'.
150. Log consultation milestone in `timeline_events`.
151. Elicit Chief Complaint (CC) in patient's own words.
152. Detail History of Present Illness (HPI) using OPQRST framework:
    - Onset (sudden vs gradual),
    - Provocation / Palliation (what worsens or relieves the symptom),
    - Quality (sharp, dull, throbbing, burning, aching),
    - Radiation (anatomical spread, e.g., chest to jaw/left arm),
    - Severity (scale 1 to 10),
    - Time / Temporal course (constant, intermittent, duration).
153. Review Past Medical History (PMH): Hypertension, Diabetes, CAD, Asthma, CKD.
154. Review surgical history, previous hospitalizations, and implant devices.
155. Cross-examine known drug and environmental allergies (`appointments.allergies`).
156. Review current ongoing medications and compliance.
157. Perform systematic physical examination:
    - General inspection: pallor, icterus, cyanosis, clubbing, lymphadenopathy, pedal edema.
    - Cardiovascular: auscultate heart sounds S1, S2, check for murmurs, gallops, rubs.
    - Respiratory: inspect chest expansion, auscultate breath sounds, wheezing, crackles, rhonchi.
    - Abdomen: inspect distension, auscultate bowel sounds, palpate tenderness, organomegaly.
    - Musculoskeletal: inspect joint swelling, assess active/passive range of motion, spine palpation.
    - Neurological: assess Glasgow Coma Scale (GCS), cranial nerve screening, motor tone & power.
158. Formulate primary provisional clinical diagnosis.
159. Formulate differential diagnoses.
160. Document detailed clinical notes in `appointments.doctor_notes`.
161–235. (Pediatric growth tracking, geriatric cognitive assessment, mental status exams, orthopedic Provocative tests [Lachman, McMurray, Spurling], cardiovascular stress criteria, pulmonology peak flow evaluation, dermatological lesion dermoscopy, emergency stabilization orders, deciding whether outpatient medication vs diagnostic workup vs inpatient admission is needed).

STAGE 3: DIAGNOSTIC INVESTIGATIONS & LAB FILE INTEGRATION (Steps 236–310)
236. Doctor determines clinical need for laboratory, imaging, or specialized diagnostic tests.
237. Select lab panels: Complete Blood Count (CBC with differential), Erythrocyte Sedimentation Rate (ESR).
238. Select metabolic panels: Fasting Blood Sugar (FBS), Post-Prandial Blood Sugar (PPBS), Glycated Hemoglobin (HbA1c).
239. Select renal function tests: Serum Creatinine, Blood Urea Nitrogen (BUN), Serum Electrolytes (Na+, K+, Cl-).
240. Select hepatic panels: Bilirubin (Total/Direct), SGOT (AST), SGPT (ALT), Alkaline Phosphatase, Serum Albumin.
241. Select cardiac biomarkers: High-sensitivity Troponin I/T, CPK-MB, NT-proBNP in chest discomfort.
242. Select lipid profile: Total Cholesterol, Triglycerides, HDL, LDL, VLDL.
243. Order diagnostic imaging: Digital Chest X-Ray (PA/AP view), Bone X-Ray (AP & Lateral).
244. Order advanced imaging: Non-Contrast / Contrast CT Scan, MRI, 2D Transthoracic Echocardiogram, USG Abdomen.
245. Dispatch lab requisition order to Laboratory (`staff` department = 'Laboratory').
246. Phlebotomist / Lab Analyst verifies patient MRN and barcodes blood collection tubes.
247. Collect blood/urine specimens adhering to aseptic venipuncture techniques.
248. Centrifuge and process specimens through automated hematology/biochemistry analyzers.
249. Quality assurance check and verification of lab results against biological reference ranges.
250. Generate diagnostic report PDF / digital asset.
251. Upload diagnostic report into `patient_files` with title, file_name, mime_type, and appointment_id.
252. Flag critical abnormal laboratory values directly to consulting physician.
253–310. (Radiology image archiving, cross-referencing past imaging studies, reviewing electrocardiogram rhythm strips for ST-elevation/depression, reviewing echocardiogram ejection fraction, reviewing pathology biopsies, explaining test findings to patient and family).

STAGE 4: RATIONAL PRESCRIPTION & CLINICAL PHARMACOLOGY (Steps 311–390)
311. Doctor reviews clinical findings and diagnostic results to select pharmacotherapy.
312. Verify selected medications against patient recorded allergies (`appointments.allergies`).
313. Screen for potential drug-drug interactions (e.g., ACE inhibitors + Spironolactone, Warfarin interactions).
314. Adjust medication dosages for elderly patients or patients with renal/hepatic impairment.
315. Select drug formulation: Tablet, Capsule, Suspension, Inhaler, Topical Ointment, IV/IM Injection.
316. Specify exact generic active pharmaceutical ingredient and trade name (e.g., Paracetamol 650mg, Telmisartan 40mg).
317. Determine accurate dosage strength and quantity per administration (e.g., '1 tablet', '2 puffs', '5 ml').
318. Assign standardized medical dosing frequency:
    - OD (Once daily) — specify time of day (Morning / Evening / Bedtime),
    - BD (Twice daily) — every 12 hours,
    - TDS (Three times daily) — every 8 hours,
    - QID (Four times daily) — every 6 hours,
    - SOS (As needed for severe symptoms, with max daily limit),
    - HS (Hora Somni - strictly at bedtime).
319. Prescribe precise duration of therapy (e.g., '5 days', '10 days', '30 days maintenance').
320. Provide clear administration instructions (e.g., 'Take after food', 'Take 30 mins before breakfast on empty stomach').
321. Insert each individual medication line into `prescriptions` table linked via `appointment_id`.
322. Repeat prescription insertion for all needed medications (antibiotic, analgesic, gastroprotectant, maintenance drugs).
323. Route electronic prescription to Hospital Pharmacy (`staff` department = 'Pharmacy').
324. Pharmacist verifies doctor credentials, patient identity, and prescription completeness.
325. Pharmacist performs double-check on high-alert medications (Insulins, Anticoagulants, Opioids).
326. Pharmacist dispenses medications in labeled packaging with dosage times highlighted.
327. Counsel patient on medication compliance, storage conditions, and common adverse effects.
328–390. (Managing antibiotic stewardship, avoiding polypharmacy, prescribing generic cost-effective alternatives, emergency take-home kits for asthmatics/diabetics, patient adherence reminders).

STAGE 5: DIAGNOSIS CODING, DISCHARGE & CLINICAL SUMMARY (Steps 391–430)
391. Consulting physician establishes confirmed clinical diagnosis.
392. Format diagnosis description with standardized medical nomenclature and ICD-10 coding.
393. Insert diagnosis into `diagnoses` table with appointment_id (e.g., 'Essential Hypertension (ICD-10 I10)').
394. If multiple morbidities exist, insert secondary diagnoses into `diagnoses` (e.g., 'Type 2 Diabetes Mellitus').
395. Doctor completes outpatient discharge summary and signs off on clinical notes.
396. Transition appointment lifecycle stage to 4 (Discharged / Completed).
397. Update appointment status to 'Discharged (Normal Medicine)'.
398. Insert discharge milestone into `timeline_events` with detailed summary of completed visit.
399. Provide patient with comprehensive dietary guidelines, hydration goals, and lifestyle advice.
400. Schedule follow-up visit date (e.g., in 7 days, 14 days, or 1 month) in `appointments`.
401–430. (Preventive immunization counseling, chronic disease monitoring schedules, specialist referral letters, patient portal access instructions, satisfaction survey feedback).

STAGE 6: INPATIENT ADMISSION & ICU CRITICAL CARE LIFECYCLE (Steps 431–475)
431. Clinical determination of necessity for inpatient admission (acute myocardial infarction, respiratory failure, severe sepsis, surgical intervention, orthopedic trauma, acute pediatrics).
432. Check real-time bed availability in `beds` table (`status = 'Available'`).
433. Select appropriate ward type based on patient clinical acuity:
    - ICU (Intensive Care Unit) — for hemodynamically unstable, intubated, or high-dependency patients (`beds.type = 'ICU'`).
    - General Ward — for stable medical management, post-op observation, IV infusions (`beds.type = 'OPD'` / 'General').
    - Private / Semi-Private — for isolated recovery upon patient request.
434. Allocate specific bed number (e.g., 'ICU-01', 'ICU-02', 'OPD-101', 'OPD-102').
435. Update `beds` record: set `status = 'Occupied'` and `patient_id = <patient_id>`.
436. Update `appointments` record: set `stage = 5`, `status = 'Admitted to Bed'`, and `bed_number = <bed_number>`.
437. Log inpatient admission milestone in `timeline_events` with admitting diagnosis and bed number.
438. Notify Inpatient Charge Nurse and assign primary duty nurse (`staff` role = 'ICU Charge Nurse' / 'Senior Staff Nurse').
439. Initiate 24/7 continuous cardiac, pulse oximetry, and arterial pressure monitoring in ICU.
440. Establish peripheral intravenous (IV) line access or central venous catheter.
441. Execute inpatient medication administration record (MAR): IV antibiotics, IV fluids, anticoagulants, analgesia.
442. Perform round-the-clock nursing shifts (Morning 08:00–16:00, Evening 16:00–00:00, Night 00:00–08:00).
443. Conduct daily morning multidisciplinary consultant rounds and clinical progress charting.
444. Inpatient discharge readiness evaluation: clinical stability for 48 hours, oral feeding tolerated, normal vitals.
445. Consulting doctor issues formal Inpatient Discharge Order.
446. Clear inpatient bed in database: update `beds` record set `status = 'Available'`, `patient_id = NULL`.
447. Update `appointments` record set `status = 'Discharged from Bed'`.
448. Log bed discharge event in `timeline_events`.
449. Housekeeping sanitized bed protocol triggered (`staff` role = 'Housekeeping Supervisor').
450–475. (Inpatient medication reconciliation, discharge transfer summaries, physical therapy rehabilitation, home oxygen arrangement, step-down telemetry care, critical care weaning protocols).

STAGE 7: HOSPITAL GOVERNANCE, STAFF ATTENDANCE & AUDIT ASSURANCE (Steps 476–500+)
476. Administer hospital staff directory across all departments (`staff` table).
477. Assign staff codes (STF-101, STF-201..), roles, qualifications, and shifts.
478. Execute biometric clock-in logging at beginning of scheduled shifts.
479. Determine attendance status: 'Present' (on-time arrival), 'Late' (arrived > 15 mins after shift start), 'Absent' (no show), 'Half Day' (worked < 5 hours), 'On Leave' (approved medical/annual leave).
480. Calculate daily working hours (8.00 standard, 7.50 for late, 4.00 for half day, 0.00 for absent).
481. Record staff attendance in `staff_attendance` with strict (hospital_id, staff_id, date) uniqueness.
482. Support bulk supervisor attendance marking ('Mark all staff present').
483. Support individual staff attendance adjustments with supervisor audit notes.
484. Enforce strict multi-tenant hospital data isolation: ALL queries must filter by `hospital_id`.
485. Gate all AI-driven database mutations through the two-step confirmation engine (`ai_pending_actions`).
486. Maintain immutable chronological security audit trail of all AI actions in `ai_action_log`.
487. Enforce HIPAA/privacy standards: Never expose raw passwords or sensitive auth keys in AI outputs.
488. Enforce clean clinical communication: ZERO SQL or technical jargon in responses to healthcare staff.
489. Provide executive highlight at top of every response (> 💡 **Direct Answer:** ...).
490. Provide interactive embedded forms directly in chat for common operational tasks (Booking, Bed Admission, Attendance).
491–500+. (Disaster preparedness protocols, emergency code calls [Code Blue = Cardiac Arrest, Code Red = Fire, Code Pink = Infant Abduction], biomedical equipment preventative maintenance, infection control committee compliance, clinical quality indicators tracking, perpetual audit log preservation).
KNOWLEDGE;
}

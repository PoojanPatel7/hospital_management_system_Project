<p align="center">
  <img src="images/Logo.png" alt="Hospital Management System Logo" width="120"/>
</p>

<h1 align="center">🏥 Hospital Management System</h1>

<p align="center">
  <strong>A modern, full-featured web-based Hospital Management System built with PHP, MySQL, and Tailwind CSS</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/MariaDB-10.3+-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript"/>
  <img src="https://img.shields.io/badge/XAMPP-Apache-FB7A24?style=for-the-badge&logo=xampp&logoColor=white" alt="XAMPP"/>
</p>

---

## 📋 Table of Contents
- [Project Overview](#-project-overview)
- [Complete Feature List](#-complete-feature-list)
- [Database Schema (16 tables)](#-database-schema-16-tables)
- [Project Architecture](#-project-architecture-file-tree)
- [API Reference](#-api-reference)
- [How The Consultation Workflow Works](#-how-the-consultation-workflow-works)
- [How Backup & Restore Works](#-how-backup--restore-works)
- [How BLOB File Storage Works](#-how-blob-file-storage-works)
- [Installation & Setup](#%EF%B8%8F-installation--setup)
- [Security Features](#-security-features)
- [Benefits](#-benefits)
- [Future Enhancements](#-future-enhancements)
- [Contributing](#-contributing)
- [License](#-license)
- [Author](#-author)

---

## 🌟 PROJECT OVERVIEW
A comprehensive web-based Hospital Management System for digitizing hospital operations — patients, doctors, consultations, bed management, staff, backup/restore, and more. Built with PHP 8+, MySQL/MariaDB, Tailwind CSS 3.x, JavaScript ES6+, and Apache (XAMPP).

---

## 🚀 COMPLETE FEATURE LIST

### 1. Authentication & Multi-Hospital Support 🔐
- Session/cookie-based login with hospital code verification
- Multi-hospital support — each hospital gets a unique code
- Hospital registration with auto-generated codes
- Protected routes — all pages require authentication
- Logout confirmation modal

### 2. Dashboard (`dashboard.php`) 📊
- Real-time statistics: total patients, live queue count, today's appointments, occupied beds, active staff, total doctors
- Live heartbeat canvas animation in header
- Emergency notification banner — auto-displays when emergency patients are in the queue with critical alert cards
- Quick action buttons to navigate to key modules
- Staff summary section showing attendance overview
- Live system active indicator

### 3. Patient Management 👤
- Patient Registration (`index.php`) with floating label inputs, custom dropdowns for Gender & Blood Group
- Patient Directory (`patients.php`) with searchable grid/table view, instant multi-term search
- 10-digit phone validation, clickable tel: links
- Full CRUD operations with confirmation modals

### 4. Patient Profile (`patient_profile.php`) — DETAILED 📝
- **Full 4-step consultation workstation display** matching the `queue.php` clinical workflow:
  - Step 1: Diagnoses (ICD-style diagnosis list)
  - Step 2: Prescriptions with patient-friendly frequency translations (TID→'3 Times Daily', SOS→'As Needed', BID→'Twice Daily', QID→'4 Times Daily', OD→'Once Daily', etc.)
  - Step 3: Disposition info with state banners (Discharged, Waiting for Reports, Admitted Ward/ICU with bed numbers)
  - Step 4: Doctor's advice and clinical notes
- Ordered diagnostic tests display
- Quick stats bar (total visits, medicines prescribed, files uploaded)
- Printable prescription slip modal with `@media print` CSS
- Image preview lightbox modal
- First appointment auto-expanded
- Edit profile modal

### 5. Queue Management & Clinical Consultation (`queue.php`) ⏱️
- Live patient queue pipeline with drag-and-drop-like status management
- 4-Step Consultation Workstation:
  - Step 1 — Diagnoses: Add multiple diagnoses with descriptions
  - Step 2 — Prescriptions: Medicine name, dosage, frequency, duration, instructions
  - Step 3 — Disposition & Tests: Set patient disposition (Discharged/Admitted Ward/Admitted ICU/Waiting for Reports), order diagnostic tests, assign beds
  - Step 4 — Advice & Summary: Doctor's notes, clinical summary, finalize
- File uploads during consultation (stored as binary BLOB data in database, NOT file paths)
- Activity timeline auto-generated for each visit
- Patient dossier modal for quick-view from queue

### 6. Binary BLOB File Storage System 🗄️
- All medical files (X-rays, lab reports, scans) stored as binary BLOB data directly in the MySQL database
- No dependency on filesystem — data is safe even if files are accidentally deleted from device
- `patient_files` table stores: file_data (LONGBLOB), file_type (MIME), file_name, file_size
- Files served via `api/file.php` endpoint with proper Content-Type headers
- Supports image preview (JPEG, PNG) and PDF files

### 7. Doctor Management (`doctors.php`) 👨‍⚕️
- Doctor directory with department filtering
- Availability tracking (Available, Consulting, On Leave)
- Doctor categories and specializations
- Day-wise schedule management

### 8. Bed & Ward Management (`beds.php`) 🏨
- Visual bed map organized by wards
- Color-coded bed status (Available, Occupied, Maintenance)
- Assign/release beds directly from interface
- Ward occupancy statistics

### 9. Staff Management (`staff.php`) — DETAILED 👥
- **3 View Modes:**
  - Daily Roster: See today's attendance with check-in status per staff member
  - Staff Directory: Full list of all staff with roles, departments, contact info
  - Monthly Sheet: Day-by-day attendance matrix for entire month
- Add/Edit/Delete staff members with full profile (name, role, department, phone, email, photo)
- Staff photos stored as BLOB
- Quick Mark All Present — one-click mark all active staff as present
- Individual attendance marking (Present, Absent, Half-Day, On Leave)
- Daily summary statistics (total staff, present, absent, on leave)
- Monthly analytics with attendance percentage per staff member
- User account creation for staff members (optional)
- Staff profile detail modal with performance analytics

### 10. Database Backup System (`backup.php`) — DETAILED 💾
- **Create Backup:** Generates complete SQL dump of all 16 database tables including:
  - Schema (CREATE TABLE statements with IF NOT EXISTS)
  - Full data (INSERT statements with proper escaping)
  - Binary BLOB data encoded as hex literals
  - Wrapped in START TRANSACTION / COMMIT for atomicity
- **List Backups:** Shows all saved .sql backup files with file size and creation date
- **Download Backup:** Download any backup file directly to PC
- **Delete Backup:** Remove old backup files with confirmation
- **Open Backup Folder:** Opens the backup folder in PC's File Explorer (Windows explorer.exe integration)
- Animated success checkmark SVG after backup creation
- Backups stored in `/backups/` directory

### 11. Data Restore System (`restore.php`) — DETAILED 🔄
- **Upload SQL File:** Upload a .sql backup file from PC
- **Schema Compatibility Scanner:**
  - Scans the uploaded SQL file for CREATE TABLE statements
  - Compares against current database schema (all 16 tables)
  - Validates table names AND column fields match
  - Shows animated scanning progress — tables scanned one by one with visual animation
  - Green checkmark for compatible tables, red X for mismatched tables
  - Shows detailed field comparison if mismatch detected
  - Gives error/warning if schema doesn't match — prevents corrupted restores
- **Restore Execution:** If all tables pass compatibility check, gives option to execute the restore
- **Success Animation:** Animated checkmark SVG on successful restore
- Supports both scanning local backup files and uploaded files

### 12. Medical Records & History (`history.php`) 📂
- Global searchable table of all patient visits
- Shows date, patient name, doctor, consultation type, status, diagnoses
- Click-to-profile navigation
- Appointment history per patient with expandable accordion
- Activity timeline per visit

### 13. Appointment Booking System 📅
- Quick booking from patient directory or profile
- Department → Doctor → Time Slot selection flow
- Auto-generated appointment codes (APP-XXXX format)
- Prevents double-booking, checks doctor availability

### 14. UI/UX Design Features 🎨
- Modern glass-morphism design with rounded corners, shadows, subtle animations
- Fully responsive (desktop, tablet, mobile)
- Sidebar navigation (desktop) + hamburger drawer (mobile)
- Custom styled dropdowns for Gender and Blood Group
- Floating label animated inputs
- Toast notifications (success/error)
- Skeleton loaders during data fetch
- Confirmation modals for destructive actions
- Gradient backgrounds and card-based layouts

---

## 🗄️ DATABASE SCHEMA (16 tables)

The system uses a robust 16-table MySQL architecture:

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `hospitals` | Hospital registration | `id, name, code, created_at` |
| `patients` | Patient records | `id, name, surname, father_name, phone, age, gender, blood_group, emergency_contact_name, emergency_contact_phone, hospital_id` |
| `doctors` | Doctor profiles | `id, name, specialization, department_id, status, hospital_id` |
| `departments` | Hospital departments | `id, name, hospital_id` |
| `appointments` | Bookings | `id, patient_id, doctor_id, date, slot, type, status, symptoms, doctor_notes, bed_number, hospital_id` |
| `diagnoses` | Diagnoses per appointment | `id, appointment_id, description` |
| `prescriptions` | Medicine prescriptions | `id, appointment_id, medicine_name, dosage, frequency, duration, instructions` |
| `timeline_events` | Activity timeline | `id, appointment_id, event_time, event_description` |
| `patient_files` | Medical files as BLOB | `id, patient_id, appointment_id, file_name, file_type, file_size, file_data LONGBLOB, title, record_date` |
| `beds` | Bed/ward inventory | `id, bed_number, ward, status, patient_id` |
| `doctor_categories` | Specialization categories | - |
| `doctor_day_schedules`| Day-wise doctor schedules | - |
| `doctor_slots` | Available time slots per doctor | - |
| `staff` | Staff registry | `id, name, role, department, phone, email, photo LONGBLOB, status, hospital_id` |
| `staff_attendance` | Daily attendance records | `id, staff_id, date, status, check_in_time` |
| `system_state` | System configuration/state | - |

---

## 🏗️ PROJECT ARCHITECTURE (File Tree)

```text
Hospital Management System/
├── api/                          # REST API Endpoints
│   ├── auth.php                  # Authentication (login, register, verify)
│   ├── patients.php              # Patient CRUD operations
│   ├── doctors.php               # Doctor management
│   ├── booking.php               # Appointment booking
│   ├── consultation.php          # Consultation & clinical data (supports BLOB uploads)
│   ├── beds.php                  # Bed/ward management
│   ├── history.php               # Patient history & dossier
│   ├── queue.php                 # Queue management
│   ├── staff.php                 # Staff CRUD & attendance management
│   ├── backup.php                # Backup create/list/delete/download/restore/scan
│   └── file.php                  # Binary file serving from BLOB storage
│
├── includes/                     # Reusable PHP Components
│   ├── header.php                # Global sidebar + mobile drawer navigation
│   ├── footer.php                # Global page footer
│   ├── booking_modal.php         # Appointment booking modal
│   ├── confirm_modal.php         # Confirmation dialog component
│   ├── dossier_modal.php         # Patient dossier popup modal
│   ├── logout_modal.php          # Logout confirmation modal
│   └── book_popup.php            # Quick booking popup
│
├── images/                       # Static image assets
├── backups/                      # SQL backup files (auto-created)
├── Database/                     # Database reference files
│
├── index.php                     # Patient Registration Page
├── login.php                     # Login Page
├── register_hospital.php         # Hospital Registration Page
├── dashboard.php                 # Main Dashboard with live stats
├── patients.php                  # Patient Directory
├── patient_profile.php           # Full Patient Profile with consultation details
├── doctors.php                   # Doctor Management
├── book.php                      # Appointment Booking
├── beds.php                      # Bed & Ward Management
├── queue.php                     # Live Queue & 4-Step Consultation Workstation
├── history.php                   # Global Patient History Log
├── staff.php                     # Staff Management & Attendance
├── backup.php                    # Database Backup Management
├── restore.php                   # Data Restore with Schema Validation
├── auth.php                      # Authentication Guard
├── db.php                        # Database Connection & Auto-Schema Setup
├── setup.php                     # Initial Database Setup
└── README.md                     # This file
```

---

## 🔌 API REFERENCE

### Patients API (`api/patients.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all` | GET | — | Fetch all patients |
| `register` | POST | name, surname, father, phone, age, gender, blood_group, emergency contacts | Register new patient |
| `update` | POST | id + fields | Update patient info |
| `delete` | GET | id | Delete a patient |

### History API (`api/history.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all_patients` | GET | q (optional search) | Fetch all visit history |
| `get_dossier` | GET | patient_id | Complete patient dossier with appointments, diagnoses, prescriptions, files, timeline, symptoms, doctor_notes, tests_ordered |

### Staff API (`api/staff.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_staff` | GET | date (optional) | List staff with attendance status |
| `get_staff_details` | GET | id | Full staff profile + monthly analytics |
| `save_staff` | POST | name, role, department, phone, email, photo, create_account | Create/update staff |
| `delete_staff` | GET | id | Delete staff member |
| `mark_attendance` | POST | staff_id, date, status | Mark individual attendance |
| `quick_mark_all` | POST | date | Mark all active staff present |
| `get_daily_summary` | GET | date | Aggregate daily stats |
| `get_monthly_sheet` | GET | month, year | Full month attendance matrix |

### Backup API (`api/backup.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `create_backup` | POST | — | Generate SQL dump of entire database |
| `list_backups` | GET | — | List all backup files |
| `download` | GET | file | Download backup .sql file |
| `delete_backup` | POST | file | Delete a backup file |
| `open_folder` | GET | — | Open backup directory in Windows File Explorer |
| `scan_sql_file` | POST | file | Scan local backup for schema compatibility |
| `upload_sql_file` | POST | file (multipart) | Upload + scan external SQL file |
| `restore_backup` | POST | file | Execute database restore from validated backup |

### File API (`api/file.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| — | GET | id | Serve binary file from BLOB storage with proper MIME type headers |

### Consultation API (`api/consultation.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_consultation` | GET | appointment_id | Get diagnoses, medicines, files, tests for appointment |
| `save_with_files` | POST | multipart form with diagnoses, prescriptions, disposition, tests, file uploads (BLOB) | Save complete consultation |
| `save` | POST | JSON body | Save consultation (without files) |

### Booking API (`api/booking.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_slots` | GET | doctor_id, date | Get available time slots |
| `book` | POST | patient_id, doctor_id, date, slot, type | Book appointment |

### Authentication API (`api/auth.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `login` | POST | username, password, hospital_code | Authenticate |
| `register` | POST | hospital_name, username, password | Register hospital |
| `verify` | GET | — | Verify session |
| `logout` | GET | — | Destroy session |

### Beds API (`api/beds.php`)
| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all` | GET | — | Fetch all beds |
| `assign` | POST | bed_id, patient_id | Assign patient to bed |
| `release` | POST | bed_id | Release bed |

---

## ⚕️ HOW THE CONSULTATION WORKFLOW WORKS
The 4-step clinical workflow provides an intuitive process for doctors:
1. Patient enters the queue from an appointment booking.
2. Doctor opens the consultation workstation from the queue.
3. **Step 1 — Diagnoses:** Doctor adds one or more diagnoses.
4. **Step 2 — Prescriptions:** Doctor prescribes medicines with dosage, frequency, duration.
5. **Step 3 — Disposition:** Doctor sets patient outcome (discharge, admit to ward/ICU, waiting for reports) and orders diagnostic tests.
6. **Step 4 — Summary:** Doctor adds clinical notes and finalizes the consultation.
7. All data including uploaded files (as BLOB) is saved via the consultation API.
8. Timeline events are auto-generated throughout the process.
9. All consultation data is viewable later in `patient_profile.php` and via the dossier modal.

---

## 💾 HOW BACKUP & RESTORE WORKS
1. **Backup:** Generates a complete SQL dump with `CREATE TABLE` + `INSERT` statements for all 16 tables, including BLOB hex encoding.
2. The file is saved in the `/backups/` directory with a timestamp filename.
3. **Restore:** Upload or select a backup SQL file.
4. **Schema Scanner:** Validates every table and every column matches the current schema.
5. Visual animation shows scanning progress table by table.
6. If compatible, the restore replaces all data using a `TRANSACTION` for safety.
7. If incompatible, shows a detailed error with which tables/fields don't match.

---

## 🗄️ HOW BLOB FILE STORAGE WORKS
1. During consultation, files are uploaded via a multipart form.
2. PHP reads the file content with `file_get_contents()` and stores it as a `LONGBLOB` in the `patient_files` table.
3. File metadata (name, MIME type, size) is stored alongside the binary data.
4. Files are served via `api/file.php?id=X` which reads the BLOB and sends it with the proper `Content-Type` header.
5. This approach means data is safe even if the device filesystem is corrupted or files are manually deleted.

---

## ⚙️ INSTALLATION & SETUP

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 8.0+
- MySQL 5.7+ or MariaDB

### Steps:
1. **Clone:** `git clone https://github.com/PoojanPatel7/hospital_management_system_Project.git`
2. **Move:** Copy to `C:\xampp\htdocs\Hospital Management System`
3. **Start:** Launch Apache and MySQL in XAMPP.
4. **Database:** The database auto-creates! Just visit the app. `db.php` auto-creates all tables and runs migrations.
5. *(Optional)* Use `setup.php` for manual setup, or import `schema.sql` via phpMyAdmin.
6. **Configure:** Check `db.php` (`host=localhost, user=root, pass='', db=hospital_db`).
7. **Run:** Visit `http://localhost/Hospital%20Management%20System/login.php`
8. Register a hospital first, then login.

---

## 🔒 SECURITY FEATURES
- **Session-based auth** with hospital-scoped access
- **Route protection** via `auth.php` guard
- **Prepared statements** for SQL injection prevention
- `htmlspecialchars()` for XSS protection
- 10-digit phone validation
- Confirmation modals for destructive actions
- Password hashing
- **Binary file storage** in DB (no filesystem exposure)

---

## 💡 BENEFITS
- **Seamless Digitization:** Completely paperless hospital environment.
- **Robust Data Safety:** Reliable BLOB storage and atomic backup/restore guarantees data integrity.
- **Rapid Decision-Making:** Live queue, real-time dashboards, and unified dossiers improve hospital response times.
- **Modern User Experience:** Smooth, visually appealing interface ensuring low learning curves for medical staff.

---

## 🔮 FUTURE ENHANCEMENTS
- [ ] Comprehensive Reporting & Analytics Dashboard
- [ ] Automated SMS/Email Notifications for Appointments
- [ ] Integration with External Laboratory Information Systems
- [ ] Dedicated Billing & Insurance Module
- [ ] Role-Based Access Control (Admin, Doctor, Nurse, Receptionist)
- [ ] Pharmacy Inventory Management Module
- [ ] Telemedicine Video Consultation Integration

---

## 🤝 CONTRIBUTING
Contributions are highly welcome!
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

## 📝 LICENSE
This project is open-source and available under the [MIT License](LICENSE).

---

## 👨‍💻 AUTHOR
**Poojan Patel**
- GitHub: [@PoojanPatel7](https://github.com/PoojanPatel7)

---

<p align="center">
  <strong>⭐ If you found this project useful, please give it a star on GitHub! ⭐</strong>
</p>

<p align="center">
  Built with ❤️ for better healthcare management
</p>

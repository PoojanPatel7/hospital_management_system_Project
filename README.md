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
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript"/>
  <img src="https://img.shields.io/badge/XAMPP-Apache-FB7A24?style=for-the-badge&logo=xampp&logoColor=white" alt="XAMPP"/>
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Screenshots](#-screenshots)
- [Technology Stack](#-technology-stack)
- [Project Architecture](#-project-architecture)
- [Installation & Setup](#-installation--setup)
- [Database Schema](#-database-schema)
- [Module Documentation](#-module-documentation)
- [API Reference](#-api-reference)
- [Security Features](#-security-features)
- [Benefits](#-benefits)
- [Future Enhancements](#-future-enhancements)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Overview

The **Hospital Management System** is a comprehensive web application designed to digitize and streamline hospital operations. It provides a centralized platform for managing patients, doctors, appointments, consultations, bed assignments, medical history, prescriptions, and clinical files — all through an elegant, modern user interface.

This system eliminates paper-based processes and provides real-time data access to hospital staff, enabling faster decision-making, better patient care, and efficient resource utilization.

---

## 🚀 Key Features

### 👤 Patient Management
- **Patient Registration** — Register new patients with full demographic details including name, age, gender, blood group, phone number, father's name, and emergency contact information
- **Patient Directory** — Searchable, filterable grid/table view of all registered patients with instant multi-term search
- **Patient Profile** — Dedicated full-profile page for each patient showing demographics, contact info, appointment history, medical files, prescriptions, diagnoses, and activity timeline
- **Edit & Delete** — Full CRUD operations on patient records with confirmation modals and validation
- **10-Digit Phone Validation** — Strict validation enforced on all phone number fields across registration and edit forms
- **Direct Call Integration** — All phone numbers displayed as clickable `tel:` links for instant dialing from mobile devices

### 👨‍⚕️ Doctor Management
- **Doctor Directory** — Full list of all hospital doctors with their specializations, departments, and availability status
- **Department-Wise Filtering** — Filter doctors by department/specialization
- **Availability Tracking** — Real-time tracking of doctor availability status (Available, Consulting, On Leave)

### 📅 Appointment & Booking System
- **Quick Booking** — Book appointments directly from the patient directory or patient profile
- **Doctor Selection** — Choose from available doctors filtered by department
- **Time Slot Management** — Select available consultation time slots
- **Appointment Codes** — Auto-generated unique appointment codes (APP-XXXX format) for easy tracking

### 🏨 Bed & Ward Management
- **Visual Bed Map** — Interactive visual representation of hospital beds organized by wards
- **Real-Time Bed Status** — Color-coded bed availability (Available, Occupied, Maintenance)
- **Admission & Discharge** — Assign and release beds directly from the bed management interface
- **Ward Statistics** — Dashboard showing occupancy rates per ward

### 📊 Dashboard & Analytics
- **Real-Time Statistics** — Live counts of total patients, active doctors, occupied beds, and today's appointments
- **Quick Actions** — One-click access to register patients, book appointments, and manage beds
- **Status Overview** — At-a-glance view of hospital operational status

### 📂 Medical Records & History
- **Global Patient History** — Master log of all patient visits, admissions, checkups, and discharges in a searchable table
- **Appointment History** — Detailed chronological history of each patient's visits with expandable accordion details
- **Year Display** — Appointment dates show full year for easy historical reference
- **Activity Timeline** — Step-by-step timeline of events during each visit (check-in, consultation, discharge)
- **Click-to-Profile Navigation** — Click any history record to navigate directly to that patient's full profile

### 💊 Clinical Features
- **Diagnoses Tracking** — Record and display multiple diagnoses per appointment
- **Prescription Management** — Full prescription details including medicine name, dosage, frequency, and doctor notes
- **Doctor's Notes** — Free-text clinical notes attached to each consultation
- **File & Scan Uploads** — Upload and manage medical files, lab reports, X-rays, and scans per appointment
- **Medical Files Gallery** — Dedicated tab showing all uploaded files with image previews and PDF icons

### 🔐 Authentication & Security
- **Secure Login System** — Cookie/session-based authentication with hospital code verification
- **Hospital Registration** — Multi-hospital support with unique hospital code generation
- **Protected Routes** — All pages require authentication; unauthorized access is redirected to login
- **Logout Confirmation** — Beautiful modal confirmation before logout with session cleanup

### 🎨 UI/UX Design
- **Modern Glass-Morphism Design** — Clean, professional UI with rounded corners, shadows, and subtle animations
- **Fully Responsive** — Optimized for desktop, tablet, and mobile devices
- **Custom Dropdowns** — Beautifully designed custom dropdown selectors for Gender and Blood Group with colored icons
- **Floating Labels** — Animated floating label inputs that rise when focused
- **Toast Notifications** — Elegant slide-in toast messages for success/error feedback
- **Skeleton Loaders** — Smooth loading animations while data is being fetched
- **Confirmation Modals** — Professional confirmation dialogs for all destructive or important actions

---

## 🛠 Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) | UI structure, styling, and interactivity |
| **CSS Framework** | Tailwind CSS 3.x (CDN) | Utility-first responsive design |
| **Icons** | Font Awesome 6 | Professional icon library |
| **Backend** | PHP 8.0+ | Server-side logic and API endpoints |
| **Database** | MySQL 5.7+ / MariaDB | Relational data storage |
| **Server** | Apache (XAMPP) | Local development web server |
| **Architecture** | REST API + SPA-like | Async data loading via `fetch()` API |

---

## 🏗 Project Architecture

```
Hospital Management System/
│
├── api/                          # REST API Endpoints
│   ├── auth.php                  # Authentication (login, register, verify)
│   ├── patients.php              # Patient CRUD operations
│   ├── doctors.php               # Doctor management
│   ├── booking.php               # Appointment booking
│   ├── consultation.php          # Consultation & clinical data
│   ├── beds.php                  # Bed/ward management
│   ├── history.php               # Patient history & dossier
│   └── queue.php                 # Queue management
│
├── includes/                     # Reusable PHP Components
│   ├── header.php                # Global page header with navigation
│   ├── footer.php                # Global page footer with modals
│   ├── booking_modal.php         # Appointment booking modal
│   ├── confirm_modal.php         # Confirmation dialog component
│   ├── dossier_modal.php         # Patient dossier modal
│   ├── logout_modal.php          # Logout confirmation modal
│   └── book_popup.php            # Quick booking popup
│
├── images/                       # Static Image Assets
│   ├── Logo.png                  # Hospital system logo
│   ├── Login.png                 # Login page illustration
│   ├── Patient Registration.jpg  # Registration page illustration
│   ├── Patient Directory.jpg     # Directory page illustration
│   ├── Book Appointment.jpg      # Booking illustration
│   └── ...                       # Other status/ward images
│
├── uploads/                      # User-uploaded medical files
├── Database/                     # Database related files
├── video/                        # Video assets
│
├── index.php                     # Patient Registration Page
├── login.php                     # Login Page
├── register_hospital.php         # Hospital Registration Page
├── dashboard.php                 # Main Dashboard
├── patients.php                  # Patient Directory
├── patient_profile.php           # Individual Patient Profile
├── doctors.php                   # Doctor Management
├── book.php                      # Appointment Booking
├── beds.php                      # Bed & Ward Management
├── queue.php                     # Patient Queue
├── history.php                   # Global Patient History Log
├── auth.php                      # Authentication Guard
├── db.php                        # Database Connection
├── setup.php                     # Initial Database Setup
├── schema.sql                    # Database Schema
├── hospital_db_dump.sql          # Full Database Dump
├── .htaccess                     # Apache URL Rewriting
└── README.md                     # This file
```

---

## ⚙️ Installation & Setup

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP) **or** any AMP stack
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web browser (Chrome, Firefox, Edge recommended)

### Step-by-Step Installation

#### 1️⃣ Clone the Repository

```bash
git clone https://github.com/PoojanPatel7/hospital_management_system_Project.git
```

#### 2️⃣ Move to Web Server Directory

Copy/move the project folder to your web server's root directory:

```bash
# For XAMPP on Windows:
Move the folder to: C:\xampp\htdocs\Hospital Management System

# For XAMPP on Mac:
Move the folder to: /Applications/XAMPP/htdocs/Hospital Management System

# For Linux (LAMP):
Move the folder to: /var/www/html/Hospital Management System
```

#### 3️⃣ Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (Web Server)
3. Start **MySQL** (Database Server)

#### 4️⃣ Create the Database

**Option A — Automatic Setup:**
1. Open your browser and navigate to: `http://localhost/Hospital%20Management%20System/setup.php`
2. The setup script will automatically create the database and tables

**Option B — Manual Setup via phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Create a new database named `hospital_db`
3. Import the `schema.sql` file (or `hospital_db_dump.sql` for sample data)

#### 5️⃣ Configure Database Connection

Open `db.php` and verify the connection settings:

```php
$host = "localhost";
$username = "root";
$password = "";           // Default XAMPP has no password
$database = "hospital_db";
```

#### 6️⃣ Access the Application

Open your browser and navigate to:

```
http://localhost/Hospital%20Management%20System/login.php
```

#### 7️⃣ First-Time Setup

1. Click **"Register Hospital"** to create a new hospital account
2. Enter your hospital name and create login credentials
3. You'll receive a unique **Hospital Code** — save this!
4. Log in with your credentials and hospital code
5. Start by registering patients from the **Patient Registration** page

---

## 🗄 Database Schema

The system uses a relational MySQL database with the following core tables:

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `hospitals` | Hospital registration data | id, name, code, created_at |
| `users` | Login credentials per hospital | id, username, password, hospital_id |
| `patients` | Patient demographic records | id, name, surname, father_name, phone, blood_group, demographics, emergency_contact_name, emergency_contact_phone |
| `doctors` | Doctor profiles | id, name, specialization, department_id, status |
| `appointments` | Appointment bookings | id, patient_id, doctor_id, date, slot, type, status, bed_number |
| `diagnoses` | Diagnoses per appointment | id, appointment_id, description |
| `prescriptions` | Medicine prescriptions | id, appointment_id, medicine_name, dosage, frequency, duration, instructions |
| `timeline_events` | Activity timeline entries | id, appointment_id, event_time, event_description |
| `patient_files` | Uploaded medical files | id, patient_id, appointment_id, title, file_path, record_date |
| `beds` | Bed/ward inventory | id, bed_number, ward, status, patient_id |

### Entity Relationship

```
hospitals ─┐
           ├── users
           │
patients ──┤
           ├── appointments ──┬── diagnoses
           │                  ├── prescriptions
           │                  ├── timeline_events
           │                  └── patient_files
           │
doctors ───┘
           │
beds ──────┘
```

---

## 📖 Module Documentation

### 1. Patient Registration (`index.php`)
- **Design:** Two-column split layout with registration illustration on the left
- **Features:** Floating label inputs, custom styled dropdowns for Gender & Blood Group, 10-digit phone validation
- **Flow:** Fill form → Preview confirmation modal → Submit → Patient created with auto-generated MRN ID

### 2. Patient Directory (`patients.php`)
- **Design:** Hero header with `Patient Directory.jpg` image, search bar, Grid/Table toggle
- **Features:** Multi-term fuzzy search across all patient fields, direct phone call links
- **Grid View:** Clean card layout with avatar, demographics, contact info, and action buttons
- **Table View:** Compact tabular view with sorting and quick actions

### 3. Patient Profile (`patient_profile.php`)
- **Design:** Two-column layout — left sidebar (profile card + latest visit) and right content area (tabs)
- **Profile Card:** Separate boxes for Age, Gender, Blood Group; clickable phone numbers; emergency contact section
- **Appointments Tab:** Expandable accordion cards showing full visit details including timeline, diagnoses, prescriptions, doctor's notes, and uploaded files
- **Files Tab:** Gallery grid of all uploaded medical files with image previews

### 4. Dashboard (`dashboard.php`)
- **Design:** Statistics cards at top, quick action buttons, and recent activity
- **Stats:** Total patients, active doctors, occupied beds, today's appointments
- **Quick Actions:** Register patient, book appointment, manage beds

### 5. Doctor Management (`doctors.php`)
- **Features:** Doctor listing with department filtering, availability status tracking
- **Status Colors:** Green (Available), Yellow (Consulting), Red (On Leave)

### 6. Bed Management (`beds.php`)
- **Design:** Visual bed map organized by wards
- **Features:** Assign/release beds, color-coded status, occupancy statistics

### 7. Global History (`history.php`)
- **Design:** Full-width searchable table of all patient visit history
- **Features:** Shows date, patient name, doctor, consultation type, status, bed, diagnoses
- **Navigation:** Click any row to open that patient's full profile

### 8. Appointment Booking (`book.php`)
- **Features:** Select patient, choose department, pick doctor, select time slot
- **Validation:** Prevents double-booking, checks doctor availability

---

## 🔌 API Reference

All API endpoints are located in the `api/` directory and return JSON responses.

### Patients API (`api/patients.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all` | GET | — | Fetch all patients |
| `register` | POST | name, surname, father, phone, age, gender, blood_group, emergency_contact_name, emergency_contact_phone | Register new patient |
| `update` | POST | id, name, surname, father, phone, age, gender, blood_group, emergency_contact_name, emergency_contact_phone | Update patient info |
| `delete` | GET | id | Delete a patient |

### History API (`api/history.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all_patients` | GET | q (optional search) | Fetch all visit history records |
| `get_dossier` | GET | patient_id | Fetch complete patient dossier with appointments, diagnoses, prescriptions, files, timeline |

### Booking API (`api/booking.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_slots` | GET | doctor_id, date | Get available time slots |
| `book` | POST | patient_id, doctor_id, date, slot, type | Book an appointment |

### Doctors API (`api/doctors.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all` | GET | — | Fetch all doctors |
| `get_by_dept` | GET | dept | Filter doctors by department |

### Beds API (`api/beds.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `get_all` | GET | — | Fetch all beds with status |
| `assign` | POST | bed_id, patient_id | Assign patient to bed |
| `release` | POST | bed_id | Release/discharge bed |

### Authentication API (`api/auth.php`)

| Action | Method | Parameters | Description |
|--------|--------|-----------|-------------|
| `login` | POST | username, password, hospital_code | Authenticate user |
| `register` | POST | hospital_name, username, password | Register new hospital |
| `verify` | GET | — | Verify current session |
| `logout` | GET | — | Destroy session and logout |

---

## 🔒 Security Features

- **Session-Based Authentication** — Secure PHP sessions with hospital-scoped access
- **Route Protection** — `auth.php` guard included on every page; redirects unauthorized users to login
- **Input Validation** — Server-side and client-side validation on all forms
- **Phone Number Validation** — Pattern-based 10-digit enforcement with `maxlength` restrictions
- **SQL Injection Prevention** — Prepared statements used for database queries
- **XSS Protection** — `htmlspecialchars()` used when rendering user-supplied data
- **Confirmation Modals** — All destructive actions (delete, edit) require explicit user confirmation
- **Password Security** — Passwords hashed before storage

---

## 💡 Benefits

### For Hospital Administration
- 📊 **Real-time analytics** on patient flow, bed occupancy, and doctor availability
- 📋 **Paperless operations** — No more physical patient files or appointment books
- 🔍 **Instant search** — Find any patient, doctor, or record in milliseconds
- 📈 **Scalable** — Supports unlimited patients, doctors, and appointments

### For Doctors & Medical Staff
- 📂 **Complete patient history** at a glance — diagnoses, prescriptions, clinical notes
- 🗓 **Organized appointments** — Clear view of daily schedule and patient queue
- 📎 **Medical file access** — Quick access to lab reports, X-rays, and scans
- ⏱ **Activity timeline** — Track exactly what happened during each patient visit

### For Patient Care
- 📱 **Direct call integration** — Call patients or emergency contacts with one tap
- 🩸 **Blood group visibility** — Clearly displayed across all views for emergency access
- 🚑 **Emergency contact info** — Always accessible from the patient profile and directory cards
- 🔄 **Complete medical history** — Full longitudinal view of all visits, treatments, and outcomes

### Technical Benefits
- 🎨 **Modern UI/UX** — Beautiful, intuitive interface that requires minimal training
- 📱 **Responsive design** — Works on any device (desktop, tablet, mobile)
- ⚡ **Fast performance** — Async data loading with no page reloads
- 🔧 **Easy maintenance** — Clean code architecture with separated concerns (API, UI, includes)
- 🌐 **No external dependencies** — Runs entirely on a standard LAMP/XAMPP stack

---

## 🔮 Future Enhancements

- [ ] **Reporting & Analytics Dashboard** — Charts, graphs, and exportable reports
- [ ] **SMS/Email Notifications** — Appointment reminders and discharge summaries
- [ ] **Lab Integration** — Direct integration with laboratory information systems
- [ ] **Billing Module** — Invoice generation, payment tracking, and insurance claims
- [ ] **Role-Based Access Control** — Separate roles for admin, doctor, nurse, receptionist
- [ ] **Multi-Language Support** — Localization for regional hospital requirements
- [ ] **Dark Mode** — System-wide dark theme toggle
- [ ] **Pharmacy Module** — Medicine inventory tracking and dispensing
- [ ] **Telemedicine** — Video consultation integration
- [ ] **Mobile App** — Native Android/iOS companion app

---

## 🤝 Contributing

Contributions are welcome! If you'd like to contribute to this project:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

Please make sure your code follows the existing coding style and includes appropriate comments.

---

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

---

## 👨‍💻 Author

**Poojan Patel**
- GitHub: [@PoojanPatel7](https://github.com/PoojanPatel7)

---

<p align="center">
  <strong>⭐ If you found this project useful, please give it a star on GitHub! ⭐</strong>
</p>

<p align="center">
  Built with ❤️ for better healthcare management
</p>

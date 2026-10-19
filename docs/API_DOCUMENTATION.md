# 🌐 Hospital Management System — Comprehensive REST API Reference

> **API Documentation Version:** 3.0 Enterprise  
> **Base URL:** `http://<server-ip>/Hospital%20Management%20System/api/`  
> **Data Format:** JSON (`Content-Type: application/json; charset=utf-8`)  
> **Authentication:** PHP Session Cookie (`PHPSESSID`) or API Key Header

---

## 📋 Table of API Endpoints

| Category | Endpoint | Methods | Key Actions |
|---|---|:---:|---|
| **Authentication** | `api/auth.php` | POST | Login, Logout, Session Verification |
| **Bed Management** | `api/beds.php` | GET, POST | `get_all`, `admit`, `discharge`, `transfer`, `add_observation`, `get_observations` |
| **Face Biometrics** | `api/staff_face.php` | GET, POST | `get_descriptors`, `register`, `record_punch`, `today_roster` |
| **AI Engine** | `api/chatbot.php` | POST | Natural Language Query, Clinical Dossier, SQL Execution |
| **AI Training** | `api/ai_train_api.php` | GET, POST | `status`, `start_training`, `stop_training`, `generate_dataset`, `ollama_status` |
| **QR Code Engine** | `api/qr.php` | GET | `lookup`, `image`, `generate` |
| **Dashboard Analytics**| `api/dashboard.php` | GET | Real-time counters, revenue charts, bed occupancy rates |
| **Staff & RBAC** | `api/staff.php` | GET, POST | Staff CRUD, Permissions Update, Password Reset |
| **Patient Directory** | `api/patients.php` | GET, POST | Patient Search, Registration, Profile Updates |
| **Doctors & Slots** | `api/doctors.php`, `api/doctor_slots.php`| GET, POST | Doctor Profiles, Weekly Slot Matrices |
| **Billing & Charges** | `api/charges.php` | GET, POST | Invoicing, Itemized Bills, Payment Processing |
| **File Management** | `api/file.php`, `api/files.php` | GET, POST | BLOB Image Upload, Binary Stream Download, Audit |

---

## 1. Bed Management API (`api/beds.php`)

### 1.1 Get All Beds & Occupancy Status
- **Method:** `GET`
- **Query Param:** `?action=get_all`
- **Response Example:**
```json
{
  "status": "success",
  "summary": {
    "total": 45,
    "occupied": 28,
    "available": 17,
    "occupancy_rate": 62.2
  },
  "beds": [
    {
      "id": "bed-101",
      "bed_number": "ICU-01",
      "type": "ICU",
      "wing": "Critical Care Floor, 3rd Floor",
      "status": "Occupied",
      "patient_id": "CP-2026-002",
      "patient_name": "Dev Patel",
      "age": "42",
      "gender": "Male",
      "blood_group": "B+",
      "vital_bp": "124/82",
      "vital_pulse": "76",
      "vital_spo2": "98%",
      "vital_temp": "98.6 F",
      "total_observations": 14,
      "latest_monitor_image": "api/file.php?id=30"
    }
  ]
}
```

### 1.2 Add Hourly Bed Observation & Machine Monitor Photo
- **Method:** `POST` (Multipart / Form-Data or JSON)
- **Parameters:**
  - `action`: `add_observation`
  - `bed_number`: `"ICU-01"`
  - `vital_bp`: `"120/80"`
  - `vital_pulse`: `"72"`
  - `vital_spo2`: `"99%"`
  - `vital_temp`: `"98.4 F"`
  - `vital_resp`: `"16"`
  - `notes`: `"Patient stable, breathing comfortably on room air."`
  - `monitor_image`: `[Binary Image File / Base64]` *(Optional)*
- **Response Example:**
```json
{
  "status": "success",
  "message": "Hourly vital signs and monitor snapshot successfully recorded.",
  "observation_id": 84,
  "file_id": 35
}
```

### 1.3 Admit Patient to Bed
- **Method:** `POST` (JSON)
- **Payload:**
```json
{
  "action": "admit",
  "patient_id": "CP-2026-005",
  "bed_number": "OPD-101",
  "doctor_id": 3,
  "reason": "Observation following acute asthma exacerbation",
  "vital_bp": "130/85",
  "vital_pulse": "88",
  "vital_spo2": "95%",
  "vital_temp": "99.1 F"
}
```

### 1.4 Discharge Patient from Bed
- **Method:** `POST` (JSON)
- **Payload:**
```json
{
  "action": "discharge",
  "number": "OPD-101",
  "notes": "Patient recovered. Vitals stable. Follow up in 5 days."
}
```

---

## 2. AI Face Attendance API (`api/staff_face.php`)

### 2.1 Get All Enrolled Facial Descriptors
- **Method:** `GET`
- **Query Param:** `?action=get_descriptors`
- **Response Example:**
```json
{
  "status": "success",
  "count": 48,
  "staff": [
    {
      "id": 1,
      "name": "Dr. Ambarish Kulkarni",
      "role": "Doctor",
      "department": "General Medicine",
      "descriptor": [-0.1423, 0.0891, 0.2015, -0.0543, ...]
    }
  ]
}
```

### 2.2 Record Biometric Face Punch
- **Method:** `POST` (JSON)
- **Payload:**
```json
{
  "action": "record_punch",
  "staff_id": 1,
  "confidence": 96.4
}
```
- **Response Example:**
```json
{
  "status": "success",
  "staff_name": "Dr. Ambarish Kulkarni",
  "punch_type": "Check-In",
  "time": "08:02:15 AM",
  "attendance_status": "Present",
  "message": "Welcome, Dr. Ambarish Kulkarni. Check-in recorded at 08:02 AM."
}
```

---

## 3. BHOOMA AI Chatbot API (`api/chatbot.php`)

### 3.1 Conversational Query & Synthesis
- **Method:** `POST` (JSON)
- **Payload:**
```json
{
  "message": "who is patient CP-2026-005 and what is his admission status?",
  "history": []
}
```
- **Response Example:**
```json
{
  "status": "success",
  "reply": "Here is the medical record for **Raj Patel** (MRN: `CP-2026-005`):\n\n• **Demographics:** Male, 34 years, Blood Group: AB+\n• **Contact:** Phone: 7892345678\n• **Admission Status:** Currently Admitted in Bed **OPD-101** (North Wing, 2nd Floor)\n• **Medical Files:** 6 file(s) attached — [View Latest File](api/file.php?id=12)",
  "intent": "PATIENT_LOOKUP",
  "sql_executed": "SELECT * FROM patients WHERE id = 'CP-2026-005' AND hospital_id = 1",
  "has_data_table": true,
  "data": [
    {
      "id": "CP-2026-005",
      "name": "Raj",
      "surname": "Patel",
      "bed_number": "OPD-101"
    }
  ]
}
```

---

## 4. Patient QR Code API (`api/qr.php`)

### 4.1 Lookup Patient by QR Token
- **Method:** `GET`
- **Query Param:** `?action=lookup&token=6a12b34c-5678-90ab-cdef-1234567890ab`
- **Response Example:**
```json
{
  "status": "success",
  "patient": {
    "id": "CP-2026-005",
    "name": "Raj",
    "surname": "Patel",
    "blood_group": "AB+",
    "age": 34,
    "gender": "Male",
    "phone": "7892345678",
    "active_bed": "OPD-101"
  }
}
```

### 4.2 Stream Offline High-Res QR Code Image
- **Method:** `GET`
- **Query Param:** `?action=image&token=6a12b34c-5678-90ab-cdef-1234567890ab`
- **Response:** Binary image stream (`Content-Type: image/png` or `image/svg+xml`).

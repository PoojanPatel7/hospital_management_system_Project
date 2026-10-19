# 🔒 Staff Permissions, RBAC & Hospital Cybersecurity Guide

> **Module Documentation:** Role-Based Access Control, Granular Permissions & System Security  
> **Source Files:** `staff_permissions.php`, `includes/permissions_modal.php`, `js/permissions.js`, `api/staff.php`, `auth.php`  
> **Target Audience:** Information Security Officers (CISO), System Administrators, Compliance Auditors

---

## 📋 Table of Contents
1. [Security Architecture & Zero-Trust Principles](#1-security-architecture--zero-trust-principles)
2. [The 11+ Granular Staff Permission Keys](#2-the-11-granular-staff-permission-keys)
3. [Interactive Permission Management Modal](#3-interactive-permission-management-modal)
4. [Session Protection & Authentication Guards](#4-session-protection--authentication-guards)
5. [SQL Injection Prevention & Data Sanitization](#5-sql-injection-prevention--data-sanitization)
6. [Binary BLOB Medical File Security](#6-binary-blob-medical-file-security)
7. [Comprehensive Audit Trail & Event Logging](#7-comprehensive-audit-trail--event-logging)
8. [Multi-Hospital Tenant Isolation](#8-multi-hospital-tenant-isolation)

---

## 1. Security Architecture & Zero-Trust Principles

Hospitals handle the most sensitive data in society: private health histories, financial transactions, and medication orders. Compromised credentials or unauthorized internal privilege escalation can lead to catastrophic medical errors or regulatory penalties.

The HMS implements a **Granular Zero-Trust Access Architecture**:
- Authenticated sessions are strictly scoped to the user's assigned hospital tenant (`hospital_id`).
- Staff capabilities are governed not merely by a broad "Role" label, but by **individual, togglable permission keys**.

---

## 2. The 11+ Granular Staff Permission Keys

Each staff account has an isolated JSON permissions array stored in `staff.permissions`. Administrators can grant or revoke any of the following 11+ capabilities independently:

| Permission Key | Display Name | Functional Capability Controlled |
|---|---|---|
| `can_view_patients` | View Patients | Browse patient directories, search demographics, view health passes |
| `can_edit_patients` | Edit / Create Patients | Register new patients, edit addresses, update emergency contacts |
| `can_delete_patients` | Delete Patients | Permanently remove patient records (restricted to Super Admins) |
| `can_view_queue` | Access Queue | View the 4-stage live patient Kanban pipeline |
| `can_manage_queue` | Manage Queue | Check in patients, change token stages, call tokens to consultation |
| `can_consult_patients` | Doctor Consultation | Open consultation modals, record clinical diagnoses, issue prescriptions |
| `can_manage_beds` | Bed Ward Management | Admit patients to beds, log hourly vitals, capture monitor photos, discharge |
| `can_view_billing` | View Billing & Charges | Review fee invoices, patient bills, daily collection totals |
| `can_manage_billing` | Manage Billing | Add bill items, apply discounts, issue payment receipts |
| `can_manage_staff` | Staff Management | Create staff profiles, assign roles, manage passwords, enroll face biometrics |
| `can_access_ai_admin` | AI & System Logs | Access AI training dashboard, view SQL queries, fine-tune models |

---

## 3. Interactive Permission Management Modal

Administrators configure permissions via the **Staff Permissions Modal** (`includes/permissions_modal.php` & `js/permissions.js`):
- **Role Presets:** Fast-click presets for *Doctor*, *Nurse*, *Receptionist*, *Pharmacist*, *Accountant*, and *Administrator*.
- **Fine-Grained Checkboxes:** Toggle individual keys with instant real-time saving via `api/staff.php?action=update_permissions`.
- **Active State Badges:** Visual indicator pills displaying how many permissions each staff member currently holds.

---

## 4. Session Protection & Authentication Guards

### Hardened Session Management (`auth.php`):
- **HTTP-Only & Secure Cookies:** Session cookies are flagged `HttpOnly` and `SameSite=Strict` to prevent Cross-Site Scripting (XSS) session theft.
- **Session Hijacking Defense:** Validates client `User-Agent` and IP subnet on every sensitive request.
- **Inactivity Timeout:** Automated session expiration after 30 minutes of idle time.

---

## 5. SQL Injection Prevention & Data Sanitization

- **Parameterized Prepared Statements:** 100% of user inputs across all REST endpoints (`api/*.php`) are executed via `mysqli_prepare()` or parameterized bound queries (`$stmt->bind_param(...)`).
- **AI Query Isolation:** BHOOMA AI SQL queries pass through `ai/sql_sanitizer.php`, which parses Abstract Syntax Trees (AST) and blocks any statement containing `INSERT`, `UPDATE`, `DELETE`, `DROP`, or `UNION`.

---

## 6. Binary BLOB Medical File Security

Instead of storing patient files and monitor photos in public server directories where misconfigured web servers could expose them, the HMS stores all media as **Binary BLOBs (`LONGBLOB`) inside MariaDB**:
- Files cannot be downloaded without authenticating through `api/file.php`.
- Each request checks the user's hospital tenancy and permission keys before streaming the binary data with appropriate `Content-Type` headers.
- Immune to directory traversal attacks (`../../uploads/secret.pdf`).

---

## 7. Comprehensive Audit Trail & Event Logging

Every clinical action is permanently logged in `timeline_events`:
- Timestamp (to the second).
- Patient MRN ID and Appointment ID.
- Staff Member ID and Full Name.
- Detailed Event Description (e.g., *"Admitted to Bed ICU-04 by Nurse Sunita. Vitals: BP 130/85, SpO2 98%."*).
- System logs cannot be overwritten or deleted from the frontend interface.

---

## 8. Multi-Hospital Tenant Isolation

For healthcare chains or multi-campus hospital groups:
- Every table includes a `hospital_id` foreign key.
- Queries automatically inject `WHERE hospital_id = ?` based on the authenticated session.
- Staff from Hospital A can never access, query, or view patient records or financial ledgers belonging to Hospital B.

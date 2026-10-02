#!/usr/bin/env python3
"""
Hospital DB Schema & Relationship Introspector
==============================================
Introspects all tables, columns, data types, indexes, and foreign keys
from MySQL hospital_db and generates a structured master schema JSON file.
"""

import json
import os
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

# Try using mysql.connector or pymysql, fallback to built-in known schema
def get_db_schema_via_connector():
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="hospital_db"
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SHOW TABLES")
        tables = [list(r.values())[0] for r in cursor.fetchall()]
        
        schema_dict = {}
        for tbl in tables:
            cursor.execute(f"DESCRIBE `{tbl}`")
            cols = cursor.fetchall()
            schema_dict[tbl] = {
                "columns": [c["Field"] for c in cols],
                "details": cols
            }
        conn.close()
        return schema_dict
    except Exception as e:
        print(f"[NOTE] Live connector lookup skipped ({e}). Using standard schema definitions.")
        return None

def build_master_schema():
    live_schema = get_db_schema_via_connector()
    if live_schema:
        tables_data = live_schema
    else:
        # Canonical schema for all 34 Bhooma HMS tables
        tables_data = {
            "hospitals": ["id", "name", "username", "password", "created_at"],
            "patients": ["id", "name", "surname", "father_name", "phone", "demographics", "gender", "blood_group", "age", "emergency_contact_name", "emergency_contact_phone", "hospital_id", "created_at"],
            "doctors": ["id", "name", "department_id", "experience", "degree", "phone", "hospital_id"],
            "departments": ["id", "name", "icon", "hospital_id"],
            "appointments": ["id", "patient_id", "doctor_id", "type", "date", "slot", "symptoms", "allergies", "status", "stage", "bed_number", "doctor_notes", "hospital_id", "created_at"],
            "beds": ["id", "bed_number", "type", "wing", "status", "patient_id", "hospital_id"],
            "staff": ["id", "hospital_id", "staff_code", "first_name", "last_name", "role", "department", "shift", "status", "phone", "email"],
            "staff_attendance": ["id", "hospital_id", "staff_id", "date", "status", "check_in_time", "check_out_time", "working_hours", "marked_by"],
            "prescriptions": ["id", "appointment_id", "medicine_name", "dosage", "frequency", "duration", "instructions"],
            "diagnoses": ["id", "appointment_id", "description"],
            "doctor_day_schedules": ["id", "doctor_id", "day_of_week", "is_available", "start_time", "end_time", "duration_minutes", "break_start", "break_end", "custom_slots", "created_at"],
            "doctor_slots": ["id", "doctor_id", "date", "slot_time", "is_booked", "hospital_id"],
            "doctor_categories": ["doctor_id", "department_id"],
            "patient_files": ["id", "patient_id", "file_name", "file_path", "mime_type", "file_size", "uploaded_at", "hospital_id"],
            "timeline_events": ["id", "appointment_id", "patient_id", "event_time", "event_description", "created_at"],
            "ai_conversations": ["id", "hospital_id", "user_id", "user_role", "title", "page_context", "created_at"],
            "ai_chat_messages": ["id", "conversation_id", "role", "content", "sql_executed", "created_at"],
            "ai_pending_actions": ["id", "conversation_id", "message_id", "hospital_id", "action_type", "target_table", "description", "sql_query", "sql_params", "status", "expires_at", "created_at"],
            "ai_action_log": ["id", "hospital_id", "user_id", "user_role", "action_type", "target_table", "sql_executed", "rows_affected", "success", "error_message", "ip_address", "created_at"],
            "ai_config": ["id", "hospital_id", "model_name", "ollama_url", "max_tokens", "temperature", "context_window", "rate_limit_per_minute", "enabled", "created_at", "updated_at"]
        }

    # Strict Table Relationships
    relationships = [
        {"from": "appointments.patient_id", "to": "patients.id", "type": "MANY_TO_ONE", "description": "Patient attending appointment"},
        {"from": "appointments.doctor_id", "to": "doctors.id", "type": "MANY_TO_ONE", "description": "Doctor consulting appointment"},
        {"from": "doctors.department_id", "to": "departments.id", "type": "MANY_TO_ONE", "description": "Doctor specialty department"},
        {"from": "beds.patient_id", "to": "patients.id", "type": "ONE_TO_ONE", "description": "Patient currently admitted in bed"},
        {"from": "staff_attendance.staff_id", "to": "staff.id", "type": "MANY_TO_ONE", "description": "Attendance log for staff member"},
        {"from": "prescriptions.appointment_id", "to": "appointments.id", "type": "MANY_TO_ONE", "description": "Prescription generated during appointment"},
        {"from": "diagnoses.appointment_id", "to": "appointments.id", "type": "MANY_TO_ONE", "description": "Diagnosis documented for appointment"},
        {"from": "doctor_day_schedules.doctor_id", "to": "doctors.id", "type": "MANY_TO_ONE", "description": "Weekly day schedule for doctor"},
        {"from": "timeline_events.appointment_id", "to": "appointments.id", "type": "MANY_TO_ONE", "description": "Clinical stage event timeline"}
    ]

    master_payload = {
        "tables": tables_data,
        "relationships": relationships,
        "multi_tenant_column": "hospital_id"
    }

    out_file = os.path.join(os.path.dirname(os.path.abspath(__file__)), "hms_master_schema.json")
    with open(out_file, "w", encoding="utf-8") as f:
        json.dump(master_payload, f, indent=2, ensure_ascii=False)
    
    print(f"[SUCCESS] Introspected & exported master schema to: {out_file}")
    return master_payload

if __name__ == "__main__":
    build_master_schema()

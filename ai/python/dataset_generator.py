#!/usr/bin/env python3
"""
HMS AI Dataset Generator
========================
Generates a comprehensive, fine-tuning dataset (1000+ examples) for training a dedicated
hospital management AI model (Qwen2.5-3B-Instruct / Llama-3-3B) tailored specifically
to the Bhooma Hospital Management System schema and operational workflows.

Outputs:
  - hms_chatml.jsonl (Standard ChatML format for Hugging Face / Axolotl / SFTTrainer)
  - hms_alpaca.json  (Instruction / Input / Output format for Unsloth / LLaMA-Factory)
"""

import json
import random
import os
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

SYSTEM_PROMPT = """You are BHOOMA HMS AI, an intelligent, professional Hospital Information Assistant.
You have real-time access to the hospital database.
Rules:
1. Always isolate data by hospital_id = ? for multi-tenant safety.
2. For read queries, provide a clear direct answer highlighted with '> 💡 **Direct Answer:**' followed by the exact, optimized MySQL SELECT query inside ```sql codeblocks.
3. For staff attendance queries, join `staff` and `staff_attendance` on staff.id = staff_attendance.staff_id with staff_attendance.date = CURDATE().
4. Never expose sensitive columns like passwords or private keys.
5. Never perform DROP, DELETE, ALTER, or TRUNCATE operations."""

# Real names and entities matching hospital_db
DOCTORS = [
    ("doc-1", "Dr. Ambarish A. Panchasara", "Orthopedics", "Surgeon"),
    ("doc-2", "Dr. Priya Patel", "Cardiology", "Cardiologist"),
    ("doc-3", "Dr. Rajesh Sharma", "Pediatrics", "Pediatrician"),
    ("doc-4", "Dr. Neha Verma", "General Medicine", "Physician"),
    ("doc-5", "Dr. Suresh Joshi", "Neurology", "Neurologist")
]

DEPARTMENTS = ["Cardiology", "Orthopedics", "Pediatrics", "General Medicine", "Neurology", "ICU", "Emergency"]

BED_TYPES = ["ICU", "General Ward", "Private", "Semi-Private"]
BED_NUMBERS = [f"ICU-{i:02d}" for i in range(1, 11)] + [f"GEN-{i:03d}" for i in range(101, 131)] + [f"PVT-{i:03d}" for i in range(201, 215)]

STAFF_ROLES = ["Senior Staff Nurse", "Staff Nurse", "Ward Attendant", "Receptionist", "Lab Technician", "Pharmacist", "Radiologist"]
FIRST_NAMES = ["Amit", "Pooja", "Vikram", "Sneha", "Rahul", "Anjali", "Karan", "Ritu", "Deepak", "Sunita", "Manoj", "Kavita", "Sanjay", "Meena", "Gaurav", "Swati", "Naveen", "Divya", "Rohit", "Priyanka"]
LAST_NAMES = ["Sharma", "Shah", "Patel", "Verma", "Mehta", "Desai", "Joshi", "Chauhan", "Trivedi", "Rathod", "Yadav", "Dave", "Pandya", "Kapadia", "Vyas"]

TIME_SLOTS = ["09:00 AM", "09:30 AM", "10:00 AM", "10:30 AM", "11:00 AM", "11:30 AM", "02:00 PM", "02:30 PM", "03:00 PM", "03:30 PM", "04:00 PM", "04:30 PM"]
APPOINTMENT_TYPES = ["General Consultation", "Specialist Follow-up", "Routine Check-up", "Emergency Assessment", "Post-Op Review"]

def generate_templates():
    templates = []

    # 1. DOCTOR AVAILABILITY & DETAILS
    for doc_id, doc_name, dept, role in DOCTORS:
        templates.append({
            "user": f"Is {doc_name} available today?",
            "answer": f"> 💡 **Direct Answer:** Checking today's schedule and appointments for {doc_name} ({dept}).\n\n```sql\nSELECT d.name, d.experience, d.degree, dep.name AS department, dds.start_time, dds.end_time, dds.is_available FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.id LEFT JOIN doctor_day_schedules dds ON d.id = dds.doctor_id AND dds.day_of_week = DAYNAME(CURDATE()) WHERE d.id = '{doc_id}' AND d.hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"Show qualifications and experience of {doc_name}",
            "answer": f"> 💡 **Direct Answer:** {doc_name} is a specialist in {dept}.\n\n```sql\nSELECT name, experience, degree, phone FROM doctors WHERE id = '{doc_id}' AND hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"List all appointments scheduled for {doc_name} today",
            "answer": f"> 💡 **Direct Answer:** Fetching today's scheduled patient visits for {doc_name}.\n\n```sql\nSELECT a.id AS token_id, a.slot, CONCAT(p.name, ' ', COALESCE(p.surname, '')) AS patient_name, a.type, a.status FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = '{doc_id}' AND a.date = CURDATE() AND a.hospital_id = 1 ORDER BY a.slot ASC;\n```"
        })

    # 2. DEPARTMENT DOCTOR LOOKUPS
    for dept in DEPARTMENTS:
        templates.append({
            "user": f"Which doctors are in {dept} department?",
            "answer": f"> 💡 **Direct Answer:** Listing all practicing specialists in the {dept} department.\n\n```sql\nSELECT d.id, d.name, d.experience, d.degree, d.phone FROM doctors d JOIN departments dep ON d.department_id = dep.id WHERE dep.name LIKE '%{dept}%' AND d.hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"Who is available for consultation in {dept} right now?",
            "answer": f"> 💡 **Direct Answer:** Checking currently active consultation specialists in {dept}.\n\n```sql\nSELECT d.name, dep.name AS department, dds.start_time, dds.end_time FROM doctors d JOIN departments dep ON d.department_id = dep.id JOIN doctor_day_schedules dds ON d.id = dds.doctor_id WHERE dep.name LIKE '%{dept}%' AND dds.day_of_week = DAYNAME(CURDATE()) AND dds.is_available = 1 AND d.hospital_id = 1;\n```"
        })

    # 3. BED MANAGEMENT (ICU, GENERAL, PRIVATE)
    for b_type in BED_TYPES:
        templates.append({
            "user": f"How many {b_type} beds are vacant?",
            "answer": f"> 💡 **Direct Answer:** Checking current occupancy status for {b_type} beds.\n\n```sql\nSELECT COUNT(*) AS available_beds FROM beds WHERE type = '{b_type}' AND status = 'Available' AND hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"List all empty {b_type} beds",
            "answer": f"> 💡 **Direct Answer:** Displaying available {b_type} bed inventory.\n\n```sql\nSELECT bed_number, type, wing FROM beds WHERE type = '{b_type}' AND status = 'Available' AND hospital_id = 1 ORDER BY bed_number ASC;\n```"
        })
        templates.append({
            "user": f"Show all occupied {b_type} beds with patient details",
            "answer": f"> 💡 **Direct Answer:** Listing occupied {b_type} beds along with admitted patients.\n\n```sql\nSELECT b.bed_number, b.type, b.wing, p.id AS mrn, CONCAT(p.name, ' ', COALESCE(p.surname, '')) AS patient_name, a.date AS admission_date FROM beds b JOIN patients p ON b.patient_id = p.id LEFT JOIN appointments a ON a.patient_id = p.id AND a.bed_number = b.bed_number WHERE b.type = '{b_type}' AND b.status = 'Occupied' AND b.hospital_id = 1;\n```"
        })

    # 4. STAFF & ATTENDANCE QUERIES
    attendance_queries = [
        ("who is present today in staff", "Present", "Listing all hospital staff members checked in on duty today."),
        ("list absent staff members", "Absent", "Showing all staff members marked absent for today."),
        ("how many staff are present today", "Present", "Counting total on-duty hospital personnel for today."),
        ("who is on leave today", "On Leave", "Fetching staff members currently approved on official leave."),
        ("who is on night shift", "Night", "Displaying personnel scheduled on the night shift rotation.")
    ]
    for q, status, direct_ans in attendance_queries:
        if status in ["Present", "Absent", "On Leave"]:
            templates.append({
                "user": q,
                "answer": f"> 💡 **Direct Answer:** {direct_ans}\n\n```sql\nSELECT s.id, s.staff_code, CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS staff_name, s.role, s.department, sa.check_in_time, sa.status FROM staff s JOIN staff_attendance sa ON s.id = sa.staff_id WHERE sa.date = CURDATE() AND sa.status = '{status}' AND s.hospital_id = 1 ORDER BY s.role ASC;\n```"
            })
        elif status == "Night":
            templates.append({
                "user": q,
                "answer": f"> 💡 **Direct Answer:** {direct_ans}\n\n```sql\nSELECT s.id, CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS staff_name, s.role, s.department, s.shift FROM staff s WHERE s.shift = 'Night' AND s.status = 'Active' AND s.hospital_id = 1;\n```"
            })

    # 5. PATIENT ADMISSIONS, MRN LOOKUPS & DEMOGRAPHICS
    for _ in range(30):
        first = random.choice(FIRST_NAMES)
        last = random.choice(LAST_NAMES)
        full_name = f"{first} {last}"
        pat_id = f"PAT-{random.randint(1001, 1150):04d}"
        
        templates.append({
            "user": f"Find patient records for {full_name}",
            "answer": f"> 💡 **Direct Answer:** Retrieving medical record profile for patient {full_name}.\n\n```sql\nSELECT id, name, surname, phone, demographics, gender, blood_group, age, created_at FROM patients WHERE (name LIKE '%{first}%' AND surname LIKE '%{last}%') AND hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"Show details of patient MRN {pat_id}",
            "answer": f"> 💡 **Direct Answer:** Accessing patient demographic and emergency profile for {pat_id}.\n\n```sql\nSELECT id, name, surname, phone, emergency_contact_name, emergency_contact_phone, blood_group, age, gender FROM patients WHERE id = '{pat_id}' AND hospital_id = 1;\n```"
        })
        templates.append({
            "user": f"What prescriptions and medications were given to {pat_id}?",
            "answer": f"> 💡 **Direct Answer:** Fetching pharmacy prescription history for patient {pat_id}.\n\n```sql\nSELECT pr.medicine_name, pr.dosage, pr.frequency, pr.duration, pr.instructions, a.date, d.name AS prescribed_by FROM prescriptions pr JOIN appointments a ON pr.appointment_id = a.id JOIN doctors d ON a.doctor_id = d.id WHERE a.patient_id = '{pat_id}' AND a.hospital_id = 1 ORDER BY a.date DESC;\n```"
        })

    # 6. TODAY'S APPOINTMENT OVERVIEW & QUEUE
    templates.append({
        "user": "How many appointments are booked for today?",
        "answer": "> 💡 **Direct Answer:** Calculating total OPD consultation volume for today.\n\n```sql\nSELECT COUNT(*) AS total_today, SUM(CASE WHEN status = 'Checked In' THEN 1 ELSE 0 END) AS checked_in_count, SUM(CASE WHEN status = 'Pre-Booked' THEN 1 ELSE 0 END) AS pre_booked_count, SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_count FROM appointments WHERE date = CURDATE() AND hospital_id = 1;\n```"
    })
    templates.append({
        "user": "Show queue of patients waiting for consultation",
        "answer": "> 💡 **Direct Answer:** Listing checked-in patients currently awaiting doctor consultation.\n\n```sql\nSELECT a.id AS token, CONCAT(p.name, ' ', COALESCE(p.surname, '')) AS patient_name, d.name AS doctor_name, a.slot, a.symptoms FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id WHERE a.date = CURDATE() AND a.status = 'Checked In' AND a.hospital_id = 1 ORDER BY a.slot ASC;\n```"
    })

    # 7. MULTILINGUAL & COMMON TYPOS (Hinglish + Human queries)
    typo_variations = [
        ("kitne docter available hai aaj", "> 💡 **Direct Answer:** Listing currently available practicing doctors for today.\n\n```sql\nSELECT d.name, dep.name AS department, dds.start_time, dds.end_time FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.id LEFT JOIN doctor_day_schedules dds ON d.id = dds.doctor_id AND dds.day_of_week = DAYNAME(CURDATE()) WHERE dds.is_available = 1 AND d.hospital_id = 1;\n```"),
        ("aaj kitne pecent admit hai", "> 💡 **Direct Answer:** Counting total inpatient bed admissions.\n\n```sql\nSELECT COUNT(*) AS admitted_patients FROM beds WHERE status = 'Occupied' AND hospital_id = 1;\n```"),
        ("kon kon nurse duty par hai", "> 💡 **Direct Answer:** Showing nursing staff members currently on duty today.\n\n```sql\nSELECT s.id, CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS nurse_name, s.department, sa.check_in_time FROM staff s JOIN staff_attendance sa ON s.id = sa.staff_id WHERE s.role LIKE '%Nurse%' AND sa.date = CURDATE() AND sa.status = 'Present' AND s.hospital_id = 1;\n```"),
        ("icu me kitne bed khali hai", "> 💡 **Direct Answer:** Checking vacant ICU beds count.\n\n```sql\nSELECT bed_number, wing FROM beds WHERE type = 'ICU' AND status = 'Available' AND hospital_id = 1;\n```")
    ]
    for q, ans in typo_variations:
        templates.append({"user": q, "answer": ans})

    return templates

def generate_augmented_dataset(target_count=1200):
    base_templates = generate_templates()
    dataset = []
    
    # Add base templates
    for item in base_templates:
        dataset.append({
            "system": SYSTEM_PROMPT,
            "instruction": item["user"],
            "input": "",
            "output": item["answer"]
        })

    # Synthesize augmentations with variations
    augment_prefixes = [
        "Please ", "Can you ", "Kindly ", "Help me to ", "Could you ", "I need to ",
        "Quickly ", "Tell me ", "Fetch ", "Check if ", ""
    ]
    
    while len(dataset) < target_count:
        base = random.choice(base_templates)
        prefix = random.choice(augment_prefixes)
        new_q = prefix + base["user"][0].lower() + base["user"][1:] if prefix else base["user"]
        dataset.append({
            "system": SYSTEM_PROMPT,
            "instruction": new_q,
            "input": "",
            "output": base["answer"]
        })

    return dataset[:target_count]

def main():
    print("=" * 60)
    print("[HMS AI] Generating Synthetic AI Fine-Tuning Dataset...")
    print("=" * 60)

    dataset = generate_augmented_dataset(1250)
    out_dir = os.path.dirname(os.path.abspath(__file__))

    # 1. Save Alpaca Format
    alpaca_file = os.path.join(out_dir, "hms_training_alpaca.json")
    with open(alpaca_file, "w", encoding="utf-8") as f:
        json.dump(dataset, f, indent=2, ensure_ascii=False)
    print(f"[SUCCESS] Generated Alpaca JSON: {alpaca_file} ({len(dataset)} examples)")

    # 2. Save ChatML JSONL Format
    chatml_file = os.path.join(out_dir, "hms_training_chatml.jsonl")
    with open(chatml_file, "w", encoding="utf-8") as f:
        for ex in dataset:
            chatml_entry = {
                "messages": [
                    {"role": "system", "content": ex["system"]},
                    {"role": "user", "content": ex["instruction"]},
                    {"role": "assistant", "content": ex["output"]}
                ]
            }
            f.write(json.dumps(chatml_entry, ensure_ascii=False) + "\n")
    print(f"[SUCCESS] Generated ChatML JSONL: {chatml_file} ({len(dataset)} examples)")

    print("=" * 60)
    print("Dataset generation complete! Ready for Unsloth / Hugging Face training.")
    print("=" * 60)

if __name__ == "__main__":
    main()

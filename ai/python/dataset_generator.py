#!/usr/bin/env python3
"""
BHOOMA HMS AI — Ultra-Scale Training Dataset Generator
Generates 3,000,000 fine-tuning examples for the HMS AI chatbot.
Streams to disk in chunks to avoid memory exhaustion.

Usage:
    python dataset_generator.py [target_count]
    Default: 3000000
"""

import json, random, os, sys, time, itertools
from pathlib import Path

# ============================================================
# SYSTEM PROMPT (embedded in every ChatML example)
# ============================================================
SYSTEM_PROMPT = (
    "You are BHOOMA HMS AI, an elite clinical & administrative intelligence assistant for BHOOMA Medicare Hospital. "
    "You deliver answers with the crisp, direct, and authoritative precision of Claude 3.5 and Gemini 1.5. "
    "RULES: 1) Always begin with '> 💡 **Direct Answer:**'. 2) Never show SQL or table names. "
    "3) For data queries, present results in bullet points with bold labels. NEVER mention 'confirm' or 'action card' for data viewing. "
    "4) For actions (delete/update/insert), instruct user to click Confirm on the action card. "
    "5) Always filter by hospital_id. 6) Resolve pronouns from conversation history."
)

# ============================================================
# SEED DATA — Realistic hospital entities
# ============================================================
DOCTORS = [
    {"id": 1, "name": "Dr. Ambarish Kulkarni", "degree": "MBBS, MD (Medicine)", "dept": "General Medicine", "exp": "18 Years", "fee": 600, "phone": "9876543210"},
    {"id": 2, "name": "Dr. Priya Sharma", "degree": "MBBS, MS (Ortho)", "dept": "Orthopaedics", "exp": "12 Years", "fee": 800, "phone": "9876543211"},
    {"id": 3, "name": "Dr. Rajesh Patel", "degree": "MBBS, DM (Cardio)", "dept": "Cardiology", "exp": "22 Years", "fee": 1200, "phone": "9876543212"},
    {"id": 4, "name": "Dr. Neha Desai", "degree": "MBBS, MD (Pedia)", "dept": "Paediatrics", "exp": "10 Years", "fee": 700, "phone": "9876543213"},
    {"id": 5, "name": "Dr. Sanjay Mehta", "degree": "MBBS, MD (Derma)", "dept": "Dermatology", "exp": "15 Years", "fee": 900, "phone": "9876543214"},
    {"id": 6, "name": "Dr. Kavita Joshi", "degree": "MBBS, MS (Ophth)", "dept": "Ophthalmology", "exp": "14 Years", "fee": 750, "phone": "9876543215"},
    {"id": 7, "name": "Dr. Arjun Nair", "degree": "MBBS, MD (Neuro)", "dept": "Neurology", "exp": "20 Years", "fee": 1500, "phone": "9876543216"},
    {"id": 8, "name": "Dr. Fatima Khan", "degree": "MBBS, MD (Gynae)", "dept": "Gynaecology", "exp": "16 Years", "fee": 1000, "phone": "9876543217"},
    {"id": 9, "name": "Dr. Vikram Singh", "degree": "MBBS, MS (Surgery)", "dept": "General Surgery", "exp": "25 Years", "fee": 1100, "phone": "9876543218"},
    {"id": 10, "name": "Dr. Ananya Reddy", "degree": "MBBS, MD (Pulmo)", "dept": "Pulmonology", "exp": "11 Years", "fee": 850, "phone": "9876543219"},
    {"id": 11, "name": "Dr. Manish Gupta", "degree": "MBBS, MD (Gastro)", "dept": "Gastroenterology", "exp": "17 Years", "fee": 950, "phone": "9876543220"},
    {"id": 12, "name": "Dr. Sunita Rao", "degree": "MBBS, MD (Onco)", "dept": "Oncology", "exp": "19 Years", "fee": 1300, "phone": "9876543221"},
]

DEPARTMENTS = [
    "General Medicine", "Orthopaedics", "Cardiology", "Paediatrics", "Dermatology",
    "Ophthalmology", "Neurology", "Gynaecology", "General Surgery", "Pulmonology",
    "Gastroenterology", "Oncology", "ENT", "Psychiatry", "Radiology",
    "Pathology", "Anaesthesiology", "Emergency Medicine", "Urology", "Nephrology"
]

BLOOD_GROUPS = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"]
GENDERS = ["Male", "Female"]
BED_TYPES = ["ICU", "General Ward", "Private Room", "Semi-Private", "NICU", "HDU", "Emergency"]
BED_WINGS = ["North Wing", "South Wing", "East Block", "West Block", "Critical Care Unit", "Maternity Ward"]
BED_NUMBERS_ICU = [f"ICU-{i}" for i in range(1, 21)]
BED_NUMBERS_GEN = [f"GEN-{i}" for i in range(101, 151)]
BED_NUMBERS_PVT = [f"PVT-{i}" for i in range(201, 221)]
BED_NUMBERS_SEMI = [f"SEMI-{i}" for i in range(301, 321)]
ALL_BEDS = BED_NUMBERS_ICU + BED_NUMBERS_GEN + BED_NUMBERS_PVT + BED_NUMBERS_SEMI

STAFF_ROLES = ["Nurse", "Receptionist", "Lab Technician", "Pharmacist", "Ward Boy", "Accountant", "Security Guard", "Housekeeping", "Radiologist Technician", "OT Assistant"]
STAFF_NAMES = [
    ("Rahul", "Verma"), ("Anjali", "Deshmukh"), ("Mohan", "Tiwari"), ("Seema", "Patil"),
    ("Amit", "Shah"), ("Pooja", "Yadav"), ("Deepak", "Kumar"), ("Nisha", "Agarwal"),
    ("Suresh", "Reddy"), ("Kavita", "Sharma"), ("Ganesh", "Nair"), ("Meena", "Joshi"),
    ("Prakash", "Gupta"), ("Lata", "Singh"), ("Ravi", "Mehta"), ("Sunita", "Chauhan"),
    ("Vinod", "Mishra"), ("Rekha", "Das"), ("Manoj", "Pillai"), ("Geeta", "Iyer"),
]

PATIENT_NAMES = [
    ("Dev", "Patel"), ("Rohan", "Mehta"), ("Priya", "Sharma"), ("Anita", "Desai"),
    ("Kiran", "Reddy"), ("Sunil", "Kumar"), ("Neha", "Verma"), ("Ramesh", "Gupta"),
    ("Lalita", "Yadav"), ("Vikash", "Singh"), ("Fatima", "Syed"), ("Harish", "Joshi"),
    ("Meera", "Nair"), ("Arjun", "Shah"), ("Pooja", "Tiwari"), ("Sameer", "Khan"),
    ("Divya", "Rao"), ("Gopal", "Mishra"), ("Nandini", "Agarwal"), ("Rajesh", "Chauhan"),
    ("Sita", "Pillai"), ("Bharat", "Das"), ("Kavya", "Iyer"), ("Omkar", "Kulkarni"),
    ("Shreya", "Bhat"), ("Tushar", "Sawant"), ("Manisha", "Ghosh"), ("Anil", "Pandey"),
]

MEDICINES = [
    {"name": "Paracetamol 500mg", "dosage": "1 tablet", "freq": "Thrice daily", "dur": "5 days", "instr": "After meals"},
    {"name": "Amoxicillin 250mg", "dosage": "1 capsule", "freq": "Twice daily", "dur": "7 days", "instr": "Before meals"},
    {"name": "Omeprazole 20mg", "dosage": "1 capsule", "freq": "Once daily", "dur": "14 days", "instr": "Before breakfast"},
    {"name": "Metformin 500mg", "dosage": "1 tablet", "freq": "Twice daily", "dur": "30 days", "instr": "With meals"},
    {"name": "Atorvastatin 10mg", "dosage": "1 tablet", "freq": "Once daily", "dur": "30 days", "instr": "At bedtime"},
    {"name": "Azithromycin 500mg", "dosage": "1 tablet", "freq": "Once daily", "dur": "3 days", "instr": "Empty stomach"},
    {"name": "Ciprofloxacin 500mg", "dosage": "1 tablet", "freq": "Twice daily", "dur": "5 days", "instr": "With water"},
    {"name": "Amlodipine 5mg", "dosage": "1 tablet", "freq": "Once daily", "dur": "30 days", "instr": "Morning"},
    {"name": "Ceftriaxone 1g IV", "dosage": "1g injection", "freq": "Once daily", "dur": "5 days", "instr": "IV infusion"},
    {"name": "Insulin Glargine", "dosage": "10 units", "freq": "Once daily", "dur": "30 days", "instr": "Subcutaneous at bedtime"},
    {"name": "Salbutamol Nebulizer", "dosage": "2.5mg", "freq": "As needed", "dur": "PRN", "instr": "Via nebulizer"},
    {"name": "Pantoprazole 40mg", "dosage": "1 tablet", "freq": "Once daily", "dur": "14 days", "instr": "Empty stomach"},
]

SYMPTOMS = [
    "Chest pain", "Fever", "Cough", "Headache", "Abdominal pain", "Back pain",
    "Shortness of breath", "Joint pain", "Nausea", "Vomiting", "Dizziness",
    "Skin rash", "Blurred vision", "Sore throat", "Body aches", "Fatigue",
    "Swelling in legs", "Blood in urine", "Weight loss", "Difficulty breathing",
    "Palpitations", "Chest tightness", "Burning urination", "Diarrhoea", "Constipation"
]

APPOINTMENT_TYPES = ["General Consultation", "Specialist Review", "Follow-up Consultation", "Emergency Consultation", "Pre-Surgery Assessment", "Routine Check-up"]
APPOINTMENT_STATUSES = ["Scheduled", "Checked-In", "In Progress", "Completed", "Cancelled", "No Show"]
SHIFTS = ["Morning (08:00 - 16:00)", "Evening (16:00 - 00:00)", "Night (00:00 - 08:00)"]
ATTENDANCE_STATUSES = ["Present", "Absent", "Late", "Half Day", "On Leave"]

# Prefix variations for natural language diversity
PREFIXES = [
    "", "Please ", "Can you ", "Could you ", "Kindly ", "Help me to ", "I need to ",
    "Quickly ", "Tell me ", "Show me ", "Check if ", "Find ", "Search for ", "Lookup ",
    "Display ", "List ", "Get me ", "Fetch ", "Pull up ", "What is ", "What are ",
    "How many ", "Give me ", "I want to see ", "Let me know ", "Report on ",
    "Provide ", "Share ", "Reveal ", "Open ", "Access ", "Query ", "Retrieve ",
]

# Hindi/Hinglish prefixes
HINDI_PREFIXES = [
    "Mujhe batao ", "Dikhao ", "Kitne ", "Kaun ", "Kya ", "Kahan ", "Kab ",
    "Bhai ", "Please bata do ", "Zara dekhna ", "Jaldi se ", "Check karo ",
    "Pata karo ", "Dhundh ke batao ", "Dekho ", "Batao na ",
]

# Typo/misspelling variants
TYPO_MAP = {
    "patient": ["pateint", "patiant", "patinet", "paitent"],
    "doctor": ["docter", "doctar", "dr", "doc"],
    "appointment": ["apointment", "appoitment", "apt", "booking"],
    "blood group": ["blood grp", "blood grop", "blod group", "bloodgroup"],
    "available": ["availble", "avialable", "availabl"],
    "delete": ["delate", "delet", "remov", "remove"],
    "discharge": ["discharg", "dischrage", "dischareg"],
    "attendance": ["attendence", "attandance", "attendace"],
    "schedule": ["shedule", "scedule", "scheduel"],
    "prescription": ["prescripton", "presription", "priscription"],
    "emergency": ["emrgency", "emergancy", "emergeny"],
}


def apply_typo(text, probability=0.15):
    """Randomly introduce typos to simulate real user input."""
    if random.random() > probability:
        return text
    for correct, typos in TYPO_MAP.items():
        if correct in text.lower():
            if random.random() < 0.3:
                text = text.replace(correct, random.choice(typos), 1)
                break
    return text


def random_prefix():
    if random.random() < 0.12:
        return random.choice(HINDI_PREFIXES)
    return random.choice(PREFIXES)


def make_example(instruction, output):
    """Create a ChatML training example."""
    return {
        "messages": [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": instruction},
            {"role": "assistant", "content": output}
        ]
    }


# ============================================================
# SCENARIO GENERATORS — Each yields (instruction, output) tuples
# ============================================================

def gen_blood_group_queries():
    """Category 1: Multi-attribute patient searches (blood group × gender × age)."""
    age_ranges = [
        ("pediatric", "0", "18"), ("young adult", "18", "35"),
        ("adult", "35", "60"), ("senior", "60", "120"), ("elderly", "70", "120"),
    ]
    query_templates = [
        "i need blood {bg}",
        "i need {bg} blood",
        "i need {gender} blood {bg}",
        "urgent requirement blood {bg}",
        "now all {gender} with {bg}",
        "now only {gender} with {bg}",
        "now {gender} patients with {bg}",
        "now show {gender} with {bg}",
        "now filter {gender} with {bg}",
        "show me all {gender} patients with blood group {bg}",
        "find {gender} {bg} patients",
        "list patients blood group {bg} gender {gender}",
        "{bg} {gender} patients",
        "blood group {bg} {gender}",
        "patients with {bg} blood group who are {gender}",
        "how many {gender} patients have blood group {bg}",
        "count of {bg} {gender} patients",
        "get all {bg} patients",
        "show {bg} patients list",
        "find all patients with {bg}",
        "{gender} patients with {bg} blood type",
        "search {bg} blood group {gender}",
        "any {gender} with blood group {bg}?",
        "kya {bg} blood group ke {gender} patient hain?",
        "{bg} wale {gender} mareez dikhao",
        "blood grp {bg} {gender} list",
        "show me {bg} positive patients" if "+" in "{bg}" else "show me {bg} patients",
    ]
    for bg in BLOOD_GROUPS:
        for gender in GENDERS:
            for tmpl in query_templates:
                q = tmpl.format(bg=bg, gender=gender.lower())
                pat = random.choice(PATIENT_NAMES)
                ans = (
                    f"> 💡 **Direct Answer:** Here are the **{gender}** patients with blood group **{bg}** at BHOOMA Medicare Hospital:\n\n"
                    f"• **{pat[0]} {pat[1]}** | Age: {random.randint(18,75)} | Phone: +91 {random.randint(7000000000,9999999999)}\n"
                    f"• **{random.choice(PATIENT_NAMES)[0]} {random.choice(PATIENT_NAMES)[1]}** | Age: {random.randint(18,75)} | Phone: +91 {random.randint(7000000000,9999999999)}\n\n"
                    f"📊 The detailed records are displayed in the data table below."
                )
                yield (q, ans)

            # With age range
            for age_label, age_min, age_max in age_ranges:
                q = f"show {age_label} {gender.lower()} patients with blood group {bg}"
                ans = (
                    f"> 💡 **Direct Answer:** Searching for **{age_label} {gender.lower()}** patients with blood group **{bg}** (age {age_min}-{age_max}).\n\n"
                    f"📊 Results are displayed in the data table below."
                )
                yield (q, ans)


def gen_doctor_queries():
    """Category 2: Doctor lookups by name, dept, specialization, experience, fee."""
    for doc in DOCTORS:
        queries = [
            f"show me all doctors",
            f"list doctors in {doc['dept']}",
            f"who is {doc['name']}?",
            f"details of {doc['name']}",
            f"what is {doc['name']}'s fee?",
            f"which department is {doc['name']} in?",
            f"experience of {doc['name']}",
            f"find doctors with more than 15 years experience",
            f"doctors in {doc['dept']} department",
            f"{doc['dept']} ke doctor kaun hain?",
            f"{doc['name']} ki degree kya hai?",
            f"show {doc['dept']} specialists",
            f"doctor phone number for {doc['name']}",
            f"list all cardiologists" if doc['dept'] == 'Cardiology' else f"show {doc['dept']} doctors",
            f"top experienced doctors",
            f"most senior doctor",
            f"cheapest consultation fee",
            f"doctors with fee less than 1000",
            f"doctors available today",
        ]
        for q in queries:
            ans = (
                f"> 💡 **Direct Answer:** **{doc['name']}** — {doc['degree']}\n\n"
                f"• **Department:** {doc['dept']}\n"
                f"• **Experience:** {doc['exp']}\n"
                f"• **Consultation Fee:** ₹{doc['fee']}\n"
                f"• **Contact:** {doc['phone']}\n\n"
                f"📊 Full doctor records are displayed in the data table below."
            )
            yield (q, ans)


def gen_bed_queries():
    """Category 3: Bed inventory, availability, occupancy status."""
    for btype in BED_TYPES:
        for wing in BED_WINGS:
            queries = [
                f"show available {btype} beds",
                f"how many {btype} beds are free?",
                f"check {btype} bed availability in {wing}",
                f"{btype} beds status",
                f"occupied {btype} beds",
                f"list all beds in {wing}",
                f"empty beds in {wing}",
                f"free {btype.lower()} beds",
                f"{btype} mein kitne beds khali hain?",
                f"{wing} ke beds dikhao",
                f"bed occupancy rate for {btype}",
                f"total beds in hospital",
            ]
            for q in queries:
                count = random.randint(0, 15)
                ans = (
                    f"> 💡 **Direct Answer:** **{count} {btype}** beds are available in **{wing}**.\n\n"
                    f"• **Total {btype} Beds:** {count + random.randint(1,10)}\n"
                    f"• **Available:** {count}\n"
                    f"• **Occupied:** {random.randint(1,8)}\n\n"
                    f"📊 Bed inventory details shown in the data table below."
                )
                yield (q, ans)


def gen_appointment_queries():
    """Category 4: Appointment scheduling, status, queue, cancellation."""
    for doc in DOCTORS[:8]:
        for atype in APPOINTMENT_TYPES:
            for status in APPOINTMENT_STATUSES:
                queries = [
                    f"show today's appointments",
                    f"appointments for {doc['name']}",
                    f"{status.lower()} appointments",
                    f"list {atype.lower()} appointments",
                    f"how many appointments today?",
                    f"upcoming appointments for {doc['name']}",
                    f"cancel appointment #{random.randint(100,999)}",
                    f"aaj ke appointments dikhao",
                    f"patient queue for {doc['name']}",
                    f"next patient in line for {doc['name']}",
                ]
                q = random.choice(queries)
                pat = random.choice(PATIENT_NAMES)
                ans = (
                    f"> 💡 **Direct Answer:** Today's schedule for **{doc['name']}** ({doc['dept']}):\n\n"
                    f"• **{pat[0]} {pat[1]}** | {atype} | Status: **{status}** | Slot: {random.choice(['09:00 AM','10:30 AM','02:00 PM','04:00 PM'])}\n\n"
                    f"📊 Full appointment records are shown in the data table below."
                )
                yield (q, ans)


def gen_staff_queries():
    """Category 5: Staff attendance, shifts, roles."""
    for fname, lname in STAFF_NAMES:
        role = random.choice(STAFF_ROLES)
        shift = random.choice(SHIFTS)
        for att_status in ATTENDANCE_STATUSES:
            queries = [
                f"who is present today?",
                f"list absent staff",
                f"show staff attendance for today",
                f"staff on {shift} shift",
                f"attendance of {fname} {lname}",
                f"is {fname} {lname} present?",
                f"how many staff are on leave today?",
                f"night shift staff list",
                f"today's attendance summary",
                f"aaj kaun hazir hai?",
                f"kaun kaun absent hai?",
                f"staff attendance report",
                f"{fname} ki attendance kya hai?",
            ]
            q = random.choice(queries)
            ans = (
                f"> 💡 **Direct Answer:** **{fname} {lname}** ({role}) — Status: **{att_status}** | Shift: {shift}\n\n"
                f"📊 Full attendance data is shown in the data table below."
            )
            yield (q, ans)


def gen_prescription_queries():
    """Category 6: Prescription and medication lookups."""
    for med in MEDICINES:
        for pat in PATIENT_NAMES[:14]:
            queries = [
                f"prescriptions for {pat[0]} {pat[1]}",
                f"what medicines does {pat[0]} take?",
                f"medication list for patient {pat[0]}",
                f"show active prescriptions",
                f"dawa list for {pat[0]} {pat[1]}",
                f"{pat[0]} ko kaun si goli chal rahi hai?",
                f"check {pat[0]}'s medicine",
                f"current treatment for {pat[0]} {pat[1]}",
            ]
            q = random.choice(queries)
            ans = (
                f"> 💡 **Direct Answer:** Active prescriptions for **{pat[0]} {pat[1]}**:\n\n"
                f"💊 **{med['name']}** — {med['dosage']} | {med['freq']} | Duration: {med['dur']}\n"
                f"   _Instructions:_ {med['instr']}\n\n"
                f"📊 Complete prescription records shown in the data table below."
            )
            yield (q, ans)


def gen_delete_actions():
    """Category 7: Delete/remove operations requiring confirmation."""
    for doc in DOCTORS:
        queries = [
            f"delete {doc['name']}",
            f"remove {doc['name']} from hospital",
            f"delete doctor {doc['name']}",
            f"{doc['name']} ko hatao",
            f"remove dr {doc['name'].split()[-1]}",
            f"delete that doctor",
            f"remove this physician",
            f"remove {doc['name']} permanently",
        ]
        for q in queries:
            ans = (
                f"> 💡 **Direct Answer:** A deletion action has been prepared for **{doc['name']}** ({doc['degree']}).\n\n"
                f"⚠️ This will permanently remove {doc['name']} from the hospital database.\n\n"
                f"Please review and click **Confirm** on the action card below to execute this deletion."
            )
            yield (q, ans)

    # Patient deletions
    for pat in PATIENT_NAMES[:10]:
        q = f"delete patient {pat[0]} {pat[1]}"
        ans = (
            f"> 💡 **Direct Answer:** A deletion action has been prepared for patient **{pat[0]} {pat[1]}**.\n\n"
            f"⚠️ This is irreversible. Please review and click **Confirm** on the action card below."
        )
        yield (q, ans)


def gen_update_actions():
    """Category 8: Update/edit operations with pre-filled forms."""
    for doc in DOCTORS:
        queries = [
            f"update {doc['name']}'s phone number",
            f"edit {doc['name']}",
            f"change {doc['name']}'s fee to 1500",
            f"modify doctor {doc['name']}",
            f"update {doc['name']} experience",
            f"{doc['name']} ki degree update karo",
        ]
        for q in queries:
            ans = (
                f"> 💡 **Direct Answer:** I've retrieved the current profile for **{doc['name']}**.\n\n"
                f"Please edit the fields in the form below and click **Submit Changes** to update the record."
            )
            yield (q, ans)


def gen_form_triggers():
    """Category 9: In-chat form creation triggers."""
    form_scenarios = [
        ("add patient", "Patient Registration", "Register New Patient"),
        ("register patient", "Patient Registration", "Register New Patient"),
        ("new patient form", "Patient Registration", "Register New Patient"),
        ("add doctor", "Doctor Onboarding", "Add New Doctor"),
        ("register doctor", "Doctor Onboarding", "Add New Doctor"),
        ("onboard new physician", "Doctor Onboarding", "Add New Doctor"),
        ("add bed", "Bed Creation", "Add New Bed"),
        ("create new bed", "Bed Creation", "Add New Bed"),
        ("book appointment", "Appointment Booking", "Book Consultation"),
        ("schedule appointment", "Appointment Booking", "Book Consultation"),
        ("admit patient", "Patient Admission", "Admit to Bed"),
        ("patient admission form", "Patient Admission", "Admit to Bed"),
        ("patient add karo", "Patient Registration", "Register New Patient"),
        ("doctor add karo", "Doctor Onboarding", "Add New Doctor"),
        ("nayi entry patient", "Patient Registration", "Register New Patient"),
        ("mareez register karo", "Patient Registration", "Register New Patient"),
        ("bed add karo", "Bed Creation", "Add New Bed"),
        ("new ward bed", "Bed Creation", "Add New Bed"),
        ("appointment book karo", "Appointment Booking", "Book Consultation"),
    ]
    for trigger, category, title in form_scenarios:
        prefixes_to_use = ["", "Please ", "I want to ", "Open ", "Can you "]
        for prefix in prefixes_to_use:
            q = f"{prefix}{trigger}"
            ans = (
                f"> 💡 **Direct Answer:** I've prepared the **{title}** form for you.\n\n"
                f"📋 Please fill in the details in the interactive form below and click Submit."
            )
            yield (q, ans)


def gen_discharge_actions():
    """Category 10: Bed discharge operations."""
    for bed in ALL_BEDS[:30]:
        queries = [
            f"discharge patient from bed {bed}",
            f"discharge bed {bed}",
            f"vacate bed {bed}",
            f"release bed {bed}",
            f"mark bed {bed} as available",
            f"bed {bed} se patient discharge karo",
        ]
        for q in queries:
            ans = (
                f"> 💡 **Direct Answer:** A discharge action has been prepared for **Bed {bed}**.\n\n"
                f"This will mark the bed as Available and update the patient's appointment status.\n\n"
                f"Please review and click **Confirm** on the action card below."
            )
            yield (q, ans)


def gen_hindi_hinglish():
    """Category 11: Hindi/Hinglish clinical vocabulary."""
    hindi_scenarios = [
        ("kamra khali hai kya?", "> 💡 **Direct Answer:** Checking available rooms/beds at BHOOMA Medicare Hospital.\n\n📊 Bed availability data is shown in the data table below."),
        ("kitne kamre khali hain?", "> 💡 **Direct Answer:** Here is the current bed/room availability.\n\n📊 Data table below shows all available rooms."),
        ("parchi cancel karo", "> 💡 **Direct Answer:** An appointment cancellation action has been prepared.\n\nPlease review and click **Confirm** on the action card below."),
        ("dawa ki list dikhao", "> 💡 **Direct Answer:** Here are the active prescriptions and medications.\n\n📊 Complete prescription data shown in the table below."),
        ("haziri laga do sabki", "> 💡 **Direct Answer:** Bulk attendance marking action prepared for all active staff.\n\nPlease review and click **Confirm** on the action card below."),
        ("aaj kaun kaun absent hai?", "> 💡 **Direct Answer:** Here are the staff members marked absent today.\n\n📊 Attendance data shown in the table below."),
        ("doctor sahab ka phone number chahiye", "> 💡 **Direct Answer:** Here are the doctor contact details.\n\n📊 Phone numbers shown in the data table below."),
        ("ICU mein bed milega?", "> 💡 **Direct Answer:** Checking ICU bed availability at BHOOMA Medicare Hospital.\n\n📊 ICU bed status shown in the data table below."),
        ("mareez ki report dikhao", "> 💡 **Direct Answer:** Displaying patient medical records and reports.\n\n📊 Patient data shown in the table below."),
        ("hisaab dikhao aaj ka", "> 💡 **Direct Answer:** Here is today's financial summary.\n\n📊 Billing data shown in the data table below."),
        ("goli badal do patient ki", "> 💡 **Direct Answer:** A prescription update form has been prepared.\n\nPlease modify the medication details in the form below."),
        ("nurse kaun duty pe hai?", "> 💡 **Direct Answer:** Here are the nurses currently on duty.\n\n📊 Staff attendance data shown in the table below."),
        ("doctor ki chutti hai kya aaj?", "> 💡 **Direct Answer:** Checking doctor availability for today.\n\n📊 Doctor schedule data shown below."),
        ("operation theatre khali hai?", "> 💡 **Direct Answer:** Checking Operation Theatre availability.\n\n📊 OT status shown in the data table below."),
        ("emergency mein bed chahiye", "> 💡 **Direct Answer:** Checking emergency bed availability at BHOOMA Medicare Hospital.\n\n📊 Emergency bed data shown in the table below."),
        ("patient ka blood group batao", "> 💡 **Direct Answer:** Here are the patient blood group records.\n\n📊 Blood group data shown in the data table below."),
    ]
    for q, ans in hindi_scenarios:
        yield (q, ans)
        # Variations with prefixes
        for prefix in ["bhai ", "yaar ", "zara ", "jaldi "]:
            yield (f"{prefix}{q}", ans)


def gen_emergency_triage():
    """Category 12: Emergency and clinical triage guidance."""
    emergencies = [
        ("patient having chest pain", "🚨 **EMERGENCY PROTOCOL — Chest Pain/ACS:**\n\n• Immediate **ECG** within 10 minutes\n• **Aspirin 325mg** chewable STAT\n• Start **IV access**, draw **Troponin I**\n• Alert **Dr. Rajesh Patel** (Cardiology, Ext: 3212)\n• Prepare ICU transfer if ST-elevation detected\n\n📍 BHOOMA has **{icu}** ICU beds available."),
        ("dengue suspected", "🚨 **DENGUE FEVER ALERT:**\n\n• Check **CBC with platelet count** STAT\n• **NS1 Antigen** test if within 5 days of fever\n• Start **IV fluids** if platelets < 100,000\n• Monitor vitals every 4 hours\n• **Do NOT give Aspirin/Ibuprofen**\n\n📍 BHOOMA has **{gen}** General Ward beds available."),
        ("high fever child 104", "🚨 **PEDIATRIC HYPERPYREXIA (104°F):**\n\n• **Tepid sponging** immediately\n• **Paracetamol 15mg/kg** STAT\n• Rule out **Febrile seizures**\n• Alert **Dr. Neha Desai** (Paediatrics)\n• Check **CBC, Blood culture, Urine R/M**\n\n📍 Contact Dr. Neha Desai: 9876543213"),
        ("snake bite patient", "🚨 **SNAKE BITE PROTOCOL:**\n\n• **Immobilize** the affected limb\n• **Do NOT** apply tourniquet or cut the wound\n• Start **IV line** with Normal Saline\n• Prepare **Anti-Snake Venom (ASV)** if signs of envenomation\n• Alert **Emergency Medicine** team STAT\n\n📍 BHOOMA Emergency has **{icu}** ICU beds."),
        ("cardiac arrest code blue", "🚨 **CODE BLUE — CARDIAC ARREST:**\n\n• Start **CPR** immediately (30:2 ratio)\n• Call for **defibrillator** / **AED**\n• Alert **Dr. Rajesh Patel** (Cardiology) & ICU team\n• Push **Epinephrine 1mg IV** every 3-5 min\n• Secure airway with **ET tube**\n\n📍 BHOOMA ICU: Call Ext 3212"),
        ("allergic reaction severe", "🚨 **ANAPHYLAXIS PROTOCOL:**\n\n• **Epinephrine 0.3mg IM** (lateral thigh) STAT\n• **High-flow O2** via mask\n• **IV Normal Saline** bolus 500ml\n• **Hydrocortisone 200mg IV** + **Chlorpheniramine 10mg IV**\n• Monitor for **biphasic reaction** for 24 hours"),
        ("stroke symptoms", "🚨 **STROKE ALERT — ACT FAST:**\n\n• **F**ace drooping? **A**rm weakness? **S**peech difficulty? **T**ime to call!\n• **CT Head** STAT (rule out hemorrhage)\n• Check **blood glucose**, **BP**, **INR**\n• Alert **Dr. Arjun Nair** (Neurology)\n• Window for **thrombolysis**: 4.5 hours from onset"),
        ("broken leg fracture", "🚨 **FRACTURE MANAGEMENT:**\n\n• **Immobilize** with splint\n• **X-ray** the affected area\n• **Pain management**: Tramadol 50mg IV\n• Alert **Dr. Priya Sharma** (Orthopaedics)\n• Prepare for possible surgical fixation\n\n📍 Contact Ortho: 9876543211"),
    ]
    for q, ans_tmpl in emergencies:
        icu = random.randint(1, 6)
        gen_beds = random.randint(5, 20)
        ans = f"> 💡 **Direct Answer:** " + ans_tmpl.format(icu=icu, gen=gen_beds)
        yield (q, ans)
        # Variations
        yield (f"emergency {q}", ans)
        yield (f"urgent: {q}", ans)


def gen_multi_turn_pronoun():
    """Category 13: Multi-turn pronoun resolution patterns."""
    for doc in DOCTORS[:6]:
        for action in ["delete", "update", "show details of", "call"]:
            history_qs = [
                f"who is {doc['name']}",
                f"tell me about {doc['name']}",
                f"show {doc['name']}'s profile",
            ]
            followups = [
                f"{action} that doctor",
                f"{action} him" if "Male" in str(doc) or random.random() < 0.5 else f"{action} her",
                f"{action} this one",
                f"now {action} this doctor",
                f"ok {action} the same doctor",
            ]
            for hq in history_qs:
                for fq in followups:
                    if "delete" in action or "remove" in action:
                        ans = (
                            f"> 💡 **Direct Answer:** A deletion action for **{doc['name']}** has been prepared.\n\n"
                            f"⚠️ This will permanently remove {doc['name']} from the database.\n\n"
                            f"Please review and click **Confirm** on the action card below."
                        )
                    elif "update" in action:
                        ans = (
                            f"> 💡 **Direct Answer:** I've loaded **{doc['name']}**'s profile for editing.\n\n"
                            f"Please modify the fields in the form below."
                        )
                    else:
                        ans = (
                            f"> 💡 **Direct Answer:** **{doc['name']}** — {doc['degree']}\n\n"
                            f"• **Department:** {doc['dept']} | **Experience:** {doc['exp']} | **Fee:** ₹{doc['fee']}\n\n"
                            f"📊 Full details in the data table below."
                        )
                    yield (fq, ans)


def gen_statistics_queries():
    """Category 14: Hospital KPI and statistics queries."""
    stat_queries = [
        ("how many patients today", "total_patients", lambda: random.randint(15, 80)),
        ("total appointments today", "appointments_today", lambda: random.randint(20, 60)),
        ("bed occupancy rate", "occupancy_rate", lambda: f"{random.randint(55, 95)}%"),
        ("how many doctors on duty", "doctors_on_duty", lambda: random.randint(5, 12)),
        ("total staff present", "staff_present", lambda: random.randint(30, 80)),
        ("ICU beds available", "icu_available", lambda: random.randint(0, 8)),
        ("emergency cases today", "emergency_count", lambda: random.randint(0, 10)),
        ("patient count this week", "weekly_patients", lambda: random.randint(100, 400)),
        ("today's summary", "daily_summary", lambda: "See dashboard"),
        ("hospital overview", "overview", lambda: "Full dashboard"),
        ("revenue today", "revenue", lambda: f"₹{random.randint(50000, 500000):,}"),
        ("pending appointments", "pending_apts", lambda: random.randint(5, 25)),
        ("total admitted patients", "admitted", lambda: random.randint(10, 50)),
        ("average wait time", "avg_wait", lambda: f"{random.randint(10, 45)} minutes"),
    ]
    for q, metric, val_fn in stat_queries:
        val = val_fn()
        label = metric.replace("_", " ").title()
        ans = (
            f"> 💡 **Direct Answer:** **{label}:** {val}\n\n"
            f"📊 Detailed data shown in the data table below."
        )
        yield (q, ans)
        yield (f"what is the {q}?", ans)
        yield (f"tell me {q}", ans)


def gen_general_medical():
    """Category 15: General medical/health questions related to hospital."""
    medical_qa = [
        ("what are symptoms of diabetes", "Common symptoms of diabetes include excessive thirst, frequent urination, unexplained weight loss, fatigue, and blurred vision. At BHOOMA, consult **Dr. Ambarish Kulkarni** (General Medicine) for diabetes management."),
        ("first aid for burns", "For burns: 1) Cool under running water for 20 min, 2) Cover with clean cloth, 3) Do NOT apply ice/butter, 4) Seek medical help for severe burns. Visit BHOOMA Emergency."),
        ("what is hypertension", "Hypertension (High BP) is blood pressure consistently above 140/90 mmHg. Managed with lifestyle changes and medication. At BHOOMA, consult **Dr. Rajesh Patel** (Cardiology)."),
        ("how to manage fever", "For fever: 1) Take Paracetamol 500mg, 2) Stay hydrated, 3) Rest, 4) Cool sponging if >102°F, 5) Seek doctor if fever persists >3 days."),
        ("what is BMI", "BMI = Weight(kg) / Height(m)². Normal: 18.5-24.9. At BHOOMA, our dietitians can help with weight management plans."),
        ("covid symptoms", "COVID-19 symptoms: Fever, dry cough, fatigue, loss of taste/smell, body aches, sore throat. Get tested and isolate. BHOOMA has dedicated COVID screening."),
        ("normal blood pressure range", "Normal BP: 120/80 mmHg. Pre-hypertension: 120-139/80-89. Hypertension: ≥140/90. Regular monitoring recommended."),
        ("what vaccines are needed for children", "Key vaccines: BCG, OPV, DPT, Hepatitis B, MMR, Varicella. Follow IAP immunization schedule. Consult **Dr. Neha Desai** (Paediatrics) at BHOOMA."),
    ]
    for q, ans_text in medical_qa:
        ans = f"> 💡 **Direct Answer:** {ans_text}"
        yield (q, ans)
        yield (f"doctor, {q}", ans)
        yield (f"health query: {q}", ans)


def gen_cancel_appointment():
    """Category 16: Appointment cancellation scenarios."""
    for i in range(100, 300):
        queries = [
            f"cancel appointment {i}",
            f"cancel appointment #{i}",
            f"cancel apt {i}",
            f"appointment {i} cancel karo",
            f"cancel booking {i}",
        ]
        q = random.choice(queries)
        ans = (
            f"> 💡 **Direct Answer:** Cancellation action prepared for **Appointment #{i}**.\n\n"
            f"Please review and click **Confirm** on the action card below to cancel this appointment."
        )
        yield (q, ans)


def gen_mark_attendance():
    """Category 17: Attendance marking (bulk and individual)."""
    for fname, lname in STAFF_NAMES:
        for status in ATTENDANCE_STATUSES:
            queries = [
                f"mark {fname} {lname} as {status.lower()}",
                f"mark all staff present",
                f"mark everyone absent",
                f"bulk attendance: all present",
                f"sabki haziri laga do present",
                f"{fname} ko {status.lower()} mark karo",
                f"set {fname} attendance to {status.lower()}",
            ]
            q = random.choice(queries)
            if "all" in q or "sabki" in q or "everyone" in q or "bulk" in q:
                ans = (
                    f"> 💡 **Direct Answer:** Bulk attendance action prepared — marking all active staff as **{status}**.\n\n"
                    f"Please review and click **Confirm** on the action card below."
                )
            else:
                ans = (
                    f"> 💡 **Direct Answer:** Attendance action prepared for **{fname} {lname}** — marking as **{status}**.\n\n"
                    f"Please review and click **Confirm** on the action card below."
                )
            yield (q, ans)


def gen_complex_joins():
    """Category 18: Complex cross-table queries."""
    join_queries = [
        ("show patients with their doctors", "Listing patients along with their assigned consulting doctors.\n\n📊 Patient-Doctor mapping shown in the data table below."),
        ("which doctor has most appointments", "Identifying the doctor with the highest appointment volume.\n\n📊 Doctor appointment counts shown in the data table below."),
        ("patients admitted in ICU with their diagnosis", "Listing ICU-admitted patients with their clinical diagnoses.\n\n📊 ICU patient data shown in the data table below."),
        ("staff working night shift with attendance", "Night shift staff and their attendance records.\n\n📊 Night shift data shown in the data table below."),
        ("doctors and their appointment count today", "Doctor-wise appointment distribution for today.\n\n📊 Appointment counts shown in the data table below."),
        ("beds occupied with patient name and doctor", "Occupied beds with assigned patient and treating doctor details.\n\n📊 Bed occupancy data shown in the data table below."),
        ("prescriptions given today with doctor and patient names", "Today's prescriptions with prescribing doctor and patient information.\n\n📊 Prescription records shown in the data table below."),
        ("departments with doctor count", "Department-wise doctor distribution.\n\n📊 Department statistics shown in the data table below."),
    ]
    for q, ans_body in join_queries:
        ans = f"> 💡 **Direct Answer:** {ans_body}"
        yield (q, ans)
        yield (f"show me {q}", ans)
        yield (f"give me {q}", ans)


def gen_conversation_starters():
    """Category 19: Greetings and meta-queries."""
    starters = [
        ("hello", "Hello! 👋 I'm **BHOOMA AI**, your hospital intelligence assistant. How can I help you today?\n\nYou can ask me about patients, doctors, beds, appointments, staff attendance, or any hospital operation."),
        ("hi", "Hi there! 👋 I'm **BHOOMA AI**. Ask me anything about BHOOMA Medicare Hospital."),
        ("who are you", "I'm **BHOOMA AI** — an advanced clinical & administrative intelligence assistant for BHOOMA Medicare Hospital. I can query patient records, manage appointments, track beds, handle staff attendance, and much more."),
        ("what can you do", "I can help with:\n• 🔍 **Search** patients, doctors, beds, staff\n• 📅 **Book/Cancel** appointments\n• 🛏️ **Manage** bed occupancy & discharges\n• 📋 **Mark** staff attendance\n• ➕ **Add** patients, doctors, beds\n• 💊 **View** prescriptions\n• 🗑️ **Delete** records (with confirmation)"),
        ("help", "Here's what I can do:\n• Search hospital records\n• Manage appointments & admissions\n• Track bed availability\n• Handle staff attendance\n• Add or update records\n\nJust ask naturally — I understand English, Hindi, and Hinglish!"),
        ("thank you", "You're welcome! 😊 Let me know if you need anything else."),
        ("thanks", "Happy to help! 😊 Feel free to ask if you need more."),
        ("good morning", "Good morning! ☀️ I'm **BHOOMA AI**, ready to assist with hospital operations. What do you need today?"),
    ]
    for q, ans in starters:
        yield (q, f"> 💡 **Direct Answer:** {ans}")


def gen_outside_context():
    """Category 20: Outside/unrelated queries with graceful handling."""
    outside_qa = [
        ("what is the weather today", "I'm specialized in hospital management, but I'll do my best! For weather, please check a weather app. Meanwhile, how can I help with hospital operations?"),
        ("tell me a joke", "Why did the doctor carry a red pen? In case they needed to draw blood! 😄\n\nBut seriously, I'm here for hospital management. How can I help?"),
        ("who is the prime minister", "I focus on hospital management. For general knowledge, please check a search engine. Can I help with any hospital queries?"),
        ("play music", "I can't play music, but I can play with hospital data! 📊 What records would you like to see?"),
        ("what time is it", "Check your device clock for current time. I can tell you today's appointment schedule or staff attendance if needed!"),
    ]
    for q, ans in outside_qa:
        yield (q, f"> 💡 **Direct Answer:** {ans}")


# ============================================================
# MAIN GENERATOR — Streams to disk in chunks
# ============================================================

def collect_all_base_scenarios():
    """Collect all scenario generators into one list."""
    generators = [
        gen_blood_group_queries,
        gen_doctor_queries,
        gen_bed_queries,
        gen_appointment_queries,
        gen_staff_queries,
        gen_prescription_queries,
        gen_delete_actions,
        gen_update_actions,
        gen_form_triggers,
        gen_discharge_actions,
        gen_hindi_hinglish,
        gen_emergency_triage,
        gen_multi_turn_pronoun,
        gen_statistics_queries,
        gen_general_medical,
        gen_cancel_appointment,
        gen_mark_attendance,
        gen_complex_joins,
        gen_conversation_starters,
        gen_outside_context,
    ]
    print(f"[GENERATOR] Collecting base scenarios from {len(generators)} categories...")
    all_scenarios = []
    for gen_fn in generators:
        cat_count = 0
        for instruction, output in gen_fn():
            all_scenarios.append((instruction, output))
            cat_count += 1
        print(f"  [OK] {gen_fn.__name__}: {cat_count:,} examples")
    print(f"[GENERATOR] Total base scenarios: {len(all_scenarios):,}")
    return all_scenarios


def generate_to_disk(target_count=3_000_000):
    """Generate target_count examples and stream to disk in chunks."""
    script_dir = Path(__file__).parent
    jsonl_path = script_dir / "hms_training_chatml.jsonl"
    alpaca_path = script_dir / "hms_training_alpaca.json"

    base = collect_all_base_scenarios()
    base_count = len(base)

    if base_count == 0:
        print("[ERROR] No base scenarios generated!")
        return

    print(f"\n[GENERATOR] Generating {target_count:,} examples (base: {base_count:,})...")
    print(f"[GENERATOR] Output: {jsonl_path}")

    CHUNK_SIZE = 50_000
    written = 0
    start_time = time.time()

    with open(jsonl_path, "w", encoding="utf-8") as f_jsonl:
        # Phase 1: Write all base scenarios verbatim
        random.shuffle(base)
        for instruction, output in base:
            example = make_example(instruction, output)
            f_jsonl.write(json.dumps(example, ensure_ascii=False) + "\n")
            written += 1

        if written % CHUNK_SIZE == 0 or written == base_count:
            elapsed = time.time() - start_time
            rate = written / max(elapsed, 0.01)
            print(f"  [PROGRESS] {written:>10,} / {target_count:,} ({written*100/target_count:.1f}%) — {rate:.0f} ex/s")

        # Phase 2: Generate permutations until target is reached
        all_prefixes = PREFIXES + HINDI_PREFIXES
        while written < target_count:
            batch_size = min(CHUNK_SIZE, target_count - written)
            for _ in range(batch_size):
                instruction, output = random.choice(base)

                # Apply random transformations
                r = random.random()
                if r < 0.35:
                    # Add prefix
                    prefix = random.choice(all_prefixes)
                    if prefix and instruction[0].isupper():
                        instruction = prefix + instruction[0].lower() + instruction[1:]
                    else:
                        instruction = prefix + instruction
                elif r < 0.55:
                    # Apply typo
                    instruction = apply_typo(instruction, probability=0.5)
                elif r < 0.70:
                    # Add trailing question mark or period
                    instruction = instruction.rstrip("?.! ") + random.choice(["?", ".", "!", ""])
                elif r < 0.80:
                    # Lowercase entire query
                    instruction = instruction.lower()
                elif r < 0.88:
                    # Add polite suffix
                    suffix = random.choice([" please", " thanks", " asap", " urgently", " jaldi", " abhi"])
                    instruction = instruction.rstrip("?.! ") + suffix
                elif r < 0.94:
                    # Swap word order slightly
                    words = instruction.split()
                    if len(words) > 3:
                        i = random.randint(0, len(words) - 2)
                        words[i], words[i+1] = words[i+1], words[i]
                        instruction = " ".join(words)
                # else: use as-is

                example = make_example(instruction, output)
                f_jsonl.write(json.dumps(example, ensure_ascii=False) + "\n")
                written += 1

            elapsed = time.time() - start_time
            rate = written / max(elapsed, 0.01)
            eta = (target_count - written) / max(rate, 1)
            print(f"  [PROGRESS] {written:>10,} / {target_count:,} ({written*100/target_count:.1f}%) — {rate:.0f} ex/s — ETA: {eta:.0f}s")

    elapsed = time.time() - start_time
    file_size_mb = jsonl_path.stat().st_size / (1024 * 1024)
    print(f"\n{'='*60}")
    print(f"[COMPLETE] Generated {written:,} examples")
    print(f"[FILE] {jsonl_path} — {file_size_mb:.1f} MB")
    print(f"[TIME] {elapsed:.1f} seconds ({elapsed/60:.1f} minutes)")
    print(f"{'='*60}")

    # Also generate Alpaca JSON (first 100K for quick reference)
    print(f"\n[GENERATOR] Writing Alpaca JSON subset (first 100,000 examples)...")
    alpaca_data = []
    with open(jsonl_path, "r", encoding="utf-8") as f:
        for i, line in enumerate(f):
            if i >= 100_000:
                break
            msg = json.loads(line)
            alpaca_data.append({
                "system": msg["messages"][0]["content"],
                "instruction": msg["messages"][1]["content"],
                "input": "",
                "output": msg["messages"][2]["content"]
            })
    with open(alpaca_path, "w", encoding="utf-8") as f:
        json.dump(alpaca_data, f, indent=2, ensure_ascii=False)
    print(f"[COMPLETE] Alpaca subset: {alpaca_path}")


if __name__ == "__main__":
    target = int(sys.argv[1]) if len(sys.argv) > 1 else 3_000_000
    generate_to_disk(target)

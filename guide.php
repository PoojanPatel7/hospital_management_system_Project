<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital & I.C.U';
$user_name = $_SESSION['username'] ?? 'Administrator';
?>

<div class="space-y-8 pb-16 max-w-7xl mx-auto">

    <!-- ========================================================================= -->
    <!-- 1. TOP HERO: SYSTEM GUIDE & INTERACTIVE KNOWLEDGE BASE                    -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/80 shadow-[0_10px_35px_rgb(0,0,0,0.03)]">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-gradient-to-br from-sky-100/60 via-indigo-100/40 to-emerald-100/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200/60 shadow-2xs">
                        <i class="fa-solid fa-book-open text-sky-600"></i>
                        <span>Complete User Manual &amp; Tour</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Beginner-Friendly Walkthrough</span>
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-tight">
                    Hospital Clinical OS &mdash; System User Guide
                </h1>
                
                <p class="text-slate-600 mt-2.5 text-sm sm:text-base font-normal leading-relaxed">
                    Welcome! Whether you are a newly joined doctor, receptionist, triage nurse, or hospital administrator, this interactive guide explains every page, section, button, and clinical workflow in the system.
                </p>

                <!-- Omni Search Input for Guide -->
                <div class="mt-6 relative max-w-xl">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input 
                        type="text" 
                        id="guide-search-input" 
                        oninput="filterGuideTopics()" 
                        placeholder="Search any feature, page, or button (e.g. token, MRN, slots, backup, triage)..." 
                        class="w-full border border-slate-300 rounded-2xl pl-11 pr-24 py-3 text-xs sm:text-sm font-semibold bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition shadow-sm">
                    <button type="button" onclick="clearGuideSearch()" id="guide-clear-search-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold px-2 py-1 bg-slate-100 rounded-lg">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                <a href="dashboard.php" class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Open Live Dashboard</span>
                </a>
                <a href="about.php" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 shadow-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-info text-sky-600"></i>
                    <span>About Hospital Overview</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. QUICK MODULE FILTER TABS                                               -->
    <!-- ========================================================================= -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 text-xs font-bold">
        <button type="button" onclick="selectGuideCategory('all')" id="tab-cat-all" class="guide-cat-tab px-4 py-2 rounded-xl bg-slate-900 text-white transition shadow-sm shrink-0">
            🌟 All Modules (12)
        </button>
        <button type="button" onclick="selectGuideCategory('workflow')" id="tab-cat-workflow" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            🔄 Patient Journey
        </button>
        <button type="button" onclick="selectGuideCategory('overview')" id="tab-cat-overview" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            📊 Dashboard &amp; Analytics
        </button>
        <button type="button" onclick="selectGuideCategory('frontdesk')" id="tab-cat-frontdesk" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            🏥 Front Desk &amp; Patients
        </button>
        <button type="button" onclick="selectGuideCategory('appointments')" id="tab-cat-appointments" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            📅 Appointments &amp; Slots
        </button>
        <button type="button" onclick="selectGuideCategory('pipeline')" id="tab-cat-pipeline" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            ⚡ Live Queue &amp; Pipeline
        </button>
        <button type="button" onclick="selectGuideCategory('clinical')" id="tab-cat-clinical" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            👨‍⚕️ Doctors &amp; Wards
        </button>
        <button type="button" onclick="selectGuideCategory('system')" id="tab-cat-system" class="guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0">
            💾 Staff &amp; Database Backups
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. VISUAL PATIENT JOURNEY (THE 6-STEP COMPLETE CLINICAL LIFECYCLE)         -->
    <!-- ========================================================================= -->
    <div id="section-patient-journey" class="guide-topic-card apple-card p-6 sm:p-10 bg-white border border-slate-200/90 shadow-sm space-y-6" data-category="workflow" data-keywords="workflow lifecycle patient flow steps journey registration booking consultation queue bed discharge">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-arrows-spin"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">The Complete Patient Lifecycle (Step-by-Step Flow)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">How a patient enters the hospital, undergoes consultation, and exits the system.</p>
                </div>
            </div>
            <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full self-start sm:self-auto">
                Standard Clinical SOP
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Step 1 -->
            <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-black text-xs flex items-center justify-center shadow-sm">1</span>
                    <a href="index.php" class="text-[11px] font-bold text-blue-700 hover:underline">Reception Desk &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Patient Registration</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Front-desk staff inputs name, father's name, phone, age, gender, and blood group. The system automatically creates a unique <strong>Medical Record Number (MRN)</strong> formatted as <code class="bg-blue-100 text-blue-800 px-1 py-0.5 rounded text-[10px]">CP-2026-XXX</code>.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-sm">2</span>
                    <a href="book.php" class="text-[11px] font-bold text-indigo-700 hover:underline">Book Appointment &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Consultation Booking</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Select department and doctor. Pick an available time slot (e.g. 09:30 AM), enter chief complaints, and designate whether it is a <strong>General</strong>, <strong>Follow-up</strong>, or urgent <strong>Emergency</strong> case.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-teal-600 text-white font-black text-xs flex items-center justify-center shadow-sm">3</span>
                    <a href="queue.php" class="text-[11px] font-bold text-teal-700 hover:underline">Live Pipeline &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Token Check-In &amp; Triage</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Patient receives Token #. They advance in real-time through: <strong>Stage 1 (Checked-In)</strong> &rarr; <strong>Stage 2 (Arrived at Reception)</strong> &rarr; <strong>Stage 3 (Waiting Lounge)</strong>. Emergency cases flash in red!
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-purple-600 text-white font-black text-xs flex items-center justify-center shadow-sm">4</span>
                    <a href="queue.php" class="text-[11px] font-bold text-purple-700 hover:underline">Consulting Room &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Doctor Consultation</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    At <strong>Stage 4 (Consulting Room)</strong>, the doctor opens the consultation modal, reviews history, inputs diagnosis, prescribes medicines with dosages/instructions, and logs clinical notes.
                </p>
            </div>

            <!-- Step 5 -->
            <div class="p-5 rounded-2xl bg-rose-50/50 border border-rose-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-rose-600 text-white font-black text-xs flex items-center justify-center shadow-sm">5</span>
                    <a href="beds.php" class="text-[11px] font-bold text-rose-700 hover:underline">Bed Ward &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Inpatient Bed Admission</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    If inpatient care or surgery is required, the doctor allocates an ICU, HDU, or General Bed. The bed status automatically switches to <strong class="text-rose-600">Occupied</strong> with patient name and MRN attached.
                </p>
            </div>

            <!-- Step 6 -->
            <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 space-y-2.5 relative">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-sm">6</span>
                    <a href="history.php" class="text-[11px] font-bold text-emerald-700 hover:underline">Medical Dossiers &rarr;</a>
                </div>
                <h4 class="font-extrabold text-slate-900 text-sm">Discharge &amp; Dossier Vault</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Patient is discharged with printed prescription and summary. Their complete record permanently archives into the <strong>Medical Records Vault</strong> with timestamps and doctor signatures.
                </p>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. DETAILED PAGE-BY-PAGE COMPLETE MANUAL                                  -->
    <!-- ========================================================================= -->
    <div class="space-y-6" id="guide-modules-container">
        
        <!-- Module 1: Dashboard -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="overview" data-keywords="dashboard charts analytics graph timeline doctor filter kpi metrics overview ekg heartbeat emergency banner">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Hospital Dashboard</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">dashboard.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Real-time command center, key performance indicators, and interactive analytics.</p>
                    </div>
                </div>
                <a href="dashboard.php" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-xl border border-indigo-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Dashboard</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-indigo-600 text-xs"></i> <span>What You See Here</span>
                    </h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li><strong>Live Heartbeat Oscilloscope:</strong> Medical monitor animation with real-time digital clock.</li>
                        <li><strong>Critical Emergency Banner:</strong> Prominently alerts staff if high-priority cases require triage.</li>
                        <li><strong>6 Big KPI Metric Cards:</strong> Total Patients, Live Pipeline Queue, Today's Consultations, Doctors on Duty, Bed Ward Space, Staff Attendance.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-indigo-600 text-xs"></i> <span>Interactive Chart.js Analytics</span>
                    </h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li><strong>Doctor Filter:</strong> Filter appointment charts by a specific doctor or all consultants.</li>
                        <li><strong>Timeline Selector:</strong> Switch between 7 Days, 14 Days, 30 Days, or 12 Months.</li>
                        <li><strong>Doughnut Chart:</strong> Visual breakdown of Waiting, Consulting, Completed, and Pre-Booked visits.</li>
                        <li><strong>Department Workload:</strong> Compares patient intake across hospital specialties.</li>
                    </ul>
                </div>

                <div class="space-y-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <h5 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-lightbulb text-amber-500 text-xs"></i> <span>Beginner Tip</span>
                    </h5>
                    <p class="text-slate-600">
                        Always check the top Emergency Banner when starting your shift. If a red card is present, immediately click <strong>Triage Emergency Pipeline</strong> to prioritize the patient.
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 2: Reception Desk -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="frontdesk" data-keywords="reception desk register patient new profile mrn phone demographics duplicate check checkin frontdesk">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Reception Desk &amp; Registration</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">index.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Patient intake, profile creation, and MRN assignment.</p>
                    </div>
                </div>
                <a href="index.php" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl border border-blue-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Reception Desk</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">How to Register a New Patient:</h5>
                    <ol class="space-y-1.5 list-decimal list-inside text-slate-600">
                        <li>Fill in <strong>First Name</strong>, <strong>Surname</strong>, and <strong>Father's Name</strong>.</li>
                        <li>Enter patient's mobile number, age, gender, and blood group.</li>
                        <li>Add address/demographics and emergency contact details.</li>
                        <li>Click <strong>Register Patient</strong>. The system generates an MRN like <code class="bg-blue-50 text-blue-700 font-bold px-1 py-0.2 rounded">CP-2026-006</code>.</li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Duplicate Detection &amp; Safety:</h5>
                    <p>
                        The system automatically checks for existing patients with matching name, surname, and father's name. If found, a duplicate modal alerts the receptionist to avoid creating redundant files!
                    </p>
                </div>

                <div class="space-y-2 bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                    <h5 class="font-extrabold text-blue-900 text-sm">Next Step After Registration:</h5>
                    <p class="text-blue-800">
                        After registering, click <strong>Book Consultation</strong> to assign the patient to a doctor or check them into the Live Pipeline immediately!
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 3: Book Appointment -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="appointments" data-keywords="book appointment schedule slot time doctor date consultation token emergency complaints allergies">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Book Appointment Workspace</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">book.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Scheduling consultations, choosing doctors, time slots, and token allocation.</p>
                    </div>
                </div>
                <a href="book.php" class="text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-3.5 py-2 rounded-xl border border-purple-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Booking Page</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Booking Steps:</h5>
                    <ol class="space-y-1.5 list-decimal list-inside text-slate-600">
                        <li>Search and select the patient by MRN or Name.</li>
                        <li>Select clinical department (e.g. Cardiology, General Medicine).</li>
                        <li>Choose the attending Doctor and consultation Date.</li>
                        <li>Pick an available Time Slot (e.g. 10:00 AM). Booked slots appear grayed out.</li>
                        <li>Choose appointment type: <strong>General</strong>, <strong>Follow-up</strong>, or <strong>Emergency</strong>.</li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Token &amp; Appointment Code:</h5>
                    <p>
                        Every confirmed appointment receives an official code like <code class="bg-purple-50 text-purple-700 font-bold px-1 py-0.2 rounded">APP-0042</code> and an ordinal Token Number for the day.
                    </p>
                </div>

                <div class="space-y-2 bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                    <h5 class="font-extrabold text-purple-900 text-sm">Pre-Booked vs Immediate:</h5>
                    <p class="text-purple-800">
                        Appointments for future dates stay marked as <strong>Pre-Booked</strong>. Once the day arrives and the patient walks into the hospital, they transition into the Live Pipeline!
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 4: Live Pipeline & Queue -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="pipeline" data-keywords="live pipeline queue stages check-in available waiting lounge consulting room token kanban triage emergency call token">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Live Pipeline Board</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">queue.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Real-time Kanban triage across desk check-in, waiting lounge, and consulting room.</p>
                    </div>
                </div>
                <a href="queue.php" class="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl border border-emerald-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Live Pipeline</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-blue-700">Stage 1</span>
                    <h5 class="font-extrabold text-slate-900 text-sm">Checked-In</h5>
                    <p class="text-slate-600 text-[11px]">Patient has entered the hospital premises and registered their arrival at the desk.</p>
                </div>

                <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-teal-700">Stage 2</span>
                    <h5 class="font-extrabold text-slate-900 text-sm">Available</h5>
                    <p class="text-slate-600 text-[11px]">Vitals checked by nursing staff. Patient is confirmed available and ready for consultation.</p>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-amber-700">Stage 3</span>
                    <h5 class="font-extrabold text-slate-900 text-sm">Waiting Lounge</h5>
                    <p class="text-slate-600 text-[11px]">Patient seated in the waiting lounge outside the assigned doctor's examination suite.</p>
                </div>

                <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-200 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-purple-700">Stage 4</span>
                    <h5 class="font-extrabold text-slate-900 text-sm">Consulting Room</h5>
                    <p class="text-slate-600 text-[11px]">Doctor is currently examining the patient, writing prescriptions, and creating diagnoses.</p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-600 space-y-2">
                <h5 class="font-extrabold text-slate-900 text-sm">Triage &amp; Advancement Actions:</h5>
                <p>
                    Staff can drag-and-drop or click <strong>Advance Stage</strong> to push the patient forward. Click <strong>Call Token</strong> to trigger a visual audio chime calling the patient by their token number.
                </p>
            </div>
        </div>

        <!-- Module 5: Appointments Schedule -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="appointments" data-keywords="appointments schedule calendar list search filter doctor date status cancel table view cards view">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Appointments Schedule Directory</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">appointments.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Filter by doctor and date, view cards or table lists, and manage patient visits.</p>
                    </div>
                </div>
                <a href="appointments.php" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-xl border border-indigo-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Appointments Hub</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Core Capabilities:</h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li><strong>Doctor Selector:</strong> Switch between All Doctors or select a specific physician.</li>
                        <li><strong>Date Picker:</strong> Pick any historical or upcoming date, or click <em>Today</em> / <em>Tomorrow</em>.</li>
                        <li><strong>View Switcher:</strong> Toggle between <em>Cards Grid</em> and <em>Detailed Table View</em>.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Advanced Multi-Field Filters:</h5>
                    <p>
                        Open the <strong>Advanced Search</strong> drawer to filter by Status (Waiting, In Consultation, Pre-Booked, Completed, Cancelled) or Type (General, Follow-up, Emergency), or search by symptom keywords.
                    </p>
                </div>

                <div class="space-y-2 bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100">
                    <h5 class="font-extrabold text-indigo-900 text-sm">Cancelling Appointments:</h5>
                    <p class="text-indigo-800">
                        To cancel an appointment, click the <em>Cancel</em> button on any visit card. A safe confirmation modal asks for confirmation before updating status to Cancelled.
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 6: Doctor Slots -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="appointments" data-keywords="doctor slots timings weekly schedule intervals duty hours break time consultation duration availability">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Doctor Slot Timings &amp; Schedules</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">doctor_slots.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Configure weekly duty hours, slot intervals, and consultation availability.</p>
                    </div>
                </div>
                <a href="doctor_slots.php" class="text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-3.5 py-2 rounded-xl border border-purple-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Manage Doctor Slots</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">How to Set Weekly Schedules:</h5>
                    <ol class="space-y-1.5 list-decimal list-inside text-slate-600">
                        <li>Select a doctor from the roster.</li>
                        <li>For each day (Monday to Sunday), mark if the doctor is <strong>Available</strong> or taking a <strong>Day Off</strong>.</li>
                        <li>Configure <strong>Shift Start Time</strong> and <strong>Shift End Time</strong>.</li>
                        <li>Set <strong>Slot Duration</strong> (e.g. 15, 30, or 45 minutes per patient).</li>
                        <li>Click <strong>Save Weekly Schedule</strong>.</li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Automated Slot Generation:</h5>
                    <p>
                        The booking calendar automatically parses these duty hours into clean slots (e.g., 09:00 AM, 09:30 AM, 10:00 AM) and marks days off as unavailable!
                    </p>
                </div>

                <div class="space-y-2 bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                    <h5 class="font-extrabold text-purple-900 text-sm">Capacity Management:</h5>
                    <p class="text-purple-800">
                        Adjusting slot intervals allows busy specialists to see more patients without causing long waiting room queues.
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 7: Medical Records Vault -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="frontdesk" data-keywords="medical records history dossier vault rx prescriptions lab files diagnoses tests pdf print notes">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-folder-medical"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Medical Records &amp; Dossiers Vault</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">history.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Comprehensive electronic health records, past prescriptions, and lab reports.</p>
                    </div>
                </div>
                <a href="history.php" class="text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 px-3.5 py-2 rounded-xl border border-amber-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Records Vault</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">What is Stored in the Vault:</h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li>Every past consultation timeline and date.</li>
                        <li>Attending physician and department.</li>
                        <li>Recorded diagnoses and clinical symptoms.</li>
                        <li>Digital prescriptions with medicine name, dosage, frequency, and instructions.</li>
                        <li>Uploaded laboratory files, X-rays, and ECG reports.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Searching Patient Files:</h5>
                    <p>
                        Use the search input to search by patient name, MRN (<code class="bg-slate-100 text-slate-700 font-bold px-1 py-0.2 rounded">CP-2026-XXX</code>), phone number, or prescription medicine name.
                    </p>
                </div>

                <div class="space-y-2 bg-amber-50/50 p-4 rounded-2xl border border-amber-100">
                    <h5 class="font-extrabold text-amber-900 text-sm">Printing &amp; Export:</h5>
                    <p class="text-amber-800">
                        Click the <strong>Print Dossier</strong> button on any record to generate a clean, official medical summary formatted for printing or insurance claims!
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 8: Doctors Directory -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="clinical" data-keywords="doctors directory add doctor specialties departments degree experience consultants roster">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Doctors Directory &amp; Specialists</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-cyan-100 text-cyan-800">doctors.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Managing hospital consultant profiles, specialties, and contact directory.</p>
                    </div>
                </div>
                <a href="doctors.php" class="text-xs font-bold text-cyan-700 bg-cyan-50 hover:bg-cyan-100 px-3.5 py-2 rounded-xl border border-cyan-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Doctors Directory</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Managing Doctors:</h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li>Click <strong>Add New Doctor</strong> to register a physician.</li>
                        <li>Assign multi-specialty departments (e.g. General Medicine &amp; Cardiology).</li>
                        <li>Record contact telephone number, degree, and clinical experience.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Data Integrity Protection:</h5>
                    <p>
                        When a doctor's profile is updated or removed, the system safely preserves all historical patient visit records to protect medical history integrity.
                    </p>
                </div>

                <div class="space-y-2 bg-cyan-50/50 p-4 rounded-2xl border border-cyan-100">
                    <h5 class="font-extrabold text-cyan-900 text-sm">Slot Coordination:</h5>
                    <p class="text-cyan-800">
                        After creating a doctor, immediately navigate to <strong>Doctor Time Slots</strong> to define their active consultation hours and shift schedule!
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 9: Bed Ward Management -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="clinical" data-keywords="beds ward icu general hdu occupancy allocate patient admit discharge wings">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-bed-pulse"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Bed Ward &amp; Inpatient Management</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800">beds.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Live tracking of ICU, HDU, and general ward bed turnover and occupancy.</p>
                    </div>
                </div>
                <a href="beds.php" class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3.5 py-2 rounded-xl border border-rose-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Bed Ward</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Bed Classification:</h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li><strong>ICU Beds:</strong> Equipped with intensive cardiac monitors and oxygen.</li>
                        <li><strong>HDU Beds:</strong> Step-down high dependency monitoring units.</li>
                        <li><strong>General Wards:</strong> Inpatient post-surgery and observation recovery rooms.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Allocating &amp; Discharging Beds:</h5>
                    <p>
                        To admit a patient, select an <em>Available</em> bed, search the patient's MRN, and confirm allocation. When the patient recovers, click <strong>Discharge Bed</strong> to free up the bed immediately!
                    </p>
                </div>

                <div class="space-y-2 bg-rose-50/50 p-4 rounded-2xl border border-rose-100">
                    <h5 class="font-extrabold text-rose-900 text-sm">Dashboard Integration:</h5>
                    <p class="text-rose-800">
                        Bed availability percentages reflect in real time on the top KPI card on the main Dashboard so emergency teams know ward capacity at all times.
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 10: Staff & Attendance -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="system" data-keywords="staff attendance checkin checkout shifts roster workforce present late absent on duty employee">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Staff Registry &amp; Daily Attendance</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800">staff.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Workforce roster, check-in times, attendance status, and portal logins.</p>
                    </div>
                </div>
                <a href="staff.php" class="text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 px-3.5 py-2 rounded-xl border border-teal-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Staff &amp; Attendance</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Marking Attendance:</h5>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li>Quick mark as <strong>Present</strong>, <strong>Late</strong>, <strong>Half Day</strong>, or <strong>Absent / On Leave</strong>.</li>
                        <li>System records exact check-in and check-out timestamps.</li>
                        <li>Computes total daily working hours automatically.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Attendance Rate Metrics:</h5>
                    <p>
                        A real-time attendance rate gauge displays total on-duty percentage for the current day and updates the dashboard staff card automatically.
                    </p>
                </div>

                <div class="space-y-2 bg-teal-50/50 p-4 rounded-2xl border border-teal-100">
                    <h5 class="font-extrabold text-teal-900 text-sm">Staff Directory:</h5>
                    <p class="text-teal-800">
                        Register nurses, ward boys, lab technicians, and receptionists with role-based designations and departmental tags.
                    </p>
                </div>
            </div>
        </div>

        <!-- Module 11: Database Backup & Security -->
        <div class="guide-topic-card apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5" data-category="system" data-keywords="backup restore database sql dump snapshot disaster recovery security archive download tables">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Database Backup &amp; Disaster Recovery</h3>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">backup.php</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Disaster recovery snapshots, safe archives, and database restore procedures.</p>
                    </div>
                </div>
                <a href="backup.php" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl border border-blue-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Open Backup Center</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">How to Take an SQL Snapshot:</h5>
                    <ol class="space-y-1.5 list-decimal list-inside text-slate-600">
                        <li>Navigate to <strong>Database Backup</strong> (`backup.php`).</li>
                        <li>Click the prominent button: <strong>Take Full Database Backup Now</strong>.</li>
                        <li>The system automatically serializes all 16 MySQL tables into a timestamped `.sql` file in the `backups/` folder.</li>
                        <li>Click <strong>Download SQL</strong> to store a secondary copy on your local computer or secure drive.</li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <h5 class="font-extrabold text-slate-900 text-sm">Restoring from a Snapshot:</h5>
                    <p>
                        In the event of accidental data loss or hardware migration, visit <strong>restore.php</strong>, select the desired backup file, and execute restoration to return the database to that snapshot.
                    </p>
                </div>

                <div class="space-y-2 bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                    <h5 class="font-extrabold text-blue-900 text-sm">Recommended Best Practice:</h5>
                    <p class="text-blue-800">
                        Hospital administrators should create a backup daily at the end of shifts or before performing major staff roster updates.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 5. BEGINNER FAQS & OPERATIONAL CHEATSHEET                                 -->
    <!-- ========================================================================= -->
    <div class="apple-card p-6 sm:p-10 bg-white border border-slate-200/90 shadow-sm space-y-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-circle-question"></i>
                </span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Frequently Asked Questions &amp; Cheatsheet</h2>
            </div>
            <p class="text-sm text-slate-500">Quick answers to common clinical scenarios and technical questions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs text-slate-600">
            
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-2">
                <h4 class="font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                    <span>How do I handle an urgent emergency walk-in?</span>
                </h4>
                <p class="leading-relaxed">
                    1. Go to <strong>Book Appointment</strong> (`book.php`).<br>
                    2. Select the patient or quickly register them at <strong>Reception</strong>.<br>
                    3. Select Type: <strong class="text-rose-600 font-bold">Emergency Case</strong>.<br>
                    4. Submit booking. The patient immediately appears on the top red <strong>Emergency Alert Banner</strong> on the Dashboard and in the Live Pipeline with pulsing red badges for instant triage!
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-2">
                <h4 class="font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor text-purple-500"></i>
                    <span>How do I add new consulting doctors to the hospital?</span>
                </h4>
                <p class="leading-relaxed">
                    1. Visit <strong>Doctors Directory</strong> (`doctors.php`) and click <em>Add Doctor</em>.<br>
                    2. Choose their specialty departments (Cardiology, General Medicine, etc.) and save.<br>
                    3. Next, navigate to <strong>Manage Slot Timings</strong> (`doctor_slots.php`) to set their working days, start/end hours, and slot intervals (e.g. 30 mins).
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-2">
                <h4 class="font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-print text-amber-500"></i>
                    <span>How do I print a patient's prescription or medical history?</span>
                </h4>
                <p class="leading-relaxed">
                    1. Navigate to <strong>Medical Records Vault</strong> (`history.php`).<br>
                    2. Type the patient's name or MRN code into the search box.<br>
                    3. Open their visit record and click <strong>Print Dossier</strong> or <strong>Print Prescription</strong> to open a clean, print-ready document with hospital headers.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-2">
                <h4 class="font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-lock text-emerald-500"></i>
                    <span>Is patient health information secure?</span>
                </h4>
                <p class="leading-relaxed">
                    Yes. All patient files, prescriptions, and diagnoses are stored in authenticated relational database tables isolated per hospital tenant. Automatic session timeouts protect inactive terminals, and regular SQL snapshots protect against hardware failure.
                </p>
            </div>

        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: INTERACTIVE GUIDE FILTERING & SEARCH                          -->
<!-- ========================================================================= -->
<script>
let currentActiveCat = 'all';

function selectGuideCategory(category) {
    currentActiveCat = category;

    // Update Tab Styles
    document.querySelectorAll('.guide-cat-tab').forEach(tab => {
        tab.className = "guide-cat-tab px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shrink-0";
    });

    const activeTab = document.getElementById(`tab-cat-${category}`);
    if (activeTab) {
        activeTab.className = "guide-cat-tab px-4 py-2 rounded-xl bg-slate-900 text-white transition shadow-sm shrink-0";
    }

    applyFilters();
}

function filterGuideTopics() {
    const input = document.getElementById('guide-search-input');
    const clearBtn = document.getElementById('guide-clear-search-btn');
    const term = (input.value || '').trim();

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', term.length === 0);
    }

    applyFilters();
}

function clearGuideSearch() {
    const input = document.getElementById('guide-search-input');
    const clearBtn = document.getElementById('guide-clear-search-btn');
    if (input) input.value = '';
    if (clearBtn) clearBtn.classList.add('hidden');
    applyFilters();
}

function applyFilters() {
    const searchVal = (document.getElementById('guide-search-input').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.guide-topic-card');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const keywords = (card.getAttribute('data-keywords') || '').toLowerCase();
        const cardText = card.innerText.toLowerCase();

        const matchesCat = (currentActiveCat === 'all') || (cat === currentActiveCat);
        const matchesSearch = (searchVal === '') || (keywords.includes(searchVal)) || (cardText.includes(searchVal));

        if (matchesCat && matchesSearch) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

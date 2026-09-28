<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Medical Staff & Schedules</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Inspect doctor availability by date, view booked appointments, and schedule advance visits.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-semibold">
            <button id="btn-doc-view-schedule" onclick="setDocViewMode('schedule')" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold transition">Daily Schedule</button>
            <button id="btn-doc-view-directory" onclick="setDocViewMode('directory')" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">Staff Directory</button>
        </div>
        <button onclick="openDoctorTimingModal()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2 px-3.5 rounded-xl border border-indigo-200/80 transition flex items-center gap-1.5 text-xs shrink-0">
            <i class="fa-regular fa-clock"></i> <span>Manage Timings & Slots</span>
        </button>
        <button onclick="openAddDoctor()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-md transition flex items-center gap-2 text-xs shrink-0">
            <i class="fa-solid fa-user-plus"></i> <span>Add Doctor</span>
        </button>
    </div>
</div>

<!-- ================= TOP DATE SLIDER ================= -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold shadow-sm">
                <i class="fa-regular fa-calendar-days"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Date Slider & Appointment Calendar</h3>
                <p class="text-[11px] text-slate-500">Select any date below to inspect appointments and available time slots.</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <button onclick="slideDateTrack(-1)" aria-label="Slide dates left" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition shadow-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button onclick="slideDateTrack(1)" aria-label="Slide dates right" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition shadow-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Horizontal Scrollable Date Carousel -->
    <div id="date-slider-track" class="flex gap-2.5 overflow-x-auto custom-scrollbar pb-2 pt-1 scroll-smooth">
        <div class="text-xs text-slate-400 py-3 px-4">Loading calendar dates...</div>
    </div>
</div>

<!-- ================= SCHEDULE VIEW MODE ================= -->
<div id="view-schedule-section" class="space-y-6">
    
    <!-- Selected Date Appointments & Status Showcase -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span id="selected-date-badge" class="text-xs font-black px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">Today</span>
                    <h4 id="selected-date-heading" class="font-extrabold text-slate-900 text-base sm:text-lg">Appointments on This Date</h4>
                </div>
                <p id="selected-date-subtext" class="text-xs text-slate-500 mt-0.5">Real-time status of all consultations scheduled for the selected day.</p>
            </div>
            <div id="selected-date-status-pills" class="flex flex-wrap items-center gap-2">
                <!-- Status summary badges -->
            </div>
        </div>

        <div id="selected-date-appointments-container" class="mt-4">
            <!-- Loaded via JS -->
        </div>
    </div>

    <!-- Doctor Available Time Slots for Selected Date -->
    <div>
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base sm:text-lg flex items-center gap-2">
                    <i class="fa-solid fa-clock text-indigo-600"></i> Doctor Available Times & Slots
                </h3>
                <p class="text-xs text-slate-500">Click any available green time slot to book an advance consultation instantly.</p>
            </div>
        </div>

        <div id="doctor-slots-cards-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Loaded via JS -->
        </div>
    </div>
</div>

<!-- ================= STAFF DIRECTORY VIEW MODE ================= -->
<div id="view-directory-section" class="hidden apple-card p-2 overflow-hidden shadow-sm">
    <div class="p-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
        <div>
            <h4 class="font-bold text-slate-900 text-sm">Registered Doctors & Specialties</h4>
            <p class="text-xs text-slate-500">Complete staff registry with contact information and assigned categories.</p>
        </div>
    </div>
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-sm min-w-[600px]">
            <thead class="border-b border-slate-100 bg-slate-50/50 text-slate-500 font-semibold uppercase text-[11px] tracking-wider">
                <tr>
                    <th class="py-4 px-6">Doctor Name</th>
                    <th class="py-4 px-6">Phone</th>
                    <th class="py-4 px-6">Specialties</th>
                    <th class="py-4 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="doctors-tbody" class="divide-y divide-slate-100">
                <!-- Loaded via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ================= ADVANCE BOOKING MODAL ================= -->
<div id="modal-advance-doctor-book" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="apple-card max-w-xl sm:max-w-2xl lg:max-w-3xl w-full p-6 sm:p-8 relative my-8 max-h-[90vh] overflow-y-auto">
        <button onclick="closeAdvanceDoctorBookModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold shadow-sm">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Book Advance Consultation</h3>
                <p id="adv-doc-subtitle" class="text-xs text-slate-500 font-medium">With Doctor</p>
            </div>
        </div>

        <form id="form-advance-doctor-book" onsubmit="handleAdvanceDoctorBookSubmit(event)" class="space-y-4">
            <input type="hidden" id="adv-book-doctor-id">
            
            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 text-xs space-y-1">
                <div><span class="text-slate-500 font-semibold">Doctor:</span> <strong id="adv-book-doc-name" class="text-slate-900">...</strong></div>
                <div><span class="text-slate-500 font-semibold">Scheduled Date:</span> <strong id="adv-book-date-display" class="text-indigo-700 font-bold">...</strong></div>
                <div><span class="text-slate-500 font-semibold">Time Slot:</span> <strong id="adv-book-slot-display" class="text-emerald-700 font-bold">...</strong></div>
                <input type="hidden" id="adv-book-date">
                <input type="hidden" id="adv-book-slot">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Select Patient *</label>
                <input type="hidden" id="adv-book-patient-id" required>

                <!-- Search Input with Live Suggestions Dropdown -->
                <div id="adv-patient-search-box" class="relative">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input 
                                type="text" 
                                id="adv-patient-search-input" 
                                oninput="handleAdvDoctorPatientSearch(this.value)" 
                                onfocus="handleAdvDoctorPatientSearch(this.value)"
                                autocomplete="off"
                                placeholder="Search by any info (Name, MRN, Phone, Blood, Father...)" 
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-8 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
                            <button type="button" id="adv-patient-search-clear" onclick="clearAdvDoctorPatientSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <a href="index.php" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center shrink-0" title="Register new patient">
                            <i class="fa-solid fa-user-plus mr-1"></i> New
                        </a>
                    </div>

                    <!-- Floating Live Suggestions Dropdown -->
                    <div id="adv-patient-suggestions" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Selected Patient Display Card -->
                <div id="adv-patient-selected-card" class="hidden bg-indigo-50/80 border border-indigo-200 rounded-2xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div id="adv-selected-avatar" class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm">
                            --
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h5 id="adv-selected-name" class="font-extrabold text-slate-900 text-xs truncate">Patient Name</h5>
                                <span id="adv-selected-blood" class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-white text-rose-600 border border-rose-100 shrink-0">🩸 B+</span>
                            </div>
                            <p id="adv-selected-details" class="text-[10px] text-slate-500 mt-0.5 truncate font-medium">MRN: CP-2026-002 • 📞 9876543210</p>
                        </div>
                    </div>
                    <button type="button" onclick="resetAdvDoctorPatientSelection()" class="text-[11px] font-bold text-slate-600 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 px-2.5 py-1.5 rounded-xl transition shadow-sm flex items-center gap-1 shrink-0 ml-2">
                        <i class="fa-solid fa-pen text-[10px]"></i> Change
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Consultation Type *</label>
                <select id="adv-book-type" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white">
                    <option value="General Consultation">General Consultation</option>
                    <option value="Follow-up">Follow-up</option>
                    <option value="Emergency Case">Emergency Case</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Symptoms / Reason *</label>
                <textarea id="adv-book-symptoms" required rows="2" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white" placeholder="Describe symptoms or medical reason..."></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" id="btn-submit-adv-doc-book" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition text-sm">
                    Confirm Advance Booking
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= ADD / EDIT DOCTOR MODAL ================= -->
<div id="modal-doctor" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="apple-card max-w-xl sm:max-w-2xl w-full p-6 sm:p-8 relative my-8 max-h-[90vh] overflow-y-auto">
        <button onclick="document.getElementById('modal-doctor').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        <h3 id="doc-modal-title" class="text-xl font-bold text-slate-900 mb-1">Add New Doctor</h3>
        <p class="text-xs text-slate-500 mb-6">Assign specialty categories and contact details.</p>

        <form id="form-doctor" onsubmit="handleDoctorSubmit(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Doctor Name *</label>
                <input type="text" id="doc-name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none" placeholder="Dr. John Doe">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Phone Number *</label>
                <input type="text" id="doc-phone" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none" placeholder="+1 234 567 890">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Select Categories * (Hold Ctrl/Cmd)</label>
                <select id="doc-categories" multiple required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none h-32 bg-slate-50">
                    <!-- Loaded via JS -->
                </select>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 transition text-sm">Review & Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MANAGE DOCTOR DAY TIMINGS & DURATION MODAL ================= -->
<div id="modal-doctor-timings" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="apple-card max-w-4xl lg:max-w-5xl xl:max-w-6xl w-full p-5 sm:p-7 relative my-6 max-h-[94vh] overflow-y-auto flex flex-col justify-between shadow-2xl border border-slate-200">
        <button onclick="closeDoctorTimingModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center"><i class="fa-solid fa-xmark text-sm"></i></button>

        <div>
            <!-- Header with Doctor Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-indigo-600/20 shrink-0">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg sm:text-xl font-black text-slate-900">Manage Slot Timings & Duration</h3>
                            <span id="timing-doc-badge" class="text-[11px] font-mono font-bold bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-lg border border-indigo-200">ID</span>
                        </div>
                        <p id="timing-doc-subtitle" class="text-xs text-slate-500 mt-0.5">Customize daily consultation hours, slot intervals, and availability per day.</p>
                    </div>
                </div>

                <!-- Doctor Switcher Select (Quick switch across doctors) -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 whitespace-nowrap hidden sm:inline">Doctor:</label>
                    <select id="timing-doctor-select" onchange="switchTimingDoctor(this.value)" class="text-xs font-bold border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none shadow-xs max-w-[200px] truncate">
                        <!-- Populated dynamically via JS -->
                    </select>
                </div>
            </div>

            <!-- 7-Day Navigation Bar -->
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar-days text-indigo-500"></i> Select Day of Week
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyCurrentDayToWeekdays()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                            <i class="fa-regular fa-copy"></i> Apply to Weekdays (Mon-Fri)
                        </button>
                        <span class="text-slate-300">•</span>
                        <button type="button" onclick="copyCurrentDayToAll()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                            <i class="fa-solid fa-clone"></i> Copy to All 7 Days
                        </button>
                    </div>
                </div>

                <!-- Day Tabs Grid -->
                <div id="timing-day-tabs" class="grid grid-cols-7 gap-1.5 sm:gap-2 p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80">
                    <!-- Tabs loaded dynamically via JS -->
                </div>
            </div>

            <!-- Main Editor Area (2 Columns on PC) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                
                <!-- Left Column: Controls (lg:col-span-7) -->
                <div class="lg:col-span-7 space-y-4">
                    
                    <!-- Day Status Toggle Card -->
                    <div class="apple-card p-4 sm:p-5 border border-slate-200/80 bg-white flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" id="timing-avail-dot"></span>
                                <h4 id="timing-active-day-title" class="font-extrabold text-base text-slate-900">Monday Schedule</h4>
                            </div>
                            <p id="timing-avail-status-desc" class="text-xs text-slate-500 mt-0.5">Consultations and patient booking enabled.</p>
                        </div>
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                            <span id="timing-avail-label" class="text-xs font-black text-emerald-700">Available</span>
                            <input type="checkbox" id="timing-day-avail-toggle" onchange="handleDayAvailToggle(this.checked)" class="sr-only peer" checked>
                            <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Working Hours & Duration (When Available) -->
                    <div id="timing-controls-container" class="space-y-4">
                        <div class="apple-card p-4 sm:p-5 border border-slate-200/80 bg-white space-y-4">
                            
                            <!-- Consultation Hours (Start & End) -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">
                                    <i class="fa-regular fa-clock text-indigo-500 mr-1"></i> Consultation Shift Hours
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <span class="block text-[11px] font-bold text-slate-500 mb-1">Shift Starts (From)</span>
                                        <select id="timing-start-time" onchange="recalculateCurrentDaySlots()" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50 focus:bg-white">
                                            <!-- Dynamically populated -->
                                        </select>
                                    </div>
                                    <div>
                                        <span class="block text-[11px] font-bold text-slate-500 mb-1">Shift Ends (To)</span>
                                        <select id="timing-end-time" onchange="recalculateCurrentDaySlots()" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50 focus:bg-white">
                                            <!-- Dynamically populated -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Slot Duration -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-black uppercase tracking-wider text-slate-600">
                                        <i class="fa-solid fa-stopwatch text-indigo-500 mr-1"></i> Slot Interval Duration
                                    </label>
                                    <span id="timing-duration-display" class="text-[11px] font-black text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-200">30 Minutes</span>
                                </div>
                                <div class="grid grid-cols-5 gap-2">
                                    <button type="button" onclick="setDaySlotDuration(15)" class="timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition shadow-xs" data-dur="15">15 min</button>
                                    <button type="button" onclick="setDaySlotDuration(20)" class="timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition shadow-xs" data-dur="20">20 min</button>
                                    <button type="button" onclick="setDaySlotDuration(30)" class="timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-indigo-600 bg-indigo-600 text-white shadow-sm transition" data-dur="30">30 min</button>
                                    <button type="button" onclick="setDaySlotDuration(45)" class="timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition shadow-xs" data-dur="45">45 min</button>
                                    <button type="button" onclick="setDaySlotDuration(60)" class="timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition shadow-xs" data-dur="60">60 min</button>
                                </div>
                            </div>

                            <!-- Lunch / Break Time (Optional) -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                        <i class="fa-solid fa-mug-saucer text-amber-500"></i> Break / Lunch Time Exclusion (Optional)
                                    </span>
                                    <button type="button" onclick="clearDayBreak()" class="text-[11px] text-slate-400 hover:text-rose-600 font-bold transition">
                                        <i class="fa-solid fa-xmark mr-0.5"></i> Clear Break
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Break Starts</label>
                                        <select id="timing-break-start" onchange="recalculateCurrentDaySlots()" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold outline-none bg-slate-50 focus:bg-white">
                                            <option value="">None (Continuous)</option>
                                            <!-- Populated dynamically -->
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Break Ends</label>
                                        <select id="timing-break-end" onchange="recalculateCurrentDaySlots()" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold outline-none bg-slate-50 focus:bg-white">
                                            <option value="">None (Continuous)</option>
                                            <!-- Populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Presets Toolbar -->
                        <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i> Quick Shift Presets:
                            </span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" onclick="applySchedulePreset('standard')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-indigo-50 text-indigo-700 border border-slate-200 transition shadow-2xs">
                                    9 AM - 5 PM (30m)
                                </button>
                                <button type="button" onclick="applySchedulePreset('morning')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-slate-200 transition shadow-2xs">
                                    9 AM - 1 PM (20m)
                                </button>
                                <button type="button" onclick="applySchedulePreset('evening')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-purple-50 text-purple-700 border border-slate-200 transition shadow-2xs">
                                    2 PM - 8 PM (15m)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Day Off State Banner (When Toggled Off) -->
                    <div id="timing-day-off-banner" class="hidden apple-card p-8 text-center bg-amber-50/40 rounded-2xl border border-amber-200">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>
                        <h5 class="font-black text-amber-900 text-base">Marked as Day Off</h5>
                        <p class="text-xs text-amber-700/90 mt-1 max-w-sm mx-auto">No consultation slots will be generated or available for booking on this day. Patients will see this day as closed.</p>
                        <button type="button" onclick="document.getElementById('timing-day-avail-toggle').click()" class="mt-4 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow transition">
                            Enable as Working Day
                        </button>
                    </div>
                </div>

                <!-- Right Column: Live Slots Preview (lg:col-span-5) -->
                <div class="lg:col-span-5">
                    <div class="apple-card p-4 sm:p-5 border border-slate-200/80 bg-white h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-extrabold text-sm text-slate-900">Generated Slots</h4>
                                        <span class="text-xs font-bold text-slate-500">(<span id="timing-slots-day-name" class="text-indigo-600">Monday</span>)</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Click any slot chip to toggle it on/off.</p>
                                </div>
                                <span id="timing-slots-count-badge" class="text-xs font-black px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                    0 Slots
                                </span>
                            </div>

                            <!-- Slots Scroll Grid -->
                            <div id="timing-slots-chips-grid" class="flex flex-wrap gap-2 max-h-[380px] overflow-y-auto custom-scrollbar p-3 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                                <!-- Chips loaded via JS -->
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                            <span><span class="w-2 h-2 inline-block rounded-full bg-emerald-500 mr-1"></span> Active Slot</span>
                            <span><span class="w-2 h-2 inline-block rounded-full bg-slate-300 mr-1"></span> Excluded (Click to restore)</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Actions -->
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
            <div class="text-xs text-slate-500 hidden sm:block">
                <span>Changes will apply to future appointment bookings for this doctor.</span>
            </div>
            <div class="flex items-center gap-2.5 ml-auto">
                <button type="button" onclick="closeDoctorTimingModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs transition">
                    Cancel
                </button>
                <button type="button" id="btn-save-timing-schedules" onclick="saveDoctorTimingSchedules()" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> <span>Save Day Schedules</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let tempDoctorPayload = null;
    let deleteDoctorId = null;
    let editDoctorId = null;
    let allDoctorsList = [];
    let allPatientsDropdownList = [];
    
    let currentDocView = 'schedule';
    let selectedDate = '<?php echo date("Y-m-d"); ?>';
    let sliderDates = [];
    let currentScheduleData = null;

    document.addEventListener('DOMContentLoaded', () => {
        fetchCategories();
        fetchDoctors();
        fetchPatientsList();
        loadDateSlider();
    });

    function setDocViewMode(mode) {
        currentDocView = mode;
        document.getElementById('btn-doc-view-schedule').className = mode === 'schedule' 
            ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold transition" 
            : "px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition";
        document.getElementById('btn-doc-view-directory').className = mode === 'directory' 
            ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold transition" 
            : "px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition";
        
        document.getElementById('view-schedule-section').classList.toggle('hidden', mode !== 'schedule');
        document.getElementById('view-directory-section').classList.toggle('hidden', mode !== 'directory');
    }

    async function loadDateSlider() {
        try {
            const res = await fetch('api/doctors.php?action=get_date_slider_data');
            const data = await res.json();
            if (data.status === 'success') {
                sliderDates = data.dates;
                renderDateSlider();
                loadScheduleForDate(selectedDate);
            }
        } catch (e) { console.error(e); }
    }

    function renderDateSlider() {
        const track = document.getElementById('date-slider-track');
        track.innerHTML = '';
        sliderDates.forEach(d => {
            const isSelected = d.date === selectedDate;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = isSelected 
                ? "shrink-0 py-2.5 px-4 rounded-2xl bg-blue-600 text-white font-bold shadow-md shadow-blue-500/20 flex flex-col items-center justify-center min-w-[90px] border border-blue-600 transition"
                : "shrink-0 py-2.5 px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200 flex flex-col items-center justify-center min-w-[90px] transition";
            
            const badgeBg = isSelected 
                ? "bg-white/25 text-white" 
                : (d.appointments_count > 0 ? "bg-blue-100 text-blue-700 font-bold" : "bg-slate-200/70 text-slate-500");

            item.innerHTML = `
                <span class="text-[10px] uppercase tracking-wider font-extrabold ${isSelected ? 'text-blue-100' : 'text-slate-500'}">${d.day_name}</span>
                <span class="text-sm font-black my-0.5">${d.display_date}</span>
                <span class="text-[9px] px-2 py-0.5 rounded-full ${badgeBg}">${d.appointments_count} appts</span>
            `;
            item.onclick = () => {
                selectedDate = d.date;
                renderDateSlider();
                loadScheduleForDate(selectedDate);
            };
            track.appendChild(item);
        });
    }

    function slideDateTrack(dir) {
        const track = document.getElementById('date-slider-track');
        track.scrollBy({ left: dir * 220, behavior: 'smooth' });
    }

    async function loadScheduleForDate(date) {
        try {
            const res = await fetch(`api/doctors.php?action=get_doctor_schedule&date=${date}`);
            const data = await res.json();
            if (data.status === 'success') {
                currentScheduleData = data;
                renderSelectedDateAppointments(data.appointments, date);
                renderDoctorScheduleCards(data.doctors, date);
            }
        } catch (e) { console.error(e); }
    }

    function renderSelectedDateAppointments(appointments, date) {
        const container = document.getElementById('selected-date-appointments-container');
        const heading = document.getElementById('selected-date-heading');
        const badge = document.getElementById('selected-date-badge');
        const statusPills = document.getElementById('selected-date-status-pills');
        
        const isToday = (date === new Date().toISOString().substring(0, 10));
        badge.textContent = isToday ? 'Today' : date;
        badge.className = isToday 
            ? "text-xs font-black px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800" 
            : "text-xs font-black px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800";
        heading.textContent = `Appointments on ${date}`;

        // Status counts
        const stageCounts = {
            checkedIn: appointments.filter(a => a.stage === 1).length,
            available: appointments.filter(a => a.stage === 2).length,
            waiting: appointments.filter(a => a.stage === 3).length,
            consulting: appointments.filter(a => a.stage === 4).length,
            completed: appointments.filter(a => a.stage >= 5 || a.status.includes('Discharged')).length
        };

        statusPills.innerHTML = `
            <span class="text-[11px] bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-bold">Total: ${appointments.length}</span>
            <span class="text-[11px] bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg font-bold">Checked-In: ${stageCounts.checkedIn}</span>
            <span class="text-[11px] bg-amber-50 text-amber-700 px-2.5 py-1 rounded-lg font-bold">Waiting: ${stageCounts.waiting}</span>
            <span class="text-[11px] bg-purple-50 text-purple-700 px-2.5 py-1 rounded-lg font-bold">Consulting: ${stageCounts.consulting}</span>
        `;

        container.innerHTML = '';
        if (appointments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-slate-400 text-xs">
                    <i class="fa-regular fa-calendar-xmark text-2xl text-slate-300 block mb-1.5"></i>
                    No appointments booked for this date yet. You can pre-book any available slot below.
                </div>
            `;
            return;
        }

        const grid = document.createElement('div');
        grid.className = "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3";

        appointments.forEach(a => {
            const card = document.createElement('div');
            card.className = "bg-slate-50 hover:bg-white rounded-xl p-3 border border-slate-200 transition shadow-sm flex flex-col justify-between";
            card.innerHTML = `
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="bg-blue-600 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-md shadow-sm">Token #${a.token_no}</span>
                        <span class="text-[10px] font-bold text-slate-700 bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                            <i class="fa-regular fa-clock text-slate-400 mr-1"></i>${a.slot || 'N/A'}
                        </span>
                    </div>
                    <h5 class="font-extrabold text-slate-900 text-xs leading-tight">${a.patient_name} ${a.patient_surname}</h5>
                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">${a.patient_id} • 🩸 ${a.blood_group || 'Unknown'}</div>
                    <div class="mt-2 text-[11px] text-slate-600 bg-white p-2 rounded-lg border border-slate-100 space-y-0.5">
                        <div><strong class="text-slate-700">Doctor:</strong> ${a.doctor_name || 'Unassigned'}</div>
                        <div class="truncate"><strong class="text-slate-700">Reason:</strong> ${a.symptoms || 'General'}</div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${a.stage === 4 ? 'bg-purple-100 text-purple-800' : (a.stage === 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700')}">${a.status}</span>
                    <span class="text-[10px] text-slate-400 font-mono">Stage ${a.stage}</span>
                </div>
            `;
            grid.appendChild(card);
        });
        container.appendChild(grid);
    }

    function renderDoctorScheduleCards(doctors, date) {
        const container = document.getElementById('doctor-slots-cards-container');
        container.innerHTML = '';

        if (doctors.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-slate-400 text-xs">No doctors available. Click "Add Doctor" above to get started.</div>`;
            return;
        }

        doctors.forEach(d => {
            const card = document.createElement('div');
            card.className = "bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4 transition hover:shadow-md";

            const specialtiesHtml = (d.categories || []).map(c => `<span class="inline-block bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-0.5 rounded-md border border-blue-100 mr-1 mb-1">${c}</span>`).join('') || '<span class="text-xs text-slate-400 italic">General Practice</span>';

            // Slots or Day Off content
            let contentHtml = '';
            if (d.is_available === false) {
                contentHtml = `
                    <div class="py-5 px-4 bg-amber-50/70 border border-amber-200 rounded-xl text-center space-y-2">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-100 text-amber-600 text-sm">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>
                        <h5 class="font-extrabold text-amber-900 text-xs sm:text-sm">Off Duty / Day Off (${d.day_of_week || 'Selected Day'})</h5>
                        <p class="text-[11px] text-amber-700/80 max-w-xs mx-auto">No consultation time slots scheduled for this day.</p>
                        <button type="button" onclick="openDoctorTimingModal('${d.id}', '${escapeJs(d.name)}', '${d.day_of_week || 'Monday'}')" class="mt-1 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs transition inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-clock text-xs"></i> Set Timings or Enable
                        </button>
                    </div>
                `;
            } else {
                let slotsHtml = '';
                if (!d.slots || d.slots.length === 0) {
                    slotsHtml = `<div class="col-span-full text-center py-4 text-xs text-slate-400">No time slots generated for this shift.</div>`;
                } else {
                    d.slots.forEach(s => {
                        if (s.is_booked) {
                            const patName = s.appointment ? `${s.appointment.patient_name} ${s.appointment.patient_surname}` : 'Booked';
                            slotsHtml += `
                                <div class="p-2 rounded-xl bg-slate-100 border border-slate-200 text-center cursor-not-allowed">
                                    <span class="block text-[11px] font-bold text-slate-400 line-through">${s.time}</span>
                                    <span class="block text-[9px] text-slate-500 font-semibold truncate mt-0.5" title="${patName}">${patName}</span>
                                </div>
                            `;
                        } else {
                            slotsHtml += `
                                <button type="button" onclick="openAdvanceDoctorBookModal('${d.id}', '${escapeJs(d.name)}', '${date}', '${s.time}')" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition text-center shadow-sm hover:scale-105" title="Click to book this slot">
                                    <span class="block text-[11px] font-black">${s.time}</span>
                                    <span class="block text-[9px] font-bold text-emerald-600 mt-0.5"><i class="fa-solid fa-plus text-[8px] mr-0.5"></i>Available</span>
                                </button>
                            `;
                        }
                    });
                }

                contentHtml = `
                    <div>
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-2">
                            <span class="flex items-center gap-1.5">
                                <span>Time Slots (${date}):</span>
                                <span class="text-[9px] font-mono font-bold bg-indigo-50 text-indigo-700 px-1.5 py-0.2 rounded border border-indigo-100">${d.duration_minutes || 30}m slots</span>
                            </span>
                            <span class="text-xs text-indigo-600 font-semibold">Click slot to pre-book</span>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto custom-scrollbar p-1">
                            ${slotsHtml}
                        </div>
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner shrink-0">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-extrabold text-slate-900 text-base leading-tight truncate">${d.name}</h4>
                            <p class="text-xs text-slate-500 mt-0.5 truncate"><i class="fa-solid fa-phone text-[10px] mr-1"></i>${d.phone || 'No phone'}</p>
                            <div class="text-[10px] text-slate-500 font-semibold mt-0.5 flex items-center gap-1 truncate">
                                <i class="fa-regular fa-clock text-slate-400"></i>
                                <span>${d.timing || '09:00 AM - 05:00 PM'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-xl ${d.available_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'}">
                            ${d.is_available ? `${d.available_count} / ${d.total_slots} Free` : 'Day Off'}
                        </span>
                        <button type="button" onclick="openDoctorTimingModal('${d.id}', '${escapeJs(d.name)}', '${d.day_of_week || 'Monday'}')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-lg border border-indigo-200/80 transition flex items-center gap-1">
                            <i class="fa-regular fa-clock text-[10px]"></i> Timings & Slots
                        </button>
                    </div>
                </div>

                <div>
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1.5">Specialties</div>
                    <div>${specialtiesHtml}</div>
                </div>

                ${contentHtml}
            `;
            container.appendChild(card);
        });
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    async function fetchPatientsList() {
        try {
            const res = await fetch('api/patients.php?action=get_all');
            const data = await res.json();
            if (data.status === 'success') {
                allPatientsDropdownList = data.patients || [];
            }
        } catch (e) {}
    }

    function handleAdvDoctorPatientSearch(query) {
        const dropdown = document.getElementById('adv-patient-suggestions');
        const clearBtn = document.getElementById('adv-patient-search-clear');
        const rawQ = (query || '').trim();
        
        if (clearBtn) clearBtn.classList.toggle('hidden', rawQ.length === 0);

        if (!allPatientsDropdownList || allPatientsDropdownList.length === 0) {
            dropdown.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center">Loading patients...</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        const terms = rawQ.toLowerCase().split(/\s+/).filter(Boolean);
        
        const matches = allPatientsDropdownList.filter(p => {
            if (terms.length === 0) return true;
            const searchable = [
                p.id,
                p.name,
                p.surname,
                `${p.name || ''} ${p.surname || ''}`,
                `${p.surname || ''} ${p.name || ''}`,
                `${p.name || ''} ${p.father_name || ''} ${p.surname || ''}`,
                p.father_name,
                p.phone,
                p.blood_group,
                `blood ${p.blood_group || ''}`,
                p.gender,
                p.age ? `${p.age}` : '',
                p.age ? `${p.age} y` : '',
                p.demographics,
                p.emergency_contact_name,
                p.emergency_contact_phone,
                p.reg_date
            ].filter(Boolean).join(' ').toLowerCase();

            return terms.every(term => searchable.includes(term));
        });

        dropdown.innerHTML = '';
        if (matches.length === 0) {
            dropdown.innerHTML = `
                <div class="p-4 text-center">
                    <p class="text-xs text-slate-500 font-semibold">No patient found matching "${escapeJs(rawQ)}"</p>
                    <a href="index.php" target="_blank" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        + Register New Patient in Reception
                    </a>
                </div>
            `;
        } else {
            matches.slice(0, 10).forEach(p => {
                const initials = (((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : '')) || 'P').toUpperCase();
                const div = document.createElement('div');
                div.className = "p-3 hover:bg-indigo-50/60 cursor-pointer flex items-center justify-between transition gap-2";
                div.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                            ${initials}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-extrabold text-slate-900 text-xs truncate">${p.name} ${p.surname}</span>
                                <span class="text-[9px] font-bold px-1 py-0.2 rounded bg-slate-100 text-slate-600 font-mono">${p.id}</span>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5 truncate">
                                📞 ${p.phone || 'N/A'} • 🩸 ${p.blood_group || 'Unknown'} • Father: ${p.father_name || 'N/A'}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2.5 py-1 rounded-lg text-[10px] shadow-sm shrink-0 transition">
                        Select
                    </button>
                `;
                div.onclick = () => selectAdvDoctorPatient(p);
                dropdown.appendChild(div);
            });
        }
        dropdown.classList.remove('hidden');
    }

    function selectAdvDoctorPatient(p) {
        document.getElementById('adv-book-patient-id').value = p.id;
        
        const initials = (((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : '')) || 'P').toUpperCase();
        document.getElementById('adv-selected-avatar').textContent = initials;
        document.getElementById('adv-selected-name').textContent = `${p.name} ${p.surname}`;
        document.getElementById('adv-selected-blood').textContent = `🩸 ${p.blood_group || 'Unknown'}`;
        document.getElementById('adv-selected-details').textContent = `MRN: ${p.id} • 📞 ${p.phone || 'N/A'} • Father: ${p.father_name || 'N/A'}`;
        
        const suggestions = document.getElementById('adv-patient-suggestions');
        if (suggestions) suggestions.classList.add('hidden');
        document.getElementById('adv-patient-search-box').classList.add('hidden');
        document.getElementById('adv-patient-selected-card').classList.remove('hidden');
    }

    function resetAdvDoctorPatientSelection() {
        document.getElementById('adv-book-patient-id').value = '';
        const card = document.getElementById('adv-patient-selected-card');
        const box = document.getElementById('adv-patient-search-box');
        if (card) card.classList.add('hidden');
        if (box) box.classList.remove('hidden');
        const input = document.getElementById('adv-patient-search-input');
        if (input) {
            input.value = '';
            input.focus();
            handleAdvDoctorPatientSearch('');
        }
    }

    function clearAdvDoctorPatientSearch() {
        const input = document.getElementById('adv-patient-search-input');
        if (input) {
            input.value = '';
            handleAdvDoctorPatientSearch('');
            input.focus();
        }
    }

    // Close suggestions dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#adv-patient-search-box')) {
            const dropdown = document.getElementById('adv-patient-suggestions');
            if (dropdown) dropdown.classList.add('hidden');
        }
    });

    function openAdvanceDoctorBookModal(docId, docName, date, slot) {
        document.getElementById('adv-book-doctor-id').value = docId;
        document.getElementById('adv-book-doc-name').textContent = docName;
        document.getElementById('adv-doc-subtitle').textContent = `With ${docName}`;
        document.getElementById('adv-book-date').value = date;
        document.getElementById('adv-book-date-display').textContent = date;
        document.getElementById('adv-book-slot').value = slot;
        document.getElementById('adv-book-slot-display').textContent = slot;
        document.getElementById('adv-book-symptoms').value = '';
        
        // Reset patient search state
        resetAdvDoctorPatientSelection();
        if (!allPatientsDropdownList || allPatientsDropdownList.length === 0) {
            fetchPatientsList();
        }

        document.getElementById('modal-advance-doctor-book').classList.remove('hidden');
    }

    function closeAdvanceDoctorBookModal() {
        document.getElementById('modal-advance-doctor-book').classList.add('hidden');
    }

    async function handleAdvanceDoctorBookSubmit(e) {
        e.preventDefault();
        const patient_id = document.getElementById('adv-book-patient-id').value;
        const doctor_id = document.getElementById('adv-book-doctor-id').value;
        const date = document.getElementById('adv-book-date').value;
        const slot = document.getElementById('adv-book-slot').value;
        const type = document.getElementById('adv-book-type').value;
        const symptoms = document.getElementById('adv-book-symptoms').value.trim();

        if (!patient_id) {
            showToast('Patient Required', 'Please select a registered patient.', 'error');
            return;
        }

        try {
            const res = await fetch('api/queue.php?action=book_advance', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ patient_id, doctor_id, date, slot, type, symptoms })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeAdvanceDoctorBookModal();
                showToast('Pre-Booked', 'Advance appointment scheduled successfully!');
                loadDateSlider();
                loadScheduleForDate(selectedDate);
            } else {
                showToast('Error', data.message, 'error');
            }
        } catch (err) {
            showToast('Error', 'Network error during advance booking', 'error');
        }
    }

    async function fetchCategories() {
        try {
            const res = await fetch('api/doctors.php?action=get_categories');
            const data = await res.json();
            const sel = document.getElementById('doc-categories');
            sel.innerHTML = '';
            if (data.status === 'success') {
                data.categories.forEach(c => {
                    sel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            }
        } catch (e) { console.error(e); }
    }

    async function fetchDoctors() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            const tbody = document.getElementById('doctors-tbody');
            tbody.innerHTML = '';
            if (data.status === 'success') {
                allDoctorsList = data.doctors;
                if (data.doctors.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-slate-400">No medical staff found.</td></tr>`;
                }
                data.doctors.forEach(d => {
                    const cats = d.categories.map(c => `<span class="inline-block bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded-full mr-1 mb-1 border border-blue-100">${c}</span>`).join('');
                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="py-4 px-6 font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-sm"><i class="fa-solid fa-user-doctor"></i></div>
                                ${d.name}
                            </td>
                            <td class="py-4 px-6 text-slate-500">${d.phone || 'N/A'}</td>
                            <td class="py-4 px-6">${cats}</td>
                            <td class="py-4 px-6 text-right flex justify-end items-center gap-1.5">
                                <button onclick="openDoctorTimingModal('${d.id}', '${escapeJs(d.name)}')" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs flex items-center gap-1.5 transition border border-indigo-200/60 shadow-xs" title="Manage Slot Timings & Duration"><i class="fa-regular fa-clock text-[11px]"></i> Timings & Slots</button>
                                <button onclick="openEditDoctor('${d.id}')" class="w-8 h-8 rounded-full text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition flex items-center justify-center" title="Edit Doctor"><i class="fa-solid fa-pen"></i></button>
                                <button onclick="promptDeleteDoctor('${d.id}', '${escapeJs(d.name)}')" class="w-8 h-8 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition flex items-center justify-center" title="Delete Doctor"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
            }
        } catch (e) { console.error(e); }
    }

    function openAddDoctor() {
        editDoctorId = null;
        document.getElementById('form-doctor').reset();
        document.getElementById('doc-modal-title').textContent = 'Add New Doctor';
        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    function openEditDoctor(id) {
        const d = allDoctorsList.find(x => x.id == id);
        if(!d) return;
        editDoctorId = id;
        document.getElementById('doc-modal-title').textContent = 'Edit Doctor';
        document.getElementById('doc-name').value = d.name;
        document.getElementById('doc-phone').value = d.phone;
        
        // Select categories
        const sel = document.getElementById('doc-categories');
        Array.from(sel.options).forEach(opt => {
            opt.selected = d.categories.includes(opt.text);
        });

        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    function handleDoctorSubmit(e) {
        e.preventDefault();
        const selOpts = Array.from(document.getElementById('doc-categories').selectedOptions);
        
        tempDoctorPayload = {
            id: editDoctorId,
            name: document.getElementById('doc-name').value.trim(),
            phone: document.getElementById('doc-phone').value.trim(),
            categories: selOpts.map(o => o.value),
            categoryNames: selOpts.map(o => o.text)
        };

        const html = `
            <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Name:</span> <span class="col-span-2 font-semibold text-slate-900">${tempDoctorPayload.name}</span></div>
            <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Phone:</span> <span class="col-span-2 font-semibold text-slate-900">${tempDoctorPayload.phone}</span></div>
            <div class="grid grid-cols-3 gap-2"><span class="font-bold text-slate-500">Specialties:</span> <span class="col-span-2 font-semibold text-slate-900">${tempDoctorPayload.categoryNames.join(', ')}</span></div>
        `;

        document.getElementById('modal-doctor').classList.add('hidden');
        
        const title = editDoctorId ? 'Confirm Doctor Edits' : 'Confirm New Doctor';
        
        openConfirmModal(
            title,
            'Please verify the details below.',
            html,
            executeSaveDoctor,
            'success'
        );
    }

    async function executeSaveDoctor() {
        try {
            const action = editDoctorId ? 'update_hospital_doctor' : 'create_hospital_doctor';
            const res = await fetch(`api/doctors.php?action=${action}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(tempDoctorPayload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('form-doctor').reset();
                showToast('Success', 'Doctor saved successfully');
                fetchDoctors();
                loadScheduleForDate(selectedDate);
            } else {
                showToast('Error', data.message, 'error');
            }
        } catch (err) { console.error(err); }
    }

    function promptDeleteDoctor(id, name) {
        deleteDoctorId = id;
        
        const html = `
            <div class="text-rose-600 font-bold mb-2">Warning: This action cannot be undone.</div>
            <div class="font-semibold text-slate-900">You are about to delete Doctor: ${name}</div>
            <p class="text-sm mt-1">This will revoke their access and remove their profile from the hospital directory.</p>
        `;

        openConfirmModal(
            'Delete Doctor',
            'Are you absolutely sure?',
            html,
            executeDeleteDoctor,
            'danger'
        );
    }

    async function executeDeleteDoctor() {
        try {
            const res = await fetch(`api/doctors.php?action=delete_hospital_doctor&id=${deleteDoctorId}`);
            const data = await res.json();
            if (data.status === 'success') {
                showToast('Deleted', 'Doctor removed successfully');
                fetchDoctors();
                loadScheduleForDate(selectedDate);
            } else {
                showToast('Error', data.message, 'error');
            }
        } catch (e) { console.error(e); }
    }

    // ================= DOCTOR PER-DAY SLOTS & TIMING MANAGEMENT =================
    let timingActiveDocId = null;
    let timingActiveDocName = '';
    let timingActiveDay = 'Monday';
    let activeDoctorDaySchedules = [];

    const standardDayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    function generateTimeOptions() {
        const times = [];
        // From 06:00 AM to 10:00 PM in 30 min increments
        for (let hour = 6; hour <= 22; hour++) {
            for (let min of [0, 30]) {
                if (hour === 22 && min > 0) break;
                const h12 = (hour % 12 === 0) ? 12 : (hour % 12);
                const ampm = (hour < 12) ? 'AM' : 'PM';
                const str = `${String(h12).padStart(2, '0')}:${String(min).padStart(2, '0')} ${ampm}`;
                times.push(str);
            }
        }
        return times;
    }

    function populateSelectWithOptions(selectId, options, selectedVal, includeNone = false) {
        const select = document.getElementById(selectId);
        if (!select) return;
        select.innerHTML = includeNone ? '<option value="">None (No Break)</option>' : '';
        options.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (t === selectedVal) opt.selected = true;
            select.appendChild(opt);
        });
        if (selectedVal && !options.includes(selectedVal)) {
            const customOpt = document.createElement('option');
            customOpt.value = selectedVal;
            customOpt.textContent = selectedVal;
            customOpt.selected = true;
            select.appendChild(customOpt);
        }
    }

    async function openDoctorTimingModal(docId, docName, defaultDay) {
        if (!docId) {
            // If called from top header without ID, pick first available doctor
            if (allDoctorsList && allDoctorsList.length > 0) {
                docId = allDoctorsList[0].id;
                docName = allDoctorsList[0].name;
            } else {
                showToast('No Doctors', 'Please register a doctor first.', 'warning');
                return;
            }
        }

        timingActiveDocId = docId;
        timingActiveDocName = docName || 'Doctor';
        timingActiveDay = defaultDay || 'Monday';

        // Populate doctor switch dropdown
        const docSelect = document.getElementById('timing-doctor-select');
        if (docSelect && allDoctorsList) {
            docSelect.innerHTML = '';
            allDoctorsList.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                if (d.id === docId) {
                    opt.selected = true;
                    timingActiveDocName = d.name;
                }
                docSelect.appendChild(opt);
            });
        }

        document.getElementById('timing-doc-badge').textContent = docId;
        document.getElementById('timing-doc-subtitle').textContent = `Configuring consultation hours, slot durations, and availability for ${timingActiveDocName}.`;

        try {
            const res = await fetch(`api/doctors.php?action=get_doctor_day_schedules&doctor_id=${docId}`);
            const data = await res.json();
            if (data.status === 'success') {
                activeDoctorDaySchedules = data.schedules || [];
                renderTimingDayTabs();
                renderTimingDayEditor();
                document.getElementById('modal-doctor-timings').classList.remove('hidden');
            } else {
                showToast('Error', data.message || 'Could not load schedules', 'error');
            }
        } catch(e) {
            console.error(e);
            showToast('Network Error', 'Failed to connect to server', 'error');
        }
    }

    function switchTimingDoctor(newDocId) {
        const doc = (allDoctorsList || []).find(d => d.id === newDocId);
        openDoctorTimingModal(newDocId, doc ? doc.name : 'Doctor', timingActiveDay);
    }

    function closeDoctorTimingModal() {
        document.getElementById('modal-doctor-timings').classList.add('hidden');
    }

    function renderTimingDayTabs() {
        const container = document.getElementById('timing-day-tabs');
        if (!container) return;
        container.innerHTML = '';

        standardDayOrder.forEach(day => {
            const dayData = activeDoctorDaySchedules.find(s => s.day_of_week === day) || { is_available: 1 };
            const isActive = (day === timingActiveDay);
            const isAvail = (Number(dayData.is_available) === 1);
            const slotCount = (dayData.slots || []).length;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `py-2.5 px-2 rounded-xl transition flex flex-col items-center justify-center gap-1 ${
                isActive 
                    ? 'bg-indigo-600 text-white shadow-md font-black ring-2 ring-indigo-600/30' 
                    : (isAvail 
                        ? 'bg-white hover:bg-slate-50 text-slate-700 font-bold border border-slate-200/80 shadow-2xs' 
                        : 'bg-slate-50 text-slate-400 font-semibold border border-transparent')
            }`;
            btn.innerHTML = `
                <span class="text-xs tracking-tight">${day.substring(0, 3)}</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded-full font-extrabold ${
                    isActive 
                        ? 'bg-white/20 text-white' 
                        : (isAvail ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200/80 text-slate-500')
                }">
                    ${isAvail ? (slotCount > 0 ? `${slotCount}s` : 'Open') : 'Off'}
                </span>
            `;
            btn.onclick = () => switchTimingDay(day);
            container.appendChild(btn);
        });
    }

    function switchTimingDay(dayName) {
        timingActiveDay = dayName;
        renderTimingDayTabs();
        renderTimingDayEditor();
    }

    function getActiveDayConfig() {
        let conf = activeDoctorDaySchedules.find(s => s.day_of_week === timingActiveDay);
        if (!conf) {
            conf = {
                day_of_week: timingActiveDay,
                is_available: 1,
                start_time: '09:00 AM',
                end_time: '05:00 PM',
                duration_minutes: 30,
                break_start: '01:00 PM',
                break_end: '02:00 PM',
                slots: []
            };
            activeDoctorDaySchedules.push(conf);
        }
        return conf;
    }

    function renderTimingDayEditor() {
        const conf = getActiveDayConfig();
        const timeOptions = generateTimeOptions();

        document.getElementById('timing-active-day-title').textContent = `${timingActiveDay} Schedule`;
        document.getElementById('timing-slots-day-name').textContent = timingActiveDay;
        
        const isAvail = (Number(conf.is_available) === 1);
        const toggle = document.getElementById('timing-day-avail-toggle');
        const availLabel = document.getElementById('timing-avail-label');
        const availDot = document.getElementById('timing-avail-dot');
        const availDesc = document.getElementById('timing-avail-status-desc');
        const controls = document.getElementById('timing-controls-container');
        const offBanner = document.getElementById('timing-day-off-banner');

        toggle.checked = isAvail;
        availLabel.textContent = isAvail ? 'Available' : 'Day Off';
        availLabel.className = isAvail ? 'text-xs font-black text-emerald-700' : 'text-xs font-black text-amber-700';

        if (availDot) {
            availDot.className = isAvail ? 'w-2.5 h-2.5 rounded-full bg-emerald-500' : 'w-2.5 h-2.5 rounded-full bg-amber-400';
        }
        if (availDesc) {
            availDesc.textContent = isAvail ? 'Consultations and patient booking enabled.' : 'Day Off. Consultations closed on this day.';
        }

        controls.classList.toggle('hidden', !isAvail);
        offBanner.classList.toggle('hidden', isAvail);

        // Populate Start and End Times
        populateSelectWithOptions('timing-start-time', timeOptions, conf.start_time || '09:00 AM');
        populateSelectWithOptions('timing-end-time', timeOptions, conf.end_time || '05:00 PM');

        // Populate Break Start and End
        populateSelectWithOptions('timing-break-start', timeOptions, conf.break_start || '', true);
        populateSelectWithOptions('timing-break-end', timeOptions, conf.break_end || '', true);

        // Update Duration Buttons and Display
        updateDurationUI(conf.duration_minutes || 30);

        // Recalculate and render slot chips
        renderSlotsChips(conf);
    }

    function handleDayAvailToggle(isChecked) {
        const conf = getActiveDayConfig();
        conf.is_available = isChecked ? 1 : 0;
        renderTimingDayTabs();
        renderTimingDayEditor();
    }

    function updateDurationUI(dur) {
        document.getElementById('timing-duration-display').textContent = `${dur} Minutes`;
        document.querySelectorAll('.timing-dur-btn').forEach(btn => {
            const btnDur = Number(btn.getAttribute('data-dur'));
            if (btnDur === dur) {
                btn.className = "timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-indigo-600 bg-indigo-600 text-white shadow-sm transition";
            } else {
                btn.className = "timing-dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition shadow-xs";
            }
        });
    }

    function setDaySlotDuration(dur) {
        const conf = getActiveDayConfig();
        conf.duration_minutes = dur;
        updateDurationUI(dur);
        conf.custom_slots = null;
        recalculateCurrentDaySlots();
    }

    function clearDayBreak() {
        const bStart = document.getElementById('timing-break-start');
        const bEnd = document.getElementById('timing-break-end');
        if (bStart) bStart.value = '';
        if (bEnd) bEnd.value = '';
        recalculateCurrentDaySlots();
    }

    function calculateSlots(startTime, endTime, durationMinutes, breakStart, breakEnd) {
        durationMinutes = Number(durationMinutes) || 30;
        if (durationMinutes <= 0) durationMinutes = 30;

        function parseTime(tStr) {
            if (!tStr) return null;
            const match = tStr.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (!match) return null;
            let h = parseInt(match[1], 10);
            const m = parseInt(match[2], 10);
            const p = match[3].toUpperCase();
            if (p === 'PM' && h < 12) h += 12;
            if (p === 'AM' && h === 12) h = 0;
            return h * 60 + m;
        }

        const startMin = parseTime(startTime);
        const endMin = parseTime(endTime);
        if (startMin === null || endMin === null || startMin >= endMin) return [];

        const breakStartMin = breakStart ? parseTime(breakStart) : null;
        const breakEndMin = breakEnd ? parseTime(breakEnd) : null;

        const slots = [];
        let curr = startMin;
        while (curr + durationMinutes <= endMin) {
            const slotEnd = curr + durationMinutes;
            let inBreak = false;
            if (breakStartMin !== null && breakEndMin !== null && breakStartMin < breakEndMin) {
                if (curr < breakEndMin && slotEnd > breakStartMin) {
                    inBreak = true;
                }
            }

            if (!inBreak) {
                const h24 = Math.floor(curr / 60);
                const m = curr % 60;
                const h12 = (h24 % 12 === 0) ? 12 : (h24 % 12);
                const ampm = (h24 < 12) ? 'AM' : 'PM';
                slots.push(`${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`);
            }
            curr += durationMinutes;
        }
        return slots;
    }

    function recalculateCurrentDaySlots() {
        const conf = getActiveDayConfig();
        const startVal = document.getElementById('timing-start-time').value;
        const endVal = document.getElementById('timing-end-time').value;
        const breakStartVal = document.getElementById('timing-break-start').value;
        const breakEndVal = document.getElementById('timing-break-end').value;

        conf.start_time = startVal;
        conf.end_time = endVal;
        conf.break_start = breakStartVal || null;
        conf.break_end = breakEndVal || null;

        const generated = calculateSlots(startVal, endVal, conf.duration_minutes, breakStartVal, breakEndVal);
        conf.slots = generated;
        conf.custom_slots = generated;
        renderSlotsChips(conf);
        renderTimingDayTabs();
    }

    function renderSlotsChips(conf) {
        const container = document.getElementById('timing-slots-chips-grid');
        const countBadge = document.getElementById('timing-slots-count-badge');
        if (!container || !countBadge) return;

        const activeSlots = conf.slots || [];
        const fullGenerated = calculateSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);
        
        countBadge.textContent = `${activeSlots.length} ${activeSlots.length === 1 ? 'Slot' : 'Slots'}`;

        if (fullGenerated.length === 0) {
            container.innerHTML = `<span class="text-xs text-slate-400 italic p-3 text-center w-full">No slots generated. Check shift start and end times.</span>`;
            return;
        }

        container.innerHTML = fullGenerated.map(s => {
            const isActive = activeSlots.includes(s);
            if (isActive) {
                return `
                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-800 shadow-2xs hover:border-rose-300 hover:bg-rose-50/50 hover:text-rose-700 transition cursor-pointer group" title="Click to exclude this slot">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>${s}</span>
                        <i class="fa-solid fa-xmark text-[9px] text-slate-300 group-hover:text-rose-600 transition"></i>
                    </button>
                `;
            } else {
                return `
                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/80 border border-dashed border-slate-300 text-xs font-mono font-medium text-slate-400 line-through hover:border-emerald-300 hover:bg-emerald-50/60 hover:text-emerald-700 transition cursor-pointer group" title="Click to restore this slot">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-emerald-500"></span>
                        <span>${s}</span>
                        <i class="fa-solid fa-plus text-[9px] text-slate-400 group-hover:text-emerald-600 transition"></i>
                    </button>
                `;
            }
        }).join('');
    }

    function toggleSlotExclusion(slotTime) {
        const conf = getActiveDayConfig();
        const currentSlots = conf.slots || [];
        if (currentSlots.includes(slotTime)) {
            conf.slots = currentSlots.filter(s => s !== slotTime);
        } else {
            conf.slots = [...currentSlots, slotTime];
            // Sort time slots chronologically
            conf.slots.sort((a, b) => {
                const parse = (t) => {
                    const m = t.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
                    if (!m) return 0;
                    let h = parseInt(m[1], 10), min = parseInt(m[2], 10), p = m[3].toUpperCase();
                    if (p === 'PM' && h < 12) h += 12;
                    if (p === 'AM' && h === 12) h = 0;
                    return h * 60 + min;
                };
                return parse(a) - parse(b);
            });
        }
        conf.custom_slots = conf.slots;
        renderSlotsChips(conf);
        renderTimingDayTabs();
    }

    function applySchedulePreset(type) {
        let preset = {
            start: '09:00 AM',
            end: '05:00 PM',
            dur: 30,
            bStart: '01:00 PM',
            bEnd: '02:00 PM'
        };
        if (type === 'morning') {
            preset = { start: '09:00 AM', end: '01:00 PM', dur: 20, bStart: null, bEnd: null };
        } else if (type === 'evening') {
            preset = { start: '02:00 PM', end: '08:00 PM', dur: 15, bStart: null, bEnd: null };
        }

        const conf = getActiveDayConfig();
        conf.is_available = 1;
        conf.start_time = preset.start;
        conf.end_time = preset.end;
        conf.duration_minutes = preset.dur;
        conf.break_start = preset.bStart;
        conf.break_end = preset.bEnd;
        conf.slots = calculateSlots(preset.start, preset.end, preset.dur, preset.bStart, preset.bEnd);
        conf.custom_slots = conf.slots;

        renderTimingDayTabs();
        renderTimingDayEditor();
        showToast('Preset Applied', `Applied ${preset.start} - ${preset.end} (${preset.dur}m) to ${timingActiveDay}`, 'success');
    }

    function copyCurrentDayToWeekdays() {
        const conf = getActiveDayConfig();
        ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].forEach(day => {
            let dConf = activeDoctorDaySchedules.find(s => s.day_of_week === day);
            if (!dConf) {
                dConf = { day_of_week: day };
                activeDoctorDaySchedules.push(dConf);
            }
            dConf.is_available = conf.is_available;
            dConf.start_time = conf.start_time;
            dConf.end_time = conf.end_time;
            dConf.duration_minutes = conf.duration_minutes;
            dConf.break_start = conf.break_start;
            dConf.break_end = conf.break_end;
            dConf.slots = [...(conf.slots || [])];
            dConf.custom_slots = dConf.slots;
        });

        renderTimingDayTabs();
        showToast('Copied to Weekdays', `${timingActiveDay} schedule copied to Mon - Fri!`, 'success');
    }

    function copyCurrentDayToAll() {
        const conf = getActiveDayConfig();
        standardDayOrder.forEach(day => {
            if (day === timingActiveDay) return;
            let dConf = activeDoctorDaySchedules.find(s => s.day_of_week === day);
            if (!dConf) {
                dConf = { day_of_week: day };
                activeDoctorDaySchedules.push(dConf);
            }
            dConf.is_available = conf.is_available;
            dConf.start_time = conf.start_time;
            dConf.end_time = conf.end_time;
            dConf.duration_minutes = conf.duration_minutes;
            dConf.break_start = conf.break_start;
            dConf.break_end = conf.break_end;
            dConf.slots = [...(conf.slots || [])];
            dConf.custom_slots = dConf.slots;
        });

        renderTimingDayTabs();
        showToast('Copied to All Days', `${timingActiveDay} schedule copied to all 7 days!`, 'success');
    }

    async function saveDoctorTimingSchedules() {
        const saveBtn = document.getElementById('btn-save-timing-schedules');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const payload = {
                doctor_id: timingActiveDocId,
                schedules: activeDoctorDaySchedules.map(s => ({
                    day_of_week: s.day_of_week,
                    is_available: s.is_available,
                    start_time: s.start_time,
                    end_time: s.end_time,
                    duration_minutes: s.duration_minutes,
                    break_start: s.break_start,
                    break_end: s.break_end,
                    custom_slots: s.slots
                }))
            };

            const res = await fetch('api/doctors.php?action=save_doctor_day_schedules', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                showToast('Schedule Saved', `Slot timings & durations for ${timingActiveDocName} saved successfully!`, 'success');
                closeDoctorTimingModal();
                loadScheduleForDate(selectedDate);
                fetchDoctors();
            } else {
                showToast('Error', data.message || 'Could not save schedules', 'error');
            }
        } catch(e) {
            console.error(e);
            showToast('Save Failed', 'Server connection error', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> <span>Save Day Schedules</span>';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

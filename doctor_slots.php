<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$selected_doctor_id = $_GET['doctor_id'] ?? '';
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Doctor Timings & Slots</span>
            <span id="unsaved-badge" class="hidden text-xs font-black px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 animate-pulse">
                ● Unsaved Changes
            </span>
        </h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">Set daily working hours, slot durations, and consultation times.</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="doctors.php" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl border border-slate-200 text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> <span>Doctors</span>
        </a>
        <a href="appointments.php" class="bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold py-2.5 px-4 rounded-xl border border-purple-200 text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-calendar-check"></i> <span>Appointments</span>
        </a>
    </div>
</div>

<!-- ================= DOCTOR SELECTOR (BIG & CLEAR) ================= -->
<div class="bg-white p-4 sm:p-6 rounded-2xl border-2 border-slate-200/90 shadow-sm mb-5 sm:mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Big Doctor Select -->
        <div class="flex-1 min-w-0">
            <label class="block text-sm font-black uppercase tracking-wider text-slate-600 mb-2">Select Doctor</label>
            <div class="relative">
                <select id="slot-doctor-select" onchange="onDoctorChanged(this.value)" class="w-full h-14 pl-12 pr-10 text-base sm:text-lg font-black text-slate-900 bg-slate-50 border-2 border-slate-300 rounded-2xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer appearance-none shadow-sm">
                    <option value="">Loading doctors...</option>
                </select>
                <i class="fa-solid fa-user-doctor absolute left-4 top-1/2 -translate-y-1/2 text-purple-600 text-xl pointer-events-none"></i>
                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
            </div>
        </div>

        <!-- Doctor Details & Slot Stats -->
        <div id="doctor-info-strip" class="flex flex-wrap items-center gap-4 bg-purple-50/80 px-5 py-3.5 rounded-2xl border-2 border-purple-100">
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-sm" id="doc-avatar-initials">
                DR
            </div>
            <div class="min-w-0 pr-2">
                <h3 id="doc-name-display" class="font-black text-base sm:text-lg text-slate-900 truncate">Doctor Name</h3>
                <p id="doc-specialties-display" class="text-xs sm:text-sm font-bold text-purple-700 truncate">General Medicine</p>
            </div>
            <div class="flex items-center gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 sm:border-l border-purple-200 sm:pl-4">
                <div class="text-center">
                    <span class="block text-[10px] font-black uppercase text-purple-600">Weekly Slots</span>
                    <span id="doc-total-weekly-slots" class="text-lg sm:text-xl font-black text-purple-950">0</span>
                </div>
                <div class="text-center">
                    <span class="block text-[10px] font-black uppercase text-emerald-600">Open Days</span>
                    <span id="doc-active-days-count" class="text-lg sm:text-xl font-black text-emerald-700">0 / 7</span>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- ================= 7-DAY NAVIGATION RIBBON (BIG BUTTONS) ================= -->
<div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200/90 shadow-sm mb-5 sm:mb-6">
    <div class="flex items-center justify-between mb-3">
        <span class="text-sm font-black text-slate-900">Select Day to Edit</span>
        <div class="flex items-center gap-2">
            <button type="button" onclick="copyActiveDayToWeekdays()" class="text-xs font-black text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition cursor-pointer">
                <i class="fa-regular fa-copy mr-1"></i> Copy to Mon-Fri
            </button>
            <button type="button" onclick="copyActiveDayToAll()" class="text-xs font-black text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition cursor-pointer">
                <i class="fa-solid fa-clone mr-1"></i> Copy to All Days
            </button>
        </div>
    </div>

    <!-- 7 Big Day Tabs -->
    <div id="day-tabs-grid" class="flex overflow-x-auto no-scrollbar sm:grid sm:grid-cols-7 gap-2.5 pb-1">
        <!-- Rendered dynamically with large, bold text -->
    </div>
</div>

<!-- ================= DAY CONFIGURATOR (2 COLUMNS) ================= -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start mb-6">
    
    <!-- LEFT COLUMN: Shift Settings (lg:col-span-6) -->
    <div class="lg:col-span-6 space-y-5">
        
        <!-- Day Working Status Card (Big Toggle) -->
        <div class="bg-white p-5 rounded-2xl border-2 border-slate-200/90 shadow-sm flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div id="day-status-avatar" class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-black shrink-0">
                    <i class="fa-solid fa-calendar-check" id="day-status-icon"></i>
                </div>
                <div>
                    <h3 id="active-day-heading" class="text-lg sm:text-xl font-black text-slate-900">Monday</h3>
                    <p id="day-avail-subtext" class="text-xs sm:text-sm font-bold text-emerald-700">Open for patient appointments</p>
                </div>
            </div>

            <!-- Big tactile toggle -->
            <label class="inline-flex items-center gap-2.5 cursor-pointer select-none shrink-0">
                <input type="checkbox" id="day-avail-toggle" onchange="toggleDayAvailability(this.checked)" class="sr-only peer" checked>
                <div class="w-16 h-9 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-7 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
            </label>
        </div>

        <!-- Shift & Duration Controls (Visible when Day is Open) -->
        <div id="shift-controls-section" class="bg-white p-5 sm:p-6 rounded-2xl border-2 border-slate-200/90 shadow-sm space-y-5">
            

            <!-- Start & End Time (Big Selects) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Shift Starts</label>
                    <div class="relative">
                        <select id="shift-start-time" onchange="onTimeParamsChanged()" class="w-full h-13 pl-4 pr-9 text-base sm:text-lg font-black text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer appearance-none shadow-sm">
                            <!-- Populated via JS -->
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Shift Ends</label>
                    <div class="relative">
                        <select id="shift-end-time" onchange="onTimeParamsChanged()" class="w-full h-13 pl-4 pr-9 text-base sm:text-lg font-black text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer appearance-none shadow-sm">
                            <!-- Populated via JS -->
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <!-- Slot Duration (Big Buttons) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-600">Consultation Duration</label>
                    <span id="active-duration-badge" class="text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg">30 Min</span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    <button type="button" onclick="setSlotDuration(15)" class="dur-btn py-3 px-2 rounded-xl border-2 transition font-black text-sm sm:text-base cursor-pointer text-center" data-dur="15">
                        15 min
                    </button>
                    <button type="button" onclick="setSlotDuration(20)" class="dur-btn py-3 px-2 rounded-xl border-2 transition font-black text-sm sm:text-base cursor-pointer text-center" data-dur="20">
                        20 min
                    </button>
                    <button type="button" onclick="setSlotDuration(30)" class="dur-btn py-3 px-2 rounded-xl border-2 transition font-black text-sm sm:text-base cursor-pointer text-center" data-dur="30">
                        30 min
                    </button>
                    <button type="button" onclick="setSlotDuration(45)" class="dur-btn py-3 px-2 rounded-xl border-2 transition font-black text-sm sm:text-base cursor-pointer text-center" data-dur="45">
                        45 min
                    </button>
                    <button type="button" onclick="setSlotDuration(60)" class="dur-btn py-3 px-2 rounded-xl border-2 transition font-black text-sm sm:text-base cursor-pointer text-center" data-dur="60">
                        60 min
                    </button>
                </div>
            </div>

            <!-- Lunch Break (Simple Switch) -->
            <div class="pt-4 border-t-2 border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-mug-saucer text-amber-500"></i> Lunch / Tea Break
                    </span>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <span id="break-toggle-label" class="text-xs font-bold text-slate-600">Included</span>
                        <input type="checkbox" id="break-enabled-toggle" onchange="onBreakToggleChanged(this.checked)" class="sr-only peer" checked>
                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500 shadow-inner"></div>
                    </label>
                </div>

                <div id="break-times-row" class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/50 p-3.5 rounded-2xl border border-amber-200">
                    <div>
                        <span class="block text-xs font-bold text-slate-600 mb-1">Break Starts</span>
                        <select id="break-start-time" onchange="onTimeParamsChanged()" class="w-full h-11 px-3 text-sm font-bold text-slate-900 bg-white border border-slate-300 rounded-xl outline-none cursor-pointer">
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-600 mb-1">Break Ends</span>
                        <select id="break-end-time" onchange="onTimeParamsChanged()" class="w-full h-11 px-3 text-sm font-bold text-slate-900 bg-white border border-slate-300 rounded-xl outline-none cursor-pointer">
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </div>

                <div id="break-disabled-notice" class="hidden p-3 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs font-bold text-slate-500">
                    No break scheduled. Slots run continuously.
                </div>
            </div>

        </div>

        <!-- Day Off Notice (Visible when Day is Off) -->
        <div id="day-off-banner" class="hidden bg-amber-50 p-8 rounded-2xl border-2 border-amber-200 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto font-black">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h4 class="text-lg font-black text-amber-950">Day Off (Closed)</h4>
            <p class="text-sm font-medium text-amber-800">No appointments will be booked for this doctor on <span class="font-bold" id="day-off-name">Monday</span>.</p>
            <button type="button" onclick="document.getElementById('day-avail-toggle').click()" class="mt-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm shadow-md transition inline-flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-calendar-plus"></i> <span>Make This Day Open</span>
            </button>
        </div>

    </div>

    <!-- RIGHT COLUMN: Live Slots & Big Save Button (lg:col-span-6) -->
    <div class="lg:col-span-6 space-y-5">
        
        <!-- Slots Preview Box -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border-2 border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div>
                <!-- Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-slate-100">
                    <div>
                        <h4 class="font-black text-base sm:text-lg text-slate-900 flex items-center gap-2">
                            <span>Consultation Slots</span>
                            <span class="text-purple-700" id="slots-preview-day-name">(Monday)</span>
                        </h4>
                        <p class="text-xs font-bold text-slate-400 mt-0.5">Tap any slot below to turn it ON or OFF</p>
                    </div>
                    <span id="slots-count-pill" class="text-sm font-black px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 shrink-0">
                        0 Slots
                    </span>
                </div>

                <!-- Fast Bulk Toggles -->
                <div class="flex items-center justify-between gap-2 mb-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <span class="text-xs font-black text-slate-600">Quick Adjust:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="enableAllDaySlots()" class="px-3 py-1.5 rounded-lg bg-white hover:bg-emerald-50 text-emerald-700 border border-slate-200 font-extrabold text-xs transition cursor-pointer">
                            Enable All
                        </button>
                        <button type="button" onclick="disableAllDaySlots()" class="px-3 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-rose-700 border border-slate-200 font-extrabold text-xs transition cursor-pointer">
                            Disable All
                        </button>
                        <button type="button" onclick="resetDaySlots()" class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-700 border border-slate-200 font-extrabold text-xs transition cursor-pointer">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Grouped Chips Container -->
                <div id="slots-chips-container" class="space-y-3 max-h-[440px] overflow-y-auto custom-scrollbar p-3 bg-slate-50/80 rounded-2xl border border-slate-200">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Big Desktop Save Button -->
            <button type="button" id="btn-save-all-schedules" onclick="saveAllDoctorSchedules()" class="mt-5 w-full py-4 px-6 rounded-2xl bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-black text-base sm:text-lg shadow-xl shadow-purple-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-lg"></i> <span>Save Doctor Schedule</span>
            </button>
        </div>

    </div>

</div>

<!-- ================= FULL WEEK OVERVIEW TABLE ================= -->
<div class="bg-white p-5 sm:p-6 rounded-2xl border-2 border-slate-200/90 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h4 class="font-black text-base sm:text-lg text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-table-list text-purple-600"></i> Full Week Overview
            </h4>
            <p class="text-xs font-bold text-slate-400 mt-0.5">Click any row below to edit that day</p>
        </div>
    </div>
    
    <div class="overflow-x-auto no-scrollbar rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm min-w-[620px]">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600 font-black uppercase text-xs tracking-wider">
                <tr>
                    <th class="py-3.5 px-4">Day</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Shift Hours</th>
                    <th class="py-3.5 px-4">Duration</th>
                    <th class="py-3.5 px-4">Lunch Break</th>
                    <th class="py-3.5 px-4">Total Slots</th>
                    <th class="py-3.5 px-4 text-right">Edit</th>
                </tr>
            </thead>
            <tbody id="weekly-overview-tbody" class="divide-y divide-slate-100">
                <!-- Populated via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ================= STICKY MOBILE SAVE BAR ================= -->
<div id="sticky-save-bar" class="fixed bottom-[70px] left-3 right-3 sm:hidden z-40 bg-slate-900 text-white p-3.5 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-3">
    <div class="min-w-0">
        <div class="font-black text-sm text-white truncate" id="floating-doc-name">Doctor</div>
        <div class="text-xs font-bold text-purple-300 truncate" id="floating-slot-summary">0 Slots</div>
    </div>
    <button type="button" onclick="saveAllDoctorSchedules()" id="btn-floating-save" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 active:bg-purple-700 text-white font-black text-sm shadow-md transition flex items-center gap-2 shrink-0 cursor-pointer">
        <i class="fa-solid fa-floppy-disk"></i> <span>Save</span>
    </button>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let allDoctorsList = [];
    let currentDoctorId = "<?php echo htmlspecialchars($selected_doctor_id); ?>";
    let currentDoctorName = '';
    let activeDay = 'Monday';
    let doctorSchedules = [];
    let hasUnsavedChanges = false;

    const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctors();

        // Keyboard Shortcut: Ctrl+S / Cmd+S to save
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                saveAllDoctorSchedules();
            }
        });

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });

    function setDirty(isDirty = true) {
        hasUnsavedChanges = isDirty;
        const badge = document.getElementById('unsaved-badge');
        if (badge) badge.classList.toggle('hidden', !isDirty);
    }

    // ================= 1. LOAD DOCTORS =================
    async function loadDoctors() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctorsList = data.doctors || [];
                const select = document.getElementById('slot-doctor-select');
                select.innerHTML = '';

                if (allDoctorsList.length === 0) {
                    select.innerHTML = '<option value="">No doctors registered yet</option>';
                    return;
                }

                allDoctorsList.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id;
                    const cats = (doc.categories || []).join(', ') || 'General';
                    opt.textContent = `${doc.name} (${cats})`;
                    select.appendChild(opt);
                });

                if (currentDoctorId && allDoctorsList.some(d => d.id === currentDoctorId)) {
                    select.value = currentDoctorId;
                } else {
                    currentDoctorId = allDoctorsList[0].id;
                    select.value = currentDoctorId;
                }

                updateDoctorInfoStrip();
                await loadDoctorSchedules(currentDoctorId);
            }
        } catch (e) {
            console.error('Error loading doctors:', e);
            showToast('Error', 'Could not load doctors', 'error');
        }
    }

    function onDoctorChanged(docId) {
        if (hasUnsavedChanges) {
            if (!confirm('You have unsaved changes. Discard and switch doctor?')) {
                document.getElementById('slot-doctor-select').value = currentDoctorId;
                return;
            }
        }
        currentDoctorId = docId;
        setDirty(false);
        updateDoctorInfoStrip();
        loadDoctorSchedules(docId);
    }

    function updateDoctorInfoStrip() {
        const doc = allDoctorsList.find(d => d.id === currentDoctorId);
        if (!doc) return;

        currentDoctorName = doc.name;
        document.getElementById('doc-name-display').textContent = doc.name;
        document.getElementById('floating-doc-name').textContent = doc.name;
        document.getElementById('doc-specialties-display').textContent = (doc.categories || []).join(', ') || 'General Medicine';

        const initials = doc.name.replace(/^(Dr\.?|Doctor)\s*/i, '').trim().split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'DR';
        document.getElementById('doc-avatar-initials').textContent = initials;
    }

    // ================= 2. LOAD SCHEDULES =================
    async function loadDoctorSchedules(docId) {
        if (!docId) return;
        try {
            const res = await fetch(`api/doctors.php?action=get_doctor_day_schedules&doctor_id=${encodeURIComponent(docId)}`);
            const data = await res.json();
            if (data.status === 'success') {
                doctorSchedules = data.schedules || [];
                setDirty(false);
                renderDayTabs();
                renderActiveDayEditor();
                renderWeeklyOverviewTable();
                updateWeeklySummaryStats();
            } else {
                showToast('Error', data.message || 'Could not load schedules', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Connection failed', 'error');
        }
    }

    function getDayConfig(day) {
        let conf = doctorSchedules.find(s => s.day_of_week === day);
        if (!conf) {
            conf = {
                day_of_week: day,
                is_available: (day === 'Sunday' ? 0 : 1),
                start_time: '09:00 AM',
                end_time: '05:00 PM',
                duration_minutes: 30,
                break_start: '01:00 PM',
                break_end: '02:00 PM',
                slots: []
            };
            doctorSchedules.push(conf);
        }
        return conf;
    }

    function updateWeeklySummaryStats() {
        let totalSlots = 0;
        let activeDays = 0;

        daysOfWeek.forEach(day => {
            const c = getDayConfig(day);
            if (Number(c.is_available) === 1) {
                activeDays++;
                totalSlots += (c.slots || []).length;
            }
        });

        document.getElementById('doc-total-weekly-slots').textContent = totalSlots;
        document.getElementById('doc-active-days-count').textContent = `${activeDays} / 7`;
        document.getElementById('floating-slot-summary').textContent = `${totalSlots} Total Slots (${activeDays} Days Open)`;
    }

    // ================= 3. DAY TABS (BIG BUTTONS) =================
    function renderDayTabs() {
        const container = document.getElementById('day-tabs-grid');
        container.innerHTML = '';

        daysOfWeek.forEach(day => {
            const sched = getDayConfig(day);
            const isSelected = (day === activeDay);
            const isAvail = (Number(sched.is_available) === 1);
            const slotCount = (sched.slots || []).length;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `p-3.5 sm:p-4 rounded-2xl transition flex flex-col items-center justify-center gap-1 border-2 min-w-[95px] sm:min-w-0 shrink-0 cursor-pointer select-none text-center ${
                isSelected 
                    ? 'bg-purple-600 text-white border-purple-600 shadow-lg font-black ring-2 ring-purple-600/30' 
                    : (isAvail 
                        ? 'bg-slate-50 hover:bg-purple-50 text-slate-900 border-slate-200 font-extrabold hover:border-purple-300' 
                        : 'bg-slate-100/70 text-slate-400 border-slate-200/80 font-bold')
            }`;

            btn.innerHTML = `
                <span class="text-sm sm:text-base font-black tracking-tight">${day.substring(0, 3).toUpperCase()}</span>
                <span class="text-xs font-black px-2 py-0.5 rounded-full ${
                    isSelected 
                        ? 'bg-white/20 text-white' 
                        : (isAvail ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500')
                }">
                    ${isAvail ? `${slotCount} Slots` : 'Off'}
                </span>
            `;
            btn.onclick = () => switchActiveDay(day);
            container.appendChild(btn);
        });
    }

    function switchActiveDay(day) {
        activeDay = day;
        renderDayTabs();
        renderActiveDayEditor();
    }

    // ================= 4. TIME UTILITIES =================
    function generateTimeList() {
        const list = [];
        for (let h = 6; h <= 22; h++) {
            for (let m of [0, 30]) {
                if (h === 22 && m > 0) break;
                const h12 = (h % 12 === 0) ? 12 : (h % 12);
                const ampm = (h < 12) ? 'AM' : 'PM';
                list.push(`${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`);
            }
        }
        return list;
    }

    function populateSelect(selectId, options, selectedVal) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '';
        options.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (t === selectedVal) opt.selected = true;
            sel.appendChild(opt);
        });
        if (selectedVal && !options.includes(selectedVal)) {
            const custom = document.createElement('option');
            custom.value = selectedVal;
            custom.textContent = selectedVal;
            custom.selected = true;
            sel.appendChild(custom);
        }
    }

    function parseTimeMinutes(tStr) {
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

    function computeSlots(start, end, duration, breakStart, breakEnd) {
        duration = Number(duration) || 30;
        const sMin = parseTimeMinutes(start);
        const eMin = parseTimeMinutes(end);
        if (sMin === null || eMin === null || sMin >= eMin) return [];

        const bSMin = breakStart ? parseTimeMinutes(breakStart) : null;
        const bEMin = breakEnd ? parseTimeMinutes(breakEnd) : null;

        const result = [];
        let curr = sMin;
        while (curr + duration <= eMin) {
            const slotEnd = curr + duration;
            let inBreak = false;
            if (bSMin !== null && bEMin !== null && bSMin < bEMin) {
                if (curr < bEMin && slotEnd > bSMin) {
                    inBreak = true;
                }
            }

            if (!inBreak) {
                const h24 = Math.floor(curr / 60);
                const m = curr % 60;
                const h12 = (h24 % 12 === 0) ? 12 : (h24 % 12);
                const ampm = (h24 < 12) ? 'AM' : 'PM';
                result.push(`${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`);
            }
            curr += duration;
        }
        return result;
    }

    // ================= 5. RENDER ACTIVE DAY EDITOR =================
    function renderActiveDayEditor() {
        const conf = getDayConfig(activeDay);
        const times = generateTimeList();

        document.getElementById('active-day-heading').textContent = activeDay;
        document.getElementById('slots-preview-day-name').textContent = `(${activeDay})`;
        document.getElementById('day-off-name').textContent = activeDay;

        const isAvail = (Number(conf.is_available) === 1);
        const toggle = document.getElementById('day-avail-toggle');
        const iconBg = document.getElementById('day-status-avatar');
        const icon = document.getElementById('day-status-icon');
        const subtext = document.getElementById('day-avail-subtext');
        const controls = document.getElementById('shift-controls-section');
        const banner = document.getElementById('day-off-banner');

        toggle.checked = isAvail;
        iconBg.className = isAvail ? 'w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-black shrink-0' : 'w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-black shrink-0';
        icon.className = isAvail ? 'fa-solid fa-calendar-check' : 'fa-solid fa-calendar-xmark';
        subtext.textContent = isAvail ? 'Open for patient appointments' : 'Doctor is marked as Day Off';
        subtext.className = isAvail ? 'text-xs sm:text-sm font-bold text-emerald-700' : 'text-xs sm:text-sm font-bold text-amber-700';

        controls.classList.toggle('hidden', !isAvail);
        banner.classList.toggle('hidden', isAvail);

        // Populate Shift Timings
        populateSelect('shift-start-time', times, conf.start_time || '09:00 AM');
        populateSelect('shift-end-time', times, conf.end_time || '05:00 PM');

        // Populate Break Timings
        const hasBreak = Boolean(conf.break_start && conf.break_end);
        const breakToggle = document.getElementById('break-enabled-toggle');
        const breakRow = document.getElementById('break-times-row');
        const breakNotice = document.getElementById('break-disabled-notice');
        const breakLabel = document.getElementById('break-toggle-label');

        breakToggle.checked = hasBreak;
        breakLabel.textContent = hasBreak ? 'Included' : 'None';
        breakRow.classList.toggle('hidden', !hasBreak);
        breakNotice.classList.toggle('hidden', hasBreak);

        populateSelect('break-start-time', times, conf.break_start || '01:00 PM');
        populateSelect('break-end-time', times, conf.break_end || '02:00 PM');

        // Update Duration UI
        updateDurationButtons(conf.duration_minutes || 30);

        // Render live chips
        renderSlotsChips(conf);
    }

    function toggleDayAvailability(isChecked) {
        const conf = getDayConfig(activeDay);
        conf.is_available = isChecked ? 1 : 0;
        if (isChecked && (!conf.slots || conf.slots.length === 0)) {
            conf.slots = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);
            conf.custom_slots = conf.slots;
        }
        setDirty(true);
        renderDayTabs();
        renderActiveDayEditor();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
    }

    function updateDurationButtons(dur) {
        document.getElementById('active-duration-badge').textContent = `${dur} Min`;
        document.querySelectorAll('.dur-btn').forEach(btn => {
            const bDur = Number(btn.getAttribute('data-dur'));
            if (bDur === dur) {
                btn.className = "dur-btn py-3 px-2 rounded-xl border-2 border-purple-600 bg-purple-600 text-white font-black text-sm sm:text-base shadow-md transition cursor-pointer text-center";
            } else {
                btn.className = "dur-btn py-3 px-2 rounded-xl border-2 border-slate-200 bg-slate-50 hover:bg-white text-slate-800 font-extrabold text-sm sm:text-base transition cursor-pointer text-center";
            }
        });
    }

    function setSlotDuration(dur) {
        const conf = getDayConfig(activeDay);
        conf.duration_minutes = dur;
        updateDurationButtons(dur);
        conf.custom_slots = null;
        recalculateSlots();
    }

    function onBreakToggleChanged(isChecked) {
        const conf = getDayConfig(activeDay);
        const breakRow = document.getElementById('break-times-row');
        const breakNotice = document.getElementById('break-disabled-notice');
        const breakLabel = document.getElementById('break-toggle-label');

        breakRow.classList.toggle('hidden', !isChecked);
        breakNotice.classList.toggle('hidden', isChecked);
        breakLabel.textContent = isChecked ? 'Included' : 'None';

        if (isChecked) {
            conf.break_start = document.getElementById('break-start-time').value || '01:00 PM';
            conf.break_end = document.getElementById('break-end-time').value || '02:00 PM';
        } else {
            conf.break_start = null;
            conf.break_end = null;
        }
        recalculateSlots();
    }

    function onTimeParamsChanged() {
        const conf = getDayConfig(activeDay);
        conf.start_time = document.getElementById('shift-start-time').value;
        conf.end_time = document.getElementById('shift-end-time').value;

        const breakToggle = document.getElementById('break-enabled-toggle');
        if (breakToggle.checked) {
            conf.break_start = document.getElementById('break-start-time').value;
            conf.break_end = document.getElementById('break-end-time').value;
        } else {
            conf.break_start = null;
            conf.break_end = null;
        }
        recalculateSlots();
    }


    function recalculateSlots() {
        const conf = getDayConfig(activeDay);
        const generated = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);
        conf.slots = generated;
        conf.custom_slots = generated;
        setDirty(true);
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
    }

    // ================= 6. RENDER SLOTS CHIPS (BIG & CLEAR) =================
    function renderSlotsChips(conf) {
        const container = document.getElementById('slots-chips-container');
        const pill = document.getElementById('slots-count-pill');
        const activeSlots = conf.slots || [];
        const fullGenerated = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);

        pill.textContent = `${activeSlots.length} Slots`;

        if (fullGenerated.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-slate-400 font-bold text-sm">
                    No slots generated. Check shift hours.
                </div>
            `;
            return;
        }

        const morning = [];
        const afternoon = [];
        const evening = [];

        fullGenerated.forEach(slot => {
            const min = parseTimeMinutes(slot);
            if (min < 720) morning.push(slot);
            else if (min < 1020) afternoon.push(slot);
            else evening.push(slot);
        });

        let html = '';

        function renderGroup(title, iconClass, slots) {
            if (slots.length === 0) return '';
            const activeInGroup = slots.filter(s => activeSlots.includes(s)).length;
            return `
                <div class="bg-white p-3.5 rounded-2xl border-2 border-slate-200">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-sm font-black text-slate-800 flex items-center gap-1.5">
                            <i class="${iconClass}"></i> ${title}
                        </span>
                        <span class="text-xs font-black text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            ${activeInGroup} / ${slots.length}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        ${slots.map(s => {
                            const isEnabled = activeSlots.includes(s);
                            if (isEnabled) {
                                return `
                                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="py-2.5 px-3 rounded-xl bg-purple-50 hover:bg-rose-50 border-2 border-purple-200 hover:border-rose-300 text-sm font-mono font-black text-purple-950 hover:text-rose-700 transition cursor-pointer flex items-center justify-between" title="Click to disable">
                                        <span>${s}</span>
                                        <i class="fa-solid fa-check text-xs text-emerald-600"></i>
                                    </button>
                                `;
                            } else {
                                return `
                                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="py-2.5 px-3 rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 text-sm font-mono font-bold text-slate-400 hover:text-emerald-700 line-through hover:no-underline transition cursor-pointer flex items-center justify-between" title="Click to restore">
                                        <span>${s}</span>
                                        <i class="fa-solid fa-plus text-xs text-slate-400"></i>
                                    </button>
                                `;
                            }
                        }).join('')}
                    </div>
                </div>
            `;
        }

        html += renderGroup('Morning', 'fa-regular fa-sun text-amber-500', morning);
        html += renderGroup('Afternoon', 'fa-solid fa-sun text-amber-600', afternoon);
        html += renderGroup('Evening', 'fa-solid fa-moon text-indigo-500', evening);

        container.innerHTML = html;
    }

    function toggleSlotExclusion(slotTime) {
        const conf = getDayConfig(activeDay);
        const currentSlots = conf.slots || [];
        if (currentSlots.includes(slotTime)) {
            conf.slots = currentSlots.filter(s => s !== slotTime);
        } else {
            conf.slots = [...currentSlots, slotTime];
            conf.slots.sort((a, b) => parseTimeMinutes(a) - parseTimeMinutes(b));
        }
        conf.custom_slots = conf.slots;
        setDirty(true);
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
    }

    function enableAllDaySlots() {
        const conf = getDayConfig(activeDay);
        const full = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);
        conf.slots = full;
        conf.custom_slots = full;
        setDirty(true);
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
    }

    function disableAllDaySlots() {
        const conf = getDayConfig(activeDay);
        conf.slots = [];
        conf.custom_slots = [];
        setDirty(true);
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
    }

    function resetDaySlots() {
        recalculateSlots();
    }


    // ================= 8. COPY ACTIONS =================
    function copyActiveDayToWeekdays() {
        const src = getDayConfig(activeDay);
        ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].forEach(day => {
            const dest = getDayConfig(day);
            dest.is_available = src.is_available;
            dest.start_time = src.start_time;
            dest.end_time = src.end_time;
            dest.duration_minutes = src.duration_minutes;
            dest.break_start = src.break_start;
            dest.break_end = src.break_end;
            dest.slots = [...(src.slots || [])];
            dest.custom_slots = dest.slots;
        });
        setDirty(true);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
        showToast('Copied', `${activeDay} schedule copied to Mon - Fri!`, 'success');
    }

    function copyActiveDayToAll() {
        const src = getDayConfig(activeDay);
        daysOfWeek.forEach(day => {
            if (day === activeDay) return;
            const dest = getDayConfig(day);
            dest.is_available = src.is_available;
            dest.start_time = src.start_time;
            dest.end_time = src.end_time;
            dest.duration_minutes = src.duration_minutes;
            dest.break_start = src.break_start;
            dest.break_end = src.break_end;
            dest.slots = [...(src.slots || [])];
            dest.custom_slots = dest.slots;
        });
        setDirty(true);
        renderDayTabs();
        renderWeeklyOverviewTable();
        updateWeeklySummaryStats();
        showToast('Copied', `${activeDay} schedule copied to all 7 days!`, 'success');
    }

    // ================= 9. WEEKLY OVERVIEW TABLE =================
    function renderWeeklyOverviewTable() {
        const tbody = document.getElementById('weekly-overview-tbody');
        tbody.innerHTML = '';

        daysOfWeek.forEach(day => {
            const sched = getDayConfig(day);
            const isAvail = (Number(sched.is_available) === 1);
            const isCurrent = (day === activeDay);
            const tr = document.createElement('tr');
            tr.className = `transition cursor-pointer ${isCurrent ? 'bg-purple-50 font-bold border-l-4 border-purple-600' : 'hover:bg-slate-50'}`;
            tr.onclick = (e) => {
                if (!e.target.closest('button')) switchActiveDay(day);
            };

            tr.innerHTML = `
                <td class="py-3.5 px-4 font-black text-slate-900">${day}</td>
                <td class="py-3.5 px-4">
                    <span class="text-xs font-black px-2.5 py-1 rounded-full ${isAvail ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'}">
                        ${isAvail ? 'Available' : 'Day Off'}
                    </span>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">${isAvail ? `${sched.start_time} - ${sched.end_time}` : '--'}</td>
                <td class="py-3.5 px-4 font-bold text-slate-600">${isAvail ? `${sched.duration_minutes}m` : '--'}</td>
                <td class="py-3.5 px-4 text-slate-600">${(isAvail && sched.break_start && sched.break_end) ? `${sched.break_start} - ${sched.break_end}` : 'None'}</td>
                <td class="py-3.5 px-4 font-black text-purple-700">${isAvail ? `${(sched.slots || []).length} slots` : '0'}</td>
                <td class="py-3.5 px-4 text-right">
                    <button type="button" onclick="switchActiveDay('${day}')" class="text-xs font-black text-purple-700 hover:text-purple-900 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 transition cursor-pointer">
                        Edit
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ================= 10. SAVE SCHEDULES =================
    async function saveAllDoctorSchedules() {
        if (!currentDoctorId) {
            showToast('Select Doctor', 'Please select a doctor first.', 'error');
            return;
        }

        const saveBtn = document.getElementById('btn-save-all-schedules');
        const floatingBtn = document.getElementById('btn-floating-save');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
        if (floatingBtn) {
            floatingBtn.disabled = true;
            floatingBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';
        }

        try {
            const payload = {
                doctor_id: currentDoctorId,
                schedules: doctorSchedules.map(s => ({
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
                setDirty(false);
                showToast('Saved!', `Schedules for ${currentDoctorName} updated successfully!`, 'success');
                renderDayTabs();
                renderWeeklyOverviewTable();
                updateWeeklySummaryStats();
            } else {
                showToast('Error', data.message || 'Could not save schedules', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Save Failed', 'Server connection error', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk text-lg mr-2"></i> <span>Save Doctor Schedule</span>';
            if (floatingBtn) {
                floatingBtn.disabled = false;
                floatingBtn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i> <span>Save</span>';
            }
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

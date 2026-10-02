<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$selected_doctor_id = $_GET['doctor_id'] ?? '';
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 uppercase tracking-wider">Schedule Configuration</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Manage Slot Timings & Duration</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Customize daily consultation hours, slot intervals, and lunch breaks for each doctor across the week.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="doctors.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-3.5 rounded-xl border border-slate-200 transition flex items-center gap-1.5 text-xs shrink-0 shadow-2xs">
            <i class="fa-solid fa-arrow-left"></i> <span>Doctors Directory</span>
        </a>
        <a href="appointments.php" class="bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold py-2.5 px-3.5 rounded-xl border border-purple-200 transition flex items-center gap-1.5 text-xs shrink-0 shadow-2xs">
            <i class="fa-solid fa-calendar-check"></i> <span>View Appointments</span>
        </a>
    </div>
</div>

<!-- ================= DOCTOR SWITCHER CARD ================= -->
<div class="apple-card p-5 sm:p-6 mb-6 bg-white border border-slate-200/90 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Doctor Picker Dropdown -->
        <div class="flex items-center gap-3.5 flex-1 min-w-0">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl font-bold shadow-2xs shrink-0">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Select Doctor To Configure *</label>
                <div class="relative max-w-lg">
                    <select id="slot-doctor-select" onchange="onDoctorChanged(this.value)" class="w-full border border-slate-300 rounded-xl pl-3.5 pr-8 py-2.5 text-sm font-extrabold text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-purple-500/50 outline-none transition appearance-none shadow-2xs">
                        <option value="">Loading medical doctors...</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Current Doctor Information Strip -->
        <div id="doctor-info-strip" class="hidden md:flex items-center gap-4 bg-purple-50/60 border border-purple-100 px-4 py-2.5 rounded-2xl">
            <div>
                <span class="text-[10px] font-black uppercase text-purple-600 tracking-wider block">Specialties</span>
                <span id="doc-specialties-display" class="text-xs font-bold text-slate-800">--</span>
            </div>
            <div class="h-6 w-px bg-purple-200"></div>
            <div>
                <span class="text-[10px] font-black uppercase text-purple-600 tracking-wider block">Phone Contact</span>
                <span id="doc-phone-display" class="text-xs font-bold text-slate-800 font-mono">--</span>
            </div>
            <div class="h-6 w-px bg-purple-200"></div>
            <div>
                <span class="text-[10px] font-black uppercase text-purple-600 tracking-wider block">ID Code</span>
                <span id="doc-id-display" class="text-xs font-black text-purple-700 font-mono">--</span>
            </div>
        </div>

    </div>
</div>

<!-- ================= 7-DAY WEEKLY SCHEDULE EDITOR ================= -->
<div id="schedule-editor-wrapper" class="space-y-6">

    <!-- Day Navigation Tabs & Quick Batch Actions -->
    <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                    <i class="fa-regular fa-calendar-days text-purple-600"></i> Weekly Day Configuration
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">Click any day to view or edit its shift timings and consultation slots.</p>
            </div>
            
            <!-- Quick Batch Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="copyActiveDayToWeekdays()" class="text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-regular fa-copy"></i> Copy to Weekdays (Mon-Fri)
                </button>
                <button type="button" onclick="copyActiveDayToAll()" class="text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-clone"></i> Copy to All 7 Days
                </button>
            </div>
        </div>

        <!-- 7-Day Buttons Grid -->
        <div id="day-tabs-grid" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
            <!-- Dynamically populated via JS -->
        </div>
    </div>

    <!-- Active Day Schedule Configurator (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Controls (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- Day Status Toggle Card -->
            <div class="apple-card p-5 border border-slate-200 bg-white shadow-sm flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm" id="day-avail-dot"></span>
                        <h4 id="active-day-heading" class="font-black text-lg text-slate-900">Monday Schedule</h4>
                    </div>
                    <p id="day-avail-subtext" class="text-xs text-slate-500 mt-1 font-medium">Doctor available for consultations on this day.</p>
                </div>
                
                <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                    <span id="day-avail-status-text" class="text-xs font-black text-emerald-700">Available</span>
                    <input type="checkbox" id="day-avail-toggle" onchange="toggleDayAvailability(this.checked)" class="sr-only peer" checked>
                    <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                </label>
            </div>

            <!-- Active Shift Form (When Available) -->
            <div id="shift-controls-section" class="apple-card p-5 sm:p-6 border border-slate-200 bg-white shadow-sm space-y-5">
                
                <!-- Quick Shift Presets Toolbar -->
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-600"></i> Quick Presets:
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" onclick="applyPreset('standard')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-purple-50 text-purple-700 border border-slate-200 transition shadow-2xs">
                            9 AM - 5 PM (30m)
                        </button>
                        <button type="button" onclick="applyPreset('morning')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-slate-200 transition shadow-2xs">
                            9 AM - 1 PM (20m)
                        </button>
                        <button type="button" onclick="applyPreset('evening')" class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white hover:bg-indigo-50 text-indigo-700 border border-slate-200 transition shadow-2xs">
                            2 PM - 8 PM (15m)
                        </button>
                    </div>
                </div>

                <!-- Consultation Shift Hours -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                        <i class="fa-regular fa-clock text-purple-600"></i> Consultation Shift Hours
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Shift Starts (From)</span>
                            <div class="relative">
                                <select id="shift-start-time" onchange="onTimeParamsChanged()" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-purple-500/50 outline-none bg-slate-50 focus:bg-white shadow-2xs">
                                    <!-- Populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Shift Ends (To)</span>
                            <div class="relative">
                                <select id="shift-end-time" onchange="onTimeParamsChanged()" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-purple-500/50 outline-none bg-slate-50 focus:bg-white shadow-2xs">
                                    <!-- Populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slot Duration Interval -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-stopwatch text-purple-600"></i> Slot Interval Duration
                        </label>
                        <span id="active-duration-badge" class="text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-lg border border-purple-200">
                            30 Minutes
                        </span>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <button type="button" onclick="setSlotDuration(10)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition" data-dur="10">10 min</button>
                        <button type="button" onclick="setSlotDuration(15)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition" data-dur="15">15 min</button>
                        <button type="button" onclick="setSlotDuration(20)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition" data-dur="20">20 min</button>
                        <button type="button" onclick="setSlotDuration(30)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-purple-600 bg-purple-600 text-white shadow-sm transition" data-dur="30">30 min</button>
                        <button type="button" onclick="setSlotDuration(45)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition" data-dur="45">45 min</button>
                        <button type="button" onclick="setSlotDuration(60)" class="dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition" data-dur="60">60 min</button>
                    </div>
                </div>

                <!-- Lunch / Break Time Exclusion -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-mug-saucer text-amber-500"></i> Break / Lunch Time Exclusion (Optional)
                        </span>
                        <button type="button" onclick="clearBreakTime()" class="text-[11px] text-slate-400 hover:text-rose-600 font-bold transition flex items-center gap-1">
                            <i class="fa-solid fa-xmark"></i> Clear Break
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Break Starts (From)</span>
                            <select id="break-start-time" onchange="onTimeParamsChanged()" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold outline-none bg-slate-50 focus:bg-white">
                                <option value="">None (No Break)</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Break Ends (To)</span>
                            <select id="break-end-time" onchange="onTimeParamsChanged()" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold outline-none bg-slate-50 focus:bg-white">
                                <option value="">None (No Break)</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Day Off Banner (When Toggled Off) -->
            <div id="day-off-banner" class="hidden apple-card p-10 text-center bg-amber-50/50 rounded-2xl border border-amber-200">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <h5 class="font-black text-amber-900 text-base">Marked as Day Off</h5>
                <p class="text-xs text-amber-700 mt-1 max-w-sm mx-auto">This doctor will be unavailable for consultations on this day. No slots will be bookable.</p>
                <button type="button" onclick="document.getElementById('day-avail-toggle').click()" class="mt-4 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow transition">
                    Enable as Working Day
                </button>
            </div>

        </div>

        <!-- Right Column: Live Generated Slots (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="apple-card p-5 border border-slate-200 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="font-extrabold text-sm text-slate-900">Generated Consultation Slots</h4>
                                <span class="text-xs font-bold text-purple-700">(<span id="slots-preview-day-name">Monday</span>)</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Click any chip below to exclude/enable individual slots.</p>
                        </div>
                        <span id="slots-count-pill" class="text-xs font-black px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                            0 Slots
                        </span>
                    </div>

                    <!-- Slots Interactive Chips Grid -->
                    <div id="slots-chips-container" class="flex flex-wrap gap-2 max-h-[380px] overflow-y-auto no-scrollbar p-3 bg-slate-50/70 rounded-2xl border border-slate-200/80">
                        <!-- Chips loaded via JS -->
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                    <span><span class="w-2 h-2 inline-block rounded-full bg-emerald-500 mr-1"></span> Enabled Slot</span>
                    <span><span class="w-2 h-2 inline-block rounded-full bg-slate-300 mr-1"></span> Excluded (Click to restore)</span>
                </div>
            </div>

            <!-- Big Prominent Save Button -->
            <button type="button" id="btn-save-all-schedules" onclick="saveAllDoctorSchedules()" class="w-full py-4 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-black text-sm shadow-xl shadow-purple-600/30 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-base"></i> <span>Save & Apply All Day Schedules</span>
            </button>
        </div>

    </div>

    <!-- Weekly Overview Matrix Card (At a glance summary of all 7 days) -->
    <div class="apple-card p-5 sm:p-6 bg-white border border-slate-200/90 shadow-sm">
        <h4 class="font-extrabold text-sm text-slate-900 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-table-list text-purple-600"></i> Full Week Overview
        </h4>
        <p class="text-xs text-slate-500 mb-4">Summary of working hours and slot capacity for the entire week.</p>
        
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Day</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Shift Timings</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4">Break Period</th>
                        <th class="py-3 px-4">Total Slots</th>
                        <th class="py-3 px-4 text-right">Quick Edit</th>
                    </tr>
                </thead>
                <tbody id="weekly-overview-tbody" class="divide-y divide-slate-100">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let allDoctorsList = [];
    let currentDoctorId = "<?php echo htmlspecialchars($selected_doctor_id); ?>";
    let currentDoctorName = '';
    let activeDay = 'Monday';
    let doctorSchedules = [];

    const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctors();
    });

    async function loadDoctors() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctorsList = data.doctors || [];
                const select = document.getElementById('slot-doctor-select');
                select.innerHTML = '';

                if (allDoctorsList.length === 0) {
                    select.innerHTML = '<option value="">No doctors registered yet. Add doctors first.</option>';
                    return;
                }

                allDoctorsList.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id;
                    const cats = (doc.categories || []).join(', ') || 'General';
                    opt.textContent = `${doc.name} (${cats})`;
                    select.appendChild(opt);
                });

                // Pick selected or first
                if (currentDoctorId && allDoctorsList.some(d => d.id === currentDoctorId)) {
                    select.value = currentDoctorId;
                } else {
                    currentDoctorId = allDoctorsList[0].id;
                    select.value = currentDoctorId;
                }

                updateDoctorInfoStrip();
                loadDoctorSchedules(currentDoctorId);
            }
        } catch (e) {
            console.error('Error loading doctors:', e);
        }
    }

    function onDoctorChanged(docId) {
        currentDoctorId = docId;
        updateDoctorInfoStrip();
        loadDoctorSchedules(docId);
    }

    function updateDoctorInfoStrip() {
        const doc = allDoctorsList.find(d => d.id === currentDoctorId);
        const strip = document.getElementById('doctor-info-strip');
        if (!doc) {
            strip.classList.add('hidden');
            return;
        }

        currentDoctorName = doc.name;
        document.getElementById('doc-specialties-display').textContent = (doc.categories || []).join(', ') || 'General';
        document.getElementById('doc-phone-display').textContent = doc.phone || 'N/A';
        document.getElementById('doc-id-display').textContent = doc.id;
        strip.classList.remove('hidden');
    }

    async function loadDoctorSchedules(docId) {
        if (!docId) return;
        try {
            const res = await fetch(`api/doctors.php?action=get_doctor_day_schedules&doctor_id=${encodeURIComponent(docId)}`);
            const data = await res.json();
            if (data.status === 'success') {
                doctorSchedules = data.schedules || [];
                renderDayTabs();
                renderActiveDayEditor();
                renderWeeklyOverviewTable();
            } else {
                showToast('Error', data.message || 'Could not load schedules', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Connection failed', 'error');
        }
    }

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
            btn.className = `p-3 rounded-2xl transition flex flex-col items-center justify-center gap-1.5 border ${
                isSelected 
                    ? 'bg-purple-600 text-white border-purple-600 shadow-md font-extrabold ring-2 ring-purple-600/30' 
                    : (isAvail 
                        ? 'bg-slate-50 hover:bg-purple-50/50 text-slate-800 border-slate-200/90 font-bold' 
                        : 'bg-slate-100/60 text-slate-400 border-slate-200/60 font-semibold')
            }`;

            btn.innerHTML = `
                <span class="text-xs tracking-tight">${day}</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-black ${
                    isSelected 
                        ? 'bg-white/20 text-white' 
                        : (isAvail ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500')
                }">
                    ${isAvail ? `${slotCount} Slots` : 'Day Off'}
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

    function populateSelect(selectId, options, selectedVal, includeNone = false) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = includeNone ? '<option value="">None (No Break)</option>' : '';
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

    function renderActiveDayEditor() {
        const conf = getDayConfig(activeDay);
        const times = generateTimeList();

        document.getElementById('active-day-heading').textContent = `${activeDay} Schedule`;
        document.getElementById('slots-preview-day-name').textContent = activeDay;

        const isAvail = (Number(conf.is_available) === 1);
        const toggle = document.getElementById('day-avail-toggle');
        const statusText = document.getElementById('day-avail-status-text');
        const availDot = document.getElementById('day-avail-dot');
        const subtext = document.getElementById('day-avail-subtext');
        const controls = document.getElementById('shift-controls-section');
        const banner = document.getElementById('day-off-banner');

        toggle.checked = isAvail;
        statusText.textContent = isAvail ? 'Available' : 'Day Off';
        statusText.className = isAvail ? 'text-xs font-black text-emerald-700' : 'text-xs font-black text-amber-700';
        availDot.className = isAvail ? 'w-3 h-3 rounded-full bg-emerald-500 shadow-sm' : 'w-3 h-3 rounded-full bg-amber-400 shadow-sm';
        subtext.textContent = isAvail ? 'Doctor available for consultations on this day.' : 'Day Off. Consultation bookings disabled on this day.';

        controls.classList.toggle('hidden', !isAvail);
        banner.classList.toggle('hidden', isAvail);

        // Populate Shift Timings
        populateSelect('shift-start-time', times, conf.start_time || '09:00 AM');
        populateSelect('shift-end-time', times, conf.end_time || '05:00 PM');

        // Populate Break Timings
        populateSelect('break-start-time', times, conf.break_start || '', true);
        populateSelect('break-end-time', times, conf.break_end || '', true);

        // Update Duration UI
        updateDurationButtons(conf.duration_minutes || 30);

        // Render live chips
        renderSlotsChips(conf);
    }

    function toggleDayAvailability(isChecked) {
        const conf = getDayConfig(activeDay);
        conf.is_available = isChecked ? 1 : 0;
        renderDayTabs();
        renderActiveDayEditor();
        renderWeeklyOverviewTable();
    }

    function updateDurationButtons(dur) {
        document.getElementById('active-duration-badge').textContent = `${dur} Minutes`;
        document.querySelectorAll('.dur-btn').forEach(btn => {
            const bDur = Number(btn.getAttribute('data-dur'));
            if (bDur === dur) {
                btn.className = "dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-purple-600 bg-purple-600 text-white shadow-sm transition";
            } else {
                btn.className = "dur-btn py-2.5 text-xs font-extrabold rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 transition";
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

    function clearBreakTime() {
        const bStart = document.getElementById('break-start-time');
        const bEnd = document.getElementById('break-end-time');
        if (bStart) bStart.value = '';
        if (bEnd) bEnd.value = '';
        onTimeParamsChanged();
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

    function onTimeParamsChanged() {
        const conf = getDayConfig(activeDay);
        conf.start_time = document.getElementById('shift-start-time').value;
        conf.end_time = document.getElementById('shift-end-time').value;
        conf.break_start = document.getElementById('break-start-time').value || null;
        conf.break_end = document.getElementById('break-end-time').value || null;
        recalculateSlots();
    }

    function recalculateSlots() {
        const conf = getDayConfig(activeDay);
        const generated = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);
        conf.slots = generated;
        conf.custom_slots = generated;
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
    }

    function renderSlotsChips(conf) {
        const container = document.getElementById('slots-chips-container');
        const pill = document.getElementById('slots-count-pill');
        const activeSlots = conf.slots || [];
        const fullGenerated = computeSlots(conf.start_time, conf.end_time, conf.duration_minutes, conf.break_start, conf.break_end);

        pill.textContent = `${activeSlots.length} Slots`;

        if (fullGenerated.length === 0) {
            container.innerHTML = '<span class="text-xs text-slate-400 italic p-4 text-center w-full">No slots generated. Please review start and end times.</span>';
            return;
        }

        container.innerHTML = fullGenerated.map(s => {
            const isEnabled = activeSlots.includes(s);
            if (isEnabled) {
                return `
                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-800 shadow-2xs hover:border-rose-300 hover:bg-rose-50/60 hover:text-rose-700 transition cursor-pointer group" title="Click to disable this slot">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>${s}</span>
                        <i class="fa-solid fa-xmark text-[9px] text-slate-300 group-hover:text-rose-600 transition"></i>
                    </button>
                `;
            } else {
                return `
                    <button type="button" onclick="toggleSlotExclusion('${s}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/90 border border-dashed border-slate-300 text-xs font-mono font-medium text-slate-400 line-through hover:border-emerald-300 hover:bg-emerald-50/60 hover:text-emerald-700 transition cursor-pointer group" title="Click to restore this slot">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-emerald-500"></span>
                        <span>${s}</span>
                        <i class="fa-solid fa-plus text-[9px] text-slate-400 group-hover:text-emerald-600 transition"></i>
                    </button>
                `;
            }
        }).join('');
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
        renderSlotsChips(conf);
        renderDayTabs();
        renderWeeklyOverviewTable();
    }

    function applyPreset(type) {
        let preset = { start: '09:00 AM', end: '05:00 PM', dur: 30, bStart: '01:00 PM', bEnd: '02:00 PM' };
        if (type === 'morning') {
            preset = { start: '09:00 AM', end: '01:00 PM', dur: 20, bStart: null, bEnd: null };
        } else if (type === 'evening') {
            preset = { start: '02:00 PM', end: '08:00 PM', dur: 15, bStart: null, bEnd: null };
        }

        const conf = getDayConfig(activeDay);
        conf.is_available = 1;
        conf.start_time = preset.start;
        conf.end_time = preset.end;
        conf.duration_minutes = preset.dur;
        conf.break_start = preset.bStart;
        conf.break_end = preset.bEnd;
        conf.slots = computeSlots(preset.start, preset.end, preset.dur, preset.bStart, preset.bEnd);
        conf.custom_slots = conf.slots;

        renderDayTabs();
        renderActiveDayEditor();
        renderWeeklyOverviewTable();
        showToast('Preset Applied', `Applied ${preset.start} - ${preset.end} (${preset.dur}m) to ${activeDay}`, 'success');
    }

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
        renderDayTabs();
        renderWeeklyOverviewTable();
        showToast('Copied to Weekdays', `${activeDay} schedule duplicated to Mon - Fri!`, 'success');
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
        renderDayTabs();
        renderWeeklyOverviewTable();
        showToast('Copied to All 7 Days', `${activeDay} schedule applied across entire week!`, 'success');
    }

    function renderWeeklyOverviewTable() {
        const tbody = document.getElementById('weekly-overview-tbody');
        tbody.innerHTML = '';

        daysOfWeek.forEach(day => {
            const sched = getDayConfig(day);
            const isAvail = (Number(sched.is_available) === 1);
            const tr = document.createElement('tr');
            tr.className = `hover:bg-slate-50 transition ${day === activeDay ? 'bg-purple-50/40' : ''}`;

            tr.innerHTML = `
                <td class="py-3 px-4 font-extrabold text-slate-900">${day}</td>
                <td class="py-3 px-4">
                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full ${isAvail ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500'}">
                        ${isAvail ? 'Available' : 'Day Off'}
                    </span>
                </td>
                <td class="py-3 px-4 font-mono font-semibold text-slate-700">${isAvail ? `${sched.start_time} - ${sched.end_time}` : '--'}</td>
                <td class="py-3 px-4 font-bold text-slate-600">${isAvail ? `${sched.duration_minutes}m` : '--'}</td>
                <td class="py-3 px-4 text-slate-500">${(isAvail && sched.break_start && sched.break_end) ? `${sched.break_start} - ${sched.break_end}` : 'None'}</td>
                <td class="py-3 px-4 font-black text-purple-700">${isAvail ? `${(sched.slots || []).length} slots` : '0'}</td>
                <td class="py-3 px-4 text-right">
                    <button type="button" onclick="switchActiveDay('${day}')" class="text-xs font-bold text-purple-700 hover:text-purple-900 hover:underline">
                        Edit Day
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async function saveAllDoctorSchedules() {
        if (!currentDoctorId) {
            showToast('Doctor Required', 'Please select a doctor to configure.', 'error');
            return;
        }

        const saveBtn = document.getElementById('btn-save-all-schedules');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving schedules...';

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
                showToast('Success', `Timings & slot configurations for ${currentDoctorName} saved successfully!`, 'success');
                renderDayTabs();
                renderWeeklyOverviewTable();
            } else {
                showToast('Error', data.message || 'Could not save schedules', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Save Failed', 'Server connection error', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk text-base"></i> <span>Save & Apply All Day Schedules</span>';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

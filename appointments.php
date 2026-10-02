<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$initial_doctor_id = $_GET['doctor_id'] ?? 'all';
$initial_date = $_GET['date'] ?? date('Y-m-d');
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 uppercase tracking-wider">Appointments Hub</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Appointments & Schedule</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Select a Doctor and Date to view all appointments, perform advanced multi-field searches, and manage visits.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="doctor_slots.php" class="bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold py-2.5 px-3.5 rounded-xl border border-purple-200 transition flex items-center gap-1.5 text-xs shrink-0 shadow-2xs">
            <i class="fa-regular fa-clock"></i> <span>Manage Doctor Slots</span>
        </a>
        <a href="doctors.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-3.5 rounded-xl border border-slate-200 transition flex items-center gap-1.5 text-xs shrink-0">
            <i class="fa-solid fa-user-doctor"></i> <span>Doctors Directory</span>
        </a>
        <a href="book.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2 text-xs shrink-0">
            <i class="fa-solid fa-calendar-plus"></i> <span>Book Appointment</span>
        </a>
    </div>
</div>

<!-- ================= SUMMARY METRICS ================= -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3 sm:gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Appointments</span>
            <span id="metric-total" class="text-xl sm:text-2xl font-black text-slate-900">0</span>
        </div>
    </div>
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3 sm:gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Waiting / Scheduled</span>
            <span id="metric-waiting" class="text-xl sm:text-2xl font-black text-amber-700">0</span>
        </div>
    </div>
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3 sm:gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-stethoscope"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">In Consultation</span>
            <span id="metric-consulting" class="text-xl sm:text-2xl font-black text-purple-700">0</span>
        </div>
    </div>
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3 sm:gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Completed / Done</span>
            <span id="metric-completed" class="text-xl sm:text-2xl font-black text-emerald-700">0</span>
        </div>
    </div>
</div>

<!-- ================= DOCTOR & DATE SELECTION + ADVANCED SEARCH ================= -->
<div class="apple-card p-5 sm:p-6 mb-6 bg-white border border-slate-200/90 shadow-sm space-y-4">
    <!-- Primary Filter Bar: Doctor & Date Selection -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end pb-4 border-b border-slate-100">
        
        <!-- Doctor Selector -->
        <div class="md:col-span-6 lg:col-span-5">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-user-doctor text-indigo-600"></i> Select Doctor *
            </label>
            <div class="relative">
                <select id="filter-doctor" onchange="triggerLoadAppointments()" class="w-full border border-slate-300 rounded-xl pl-3 pr-8 py-2.5 text-xs sm:text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition appearance-none shadow-2xs">
                    <option value="all">👨‍⚕️ All Medical Doctors (All Departments)</option>
                    <!-- Populated dynamically -->
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
            </div>
        </div>

        <!-- Date Selector -->
        <div class="md:col-span-6 lg:col-span-4">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-regular fa-calendar text-indigo-600"></i> Select Date *
            </label>
            <div class="flex items-center gap-2">
                <input 
                    type="date" 
                    id="filter-date" 
                    value="<?php echo htmlspecialchars($initial_date); ?>" 
                    onchange="triggerLoadAppointments()" 
                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition shadow-2xs">
                
                <button type="button" onclick="setQuickDate('today')" title="Set to Today" class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 text-xs font-black border border-slate-200 transition shrink-0">
                    Today
                </button>
                <button type="button" onclick="setQuickDate('tomorrow')" title="Set to Tomorrow" class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 text-xs font-black border border-slate-200 transition shrink-0">
                    Tmrw
                </button>
            </div>
        </div>

        <!-- Quick View Controls -->
        <div class="md:col-span-12 lg:col-span-3 flex items-center justify-between lg:justify-end gap-2">
            <button type="button" onclick="toggleAdvancedSearch()" id="btn-toggle-adv-search" class="text-xs font-bold px-3.5 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-sliders text-xs"></i> <span>Advanced Search</span>
            </button>
            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold">
                <button type="button" id="btn-view-cards" onclick="setAppointmentsViewMode('cards')" title="Cards Grid View" class="px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition">
                    <i class="fa-solid fa-grip"></i>
                </button>
                <button type="button" id="btn-view-table" onclick="setAppointmentsViewMode('table')" title="Detailed Table View" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition">
                    <i class="fa-solid fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Search Drawer / Filter Panel -->
    <div id="advanced-search-panel" class="space-y-4 pt-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Keyword Search -->
            <div class="lg:col-span-5">
                <label class="block text-xs font-bold text-slate-600 mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input 
                        type="text" 
                        id="filter-search" 
                        oninput="debounceSearch()" 
                        placeholder="Search patient name, MRN, phone, symptoms..." 
                        class="w-full border border-slate-300 rounded-xl pl-9 pr-8 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-2xs">
                    <button type="button" id="btn-clear-search" onclick="clearKeywordSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-600 mb-1">Status</label>
                <select id="filter-status" onchange="triggerLoadAppointments()" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    <option value="all">All Statuses</option>
                    <option value="Waiting">Waiting / Queued</option>
                    <option value="In Consultation">In Consultation</option>
                    <option value="Pre-Booked">Pre-Booked</option>
                    <option value="Discharged (Normal Medicine)">Completed / Discharged</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Consultation Type Filter -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Type</label>
                <select id="filter-type" onchange="triggerLoadAppointments()" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    <option value="all">All Types</option>
                    <option value="General Consultation">General</option>
                    <option value="Follow-up">Follow-up</option>
                    <option value="Emergency Case">Emergency</option>
                </select>
            </div>

            <!-- Reset Filters -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="button" onclick="resetAllFilters()" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-rotate-left"></i> <span>Reset Filters</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= APPOINTMENTS DISPLAY AREA ================= -->
<div class="space-y-4">
    <!-- Active Filter Summary Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
        <div class="flex items-center gap-2 flex-wrap">
            <span id="results-count-badge" class="text-xs font-black px-2.5 py-1 rounded-full bg-slate-100 text-slate-800">
                0 Appointments Found
            </span>
            <span id="active-doctor-badge" class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2.5 py-0.5 rounded-lg">
                Doctor: All
            </span>
            <span id="active-date-badge" class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-lg">
                Date: <?php echo htmlspecialchars($initial_date); ?>
            </span>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <i class="fa-solid fa-circle-info"></i>
            <span>Real-time hospital consultation pipeline</span>
        </div>
    </div>

    <!-- Cards Container (Default View) -->
    <div id="appointments-cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Loaded via JS -->
    </div>

    <!-- Table Container (Alternate View) -->
    <div id="appointments-table-container" class="hidden apple-card overflow-hidden border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-sm min-w-[800px]">
                <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 font-extrabold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-4 px-4 text-center w-16">Token</th>
                        <th class="py-4 px-4">Patient Information</th>
                        <th class="py-4 px-4">Assigned Doctor</th>
                        <th class="py-4 px-4">Date & Slot</th>
                        <th class="py-4 px-4">Consultation / Symptoms</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="appointments-table-body" class="divide-y divide-slate-100">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div id="appointments-empty-state" class="hidden apple-card p-12 text-center bg-white border border-slate-200">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
            <i class="fa-regular fa-calendar-xmark"></i>
        </div>
        <h4 class="font-black text-slate-900 text-base">No Appointments Found</h4>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">There are no consultations scheduled matching the chosen doctor, date, or search filters.</p>
        <div class="mt-4 flex items-center justify-center gap-2">
            <button type="button" onclick="resetAllFilters()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Clear Filters
            </button>
            <a href="book.php" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm">
                Book New Appointment
            </a>
        </div>
    </div>

    <!-- Loading State Skeleton -->
    <div id="appointments-loading-state" class="hidden py-16 text-center text-slate-400 text-xs">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-indigo-600 block mb-2"></i>
        Loading appointments and medical schedule...
    </div>
</div>

<!-- ================= CANCEL APPOINTMENT CONFIRMATION MODAL ================= -->
<div id="modal-cancel-appointment" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="apple-card max-w-md w-full p-6 relative bg-white shadow-2xl border border-slate-200">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h4 class="font-black text-slate-900 text-base mb-1">Cancel Patient Appointment</h4>
        <p class="text-xs text-slate-500 mb-4">Are you sure you want to cancel this appointment? This action cannot be reversed.</p>
        <input type="hidden" id="cancel-modal-appt-id">
        <div class="mb-4">
            <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Cancellation</label>
            <input type="text" id="cancel-modal-reason" placeholder="e.g. Patient requested, Doctor rescheduled..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-rose-500">
        </div>
        <div class="flex items-center justify-end gap-2">
            <button type="button" onclick="closeCancelModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs">Keep Appointment</button>
            <button type="button" onclick="executeCancelAppointment()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow">Confirm Cancellation</button>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
    let currentViewMode = 'cards';
    let allDoctors = [];
    let currentAppointmentsList = [];
    let searchDebounceTimer = null;

    const initialDocParam = "<?php echo htmlspecialchars($initial_doctor_id); ?>";

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctorsList();
        triggerLoadAppointments();
    });

    async function loadDoctorsList() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctors = data.doctors || [];
                const select = document.getElementById('filter-doctor');
                select.innerHTML = '<option value="all">👨‍⚕️ All Medical Doctors (All Departments)</option>';
                allDoctors.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id;
                    const cats = (doc.categories || []).join(', ') || 'General';
                    opt.textContent = `${doc.name} (${cats})`;
                    if (doc.id === initialDocParam) opt.selected = true;
                    select.appendChild(opt);
                });
            }
        } catch (e) { console.error('Error loading doctors:', e); }
    }

    function setQuickDate(type) {
        const dateInput = document.getElementById('filter-date');
        const now = new Date();
        if (type === 'today') {
            dateInput.value = now.toISOString().substring(0, 10);
        } else if (type === 'tomorrow') {
            const tmrw = new Date();
            tmrw.setDate(tmrw.getDate() + 1);
            dateInput.value = tmrw.toISOString().substring(0, 10);
        }
        triggerLoadAppointments();
    }

    function toggleAdvancedSearch() {
        const panel = document.getElementById('advanced-search-panel');
        const btn = document.getElementById('btn-toggle-adv-search');
        panel.classList.toggle('hidden');
        if (panel.classList.contains('hidden')) {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('bg-indigo-50', 'text-indigo-700');
        } else {
            btn.classList.remove('bg-indigo-50', 'text-indigo-700');
            btn.classList.add('bg-indigo-600', 'text-white');
        }
    }

    function setAppointmentsViewMode(mode) {
        currentViewMode = mode;
        const btnCards = document.getElementById('btn-view-cards');
        const btnTable = document.getElementById('btn-view-table');
        const cardsEl = document.getElementById('appointments-cards-container');
        const tableEl = document.getElementById('appointments-table-container');

        if (mode === 'cards') {
            btnCards.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition";
            btnTable.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition";
            cardsEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
        } else {
            btnTable.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition";
            btnCards.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition";
            cardsEl.classList.add('hidden');
            tableEl.classList.remove('hidden');
        }
    }

    function debounceSearch() {
        clearTimeout(searchDebounceTimer);
        const clearBtn = document.getElementById('btn-clear-search');
        const val = document.getElementById('filter-search').value.trim();
        clearBtn.classList.toggle('hidden', val.length === 0);
        searchDebounceTimer = setTimeout(triggerLoadAppointments, 300);
    }

    function clearKeywordSearch() {
        document.getElementById('filter-search').value = '';
        document.getElementById('btn-clear-search').classList.add('hidden');
        triggerLoadAppointments();
    }

    function resetAllFilters() {
        document.getElementById('filter-doctor').value = 'all';
        document.getElementById('filter-date').value = new Date().toISOString().substring(0, 10);
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-type').value = 'all';
        document.getElementById('btn-clear-search').classList.add('hidden');
        triggerLoadAppointments();
    }

    async function triggerLoadAppointments() {
        const docId = document.getElementById('filter-doctor').value;
        const date = document.getElementById('filter-date').value;
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        const search = document.getElementById('filter-search').value.trim();

        // Update badge labels
        const docSelect = document.getElementById('filter-doctor');
        const selectedDocText = docSelect.options[docSelect.selectedIndex]?.text || 'All';
        document.getElementById('active-doctor-badge').textContent = `Doctor: ${selectedDocText.split('(')[0].trim()}`;
        document.getElementById('active-date-badge').textContent = `Date: ${date || 'All'}`;

        const cardsEl = document.getElementById('appointments-cards-container');
        const tableBody = document.getElementById('appointments-table-body');
        const emptyEl = document.getElementById('appointments-empty-state');
        const loadingEl = document.getElementById('appointments-loading-state');

        loadingEl.classList.remove('hidden');
        cardsEl.classList.add('hidden');
        document.getElementById('appointments-table-container').classList.add('hidden');
        emptyEl.classList.add('hidden');

        try {
            const params = new URLSearchParams({
                action: 'get_appointments_filtered',
                doctor_id: docId,
                date: date,
                status: status,
                type: type,
                search: search
            });

            const res = await fetch(`api/doctors.php?${params.toString()}`);
            const data = await res.json();

            loadingEl.classList.add('hidden');

            if (data.status === 'success') {
                currentAppointmentsList = data.appointments || [];
                
                // Update metrics
                document.getElementById('metric-total').textContent = data.summary.total || 0;
                document.getElementById('metric-waiting').textContent = data.summary.waiting || 0;
                document.getElementById('metric-consulting').textContent = data.summary.consulting || 0;
                document.getElementById('metric-completed').textContent = data.summary.completed || 0;
                document.getElementById('results-count-badge').textContent = `${currentAppointmentsList.length} Appointments Found`;

                if (currentAppointmentsList.length === 0) {
                    emptyEl.classList.remove('hidden');
                    return;
                }

                // Render both views
                renderAppointmentsCards(currentAppointmentsList);
                renderAppointmentsTable(currentAppointmentsList);

                if (currentViewMode === 'cards') {
                    cardsEl.classList.remove('hidden');
                } else {
                    document.getElementById('appointments-table-container').classList.remove('hidden');
                }
            } else {
                showToast('Error', data.message || 'Failed to fetch appointments', 'error');
            }
        } catch (e) {
            console.error('Error fetching appointments:', e);
            loadingEl.classList.add('hidden');
            showToast('Network Error', 'Could not retrieve appointments', 'error');
        }
    }

    function renderAppointmentsCards(list) {
        const container = document.getElementById('appointments-cards-container');
        container.innerHTML = '';

        list.forEach(a => {
            const card = document.createElement('div');
            card.className = "apple-card p-4 sm:p-5 flex flex-col justify-between border border-slate-200/90 bg-white hover:border-indigo-300 hover:shadow-md transition";
            
            const initials = (((a.patient_name ? a.patient_name.charAt(0) : '') + (a.patient_surname ? a.patient_surname.charAt(0) : '')) || 'P').toUpperCase();
            
            // Status styling
            let statusBadge = '';
            if (a.status === 'Cancelled') {
                statusBadge = '<span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">Cancelled</span>';
            } else if (a.stage >= 5 || (a.status || '').includes('Discharged')) {
                statusBadge = '<span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Completed</span>';
            } else if (a.stage === 4) {
                statusBadge = '<span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">In Consultation</span>';
            } else {
                statusBadge = '<span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Waiting / Queued</span>';
            }

            card.innerHTML = `
                <div>
                    <!-- Top Token & Status Bar -->
                    <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span class="bg-indigo-600 text-white font-black text-xs px-2.5 py-0.5 rounded-lg shadow-2xs">#${a.token_no}</span>
                            <span class="text-[11px] font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                <i class="fa-regular fa-clock text-slate-400 mr-1"></i>${a.slot || 'N/A'}
                            </span>
                        </div>
                        ${statusBadge}
                    </div>

                    <!-- Patient Info -->
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-black flex items-center justify-center text-xs shrink-0 shadow-2xs">
                            ${initials}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-extrabold text-slate-900 text-sm truncate leading-tight">${a.patient_name} ${a.patient_surname}</h4>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5 truncate">${a.patient_id} • 🩸 ${a.blood_group || 'Unknown'}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i>${a.patient_phone || 'N/A'}</div>
                        </div>
                    </div>

                    <!-- Doctor & Reason Details -->
                    <div class="bg-slate-50/80 rounded-xl p-2.5 border border-slate-200/70 text-xs space-y-1 mb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-semibold">Doctor:</span>
                            <strong class="text-slate-900 truncate max-w-[170px]">${a.doctor_name || 'Unassigned'}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-semibold">Type:</span>
                            <span class="text-indigo-700 font-bold">${a.type || 'General'}</span>
                        </div>
                        <div class="pt-1 border-t border-slate-200/50">
                            <span class="text-slate-500 font-semibold block text-[10px] uppercase">Symptoms / Reason:</span>
                            <p class="text-slate-700 line-clamp-2 text-[11px] mt-0.5 font-medium">${a.symptoms || 'Regular consultation'}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="patient_profile.php?id=${encodeURIComponent(a.patient_id)}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl border border-indigo-200 transition flex items-center gap-1 shadow-2xs">
                        <i class="fa-regular fa-id-card text-[11px]"></i> Profile
                    </a>
                    <div class="flex items-center gap-1.5">
                        <a href="queue.php" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-xl transition" title="View in Live Pipeline">
                            <i class="fa-solid fa-bars-staggered"></i>
                        </a>
                        ${a.status !== 'Cancelled' ? `
                            <button type="button" onclick="promptCancelAppointment(${a.id})" class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-xl border border-rose-200 transition" title="Cancel Appointment">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function renderAppointmentsTable(list) {
        const tbody = document.getElementById('appointments-table-body');
        tbody.innerHTML = '';

        list.forEach(a => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50/80 transition group";

            let statusBadge = '';
            if (a.status === 'Cancelled') {
                statusBadge = '<span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-rose-100 text-rose-800">Cancelled</span>';
            } else if (a.stage >= 5 || (a.status || '').includes('Discharged')) {
                statusBadge = '<span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">Completed</span>';
            } else if (a.stage === 4) {
                statusBadge = '<span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-purple-100 text-purple-800">Consultation</span>';
            } else {
                statusBadge = '<span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">Waiting</span>';
            }

            tr.innerHTML = `
                <td class="py-3.5 px-4 text-center font-black text-indigo-700">#${a.token_no}</td>
                <td class="py-3.5 px-4">
                    <div class="font-extrabold text-slate-900 text-xs">${a.patient_name} ${a.patient_surname}</div>
                    <div class="text-[11px] text-slate-500 font-mono">${a.patient_id} • 📞 ${a.patient_phone || 'N/A'} • 🩸 ${a.blood_group || 'Unknown'}</div>
                </td>
                <td class="py-3.5 px-4">
                    <div class="font-bold text-slate-800 text-xs">${a.doctor_name || 'Unassigned'}</div>
                    <div class="text-[10px] text-slate-400 truncate max-w-[160px]">${a.doctor_specialties || 'General'}</div>
                </td>
                <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700">
                    <div>${a.date}</div>
                    <div class="text-indigo-600 font-bold">${a.slot || 'N/A'}</div>
                </td>
                <td class="py-3.5 px-4 max-w-xs">
                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 inline-block mb-1">${a.type || 'General'}</span>
                    <p class="text-slate-600 text-xs truncate font-medium">${a.symptoms || 'General consultation'}</p>
                </td>
                <td class="py-3.5 px-4">${statusBadge}</td>
                <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="patient_profile.php?id=${encodeURIComponent(a.patient_id)}" class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 border border-slate-200 transition" title="Patient Profile">
                            <i class="fa-regular fa-id-card text-xs"></i>
                        </a>
                        <a href="queue.php" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 border border-slate-200 transition" title="Live Pipeline">
                            <i class="fa-solid fa-bars-staggered text-xs"></i>
                        </a>
                        ${a.status !== 'Cancelled' ? `
                            <button onclick="promptCancelAppointment(${a.id})" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 border border-slate-200 transition" title="Cancel">
                                <i class="fa-solid fa-ban text-xs"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function promptCancelAppointment(id) {
        document.getElementById('cancel-modal-appt-id').value = id;
        document.getElementById('cancel-modal-reason').value = '';
        document.getElementById('modal-cancel-appointment').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('modal-cancel-appointment').classList.add('hidden');
    }

    async function executeCancelAppointment() {
        const apptId = document.getElementById('cancel-modal-appt-id').value;
        const reason = document.getElementById('cancel-modal-reason').value.trim() || 'Cancelled by staff';

        try {
            const res = await fetch('api/doctors.php?action=cancel_appointment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ appointment_id: apptId, reason })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeCancelModal();
                showToast('Cancelled', 'Appointment cancelled successfully', 'success');
                triggerLoadAppointments();
            } else {
                showToast('Error', data.message || 'Could not cancel', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Connection failed', 'error');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

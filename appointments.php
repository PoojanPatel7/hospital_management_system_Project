<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$initial_doctor_id = $_GET['doctor_id'] ?? 'all';
$initial_date = $_GET['date'] ?? date('Y-m-d');
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Appointments</span>
            <span id="header-count-badge" class="text-xs font-black px-3 py-1 rounded-full bg-purple-100 text-purple-800">
                0 Total
            </span>
        </h1>
        <p class="text-slate-500 mt-1 text-sm font-medium">View, search, and manage doctor consultations.</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="doctor_slots.php" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl border border-slate-200 text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-regular fa-clock text-purple-600"></i> <span>Doctor Slots</span>
        </a>
        <a href="book.php" class="bg-purple-600 hover:bg-purple-700 text-white font-black py-2.5 px-4 rounded-xl shadow-md shadow-purple-600/20 transition flex items-center gap-2 text-sm cursor-pointer">
            <i class="fa-solid fa-plus"></i> <span>Book Appointment</span>
        </a>
    </div>
</div>

<!-- ================= FILTERS CARD (BIG & SIMPLE) ================= -->
<div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200/90 shadow-sm mb-5 sm:mb-6 space-y-4">
    
    <!-- Top Row: Date, Doctor, Search -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
        
        <!-- Date Selector -->
        <div class="md:col-span-4">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Date</label>
            <div class="flex items-center gap-2">
                <input 
                    type="date" 
                    id="filter-date" 
                    value="<?php echo htmlspecialchars($initial_date); ?>" 
                    onchange="loadAppointments()" 
                    class="w-full h-12 px-3.5 text-sm sm:text-base font-black text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer">
                
                <button type="button" onclick="setDatePreset('today')" class="h-12 px-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-black text-xs border border-purple-200 transition shrink-0 cursor-pointer">
                    Today
                </button>
                <button type="button" onclick="setDatePreset('tomorrow')" class="h-12 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition shrink-0 cursor-pointer">
                    Tmrw
                </button>
            </div>
        </div>

        <!-- Doctor Selector -->
        <div class="md:col-span-4">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Doctor</label>
            <div class="relative">
                <select id="filter-doctor" onchange="loadAppointments()" class="w-full h-12 pl-10 pr-8 text-sm sm:text-base font-black text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer appearance-none">
                    <option value="all">👨‍⚕️ All Doctors</option>
                    <!-- Populated dynamically -->
                </select>
                <i class="fa-solid fa-user-doctor absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-600 text-sm pointer-events-none"></i>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>
        </div>

        <!-- Patient Search Input -->
        <div class="md:col-span-4">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Search Patient</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input 
                    type="text" 
                    id="filter-search" 
                    oninput="debounceSearch()" 
                    placeholder="Name, phone, or token..." 
                    class="w-full h-12 pl-10 pr-9 text-sm font-bold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-purple-600 focus:bg-white outline-none transition">
                <button type="button" id="btn-clear-search" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- Bottom Row: Simple Status Filter Tabs (Live Counts) -->
    <div class="pt-3 border-t-2 border-slate-100 flex items-center justify-between gap-2 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-2">
            <button type="button" onclick="setStatusFilter('all')" class="status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-black transition cursor-pointer bg-purple-600 text-white shadow-sm" data-status="all">
                All (<span id="count-all">0</span>)
            </button>
            <button type="button" onclick="setStatusFilter('Waiting')" class="status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-100 hover:bg-amber-50 text-slate-700 hover:text-amber-800" data-status="Waiting">
                🟡 Waiting (<span id="count-waiting">0</span>)
            </button>
            <button type="button" onclick="setStatusFilter('In Consultation')" class="status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-800" data-status="In Consultation">
                🟣 Consulting (<span id="count-consulting">0</span>)
            </button>
            <button type="button" onclick="setStatusFilter('Discharged (Normal Medicine)')" class="status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800" data-status="Discharged (Normal Medicine)">
                🟢 Completed (<span id="count-completed">0</span>)
            </button>
            <button type="button" onclick="setStatusFilter('Cancelled')" class="status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-800" data-status="Cancelled">
                🔴 Cancelled
            </button>
        </div>

        <button type="button" onclick="resetFilters()" class="text-xs font-bold text-slate-400 hover:text-purple-700 px-2 py-1 transition cursor-pointer shrink-0">
            <i class="fa-solid fa-rotate-left mr-1"></i> Reset
        </button>
    </div>

</div>

<!-- ================= APPOINTMENTS LIST CONTAINER ================= -->
<div>
    <!-- Cards Grid -->
    <div id="appointments-cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Loaded via JS -->
    </div>

    <!-- Empty State -->
    <div id="appointments-empty-state" class="hidden bg-white p-10 rounded-2xl border-2 border-slate-200 text-center space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto font-black">
            <i class="fa-regular fa-calendar-xmark"></i>
        </div>
        <h4 class="text-lg font-black text-slate-900">No Appointments Found</h4>
        <p class="text-sm font-medium text-slate-500">No consultations scheduled for the selected doctor or date.</p>
        <div>
            <a href="book.php" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-md transition inline-flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus"></i> <span>Book New Appointment</span>
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div id="appointments-loading-state" class="hidden py-16 text-center text-slate-400 font-bold text-sm">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-purple-600 block mb-2 mx-auto"></i>
        Loading appointments...
    </div>
</div>

<!-- ================= CANCEL APPOINTMENT MODAL ================= -->
<div id="modal-cancel-appointment" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white max-w-md w-full p-6 rounded-2xl shadow-2xl border-2 border-slate-200 relative">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4 font-black">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h4 class="font-black text-slate-900 text-lg mb-1">Cancel Appointment</h4>
        <p class="text-sm text-slate-500 mb-4">Are you sure you want to cancel this appointment?</p>
        <input type="hidden" id="cancel-modal-appt-id">
        <div class="mb-5">
            <label class="block text-xs font-black uppercase text-slate-600 mb-1">Reason (Optional)</label>
            <input type="text" id="cancel-modal-reason" placeholder="e.g. Patient requested, Doctor rescheduled..." class="w-full h-11 border-2 border-slate-200 rounded-xl px-3.5 text-sm font-bold outline-none focus:border-rose-500">
        </div>
        <div class="flex items-center justify-end gap-2.5">
            <button type="button" onclick="closeCancelModal()" class="px-4 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-100 font-black text-xs cursor-pointer">Keep Appointment</button>
            <button type="button" onclick="executeCancelAppointment()" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md cursor-pointer">Confirm Cancel</button>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let allDoctors = [];
    let currentStatusFilter = 'all';
    let searchDebounceTimer = null;

    const initialDocParam = "<?php echo htmlspecialchars($initial_doctor_id); ?>";

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctorsList();
        loadAppointments();
    });

    async function loadDoctorsList() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctors = data.doctors || [];
                const select = document.getElementById('filter-doctor');
                select.innerHTML = '<option value="all">👨‍⚕️ All Doctors</option>';
                allDoctors.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id;
                    const cats = (doc.categories || []).join(', ') || 'General';
                    opt.textContent = `${doc.name} (${cats})`;
                    if (doc.id === initialDocParam) opt.selected = true;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Error loading doctors:', e);
        }
    }

    function setDatePreset(type) {
        const dateInput = document.getElementById('filter-date');
        const now = new Date();
        if (type === 'today') {
            dateInput.value = now.toISOString().substring(0, 10);
        } else if (type === 'tomorrow') {
            const tmrw = new Date();
            tmrw.setDate(tmrw.getDate() + 1);
            dateInput.value = tmrw.toISOString().substring(0, 10);
        }
        loadAppointments();
    }

    function setStatusFilter(status) {
        currentStatusFilter = status;
        document.querySelectorAll('.status-tab-btn').forEach(btn => {
            const bStat = btn.getAttribute('data-status');
            if (bStat === status) {
                btn.className = "status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-black transition cursor-pointer bg-purple-600 text-white shadow-sm";
            } else {
                btn.className = "status-tab-btn px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700";
            }
        });
        loadAppointments();
    }

    function debounceSearch() {
        clearTimeout(searchDebounceTimer);
        const clearBtn = document.getElementById('btn-clear-search');
        const val = document.getElementById('filter-search').value.trim();
        clearBtn.classList.toggle('hidden', val.length === 0);
        searchDebounceTimer = setTimeout(loadAppointments, 300);
    }

    function clearSearch() {
        document.getElementById('filter-search').value = '';
        document.getElementById('btn-clear-search').classList.add('hidden');
        loadAppointments();
    }

    function resetFilters() {
        document.getElementById('filter-doctor').value = 'all';
        document.getElementById('filter-date').value = new Date().toISOString().substring(0, 10);
        document.getElementById('filter-search').value = '';
        document.getElementById('btn-clear-search').classList.add('hidden');
        setStatusFilter('all');
    }

    async function loadAppointments() {
        const docId = document.getElementById('filter-doctor').value;
        const date = document.getElementById('filter-date').value;
        const search = document.getElementById('filter-search').value.trim();

        const cardsEl = document.getElementById('appointments-cards-container');
        const emptyEl = document.getElementById('appointments-empty-state');
        const loadingEl = document.getElementById('appointments-loading-state');

        loadingEl.classList.remove('hidden');
        cardsEl.classList.add('hidden');
        emptyEl.classList.add('hidden');

        try {
            const params = new URLSearchParams({
                action: 'get_appointments_filtered',
                doctor_id: docId,
                date: date,
                status: currentStatusFilter,
                search: search
            });

            const res = await fetch(`api/doctors.php?${params.toString()}`);
            const data = await res.json();

            loadingEl.classList.add('hidden');

            if (data.status === 'success') {
                const list = data.appointments || [];

                // Update summary metrics
                document.getElementById('header-count-badge').textContent = `${data.summary?.total || list.length} Total`;
                document.getElementById('count-all').textContent = data.summary?.total || list.length;
                document.getElementById('count-waiting').textContent = data.summary?.waiting || 0;
                document.getElementById('count-consulting').textContent = data.summary?.consulting || 0;
                document.getElementById('count-completed').textContent = data.summary?.completed || 0;

                if (list.length === 0) {
                    emptyEl.classList.remove('hidden');
                    return;
                }

                renderAppointmentCards(list);
                cardsEl.classList.remove('hidden');
            } else {
                showToast('Error', data.message || 'Could not fetch appointments', 'error');
            }
        } catch (e) {
            console.error('Error fetching appointments:', e);
            loadingEl.classList.add('hidden');
            showToast('Error', 'Connection failed', 'error');
        }
    }

    function renderAppointmentCards(list) {
        const container = document.getElementById('appointments-cards-container');
        container.innerHTML = '';

        list.forEach(a => {
            const card = document.createElement('div');
            card.className = "bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200/90 shadow-sm flex flex-col justify-between hover:border-purple-300 transition";

            // Status Badge
            let statusBadge = '';
            if (a.status === 'Cancelled') {
                statusBadge = '<span class="text-xs font-black px-2.5 py-1 rounded-full bg-rose-100 text-rose-800">Cancelled</span>';
            } else if (a.stage >= 5 || (a.status || '').includes('Discharged')) {
                statusBadge = '<span class="text-xs font-black px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">Completed</span>';
            } else if (a.stage === 4) {
                statusBadge = '<span class="text-xs font-black px-2.5 py-1 rounded-full bg-purple-100 text-purple-800">Consulting</span>';
            } else {
                statusBadge = '<span class="text-xs font-black px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">Waiting</span>';
            }

            card.innerHTML = `
                <div>
                    <!-- Top Token & Time Slot -->
                    <div class="flex items-center justify-between pb-3 mb-3 border-b-2 border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="bg-purple-600 text-white font-black text-sm px-3 py-1 rounded-xl shadow-sm">#${a.token_no}</span>
                            <span class="text-xs sm:text-sm font-mono font-black text-slate-800 bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200">
                                <i class="fa-regular fa-clock text-purple-600 mr-1"></i>${a.slot || 'Regular'}
                            </span>
                        </div>
                        ${statusBadge}
                    </div>

                    <!-- Patient Info -->
                    <div class="mb-3">
                        <h3 class="font-black text-slate-900 text-base sm:text-lg leading-tight truncate">
                            ${a.patient_name} ${a.patient_surname}
                        </h3>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 font-bold">
                            <span class="font-mono">${a.patient_id}</span>
                            ${a.patient_phone ? `
                                <span>•</span>
                                <a href="tel:${a.patient_phone}" class="text-purple-700 hover:underline">
                                    <i class="fa-solid fa-phone text-[10px] mr-1"></i>${a.patient_phone}
                                </a>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Doctor & Symptoms -->
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-1 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-bold">Doctor:</span>
                            <span class="font-black text-slate-900 truncate max-w-[180px]">${a.doctor_name || 'Unassigned'}</span>
                        </div>
                        ${a.symptoms ? `
                            <div class="flex items-start justify-between gap-2 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-500 font-bold shrink-0">Reason:</span>
                                <span class="font-bold text-slate-700 truncate max-w-[180px]">${a.symptoms}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="pt-3 border-t-2 border-slate-100 flex items-center justify-between gap-2">
                    <a href="patient_profile.php?id=${encodeURIComponent(a.patient_id)}" class="flex-1 py-2 px-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-black text-xs text-center border border-purple-200 transition">
                        Patient Profile
                    </a>
                    <a href="queue.php" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition" title="Queue Pipeline">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </a>
                    ${a.status !== 'Cancelled' ? `
                        <button type="button" onclick="promptCancelAppointment(${a.id})" class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition cursor-pointer" title="Cancel">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    ` : ''}
                </div>
            `;
            container.appendChild(card);
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
                loadAppointments();
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

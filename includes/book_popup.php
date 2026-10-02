<!-- Book New Patient Modal (Wider for PC) -->
<div id="modal-book-direct" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="apple-card max-w-2xl sm:max-w-3xl lg:max-w-5xl xl:max-w-6xl w-full relative my-8 max-h-[92vh] flex flex-col shadow-2xl overflow-hidden bg-white">
        <button onclick="closeDirectBookModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 z-50 w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded-full transition"><i class="fa-solid fa-xmark text-sm"></i></button>

        <div class="flex flex-col lg:flex-row h-full overflow-hidden max-h-[92vh]">
            
            <!-- Left Side (Live Info) -->
            <div class="w-full lg:w-[60%] bg-slate-50 border-b lg:border-b-0 lg:border-r border-slate-200 p-6 sm:p-8 overflow-y-auto custom-scrollbar flex flex-col relative">
                
                <!-- Header Card (Image + Doc Name) -->
                <div class="mb-5 bg-white border border-slate-200 rounded-2xl p-3 shadow-sm flex items-center gap-4">
                    <div class="w-24 h-24 shrink-0 rounded-xl bg-indigo-50/50 border border-indigo-50 flex items-center justify-center p-2">
                        <img src="images/Book Appointment.jpg" alt="Book Appointment" class="max-h-full max-w-full object-contain">
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-500 mb-1">Live Schedule</p>
                        <h4 class="font-black text-2xl leading-tight text-slate-900 drop-shadow-sm" id="right-panel-doc-name">Doctor's Schedule</h4>
                    </div>
                </div>

                <!-- Live Pipeline -->
                <div class="mb-6 flex-1 flex flex-col min-h-0">
                    <div class="flex items-center justify-between mb-3 shrink-0">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-bars-staggered text-indigo-500"></i> <span id="pipeline-heading-label">Today's Live Pipeline</span>
                        </h4>
                        <span id="right-panel-pipeline-count" class="text-[10px] font-bold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full">0</span>
                    </div>
                    
                    <div class="overflow-x-auto bg-white border border-slate-200 rounded-xl shadow-sm custom-scrollbar flex-1">
                        <table class="w-full text-left text-xs text-slate-600 min-w-[600px]">
                            <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4">Token #</th>
                                    <th class="py-3 px-4">Stage & Status</th>
                                    <th class="py-3 px-4">Patient Details</th>
                                    <th class="py-3 px-4">Priority</th>
                                    <th class="py-3 px-4">Symptoms</th>
                                </tr>
                            </thead>
                            <tbody id="right-panel-pipeline" class="divide-y divide-slate-100">
                                <!-- Table rows injected here via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Available Slots -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <i class="fa-regular fa-clock text-indigo-500"></i> <span id="slots-heading-label">Available Slots Today</span>
                    </h4>
                    <div id="right-panel-slots" class="bg-white border border-slate-200 rounded-xl p-4 flex flex-wrap gap-2 shadow-sm">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
            
            <!-- Right Side (Form) -->
            <div class="w-full lg:w-[40%] p-6 sm:p-8 overflow-y-auto custom-scrollbar">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center text-xl sm:text-2xl bg-indigo-50 text-indigo-600 shrink-0 border border-indigo-100">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Book Consultation</h3>
                        <p id="direct-doc-name" class="text-sm text-indigo-600 font-bold mt-0.5">Dr. Name</p>
                    </div>
                </div>

                <form id="form-book-direct" onsubmit="handleDirectBook(event)" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Select Patient *</label>
                        <input type="hidden" id="direct-patient-id" required>

                        <!-- Search Input with Live Suggestions Dropdown -->
                        <div id="direct-patient-search-box" class="relative">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                    <input 
                                        type="text" 
                                        id="direct-patient-search-input" 
                                        oninput="handleDirectPatientSearch(this.value)" 
                                        onfocus="handleDirectPatientSearch(this.value)"
                                        autocomplete="off"
                                        placeholder="Search by any info (Name, MRN, Phone...)" 
                                        class="w-full border border-slate-200 rounded-xl pl-10 pr-8 py-3 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
                                    <button type="button" id="direct-patient-search-clear" onclick="clearDirectPatientSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <button type="button" onclick="openQuickRegisterModal()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-3 rounded-xl text-sm font-bold transition flex items-center justify-center shrink-0 border border-indigo-200" title="Register new patient"><i class="fa-solid fa-user-plus mr-1.5"></i> New</button>
                            </div>

                            <!-- Floating Live Suggestions Dropdown -->
                            <div id="direct-patient-suggestions" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                <!-- Populated dynamically via JS -->
                            </div>
                        </div>

                        <!-- Selected Patient Display Card -->
                        <div id="direct-patient-selected-card" class="hidden bg-indigo-50/80 border border-indigo-200 rounded-2xl p-4 mt-2 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div id="direct-selected-avatar" class="w-11 h-11 rounded-xl bg-indigo-600 text-white font-black flex items-center justify-center text-sm shrink-0 shadow-md">
                                    --
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h5 id="direct-selected-name" class="font-extrabold text-slate-900 text-sm truncate">Patient Name</h5>
                                        <span id="direct-selected-blood" class="text-[10px] font-bold px-2 py-0.5 rounded bg-white text-rose-600 border border-rose-100 shrink-0 shadow-sm">🩸 B+</span>
                                    </div>
                                    <p id="direct-selected-details" class="text-xs text-slate-500 mt-0.5 truncate font-medium">MRN: CP-2026-002 • 📞 9876543210</p>
                                </div>
                            </div>
                            <button type="button" onclick="resetDirectPatientSelection()" class="text-xs font-bold text-slate-600 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 px-3 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5 shrink-0 ml-3">
                                <i class="fa-solid fa-pen text-[10px]"></i> Change
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Appointment Date *</label>
                            <div class="relative group">
                                <input type="date" id="direct-date" onchange="handleDirectDateChange(this.value)" required class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm group-hover:border-indigo-300">
                                <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-500 transition pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="relative" id="custom-type-dropdown-container">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Consultation Type *</label>
                            <input type="hidden" id="direct-type" value="General Consultation" required>
                            
                            <button type="button" onclick="toggleTypeDropdown(event)" class="w-full relative flex items-center border border-slate-200 rounded-xl pl-11 pr-10 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 hover:bg-white hover:border-indigo-300 transition shadow-sm text-left group">
                                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 w-6 h-6 bg-indigo-100 rounded-md flex items-center justify-center text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <i id="direct-type-icon" class="fa-solid fa-stethoscope text-[10px]"></i>
                                </div>
                                <span id="direct-type-display" class="truncate w-full block">General Consultation</span>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] transition group-hover:text-indigo-600"></i>
                            </button>
                            
                            <div id="type-dropdown-menu" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden flex flex-col p-1.5 gap-0.5 min-w-[200px]">
                                <button type="button" onclick="selectTypeOption('General Consultation', 'fa-stethoscope', 'text-indigo-600', 'bg-indigo-100')" class="text-left w-full px-3 py-2.5 rounded-lg text-sm font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition flex items-center gap-2">
                                    <div class="w-6 h-6 bg-indigo-100 rounded flex items-center justify-center text-indigo-600 shrink-0"><i class="fa-solid fa-stethoscope text-[10px]"></i></div>
                                    General Consultation
                                </button>
                                <button type="button" onclick="selectTypeOption('Follow-up', 'fa-rotate-left', 'text-blue-600', 'bg-blue-100')" class="text-left w-full px-3 py-2.5 rounded-lg text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition flex items-center gap-2">
                                    <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center text-blue-600 shrink-0"><i class="fa-solid fa-rotate-left text-[10px]"></i></div>
                                    Follow-up
                                </button>
                                <button type="button" onclick="selectTypeOption('Emergency Case', 'fa-triangle-exclamation', 'text-rose-600', 'bg-rose-100')" class="text-left w-full px-3 py-2.5 rounded-lg text-sm font-bold text-slate-700 hover:bg-rose-50 hover:text-rose-700 transition flex items-center gap-2">
                                    <div class="w-6 h-6 bg-rose-100 rounded flex items-center justify-center text-rose-600 shrink-0"><i class="fa-solid fa-triangle-exclamation text-[10px]"></i></div>
                                    Emergency Case
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Symptoms / Reason *</label>
                        <textarea id="direct-symptoms" required rows="4" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm" placeholder="Describe symptoms or reasons in detail..."></textarea>
                    </div>

                    <!-- Hidden Slot Input & Display -->
                    <input type="hidden" id="direct-slot" required>
                    <div id="direct-slot-display-container" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800">Selected Time Slot:</span>
                        <span id="direct-slot-display" class="font-black text-emerald-700 bg-white px-2 py-1 rounded shadow-sm border border-emerald-100">--:--</span>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-[0_4px_15px_rgba(79,70,229,0.4)] hover:shadow-[0_6px_20px_rgba(79,70,229,0.5)] hover:-translate-y-0.5 transition-all duration-300 text-sm">
                            Confirm & Review Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    let currentDoctorId = null;
    let currentDoctorName = null;
    let tempBookPayload = null;
    let allAvailableDoctors = [];
    let allPatientsList = [];
    let selectedDirectPatient = null;

    document.addEventListener('DOMContentLoaded', () => {
        fetchDoctorsForBooking();
        fetchPatientsForDirectBooking();
    });

    function scrollDoctorSlider(sliderId, direction) {
        const el = document.getElementById(sliderId);
        if (el) {
            el.scrollBy({ left: 300 * direction, behavior: 'smooth' });
        }
    }

    async function fetchDoctorsForBooking() {
        try {
            const res = await fetch('api/booking.php?action=get_doctors_by_category');
            const data = await res.json();
            if (data.status === 'success') {
                allAvailableDoctors = data.doctors || [];
                renderDoctorsCards(allAvailableDoctors);
            }
        } catch (e) { console.error(e); }
    }

    function filterDoctorsList() {
        const q = (document.getElementById('doc-filter-query').value || '').toLowerCase().trim();
        if (!q) {
            renderDoctorsCards(allAvailableDoctors);
            return;
        }
        const filtered = allAvailableDoctors.filter(d => {
            const str = `${d.name} ${d.phone || ''} ${(d.categories || []).join(' ')}`.toLowerCase();
            return str.includes(q);
        });
        renderDoctorsCards(filtered);
    }

    function renderDoctorsCards(docs) {
        const slider = document.getElementById('book-page-slider');
        const container = document.getElementById('book-docs-container');
        const countBadge = document.getElementById('book-docs-count-badge');
        
        if (countBadge) countBadge.textContent = `${docs.length} ${docs.length === 1 ? 'Doctor' : 'Doctors'}`;
        if (container) container.innerHTML = '';
        if (slider) slider.innerHTML = '';

        if (docs.length === 0) {
            if (container) container.innerHTML = `<div class="col-span-full text-center text-slate-400 py-12 text-sm">No doctors match your search.</div>`;
            if (slider) slider.innerHTML = `<div class="text-center text-slate-400 py-6 text-xs w-full">No doctors match your search.</div>`;
            return;
        }

        docs.forEach(d => {
            const initials = (d.name || 'Dr').replace(/^Dr\.?\s*/i, '').substring(0, 2).toUpperCase() || 'DR';
            const cats = (d.categories || []).map(c => `<span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded mr-1 mb-1 border border-indigo-100">${c}</span>`).join('') || '<span class="text-xs text-slate-400 italic">General</span>';
            const isAvail = (d.is_available !== false && d.is_available !== 0);
            const availSlots = d.available_count ?? (d.slots || []).length;
            const totalSlots = d.total_slots ?? (d.slots || []).length;
            const timingText = d.today_timing || (isAvail ? '09:00 AM - 05:00 PM' : 'Day Off');

            // 1. Horizontal Slider Card
            if (slider) {
                const sliderCard = document.createElement('div');
                sliderCard.className = "w-[280px] sm:w-[300px] shrink-0 snap-start apple-card p-4 sm:p-5 flex flex-col justify-between border border-slate-200/80 bg-white hover:border-indigo-300 hover:shadow-md transition";
                sliderCard.innerHTML = `
                    <div>
                        <div class="flex items-start gap-3 mb-3">
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black shadow-inner">
                                    ${initials}
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full ${isAvail ? 'bg-emerald-500' : 'bg-slate-300'} absolute -bottom-0.5 -right-0.5 ring-2 ring-white"></span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-extrabold text-slate-900 text-sm truncate" title="${d.name}">${d.name}</h4>
                                <p class="text-xs text-slate-500 mt-0.5 truncate"><i class="fa-solid fa-phone text-[10px] mr-1 text-slate-400"></i>${d.phone || 'No phone'}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="flex flex-wrap gap-1">${cats}</div>
                        </div>

                        <div class="space-y-1 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100 mb-3">
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="font-medium flex items-center gap-1"><i class="fa-regular fa-clock text-slate-400"></i> Hours:</span>
                                <strong class="font-bold ${isAvail ? 'text-slate-800' : 'text-amber-600'}">${timingText}</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="font-medium flex items-center gap-1"><i class="fa-solid fa-ticket text-slate-400"></i> Free Slots:</span>
                                <strong class="font-bold ${availSlots > 0 ? 'text-emerald-700' : 'text-rose-600'}">${availSlots} / ${totalSlots} available</strong>
                            </div>
                        </div>
                    </div>

                    <button onclick="openDirectBook('${d.id}', '${escapeJs(d.name)}')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-indigo-600/20 transition text-xs flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-calendar-check"></i> Book Consultation
                    </button>
                `;
                slider.appendChild(sliderCard);
            }

            // 2. Directory Grid Card
            if (container) {
                container.innerHTML += `
                    <div class="apple-card p-5 sm:p-6 flex flex-col justify-between h-full border border-slate-200/80 bg-white hover:border-indigo-300 hover:shadow-md transition">
                        <div>
                            <div class="flex items-start gap-3.5 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner shrink-0 font-bold">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-extrabold text-slate-900 text-base leading-tight truncate">${d.name}</h3>
                                    <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> ${d.phone || 'No phone'}</p>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Specialties</div>
                                <div>${cats}</div>
                            </div>
                            <div class="text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 mb-4 space-y-1">
                                <div class="flex justify-between text-slate-600">
                                    <span>Hours Today:</span>
                                    <strong class="${isAvail ? 'text-slate-800' : 'text-amber-600'}">${timingText}</strong>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Free Slots:</span>
                                    <strong class="${availSlots > 0 ? 'text-emerald-700' : 'text-rose-600'}">${availSlots} available</strong>
                                </div>
                            </div>
                        </div>
                        <button onclick="openDirectBook('${d.id}', '${escapeJs(d.name)}')" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl shadow-md transition text-xs sm:text-sm flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-calendar-check"></i> Book Consultation
                        </button>
                    </div>
                `;
            }
        });
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    async function fetchPatientsForDirectBooking() {
        try {
            const res = await fetch('api/patients.php?action=get_all');
            const data = await res.json();
            if (data.status === 'success') {
                allPatientsList = data.patients || [];
            }
        } catch (e) { console.error(e); }
    }

    function handleDirectPatientSearch(query) {
        const dropdown = document.getElementById('direct-patient-suggestions');
        const clearBtn = document.getElementById('direct-patient-search-clear');
        const rawQ = (query || '').trim();

        if (clearBtn) clearBtn.classList.toggle('hidden', rawQ.length === 0);

        if (!allPatientsList || allPatientsList.length === 0) {
            dropdown.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center">Loading patients...</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        const terms = rawQ.toLowerCase().split(/\s+/).filter(Boolean);

        const matches = allPatientsList.filter(p => {
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
                    <button type="button" onclick="openQuickRegisterModal()" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">+ Register New Patient</button>
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
                div.onclick = () => selectDirectPatient(p);
                dropdown.appendChild(div);
            });
        }
        dropdown.classList.remove('hidden');
    }

    function selectDirectPatient(p) {
        selectedDirectPatient = p;
        document.getElementById('direct-patient-id').value = p.id;
        
        const initials = (((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : '')) || 'P').toUpperCase();
        document.getElementById('direct-selected-avatar').textContent = initials;
        document.getElementById('direct-selected-name').textContent = `${p.name} ${p.surname}`;
        document.getElementById('direct-selected-blood').textContent = `🩸 ${p.blood_group || 'Unknown'}`;
        document.getElementById('direct-selected-details').textContent = `MRN: ${p.id} • 📞 ${p.phone || 'N/A'} • Father: ${p.father_name || 'N/A'}`;
        
        const suggestions = document.getElementById('direct-patient-suggestions');
        if (suggestions) suggestions.classList.add('hidden');
        document.getElementById('direct-patient-search-box').classList.add('hidden');
        document.getElementById('direct-patient-selected-card').classList.remove('hidden');
    }

    function resetDirectPatientSelection() {
        selectedDirectPatient = null;
        document.getElementById('direct-patient-id').value = '';
        const card = document.getElementById('direct-patient-selected-card');
        const box = document.getElementById('direct-patient-search-box');
        if (card) card.classList.add('hidden');
        if (box) box.classList.remove('hidden');
        const input = document.getElementById('direct-patient-search-input');
        if (input) {
            input.value = '';
            input.focus();
            handleDirectPatientSearch('');
        }
    }

    function clearDirectPatientSearch() {
        const input = document.getElementById('direct-patient-search-input');
        if (input) {
            input.value = '';
            handleDirectPatientSearch('');
            input.focus();
        }
    }

    // Close suggestions dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#direct-patient-search-box')) {
            const dropdown = document.getElementById('direct-patient-suggestions');
            if (dropdown) dropdown.classList.add('hidden');
        }
    });

    async function fetchDoctorsForDropdown() {
        try {
            const res = await fetch('api/booking.php?action=get_doctors_by_category');
            const data = await res.json();
            if (data.status === 'success') {
                const select = document.getElementById('direct-doc-select');
                if (select) {
                    select.innerHTML = '<option value="">Select Doctor...</option>';
                    data.doctors.forEach(d => {
                        select.innerHTML += `<option value="${d.id}" data-name="${(d.name || '').replace(/"/g, '&quot;')}">${d.name}</option>`;
                    });
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function handleDirectDoctorSelect(selectEl) {
        if (!selectEl.value) {
            currentDoctorId = null;
            currentDoctorName = null;
            document.getElementById('right-panel-doc-name').innerHTML = 'Select a Doctor';
            document.getElementById('right-panel-slots').innerHTML = '';
            document.getElementById('right-panel-pipeline').innerHTML = '';
            document.getElementById('right-panel-pipeline-count').textContent = '0';
            return;
        }
        
        currentDoctorId = selectEl.value;
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        currentDoctorName = selectedOption.getAttribute('data-name');
        
        document.getElementById('right-panel-doc-name').innerHTML = currentDoctorName;
        
        const today = document.getElementById('direct-date').value || new Date().toISOString().substring(0, 10);
        await fetchScheduleForDate(today);
    }

    async function openDirectBook(docId, docName) {
        currentDoctorId = docId;
        currentDoctorName = docName;
        
        const directDocEl = document.getElementById('direct-doc-name');
        const rightPanelDocEl = document.getElementById('right-panel-doc-name');
        
        if (docId) {
            directDocEl.innerHTML = `With ${docName}`;
            rightPanelDocEl.innerHTML = docName;
        } else {
            directDocEl.innerHTML = `<select id="direct-doc-select" onchange="handleDirectDoctorSelect(this)" class="text-sm text-indigo-600 font-bold bg-transparent border border-indigo-200 outline-none w-full px-2 py-1 mt-1 rounded"><option value="">Select Doctor...</option></select>`;
            rightPanelDocEl.innerHTML = 'Select a Doctor';
            fetchDoctorsForDropdown();
        }

        document.getElementById('form-book-direct').reset();
        document.getElementById('direct-slot').value = '';
        document.getElementById('direct-slot-display-container').classList.add('hidden');
        resetDirectPatientSelection();
        
        if (!allPatientsList || allPatientsList.length === 0) {
            fetchPatientsForDirectBooking();
        }
        
        const today = new Date().toISOString().substring(0, 10);
        document.getElementById('direct-date').value = today;
        document.getElementById('modal-book-direct').classList.remove('hidden');

        await fetchScheduleForDate(today);
    }

    async function handleDirectDateChange(newDate) {
        if (!newDate) return;
        document.getElementById('direct-slot').value = '';
        document.getElementById('direct-slot-display-container').classList.add('hidden');
        await fetchScheduleForDate(newDate, false);
    }

    async function fetchScheduleForDate(dateStr, updatePipeline = true) {
        const todayStr = new Date().toISOString().substring(0, 10);
        const pipeHead = document.getElementById('pipeline-heading-label');
        const slotHead = document.getElementById('slots-heading-label');
        if (pipeHead) {
            pipeHead.textContent = (dateStr === todayStr) ? "Today's Live Pipeline" : `Schedule (${dateStr})`;
        }
        if (slotHead) {
            slotHead.textContent = (dateStr === todayStr) ? "Available Slots Today" : `Available Slots (${dateStr})`;
        }

        if (updatePipeline) {
            // Loading state for right panel
            document.getElementById('right-panel-pipeline').innerHTML = `
                <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs bg-white rounded-b-xl border-dashed">
                    <i class="fa-solid fa-circle-notch fa-spin mb-2 block text-indigo-300 text-lg"></i>
                    Loading schedule for ${dateStr}...
                </td></tr>
            `;
            document.getElementById('right-panel-pipeline-count').textContent = '...';
        }

        document.getElementById('right-panel-slots').innerHTML = `
            <div class="col-span-full text-center py-4 text-xs text-slate-400">Loading slots...</div>
        `;
        document.getElementById('right-panel-slots').className = "bg-slate-50 border border-slate-200 rounded-xl p-3 shadow-inner";
        
        const submitBtn = document.querySelector('#form-book-direct button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Checking Availability...';
        submitBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        submitBtn.classList.add('bg-slate-400', 'cursor-not-allowed');

        try {
            const res = await fetch(`api/doctors.php?action=get_doctor_schedule&date=${dateStr}`);
            const data = await res.json();
            if (data.status === 'success') {
                const docData = data.doctors.find(d => d.id == currentDoctorId);
                
                if (!docData) {
                    throw new Error("Doctor schedule not found");
                }

                if (updatePipeline) {
                    // Render Live Pipeline with selected date's appointments
                    const docQueue = (data.appointments || []).filter(a => a.doctor_id == currentDoctorId);
                    document.getElementById('right-panel-pipeline-count').textContent = docQueue.length;
                    
                    if (docQueue.length === 0) {
                        document.getElementById('right-panel-pipeline').innerHTML = ` 
                            <tr><td colspan="5" class="text-center py-8 text-slate-500 text-xs bg-white rounded-b-xl border-dashed">
                                <i class="fa-regular fa-calendar-check text-slate-300 text-2xl mb-2 block"></i>
                                <span class="font-bold">No appointments for this date.</span><br>
                                Schedule is clear.
                            </td></tr>
                        `;
                    } else {
                        const html = docQueue.map((q, idx) => {
                            const isEmergency = q.type === 'Emergency Case';
                            const aId = q.appointment_id || q.id || 0;
                            const apptCode = q.appointment_code || ('APP-' + String(aId).padStart(4, '0'));
                            
                            let statusColor = "bg-amber-100 text-amber-700 border-amber-200";
                            if (q.status === 'Waiting for Reports') statusColor = "bg-amber-100 text-amber-800 border-amber-300";
                            else if (q.stage === 4) statusColor = "bg-purple-100 text-purple-700 border-purple-200";
                            else if (q.stage === 3) statusColor = "bg-blue-100 text-blue-700 border-blue-200";
                            else if (q.stage === 2) statusColor = "bg-emerald-100 text-emerald-700 border-emerald-200";
                            else if (q.stage === 1) statusColor = "bg-indigo-100 text-indigo-700 border-indigo-200";

                            const isPre = (q.stage === 0 || q.status === 'Pre-Booked');
                            const stageBadge = isPre 
                                ? `<span class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-0.5 rounded-lg border bg-indigo-50 text-indigo-700 border-indigo-200"><i class="fa-regular fa-calendar-check text-[9px]"></i> Pre-Booked</span>`
                                : `Stage ${q.stage}: <span class="inline-block text-[9px] font-bold px-1.5 py-0.5 rounded border ${statusColor}">${q.status}</span>`;

                            return `
                            <tr class="hover:bg-slate-50 transition border-b ${isEmergency ? 'bg-rose-50/70 border-l-4 border-l-rose-600 font-medium' : ''}">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-lg shadow-sm">Token #${q.token_no || idx + 1}</span>
                                        <span class="font-mono font-black text-[10px] px-2 py-0.5 rounded ${isEmergency ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200'}">${apptCode}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-700 text-[11px]">
                                    ${stageBadge}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 text-xs flex items-center gap-1 group">
                                        <span>${q.patient_name} ${q.patient_surname || ''}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">${q.patient_id}</div>
                                    <div class="text-[10px] text-rose-500 font-bold mt-0.5">📞 ${q.patient_phone || 'N/A'}</div>
                                </td>
                                <td class="py-3 px-4">
                                    ${isEmergency 
                                        ? `<span class="text-[9px] font-black px-2.5 py-1 rounded-full bg-rose-600 text-white animate-pulse flex items-center gap-1 shadow-sm w-fit"><i class="fa-solid fa-triangle-exclamation"></i> EMERGENCY</span>` 
                                        : `<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">Regular</span>`}
                                </td>
                                <td class="py-3 px-4 text-[11px] truncate max-w-[120px]" title="${q.symptoms || 'General'}">
                                    ${q.symptoms || 'General'}
                                </td>
                            </tr>
                            `;
                        }).join('');
                        document.getElementById('right-panel-pipeline').innerHTML = html;
                    }
                }

                // Render Slot Grid
                const slotsContainer = document.getElementById('right-panel-slots');
                if (docData.is_available === false) {
                    slotsContainer.className = "bg-amber-50 border border-amber-200 rounded-xl p-6 text-center";
                    slotsContainer.innerHTML = `
                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 mb-2">
                            <i class="fa-solid fa-calendar-xmark text-sm"></i>
                        </div>
                        <h5 class="font-bold text-amber-900 text-xs">Off Duty Today</h5>
                        <p class="text-[10px] text-amber-700/80 mt-1">No consultation time slots scheduled.</p>
                    `;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-ban mr-1.5"></i> Doctor Off Duty';
                    submitBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'bg-slate-400');
                    submitBtn.classList.add('bg-rose-500', 'cursor-not-allowed');
                } else {
                    slotsContainer.className = "grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto custom-scrollbar p-1";
                    
                    let slotsHtml = '';
                    if (!docData.slots || docData.slots.length === 0) {
                        slotsHtml = `<div class="col-span-full text-center py-4 text-xs text-slate-400">No time slots generated.</div>`;
                    } else {
                        docData.slots.forEach(s => {
                            if (s.is_booked) {
                                const patName = s.appointment ? `${s.appointment.patient_name}` : 'Booked';
                                slotsHtml += `
                                    <div class="p-2 rounded-xl bg-slate-100 border border-slate-200 text-center cursor-not-allowed opacity-70">
                                        <span class="block text-[10px] font-bold text-slate-400 line-through">${s.time}</span>
                                        <span class="block text-[8px] text-slate-500 font-semibold truncate mt-0.5" title="${patName}">${patName}</span>
                                    </div>
                                `;
                            } else {
                                slotsHtml += `
                                    <button type="button" onclick="selectSlotForBooking('${s.time}', this)" class="slot-btn p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition text-center shadow-sm hover:-translate-y-0.5 focus:ring-2 focus:ring-emerald-500" title="Click to select this slot">
                                        <span class="block text-[10px] font-black pointer-events-none">${s.time}</span>
                                        <span class="block text-[8px] font-bold text-emerald-600 mt-0.5 pointer-events-none">Available</span>
                                    </button>
                                `;
                            }
                        });
                    }
                    slotsContainer.innerHTML = slotsHtml;

                    const available = docData.available_count;
                    if (available > 0) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-hand-pointer mr-1.5"></i> Select a Slot First';
                        submitBtn.classList.remove('bg-slate-400', 'cursor-not-allowed');
                        submitBtn.classList.add('bg-indigo-300', 'cursor-not-allowed');
                    } else {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-ban mr-1.5"></i> Fully Booked';
                        submitBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'bg-slate-400', 'bg-indigo-300');
                        submitBtn.classList.add('bg-rose-500', 'cursor-not-allowed');
                    }
                }
            }
        } catch (e) { console.error(e); }
    }

    function selectSlotForBooking(time, btnEl) {
        document.getElementById('direct-slot').value = time;
        document.getElementById('direct-slot-display').textContent = time;
        document.getElementById('direct-slot-display-container').classList.remove('hidden');

        // Reset visual state of all slot buttons
        document.querySelectorAll('.slot-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-700', 'shadow-md');
            btn.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
            btn.querySelector('span:last-child').classList.remove('text-emerald-100');
            btn.querySelector('span:last-child').classList.add('text-emerald-600');
        });

        // Highlight selected button
        btnEl.classList.remove('bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
        btnEl.classList.add('bg-emerald-600', 'text-white', 'border-emerald-700', 'shadow-md');
        btnEl.querySelector('span:last-child').classList.remove('text-emerald-600');
        btnEl.querySelector('span:last-child').classList.add('text-emerald-100');

        // Enable submit button
        const submitBtn = document.querySelector('#form-book-direct button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Confirm & Review Booking';
        submitBtn.classList.remove('bg-indigo-300', 'cursor-not-allowed', 'bg-rose-500');
        submitBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    }

    // Custom Dropdown Logic
    function toggleTypeDropdown(e) {
        if(e) e.stopPropagation();
        document.getElementById('type-dropdown-menu').classList.toggle('hidden');
    }

    function selectTypeOption(value, iconClass, textClass, bgClass) {
        document.getElementById('direct-type').value = value;
        document.getElementById('direct-type-display').textContent = value;
        
        const iconEl = document.getElementById('direct-type-icon');
        iconEl.className = `fa-solid ${iconClass} text-[10px]`;
        
        const iconContainer = iconEl.parentElement;
        iconContainer.className = `absolute left-3.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-md flex items-center justify-center pointer-events-none transition ${bgClass} ${textClass}`;

        document.getElementById('type-dropdown-menu').classList.add('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const container = document.getElementById('custom-type-dropdown-container');
        const menu = document.getElementById('type-dropdown-menu');
        if (container && !container.contains(e.target)) {
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        }
    });

    function closeDirectBookModal() {
        const patientSelected = document.getElementById('direct-patient-id').value;
        const symptomsEntered = document.getElementById('direct-symptoms').value.trim();
        const slotSelected = document.getElementById('direct-slot').value;

        if (patientSelected || symptomsEntered || slotSelected) {
            openConfirmModal(
                'Discard Changes?',
                'You have entered booking details. Are you sure you want to close? Your changes will be lost.',
                '',
                () => { document.getElementById('modal-book-direct').classList.add('hidden'); },
                'danger'
            );
        } else {
            document.getElementById('modal-book-direct').classList.add('hidden');
        }
    }

    function handleDirectBook(e) {
        e.preventDefault();
        const patientId = document.getElementById('direct-patient-id').value;
        if (!patientId || !selectedDirectPatient) {
            showToast('Patient Required', 'Please search and select a patient.', 'error');
            return;
        }
        if (!currentDoctorId) {
            showToast('Doctor Required', 'Please select a doctor.', 'error');
            return;
        }
        
        const patientText = `${selectedDirectPatient.name} ${selectedDirectPatient.surname} (${selectedDirectPatient.id})`;
        
        tempBookPayload = {
            patient_id: patientId,
            doctor_id: currentDoctorId,
            type: document.getElementById('direct-type').value,
            symptoms: document.getElementById('direct-symptoms').value.trim(),
            slot: document.getElementById('direct-slot').value,
            date: document.getElementById('direct-date').value
        };

        const html = `
            <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100 mb-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-wider mb-0.5">Selected Patient</p>
                        <h4 class="font-extrabold text-sm text-slate-900">${selectedDirectPatient.name} ${selectedDirectPatient.surname}</h4>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">${selectedDirectPatient.id}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 rounded bg-white border border-slate-200 text-rose-600 shadow-sm">🩸 ${selectedDirectPatient.blood_group || 'Unknown'}</span>
                </div>
                <div class="text-[11px] text-slate-600 bg-white p-2 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-phone mr-1.5 text-slate-400"></i> ${selectedDirectPatient.phone || 'No phone'}
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-3">
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-0.5">Appointment Details</p>
                    <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5"><i class="fa-solid fa-user-doctor text-indigo-500"></i> ${currentDoctorName}</h4>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] text-slate-500 font-bold mb-1">Date & Time</span>
                        <span class="block text-xs font-black text-indigo-700">${tempBookPayload.date}</span>
                        <span class="block text-xs font-extrabold text-emerald-600 mt-0.5">${tempBookPayload.slot}</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                        <span class="block text-[10px] text-slate-500 font-bold mb-1">Consultation Type</span>
                        <span class="block text-xs font-bold text-slate-800">${tempBookPayload.type}</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm mt-3">
                    <span class="block text-[10px] text-slate-500 font-bold mb-1">Symptoms / Reason</span>
                    <p class="text-xs font-medium text-slate-700 italic">${tempBookPayload.symptoms}</p>
                </div>
            </div>
        `;

        document.getElementById('modal-book-direct').classList.add('hidden');
        
        openConfirmModal(
            'Confirm Appointment',
            'Please verify the booking details below before proceeding.',
            html,
            executeDirectBook,
            'success',
            () => { document.getElementById('modal-book-direct').classList.remove('hidden'); }
        );
    }

    async function executeDirectBook() {
        try {
            const res = await fetch('api/queue.php?action=book_existing', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(tempBookPayload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                const msg = data.is_prebooked ? 'Advance appointment pre-booked successfully!' : 'Patient added to the live pipeline successfully!';
                showToast('Booked Successfully', msg);
                if (window.location.pathname.endsWith('queue.php')) {
                    document.getElementById('modal-book-direct').classList.add('hidden');
                    if (typeof fetchQueuePipeline === 'function') fetchQueuePipeline();
                    if (typeof fetchAdvanceAppointments === 'function') fetchAdvanceAppointments();
                } else {
                    setTimeout(() => window.location.href = 'queue.php', 1200);
                }
            } else {
                showToast('Error', data.message || 'Booking failed', 'error');
            }
        } catch (err) { console.error(err); }
    }
</script>

<?php include 'includes/footer.php'; ?>




<!-- Quick Register Modal -->
<div id="modal-quick-register" class="hidden fixed inset-0 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity" style="z-index: 9999;">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl relative border border-slate-200">
        <button onclick="closeQuickRegisterModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition"><i class="fa-solid fa-xmark"></i></button>
        <div class="flex items-center gap-3 mb-5 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shadow-inner font-bold">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 leading-tight">Quick Register</h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">New Patient</p>
            </div>
        </div>
        <form id="form-quick-register" onsubmit="handleQuickRegister(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">First Name *</label>
                <input type="text" id="qr-fname" required oninput="resetQRWarning()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Last Name *</label>
                <input type="text" id="qr-lname" required oninput="resetQRWarning()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Father's Name (Optional)</label>
                <input type="text" id="qr-father" oninput="resetQRWarning()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Phone Number *</label>
                <input type="tel" id="qr-phone" required oninput="resetQRWarning()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">
            </div>
            
            <div id="qr-duplicate-warning" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs mt-3">
                <p class="font-bold text-amber-800 mb-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Existing patient(s) found. Did you mean one of these?</p>
                <div id="qr-duplicate-list" class="space-y-2 mb-2 max-h-32 overflow-y-auto custom-scrollbar"></div>
                <div class="border-t border-amber-200 pt-2 flex items-center justify-between">
                    <span class="text-amber-700 font-semibold">Or create a new record:</span>
                    <button type="button" onclick="forceQuickRegister()" class="bg-amber-100 hover:bg-amber-200 text-amber-800 px-3 py-1.5 rounded-lg font-bold transition">Create Anyway</button>
                </div>
            </div>

            <button type="submit" id="qr-submit-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-indigo-600/20 transition text-sm flex items-center justify-center gap-2 mt-2">
                <i class="fa-solid fa-check"></i> Register & Select
            </button>
        </form>
    </div>
</div>
<script>
    let qrForceNew = false;

    function openQuickRegisterModal() {
        document.getElementById('form-quick-register').reset();
        resetQRWarning();
        document.getElementById('modal-quick-register').classList.remove('hidden');
        document.getElementById('qr-fname').focus();
    }
    
    function closeQuickRegisterModal() {
        document.getElementById('modal-quick-register').classList.add('hidden');
    }
    
    function resetQRWarning() {
        qrForceNew = false;
        document.getElementById('qr-duplicate-warning').classList.add('hidden');
        document.getElementById('qr-submit-btn').classList.remove('hidden');
    }
    
    function selectExistingFromQR(id) {
        if (!allPatientsList) return;
        const p = allPatientsList.find(x => x.id === id);
        if (p) {
            closeQuickRegisterModal();
            selectDirectPatient(p);
        }
    }
    
    function forceQuickRegister() {
        qrForceNew = true;
        doQuickRegister();
    }

    async function handleQuickRegister(e) {
        e.preventDefault();
        
        const fname = document.getElementById('qr-fname').value.trim();
        const lname = document.getElementById('qr-lname').value.trim();
        const phone = document.getElementById('qr-phone').value.trim();
        const father = (document.getElementById('qr-father') ? document.getElementById('qr-father').value.trim() : '');
        
        if (!qrForceNew && allPatientsList && allPatientsList.length > 0) {
            const matches = allPatientsList.filter(p => {
                const nameMatch = (p.name.toLowerCase() === fname.toLowerCase() && p.surname.toLowerCase() === lname.toLowerCase());
                const phoneMatch = (phone && p.phone && p.phone.replace(/[^0-9]/g, '') === phone.replace(/[^0-9]/g, ''));
                const fatherMatch = (father && p.father_name && p.father_name.toLowerCase() === father.toLowerCase());
                return (nameMatch && (father === '' || fatherMatch)) || phoneMatch;
            });
            
            if (matches.length > 0) {
                const listEl = document.getElementById('qr-duplicate-list');
                listEl.innerHTML = matches.map(p => `
                    <div class="bg-white p-2 rounded border border-amber-200 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800">${p.name} ${p.surname}</div>
                            <div class="text-[10px] text-slate-500 font-mono">${p.id} � ${p.phone || "No phone"}</div>
                        </div>
                        <button type="button" onclick="selectExistingFromQR('${p.id}')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-[10px] font-bold border border-indigo-200 transition">Select</button>
                    </div>
                `).join("");
                
                document.getElementById('qr-duplicate-warning').classList.remove('hidden');
                document.getElementById('qr-submit-btn').classList.add('hidden');
                return; // Stop here, wait for user to click Select or Create Anyway
            }
        }
        
        doQuickRegister();
    }
    
    async function doQuickRegister() {
        const fname = document.getElementById('qr-fname').value.trim();
        const lname = document.getElementById('qr-lname').value.trim();
        const phone = document.getElementById('qr-phone').value.trim();
        const father = (document.getElementById('qr-father') ? document.getElementById('qr-father').value.trim() : '');
        
        try {
            const res = await fetch('api/patients.php?action=create', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ name: fname, surname: lname, phone: phone, father: father })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeQuickRegisterModal();
                showToast('Success', 'Patient registered successfully!', 'success');
                const p = {
                    id: data.mrn,
                    name: fname,
                    surname: lname,
                    phone: phone,
                    blood_group: '',
                    father_name: father
                };
                if (!allPatientsList) allPatientsList = [];
                allPatientsList.unshift(p);
                selectDirectPatient(p);
            } else {
                showToast('Error', data.message || 'Registration failed', 'error');
            }
        } catch (err) {
            showToast('Error', 'Network error', 'error');
        }
    }
</script>

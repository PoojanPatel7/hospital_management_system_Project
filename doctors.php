<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 uppercase tracking-wider">Medical Staff</span>
            <span class="text-xs font-semibold text-slate-400">• Hospital Roster</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Doctors Directory</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Manage hospital doctors, assign departments, and configure availability slots.</p>
    </div>
    
    <!-- Action Buttons -->
    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
        <a href="doctor_slots.php" class="flex-1 sm:flex-none justify-center bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold py-2.5 px-3.5 rounded-xl border border-purple-200 transition flex items-center gap-2 text-xs shrink-0 shadow-2xs">
            <i class="fa-regular fa-clock text-sm"></i> <span>Timings & Slots</span>
        </a>
        <a href="appointments.php" class="flex-1 sm:flex-none justify-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2.5 px-3.5 rounded-xl border border-indigo-200 transition flex items-center gap-2 text-xs shrink-0 shadow-2xs">
            <i class="fa-solid fa-calendar-check text-sm"></i> <span>Appointments</span>
        </a>
        <button onclick="openAddDoctor()" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-blue-600/20 transition flex items-center gap-2 text-xs shrink-0">
            <i class="fa-solid fa-user-plus text-sm"></i> <span>+ Add Doctor</span>
        </button>
    </div>
</div>

<!-- ================= SUMMARY METRICS ================= -->
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-2xs">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
            <i class="fa-solid fa-user-doctor"></i>
        </div>
        <div class="min-w-0">
            <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block truncate">Total Doctors</span>
            <span id="metric-total-docs" class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">0</span>
        </div>
    </div>
    
    <div class="apple-card p-4 sm:p-5 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-2xs">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
            <i class="fa-solid fa-stethoscope"></i>
        </div>
        <div class="min-w-0">
            <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block truncate">Specialties</span>
            <span id="metric-total-cats" class="text-xl sm:text-2xl font-black text-indigo-700 leading-tight">0</span>
        </div>
    </div>
    
    <div class="apple-card p-4 sm:p-5 col-span-2 sm:col-span-1 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-2xs">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="min-w-0">
            <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block truncate">Staff Status</span>
            <span class="text-xs sm:text-sm font-extrabold text-emerald-700 leading-tight block">Active & Verified</span>
        </div>
    </div>
</div>

<!-- ================= FILTER & SEARCH BAR ================= -->
<div class="apple-card p-4 sm:p-5 mb-6 bg-white border border-slate-200/90 shadow-sm space-y-3.5">
    
    <!-- Controls Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Bar -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input 
                type="text" 
                id="doc-search-input" 
                oninput="handleDoctorFilter()" 
                placeholder="Search doctor by name or phone..." 
                class="w-full border border-slate-300 rounded-xl pl-9 pr-9 py-2.5 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-2xs">
            <button type="button" id="doc-search-clear" onclick="clearDocSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-200 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Specialty Filter Dropdown & View Mode Switcher -->
        <div class="flex items-center gap-2 flex-wrap">
            <select id="doc-category-filter" onchange="handleDropdownFilterChange()" class="flex-1 sm:flex-none border border-slate-300 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/50 outline-none shadow-2xs">
                <option value="all">All Specialties / Departments</option>
                <!-- Loaded dynamically -->
            </select>

            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold shrink-0">
                <button type="button" id="btn-view-grid" onclick="setDoctorViewMode('grid')" title="Cards Grid View" class="px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-grip"></i> <span class="hidden sm:inline text-xs">Grid</span>
                </button>
                <button type="button" id="btn-view-table" onclick="setDoctorViewMode('table')" title="List Table View" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-list"></i> <span class="hidden sm:inline text-xs">Table</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Category Pills (Horizontally Scrollable) -->
    <div class="pt-2 border-t border-slate-100 flex items-center gap-2 overflow-x-auto no-scrollbar py-1" id="doc-filter-pills">
        <!-- Loaded dynamically -->
    </div>
</div>

<!-- ================= DOCTORS CONTENT AREA ================= -->
<div>
    <!-- Results Header -->
    <div class="flex items-center justify-between mb-3 px-1">
        <div class="flex items-center gap-2 flex-wrap">
            <span id="doctors-count-label" class="text-xs font-black text-slate-700 bg-slate-100 px-3 py-1 rounded-full">
                0 Doctors Registered
            </span>
            <span id="active-filter-badge" class="hidden text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                <span id="active-filter-text"></span>
                <button type="button" onclick="selectDepartmentFilter('all')" class="hover:text-blue-900 ml-1">✕</button>
            </span>
        </div>
        <span class="text-xs text-slate-400 hidden sm:inline">Tap card buttons to manage timings or view appointments</span>
    </div>

    <!-- Grid Cards View (Default) -->
    <div id="doctors-grid-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- Loaded via JS -->
    </div>

    <!-- Table View (Alternate) -->
    <div id="doctors-table-container" class="hidden apple-card overflow-hidden border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-sm min-w-[700px]">
                <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 font-extrabold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Doctor Name</th>
                        <th class="py-4 px-6">Phone Number</th>
                        <th class="py-4 px-6">Assigned Specialties</th>
                        <th class="py-4 px-6">Doctor ID</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="doctors-tbody" class="divide-y divide-slate-100">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div id="doctors-empty-state" class="hidden apple-card p-10 sm:p-14 text-center bg-white border border-slate-200">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
            <i class="fa-solid fa-user-doctor"></i>
        </div>
        <h4 class="font-black text-slate-900 text-base sm:text-lg">No Doctors Found</h4>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">No medical doctors match your current search keywords or selected department filter.</p>
        <button type="button" onclick="clearDocSearch()" class="mt-4 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            <i class="fa-solid fa-rotate-left mr-1.5"></i> Clear Search & Filters
        </button>
    </div>
</div>

<!-- ================= ADD / EDIT DOCTOR MODAL ================= -->
<div id="modal-doctor" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="apple-card max-w-lg w-full max-h-[92vh] flex flex-col p-0 relative my-auto bg-white shadow-2xl border border-slate-200 rounded-2xl overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold shadow-2xs shrink-0">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <div>
                    <h3 id="doc-modal-title" class="text-base sm:text-lg font-black text-slate-900 leading-tight">Add New Doctor</h3>
                    <p class="text-xs text-slate-500 font-medium">Assign departments and contact details.</p>
                </div>
            </div>
            <button onclick="closeDoctorModal()" type="button" class="text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Body & Form -->
        <form id="form-doctor" onsubmit="handleDoctorSubmit(event)" class="flex flex-col flex-1 overflow-hidden">
            <div class="p-4 sm:p-6 overflow-y-auto space-y-4 flex-1">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 ml-0.5">Doctor Full Name *</label>
                    <input type="text" id="doc-name" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition" placeholder="e.g. Dr. Jane Smith, MD">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 ml-0.5">Phone Number *</label>
                    <input type="tel" id="doc-phone" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition" placeholder="e.g. +1 234 567 8900">
                </div>

                <!-- Hidden Native Multi-Select (for backward compatibility & form submissions) -->
                <select id="doc-categories" multiple class="hidden">
                    <!-- Synced dynamically -->
                </select>

                <!-- Interactive Specialty Chips Picker -->
                <div>
                    <div class="flex items-center justify-between mb-2 ml-0.5">
                        <label class="block text-xs font-bold text-slate-700">
                            Select Specialties / Departments *
                        </label>
                        <div class="flex items-center gap-2">
                            <span id="modal-dept-selected-count" class="text-[11px] font-extrabold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                                0 selected
                            </span>
                            <button type="button" onclick="selectAllModalDepts(false)" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 underline">
                                Clear
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-[11px] text-slate-400 mb-2 ml-0.5">Tap on the departments this doctor specializes in:</p>

                    <div id="modal-dept-chips" class="flex flex-wrap gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl max-h-48 overflow-y-auto">
                        <!-- Populated by JS -->
                    </div>
                    <p id="modal-dept-error" class="hidden text-xs text-rose-600 font-bold mt-1.5 ml-0.5">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Please select at least one department.
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-slate-100 flex items-center justify-end gap-2 bg-slate-50/80 shrink-0">
                <button type="button" onclick="closeDoctorModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> <span>Save Doctor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let allDoctorsList = [];
    let allCategoriesList = [];
    let currentDoctorView = 'grid';
    let editDoctorId = null;
    let tempDoctorPayload = null;
    let deleteDoctorId = null;

    document.addEventListener('DOMContentLoaded', () => {
        fetchCategories();
        fetchDoctors();
    });

    function setDoctorViewMode(mode) {
        currentDoctorView = mode;
        const btnGrid = document.getElementById('btn-view-grid');
        const btnTable = document.getElementById('btn-view-table');
        const gridEl = document.getElementById('doctors-grid-container');
        const tableEl = document.getElementById('doctors-table-container');

        if (mode === 'grid') {
            btnGrid.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5";
            btnTable.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5";
            gridEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
        } else {
            btnTable.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5";
            btnGrid.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5";
            gridEl.classList.add('hidden');
            tableEl.classList.remove('hidden');
        }
    }

    async function fetchCategories() {
        try {
            const res = await fetch('api/doctors.php?action=get_categories');
            const data = await res.json();
            if (data.status === 'success') {
                allCategoriesList = data.categories || [];
                
                // Populate native select inside modal
                const modalSelect = document.getElementById('doc-categories');
                modalSelect.innerHTML = '';
                
                // Populate filter select
                const filterSelect = document.getElementById('doc-category-filter');
                filterSelect.innerHTML = '<option value="all">All Specialties / Departments</option>';

                // Populate interactive modal chips
                const modalChips = document.getElementById('modal-dept-chips');
                modalChips.innerHTML = '';

                allCategoriesList.forEach(c => {
                    // Native hidden select option
                    modalSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    
                    // Filter dropdown option
                    filterSelect.innerHTML += `<option value="${c.name}">${c.name}</option>`;

                    // Modal interactive chip
                    const chipBtn = document.createElement('button');
                    chipBtn.type = 'button';
                    chipBtn.setAttribute('data-dept-id', c.id);
                    chipBtn.className = "dept-chip px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-1.5 transition";
                    chipBtn.onclick = () => toggleModalDeptChip(c.id);
                    chipBtn.innerHTML = `
                        <span class="check-icon hidden text-blue-600 font-bold"><i class="fa-solid fa-check text-xs"></i></span>
                        <span>${c.name}</span>
                    `;
                    modalChips.appendChild(chipBtn);
                });

                renderCategoryPills();
            }
        } catch (e) {
            console.error('Error fetching categories:', e);
        }
    }

    async function fetchDoctors() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctorsList = data.doctors || [];
                document.getElementById('metric-total-docs').textContent = allDoctorsList.length;
                document.getElementById('metric-total-cats').textContent = allCategoriesList.length;
                renderCategoryPills();
                handleDoctorFilter();
            }
        } catch (e) {
            console.error('Error fetching doctors:', e);
        }
    }

    function renderCategoryPills() {
        const pillsContainer = document.getElementById('doc-filter-pills');
        if (!pillsContainer) return;

        const currentCat = document.getElementById('doc-category-filter') ? document.getElementById('doc-category-filter').value : 'all';

        let html = `
            <button type="button" onclick="selectDepartmentFilter('all')" data-cat="all" class="doc-cat-pill shrink-0 px-3 py-1.5 rounded-xl text-xs font-extrabold transition ${currentCat === 'all' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'}">
                All (${allDoctorsList.length})
            </button>
        `;

        allCategoriesList.forEach(c => {
            const count = allDoctorsList.filter(d => (d.categories || []).includes(c.name)).length;
            const isSelected = currentCat === c.name;
            html += `
                <button type="button" onclick="selectDepartmentFilter('${escapeJs(c.name)}')" data-cat="${escapeJs(c.name)}" class="doc-cat-pill shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition ${isSelected ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'}">
                    ${c.name} <span class="text-[10px] opacity-75 font-mono ml-0.5">(${count})</span>
                </button>
            `;
        });

        pillsContainer.innerHTML = html;
    }

    function handleDropdownFilterChange() {
        const val = document.getElementById('doc-category-filter').value;
        selectDepartmentFilter(val);
    }

    function selectDepartmentFilter(catName) {
        const sel = document.getElementById('doc-category-filter');
        if (sel) sel.value = catName;

        // Update pills active styling
        document.querySelectorAll('.doc-cat-pill').forEach(pill => {
            const cat = pill.getAttribute('data-cat');
            if (cat === catName) {
                pill.className = "doc-cat-pill shrink-0 px-3 py-1.5 rounded-xl text-xs font-extrabold transition bg-blue-600 text-white shadow-2xs";
            } else {
                pill.className = "doc-cat-pill shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700";
            }
        });

        handleDoctorFilter();
    }

    function handleDoctorFilter() {
        const query = document.getElementById('doc-search-input').value.trim().toLowerCase();
        const selectedCat = document.getElementById('doc-category-filter').value;
        const clearBtn = document.getElementById('doc-search-clear');
        if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);

        const filtered = allDoctorsList.filter(d => {
            const nameMatch = (d.name || '').toLowerCase().includes(query);
            const phoneMatch = (d.phone || '').toLowerCase().includes(query);
            const catMatch = (selectedCat === 'all') || (d.categories || []).includes(selectedCat);
            return (nameMatch || phoneMatch) && catMatch;
        });

        document.getElementById('doctors-count-label').textContent = `${filtered.length} Doctor${filtered.length === 1 ? '' : 's'} Displayed`;
        
        // Active filter badge
        const badgeEl = document.getElementById('active-filter-badge');
        const badgeText = document.getElementById('active-filter-text');
        if (selectedCat !== 'all') {
            badgeText.textContent = selectedCat;
            badgeEl.classList.remove('hidden');
        } else {
            badgeEl.classList.add('hidden');
        }

        const emptyEl = document.getElementById('doctors-empty-state');
        const gridEl = document.getElementById('doctors-grid-container');
        const tableEl = document.getElementById('doctors-table-container');

        if (filtered.length === 0) {
            emptyEl.classList.remove('hidden');
            gridEl.classList.add('hidden');
            tableEl.classList.add('hidden');
            return;
        }

        emptyEl.classList.add('hidden');
        renderDoctorCards(filtered);
        renderDoctorTable(filtered);

        if (currentDoctorView === 'grid') {
            gridEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
        } else {
            tableEl.classList.remove('hidden');
            gridEl.classList.add('hidden');
        }
    }

    function clearDocSearch() {
        document.getElementById('doc-search-input').value = '';
        selectDepartmentFilter('all');
    }

    function renderDoctorCards(list) {
        const container = document.getElementById('doctors-grid-container');
        container.innerHTML = '';

        list.forEach(d => {
            const card = document.createElement('div');
            card.className = "apple-card p-4 sm:p-5 flex flex-col justify-between border border-slate-200/90 bg-white hover:border-blue-400 hover:shadow-md transition rounded-2xl";

            const specialtiesHtml = (d.categories || []).map(c => 
                `<span class="inline-block bg-amber-50 text-amber-900 text-[11px] font-bold px-2.5 py-0.5 rounded-lg border border-amber-200/70 mr-1 mb-1 shadow-2xs">${c}</span>`
            ).join('') || '<span class="text-xs text-slate-400 italic">General Medicine</span>';

            const initials = (d.name || 'Dr').replace(/[^a-zA-Z]/g, '').substring(0, 2).toUpperCase() || 'DR';

            card.innerHTML = `
                <div>
                    <!-- Top Doctor Info -->
                    <div class="flex items-start justify-between gap-3 pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-800 border border-amber-200/60 flex items-center justify-center text-sm font-black shadow-2xs shrink-0">
                                ${initials}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-sm sm:text-base leading-snug truncate" title="${escapeJs(d.name)}">${d.name}</h4>
                                <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md mt-0.5 inline-block truncate">${d.id}</span>
                            </div>
                        </div>

                        <!-- Edit & Delete Action Buttons -->
                        <div class="flex items-center gap-1 shrink-0">
                            <button onclick="openEditDoctor('${d.id}')" class="w-8 h-8 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition flex items-center justify-center shadow-2xs border border-transparent hover:border-blue-200" title="Edit Doctor Profile">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <button onclick="promptDeleteDoctor('${d.id}', '${escapeJs(d.name)}')" class="w-8 h-8 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition flex items-center justify-center shadow-2xs border border-transparent hover:border-rose-200" title="Delete Doctor">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Phone Contact -->
                    <div class="mb-3">
                        <a href="tel:${d.phone || ''}" class="inline-flex items-center gap-2 text-xs text-slate-600 hover:text-blue-600 font-semibold transition" title="Tap to call">
                            <i class="fa-solid fa-phone text-slate-400 text-[11px]"></i>
                            <span class="font-mono">${d.phone || 'No phone provided'}</span>
                        </a>
                    </div>

                    <!-- Specialties Tags -->
                    <div class="mb-4">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1.5">Departments</span>
                        <div>${specialtiesHtml}</div>
                    </div>
                </div>

                <!-- Action Buttons Footer -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <a href="doctor_slots.php?doctor_id=${encodeURIComponent(d.id)}" class="py-2.5 px-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition flex items-center justify-center gap-1.5 border border-purple-200 shadow-2xs text-center" title="Manage Availability & Slot Timings">
                        <i class="fa-regular fa-clock text-xs"></i> <span>Timings</span>
                    </a>
                    <a href="appointments.php?doctor_id=${encodeURIComponent(d.id)}" class="py-2.5 px-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition flex items-center justify-center gap-1.5 border border-indigo-200 shadow-2xs text-center" title="View Patient Appointments">
                        <i class="fa-solid fa-calendar-check text-xs"></i> <span>Appts</span>
                    </a>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function renderDoctorTable(list) {
        const tbody = document.getElementById('doctors-tbody');
        tbody.innerHTML = '';

        list.forEach(d => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50/80 transition group";

            const specialtiesHtml = (d.categories || []).map(c => 
                `<span class="inline-block bg-amber-50 text-amber-900 text-[10px] font-bold px-2 py-0.5 rounded-md border border-amber-200 mr-1 mb-1">${c}</span>`
            ).join('') || '<span class="text-xs text-slate-400 italic">General</span>';

            tr.innerHTML = `
                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-black shadow-2xs shrink-0">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    ${d.name}
                </td>
                <td class="py-4 px-6 text-slate-600 font-mono text-xs">
                    <a href="tel:${d.phone || ''}" class="hover:text-blue-600 transition">${d.phone || 'N/A'}</a>
                </td>
                <td class="py-4 px-6 max-w-xs">${specialtiesHtml}</td>
                <td class="py-4 px-6 font-mono text-xs text-slate-400">${d.id}</td>
                <td class="py-4 px-6 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="doctor_slots.php?doctor_id=${encodeURIComponent(d.id)}" class="px-2.5 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs border border-purple-200 transition" title="Manage Slots">
                            <i class="fa-regular fa-clock mr-1"></i> Slots
                        </a>
                        <a href="appointments.php?doctor_id=${encodeURIComponent(d.id)}" class="px-2.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs border border-indigo-200 transition" title="Appointments">
                            <i class="fa-solid fa-calendar-check mr-1"></i> Appts
                        </a>
                        <button onclick="openEditDoctor('${d.id}')" class="p-2 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button onclick="promptDeleteDoctor('${d.id}', '${escapeJs(d.name)}')" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Modal Department Chips Synchronizer
    function toggleModalDeptChip(deptId) {
        const sel = document.getElementById('doc-categories');
        const opt = Array.from(sel.options).find(o => o.value == deptId);
        if (opt) {
            opt.selected = !opt.selected;
            syncModalChipsFromSelect();
        }
    }

    function selectAllModalDepts(selectAll) {
        const sel = document.getElementById('doc-categories');
        Array.from(sel.options).forEach(o => o.selected = selectAll);
        syncModalChipsFromSelect();
    }

    function syncModalChipsFromSelect() {
        const sel = document.getElementById('doc-categories');
        const selectedValues = new Set(Array.from(sel.selectedOptions).map(o => o.value));
        const chipsContainer = document.getElementById('modal-dept-chips');
        
        chipsContainer.querySelectorAll('[data-dept-id]').forEach(chip => {
            const id = chip.getAttribute('data-dept-id');
            const isSelected = selectedValues.has(id);
            const icon = chip.querySelector('.check-icon');
            
            if (isSelected) {
                chip.className = "dept-chip active px-3 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white border border-blue-600 shadow-2xs flex items-center gap-1.5 transition";
                if (icon) {
                    icon.classList.remove('hidden');
                    icon.className = "check-icon text-white font-bold";
                }
            } else {
                chip.className = "dept-chip px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-1.5 transition";
                if (icon) icon.classList.add('hidden');
            }
        });

        const countEl = document.getElementById('modal-dept-selected-count');
        if (countEl) {
            countEl.textContent = `${selectedValues.size} selected`;
        }

        const errorEl = document.getElementById('modal-dept-error');
        if (errorEl && selectedValues.size > 0) {
            errorEl.classList.add('hidden');
        }
    }

    function openAddDoctor() {
        editDoctorId = null;
        document.getElementById('form-doctor').reset();
        document.getElementById('doc-modal-title').textContent = 'Add New Doctor';
        
        const sel = document.getElementById('doc-categories');
        Array.from(sel.options).forEach(opt => opt.selected = false);
        syncModalChipsFromSelect();

        const errorEl = document.getElementById('modal-dept-error');
        if (errorEl) errorEl.classList.add('hidden');

        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    function openEditDoctor(id) {
        const d = allDoctorsList.find(x => x.id == id);
        if (!d) return;

        editDoctorId = id;
        document.getElementById('doc-modal-title').textContent = 'Edit Doctor Details';
        document.getElementById('doc-name').value = d.name;
        document.getElementById('doc-phone').value = d.phone;

        const sel = document.getElementById('doc-categories');
        Array.from(sel.options).forEach(opt => {
            opt.selected = (d.categories || []).includes(opt.text);
        });
        syncModalChipsFromSelect();

        const errorEl = document.getElementById('modal-dept-error');
        if (errorEl) errorEl.classList.add('hidden');

        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    function closeDoctorModal() {
        document.getElementById('modal-doctor').classList.add('hidden');
    }

    function handleDoctorSubmit(e) {
        e.preventDefault();
        const selOpts = Array.from(document.getElementById('doc-categories').selectedOptions);

        if (selOpts.length === 0) {
            const errorEl = document.getElementById('modal-dept-error');
            if (errorEl) errorEl.classList.remove('hidden');
            showToast('Department Required', 'Please select at least one specialty department', 'warning');
            return;
        }

        tempDoctorPayload = {
            id: editDoctorId,
            name: document.getElementById('doc-name').value.trim(),
            phone: document.getElementById('doc-phone').value.trim(),
            categories: selOpts.map(o => o.value),
            categoryNames: selOpts.map(o => o.text)
        };

        const html = `
            <div class="space-y-2 text-xs sm:text-sm">
                <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Name:</span> <span class="col-span-2 font-black text-slate-900">${tempDoctorPayload.name}</span></div>
                <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Phone:</span> <span class="col-span-2 font-mono font-semibold text-slate-900">${tempDoctorPayload.phone}</span></div>
                <div class="grid grid-cols-3 gap-2"><span class="font-bold text-slate-500">Specialties:</span> <span class="col-span-2 font-semibold text-slate-900">${tempDoctorPayload.categoryNames.join(', ')}</span></div>
            </div>
        `;

        closeDoctorModal();

        const title = editDoctorId ? 'Confirm Doctor Edits' : 'Confirm New Doctor';

        openConfirmModal(
            title,
            'Please verify the details below before saving.',
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
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(tempDoctorPayload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('form-doctor').reset();
                showToast('Success', editDoctorId ? 'Doctor updated successfully' : 'Doctor registered successfully');
                fetchDoctors();
            } else {
                showToast('Error', data.message || 'Could not save doctor', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'Connection failed', 'error');
        }
    }

    function promptDeleteDoctor(id, name) {
        deleteDoctorId = id;
        const html = `
            <div class="text-xs sm:text-sm space-y-2">
                <div class="text-rose-600 font-extrabold flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> Warning: This action cannot be undone.</div>
                <div class="font-bold text-slate-900">You are about to remove Doctor: <span class="text-blue-600">${name}</span></div>
                <p class="text-xs text-slate-500">Their profile will be removed from the active hospital registry and their scheduled slots will be cleared.</p>
            </div>
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
            } else {
                showToast('Error', data.message || 'Could not delete doctor', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Connection failed', 'error');
        }
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'");
    }
</script>

<?php include 'includes/footer.php'; ?>

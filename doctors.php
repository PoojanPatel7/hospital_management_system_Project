<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 uppercase tracking-wider">Medical Staff</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Doctors Directory</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Manage registered doctors, assign specialty departments, and configure doctor profiles.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="doctor_slots.php" class="bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold py-2.5 px-3.5 rounded-xl border border-purple-200 transition flex items-center gap-1.5 text-xs shrink-0 shadow-2xs">
            <i class="fa-regular fa-clock"></i> <span>Manage Timings & Slots</span>
        </a>
        <a href="appointments.php" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2.5 px-3.5 rounded-xl border border-indigo-200 transition flex items-center gap-1.5 text-xs shrink-0 shadow-2xs">
            <i class="fa-solid fa-calendar-check"></i> <span>View Appointments</span>
        </a>
        <button onclick="openAddDoctor()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-blue-600/20 transition flex items-center gap-2 text-xs shrink-0">
            <i class="fa-solid fa-user-plus"></i> <span>Add New Doctor</span>
        </button>
    </div>
</div>

<!-- ================= SUMMARY METRICS ================= -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="apple-card p-4 sm:p-5 flex items-center gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-user-doctor"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Doctors</span>
            <span id="metric-total-docs" class="text-2xl font-black text-slate-900">0</span>
        </div>
    </div>
    <div class="apple-card p-4 sm:p-5 flex items-center gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-stethoscope"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Specialties & Depts</span>
            <span id="metric-total-cats" class="text-2xl font-black text-indigo-700">0</span>
        </div>
    </div>
    <div class="apple-card p-4 sm:p-5 flex items-center gap-4 bg-white border border-slate-200/80">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Staff Registry</span>
            <span class="text-sm font-extrabold text-emerald-700">Active & Verified</span>
        </div>
    </div>
</div>

<!-- ================= FILTER & SEARCH BAR ================= -->
<div class="apple-card p-4 sm:p-5 mb-6 bg-white border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3.5">
    
    <!-- Search Bar -->
    <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
            type="text" 
            id="doc-search-input" 
            oninput="handleDoctorFilter()" 
            placeholder="Search doctor by name or phone..." 
            class="w-full border border-slate-300 rounded-xl pl-9 pr-8 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-2xs">
        <button type="button" id="doc-search-clear" onclick="clearDocSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Specialty Filter & View Switch -->
    <div class="flex items-center gap-2 flex-wrap">
        <select id="doc-category-filter" onchange="handleDoctorFilter()" class="border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/50 outline-none shadow-2xs">
            <option value="all">All Departments / Specialties</option>
            <!-- Loaded dynamically -->
        </select>

        <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold">
            <button type="button" id="btn-view-grid" onclick="setDoctorViewMode('grid')" title="Cards Grid View" class="px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition">
                <i class="fa-solid fa-grip"></i>
            </button>
            <button type="button" id="btn-view-table" onclick="setDoctorViewMode('table')" title="List Table View" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition">
                <i class="fa-solid fa-list"></i>
            </button>
        </div>
    </div>
</div>

<!-- ================= DOCTORS CONTENT AREA ================= -->
<div>
    <!-- Results Header -->
    <div class="flex items-center justify-between mb-3 px-1">
        <span id="doctors-count-label" class="text-xs font-black text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full">
            0 Doctors Registered
        </span>
        <span class="text-xs text-slate-400">Click a doctor card for quick actions</span>
    </div>

    <!-- Grid Cards View (Default) -->
    <div id="doctors-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
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
    <div id="doctors-empty-state" class="hidden apple-card p-12 text-center bg-white border border-slate-200">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
            <i class="fa-solid fa-user-doctor"></i>
        </div>
        <h4 class="font-black text-slate-900 text-base">No Doctors Found</h4>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">No medical doctors match your current search keywords or selected department filter.</p>
        <button type="button" onclick="clearDocSearch()" class="mt-4 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            Clear Search
        </button>
    </div>
</div>

<!-- ================= ADD / EDIT DOCTOR MODAL ================= -->
<div id="modal-doctor" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="apple-card max-w-lg w-full p-6 sm:p-7 relative my-8 bg-white shadow-2xl border border-slate-200">
        <button onclick="closeDoctorModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
        
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold shadow-2xs">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <h3 id="doc-modal-title" class="text-lg font-black text-slate-900">Add New Doctor</h3>
                <p class="text-xs text-slate-500 font-medium">Assign specialties and contact details.</p>
            </div>
        </div>

        <form id="form-doctor" onsubmit="handleDoctorSubmit(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Doctor Full Name *</label>
                <input type="text" id="doc-name" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition" placeholder="e.g. Dr. Jane Smith, MD">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Phone Number *</label>
                <input type="text" id="doc-phone" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/50 outline-none bg-slate-50 focus:bg-white transition" placeholder="e.g. +1 234 567 8900">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Select Specialty Departments * <span class="text-[11px] font-normal text-slate-400">(Hold Ctrl/Cmd to select multiple)</span></label>
                <select id="doc-categories" multiple required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-blue-500/50 outline-none h-32 bg-slate-50 focus:bg-white">
                    <!-- Loaded via JS -->
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeDoctorModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-md shadow-blue-600/30 transition">
                    Save Doctor
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
            btnGrid.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition";
            btnTable.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition";
            gridEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
        } else {
            btnTable.className = "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition";
            btnGrid.className = "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition";
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
                
                // Populate modal select
                const modalSelect = document.getElementById('doc-categories');
                modalSelect.innerHTML = '';
                
                // Populate filter select
                const filterSelect = document.getElementById('doc-category-filter');
                filterSelect.innerHTML = '<option value="all">All Departments / Specialties</option>';

                allCategoriesList.forEach(c => {
                    modalSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    filterSelect.innerHTML += `<option value="${c.name}">${c.name}</option>`;
                });
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
                handleDoctorFilter();
            }
        } catch (e) {
            console.error('Error fetching doctors:', e);
        }
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

        document.getElementById('doctors-count-label').textContent = `${filtered.length} Doctors Displayed`;
        
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
        document.getElementById('doc-category-filter').value = 'all';
        handleDoctorFilter();
    }

    function renderDoctorCards(list) {
        const container = document.getElementById('doctors-grid-container');
        container.innerHTML = '';

        list.forEach(d => {
            const card = document.createElement('div');
            card.className = "apple-card p-5 flex flex-col justify-between border border-slate-200/90 bg-white hover:border-amber-300 hover:shadow-md transition";

            const specialtiesHtml = (d.categories || []).map(c => 
                `<span class="inline-block bg-amber-50 text-amber-800 text-[10px] font-black px-2.5 py-0.5 rounded-md border border-amber-200/70 mr-1 mb-1 shadow-2xs">${c}</span>`
            ).join('') || '<span class="text-xs text-slate-400 italic">General Medicine</span>';

            const initials = (d.name || 'Dr').replace(/[^a-zA-Z]/g, '').substring(0, 2).toUpperCase() || 'DR';

            card.innerHTML = `
                <div>
                    <!-- Top Doctor Info -->
                    <div class="flex items-start justify-between gap-3 pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-black shadow-2xs shrink-0">
                                ${initials}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-base leading-tight truncate">${d.name}</h4>
                                <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-0.2 rounded border border-slate-200 mt-1 inline-block truncate">${d.id}</span>
                            </div>
                        </div>

                        <!-- Edit & Delete Menu -->
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
                    <div class="text-xs text-slate-600 font-semibold mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-phone text-slate-400 text-[11px]"></i>
                        <span class="font-mono">${d.phone || 'No phone provided'}</span>
                    </div>

                    <!-- Specialties -->
                    <div class="mb-4">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1.5">Specialties</span>
                        <div>${specialtiesHtml}</div>
                    </div>
                </div>

                <!-- Action Buttons Footer -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <a href="doctor_slots.php?doctor_id=${encodeURIComponent(d.id)}" class="py-2 px-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition flex items-center justify-center gap-1.5 border border-purple-200 shadow-2xs" title="Configure Slots & Timings">
                        <i class="fa-regular fa-clock text-[11px]"></i> <span>Timings & Slots</span>
                    </a>
                    <a href="appointments.php?doctor_id=${encodeURIComponent(d.id)}" class="py-2 px-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition flex items-center justify-center gap-1.5 border border-indigo-200 shadow-2xs" title="View Patient Appointments">
                        <i class="fa-solid fa-calendar-check text-[11px]"></i> <span>Appointments</span>
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
                `<span class="inline-block bg-amber-50 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-md border border-amber-200 mr-1 mb-1">${c}</span>`
            ).join('') || '<span class="text-xs text-slate-400 italic">General</span>';

            tr.innerHTML = `
                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black shadow-2xs shrink-0">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    ${d.name}
                </td>
                <td class="py-4 px-6 text-slate-600 font-mono text-xs">${d.phone || 'N/A'}</td>
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

    function openAddDoctor() {
        editDoctorId = null;
        document.getElementById('form-doctor').reset();
        document.getElementById('doc-modal-title').textContent = 'Add New Doctor';
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

        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    function closeDoctorModal() {
        document.getElementById('modal-doctor').classList.add('hidden');
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

        closeDoctorModal();

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
            <div class="text-rose-600 font-bold mb-2">Warning: This action cannot be undone.</div>
            <div class="font-semibold text-slate-900">You are about to delete Doctor: ${name}</div>
            <p class="text-sm mt-1">This will remove their profile from the active medical registry.</p>
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

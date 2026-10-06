<?php require_once 'auth.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="space-y-6">
  <!-- Hero Header -->
  <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm flex flex-col md:flex-row">
    <div class="w-full md:w-1/3 lg:w-1/4 bg-slate-50 flex items-center justify-center p-6 border-b md:border-b-0 md:border-r border-slate-100">
        <img src="images/Patient Directory.jpg" alt="Patient Directory" class="w-full max-w-[200px] h-auto object-contain drop-shadow-sm mix-blend-multiply">
    </div>
    
    <div class="p-6 sm:p-8 flex-1 flex flex-col justify-center">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
        <div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold mb-3 shadow-sm border border-blue-100">
            <i class="fa-solid fa-users"></i>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Patient Directory</h2>
          <p class="text-sm text-slate-500 mt-1.5 font-medium max-w-md">Search, manage, and view profiles for all registered patients across the hospital system.</p>
          <div class="mt-4">
            <span id="patient-count-badge" class="inline-block px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Loading directory...</span>
          </div>
        </div>
        
        <div class="flex flex-col gap-3 w-full sm:w-auto sm:min-w-[280px]">
          <div class="relative w-full shadow-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
            <input type="text" id="directory-search" oninput="handleSearchInput()" onkeydown="if(event.key==='Enter'){event.preventDefault(); triggerSearchNow();}" placeholder="Search patients..." class="w-full text-sm font-semibold text-slate-800 border border-slate-200 rounded-2xl pl-10 pr-10 py-3.5 focus:ring-2 focus:ring-blue-500/50 outline-none transition bg-white placeholder-slate-400">
            <button id="search-clear-btn" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </div>
          
          <div class="bg-slate-100 p-1.5 rounded-2xl flex items-center border border-slate-200 text-sm font-bold shadow-inner">
            <button id="btn-pat-view-grid" onclick="setPatDisplayMode('grid')" class="flex-1 py-2 rounded-xl bg-white shadow-sm text-slate-900 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-border-all text-blue-500"></i> Grid
            </button>
            <button id="btn-pat-view-table" onclick="setPatDisplayMode('table')" class="flex-1 py-2 rounded-xl text-slate-500 hover:text-slate-700 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-table-list"></i> Table
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Grid View -->
  <div id="pat-container-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 transition-opacity duration-200"></div>

  <!-- Table View -->
  <div id="pat-container-table" class="hidden bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm transition-opacity duration-200">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">MRN</th>
            <th class="py-3 px-4">Name</th>
            <th class="py-3 px-4">Father's Name</th>
            <th class="py-3 px-4">Phone</th>
            <th class="py-3 px-4">Blood</th>
            <th class="py-3 px-4">Registered Date</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody id="pat-table-tbody" class="divide-y divide-slate-100"></tbody>
      </table>
    </div>
  </div>

  <!-- Pagination Bar (< and >) -->
  <div id="pat-pagination-bar" class="bg-white rounded-2xl border border-slate-200 px-5 py-3.5 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="text-xs font-semibold text-slate-500">
      Showing <span id="pat-page-start" class="font-extrabold text-slate-900">0</span> – <span id="pat-page-end" class="font-extrabold text-slate-900">0</span> of <span id="pat-page-total" class="font-extrabold text-slate-900">0</span> patients (10 per page)
    </div>

    <div class="flex items-center gap-1.5 sm:gap-2">
      <!-- First Page -->
      <button id="pat-btn-first" onclick="goToPage(1)" title="First Page" class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-30 disabled:pointer-events-none flex items-center justify-center text-xs font-bold transition shadow-sm">
        <i class="fa-solid fa-angles-left"></i>
      </button>

      <!-- Previous Page (<) -->
      <button id="pat-btn-prev" onclick="goToPage(currentPage - 1)" title="Previous 10 Patients" class="h-9 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 disabled:opacity-30 disabled:pointer-events-none flex items-center justify-center gap-1 text-xs font-bold transition shadow-sm">
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
        <span>&lt; Prev</span>
      </button>

      <!-- Page Numbers Container -->
      <div id="pat-page-numbers" class="flex items-center gap-1"></div>

      <!-- Next Page (>) -->
      <button id="pat-btn-next" onclick="goToPage(currentPage + 1)" title="Next 10 Patients" class="h-9 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 disabled:opacity-30 disabled:pointer-events-none flex items-center justify-center gap-1 text-xs font-bold transition shadow-sm">
        <span>Next &gt;</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </button>

      <!-- Last Page -->
      <button id="pat-btn-last" onclick="goToPage(totalPages)" title="Last Page" class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-30 disabled:pointer-events-none flex items-center justify-center text-xs font-bold transition shadow-sm">
        <i class="fa-solid fa-angles-right"></i>
      </button>
    </div>
  </div>
</div>

<script>
  let allPatients = [];
  let displayMode = 'grid';
  let currentPage = 1;
  const pageSize = 10;
  let totalPatients = 0;
  let totalPages = 1;
  let isLoading = false;
  let searchDebounceTimer = null;

  window.addEventListener('DOMContentLoaded', () => {
    fetchPatients(1);
  });

  function setPatDisplayMode(mode) {
    displayMode = mode;
    document.getElementById('btn-pat-view-grid').className = mode === 'grid' 
      ? "flex-1 py-2 rounded-xl bg-white shadow-sm text-slate-900 flex items-center justify-center gap-2 transition font-bold" 
      : "flex-1 py-2 rounded-xl text-slate-500 hover:text-slate-700 flex items-center justify-center gap-2 transition font-medium";
    document.getElementById('btn-pat-view-table').className = mode === 'table' 
      ? "flex-1 py-2 rounded-xl bg-white shadow-sm text-slate-900 flex items-center justify-center gap-2 transition font-bold" 
      : "flex-1 py-2 rounded-xl text-slate-500 hover:text-slate-700 flex items-center justify-center gap-2 transition font-medium";
    
    document.getElementById('pat-container-grid').classList.toggle('hidden', mode !== 'grid');
    document.getElementById('pat-container-table').classList.toggle('hidden', mode !== 'table');
    renderPatients();
  }

  function handleSearchInput() {
    const rawQ = (document.getElementById('directory-search').value || '').trim();
    const clearBtn = document.getElementById('search-clear-btn');
    if (clearBtn) clearBtn.classList.toggle('hidden', rawQ.length === 0);

    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
      fetchPatients(1);
    }, 280);
  }

  function triggerSearchNow() {
    clearTimeout(searchDebounceTimer);
    fetchPatients(1);
  }

  function clearSearch() {
    const input = document.getElementById('directory-search');
    input.value = '';
    const clearBtn = document.getElementById('search-clear-btn');
    if (clearBtn) clearBtn.classList.add('hidden');
    clearTimeout(searchDebounceTimer);
    fetchPatients(1);
    input.focus();
  }

  function showLoadingUI(loading) {
    const grid = document.getElementById('pat-container-grid');
    const table = document.getElementById('pat-container-table');
    const badge = document.getElementById('patient-count-badge');
    const prevBtn = document.getElementById('pat-btn-prev');
    const nextBtn = document.getElementById('pat-btn-next');
    const firstBtn = document.getElementById('pat-btn-first');
    const lastBtn = document.getElementById('pat-btn-last');

    if (loading) {
      if (prevBtn) prevBtn.disabled = true;
      if (nextBtn) nextBtn.disabled = true;
      if (firstBtn) firstBtn.disabled = true;
      if (lastBtn) lastBtn.disabled = true;

      if (allPatients.length === 0) {
        if (badge) badge.textContent = 'Loading patients...';
        if (grid) grid.innerHTML = `<div class="col-span-full text-center text-slate-400 py-16 text-sm flex flex-col items-center justify-center gap-3"><i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-500"></i><span class="font-medium">Loading patients...</span></div>`;
        const tbody = document.getElementById('pat-table-tbody');
        if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-400 py-16 text-sm"><i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-500 mr-2 align-middle"></i><span class="font-medium">Loading patients...</span></td></tr>`;
      } else {
        if (grid) grid.classList.add('opacity-40', 'pointer-events-none');
        if (table) table.classList.add('opacity-40', 'pointer-events-none');
      }
    } else {
      if (grid) grid.classList.remove('opacity-40', 'pointer-events-none');
      if (table) table.classList.remove('opacity-40', 'pointer-events-none');
    }
  }

  async function fetchPatients(page = 1) {
    if (isLoading) return;
    isLoading = true;
    currentPage = page;
    showLoadingUI(true);

    const searchVal = (document.getElementById('directory-search')?.value || '').trim();

    try {
      const res = await fetch(`api/patients.php?action=get_all&page=${encodeURIComponent(page)}&limit=${pageSize}&search=${encodeURIComponent(searchVal)}`);
      const data = await res.json();
      if (data.status === 'success') {
        allPatients = data.patients || [];
        totalPatients = Number(data.total ?? allPatients.length);
        totalPages = Math.max(1, Number(data.total_pages ?? Math.ceil(totalPatients / pageSize)));
        currentPage = Math.min(Math.max(1, Number(data.page ?? page)), totalPages);
        renderPatients();
        renderPagination();
      } else {
        showToast('Error', data.message || 'Failed to fetch patients', 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Error', 'Unable to connect to server', 'error');
    } finally {
      isLoading = false;
      showLoadingUI(false);
    }
  }

  function goToPage(page) {
    if (page < 1 || page > totalPages || page === currentPage || isLoading) return;
    fetchPatients(page);
    const container = document.getElementById('pat-container-grid') || document.getElementById('pat-container-table');
    if (container) {
      window.scrollTo({
        top: Math.max(0, container.getBoundingClientRect().top + window.pageYOffset - 120),
        behavior: 'smooth'
      });
    }
  }

  function renderPagination() {
    const startSpan = document.getElementById('pat-page-start');
    const endSpan = document.getElementById('pat-page-end');
    const totalSpan = document.getElementById('pat-page-total');
    const prevBtn = document.getElementById('pat-btn-prev');
    const nextBtn = document.getElementById('pat-btn-next');
    const firstBtn = document.getElementById('pat-btn-first');
    const lastBtn = document.getElementById('pat-btn-last');
    const numbersContainer = document.getElementById('pat-page-numbers');

    const start = totalPatients === 0 ? 0 : (currentPage - 1) * pageSize + 1;
    const end = Math.min(currentPage * pageSize, totalPatients);

    if (startSpan) startSpan.textContent = start;
    if (endSpan) endSpan.textContent = end;
    if (totalSpan) totalSpan.textContent = totalPatients;

    if (prevBtn) prevBtn.disabled = (currentPage <= 1 || isLoading);
    if (nextBtn) nextBtn.disabled = (currentPage >= totalPages || isLoading);
    if (firstBtn) firstBtn.disabled = (currentPage <= 1 || isLoading);
    if (lastBtn) lastBtn.disabled = (currentPage >= totalPages || isLoading);

    if (numbersContainer) {
      numbersContainer.innerHTML = '';
      if (totalPages <= 1) {
        numbersContainer.innerHTML = `<span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 text-xs font-bold border border-blue-100 flex items-center justify-center">1</span>`;
        return;
      }

      const maxVisible = 5;
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, startPage + maxVisible - 1);
      if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
      }

      if (startPage > 1) {
        numbersContainer.innerHTML += `<button onclick="goToPage(1)" class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center justify-center">1</button>`;
        if (startPage > 2) {
          numbersContainer.innerHTML += `<span class="px-1 text-slate-400 text-xs">…</span>`;
        }
      }

      for (let p = startPage; p <= endPage; p++) {
        if (p === currentPage) {
          numbersContainer.innerHTML += `<button class="w-8 h-8 rounded-xl bg-blue-600 text-white text-xs font-black shadow-md shadow-blue-200 cursor-default flex items-center justify-center">${p}</button>`;
        } else {
          numbersContainer.innerHTML += `<button onclick="goToPage(${p})" class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center justify-center">${p}</button>`;
        }
      }

      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          numbersContainer.innerHTML += `<span class="px-1 text-slate-400 text-xs">…</span>`;
        }
        numbersContainer.innerHTML += `<button onclick="goToPage(${totalPages})" class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center justify-center">${totalPages}</button>`;
      }
    }
  }

  function renderPatients() {
    const badge = document.getElementById('patient-count-badge');
    const searchVal = (document.getElementById('directory-search')?.value || '').trim();
    if (badge) {
      if (totalPatients === 0) {
        badge.textContent = '0 patients found';
      } else if (searchVal.length > 0) {
        badge.textContent = `Found ${totalPatients} patient${totalPatients === 1 ? '' : 's'} (Page ${currentPage} of ${totalPages})`;
      } else {
        badge.textContent = `${totalPatients} patient${totalPatients === 1 ? '' : 's'} (Page ${currentPage} of ${totalPages})`;
      }
    }

    if (displayMode === 'grid') {
      const grid = document.getElementById('pat-container-grid');
      grid.innerHTML = '';
      if (allPatients.length === 0) {
        grid.innerHTML = `<div class="col-span-full text-center text-slate-400 py-16 text-sm font-semibold"><i class="fa-solid fa-user-slash text-3xl block mb-2 text-slate-300"></i>No patients found.</div>`;
        return;
      }
      allPatients.forEach(p => {
        const initials = (((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : '')) || 'P').toUpperCase();
        grid.innerHTML += `
          <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 relative group flex flex-col">
            <div class="p-5 pb-0 flex gap-4 relative">
                <!-- Avatar -->
                <div class="w-16 h-16 rounded-[1.25rem] bg-blue-50 text-blue-600 flex items-center justify-center font-black text-2xl border border-blue-100 shrink-0">
                  ${initials}
                </div>
                
                <div class="flex-1 min-w-0 pt-1">
                    <div class="flex justify-between items-start mb-1">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 font-mono text-[10px] font-bold border border-slate-200">${p.id}</span>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="editPatient('${p.id}')" class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition" title="Edit Patient"><i class="fa-solid fa-pen text-[10px]"></i></button>
                            <button onclick="promptDeletePatient('${p.id}', '${p.name} ${p.surname}')" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-400 hover:text-rose-700 flex items-center justify-center transition" title="Delete Patient"><i class="fa-solid fa-trash text-[10px]"></i></button>
                        </div>
                    </div>
                    <h3 class="font-black text-slate-900 text-lg leading-tight truncate" title="${p.name} ${p.surname}">${p.name} ${p.surname}</h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5 truncate">${p.demographics || 'Demographics Unknown'} <span class="mx-1 text-slate-300">•</span> <span class="font-bold text-rose-500">${p.blood_group || '?'}</span></p>
                </div>
            </div>
            
            <div class="px-5 pt-4 pb-5 flex-1 flex flex-col">
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex-1 space-y-3 mb-5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                            <i class="fa-solid fa-phone text-slate-400 w-3 text-center"></i> Phone
                        </div>
                        <a href="${p.phone ? 'tel:'+p.phone.replace(/[^0-9+]/g,'') : '#'}" class="font-bold text-blue-600 hover:text-blue-800 transition">${p.phone || 'N/A'}</a>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                            <i class="fa-solid fa-user-tie text-slate-400 w-3 text-center"></i> Father
                        </div>
                        <span class="font-bold text-slate-700 truncate max-w-[120px]" title="${p.father_name}">${p.father_name || 'N/A'}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                            <i class="fa-solid fa-truck-medical text-rose-400 w-3 text-center"></i> Em. Contact
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-slate-700 truncate max-w-[120px]" title="${p.emergency_contact_name}">${p.emergency_contact_name || 'N/A'}</div>
                            <a href="${p.emergency_contact_phone ? 'tel:'+p.emergency_contact_phone.replace(/[^0-9+]/g,'') : '#'}" class="font-bold text-blue-600 hover:text-blue-800 transition block mt-0.5 text-[10px]">${p.emergency_contact_phone || ''}</a>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-1.5 mt-auto">
                    <button onclick="openBookingModal('${p.id}', '${p.name}', '${p.surname}')" class="flex-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold py-2 rounded-xl text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <i class="fa-solid fa-calendar-plus"></i> Book
                    </button>
                    <button onclick="showPatientQRCode('${p.id}')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold py-2 px-2.5 rounded-xl text-xs transition flex items-center justify-center gap-1 shadow-sm" title="Lifetime Patient QR Card">
                        <i class="fa-solid fa-qrcode"></i> QR
                    </button>
                    <button onclick="window.location.href='patient_profile.php?id=${p.id}'" class="flex-1 bg-slate-900 hover:bg-black text-white font-bold py-2 rounded-xl text-xs transition flex items-center justify-center gap-1 shadow-md">
                        Profile <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </div>
          </div>
        `;
      });
    } else {
      const tbody = document.getElementById('pat-table-tbody');
      tbody.innerHTML = '';
      if (allPatients.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-400 py-16 text-sm font-semibold"><i class="fa-solid fa-user-slash text-2xl inline-block mr-2 text-slate-300"></i>No patients found.</td></tr>`;
        return;
      }
      allPatients.forEach(p => {
        tbody.innerHTML += `
          <tr class="hover:bg-slate-50 transition">
            <td class="py-3 px-4 font-mono font-bold">${p.id}</td>
            <td class="py-3 px-4 font-bold text-slate-900">${p.name} ${p.surname}</td>
            <td class="py-3 px-4">${p.father_name}</td>
            <td class="py-3 px-4">${p.phone || '-'}</td>
            <td class="py-3 px-4">🩸 ${p.blood_group || '-'}</td>
            <td class="py-3 px-4">${p.reg_date || '-'}</td>
            <td class="py-3 px-4 text-right flex justify-end gap-1.5 items-center">
              <button onclick="showPatientQRCode('${p.id}')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition flex items-center gap-1" title="Lifetime QR Card"><i class="fa-solid fa-qrcode"></i> QR</button>
              <button onclick="openBookingModal('${p.id}', '${p.name}', '${p.surname}')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Book</button>
              <button onclick="window.location.href='patient_profile.php?id=${p.id}'" class="bg-slate-50 hover:bg-slate-100 text-blue-700 px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Profile</button>
              <button onclick="editPatient('${p.id}')" class="bg-slate-50 hover:bg-slate-100 text-slate-600 px-2.5 py-1.5 rounded-lg text-[10px] transition"><i class="fa-solid fa-pen"></i></button>
              <button onclick="promptDeletePatient('${p.id}', '${p.name} ${p.surname}')" class="bg-slate-50 hover:bg-rose-100 text-slate-600 hover:text-rose-600 px-2.5 py-1.5 rounded-lg text-[10px] transition"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
        `;
      });
    }
  }

  // --- Edit Patient Logic ---
  let editPatId = null;
  function editPatient(id) {
      const p = allPatients.find(x => x.id === id);
      if(!p) return;
      editPatId = id;
      document.getElementById('edit-pat-name').value = p.name;
      document.getElementById('edit-pat-surname').value = p.surname;
      document.getElementById('edit-pat-father').value = p.father_name;
      document.getElementById('edit-pat-phone').value = p.phone || '';

      let age = '', gender = '', blood = p.blood_group || '';
      if (p.demographics) {
          const parts = p.demographics.split(',').map(s => s.trim());
          parts.forEach(part => {
              if (part.endsWith('Y') || part.endsWith('y')) age = part.replace(/[^0-9]/g, '');
              if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
              if (part.includes('+') || part.includes('-')) blood = part;
          });
      }
      
      document.getElementById('edit-pat-age').value = age;
      document.getElementById('edit-pat-gender').value = gender;
      document.getElementById('edit-pat-blood').value = blood;
      document.getElementById('edit-pat-em-name').value = p.emergency_contact_name || '';
      document.getElementById('edit-pat-em-phone').value = p.emergency_contact_phone || '';

      document.getElementById('modal-edit-patient').classList.remove('hidden');
  }

  function handleEditPatient(e) {
      e.preventDefault();
      
      const payload = {
          id: editPatId,
          name: document.getElementById('edit-pat-name').value.trim(),
          surname: document.getElementById('edit-pat-surname').value.trim(),
          father: document.getElementById('edit-pat-father').value.trim(),
          phone: document.getElementById('edit-pat-phone').value.trim(),
          age: document.getElementById('edit-pat-age').value.trim(),
          gender: document.getElementById('edit-pat-gender').value.trim(),
          blood_group: document.getElementById('edit-pat-blood').value.trim(),
          emergency_contact_name: document.getElementById('edit-pat-em-name').value.trim(),
          emergency_contact_phone: document.getElementById('edit-pat-em-phone').value.trim()
      };

      const html = `
          <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Name:</span> <span class="col-span-2 font-semibold text-slate-900">${payload.name} ${payload.surname}</span></div>
          <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Demographics:</span> <span class="col-span-2 font-semibold text-slate-900">${payload.age ? payload.age+' Y, ' : ''}${payload.gender} (Blood: ${payload.blood_group})</span></div>
          <div class="grid grid-cols-3 gap-2 border-b border-slate-100 pb-2"><span class="font-bold text-slate-500">Phone:</span> <span class="col-span-2 font-semibold text-slate-900">${payload.phone}</span></div>
          <div class="grid grid-cols-3 gap-2"><span class="font-bold text-slate-500">Em. Contact:</span> <span class="col-span-2 font-semibold text-slate-900">${payload.emergency_contact_name} (${payload.emergency_contact_phone})</span></div>
      `;

      document.getElementById('modal-edit-patient').classList.add('hidden');
      
      openConfirmModal('Confirm Edit', 'Verify the updated patient details.', html, async () => {
          try {
              const res = await fetch('api/patients.php?action=update', {
                  method: 'POST',
                  headers: {'Content-Type': 'application/json'},
                  body: JSON.stringify(payload)
              });
              const data = await res.json();
              if (data.status === 'success') {
                  showToast('Success', 'Patient updated successfully.');
                  fetchPatients(currentPage);
              } else {
                  showToast('Error', data.message, 'error');
              }
          } catch(err) { console.error(err); }
      });
  }

  let deletePatId = null;
  function promptDeletePatient(id, name) {
      deletePatId = id;
      const html = `
          <div class="text-rose-600 font-bold mb-2">Warning: This action cannot be undone.</div>
          <div class="font-semibold text-slate-900">You are about to delete Patient: ${name} (${id})</div>
          <p class="text-sm mt-1">This will permanently remove their profile and all associated files/history.</p>
      `;
      openConfirmModal('Delete Patient', 'Are you absolutely sure?', html, executeDeletePatient, 'danger');
  }

  async function executeDeletePatient() {
      try {
          const res = await fetch(`api/patients.php?action=delete&id=${deletePatId}`);
          const data = await res.json();
          if (data.status === 'success') {
              showToast('Deleted', 'Patient removed successfully');
              if (allPatients.length <= 1 && currentPage > 1) {
                  fetchPatients(currentPage - 1);
              } else {
                  fetchPatients(currentPage);
              }
          } else {
              showToast('Error', data.message, 'error');
          }
      } catch (e) { console.error(e); }
  }

</script>

<!-- Edit Patient Modal -->
<div id="modal-edit-patient" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-5 sm:p-8 relative my-8 shadow-2xl border border-slate-200">
        <button onclick="document.getElementById('modal-edit-patient').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        <h3 class="text-xl font-bold text-slate-900 mb-1">Edit Patient Profile</h3>
        <p class="text-xs text-slate-500 mb-5">Update full patient information and details.</p>

        <form onsubmit="handleEditPatient(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Personal Info -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">First Name *</label>
                <input type="text" id="edit-pat-name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Surname *</label>
                <input type="text" id="edit-pat-surname" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Father's Name *</label>
                <input type="text" id="edit-pat-father" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Phone Number</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="edit-pat-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            
            <!-- Demographics -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Age</label>
                <input type="number" id="edit-pat-age" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Gender</label>
                <select id="edit-pat-gender" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none bg-white">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Blood Group</label>
                <select id="edit-pat-blood" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none bg-white">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            
            <!-- Emergency Contact -->
            <div class="md:col-span-2 pt-2 border-t border-slate-100 mt-2">
                <h4 class="text-xs font-bold text-slate-800 mb-3">Emergency Contact</h4>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Contact Name</label>
                <input type="text" id="edit-pat-em-name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Contact Phone</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="edit-pat-em-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>

            <div class="md:col-span-2 pt-4">
                <button type="submit" id="ep-submit-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition text-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/booking_modal.php'; ?>
<?php include 'includes/patient_qr_card_modal.php'; ?>

<?php include 'includes/footer.php'; ?>

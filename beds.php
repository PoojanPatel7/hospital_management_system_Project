<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<div class="space-y-6">
  <!-- Bed Controls & Stats -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
      <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-bed-pulse text-rose-600"></i> Hospital Bed Ward
      </h2>
      <p class="text-xs text-slate-500 mt-1">Interactive graphical floor layout and table registry for OPD and ICU.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-semibold">
        <button id="btn-bed-view-grid" onclick="setBedDisplayMode('grid')" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900">Grid</button>
        <button id="btn-bed-view-table" onclick="setBedDisplayMode('table')" class="px-3 py-1.5 rounded-lg text-slate-600">Table</button>
      </div>
      <button onclick="openAddBedModal()" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition flex items-center gap-1.5">
        <i class="fa-solid fa-plus"></i> Add Bed
      </button>
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between">
      <div><div class="text-xs text-slate-500">OPD Beds</div><div id="stat-opd-total" class="text-xl sm:text-2xl font-black">0</div></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between">
      <div><div class="text-xs text-slate-500">OPD Available</div><div id="stat-opd-avail" class="text-xl sm:text-2xl font-black text-emerald-600">0</div></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between">
      <div><div class="text-xs text-slate-500">ICU Beds</div><div id="stat-icu-total" class="text-xl sm:text-2xl font-black">0</div></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between">
      <div><div class="text-xs text-slate-500">ICU Available</div><div id="stat-icu-avail" class="text-xl sm:text-2xl font-black text-amber-600">0</div></div>
    </div>
  </div>

  <div id="bed-container-grid" class="space-y-6 sm:space-y-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6">
      <h3 class="font-bold text-base text-slate-900 mb-4 border-b pb-2 flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> OPD Observation Ward
      </h3>
      <div id="opd-bed-matrix" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4"></div>
    </div>
    <div class="bg-white rounded-2xl border border-rose-200 p-4 sm:p-6">
      <h3 class="font-bold text-base text-slate-900 mb-4 border-b border-rose-100 pb-2 text-rose-700 flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> ICU Critical Care
      </h3>
      <div id="icu-bed-matrix" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4"></div>
    </div>
  </div>

  <div id="bed-container-table" class="hidden bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex gap-2 justify-end">
      <select id="bed-type-filter" onchange="renderBedTable()" class="text-xs border px-3 py-1.5 rounded-xl bg-white"><option value="All">All Types</option><option value="OPD">OPD Only</option><option value="ICU">ICU Only</option></select>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs min-w-[500px]">
        <thead class="bg-slate-100 font-bold border-b text-slate-700 uppercase tracking-wider text-[11px]">
          <tr>
            <th class="p-3">Bed</th>
            <th class="p-3">Type</th>
            <th class="p-3">Status</th>
            <th class="p-3">Patient</th>
            <th class="p-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="bed-table-tbody" class="divide-y divide-slate-100"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ADD BED MODAL -->
<div id="modal-add-bed" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 relative my-8 max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
    <button onclick="closeAddBedModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-bed-pulse"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-900 text-base">Add New Bed</h3>
        <p class="text-[11px] text-slate-500">Configure ward and bed designation.</p>
      </div>
    </div>
    <form onsubmit="handleCreateBed(event)" class="space-y-3">
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Ward Type</label>
        <select id="new-bed-type" onchange="autoSuggestBedNumber()" class="w-full text-xs border rounded-xl p-2.5 bg-slate-50 focus:bg-white"><option value="OPD">OPD</option><option value="ICU">ICU</option></select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Bed Number</label>
        <input type="text" id="new-bed-number" required class="w-full text-xs border rounded-xl p-2.5 font-mono bg-slate-50 focus:bg-white">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Wing Location</label>
        <input type="text" id="new-bed-wing" value="General Wing" class="w-full text-xs border rounded-xl p-2.5 bg-slate-50 focus:bg-white">
      </div>
      <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-2.5 text-xs font-bold shadow-md transition">Add Bed</button>
    </form>
  </div>
</div>

<script>
  let beds = [];
  let displayMode = 'grid';

  window.addEventListener('DOMContentLoaded', fetchBeds);

  function setBedDisplayMode(mode) {
    displayMode = mode;
    document.getElementById('btn-bed-view-grid').className = mode === 'grid' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold" : "px-3 py-1.5 rounded-lg text-slate-600 font-semibold";
    document.getElementById('btn-bed-view-table').className = mode === 'table' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold" : "px-3 py-1.5 rounded-lg text-slate-600 font-semibold";
    document.getElementById('bed-container-grid').classList.toggle('hidden', mode !== 'grid');
    document.getElementById('bed-container-table').classList.toggle('hidden', mode !== 'table');
    if(mode === 'table') renderBedTable();
  }

  async function fetchBeds() {
      try {
          const res = await fetch('api/beds.php?action=get_all');
          const data = await res.json();
          if (data.status === 'success') {
              beds = data.beds;
              renderBedsGrid();
              if(displayMode === 'table') renderBedTable();
          }
      } catch (e) {}
  }

  function renderBedsGrid() {
    const opdBeds = beds.filter(b => b.type === 'OPD');
    const icuBeds = beds.filter(b => b.type === 'ICU');

    document.getElementById('stat-opd-total').textContent = opdBeds.length;
    document.getElementById('stat-opd-avail').textContent = opdBeds.filter(b => b.status === 'Available').length;
    document.getElementById('stat-icu-total').textContent = icuBeds.length;
    document.getElementById('stat-icu-avail').textContent = icuBeds.filter(b => b.status === 'Available').length;

    const opdMatrix = document.getElementById('opd-bed-matrix');
    opdMatrix.innerHTML = '';
    opdBeds.forEach(bed => opdMatrix.appendChild(createBedCard(bed)));

    const icuMatrix = document.getElementById('icu-bed-matrix');
    icuMatrix.innerHTML = '';
    icuBeds.forEach(bed => icuMatrix.appendChild(createBedCard(bed)));
  }

  function createBedCard(bed) {
    const isOccupied = bed.status === 'Occupied';
    const card = document.createElement('div');
    card.className = `rounded-2xl p-4 border transition ${isOccupied ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-white'}`;
    card.innerHTML = `
      <div class="flex justify-between font-mono font-bold text-sm mb-2">
        ${bed.bed_number}
        <span class="text-[10px] ${isOccupied ? 'text-rose-600' : 'text-emerald-600'}">${isOccupied ? 'Occupied' : 'Vacant'}</span>
      </div>
      ${isOccupied ? `<div class="text-xs font-bold">${bed.name} ${bed.surname}</div><div class="text-[10px] text-slate-500">${bed.patient_id}</div>` : `<div class="h-8"></div>`}
      <div class="mt-3 pt-3 border-t flex items-center justify-between">
        ${isOccupied ? `
          <button onclick="openPatientDossier('${bed.patient_id}')" class="text-[10px] text-emerald-700 bg-emerald-100 font-bold px-2 py-1 rounded">View Info</button>
          <button onclick="dischargeBedPatient('${bed.bed_number}')" class="text-[10px] text-rose-600 font-bold border border-rose-200 px-2 py-1 rounded">Discharge</button>
        ` : `
          <div></div><button onclick="deleteBed('${bed.bed_number}')" class="text-[10px] text-slate-400 hover:text-rose-600"><i class="fa-solid fa-trash"></i></button>
        `}
      </div>
    `;
    return card;
  }

  function renderBedTable() {
    const type = document.getElementById('bed-type-filter').value;
    const tbody = document.getElementById('bed-table-tbody');
    tbody.innerHTML = '';
    beds.filter(b => type === 'All' || b.type === type).forEach(b => {
      tbody.innerHTML += `
        <tr class="border-b hover:bg-slate-50 transition">
          <td class="p-3 font-mono font-bold">${b.bed_number}</td>
          <td class="p-3">${b.type}</td>
          <td class="p-3">${b.status}</td>
          <td class="p-3">${b.patient_id ? b.name + ' ' + b.surname : '-'}</td>
          <td class="p-3 text-right flex justify-end gap-2">
            ${b.status === 'Occupied' ? `
              <button onclick="openPatientDossier('${b.patient_id}')" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-bold">Info</button>
              <button onclick="dischargeBedPatient('${b.bed_number}')" class="text-xs text-rose-600 border border-rose-200 px-2 py-1 rounded font-bold">Discharge</button>
            ` : `
              <button onclick="deleteBed('${b.bed_number}')" class="text-xs text-slate-400">Delete</button>
            `}
          </td>
        </tr>
      `;
    });
  }

  function openAddBedModal() { autoSuggestBedNumber(); document.getElementById('modal-add-bed').classList.remove('hidden'); }
  function closeAddBedModal() { document.getElementById('modal-add-bed').classList.add('hidden'); }
  function autoSuggestBedNumber() {
    const type = document.getElementById('new-bed-type').value;
    document.getElementById('new-bed-number').value = `${type}-${beds.filter(b=>b.type===type).length + 1}`;
  }

  async function handleCreateBed(e) {
    e.preventDefault();
    try {
        const res = await fetch('api/beds.php?action=add', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ type: document.getElementById('new-bed-type').value, number: document.getElementById('new-bed-number').value, wing: document.getElementById('new-bed-wing').value })
        });
        const data = await res.json();
        if (data.status === 'success') { closeAddBedModal(); fetchBeds(); showToast('Success', data.message); }
    } catch(err){}
  }
  
  async function dischargeBedPatient(num) {
      if(!confirm('Discharge patient and free bed?')) return;
      try {
          const res = await fetch('api/beds.php?action=discharge', { method: 'POST', body: JSON.stringify({ number: num }) });
          fetchBeds(); showToast('Discharged', 'Bed is now free');
      } catch(err){}
  }
  
  async function deleteBed(num) {
      try {
          await fetch('api/beds.php?action=delete', { method: 'POST', body: JSON.stringify({ number: num }) });
          fetchBeds();
      } catch(err){}
  }
</script>

<?php include 'includes/dossier_modal.php'; ?>
<?php include 'includes/footer.php'; ?>

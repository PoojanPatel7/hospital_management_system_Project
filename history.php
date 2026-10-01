<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<div class="space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
      <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Global Patient History
      </h2>
      <p class="text-xs text-slate-500 mt-1">Master log of all patient visits, admissions, and checkups.</p>
    </div>
    
    <div class="w-full sm:w-96 relative">
      <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
      <input type="text" id="patient-search-query" oninput="fetchPatientHistoryTable()" placeholder="Search patient ID, name, or status..." class="w-full text-sm font-semibold border border-slate-300 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-emerald-500/50 outline-none bg-slate-50 focus:bg-white shadow-sm transition">
    </div>
  </div>

  <!-- Table View -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-600 min-w-[900px]">
        <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200 text-[10px] tracking-wider">
          <tr>
            <th class="py-4 px-5">Date & Visit ID</th>
            <th class="py-4 px-5">Patient Name</th>
            <th class="py-4 px-5">Consultation</th>
            <th class="py-4 px-5">Doctor</th>
            <th class="py-4 px-5">Status / Outcome</th>
            <th class="py-4 px-5">Bed</th>
            <th class="py-4 px-5">Diagnoses</th>
            <th class="py-4 px-5 text-right">Action</th>
          </tr>
        </thead>
        <tbody id="patient-history-tbody" class="divide-y divide-slate-100"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let searchTimeout = null;

  window.addEventListener('DOMContentLoaded', fetchPatientHistoryTable);

  function fetchPatientHistoryTable() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(async () => {
          const q = document.getElementById('patient-search-query').value;
          try {
              const res = await fetch(`api/history.php?action=get_all_patients&q=${encodeURIComponent(q)}`);
              const data = await res.json();
              if (data.status === 'success') {
                  renderHistoryData(data.patients || []);
              }
          } catch(e) { console.error(e); }
      }, 250);
  }

  function renderHistoryData(historyRecords) {
      const tbody = document.getElementById('patient-history-tbody');
      tbody.innerHTML = '';
      if (historyRecords.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12 text-slate-400 text-sm font-medium">No history records found.</td></tr>`;
          return;
      }
      historyRecords.forEach(h => {
          const statusColors = h.status === 'Waiting for Reports'
              ? 'bg-amber-100 text-amber-800 border-amber-200'
              : (h.status && h.status.includes('Admit') 
                  ? 'bg-rose-100 text-rose-800 border-rose-200' 
                  : 'bg-emerald-100 text-emerald-800 border-emerald-200');
              
          tbody.innerHTML += `
            <tr class="hover:bg-slate-50 transition cursor-pointer group" onclick="window.location.href='patient_profile.php?id=${h.patient_id}'">
              <td class="py-4 px-5">
                <div class="font-bold text-slate-900">${h.date ? h.date.split(' ')[0] : '-'}</div>
                <div class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded inline-block mt-1">${h.appointment_code}</div>
              </td>
              <td class="py-4 px-5">
                <div class="font-bold text-slate-900 text-sm">${h.name} ${h.surname}</div>
                <div class="text-[10px] text-slate-500 font-mono mt-0.5">${h.patient_id}</div>
              </td>
              <td class="py-4 px-5 font-semibold text-slate-700">${h.type || '-'}</td>
              <td class="py-4 px-5 text-slate-700">${h.doctor_name || '-'}</td>
              <td class="py-4 px-5">
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border ${statusColors}">
                  ${h.status || 'General Checkup'}
                </span>
              </td>
              <td class="py-4 px-5 font-mono font-bold ${h.bed_number ? 'text-rose-600' : 'text-slate-400'}">${h.bed_number || '-'}</td>
              <td class="py-4 px-5 text-slate-700 italic truncate max-w-[200px]" title="${h.diagnoses.join(', ')}">
                ${h.diagnoses.length > 0 ? h.diagnoses.join(', ') : 'No diagnoses'}
              </td>
              <td class="py-4 px-5 text-right">
                <button class="bg-white group-hover:bg-blue-600 text-blue-600 group-hover:text-white border border-blue-200 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                  View Profile
                </button>
              </td>
            </tr>
          `;
      });
  }
</script>

<?php include 'includes/footer.php'; ?>

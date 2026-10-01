<!-- DOCTOR'S DOSSIER MODAL -->
<div id="modal-dossier" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-4xl w-full shadow-2xl border border-slate-200 relative my-auto sm:my-8 flex flex-col max-h-[92vh]">
    <!-- Close button -->
    <button onclick="closeDossierModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition z-10">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <!-- Header Section -->
    <div class="p-4 sm:p-6 md:p-8 border-b border-slate-200 bg-slate-50/50 rounded-t-2xl shrink-0">
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <span id="dossier-status" class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Loading...</span>
            <span class="text-xs text-slate-500 font-mono flex items-center gap-1"><i class="fa-solid fa-hashtag text-slate-300"></i> <span id="dossier-mrn">MRN-000</span></span>
          </div>
          <h3 id="dossier-name" class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900">Patient Name</h3>
          <p class="text-xs sm:text-sm text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
            <span>Father: <strong id="dossier-father" class="text-slate-800">...</strong></span> • 
            <span>Phone: <strong id="dossier-phone" class="text-slate-800">...</strong></span> • 
            <span>Blood: <strong id="dossier-blood" class="text-slate-800">...</strong></span> • 
            <span id="dossier-demographics">...</span>
          </p>
        </div>
        <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-sm text-xs w-full sm:w-auto sm:min-w-[200px]">
          <div class="font-bold text-slate-400 uppercase mb-1">Latest Visit</div>
          <div class="flex justify-between mb-0.5"><span class="text-slate-500">Dept:</span> <strong id="dossier-last-dept" class="text-slate-800">...</strong></div>
          <div class="flex justify-between mb-0.5"><span class="text-slate-500">Doctor:</span> <strong id="dossier-last-doc" class="text-slate-800">...</strong></div>
          <div class="flex justify-between"><span class="text-slate-500">Type:</span> <strong id="dossier-last-type" class="text-slate-800">...</strong></div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-slate-200 mt-5 gap-2 overflow-x-auto">
        <button onclick="switchDossierTab('appointments')" id="tab-btn-appointments" class="dossier-tab active text-xs sm:text-sm font-bold text-blue-600 border-b-2 border-blue-600 px-3 sm:px-4 py-2 hover:bg-blue-50 rounded-t-lg transition shrink-0">Appointments</button>
        <button onclick="switchDossierTab('files')" id="tab-btn-files" class="dossier-tab text-xs sm:text-sm font-bold text-slate-500 border-b-2 border-transparent px-3 sm:px-4 py-2 hover:text-slate-700 hover:bg-slate-100 rounded-t-lg transition shrink-0">All Medical Scans</button>
      </div>
    </div>

    <!-- Scrollable Content Body -->
    <div class="p-4 sm:p-6 md:p-8 overflow-y-auto custom-scrollbar flex-1 bg-white rounded-b-2xl">
      
      <!-- Tab Content: Appointments -->
      <div id="tab-content-appointments" class="dossier-content">
        <div id="dossier-appointments-list"></div>
      </div>

      <!-- Tab Content: Files -->
      <div id="tab-content-files" class="dossier-content hidden">
        <div id="dossier-files-list" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          <!-- Files rendered here -->
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  let activeDossierId = null;

  function switchDossierTab(tabId) {
    document.querySelectorAll('.dossier-tab').forEach(el => {
      el.classList.remove('text-blue-600', 'border-blue-600', 'bg-blue-50');
      el.classList.add('text-slate-500', 'border-transparent');
    });
    document.querySelectorAll('.dossier-content').forEach(el => el.classList.add('hidden'));

    const btn = document.getElementById(`tab-btn-${tabId}`);
    btn.classList.add('text-blue-600', 'border-blue-600');
    btn.classList.remove('text-slate-500', 'border-transparent');
    document.getElementById(`tab-content-${tabId}`).classList.remove('hidden');
  }

  async function openPatientDossier(patient_id) {
    activeDossierId = patient_id;
    try {
        const res = await fetch(`api/history.php?action=get_dossier&patient_id=${patient_id}`);
        const data = await res.json();
        
        if (data.status === 'success') {
            const p = data.dossier;
            
            // Header Info
            document.getElementById('dossier-name').textContent = p.name + ' ' + p.surname;
            document.getElementById('dossier-mrn').textContent = p.id;
            document.getElementById('dossier-father').textContent = p.father_name || 'N/A';
            document.getElementById('dossier-demographics').textContent = p.demographics || 'N/A';
            document.getElementById('dossier-phone').textContent = p.phone || 'N/A';
            document.getElementById('dossier-blood').textContent = p.blood_group ? `🩸 ${p.blood_group}` : 'N/A';
            
            // Appt Status
            document.getElementById('dossier-status').textContent = p.status || 'Unknown';
            let dStatusCls = 'text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800';
            if (p.status === 'Waiting for Reports') {
                dStatusCls = 'text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300';
            } else if (p.status && p.status.includes('Admit')) {
                dStatusCls = 'text-xs font-bold px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300';
            }
            document.getElementById('dossier-status').className = dStatusCls;
            
            // Last Visit Info
            document.getElementById('dossier-last-dept').textContent = p.dept && p.dept !== '-' ? p.dept : 'N/A';
            document.getElementById('dossier-last-doc').textContent = p.doctor && p.doctor !== '-' ? p.doctor : 'N/A';
            document.getElementById('dossier-last-type').textContent = p.type && p.type !== '-' ? p.type : 'N/A';

            // Appointments Tab
            const apptsList = document.getElementById('dossier-appointments-list');
            if (p.appointments && p.appointments.length > 0) {
                apptsList.innerHTML = p.appointments.map((appt, idx) => `
                    <div class="border border-slate-200 rounded-xl mb-4 bg-white overflow-hidden shadow-sm">
                        <button onclick="toggleAppointmentDetails(${idx})" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 transition text-left">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-100 text-blue-800 border border-blue-200">${appt.appointment_code || ('APP-' + String(appt.id).padStart(4, '0'))}</span>
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">${appt.date}</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900">Visit for ${appt.dept} with ${appt.doctor_name || '-'}</h4>
                                ${appt.symptoms ? `<p class="text-xs text-slate-500 mt-0.5"><strong>Reason:</strong> ${appt.symptoms}</p>` : ''}
                            </div>
                            <i id="icon-appt-${idx}" class="fa-solid fa-chevron-down text-slate-400 transition-transform"></i>
                        </button>
                        
                        <div id="content-appt-${idx}" class="hidden p-4 border-t border-slate-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Timeline & Files -->
                                <div>
                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3"><i class="fa-solid fa-clock-rotate-left"></i> Activity Timeline</h5>
                                    <div class="space-y-3 mb-6">
                                        ${appt.timeline && appt.timeline.length > 0 ? appt.timeline.map(t => `
                                            <div class="flex items-start gap-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-1.5"></div>
                                                <div>
                                                    <span class="text-[10px] text-slate-400 font-bold block">${t.time}</span>
                                                    <span class="text-xs text-slate-700">${t.event}</span>
                                                </div>
                                            </div>
                                        `).join('') : '<span class="text-xs text-slate-400 italic">No timeline events</span>'}
                                    </div>

                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3"><i class="fa-solid fa-images"></i> Files / Images</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        ${appt.files && appt.files.length > 0 ? appt.files.map(f => {
                                            const fileName = f.file_name || f.file_path || '';
                                            const ext = (fileName.includes('.') ? fileName.split('?')[0].split('.').pop() : '').toLowerCase();
                                            const isImg = (f.mime_type && f.mime_type.startsWith('image/')) || ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
                                            return `
                                                <a href="${f.file_path}" target="_blank" class="block border border-slate-200 rounded flex flex-col p-1 hover:border-blue-300">
                                                    ${isImg ? `<img src="${f.file_path}" class="w-full h-20 object-cover rounded mb-1" />` : `<div class="w-full h-20 bg-slate-100 flex items-center justify-center rounded mb-1"><i class="fa-solid fa-file-pdf text-rose-400 text-2xl"></i></div>`}
                                                    <span class="text-[10px] truncate text-slate-600 px-1" title="${f.title}">${f.title}</span>
                                                </a>
                                            `;
                                        }).join('') : '<span class="text-xs text-slate-400 italic col-span-2">No files</span>'}
                                    </div>
                                </div>
                                
                                <!-- Clinical Details -->
                                <div>
                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3"><i class="fa-solid fa-stethoscope"></i> Diagnoses</h5>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        ${appt.diagnoses && appt.diagnoses.length > 0 ? appt.diagnoses.map(d => `<span class="bg-blue-50 text-blue-700 text-xs px-2 py-1 rounded border border-blue-200">${d}</span>`).join('') : '<span class="text-xs text-slate-400 italic">No diagnoses</span>'}
                                    </div>

                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3"><i class="fa-solid fa-pills"></i> Medicines Detailed</h5>
                                    <div class="space-y-2 mb-4">
                                        ${appt.medicines && appt.medicines.length > 0 ? appt.medicines.map(m => `
                                            <div class="bg-slate-50 border border-slate-200 rounded p-2 text-xs">
                                                <div class="font-bold text-slate-800">${m.name}</div>
                                                <div class="text-slate-600">${m.dose} • ${m.freq} ${m.duration ? '• ' + m.duration : ''}</div>
                                                ${m.note ? `<div class="text-slate-500 mt-1 italic">${m.note}</div>` : ''}
                                            </div>
                                        `).join('') : '<span class="text-xs text-slate-400 italic">No medicines</span>'}
                                    </div>

                                    ${appt.tests_ordered ? `
                                        <h5 class="text-xs font-bold text-amber-700 uppercase mb-2"><i class="fa-solid fa-flask-vial"></i> Ordered Tests</h5>
                                        <div class="p-2 rounded bg-amber-50 border border-amber-200 text-xs text-amber-900 mb-4 font-semibold">${appt.tests_ordered}</div>
                                    ` : ''}

                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3"><i class="fa-solid fa-notes-medical"></i> Doctor's Notes</h5>
                                    <div class="text-xs text-slate-700 bg-amber-50 p-3 rounded border border-amber-100">
                                        ${appt.doctor_notes || '<span class="italic text-slate-400">No notes provided.</span>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                apptsList.innerHTML = '<div class="text-sm text-slate-400 text-center py-8">No appointments found.</div>';
            }

            // All Files tab
            const filesList = document.getElementById('dossier-files-list');
            if (p.files && p.files.length > 0) {
                filesList.innerHTML = p.files.map(f => {
                    const fileName = f.file_name || f.file_path || '';
                    const ext = (fileName.includes('.') ? fileName.split('?')[0].split('.').pop() : '').toLowerCase();
                    const isImg = (f.mime_type && f.mime_type.startsWith('image/')) || ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
                    const icon = isImg ? 'fa-image text-emerald-500' : 'fa-file-pdf text-rose-500';
                    return `
                        <a href="${f.file_path}" target="_blank" class="block border border-slate-200 rounded-xl p-3 bg-white hover:border-blue-300 hover:shadow-md transition group">
                            <div class="aspect-square bg-slate-50 rounded-lg mb-3 flex items-center justify-center text-4xl group-hover:bg-blue-50 transition overflow-hidden">
                                ${isImg ? `<img src="${f.file_path}" class="w-full h-full object-cover" />` : `<i class="fa-solid ${icon} group-hover:scale-110 transition-transform"></i>`}
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="text-sm font-bold text-slate-800 truncate" title="${f.title}">${f.title}</h5>
                                <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wide">${f.file_date}</p>
                            </div>
                        </a>
                    `;
                }).join('');
            } else {
                filesList.innerHTML = '<div class="col-span-full text-sm text-slate-400 text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-200">No medical files or scans uploaded yet.</div>';
            }

            switchDossierTab('appointments');
            document.getElementById('modal-dossier').classList.remove('hidden');
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch (err) { console.error(err); }
  }

  function toggleAppointmentDetails(idx) {
    const content = document.getElementById(`content-appt-${idx}`);
    const icon = document.getElementById(`icon-appt-${idx}`);
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
  }

  function closeDossierModal() { document.getElementById('modal-dossier').classList.add('hidden'); }
</script>

<?php require_once 'auth.php'; ?>
<?php 
if (!isset($_GET['id'])) {
    header("Location: patients.php");
    exit;
}
$patient_id = $_GET['id'];
?>
<?php include 'includes/header.php'; ?>

<div class="flex flex-col lg:flex-row gap-6 max-w-7xl mx-auto">
    <!-- Left Sidebar: Profile Summary -->
    <div class="w-full lg:w-1/3 xl:w-1/4 space-y-6 shrink-0">
        <!-- Main Info Card -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-600 relative">
                <a href="patients.php" class="absolute top-4 left-4 bg-black/20 hover:bg-black/40 backdrop-blur text-white w-9 h-9 flex items-center justify-center rounded-xl transition shadow-inner">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <button onclick="openEditProfile()" class="absolute top-4 right-4 bg-black/20 hover:bg-black/40 backdrop-blur text-white px-3 py-1.5 text-xs font-bold flex items-center gap-2 rounded-xl transition shadow-inner">
                    <i class="fa-solid fa-pen"></i> Edit Profile
                </button>
            </div>
            <div class="px-6 pb-8 relative">
                <div class="w-24 h-24 bg-white rounded-[1.25rem] shadow-md border-[6px] border-white flex items-center justify-center text-4xl font-black text-blue-600 absolute -top-12 z-10" id="dossier-initials">
                    <i class="fa-solid fa-user-injured text-slate-300"></i>
                </div>
                <div class="pt-14">
                    <div class="flex items-center justify-between mb-1.5">
                        <span id="dossier-status" class="text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider bg-slate-100 text-slate-500">Loading</span>
                        <span class="text-[11px] font-bold font-mono text-slate-400 bg-slate-50 px-2 py-1 rounded border border-slate-100" id="dossier-mrn">...</span>
                    </div>
                    <h2 id="dossier-name" class="text-2xl font-extrabold text-slate-900 leading-tight mb-4">Loading...</h2>
                    
                    <!-- Demographics Grid -->
                    <div class="grid grid-cols-3 gap-2 mb-6">
                        <div class="bg-slate-50 rounded-xl p-2 text-center border border-slate-100">
                            <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Age</span>
                            <span id="dossier-age" class="text-sm font-bold text-slate-700">--</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-2 text-center border border-slate-100">
                            <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Gender</span>
                            <span id="dossier-gender" class="text-sm font-bold text-slate-700">--</span>
                        </div>
                        <div class="bg-rose-50 rounded-xl p-2 text-center border border-rose-100">
                            <span class="block text-[9px] text-rose-400 font-bold uppercase tracking-wider mb-0.5">Blood</span>
                            <span id="dossier-blood" class="text-sm font-bold text-rose-700">--</span>
                        </div>
                    </div>
                    
                    <hr class="my-6 border-slate-100">
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-sm border border-blue-100/50 text-lg"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Phone</p>
                                <a href="#" id="dossier-phone-link" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">...</a>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 shadow-sm border border-amber-100/50 text-lg"><i class="fa-solid fa-user-tie"></i></div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Father's Name</p>
                                <p id="dossier-father" class="text-sm font-bold text-slate-800">...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-1 border-t border-slate-100">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-100/50 text-lg"><i class="fa-solid fa-truck-medical"></i></div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Emergency Contact</p>
                                <p id="dossier-emergency-name" class="text-sm font-bold text-slate-800 leading-tight">...</p>
                                <a href="#" id="dossier-emergency-phone-link" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition mt-0.5 block">...</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Visit Widget -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-black text-slate-900 mb-5 flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-indigo-500"></i> Latest Visit</h3>
            <div class="space-y-4">
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Department</span> 
                    <span id="dossier-last-dept" class="text-sm font-bold text-slate-800">...</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Doctor</span> 
                    <span id="dossier-last-doc" class="text-sm font-bold text-slate-800">...</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Type</span> 
                    <span id="dossier-last-type" class="text-sm font-bold text-slate-800">...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Content Area -->
    <div class="flex-1 min-w-0 flex flex-col h-full">
        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-1.5 flex gap-1.5 mb-6 overflow-x-auto shrink-0 sticky top-0 z-20">
            <button onclick="switchDossierTab('appointments')" id="tab-btn-appointments" class="dossier-tab active flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-blue-700 bg-blue-50 transition whitespace-nowrap">
                <i class="fa-solid fa-clipboard-list"></i> Appointments History
            </button>
            <button onclick="switchDossierTab('files')" id="tab-btn-files" class="dossier-tab flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition whitespace-nowrap">
                <i class="fa-solid fa-folder-open"></i> Medical Files & Scans
            </button>
        </div>

        <!-- Appointments -->
        <div id="tab-content-appointments" class="dossier-content flex-1">
            <div id="dossier-appointments-list" class="space-y-5">
                <!-- Skeleton Loader for Appointments -->
                <div class="animate-pulse space-y-4">
                    <div class="h-24 bg-slate-200 rounded-2xl"></div>
                    <div class="h-24 bg-slate-200 rounded-2xl"></div>
                </div>
            </div>
        </div>

        <!-- Files -->
        <div id="tab-content-files" class="dossier-content hidden flex-1">
            <div id="dossier-files-list" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4"></div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="modal-edit-profile" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-5 sm:p-8 relative my-8 shadow-2xl border border-slate-200">
        <button onclick="document.getElementById('modal-edit-profile').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        <h3 class="text-xl font-bold text-slate-900 mb-1">Edit Patient Profile</h3>
        <p class="text-xs text-slate-500 mb-5">Update full patient information and details.</p>

        <form id="edit-profile-form" onsubmit="submitEditProfile(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Personal Info -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">First Name *</label>
                <input type="text" id="ep-name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Surname *</label>
                <input type="text" id="ep-surname" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Father's Name *</label>
                <input type="text" id="ep-father" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Phone Number</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="ep-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            
            <!-- Demographics -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Age</label>
                <input type="number" id="ep-age" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Gender</label>
                <select id="ep-gender" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none bg-white">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Blood Group</label>
                <select id="ep-blood" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none bg-white">
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
                <input type="text" id="ep-em-name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Contact Phone</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="ep-em-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/50 outline-none">
            </div>

            <div class="md:col-span-2 pt-4">
                <button type="submit" id="ep-submit-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition text-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPatientData = null;
    const patientId = "<?php echo htmlspecialchars($patient_id); ?>";

    window.addEventListener('DOMContentLoaded', () => {
        loadPatientProfile(patientId);
    });

    function switchDossierTab(tabId) {
        document.querySelectorAll('.dossier-tab').forEach(el => {
            el.classList.remove('text-blue-700', 'bg-blue-50');
            el.classList.add('text-slate-500', 'hover:bg-slate-50', 'hover:text-slate-700');
        });
        document.querySelectorAll('.dossier-content').forEach(el => el.classList.add('hidden'));

        const btn = document.getElementById(`tab-btn-${tabId}`);
        btn.classList.add('text-blue-700', 'bg-blue-50');
        btn.classList.remove('text-slate-500', 'hover:bg-slate-50', 'hover:text-slate-700');
        document.getElementById(`tab-content-${tabId}`).classList.remove('hidden');
    }

    async function loadPatientProfile(patient_id) {
        try {
            const res = await fetch(`api/history.php?action=get_dossier&patient_id=${patient_id}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const p = data.dossier;
                currentPatientData = p;
                
                // Header Info
                document.getElementById('dossier-name').textContent = p.name + ' ' + p.surname;
                document.getElementById('dossier-initials').textContent = ((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : 'P')).toUpperCase();
                document.getElementById('dossier-mrn').textContent = p.id;
                document.getElementById('dossier-father').textContent = p.father_name || 'N/A';
                
                // Parse demographics
                let age = '--', gender = '--', blood = p.blood_group || '--';
                if (p.demographics) {
                    const parts = p.demographics.split(',').map(s => s.trim());
                    parts.forEach(part => {
                        if (part.endsWith('Y') || part.endsWith('y')) age = part;
                        if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
                        if (part.includes('+') || part.includes('-')) blood = part;
                    });
                }
                
                document.getElementById('dossier-age').textContent = age;
                document.getElementById('dossier-gender').textContent = gender;
                document.getElementById('dossier-blood').textContent = blood;
                
                // Phones with tel links
                const phoneLink = document.getElementById('dossier-phone-link');
                phoneLink.textContent = p.phone || 'N/A';
                phoneLink.href = p.phone ? `tel:${p.phone.replace(/[^0-9+]/g, '')}` : '#';
                
                document.getElementById('dossier-emergency-name').textContent = p.emergency_contact_name || 'Not provided';
                
                const emPhoneLink = document.getElementById('dossier-emergency-phone-link');
                emPhoneLink.textContent = p.emergency_contact_phone || 'No number';
                emPhoneLink.href = p.emergency_contact_phone ? `tel:${p.emergency_contact_phone.replace(/[^0-9+]/g, '')}` : '#';
                
                // Appt Status
                document.getElementById('dossier-status').textContent = p.status || 'Unknown';
                document.getElementById('dossier-status').className = (p.status && p.status.includes('Admit')) ? 'text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider bg-rose-100 text-rose-700 border border-rose-200' : 'text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200';
                
                // Last Visit Info
                document.getElementById('dossier-last-dept').textContent = p.dept && p.dept !== '-' ? p.dept : 'No records';
                document.getElementById('dossier-last-doc').textContent = p.doctor && p.doctor !== '-' ? p.doctor : 'No records';
                document.getElementById('dossier-last-type').textContent = p.type && p.type !== '-' ? p.type : 'No records';

                // Appointments Tab
                const apptsList = document.getElementById('dossier-appointments-list');
                if (p.appointments && p.appointments.length > 0) {
                    apptsList.innerHTML = p.appointments.map((appt, idx) => {
                        const dateParts = appt.date ? appt.date.split(' ')[0].split('-') : []; // format is usually YYYY-MM-DD
                        const year = dateParts.length === 3 ? dateParts[0] : '--';
                        const day = dateParts.length === 3 ? dateParts[2] : '--';
                        
                        return `
                        <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition duration-300">
                            <button onclick="toggleAppointmentDetails(${idx})" class="w-full flex items-center justify-between p-5 sm:p-6 bg-white hover:bg-slate-50 transition text-left group">
                                <div class="flex items-center gap-4 sm:gap-6 w-full">
                                    <div class="hidden sm:flex flex-col items-center justify-center bg-blue-50 text-blue-700 w-16 h-16 rounded-2xl shrink-0 border border-blue-100 relative">
                                        <span class="text-xl font-black leading-none">${day}</span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">${appt.date ? getMonthShort(appt.date) : 'MTH'}</span>
                                        <span class="absolute -bottom-2.5 bg-blue-600 text-white text-[8px] font-bold px-2 py-0.5 rounded-full shadow-sm">${year}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-slate-100 text-slate-600 border border-slate-200">${appt.appointment_code || ('APP-' + String(appt.id).padStart(4, '0'))}</span>
                                            <span class="sm:hidden text-xs font-bold text-slate-500 uppercase tracking-wider">${appt.date}</span>
                                        </div>
                                        <h4 class="text-base sm:text-lg font-extrabold text-slate-900 truncate group-hover:text-blue-700 transition">Visit for ${appt.dept} with ${appt.doctor_name || '-'}</h4>
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 group-hover:bg-blue-50 group-hover:border-blue-200 group-hover:text-blue-600 transition ml-4">
                                    <i id="icon-appt-${idx}" class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                                </div>
                            </button>
                            
                            <div id="content-appt-${idx}" class="hidden">
                                <div class="p-5 sm:p-6 sm:pt-2 border-t border-slate-100">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10">
                                        <!-- Timeline & Files -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                                <h5 class="text-sm font-extrabold text-slate-800 uppercase tracking-wide">Activity Timeline</h5>
                                            </div>
                                            <div class="space-y-4 mb-8 relative before:absolute before:inset-y-0 before:left-[11px] before:w-[2px] before:bg-slate-100 before:-z-10 z-0 pl-1">
                                                ${appt.timeline && appt.timeline.length > 0 ? appt.timeline.map(t => `
                                                    <div class="flex items-start gap-4">
                                                        <div class="w-5 h-5 rounded-full bg-white border-4 border-indigo-200 flex-shrink-0 mt-0.5 shadow-sm"></div>
                                                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex-1">
                                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-0.5">${t.time}</span>
                                                            <span class="text-sm font-semibold text-slate-700">${t.event}</span>
                                                        </div>
                                                    </div>
                                                `).join('') : '<span class="text-sm text-slate-400 italic pl-6">No timeline events</span>'}
                                            </div>

                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-6 h-6 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs"><i class="fa-solid fa-images"></i></div>
                                                <h5 class="text-sm font-extrabold text-slate-800 uppercase tracking-wide">Files / Scans</h5>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                ${appt.files && appt.files.length > 0 ? appt.files.map(f => {
                                                    const ext = f.file_path.split('.').pop().toLowerCase();
                                                    const isImg = ['jpg','jpeg','png','gif','webp'].includes(ext);
                                                    return `
                                                        <a href="${f.file_path}" target="_blank" class="block border border-slate-200 rounded-xl flex flex-col p-2 hover:border-teal-300 hover:shadow-md transition group bg-white">
                                                            ${isImg ? `<div class="aspect-video bg-slate-100 rounded-lg mb-2 overflow-hidden"><img src="${f.file_path}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" /></div>` : `<div class="aspect-video bg-slate-50 flex items-center justify-center rounded-lg mb-2 group-hover:bg-teal-50 transition border border-slate-100"><i class="fa-solid fa-file-pdf text-rose-400 text-3xl"></i></div>`}
                                                            <span class="text-xs truncate text-slate-700 px-1 font-bold" title="${f.title}">${f.title}</span>
                                                        </a>
                                                    `;
                                                }).join('') : '<span class="text-sm text-slate-400 italic col-span-2">No files attached to this visit.</span>'}
                                            </div>
                                        </div>
                                        
                                        <!-- Clinical Details -->
                                        <div class="lg:border-l lg:border-slate-100 lg:pl-10">
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-6 h-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs"><i class="fa-solid fa-stethoscope"></i></div>
                                                <h5 class="text-sm font-extrabold text-slate-800 uppercase tracking-wide">Diagnoses</h5>
                                            </div>
                                            <div class="flex flex-wrap gap-2 mb-8">
                                                ${appt.diagnoses && appt.diagnoses.length > 0 ? appt.diagnoses.map(d => `<span class="bg-rose-50 text-rose-700 text-sm px-3 py-1.5 rounded-lg border border-rose-100 font-bold shadow-sm">${d}</span>`).join('') : '<span class="text-sm text-slate-400 italic">No diagnoses</span>'}
                                            </div>

                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs"><i class="fa-solid fa-pills"></i></div>
                                                <h5 class="text-sm font-extrabold text-slate-800 uppercase tracking-wide">Prescriptions</h5>
                                            </div>
                                            <div class="space-y-3 mb-8">
                                                ${appt.medicines && appt.medicines.length > 0 ? appt.medicines.map(m => `
                                                    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-emerald-200 transition relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
                                                        <div class="font-extrabold text-slate-800 text-base mb-1">${m.name}</div>
                                                        <div class="text-slate-600 font-semibold text-sm flex items-center gap-2">
                                                            <span class="bg-slate-100 px-2 py-0.5 rounded text-xs">${m.dose}</span> 
                                                            <span class="text-slate-300">&bull;</span>
                                                            <span class="bg-slate-100 px-2 py-0.5 rounded text-xs">${m.freq}</span>
                                                        </div>
                                                        ${m.note ? `<div class="text-slate-500 mt-3 italic text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-100"><i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> ${m.note}</div>` : ''}
                                                    </div>
                                                `).join('') : '<span class="text-sm text-slate-400 italic">No prescriptions</span>'}
                                            </div>

                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs"><i class="fa-solid fa-notes-medical"></i></div>
                                                <h5 class="text-sm font-extrabold text-slate-800 uppercase tracking-wide">Doctor's Notes</h5>
                                            </div>
                                            <div class="text-sm text-slate-800 bg-amber-50/50 p-5 rounded-2xl border border-amber-200/60 leading-relaxed whitespace-pre-wrap shadow-inner font-medium">
                                                ${appt.doctor_notes || '<span class="italic text-slate-400">No notes provided.</span>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    }).join('');
                } else {
                    apptsList.innerHTML = '<div class="text-base text-slate-400 text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200 font-medium">No appointment history found for this patient.</div>';
                }

                // All Files tab
                const filesList = document.getElementById('dossier-files-list');
                if (p.files && p.files.length > 0) {
                    filesList.innerHTML = p.files.map(f => {
                        const ext = f.file_path.split('.').pop().toLowerCase();
                        const isImg = ['jpg','jpeg','png','gif','webp'].includes(ext);
                        const icon = isImg ? 'fa-image text-emerald-500' : 'fa-file-pdf text-rose-500';
                        return `
                            <a href="${f.file_path}" target="_blank" class="block border border-slate-200 rounded-2xl p-4 bg-white hover:border-blue-300 hover:shadow-xl transition-all duration-300 group">
                                <div class="aspect-square bg-slate-50 rounded-xl mb-4 flex items-center justify-center text-5xl group-hover:bg-blue-50/50 transition overflow-hidden">
                                    ${isImg ? `<img src="${f.file_path}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />` : `<i class="fa-solid ${icon} group-hover:scale-110 transition-transform duration-300"></i>`}
                                </div>
                                <div class="overflow-hidden">
                                    <h5 class="text-sm font-extrabold text-slate-800 truncate" title="${f.title}">${f.title}</h5>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider font-bold">${f.file_date}</p>
                                </div>
                            </a>
                        `;
                    }).join('');
                } else {
                    filesList.innerHTML = '<div class="col-span-full text-base text-slate-400 text-center py-20 bg-white rounded-3xl border border-dashed border-slate-200 font-medium">No medical files or scans uploaded yet.</div>';
                }

                switchDossierTab('appointments');
            } else {
                showToast('Error', data.message || 'Failed to load patient profile', 'error');
            }
        } catch (err) { 
            console.error(err); 
            showToast('Error', 'An unexpected error occurred', 'error');
        }
    }

    function openEditProfile() {
        if (!currentPatientData) return;
        
        document.getElementById('ep-name').value = currentPatientData.name || '';
        document.getElementById('ep-surname').value = currentPatientData.surname || '';
        document.getElementById('ep-father').value = currentPatientData.father_name || '';
        document.getElementById('ep-phone').value = currentPatientData.phone || '';
        
        // Try to parse age, gender, blood group from demographics if not directly available
        let age = '', gender = '', blood = currentPatientData.blood_group || '';
        if (currentPatientData.demographics) {
            const parts = currentPatientData.demographics.split(',').map(s => s.trim());
            parts.forEach(p => {
                if (p.endsWith('Y') || p.endsWith('y')) age = p.replace(/[^0-9]/g, '');
                if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
                if (p.includes('+') || p.includes('-')) blood = p;
            });
        }
        
        document.getElementById('ep-age').value = age;
        document.getElementById('ep-gender').value = gender;
        document.getElementById('ep-blood').value = blood;
        document.getElementById('ep-em-name').value = currentPatientData.emergency_contact_name || '';
        document.getElementById('ep-em-phone').value = currentPatientData.emergency_contact_phone || '';
        
        document.getElementById('modal-edit-profile').classList.remove('hidden');
    }

    async function submitEditProfile(e) {
        e.preventDefault();
        const btn = document.getElementById('ep-submit-btn');
        const ogText = btn.textContent;
        btn.textContent = 'Saving...';
        btn.disabled = true;

        const payload = {
            id: currentPatientData.id,
            name: document.getElementById('ep-name').value.trim(),
            surname: document.getElementById('ep-surname').value.trim(),
            father: document.getElementById('ep-father').value.trim(),
            phone: document.getElementById('ep-phone').value.trim(),
            age: document.getElementById('ep-age').value.trim(),
            gender: document.getElementById('ep-gender').value.trim(),
            blood_group: document.getElementById('ep-blood').value.trim(),
            emergency_contact_name: document.getElementById('ep-em-name').value.trim(),
            emergency_contact_phone: document.getElementById('ep-em-phone').value.trim()
        };

        try {
            const res = await fetch('api/patients.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                showToast('Success', 'Patient profile updated successfully.');
                document.getElementById('modal-edit-profile').classList.add('hidden');
                loadPatientProfile(currentPatientData.id); // reload data
            } else {
                showToast('Error', data.message || 'Failed to update patient', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'An unexpected error occurred', 'error');
        } finally {
            btn.textContent = ogText;
            btn.disabled = false;
        }
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

    // Helper for month formatting
    function getMonthShort(dateString) {
        const d = new Date(dateString);
        if (isNaN(d)) return 'MTH';
        return d.toLocaleString('default', { month: 'short' });
    }
</script>

<?php include 'includes/footer.php'; ?>

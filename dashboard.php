<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- Header -->
<div class="mb-8">
    <div class="relative overflow-hidden flex flex-col md:flex-row md:items-end justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] group">
        <!-- Live Heartbeat Canvas -->
        <canvas id="heartbeat-canvas" class="absolute inset-0 w-full h-full z-0 pointer-events-none opacity-40"></canvas>
        
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2 text-xs font-bold text-blue-600 tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-[10px] shadow-sm">
                    <i class="fa-solid fa-staff-snake"></i>
                </div>
                Clinical OS Workspace
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">Hospital Dashboard</h2>
            <p class="text-slate-500 mt-1 text-sm font-medium">Real-time overview of clinical operations, staff, and patient triage.</p>
        </div>
        <div class="relative z-10 flex items-center gap-2.5 text-xs font-bold text-slate-700 bg-white/80 backdrop-blur-sm px-4 py-2.5 rounded-xl border border-slate-200 self-start md:self-auto shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)] animate-pulse"></span>
            <span>Live System Active</span>
        </div>
    </div>
</div>

<!-- EMERGENCY NOTIFICATION BANNER (Displayed dynamically if emergency patients are active) -->
<div id="dashboard-emergency-banner" class="hidden mb-8 bg-white rounded-2xl p-5 sm:p-6 border border-rose-200 border-l-4 border-l-rose-500 shadow-sm relative overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shrink-0 border border-rose-100 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation animate-pulse"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-700 px-2.5 py-0.5 rounded-md">Critical Alert</span>
                    <span id="emergency-banner-count" class="text-xs font-bold text-slate-500">0 Active Patients</span>
                </div>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">Emergency Cases Requiring Immediate Medical Attention</h3>
                <p class="text-sm text-slate-500 mt-0.5" id="emergency-banner-subtext">The following patients are flagged with urgent priority in the hospital pipeline.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
            <a href="queue.php" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-sm px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-bolt text-rose-600"></i>
                <span>Manage Live Pipeline</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
    <!-- Dynamic Emergency Cards Grid -->
    <div id="emergency-banner-cards" class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
</div>

<!-- Live Stats Overview -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="apple-card p-6 flex flex-col gap-4 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 text-blue-500 opacity-10 text-8xl group-hover:scale-110 transition-transform duration-500"><i class="fa-solid fa-users"></i></div>
        <div class="flex justify-between items-start z-10">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Total</div>
        </div>
        <div class="z-10">
            <div id="stat-dash-patients" class="text-3xl font-black text-slate-900 tracking-tight">...</div>
            <div class="text-xs font-bold text-slate-500 mt-1">Registered Patients</div>
        </div>
    </div>

    <div class="apple-card p-6 flex flex-col gap-4 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 text-emerald-500 opacity-10 text-8xl group-hover:scale-110 transition-transform duration-500"><i class="fa-solid fa-bars-staggered"></i></div>
        <div class="flex justify-between items-start z-10">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-bars-staggered"></i>
            </div>
            <div class="bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Live</div>
        </div>
        <div class="z-10">
            <div id="stat-dash-queue" class="text-3xl font-black text-emerald-600 tracking-tight">...</div>
            <div class="text-xs font-bold text-slate-500 mt-1">Active in Pipeline</div>
        </div>
    </div>

    <div class="apple-card p-6 flex flex-col gap-4 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 text-indigo-500 opacity-10 text-8xl group-hover:scale-110 transition-transform duration-500"><i class="fa-solid fa-user-doctor"></i></div>
        <div class="flex justify-between items-start z-10">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div class="bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Staff</div>
        </div>
        <div class="z-10">
            <div id="stat-dash-doctors" class="text-3xl font-black text-indigo-600 tracking-tight">...</div>
            <div class="text-xs font-bold text-slate-500 mt-1">Medical Professionals</div>
        </div>
    </div>

    <div class="apple-card p-6 flex flex-col gap-4 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 text-rose-500 opacity-10 text-8xl group-hover:scale-110 transition-transform duration-500"><i class="fa-solid fa-bed-pulse"></i></div>
        <div class="flex justify-between items-start z-10">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-bed-pulse"></i>
            </div>
            <div class="bg-rose-50 text-rose-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Beds</div>
        </div>
        <div class="z-10">
            <div id="stat-dash-beds" class="text-3xl font-black text-rose-600 tracking-tight">...</div>
            <div class="text-xs font-bold text-slate-500 mt-1">Available Capacity</div>
        </div>
    </div>
</div>

<!-- Quick Actions Hub Grid -->
<div class="mb-8">
    <div class="flex items-center gap-3 mb-5">
        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
            <i class="fa-solid fa-compass"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900 tracking-tight">Quick Access Hub</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        <a href="index.php" class="apple-card p-6 block cursor-pointer group hover:bg-blue-50/30 hover:border-blue-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-blue-500/20 z-10 relative">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Reception Desk</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Register new patient profiles, verify details, and handle front-desk operations.</p>
        </a>

        <a href="book.php" class="apple-card p-6 block cursor-pointer group hover:bg-indigo-50/30 hover:border-indigo-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-indigo-500/20 z-10 relative">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Book Appointment</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Schedule direct consultations and manage doctor calendars efficiently.</p>
        </a>
        
        <a href="queue.php" class="apple-card p-6 block cursor-pointer group hover:bg-emerald-50/30 hover:border-emerald-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-emerald-500/20 z-10 relative">
                <i class="fa-solid fa-bars-staggered"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Live Pipeline</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Track patient tokens, lane progression, and real-time waiting areas.</p>
        </a>

        <a href="patients.php" class="apple-card p-6 block cursor-pointer group hover:bg-purple-50/30 hover:border-purple-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-purple-500/20 z-10 relative">
                <i class="fa-solid fa-address-book"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Patient Directory</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Omni-field search engine across all registered historical patients.</p>
        </a>

        <a href="doctors.php" class="apple-card p-6 block cursor-pointer group hover:bg-cyan-50/30 hover:border-cyan-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-cyan-500/20 z-10 relative">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Medical Staff</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Review doctor schedules, availability matrix, and specialization slots.</p>
        </a>

        <a href="beds.php" class="apple-card p-6 block cursor-pointer group hover:bg-rose-50/30 hover:border-rose-300 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-rose-500/20 z-10 relative">
                <i class="fa-solid fa-bed-pulse"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Bed Ward</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Live tracking of ICU, general wards, and occupancy metrics.</p>
        </a>

        <a href="history.php" class="apple-card p-6 block cursor-pointer group hover:bg-amber-50/30 hover:border-amber-300 transition-all duration-300 relative overflow-hidden sm:col-span-2 xl:col-span-2">
            <div class="absolute top-0 right-0 w-48 h-48 bg-amber-100 rounded-full blur-3xl opacity-40 -mr-20 -mt-20 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-amber-500/20 z-10 relative">
                <i class="fa-solid fa-folder-medical"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900 z-10 relative">Medical Records & Dossiers</h4>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Access complete patient histories, past prescriptions, lab reports, and clinical notes in a secure vault.</p>
        </a>
    </div>
</div>

<!-- 4-Section Pipeline Summary with Images and Concise Patient Info -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Department Pipeline Summary</h3>
            </div>
            <p class="text-sm text-slate-500">Live patient status and active counts across all 4 hospital clinical stages.</p>
        </div>
        <a href="queue.php" class="text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center gap-2 self-start sm:self-auto bg-blue-50 hover:bg-blue-100 px-5 py-2.5 rounded-xl border border-blue-200 transition shadow-sm hover:shadow-md">
            <span>Open Full Live Pipeline</span> <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>

    <!-- 4 Section Cards Grid -->
    <div id="dash-stages-summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="col-span-full py-12 text-center text-slate-400 text-sm flex items-center justify-center font-medium">
            <i class="fa-solid fa-circle-notch fa-spin mr-3 text-blue-500 text-xl"></i> Loading departmental stage summaries...
        </div>
    </div>
</div>

<!-- Active Queue Snapshot -->
<div class="apple-card p-6 sm:p-8 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Today's Live Queue List</h3>
            <p class="text-sm text-slate-500 mt-1">Patient chronology across consultation and waiting areas.</p>
        </div>
        <a href="queue.php" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-2 self-start sm:self-auto hover:bg-blue-50 px-3 py-2 rounded-lg transition">
            View Live Board <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
    
    <div id="dash-queue-preview" class="space-y-3">
        <div class="flex items-center justify-center h-32 text-slate-400 text-sm font-medium">
            <i class="fa-solid fa-spinner fa-spin mr-3 text-xl"></i> Loading live queue...
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadDashboardStats();
});

async function loadDashboardStats() {
    // 1. Fetch Patients Count
    try {
        const resPat = await fetch('api/patients.php?action=get_all');
        const dataPat = await resPat.json();
        if (dataPat.status === 'success') {
            document.getElementById('stat-dash-patients').textContent = dataPat.patients ? dataPat.patients.length : 0;
        }
    } catch(err) {
        console.warn('Patients fetch error:', err);
    }

    // 2. Fetch Queue & Render Pipeline Summaries
    try {
        const resQueue = await fetch('api/queue.php?action=get_queue');
        const dataQueue = await resQueue.json();
        if (dataQueue.status === 'success') {
            const pipeline = dataQueue.queue || dataQueue.pipeline || [];
            document.getElementById('stat-dash-queue').textContent = pipeline.length;

            renderEmergencyBanner(pipeline);
            renderQueuePreview(pipeline);
            renderDashboardStageSummary(pipeline);
        } else {
            renderQueuePreview([]);
            renderDashboardStageSummary([]);
        }
    } catch(err) {
        console.warn('Queue fetch error:', err);
        renderQueuePreview([]);
        renderDashboardStageSummary([]);
    }

    // 3. Fetch Doctors Count
    try {
        const resDoc = await fetch('api/doctors.php?action=get_hospital_doctors');
        const dataDoc = await resDoc.json();
        if (dataDoc.status === 'success') {
            document.getElementById('stat-dash-doctors').textContent = dataDoc.doctors ? dataDoc.doctors.length : 0;
        }
    } catch(err) {
        console.warn('Doctors fetch error:', err);
    }

    // 4. Fetch Beds Count
    try {
        const resBed = await fetch('api/beds.php?action=get_all');
        const dataBed = await resBed.json();
        if (dataBed.status === 'success') {
            const avail = dataBed.beds.filter(b => b.status === 'Available').length;
            document.getElementById('stat-dash-beds').textContent = `${avail} / ${dataBed.beds.length}`;
        }
    } catch(err) {
        console.warn('Beds fetch error:', err);
    }
}

function renderEmergencyBanner(pipeline) {
    const emergencyPatients = (pipeline || []).filter(p => p.type === 'Emergency Case' || p.type === 'Emergency');
    const banner = document.getElementById('dashboard-emergency-banner');
    const bannerCount = document.getElementById('emergency-banner-count');
    const bannerCards = document.getElementById('emergency-banner-cards');
    if (!banner || !bannerCount || !bannerCards) return;

    if (emergencyPatients.length > 0) {
        banner.classList.remove('hidden');
        bannerCount.textContent = `${emergencyPatients.length} Active Emergency ${emergencyPatients.length === 1 ? 'Case' : 'Cases'}`;
        bannerCards.innerHTML = emergencyPatients.map(p => {
            const apptCode = p.appointment_code || ('APP-' + String(p.id || p.appointment_id).padStart(4, '0'));
            return `
            <div class="bg-rose-50 rounded-xl p-3 border border-rose-200 text-xs flex flex-col justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="bg-rose-600 text-white font-black px-2 py-0.5 rounded text-[10px] shadow-sm">Token #${p.token_no || 1}</span>
                        <span class="font-mono text-[10px] font-bold text-rose-800 bg-rose-200/50 px-1.5 py-0.5 rounded border border-rose-200">${apptCode}</span>
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-sm truncate">${p.name} ${p.surname}</h4>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5 truncate">MRN: ${p.id} &bull; 🩸 ${p.blood_group || 'Unknown'}</p>
                    <div class="mt-2 text-[11px] bg-white rounded-lg p-1.5 space-y-0.5 border border-rose-100 shadow-sm">
                        <div class="truncate text-slate-700"><span class="text-slate-500 font-bold">Doctor:</span> <strong>Dr. ${p.doctor_name || p.doctor || 'Unassigned'}</strong></div>
                        <div class="truncate text-slate-700"><span class="text-slate-500 font-bold">Symptoms:</span> ${p.symptoms || 'Emergency triage'}</div>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-rose-200 flex items-center justify-between text-[10px]">
                    <span class="font-bold text-rose-800 bg-rose-200/50 px-2 py-0.5 rounded border border-rose-200">Stage ${p.stage}: ${p.status}</span>
                    <a href="queue.php" class="font-extrabold text-rose-700 bg-white hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition flex items-center gap-1 shadow-sm">
                        Triage Now <i class="fa-solid fa-arrow-right text-[8px]"></i>
                    </a>
                </div>
            </div>
            `;
        }).join('');
    } else {
        banner.classList.add('hidden');
    }
}

function renderQueuePreview(pipeline) {
    const previewContainer = document.getElementById('dash-queue-preview');
    if (!previewContainer) return;

    if (!pipeline || pipeline.length === 0) {
        previewContainer.innerHTML = `
            <div class="text-center py-8 text-slate-400 text-sm font-medium">
                <i class="fa-regular fa-circle-check text-4xl text-slate-200 block mb-3"></i>
                No patients waiting in queue right now.<br><span class="text-xs font-normal">Use Reception Desk or Book Appointment to check patients in.</span>
            </div>
        `;
        return;
    }

    previewContainer.innerHTML = pipeline.slice(0, 5).map(p => {
        const isEmerg = p.type === 'Emergency Case' || p.type === 'Emergency';
        const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
        return `
        <div class="flex items-center justify-between p-4 rounded-2xl ${isEmerg ? 'bg-rose-50/50 border-rose-200 hover:bg-rose-50' : 'bg-slate-50 hover:bg-white border-slate-100 hover:border-slate-200'} border transition-all duration-300 hover:shadow-md text-sm group cursor-pointer" onclick="window.location.href='queue.php'">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="${isEmerg ? 'bg-rose-500 shadow-rose-500/30' : 'bg-blue-500 shadow-blue-500/30'} text-white font-extrabold text-xs w-10 h-10 flex items-center justify-center rounded-xl shadow-md shrink-0">#${p.token_no || 1}</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h5 class="font-extrabold text-slate-900 truncate text-base group-hover:text-blue-600 transition-colors">${p.name} ${p.surname}</h5>
                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700">${apptCode}</span>
                        ${isEmerg ? '<span class="text-[10px] font-black uppercase bg-rose-500 text-white px-2 py-0.5 rounded-md animate-pulse shadow-sm">EMERGENCY</span>' : ''}
                    </div>
                    <p class="text-xs text-slate-500 font-medium truncate"><i class="fa-solid fa-hashtag text-[10px] opacity-50 mr-1"></i>${p.id} <span class="mx-1.5 text-slate-300">|</span> <i class="fa-solid fa-stethoscope text-[10px] opacity-50 mr-1"></i>${p.dept || 'General Department'}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-3">
                <span class="text-xs font-bold px-3 py-1 rounded-full ${isEmerg ? 'bg-rose-100 text-rose-700 border border-rose-200' : (p.stage === 4 ? 'bg-purple-100 text-purple-700' : (p.stage === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'))}">${p.status}</span>
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 group-hover:bg-blue-50 group-hover:border-blue-200 text-slate-400 group-hover:text-blue-600 flex items-center justify-center transition shadow-sm">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function renderDashboardStageSummary(pipeline) {
    const stages = [
        {
            stage: 1,
            name: "Checked-In",
            title: "Desk Check-In & MRN",
            image: "images/Checked-In.png",
            border: "border-blue-200 hover:border-blue-400",
            bg: "bg-blue-50/40 hover:bg-blue-50/70",
            tagBg: "bg-blue-600",
            badgeBg: "bg-blue-100 text-blue-800 border-blue-200"
        },
        {
            stage: 2,
            name: "Available",
            title: "Reception & Arrived",
            image: "images/Available.png",
            border: "border-teal-200 hover:border-teal-400",
            bg: "bg-teal-50/40 hover:bg-teal-50/70",
            tagBg: "bg-teal-600",
            badgeBg: "bg-teal-100 text-teal-800 border-teal-200"
        },
        {
            stage: 3,
            name: "Waiting Lounge",
            title: "Waiting Lounge Area",
            image: "images/Waiting Lounge.png",
            border: "border-amber-200 hover:border-amber-400",
            bg: "bg-amber-50/40 hover:bg-amber-50/70",
            tagBg: "bg-amber-600",
            badgeBg: "bg-amber-100 text-amber-800 border-amber-200"
        },
        {
            stage: 4,
            name: "Consulting",
            title: "Doctor Consulting Room",
            image: "images/Consulting.png",
            border: "border-purple-200 hover:border-purple-400",
            bg: "bg-purple-50/40 hover:bg-purple-50/70",
            tagBg: "bg-purple-600",
            badgeBg: "bg-purple-100 text-purple-800 border-purple-200"
        }
    ];

    const container = document.getElementById('dash-stages-summary');
    if (!container) return;

    container.innerHTML = stages.map(s => {
        const stagePatients = pipeline.filter(p => Number(p.stage) === s.stage);
        const count = stagePatients.length;
        
        let patientsHtml = '';
        if (count === 0) {
            patientsHtml = '<div class="text-slate-400 italic text-xs py-5 text-center flex items-center justify-center">No active patients</div>';
        } else {
            // Display concise patient info (not full dossier)
            patientsHtml = stagePatients.slice(0, 2).map(p => {
                const isEmerg = p.type === 'Emergency Case' || p.type === 'Emergency';
                const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
                return `
                <div class="p-2 rounded-xl ${isEmerg ? 'bg-rose-50 border border-rose-200 ring-1 ring-rose-300/40' : 'bg-white border border-slate-200/80'} shadow-xs text-xs mb-1.5 transition">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="font-extrabold text-[10px] ${isEmerg ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-700'} px-1.5 py-0.2 rounded shrink-0">#${p.token_no || 1}</span>
                            <span class="font-bold text-slate-900 text-xs truncate">${p.name} ${p.surname}</span>
                        </div>
                        <span class="font-mono text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200 shrink-0">${apptCode}</span>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-0.5">
                        <span class="truncate max-w-[120px]">${p.doctor || 'General OPD'}</span>
                        <span class="font-bold ${isEmerg ? 'text-rose-600' : 'text-slate-400'}">${isEmerg ? '🚨 Emergency' : (p.status || 'Active')}</span>
                    </div>
                </div>
                `;
            }).join('');

            if (count > 2) {
                patientsHtml += `<div class="text-[10px] font-bold text-slate-500 text-center pt-1">+${count - 2} more in this stage</div>`;
            }
        }

        return `
        <a href="queue.php" class="apple-card p-5 flex flex-col justify-between hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group border ${s.border} bg-white cursor-pointer rounded-3xl relative block overflow-hidden">
            <div class="absolute inset-0 opacity-[0.03] group-hover:opacity-10 transition-opacity duration-300 pointer-events-none" style="background-image: radial-gradient(circle at 100% 0%, currentColor 0%, transparent 50%); color: var(--tw-color-${s.tagBg.replace('bg-', '').split('-')[0]}-500);"></div>
            <div class="relative z-10">
                <!-- Section Image without cutting -->
                <div class="relative w-full h-36 rounded-2xl overflow-hidden mb-4 border border-slate-100 bg-slate-50 flex items-center justify-center">
                    <img src="${s.image}" alt="${s.name}" class="absolute inset-0 w-full h-full object-cover blur-sm opacity-20 scale-110 pointer-events-none" />
                    <img src="${s.image}" alt="${s.name}" class="relative h-[80%] max-w-full object-contain p-2 z-10 drop-shadow-md group-hover:scale-110 transition-transform duration-500" />
                    <div class="absolute bottom-2 left-2 right-2 px-2 py-1.5 rounded-lg bg-white/90 backdrop-blur-md text-[10px] text-slate-800 font-bold flex items-center justify-between z-20 shadow-sm border border-white">
                        <span class="truncate">${s.title}</span>
                        <span class="${s.tagBg} text-white px-1.5 py-0.5 rounded text-[9px] font-black shrink-0">Stage ${s.stage}</span>
                    </div>
                </div>

                <!-- Section Header and Count Badge -->
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                    <h4 class="font-extrabold text-base text-slate-900 group-hover:text-blue-600 transition-colors">${s.stage}. ${s.name}</h4>
                    <span class="text-xs font-mono font-bold px-3 py-1 rounded-full border ${s.badgeBg} shadow-sm">${count} ${count === 1 ? 'Patient' : 'Patients'}</span>
                </div>

                <!-- Concise Patient Info (Not Full) -->
                <div class="min-h-[80px]">
                    ${patientsHtml}
                </div>
            </div>

            <!-- Card Bottom Action Link -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-600 group-hover:text-blue-700 relative z-10">
                <span>View Stage Pipeline</span>
                <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </div>
        </a>
        `;
    }).join('');
}

// Live Heartbeat Animation
(function() {
    const canvas = document.getElementById('heartbeat-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    let w = canvas.width = canvas.offsetWidth;
    let h = canvas.height = canvas.offsetHeight;
    
    let x = 0;
    let y = h / 2;
    let isBeating = false;
    let beatPattern = [];
    
    window.addEventListener('resize', () => {
        w = canvas.width = canvas.offsetWidth;
        h = canvas.height = canvas.offsetHeight;
        y = h / 2;
        x = 0;
    });

    function step() {
        ctx.beginPath();
        ctx.moveTo(x, y);
        
        let speed = 4; // Increased speed
        
        // Randomly start a heartbeat (more frequent)
        if (!isBeating && Math.random() < 0.02) { 
            isBeating = true;
            let amp = h * 0.35; // height of beat
            beatPattern = [
                { dy: -amp*0.15, dx: 10 },
                { dy: 0, dx: 8 },
                { dy: amp*0.25, dx: 6 },
                { dy: -amp, dx: 10 },
                { dy: amp*0.4, dx: 14 },
                { dy: 0, dx: 8 },
                { dy: -amp*0.2, dx: 16 },
                { dy: 0, dx: 12 }
            ];
        }
        
        if (isBeating) {
            let currentSegment = beatPattern[0];
            if (currentSegment) {
                let targetY = (h / 2) + currentSegment.dy;
                y += (targetY - y) * 0.5; // ease towards target
                currentSegment.dx -= speed;
                if (currentSegment.dx <= 0) {
                    beatPattern.shift();
                }
            } else {
                isBeating = false;
                y = h / 2;
            }
        } else {
            // slight random noise for "normal" state
            y = (h / 2) + (Math.random() * 2 - 1);
        }
        
        x += speed;
        
        ctx.lineTo(x, y);
        // Medical blue color
        ctx.strokeStyle = '#3b82f6';
        ctx.lineWidth = 1.5;
        ctx.lineJoin = 'round';
        ctx.stroke();
        
        // fade out effect to create a trailing line
        ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
        ctx.fillRect(0, 0, w, h);
        
        // Loop back to start
        if (x > w) {
            x = 0;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            ctx.beginPath();
            ctx.moveTo(x, y);
        }
        
        requestAnimationFrame(step);
    }
    
    // Initial clear
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, w, h);
    step();
})();
</script>

<?php include 'includes/footer.php'; ?>

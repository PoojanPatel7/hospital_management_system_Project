<?php
// Unified Quick Reception & Patient Check-In Hub
// Provides instant QR scanning, patient search with live results, and 1-click check-in flow
?>
<div class="space-y-6">
    <!-- ================= HERO RECEPTION & PATIENT SEARCH CARD ================= -->
    <div class="relative overflow-hidden bg-gradient-to-br from-white via-indigo-50/20 to-white p-6 sm:p-8 rounded-[2.5rem] border-2 border-indigo-200/80 shadow-[0_15px_40px_rgb(99,102,241,0.08)] group">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-gradient-to-br from-indigo-100/50 via-purple-100/30 to-blue-100/40 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- Header Title -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-3 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-600 text-white shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-hospital-user text-[11px]"></i> Reception &amp; Patient Check-In
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500 bg-white px-2.5 py-0.5 rounded-full border border-slate-200">
                            Live OPD Desk
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                        Scan QR or Search Patient to Check-In
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-2xl mt-1">
                        Scan patient's QR code or search by Name, Phone, or MRN to view online appointments, assign token numbers by typing, and manage medical files.
                    </p>
                </div>

                <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                    <a href="queue.php" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs flex items-center gap-2 shadow-xs transition">
                        <i class="fa-solid fa-bars-staggered text-emerald-400"></i>
                        <span>Open Live Pipeline</span>
                    </a>
                </div>
            </div>

            <!-- Main Interactive Reception Bar: Big QR Button + Live Patient Search -->
            <div class="flex flex-col lg:flex-row items-stretch gap-3">
                <!-- 1. Big QR Scanner Button -->
                <button type="button" onclick="openQRScannerModal()" class="px-6 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-extrabold text-sm sm:text-base flex items-center justify-center gap-3 shadow-lg shadow-indigo-600/25 transition-all group shrink-0 cursor-pointer">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur text-white flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <span>Scan Patient QR Code</span>
                </button>

                <!-- 2. Big Live Patient Search Bar with Autocomplete Dropdown -->
                <div class="relative flex-1">
                    <div class="flex items-center bg-white border-2 border-indigo-200 hover:border-indigo-400 focus-within:border-indigo-600 rounded-2xl p-1.5 transition-all shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-indigo-500 ml-3 text-base"></i>
                        <input 
                            type="text" 
                            id="reception-search-input" 
                            placeholder="🔍 Type Patient Name, Mobile Number, or MRN..." 
                            autocomplete="off" 
                            class="w-full bg-transparent px-3 py-2.5 text-xs sm:text-sm md:text-base font-semibold text-slate-900 placeholder:text-slate-400 outline-none" 
                            oninput="handleReceptionSearch(this.value)" 
                            onkeydown="if(event.key==='Enter') executeReceptionSearch()" 
                        />
                        <button 
                            type="button" 
                            onclick="executeReceptionSearch()" 
                            class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-extrabold text-xs sm:text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer shrink-0"
                        >
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            <span>Search</span>
                        </button>
                    </div>

                    <!-- Instant Floating Autocomplete Results Dropdown -->
                    <div id="reception-search-results" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 max-h-96 overflow-y-auto"></div>
                </div>

                <!-- 3. New Patient Shortcut -->
                <a href="index.php" class="px-5 py-3.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs sm:text-sm border border-slate-200 shadow-xs flex items-center justify-center gap-2 transition shrink-0">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>New Patient Registration</span>
                </a>
            </div>

            <!-- Quick Reception Pipeline Counters -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <a href="queue.php" class="bg-white/80 backdrop-blur rounded-2xl p-3.5 border border-slate-200/80 hover:border-indigo-300 transition shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Checked-In</div>
                        <div class="text-xl font-black text-slate-900" id="hub-stat-total">...</div>
                    </div>
                </a>

                <a href="queue.php" class="bg-white/80 backdrop-blur rounded-2xl p-3.5 border border-slate-200/80 hover:border-amber-300 transition shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Waiting for Doctor</div>
                        <div class="text-xl font-black text-amber-600" id="hub-stat-waiting">...</div>
                    </div>
                </a>

                <a href="queue.php" class="bg-white/80 backdrop-blur rounded-2xl p-3.5 border border-slate-200/80 hover:border-emerald-300 transition shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">In Consultation</div>
                        <div class="text-xl font-black text-emerald-600" id="hub-stat-consulting">...</div>
                    </div>
                </a>

                <a href="queue.php" class="bg-white/80 backdrop-blur rounded-2xl p-3.5 border border-slate-200/80 hover:border-indigo-300 transition shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Pharmacy &amp; Done</div>
                        <div class="text-xl font-black text-indigo-600" id="hub-stat-completed">...</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- ================= TODAY'S ACTIVE OPD QUEUE SUMMARY TABLE ================= -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-ticket-simple"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Today's Active OPD Queue</h3>
                    <p class="text-[11px] text-slate-400">Click any patient to open their profile, view files, and assign/check token</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span id="hub-queue-count-badge" class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                    Loading queue...
                </span>
                <a href="queue.php" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1">
                    <span>Full Live Board</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <div id="hub-queue-table-container" class="overflow-x-auto">
            <div class="text-center text-slate-400 py-8 text-xs font-medium">
                <i class="fa-solid fa-circle-notch fa-spin text-indigo-500 text-lg mb-2 block"></i>
                Loading today's queue list...
            </div>
        </div>
    </div>
</div>

<script>
let receptionSearchDebounce = null;

function handleReceptionSearch(query) {
    clearTimeout(receptionSearchDebounce);
    const q = (query || '').trim();
    const resultsBox = document.getElementById('reception-search-results');
    if (!resultsBox) return;

    if (q.length < 1) {
        resultsBox.classList.add('hidden');
        resultsBox.innerHTML = '';
        return;
    }

    receptionSearchDebounce = setTimeout(async () => {
        try {
            resultsBox.classList.remove('hidden');
            resultsBox.innerHTML = `
                <div class="p-4 text-center text-xs text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-indigo-500 mr-1.5"></i> Searching patients...
                </div>
            `;

            const res = await fetch(`api/patients.php?action=search&q=${encodeURIComponent(q)}`);
            const data = await res.json();

            if (data.status === 'success' && data.patients && data.patients.length > 0) {
                let html = '<div class="divide-y divide-slate-100">';
                data.patients.forEach(p => {
                    const fullName = p.full_name || `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient';
                    const targetUrl = `patient_profile_qr.php?patient_id=${encodeURIComponent(p.id)}`;
                    html += `
                        <a href="${targetUrl}" class="flex items-center justify-between p-3.5 hover:bg-indigo-50/70 transition group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-black text-sm flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition">
                                    ${escapeHtml(p.name ? p.name.charAt(0).toUpperCase() : 'P')}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-black text-slate-900 group-hover:text-indigo-600 transition truncate">
                                        ${escapeHtml(fullName)}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium flex items-center gap-2 mt-0.5">
                                        <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200/60">${escapeHtml(p.id)}</span>
                                        <span>📞 ${escapeHtml(p.phone || 'No phone')}</span>
                                        ${p.age ? `<span>• Age: ${escapeHtml(p.age)}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white text-xs font-bold transition flex items-center gap-1.5 ml-2 shadow-2xs">
                                <span>Open Profile &amp; Check-In</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </span>
                        </a>
                    `;
                });
                html += '</div>';
                resultsBox.innerHTML = html;
            } else {
                resultsBox.innerHTML = `
                    <div class="p-6 text-center">
                        <div class="text-slate-400 text-xs font-semibold mb-2">No patient matching "${escapeHtml(q)}"</div>
                        <a href="index.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition shadow-xs">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Register New Patient</span>
                        </a>
                    </div>
                `;
            }
        } catch(err) {
            console.error('Error searching patients:', err);
            resultsBox.innerHTML = '<div class="p-4 text-center text-xs text-rose-500">Error connecting to patient search.</div>';
        }
    }, 200);
}

function executeReceptionSearch() {
    const input = document.getElementById('reception-search-input');
    if (!input) return;
    const q = input.value.trim();
    if (!q) return;

    // If query matches exact MRN format, jump straight there
    if (/^(CP-\d{4}-\d+|PAT-\d+)$/i.test(q)) {
        window.location.href = `patient_profile_qr.php?patient_id=${encodeURIComponent(q.toUpperCase())}`;
        return;
    }
    handleReceptionSearch(q);
}

// Close search results dropdown on outside click
document.addEventListener('click', function(e) {
    const input = document.getElementById('reception-search-input');
    const results = document.getElementById('reception-search-results');
    if (results && input && !input.contains(e.target) && !results.contains(e.target)) {
        results.classList.add('hidden');
    }
});

// Load today's active pipeline queue list
async function loadReceptionQueueList() {
    try {
        const res = await fetch('api/queue.php?action=get_queue');
        const data = await res.json();
        const container = document.getElementById('hub-queue-table-container');
        const countBadge = document.getElementById('hub-queue-count-badge');
        if (!container) return;

        if (data.status === 'success') {
            const list = data.queue || data.data || [];
            if (countBadge) countBadge.textContent = `${list.length} in Today's Pipeline`;

            // Update stats
            const waitingCount = list.filter(x => x.stage == 1).length;
            const consultingCount = list.filter(x => x.stage == 2).length;
            const completedCount = list.filter(x => x.stage >= 3).length;

            const stTotal = document.getElementById('hub-stat-total');
            const stWait = document.getElementById('hub-stat-waiting');
            const stCons = document.getElementById('hub-stat-consulting');
            const stComp = document.getElementById('hub-stat-completed');

            if (stTotal) stTotal.textContent = list.length;
            if (stWait) stWait.textContent = waitingCount;
            if (stCons) stCons.textContent = consultingCount;
            if (stComp) stComp.textContent = completedCount;

            // Sort strictly by token number ascending: lower token is first (up to down: #1, #2, #3...)
            list.sort((a, b) => {
                const tA = parseInt(a.token_number || a.token_no) || 999999;
                const tB = parseInt(b.token_number || b.token_no) || 999999;
                return tA - tB;
            });

            if (!list.length) {
                container.innerHTML = `
                    <div class="text-center py-8 text-slate-400 space-y-2">
                        <i class="fa-regular fa-calendar-check text-2xl text-slate-300"></i>
                        <p class="text-xs font-semibold">No patients currently in today's OPD queue.</p>
                        <p class="text-[11px] text-slate-400">Scan QR or search above to check-in the first patient!</p>
                    </div>
                `;
                return;
            }

            let rows = '';
            list.slice(0, 8).forEach(p => {
                const tokenStr = p.token_number ? String(p.token_number).padStart(2, '0') : '--';
                const pName = p.patient_name || 'Patient';
                const docName = p.doctor_name || 'Consultant Doctor';
                const statusName = p.status || 'Checked-In';
                const mrn = p.patient_id || '';
                const targetUrl = `patient_profile_qr.php?patient_id=${encodeURIComponent(mrn)}`;

                let stageBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">Waiting</span>';
                if (p.stage == 2) stageBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">In Consultation</span>';
                else if (p.stage >= 3) stageBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">Pharmacy / Done</span>';

                rows += `
                    <tr class="hover:bg-slate-50/80 transition border-b border-slate-100">
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 font-mono font-black text-xs shadow-2xs">
                                #${tokenStr}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-extrabold text-slate-900 text-xs sm:text-sm">${escapeHtml(pName)}</div>
                            <div class="font-mono text-[10px] text-slate-400">${escapeHtml(mrn)}</div>
                        </td>
                        <td class="py-3 px-4 text-xs font-bold text-slate-700">
                            Dr. ${escapeHtml(docName)}
                        </td>
                        <td class="py-3 px-4">
                            ${stageBadge}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="${targetUrl}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-xs transition shadow-2xs">
                                <i class="fa-solid fa-folder-open text-xs"></i>
                                <span>Profile &amp; Files</span>
                            </a>
                        </td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50/50">
                            <th class="py-2.5 px-4 rounded-l-xl">Token</th>
                            <th class="py-2.5 px-4">Patient &amp; MRN</th>
                            <th class="py-2.5 px-4">Consulting Doctor</th>
                            <th class="py-2.5 px-4">Current Stage</th>
                            <th class="py-2.5 px-4 text-right rounded-r-xl">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            `;
        }
    } catch(e) {
        console.error('Error loading reception queue:', e);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', loadReceptionQueueList);
</script>

<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital & I.C.U';
$user_name = $_SESSION['username'] ?? 'Gopalbhai';

if (isset($isStaff) && $isStaff) {
    include __DIR__ . '/includes/staff_dashboard_view.php';
    return;
}
?>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-[#fbfbfd] text-[#1d1d1f] font-sans pb-24">
    <!-- Header -->
    <div class="px-6 sm:px-12 pt-12 pb-8">
        <h1 class="text-[40px] leading-tight font-semibold tracking-tight text-[#1d1d1f]">
            <?php echo htmlspecialchars($hospital_name); ?>
        </h1>
        <p class="text-[21px] text-[#86868b] font-medium tracking-tight mt-1">
            Good morning, <?php echo htmlspecialchars($user_name); ?>.
        </p>
    </div>

    <!-- Main Container -->
    <div class="px-6 sm:px-12 space-y-6">
        
        <?php include __DIR__ . '/includes/reception_hub.php'; ?>

        <!-- Emergency Banner (Hidden by Default) -->
        <div id="dashboard-emergency-banner" class="hidden bg-rose-50/50 backdrop-blur-xl rounded-[24px] p-6 sm:p-8 border border-rose-100 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-rose-600">Urgent Attention</h3>
                    <p id="emergency-banner-count" class="text-sm text-rose-500 font-medium">0 Cases</p>
                </div>
                <a href="queue.php" class="px-5 py-2.5 bg-rose-600 text-white rounded-full font-medium text-sm hover:bg-rose-700 transition">Triage</a>
            </div>
            <div id="emergency-banner-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4"></div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="patients.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-solid fa-users text-xl"></i></div>
                <div id="stat-dash-patients" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Patients</div>
            </a>
            <a href="queue.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-solid fa-bars-staggered text-xl"></i></div>
                <div id="stat-dash-queue" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Live Pipeline</div>
            </a>
            <a href="appointments.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-regular fa-calendar text-xl"></i></div>
                <div id="stat-dash-appointments" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Appointments</div>
            </a>
            <a href="doctors.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-solid fa-stethoscope text-xl"></i></div>
                <div id="stat-dash-doctors" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Doctors</div>
            </a>
            <a href="beds.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-solid fa-bed text-xl"></i></div>
                <div id="stat-dash-beds" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Available Beds</div>
            </a>
            <a href="staff.php" class="bg-white rounded-[24px] p-6 shadow-sm hover:shadow-md transition">
                <div class="text-[#86868b] mb-4"><i class="fa-regular fa-id-badge text-xl"></i></div>
                <div id="stat-dash-staff" class="text-3xl font-semibold text-[#1d1d1f] tracking-tight">--</div>
                <div class="text-[13px] text-[#86868b] mt-1 font-medium">Staff On Duty</div>
            </a>
        </div>

        <!-- Charts Section -->
        <div class="bg-white rounded-[32px] p-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <h3 class="text-[24px] font-semibold tracking-tight text-[#1d1d1f]">Analytics Overview</h3>
                <div class="flex items-center gap-3">
                    <select id="chart-filter-doctor" onchange="refreshChartsData()" class="bg-[#f5f5f7] border-none rounded-full px-5 py-2.5 text-[13px] font-medium text-[#1d1d1f] focus:ring-0 outline-none appearance-none cursor-pointer">
                        <option value="all">All Doctors</option>
                    </select>
                    <div class="flex bg-[#f5f5f7] rounded-full p-1">
                        <button onclick="setTimelineFilter('7days')" id="btn-timeline-7days" class="px-4 py-1.5 rounded-full text-[13px] font-medium bg-white shadow-sm text-[#1d1d1f]">7D</button>
                        <button onclick="setTimelineFilter('14days')" id="btn-timeline-14days" class="px-4 py-1.5 rounded-full text-[13px] font-medium text-[#86868b] hover:text-[#1d1d1f]">14D</button>
                        <button onclick="setTimelineFilter('30days')" id="btn-timeline-30days" class="px-4 py-1.5 rounded-full text-[13px] font-medium text-[#86868b] hover:text-[#1d1d1f]">30D</button>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="h-64"><canvas id="canvas-timeline-chart"></canvas></div>
                    <div class="grid grid-cols-4 gap-4 mt-8 pt-6 border-t border-[#f5f5f7]">
                        <div><div class="text-[12px] text-[#86868b] font-medium">Total</div><div id="chart-stat-total" class="text-xl font-semibold mt-1">--</div></div>
                        <div><div class="text-[12px] text-[#86868b] font-medium">Completion</div><div id="chart-stat-rate" class="text-xl font-semibold mt-1">--</div></div>
                        <div><div class="text-[12px] text-[#86868b] font-medium">Peak</div><div id="chart-stat-peak" class="text-xl font-semibold mt-1">--</div></div>
                        <div><div class="text-[12px] text-[#86868b] font-medium">Daily Avg</div><div id="chart-stat-avg" class="text-xl font-semibold mt-1">--</div></div>
                    </div>
                </div>
                <div>
                    <div class="h-64"><canvas id="canvas-status-chart"></canvas></div>
                    <div id="chart-status-legend" class="mt-6 flex flex-wrap justify-center gap-3 text-[12px] text-[#86868b] font-medium"></div>
                </div>
            </div>
        </div>

        <!-- Queue & Pipeline -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[32px] p-8 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-[24px] font-semibold tracking-tight text-[#1d1d1f]">Active Stages</h3>
                    <a href="queue.php" class="text-[13px] font-medium text-[#0066cc]">View Full &rarr;</a>
                </div>
                <div id="dash-stages-summary" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-2 text-center text-[#86868b] py-8 text-[13px]">Loading stages...</div>
                </div>
            </div>
            <div class="bg-white rounded-[32px] p-8 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-[24px] font-semibold tracking-tight text-[#1d1d1f]">Waiting Queue</h3>
                    <span id="dash-queue-count-pill" class="text-[12px] font-medium bg-[#f5f5f7] text-[#1d1d1f] px-3 py-1 rounded-full">0</span>
                </div>
                <div id="dash-queue-preview" class="space-y-3 flex-1">
                    <div class="text-center text-[#86868b] py-8 text-[13px]">Loading queue...</div>
                </div>
            </div>
        </div>

        <!-- Appointments & Directory -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-[32px] p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-semibold tracking-tight text-[#1d1d1f]">Schedule</h3>
                </div>
                <div id="dash-today-appointments-list" class="space-y-4">
                    <div class="text-center text-[#86868b] py-4 text-[13px]">Loading...</div>
                </div>
            </div>
            <div class="bg-white rounded-[32px] p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-semibold tracking-tight text-[#1d1d1f]">On Duty</h3>
                </div>
                <div id="dash-doctors-roster-list" class="space-y-4">
                    <div class="text-center text-[#86868b] py-4 text-[13px]">Loading...</div>
                </div>
            </div>
            <div class="bg-white rounded-[32px] p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-semibold tracking-tight text-[#1d1d1f]">Recent Patients</h3>
                </div>
                <div class="relative mb-6">
                    <input type="text" id="dash-quick-patient-search" placeholder="Search..." onkeydown="if(event.key === 'Enter') navigatePatientSearch(this.value)" class="w-full bg-[#f5f5f7] border-none rounded-2xl px-4 py-2.5 text-[14px] outline-none">
                </div>
                <div id="dash-recent-patients-list" class="space-y-4">
                    <div class="text-center text-[#86868b] py-4 text-[13px]">Loading...</div>
                </div>
            </div>
        </div>
        
        <!-- App Grid -->
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-4 py-8">
            <a href="index.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-blue-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-user-plus"></i></div><span class="text-[11px] font-medium text-[#86868b]">Desk</span></a>
            <a href="book.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-indigo-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-calendar-check"></i></div><span class="text-[11px] font-medium text-[#86868b]">Book</span></a>
            <a href="queue.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-emerald-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-bars-staggered"></i></div><span class="text-[11px] font-medium text-[#86868b]">Queue</span></a>
            <a href="patients.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-purple-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-address-book"></i></div><span class="text-[11px] font-medium text-[#86868b]">Patients</span></a>
            <a href="doctors.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-cyan-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-user-doctor"></i></div><span class="text-[11px] font-medium text-[#86868b]">Doctors</span></a>
            <a href="doctor_slots.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-sky-500 text-xl group-hover:scale-105 transition"><i class="fa-regular fa-clock"></i></div><span class="text-[11px] font-medium text-[#86868b]">Slots</span></a>
            <a href="beds.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-rose-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-bed"></i></div><span class="text-[11px] font-medium text-[#86868b]">Wards</span></a>
            <a href="staff.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-teal-500 text-xl group-hover:scale-105 transition"><i class="fa-regular fa-id-badge"></i></div><span class="text-[11px] font-medium text-[#86868b]">Staff</span></a>
            <a href="backup.php" class="flex flex-col items-center gap-2 group"><div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-slate-500 text-xl group-hover:scale-105 transition"><i class="fa-solid fa-database"></i></div><span class="text-[11px] font-medium text-[#86868b]">System</span></a>
        </div>

    </div>
</div>

<div style="display:none;">
    <canvas id="canvas-department-chart"></canvas>
    <div id="dash-stage-bars"></div>
    <div id="dash-pipeline-total-indicator"></div>
    <span id="stat-dash-appointments-sub"></span>
    <span id="stat-dash-beds-sub"></span>
    <span id="stat-dash-staff-sub"></span>
    <div id="dash-staff-rate-badge"></div>
    <div id="dash-staff-total"></div>
    <div id="dash-staff-present"></div>
    <div id="dash-staff-late"></div>
    <div id="dash-staff-half"></div>
    <div id="dash-staff-absent"></div>
    <div id="dash-staff-roster-preview"></div>
    <div id="dash-backup-dbname"></div>
    <div id="dash-backup-tables"></div>
    <div id="dash-backup-count"></div>
    <div id="dash-backup-latest"></div>
    <div id="dash-backup-size"></div>
    <i id="chart-reload-icon"></i>
    <canvas id="heartbeat-canvas"></canvas>
    <div id="live-digital-clock"></div>
</div>

<script>
// JS Config
let timelineChart = null;
let statusChart = null;
let departmentChart = null;
let activeTimeline = '7days';

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
    fetchChartsData(activeTimeline);
    fetchDashboardLists();
    
    // Poll queue every 15s
    setInterval(() => {
        fetch('api/pipeline.php?action=get_active_pipeline')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    renderStagesSummary(data.data || []);
                    renderQueuePreview(data.data || []);
                    renderEmergencyBanner(data.data || []);
                }
            }).catch(e => console.warn(e));
    }, 15000);
});

async function fetchDashboardStats() {
    try {
        const res = await fetch('api/stats.php?action=get_dashboard_stats');
        const data = await res.json();
        if (data.status === 'success' && data.data) {
            const st = data.data;
            const el = id => { const e = document.getElementById(id); if (e) e.textContent = st[id.replace('stat-dash-', '')] || '0'; };
            ['patients','queue','appointments','doctors','beds','staff'].forEach(k => el('stat-dash-'+k));
        }
    } catch(e) {}
}

async function fetchChartsData(range) {
    try {
        const dId = document.getElementById('chart-filter-doctor')?.value || 'all';
        const res = await fetch(`api/stats.php?action=get_appointment_analytics&range=${range}&doctor_id=${dId}`);
        const data = await res.json();
        if (data.status === 'success' && data.data) {
            renderTimelineChart(data.data.timeline, data.data.summary);
            renderStatusChart(data.data.status_dist);
            updateDoctorsDropdown(data.data.doctors_list, dId);
        }
    } catch(e) {}
}

function refreshChartsData() {
    fetchChartsData(activeTimeline);
}

function setTimelineFilter(range) {
    activeTimeline = range;
    ['7days','14days','30days'].forEach(id => {
        const btn = document.getElementById('btn-timeline-'+id);
        if(btn) {
            if(id === range) {
                btn.className = 'px-4 py-1.5 rounded-full text-[13px] font-medium bg-white shadow-sm text-[#1d1d1f]';
            } else {
                btn.className = 'px-4 py-1.5 rounded-full text-[13px] font-medium text-[#86868b] hover:text-[#1d1d1f] bg-transparent';
            }
        }
    });
    fetchChartsData(range);
}

function updateDoctorsDropdown(docs, curId) {
    const sel = document.getElementById('chart-filter-doctor');
    if (!sel || !docs) return;
    sel.innerHTML = '<option value="all">All Doctors</option>' + 
        docs.map(d => `<option value="${d.id}" ${d.id == curId ? 'selected':''}>${d.name}</option>`).join('');
}

function renderTimelineChart(tl, summ) {
    document.getElementById('chart-stat-total').textContent = summ?.total || 0;
    document.getElementById('chart-stat-rate').textContent = (summ?.completion_rate || 0) + '%';
    document.getElementById('chart-stat-peak').textContent = summ?.peak_day_date || '--';
    document.getElementById('chart-stat-avg').textContent = summ?.daily_avg || 0;

    const ctx = document.getElementById('canvas-timeline-chart');
    if (!ctx) return;
    if (timelineChart) timelineChart.destroy();
    
    const labels = tl.map(i => i.date_short);
    const totals = tl.map(i => i.total);
    const comps = tl.map(i => i.completed);

    timelineChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Completed',
                    data: comps,
                    borderColor: '#34c759',
                    backgroundColor: 'rgba(52, 199, 89, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                },
                {
                    label: 'Total',
                    data: totals,
                    borderColor: '#007aff',
                    borderDash: [5,5],
                    fill: false,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#86868b', font: { size: 10 } } },
                y: { grid: { color: '#f5f5f7' }, ticks: { color: '#86868b', font: { size: 10 }, stepSize: 1, precision: 0 } }
            },
            interaction: { mode: 'index', intersect: false }
        }
    });
}

function renderStatusChart(st) {
    const ctx = document.getElementById('canvas-status-chart');
    const leg = document.getElementById('chart-status-legend');
    if (!ctx || !st) return;
    if (statusChart) statusChart.destroy();

    const labels = st.map(i => i.status);
    const data = st.map(i => i.count);
    const colors = ['#007aff', '#34c759', '#ff9500', '#ff3b30', '#af52de'];

    statusChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });

    if (leg) {
        leg.innerHTML = st.map((i, idx) => `
            <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full" style="background:${colors[idx%colors.length]}"></div>
                <span>${i.status} (${i.count})</span>
            </div>
        `).join('');
    }
}

async function fetchDashboardLists() {
    try {
        const res = await fetch('api/stats.php?action=get_dashboard_lists');
        const data = await res.json();
        if (data.status === 'success' && data.data) {
            renderList('dash-today-appointments-list', data.data.appointments, (i) => `
                <div class="flex items-center justify-between p-3 rounded-2xl bg-[#f5f5f7] mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center font-semibold text-[13px] shadow-sm">${i.token_no||'-'}</div>
                        <div>
                            <div class="font-semibold text-[14px] text-[#1d1d1f]">${i.patient_name}</div>
                            <div class="text-[12px] text-[#86868b]">Dr. ${i.doctor_name || 'General'} &bull; ${i.status}</div>
                        </div>
                    </div>
                </div>
            `);
            
            renderList('dash-doctors-roster-list', data.data.doctors, (i) => `
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-[#f5f5f7] mb-2">
                    <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center font-semibold text-[13px]"><i class="fa-solid fa-user-doctor"></i></div>
                    <div>
                        <div class="font-semibold text-[14px] text-[#1d1d1f]">${i.name}</div>
                        <div class="text-[12px] text-[#86868b]">${i.specialization} &bull; Dept: ${i.department_id}</div>
                    </div>
                </div>
            `);

            renderList('dash-recent-patients-list', data.data.recent_patients, (i) => `
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-[#f5f5f7] mb-2">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold text-[13px]"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <div class="font-semibold text-[14px] text-[#1d1d1f]">${i.name} ${i.surname}</div>
                        <div class="text-[12px] text-[#86868b]">ID: ${i.id} &bull; ${i.phone || 'No phone'}</div>
                    </div>
                </div>
            `);
        }
    } catch(e) {}
    
    // Also fetch pipeline
    fetch('api/pipeline.php?action=get_active_pipeline')
        .then(r => r.json())
        .then(d => {
            if(d.status === 'success') {
                renderStagesSummary(d.data || []);
                renderQueuePreview(d.data || []);
                renderEmergencyBanner(d.data || []);
            }
        }).catch(e=>{});
}

function renderList(id, arr, templateFn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (!arr || !arr.length) {
        el.innerHTML = '<div class="text-center text-[#86868b] py-4 text-[13px]">No records found.</div>';
        return;
    }
    el.innerHTML = arr.slice(0,4).map(templateFn).join('');
}

function renderStagesSummary(pipeline) {
    const stages = [
        { stage: 1, name: 'Check-In' },
        { stage: 2, name: 'Vitals & Waiting' },
        { stage: 3, name: 'Consulting' },
        { stage: 4, name: 'Pharmacy/Billing' }
    ];
    
    const container = document.getElementById('dash-stages-summary');
    if (!container) return;

    container.innerHTML = stages.map(s => {
        const count = pipeline.filter(p => Number(p.stage) === s.stage).length;
        return `
        <div class="bg-[#f5f5f7] rounded-[20px] p-5">
            <div class="text-[12px] font-medium text-[#86868b] uppercase tracking-wide">Stage ${s.stage}</div>
            <div class="text-[16px] font-semibold text-[#1d1d1f] mt-1">${s.name}</div>
            <div class="text-3xl font-semibold mt-3 text-[#1d1d1f]">${count}</div>
        </div>
        `;
    }).join('');
}

function renderQueuePreview(pipeline) {
    const el = document.getElementById('dash-queue-preview');
    const pill = document.getElementById('dash-queue-count-pill');
    if (pill) pill.textContent = pipeline.length;
    
    if (!el) return;
    if (!pipeline || !pipeline.length) {
        el.innerHTML = '<div class="text-center text-[#86868b] py-8 text-[13px]">Queue is empty.</div>';
        return;
    }
    
    el.innerHTML = pipeline.slice(0,5).map(p => {
        const isEm = (p.type === 'Emergency Case' || p.type === 'Emergency');
        return `
        <div class="flex items-center justify-between p-3 rounded-2xl ${isEm ? 'bg-rose-50' : 'bg-[#f5f5f7]'} mb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full ${isEm ? 'bg-rose-600 text-white' : 'bg-white text-[#1d1d1f]'} shadow-sm flex items-center justify-center font-semibold text-[13px]">${p.token_no||1}</div>
                <div>
                    <div class="font-semibold text-[14px] text-[#1d1d1f] flex items-center gap-2">
                        ${p.name} ${p.surname}
                        ${isEm ? '<span class="px-1.5 py-0.5 rounded bg-rose-200 text-rose-700 text-[9px] uppercase font-bold">Urgent</span>' : ''}
                    </div>
                    <div class="text-[12px] text-[#86868b]">Stage ${p.stage} &bull; ${p.status}</div>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function renderEmergencyBanner(pipeline) {
    const em = (pipeline || []).filter(p => p.type === 'Emergency Case' || p.type === 'Emergency');
    const banner = document.getElementById('dashboard-emergency-banner');
    const count = document.getElementById('emergency-banner-count');
    const cards = document.getElementById('emergency-banner-cards');
    
    if (!banner || !count || !cards) return;
    
    if (em.length > 0) {
        banner.classList.remove('hidden');
        count.textContent = em.length + (em.length===1 ? ' Case' : ' Cases');
        cards.innerHTML = em.map(p => `
            <div class="bg-white/80 rounded-[16px] p-4 border border-rose-100">
                <div class="font-semibold text-[14px] text-[#1d1d1f]">${p.name} ${p.surname}</div>
                <div class="text-[12px] text-[#86868b] mt-1">Stage ${p.stage}: ${p.status}</div>
                <div class="text-[12px] text-rose-600 mt-2 font-medium">Dr. ${p.doctor || 'Unassigned'}</div>
            </div>
        `).join('');
    } else {
        banner.classList.add('hidden');
    }
}

function navigatePatientSearch(val) {
    if(val.trim()) window.location.href = 'patients.php?search=' + encodeURIComponent(val.trim());
}
</script>

<?php include 'includes/footer.php'; ?>

<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital & I.C.U';
$user_name = $_SESSION['username'] ?? 'Administrator';
?>

<!-- Load Chart.js for High-Performance Interactive Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-8 pb-12">

    <!-- ========================================================================= -->
    <!-- 1. TOP HERO: HOSPITAL COMMAND BANNER & LIVE SYSTEM STATUS                 -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-[0_10px_35px_rgb(0,0,0,0.03)] group">
        <!-- Live Medical Oscilloscope / Heartbeat Canvas -->
        <canvas id="heartbeat-canvas" class="absolute inset-0 w-full h-full z-0 pointer-events-none opacity-30"></canvas>
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-gradient-to-br from-blue-100/50 to-indigo-100/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left: Hospital Greeting & Metadata -->
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-2xs">
                        <i class="fa-solid fa-hospital-user text-indigo-600"></i>
                        <span>Clinical Command Center</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live Operations Online</span>
                    </span>
                    <span id="live-digital-clock" class="text-xs font-mono font-bold text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                        --:--:--
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-tight">
                    <?php echo htmlspecialchars($hospital_name); ?>
                </h1>
                <p class="text-slate-500 mt-1.5 text-sm sm:text-base font-medium max-w-2xl leading-relaxed">
                    Centralized management for patient triage, doctor consultations, emergency care, and hospital operations.
                </p>
            </div>

            <!-- Right: Quick Operational Shortcuts -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="index.php" class="px-4 py-3 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 shadow-sm hover:shadow transition-all flex items-center gap-2 group/btn">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover/btn:scale-110 transition-transform">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <span>New Patient</span>
                </a>

                <a href="book.php" class="px-4 py-3 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 shadow-sm hover:shadow transition-all flex items-center gap-2 group/btn">
                    <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs group-hover/btn:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <span>Book Visit</span>
                </a>

                <a href="queue.php" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center gap-2.5">
                    <i class="fa-solid fa-bolt text-amber-300 animate-pulse"></i>
                    <span>Live Pipeline</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. EMERGENCY NOTIFICATION BANNER (DYNAMICALLY SHOWN ON URGENT CASES)      -->
    <!-- ========================================================================= -->
    <div id="dashboard-emergency-banner" class="hidden bg-white rounded-3xl p-6 border border-rose-200 border-l-8 border-l-rose-500 shadow-lg shadow-rose-500/5 relative overflow-hidden transition-all">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shrink-0 border border-rose-100 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation animate-bounce"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white px-2.5 py-0.5 rounded-md shadow-sm">
                            High Priority Alert
                        </span>
                        <span id="emergency-banner-count" class="text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md">
                            0 Urgent Cases
                        </span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">Emergency Cases Requiring Immediate Medical Attention</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5" id="emergency-banner-subtext">The following patients are flagged with urgent priority in the hospital pipeline.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                <a href="queue.php" class="bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs sm:text-sm px-5 py-3 rounded-2xl shadow-md shadow-rose-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-stethoscope"></i>
                    <span>Triage Emergency Pipeline</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        <!-- Dynamic Emergency Cards Grid -->
        <div id="emergency-banner-cards" class="mt-5 pt-4 border-t border-rose-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. MAIN TOP METRICS: EXPANSIVE, CLEAN & SPACIOUS KPI CARDS                -->
    <!-- ========================================================================= -->
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Key Hospital Metrics</h2>
            </div>
            <span class="text-xs font-bold text-slate-400">Live Synchronized</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-5">
            <!-- 1. Total Registered Patients -->
            <a href="patients.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-blue-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-xl shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100">
                        Directory
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-patients" class="text-3xl font-black text-slate-900 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">Total Patients</div>
                    <div class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center gap-1 group-hover:underline">
                        <span>View Profiles</span> &rarr;
                    </div>
                </div>
            </a>

            <!-- 2. Live Pipeline Queue -->
            <a href="queue.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-emerald-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-queue" class="text-3xl font-black text-emerald-600 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">Active Pipeline</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1 group-hover:underline">
                        <span>Open Triage Board</span> &rarr;
                    </div>
                </div>
            </a>

            <!-- 3. Today's Appointments -->
            <a href="appointments.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-indigo-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Today
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-appointments" class="text-3xl font-black text-indigo-600 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">Appointments</div>
                    <div id="stat-dash-appointments-sub" class="text-[11px] text-slate-400 font-semibold mt-1 truncate">
                        Scheduled visits
                    </div>
                </div>
            </a>

            <!-- 4. Doctors & Consultants -->
            <a href="doctors.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-purple-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center text-xl shadow-md shadow-purple-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-100">
                        Doctors
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-doctors" class="text-3xl font-black text-purple-600 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">Medical Specialists</div>
                    <div class="text-[11px] text-purple-600 font-semibold mt-1 flex items-center gap-1 group-hover:underline">
                        <span>Doctor Directory</span> &rarr;
                    </div>
                </div>
            </a>

            <!-- 5. Bed Ward Availability -->
            <a href="beds.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-rose-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center text-xl shadow-md shadow-rose-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-bed-pulse"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-100">
                        Wards
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-beds" class="text-3xl font-black text-rose-600 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">Available Beds</div>
                    <div id="stat-dash-beds-sub" class="text-[11px] text-slate-400 font-semibold mt-1 truncate">
                        ICU & General
                    </div>
                </div>
            </a>

            <!-- 6. Staff On Duty -->
            <a href="staff.php" class="apple-card p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group hover:border-teal-400 hover:-translate-y-1 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 text-white flex items-center justify-center text-xl shadow-md shadow-teal-500/20 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-teal-50 text-teal-700 border border-teal-100">
                        Staff
                    </span>
                </div>
                <div class="mt-4">
                    <div id="stat-dash-staff" class="text-3xl font-black text-teal-600 tracking-tight">...</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">On Duty Today</div>
                    <div id="stat-dash-staff-sub" class="text-[11px] text-teal-600 font-semibold mt-1 truncate">
                        100% Attendance
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. INTERACTIVE CLINICAL ANALYTICS & ADVANCED GRAPHS SECTION               -->
    <!-- ========================================================================= -->
    <div class="apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-6">
        
        <!-- Analytics Header & Filter Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Clinical Intelligence & Appointment Analytics</h3>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">
                    Interactive patient flow charts, doctor schedule workloads, and department distributions.
                </p>
            </div>

            <!-- Interactive Filters: Select Doctor & Timeline -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- Doctor Filter Dropdown -->
                <div class="relative min-w-[220px]">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-solid fa-user-doctor text-indigo-600"></i>
                    </div>
                    <select id="chart-filter-doctor" onchange="refreshChartsData()" class="w-full border border-slate-300 rounded-xl pl-9 pr-8 py-2 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition appearance-none shadow-2xs">
                        <option value="all">👨‍⚕️ All Medical Doctors</option>
                        <!-- Populated dynamically -->
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px]"></i>
                </div>

                <!-- Timeline Range Filter Tabs -->
                <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold">
                    <button type="button" onclick="setTimelineFilter('7days')" id="btn-timeline-7days" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-indigo-600 font-extrabold transition">
                        7 Days
                    </button>
                    <button type="button" onclick="setTimelineFilter('14days')" id="btn-timeline-14days" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        14 Days
                    </button>
                    <button type="button" onclick="setTimelineFilter('30days')" id="btn-timeline-30days" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        30 Days
                    </button>
                    <button type="button" onclick="setTimelineFilter('year')" id="btn-timeline-year" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        12 Months
                    </button>
                </div>

                <!-- Reload Button -->
                <button type="button" onclick="refreshChartsData()" title="Refresh Charts" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition text-xs shadow-2xs">
                    <i id="chart-reload-icon" class="fa-solid fa-arrows-rotate"></i>
                </button>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left Chart (8 Cols): Appointment Volume & Flow Trend -->
            <div class="lg:col-span-8 flex flex-col justify-between bg-slate-50/50 p-5 sm:p-6 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900" id="chart-main-title">Appointment Flow Over Time</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5" id="chart-main-subtitle">Showing total consultations vs completed visits</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="flex items-center gap-1.5 text-indigo-700">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span> Total Visits
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-700">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Completed
                        </span>
                    </div>
                </div>

                <!-- Canvas Container -->
                <div class="relative w-full h-72 sm:h-80">
                    <canvas id="canvas-timeline-chart"></canvas>
                </div>

                <!-- Dynamic Chart Summary Footer -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-4 border-t border-slate-200/80">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 text-center shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total In Period</span>
                        <span id="chart-stat-total" class="text-base font-black text-slate-900">0</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 text-center shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Completion Rate</span>
                        <span id="chart-stat-rate" class="text-base font-black text-emerald-600">0%</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 text-center shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Peak Volume Day</span>
                        <span id="chart-stat-peak" class="text-base font-black text-indigo-600 truncate block">N/A</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 text-center shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Daily Average</span>
                        <span id="chart-stat-avg" class="text-base font-black text-slate-800">0</span>
                    </div>
                </div>
            </div>

            <!-- Right Chart (4 Cols): Status Distribution Doughnut -->
            <div class="lg:col-span-4 flex flex-col justify-between bg-slate-50/50 p-5 sm:p-6 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900">Status Distribution</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Categorized breakdown of bookings</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800">Ratio</span>
                </div>

                <!-- Doughnut Canvas -->
                <div class="relative w-full h-56 flex items-center justify-center my-auto">
                    <canvas id="canvas-status-chart"></canvas>
                </div>

                <!-- Status Custom Legend -->
                <div id="chart-status-legend" class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-200/80 text-xs">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>

        <!-- Secondary Row: Department Workload & Stage Funnel -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
            
            <!-- Department Workload Chart -->
            <div class="bg-slate-50/50 p-5 sm:p-6 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900">Clinical Department Load</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Patient volume across medical specialties</p>
                    </div>
                    <span class="text-xs font-bold text-indigo-600 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
                        Specialties
                    </span>
                </div>
                <div class="relative w-full h-60">
                    <canvas id="canvas-department-chart"></canvas>
                </div>
            </div>

            <!-- Live Clinical Pipeline Stage Funnel -->
            <div class="bg-slate-50/50 p-5 sm:p-6 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900">Active Pipeline Progression</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">Real-time patient triage flow across hospital checkpoints</p>
                        </div>
                        <a href="queue.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <span>Open Pipeline</span> &rarr;
                        </a>
                    </div>

                    <!-- Visual Stage Bars -->
                    <div id="dash-stage-bars" class="space-y-3.5 my-2">
                        <!-- Populated dynamically via JS -->
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading stage funnel...
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check-double text-emerald-500"></i> Seamless triage transitions</span>
                    <span class="font-mono font-bold text-slate-700" id="dash-pipeline-total-indicator">0 Active</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 5. CATEGORY TYPE 1: LIVE PIPELINE & 4 CLINICAL STAGES                      -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Live Pipeline Stages & Active Queue</h3>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">Patient arrival, waiting room triage, and active doctor consultations.</p>
            </div>
            <a href="queue.php" class="text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-4 py-2.5 rounded-xl border border-emerald-200 transition flex items-center gap-2 self-start sm:self-auto shadow-2xs">
                <span>Manage Live Pipeline Board</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- 4-Stage Visual Cards Grid with Images -->
        <div id="dash-stages-summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="col-span-full py-12 text-center text-slate-400 text-sm flex items-center justify-center font-medium">
                <i class="fa-solid fa-circle-notch fa-spin mr-3 text-blue-500 text-xl"></i> Loading departmental stage summaries...
            </div>
        </div>

        <!-- Live Queue List Feed -->
        <div class="apple-card p-6 sm:p-7 bg-white border border-slate-200/90 shadow-sm mt-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Today's Live Queue Feed</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Current chronological queue of patients awaiting attention</p>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 self-start sm:self-auto" id="dash-queue-count-pill">
                    0 Waiting
                </span>
            </div>

            <div id="dash-queue-preview" class="space-y-3">
                <div class="flex items-center justify-center h-32 text-slate-400 text-sm font-medium">
                    <i class="fa-solid fa-spinner fa-spin mr-3 text-xl"></i> Loading live queue...
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. CATEGORY TYPE 2: TODAY'S APPOINTMENTS & CONSULTATION SCHEDULE          -->
    <!-- ========================================================================= -->
    <div class="apple-card p-6 sm:p-8 bg-white border border-slate-200/90 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Today's Appointment Schedule</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pre-booked visits and walk-in consultation slots for today</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="book.php" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3.5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> <span>Book Visit</span>
                </a>
                <a href="appointments.php" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2.5 rounded-xl border border-indigo-200 transition flex items-center gap-1.5">
                    <span>Full Schedule</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Appointments Preview List -->
        <div id="dash-today-appointments-list" class="space-y-3">
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading today's appointments...
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. CATEGORY TYPE 3 & 4: DOCTORS ROSTER & PATIENTS DIRECTORY               -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        <!-- Doctors & Medical Consultants Roster -->
        <div class="apple-card p-6 sm:p-7 bg-white border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-slate-900">Hospital Consultants & Doctors</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Active specialists and daily appointment capacity</p>
                        </div>
                    </div>
                    <a href="doctors.php" class="text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-2 rounded-xl border border-purple-200 transition">
                        Directory &rarr;
                    </a>
                </div>

                <div id="dash-doctors-roster-list" class="space-y-3">
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading doctors roster...
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="doctor_slots.php" class="text-purple-600 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-regular fa-clock"></i> <span>Manage Time Slots</span>
                </a>
                <a href="doctors.php" class="text-slate-600 hover:text-slate-900 font-bold">
                    View All Doctors &rarr;
                </a>
            </div>
        </div>

        <!-- Patients Directory Quick Lookup & Recent Registrations -->
        <div class="apple-card p-6 sm:p-7 bg-white border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-slate-900">Patient Directory & Recent Intake</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Latest registrations & quick dossier search</p>
                        </div>
                    </div>
                    <a href="patients.php" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl border border-blue-200 transition">
                        Directory &rarr;
                    </a>
                </div>

                <!-- Quick Patient Search Bar -->
                <div class="mb-4">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            id="dash-quick-patient-search" 
                            placeholder="Quick lookup by patient name, MRN, or phone..." 
                            onkeydown="if(event.key === 'Enter') navigatePatientSearch(this.value)"
                            class="w-full border border-slate-200 rounded-xl pl-9 pr-24 py-2.5 text-xs font-semibold bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/50 outline-none transition shadow-2xs">
                        <button type="button" onclick="navigatePatientSearch(document.getElementById('dash-quick-patient-search').value)" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg transition shadow-2xs">
                            Search
                        </button>
                    </div>
                </div>

                <!-- Recent Registrations Preview -->
                <div id="dash-recent-patients-list" class="space-y-2.5">
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading recent patients...
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="index.php" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-user-plus"></i> <span>Register New Patient</span>
                </a>
                <a href="history.php" class="text-slate-600 hover:text-slate-900 font-bold">
                    Medical Dossiers Vault &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 8. CATEGORY TYPE 5: STAFF ATTENDANCE & DATABASE HEALTH                    -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Staff Attendance Live Overview -->
        <div class="lg:col-span-2 apple-card p-6 sm:p-7 bg-white border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-clipboard-user"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-base font-extrabold text-slate-900">Hospital Staff Attendance Today</h4>
                                <span id="dash-staff-rate-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">100% Rate</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Real-time workforce attendance, shifts, and departmental duty roster.</p>
                        </div>
                    </div>
                    <a href="staff.php" class="text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 px-3.5 py-2 rounded-xl border border-teal-200 transition flex items-center gap-1.5 self-start sm:self-auto shadow-2xs">
                        <span>Manage Attendance</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Attendance Stats Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 my-4">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-center">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Staff</span>
                        <span class="text-lg font-black text-slate-800" id="dash-staff-total">0</span>
                    </div>
                    <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100 text-center">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 block">Present</span>
                        <span class="text-lg font-black text-emerald-800" id="dash-staff-present">0</span>
                    </div>
                    <div class="bg-amber-50/60 p-2.5 rounded-xl border border-amber-100 text-center">
                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Late</span>
                        <span class="text-lg font-black text-amber-800" id="dash-staff-late">0</span>
                    </div>
                    <div class="bg-sky-50/60 p-2.5 rounded-xl border border-sky-100 text-center">
                        <span class="text-[10px] uppercase font-bold text-sky-700 block">Half Day</span>
                        <span class="text-lg font-black text-sky-800" id="dash-staff-half">0</span>
                    </div>
                    <div class="bg-rose-50/60 p-2.5 rounded-xl border border-rose-100 text-center col-span-2 sm:col-span-1">
                        <span class="text-[10px] uppercase font-bold text-rose-700 block">Absent/Leave</span>
                        <span class="text-lg font-black text-rose-800" id="dash-staff-absent">0</span>
                    </div>
                </div>

                <!-- Mini On-Duty Roster List -->
                <div class="space-y-2" id="dash-staff-roster-preview">
                    <div class="text-center py-6 text-slate-400 text-xs font-medium">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading staff duty roster...
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-500"></i> Auto-synced daily check-in times</span>
                <a href="staff.php" class="font-bold text-teal-600 hover:text-teal-800">Open Staff Roster &rarr;</a>
            </div>
        </div>

        <!-- Right 1 Col: Database Health & Backup Overview -->
        <div class="lg:col-span-1 apple-card p-6 sm:p-7 bg-gradient-to-br from-white to-blue-50/40 border border-slate-200/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-slate-900">Database & Security</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Disaster recovery snapshots</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-mono">SQL Dump</span>
                </div>

                <div class="my-4 space-y-3">
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">Database Engine:</span>
                            <span class="font-mono font-bold text-slate-800" id="dash-backup-dbname">hospital_db</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">Live Tables:</span>
                            <span class="font-bold text-emerald-700" id="dash-backup-tables">16 Tables Online</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">Total Snapshots:</span>
                            <span class="font-bold text-slate-800" id="dash-backup-count">0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-semibold">Archive Storage:</span>
                            <span class="font-bold text-slate-800" id="dash-backup-size">0 KB</span>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50/70 border border-emerald-200/70 rounded-xl text-xs text-emerald-800">
                        <div class="font-bold flex items-center gap-1.5 mb-0.5">
                            <i class="fa-solid fa-shield-check text-emerald-600"></i> Protected Storage
                        </div>
                        <p class="text-[11px] text-emerald-700">Latest backup: <strong id="dash-backup-latest">Never</strong></p>
                    </div>
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <a href="backup.php" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Take Database Backup</span>
                </a>
                <a href="backup.php" class="w-full py-2 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition text-center block">
                    View Backup Archives
                </a>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 9. OTHER HOSPITAL MODULES AT BOTTOM: QUICK NAVIGATION HUB                 -->
    <!-- ========================================================================= -->
    <div class="pt-2">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner text-base">
                <i class="fa-solid fa-compass"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 tracking-tight">Hospital Operational Hub</h3>
                <p class="text-xs text-slate-500">Quick access to all hospital functional management workspaces.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            <!-- Reception Desk -->
            <a href="index.php" class="apple-card p-6 block cursor-pointer group hover:bg-blue-50/30 hover:border-blue-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-blue-500/20 z-10 relative">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Reception Desk</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Register new patient profiles, verify demographics, and issue patient identification codes.</p>
            </a>

            <!-- Book Appointment -->
            <a href="book.php" class="apple-card p-6 block cursor-pointer group hover:bg-indigo-50/30 hover:border-indigo-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-indigo-500/20 z-10 relative">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Book Appointment</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Schedule consultations, view doctor slots, and generate verified appointment booking tokens.</p>
            </a>
            
            <!-- Live Pipeline Board -->
            <a href="queue.php" class="apple-card p-6 block cursor-pointer group hover:bg-emerald-50/30 hover:border-emerald-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-emerald-500/20 z-10 relative">
                    <i class="fa-solid fa-bars-staggered"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Live Pipeline</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Real-time Kanban triage across Desk Check-in, Waiting Area, and Doctor Consulting Rooms.</p>
            </a>

            <!-- Patient Directory -->
            <a href="patients.php" class="apple-card p-6 block cursor-pointer group hover:bg-purple-50/30 hover:border-purple-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-purple-500/20 z-10 relative">
                    <i class="fa-solid fa-address-book"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Patient Directory</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Omni-field search engine across all registered historical patients and medical profiles.</p>
            </a>

            <!-- Doctors Directory -->
            <a href="doctors.php" class="apple-card p-6 block cursor-pointer group hover:bg-cyan-50/30 hover:border-cyan-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-cyan-500/20 z-10 relative">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Doctors Directory</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Manage hospital medical consultants, specialties, degrees, phone contacts, and rosters.</p>
            </a>

            <!-- Doctor Slots & Schedules -->
            <a href="doctor_slots.php" class="apple-card p-6 block cursor-pointer group hover:bg-sky-50/30 hover:border-sky-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-sky-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-sky-500/20 z-10 relative">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Doctor Time Slots</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Configure weekly doctor availability, slot intervals, duty hours, and consultation limits.</p>
            </a>

            <!-- Bed Ward Management -->
            <a href="beds.php" class="apple-card p-6 block cursor-pointer group hover:bg-rose-50/30 hover:border-rose-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-rose-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-rose-500/20 z-10 relative">
                    <i class="fa-solid fa-bed-pulse"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Bed Ward Management</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Live tracking of ICU, general wards, patient allocation, and bed turnover occupancy.</p>
            </a>

            <!-- Staff & Attendance -->
            <a href="staff.php" class="apple-card p-6 block cursor-pointer group hover:bg-teal-50/30 hover:border-teal-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-teal-100 rounded-full blur-2xl opacity-50 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-teal-500/20 z-10 relative">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Staff & Attendance</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Workforce registry, daily attendance check-ins, portal logins, and shift monitoring.</p>
            </a>

            <!-- Medical Dossiers Vault -->
            <a href="history.php" class="apple-card p-6 block cursor-pointer group hover:bg-amber-50/30 hover:border-amber-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-100 rounded-full blur-2xl opacity-40 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-amber-500/20 z-10 relative">
                    <i class="fa-solid fa-folder-medical"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Medical Records Vault</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Secure electronic health records vault: diagnoses, digital prescriptions, lab files, and history.</p>
            </a>

            <!-- Database Backup & Security -->
            <a href="backup.php" class="apple-card p-6 block cursor-pointer group hover:bg-blue-50/30 hover:border-blue-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 rounded-full blur-2xl opacity-40 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-blue-500/20 z-10 relative">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">Database Backup</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Generate SQL database snapshots, download backups, and protect against data loss.</p>
            </a>

            <!-- System User Guide -->
            <a href="guide.php" class="apple-card p-6 block cursor-pointer group hover:bg-sky-50/30 hover:border-sky-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-sky-100 rounded-full blur-2xl opacity-40 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-sky-500/20 z-10 relative">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">System User Guide</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Comprehensive manual and interactive onboarding tour covering every page, module, and feature.</p>
            </a>

            <!-- About Hospital -->
            <a href="about.php" class="apple-card p-6 block cursor-pointer group hover:bg-emerald-50/30 hover:border-emerald-300 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-100 rounded-full blur-2xl opacity-40 -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md shadow-emerald-500/20 z-10 relative">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 z-10 relative">About Hospital &amp; OS</h4>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed z-10 relative">Institutional overview, clinical departments, emergency capabilities, and infrastructure standards.</p>
            </a>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: DASHBOARD DATA SYNC, CHARTS & REAL-TIME UPDATES               -->
<!-- ========================================================================= -->
<script>
// Global Chart Instances
let timelineChart = null;
let statusChart = null;
let departmentChart = null;
let activeTimeline = '7days';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Start Live Clock
    initLiveClock();

    // 2. Load Core KPIs & Queue Data
    loadDashboardKPIs();

    // 3. Load Doctors Roster & Recent Patients
    loadRecentData();

    // 4. Load Chart Data
    loadChartData();

    // 5. Load Staff & Backup Stats
    loadStaffAndBackup();
});

// Digital Live Clock
function initLiveClock() {
    function update() {
        const el = document.getElementById('live-digital-clock');
        if (!el) return;
        const now = new Date();
        const opts = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        el.textContent = now.toLocaleDateString('en-US', opts);
    }
    update();
    setInterval(update, 1000);
}

// Timeline Filter Selector
function setTimelineFilter(timeline) {
    activeTimeline = timeline;
    ['7days', '14days', '30days', 'year'].forEach(t => {
        const btn = document.getElementById(`btn-timeline-${t}`);
        if (btn) {
            if (t === timeline) {
                btn.className = "px-3 py-1.5 rounded-lg bg-white shadow-xs text-indigo-600 font-extrabold transition";
            } else {
                btn.className = "px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition";
            }
        }
    });
    loadChartData();
}

function refreshChartsData() {
    const icon = document.getElementById('chart-reload-icon');
    if (icon) icon.classList.add('fa-spin');
    loadChartData().finally(() => {
        if (icon) icon.classList.remove('fa-spin');
    });
}

// -------------------------------------------------------------
// 1. LOAD DASHBOARD KPIS & LIVE QUEUE
// -------------------------------------------------------------
async function loadDashboardKPIs() {
    try {
        const res = await fetch('api/dashboard.php?action=get_overview');
        const data = await res.json();
        if (data.status === 'success' && data.kpis) {
            const k = data.kpis;
            
            // Patients
            const patEl = document.getElementById('stat-dash-patients');
            if (patEl) patEl.textContent = k.total_patients || 0;

            // Live Queue
            const qEl = document.getElementById('stat-dash-queue');
            if (qEl) qEl.textContent = k.live_queue || 0;

            // Doctors
            const docEl = document.getElementById('stat-dash-doctors');
            if (docEl) docEl.textContent = k.total_doctors || 0;

            // Beds
            const bedEl = document.getElementById('stat-dash-beds');
            const bedSubEl = document.getElementById('stat-dash-beds-sub');
            if (bedEl) bedEl.textContent = `${k.available_beds} / ${k.total_beds}`;
            if (bedSubEl) {
                const pct = k.total_beds > 0 ? Math.round((k.available_beds / k.total_beds) * 100) : 100;
                bedSubEl.textContent = `${pct}% Capacity Available`;
            }

            // Today's Appointments
            const appEl = document.getElementById('stat-dash-appointments');
            const appSubEl = document.getElementById('stat-dash-appointments-sub');
            if (appEl) appEl.textContent = k.today_appointments ? k.today_appointments.total : 0;
            if (appSubEl && k.today_appointments) {
                appSubEl.textContent = `${k.today_appointments.completed} Done &bull; ${k.today_appointments.scheduled + k.today_appointments.in_progress} Pending`;
            }

            // Staff Rate Badge
            if (k.staff) {
                const sEl = document.getElementById('stat-dash-staff');
                const sSubEl = document.getElementById('stat-dash-staff-sub');
                if (sEl) sEl.textContent = `${k.staff.on_duty} / ${k.staff.total}`;
                if (sSubEl) sSubEl.textContent = `${k.staff.rate}% Attendance Today`;
            }
        }
    } catch (err) {
        console.warn('Dashboard overview error:', err);
    }

    // Load full pipeline queue for live stage breakdown
    try {
        const resQueue = await fetch('api/queue.php?action=get_queue');
        const dataQueue = await resQueue.json();
        if (dataQueue.status === 'success') {
            const pipeline = dataQueue.queue || [];
            renderEmergencyBanner(pipeline);
            renderQueuePreview(pipeline);
            renderDashboardStageSummary(pipeline);
            renderPipelineFunnel(pipeline);
        } else {
            renderQueuePreview([]);
            renderDashboardStageSummary([]);
            renderPipelineFunnel([]);
        }
    } catch(err) {
        console.warn('Queue fetch error:', err);
        renderQueuePreview([]);
        renderDashboardStageSummary([]);
        renderPipelineFunnel([]);
    }
}

// -------------------------------------------------------------
// 2. LOAD RECENT DATA (DOCTORS, APPOINTMENTS, PATIENTS)
// -------------------------------------------------------------
async function loadRecentData() {
    try {
        const res = await fetch('api/dashboard.php?action=get_recent_data');
        const data = await res.json();
        if (data.status === 'success') {
            // Populate Doctors filter & roster
            populateDoctorFilters(data.doctors || []);
            renderDoctorsRoster(data.doctors || []);

            // Populate Today's Appointments
            renderTodayAppointments(data.today_appointments || []);

            // Populate Recent Patients
            renderRecentPatients(data.recent_patients || []);
        }
    } catch (err) {
        console.warn('Recent data error:', err);
    }
}

function populateDoctorFilters(doctors) {
    const select = document.getElementById('chart-filter-doctor');
    if (!select) return;

    const currentVal = select.value;
    select.innerHTML = '<option value="all">👨‍⚕️ All Medical Doctors</option>';
    doctors.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = `Dr. ${d.name} (${d.specialties || 'Specialist'})`;
        select.appendChild(opt);
    });
    if (currentVal && doctors.some(d => d.id === currentVal)) {
        select.value = currentVal;
    }
}

function renderDoctorsRoster(doctors) {
    const container = document.getElementById('dash-doctors-roster-list');
    if (!container) return;

    if (!doctors || doctors.length === 0) {
        container.innerHTML = `<div class="text-center py-6 text-slate-400 text-xs">No doctors registered yet.</div>`;
        return;
    }

    container.innerHTML = doctors.slice(0, 4).map(d => {
        const initials = d.name.replace(/^Dr\.\s*/i, '').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'DR';
        return `
        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/70 hover:bg-slate-100/70 transition border border-slate-100 text-xs">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm shadow-purple-600/20">
                    ${initials}
                </div>
                <div class="truncate">
                    <span class="font-bold text-slate-900 block truncate text-sm">Dr. ${d.name}</span>
                    <span class="text-[11px] text-purple-700 font-semibold truncate block">${d.specialties || 'General Medicine'}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                    ${d.today_appointments || 0} Visits Today
                </span>
                <a href="appointments.php?doctor_id=${d.id}" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-purple-50 hover:text-purple-700 text-slate-400 flex items-center justify-center transition" title="View Schedule">
                    <i class="fa-solid fa-calendar-day text-[10px]"></i>
                </a>
            </div>
        </div>
        `;
    }).join('');
}

function renderTodayAppointments(appts) {
    const container = document.getElementById('dash-today-appointments-list');
    if (!container) return;

    if (!appts || appts.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-regular fa-calendar-check text-3xl text-slate-200 block mb-2"></i>
                No appointments booked for today yet.<br>
                <a href="book.php" class="text-indigo-600 font-bold hover:underline inline-block mt-2">Book an Appointment &rarr;</a>
            </div>
        `;
        return;
    }

    container.innerHTML = appts.map(a => {
        let badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';
        if (a.status === 'In Consultation') badgeColor = 'bg-purple-100 text-purple-800 border-purple-200';
        else if (a.status.includes('Discharged')) badgeColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
        else if (a.status === 'Cancelled') badgeColor = 'bg-rose-100 text-rose-800 border-rose-200';

        return `
        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/70 hover:bg-slate-100/70 transition border border-slate-100 text-xs group cursor-pointer" onclick="window.location.href='appointments.php'">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-sm shadow-indigo-600/20">
                    #${a.token_no}
                </div>
                <div class="truncate">
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 text-sm group-hover:text-indigo-600 transition-colors">${a.patient_name} ${a.patient_surname}</span>
                        <span class="font-mono text-[10px] text-slate-500 bg-white border border-slate-200 px-1.5 py-0.2 rounded font-bold">${a.appointment_code}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                        <span><i class="fa-solid fa-user-doctor text-[10px] mr-1 opacity-50"></i>Dr. ${a.doctor_name || 'Assigned Specialist'}</span>
                        <span class="mx-1.5 text-slate-300">|</span>
                        <span><i class="fa-regular fa-clock text-[10px] mr-1 opacity-50"></i>${a.slot || 'Regular Slot'}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-2">
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border ${badgeColor}">
                    ${a.status}
                </span>
                <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all"></i>
            </div>
        </div>
        `;
    }).join('');
}

function renderRecentPatients(patients) {
    const container = document.getElementById('dash-recent-patients-list');
    if (!container) return;

    if (!patients || patients.length === 0) {
        container.innerHTML = `<div class="text-center py-6 text-slate-400 text-xs">No patients registered.</div>`;
        return;
    }

    container.innerHTML = patients.map(p => {
        return `
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/70 hover:bg-slate-100/70 transition border border-slate-100 text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-black text-[11px] flex items-center justify-center shrink-0">
                    ${(p.blood_group || 'O+')}
                </div>
                <div class="truncate">
                    <span class="font-bold text-slate-900 block truncate">${p.name} ${p.surname}</span>
                    <span class="text-[10px] text-slate-400 font-mono">${p.id} &bull; ${p.gender || 'Patient'} (${p.age || 'N/A'} yrs)</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">${p.reg_date || ''}</span>
                <a href="patients.php?search=${encodeURIComponent(p.id)}" class="text-blue-600 hover:text-blue-800 font-bold text-xs p-1">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>
        `;
    }).join('');
}

function navigatePatientSearch(query) {
    if (!query) return;
    window.location.href = `patients.php?search=${encodeURIComponent(query.trim())}`;
}

// -------------------------------------------------------------
// 3. LOAD CHART DATA (CHART.JS INTERACTIVE VISUALIZATIONS)
// -------------------------------------------------------------
async function loadChartData() {
    const docSelect = document.getElementById('chart-filter-doctor');
    const docId = docSelect ? docSelect.value : 'all';

    try {
        const res = await fetch(`api/dashboard.php?action=get_charts&doctor_id=${encodeURIComponent(docId)}&timeline=${encodeURIComponent(activeTimeline)}`);
        const data = await res.json();

        if (data.status === 'success') {
            // Update summary cards
            const summ = data.summary || {};
            document.getElementById('chart-stat-total').textContent = summ.total || 0;
            document.getElementById('chart-stat-rate').textContent = `${summ.completion_rate || 0}%`;
            document.getElementById('chart-stat-peak').textContent = summ.peak_day || 'N/A';
            document.getElementById('chart-stat-avg').textContent = summ.avg_per_day || 0;

            // Render Timeline Area Chart
            renderTimelineChart(data.timeline || []);

            // Render Status Distribution Doughnut
            renderStatusDoughnut(data.status_distribution || {});

            // Render Department Bar Chart
            renderDepartmentBarChart(data.department_distribution || []);
        }
    } catch (err) {
        console.warn('Charts load error:', err);
    }
}

function renderTimelineChart(timeline) {
    const canvas = document.getElementById('canvas-timeline-chart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const labels = timeline.map(t => t.label);
    const totalData = timeline.map(t => t.total);
    const completedData = timeline.map(t => t.completed);

    if (timelineChart) {
        timelineChart.destroy();
    }

    // Gradient Fills
    const gradTotal = ctx.createLinearGradient(0, 0, 0, 300);
    gradTotal.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
    gradTotal.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

    const gradCompleted = ctx.createLinearGradient(0, 0, 0, 300);
    gradCompleted.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
    gradCompleted.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

    timelineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Appointments',
                    data: totalData,
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    backgroundColor: gradTotal,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Completed Visits',
                    data: completedData,
                    borderColor: '#10b981',
                    borderWidth: 2.5,
                    backgroundColor: gradCompleted,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    }
                },
                x: {
                    ticks: {
                        font: { size: 11 }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function renderStatusDoughnut(statusCounts) {
    const canvas = document.getElementById('canvas-status-chart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const labels = Object.keys(statusCounts);
    const dataValues = Object.values(statusCounts);
    const total = dataValues.reduce((a, b) => a + b, 0);

    const colors = [
        '#f59e0b', // Waiting (Amber)
        '#8b5cf6', // In Consultation (Purple)
        '#10b981', // Completed (Emerald)
        '#3b82f6', // Pre-Booked (Blue)
        '#f43f5e'  // Cancelled (Rose)
    ];

    if (statusChart) {
        statusChart.destroy();
    }

    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: total === 0 ? [1] : dataValues,
                backgroundColor: total === 0 ? ['#e2e8f0'] : colors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: total > 0,
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.raw;
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${ctx.label}: ${val} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // Render Custom Legend
    const legendEl = document.getElementById('chart-status-legend');
    if (legendEl) {
        legendEl.innerHTML = labels.map((l, idx) => {
            const count = statusCounts[l] || 0;
            const col = colors[idx] || '#64748b';
            return `
            <div class="flex items-center justify-between p-1.5 rounded-lg bg-white border border-slate-100 shadow-2xs">
                <span class="flex items-center gap-1.5 text-slate-600 font-semibold truncate">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: ${col};"></span>
                    <span class="truncate">${l}</span>
                </span>
                <span class="font-mono font-black text-slate-800 ml-1">${count}</span>
            </div>
            `;
        }).join('');
    }
}

function renderDepartmentBarChart(departments) {
    const canvas = document.getElementById('canvas-department-chart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const labels = departments.map(d => d.department);
    const dataVals = departments.map(d => d.count);

    if (departmentChart) {
        departmentChart.destroy();
    }

    departmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Appointments',
                data: dataVals,
                backgroundColor: [
                    '#3b82f6', '#6366f1', '#8b5cf6', '#06b6d4',
                    '#10b981', '#f59e0b', '#ec4899', '#f43f5e'
                ],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: 'rgba(226, 232, 240, 0.6)' }
                },
                x: {
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 0
                    },
                    grid: { display: false }
                }
            }
        }
    });
}

function renderPipelineFunnel(pipeline) {
    const stages = [
        { stage: 1, name: 'Check-In', color: 'bg-blue-500' },
        { stage: 2, name: 'Available', color: 'bg-teal-500' },
        { stage: 3, name: 'Waiting Lounge', color: 'bg-amber-500' },
        { stage: 4, name: 'Doctor Consultation', color: 'bg-purple-500' }
    ];

    const total = pipeline.length;
    const totalIndicator = document.getElementById('dash-pipeline-total-indicator');
    if (totalIndicator) totalIndicator.textContent = `${total} Patients Active`;

    const container = document.getElementById('dash-stage-bars');
    if (!container) return;

    container.innerHTML = stages.map(s => {
        const count = pipeline.filter(p => Number(p.stage) === s.stage).length;
        const pct = total > 0 ? Math.round((count / total) * 100) : 0;

        return `
        <div class="space-y-1">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full ${s.color}"></span>
                    Stage ${s.stage}: ${s.name}
                </span>
                <span class="font-mono font-bold text-slate-800">${count} (${pct}%)</span>
            </div>
            <div class="w-full h-2 rounded-full bg-slate-200/70 overflow-hidden">
                <div class="${s.color} h-full rounded-full transition-all duration-500" style="width: ${pct}%;"></div>
            </div>
        </div>
        `;
    }).join('');
}

// -------------------------------------------------------------
// 4. LIVE PIPELINE 4-STAGES WITH IMAGES & CONCISE INFO
// -------------------------------------------------------------
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
            patientsHtml = stagePatients.slice(0, 2).map(p => {
                const isEmerg = p.type === 'Emergency Case' || p.type === 'Emergency';
                const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
                return `
                <div class="p-2.5 rounded-xl ${isEmerg ? 'bg-rose-50 border border-rose-200 ring-1 ring-rose-300/40' : 'bg-white border border-slate-200/80'} shadow-xs text-xs mb-2 transition">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="font-black text-[10px] ${isEmerg ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-700'} px-1.5 py-0.2 rounded shrink-0">#${p.token_no || 1}</span>
                            <span class="font-bold text-slate-900 text-xs truncate">${p.name} ${p.surname}</span>
                        </div>
                        <span class="font-mono text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200 shrink-0">${apptCode}</span>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-0.5">
                        <span class="truncate max-w-[120px] font-medium">${p.doctor || 'General OPD'}</span>
                        <span class="font-bold ${isEmerg ? 'text-rose-600 animate-pulse' : 'text-slate-400'}">${isEmerg ? '🚨 Urgent' : (p.status || 'Active')}</span>
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
            <div class="relative z-10">
                <!-- Section Image without clipping -->
                <div class="relative w-full h-36 rounded-2xl overflow-hidden mb-4 border border-slate-100 bg-slate-50 flex items-center justify-center">
                    <img src="${s.image}" alt="${s.name}" class="absolute inset-0 w-full h-full object-cover blur-sm opacity-20 scale-110 pointer-events-none" />
                    <img src="${s.image}" alt="${s.name}" class="relative h-[85%] max-w-full object-contain p-2 z-10 drop-shadow-md group-hover:scale-110 transition-transform duration-500" />
                    <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1.5 rounded-lg bg-white/90 backdrop-blur-md text-[10px] text-slate-800 font-bold flex items-center justify-between z-20 shadow-sm border border-white">
                        <span class="truncate">${s.title}</span>
                        <span class="${s.tagBg} text-white px-2 py-0.5 rounded text-[9px] font-black shrink-0">Stage ${s.stage}</span>
                    </div>
                </div>

                <!-- Section Header and Count Badge -->
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                    <h4 class="font-extrabold text-base text-slate-900 group-hover:text-blue-600 transition-colors">${s.stage}. ${s.name}</h4>
                    <span class="text-xs font-mono font-bold px-3 py-1 rounded-full border ${s.badgeBg} shadow-sm">${count} ${count === 1 ? 'Patient' : 'Patients'}</span>
                </div>

                <!-- Concise Patient Info -->
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

// -------------------------------------------------------------
// 5. LIVE QUEUE PREVIEW & EMERGENCY BANNER
// -------------------------------------------------------------
function renderQueuePreview(pipeline) {
    const previewContainer = document.getElementById('dash-queue-preview');
    const pill = document.getElementById('dash-queue-count-pill');
    if (pill) pill.textContent = `${pipeline.length} Waiting`;

    if (!previewContainer) return;

    if (!pipeline || pipeline.length === 0) {
        previewContainer.innerHTML = `
            <div class="text-center py-8 text-slate-400 text-sm font-medium">
                <i class="fa-regular fa-circle-check text-4xl text-slate-200 block mb-3"></i>
                No patients waiting in queue right now.<br>
                <span class="text-xs font-normal text-slate-400">Use Reception Desk or Book Appointment to check patients in.</span>
            </div>
        `;
        return;
    }

    previewContainer.innerHTML = pipeline.slice(0, 5).map(p => {
        const isEmerg = p.type === 'Emergency Case' || p.type === 'Emergency';
        const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
        return `
        <div class="flex items-center justify-between p-4 rounded-2xl ${isEmerg ? 'bg-rose-50/50 border-rose-200 hover:bg-rose-50' : 'bg-slate-50/70 hover:bg-white border-slate-100 hover:border-slate-200'} border transition-all duration-300 hover:shadow-md text-sm group cursor-pointer" onclick="window.location.href='queue.php'">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="${isEmerg ? 'bg-rose-500 shadow-rose-500/30' : 'bg-blue-500 shadow-blue-500/30'} text-white font-extrabold text-xs w-10 h-10 flex items-center justify-center rounded-xl shadow-md shrink-0">#${p.token_no || 1}</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h5 class="font-extrabold text-slate-900 truncate text-base group-hover:text-blue-600 transition-colors">${p.name} ${p.surname}</h5>
                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700">${apptCode}</span>
                        ${isEmerg ? '<span class="text-[10px] font-black uppercase bg-rose-500 text-white px-2 py-0.5 rounded-md animate-pulse shadow-sm">EMERGENCY</span>' : ''}
                    </div>
                    <p class="text-xs text-slate-500 font-medium truncate">
                        <i class="fa-solid fa-hashtag text-[10px] opacity-50 mr-1"></i>${p.id} 
                        <span class="mx-1.5 text-slate-300">|</span> 
                        <i class="fa-solid fa-user-doctor text-[10px] opacity-50 mr-1"></i>${p.doctor || 'General Consultation'}
                    </p>
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

function renderEmergencyBanner(pipeline) {
    const emergencyPatients = (pipeline || []).filter(p => p.type === 'Emergency Case' || p.type === 'Emergency');
    const banner = document.getElementById('dashboard-emergency-banner');
    const bannerCount = document.getElementById('emergency-banner-count');
    const bannerCards = document.getElementById('emergency-banner-cards');
    if (!banner || !bannerCount || !bannerCards) return;

    if (emergencyPatients.length > 0) {
        banner.classList.remove('hidden');
        bannerCount.textContent = `${emergencyPatients.length} Active Urgent ${emergencyPatients.length === 1 ? 'Case' : 'Cases'}`;
        bannerCards.innerHTML = emergencyPatients.map(p => {
            const apptCode = p.appointment_code || ('APP-' + String(p.id || p.appointment_id).padStart(4, '0'));
            return `
            <div class="bg-rose-50/80 rounded-2xl p-4 border border-rose-200 text-xs flex flex-col justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <span class="bg-rose-600 text-white font-black px-2.5 py-0.5 rounded text-[10px] shadow-sm">Token #${p.token_no || 1}</span>
                        <span class="font-mono text-[10px] font-bold text-rose-800 bg-rose-200/50 px-2 py-0.5 rounded border border-rose-200">${apptCode}</span>
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base truncate">${p.name} ${p.surname}</h4>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5 truncate">MRN: ${p.id} &bull; 🩸 ${p.blood_group || 'Unknown'}</p>
                    <div class="mt-2.5 text-[11px] bg-white rounded-xl p-2.5 space-y-1 border border-rose-100 shadow-2xs">
                        <div class="truncate text-slate-700"><span class="text-slate-500 font-bold">Doctor:</span> <strong>Dr. ${p.doctor || 'Unassigned'}</strong></div>
                        <div class="truncate text-slate-700"><span class="text-slate-500 font-bold">Symptoms:</span> ${p.symptoms || 'Urgent Medical Attention'}</div>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-rose-200 flex items-center justify-between text-[11px]">
                    <span class="font-bold text-rose-800 bg-rose-200/50 px-2 py-0.5 rounded">Stage ${p.stage}: ${p.status}</span>
                    <a href="queue.php" class="font-black text-rose-700 bg-white hover:bg-rose-100 border border-rose-200 px-3 py-1 rounded-xl transition flex items-center gap-1 shadow-2xs">
                        Triage Now &rarr;
                    </a>
                </div>
            </div>
            `;
        }).join('');
    } else {
        banner.classList.add('hidden');
    }
}

// -------------------------------------------------------------
// 6. STAFF ATTENDANCE & DATABASE SNAPSHOTS SYNC
// -------------------------------------------------------------
async function loadStaffAndBackup() {
    // 1. Staff Attendance
    try {
        const todayStr = new Date().toISOString().split('T')[0];
        const resStaffSumm = await fetch(`api/staff.php?action=get_daily_summary&date=${todayStr}`);
        const dataStaffSumm = await resStaffSumm.json();

        const resStaffList = await fetch(`api/staff.php?action=get_staff&date=${todayStr}`);
        const dataStaffList = await resStaffList.json();

        if (dataStaffSumm.status === 'success' && dataStaffSumm.summary) {
            const s = dataStaffSumm.summary;
            renderStaffDashboardWidget(s, dataStaffList.data || []);
        }
    } catch(err) {
        console.warn('Staff fetch error:', err);
    }

    // 2. Database Backup Snapshots
    try {
        const resBackup = await fetch('api/backup.php?action=list_backups');
        const dataBackup = await resBackup.json();
        if (dataBackup.status === 'success' && dataBackup.data) {
            renderBackupDashboardWidget(dataBackup.data);
        }
    } catch(err) {
        console.warn('Backup fetch error:', err);
    }
}

function renderStaffDashboardWidget(summary, list) {
    document.getElementById('dash-staff-total').textContent = summary.total_active_staff || 0;
    document.getElementById('dash-staff-present').textContent = summary.present || 0;
    document.getElementById('dash-staff-late').textContent = summary.late || 0;
    document.getElementById('dash-staff-half').textContent = summary.half_day || 0;
    document.getElementById('dash-staff-absent').textContent = (summary.absent || 0) + (summary.on_leave || 0);

    const rateBadge = document.getElementById('dash-staff-rate-badge');
    if (rateBadge) {
        rateBadge.textContent = `${summary.attendance_rate || 0}% Rate`;
    }

    const container = document.getElementById('dash-staff-roster-preview');
    if (!container) return;

    if (!list || list.length === 0) {
        container.innerHTML = `<div class="text-center py-5 text-slate-400 text-xs font-medium">No staff members registered.</div>`;
        return;
    }

    const previewList = list.slice(0, 4);
    container.innerHTML = previewList.map(s => {
        const initials = ((s.first_name || '')[0] || '') + ((s.last_name || '')[0] || '');
        const st = s.att_status || 'Unmarked';
        let statusBadge = 'bg-slate-100 text-slate-600';
        if (st === 'Present') statusBadge = 'bg-emerald-100 text-emerald-800';
        else if (st === 'Late') statusBadge = 'bg-amber-100 text-amber-800';
        else if (st === 'Half Day') statusBadge = 'bg-sky-100 text-sky-800';
        else if (st === 'Absent') statusBadge = 'bg-rose-100 text-rose-800';
        else if (st === 'On Leave') statusBadge = 'bg-purple-100 text-purple-800';

        return `
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/70 hover:bg-slate-100/70 transition border border-slate-100 text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-teal-600 text-white font-black text-[10px] flex items-center justify-center shrink-0">
                    ${initials.toUpperCase()}
                </div>
                <div class="truncate">
                    <span class="font-bold text-slate-900 block truncate">${s.first_name} ${s.last_name}</span>
                    <span class="text-[10px] text-slate-500 font-medium">${s.role} &bull; ${s.department}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${statusBadge}">${st}</span>
                <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">${s.check_in_time ? s.check_in_time.substring(0, 5) : '--:--'}</span>
            </div>
        </div>
        `;
    }).join('');
}

function renderBackupDashboardWidget(backupData) {
    const summ = backupData.summary || {};
    const backups = backupData.backups || [];

    const count = summ.total_backups || 0;
    const dbName = summ.database_name || 'hospital_db';
    const tablesCount = summ.db_tables_count || 16;
    const totalSize = summ.total_size_formatted || '0 KB';

    const dbNameEl = document.getElementById('dash-backup-dbname');
    if (dbNameEl) dbNameEl.textContent = dbName;

    const tablesEl = document.getElementById('dash-backup-tables');
    if (tablesEl) tablesEl.textContent = `${tablesCount} Tables Online`;

    const countEl = document.getElementById('dash-backup-count');
    if (countEl) countEl.textContent = `${count} ${count === 1 ? 'Snapshot' : 'Snapshots'}`;

    const sizeEl = document.getElementById('dash-backup-size');
    if (sizeEl) sizeEl.textContent = totalSize;

    const latestEl = document.getElementById('dash-backup-latest');
    if (latestEl) {
        if (backups.length > 0) {
            latestEl.textContent = `${backups[0].relative_time} (${backups[0].size_formatted})`;
        } else {
            latestEl.textContent = 'None yet (Create first snapshot)';
        }
    }
}

// -------------------------------------------------------------
// 7. SLEEK MEDICAL OSCILLOSCOPE HEARTBEAT ANIMATION
// -------------------------------------------------------------
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
        if (!canvas) return;
        w = canvas.width = canvas.offsetWidth;
        h = canvas.height = canvas.offsetHeight;
        y = h / 2;
        x = 0;
    });

    function step() {
        ctx.beginPath();
        ctx.moveTo(x, y);
        
        let speed = 4;
        
        if (!isBeating && Math.random() < 0.02) { 
            isBeating = true;
            let amp = h * 0.35;
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
                y += (targetY - y) * 0.5;
                currentSegment.dx -= speed;
                if (currentSegment.dx <= 0) {
                    beatPattern.shift();
                }
            } else {
                isBeating = false;
                y = h / 2;
            }
        } else {
            y = (h / 2) + (Math.random() * 2 - 1);
        }
        
        x += speed;
        
        ctx.lineTo(x, y);
        ctx.strokeStyle = '#4f46e5'; // Medical Indigo Pulse
        ctx.lineWidth = 1.75;
        ctx.lineJoin = 'round';
        ctx.stroke();
        
        ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
        ctx.fillRect(0, 0, w, h);
        
        if (x > w) {
            x = 0;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            ctx.beginPath();
            ctx.moveTo(x, y);
        }
        
        requestAnimationFrame(step);
    }
    
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, w, h);
    step();
})();
</script>

<?php include 'includes/footer.php'; ?>

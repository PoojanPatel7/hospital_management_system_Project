<?php 
require_once 'auth.php'; 
require_once 'db.php';
include 'includes/header.php'; 

$hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital & I.C.U';
$user_name = $_SESSION['username'] ?? 'Gopalbhai';

if (isset($isStaff) && $isStaff) {
    include __DIR__ . '/includes/staff_dashboard_view.php';
    return;
}

$current_hour = (int)date('H');
if ($current_hour < 12) {
    $greeting = "Good morning";
} elseif ($current_hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Check online appointment booking state
$online_booking_active = 1;
$res = $conn->query("SHOW COLUMNS FROM system_state LIKE 'online_booking_enabled'");
if ($res && $res->num_rows == 0) {
    $conn->query("ALTER TABLE system_state ADD COLUMN online_booking_enabled TINYINT(1) DEFAULT 1");
}
$stRes = $conn->query("SELECT online_booking_enabled FROM system_state WHERE id = 1");
if ($stRes && $stRow = $stRes->fetch_assoc()) {
    $online_booking_active = (int)($stRow['online_booking_enabled'] ?? 1);
}

// Fetch all staff members & today's attendance for the dashboard roster at Visit Analytics
$hospital_id = !empty($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 1;
$todayDate = date('Y-m-d');
$staffListQuery = "
    SELECT s.id, s.staff_code, s.first_name, s.last_name, s.role, s.department, s.shift, s.phone, s.status, s.avatar,
           a.status AS att_status, a.check_in_time
    FROM staff s
    LEFT JOIN staff_attendance a ON s.id = a.staff_id AND a.date = ?
    WHERE (s.hospital_id = ? OR s.hospital_id IS NULL)
    ORDER BY 
        CASE 
            WHEN a.status = 'Present' THEN 1
            WHEN a.status = 'Late' THEN 2
            WHEN a.status = 'Half Day' THEN 3
            WHEN a.status = 'On Leave' THEN 4
            WHEN a.status = 'Absent' THEN 5
            ELSE 6
        END ASC,
        s.first_name ASC
";
$staffStmt = $conn->prepare($staffListQuery);
$staffStmt->bind_param("si", $todayDate, $hospital_id);
$staffStmt->execute();
$staffResult = $staffStmt->get_result();
$dashboardStaffList = [];
$staffCounts = ['total' => 0, 'present' => 0, 'absent' => 0, 'leave' => 0, 'off_duty' => 0];

while ($st = $staffResult->fetch_assoc()) {
    $staffCounts['total']++;
    $att = $st['att_status'] ?? '';
    if ($att === 'Present' || $att === 'Late') {
        $staffCounts['present']++;
    } elseif ($att === 'Absent') {
        $staffCounts['absent']++;
    } elseif ($att === 'On Leave' || $att === 'Half Day') {
        $staffCounts['leave']++;
    } else {
        $staffCounts['off_duty']++;
    }
    $dashboardStaffList[] = $st;
}
?>

<!-- GSAP Animation Engine & Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Antigravity Spatial & Weightless UI Principles */
.dash-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.85);
    border-radius: 24px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.03), 0 4px 10px -2px rgba(15, 23, 42, 0.02);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
    will-change: transform;
}
.dash-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.06), 0 6px 14px -3px rgba(15, 23, 42, 0.03);
    border-color: rgba(203, 213, 225, 0.9);
}
.dash-glass {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.dash-metric-number {
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.03em;
}
#employee-roster-container::-webkit-scrollbar {
    width: 4px;
}
#employee-roster-container::-webkit-scrollbar-track {
    background: transparent;
}
#employee-roster-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
#employee-roster-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 font-sans">
    
    <!-- ================= 1. HEADER (MINIMALIST & CLEAN) ================= -->
    <div class="dash-card dash-glass p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                <?php echo htmlspecialchars($hospital_name); ?>
            </h1>
            <p class="text-sm sm:text-base text-slate-500 font-semibold tracking-tight mt-1">
                <?php echo $greeting; ?>, <?php echo htmlspecialchars($user_name); ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 self-start md:self-auto">
            <!-- Live Clock & Date Pill -->
            <div class="px-4 py-2 rounded-full bg-slate-100/80 border border-slate-200 flex items-center gap-2.5 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="dash-live-time" class="text-xs sm:text-sm font-black font-mono text-slate-800">--:--:--</span>
                <span class="text-slate-300">&bull;</span>
                <span id="dash-live-date" class="text-xs font-semibold text-slate-500">Today</span>
            </div>

            <!-- Online Booking Toggle Button -->
            <button 
                id="btn-dash-booking-toggle" 
                type="button" 
                onclick="toggleDashboardBooking()" 
                class="px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-2 border cursor-pointer active:scale-95 shadow-2xs <?php echo $online_booking_active ? 'bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100' : 'bg-rose-50 border-rose-200 text-rose-800 hover:bg-rose-100'; ?>"
            >
                <i id="dash-booking-toggle-icon" class="fa-solid <?php echo $online_booking_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-rose-600'; ?> text-sm"></i>
                <span id="dash-booking-toggle-text">Online Booking: <?php echo $online_booking_active ? 'Active' : 'Paused'; ?></span>
            </button>
        </div>
    </div>

    <!-- Emergency Case Alert Banner (Appears only when emergency case exists) -->
    <div id="dash-emergency-alert" class="hidden dash-card p-4 sm:p-5 bg-rose-50 border-rose-200 transition-all">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center text-sm font-bold animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-rose-700">Urgent Triage Required</span>
                    <div id="dash-emergency-count" class="text-sm font-bold text-slate-900">0 Critical Cases in Queue</div>
                </div>
            </div>
            <a href="queue.php" class="px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                Open Queue &rarr;
            </a>
        </div>
    </div>

    <!-- ================= 2. RECEPTION & QUICK CHECK-IN HERO CARD ================= -->
    <div class="dash-card p-6 sm:p-8 space-y-6">
        <!-- Card Top Bar: Title & Primary Shortcuts -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm font-black shadow-xs">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Reception Desk</h2>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="queue.php" class="px-4 py-2 rounded-full bg-slate-900 hover:bg-black text-white text-xs font-bold transition flex items-center gap-2 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Pipeline</span>
                </a>
                <a href="index.php" class="px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200/80">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>New Patient</span>
                </a>
            </div>
        </div>

        <!-- Primary Interactive Action: QR Scanner + Patient Search Bar -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 sm:gap-4">
            <!-- 1. Big QR Scanner Button -->
            <button 
                type="button" 
                onclick="openQRScannerModal()" 
                class="px-6 py-4 rounded-2xl sm:rounded-full bg-slate-900 hover:bg-black text-white font-extrabold text-xs sm:text-sm flex items-center justify-center gap-3 shadow-sm transition-all cursor-pointer shrink-0 active:scale-95"
            >
                <div class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white text-xs">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span>Scan Patient QR</span>
            </button>

            <!-- 2. Big Live Patient Search Bar with Instant Autocomplete Dropdown -->
            <div class="relative flex-1">
                <div class="flex items-center bg-slate-50 hover:bg-white focus-within:bg-white border-2 border-slate-200 hover:border-slate-300 focus-within:border-slate-900 rounded-2xl sm:rounded-full p-1.5 pl-5 transition-all shadow-2xs">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm mr-2.5"></i>
                    <input 
                        type="text" 
                        id="reception-search-input" 
                        placeholder="Search patient by Name, Phone, or MRN..." 
                        autocomplete="off" 
                        class="w-full bg-transparent text-xs sm:text-sm md:text-base font-semibold text-slate-900 placeholder:text-slate-400 outline-none" 
                        oninput="handleReceptionSearch(this.value)" 
                        onkeydown="if(event.key==='Enter') executeReceptionSearch()" 
                    />
                    <button 
                        type="button" 
                        onclick="executeReceptionSearch()" 
                        class="px-5 py-2.5 rounded-full bg-slate-900 hover:bg-black text-white font-extrabold text-xs transition flex items-center gap-1.5 cursor-pointer shrink-0 active:scale-95"
                    >
                        <span>Search</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>
                </div>

                <!-- Instant Autocomplete Dropdown -->
                <div id="reception-search-results" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 max-h-80 overflow-y-auto"></div>
            </div>
        </div>

        <!-- 3. Minimal Flow Counters Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            <a href="queue.php" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Arrived</div>
                    <div id="hub-stat-total" class="text-xl sm:text-2xl font-black text-slate-900 dash-metric-number">--</div>
                </div>
                <div class="w-8 h-8 rounded-xl bg-white text-slate-700 flex items-center justify-center text-xs shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </a>

            <a href="queue.php" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiting</div>
                    <div id="hub-stat-waiting" class="text-xl sm:text-2xl font-black text-amber-600 dash-metric-number">--</div>
                </div>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-2xs">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
            </a>

            <a href="queue.php" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Consulting</div>
                    <div id="hub-stat-consulting" class="text-xl sm:text-2xl font-black text-blue-600 dash-metric-number">--</div>
                </div>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-2xs">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </a>

            <a href="queue.php" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 transition border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Completed</div>
                    <div id="hub-stat-completed" class="text-xl sm:text-2xl font-black text-emerald-600 dash-metric-number">--</div>
                </div>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-2xs">
                    <i class="fa-solid fa-check"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- ================= 3. CORE METRICS KPI GRID ================= -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Patients -->
        <a href="patients.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Patients</span>
                <i class="fa-solid fa-users text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-patients" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Total Registered</div>
        </a>

        <!-- Live Queue -->
        <a href="queue.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pipeline</span>
                <i class="fa-solid fa-bars-staggered text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-queue" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">In Today's Flow</div>
        </a>

        <!-- Appointments -->
        <a href="appointments.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bookings</span>
                <i class="fa-regular fa-calendar-check text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-appointments" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Scheduled Today</div>
        </a>

        <!-- Doctors -->
        <a href="doctors.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doctors</span>
                <i class="fa-solid fa-user-doctor text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-doctors" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Active Consultants</div>
        </a>

        <!-- Beds -->
        <a href="beds.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Beds</span>
                <i class="fa-solid fa-bed text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-beds" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Available Now</div>
        </a>

        <!-- Staff -->
        <a href="staff.php" class="dash-card p-5 block group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Staff</span>
                <i class="fa-regular fa-id-badge text-slate-400 group-hover:text-slate-900 transition text-sm"></i>
            </div>
            <div id="kpi-staff" class="text-2xl sm:text-3xl font-black text-slate-900 dash-metric-number">--</div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Present on Duty</div>
        </a>
    </div>

    <!-- ================= 4. OPERATIONAL WORKFLOW: QUEUE PIPELINE & AI CONTROLS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: OPD Pipeline Stages & Queue Preview (7 cols) -->
        <div class="lg:col-span-7 dash-card p-6 sm:p-8 space-y-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Active Pipeline</h3>
                    </div>
                    <a href="queue.php" class="text-xs font-bold text-slate-600 hover:text-black flex items-center gap-1 transition">
                        <span>Full Board</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>

                <!-- 4 Stages Boxes -->
                <div id="dash-pipeline-stages" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Stage 1</div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Check-In</div>
                        <div id="stage-count-1" class="text-2xl font-black text-slate-900 mt-2 dash-metric-number">0</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Stage 2</div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Waiting</div>
                        <div id="stage-count-2" class="text-2xl font-black text-slate-900 mt-2 dash-metric-number">0</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Stage 3</div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Doctor</div>
                        <div id="stage-count-3" class="text-2xl font-black text-slate-900 mt-2 dash-metric-number">0</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Stage 4</div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5">Pharmacy</div>
                        <div id="stage-count-4" class="text-2xl font-black text-slate-900 mt-2 dash-metric-number">0</div>
                    </div>
                </div>

                <!-- Live Waiting Queue Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[11px] font-bold text-slate-400 uppercase border-b border-slate-100">
                                <th class="py-2 px-3">Token</th>
                                <th class="py-2 px-3">Patient</th>
                                <th class="py-2 px-3">Doctor</th>
                                <th class="py-2 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="dash-live-queue-body">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-xs text-slate-400 font-medium">
                                    Loading active queue...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: AI Assistant & Fast Operations (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Minimal AI Assistant Card -->
            <div class="dash-card p-6 sm:p-7 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-violet-600 text-white flex items-center justify-center text-xs font-black shadow-xs">
                            <i class="fa-solid fa-brain"></i>
                        </div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">AI Assistant</h3>
                    </div>
                    
                    <span id="ai-telemetry-badge" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                        Checking...
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">Hospital AI Model</span>
                            <span id="ai-model-status-pill" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-200 text-slate-600">OFF</span>
                        </div>
                        <div id="ai-model-serving-name" class="text-[11px] font-mono text-slate-400 mt-0.5">hms-ai:latest</div>
                    </div>
                    <!-- Model ON / OFF Toggle Button -->
                    <button 
                        type="button" 
                        id="btn-ai-toggle-quick" 
                        onclick="toggleAiEngineModel()" 
                        class="px-4 py-2 rounded-full font-bold text-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95 shadow-2xs border bg-slate-900 text-white hover:bg-black"
                    >
                        <i id="btn-ai-icon" class="fa-solid fa-power-off text-xs"></i>
                        <span id="btn-ai-text">Turn ON</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" onclick="openChatbotModal()" class="py-3 px-4 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-800 text-xs font-bold transition flex items-center justify-center gap-2 border border-violet-100 cursor-pointer">
                        <i class="fa-solid fa-message"></i>
                        <span>Open Chatbot</span>
                    </button>

                    <a href="ai_training_dashboard.php" class="py-3 px-4 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-2 border border-slate-200">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>AI Lab</span>
                    </a>
                </div>
            </div>

            <!-- Quick Data Services Card -->
            <div class="dash-card p-6 sm:p-7 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 tracking-tight">System &amp; Data</h3>
                    <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Healthy</span>
                </div>

                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-database text-slate-400"></i>
                        <span>Database Status</span>
                    </div>
                    <span class="text-slate-900 font-mono">Connected</span>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <a href="backup.php" class="py-3 px-4 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition text-center flex items-center justify-center gap-2 shadow-2xs">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>Backup DB</span>
                    </a>
                    <a href="restore.php" class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition text-center flex items-center justify-center gap-2 border border-slate-200">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        <span>Restore DB</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= 5. ANALYTICS & TIMELINE OVERVIEW + EMPLOYEE STATUS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <!-- Left Column: Visit Analytics (lg:col-span-7) -->
        <div id="visit-analytics-card" class="dash-card p-4 sm:p-6 lg:p-7 space-y-5 lg:col-span-7 flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center justify-between w-full sm:w-auto">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Visit Analytics</h3>
                                <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Patient footfall &amp; completion trends</p>
                            </div>
                        </div>
                        <!-- Phone view shortcut to jump to staff status -->
                        <a href="#dash-employee-status-card" class="lg:hidden inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="fa-solid fa-id-badge text-blue-500"></i>
                            <span>Staff (<?php echo $staffCounts['total']; ?>) &darr;</span>
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-full border border-slate-200/80 self-start sm:self-auto">
                        <button type="button" onclick="setChartTimeline('7days')" id="btn-chart-7d" class="px-3 py-1 rounded-full text-xs font-bold bg-white text-slate-900 shadow-2xs">7 Days</button>
                        <button type="button" onclick="setChartTimeline('14days')" id="btn-chart-14d" class="px-3 py-1 rounded-full text-xs font-bold text-slate-500 hover:text-slate-900">14 Days</button>
                        <button type="button" onclick="setChartTimeline('30days')" id="btn-chart-30d" class="px-3 py-1 rounded-full text-xs font-bold text-slate-500 hover:text-slate-900">30 Days</button>
                    </div>
                </div>

                <div class="h-60 sm:h-72 w-full mt-2">
                    <canvas id="canvas-dashboard-timeline"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 pt-4 border-t border-slate-100">
                <div class="p-1 sm:p-0">
                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Visits</div>
                    <div id="chart-stat-total" class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 dash-metric-number">--</div>
                </div>
                <div class="p-1 sm:p-0">
                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Completion</div>
                    <div id="chart-stat-rate" class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 dash-metric-number">--%</div>
                </div>
                <div class="p-1 sm:p-0">
                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peak Volume</div>
                    <div id="chart-stat-peak" class="text-base sm:text-xl font-black text-slate-900 mt-0.5 truncate">--</div>
                </div>
                <div class="p-1 sm:p-0">
                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Daily Avg</div>
                    <div id="chart-stat-avg" class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 dash-metric-number">--</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Employee Status Card (lg:col-span-5) -->
        <div id="dash-employee-status-card" class="dash-card p-4 sm:p-6 lg:p-7 space-y-4 lg:col-span-5 flex flex-col justify-between">
            <div>
                <!-- Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs shadow-2xs">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Employee Status</h3>
                                <span id="staff-badge-total" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                    <?php echo $staffCounts['total']; ?> Staff
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium">Real-time attendance &amp; on-duty roster</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Jump back to analytics on phone -->
                        <a href="#visit-analytics-card" class="lg:hidden text-[11px] font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                            <i class="fa-solid fa-arrow-up text-[10px]"></i> Analytics
                        </a>
                        <a href="face_attendance.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200/80 text-xs font-bold transition shadow-2xs">
                            <i class="fa-solid fa-camera text-purple-600 text-xs"></i>
                            <span>Mark Attendance</span>
                        </a>
                        <a href="staff.php" class="hidden sm:inline-flex text-xs font-bold text-slate-500 hover:text-slate-900 items-center gap-1 transition p-1.5 rounded-lg hover:bg-slate-100" title="Manage Staff Directory">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Controls: Search & Filter Tabs (Optimized for Mobile Phone & Touch) -->
                <div class="space-y-2.5 pt-2">
                    <!-- Search Input -->
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            id="employee-search-input" 
                            oninput="filterStaffRoster()" 
                            placeholder="Search employee name..." 
                            class="w-full pl-8 pr-8 py-2 rounded-xl bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs font-medium text-slate-800 placeholder-slate-400 transition outline-none"
                        />
                        <button 
                            type="button" 
                            id="btn-clear-staff-search" 
                            onclick="clearStaffSearch()" 
                            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 text-xs"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Filter Chips (Horizontal swipeable on phone view) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs select-none no-scrollbar">
                        <button type="button" onclick="setStaffFilter('all')" id="filter-staff-all" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-900 text-white shadow-2xs transition">
                            All (<span id="count-staff-all"><?php echo $staffCounts['total']; ?></span>)
                        </button>
                        <button type="button" onclick="setStaffFilter('present')" id="filter-staff-present" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 transition">
                            Present (<span id="count-staff-present"><?php echo $staffCounts['present']; ?></span>)
                        </button>
                        <button type="button" onclick="setStaffFilter('off_duty')" id="filter-staff-off_duty" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Off Duty (<span id="count-staff-off_duty"><?php echo $staffCounts['off_duty']; ?></span>)
                        </button>
                        <button type="button" onclick="setStaffFilter('absent')" id="filter-staff-absent" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200/80 hover:bg-rose-100 transition">
                            Absent/Leave (<span id="count-staff-absent"><?php echo $staffCounts['absent'] + $staffCounts['leave']; ?></span>)
                        </button>
                    </div>
                </div>

                <!-- Scrollable Employee List Container (Touch Friendly, Phone Optimized) -->
                <div id="employee-roster-container" class="mt-2.5 max-h-[300px] sm:max-h-[360px] overflow-y-auto space-y-2 pr-1">
                    <?php if (empty($dashboardStaffList)): ?>
                        <div class="py-8 text-center text-xs text-slate-400 font-medium">
                            No employees found in system.
                        </div>
                    <?php else: ?>
                        <?php foreach ($dashboardStaffList as $st): 
                            $name = trim($st['first_name'] . ' ' . $st['last_name']);
                            $role = $st['role'] ?? 'Staff';
                            $dept = $st['department'] ?? 'Hospital';
                            $att = $st['att_status'] ?? '';
                            $empStatus = $st['status'] ?? 'Active';
                            $initials = strtoupper(substr($st['first_name'] ?? 'S', 0, 1) . substr($st['last_name'] ?? '', 0, 1));
                            if (empty($initials)) $initials = 'ST';

                            // Determine status pill & styling
                            $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                            $dotClass = 'bg-slate-400';
                            $statusLabel = 'Off Duty';
                            $filterGroup = 'off_duty';

                            if ($att === 'Present') {
                                $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200/90';
                                $dotClass = 'bg-emerald-500 animate-pulse';
                                $statusLabel = 'Present';
                                $filterGroup = 'present';
                            } elseif ($att === 'Late') {
                                $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200/90';
                                $dotClass = 'bg-amber-500';
                                $statusLabel = 'Late';
                                $filterGroup = 'present';
                            } elseif ($att === 'Half Day') {
                                $badgeClass = 'bg-sky-50 text-sky-800 border-sky-200/90';
                                $dotClass = 'bg-sky-500';
                                $statusLabel = 'Half Day';
                                $filterGroup = 'absent';
                            } elseif ($att === 'On Leave') {
                                $badgeClass = 'bg-purple-50 text-purple-800 border-purple-200/90';
                                $dotClass = 'bg-purple-500';
                                $statusLabel = 'On Leave';
                                $filterGroup = 'absent';
                            } elseif ($att === 'Absent') {
                                $badgeClass = 'bg-rose-50 text-rose-800 border-rose-200/90';
                                $dotClass = 'bg-rose-500';
                                $statusLabel = 'Absent';
                                $filterGroup = 'absent';
                            } elseif ($empStatus === 'Inactive') {
                                $badgeClass = 'bg-slate-200 text-slate-600 border-slate-300';
                                $dotClass = 'bg-slate-500';
                                $statusLabel = 'Inactive';
                                $filterGroup = 'off_duty';
                            }

                            // Avatar background tones
                            $avatarColors = [
                                'bg-blue-100 text-blue-700',
                                'bg-emerald-100 text-emerald-700',
                                'bg-indigo-100 text-indigo-700',
                                'bg-violet-100 text-violet-700',
                                'bg-amber-100 text-amber-700',
                                'bg-rose-100 text-rose-700'
                            ];
                            $avatarColor = $avatarColors[$st['id'] % count($avatarColors)];
                        ?>
                        <div 
                            class="staff-roster-row flex items-center justify-between p-2 sm:p-2.5 rounded-2xl bg-slate-50/70 hover:bg-white hover:border-slate-300/80 border border-slate-100/90 transition shadow-2xs group"
                            data-name="<?php echo htmlspecialchars(strtolower($name)); ?>"
                            data-role="<?php echo htmlspecialchars(strtolower($role)); ?>"
                            data-dept="<?php echo htmlspecialchars(strtolower($dept)); ?>"
                            data-filter="<?php echo $filterGroup; ?>"
                        >
                            <!-- Avatar + Name Only -->
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <div class="w-8 h-8 rounded-xl <?php echo $avatarColor; ?> flex items-center justify-center text-[11px] font-black shrink-0 shadow-2xs">
                                    <?php echo htmlspecialchars($initials); ?>
                                </div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                    <?php echo htmlspecialchars($name); ?>
                                </h4>
                            </div>

                            <!-- Status Pill (Simple & Clear) -->
                            <div class="shrink-0 flex items-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold border shadow-2xs <?php echo $badgeClass; ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?php echo $dotClass; ?>"></span>
                                    <span><?php echo $statusLabel; ?></span>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Empty search results placeholder -->
                    <div id="staff-search-empty" class="hidden py-8 text-center">
                        <i class="fa-solid fa-user-xmark text-slate-300 text-2xl mb-1.5"></i>
                        <p class="text-xs font-bold text-slate-600">No matching employees found</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Try searching with a different name or role.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Quick Stats & Links -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-[11px]">
                <div class="text-slate-500 font-medium">
                    <span class="text-emerald-600 font-bold" id="staff-summary-onduty"><?php echo $staffCounts['present']; ?></span> on duty now &bull; <span class="text-slate-700 font-bold"><?php echo $staffCounts['total']; ?></span> registered
                </div>
                <a href="staff.php" class="font-bold text-blue-600 hover:text-blue-800 transition">
                    Staff Directory &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- ================= 6. MARK ATTENDANCE (AI FACE ATTENDANCE KIOSK) ================= -->
    <div class="dash-card p-5 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-5 bg-gradient-to-r from-purple-50/50 via-white to-indigo-50/40 border border-purple-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-xl sm:text-2xl font-black shadow-lg shadow-purple-500/25 shrink-0">
                <i class="fa-solid fa-face-viewfinder"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">
                        AI Facial Terminal
                    </span>
                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Kiosk Active
                    </span>
                </div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Mark Attendance</h3>
                <p class="text-xs text-slate-500 mt-0.5">Instant touchless attendance verification with face recognition &amp; live punch records</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <!-- Quick punches summary pill -->
            <div class="hidden sm:flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs text-xs font-semibold">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Punched Today</span>
                    <span class="text-emerald-700 font-black text-sm"><?php echo $staffCounts['present']; ?> Staff</span>
                </div>
                <div class="w-px h-6 bg-slate-200"></div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Unmarked</span>
                    <span class="text-slate-500 font-black text-sm"><?php echo $staffCounts['off_duty']; ?> Remaining</span>
                </div>
            </div>

            <!-- Open Face Attendance button -->
            <a 
                href="face_attendance.php" 
                class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-purple-500/20 hover:shadow-purple-500/30 transition flex items-center justify-center gap-2.5 active:scale-95 text-center group"
            >
                <i class="fa-solid fa-camera text-sm"></i>
                <span>Open Face Attendance</span>
                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>
    </div>

    <!-- ================= 7. MINIMALIST APP LAUNCHER GRID ================= -->
    <div class="dash-card p-6 sm:p-8 space-y-4">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Quick App Launcher</h3>
        
        <div class="grid grid-cols-3 sm:grid-cols-9 gap-3 sm:gap-4 pt-2">
            <a href="index.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-address-card"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Desk</span>
            </a>

            <a href="appointments.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Bookings</span>
            </a>

            <a href="queue.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-bars-staggered"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Queue</span>
            </a>

            <a href="patients.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Patients</span>
            </a>

            <a href="doctors.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Doctors</span>
            </a>

            <a href="doctor_slots.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Slots</span>
            </a>

            <a href="beds.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-solid fa-bed"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Wards</span>
            </a>

            <a href="staff.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-slate-900 group-hover:text-white text-slate-700 flex items-center justify-center text-lg transition shadow-2xs">
                    <i class="fa-regular fa-id-badge"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Staff</span>
            </a>

            <a href="face_attendance.php" class="flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-purple-50/70 transition">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 group-hover:bg-purple-600 group-hover:text-white text-purple-700 flex items-center justify-center text-lg transition shadow-2xs border border-purple-200/60">
                    <i class="fa-solid fa-face-viewfinder"></i>
                </div>
                <span class="text-xs font-bold text-purple-900">Attendance</span>
            </a>
        </div>
    </div>

</div>

<!-- ================= JAVASCRIPT: LOGIC & GSAP ANIMATIONS ================= -->
<script>
let timelineChart = null;
let currentTimelineRange = '7days';
let isDashboardBookingActive = <?php echo $online_booking_active ? 'true' : 'false'; ?>;
let isAiEngineOnline = false;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initial GSAP Staggered Entrance (Antigravity Motion Rule)
    if (typeof gsap !== 'undefined') {
        gsap.from(".dash-card", {
            opacity: 0,
            y: 20,
            duration: 0.5,
            stagger: 0.05,
            ease: "power2.out"
        });
    }

    // 2. Start Live Clock
    updateClock();
    setInterval(updateClock, 1000);

    // 3. Load Overview KPIs & Data
    fetchOverviewKPIs();
    fetchTimelineChart(currentTimelineRange);
    fetchPipelineQueue();
    fetchAiTelemetry();
    fetchStaffRosterLive();

    // 4. Polling every 15s for live OPD updates
    setInterval(() => {
        fetchPipelineQueue();
        fetchOverviewKPIs();
        fetchStaffRosterLive();
    }, 15000);
});

// Live Clock
function updateClock() {
    const now = new Date();
    const tEl = document.getElementById('dash-live-time');
    const dEl = document.getElementById('dash-live-date');
    if (tEl) tEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    if (dEl) dEl.textContent = now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
}

// Overview KPI Stats
async function fetchOverviewKPIs() {
    try {
        const res = await fetch('api/dashboard.php?action=get_overview');
        const data = await res.json();
        if (data.status === 'success' && data.kpis) {
            const k = data.kpis;
            setNum('kpi-patients', k.total_patients);
            setNum('kpi-queue', k.live_queue);
            setNum('kpi-appointments', k.today_appointments ? k.today_appointments.total : 0);
            setNum('kpi-doctors', k.total_doctors);
            setNum('kpi-beds', k.available_beds);
            setNum('kpi-staff', k.staff ? k.staff.on_duty : 0);

            // Emergency banner
            const emAlert = document.getElementById('dash-emergency-alert');
            const emCount = document.getElementById('dash-emergency-count');
            if (emAlert && emCount) {
                if (k.emergency_cases > 0) {
                    emAlert.classList.remove('hidden');
                    emCount.textContent = `${k.emergency_cases} Critical ${k.emergency_cases === 1 ? 'Case' : 'Cases'} in Queue`;
                } else {
                    emAlert.classList.add('hidden');
                }
            }
        }
    } catch(e) {
        console.warn('KPI load error:', e);
    }
}

function setNum(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = (val !== undefined && val !== null) ? val : '0';
}

// Timeline Analytics Chart
async function fetchTimelineChart(range) {
    try {
        const res = await fetch(`api/dashboard.php?action=get_charts&timeline=${range}`);
        const data = await res.json();
        if (data.status === 'success') {
            renderTimelineChart(data.timeline || [], data.summary || {});
        }
    } catch(e) {
        console.warn('Chart load error:', e);
    }
}

function setChartTimeline(range) {
    currentTimelineRange = range;
    ['7d', '14d', '30d'].forEach(k => {
        const btn = document.getElementById('btn-chart-' + k);
        if (btn) {
            if (range.startsWith(k.replace('d', ''))) {
                btn.className = 'px-3.5 py-1.5 rounded-full text-xs font-bold bg-white text-slate-900 shadow-2xs';
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-full text-xs font-bold text-slate-500 hover:text-slate-900';
            }
        }
    });
    fetchTimelineChart(range);
}

function renderTimelineChart(timeline, summary) {
    document.getElementById('chart-stat-total').textContent = summary.total || 0;
    document.getElementById('chart-stat-rate').textContent = (summary.completion_rate || 0) + '%';
    document.getElementById('chart-stat-peak').textContent = summary.peak_day || '--';
    document.getElementById('chart-stat-avg').textContent = summary.avg_per_day || 0;

    const ctx = document.getElementById('canvas-dashboard-timeline');
    if (!ctx) return;
    if (timelineChart) timelineChart.destroy();

    const labels = timeline.map(i => i.label || i.key);
    const totals = timeline.map(i => i.total || 0);
    const completed = timeline.map(i => i.completed || 0);

    timelineChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Visits',
                    data: totals,
                    borderColor: '#0f172a',
                    backgroundColor: 'rgba(15, 23, 42, 0.04)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#0f172a'
                },
                {
                    label: 'Completed',
                    data: completed,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderDash: [4, 4],
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 2.5,
                    pointBackgroundColor: '#10b981'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(241, 245, 249, 1)' },
                    ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8', stepSize: 1 }
                }
            }
        }
    });
}

// Pipeline & Queue Preview
async function fetchPipelineQueue() {
    try {
        const res = await fetch('api/queue.php?action=get_queue');
        const data = await res.json();
        if (data.status === 'success') {
            const list = data.queue || [];
            
            // Counters
            const s1 = list.filter(p => Number(p.stage) === 1).length;
            const s2 = list.filter(p => Number(p.stage) === 2).length;
            const s3 = list.filter(p => Number(p.stage) === 3).length;
            const s4 = list.filter(p => Number(p.stage) >= 4).length;

            setNum('stage-count-1', s1);
            setNum('stage-count-2', s2);
            setNum('stage-count-3', s3);
            setNum('stage-count-4', s4);

            setNum('hub-stat-total', list.length);
            setNum('hub-stat-waiting', s1 + s2);
            setNum('hub-stat-consulting', s3);
            setNum('hub-stat-completed', s4);

            // Render live queue table
            const tbody = document.getElementById('dash-live-queue-body');
            if (!tbody) return;

            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-xs text-slate-400 font-medium">No patients currently in queue.</td></tr>`;
                return;
            }

            tbody.innerHTML = list.slice(0, 5).map(p => {
                const tokenStr = p.token_no ? ('#' + String(p.token_no).padStart(2, '0')) : '--';
                const pName = p.name ? `${p.name} ${p.surname||''}`.trim() : 'Patient';
                const docName = p.doctor || 'Unassigned';
                const mrn = p.id || '';
                return `
                    <tr class="border-b border-slate-50 hover:bg-slate-50/70 transition">
                        <td class="py-3 px-3">
                            <span class="inline-flex px-2 py-0.5 rounded-lg bg-slate-100 font-mono font-black text-xs text-slate-800">${escapeHtml(tokenStr)}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="text-xs font-bold text-slate-900">${escapeHtml(pName)}</div>
                            <div class="text-[10px] font-mono text-slate-400">${escapeHtml(mrn)}</div>
                        </td>
                        <td class="py-3 px-3 text-xs font-semibold text-slate-600">
                            Dr. ${escapeHtml(docName)}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="patient_profile_qr.php?patient_id=${encodeURIComponent(mrn)}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white text-[11px] font-bold transition">
                                Open
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    } catch(e) {
        console.warn('Queue load error:', e);
    }
}

// Patient Search with Autocomplete
let searchDebounceTimer = null;
function handleReceptionSearch(val) {
    clearTimeout(searchDebounceTimer);
    const q = (val || '').trim();
    const drop = document.getElementById('reception-search-results');
    if (!drop) return;
    
    if (q.length < 1) {
        drop.classList.add('hidden');
        drop.innerHTML = '';
        return;
    }
    
    drop.classList.remove('hidden');
    drop.innerHTML = `<div class="p-4 text-center text-xs text-slate-400 font-semibold"><i class="fa-solid fa-circle-notch fa-spin mr-1.5 text-slate-600"></i> Searching patients...</div>`;
    
    searchDebounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`api/patients.php?action=search&q=${encodeURIComponent(q)}`);
            const data = await res.json();
            if (data.status === 'success' && data.patients && data.patients.length > 0) {
                let html = '<div class="divide-y divide-slate-100">';
                data.patients.forEach(p => {
                    const fullName = p.full_name || `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient';
                    const targetUrl = `patient_profile_qr.php?patient_id=${encodeURIComponent(p.id)}`;
                    html += `
                        <a href="${targetUrl}" class="flex items-center justify-between p-3 hover:bg-slate-50 transition group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    ${escapeHtml(p.name ? p.name.charAt(0).toUpperCase() : 'P')}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-black transition truncate">${escapeHtml(fullName)}</div>
                                    <div class="text-[11px] text-slate-400 font-medium flex items-center gap-2">
                                        <span class="font-mono text-slate-600">${escapeHtml(p.id)}</span>
                                        <span>•</span>
                                        <span>${escapeHtml(p.phone || 'No phone')}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1 rounded-full bg-slate-900 text-white text-[11px] font-bold transition">
                                Check-In
                            </span>
                        </a>
                    `;
                });
                html += '</div>';
                drop.innerHTML = html;
            } else {
                drop.innerHTML = `
                    <div class="p-5 text-center">
                        <div class="text-slate-400 text-xs font-semibold mb-2">No patient matching "${escapeHtml(q)}"</div>
                        <a href="index.php" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-900 text-white font-bold text-xs hover:bg-black transition">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Register New Patient</span>
                        </a>
                    </div>
                `;
            }
        } catch(e) {
            drop.innerHTML = `<div class="p-3 text-center text-xs text-rose-500 font-semibold">Error connecting to search.</div>`;
        }
    }, 200);
}

function executeReceptionSearch() {
    const input = document.getElementById('reception-search-input');
    if (!input) return;
    const q = input.value.trim();
    if (!q) return;
    if (/^(CP-\d{4}-\d+|PAT-\d+)$/i.test(q)) {
        window.location.href = `patient_profile_qr.php?patient_id=${encodeURIComponent(q.toUpperCase())}`;
        return;
    }
    handleReceptionSearch(q);
}

document.addEventListener('click', function(e) {
    const input = document.getElementById('reception-search-input');
    const drop = document.getElementById('reception-search-results');
    if (drop && input && !input.contains(e.target) && !drop.contains(e.target)) {
        drop.classList.add('hidden');
    }
});

// Online Booking Toggle
async function toggleDashboardBooking() {
    const btn = document.getElementById('btn-dash-booking-toggle');
    const icon = document.getElementById('dash-booking-toggle-icon');
    const text = document.getElementById('dash-booking-toggle-text');
    const nextState = !isDashboardBookingActive;

    btn.disabled = true;
    btn.classList.add('opacity-75');

    try {
        const res = await fetch('api/dashboard.php?action=toggle_booking_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ enabled: nextState ? 1 : 0 })
        });
        const data = await res.json();
        if (data.status === 'success') {
            isDashboardBookingActive = (data.booking_enabled === 1);
            if (isDashboardBookingActive) {
                btn.className = "px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-2 border cursor-pointer active:scale-95 shadow-2xs bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100";
                icon.className = "fa-solid fa-toggle-on text-emerald-600 text-sm";
                text.textContent = "Online Booking: Active";
            } else {
                btn.className = "px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-2 border cursor-pointer active:scale-95 shadow-2xs bg-rose-50 border-rose-200 text-rose-800 hover:bg-rose-100";
                icon.className = "fa-solid fa-toggle-off text-rose-600 text-sm";
                text.textContent = "Online Booking: Paused";
            }
        }
    } catch(e) {
        console.warn('Booking toggle error:', e);
    } finally {
        btn.disabled = false;
        btn.classList.remove('opacity-75');
    }
}

// AI Engine Telemetry & Fast Controller (ON / OFF Model Switch)
async function fetchAiTelemetry() {
    try {
        const res = await fetch('api/ai_train_api.php?action=status');
        const data = await res.json();
        const badge = document.getElementById('ai-telemetry-badge');
        const statusPill = document.getElementById('ai-model-status-pill');
        const btn = document.getElementById('btn-ai-toggle-quick');
        const btnText = document.getElementById('btn-ai-text');
        const btnIcon = document.getElementById('btn-ai-icon');
        const servingName = document.getElementById('ai-model-serving-name');

        if (data.success && data.ollama_online) {
            isAiEngineOnline = true;
            const modelLoaded = data.model_loaded_in_vram;
            const vram = data.model_vram_size ? ` (${data.model_vram_size})` : '';

            if (badge) {
                badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200';
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1 animate-pulse"></span> Online';
            }
            if (statusPill) {
                statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200';
                statusPill.textContent = 'ON' + (modelLoaded ? ' • Active' : '');
            }
            if (servingName) {
                servingName.textContent = (data.current_serving_model || 'hms-ai:latest') + vram;
            }
            if (btn) {
                btn.className = "px-4 py-2 rounded-full font-bold text-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95 shadow-2xs border bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100";
            }
            if (btnText) btnText.textContent = 'Turn OFF';
            if (btnIcon) btnIcon.className = 'fa-solid fa-power-off text-rose-600 text-xs';
        } else {
            isAiEngineOnline = false;
            if (badge) {
                badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200';
                badge.textContent = 'Offline';
            }
            if (statusPill) {
                statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-200 text-slate-600';
                statusPill.textContent = 'OFF';
            }
            if (servingName) {
                servingName.textContent = data.current_serving_model || 'hms-ai:latest';
            }
            if (btn) {
                btn.className = "px-4 py-2 rounded-full font-bold text-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95 shadow-2xs border bg-slate-900 text-white hover:bg-black";
            }
            if (btnText) btnText.textContent = 'Turn ON';
            if (btnIcon) btnIcon.className = 'fa-solid fa-power-off text-amber-300 text-xs';
        }
    } catch(e) {
        console.warn('AI telemetry error:', e);
    }
}

async function toggleAiEngineModel() {
    const btn = document.getElementById('btn-ai-toggle-quick');
    const btnText = document.getElementById('btn-ai-text');
    const btnIcon = document.getElementById('btn-ai-icon');

    btn.disabled = true;
    btn.classList.add('opacity-75');

    if (isAiEngineOnline) {
        // Currently ON -> Turn OFF
        if (btnText) btnText.textContent = 'Turning OFF...';
        if (btnIcon) btnIcon.className = 'fa-solid fa-circle-notch fa-spin text-xs';
        try {
            await fetch('api/ai_train_api.php?action=stop_ollama', { method: 'POST' });
        } catch(e) {}
    } else {
        // Currently OFF -> Turn ON
        if (btnText) btnText.textContent = 'Starting...';
        if (btnIcon) btnIcon.className = 'fa-solid fa-circle-notch fa-spin text-xs';
        try {
            await fetch('api/ai_train_api.php?action=start_both', { method: 'POST' });
        } catch(e) {}
    }

    setTimeout(() => {
        fetchAiTelemetry();
        btn.disabled = false;
        btn.classList.remove('opacity-75');
    }, 1500);
}

function openChatbotModal() {
    const trigger = document.getElementById('ai-chat-pill-trigger') || document.querySelector('[onclick*="toggleChatbot"]');
    if (trigger) trigger.click();
    else if (typeof toggleChatbot === 'function') toggleChatbot();
}

// ================= STAFF EMPLOYEE STATUS ROSTER LOGIC =================
let currentStaffFilter = 'all';

function filterStaffRoster() {
    const input = document.getElementById('employee-search-input');
    const term = input ? input.value.trim().toLowerCase() : '';
    const clearBtn = document.getElementById('btn-clear-staff-search');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', term === '');
    }

    const rows = document.querySelectorAll('.staff-roster-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const role = row.getAttribute('data-role') || '';
        const dept = row.getAttribute('data-dept') || '';
        const filterGroup = row.getAttribute('data-filter') || '';

        const matchesSearch = term === '' || name.includes(term) || role.includes(term) || dept.includes(term);
        const matchesFilter = (currentStaffFilter === 'all') || (filterGroup === currentStaffFilter);

        if (matchesSearch && matchesFilter) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    const emptyMsg = document.getElementById('staff-search-empty');
    if (emptyMsg) {
        emptyMsg.classList.toggle('hidden', visibleCount > 0);
    }
}

function setStaffFilter(filter) {
    currentStaffFilter = filter;
    const filterKeys = ['all', 'present', 'off_duty', 'absent'];
    filterKeys.forEach(k => {
        const btn = document.getElementById('filter-staff-' + k);
        if (btn) {
            if (k === filter) {
                btn.className = 'shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-900 text-white shadow-2xs transition';
            } else if (k === 'present') {
                btn.className = 'shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 transition';
            } else if (k === 'absent') {
                btn.className = 'shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200/80 hover:bg-rose-100 transition';
            } else {
                btn.className = 'shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition';
            }
        }
    });
    filterStaffRoster();
}

function clearStaffSearch() {
    const input = document.getElementById('employee-search-input');
    if (input) {
        input.value = '';
    }
    filterStaffRoster();
}

// Live Staff Roster Polling & Real-time Update
async function fetchStaffRosterLive() {
    try {
        const res = await fetch('api/dashboard.php?action=get_staff_roster');
        const data = await res.json();
        if (data.status === 'success' && data.counts) {
            const c = data.counts;
            const bTotal = document.getElementById('staff-badge-total');
            if (bTotal) bTotal.textContent = `${c.total} Staff`;
            
            const cAll = document.getElementById('count-staff-all');
            if (cAll) cAll.textContent = c.total;
            
            const cPres = document.getElementById('count-staff-present');
            if (cPres) cPres.textContent = c.present;
            
            const cOff = document.getElementById('count-staff-off_duty');
            if (cOff) cOff.textContent = c.off_duty;
            
            const cAbs = document.getElementById('count-staff-absent');
            if (cAbs) cAbs.textContent = c.absent + c.leave;
            
            const sumOn = document.getElementById('staff-summary-onduty');
            if (sumOn) sumOn.textContent = c.present;
        }
    } catch (e) {
        console.warn('Staff roster live poll error:', e);
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
</script>

<?php include 'includes/footer.php'; ?>

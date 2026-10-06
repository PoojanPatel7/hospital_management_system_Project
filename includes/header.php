<?php
require_once __DIR__ . '/../auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$hospital_name = $_SESSION['hospital_name'] ?? 'CarePulse';

$isStaff = isStaff();
$isAdmin = isAdmin();
$staffName = $_SESSION['staff_name'] ?? 'Staff Member';
$staffRole = $_SESSION['staff_role'] ?? 'Staff';

$userName = $isAdmin ? ($_SESSION['username'] ?? 'Gopalbhai') : $staffName;
$userRole = $isAdmin ? 'Hospital Owner & Super Administrator' : $staffRole;
$userInitial = $isAdmin ? 'G' : strtoupper(substr($userName, 0, 1));

$canUpload = hasPermission($conn, 'can_upload');
$canViewFiles = hasPermission($conn, 'can_view_files');
$canViewPatients = hasPermission($conn, 'can_view_patients');
$canManageAppts = hasPermission($conn, 'can_manage_appointments');
$canAssignTokens = hasPermission($conn, 'can_assign_tokens');
$canGenerateQR = hasPermission($conn, 'can_generate_qr');
$canConsultOnline = hasPermission($conn, 'can_consult_online');
$canDownloadPdf = hasPermission($conn, 'can_download_pdf');
$canEditPatients = hasPermission($conn, 'can_edit_patients');
$canEditFiles = hasPermission($conn, 'can_edit_files');
$canDeleteFiles = hasPermission($conn, 'can_delete_files');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($hospital_name); ?> | Hospital System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    
    /* Clean scrollbar with zero visible line / indicator track */
    .no-scrollbar::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }
    .no-scrollbar {
      -ms-overflow-style: none !important;
      scrollbar-width: none !important;
    }

    .glass-sidebar {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-right: 1px solid rgba(226, 232, 240, 0.8);
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .apple-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0,0,0,0.02);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease, border-color 0.2s ease;
        border: 1px solid rgba(226, 232, 240, 0.7);
    }
    .apple-card:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06), 0 2px 4px rgba(0,0,0,0.03);
    }
    .nav-item {
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
    }
    .nav-item.active {
        background: #f1f5f9;
        color: #000000;
        font-weight: 700;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        border-left: 3px solid #000000;
    }
    .nav-item:hover:not(.active) {
        background: #f8fafc;
        color: #000000;
    }
    /* Strictly enforce uniform black color for all sidebar/slider logos & icons */
    #desktop-sidebar-nav .nav-item i,
    #mobile-nav-drawer .nav-item i,
    .sidebar-icon {
        color: #000000 !important;
    }
    .brand-badge i,
    .ai-white-icon {
        color: #ffffff !important;
    }
  </style>
</head>
<body class="text-slate-800 h-screen flex overflow-hidden">

  <!-- ================= DESKTOP SIDEBAR ================= -->
  <aside class="w-64 glass-sidebar flex flex-col hidden md:flex shrink-0 shadow-sm z-20 relative">
    <!-- Brand -->
    <div class="h-20 flex items-center px-4 shrink-0 border-b border-slate-200/80">
      <a href="dashboard.php" class="flex items-center gap-3 w-full py-1 group">
        <img src="images/Logo.png" alt="Bhooma Medicare Hospital" class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="hidden items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-2xl bg-black text-white flex items-center justify-center text-lg font-black shadow-md shrink-0 group-hover:scale-105 transition-transform duration-300 brand-badge">
            <i class="fa-solid fa-hospital"></i>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-black tracking-tight leading-tight">
              <span class="text-black font-black">BHOOMA</span>
            </div>
            <div class="text-[9px] font-extrabold uppercase tracking-wider text-neutral-500 leading-tight truncate mt-0.5">
              Medicare Hospital &amp; I.C.U
            </div>
          </div>
        </div>
      </a>
    </div>

    <!-- Navigation without visible scrollbar line + scroll position remembered -->
    <nav id="desktop-sidebar-nav" class="flex-1 px-3 py-4 space-y-1 overflow-y-auto no-scrollbar font-medium">
      
    <?php if ($isAdmin): ?>
      <!-- ================= OWNER / ADMIN SIDEBAR NAVIGATION ================= -->
      <!-- Group 1: Overview -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-overview')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Overview</span>
          <i id="chevron-sec-overview" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-overview" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center text-black"></i>
            <span class="text-sm">Dashboard</span>
          </a>
          <a href="queue.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-6 text-center text-black"></i>
            <span class="text-sm">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Front Desk & Patients -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-frontdesk')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Front Desk</span>
          <i id="chevron-sec-frontdesk" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-frontdesk" class="space-y-0.5 mt-1 transition-all">
          <a href="index.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-hospital-user w-6 text-center text-black"></i>
            <span class="text-sm">Reception Desk</span>
          </a>
          <a href="patients.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-6 text-center text-black"></i>
            <span class="text-sm">Patient Directory</span>
          </a>
          <a href="history.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-6 text-center text-black"></i>
            <span class="text-sm">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments & Schedules -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-appointments')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Appointments</span>
          <i id="chevron-sec-appointments" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-appointments" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-6 text-center text-black"></i>
            <span class="text-sm">Appointments Schedule</span>
          </a>
          <a href="book.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-6 text-center text-black"></i>
            <span class="text-sm">Book Appointment</span>
          </a>
          <a href="doctor_slots.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'doctor_slots.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-clock w-6 text-center text-black"></i>
            <span class="text-sm">Manage Slot Timings</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 4: Medical Staff & Wards -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Staff &amp; Facilities</span>
          <i id="chevron-sec-staff" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-staff" class="space-y-0.5 mt-1 transition-all">
          <a href="doctors.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-6 text-center text-black"></i>
            <span class="text-sm">Doctors Directory</span>
          </a>
          <a href="staff.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-6 text-center text-black"></i>
            <span class="text-sm">Staff &amp; Attendance</span>
          </a>
          <a href="staff_permissions.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'staff_permissions.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-shield w-6 text-center text-black"></i>
            <span class="text-sm">Staff Permissions</span>
          </a>
          <a href="charges.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'charges.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-receipt w-6 text-center text-black"></i>
            <span class="text-sm">Consultation Charges</span>
          </a>
          <a href="beds.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'beds.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-6 text-center text-black"></i>
            <span class="text-sm">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 5: System & Data -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-system')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">System &amp; Data</span>
          <i id="chevron-sec-system" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-system" class="space-y-0.5 mt-1 transition-all">
          <a href="backup.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-database w-6 text-center text-black"></i>
            <span class="text-sm">Database Backup</span>
          </a>
          <a href="restore.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'restore.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-6 text-center text-black"></i>
            <span class="text-sm">Load / Restore Data</span>
          </a>
          <a href="ai_logs.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'ai_logs.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-brain w-6 text-center text-black"></i>
            <span class="text-sm">AI Chat History &amp; Logs</span>
          </a>
          <a href="ai_training_dashboard.php" class="nav-item flex items-center justify-between px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'ai_training_dashboard.php' ? 'active' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-microchip w-6 text-center text-black"></i>
              <span class="text-sm">AI Training &amp; GPU Lab</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-black text-white px-1.5 py-0.5 rounded-full border border-black">LIVE</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

    <?php else: ?>
      <!-- ================= STAFF SPECIFIC RESTRICTED SIDEBAR ================= -->
      <!-- Group 1: Staff Workspace -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff-work')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">My Workspace</span>
          <i id="chevron-sec-staff-work" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-staff-work" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge-high w-6 text-center text-black"></i>
            <span class="text-sm">Staff Dashboard</span>
          </a>
          <a href="selfie_attendance.php" class="nav-item flex items-center justify-between px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'selfie_attendance.php' ? 'active' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-camera w-6 text-center text-black"></i>
              <span class="text-sm">Selfie Attendance</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">Daily</span>
          </a>
          <?php if ($canAssignTokens || $canManageAppts): ?>
          <a href="queue.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-6 text-center text-black"></i>
            <span class="text-sm">Live Pipeline</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Patient Records & Care -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff-records')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Patient Care</span>
          <i id="chevron-sec-staff-records" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-staff-records" class="space-y-0.5 mt-1 transition-all">
          <?php if ($canViewPatients || $canUpload): ?>
          <a href="patients.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-6 text-center text-black"></i>
            <span class="text-sm">Patient Directory</span>
          </a>
          <button type="button" onclick="openQRScannerModal()" class="w-full nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black cursor-pointer text-left">
            <i class="fa-solid fa-qrcode w-6 text-center text-indigo-600"></i>
            <span class="text-sm">Scan Patient QR</span>
          </button>
          <?php endif; ?>
          <?php if ($canUpload): ?>
          <a href="patients.php" class="nav-item flex items-center justify-between px-3 py-2 text-slate-700 hover:text-black">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-cloud-arrow-up w-6 text-center text-black"></i>
              <span class="text-sm">Upload Reports</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">Upload</span>
          </a>
          <?php endif; ?>
          <?php if ($canViewFiles): ?>
          <a href="history.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-6 text-center text-black"></i>
            <span class="text-sm">Medical Records</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments (If Authorized) -->
      <?php if ($canManageAppts): ?>
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff-appts')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Appointments</span>
          <i id="chevron-sec-staff-appts" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-staff-appts" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-6 text-center text-black"></i>
            <span class="text-sm">Appointments Schedule</span>
          </a>
          <a href="book.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-6 text-center text-black"></i>
            <span class="text-sm">Book Appointment</span>
          </a>
        </div>
      </div>
      <div class="h-px bg-slate-200 my-2 mx-1"></div>
      <?php endif; ?>

      <!-- Group 4: Online Doctor Consultations (If Authorized) -->
      <?php if ($canConsultOnline): ?>
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff-tele')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Consultations</span>
          <i id="chevron-sec-staff-tele" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-staff-tele" class="space-y-0.5 mt-1 transition-all">
          <a href="online_consult.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'online_consult.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-laptop-medical w-6 text-center text-black"></i>
            <span class="text-sm">Online Consultations</span>
          </a>
        </div>
      </div>
      <div class="h-px bg-slate-200 my-2 mx-1"></div>
      <?php endif; ?>

    <?php endif; ?>

      <!-- Help & Information (Both Admin and Staff) -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-help')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Help &amp; Info</span>
          <i id="chevron-sec-help" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="sec-help" class="space-y-0.5 mt-1 transition-all">
          <a href="guide.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'guide.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open w-6 text-center text-black"></i>
            <span class="text-sm">System User Guide</span>
          </a>
          <a href="about.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-700 hover:text-black <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-info w-6 text-center text-black"></i>
            <span class="text-sm">About Hospital</span>
          </a>
        </div>
      </div>
      
      <?php if ($isAdmin): ?>
      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- AI Assistant Button in Sidebar (Admin Only) -->
      <div class="px-1">
        <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle();" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-black transition shadow-xs font-semibold text-left cursor-pointer">
          <div class="w-6 h-6 rounded-lg bg-black flex items-center justify-center text-white text-xs shrink-0 shadow-xs">
            <i class="fa-solid fa-robot ai-white-icon"></i>
          </div>
          <span class="text-sm font-semibold">BHOOMA AI</span>
          <span class="ml-auto text-[10px] bg-slate-200 text-black font-bold px-1.5 py-0.5 rounded border border-slate-300">Ctrl+K</span>
        </button>
      </div>
      <?php endif; ?>

    </nav>

    <!-- User badge bottom -->
    <div class="p-3 border-t border-slate-200/80 bg-slate-50/80">
      <?php if ($isStaff): ?>
      <div class="flex items-center gap-2.5 mb-2.5 px-1">
        <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
          <?php echo strtoupper(substr($staffName, 0, 1)); ?>
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-xs font-black text-slate-900 truncate"><?php echo htmlspecialchars($staffName); ?></div>
          <div class="text-[10px] text-slate-500 font-semibold truncate"><?php echo htmlspecialchars($staffRole); ?></div>
        </div>
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Active Account"></span>
      </div>
      <?php else: ?>
      <!-- MAIN TOP USER: GOPALBHAI -->
      <div class="flex items-center gap-2.5 mb-2.5 px-1">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white flex items-center justify-center font-black text-xs shrink-0 shadow-sm ring-2 ring-indigo-200">
          G
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-xs font-black text-slate-900 truncate flex items-center gap-1">
            <span><?php echo htmlspecialchars($userName); ?></span>
            <i class="fa-solid fa-crown text-[9px] text-amber-500" title="Main Top Administrator"></i>
          </div>
          <div class="text-[10px] text-indigo-700 font-extrabold uppercase tracking-wider truncate">Main Top Admin</div>
        </div>
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Super Admin Online"></span>
      </div>
      <?php endif; ?>
      <button onclick="openLogoutModal()" class="nav-item w-full flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-700 hover:text-black py-2.5 rounded-xl transition text-xs font-bold shadow-xs border border-slate-200 hover:border-slate-400 group relative">
          <i class="fa-solid fa-arrow-right-from-bracket text-black"></i> <span>Sign Out</span>
      </button>
    </div>
  </aside>

  <!-- ================= MOBILE DRAWER NAVIGATION ================= -->
  <div id="mobile-nav-backdrop" onclick="toggleMobileNav(false)" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 transition-opacity duration-300 md:hidden"></div>

  <aside id="mobile-nav-drawer" class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl md:hidden">
    <!-- Drawer Header -->
    <div class="h-20 flex items-center justify-between px-4 border-b border-slate-100 shrink-0">
      <a href="dashboard.php" class="flex items-center gap-2.5 min-w-0">
        <img src="images/Logo.png" alt="Bhooma Medicare Hospital" class="h-9 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="hidden items-center gap-2.5 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-black text-white flex items-center justify-center text-base font-black shadow-md shrink-0 brand-badge">
            <i class="fa-solid fa-hospital"></i>
          </div>
          <div class="min-w-0">
            <div class="text-sm font-black tracking-tight leading-tight">
              <span class="text-black font-black">BHOOMA</span>
            </div>
            <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">
              Medicare Hospital &amp; I.C.U
            </div>
          </div>
        </div>
      </a>
      <button onclick="toggleMobileNav(false)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-black flex items-center justify-center transition shrink-0">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Drawer Links without visible scrollbar line -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
      
    <?php if ($isAdmin): ?>
      <!-- Group 1: Overview -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-overview')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Overview</span>
          <i id="chevron-mob-sec-overview" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-overview" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Dashboard</span>
          </a>
          <a href="queue.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Front Desk & Patients -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-frontdesk')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Front Desk</span>
          <i id="chevron-mob-sec-frontdesk" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-frontdesk" class="space-y-0.5 mt-1 transition-all">
          <a href="index.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-hospital-user w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Reception Desk</span>
          </a>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Patient Directory</span>
          </a>
          <a href="history.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments & Schedules -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-appointments')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Appointments</span>
          <i id="chevron-mob-sec-appointments" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-appointments" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Appointments Schedule</span>
          </a>
          <a href="book.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Book Appointment</span>
          </a>
          <a href="doctor_slots.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'doctor_slots.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-clock w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Manage Slot Timings</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 4: Medical Staff & Wards -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Staff &amp; Facilities</span>
          <i id="chevron-mob-sec-staff" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff" class="space-y-0.5 mt-1 transition-all">
          <a href="doctors.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Doctors Directory</span>
          </a>
          <a href="staff.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Staff &amp; Attendance</span>
          </a>
          <a href="staff_permissions.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'staff_permissions.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-shield w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Staff Permissions</span>
          </a>
          <a href="charges.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'charges.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-receipt w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Consultation Charges</span>
          </a>
          <a href="beds.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'beds.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 5: System & Data -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-system')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">System &amp; Data</span>
          <i id="chevron-mob-sec-system" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-system" class="space-y-0.5 mt-1 transition-all">
          <a href="backup.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-database w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Database Backup</span>
          </a>
          <a href="restore.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'restore.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Load / Restore Data</span>
          </a>
          <a href="ai_logs.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'ai_logs.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-brain w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">AI Logs &amp; History</span>
          </a>
          <a href="ai_training_dashboard.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center justify-between px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'ai_training_dashboard.php' ? 'active' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-microchip w-5 text-center text-black"></i>
              <span class="text-sm font-semibold">AI Training &amp; GPU Lab</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-black text-white px-1.5 py-0.5 rounded-full border border-black">LIVE</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

    <?php else: ?>
      <!-- Staff Mobile Navigation -->
      <!-- Group 1: Staff Workspace -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff-work')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">My Workspace</span>
          <i id="chevron-mob-sec-staff-work" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff-work" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge-high w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Staff Dashboard</span>
          </a>
          <a href="selfie_attendance.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center justify-between px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'selfie_attendance.php' ? 'active' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-camera w-5 text-center text-black"></i>
              <span class="text-sm font-semibold">Selfie Attendance</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">Daily</span>
          </a>
          <?php if ($canAssignTokens || $canManageAppts): ?>
          <a href="queue.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Live Pipeline</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Patient Care & Files -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff-records')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Patient Care</span>
          <i id="chevron-mob-sec-staff-records" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff-records" class="space-y-0.5 mt-1 transition-all">
          <?php if ($canViewPatients || $canUpload): ?>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Patient Directory</span>
          </a>
          <button type="button" onclick="toggleMobileNav(false); openQRScannerModal();" class="w-full nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black cursor-pointer text-left">
            <i class="fa-solid fa-qrcode w-5 text-center text-indigo-600"></i>
            <span class="text-sm font-semibold">Scan Patient QR</span>
          </button>
          <?php endif; ?>
          <?php if ($canUpload): ?>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center justify-between px-3 py-2.5 text-slate-700 hover:text-black">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-cloud-arrow-up w-5 text-center text-black"></i>
              <span class="text-sm font-semibold">Upload Reports</span>
            </div>
            <span class="text-[9px] font-extrabold uppercase bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">Upload</span>
          </a>
          <?php endif; ?>
          <?php if ($canViewFiles): ?>
          <a href="history.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Medical Records</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments (If Authorized) -->
      <?php if ($canManageAppts): ?>
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff-appts')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Appointments</span>
          <i id="chevron-mob-sec-staff-appts" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff-appts" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Appointments Schedule</span>
          </a>
          <a href="book.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Book Appointment</span>
          </a>
        </div>
      </div>
      <div class="h-px bg-slate-200 my-2 mx-1"></div>
      <?php endif; ?>

      <!-- Group 4: Consultations (If Authorized) -->
      <?php if ($canConsultOnline): ?>
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff-tele')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Consultations</span>
          <i id="chevron-mob-sec-staff-tele" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff-tele" class="space-y-0.5 mt-1 transition-all">
          <a href="online_consult.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'online_consult.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-laptop-medical w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">Online Consultations</span>
          </a>
        </div>
      </div>
      <div class="h-px bg-slate-200 my-2 mx-1"></div>
      <?php endif; ?>

    <?php endif; ?>

      <!-- Group 6: Help & Information -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-help')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-black">Help &amp; Info</span>
          <i id="chevron-mob-sec-help" class="fa-solid fa-chevron-down text-[10px] text-neutral-400 group-hover:text-black transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-help" class="space-y-0.5 mt-1 transition-all">
          <a href="guide.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'guide.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">System User Guide</span>
          </a>
          <a href="about.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 hover:text-black <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-info w-5 text-center text-black"></i>
            <span class="text-sm font-semibold">About Hospital</span>
          </a>
        </div>
      </div>
      
      <?php if ($isAdmin): ?>
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- AI Assistant Button in Sidebar -->
      <div class="px-1">
        <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle();" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-black transition shadow-xs font-semibold text-left cursor-pointer">
          <div class="w-6 h-6 rounded-lg bg-black flex items-center justify-center text-white text-xs shrink-0 shadow-xs">
            <i class="fa-solid fa-robot ai-white-icon"></i>
          </div>
          <span class="text-sm font-semibold">BHOOMA AI</span>
          <span class="ml-auto text-[10px] bg-slate-200 text-black font-bold px-1.5 py-0.5 rounded border border-slate-300">Ctrl+K</span>
        </button>
      </div>
      <?php endif; ?>

    </nav>

    <!-- Drawer Footer -->
    <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-3">
      <?php if ($isAdmin): ?>
      <div class="flex items-center gap-2.5 px-1">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm ring-1 ring-indigo-200">
          G
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-xs font-black text-slate-900 truncate flex items-center gap-1">
            <span><?php echo htmlspecialchars($userName); ?></span>
            <i class="fa-solid fa-crown text-[9px] text-amber-500"></i>
          </div>
          <div class="text-[10px] text-indigo-700 font-extrabold uppercase tracking-wider truncate">Main Top Admin</div>
        </div>
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
      </div>
      <?php elseif ($isStaff): ?>
      <div class="flex items-center gap-2.5 px-1">
        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
          <?php echo strtoupper(substr($staffName, 0, 1)); ?>
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-xs font-black text-slate-900 truncate"><?php echo htmlspecialchars($staffName); ?></div>
          <div class="text-[10px] text-slate-500 font-semibold truncate"><?php echo htmlspecialchars($staffRole); ?></div>
        </div>
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
      </div>
      <?php endif; ?>
      <button onclick="openLogoutModal()" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-800 py-3 rounded-xl transition text-xs font-bold border border-slate-200 shadow-2xs">
        <i class="fa-solid fa-arrow-right-from-bracket text-slate-600"></i> Logout
      </button>
    </div>
  </aside>

  <!-- ================= MAIN CONTENT WRAPPER ================= -->
  <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
    
    <!-- Top Header Bar (Desktop & Mobile) -->
    <header class="h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 md:px-8 shrink-0 z-30 shadow-2xs">
      <!-- Left side: Mobile Hamburger + Brand / Page Title -->
      <div class="flex items-center gap-3 min-w-0">
        <button onclick="toggleMobileNav(true)" aria-label="Open Navigation Menu" class="md:hidden w-10 h-10 rounded-xl bg-slate-100 active:bg-slate-200 text-black flex items-center justify-center transition shadow-sm shrink-0">
          <i class="fa-solid fa-bars text-lg"></i>
        </button>
        
        <!-- Mobile Brand -->
        <div class="flex items-center gap-2 min-w-0 md:hidden">
          <img src="images/Logo.png" alt="Bhooma Medicare Hospital" class="h-8 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="hidden items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-black text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0 brand-badge">
              <i class="fa-solid fa-hospital"></i>
            </div>
            <div class="min-w-0">
              <div class="text-xs font-black tracking-tight leading-tight">
                <span class="text-black font-black">BHOOMA</span>
              </div>
              <div class="text-[8px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">
                Medicare Hospital &amp; I.C.U
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Page Context Breadcrumb / Live Status -->
        <div class="hidden md:flex items-center gap-2 text-xs font-bold text-slate-600">
          <span class="w-2.5 h-2.5 rounded-full bg-black inline-block animate-pulse"></span>
          <span class="text-slate-500 font-medium"><?php echo htmlspecialchars($hospital_name); ?></span>
          <span class="text-slate-300">/</span>
          <?php if ($isStaff): ?>
          <span class="bg-slate-900 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-md tracking-wider">Staff Terminal</span>
          <span class="text-slate-300">/</span>
          <?php endif; ?>
          <span class="text-black font-extrabold capitalize"><?php echo str_replace(['.php', '_'], ['', ' '], $current_page); ?></span>
        </div>
      </div>

      <!-- Right side: Desktop Quick Actions + Mobile AI & Logout -->
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Mobile User Badge Shortcut -->
        <button type="button" onclick="openLogoutModal()" class="md:hidden flex items-center gap-1.5 bg-slate-100 active:bg-slate-200 text-slate-800 font-extrabold px-2 py-1 rounded-xl border border-slate-200 shadow-2xs transition shrink-0" title="Logged in as <?php echo htmlspecialchars($userName); ?>">
          <div class="w-6 h-6 rounded-lg <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-xs shadow-2xs">
            <?php echo $userInitial; ?>
          </div>
          <span class="text-[11px] font-black max-w-[70px] truncate"><?php echo htmlspecialchars($userName); ?></span>
          <?php if ($isAdmin): ?>
          <i class="fa-solid fa-crown text-[8px] text-amber-500"></i>
          <?php endif; ?>
        </button>

        <!-- Mobile AI Quick Trigger -->
        <?php if ($isAdmin): ?>
        <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle();" class="md:hidden text-xs bg-slate-100 active:bg-slate-200 text-black font-extrabold px-2 py-1.5 rounded-xl flex items-center gap-1 border border-slate-200 shadow-2xs transition cursor-pointer" title="BHOOMA AI Assistant">
          <i class="fa-solid fa-robot text-black text-xs"></i>
          <span class="text-[11px]">AI</span>
        </button>
        <?php endif; ?>

        <!-- Mobile Logout Shortcut -->
        <button type="button" onclick="openLogoutModal()" class="md:hidden w-8 h-8 rounded-xl bg-slate-100 active:bg-slate-200 text-slate-700 hover:text-black flex items-center justify-center border border-slate-200 shadow-2xs transition shrink-0 cursor-pointer" title="Sign Out">
          <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
        </button>

        <!-- Desktop Quick Action Buttons (Visible on desktop; on mobile these are in the App Bottom Nav Bar) -->
        <div class="hidden md:flex items-center gap-2">
          <!-- Universal Scan QR Button -->
          <button type="button" onclick="openQRScannerModal()" class="text-xs bg-slate-900 hover:bg-black text-white font-extrabold px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs transition cursor-pointer" title="Scan Patient QR Code">
            <i class="fa-solid fa-qrcode text-indigo-400"></i>
            <span>Scan QR</span>
          </button>

          <!-- Live Pipeline Link -->
          <a href="queue.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-black border border-slate-200 font-extrabold px-2.5 sm:px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs transition <?php echo $current_page == 'queue.php' ? 'ring-2 ring-black font-black' : ''; ?>" title="Live OPD Queue">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Pipeline</span>
          </a>

          <!-- Patient Directory Link -->
          <a href="patients.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 font-extrabold px-2.5 sm:px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs transition <?php echo $current_page == 'patients.php' ? 'ring-2 ring-black font-black' : ''; ?>" title="Patient Directory">
            <i class="fa-solid fa-address-book text-slate-600"></i>
            <span>Patients</span>
          </a>

          <!-- More ▼ Features Dropdown (Hides secondary tools cleanly without deleting) -->
          <div class="relative" id="header-more-dropdown-wrapper">
            <button type="button" onclick="toggleHeaderMoreDropdown(event)" id="btn-header-more" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold px-2.5 sm:px-3 py-2 rounded-xl flex items-center gap-1 border border-slate-200 shadow-2xs transition cursor-pointer">
              <span>More</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200" id="header-more-chevron"></i>
            </button>
            
            <div id="header-more-menu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 transition-all max-h-[80vh] overflow-y-auto">
              <!-- Appointments & Front Desk -->
              <div class="px-3.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Front Desk &amp; Visits</div>
              <a href="index.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-hospital-user w-4 text-center text-slate-400"></i>
                <span>Reception Desk</span>
              </a>
              <a href="appointments.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-calendar-check w-4 text-center text-slate-400"></i>
                <span>Appointments Schedule</span>
              </a>
              <a href="book.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-calendar-plus w-4 text-center text-slate-400"></i>
                <span>Book Appointment</span>
              </a>
              <a href="doctor_slots.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-regular fa-clock w-4 text-center text-slate-400"></i>
                <span>Slot Timings &amp; Schedules</span>
              </a>

              <div class="h-px bg-slate-100 my-1 mx-2"></div>

              <!-- Facilities & Hospital Operations -->
              <div class="px-3.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Facilities &amp; Staff</div>
              <a href="beds.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-bed-pulse w-4 text-center text-slate-400"></i>
                <span>Bed Ward Management</span>
              </a>
              <a href="doctors.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-user-doctor w-4 text-center text-slate-400"></i>
                <span>Doctors Directory</span>
              </a>
              <a href="charges.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-receipt w-4 text-center text-slate-400"></i>
                <span>Consultation Charges</span>
              </a>
              <a href="staff.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-clipboard-user w-4 text-center text-slate-400"></i>
                <span>Staff &amp; Attendance</span>
              </a>
              <a href="selfie_attendance.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-camera w-4 text-center text-slate-400"></i>
                <span>Face Selfie Attendance</span>
              </a>

              <div class="h-px bg-slate-100 my-1 mx-2"></div>

              <!-- System & AI Tools -->
              <div class="px-3.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">System &amp; Tools</div>
              <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle(); toggleHeaderMoreDropdown();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition text-left cursor-pointer">
                <i class="fa-solid fa-robot w-4 text-center text-slate-400"></i>
                <span>BHOOMA AI Assistant</span>
              </button>
              <?php if ($isAdmin): ?>
              <a href="backup.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-database w-4 text-center text-slate-400"></i>
                <span>Database Backup</span>
              </a>
              <a href="restore.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-rotate-left w-4 text-center text-slate-400"></i>
                <span>Load / Restore Data</span>
              </a>
              <?php endif; ?>
              <a href="guide.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-book-open w-4 text-center text-slate-400"></i>
                <span>System User Guide</span>
              </a>
              <a href="about.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-circle-info w-4 text-center text-slate-400"></i>
                <span>About Hospital</span>
              </a>
            </div>
          </div>

          <!-- Top User Profile Pill (Gopalbhai / Staff) -->
          <div class="relative" id="header-user-dropdown-wrapper">
            <button type="button" onclick="toggleHeaderUserDropdown(event)" id="btn-header-user" class="text-xs bg-gradient-to-r from-slate-50 to-indigo-50/70 hover:from-slate-100 hover:to-indigo-100 text-slate-900 font-extrabold pl-2 pr-3 py-1.5 rounded-xl flex items-center gap-2 border border-slate-200/90 shadow-2xs transition cursor-pointer" title="<?php echo htmlspecialchars($userRole); ?>">
              <div class="w-7 h-7 rounded-lg <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-xs shadow-xs shrink-0 ring-1 ring-slate-300">
                <?php echo $userInitial; ?>
              </div>
              <div class="text-left hidden lg:block leading-tight">
                <div class="text-xs font-black text-slate-900 flex items-center gap-1">
                  <span><?php echo htmlspecialchars($userName); ?></span>
                  <?php if ($isAdmin): ?>
                  <i class="fa-solid fa-crown text-[10px] text-amber-500" title="Main Top Administrator"></i>
                  <?php endif; ?>
                </div>
                <div class="text-[9px] font-bold text-indigo-700 uppercase tracking-wider"><?php echo $isAdmin ? 'Main Admin' : htmlspecialchars($staffRole); ?></div>
              </div>
              <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200" id="header-user-chevron"></i>
            </button>

            <!-- User Profile Dropdown Menu -->
            <div id="header-user-menu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2.5 z-50 transition-all">
              <div class="px-4 py-2 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                  <div class="w-10 h-10 rounded-xl <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-sm shadow-sm ring-1 ring-slate-200 shrink-0">
                    <?php echo $userInitial; ?>
                  </div>
                  <div class="min-w-0">
                    <div class="font-black text-xs text-slate-900 truncate flex items-center gap-1">
                      <span><?php echo htmlspecialchars($userName); ?></span>
                      <?php if ($isAdmin): ?>
                      <i class="fa-solid fa-crown text-[10px] text-amber-500"></i>
                      <?php endif; ?>
                    </div>
                    <div class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider truncate">
                      <?php echo $isAdmin ? 'Hospital Owner & Super Admin' : htmlspecialchars($staffRole); ?>
                    </div>
                    <div class="text-[9px] text-slate-400 font-medium truncate mt-0.5">
                      <?php echo htmlspecialchars($hospital_name); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="py-1">
                <a href="dashboard.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-gauge-high w-4 text-center text-slate-400"></i>
                  <span>Command Dashboard</span>
                </a>
                <?php if ($isAdmin): ?>
                <a href="staff_permissions.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-user-shield w-4 text-center text-indigo-500"></i>
                  <span>Staff &amp; Permissions</span>
                </a>
                <a href="backup.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-database w-4 text-center text-slate-400"></i>
                  <span>Database Backup</span>
                </a>
                <?php endif; ?>
              </div>
              <div class="pt-2 px-3 border-t border-slate-100">
                <button type="button" onclick="openLogoutModal()" class="w-full py-2 px-3 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-700 hover:text-rose-600 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                  <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                  <span>Sign Out</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- ================= APP BOTTOM NAVIGATION BAR (LIKE MOBILE APP) ================= -->
    <nav id="app-bottom-navbar" class="fixed bottom-0 left-0 right-0 md:left-64 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] pb-[max(0.4rem,env(safe-area-inset-bottom))] pt-1.5 px-3 select-none">
      <div class="max-w-lg mx-auto grid grid-cols-5 items-center justify-items-center h-14">
        
        <!-- Tab 1: Dashboard / Home -->
        <a href="dashboard.php" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $current_page == 'dashboard.php' ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
          <div class="relative flex items-center justify-center">
            <i class="fa-solid fa-gauge-high text-lg sm:text-xl transition-transform group-active:scale-90 <?php echo $current_page == 'dashboard.php' ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
            <?php if ($current_page == 'dashboard.php'): ?>
            <span class="absolute -top-1 -right-1 w-1.5 h-1.5 rounded-full bg-black"></span>
            <?php endif; ?>
          </div>
          <span class="text-[10px] tracking-tight mt-1 <?php echo $current_page == 'dashboard.php' ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>">Dashboard</span>
        </a>

        <!-- Tab 2: Live Pipeline / Queue -->
        <a href="queue.php" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $current_page == 'queue.php' ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
          <div class="relative flex items-center justify-center">
            <i class="fa-solid fa-bars-staggered text-lg sm:text-xl transition-transform group-active:scale-90 <?php echo $current_page == 'queue.php' ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
            <!-- Pulsing live indicator dot -->
            <span class="absolute -top-1 -right-1.5 flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
          </div>
          <span class="text-[10px] tracking-tight mt-1 <?php echo $current_page == 'queue.php' ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>">Pipeline</span>
        </a>

        <!-- Tab 3: CENTER HERO SCAN QR BUTTON (Floating Raised App Button) -->
        <div class="flex flex-col items-center justify-center -mt-6 sm:-mt-7">
          <button type="button" onclick="openQRScannerModal()" class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-slate-900 active:scale-90 hover:bg-black text-white flex items-center justify-center shadow-lg shadow-slate-900/30 border-[3px] border-white ring-2 ring-slate-200/80 transition-all cursor-pointer group" title="Scan Patient QR Code">
            <i class="fa-solid fa-qrcode text-xl sm:text-2xl text-white group-hover:scale-110 transition-transform"></i>
          </button>
          <span class="text-[10px] font-black text-slate-900 tracking-tight mt-0.5">Scan QR</span>
        </div>

        <!-- Tab 4: Patients Directory -->
        <?php 
          $isPatientsActive = in_array($current_page, ['patients.php', 'patient_profile.php', 'patient_profile_qr.php', 'patient_file_view.php']); 
        ?>
        <a href="patients.php" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $isPatientsActive ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
          <div class="relative flex items-center justify-center">
            <i class="fa-solid fa-address-book text-lg sm:text-xl transition-transform group-active:scale-90 <?php echo $isPatientsActive ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
            <?php if ($isPatientsActive): ?>
            <span class="absolute -top-1 -right-1 w-1.5 h-1.5 rounded-full bg-black"></span>
            <?php endif; ?>
          </div>
          <span class="text-[10px] tracking-tight mt-1 <?php echo $isPatientsActive ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>">Patients</span>
        </a>

        <!-- Tab 5: More (Full App Menu Drawer / More Menu) -->
        <button type="button" onclick="handleBottomNavMore(event)" class="flex flex-col items-center justify-center w-full py-1 group text-slate-400 hover:text-slate-800 font-medium cursor-pointer transition-colors" title="All Features & Settings">
          <div class="relative flex items-center justify-center">
            <i class="fa-solid fa-bars text-lg sm:text-xl transition-transform group-active:scale-90 text-slate-500 group-hover:text-black"></i>
          </div>
          <span class="text-[10px] font-semibold text-slate-500 group-hover:text-black tracking-tight mt-1">More</span>
        </button>

      </div>
    </nav>

    <!-- Page Content Scrollable Area -->
    <main class="flex-1 overflow-y-auto custom-scrollbar p-3 sm:p-6 md:p-8 pb-28 sm:pb-28 md:pb-28 relative">
      
      <!-- Shared Toast Notification Banner -->
      <div id="toast-banner" class="hidden fixed top-4 right-4 sm:top-6 sm:right-6 z-[200] rounded-2xl border p-4 shadow-2xl flex items-start justify-between transition-all max-w-[92vw] sm:min-w-[320px] bg-white">
        <div class="flex items-center space-x-3">
          <i id="toast-icon" class="fa-solid fa-circle-check text-xl text-emerald-500 shrink-0"></i>
          <div>
            <h4 id="toast-title" class="font-bold text-sm text-slate-900"></h4>
            <p id="toast-msg" class="text-xs text-slate-500 mt-0.5 break-words"></p>
          </div>
        </div>
        <button onclick="closeToast()" class="text-slate-400 hover:text-slate-600 ml-3 shrink-0"><i class="fa-solid fa-xmark"></i></button>
      </div>
      
      <!-- Include Confirm, Logout Modals & Chatbot Widget -->
      <?php include_once __DIR__ . '/confirm_modal.php'; ?>
      <?php include_once __DIR__ . '/logout_modal.php'; ?>
      <?php include_once __DIR__ . '/qr_scanner_modal.php'; ?>
      <?php if ($isAdmin) include_once __DIR__ . '/chatbot_widget.php'; ?>

      <?php if (!empty($_SESSION['flash_error'])): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          showToast('Access Restricted', '<?php echo addslashes($_SESSION['flash_error']); ?>', 'error');
        });
      </script>
      <?php unset($_SESSION['flash_error']); endif; ?>

      <script>
        function handleBottomNavMore(e) {
          if (e) e.stopPropagation();
          if (window.innerWidth < 768) {
            toggleMobileNav(true);
          } else {
            toggleHeaderMoreDropdown(e);
          }
        }

        function toggleHeaderMoreDropdown(e) {
          if (e) e.stopPropagation();
          const menu = document.getElementById('header-more-menu');
          const chevron = document.getElementById('header-more-chevron');
          if (!menu) return;
          const isClosed = menu.classList.contains('hidden');
          if (isClosed) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
          } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
          }
        }

        function toggleHeaderUserDropdown(e) {
          if (e) e.stopPropagation();
          const menu = document.getElementById('header-user-menu');
          const chevron = document.getElementById('header-user-chevron');
          if (!menu) return;
          const isClosed = menu.classList.contains('hidden');
          if (isClosed) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
          } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
          }
        }

        document.addEventListener('click', function(e) {
          const wrapper = document.getElementById('header-more-dropdown-wrapper');
          const menu = document.getElementById('header-more-menu');
          const chevron = document.getElementById('header-more-chevron');
          if (wrapper && menu && !wrapper.contains(e.target)) {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
          }

          const userWrapper = document.getElementById('header-user-dropdown-wrapper');
          const userMenu = document.getElementById('header-user-menu');
          const userChevron = document.getElementById('header-user-chevron');
          if (userWrapper && userMenu && !userWrapper.contains(e.target)) {
            userMenu.classList.add('hidden');
            if (userChevron) userChevron.classList.remove('rotate-180');
          }
        });

        function toggleMobileNav(open) {
          const drawer = document.getElementById('mobile-nav-drawer');
          const backdrop = document.getElementById('mobile-nav-backdrop');
          if (!drawer || !backdrop) return;
          
          if (open) {
            backdrop.classList.remove('hidden');
            drawer.classList.remove('-translate-x-full');
            drawer.classList.add('translate-x-0');
            document.body.classList.add('overflow-hidden');
          } else {
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
          }
        }

        function showToast(title, msg, type = 'success') {
            const banner = document.getElementById('toast-banner');
            if (!banner) return;
            document.getElementById('toast-title').textContent = title;
            document.getElementById('toast-msg').textContent = msg;
            
            const icon = document.getElementById('toast-icon');
            icon.className = type === 'success' ? 'fa-solid fa-circle-check text-xl text-emerald-500 shrink-0' : 'fa-solid fa-circle-exclamation text-xl text-rose-500 shrink-0';
            
            banner.classList.remove('hidden');
            setTimeout(closeToast, 4000);
        }
        function closeToast() {
            const banner = document.getElementById('toast-banner');
            if (banner) banner.classList.add('hidden');
        }
        function logout() {
            openLogoutModal();
        }

        // Expandable / Collapsible Navigation Sections
        function toggleNavGroup(groupId) {
            const el = document.getElementById(groupId);
            const chevron = document.getElementById('chevron-' + groupId);
            if (!el) return;

            const isHidden = el.classList.contains('hidden');
            if (isHidden) {
                el.classList.remove('hidden');
                if (chevron) chevron.classList.remove('-rotate-90');
                localStorage.setItem('nav_sec_' + groupId, 'open');
            } else {
                el.classList.add('hidden');
                if (chevron) chevron.classList.add('-rotate-90');
                localStorage.setItem('nav_sec_' + groupId, 'closed');
            }
        }

        function initNavSections() {
            const sections = [
                'sec-overview', 'sec-frontdesk', 'sec-appointments', 'sec-staff', 'sec-system',
                'mob-sec-overview', 'mob-sec-frontdesk', 'mob-sec-appointments', 'mob-sec-staff', 'mob-sec-system'
            ];
            
            sections.forEach(secId => {
                const el = document.getElementById(secId);
                const chevron = document.getElementById('chevron-' + secId);
                if (!el) return;

                // Always keep the active section open so current page is visible
                const hasActive = el.querySelector('.nav-item.active') !== null;
                if (hasActive) {
                    el.classList.remove('hidden');
                    if (chevron) chevron.classList.remove('-rotate-90');
                    return;
                }

                const savedState = localStorage.getItem('nav_sec_' + secId);
                if (savedState === 'closed') {
                    el.classList.add('hidden');
                    if (chevron) chevron.classList.add('-rotate-90');
                } else {
                    el.classList.remove('hidden');
                    if (chevron) chevron.classList.remove('-rotate-90');
                }
            });
        }

        // Maintain sidebar scroll position across page reloads & link clicks
        (function() {
            function initSidebarScroll() {
                initNavSections();

                const nav = document.getElementById('desktop-sidebar-nav');
                if (!nav) return;

                // Restore saved scroll position
                const savedPos = sessionStorage.getItem('sidebar_scroll_top');
                if (savedPos !== null) {
                    nav.scrollTop = parseInt(savedPos, 10);
                } else {
                    // If no saved position, ensure active nav item is visible
                    const activeItem = nav.querySelector('.nav-item.active');
                    if (activeItem) {
                        activeItem.scrollIntoView({ block: 'nearest' });
                    }
                }

                // Save scroll position on user scroll
                nav.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebar_scroll_top', nav.scrollTop);
                }, { passive: true });

                // Also save before user navigates via link
                nav.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('sidebar_scroll_top', nav.scrollTop);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarScroll);
            } else {
                initSidebarScroll();
            }
        })();
      </script>

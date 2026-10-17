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

// Active group detection for top header nav
$isOverviewActive = in_array($current_page, ['dashboard.php', 'about.php']);
$isFrontDeskActive = in_array($current_page, ['index.php', 'patients.php', 'patient_profile.php', 'patient_profile_qr.php', 'patient_file_view.php', 'history.php']);
$isAppointmentsActive = in_array($current_page, ['appointments.php', 'book.php', 'doctor_slots.php', 'online_consult.php']);
$isStaffActive = in_array($current_page, ['doctors.php', 'staff.php', 'staff_face_test.php', 'selfie_attendance.php', 'beds.php', 'charges.php', 'staff_permissions.php']);
$isSystemActive = in_array($current_page, ['backup.php', 'restore.php', 'ai_logs.php', 'ai_training_dashboard.php', 'guide.php']);
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
    body { 
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
      background-color: #f8fafc; 
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    .no-scrollbar::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }
    .no-scrollbar {
      -ms-overflow-style: none !important;
      scrollbar-width: none !important;
    }

    /* Invisible hover bridge: guarantees mouse never leaves dropdown container during transition */
    .header-dropdown-container {
      position: relative;
    }
    .header-dropdown-container::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      top: 100%;
      height: 14px;
      z-index: 45;
      pointer-events: auto;
    }
    .header-dropdown-menu {
      pointer-events: auto;
    }
    .header-dropdown-menu::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 14px;
      z-index: 45;
      pointer-events: auto;
    }

    /* Top Header Dropdown Hover on Desktop (lg+) */
    @media (min-width: 1024px) {
      .header-dropdown-container:hover > .header-dropdown-menu {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) scale(1) !important;
        pointer-events: auto !important;
      }
      .header-dropdown-container:hover .header-dropdown-chevron {
        transform: rotate(180deg) !important;
      }
    }

    /* Bottom Navigation Bar: strictly mobile phone only (< 768px) */
    @media (min-width: 768px) {
      #app-bottom-navbar,
      nav#app-bottom-navbar {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
        height: 0 !important;
        max-height: 0 !important;
        width: 0 !important;
        overflow: hidden !important;
        position: fixed !important;
        bottom: -9999px !important;
        left: -9999px !important;
        z-index: -9999 !important;
      }
    }

    @media (max-width: 767.98px) {
      #app-bottom-navbar {
        display: block !important;
      }
    }
  </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-slate-900 selection:text-white">

  <!-- ================= TOP MAIN REAL HEADER NAVBAR ================= -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.05)] transition-all">
    <div class="w-full max-w-[1720px] mx-auto px-3 sm:px-5 lg:px-6 xl:px-8 h-16 sm:h-[70px] flex items-center justify-between gap-2 xl:gap-3">
      
      <!-- Left Section: Mobile Hamburger + Brand Logo -->
      <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        <!-- Mobile Drawer Hamburger Toggle (lg:hidden) -->
        <button type="button" onclick="toggleMobileNav(true)" aria-label="Open Navigation Menu" class="lg:hidden w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-800 flex items-center justify-center transition shadow-2xs shrink-0 cursor-pointer">
          <i class="fa-solid fa-bars text-sm sm:text-base"></i>
        </button>

        <!-- Brand Logo & Hospital Title -->
        <a href="dashboard.php" class="flex items-center gap-2 sm:gap-2.5 group py-1">
          <img src="images/Logo.png" alt="Bhooma Medicare Hospital" class="h-8 sm:h-9 w-auto object-contain transition-transform duration-300 group-hover:scale-105" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="hidden items-center gap-2">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-black text-white flex items-center justify-center text-sm font-black shadow-md shrink-0 group-hover:scale-105 transition-transform duration-300">
              <i class="fa-solid fa-hospital"></i>
            </div>
            <div class="min-w-0">
              <div class="text-xs sm:text-sm font-black tracking-tight leading-tight text-slate-900">
                <span>BHOOMA</span>
              </div>
              <div class="text-[8px] sm:text-[9px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">
                Medicare Hospital &amp; I.C.U
              </div>
            </div>
          </div>
          <!-- Live pulse badge -->
          <span class="hidden 2xl:inline-flex items-center gap-1.5 ml-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-[10px] font-bold text-emerald-700 whitespace-nowrap" title="System Operational &amp; Live">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live</span>
          </span>
        </a>
      </div>

      <!-- Center Section: Real Desktop Navigation with Proper Dropdowns (lg:flex) -->
      <nav class="hidden lg:flex items-center gap-1 xl:gap-1.5 shrink-0" aria-label="Main Navigation">
        
        <?php if ($isAdmin): ?>
        <!-- ================= ADMIN NAVIGATION ================= -->

        <!-- 1. Overview Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-overview', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo $isOverviewActive ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90 shadow-2xs' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-chart-pie text-xs <?php echo $isOverviewActive ? 'text-black' : 'text-slate-500'; ?>"></i>
            <span>Overview</span>
            <i id="chev-nav-drop-overview" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-overview" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-56 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 px-1.5 space-y-0.5">
              <a href="dashboard.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'dashboard.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-gauge-high"></i>
                </div>
                <span>Command Dashboard</span>
              </a>
              <a href="queue.php" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'queue.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                    <i class="fa-solid fa-bars-staggered"></i>
                  </div>
                  <span>Live Pipeline</span>
                </div>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
              </a>
              <a href="about.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'about.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
                <span>About Hospital</span>
              </a>
            </div>
          </div>
        </div>

        <!-- 2. Front Desk Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-frontdesk', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo $isFrontDeskActive ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90 shadow-2xs' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-hospital-user text-xs <?php echo $isFrontDeskActive ? 'text-black' : 'text-slate-500'; ?>"></i>
            <span>Front Desk</span>
            <i id="chev-nav-drop-frontdesk" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-frontdesk" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-56 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 px-1.5 space-y-0.5">
              <a href="index.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'index.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-hospital-user"></i>
                </div>
                <span>Reception Desk</span>
              </a>
              <a href="patients.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'patients.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-address-book"></i>
                </div>
                <span>Patient Directory</span>
              </a>
              <a href="history.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'history.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-file-medical"></i>
                </div>
                <span>Medical Records</span>
              </a>
              <div class="h-px bg-slate-100 my-1 mx-2"></div>
              <button type="button" onclick="openQRScannerModal(); closeAllNavDropdowns();" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group text-slate-700 hover:bg-indigo-50 hover:text-indigo-900 text-left cursor-pointer">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-xs text-indigo-600 transition shrink-0">
                  <i class="fa-solid fa-qrcode"></i>
                </div>
                <span>Scan Patient QR</span>
              </button>
            </div>
          </div>
        </div>

        <!-- 3. Appointments Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-appts', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo $isAppointmentsActive ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90 shadow-2xs' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-calendar-check text-xs <?php echo $isAppointmentsActive ? 'text-black' : 'text-slate-500'; ?>"></i>
            <span>Appointments</span>
            <i id="chev-nav-drop-appts" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-appts" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-60 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 px-1.5 space-y-0.5">
              <a href="appointments.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'appointments.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-calendar-check"></i>
                </div>
                <span>Appointments Schedule</span>
              </a>
              <a href="book.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'book.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <span>Book Appointment</span>
              </a>
              <a href="doctor_slots.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'doctor_slots.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-regular fa-clock"></i>
                </div>
                <span>Manage Slot Timings</span>
              </a>
              <a href="online_consult.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'online_consult.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-laptop-medical"></i>
                </div>
                <span>Online Consultations</span>
              </a>
            </div>
          </div>
        </div>

        <!-- 4. Staff & Facilities Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-staff', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo $isStaffActive ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90 shadow-2xs' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-user-doctor text-xs <?php echo $isStaffActive ? 'text-black' : 'text-slate-500'; ?>"></i>
            <span>Staff &amp; Wards</span>
            <i id="chev-nav-drop-staff" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-staff" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-60 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 px-1.5 space-y-0.5">
              <a href="doctors.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'doctors.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-user-doctor"></i>
                </div>
                <span>Doctors Directory</span>
              </a>
              <a href="staff.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'staff.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <span>Staff &amp; Attendance</span>
              </a>
              <a href="selfie_attendance.php" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'selfie_attendance.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                    <i class="fa-solid fa-camera"></i>
                  </div>
                  <span>Selfie Attendance</span>
                </div>
                <span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded shrink-0">Daily</span>
              </a>
              <a href="staff_face_test.php" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'staff_face_test.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-purple-50 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-purple-700 transition shrink-0">
                    <i class="fa-solid fa-face-viewfinder"></i>
                  </div>
                  <span>Face Recognition Lab</span>
                </div>
                <span class="text-[9px] font-extrabold uppercase bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded shrink-0">AI Test</span>
              </a>
              <a href="beds.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'beds.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-bed-pulse"></i>
                </div>
                <span>Bed Ward &amp; ICU</span>
              </a>
              <a href="charges.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'charges.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-receipt"></i>
                </div>
                <span>Consultation Charges</span>
              </a>
              <div class="h-px bg-slate-100 my-1 mx-2"></div>
              <a href="staff_permissions.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'staff_permissions.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-indigo-700 transition shrink-0">
                  <i class="fa-solid fa-user-shield"></i>
                </div>
                <span>Staff Permissions</span>
              </a>
            </div>
          </div>
        </div>

        <!-- 5. System & AI Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-system', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo $isSystemActive ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90 shadow-2xs' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-sliders text-xs <?php echo $isSystemActive ? 'text-black' : 'text-slate-500'; ?>"></i>
            <span>System &amp; AI</span>
            <i id="chev-nav-drop-system" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-system" class="header-dropdown-menu hidden absolute top-full right-0 xl:left-0 pt-2 z-50">
            <div class="w-60 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 px-1.5 space-y-0.5">
              <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle(); closeAllNavDropdowns();" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition group text-slate-700 hover:bg-slate-900 hover:text-white text-left cursor-pointer">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                    <i class="fa-solid fa-robot"></i>
                  </div>
                  <span>BHOOMA AI Assistant</span>
                </div>
                <span class="text-[9px] font-mono bg-slate-200 text-black px-1.5 py-0.5 rounded group-hover:bg-slate-800 group-hover:text-white shrink-0">Ctrl+K</span>
              </button>
              <a href="ai_training_dashboard.php" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'ai_training_dashboard.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                    <i class="fa-solid fa-microchip"></i>
                  </div>
                  <span>AI Training &amp; GPU Lab</span>
                </div>
                <span class="text-[9px] font-black uppercase bg-black text-white px-1.5 py-0.5 rounded shrink-0">LIVE</span>
              </a>
              <a href="ai_logs.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'ai_logs.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-brain"></i>
                </div>
                <span>AI Chat History &amp; Logs</span>
              </a>
              <div class="h-px bg-slate-100 my-1 mx-2"></div>
              <a href="backup.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'backup.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-database"></i>
                </div>
                <span>Database Backup</span>
              </a>
              <a href="restore.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'restore.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-rotate-left"></i>
                </div>
                <span>Load / Restore Data</span>
              </a>
              <a href="guide.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition group <?php echo $current_page == 'guide.php' ? 'bg-slate-100 font-bold text-black border-l-2 border-black' : 'text-slate-700 hover:bg-slate-50 hover:text-black'; ?>">
                <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-black group-hover:text-white flex items-center justify-center text-xs text-slate-700 transition shrink-0">
                  <i class="fa-solid fa-book-open"></i>
                </div>
                <span>System User Guide</span>
              </a>
            </div>
          </div>
        </div>

        <?php else: ?>
        <!-- ================= STAFF SPECIFIC NAVIGATION ================= -->

        <!-- Workspace Dropdown -->
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-staff-work', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo in_array($current_page, ['dashboard.php', 'selfie_attendance.php', 'queue.php']) ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-gauge-high text-xs text-slate-500"></i>
            <span>My Workspace</span>
            <i id="chev-nav-drop-staff-work" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-staff-work" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2 px-1.5">
              <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-gauge-high w-4 text-center text-slate-500"></i>
                <span>Staff Dashboard</span>
              </a>
              <a href="selfie_attendance.php" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <div class="flex items-center gap-3">
                  <i class="fa-solid fa-camera w-4 text-center text-slate-500"></i>
                  <span>Selfie Attendance</span>
                </div>
                <span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">Daily</span>
              </a>
              <?php if ($canAssignTokens || $canManageAppts): ?>
              <a href="queue.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-bars-staggered w-4 text-center text-slate-500"></i>
                <span>Live Pipeline</span>
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Patient Care Dropdown -->
        <?php if ($canViewPatients || $canUpload || $canViewFiles): ?>
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-staff-pat', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo in_array($current_page, ['patients.php', 'history.php']) ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-hospital-user text-xs text-slate-500"></i>
            <span>Patient Care</span>
            <i id="chev-nav-drop-staff-pat" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-staff-pat" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2 px-1.5">
              <?php if ($canViewPatients || $canUpload): ?>
              <a href="patients.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-address-book w-4 text-center text-slate-500"></i>
                <span>Patient Directory</span>
              </a>
              <button type="button" onclick="openQRScannerModal(); closeAllNavDropdowns();" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition text-left cursor-pointer">
                <i class="fa-solid fa-qrcode w-4 text-center text-indigo-600"></i>
                <span>Scan Patient QR</span>
              </button>
              <?php endif; ?>
              <?php if ($canViewFiles): ?>
              <a href="history.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-file-medical w-4 text-center text-slate-500"></i>
                <span>Medical Records</span>
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Appointments Dropdown -->
        <?php if ($canManageAppts): ?>
        <div class="relative header-dropdown-container">
          <button type="button" onclick="toggleHeaderNavDropdown('nav-drop-staff-appts', event)" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap <?php echo in_array($current_page, ['appointments.php', 'book.php']) ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90' : 'text-slate-700 hover:text-black hover:bg-slate-100/80'; ?>">
            <i class="fa-solid fa-calendar-check text-xs text-slate-500"></i>
            <span>Appointments</span>
            <i id="chev-nav-drop-staff-appts" class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200"></i>
          </button>
          
          <div id="nav-drop-staff-appts" class="header-dropdown-menu hidden absolute top-full left-0 pt-2 z-50">
            <div class="w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2 px-1.5">
              <a href="appointments.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-calendar-check w-4 text-center text-slate-500"></i>
                <span>Appointments Schedule</span>
              </a>
              <a href="book.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                <i class="fa-solid fa-calendar-plus w-4 text-center text-slate-500"></i>
                <span>Book Appointment</span>
              </a>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Telemedicine -->
        <?php if ($canConsultOnline): ?>
        <a href="online_consult.php" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold text-slate-700 hover:text-black hover:bg-slate-100 transition whitespace-nowrap <?php echo $current_page == 'online_consult.php' ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90' : ''; ?>">
          <i class="fa-solid fa-laptop-medical text-xs text-slate-500"></i>
          <span>Consultations</span>
        </a>
        <?php endif; ?>

        <!-- Help Link -->
        <a href="guide.php" class="h-9 flex items-center gap-1.5 px-2.5 xl:px-3 rounded-xl text-xs font-bold text-slate-700 hover:text-black hover:bg-slate-100 transition whitespace-nowrap <?php echo $current_page == 'guide.php' ? 'bg-slate-100 text-black font-extrabold ring-1 ring-slate-200/90' : ''; ?>">
          <i class="fa-solid fa-book-open text-xs text-slate-500"></i>
          <span>Guide</span>
        </a>

        <?php endif; ?>

      </nav>

      <!-- Right Section: Fast Quick Action Buttons + Clock + User Profile -->
      <div class="flex items-center gap-1.5 xl:gap-2 shrink-0">
        
        <!-- 1. Universal Fast QR Scanner Button -->
        <button type="button" onclick="openQRScannerModal()" class="h-9 text-xs bg-slate-900 hover:bg-black text-white font-extrabold px-2.5 sm:px-3 rounded-xl flex items-center gap-1.5 shadow-2xs transition active:scale-95 cursor-pointer whitespace-nowrap shrink-0" title="Scan Patient QR Code">
          <i class="fa-solid fa-qrcode text-indigo-400"></i>
          <span class="hidden sm:inline">Scan QR</span>
        </button>

        <!-- 2. Fast Live Pipeline Pill -->
        <a href="queue.php" class="hidden sm:flex h-9 items-center gap-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 font-bold px-2.5 xl:px-3 rounded-xl shadow-2xs transition whitespace-nowrap shrink-0 <?php echo $current_page == 'queue.php' ? 'ring-2 ring-black font-black' : ''; ?>" title="Live OPD Queue &amp; Patient Tokens">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="hidden xl:inline">Live Pipeline</span>
          <span class="xl:hidden">Pipeline</span>
        </a>

        <!-- 3. Digital Live Clock (2xl:flex only to preserve space on standard laptops) -->
        <div class="hidden 2xl:flex h-9 items-center gap-1.5 text-xs text-slate-600 font-bold px-2.5 bg-slate-100/80 rounded-xl border border-slate-200/60 whitespace-nowrap shrink-0" title="Hospital Local Time">
          <i class="fa-regular fa-clock text-slate-400"></i>
          <span id="clock" class="font-mono">--:--</span>
        </div>

        <!-- 4. User Profile Dropdown Pill -->
        <div class="relative header-dropdown-container shrink-0">
          <button type="button" onclick="toggleHeaderUserDropdown(event)" id="btn-header-user" class="h-9 text-xs bg-gradient-to-r from-slate-50 to-indigo-50/70 hover:from-slate-100 hover:to-indigo-100 text-slate-900 font-extrabold pl-1.5 pr-2.5 xl:pl-2 xl:pr-3 rounded-xl flex items-center gap-2 border border-slate-200 shadow-2xs transition cursor-pointer whitespace-nowrap" title="<?php echo htmlspecialchars($userRole); ?>">
            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-xs shadow-xs shrink-0 ring-1 ring-slate-300">
              <?php echo $userInitial; ?>
            </div>
            <div class="text-left hidden md:block leading-tight max-w-[90px] xl:max-w-[120px] truncate">
              <div class="text-xs font-black text-slate-900 flex items-center gap-1 truncate">
                <span class="truncate"><?php echo htmlspecialchars($userName); ?></span>
                <?php if ($isAdmin): ?>
                <i class="fa-solid fa-crown text-[8px] text-amber-500" title="Super Administrator"></i>
                <?php endif; ?>
              </div>
              <div class="text-[8px] font-bold text-indigo-700 uppercase tracking-wider truncate hidden xl:block"><?php echo $isAdmin ? 'Super Admin' : htmlspecialchars($staffRole); ?></div>
            </div>
            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 header-dropdown-chevron transition-transform duration-200" id="header-user-chevron"></i>
          </button>

          <!-- User Profile Dropdown Panel with pt-2 wrapper -->
          <div id="header-user-menu" class="header-dropdown-menu hidden absolute top-full right-0 pt-2 z-50">
            <div class="w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2.5">
              <div class="px-4 py-2.5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-sm shadow-sm ring-1 ring-slate-200 shrink-0">
                    <?php echo $userInitial; ?>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="font-black text-xs text-slate-900 truncate flex items-center gap-1.5">
                      <span class="truncate"><?php echo htmlspecialchars($userName); ?></span>
                      <?php if ($isAdmin): ?>
                      <i class="fa-solid fa-crown text-[10px] text-amber-500" title="Hospital Owner"></i>
                      <?php endif; ?>
                    </div>
                    <div class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider truncate">
                      <?php echo $isAdmin ? 'Hospital Owner & Super Admin' : htmlspecialchars($staffRole); ?>
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5 flex items-center gap-1">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                      <span><?php echo htmlspecialchars($hospital_name); ?></span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="py-1.5 px-1.5">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-gauge-high w-4 text-center text-slate-400"></i>
                  <span>Command Dashboard</span>
                </a>
                <?php if ($isAdmin): ?>
                <a href="staff_permissions.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-user-shield w-4 text-center text-indigo-500"></i>
                  <span>Staff &amp; Permissions</span>
                </a>
                <a href="backup.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-database w-4 text-center text-slate-400"></i>
                  <span>Database Backup</span>
                </a>
                <?php endif; ?>
                <a href="guide.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-book-open w-4 text-center text-slate-400"></i>
                  <span>User Guide</span>
                </a>
                <a href="about.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-black transition">
                  <i class="fa-solid fa-circle-info w-4 text-center text-slate-400"></i>
                  <span>About Hospital</span>
                </a>
              </div>

              <div class="pt-2 px-3 border-t border-slate-100">
                <button type="button" onclick="openLogoutModal()" class="w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-700 hover:text-rose-600 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer border border-slate-200/60 hover:border-rose-200">
                  <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                  <span>Sign Out</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </header>

  <!-- ================= MOBILE OFF-CANVAS NAVIGATION DRAWER ================= -->
  <div id="mobile-nav-backdrop" onclick="toggleMobileNav(false)" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 transition-opacity duration-300 lg:hidden"></div>

  <aside id="mobile-nav-drawer" class="fixed inset-y-0 left-0 w-80 max-w-[85vw] bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl lg:hidden">
    <!-- Drawer Header -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100 shrink-0">
      <a href="dashboard.php" class="flex items-center gap-2.5 min-w-0">
        <img src="images/Logo.png" alt="Bhooma Medicare Hospital" class="h-8 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="hidden items-center gap-2 min-w-0">
          <div class="w-8 h-8 rounded-xl bg-black text-white flex items-center justify-center text-sm font-black shadow-md shrink-0">
            <i class="fa-solid fa-hospital"></i>
          </div>
          <div class="min-w-0">
            <div class="text-sm font-black tracking-tight leading-tight text-slate-900">BHOOMA</div>
            <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">Medicare Hospital</div>
          </div>
        </div>
      </a>
      <button onclick="toggleMobileNav(false)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 flex items-center justify-center transition shrink-0 cursor-pointer">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Drawer Navigation Content -->
    <nav class="flex-1 px-4 py-4 space-y-4 overflow-y-auto no-scrollbar font-medium">
      
      <?php if ($isAdmin): ?>
      <!-- Section: Overview -->
      <div>
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1 px-2">Overview</div>
        <div class="space-y-0.5">
          <a href="dashboard.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'dashboard.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-gauge-high w-4 text-center"></i>
            <span>Command Dashboard</span>
          </a>
          <a href="queue.php" onclick="toggleMobileNav(false)" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'queue.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-bars-staggered w-4 text-center"></i>
              <span>Live Pipeline</span>
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          </a>
        </div>
      </div>

      <!-- Section: Front Desk -->
      <div>
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1 px-2">Front Desk &amp; Patients</div>
        <div class="space-y-0.5">
          <a href="index.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'index.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-hospital-user w-4 text-center"></i>
            <span>Reception Desk</span>
          </a>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'patients.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-address-book w-4 text-center"></i>
            <span>Patient Directory</span>
          </a>
          <a href="history.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'history.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-file-medical w-4 text-center"></i>
            <span>Medical Records</span>
          </a>
          <button type="button" onclick="toggleMobileNav(false); openQRScannerModal();" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100 text-left">
            <i class="fa-solid fa-qrcode w-4 text-center"></i>
            <span>Scan Patient QR</span>
          </button>
        </div>
      </div>

      <!-- Section: Appointments -->
      <div>
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1 px-2">Appointments</div>
        <div class="space-y-0.5">
          <a href="appointments.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'appointments.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-4 text-center"></i>
            <span>Appointments Schedule</span>
          </a>
          <a href="book.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'book.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-4 text-center"></i>
            <span>Book Appointment</span>
          </a>
          <a href="doctor_slots.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'doctor_slots.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-regular fa-clock w-4 text-center"></i>
            <span>Manage Slot Timings</span>
          </a>
        </div>
      </div>

      <!-- Section: Staff & Wards -->
      <div>
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1 px-2">Staff &amp; Facilities</div>
        <div class="space-y-0.5">
          <a href="doctors.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'doctors.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-4 text-center"></i>
            <span>Doctors Directory</span>
          </a>
          <a href="staff.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'staff.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-4 text-center"></i>
            <span>Staff &amp; Attendance</span>
          </a>
          <a href="selfie_attendance.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'selfie_attendance.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-camera w-4 text-center"></i>
            <span>Selfie Attendance</span>
          </a>
          <a href="staff_face_test.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'staff_face_test.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-face-viewfinder w-4 text-center text-purple-600"></i>
            <span>Face Recognition Lab</span>
          </a>
          <a href="beds.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'beds.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-4 text-center"></i>
            <span>Bed Ward Management</span>
          </a>
          <a href="charges.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'charges.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-receipt w-4 text-center"></i>
            <span>Consultation Charges</span>
          </a>
          <a href="staff_permissions.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'staff_permissions.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-user-shield w-4 text-center"></i>
            <span>Staff Permissions</span>
          </a>
        </div>
      </div>

      <!-- Section: System & AI -->
      <div>
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1 px-2">System &amp; AI</div>
        <div class="space-y-0.5">
          <button type="button" onclick="toggleMobileNav(false); if(window.BhoomaAI) BhoomaAI.toggle();" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white text-left">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-robot w-4 text-center"></i>
              <span>BHOOMA AI Assistant</span>
            </div>
            <span class="text-[9px] font-bold bg-slate-800 text-slate-200 px-1.5 py-0.5 rounded">Ctrl+K</span>
          </button>
          <a href="ai_training_dashboard.php" onclick="toggleMobileNav(false)" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'ai_training_dashboard.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-microchip w-4 text-center"></i>
              <span>AI Training &amp; GPU Lab</span>
            </div>
            <span class="text-[9px] font-black bg-black text-white px-1.5 py-0.5 rounded">LIVE</span>
          </a>
          <a href="ai_logs.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'ai_logs.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-brain w-4 text-center"></i>
            <span>AI Logs &amp; Audits</span>
          </a>
          <a href="backup.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'backup.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-database w-4 text-center"></i>
            <span>Database Backup</span>
          </a>
          <a href="restore.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'restore.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-4 text-center"></i>
            <span>Load / Restore Data</span>
          </a>
          <a href="guide.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-black <?php echo $current_page == 'guide.php' ? 'bg-slate-100 text-black font-extrabold' : ''; ?>">
            <i class="fa-solid fa-book-open w-4 text-center"></i>
            <span>System User Guide</span>
          </a>
        </div>
      </div>

      <?php else: ?>
      <!-- Staff Mobile Navigation Links -->
      <div class="space-y-1">
        <a href="dashboard.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-gauge-high w-4 text-center"></i> Staff Dashboard
        </a>
        <a href="selfie_attendance.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-camera w-4 text-center"></i> Selfie Attendance
        </a>
        <?php if ($canAssignTokens || $canManageAppts): ?>
        <a href="queue.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-bars-staggered w-4 text-center"></i> Live Pipeline
        </a>
        <?php endif; ?>
        <?php if ($canViewPatients || $canUpload): ?>
        <a href="patients.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-address-book w-4 text-center"></i> Patient Directory
        </a>
        <button type="button" onclick="toggleMobileNav(false); openQRScannerModal();" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 text-left">
          <i class="fa-solid fa-qrcode w-4 text-center"></i> Scan Patient QR
        </button>
        <?php endif; ?>
        <?php if ($canViewFiles): ?>
        <a href="history.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-file-medical w-4 text-center"></i> Medical Records
        </a>
        <?php endif; ?>
        <?php if ($canManageAppts): ?>
        <a href="appointments.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-calendar-check w-4 text-center"></i> Appointments Schedule
        </a>
        <a href="book.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-calendar-plus w-4 text-center"></i> Book Appointment
        </a>
        <?php endif; ?>
        <a href="guide.php" onclick="toggleMobileNav(false)" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-book-open w-4 text-center"></i> User Guide
        </a>
      </div>
      <?php endif; ?>

    </nav>

    <!-- Drawer Footer User Card & Logout -->
    <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-3">
      <div class="flex items-center gap-2.5 px-1">
        <div class="w-9 h-9 rounded-xl <?php echo $isAdmin ? 'bg-gradient-to-tr from-indigo-700 via-indigo-900 to-black text-white' : 'bg-slate-900 text-white'; ?> flex items-center justify-center font-black text-sm shadow-sm shrink-0">
          <?php echo $userInitial; ?>
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-xs font-black text-slate-900 truncate flex items-center gap-1">
            <span><?php echo htmlspecialchars($userName); ?></span>
            <?php if ($isAdmin): ?>
            <i class="fa-solid fa-crown text-[9px] text-amber-500"></i>
            <?php endif; ?>
          </div>
          <div class="text-[10px] text-indigo-700 font-extrabold uppercase tracking-wider truncate">
            <?php echo $isAdmin ? 'Super Admin' : htmlspecialchars($staffRole); ?>
          </div>
        </div>
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
      </div>

      <button type="button" onclick="openLogoutModal()" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-800 py-3 rounded-xl transition text-xs font-bold border border-slate-200 shadow-2xs cursor-pointer">
        <i class="fa-solid fa-arrow-right-from-bracket text-slate-600"></i> Sign Out
      </button>
    </div>
  </aside>

  <!-- ================= APP BOTTOM NAVIGATION BAR (PHONE VIEW ONLY - HIDDEN ON PC) ================= -->
  <?php
    $tab2Link = 'queue.php';
    $tab2Label = 'Pipeline';
    $tab2Icon = 'fa-solid fa-bars-staggered';
    $tab2IsLive = true;
    $tab2Active = ($current_page == 'queue.php');

    if ($isStaff && !$isAdmin) {
      if ($canAssignTokens || $canManageAppts) {
        $tab2Link = 'queue.php';
        $tab2Label = 'Pipeline';
        $tab2Icon = 'fa-solid fa-bars-staggered';
        $tab2IsLive = true;
        $tab2Active = ($current_page == 'queue.php');
      } elseif ($canManageAppts) {
        $tab2Link = 'appointments.php';
        $tab2Label = 'Appts';
        $tab2Icon = 'fa-solid fa-calendar-check';
        $tab2IsLive = false;
        $tab2Active = in_array($current_page, ['appointments.php', 'book.php']);
      } else {
        $tab2Link = 'selfie_attendance.php';
        $tab2Label = 'Attendance';
        $tab2Icon = 'fa-solid fa-camera';
        $tab2IsLive = false;
        $tab2Active = ($current_page == 'selfie_attendance.php');
      }
    }

    $tab4Link = 'patients.php';
    $tab4Label = 'Patients';
    $tab4Icon = 'fa-solid fa-address-book';
    $tab4Active = in_array($current_page, ['patients.php', 'patient_profile.php', 'patient_profile_qr.php', 'patient_file_view.php']);

    if ($isStaff && !$isAdmin && !$canViewPatients && !$canUpload) {
      if ($canViewFiles) {
        $tab4Link = 'history.php';
        $tab4Label = 'Records';
        $tab4Icon = 'fa-solid fa-file-medical';
        $tab4Active = ($current_page == 'history.php');
      } else {
        $tab4Link = 'guide.php';
        $tab4Label = 'Guide';
        $tab4Icon = 'fa-solid fa-book-open';
        $tab4Active = ($current_page == 'guide.php');
      }
    }

    $isMoreActive = !in_array($current_page, [
      'dashboard.php',
      $tab2Link,
      'patients.php', 'patient_profile.php', 'patient_profile_qr.php', 'patient_file_view.php'
    ]);
  ?>
  <nav id="app-bottom-navbar" class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-1 px-2 select-none">
    <div class="max-w-md mx-auto grid grid-cols-5 items-center justify-items-center h-14">
      
      <!-- Tab 1: Dashboard -->
      <a href="dashboard.php" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $current_page == 'dashboard.php' ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
        <div class="relative flex items-center justify-center w-8 h-7">
          <i class="fa-solid fa-gauge-high text-lg transition-transform group-active:scale-90 <?php echo $current_page == 'dashboard.php' ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
          <?php if ($current_page == 'dashboard.php'): ?>
          <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-black"></span>
          <?php endif; ?>
        </div>
        <span class="text-[10px] tracking-tight mt-0.5 <?php echo $current_page == 'dashboard.php' ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>">Dashboard</span>
      </a>

      <!-- Tab 2: Live Pipeline / Queue -->
      <a href="<?php echo htmlspecialchars($tab2Link); ?>" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $tab2Active ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
        <div class="relative flex items-center justify-center w-8 h-7">
          <i class="<?php echo $tab2Icon; ?> text-lg transition-transform group-active:scale-90 <?php echo $tab2Active ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
          <?php if ($tab2IsLive): ?>
          <span class="absolute -top-0.5 -right-1 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <?php elseif ($tab2Active): ?>
          <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-black"></span>
          <?php endif; ?>
        </div>
        <span class="text-[10px] tracking-tight mt-0.5 <?php echo $tab2Active ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>"><?php echo htmlspecialchars($tab2Label); ?></span>
      </a>

      <!-- Tab 3: CENTER HERO SCAN QR BUTTON -->
      <div class="flex flex-col items-center justify-center -mt-6">
        <button type="button" onclick="openQRScannerModal()" class="rounded-2xl bg-black active:scale-90 hover:bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-black/25 border-[3px] border-white ring-1 ring-slate-200 transition-all cursor-pointer group" style="width: 52px; height: 52px;" title="Scan Patient QR Code">
          <i class="fa-solid fa-qrcode text-2xl text-white group-hover:scale-110 transition-transform"></i>
        </button>
        <span class="text-[10px] font-black text-slate-900 tracking-tight mt-0.5">Scan QR</span>
      </div>

      <!-- Tab 4: Patients Directory -->
      <a href="<?php echo htmlspecialchars($tab4Link); ?>" class="flex flex-col items-center justify-center w-full py-1 group transition-colors <?php echo $tab4Active ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>">
        <div class="relative flex items-center justify-center w-8 h-7">
          <i class="<?php echo $tab4Icon; ?> text-lg transition-transform group-active:scale-90 <?php echo $tab4Active ? 'text-black' : 'text-slate-400 group-hover:text-slate-700'; ?>"></i>
          <?php if ($tab4Active): ?>
          <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-black"></span>
          <?php endif; ?>
        </div>
        <span class="text-[10px] tracking-tight mt-0.5 <?php echo $tab4Active ? 'font-black text-black' : 'font-semibold text-slate-500'; ?>"><?php echo htmlspecialchars($tab4Label); ?></span>
      </a>

      <!-- Tab 5: Menu / More Drawer Trigger -->
      <button type="button" onclick="toggleMobileNav(true)" class="flex flex-col items-center justify-center w-full py-1 group cursor-pointer transition-colors <?php echo $isMoreActive ? 'text-black font-black' : 'text-slate-400 hover:text-slate-800 font-medium'; ?>" title="All Features &amp; Settings">
        <div class="relative flex items-center justify-center w-8 h-7">
          <i class="fa-solid fa-bars text-lg transition-transform group-active:scale-90 <?php echo $isMoreActive ? 'text-black' : 'text-slate-500 group-hover:text-black'; ?>"></i>
          <?php if ($isMoreActive): ?>
          <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-black"></span>
          <?php endif; ?>
        </div>
        <span class="text-[10px] tracking-tight mt-0.5 <?php echo $isMoreActive ? 'font-black text-black' : 'font-semibold text-slate-500 group-hover:text-black'; ?>">Menu</span>
      </button>

    </div>
  </nav>

  <!-- ================= MAIN CONTENT WRAPPER ================= -->
  <div class="flex-1 flex flex-col w-full min-h-0 relative">
    
    <!-- Page Content Container -->
    <main class="flex-1 w-full max-w-[1720px] mx-auto p-3 sm:p-6 lg:p-8 pb-24 md:pb-12 relative">
      
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
      
      <!-- Include Modals & Chatbot Widget -->
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
        // Header Dropdown Navigation Logic
        function toggleHeaderNavDropdown(menuId, event) {
          if (event) {
            event.stopPropagation();
            event.preventDefault();
          }
          const menu = document.getElementById(menuId);
          const chev = document.getElementById('chev-' + menuId);
          if (!menu) return;

          const isClosed = menu.classList.contains('hidden');

          // Close all dropdowns
          closeAllNavDropdowns();

          if (isClosed) {
            menu.classList.remove('hidden');
            if (chev) chev.classList.add('rotate-180');
          }
        }

        function toggleHeaderUserDropdown(event) {
          if (event) {
            event.stopPropagation();
            event.preventDefault();
          }
          const menu = document.getElementById('header-user-menu');
          const chev = document.getElementById('header-user-chevron');
          if (!menu) return;

          const isClosed = menu.classList.contains('hidden');
          closeAllNavDropdowns();

          if (isClosed) {
            menu.classList.remove('hidden');
            if (chev) chev.classList.add('rotate-180');
          }
        }

        function closeAllNavDropdowns() {
          document.querySelectorAll('.header-dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
          });
          document.querySelectorAll('.header-dropdown-chevron').forEach(chev => {
            chev.classList.remove('rotate-180');
          });
          const userMenu = document.getElementById('header-user-menu');
          const userChev = document.getElementById('header-user-chevron');
          if (userMenu) userMenu.classList.add('hidden');
          if (userChev) userChev.classList.remove('rotate-180');
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.header-dropdown-container')) {
            closeAllNavDropdowns();
          }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            closeAllNavDropdowns();
            toggleMobileNav(false);
          }
          // Global Ctrl+K / Cmd+K for AI
          if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (window.BhoomaAI) {
              BhoomaAI.toggle();
            }
          }
        });

        // Mobile Off-Canvas Drawer Logic
        function toggleMobileNav(open) {
          const drawer = document.getElementById('mobile-nav-drawer');
          const backdrop = document.getElementById('mobile-nav-backdrop');
          const aiBtn = document.getElementById('bhooma-ai-btn-container');
          if (!drawer || !backdrop) return;
          
          if (open) {
            closeAllNavDropdowns();
            backdrop.classList.remove('hidden');
            drawer.classList.remove('-translate-x-full');
            drawer.classList.add('translate-x-0');
            document.body.classList.add('overflow-hidden');
            if (aiBtn) aiBtn.style.setProperty('display', 'none', 'important');
          } else {
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (aiBtn) aiBtn.style.removeProperty('display');
          }
        }

        // Programmatic enforcement to guarantee bottom nav is NEVER visible on PC/Desktop
        function checkBottomNavVisibility() {
          const nav = document.getElementById('app-bottom-navbar');
          if (!nav) return;
          if (window.innerWidth >= 768) {
            nav.style.setProperty('display', 'none', 'important');
          } else {
            nav.style.removeProperty('display');
          }
        }
        window.addEventListener('resize', checkBottomNavVisibility, { passive: true });
        document.addEventListener('DOMContentLoaded', checkBottomNavVisibility);
        checkBottomNavVisibility();

        // Toast Helpers
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
      </script>

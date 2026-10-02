<?php
$current_page = basename($_SERVER['PHP_SELF']);
$hospital_name = $_SESSION['hospital_name'] ?? 'CarePulse';
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
        color: #0f172a;
        font-weight: 700;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        border-left: 3px solid #3b82f6;
    }
    .nav-item:hover:not(.active) {
        background: #f8fafc;
        color: #0f172a;
    }
  </style>
</head>
<body class="text-slate-800 h-screen flex overflow-hidden">

  <!-- ================= DESKTOP SIDEBAR ================= -->
  <aside class="w-64 glass-sidebar flex flex-col hidden md:flex shrink-0 shadow-sm z-20 relative">
    <!-- Brand -->
    <div class="h-20 flex items-center px-4 shrink-0 border-b border-slate-200/80">
      <a href="dashboard.php" class="flex items-center gap-3 w-full py-1 group">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-teal-500 text-white flex items-center justify-center text-lg font-black shadow-md shadow-indigo-500/25 shrink-0 group-hover:scale-105 transition-transform duration-300">
          <i class="fa-solid fa-hospital"></i>
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-sm font-black tracking-tight leading-tight">
            <span class="bg-gradient-to-r from-blue-700 via-indigo-600 to-teal-600 bg-clip-text text-transparent">BHOOMA</span>
          </div>
          <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate mt-0.5">
            Medicare Hospital &amp; I.C.U
          </div>
        </div>
      </a>
    </div>

    <!-- Navigation without visible scrollbar line + scroll position remembered -->
    <nav id="desktop-sidebar-nav" class="flex-1 px-3 py-4 space-y-1 overflow-y-auto no-scrollbar font-medium">
      
      <!-- Group 1: Overview -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-overview')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Overview</span>
          <i id="chevron-sec-overview" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-overview" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center text-indigo-600"></i>
            <span class="text-sm">Dashboard</span>
          </a>
          <a href="queue.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-6 text-center text-indigo-600"></i>
            <span class="text-sm">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Front Desk & Patients -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-frontdesk')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Front Desk</span>
          <i id="chevron-sec-frontdesk" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-frontdesk" class="space-y-0.5 mt-1 transition-all">
          <a href="index.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-hospital-user w-6 text-center text-emerald-600"></i>
            <span class="text-sm">Reception Desk</span>
          </a>
          <a href="patients.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-6 text-center text-emerald-600"></i>
            <span class="text-sm">Patient Directory</span>
          </a>
          <a href="history.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-6 text-center text-emerald-600"></i>
            <span class="text-sm">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments & Schedules -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-appointments')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Appointments</span>
          <i id="chevron-sec-appointments" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-appointments" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-6 text-center text-purple-600"></i>
            <span class="text-sm">Appointments Schedule</span>
          </a>
          <a href="book.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-6 text-center text-purple-600"></i>
            <span class="text-sm">Book Appointment</span>
          </a>
          <a href="doctor_slots.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'doctor_slots.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-clock w-6 text-center text-purple-600"></i>
            <span class="text-sm">Manage Slot Timings</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 4: Medical Staff & Wards -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-staff')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Staff & Facilities</span>
          <i id="chevron-sec-staff" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-staff" class="space-y-0.5 mt-1 transition-all">
          <a href="doctors.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-6 text-center text-amber-600"></i>
            <span class="text-sm">Doctors Directory</span>
          </a>
          <a href="staff.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-6 text-center text-amber-600"></i>
            <span class="text-sm">Staff & Attendance</span>
          </a>
          <a href="beds.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'beds.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-6 text-center text-amber-600"></i>
            <span class="text-sm">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 5: System & Data -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-system')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">System & Data</span>
          <i id="chevron-sec-system" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-system" class="space-y-0.5 mt-1 transition-all">
          <a href="backup.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-database w-6 text-center text-rose-600"></i>
            <span class="text-sm">Database Backup</span>
          </a>
          <a href="restore.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'restore.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-6 text-center text-rose-600"></i>
            <span class="text-sm">Load / Restore Data</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 6: Help & Information -->
      <div>
        <button type="button" onclick="toggleNavGroup('sec-help')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Help &amp; Info</span>
          <i id="chevron-sec-help" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="sec-help" class="space-y-0.5 mt-1 transition-all">
          <a href="guide.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'guide.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open w-6 text-center text-sky-600"></i>
            <span class="text-sm">System User Guide</span>
          </a>
          <a href="about.php" class="nav-item flex items-center gap-3 px-3 py-2 text-slate-600 <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-info w-6 text-center text-sky-600"></i>
            <span class="text-sm">About Hospital</span>
          </a>
        </div>
      </div>
      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- AI Assistant Button in Sidebar -->
      <div class="px-1">
        <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle();" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-100 hover:from-blue-100 hover:to-indigo-100 text-indigo-700 transition shadow-sm font-semibold text-left cursor-pointer">
          <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs shrink-0 shadow-sm">
            <i class="fa-solid fa-robot"></i>
          </div>
          <span class="text-sm font-semibold">BHOOMA AI</span>
          <span class="ml-auto text-[10px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded border border-indigo-200">Ctrl+K</span>
        </button>
      </div>

    </nav>

    <!-- User badge bottom -->
    <div class="p-4 border-t border-slate-200/60 bg-slate-50/50">
      <button onclick="openLogoutModal()" class="nav-item w-full flex items-center justify-center gap-2 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-600 py-2.5 rounded-xl transition text-sm font-bold shadow-sm border border-slate-200 hover:border-rose-200 group relative">
          <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- ================= MOBILE DRAWER NAVIGATION ================= -->
  <div id="mobile-nav-backdrop" onclick="toggleMobileNav(false)" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 transition-opacity duration-300 md:hidden"></div>

  <aside id="mobile-nav-drawer" class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl md:hidden">
    <!-- Drawer Header -->
    <div class="h-20 flex items-center justify-between px-4 border-b border-slate-100 shrink-0">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-teal-500 text-white flex items-center justify-center text-base font-black shadow-md shadow-indigo-500/25 shrink-0">
          <i class="fa-solid fa-hospital"></i>
        </div>
        <div class="min-w-0">
          <div class="text-sm font-black tracking-tight leading-tight">
            <span class="bg-gradient-to-r from-blue-700 via-indigo-600 to-teal-600 bg-clip-text text-transparent">BHOOMA</span>
          </div>
          <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">
            Medicare Hospital &amp; I.C.U
          </div>
        </div>
      </div>
      <button onclick="toggleMobileNav(false)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Drawer Links without visible scrollbar line -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
      
      <!-- Group 1: Overview -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-overview')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Overview</span>
          <i id="chevron-mob-sec-overview" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-overview" class="space-y-0.5 mt-1 transition-all">
          <a href="dashboard.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center text-indigo-600"></i>
            <span class="text-sm font-semibold">Dashboard</span>
          </a>
          <a href="queue.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-5 text-center text-indigo-600"></i>
            <span class="text-sm font-semibold">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 2: Front Desk & Patients -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-frontdesk')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Front Desk</span>
          <i id="chevron-mob-sec-frontdesk" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-frontdesk" class="space-y-0.5 mt-1 transition-all">
          <a href="index.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-hospital-user w-5 text-center text-emerald-600"></i>
            <span class="text-sm font-semibold">Reception Desk</span>
          </a>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-5 text-center text-emerald-600"></i>
            <span class="text-sm font-semibold">Patient Directory</span>
          </a>
          <a href="history.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-5 text-center text-emerald-600"></i>
            <span class="text-sm font-semibold">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 3: Appointments & Schedules -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-appointments')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Appointments</span>
          <i id="chevron-mob-sec-appointments" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-appointments" class="space-y-0.5 mt-1 transition-all">
          <a href="appointments.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-5 text-center text-purple-600"></i>
            <span class="text-sm font-semibold">Appointments Schedule</span>
          </a>
          <a href="book.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus w-5 text-center text-purple-600"></i>
            <span class="text-sm font-semibold">Book Appointment</span>
          </a>
          <a href="doctor_slots.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'doctor_slots.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-clock w-5 text-center text-purple-600"></i>
            <span class="text-sm font-semibold">Manage Slot Timings</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 4: Medical Staff & Wards -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-staff')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Staff & Facilities</span>
          <i id="chevron-mob-sec-staff" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-staff" class="space-y-0.5 mt-1 transition-all">
          <a href="doctors.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-5 text-center text-amber-600"></i>
            <span class="text-sm font-semibold">Doctors Directory</span>
          </a>
          <a href="staff.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-5 text-center text-amber-600"></i>
            <span class="text-sm font-semibold">Staff & Attendance</span>
          </a>
          <a href="beds.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'beds.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-5 text-center text-amber-600"></i>
            <span class="text-sm font-semibold">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 5: System & Data -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-system')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">System & Data</span>
          <i id="chevron-mob-sec-system" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-system" class="space-y-0.5 mt-1 transition-all">
          <a href="backup.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-database w-5 text-center text-rose-600"></i>
            <span class="text-sm font-semibold">Database Backup</span>
          </a>
          <a href="restore.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'restore.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-5 text-center text-rose-600"></i>
            <span class="text-sm font-semibold">Load / Restore Data</span>
          </a>
        </div>
      </div>

      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- Group 6: Help & Information -->
      <div>
        <button type="button" onclick="toggleNavGroup('mob-sec-help')" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 transition group text-left cursor-pointer">
          <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Help &amp; Info</span>
          <i id="chevron-mob-sec-help" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200"></i>
        </button>
        <div id="mob-sec-help" class="space-y-0.5 mt-1 transition-all">
          <a href="guide.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'guide.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open w-5 text-center text-sky-600"></i>
            <span class="text-sm font-semibold">System User Guide</span>
          </a>
          <a href="about.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-700 <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-info w-5 text-center text-sky-600"></i>
            <span class="text-sm font-semibold">About Hospital</span>
          </a>
        </div>
      <!-- Prominent Separator Line -->
      <div class="h-px bg-slate-200 my-2 mx-1"></div>

      <!-- AI Assistant Button in Sidebar -->
      <div class="px-1">
        <button type="button" onclick="if(window.BhoomaAI) BhoomaAI.toggle();" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-100 hover:from-blue-100 hover:to-indigo-100 text-indigo-700 transition shadow-sm font-semibold text-left cursor-pointer">
          <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs shrink-0 shadow-sm">
            <i class="fa-solid fa-robot"></i>
          </div>
          <span class="text-sm font-semibold">BHOOMA AI</span>
          <span class="ml-auto text-[10px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded border border-indigo-200">Ctrl+K</span>
        </button>
      </div>

    </nav>

    <!-- Drawer Footer -->
    <div class="p-4 border-t border-slate-100 bg-slate-50">
      <button onclick="openLogoutModal()" class="w-full flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 py-3 rounded-xl transition text-sm font-bold border border-rose-200">
        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
      </button>
    </div>
  </aside>

  <!-- ================= MAIN CONTENT WRAPPER ================= -->
  <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
    
    <!-- Top Header Mobile -->
    <header class="md:hidden h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 shrink-0 z-30 shadow-sm">
      <div class="flex items-center gap-2.5 min-w-0">
        <button onclick="toggleMobileNav(true)" aria-label="Open Navigation Menu" class="w-10 h-10 rounded-xl bg-slate-100 active:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-sm shrink-0">
          <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-teal-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">
            <i class="fa-solid fa-hospital"></i>
          </div>
          <div class="min-w-0">
            <div class="text-xs font-black tracking-tight leading-tight">
              <span class="bg-gradient-to-r from-blue-700 via-indigo-600 to-teal-600 bg-clip-text text-transparent">BHOOMA</span>
            </div>
            <div class="text-[8px] font-extrabold uppercase tracking-wider text-slate-500 leading-tight truncate">
              Medicare Hospital &amp; I.C.U
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="queue.php" class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Pipeline
        </a>
      </div>
    </header>

    <!-- Page Content Scrollable Area -->
    <main class="flex-1 overflow-y-auto custom-scrollbar p-3 sm:p-6 md:p-8 relative">
      
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
      <?php include_once __DIR__ . '/chatbot_widget.php'; ?>

      <script>
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

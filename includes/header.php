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
    .glass-sidebar {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-right: 1px solid rgba(0, 0, 0, 0.05);
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .apple-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0,0,0,0.02);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease, border-color 0.2s ease;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }
    .apple-card:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06), 0 2px 4px rgba(0,0,0,0.03);
    }
    .nav-item { transition: all 0.2s ease; }
    .nav-item.active { background: #eff6ff; box-shadow: inset 2px 0 0 0 #3b82f6; color: #1e3a8a; font-weight: 700; border-radius: 12px; }
    .nav-item:hover:not(.active) { background: #f1f5f9; border-radius: 12px; }
  </style>
</head>
<body class="text-slate-800 h-screen flex overflow-hidden">

  <!-- ================= DESKTOP SIDEBAR ================= -->
  <aside class="w-64 glass-sidebar flex flex-col hidden md:flex shrink-0 shadow-sm z-20 relative">
    <!-- Brand -->
    <div class="h-20 flex items-center px-5 shrink-0 border-b border-slate-200/60">
      <a href="dashboard.php" class="flex items-center justify-center group w-full py-2">
        <img src="images/Logo.png" alt="Hospital Logo" class="max-h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105" onerror="this.style.display='none'">
      </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto custom-scrollbar font-medium">
      
      <!-- Group 1 -->
      <div>
        <div class="px-3 mb-2 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Overview</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1">
          <a href="dashboard.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Dashboard</span>
          </a>
          <a href="queue.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'queue.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Group 2 -->
      <div>
        <div class="px-3 mb-2 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Front Desk</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1">
          <a href="index.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-plus w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Reception Desk</span>
          </a>
          <a href="book.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'book.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Book Appointment</span>
          </a>
          <a href="patients.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-address-book w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Patient Directory</span>
          </a>
          <a href="history.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-medical w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Group 3 -->
      <div>
        <div class="px-3 mb-2 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Staff & Facilities</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1">
          <a href="doctors.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Doctors & Slots</span>
          </a>
          <a href="staff.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Staff & Attendance</span>
          </a>
          <a href="beds.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'beds.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-6 text-center  text-blue-600"></i>
            <span class="text-sm">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Group 4 -->
      <div>
        <div class="px-3 mb-2 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">System & Data</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1">
          <a href="backup.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-database w-6 text-center text-blue-600"></i>
            <span class="text-sm">Database Backup</span>
          </a>
          <a href="restore.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-slate-600 <?php echo $current_page == 'restore.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-6 text-center text-emerald-600"></i>
            <span class="text-sm">Load / Restore Data</span>
          </a>
        </div>
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
    <div class="h-20 flex items-center justify-between px-5 border-b border-slate-100 shrink-0">
      <div class="flex items-center min-w-0">
        <img src="images/Logo.png" alt="Hospital Logo" class="max-h-10 w-auto object-contain" onerror="this.style.display='none'">
      </div>
      <button onclick="toggleMobileNav(false)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Drawer Links -->
    <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto custom-scrollbar">
      
      <!-- Group 1 -->
      <div>
        <div class="px-4 mb-3 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Overview</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1.5">
          <a href="dashboard.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'dashboard.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Dashboard</span>
          </a>
          <a href="queue.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'queue.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-bars-staggered w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Live Pipeline</span>
          </a>
        </div>
      </div>

      <!-- Group 2 -->
      <div>
        <div class="px-4 mb-3 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Front Desk</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1.5">
          <a href="index.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'index.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-user-plus w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Reception Desk</span>
          </a>
          <a href="book.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'book.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-calendar-check w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Book Appointment</span>
          </a>
          <a href="patients.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'patients.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-address-book w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Patient Directory</span>
          </a>
          <a href="history.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'history.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-file-medical w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Medical Records</span>
          </a>
        </div>
      </div>

      <!-- Group 3 -->
      <div>
        <div class="px-4 mb-3 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">Staff & Facilities</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1.5">
          <a href="doctors.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'doctors.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-user-doctor w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Doctors & Slots</span>
          </a>
          <a href="staff.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'staff.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-clipboard-user w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Staff & Attendance</span>
          </a>
          <a href="beds.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'beds.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-bed-pulse w-5 text-center  text-blue-600"></i>
            <span class="text-sm font-semibold">Bed Ward</span>
          </a>
        </div>
      </div>

      <!-- Group 4 -->
      <div>
        <div class="px-4 mb-3 flex items-center"><span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md shadow-sm">System & Data</span><div class="h-px bg-slate-100 flex-1 ml-3"></div></div>
        <div class="space-y-1.5">
          <a href="backup.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'backup.php' ? 'active bg-blue-50 text-blue-700' : ''; ?>">
            <i class="fa-solid fa-database w-5 text-center text-blue-600"></i>
            <span class="text-sm font-semibold">Database Backup</span>
          </a>
          <a href="restore.php" onclick="toggleMobileNav(false)" class="nav-item flex items-center gap-3 px-4 py-3 text-slate-700 hover:text-slate-900 <?php echo $current_page == 'restore.php' ? 'active bg-emerald-50 text-emerald-700' : ''; ?>">
            <i class="fa-solid fa-rotate-left w-5 text-center text-emerald-600"></i>
            <span class="text-sm font-semibold">Load / Restore Data</span>
          </a>
        </div>
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
        <div class="flex items-center min-w-0">
          <img src="images/Logo.png" alt="Hospital Logo" class="max-h-8 w-auto object-contain shrink-0" onerror="this.style.display='none'">
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
      
      <!-- Include Confirm & Logout Modals -->
      <?php include_once 'includes/confirm_modal.php'; ?>
      <?php include_once 'includes/logout_modal.php'; ?>

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
      </script>

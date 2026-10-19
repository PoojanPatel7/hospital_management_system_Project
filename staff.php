<?php
require_once 'auth.php';
include 'includes/header.php';
?>

<div class="space-y-5">

  <!-- ================= TOP HEADER & VIEW CONTROLS ================= -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 uppercase tracking-wider">Hospital Operations</span>
        <span class="text-xs font-semibold text-slate-400">• Staff Roster</span>
      </div>
      <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Staff & Attendance</h2>
      <p class="text-slate-500 text-xs sm:text-sm font-medium mt-0.5">Manage hospital staff registry, user accounts, and real-time attendance rosters.</p>
    </div>

    <!-- Action Buttons & Tools Bar -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
      <!-- Face Attendance Kiosk Button -->
      <a href="face_attendance.php" class="flex-1 sm:flex-initial justify-center bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2.5 px-3.5 sm:px-4 rounded-xl shadow-md shadow-purple-500/20 transition flex items-center gap-2 text-xs shrink-0" title="Open Live Camera AI Attendance Kiosk">
        <i class="fa-solid fa-face-viewfinder text-sm"></i>
        <span>AI Attendance Kiosk</span>
      </a>

      <!-- Quick Face Enrollment Button -->
      <button onclick="openFaceEnrollModal(0)" class="flex-1 sm:flex-initial justify-center bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-bold py-2.5 px-3 rounded-xl transition flex items-center gap-2 text-xs shrink-0" title="Enroll / Capture Staff Face Biometrics">
        <i class="fa-solid fa-camera text-xs"></i>
        <span>Enroll Face</span>
      </button>

      <!-- Quick Mark All Present -->
      <button onclick="quickMarkAllPresent()" id="btn-quick-mark" class="flex-1 sm:flex-initial justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-3.5 rounded-xl shadow-sm transition flex items-center gap-2 text-xs shrink-0" title="Quick Mark All Active Staff as Present for Selected Date">
        <i class="fa-solid fa-check-double text-xs"></i>
        <span>Mark All Present</span>
      </button>

      <!-- Add Staff Primary Button -->
      <button onclick="openStaffModal()" class="flex-1 sm:flex-initial justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-2 text-xs shrink-0">
        <i class="fa-solid fa-user-plus text-xs"></i>
        <span>+ Add Staff</span>
      </button>

      <!-- Tools Dropdown Menu -->
      <div class="relative inline-block text-left" id="tools-dropdown-container">
        <button type="button" onclick="toggleToolsMenu()" class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold py-2.5 px-3.5 rounded-xl shadow-xs transition flex items-center gap-2 text-xs">
          <i class="fa-solid fa-sliders text-slate-500"></i>
          <span>Tools</span>
          <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
        </button>
        <div id="tools-menu" class="hidden absolute right-0 mt-1.5 w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 text-xs divide-y divide-slate-100">
          <div class="py-1">
            <a href="face_attendance.php" class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 flex items-center gap-2.5 text-slate-700 font-semibold hover:text-emerald-700">
              <i class="fa-solid fa-face-viewfinder text-emerald-600 text-sm"></i>
              <div>
                <div class="font-bold">Face Attendance Kiosk</div>
                <div class="text-[10px] text-slate-400 font-normal">Real-time camera attendance scan</div>
              </div>
            </a>
            <button type="button" onclick="openFaceEnrollModal(0)" class="w-full text-left px-3.5 py-2 hover:bg-purple-50 flex items-center gap-2.5 text-slate-700 font-semibold hover:text-purple-700">
              <i class="fa-solid fa-camera text-purple-600 text-sm"></i>
              <div>
                <div class="font-bold">Enroll Staff Face</div>
                <div class="text-[10px] text-slate-400 font-normal">Register / update face biometrics</div>
              </div>
            </button>
            <a href="staff_face_test.php" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-semibold hover:text-purple-700">
              <i class="fa-solid fa-microchip text-slate-500 text-sm"></i>
              <div>
                <div class="font-bold">Face Recognition Lab</div>
                <div class="text-[10px] text-slate-400 font-normal">Biometric camera enroll &amp; test</div>
              </div>
            </a>
            <a href="staff_permissions.php" class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 flex items-center gap-2.5 text-slate-700 font-semibold hover:text-indigo-700">
              <i class="fa-solid fa-user-shield text-indigo-600 text-sm"></i>
              <div>
                <div class="font-bold">Accounts & Permissions</div>
                <div class="text-[10px] text-slate-400 font-normal">Login credentials & roles</div>
              </div>
            </a>
          </div>
          <div class="py-1">
            <button type="button" onclick="exportCurrentViewCSV()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-semibold">
              <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
              <span>Export Roster to CSV</span>
            </button>
            <button type="button" onclick="window.print()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-semibold">
              <i class="fa-solid fa-print text-blue-600 text-sm"></i>
              <span>Print Current Roster</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Hidden export container for backward compatibility -->
      <div id="export-dropdown-container" class="hidden">
        <div id="export-menu" class="hidden"></div>
      </div>
    </div>
  </div>

  <!-- ================= VIEW NAVIGATION TABS ================= -->
  <div class="flex items-center justify-between gap-3 overflow-x-auto no-scrollbar pb-1">
    <div class="bg-slate-100 p-1 rounded-2xl flex items-center border border-slate-200 text-xs font-semibold shadow-inner shrink-0">
      <button id="btn-view-roster" onclick="switchView('roster')" class="px-3.5 sm:px-4 py-2 rounded-xl bg-white shadow-xs text-slate-900 font-extrabold transition flex items-center gap-2">
        <i class="fa-solid fa-calendar-check text-blue-600"></i>
        <span>Daily Roster</span>
      </button>
      <button id="btn-view-directory" onclick="switchView('directory')" class="px-3.5 sm:px-4 py-2 rounded-xl text-slate-600 hover:text-slate-900 font-bold transition flex items-center gap-2">
        <i class="fa-solid fa-id-card-clip text-slate-500"></i>
        <span>Staff Directory</span>
      </button>
      <button id="btn-view-monthly" onclick="switchView('monthly')" class="px-3.5 sm:px-4 py-2 rounded-xl text-slate-600 hover:text-slate-900 font-bold transition flex items-center gap-2">
        <i class="fa-solid fa-table-cells text-slate-500"></i>
        <span>Monthly Sheet</span>
      </button>
    </div>

    <!-- Active View Helper Info -->
    <div class="hidden md:flex items-center gap-2 text-xs text-slate-500 shrink-0">
      <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Present</span>
      <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Late</span>
      <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-500"></span> Half Day</span>
      <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Absent</span>
      <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Leave</span>
    </div>
  </div>

  <!-- ================= KPI STATS HIGHLIGHTS ================= -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <!-- Active Staff -->
    <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/80 shadow-2xs">
      <div class="flex items-center justify-between">
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Total Staff</span>
        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-2">
        <span id="kpi-total-staff" class="text-2xl sm:text-3xl font-black text-slate-900">0</span>
        <span class="text-[11px] font-semibold text-slate-400">Active Roster</span>
      </div>
    </div>

    <!-- Present Today -->
    <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/80 shadow-2xs">
      <div class="flex items-center justify-between">
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-emerald-700">Present</span>
        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-check"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-2">
        <span id="kpi-present" class="text-2xl sm:text-3xl font-black text-emerald-700">0</span>
        <span id="kpi-rate" class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full">0% rate</span>
      </div>
      <span id="kpi-present-label" class="hidden">On duty</span>
    </div>

    <!-- Late & Half Day -->
    <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/80 shadow-2xs">
      <div class="flex items-center justify-between">
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-amber-700">Late & Half Day</span>
        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
          <i class="fa-regular fa-clock"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-2">
        <span id="kpi-late" class="text-2xl sm:text-3xl font-black text-amber-700">0</span>
        <span class="text-xs font-semibold text-slate-500">Late / <strong id="kpi-halfday" class="text-sky-700">0</strong> Half</span>
      </div>
    </div>

    <!-- Absent & Leave -->
    <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/80 shadow-2xs">
      <div class="flex items-center justify-between">
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-rose-700">Absent & Leave</span>
        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-calendar-xmark"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-2">
        <span id="kpi-absent" class="text-2xl sm:text-3xl font-black text-rose-700">0</span>
        <span id="kpi-leave-detail" class="text-xs font-semibold text-slate-500">0 On Leave</span>
      </div>
      <span id="kpi-unmarked" class="hidden">0 unrecorded</span>
    </div>
  </div>

  <!-- ================= DATE NAVIGATION & FILTER BAR ================= -->
  <div class="apple-card p-4 sm:p-5 bg-white border border-slate-200/90 shadow-sm space-y-3.5">
    
    <!-- Row 1: Date & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      
      <!-- Date Selector Control (Active in Daily Roster & Directory) -->
      <div id="date-controls-group" class="flex flex-wrap items-center gap-2">
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1 shadow-2xs">
          <button onclick="navigateDate(-1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition flex items-center justify-center text-xs" title="Previous Day">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <input type="date" id="selected-date-input" onchange="onDateChanged(this.value)" class="text-xs font-bold bg-transparent text-slate-800 px-2 py-1 outline-none border-none cursor-pointer">
          <button onclick="navigateDate(1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition flex items-center justify-center text-xs" title="Next Day">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>

        <button onclick="setTodayDate()" id="btn-today-pill" class="text-xs font-bold px-3 py-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
          Today
        </button>

        <span id="selected-date-label" class="text-xs font-bold text-slate-600 pl-1 hidden sm:inline">
          Loading date...
        </span>
      </div>

      <!-- Month Selector (Hidden unless in Monthly Sheet view) -->
      <div id="month-controls-group" class="hidden flex-wrap items-center gap-2">
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1 shadow-2xs">
          <button onclick="navigateMonth(-1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition flex items-center justify-center text-xs" title="Previous Month">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <input type="month" id="selected-month-input" onchange="onMonthChanged(this.value)" class="text-xs font-bold bg-transparent text-slate-800 px-2.5 py-1 outline-none border-none cursor-pointer">
          <button onclick="navigateMonth(1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition flex items-center justify-center text-xs" title="Next Month">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>

        <button onclick="setCurrentMonth()" class="text-xs font-bold px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
          This Month
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
          type="text" 
          id="filter-search" 
          oninput="debounceFilter()" 
          placeholder="Search staff by name, code, role, or phone..." 
          class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition shadow-2xs">
        <button onclick="clearSearch()" id="btn-clear-search" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-200 transition">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <!-- Row 2: Secondary Filters & Category Selectors -->
    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2.5">
      <div class="flex flex-wrap items-center gap-2">
        <!-- Department Filter -->
        <select id="filter-department" onchange="applyFilters()" class="text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition shadow-2xs">
          <option value="All">All Departments</option>
          <option value="Nursing">Nursing</option>
          <option value="Pharmacy">Pharmacy</option>
          <option value="Laboratory">Laboratory</option>
          <option value="Reception">Reception</option>
          <option value="Radiology">Radiology</option>
          <option value="Administration">Administration</option>
          <option value="Emergency">Emergency</option>
          <option value="Maintenance">Maintenance</option>
          <option value="Security">Security</option>
          <option value="General Medicine">General Medicine</option>
        </select>

        <!-- Shift Filter -->
        <select id="filter-shift" onchange="applyFilters()" class="text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition shadow-2xs">
          <option value="All">All Shifts</option>
          <option value="Morning">Morning</option>
          <option value="Evening">Evening</option>
          <option value="Night">Night</option>
          <option value="General">General</option>
        </select>

        <!-- Attendance Filter (Visible on Roster tab) -->
        <select id="filter-attendance" onchange="applyFilters()" class="text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition shadow-2xs">
          <option value="All">All Statuses</option>
          <option value="Present">Present</option>
          <option value="Late">Late</option>
          <option value="Half Day">Half Day</option>
          <option value="Absent">Absent</option>
          <option value="On Leave">On Leave</option>
          <option value="Unmarked">Unmarked</option>
        </select>
      </div>

      <div class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
        <span id="active-filter-indicator" class="hidden text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
          Filter Active
        </span>
      </div>
    </div>
  </div>

  <!-- ================= TAB 1: DAILY ATTENDANCE ROSTER ================= -->
  <div id="section-roster" class="space-y-3">
    <!-- Section Title & Roster Counter -->
    <div class="flex items-center justify-between px-1">
      <div class="flex items-center gap-2">
        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Daily Attendance</h3>
        <span id="roster-badge-count" class="text-xs font-black px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700">0 staff</span>
      </div>
      <span class="text-xs text-slate-400 hidden sm:inline">Tap [P], [L], [H], [A], or [V] to immediately record attendance</span>
    </div>

    <!-- Responsive Mobile Cards View (Visible on Small Screens) -->
    <div id="roster-cards-container" class="md:hidden space-y-3">
      <!-- Injected via JS -->
    </div>

    <!-- High-Density Table Container (Desktop & Tablet) -->
    <div class="hidden md:block apple-card overflow-hidden shadow-xs bg-white border border-slate-200">
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 uppercase font-black tracking-wider text-[11px] border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Staff Member</th>
              <th class="py-3.5 px-4">Department & Role</th>
              <th class="py-3.5 px-4">Shift</th>
              <th class="py-3.5 px-4 text-center">In / Out Time</th>
              <th class="py-3.5 px-4 text-center">Hours</th>
              <th class="py-3.5 px-4 text-center">Mark Attendance</th>
              <th class="py-3.5 px-4">Notes / Remarks</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="roster-table-body" class="divide-y divide-slate-100 font-medium">
            <tr>
              <td colspan="8" class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-500 mb-2"></i>
                <p>Loading attendance roster...</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ================= TAB 2: STAFF DIRECTORY ================= -->
  <div id="section-directory" class="hidden space-y-4">
    <div class="flex items-center justify-between px-1">
      <div>
        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base flex items-center gap-2">
          <span>Hospital Staff Registry</span>
          <span id="directory-badge-count" class="text-xs font-black px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">0 Total</span>
        </h3>
      </div>

      <!-- Layout Switcher (Grid vs Table) -->
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold shrink-0">
        <button onclick="setDirectoryLayout('grid')" id="btn-dir-grid" class="px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-blue-600 transition flex items-center gap-1.5" title="Employee ID Cards View">
          <i class="fa-solid fa-id-badge text-xs"></i> <span class="hidden sm:inline text-xs">I-Cards</span>
        </button>
        <button onclick="setDirectoryLayout('table')" id="btn-dir-table" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5" title="Dense Table View">
          <i class="fa-solid fa-list text-xs"></i> <span class="hidden sm:inline text-xs">Table</span>
        </button>
      </div>
    </div>

    <!-- Grid Container (Employee ID Cards) -->
    <div id="directory-grid-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-5 sm:gap-6">
      <!-- Injected via JS -->
    </div>

    <!-- Dense Table Container -->
    <div id="directory-table-view" class="hidden apple-card overflow-hidden shadow-xs bg-white border border-slate-200">
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 uppercase font-black tracking-wider text-[11px] border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Staff Member</th>
              <th class="py-3.5 px-4">Role & Dept</th>
              <th class="py-3.5 px-4">Shift</th>
              <th class="py-3.5 px-4">Contact</th>
              <th class="py-3.5 px-4">User Portal Login</th>
              <th class="py-3.5 px-4">Salary</th>
              <th class="py-3.5 px-4">Joining Date</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="directory-table-body" class="divide-y divide-slate-100 font-medium">
            <!-- Injected via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ================= TAB 3: MONTHLY ATTENDANCE MATRIX SHEET ================= -->
  <div id="section-monthly" class="hidden apple-card overflow-hidden shadow-xs bg-white border border-slate-200">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/70">
      <div>
        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base flex items-center gap-2">
          <span>Monthly Attendance Matrix</span>
          <span id="monthly-sheet-badge" class="text-xs font-black px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Month</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">Hospital-wide day-by-day attendance heatmap with working hour aggregates.</p>
      </div>

      <div class="flex items-center gap-2.5">
        <button onclick="exportMonthlyCSV()" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold px-3 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-2xs">
          <i class="fa-solid fa-file-excel text-emerald-600"></i>
          <span>Download Month Sheet</span>
        </button>
      </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="overflow-x-auto custom-scrollbar max-h-[70vh]">
      <table class="w-full text-left text-xs border-collapse">
        <thead id="monthly-table-header" class="bg-slate-100 text-slate-700 font-black text-[11px] sticky top-0 z-20 shadow-xs border-b border-slate-200">
          <!-- Injected via JS -->
        </thead>
        <tbody id="monthly-table-body" class="divide-y divide-slate-100">
          <tr>
            <td colspan="36" class="text-center py-12 text-slate-400">
              <i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-500 mb-2"></i>
              <p>Loading monthly attendance sheet...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ================= MODAL: ADD / EDIT STAFF MEMBER ================= -->
<div id="modal-staff-form" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-3xl w-full max-h-[92vh] flex flex-col p-0 relative my-auto bg-white shadow-2xl border border-slate-200 rounded-2xl overflow-hidden">
    
    <!-- Modal Header -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
      <div class="flex items-center gap-3">
        <div id="staff-modal-icon-bg" class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-blue-500/20 shrink-0">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
          <h3 id="staff-modal-title" class="text-base sm:text-lg font-black text-slate-900 leading-tight">Add New Staff Member</h3>
          <p class="text-xs text-slate-500 font-medium">Register employee profile and portal access.</p>
        </div>
      </div>
      <button onclick="closeStaffModal()" type="button" class="text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Modal Form Body -->
    <form id="staff-form" onsubmit="handleStaffSubmit(event)" class="flex flex-col flex-1 overflow-hidden">
      <input type="hidden" id="staff-id" value="0">

      <div class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1">
        
        <!-- Section 1: Personal Information -->
        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/70">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-user text-blue-500"></i> Personal Details
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
              <input type="text" id="staff-first-name" required placeholder="e.g. Ananya" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
              <input type="text" id="staff-last-name" required placeholder="e.g. Sharma" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Staff ID Code</label>
              <input type="text" id="staff-code" placeholder="Auto (e.g. STF-101)" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
              <select id="staff-gender" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
                <option value="Female">Female</option>
                <option value="Male">Male</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
              <input type="date" id="staff-dob" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Blood Group</label>
              <select id="staff-blood-group" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section 2: Hospital Employment -->
        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/70">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-hospital-user text-blue-500"></i> Role & Department Assignment
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Role / Job Title *</label>
              <input type="text" id="staff-role" required list="staff-roles-list" placeholder="e.g. Senior Nurse" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
              <datalist id="staff-roles-list">
                <option value="Senior Staff Nurse"></option>
                <option value="Ward Nurse"></option>
                <option value="Head Pharmacist"></option>
                <option value="Assistant Pharmacist"></option>
                <option value="Chief Medical Lab Technician"></option>
                <option value="Lab Technician"></option>
                <option value="Front Desk Officer / Receptionist"></option>
                <option value="Radiology Technician"></option>
                <option value="Housekeeping Supervisor"></option>
                <option value="Chief Security Officer"></option>
                <option value="Resident Medical Officer (RMO)"></option>
                <option value="Billing & TPA Executive"></option>
                <option value="Ward Boy / Attendant"></option>
              </datalist>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Department *</label>
              <select id="staff-department" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
                <option value="Nursing">Nursing</option>
                <option value="Pharmacy">Pharmacy</option>
                <option value="Laboratory">Laboratory</option>
                <option value="Reception">Reception</option>
                <option value="Radiology">Radiology</option>
                <option value="Administration">Administration</option>
                <option value="Emergency">Emergency</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Security">Security</option>
                <option value="General Medicine">General Medicine</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Shift *</label>
              <select id="staff-shift" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
                <option value="Morning (08:00 - 16:00)">Morning (08:00 - 16:00)</option>
                <option value="Evening (14:00 - 22:00)">Evening (14:00 - 22:00)</option>
                <option value="Night (22:00 - 08:00)">Night (22:00 - 08:00)</option>
                <option value="General (09:00 - 18:00)">General (09:00 - 18:00)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Joining Date</label>
              <input type="date" id="staff-joining-date" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Salary (₹)</label>
              <input type="number" id="staff-salary" step="500" placeholder="e.g. 45000" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Employment Status</label>
              <select id="staff-status" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>

            <div class="sm:col-span-3">
              <label class="block text-xs font-bold text-slate-700 mb-1">Educational Qualifications / Certifications</label>
              <input type="text" id="staff-qualification" placeholder="e.g. B.Sc Nursing, Critical Care Certification" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
          </div>
        </div>

        <!-- Section 3: Contact & Address -->
        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/70">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-address-book text-blue-500"></i> Contact & Address
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
              <input type="tel" id="staff-phone" placeholder="e.g. +91 98201 12345" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
              <input type="email" id="staff-email" placeholder="e.g. mary.joseph@carepulse.org" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact</label>
              <input type="text" id="staff-emergency" placeholder="e.g. +91 98201 54321 (Spouse)" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
            <div class="sm:col-span-3">
              <label class="block text-xs font-bold text-slate-700 mb-1">Residential Address</label>
              <input type="text" id="staff-address" placeholder="e.g. Flat 402, Palm Heights, Medical Enclave" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
            </div>
          </div>
        </div>

        <!-- Section 4: System User Account (Portal Login) -->
        <div class="bg-gradient-to-br from-indigo-50/60 to-blue-50/50 p-4 sm:p-5 rounded-2xl border border-indigo-200/80">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                <i class="fa-solid fa-key"></i>
              </div>
              <div>
                <h4 class="font-extrabold text-slate-900 text-sm">Portal User Login Access</h4>
                <p class="text-[11px] text-slate-500">Allow this staff member to sign in to the hospital system.</p>
              </div>
            </div>
            <!-- Toggle Checkbox -->
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="staff-is-user" onchange="toggleUserAccountFields(this.checked)" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <!-- Conditional User Account Credentials Fields -->
          <div id="user-account-fields" class="hidden mt-4 pt-4 border-t border-indigo-200/70 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Username *</label>
              <div class="relative">
                <i class="fa-solid fa-at absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="staff-username" placeholder="e.g. nurse.mary" class="w-full pl-8 pr-3 py-2.5 text-xs font-mono font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
              </div>
              <p class="text-[10px] text-slate-500 mt-1">Unique login username</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span id="staff-pwd-label">Password *</span>
                <span id="staff-pwd-hint" class="text-[10px] text-slate-400 font-normal hidden">(leave blank to keep current)</span>
              </label>
              <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="password" id="staff-password" placeholder="Enter secure password" class="w-full pl-8 pr-9 py-2.5 text-xs font-medium rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
                <button type="button" onclick="togglePasswordVisibility('staff-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <p class="text-[10px] text-slate-500 mt-1">Minimum 6 characters recommended</p>
            </div>
          </div>
        </div>

        <!-- Section 5: Smart AI Face Biometrics Profile -->
        <div class="bg-gradient-to-br from-purple-50/70 to-indigo-50/50 p-4 sm:p-5 rounded-2xl border border-purple-200/80">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3.5">
              <div id="form-face-avatar-wrap" class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white shadow-xs bg-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                <img id="form-face-avatar-img" src="" class="w-full h-full object-cover hidden" alt="Staff Face Photo">
                <i id="form-face-avatar-icon" class="fa-solid fa-face-viewfinder text-xl"></i>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="font-extrabold text-slate-900 text-sm">Smart AI Face Biometrics</h4>
                  <span id="form-face-status-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">Pending</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5" id="form-face-hint">Capture and store staff face photo for instant camera attendance.</p>
              </div>
            </div>
            <button type="button" onclick="triggerFaceEnrollFromForm()" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/20 transition flex items-center justify-center gap-1.5 shrink-0">
              <i class="fa-solid fa-camera"></i>
              <span id="form-face-btn-text">📸 Enroll / Update Face</span>
            </button>
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="p-4 sm:p-5 border-t border-slate-100 flex items-center justify-end gap-2 bg-slate-50/80 shrink-0">
        <button type="button" onclick="closeStaffModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
          Cancel
        </button>
        <button type="submit" id="btn-save-staff" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-md shadow-blue-500/30 transition flex items-center gap-1.5">
          <i class="fa-solid fa-floppy-disk"></i>
          <span>Save Staff Member</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: STAFF PROFILE DOSSIER & HISTORY ================= -->
<div id="modal-staff-dossier" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-4xl w-full p-5 sm:p-7 relative my-6 max-h-[94vh] overflow-y-auto flex flex-col justify-between shadow-2xl border border-slate-200 bg-white rounded-2xl">
    <button onclick="closeDossierModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <div class="space-y-6">
      <!-- Profile Header Banner -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
        <div class="flex items-center gap-4">
          <div id="dossier-avatar-wrap" class="w-16 h-16 rounded-2xl overflow-hidden relative border-2 border-white shadow-lg shadow-blue-500/15 shrink-0 bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center text-2xl font-black">
            <img id="dossier-avatar-img" src="" class="w-full h-full object-cover hidden" alt="Staff Profile Image">
            <span id="dossier-avatar">ST</span>
          </div>
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h3 id="dossier-name" class="text-xl sm:text-2xl font-black text-slate-900">Staff Full Name</h3>
              <span id="dossier-code" class="text-xs font-mono font-bold bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-lg border border-slate-200">STF-000</span>
              <span id="dossier-status-pill" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Active</span>
              <span id="dossier-face-badge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1 shadow-2xs"><i class="fa-solid fa-face-viewfinder text-purple-600"></i> AI Face Enrolled</span>
            </div>
            <p id="dossier-subheading" class="text-xs text-slate-500 mt-0.5">Role &bull; Department</p>
            <div id="dossier-user-tag" class="mt-1">
              <!-- User login badge if enabled -->
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <button id="btn-dossier-face" onclick="openFaceEnrollModal(activeDossierStaffId)" class="px-3.5 py-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
            <i class="fa-solid fa-face-viewfinder"></i> <span id="dossier-face-btn-text">AI Face Biometrics</span>
          </button>
          <button id="btn-dossier-edit" onclick="editStaffFromDossier()" class="px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
            <i class="fa-solid fa-user-pen"></i> Edit Profile
          </button>
        </div>
      </div>

      <!-- Quick Metrics Strip -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
          <span class="text-[10px] font-bold uppercase text-slate-400">Monthly Attendance</span>
          <div class="mt-1 flex items-baseline gap-1">
            <span id="dossier-stat-rate" class="text-xl font-black text-blue-700">0%</span>
            <span class="text-[10px] text-slate-400">rate</span>
          </div>
        </div>
        <div class="bg-emerald-50/50 rounded-xl p-3 border border-emerald-100">
          <span class="text-[10px] font-bold uppercase text-emerald-700">Days Present</span>
          <div class="mt-1 flex items-baseline gap-1">
            <span id="dossier-stat-present" class="text-xl font-black text-emerald-800">0</span>
            <span id="dossier-stat-late" class="text-[10px] text-emerald-600 font-medium">+0 late</span>
          </div>
        </div>
        <div class="bg-rose-50/50 rounded-xl p-3 border border-rose-100">
          <span class="text-[10px] font-bold uppercase text-rose-700">Days Absent</span>
          <div class="mt-1 flex items-baseline gap-1">
            <span id="dossier-stat-absent" class="text-xl font-black text-rose-800">0</span>
            <span id="dossier-stat-leave" class="text-[10px] text-rose-600 font-medium">+0 leave</span>
          </div>
        </div>
        <div class="bg-indigo-50/50 rounded-xl p-3 border border-indigo-100">
          <span class="text-[10px] font-bold uppercase text-indigo-700">Total Hours</span>
          <div class="mt-1 flex items-baseline gap-1">
            <span id="dossier-stat-hours" class="text-xl font-black text-indigo-800">0</span>
            <span class="text-[10px] text-indigo-600 font-medium">hrs this month</span>
          </div>
        </div>
      </div>

      <!-- Smart AI Face Attendance Biometrics Card -->
      <div class="bg-gradient-to-br from-purple-50/70 via-indigo-50/50 to-white rounded-2xl border border-purple-200/80 p-4 sm:p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div id="dossier-face-preview-wrap" class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-white shadow-md bg-purple-100 flex items-center justify-center text-purple-600 shrink-0">
              <img id="dossier-face-photo" src="" class="w-full h-full object-cover hidden" alt="Saved Face Photo">
              <i id="dossier-face-placeholder-icon" class="fa-solid fa-face-viewfinder text-2xl"></i>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h4 class="text-sm font-extrabold text-slate-900">Smart AI Face Attendance Biometrics</h4>
                <span id="dossier-face-status-pill" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">AI Face Active</span>
              </div>
              <p id="dossier-face-desc" class="text-xs text-slate-500 mt-0.5">Face biometric profile registered &amp; ready for high-speed AI camera attendance.</p>
              <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-600 mt-1.5 flex-wrap">
                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-shield-check text-purple-600"></i> 128-D Vector</span>
                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-bolt text-amber-500"></i> Instant 0.3s Scan</span>
                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-circle-check text-emerald-500"></i> Auto Punch Enabled</span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <button onclick="openFaceEnrollModal(activeDossierStaffId)" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/20 transition flex items-center gap-1.5">
              <i class="fa-solid fa-camera"></i>
              <span id="dossier-face-action-text">Re-Capture Face</span>
            </button>
            <a href="face_attendance.php" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-purple-200 text-purple-700 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs" title="Open Face Kiosk to Test Punch">
              <i class="fa-solid fa-play"></i>
              <span>Test Punch</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Employee Details Grid -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
          <i class="fa-solid fa-id-card text-blue-500"></i> Full Employee Bio
        </h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-y-3 gap-x-4 text-xs">
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Phone:</span>
            <span id="dossier-phone" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Email:</span>
            <span id="dossier-email" class="font-bold text-slate-800 truncate block">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Assigned Shift:</span>
            <span id="dossier-shift" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Joining Date:</span>
            <span id="dossier-joining" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Blood Group:</span>
            <span id="dossier-blood" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Salary:</span>
            <span id="dossier-salary" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Emergency Contact:</span>
            <span id="dossier-emergency" class="font-bold text-slate-800">-</span>
          </div>
          <div>
            <span class="text-slate-400 font-semibold text-[11px] block">Portal User Login:</span>
            <span id="dossier-user-login" class="font-bold text-slate-800">-</span>
          </div>
          <div class="col-span-2 sm:col-span-4">
            <span class="text-slate-400 font-semibold text-[11px] block">Qualifications:</span>
            <span id="dossier-qualification" class="font-medium text-slate-700">-</span>
          </div>
          <div class="col-span-2 sm:col-span-4">
            <span class="text-slate-400 font-semibold text-[11px] block">Residential Address:</span>
            <span id="dossier-address" class="font-medium text-slate-700">-</span>
          </div>
        </div>
      </div>

      <!-- 30-Day Attendance History -->
      <div>
        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
          <span class="flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left text-blue-500"></i> Recent 30-Day Attendance Log</span>
          <span class="text-[11px] font-normal text-slate-500">Chronological history</span>
        </h4>
        <div class="max-h-60 overflow-y-auto custom-scrollbar border border-slate-200 rounded-xl">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 text-slate-600 font-bold sticky top-0 text-[11px]">
              <tr>
                <th class="py-2.5 px-3">Date</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 px-3 text-center">In / Out</th>
                <th class="py-2.5 px-3 text-center">Hours</th>
                <th class="py-2.5 px-3">Remarks</th>
                <th class="py-2.5 px-3 text-right">Marked By</th>
              </tr>
            </thead>
            <tbody id="dossier-history-body" class="divide-y divide-slate-100 font-medium">
              <!-- Injected via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL: EDIT ATTENDANCE RECORD ================= -->
<div id="modal-attendance-edit" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-md w-full p-5 sm:p-6 relative my-6 shadow-2xl border border-slate-200 bg-white rounded-2xl">
    <button onclick="closeAttendanceEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <form id="attendance-edit-form" onsubmit="handleAttendanceEditSubmit(event)" class="space-y-4">
      <input type="hidden" id="edit-att-staff-id" value="0">
      <input type="hidden" id="edit-att-date" value="">

      <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
          <i class="fa-regular fa-clock"></i>
        </div>
        <div>
          <h3 class="text-base font-extrabold text-slate-900">Attendance Details</h3>
          <p id="edit-att-subtitle" class="text-xs text-slate-500">Staff Member &bull; Date</p>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Attendance Status *</label>
        <div class="grid grid-cols-3 gap-2">
          <label class="cursor-pointer">
            <input type="radio" name="att_status_radio" value="Present" class="sr-only peer">
            <div class="py-2 px-2 text-center rounded-xl border border-slate-200 text-xs font-bold text-slate-700 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition">
              Present
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="att_status_radio" value="Late" class="sr-only peer">
            <div class="py-2 px-2 text-center rounded-xl border border-slate-200 text-xs font-bold text-slate-700 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 transition">
              Late
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="att_status_radio" value="Half Day" class="sr-only peer">
            <div class="py-2 px-2 text-center rounded-xl border border-slate-200 text-xs font-bold text-slate-700 peer-checked:bg-sky-500 peer-checked:text-white peer-checked:border-sky-500 transition">
              Half Day
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="att_status_radio" value="Absent" class="sr-only peer">
            <div class="py-2 px-2 text-center rounded-xl border border-slate-200 text-xs font-bold text-slate-700 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition">
              Absent
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="att_status_radio" value="On Leave" class="sr-only peer">
            <div class="py-2 px-2 text-center rounded-xl border border-slate-200 text-xs font-bold text-slate-700 peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 transition">
              On Leave
            </div>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3" id="att-times-container">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Check-In Time</label>
          <input type="time" id="edit-att-check-in" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Check-Out Time</label>
          <input type="time" id="edit-att-check-out" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white outline-none">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Remarks / Reason</label>
        <textarea id="edit-att-notes" rows="2" placeholder="e.g. On-time duty, approved sick leave, delayed..." class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white outline-none resize-none"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeAttendanceEditModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
          Cancel
        </button>
        <button type="submit" id="btn-save-attendance-record" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/30 transition flex items-center gap-1.5">
          <i class="fa-solid fa-check"></i>
          <span>Save Attendance</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: SMART AI FACE ENROLLMENT ================= -->
<div id="modal-face-enroll" class="hidden fixed inset-0 z-[140] flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-xl w-full p-5 sm:p-6 relative my-6 shadow-2xl border border-slate-200 bg-white rounded-3xl overflow-hidden flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-purple-500/20 shrink-0">
          <i class="fa-solid fa-face-viewfinder"></i>
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">Smart AI Face Enrollment</h3>
          <p class="text-[11px] sm:text-xs text-slate-500">Capture &amp; save employee face biometric profile for smart camera attendance.</p>
        </div>
      </div>
      <button onclick="closeFaceEnrollModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Staff Selector / Target Staff Card -->
    <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
      <div class="flex items-center gap-3">
        <div id="enroll-staff-avatar-wrap" class="w-12 h-12 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-sm shrink-0 border border-slate-200">
          <img id="enroll-staff-avatar-img" src="" class="w-full h-full object-cover hidden" alt="Staff Profile">
          <span id="enroll-staff-avatar-initials">ST</span>
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center justify-between gap-2">
            <h4 id="enroll-staff-name" class="font-extrabold text-slate-900 text-sm truncate">Select Staff Member</h4>
            <span id="enroll-staff-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Pending</span>
          </div>
          <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 flex-wrap">
            <span id="enroll-staff-code" class="font-mono font-bold text-slate-600">STF-000</span>
            <span>&bull;</span>
            <span id="enroll-staff-dept" class="truncate">Department</span>
            <span>&bull;</span>
            <span id="enroll-staff-role" class="truncate font-medium text-slate-700">Role</span>
          </div>
        </div>
      </div>
      <!-- If opened generically (staffId == 0), provide a dropdown to pick staff -->
      <div id="enroll-staff-select-container" class="hidden mt-3 pt-3 border-t border-slate-200">
        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Select Employee to Enroll</label>
        <select id="enroll-staff-select" onchange="onEnrollStaffSelected(this.value)" class="w-full p-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-purple-500/30">
          <option value="">-- Choose Staff Member --</option>
        </select>
      </div>
    </div>

    <!-- Camera Viewport & Live Face Box -->
    <div class="mt-4 relative bg-slate-950 rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[16/11] border border-slate-800 flex items-center justify-center shadow-inner">
      <video id="enroll-video" autoplay playsinline muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
      <canvas id="enroll-canvas" class="absolute inset-0 w-full h-full pointer-events-none transform scale-x-[-1]"></canvas>

      <!-- Corner HUD Target Reticle -->
      <div class="absolute inset-0 pointer-events-none p-4 sm:p-6 flex items-center justify-center">
        <div id="enroll-scan-box" class="w-48 h-56 sm:w-56 sm:h-64 border-2 border-dashed border-white/40 rounded-3xl transition-colors duration-200 flex items-center justify-center relative">
          <!-- Top Left Corner -->
          <div class="absolute -top-1 -left-1 w-5 h-5 border-t-4 border-l-4 border-purple-500 rounded-tl-xl"></div>
          <!-- Top Right Corner -->
          <div class="absolute -top-1 -right-1 w-5 h-5 border-t-4 border-r-4 border-purple-500 rounded-tr-xl"></div>
          <!-- Bottom Left Corner -->
          <div class="absolute -bottom-1 -left-1 w-5 h-5 border-b-4 border-l-4 border-purple-500 rounded-bl-xl"></div>
          <!-- Bottom Right Corner -->
          <div class="absolute -bottom-1 -right-1 w-5 h-5 border-b-4 border-r-4 border-purple-500 rounded-br-xl"></div>
        </div>
      </div>

      <!-- Live Floating Status Badge -->
      <div class="absolute top-3 inset-x-3 flex items-center justify-between pointer-events-none">
        <div id="enroll-camera-badge" class="px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-white text-[11px] font-bold flex items-center gap-1.5 border border-white/10">
          <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <span id="enroll-camera-badge-text">Starting Camera...</span>
        </div>
        <button type="button" onclick="switchEnrollCamera()" class="pointer-events-auto px-2.5 py-1 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-md text-white text-[11px] font-bold flex items-center gap-1.5 border border-white/10 transition">
          <i class="fa-solid fa-camera-rotate"></i>
          <span>Switch</span>
        </button>
      </div>

      <!-- Bottom Hint Overlay -->
      <div class="absolute bottom-3 inset-x-4 text-center pointer-events-none">
        <div id="enroll-hint-badge" class="inline-block px-3.5 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-xs font-semibold border border-white/10 shadow-lg">
          Please look straight at the camera with clear lighting
        </div>
      </div>

      <!-- Snapshot Preview Overlay (shown after capture) -->
      <div id="enroll-preview-overlay" class="hidden absolute inset-0 bg-slate-950/92 backdrop-blur-sm flex flex-col items-center justify-center p-4 z-20">
        <img id="enroll-preview-img" src="" class="w-36 h-36 sm:w-44 sm:h-44 rounded-2xl object-cover border-4 border-emerald-500 shadow-xl mb-2 bg-slate-800">
        <div class="text-white text-sm font-bold flex items-center gap-2">
          <i class="fa-solid fa-circle-check text-emerald-400"></i> Face Snapshot Captured
        </div>
        <p class="text-slate-300 text-xs mt-1 text-center max-w-xs">128-D Biometric vector successfully computed from camera frame.</p>
        <div class="flex items-center gap-2 mt-4">
          <button type="button" onclick="retakeEnrollSnapshot()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-rotate-left"></i> Retake
          </button>
          <button type="button" id="btn-save-enroll-captured" onclick="saveCapturedFace()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg shadow-emerald-500/30 transition flex items-center gap-1.5">
            <i class="fa-solid fa-floppy-disk"></i> Save to Profile
          </button>
        </div>
      </div>
    </div>

    <!-- Action Buttons Footer -->
    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2.5">
      <div class="flex items-center gap-2">
        <button type="button" id="btn-delete-face" onclick="deleteCurrentStaffFace()" class="hidden px-3 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition flex items-center gap-1.5">
          <i class="fa-regular fa-trash-can"></i>
          <span>Delete Biometrics</span>
        </button>
      </div>

      <div class="flex items-center gap-2 ml-auto">
        <button type="button" onclick="closeFaceEnrollModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
          Close
        </button>
        <button type="button" id="btn-capture-face" onclick="captureAndProcessFace()" disabled class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 cursor-not-allowed font-black text-xs transition flex items-center gap-2 shadow-xs">
          <i class="fa-solid fa-camera"></i>
          <span id="btn-capture-face-text">Align Face to Capture</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script src="js/face-api.min.js"></script>
<script>
  // App State
  let currentView = 'roster'; // 'roster', 'directory', 'monthly'
  let directoryLayout = 'grid'; // 'grid', 'table'
  let selectedDate = new Date().toISOString().split('T')[0];
  let selectedMonth = selectedDate.substring(0, 7);
  let staffList = [];
  let filterTimer = null;
  let activeDossierStaffId = 0;
  let monthlyData = null;

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', () => {
    // Set date input to today
    document.getElementById('selected-date-input').value = selectedDate;
    document.getElementById('selected-month-input').value = selectedMonth;
    updateDateDisplay();

    // Initial Load
    fetchDailySummary();
    fetchStaffList();

    // Close tools menu on outside click
    document.addEventListener('click', (e) => {
      const container = document.getElementById('tools-dropdown-container');
      if (container && !container.contains(e.target)) {
        document.getElementById('tools-menu').classList.add('hidden');
      }
    });

    // React to radio changes in edit attendance modal
    const radios = document.querySelectorAll('input[name="att_status_radio"]');
    radios.forEach(r => {
      r.addEventListener('change', () => {
        const val = r.value;
        const timeBox = document.getElementById('att-times-container');
        if (val === 'Absent' || val === 'On Leave') {
          timeBox.classList.add('opacity-40', 'pointer-events-none');
        } else {
          timeBox.classList.remove('opacity-40', 'pointer-events-none');
        }
      });
    });
  });

  function toggleToolsMenu() {
    const menu = document.getElementById('tools-menu');
    menu.classList.toggle('hidden');
  }

  // Switch Active View
  function switchView(view) {
    currentView = view;

    // View button classes
    const btnRoster = document.getElementById('btn-view-roster');
    const btnDir = document.getElementById('btn-view-directory');
    const btnMonth = document.getElementById('btn-view-monthly');

    const secRoster = document.getElementById('section-roster');
    const secDir = document.getElementById('section-directory');
    const secMonth = document.getElementById('section-monthly');

    const dateGroup = document.getElementById('date-controls-group');
    const monthGroup = document.getElementById('month-controls-group');
    const attFilter = document.getElementById('filter-attendance');

    const activeClass = 'px-3.5 sm:px-4 py-2 rounded-xl bg-white shadow-xs text-slate-900 font-extrabold transition flex items-center gap-2';
    const inactiveClass = 'px-3.5 sm:px-4 py-2 rounded-xl text-slate-600 hover:text-slate-900 font-bold transition flex items-center gap-2';

    btnRoster.className = view === 'roster' ? activeClass : inactiveClass;
    btnDir.className = view === 'directory' ? activeClass : inactiveClass;
    btnMonth.className = view === 'monthly' ? activeClass : inactiveClass;

    secRoster.classList.toggle('hidden', view !== 'roster');
    secDir.classList.toggle('hidden', view !== 'directory');
    secMonth.classList.toggle('hidden', view !== 'monthly');

    // Show/hide date vs month controls
    if (view === 'monthly') {
      dateGroup.classList.add('hidden');
      monthGroup.classList.remove('hidden');
      attFilter.classList.add('hidden');
      fetchMonthlySheet();
    } else {
      dateGroup.classList.remove('hidden');
      monthGroup.classList.add('hidden');
      attFilter.classList.toggle('hidden', view === 'directory');
      fetchStaffList();
    }
  }

  // Toggle Directory Layout (Grid vs Table)
  function setDirectoryLayout(layout) {
    directoryLayout = layout;
    const btnGrid = document.getElementById('btn-dir-grid');
    const btnTable = document.getElementById('btn-dir-table');
    const gridView = document.getElementById('directory-grid-view');
    const tableView = document.getElementById('directory-table-view');

    if (layout === 'grid') {
      btnGrid.className = 'px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-blue-600 transition flex items-center gap-1.5';
      btnTable.className = 'px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5';
      gridView.classList.remove('hidden');
      tableView.classList.add('hidden');
    } else {
      btnTable.className = 'px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-blue-600 transition flex items-center gap-1.5';
      btnGrid.className = 'px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-900 transition flex items-center gap-1.5';
      gridView.classList.add('hidden');
      tableView.classList.remove('hidden');
    }
  }

  // Date Navigation Helpers
  function onDateChanged(val) {
    selectedDate = val;
    updateDateDisplay();
    fetchDailySummary();
    fetchStaffList();
  }

  function navigateDate(days) {
    const d = new Date(selectedDate);
    d.setDate(d.getDate() + days);
    selectedDate = d.toISOString().split('T')[0];
    document.getElementById('selected-date-input').value = selectedDate;
    updateDateDisplay();
    fetchDailySummary();
    fetchStaffList();
  }

  function setTodayDate() {
    selectedDate = new Date().toISOString().split('T')[0];
    document.getElementById('selected-date-input').value = selectedDate;
    updateDateDisplay();
    fetchDailySummary();
    fetchStaffList();
  }

  function updateDateDisplay() {
    const d = new Date(selectedDate + 'T00:00:00');
    const today = new Date().toISOString().split('T')[0];
    const yest = new Date(Date.now() - 86400000).toISOString().split('T')[0];

    const todayBtn = document.getElementById('btn-today-pill');
    if (selectedDate === today) {
      todayBtn.className = 'text-xs font-bold px-3 py-2 rounded-xl bg-blue-600 text-white shadow-xs';
    } else {
      todayBtn.className = 'text-xs font-bold px-3 py-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs';
    }

    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    const formatted = d.toLocaleDateString('en-US', options);

    let prefix = '';
    if (selectedDate === today) prefix = '(Today) ';
    else if (selectedDate === yest) prefix = '(Yesterday) ';

    document.getElementById('selected-date-label').textContent = `${prefix}${formatted}`;
  }

  // Month Navigation Helpers
  function onMonthChanged(val) {
    selectedMonth = val;
    fetchMonthlySheet();
  }

  function navigateMonth(delta) {
    const [y, m] = selectedMonth.split('-').map(Number);
    const d = new Date(y, m - 1 + delta, 1);
    const ny = d.getFullYear();
    const nm = String(d.getMonth() + 1).padStart(2, '0');
    selectedMonth = `${ny}-${nm}`;
    document.getElementById('selected-month-input').value = selectedMonth;
    fetchMonthlySheet();
  }

  function setCurrentMonth() {
    selectedMonth = new Date().toISOString().split('T')[0].substring(0, 7);
    document.getElementById('selected-month-input').value = selectedMonth;
    fetchMonthlySheet();
  }

  // Search & Filter Debouncing
  function debounceFilter() {
    const val = document.getElementById('filter-search').value.trim();
    document.getElementById('btn-clear-search').classList.toggle('hidden', val === '');
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
      fetchStaffList();
    }, 300);
  }

  function clearSearch() {
    document.getElementById('filter-search').value = '';
    document.getElementById('btn-clear-search').classList.add('hidden');
    fetchStaffList();
  }

  function applyFilters() {
    const dept = document.getElementById('filter-department').value;
    const shift = document.getElementById('filter-shift').value;
    const att = document.getElementById('filter-attendance').value;
    const ind = document.getElementById('active-filter-indicator');
    
    if (dept !== 'All' || shift !== 'All' || att !== 'All') {
      ind.classList.remove('hidden');
    } else {
      ind.classList.add('hidden');
    }

    fetchStaffList();
  }

  // Fetch KPI Daily Summary
  async function fetchDailySummary() {
    try {
      const res = await fetch(`api/staff.php?action=get_daily_summary&date=${selectedDate}`);
      const data = await res.json();
      if (data.status === 'success' && data.summary) {
        const s = data.summary;
        document.getElementById('kpi-total-staff').textContent = s.total_active_staff;
        document.getElementById('kpi-present').textContent = s.present;
        document.getElementById('kpi-late').textContent = s.late;
        document.getElementById('kpi-halfday').textContent = s.half_day;
        document.getElementById('kpi-absent').textContent = s.absent;
        document.getElementById('kpi-leave-detail').textContent = `${s.on_leave} On Leave`;
        document.getElementById('kpi-rate').textContent = `${s.attendance_rate}% rate`;
      }
    } catch (err) {
      console.error('Error fetching summary:', err);
    }
  }

  // Fetch Staff List
  async function fetchStaffList() {
    const search = encodeURIComponent(document.getElementById('filter-search').value.trim());
    const dept = encodeURIComponent(document.getElementById('filter-department').value);
    const shift = encodeURIComponent(document.getElementById('filter-shift').value);
    const attStatus = encodeURIComponent(document.getElementById('filter-attendance').value);

    const url = `api/staff.php?action=get_staff&date=${selectedDate}&search=${search}&department=${dept}&shift=${shift}&att_status=${attStatus}`;

    try {
      const res = await fetch(url);
      const json = await res.json();
      if (json.status === 'success') {
        staffList = json.data || [];
        renderRosterViews(staffList);
        renderDirectoryViews(staffList);
      } else {
        showToast('Error', json.message || 'Failed to load staff list', 'error');
      }
    } catch (err) {
      console.error('Error loading staff:', err);
    }
  }

  // Helper: Render Staff Avatar with Image (if available), Initials fallback, and Smart AI Face Badge
  function renderStaffAvatarHtml(s, size = 'w-11 h-11', textClass = 'text-xs') {
    const initials = (((s.first_name ? s.first_name[0] : '') + (s.last_name ? s.last_name[0] : '')) || 'ST').toUpperCase();
    const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
    const avatarSrc = s.avatar ? escapeHtml(s.avatar) : '';
    
    let imgHtml = '';
    if (avatarSrc) {
      imgHtml = `<img src="${avatarSrc}" alt="${escapeHtml(s.first_name)}" class="w-full h-full object-cover rounded-xl" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">`;
    }
    
    return `
      <div class="${size} rounded-2xl overflow-hidden relative shadow-2xs shrink-0 border border-slate-200/80 bg-slate-100">
        ${imgHtml}
        <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-black flex items-center justify-center ${textClass} ${avatarSrc ? 'hidden' : ''}">
          ${initials}
        </div>
        ${hasFace ? `<span title="Smart AI Face Enrolled" class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-purple-600 text-white flex items-center justify-center text-[8px] ring-2 ring-white shadow-xs pointer-events-none"><i class="fa-solid fa-face-viewfinder"></i></span>` : ''}
      </div>
    `;
  }

  // Render Daily Attendance Roster (Dual View: Mobile Cards & Desktop Table)
  function renderRosterViews(list) {
    const tbody = document.getElementById('roster-table-body');
    const cardsContainer = document.getElementById('roster-cards-container');
    document.getElementById('roster-badge-count').textContent = `${list.length} staff`;

    if (!list.length) {
      const emptyHtml = `
        <div class="text-center py-12 apple-card p-6 bg-white border border-slate-200">
          <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300"></i>
          <p class="font-bold text-slate-700 text-sm">No staff members found matching criteria.</p>
          <p class="text-xs text-slate-400 mt-1">Adjust filters or click "+ Add Staff" to register a new employee.</p>
        </div>`;
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12 text-slate-400 font-bold">No staff records found.</td></tr>`;
      cardsContainer.innerHTML = emptyHtml;
      return;
    }

    let tableHtml = '';
    let cardsHtml = '';

    list.forEach(s => {
      const initials = (s.first_name[0] || '') + (s.last_name[0] || '');
      const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
      const st = s.att_status || '';
      const inTime = s.check_in_time ? s.check_in_time.substring(0, 5) : '--:--';
      const outTime = s.check_out_time ? s.check_out_time.substring(0, 5) : '--:--';
      const hrs = s.working_hours !== null && s.working_hours !== undefined ? `${parseFloat(s.working_hours).toFixed(1)}h` : '-';
      const notes = s.att_notes ? escapeHtml(s.att_notes) : '<span class="text-slate-300 italic">No remarks</span>';

      const pActive = st === 'Present';
      const lActive = st === 'Late';
      const hActive = st === 'Half Day';
      const aActive = st === 'Absent';
      const vActive = st === 'On Leave';

      // Status pill badge
      let statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Unmarked</span>';
      if (pActive) statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Present</span>';
      else if (lActive) statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Late</span>';
      else if (hActive) statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-800">Half Day</span>';
      else if (aActive) statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">Absent</span>';
      else if (vActive) statusBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">On Leave</span>';

      // 1. Mobile Touch-Friendly Card
      cardsHtml += `
        <div class="apple-card p-4 bg-white border border-slate-200/90 rounded-2xl shadow-2xs space-y-3">
          <!-- Top Row -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              ${renderStaffAvatarHtml(s, 'w-11 h-11', 'text-xs')}
              <div class="min-w-0">
                <button onclick="openStaffDossier(${s.id})" class="font-extrabold text-slate-900 text-sm truncate text-left block">
                  ${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}
                </button>
                <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                  <span class="font-mono text-[10px] font-bold bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded">${escapeHtml(s.staff_code)}</span>
                  <span class="text-[10px] font-bold text-slate-500">&bull; ${escapeHtml(s.role)}</span>
                </div>
              </div>
            </div>
            <div>${statusBadge}</div>
          </div>

          <!-- Middle Row: Times & Remarks -->
          <div class="flex items-center justify-between text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
            <div class="flex items-center gap-2">
              <span class="text-slate-400 font-semibold">Hours:</span>
              <span class="font-mono font-bold text-slate-800">${hrs}</span>
              <span class="text-slate-300">|</span>
              <span class="font-mono text-slate-600 text-[11px]">${inTime} - ${outTime}</span>
            </div>
            <button onclick="openAttendanceEditModal(${s.id}, '${selectedDate}', '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="text-blue-600 font-bold hover:underline text-[11px]" title="Edit Time & Remarks">
              <i class="fa-regular fa-clock mr-1"></i> Edit
            </button>
          </div>

          <!-- Attendance 1-Tap Marker Buttons Row -->
          <div class="pt-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1.5">Mark Status</span>
            <div class="grid grid-cols-5 gap-1.5">
              <button onclick="quickMarkStaff(${s.id}, 'Present')" class="py-2 rounded-xl text-xs font-black transition flex flex-col items-center justify-center ${pActive ? 'bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-600/30' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/60'}">
                <span>P</span>
                <span class="text-[9px] font-semibold opacity-80">Present</span>
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Late')" class="py-2 rounded-xl text-xs font-black transition flex flex-col items-center justify-center ${lActive ? 'bg-amber-500 text-white shadow-xs ring-2 ring-amber-500/30' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/60'}">
                <span>L</span>
                <span class="text-[9px] font-semibold opacity-80">Late</span>
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Half Day')" class="py-2 rounded-xl text-xs font-black transition flex flex-col items-center justify-center ${hActive ? 'bg-sky-500 text-white shadow-xs ring-2 ring-sky-500/30' : 'bg-sky-50 text-sky-800 hover:bg-sky-100 border border-sky-200/60'}">
                <span>H</span>
                <span class="text-[9px] font-semibold opacity-80">Half</span>
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Absent')" class="py-2 rounded-xl text-xs font-black transition flex flex-col items-center justify-center ${aActive ? 'bg-rose-600 text-white shadow-xs ring-2 ring-rose-600/30' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200/60'}">
                <span>A</span>
                <span class="text-[9px] font-semibold opacity-80">Absent</span>
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'On Leave')" class="py-2 rounded-xl text-xs font-black transition flex flex-col items-center justify-center ${vActive ? 'bg-purple-600 text-white shadow-xs ring-2 ring-purple-600/30' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200/60'}">
                <span>V</span>
                <span class="text-[9px] font-semibold opacity-80">Leave</span>
              </button>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-400 text-[11px] truncate max-w-[150px]">${notes}</span>
            <div class="flex items-center gap-1 shrink-0">
              <button onclick="openFaceEnrollModal(${s.id})" class="p-1.5 rounded-lg ${hasFace ? 'text-purple-600 bg-purple-50 hover:bg-purple-100' : 'text-slate-400 hover:text-purple-600'} transition" title="${hasFace ? 'Update AI Face Biometrics' : 'Enroll AI Face for Attendance'}">
                <i class="fa-solid fa-face-viewfinder text-xs"></i>
              </button>
              <button onclick="openStaffDossier(${s.id})" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px] border border-blue-200 transition">
                Dossier
              </button>
              <button onclick="openStaffModal(${s.id})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 transition" title="Edit Profile">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </button>
            </div>
          </div>
        </div>`;

      // 2. Desktop High-Density Table Row
      tableHtml += `
        <tr class="hover:bg-slate-50/80 transition-colors group">
          <!-- Staff Info -->
          <td class="py-3.5 px-4">
            <div class="flex items-center gap-3">
              ${renderStaffAvatarHtml(s, 'w-9 h-9', 'text-[11px]')}
              <div class="min-w-0">
                <button onclick="openStaffDossier(${s.id})" class="font-extrabold text-slate-900 hover:text-blue-600 text-xs truncate text-left transition flex items-center gap-1.5">
                  <span>${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}</span>
                  ${s.is_user ? '<i class="fa-solid fa-key text-[10px] text-emerald-500" title="System User Login Active (@' + escapeHtml(s.username || '') + ')"></i>' : ''}
                </button>
                <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                  <span class="font-mono font-bold text-slate-500">${escapeHtml(s.staff_code)}</span>
                  ${s.phone ? `<span>&bull;</span><span>${escapeHtml(s.phone)}</span>` : ''}
                </div>
              </div>
            </div>
          </td>

          <!-- Dept & Role -->
          <td class="py-3.5 px-4">
            <div>
              <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] mb-0.5">
                ${escapeHtml(s.department)}
              </span>
              <div class="text-xs text-slate-700 font-medium truncate max-w-[170px]">${escapeHtml(s.role)}</div>
            </div>
          </td>

          <!-- Shift -->
          <td class="py-3.5 px-4">
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600 bg-slate-50 border border-slate-200/80 px-2 py-1 rounded-lg">
              <i class="fa-regular fa-clock text-slate-400 text-[10px]"></i>
              ${escapeHtml(s.shift || 'General')}
            </span>
          </td>

          <!-- In / Out Time -->
          <td class="py-3.5 px-4 text-center">
            <button onclick="openAttendanceEditModal(${s.id}, '${selectedDate}', '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-[11px] font-mono text-slate-700 shadow-2xs transition">
              <span class="${s.check_in_time ? 'text-emerald-700 font-bold' : 'text-slate-400'}">${inTime}</span>
              <span class="text-slate-300">-</span>
              <span class="${s.check_out_time ? 'text-blue-700 font-bold' : 'text-slate-400'}">${outTime}</span>
            </button>
          </td>

          <!-- Hours -->
          <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
            ${hrs}
          </td>

          <!-- Quick Status Buttons -->
          <td class="py-3.5 px-4 text-center">
            <div class="inline-flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/70 shadow-2xs">
              <button onclick="quickMarkStaff(${s.id}, 'Present')" title="Mark Present (P)" class="w-7 h-7 rounded-lg text-xs font-black transition ${pActive ? 'bg-emerald-600 text-white shadow-xs scale-105' : 'text-emerald-700 hover:bg-emerald-50'}">
                P
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Late')" title="Mark Late (L)" class="w-7 h-7 rounded-lg text-xs font-black transition ${lActive ? 'bg-amber-500 text-white shadow-xs scale-105' : 'text-amber-700 hover:bg-amber-50'}">
                L
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Half Day')" title="Mark Half Day (H)" class="w-7 h-7 rounded-lg text-xs font-black transition ${hActive ? 'bg-sky-500 text-white shadow-xs scale-105' : 'text-sky-700 hover:bg-sky-50'}">
                H
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'Absent')" title="Mark Absent (A)" class="w-7 h-7 rounded-lg text-xs font-black transition ${aActive ? 'bg-rose-600 text-white shadow-xs scale-105' : 'text-rose-700 hover:bg-rose-50'}">
                A
              </button>
              <button onclick="quickMarkStaff(${s.id}, 'On Leave')" title="Mark On Leave (V)" class="w-7 h-7 rounded-lg text-xs font-black transition ${vActive ? 'bg-purple-600 text-white shadow-xs scale-105' : 'text-purple-700 hover:bg-purple-50'}">
                V
              </button>
            </div>
          </td>

          <!-- Notes -->
          <td class="py-3.5 px-4">
            <button onclick="openAttendanceEditModal(${s.id}, '${selectedDate}', '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="text-left text-xs truncate max-w-[180px] block hover:text-blue-600 transition" title="Click to edit notes">
              ${notes}
            </button>
          </td>

          <!-- Actions -->
          <td class="py-3.5 px-4 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <button onclick="openFaceEnrollModal(${s.id})" class="w-8 h-8 rounded-lg ${hasFace ? 'bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200' : 'bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-purple-600'} transition flex items-center justify-center text-xs shadow-2xs" title="${hasFace ? 'Update AI Face Biometrics' : 'Enroll AI Face for Attendance'}">
                <i class="fa-solid fa-face-viewfinder"></i>
              </button>
              <button onclick="openAttendanceEditModal(${s.id}, '${selectedDate}', '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-blue-600 transition flex items-center justify-center text-xs shadow-2xs" title="Adjust Time & Remarks">
                <i class="fa-regular fa-clock"></i>
              </button>
              <button onclick="openStaffDossier(${s.id})" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-blue-600 transition flex items-center justify-center text-xs shadow-2xs" title="View Dossier">
                <i class="fa-regular fa-id-card"></i>
              </button>
              <button onclick="openStaffModal(${s.id})" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-blue-600 transition flex items-center justify-center text-xs shadow-2xs" title="Edit Staff Profile">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });

    tbody.innerHTML = tableHtml;
    cardsContainer.innerHTML = cardsHtml;
  }

  // Render Directory Views (Cards + Dense Table)
  function renderDirectoryViews(list) {
    const gridContainer = document.getElementById('directory-grid-view');
    const tableBody = document.getElementById('directory-table-body');
    document.getElementById('directory-badge-count').textContent = `${list.length} Total`;

    if (!list.length) {
      gridContainer.innerHTML = `
        <div class="col-span-full text-center py-16 apple-card p-8 bg-white border border-slate-200">
          <i class="fa-regular fa-id-badge text-4xl text-slate-300 mb-3"></i>
          <h4 class="text-base font-bold text-slate-700">No staff found</h4>
          <p class="text-xs text-slate-400 mt-1">Try resetting the search keyword or department filters.</p>
        </div>`;
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-slate-400 font-bold">No staff records found.</td></tr>`;
      return;
    }

    let gridHtml = '';
    let tableHtml = '';

    list.forEach(s => {
      const initials = (((s.first_name ? s.first_name[0] : '') + (s.last_name ? s.last_name[0] : '')) || 'ST').toUpperCase();
      const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
      const userBadge = s.is_user ? `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/80 text-[11px] font-bold">
          <i class="fa-solid fa-key text-emerald-600"></i>
          <span>@${escapeHtml(s.username || '')}</span>
        </span>` : `
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[11px] font-medium">
          <i class="fa-solid fa-lock text-[10px]"></i> No Portal Account
        </span>`;

      // Status pill
      let statusClass = 'bg-emerald-100 text-emerald-800';
      if (s.status === 'On Leave') statusClass = 'bg-amber-100 text-amber-800';
      if (s.status === 'Inactive') statusClass = 'bg-slate-200 text-slate-700';

      let accountStatusBadge = '';
      if (s.account_status === 'Blocked') {
          accountStatusBadge = '<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 border border-rose-200">Blocked</span>';
      } else if (s.account_status === 'Suspended') {
          accountStatusBadge = '<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">Suspended</span>';
      }

      // Department badge color accent
      let deptTheme = 'from-slate-900 via-blue-950 to-indigo-950';
      if (s.department === 'Emergency' || s.department === 'ICU') deptTheme = 'from-slate-900 via-rose-950 to-red-950';
      else if (s.department === 'Pediatrics') deptTheme = 'from-slate-900 via-amber-950 to-orange-950';
      else if (s.department === 'Cardiology') deptTheme = 'from-slate-900 via-red-950 to-pink-950';
      else if (s.department === 'Neurology') deptTheme = 'from-slate-900 via-purple-950 to-indigo-950';
      else if (s.department === 'Radiology' || s.department === 'Laboratory') deptTheme = 'from-slate-900 via-teal-950 to-emerald-950';

      // Portrait Image vs Fallback Initials
      const avatarSrc = s.avatar ? escapeHtml(s.avatar) : '';
      let portraitHtml = '';
      if (avatarSrc) {
        portraitHtml = `
          <img src="${avatarSrc}" alt="${escapeHtml(s.first_name)}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
          <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-black flex items-center justify-center text-2xl hidden">
            ${initials}
          </div>`;
      } else {
        portraitHtml = `
          <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-black flex items-center justify-center text-2xl">
            ${initials}
          </div>`;
      }

      // Card Grid Item (Executive Hospital Employee ID Badge)
      gridHtml += `
        <div class="apple-card flex flex-col justify-between hover:border-blue-400 transition-all duration-300 group bg-white border border-slate-200/90 rounded-3xl shadow-xs hover:shadow-xl hover:-translate-y-1 relative overflow-hidden">
          
          <div>
            <!-- Lanyard Punch Slot Header -->
            <div class="pt-3 pb-1.5 flex justify-center items-center bg-slate-50/90 border-b border-slate-100/80">
              <div class="w-12 h-2 rounded-full bg-slate-200 border border-slate-300/80 shadow-inner flex items-center justify-center">
                <span class="w-6 h-0.5 bg-slate-300 rounded-full"></span>
              </div>
            </div>

            <!-- Hospital Brand Banner -->
            <div class="px-4 py-2.5 bg-gradient-to-r ${deptTheme} text-white flex items-center justify-between shadow-xs">
              <div class="flex items-center gap-2 min-w-0">
                <div class="w-6 h-6 rounded-lg bg-white/15 backdrop-blur-xs text-white border border-white/20 flex items-center justify-center text-xs shrink-0 shadow-2xs">
                  <i class="fa-solid fa-hospital"></i>
                </div>
                <div class="min-w-0">
                  <span class="text-[11px] font-black uppercase tracking-wider block truncate leading-tight">CAREPULSE HEALTH</span>
                  <span class="text-[9px] text-white/80 font-semibold tracking-tight block truncate">Hospital Staff Identity</span>
                </div>
              </div>
              <span class="text-[9px] font-mono font-extrabold tracking-widest text-white/95 uppercase bg-black/25 px-2 py-0.5 rounded-md border border-white/15 shrink-0">
                OFFICIAL ID
              </span>
            </div>

            <!-- ID Portrait & Bio Centerpiece -->
            <div class="pt-5 pb-3 px-4 flex flex-col items-center text-center">
              
              <!-- Portrait Frame -->
              <div class="relative group-hover:scale-105 transition-transform duration-300">
                <div class="w-22 h-22 sm:w-24 sm:h-24 rounded-2xl overflow-hidden ring-4 ring-slate-100 border-2 border-white shadow-md bg-slate-100 flex items-center justify-center shrink-0">
                  ${portraitHtml}
                </div>
                ${hasFace ? `
                  <span title="Smart AI Face Biometrics Active" class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px] ring-2 ring-white shadow-md">
                    <i class="fa-solid fa-face-viewfinder"></i>
                  </span>` : `
                  <button type="button" onclick="openFaceEnrollModal(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" title="Click to Enroll AI Face" class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full bg-slate-200 hover:bg-purple-600 text-slate-600 hover:text-white flex items-center justify-center text-[10px] ring-2 ring-white shadow-md transition">
                    <i class="fa-solid fa-camera"></i>
                  </button>`}
              </div>

              <!-- Full Name -->
              <h4 class="font-black text-slate-900 text-base sm:text-lg group-hover:text-blue-600 transition mt-3 tracking-tight leading-snug truncate max-w-full px-2">
                ${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}
              </h4>

              <!-- Role / Title Pill -->
              <div class="mt-1">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200/70 max-w-[240px] truncate shadow-2xs">
                  ${escapeHtml(s.role)}
                </span>
              </div>

              <!-- Staff ID & Status Badges -->
              <div class="flex items-center justify-center gap-1.5 mt-2 flex-wrap">
                <span class="font-mono text-[11px] font-extrabold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                  ID: ${escapeHtml(s.staff_code)}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${statusClass}">
                  ${escapeHtml(s.status)}
                </span>
                ${accountStatusBadge}
              </div>
            </div>

            <!-- Badge Key Metadata Grid -->
            <div class="mx-3.5 mb-2 bg-slate-50/80 rounded-2xl border border-slate-200/80 p-3 space-y-1.5 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                  <i class="fa-regular fa-building text-[10px] text-slate-400 w-3 text-center"></i> Dept:
                </span>
                <span class="font-bold text-slate-800 text-right truncate max-w-[170px]">${escapeHtml(s.department)}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                  <i class="fa-regular fa-clock text-[10px] text-slate-400 w-3 text-center"></i> Shift:
                </span>
                <span class="font-medium text-slate-700 text-right truncate max-w-[170px]">${escapeHtml(s.shift || 'General')}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                  <i class="fa-solid fa-phone text-[10px] text-slate-400 w-3 text-center"></i> Contact:
                </span>
                <a href="tel:${s.phone || ''}" class="font-semibold text-slate-700 hover:text-blue-600 transition truncate max-w-[170px]">${escapeHtml(s.phone || '-')}</a>
              </div>
              <div class="flex items-center justify-between pt-1.5 border-t border-slate-200/70">
                <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                  <i class="fa-solid fa-key text-[10px] text-slate-400 w-3 text-center"></i> Portal:
                </span>
                <div>${userBadge}</div>
              </div>
            </div>

            <!-- Authentic Barcode & Security Strip -->
            <div class="mx-4 my-2.5 pt-2 border-t border-dashed border-slate-200 flex items-center justify-between">
              <div class="flex items-center gap-[2px] h-5 opacity-60" title="Security Barcode">
                <div class="w-[2px] h-full bg-slate-800"></div>
                <div class="w-[1px] h-full bg-slate-800"></div>
                <div class="w-[3px] h-full bg-slate-800"></div>
                <div class="w-[1px] h-full bg-slate-800"></div>
                <div class="w-[2px] h-full bg-slate-800"></div>
                <div class="w-[4px] h-full bg-slate-800"></div>
                <div class="w-[1px] h-full bg-slate-800"></div>
                <div class="w-[2px] h-full bg-slate-800"></div>
                <div class="w-[3px] h-full bg-slate-800"></div>
                <div class="w-[1px] h-full bg-slate-800"></div>
                <div class="w-[2px] h-full bg-slate-800"></div>
                <div class="w-[1px] h-full bg-slate-800"></div>
                <div class="w-[3px] h-full bg-slate-800"></div>
              </div>
              <span class="font-mono text-[9px] font-bold text-slate-400 tracking-wider">${escapeHtml(s.staff_code)}</span>
              <span class="text-xs font-bold text-slate-600">
                ₹${Number(s.salary || 0).toLocaleString('en-IN')}<span class="text-[10px] font-normal text-slate-400">/mo</span>
              </span>
            </div>
          </div>

          <!-- Card Base Actions Toolbar -->
          <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center gap-1.5">
            <button onclick="openStaffDossier(${s.id})" class="flex-1 py-1.5 px-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/80 font-bold text-xs transition flex items-center justify-center gap-1 shadow-2xs" title="View Full Employee Dossier">
              <i class="fa-regular fa-id-card"></i>
              <span>Dossier</span>
            </button>
            <button onclick="openFaceEnrollModal(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="py-1.5 px-2.5 rounded-xl ${hasFace ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' : 'bg-white text-slate-600 hover:bg-purple-50 hover:text-purple-700 border border-slate-200'} font-bold text-xs transition flex items-center justify-center gap-1 shadow-2xs" title="${hasFace ? 'Update AI Face Biometrics' : 'Enroll AI Face Biometrics'}">
              <i class="fa-solid fa-face-viewfinder text-purple-600"></i>
              <span>${hasFace ? 'Face' : 'Enroll'}</span>
            </button>
            <button onclick="openPermissionsModal(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}', '${escapeHtml(s.staff_code)}')" class="py-1.5 px-2.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 font-bold text-xs transition flex items-center justify-center gap-1 shadow-2xs" title="Manage Account Permissions">
              <i class="fa-solid fa-shield-halved"></i>
              <span>Perms</span>
            </button>
            <button onclick="openStaffModal(${s.id})" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-500 hover:text-blue-600 border border-slate-200 flex items-center justify-center text-xs transition shadow-2xs" title="Edit Staff Profile">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button onclick="confirmDeleteStaff(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="w-8 h-8 rounded-xl bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 flex items-center justify-center text-xs transition shadow-2xs" title="Delete Staff">
              <i class="fa-regular fa-trash-can"></i>
            </button>
          </div>
        </div>`;

      // Table Row Item
      tableHtml += `
        <tr class="hover:bg-slate-50 transition">
          <td class="py-3 px-4">
            <div class="flex items-center gap-2.5">
              ${renderStaffAvatarHtml(s, 'w-8 h-8', 'text-[11px]')}
              <div>
                <div class="font-bold text-slate-900">${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}</div>
                <div class="text-[11px] font-mono text-slate-400">${escapeHtml(s.staff_code)}</div>
              </div>
            </div>
          </td>
          <td class="py-3 px-4">
            <div class="font-bold text-slate-800">${escapeHtml(s.role)}</div>
            <div class="text-[11px] text-slate-400">${escapeHtml(s.department)}</div>
          </td>
          <td class="py-3 px-4 text-slate-700 text-xs">${escapeHtml(s.shift || 'General')}</td>
          <td class="py-3 px-4 text-slate-700 text-xs">
            <a href="tel:${s.phone || ''}" class="hover:text-blue-600 transition">${escapeHtml(s.phone || '-')}</a>
          </td>
          <td class="py-3 px-4">${userBadge}</td>
          <td class="py-3 px-4 font-mono font-bold text-slate-700">₹${Number(s.salary || 0).toLocaleString('en-IN')}</td>
          <td class="py-3 px-4 text-slate-600 text-xs">${s.joining_date || '-'}</td>
          <td class="py-3 px-4 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <button onclick="openStaffDossier(${s.id})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="View Dossier">
                <i class="fa-regular fa-id-card"></i>
              </button>
              <button onclick="openFaceEnrollModal(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="p-1.5 rounded-lg ${hasFace ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' : 'bg-slate-100 hover:bg-purple-50 text-slate-500 hover:text-purple-700'} transition" title="${hasFace ? 'Update AI Face Biometrics' : 'Enroll AI Face Biometrics'}">
                <i class="fa-solid fa-face-viewfinder ${hasFace ? 'text-purple-600' : ''}"></i>
              </button>
              <button onclick="openPermissionsModal(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}', '${escapeHtml(s.staff_code)}')" class="px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200/80 transition flex items-center gap-1" title="Manage Permissions">
                <i class="fa-solid fa-shield-halved"></i> Perms
              </button>
              <button onclick="openStaffModal(${s.id})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Edit Staff">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button onclick="confirmDeleteStaff(${s.id}, '${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 transition" title="Delete">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });

    gridContainer.innerHTML = gridHtml;
    tableBody.innerHTML = tableHtml;
  }

  // Quick 1-Click Mark Attendance for Staff
  async function quickMarkStaff(staffId, status) {
    try {
      const res = await fetch('api/staff.php?action=mark_attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          staff_id: staffId,
          date: selectedDate,
          status: status
        })
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Attendance Updated', `Marked as ${status}`, 'success');
        fetchDailySummary();
        fetchStaffList();
      } else {
        showToast('Error', data.message || 'Failed to mark attendance', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Connection failed', 'error');
    }
  }

  // Quick Mark All Present
  async function quickMarkAllPresent() {
    const btn = document.getElementById('btn-quick-mark');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Marking...</span>';

    try {
      const res = await fetch('api/staff.php?action=quick_mark_all', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          date: selectedDate,
          status: 'Present'
        })
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Success', data.message, 'success');
        fetchDailySummary();
        fetchStaffList();
      } else {
        showToast('Error', data.message || 'Operation failed', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Connection error', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check-double text-xs"></i> <span>Mark All Present</span>';
    }
  }

  // Open Add / Edit Staff Modal
  async function openStaffModal(staffId = 0) {
    const modal = document.getElementById('modal-staff-form');
    const form = document.getElementById('staff-form');
    form.reset();

    document.getElementById('staff-id').value = staffId;
    const titleEl = document.getElementById('staff-modal-title');
    const iconBg = document.getElementById('staff-modal-icon-bg');
    const pwdHint = document.getElementById('staff-pwd-hint');
    const pwdInput = document.getElementById('staff-password');
    const userFields = document.getElementById('user-account-fields');
    const isUserToggle = document.getElementById('staff-is-user');

    if (staffId > 0) {
      titleEl.textContent = 'Edit Staff Member';
      iconBg.innerHTML = '<i class="fa-solid fa-user-pen"></i>';
      pwdHint.classList.remove('hidden');
      pwdInput.required = false;

      // Fetch current data
      try {
        const res = await fetch(`api/staff.php?action=get_staff_details&id=${staffId}`);
        const data = await res.json();
        if (data.status === 'success' && data.data.staff) {
          const s = data.data.staff;
          document.getElementById('staff-first-name').value = s.first_name || '';
          document.getElementById('staff-last-name').value = s.last_name || '';
          document.getElementById('staff-code').value = s.staff_code || '';
          document.getElementById('staff-gender').value = s.gender || 'Female';
          document.getElementById('staff-dob').value = s.date_of_birth || '';
          document.getElementById('staff-blood-group').value = s.blood_group || '';
          document.getElementById('staff-role').value = s.role || '';
          document.getElementById('staff-department').value = s.department || 'Nursing';
          document.getElementById('staff-shift').value = s.shift || 'Morning (08:00 - 16:00)';
          document.getElementById('staff-joining-date').value = s.joining_date || '';
          document.getElementById('staff-salary').value = s.salary || '';
          document.getElementById('staff-status').value = s.status || 'Active';
          document.getElementById('staff-qualification').value = s.qualification || '';
          document.getElementById('staff-phone').value = s.phone || '';
          document.getElementById('staff-email').value = s.email || '';
          document.getElementById('staff-emergency').value = s.emergency_contact || '';
          document.getElementById('staff-address').value = s.address || '';

          // User account
          const isUser = parseInt(s.is_user) === 1;
          isUserToggle.checked = isUser;
          userFields.classList.toggle('hidden', !isUser);
          document.getElementById('staff-username').value = s.username || '';
          // Smart AI Face Biometrics Section
          const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
          const faceImg = document.getElementById('form-face-avatar-img');
          const faceIcon = document.getElementById('form-face-avatar-icon');
          const faceBadge = document.getElementById('form-face-status-badge');
          const faceHint = document.getElementById('form-face-hint');
          const faceBtnText = document.getElementById('form-face-btn-text');

          if (s.avatar) {
            faceImg.src = s.avatar;
            faceImg.classList.remove('hidden');
            faceIcon.classList.add('hidden');
          } else {
            faceImg.src = '';
            faceImg.classList.add('hidden');
            faceIcon.classList.remove('hidden');
          }

          if (hasFace) {
            faceBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 border border-purple-200';
            faceBadge.innerHTML = '<i class="fa-solid fa-circle-check text-[9px] mr-1"></i>Enrolled';
            faceHint.textContent = 'Active AI face biometric profile is registered and ready for attendance.';
            faceBtnText.textContent = 'Update Face Photo';
          } else {
            faceBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600';
            faceBadge.textContent = 'Not Enrolled';
            faceHint.textContent = 'No biometric face enrolled yet. Click to capture live photo.';
            faceBtnText.textContent = 'Enroll Face Photo';
          }
        }
      } catch (e) {
        console.error(e);
      }
    } else {
      titleEl.textContent = 'Add New Staff Member';
      iconBg.innerHTML = '<i class="fa-solid fa-user-plus"></i>';
      pwdHint.classList.add('hidden');
      document.getElementById('staff-joining-date').value = new Date().toISOString().split('T')[0];
      isUserToggle.checked = false;
      userFields.classList.add('hidden');

      // Reset face section for new staff
      const faceImg = document.getElementById('form-face-avatar-img');
      const faceIcon = document.getElementById('form-face-avatar-icon');
      const faceBadge = document.getElementById('form-face-status-badge');
      const faceHint = document.getElementById('form-face-hint');
      const faceBtnText = document.getElementById('form-face-btn-text');
      if (faceImg) {
        faceImg.src = '';
        faceImg.classList.add('hidden');
      }
      if (faceIcon) faceIcon.classList.remove('hidden');
      if (faceBadge) {
        faceBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600';
        faceBadge.textContent = 'New Staff';
      }
      if (faceHint) faceHint.textContent = 'Save this staff member first, then you can enroll their face biometrics.';
      if (faceBtnText) faceBtnText.textContent = 'Enroll Face Photo';
    }

    modal.classList.remove('hidden');
  }

  function triggerFaceEnrollFromForm() {
    const staffId = parseInt(document.getElementById('staff-id').value) || 0;
    const firstName = document.getElementById('staff-first-name').value.trim();
    const lastName = document.getElementById('staff-last-name').value.trim();
    const fullName = (firstName + ' ' + lastName).trim() || 'Staff Member';

    if (!staffId) {
      showToast('Save Required', 'Please save the staff details first before capturing face biometrics.', 'warning');
      return;
    }
    closeStaffModal();
    openFaceEnrollModal(staffId, fullName);
  }

  function closeStaffModal() {
    document.getElementById('modal-staff-form').classList.add('hidden');
  }

  function toggleUserAccountFields(checked) {
    const fields = document.getElementById('user-account-fields');
    fields.classList.toggle('hidden', !checked);
    const pwdInput = document.getElementById('staff-password');
    const staffId = parseInt(document.getElementById('staff-id').value);
    if (checked && staffId === 0) {
      pwdInput.required = true;
    } else {
      pwdInput.required = false;
    }
  }

  function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    if (input.type === 'password') {
      input.type = 'text';
    } else {
      input.type = 'password';
    }
  }

  // Handle Staff Save Submit
  async function handleStaffSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-staff');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const isUser = document.getElementById('staff-is-user').checked ? 1 : 0;
    const username = document.getElementById('staff-username').value.trim();
    const password = document.getElementById('staff-password').value;

    if (isUser && !username) {
      showToast('Validation Error', 'Username is required when granting portal access.', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> <span>Save Staff Member</span>';
      return;
    }

    const payload = {
      id: parseInt(document.getElementById('staff-id').value),
      first_name: document.getElementById('staff-first-name').value.trim(),
      last_name: document.getElementById('staff-last-name').value.trim(),
      staff_code: document.getElementById('staff-code').value.trim(),
      gender: document.getElementById('staff-gender').value,
      date_of_birth: document.getElementById('staff-dob').value,
      blood_group: document.getElementById('staff-blood-group').value,
      role: document.getElementById('staff-role').value.trim(),
      department: document.getElementById('staff-department').value,
      shift: document.getElementById('staff-shift').value,
      joining_date: document.getElementById('staff-joining-date').value,
      salary: document.getElementById('staff-salary').value,
      status: document.getElementById('staff-status').value,
      qualification: document.getElementById('staff-qualification').value.trim(),
      phone: document.getElementById('staff-phone').value.trim(),
      email: document.getElementById('staff-email').value.trim(),
      emergency_contact: document.getElementById('staff-emergency').value.trim(),
      address: document.getElementById('staff-address').value.trim(),
      is_user: isUser,
      username: username,
      password: password
    };

    try {
      const res = await fetch('api/staff.php?action=save_staff', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Success', data.message, 'success');
        closeStaffModal();
        fetchDailySummary();
        fetchStaffList();
      } else {
        showToast('Save Failed', data.message || 'Error occurred', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Server connection error', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> <span>Save Staff Member</span>';
    }
  }

  // Confirm and Delete Staff
  function confirmDeleteStaff(staffId, staffName) {
    openConfirmModal(
      'Delete Staff Member?',
      'This will remove employee information and associated attendance history.',
      `<div class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-800">
        <p class="font-bold">Staff Member: <span class="font-black">${staffName}</span></p>
        <p class="mt-1">All attendance tracking, portal credentials, and records will be deleted immediately.</p>
      </div>`,
      async () => {
        try {
          const res = await fetch('api/staff.php?action=delete_staff', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: staffId })
          });
          const data = await res.json();
          if (data.status === 'success') {
            showToast('Deleted', data.message, 'success');
            closeConfirmModal();
            fetchDailySummary();
            fetchStaffList();
          } else {
            showToast('Delete Failed', data.message || 'Could not delete staff member', 'error');
          }
        } catch (e) {
          console.error(e);
          showToast('Error', 'Connection failed', 'error');
        }
      },
      'danger'
    );
  }

  // Open Staff Dossier Modal
  async function openStaffDossier(staffId) {
    activeDossierStaffId = staffId;
    const modal = document.getElementById('modal-staff-dossier');
    modal.classList.remove('hidden');

    try {
      const res = await fetch(`api/staff.php?action=get_staff_details&id=${staffId}`);
      const data = await res.json();
      if (data.status === 'success') {
        const s = data.data.staff;
        const st = data.data.stats;
        const hist = data.data.history || [];

        const initials = (((s.first_name ? s.first_name[0] : '') + (s.last_name ? s.last_name[0] : '')) || 'ST').toUpperCase();
        const avatarImg = document.getElementById('dossier-avatar-img');
        const avatarText = document.getElementById('dossier-avatar');
        if (s.avatar) {
          avatarImg.src = s.avatar;
          avatarImg.classList.remove('hidden');
          avatarText.classList.add('hidden');
        } else {
          avatarImg.src = '';
          avatarImg.classList.add('hidden');
          avatarText.classList.remove('hidden');
          avatarText.textContent = initials;
        }

        document.getElementById('dossier-name').textContent = `${s.first_name} ${s.last_name}`;
        document.getElementById('dossier-code').textContent = s.staff_code;
        document.getElementById('dossier-subheading').textContent = `${s.role} • ${s.department}`;

        // Face biometrics dossier elements
        const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
        const faceBadge = document.getElementById('dossier-face-badge');
        const faceBtnText = document.getElementById('dossier-face-btn-text');
        const facePhoto = document.getElementById('dossier-face-photo');
        const facePlaceholderIcon = document.getElementById('dossier-face-placeholder-icon');
        const faceStatusPill = document.getElementById('dossier-face-status-pill');
        const faceDesc = document.getElementById('dossier-face-desc');
        const faceActionText = document.getElementById('dossier-face-action-text');

        if (hasFace) {
          if (faceBadge) faceBadge.classList.remove('hidden');
          if (faceBtnText) faceBtnText.textContent = 'Face Enrolled';
          if (s.avatar && facePhoto) {
            facePhoto.src = s.avatar;
            facePhoto.classList.remove('hidden');
            if (facePlaceholderIcon) facePlaceholderIcon.classList.add('hidden');
          } else if (facePhoto) {
            facePhoto.classList.add('hidden');
            if (facePlaceholderIcon) facePlaceholderIcon.classList.remove('hidden');
          }
          if (faceStatusPill) {
            faceStatusPill.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200';
            faceStatusPill.innerHTML = '<i class="fa-solid fa-circle-check text-[9px] mr-1"></i>AI Face Active';
          }
          if (faceDesc) faceDesc.textContent = 'Face biometric profile registered & ready for high-speed AI camera attendance.';
          if (faceActionText) faceActionText.textContent = 'Re-Capture Face';
        } else {
          if (faceBadge) faceBadge.classList.add('hidden');
          if (faceBtnText) faceBtnText.textContent = 'Enroll Face';
          if (facePhoto) {
            facePhoto.classList.add('hidden');
            if (facePlaceholderIcon) facePlaceholderIcon.classList.remove('hidden');
          }
          if (faceStatusPill) {
            faceStatusPill.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200';
            faceStatusPill.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-[9px] mr-1"></i>Not Registered';
          }
          if (faceDesc) faceDesc.textContent = 'No biometric face enrolled yet. Enroll face profile to enable smart 1-second auto punch.';
          if (faceActionText) faceActionText.textContent = 'Enroll Face Now';
        }

        // Status badge
        const statusEl = document.getElementById('dossier-status-pill');
        statusEl.textContent = s.status;
        statusEl.className = s.status === 'Active' ? 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800' : 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700';

        // User tag
        const userTag = document.getElementById('dossier-user-tag');
        if (s.is_user) {
          userTag.innerHTML = `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
              <i class="fa-solid fa-key text-[10px]"></i> Portal User: @${escapeHtml(s.username || '')}
            </span>`;
        } else {
          userTag.innerHTML = `<span class="text-[11px] text-slate-400 font-medium">No portal login account</span>`;
        }

        // Stats
        document.getElementById('dossier-stat-rate').textContent = `${st.attendance_rate || 0}%`;
        document.getElementById('dossier-stat-present').textContent = st.present_count || 0;
        document.getElementById('dossier-stat-late').textContent = `+${st.late_count || 0} late`;
        document.getElementById('dossier-stat-absent').textContent = st.absent_count || 0;
        document.getElementById('dossier-stat-leave').textContent = `+${st.leave_count || 0} leave`;
        document.getElementById('dossier-stat-hours').textContent = `${parseFloat(st.total_hours || 0).toFixed(1)}`;

        // Bio details
        document.getElementById('dossier-phone').textContent = s.phone || '-';
        document.getElementById('dossier-email').textContent = s.email || '-';
        document.getElementById('dossier-shift').textContent = s.shift || 'General';
        document.getElementById('dossier-joining').textContent = s.joining_date || '-';
        document.getElementById('dossier-blood').textContent = s.blood_group || '-';
        document.getElementById('dossier-salary').textContent = s.salary ? `₹${Number(s.salary).toLocaleString('en-IN')}/mo` : '-';
        document.getElementById('dossier-emergency').textContent = s.emergency_contact || '-';
        document.getElementById('dossier-user-login').textContent = s.is_user ? `@${s.username}` : 'Disabled';
        document.getElementById('dossier-qualification').textContent = s.qualification || '-';
        document.getElementById('dossier-address').textContent = s.address || '-';

        // 30-Day History Table
        const histBody = document.getElementById('dossier-history-body');
        if (!hist.length) {
          histBody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-slate-400">No attendance records in the last 30 days.</td></tr>`;
        } else {
          let hHtml = '';
          hist.forEach(h => {
            let badgeClass = 'bg-emerald-100 text-emerald-800';
            if (h.status === 'Late') badgeClass = 'bg-amber-100 text-amber-800';
            if (h.status === 'Half Day') badgeClass = 'bg-sky-100 text-sky-800';
            if (h.status === 'Absent') badgeClass = 'bg-rose-100 text-rose-800';
            if (h.status === 'On Leave') badgeClass = 'bg-purple-100 text-purple-800';

            const inT = h.check_in_time ? h.check_in_time.substring(0, 5) : '--:--';
            const outT = h.check_out_time ? h.check_out_time.substring(0, 5) : '--:--';

            hHtml += `
              <tr class="hover:bg-slate-50 transition">
                <td class="py-2.5 px-3 font-mono font-bold text-slate-700">${h.date}</td>
                <td class="py-2.5 px-3">
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${badgeClass}">${h.status}</span>
                </td>
                <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600">${inT} - ${outT}</td>
                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-700">${parseFloat(h.working_hours || 0).toFixed(1)}h</td>
                <td class="py-2.5 px-3 text-slate-600 truncate max-w-[160px]">${escapeHtml(h.notes || '-')}</td>
                <td class="py-2.5 px-3 text-right text-slate-400 text-[11px]">${escapeHtml(h.marked_by || 'Admin')}</td>
              </tr>`;
          });
          histBody.innerHTML = hHtml;
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  function closeDossierModal() {
    document.getElementById('modal-staff-dossier').classList.add('hidden');
  }

  function editStaffFromDossier() {
    closeDossierModal();
    if (activeDossierStaffId) {
      openStaffModal(activeDossierStaffId);
    }
  }

  // Open Attendance Edit Modal (Specific Times & Remarks)
  function openAttendanceEditModal(staffId, date, staffName) {
    const s = staffList.find(item => item.id == staffId);
    document.getElementById('edit-att-staff-id').value = staffId;
    document.getElementById('edit-att-date').value = date;
    document.getElementById('edit-att-subtitle').textContent = `${staffName} • ${date}`;

    const currentStatus = (s && s.att_status) ? s.att_status : 'Present';
    const radios = document.querySelectorAll('input[name="att_status_radio"]');
    radios.forEach(r => {
      r.checked = r.value === currentStatus;
    });

    const inTime = (s && s.check_in_time) ? s.check_in_time.substring(0, 5) : '08:00';
    const outTime = (s && s.check_out_time) ? s.check_out_time.substring(0, 5) : '16:00';
    const notes = (s && s.att_notes) ? s.att_notes : '';

    document.getElementById('edit-att-check-in').value = inTime;
    document.getElementById('edit-att-check-out').value = outTime;
    document.getElementById('edit-att-notes').value = notes;

    const timeBox = document.getElementById('att-times-container');
    if (currentStatus === 'Absent' || currentStatus === 'On Leave') {
      timeBox.classList.add('opacity-40', 'pointer-events-none');
    } else {
      timeBox.classList.remove('opacity-40', 'pointer-events-none');
    }

    document.getElementById('modal-attendance-edit').classList.remove('hidden');
  }

  function closeAttendanceEditModal() {
    document.getElementById('modal-attendance-edit').classList.add('hidden');
  }

  async function handleAttendanceEditSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-attendance-record');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const staffId = parseInt(document.getElementById('edit-att-staff-id').value);
    const date = document.getElementById('edit-att-date').value;
    const selectedRadio = document.querySelector('input[name="att_status_radio"]:checked');
    const status = selectedRadio ? selectedRadio.value : 'Present';

    const checkIn = document.getElementById('edit-att-check-in').value;
    const checkOut = document.getElementById('edit-att-check-out').value;
    const notes = document.getElementById('edit-att-notes').value.trim();

    try {
      const res = await fetch('api/staff.php?action=mark_attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          staff_id: staffId,
          date: date,
          status: status,
          check_in_time: checkIn ? `${checkIn}:00` : null,
          check_out_time: checkOut ? `${checkOut}:00` : null,
          notes: notes
        })
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Success', data.message, 'success');
        closeAttendanceEditModal();
        fetchDailySummary();
        fetchStaffList();
      } else {
        showToast('Error', data.message || 'Failed to save', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Connection failed', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Save Attendance</span>';
    }
  }

  // Fetch & Render Monthly Attendance Matrix Sheet
  async function fetchMonthlySheet() {
    document.getElementById('monthly-sheet-badge').textContent = selectedMonth;
    const thead = document.getElementById('monthly-table-header');
    const tbody = document.getElementById('monthly-table-body');

    try {
      const res = await fetch(`api/staff.php?action=get_monthly_sheet&month=${selectedMonth}`);
      const data = await res.json();
      if (data.status === 'success') {
        monthlyData = data;
        renderMonthlySheet(data);
      } else {
        showToast('Error', data.message || 'Failed to load month sheet', 'error');
      }
    } catch (err) {
      console.error(err);
    }
  }

  function renderMonthlySheet(data) {
    const thead = document.getElementById('monthly-table-header');
    const tbody = document.getElementById('monthly-table-body');
    const daysInMonth = data.days_in_month;
    const staffList = data.staff || [];
    const matrix = data.matrix || {};

    const [year, mon] = selectedMonth.split('-').map(Number);

    // Build Header
    let hHtml = `
      <tr>
        <th class="py-3 px-3 text-left min-w-[200px] sticky left-0 bg-slate-100 z-30 shadow-xs border-r border-slate-200">Staff Member</th>
        <th class="py-3 px-3 text-left min-w-[120px]">Dept</th>`;

    for (let d = 1; d <= daysInMonth; d++) {
      const dateObj = new Date(year, mon - 1, d);
      const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'narrow' });
      const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
      hHtml += `
        <th class="py-2 px-1 text-center w-8 min-w-[32px] ${isWeekend ? 'bg-slate-200/80 text-rose-600' : 'text-slate-600'}">
          <div class="text-[9px] font-normal uppercase">${dayName}</div>
          <div class="text-[11px] font-black">${d}</div>
        </th>`;
    }

    hHtml += `
        <th class="py-3 px-2 text-center bg-emerald-50 text-emerald-800 min-w-[36px]" title="Present">P</th>
        <th class="py-3 px-2 text-center bg-amber-50 text-amber-800 min-w-[36px]" title="Late">L</th>
        <th class="py-3 px-2 text-center bg-sky-50 text-sky-800 min-w-[36px]" title="Half Day">H</th>
        <th class="py-3 px-2 text-center bg-rose-50 text-rose-800 min-w-[36px]" title="Absent">A</th>
        <th class="py-3 px-2 text-center bg-purple-50 text-purple-800 min-w-[36px]" title="Leave">V</th>
        <th class="py-3 px-2 text-center bg-slate-50 min-w-[50px]">Hours</th>
        <th class="py-3 px-3 text-right bg-blue-50 text-blue-900 min-w-[65px]">Rate</th>
      </tr>`;
    thead.innerHTML = hHtml;

    // Build Rows
    if (!staffList.length) {
      tbody.innerHTML = `<tr><td colspan="${daysInMonth + 9}" class="text-center py-10 text-slate-400">No active staff members found.</td></tr>`;
      return;
    }

    let bHtml = '';
    staffList.forEach(s => {
      const sid = s.id;
      const summ = s.summary || { present: 0, late: 0, half_day: 0, absent: 0, leave: 0, total_hours: 0, attendance_rate: 0 };

      bHtml += `
        <tr class="hover:bg-slate-50/80 transition">
          <td class="py-2.5 px-3 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-200 shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 rounded-md bg-blue-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                ${(s.first_name[0] || '')}${(s.last_name[0] || '')}
              </span>
              <div class="truncate">
                <button onclick="openStaffDossier(${s.id})" class="font-extrabold text-slate-900 hover:text-blue-600 truncate text-xs transition text-left block">
                  ${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}
                </button>
                <span class="text-[10px] font-mono text-slate-400">${escapeHtml(s.staff_code)}</span>
              </div>
            </div>
          </td>
          <td class="py-2.5 px-3 text-slate-600 text-[11px] font-medium truncate max-w-[120px]">
            ${escapeHtml(s.department)}
          </td>`;

      // Day Cells
      for (let d = 1; d <= daysInMonth; d++) {
        const dateObj = new Date(year, mon - 1, d);
        const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
        const record = (matrix[sid] && matrix[sid][d]) ? matrix[sid][d] : null;

        let cellContent = `<span class="text-slate-300 text-[10px] font-mono">—</span>`;
        if (record) {
          const st = record.status;
          const tooltip = `${st} (${record.in ? record.in.substring(0, 5) : ''}-${record.out ? record.out.substring(0, 5) : ''}) ${record.hrs}h`;

          if (st === 'Present') cellContent = `<span class="w-6 h-6 rounded font-black font-mono text-[10px] bg-emerald-500 text-white flex items-center justify-center mx-auto shadow-2xs" title="${tooltip}">P</span>`;
          else if (st === 'Late') cellContent = `<span class="w-6 h-6 rounded font-black font-mono text-[10px] bg-amber-500 text-white flex items-center justify-center mx-auto shadow-2xs" title="${tooltip}">L</span>`;
          else if (st === 'Half Day') cellContent = `<span class="w-6 h-6 rounded font-black font-mono text-[10px] bg-sky-500 text-white flex items-center justify-center mx-auto shadow-2xs" title="${tooltip}">H</span>`;
          else if (st === 'Absent') cellContent = `<span class="w-6 h-6 rounded font-black font-mono text-[10px] bg-rose-500 text-white flex items-center justify-center mx-auto shadow-2xs" title="${tooltip}">A</span>`;
          else if (st === 'On Leave') cellContent = `<span class="w-6 h-6 rounded font-black font-mono text-[10px] bg-purple-500 text-white flex items-center justify-center mx-auto shadow-2xs" title="${tooltip}">V</span>`;
        }

        bHtml += `
          <td class="py-2 px-1 text-center ${isWeekend ? 'bg-slate-50/60' : ''}">
            ${cellContent}
          </td>`;
      }

      // Summary columns
      bHtml += `
          <td class="py-2 px-2 text-center font-bold text-emerald-700 bg-emerald-50/30 text-xs">${summ.present}</td>
          <td class="py-2 px-2 text-center font-bold text-amber-700 bg-amber-50/30 text-xs">${summ.late}</td>
          <td class="py-2 px-2 text-center font-bold text-sky-700 bg-sky-50/30 text-xs">${summ.half_day}</td>
          <td class="py-2 px-2 text-center font-bold text-rose-700 bg-rose-50/30 text-xs">${summ.absent}</td>
          <td class="py-2 px-2 text-center font-bold text-purple-700 bg-purple-50/30 text-xs">${summ.leave}</td>
          <td class="py-2 px-2 text-center font-mono font-bold text-slate-700 text-xs">${summ.total_hours}</td>
          <td class="py-2 px-3 text-right font-black text-blue-700 bg-blue-50/30 text-xs">${summ.attendance_rate}%</td>
        </tr>`;
    });

    tbody.innerHTML = bHtml;
  }

  // Export Helpers
  function exportCurrentViewCSV() {
    document.getElementById('tools-menu').classList.add('hidden');
    if (currentView === 'monthly') {
      exportMonthlyCSV();
    } else {
      exportRosterCSV();
    }
  }

  function exportRosterCSV() {
    if (!staffList.length) {
      showToast('Export Empty', 'No staff records to export.', 'error');
      return;
    }

    let csv = "Staff Code,Full Name,Role,Department,Shift,Phone,Status,Date,Attendance Status,Check-In,Check-Out,Hours,Remarks\n";
    staffList.forEach(s => {
      const row = [
        `"${s.staff_code}"`,
        `"${s.first_name} ${s.last_name}"`,
        `"${s.role}"`,
        `"${s.department}"`,
        `"${s.shift || ''}"`,
        `"${s.phone || ''}"`,
        `"${s.status}"`,
        `"${selectedDate}"`,
        `"${s.att_status || 'Unmarked'}"`,
        `"${s.check_in_time || ''}"`,
        `"${s.check_out_time || ''}"`,
        `"${s.working_hours || '0'}"`,
        `"${(s.att_notes || '').replace(/"/g, '""')}"`
      ];
      csv += row.join(',') + "\n";
    });

    downloadCSV(csv, `Hospital_Staff_Attendance_${selectedDate}.csv`);
  }

  function exportMonthlyCSV() {
    if (!monthlyData || !monthlyData.staff) {
      showToast('Export Error', 'Monthly data not loaded yet.', 'error');
      return;
    }

    const days = monthlyData.days_in_month;
    let header = ["Staff Code", "Full Name", "Department", "Role"];
    for (let d = 1; d <= days; d++) {
      header.push(`Day ${d}`);
    }
    header.push("Present", "Late", "Half Day", "Absent", "Leave", "Total Hours", "Attendance Rate %");

    let csv = header.join(',') + "\n";

    monthlyData.staff.forEach(s => {
      const sid = s.id;
      const summ = s.summary || {};
      let row = [
        `"${s.staff_code}"`,
        `"${s.first_name} ${s.last_name}"`,
        `"${s.department}"`,
        `"${s.role}"`
      ];

      for (let d = 1; d <= days; d++) {
        const rec = (monthlyData.matrix[sid] && monthlyData.matrix[sid][d]) ? monthlyData.matrix[sid][d] : null;
        row.push(`"${rec ? rec.status : '-'}"`);
      }

      row.push(
        summ.present || 0,
        summ.late || 0,
        summ.half_day || 0,
        summ.absent || 0,
        summ.leave || 0,
        summ.total_hours || 0,
        `"${summ.attendance_rate || 0}%"`
      );

      csv += row.join(',') + "\n";
    });

    downloadCSV(csv, `Hospital_Attendance_Sheet_${selectedMonth}.csv`);
  }

  function downloadCSV(csvContent, fileName) {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // HTML sanitization helper
  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // =================================================================
  // SMART AI FACE ENROLLMENT CONTROLLER
  // =================================================================
  let enrollModelsLoaded = false;
  let enrollMediaStream = null;
  let enrollCurrentStaff = null;
  let enrollCurrentDescriptor = null;
  let enrollCurrentSnapshot = null;
  let enrollCameraFacing = 'user';
  let enrollDetectLoopActive = false;
  let enrollIsProcessing = false;

  async function loadEnrollModels() {
    if (enrollModelsLoaded) return true;
    const badgeText = document.getElementById('enroll-camera-badge-text');
    if (badgeText) badgeText.textContent = 'Loading AI Models...';
    try {
      if (typeof faceapi === 'undefined') {
        console.error('faceapi is not defined');
        return false;
      }
      await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri('models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('models')
      ]);
      enrollModelsLoaded = true;
      if (badgeText) badgeText.textContent = 'AI Models Ready';
      return true;
    } catch (err) {
      console.error('Failed to load face-api models:', err);
      if (badgeText) badgeText.textContent = 'Model Load Failed';
      showToast('AI Model Error', 'Could not load biometric models from /models directory.', 'error');
      return false;
    }
  }

  async function openFaceEnrollModal(staffId = 0, staffName = '') {
    const modal = document.getElementById('modal-face-enroll');
    if (!modal) return;
    modal.classList.remove('hidden');

    retakeEnrollSnapshot();

    const selectContainer = document.getElementById('enroll-staff-select-container');
    const selectEl = document.getElementById('enroll-staff-select');

    if (!staffList || staffList.length === 0) {
      await fetchStaffList();
    }

    const sid = parseInt(staffId) || 0;
    if (sid > 0) {
      if (selectContainer) selectContainer.classList.add('hidden');
      let target = staffList.find(s => parseInt(s.id) === sid);
      if (!target) {
        try {
          const res = await fetch(`api/staff.php?action=get_staff_details&id=${sid}`);
          const data = await res.json();
          if (data.status === 'success' && data.data.staff) {
            target = data.data.staff;
          }
        } catch (e) {
          console.error(e);
        }
      }
      setEnrollTargetStaff(target || { id: sid, first_name: staffName || 'Staff', last_name: '', staff_code: 'STF-' + sid });
    } else {
      // Generic open: show staff selector dropdown
      if (selectContainer) selectContainer.classList.remove('hidden');
      if (selectEl) {
        let optHtml = '<option value="">-- Choose Staff Member --</option>';
        staffList.forEach(s => {
          const hasF = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));
          optHtml += `<option value="${s.id}">${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)} (${escapeHtml(s.staff_code)}) ${hasF ? '✓ Enrolled' : ''}</option>`;
        });
        selectEl.innerHTML = optHtml;

        if (staffList.length > 0) {
          selectEl.value = staffList[0].id;
          setEnrollTargetStaff(staffList[0]);
        } else {
          setEnrollTargetStaff(null);
        }
      }
    }

    await loadEnrollModels();
    await startEnrollCamera();
  }

  function setEnrollTargetStaff(s) {
    enrollCurrentStaff = s;
    const nameEl = document.getElementById('enroll-staff-name');
    const badgeEl = document.getElementById('enroll-staff-status-badge');
    const codeEl = document.getElementById('enroll-staff-code');
    const deptEl = document.getElementById('enroll-staff-dept');
    const roleEl = document.getElementById('enroll-staff-role');
    const avatarImg = document.getElementById('enroll-staff-avatar-img');
    const avatarInit = document.getElementById('enroll-staff-avatar-initials');
    const delBtn = document.getElementById('btn-delete-face');

    if (!s) {
      if (nameEl) nameEl.textContent = 'Select Staff Member';
      if (badgeEl) {
        badgeEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600';
        badgeEl.textContent = 'None';
      }
      if (codeEl) codeEl.textContent = '---';
      if (deptEl) deptEl.textContent = '---';
      if (roleEl) roleEl.textContent = '---';
      if (avatarImg) avatarImg.classList.add('hidden');
      if (avatarInit) {
        avatarInit.classList.remove('hidden');
        avatarInit.textContent = '--';
      }
      if (delBtn) delBtn.classList.add('hidden');
      return;
    }

    const fullName = `${s.first_name || ''} ${s.last_name || ''}`.trim() || 'Staff Member';
    const initials = (((s.first_name ? s.first_name[0] : '') + (s.last_name ? s.last_name[0] : '')) || 'ST').toUpperCase();
    const hasFace = !!(s.has_face || (s.avatar && s.avatar.includes('face_')));

    if (nameEl) nameEl.textContent = fullName;
    if (codeEl) codeEl.textContent = s.staff_code || ('STF-' + s.id);
    if (deptEl) deptEl.textContent = s.department || 'General';
    if (roleEl) roleEl.textContent = s.role || 'Staff';

    if (s.avatar && avatarImg && avatarInit) {
      avatarImg.src = s.avatar;
      avatarImg.classList.remove('hidden');
      avatarInit.classList.add('hidden');
    } else if (avatarImg && avatarInit) {
      avatarImg.src = '';
      avatarImg.classList.add('hidden');
      avatarInit.classList.remove('hidden');
      avatarInit.textContent = initials;
    }

    if (hasFace) {
      if (badgeEl) {
        badgeEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200';
        badgeEl.innerHTML = '<i class="fa-solid fa-face-viewfinder mr-1"></i>Enrolled';
      }
      if (delBtn) delBtn.classList.remove('hidden');
    } else {
      if (badgeEl) {
        badgeEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200';
        badgeEl.textContent = 'Not Enrolled';
      }
      if (delBtn) delBtn.classList.add('hidden');
    }
  }

  function onEnrollStaffSelected(staffId) {
    if (!staffId) {
      setEnrollTargetStaff(null);
      return;
    }
    const target = staffList.find(s => parseInt(s.id) === parseInt(staffId));
    if (target) {
      setEnrollTargetStaff(target);
    }
  }

  function closeFaceEnrollModal() {
    stopEnrollCamera();
    const modal = document.getElementById('modal-face-enroll');
    if (modal) modal.classList.add('hidden');
    retakeEnrollSnapshot();
  }

  async function startEnrollCamera() {
    const video = document.getElementById('enroll-video');
    const badgeText = document.getElementById('enroll-camera-badge-text');
    const hint = document.getElementById('enroll-hint-badge');

    stopEnrollCamera();

    if (badgeText) badgeText.textContent = 'Starting Camera...';

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: enrollCameraFacing
        },
        audio: false
      });

      enrollMediaStream = stream;
      if (video) {
        video.srcObject = stream;
        await new Promise(resolve => {
          video.onloadedmetadata = () => {
            video.play();
            resolve();
          };
        });
      }

      if (badgeText) badgeText.textContent = 'Camera Active';
      if (hint) hint.textContent = 'Align face inside the frame';

      startEnrollDetectionLoop();
    } catch (err) {
      console.error('Camera access error:', err);
      if (badgeText) badgeText.textContent = 'Camera Unavailable';
      if (hint) hint.textContent = 'Please allow camera permission in browser settings';
      showToast('Camera Error', 'Could not access camera. Check device permissions.', 'error');
    }
  }

  function stopEnrollCamera() {
    enrollDetectLoopActive = false;
    if (enrollMediaStream) {
      enrollMediaStream.getTracks().forEach(track => track.stop());
      enrollMediaStream = null;
    }
    const video = document.getElementById('enroll-video');
    if (video) video.srcObject = null;
  }

  async function switchEnrollCamera() {
    enrollCameraFacing = enrollCameraFacing === 'user' ? 'environment' : 'user';
    await startEnrollCamera();
  }

  function startEnrollDetectionLoop() {
    enrollDetectLoopActive = true;
    const video = document.getElementById('enroll-video');
    const canvas = document.getElementById('enroll-canvas');
    const scanBox = document.getElementById('enroll-scan-box');
    const btnCapture = document.getElementById('btn-capture-face');
    const btnText = document.getElementById('btn-capture-face-text');
    const hint = document.getElementById('enroll-hint-badge');

    const loop = async () => {
      if (!enrollDetectLoopActive || !video || video.paused || video.ended) return;

      if (enrollModelsLoaded && video.videoWidth > 0 && !enrollIsProcessing) {
        try {
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          const displaySize = { width: video.videoWidth, height: video.videoHeight };
          faceapi.matchDimensions(canvas, displaySize);

          const detection = await faceapi.detectSingleFace(video).withFaceLandmarks();
          const ctx = canvas.getContext('2d');
          ctx.clearRect(0, 0, canvas.width, canvas.height);

          if (detection) {
            const resizedDetection = faceapi.resizeResults(detection, displaySize);

            // Draw bounding box and landmarks
            faceapi.draw.drawDetections(canvas, resizedDetection);
            faceapi.draw.drawFaceLandmarks(canvas, resizedDetection);

            if (scanBox) {
              scanBox.classList.remove('border-dashed', 'border-white/40', 'border-rose-500');
              scanBox.classList.add('border-emerald-400');
            }

            if (btnCapture) {
              btnCapture.disabled = false;
              btnCapture.className = 'px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-md shadow-purple-500/25 transition flex items-center gap-2 cursor-pointer';
            }
            if (btnText) btnText.textContent = 'Capture Face';
            if (hint) hint.textContent = 'Face detected! Hold still and click Capture Face';
          } else {
            if (scanBox) {
              scanBox.classList.remove('border-emerald-400');
              scanBox.classList.add('border-dashed', 'border-white/40');
            }

            if (btnCapture) {
              btnCapture.disabled = true;
              btnCapture.className = 'px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 cursor-not-allowed font-black text-xs transition flex items-center gap-2 shadow-xs';
            }
            if (btnText) btnText.textContent = 'Align Face to Capture';
            if (hint) hint.textContent = 'Looking for face... Look straight at the camera';
          }
        } catch (e) {
          // Ignore transient detection frame errors
        }
      }

      if (enrollDetectLoopActive) {
        requestAnimationFrame(loop);
      }
    };

    requestAnimationFrame(loop);
  }

  async function captureAndProcessFace() {
    if (!enrollCurrentStaff) {
      showToast('Select Staff', 'Please choose a staff member to enroll.', 'warning');
      return;
    }

    const video = document.getElementById('enroll-video');
    const previewOverlay = document.getElementById('enroll-preview-overlay');
    const previewImg = document.getElementById('enroll-preview-img');
    const btnCapture = document.getElementById('btn-capture-face');
    const btnText = document.getElementById('btn-capture-face-text');

    if (!video || video.videoWidth === 0) return;

    enrollIsProcessing = true;
    if (btnText) btnText.textContent = 'Computing 128-D Vector...';
    if (btnCapture) btnCapture.disabled = true;

    try {
      const fullDetection = await faceapi.detectSingleFace(video)
        .withFaceLandmarks()
        .withFaceDescriptor();

      if (!fullDetection) {
        showToast('Face Lost', 'Please hold steady in front of the camera and try again.', 'warning');
        enrollIsProcessing = false;
        if (btnText) btnText.textContent = 'Capture Face';
        if (btnCapture) btnCapture.disabled = false;
        return;
      }

      enrollCurrentDescriptor = Array.from(fullDetection.descriptor);

      // Snapshot mirrored horizontally if selfie camera
      const snapCanvas = document.createElement('canvas');
      snapCanvas.width = video.videoWidth;
      snapCanvas.height = video.videoHeight;
      const ctx = snapCanvas.getContext('2d');
      if (enrollCameraFacing === 'user') {
        ctx.translate(snapCanvas.width, 0);
        ctx.scale(-1, 1);
      }
      ctx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);

      enrollCurrentSnapshot = snapCanvas.toDataURL('image/jpeg', 0.88);

      enrollDetectLoopActive = false;
      if (previewImg) previewImg.src = enrollCurrentSnapshot;
      if (previewOverlay) previewOverlay.classList.remove('hidden');

    } catch (err) {
      console.error('Face processing error:', err);
      showToast('Error', 'Failed to process biometric face data.', 'error');
    } finally {
      enrollIsProcessing = false;
    }
  }

  function retakeEnrollSnapshot() {
    const previewOverlay = document.getElementById('enroll-preview-overlay');
    if (previewOverlay) previewOverlay.classList.add('hidden');
    enrollCurrentDescriptor = null;
    enrollCurrentSnapshot = null;
    startEnrollDetectionLoop();
  }

  async function saveCapturedFace(forceOverride = false) {
    if (!enrollCurrentStaff || !enrollCurrentDescriptor || !enrollCurrentSnapshot) {
      showToast('Missing Data', 'No face biometrics captured yet.', 'warning');
      return;
    }

    const saveBtn = document.getElementById('btn-save-enroll-captured');
    const origHtml = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Biometrics...';
    }

    try {
      const payload = {
        staff_id: enrollCurrentStaff.id,
        face_descriptor: enrollCurrentDescriptor,
        snapshot_image: enrollCurrentSnapshot
      };
      if (forceOverride) {
        payload.force_override = true;
      }

      const res = await fetch('api/staff_face.php?action=register_face', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.status === 'success') {
        showToast('Face Biometrics Saved!', `${enrollCurrentStaff.first_name}'s face profile is now live for AI attendance.`, 'success');
        
        enrollCurrentStaff.has_face = true;
        if (data.data && data.data.avatar) {
          enrollCurrentStaff.avatar = data.data.avatar;
        }

        closeFaceEnrollModal();
        await fetchStaffList();

        // Update Dossier if open
        if (activeDossierStaffId && parseInt(activeDossierStaffId) === parseInt(enrollCurrentStaff.id)) {
          openStaffDossier(activeDossierStaffId);
        }

        // Update Staff Edit Modal if open
        const editStaffId = parseInt(document.getElementById('staff-id').value) || 0;
        if (editStaffId === parseInt(enrollCurrentStaff.id)) {
          const faceImg = document.getElementById('form-face-avatar-img');
          const faceIcon = document.getElementById('form-face-avatar-icon');
          const faceBadge = document.getElementById('form-face-status-badge');
          const faceBtnText = document.getElementById('form-face-btn-text');
          if (faceImg && enrollCurrentStaff.avatar) {
            faceImg.src = enrollCurrentStaff.avatar;
            faceImg.classList.remove('hidden');
            if (faceIcon) faceIcon.classList.add('hidden');
          }
          if (faceBadge) {
            faceBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 border border-purple-200';
            faceBadge.innerHTML = '<i class="fa-solid fa-circle-check text-[9px] mr-1"></i>Enrolled';
          }
          if (faceBtnText) faceBtnText.textContent = 'Update Face Photo';
        }

      } else if (data.status === 'duplicate') {
        const conf = confirm(
          `${data.message}\n\nExisting profile: ${data.data.matched_staff.name} (${data.data.matched_staff.code})\n\nDo you want to FORCE ASSIGN this face to ${enrollCurrentStaff.first_name} anyway?`
        );
        if (conf) {
          await saveCapturedFace(true);
        }
      } else {
        showToast('Save Failed', data.message || 'Could not register face biometrics.', 'error');
      }
    } catch (err) {
      console.error('Save face error:', err);
      showToast('Error', 'Connection failed while saving biometrics.', 'error');
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = origHtml;
      }
    }
  }

  async function deleteCurrentStaffFace() {
    if (!enrollCurrentStaff) return;

    if (!confirm(`Are you sure you want to delete the AI face biometric profile for ${enrollCurrentStaff.first_name} ${enrollCurrentStaff.last_name}?`)) {
      return;
    }

    try {
      const res = await fetch('api/staff_face.php?action=delete_face', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ staff_id: enrollCurrentStaff.id })
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Biometrics Removed', 'Face profile deleted successfully.', 'success');
        enrollCurrentStaff.has_face = false;
        closeFaceEnrollModal();
        await fetchStaffList();

        if (activeDossierStaffId && parseInt(activeDossierStaffId) === parseInt(enrollCurrentStaff.id)) {
          openStaffDossier(activeDossierStaffId);
        }
      } else {
        showToast('Error', data.message || 'Failed to delete biometrics.', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Connection error.', 'error');
    }
  }
</script>

<?php include 'includes/permissions_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
<script src="js/permissions.js"></script>

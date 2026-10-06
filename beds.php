<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- Clean Print Layout for Official Prescription & Medical Summary -->
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printable-prescription-container,
    #printable-prescription-container * {
        visibility: visible !important;
    }
    #printable-prescription-container {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100vw !important;
        height: auto !important;
        margin: 0 !important;
        padding: 24px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        display: block !important;
        z-index: 999999 !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="space-y-6 max-w-7xl mx-auto pb-12">

  <!-- ================= TOP HERO & WARD CONTROLS ================= -->
  <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-7 shadow-sm flex flex-col xl:flex-row items-start xl:items-center justify-between gap-5 relative overflow-hidden">
    <!-- Subtle Background Glow -->
    <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-rose-500/5 blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
      <div class="flex items-center gap-3 mb-2 flex-wrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
          Ward Monitoring Active
        </span>
        <span class="text-xs text-slate-400 font-bold" id="live-time-stamp">Updated live</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
        <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shadow-xs">
          <i class="fa-solid fa-bed-pulse"></i>
        </span>
        <span>Hospital Bed Ward & Inpatient Registry</span>
      </h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl font-medium">
        Real-time interactive floor occupancy matrix for Inpatient General Wards and ICU Critical Care. Monitor admitted patients, review full clinical reports, and manage bed allocations.
      </p>
    </div>

    <!-- Right Side: Top Action Buttons -->
    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto justify-start xl:justify-end relative z-10">
      <button onclick="fetchBeds()" class="p-2.5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs" title="Refresh Live Registry">
        <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
        <span class="hidden sm:inline">Refresh</span>
      </button>

      <button onclick="openQuickAdmitModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-2xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
        <i class="fa-solid fa-user-plus text-xs"></i>
        <span>Admit Patient</span>
      </button>

      <button onclick="openAddBedModal()" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-2xl shadow-md shadow-rose-600/20 transition flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Add New Bed</span>
      </button>
    </div>
  </div>

  <!-- ================= EXECUTIVE OCCUPANCY & CAPACITY METRICS ================= -->
  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3.5 sm:gap-4">
    <!-- Card 1: Total Hospital Capacity -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-slate-300 transition">
      <div>
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Bed Capacity</div>
        <div id="stat-total-beds" class="text-2xl sm:text-3xl font-black text-slate-900 mt-0.5">0</div>
        <div class="text-[10px] text-slate-500 font-bold mt-1">Across All Wards</div>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0">
        <i class="fa-solid fa-hospital"></i>
      </div>
    </div>

    <!-- Card 2: Vacant & Ready -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-emerald-200 transition">
      <div>
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Available / Vacant</div>
        <div id="stat-total-avail" class="text-2xl sm:text-3xl font-black text-emerald-600 mt-0.5">0</div>
        <div class="text-[10px] text-emerald-700 font-bold mt-1">Sanitized & Ready</div>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 border border-emerald-100">
        <i class="fa-solid fa-circle-check"></i>
      </div>
    </div>

    <!-- Card 3: Currently Occupied -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-rose-200 transition">
      <div>
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Occupied Beds</div>
        <div id="stat-total-occupied" class="text-2xl sm:text-3xl font-black text-rose-600 mt-0.5">0</div>
        <div class="text-[10px] text-rose-700 font-bold mt-1">Active Inpatients</div>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 border border-rose-100">
        <i class="fa-solid fa-user-injured"></i>
      </div>
    </div>

    <!-- Card 4: OPD Ward -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-blue-200 transition">
      <div>
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600">OPD Ward</div>
        <div class="flex items-baseline gap-1 mt-0.5">
          <span id="stat-opd-avail" class="text-xl sm:text-2xl font-black text-emerald-600">0</span>
          <span class="text-xs text-slate-400 font-bold">/ <span id="stat-opd-total">0</span> Total</span>
        </div>
        <div class="text-[10px] text-slate-500 font-bold mt-1" id="stat-opd-occupancy-desc">0% Occupied</div>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100">
        <i class="fa-solid fa-bed"></i>
      </div>
    </div>

    <!-- Card 5: ICU Critical Care -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-amber-200 transition col-span-2 sm:col-span-1">
      <div>
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">ICU Critical Care</div>
        <div class="flex items-baseline gap-1 mt-0.5">
          <span id="stat-icu-avail" class="text-xl sm:text-2xl font-black text-emerald-600">0</span>
          <span class="text-xs text-slate-400 font-bold">/ <span id="stat-icu-total">0</span> Total</span>
        </div>
        <div class="text-[10px] text-slate-500 font-bold mt-1" id="stat-icu-occupancy-desc">0% Occupied</div>
      </div>
      <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 border border-amber-100">
        <i class="fa-solid fa-heart-pulse"></i>
      </div>
    </div>
  </div>

  <!-- Overall Occupancy Progress Bar -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
    <div class="flex items-center justify-between text-xs font-bold mb-2">
      <span class="text-slate-700 flex items-center gap-1.5">
        <i class="fa-solid fa-chart-pie text-indigo-600"></i> Overall Hospital Bed Utilization Rate
      </span>
      <span id="occupancy-rate-label" class="text-indigo-700 font-extrabold font-mono">0% (0 / 0 Occupied)</span>
    </div>
    <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden relative">
      <div id="occupancy-progress-bar" class="h-full bg-gradient-to-r from-emerald-500 to-indigo-600 rounded-full transition-all duration-700" style="width: 0%"></div>
    </div>
  </div>

  <!-- ================= SEARCH, FILTER & VIEW CONTROLS ================= -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3.5">
    
    <!-- Search Bar -->
    <div class="relative w-full md:max-w-md">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
      <input type="text" id="bed-search-input" oninput="applyFilters()" placeholder="Search by patient name, MRN, bed number, or doctor..." class="w-full text-xs font-semibold pl-9 pr-8 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition placeholder-slate-400">
      <button onclick="clearBedSearch()" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 w-5 h-5 rounded-full flex items-center justify-center">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Ward Filter, Status Filter & Grid/Table Switcher -->
    <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-between md:justify-end">
      <!-- Ward Filter Buttons -->
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold text-slate-600 shadow-inner">
        <button id="filter-ward-all" onclick="setWardFilter('all')" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 transition">All Wards</button>
        <button id="filter-ward-opd" onclick="setWardFilter('OPD')" class="px-3 py-1.5 rounded-lg hover:text-slate-900 transition">OPD</button>
        <button id="filter-ward-icu" onclick="setWardFilter('ICU')" class="px-3 py-1.5 rounded-lg hover:text-slate-900 transition">ICU</button>
      </div>

      <!-- Status Filter Dropdown -->
      <select id="bed-status-filter" onchange="applyFilters()" class="text-xs font-bold border border-slate-200 px-3 py-2 rounded-xl bg-white text-slate-700 outline-none focus:ring-2 focus:ring-rose-500/20">
        <option value="all">All Statuses</option>
        <option value="Occupied">Occupied Only</option>
        <option value="Available">Available (Vacant) Only</option>
      </select>

      <!-- View Switcher -->
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold text-slate-600 shadow-inner">
        <button id="btn-bed-view-grid" onclick="setBedDisplayMode('grid')" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 flex items-center gap-1.5 transition">
          <i class="fa-solid fa-border-all"></i> Grid
        </button>
        <button id="btn-bed-view-table" onclick="setBedDisplayMode('table')" class="px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition">
          <i class="fa-solid fa-table-list"></i> Table
        </button>
      </div>
    </div>
  </div>

  <!-- ================= GRID VIEW: FLOOR LAYOUT MATRIX ================= -->
  <div id="bed-container-grid" class="space-y-6 sm:space-y-8">
    
    <!-- OPD Ward Section -->
    <div id="section-ward-opd" class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3 mb-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold border border-blue-100">
            <i class="fa-solid fa-bed"></i>
          </div>
          <div>
            <h2 class="font-extrabold text-base sm:text-lg text-slate-900 flex items-center gap-2">
              <span>OPD Observation Ward</span>
              <span id="badge-opd-count" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">0 Beds</span>
            </h2>
            <p class="text-xs text-slate-400 font-medium">General Inpatient Floor • North Wing, 2nd Floor</p>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span id="opd-avail-count-sub">0 Vacant</span>
          </span>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-100">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
            <span id="opd-occ-count-sub">0 Occupied</span>
          </span>
        </div>
      </div>

      <div id="opd-bed-matrix" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <!-- Dynamic OPD bed cards inserted here -->
      </div>
    </div>

    <!-- ICU Critical Care Ward Section -->
    <div id="section-ward-icu" class="bg-white rounded-3xl border border-rose-200 p-5 sm:p-6 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-rose-100 gap-3 mb-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold border border-rose-200">
            <i class="fa-solid fa-heart-pulse"></i>
          </div>
          <div>
            <h2 class="font-extrabold text-base sm:text-lg text-rose-800 flex items-center gap-2">
              <span>ICU Critical Care Ward</span>
              <span id="badge-icu-count" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300">0 Beds</span>
            </h2>
            <p class="text-xs text-rose-500 font-medium">Intensive Care Monitoring • Critical Care Wing, 3rd Floor</p>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span id="icu-avail-count-sub">0 Vacant</span>
          </span>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-bold border border-rose-200">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
            <span id="icu-occ-count-sub">0 Occupied</span>
          </span>
        </div>
      </div>

      <div id="icu-bed-matrix" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <!-- Dynamic ICU bed cards inserted here -->
      </div>
    </div>

  </div>

  <!-- ================= TABLE VIEW: COMPREHENSIVE DATA REGISTRY ================= -->
  <div id="bed-container-table" class="hidden bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50/80 font-black border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[11px]">
          <tr>
            <th class="p-4">Bed Designation</th>
            <th class="p-4">Ward / Location</th>
            <th class="p-4">Status</th>
            <th class="p-4">Patient Profile</th>
            <th class="p-4">Demographics</th>
            <th class="p-4">Attending Doctor</th>
            <th class="p-4">Admission Details</th>
            <th class="p-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="bed-table-tbody" class="divide-y divide-slate-100 font-medium text-slate-700">
          <!-- Dynamic table rows -->
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 1: PROFESSIONAL PATIENT REPORT & DOSSIER ======== -->
<!-- ================= (Modeled after patient_profile.php in popup style) ==== -->
<!-- ========================================================================= -->
<div id="modal-patient-report" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-5 bg-slate-900/75 backdrop-blur-md overflow-y-auto">
  <div class="bg-slate-50 rounded-3xl max-w-5xl w-full shadow-2xl border border-slate-200 relative my-auto sm:my-6 flex flex-col max-h-[94vh] overflow-hidden">
    
    <!-- Modal Sticky Top Bar -->
    <div class="p-3.5 sm:px-6 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 z-20">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold border border-indigo-100">
          <i class="fa-solid fa-file-medical"></i>
        </span>
        <div>
          <h2 class="text-sm font-black text-slate-900 leading-none flex items-center gap-2">
            <span>Patient Clinical Dossier & Medical Summary</span>
            <span id="pop-header-mrn" class="font-mono text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">MRN-000</span>
          </h2>
          <p class="text-[11px] text-slate-500 mt-0.5">Comprehensive in-hospital medical record, clinical workstation & investigations</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Print Slip Button -->
        <button onclick="printCurrentPatientSlip()" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center gap-1.5 transition border border-indigo-200" title="Print Medical Prescription Slip">
          <i class="fa-solid fa-print"></i>
          <span class="hidden sm:inline">Print Slip</span>
        </button>

        <!-- Open Dedicated Full Page Link -->
        <a id="pop-btn-open-full" href="#" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition" title="Open Full Screen Profile Page">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
          <span class="hidden sm:inline">Open Full Page</span>
        </a>

        <!-- Close Button -->
        <button onclick="closePatientReportModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Scrollable Modal Body -->
    <div class="overflow-y-auto custom-scrollbar flex-1 p-4 sm:p-6 space-y-6">
      
      <!-- ================= PATIENT HEADER BANNER ================= -->
      <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-3xl p-5 sm:p-6 text-white shadow-md relative overflow-hidden">
        <!-- Graphic pattern overlay -->
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
          <div class="flex items-center gap-4">
            <!-- Avatar Initials -->
            <div id="pop-avatar" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white text-indigo-700 flex items-center justify-center text-2xl sm:text-3xl font-black shadow-lg shadow-black/20 shrink-0">
              KP
            </div>

            <div>
              <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span id="pop-status-badge" class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-emerald-400/20 text-emerald-200 border border-emerald-300/30 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span>Active Inpatient</span>
                </span>
                <span id="pop-bed-badge" class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-white/20 text-white border border-white/30 flex items-center gap-1">
                  <i class="fa-solid fa-bed"></i>
                  <span id="pop-bed-text">Hospital Bed #OPD-101</span>
                </span>
              </div>

              <h1 id="pop-patient-name" class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-white">Patient Name</h1>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-blue-100 font-medium mt-1">
                <span>Father: <strong id="pop-father" class="text-white">...</strong></span>
                <span>•</span>
                <span>Phone: <a href="#" id="pop-phone" class="text-white hover:underline font-bold">...</a></span>
                <span>•</span>
                <span>Emergency: <strong id="pop-emergency" class="text-white">...</strong></span>
              </div>
            </div>
          </div>

          <!-- Demographics Badge Pills -->
          <div class="grid grid-cols-3 gap-2 shrink-0 bg-white/10 backdrop-blur-md p-2.5 rounded-2xl border border-white/15">
            <div class="text-center px-2 py-1">
              <span class="block text-[9px] uppercase tracking-wider text-blue-200 font-bold">Age</span>
              <span id="pop-age" class="text-sm font-black text-white">--</span>
            </div>
            <div class="text-center px-2 py-1 border-x border-white/15">
              <span class="block text-[9px] uppercase tracking-wider text-blue-200 font-bold">Gender</span>
              <span id="pop-gender" class="text-sm font-black text-white">--</span>
            </div>
            <div class="text-center px-2 py-1">
              <span class="block text-[9px] uppercase tracking-wider text-rose-200 font-bold">Blood</span>
              <span id="pop-blood" class="text-sm font-black text-rose-300">--</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= CLINICAL QUICK STATS ================= -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs text-center">
          <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 block mb-0.5">Visits / Consultations</span>
          <span id="pop-stat-visits" class="text-xl sm:text-2xl font-black text-slate-900">0</span>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs text-center">
          <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 block mb-0.5">Active Medicines (Rx)</span>
          <span id="pop-stat-meds" class="text-xl sm:text-2xl font-black text-slate-900">0</span>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs text-center">
          <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-600 block mb-0.5">Scans & Lab Reports</span>
          <span id="pop-stat-files" class="text-xl sm:text-2xl font-black text-slate-900">0</span>
        </div>
      </div>

      <!-- ================= MODAL TABS ================= -->
      <div class="bg-white rounded-2xl border border-slate-200 p-1.5 flex gap-1.5 shadow-2xs">
        <button onclick="switchPopupTab('consultations')" id="poptab-btn-consultations" class="poptab flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-indigo-700 bg-indigo-50 flex items-center justify-center gap-2 transition">
          <i class="fa-solid fa-stethoscope"></i>
          <span>Clinical Workstation & Consultations</span>
        </button>
        <button onclick="switchPopupTab('files')" id="poptab-btn-files" class="poptab flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 flex items-center justify-center gap-2 transition">
          <i class="fa-solid fa-file-waveform"></i>
          <span>Medical Scans & Lab Reports</span>
        </button>
        <button onclick="switchPopupTab('stay')" id="poptab-btn-stay" class="poptab flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 flex items-center justify-center gap-2 transition">
          <i class="fa-solid fa-bed"></i>
          <span>Inpatient Stay Details</span>
        </button>
      </div>

      <!-- ================= TAB CONTENT 1: CONSULTATIONS & CLINICAL WORKSTATION ================= -->
      <div id="poptab-content-consultations" class="poptab-view space-y-4">
        <!-- Rendered dynamically -->
        <div id="pop-consultations-list" class="space-y-4"></div>
      </div>

      <!-- ================= TAB CONTENT 2: SCANS & MEDICAL REPORTS ================= -->
      <div id="poptab-content-files" class="poptab-view hidden space-y-4">
        <div id="pop-files-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <!-- Rendered dynamically -->
        </div>
      </div>

      <!-- ================= TAB CONTENT 3: INPATIENT STAY & WARD LOGS ================= -->
      <div id="poptab-content-stay" class="poptab-view hidden space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
          <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
            <i class="fa-solid fa-bed text-indigo-600"></i>
            <span>Current Inpatient Bed Allocation</span>
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Bed Number</span>
              <span id="pop-stay-bed-num" class="text-base font-black text-slate-900 font-mono">--</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Ward Classification</span>
              <span id="pop-stay-ward" class="text-sm font-black text-slate-900">--</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Floor Location</span>
              <span id="pop-stay-wing" class="text-sm font-black text-slate-900">--</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Admission Timestamp</span>
              <span id="pop-stay-admit-time" class="text-sm font-black text-slate-900 font-mono">--</span>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-slate-500 font-medium">
              Attending Physician: <strong id="pop-stay-doctor" class="text-slate-800">--</strong>
            </div>

            <div class="flex items-center gap-2">
              <button id="pop-stay-transfer-btn" onclick="openTransferModalFromDossier()" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-arrows-turn-to-dots text-indigo-600"></i>
                <span>Transfer Bed</span>
              </button>
              <button id="pop-stay-discharge-btn" onclick="openDischargeModalFromDossier()" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-rose-600/20">
                <i class="fa-solid fa-person-walking-arrow-right"></i>
                <span>Discharge Patient</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 2: OFFICIAL PRINTABLE PRESCRIPTION SLIP ========= -->
<!-- ========================================================================= -->
<div id="modal-print-prescription" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-200 relative my-6 flex flex-col max-h-[94vh] overflow-hidden">
        
        <!-- Modal Top Bar (Non-printed) -->
        <div class="p-4 sm:px-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between shrink-0 no-print">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-file-prescription"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 leading-none">Medical Consultation &amp; Prescription Slip</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Print or save as PDF for patient record</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-200 transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Slip</span>
                </button>
                <button onclick="closePrintModal()" class="w-8 h-8 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Printable Document Area -->
        <div class="flex-1 overflow-y-auto p-6 sm:p-10 bg-white" id="printable-prescription-container">
            <!-- Hospital Header -->
            <div class="border-b-2 border-slate-900 pb-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-wide">BHOOMA MEDICARE HOSPITAL</h1>
                        <p class="text-xs text-slate-500 font-semibold">Multispecialty Care &amp; Intensive Care Unit • 24x7 Emergency Services</p>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500 space-y-0.5">
                    <p class="font-bold text-slate-800">Phone: +91 (079) 2685-4000</p>
                    <p>Opp. City Center, Ahmedabad, Gujarat</p>
                    <p class="font-mono text-[11px] text-slate-400">Date: <span id="print-date">--</span></p>
                </div>
            </div>

            <!-- Patient Demographics & Doctor Bar -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Patient Name</span>
                    <strong class="text-slate-900 text-sm" id="print-patient-name">--</strong>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5" id="print-patient-mrn">--</div>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Age / Gender / Blood</span>
                    <strong class="text-slate-800" id="print-demographics">--</strong>
                    <div class="text-[11px] text-slate-500 mt-0.5">Father: <span id="print-father">--</span></div>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Attending Doctor</span>
                    <strong class="text-slate-900" id="print-doctor-name">Dr. --</strong>
                    <div class="text-[11px] text-indigo-600 font-bold" id="print-doctor-dept">General Medicine</div>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Consultation Code</span>
                    <strong class="text-slate-800 font-mono" id="print-appt-code">--</strong>
                    <div class="text-[11px] text-slate-500" id="print-appt-type">General Consultation</div>
                </div>
            </div>

            <!-- Inpatient Bed Banner (If Admitted) -->
            <div class="mb-6" id="print-bed-section">
                <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-bed text-blue-600 text-base"></i>
                        <div>
                            <span class="font-extrabold block">Inpatient Admission Record:</span>
                            <span id="print-bed-text" class="text-blue-800">Admitted to Hospital Bed</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-blue-200 text-blue-800">Active Inpatient</span>
                </div>
            </div>

            <!-- Chief Complaints & Confirmed Diagnoses -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="border border-slate-200 rounded-2xl p-4">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-question text-indigo-600"></i>
                        <span>Chief Complaints / Symptoms:</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-800 italic" id="print-symptoms">None recorded</p>
                </div>
                <div class="border border-slate-200 rounded-2xl p-4">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-stethoscope text-rose-600"></i>
                        <span>Clinical Diagnoses:</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5" id="print-diagnoses-list">
                        <span class="text-xs text-slate-400 italic">None recorded</span>
                    </div>
                </div>
            </div>

            <!-- Prescribed Medications Table (Rx) -->
            <div class="mb-6">
                <div class="text-xs font-black uppercase tracking-wider text-slate-900 mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-pills text-emerald-600"></i>
                    <span>Prescribed Medications (Rx):</span>
                </div>
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 font-bold border-b border-slate-200 text-slate-700 text-[11px]">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">Medicine Name</th>
                                <th class="p-3">Dosage</th>
                                <th class="p-3">Timing / Frequency</th>
                                <th class="p-3">Duration</th>
                                <th class="p-3">Special Instructions</th>
                            </tr>
                        </thead>
                        <tbody id="print-medicines-tbody" class="divide-y divide-slate-100">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ordered Diagnostic Tests -->
            <div class="mb-6" id="print-tests-section">
                <div class="text-[11px] font-black uppercase tracking-wider text-amber-800 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-flask-vial text-amber-600"></i>
                    <span>Diagnostic Investigations &amp; Scans Ordered:</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200">
                    <div class="flex flex-wrap gap-2 mb-1" id="print-tests-list"></div>
                    <p class="text-[11px] text-amber-800 font-medium">Please undergo the above diagnostic laboratory/radiology investigations and return for clinical review with test reports.</p>
                </div>
            </div>

            <!-- Doctor's Advice & Patient Instructions -->
            <div class="mb-8" id="print-advice-section">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-doctor text-indigo-600"></i>
                    <span>Doctor's Advice &amp; Lifestyle Guidance:</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 leading-relaxed whitespace-pre-wrap" id="print-advice-text">
                    Standard hospital rest and adequate hydration. Re-consult if symptoms aggravate.
                </div>
            </div>

            <!-- Signatures -->
            <div class="border-t-2 border-slate-200 pt-6 mt-8 flex flex-col sm:flex-row items-end justify-between gap-6">
                <div class="text-[11px] text-slate-400 space-y-0.5">
                    <p>• Not valid for medico-legal purposes.</p>
                    <p>• Keep medicines away from children. Store in a cool, dry place.</p>
                    <p>• In case of emergency or severe adverse reactions, report to Emergency Room immediately.</p>
                </div>
                <div class="text-center min-w-[200px] space-y-1">
                    <div class="h-12 border-b border-dashed border-slate-300"></div>
                    <div class="text-xs font-black text-slate-900" id="print-sign-doctor">Dr. --</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Authorized Signature &amp; Seal</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 3: QUICK ADMIT PATIENT ========================== -->
<!-- ========================================================================= -->
<div id="modal-admit-patient" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 relative my-8 shadow-2xl border border-slate-200">
    <button onclick="closeQuickAdmitModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold border border-indigo-100">
        <i class="fa-solid fa-user-plus"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-lg">Inpatient Bed Admission</h3>
        <p class="text-xs text-slate-500 font-medium">Assign an admitted patient to a vacant ward or ICU bed.</p>
      </div>
    </div>

    <form onsubmit="handleAdmitPatient(event)" class="space-y-4">
      <!-- Select Patient -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Registered Patient *</label>
        <select id="admit-patient-select" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- Choose Patient --</option>
        </select>
        <p class="text-[10px] text-slate-400 mt-1">Select from registered patients in directory</p>
      </div>

      <!-- Select Bed -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Assign Vacant Bed *</label>
        <select id="admit-bed-select" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- Choose Vacant Bed --</option>
        </select>
      </div>

      <!-- Select Attending Doctor -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Attending Doctor</label>
        <select id="admit-doctor-select" class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- Unassigned / General Duty --</option>
        </select>
      </div>

      <!-- Reason / Chief Complaint -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason for Admission / Symptoms</label>
        <textarea id="admit-reason" rows="2" placeholder="e.g. Acute chest pain, continuous fever observation..." class="w-full text-xs font-medium border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none"></textarea>
      </div>

      <div class="pt-2">
        <button type="submit" id="btn-submit-admit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-xs font-black shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-check"></i>
          <span>Confirm Admission &amp; Allocate Bed</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 4: TRANSFER BED ================================= -->
<!-- ========================================================================= -->
<div id="modal-transfer-bed" class="hidden fixed inset-0 z-[140] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 relative my-8 shadow-2xl border border-slate-200">
    <button onclick="closeTransferModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold border border-blue-100">
        <i class="fa-solid fa-arrows-turn-to-dots"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-lg">Transfer Inpatient</h3>
        <p class="text-xs text-slate-500 font-medium">Relocate patient to another vacant bed.</p>
      </div>
    </div>

    <form onsubmit="handleTransferBed(event)" class="space-y-4">
      <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
        <span class="text-[10px] uppercase font-bold text-slate-400 block">Current Bed &amp; Patient</span>
        <div class="text-sm font-black text-slate-900" id="transfer-current-label">Bed --</div>
        <input type="hidden" id="transfer-current-bed-val">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Destination Vacant Bed *</label>
        <select id="transfer-new-bed-select" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
          <option value="">-- Choose New Bed --</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason for Transfer</label>
        <input type="text" id="transfer-reason" placeholder="e.g. Upgraded to ICU, Shifted to General Ward..." class="w-full text-xs font-medium border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 text-xs font-black shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-arrow-right-arrow-left"></i>
          <span>Complete Bed Relocation</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 5: DISCHARGE PATIENT CONFIRMATION =============== -->
<!-- ========================================================================= -->
<div id="modal-discharge-patient" class="hidden fixed inset-0 z-[140] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 relative my-8 shadow-2xl border border-slate-200">
    <button onclick="closeDischargeModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold border border-rose-100">
        <i class="fa-solid fa-person-walking-arrow-right"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-lg">Discharge Inpatient</h3>
        <p class="text-xs text-slate-500 font-medium">Free bed and conclude inpatient stay.</p>
      </div>
    </div>

    <form onsubmit="handleConfirmDischarge(event)" class="space-y-4">
      <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200">
        <div class="text-[10px] uppercase font-bold text-rose-600 block mb-0.5">Patient for Discharge</div>
        <div class="text-base font-black text-slate-900" id="discharge-patient-name">Patient Name</div>
        <div class="text-xs text-slate-600 mt-1 font-mono font-bold" id="discharge-bed-num">Bed #--</div>
        <input type="hidden" id="discharge-bed-val">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Discharge Notes / Post-Discharge Advice</label>
        <textarea id="discharge-notes" rows="2" class="w-full text-xs font-medium border border-slate-200 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500/20 outline-none" placeholder="e.g. Recovered well. Advised 5 days home rest."></textarea>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="button" onclick="closeDischargeModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl py-3 text-xs font-bold transition">Cancel</button>
        <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-3 text-xs font-black shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-check"></i>
          <span>Discharge &amp; Free Bed</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 6: ADD NEW BED ================================== -->
<!-- ========================================================================= -->
<div id="modal-add-bed" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-sm w-full p-6 relative my-8 shadow-2xl border border-slate-200">
    <button onclick="closeAddBedModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-bed-pulse"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-base">Add New Bed</h3>
        <p class="text-[11px] text-slate-500 font-medium">Configure ward floor and designation.</p>
      </div>
    </div>
    <form onsubmit="handleCreateBed(event)" class="space-y-3.5">
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Ward Category</label>
        <select id="new-bed-type" onchange="autoSuggestBedNumber()" class="w-full text-xs font-bold border rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none">
          <option value="OPD">OPD Observation Ward</option>
          <option value="ICU">ICU Critical Care Ward</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Bed Designation Number *</label>
        <input type="text" id="new-bed-number" required class="w-full text-xs font-bold border rounded-xl p-2.5 font-mono bg-slate-50 focus:bg-white outline-none">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Wing / Floor Location</label>
        <input type="text" id="new-bed-wing" value="North Wing, 2nd Floor" class="w-full text-xs font-medium border rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none">
      </div>
      <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-3 text-xs font-black shadow-md shadow-rose-600/20 transition">Add Bed to Inventory</button>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL 7: IMAGE PREVIEW LIGHTBOX ======================= -->
<!-- ========================================================================= -->
<div id="modal-image-preview" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" onclick="closeImagePreview()">
    <div class="relative max-w-4xl max-h-[90vh] bg-black rounded-3xl overflow-hidden shadow-2xl p-2 flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button onclick="closeImagePreview()" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="image-preview-element" src="" class="max-w-full max-h-[80vh] object-contain rounded-2xl" alt="Medical Scan Preview">
        <div class="text-white text-xs font-bold mt-2 px-4 py-1 truncate max-w-full text-center" id="image-preview-title">Report Preview</div>
    </div>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
  let allBeds = [];
  let allPatients = [];
  let allDoctors = [];
  let currentFilterWard = 'all';
  let displayMode = 'grid';
  let currentViewingPatientId = null;
  let currentViewingBedNum = null;
  let currentViewingDossier = null;
  let currentViewingAppointments = [];

  window.addEventListener('DOMContentLoaded', () => {
    fetchBeds();
    loadDirectoryPatients();
    loadHospitalDoctors();
  });

  // --- Display Mode Toggle ---
  function setBedDisplayMode(mode) {
    displayMode = mode;
    document.getElementById('btn-bed-view-grid').className = mode === 'grid' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold flex items-center gap-1.5 transition" : "px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 font-bold flex items-center gap-1.5 transition";
    document.getElementById('btn-bed-view-table').className = mode === 'table' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold flex items-center gap-1.5 transition" : "px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 font-bold flex items-center gap-1.5 transition";
    document.getElementById('bed-container-grid').classList.toggle('hidden', mode !== 'grid');
    document.getElementById('bed-container-table').classList.toggle('hidden', mode !== 'table');
  }

  // --- Ward Filter Toggle ---
  function setWardFilter(ward) {
    currentFilterWard = ward;
    ['all', 'opd', 'icu'].forEach(w => {
      const btn = document.getElementById(`filter-ward-${w}`);
      if (btn) {
        if (w.toLowerCase() === ward.toLowerCase()) {
          btn.className = "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold transition";
        } else {
          btn.className = "px-3 py-1.5 rounded-lg hover:text-slate-900 font-bold transition";
        }
      }
    });

    // Control visibility of ward sections in grid mode
    const opdSec = document.getElementById('section-ward-opd');
    const icuSec = document.getElementById('section-ward-icu');
    if (opdSec) opdSec.classList.toggle('hidden', ward === 'ICU');
    if (icuSec) icuSec.classList.toggle('hidden', ward === 'OPD');

    applyFilters();
  }

  // --- Fetch Beds Data ---
  async function fetchBeds() {
    const icon = document.getElementById('refresh-icon');
    if (icon) icon.classList.add('animate-spin');
    try {
      const res = await fetch('api/beds.php?action=get_all');
      const data = await res.json();
      if (data.status === 'success') {
        allBeds = data.beds || [];
        updateStatistics(allBeds);
        applyFilters();
        const d = new Date();
        document.getElementById('live-time-stamp').textContent = `Updated ${d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
      }
    } catch (e) {
      console.error(e);
    } finally {
      if (icon) setTimeout(() => icon.classList.remove('animate-spin'), 400);
    }
  }

  // --- Statistics Calculation & Progress Bar ---
  function updateStatistics(beds) {
    const total = beds.length;
    const avail = beds.filter(b => b.status === 'Available').length;
    const occupied = beds.filter(b => b.status === 'Occupied').length;

    const opdBeds = beds.filter(b => b.type === 'OPD');
    const opdAvail = opdBeds.filter(b => b.status === 'Available').length;
    const opdOcc = opdBeds.filter(b => b.status === 'Occupied').length;

    const icuBeds = beds.filter(b => b.type === 'ICU');
    const icuAvail = icuBeds.filter(b => b.status === 'Available').length;
    const icuOcc = icuBeds.filter(b => b.status === 'Occupied').length;

    document.getElementById('stat-total-beds').textContent = total;
    document.getElementById('stat-total-avail').textContent = avail;
    document.getElementById('stat-total-occupied').textContent = occupied;

    document.getElementById('stat-opd-total').textContent = opdBeds.length;
    document.getElementById('stat-opd-avail').textContent = opdAvail;
    document.getElementById('badge-opd-count').textContent = `${opdBeds.length} Beds`;
    document.getElementById('opd-avail-count-sub').textContent = `${opdAvail} Vacant`;
    document.getElementById('opd-occ-count-sub').textContent = `${opdOcc} Occupied`;
    const opdPct = opdBeds.length ? Math.round((opdOcc / opdBeds.length) * 100) : 0;
    document.getElementById('stat-opd-occupancy-desc').textContent = `${opdPct}% Occupied`;

    document.getElementById('stat-icu-total').textContent = icuBeds.length;
    document.getElementById('stat-icu-avail').textContent = icuAvail;
    document.getElementById('badge-icu-count').textContent = `${icuBeds.length} Beds`;
    document.getElementById('icu-avail-count-sub').textContent = `${icuAvail} Vacant`;
    document.getElementById('icu-occ-count-sub').textContent = `${icuOcc} Occupied`;
    const icuPct = icuBeds.length ? Math.round((icuOcc / icuBeds.length) * 100) : 0;
    document.getElementById('stat-icu-occupancy-desc').textContent = `${icuPct}% Occupied`;

    // Overall Progress Bar
    const overallPct = total ? Math.round((occupied / total) * 100) : 0;
    const bar = document.getElementById('occupancy-progress-bar');
    const label = document.getElementById('occupancy-rate-label');
    if (bar) bar.style.width = `${overallPct}%`;
    if (label) label.textContent = `${overallPct}% (${occupied} / ${total} Beds In Use)`;
  }

  // --- Filtering & Search ---
  function applyFilters() {
    const query = document.getElementById('bed-search-input').value.trim().toLowerCase();
    const statusFilter = document.getElementById('bed-status-filter').value;
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);

    const filtered = allBeds.filter(b => {
      // Ward filter
      if (currentFilterWard !== 'all' && b.type.toLowerCase() !== currentFilterWard.toLowerCase()) {
        return false;
      }
      // Status filter
      if (statusFilter !== 'all' && b.status !== statusFilter) {
        return false;
      }
      // Search query
      if (query) {
        const pName = ((b.name || '') + ' ' + (b.surname || '')).toLowerCase();
        const mrn = (b.patient_id || '').toLowerCase();
        const bedNum = (b.bed_number || '').toLowerCase();
        const doc = (b.doctor_name || '').toLowerCase();
        const wing = (b.wing || '').toLowerCase();
        const diag = (b.diagnoses_list || '').toLowerCase();
        const symp = (b.symptoms || '').toLowerCase();

        return pName.includes(query) || mrn.includes(query) || bedNum.includes(query) || doc.includes(query) || wing.includes(query) || diag.includes(query) || symp.includes(query);
      }
      return true;
    });

    renderGrid(filtered);
    renderTable(filtered);
  }

  function clearBedSearch() {
    document.getElementById('bed-search-input').value = '';
    applyFilters();
  }

  // --- Render Floor Layout Grid ---
  function renderGrid(beds) {
    const opdBeds = beds.filter(b => b.type === 'OPD');
    const icuBeds = beds.filter(b => b.type === 'ICU');

    const opdMatrix = document.getElementById('opd-bed-matrix');
    const icuMatrix = document.getElementById('icu-bed-matrix');

    opdMatrix.innerHTML = '';
    icuMatrix.innerHTML = '';

    if (opdBeds.length === 0) {
      opdMatrix.innerHTML = `<div class="col-span-full py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">No OPD beds matching current criteria.</div>`;
    } else {
      opdBeds.forEach(bed => opdMatrix.appendChild(createBedCard(bed)));
    }

    if (icuBeds.length === 0) {
      icuMatrix.innerHTML = `<div class="col-span-full py-8 text-center text-xs text-rose-400 bg-rose-50/50 rounded-2xl border border-dashed border-rose-200">No ICU beds matching current criteria.</div>`;
    } else {
      icuBeds.forEach(bed => icuMatrix.appendChild(createBedCard(bed)));
    }
  }

  // --- Create Beautiful Bed Card Component ---
  function createBedCard(bed) {
    const isOcc = bed.status === 'Occupied';
    const isIcu = bed.type === 'ICU';
    const card = document.createElement('div');
    
    // Outer card styling
    let borderCls = isOcc ? (isIcu ? 'border-rose-300 bg-rose-50/40 hover:border-rose-400 shadow-sm' : 'border-blue-200 bg-blue-50/30 hover:border-blue-300 shadow-sm') : 'border-slate-200 bg-white hover:border-emerald-300 hover:shadow-md';
    card.className = `rounded-3xl p-5 border transition flex flex-col justify-between relative group ${borderCls}`;

    // Patient Full Name
    const fullName = isOcc ? `${bed.name || ''} ${bed.surname || ''}`.trim() : 'Vacant Bed';
    const initials = isOcc ? ((bed.name ? bed.name.charAt(0) : '') + (bed.surname ? bed.surname.charAt(0) : 'P')).toUpperCase() : '';

    // Demographics formatting
    let age = bed.age || '', gender = bed.gender || '', blood = bed.blood_group || '';
    if ((!age || age === '--') && bed.demographics) {
      const parts = bed.demographics.split(',').map(s => s.trim());
      parts.forEach(p => {
        if (p.endsWith('Y') || p.endsWith('y') || !isNaN(p)) age = p;
        if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
        if (p.includes('+') || p.includes('-')) blood = p;
      });
    }

    const docDisplay = bed.doctor_name ? `Dr. ${bed.doctor_name}` : 'Attending Not Assigned';
    const deptDisplay = bed.dept_name || (isIcu ? 'Intensive Care Unit' : 'General Ward');
    const diagDisplay = bed.diagnoses_list || bed.symptoms || 'Inpatient Observation';

    card.innerHTML = `
      <div>
        <!-- Top Bed Header -->
        <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b ${isOcc ? (isIcu ? 'border-rose-200/80' : 'border-blue-200/80') : 'border-slate-100'}">
          <div class="flex items-center gap-2">
            <span class="font-mono font-black text-sm px-2.5 py-1 rounded-xl ${isIcu ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-800 border border-slate-200'}">
              ${escapeHtml(bed.bed_number)}
            </span>
            <span class="text-[10px] text-slate-400 font-bold truncate max-w-[110px]" title="${escapeHtml(bed.wing || '')}">${escapeHtml(bed.wing || 'Floor')}</span>
          </div>

          <!-- Status Indicator Pill -->
          ${isOcc ? `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider ${isIcu ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white'} shadow-2xs">
              <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
              <span>Occupied</span>
            </span>
          ` : `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              <span>Vacant</span>
            </span>
          `}
        </div>

        <!-- Body Content -->
        ${isOcc ? `
          <!-- Patient Profile Snapshot -->
          <div class="space-y-3">
            <div class="flex items-start gap-3">
              <div class="w-11 h-11 rounded-2xl ${isIcu ? 'bg-rose-200/80 text-rose-800' : 'bg-blue-200/80 text-blue-800'} flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                ${initials || 'PT'}
              </div>
              <div class="min-w-0 flex-1">
                <button onclick="openPatientDossier('${bed.patient_id}', '${bed.bed_number}')" class="text-sm font-black text-slate-900 hover:text-indigo-600 transition truncate block text-left w-full" title="View Full Report">
                  ${escapeHtml(fullName)}
                </button>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-[10px] font-mono font-bold text-slate-500">${escapeHtml(bed.patient_id)}</span>
                  ${blood ? `<span class="text-[9px] font-black text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded">🩸 ${escapeHtml(blood)}</span>` : ''}
                </div>
              </div>
            </div>

            <!-- Demographics & Doctor -->
            <div class="p-2.5 rounded-2xl bg-white/80 border ${isIcu ? 'border-rose-100' : 'border-blue-100'} text-xs space-y-1.5">
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-bold">Age / Gender:</span>
                <span class="font-extrabold text-slate-800">${age || '--'} • ${gender || '--'}</span>
              </div>
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-bold">Doctor:</span>
                <span class="font-bold text-indigo-700 truncate max-w-[130px]" title="${escapeHtml(docDisplay)}">${escapeHtml(docDisplay)}</span>
              </div>
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-bold">Admitted:</span>
                <span class="font-bold text-slate-600 font-mono text-[10px]">${bed.admit_date || (bed.admitted_at ? bed.admitted_at.substring(0, 10) : 'Active')}</span>
              </div>
            </div>

            <!-- Diagnosis / Reason preview -->
            <div class="text-[11px] bg-slate-50 p-2 rounded-xl border border-slate-100 text-slate-700 line-clamp-1">
              <strong class="text-slate-900 font-bold">Diagnosis:</strong> ${escapeHtml(diagDisplay)}
            </div>

            <!-- Quick counts: Meds & Files -->
            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
              <span class="bg-white px-2 py-0.5 rounded-lg border border-slate-200">💊 ${bed.prescriptions_count || 0} Meds</span>
              <span class="bg-white px-2 py-0.5 rounded-lg border border-slate-200">📄 ${bed.files_count || 0} Reports</span>
              ${bed.phone ? `<a href="tel:${bed.phone.replace(/[^0-9+]/g, '')}" class="ml-auto text-blue-600 hover:underline" title="Call Patient"><i class="fa-solid fa-phone"></i></a>` : ''}
            </div>
          </div>
        ` : `
          <!-- Vacant Empty State Card -->
          <div class="py-5 text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mx-auto border border-emerald-100 shadow-2xs">
              <i class="fa-solid fa-bed"></i>
            </div>
            <div class="text-xs font-black text-slate-800">Sanitized &amp; Ready</div>
            <p class="text-[11px] text-slate-400">Prepared for new inpatient admission</p>
          </div>
        `}
      </div>

      <!-- Footer Action Buttons -->
      <div class="mt-4 pt-3 border-t ${isOcc ? (isIcu ? 'border-rose-200/80' : 'border-blue-200/80') : 'border-slate-100'} flex items-center justify-between gap-2">
        ${isOcc ? `
          <!-- Prominent View Full Info & Report Button -->
          <button onclick="openPatientDossier('${bed.patient_id}', '${bed.bed_number}')" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-black py-2 px-3 rounded-xl shadow-sm transition flex items-center justify-center gap-1.5" title="View Professional Medical Report & Dossier">
            <i class="fa-solid fa-file-waveform text-xs"></i>
            <span>View Full Info</span>
          </button>
          
          <button onclick="openTransferModal('${bed.bed_number}', '${escapeHtml(fullName)}')" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition shadow-2xs" title="Transfer to Another Bed">
            <i class="fa-solid fa-arrows-turn-to-dots text-indigo-600"></i>
          </button>

          <button onclick="promptDischarge('${bed.bed_number}', '${escapeHtml(fullName)}')" class="p-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shadow-2xs" title="Discharge Patient">
            <i class="fa-solid fa-person-walking-arrow-right"></i>
          </button>
        ` : `
          <!-- Vacant Action: Quick Admit Here -->
          <button onclick="openQuickAdmitModal('${bed.bed_number}')" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black py-2 px-3 rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Admit Patient</span>
          </button>

          <button onclick="deleteBed('${bed.bed_number}')" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Bed from Inventory">
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        `}
      </div>
    `;

    return card;
  }

  // --- Render Comprehensive Data Table ---
  function renderTable(beds) {
    const tbody = document.getElementById('bed-table-tbody');
    tbody.innerHTML = '';

    if (beds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-xs text-slate-400">No beds found matching your filters.</td></tr>`;
      return;
    }

    beds.forEach(b => {
      const isOcc = b.status === 'Occupied';
      const fullName = isOcc ? `${b.name || ''} ${b.surname || ''}`.trim() : '-';
      const doc = b.doctor_name ? `Dr. ${b.doctor_name}` : (isOcc ? 'General Duty' : '-');

      tbody.innerHTML += `
        <tr class="hover:bg-slate-50/70 transition">
          <td class="p-4 font-mono font-black text-slate-900">${escapeHtml(b.bed_number)}</td>
          <td class="p-4">
            <span class="inline-flex items-center gap-1 font-bold ${b.type === 'ICU' ? 'text-rose-700' : 'text-blue-700'}">
              <i class="fa-solid ${b.type === 'ICU' ? 'fa-heart-pulse' : 'fa-bed'} text-[10px]"></i>
              ${b.type} Ward
            </span>
            <div class="text-[10px] text-slate-400 font-medium">${escapeHtml(b.wing || 'Floor')}</div>
          </td>
          <td class="p-4">
            ${isOcc ? `
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> Occupied
              </span>
            ` : `
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
              </span>
            `}
          </td>
          <td class="p-4">
            ${isOcc ? `
              <button onclick="openPatientDossier('${b.patient_id}', '${b.bed_number}')" class="font-extrabold text-slate-900 hover:text-indigo-600 transition text-left block">
                ${escapeHtml(fullName)}
              </button>
              <div class="text-[10px] font-mono text-slate-400">${escapeHtml(b.patient_id)}</div>
            ` : `<span class="text-slate-400 italic">Unassigned</span>`}
          </td>
          <td class="p-4">
            ${isOcc ? `
              <span class="text-xs font-bold text-slate-700">${b.age ? b.age + ' Y' : '--'} • ${b.gender || '--'}</span>
              ${b.blood_group ? `<span class="block text-[10px] font-bold text-rose-600 font-mono">🩸 ${b.blood_group}</span>` : ''}
            ` : '-'}
          </td>
          <td class="p-4">
            <span class="text-xs font-bold text-indigo-700">${escapeHtml(doc)}</span>
            <div class="text-[10px] text-slate-400">${escapeHtml(b.dept_name || '')}</div>
          </td>
          <td class="p-4">
            ${isOcc ? `
              <span class="text-[11px] font-mono text-slate-600 block">${b.admit_date || (b.admitted_at ? b.admitted_at.substring(0, 10) : 'Today')}</span>
              <span class="text-[10px] text-slate-400 line-clamp-1" title="${escapeHtml(b.diagnoses_list || b.symptoms || '')}">${escapeHtml(b.diagnoses_list || b.symptoms || 'Under observation')}</span>
            ` : '-'}
          </td>
          <td class="p-4 text-right">
            ${isOcc ? `
              <div class="flex items-center justify-end gap-1.5">
                <button onclick="openPatientDossier('${b.patient_id}', '${b.bed_number}')" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold px-3 py-1.5 rounded-xl border border-indigo-200 transition shadow-2xs" title="View Full Report">
                  <i class="fa-solid fa-file-waveform mr-1"></i> Full Info
                </button>
                <button onclick="openTransferModal('${b.bed_number}', '${escapeHtml(fullName)}')" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-xs transition" title="Transfer Bed">
                  <i class="fa-solid fa-arrows-turn-to-dots text-indigo-600"></i>
                </button>
                <button onclick="promptDischarge('${b.bed_number}', '${escapeHtml(fullName)}')" class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs transition" title="Discharge">
                  <i class="fa-solid fa-person-walking-arrow-right"></i>
                </button>
              </div>
            ` : `
              <div class="flex items-center justify-end gap-1.5">
                <button onclick="openQuickAdmitModal('${b.bed_number}')" class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold px-3 py-1.5 rounded-xl border border-emerald-200 transition">
                  <i class="fa-solid fa-user-plus mr-1"></i> Admit
                </button>
                <button onclick="deleteBed('${b.bed_number}')" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete Bed">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            `}
          </td>
        </tr>
      `;
    });
  }

  // =========================================================================
  // ================= POPUP: PATIENT PROFILE & MEDICAL REPORT ===============
  // =========================================================================
  async function openPatientDossier(patient_id, bed_number = null) {
    if (!patient_id) return;
    currentViewingPatientId = patient_id;
    currentViewingBedNum = bed_number;

    try {
      const res = await fetch(`api/history.php?action=get_dossier&patient_id=${encodeURIComponent(patient_id)}`);
      const data = await res.json();

      if (data.status === 'success') {
        const p = data.dossier;
        currentViewingDossier = p;
        currentViewingAppointments = p.appointments || [];

        // If bed_number wasn't passed directly, find from patient or beds list
        if (!currentViewingBedNum) {
          const bedObj = allBeds.find(b => b.patient_id === patient_id);
          currentViewingBedNum = bedObj ? bedObj.bed_number : p.bed_number;
        }

        // Setup Header Info
        const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient Record';
        document.getElementById('pop-patient-name').textContent = fullName;
        document.getElementById('pop-header-mrn').textContent = p.id;
        document.getElementById('pop-avatar').textContent = ((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : 'P')).toUpperCase();

        document.getElementById('pop-father').textContent = p.father_name || 'N/A';
        const phoneEl = document.getElementById('pop-phone');
        phoneEl.textContent = p.phone || 'Not provided';
        phoneEl.href = p.phone ? `tel:${p.phone.replace(/[^0-9+]/g, '')}` : '#';

        document.getElementById('pop-emergency').textContent = p.emergency_contact_name ? `${p.emergency_contact_name} (${p.emergency_contact_phone || 'No phone'})` : 'None';

        // Parse Demographics
        let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
        if ((!age || age === '--') && p.demographics) {
          const parts = p.demographics.split(',').map(s => s.trim());
          parts.forEach(part => {
            if (part.endsWith('Y') || part.endsWith('y') || !isNaN(part)) age = part;
            if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
            if (part.includes('+') || part.includes('-')) blood = part;
          });
        }
        document.getElementById('pop-age').textContent = age;
        document.getElementById('pop-gender').textContent = gender;
        document.getElementById('pop-blood').textContent = blood;

        // Bed Badge
        if (currentViewingBedNum) {
          document.getElementById('pop-bed-text').textContent = `Hospital Bed #${currentViewingBedNum}`;
          document.getElementById('pop-bed-badge').classList.remove('hidden');
        } else {
          document.getElementById('pop-bed-badge').classList.add('hidden');
        }

        // External Link to Standalone Profile
        const fullLink = document.getElementById('pop-btn-open-full');
        fullLink.href = `patient_profile.php?id=${encodeURIComponent(p.id)}`;

        // Counters
        document.getElementById('pop-stat-visits').textContent = currentViewingAppointments.length;
        let totalMeds = 0;
        currentViewingAppointments.forEach(a => {
          if (a.medicines && a.medicines.length) totalMeds += a.medicines.length;
        });
        document.getElementById('pop-stat-meds').textContent = totalMeds;
        document.getElementById('pop-stat-files').textContent = (p.files && p.files.length) ? p.files.length : 0;

        // Render Consultations Workstation
        renderPopupConsultations(currentViewingAppointments, p);

        // Render Files Grid
        renderPopupFiles(p.files || []);

        // Render Inpatient Stay Tab Details
        renderPopupStayDetails(p);

        // Switch to Consultations Tab by default
        switchPopupTab('consultations');

        document.getElementById('modal-patient-report').classList.remove('hidden');
      } else {
        showToast('Error', data.message || 'Failed to load patient report.', 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Error', 'Unable to retrieve patient report details.', 'error');
    }
  }

  function closePatientReportModal() {
    document.getElementById('modal-patient-report').classList.add('hidden');
  }

  function switchPopupTab(tabId) {
    document.querySelectorAll('.poptab').forEach(el => {
      el.classList.remove('text-indigo-700', 'bg-indigo-50');
      el.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-50');
    });
    document.querySelectorAll('.poptab-view').forEach(el => el.classList.add('hidden'));

    const btn = document.getElementById(`poptab-btn-${tabId}`);
    if (btn) {
      btn.classList.add('text-indigo-700', 'bg-indigo-50');
      btn.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-50');
    }
    const view = document.getElementById(`poptab-content-${tabId}`);
    if (view) view.classList.remove('hidden');
  }

  // --- Render Consultations inside Popup ---
  function renderPopupConsultations(appointments, patient) {
    const list = document.getElementById('pop-consultations-list');
    if (!appointments || appointments.length === 0) {
      list.innerHTML = `
        <div class="text-center py-12 px-4 bg-white rounded-3xl border border-dashed border-slate-200">
          <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl mx-auto mb-2">
            <i class="fa-solid fa-clipboard-check"></i>
          </div>
          <h4 class="text-sm font-black text-slate-800">No Clinical Consultations Recorded Yet</h4>
          <p class="text-xs text-slate-400 mt-0.5">Patient has no registered doctor visits in history.</p>
        </div>
      `;
      return;
    }

    list.innerHTML = appointments.map((appt, idx) => {
      const isFirst = idx === 0;
      const diagnoses = appt.diagnoses || [];
      const medicines = appt.medicines || [];
      const timeline = appt.timeline || [];
      const testsOrdered = appt.tests_ordered ? appt.tests_ordered.split(',').map(s => s.trim()).filter(Boolean) : [];

      return `
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
          <!-- Accordion Header -->
          <div onclick="togglePopupApptDetails(${idx})" class="w-full p-4 sm:p-5 bg-white hover:bg-slate-50/70 transition cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
              <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex flex-col items-center justify-center border border-indigo-100 shrink-0 font-mono">
                <span class="text-sm font-black leading-none">${appt.date ? appt.date.split('-')[2] : '--'}</span>
                <span class="text-[9px] font-extrabold uppercase mt-0.5">${appt.date ? getMonthShort(appt.date) : 'MTH'}</span>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                  <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">${appt.appointment_code || ('APP-' + String(appt.id).padStart(4, '0'))}</span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">${appt.type || 'Consultation'}</span>
                  ${appt.bed_number ? `<span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200"><i class="fa-solid fa-bed mr-1"></i>Bed #${appt.bed_number}</span>` : ''}
                </div>
                <h4 class="text-sm sm:text-base font-black text-slate-900 truncate">
                  Dr. ${appt.doctor_name || 'Medical Officer'} <span class="text-xs font-semibold text-slate-500">(${appt.dept || 'General'})</span>
                </h4>
                ${appt.symptoms ? `<p class="text-xs text-slate-500 mt-0.5 truncate"><strong>Complaints:</strong> ${escapeHtml(appt.symptoms)}</p>` : ''}
              </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
              <button type="button" onclick="event.stopPropagation(); printSpecificAppointmentPrescription(${idx})" class="px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-indigo-50 text-indigo-700 text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                <i class="fa-solid fa-print"></i>
                <span>Print Rx</span>
              </button>
              <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 transition">
                <i id="pop-appt-icon-${idx}" class="fa-solid fa-chevron-down transition-transform duration-300 ${isFirst ? 'rotate-180' : ''}"></i>
              </div>
            </div>
          </div>

          <!-- Expanded Body -->
          <div id="pop-appt-content-${idx}" class="${isFirst ? '' : 'hidden'} border-t border-slate-100 bg-slate-50/50 p-4 sm:p-5 space-y-4">
            
            <!-- Diagnoses Section -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-stethoscope text-rose-600"></i> Confirmed Diagnoses
              </span>
              <div class="flex flex-wrap gap-1.5">
                ${diagnoses.length > 0 ? diagnoses.map(d => `
                  <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-800 text-xs font-bold px-2.5 py-1 rounded-xl border border-rose-200">
                    <i class="fa-solid fa-circle-check text-rose-500 text-[10px]"></i>
                    <span>${escapeHtml(d)}</span>
                  </span>
                `).join('') : '<span class="text-xs text-slate-400 italic">No specific diagnoses recorded.</span>'}
              </div>
            </div>

            <!-- Medicines (Rx) Workstation -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
              <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                  <i class="fa-solid fa-pills text-emerald-600"></i> Prescribed Medicines (Rx)
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">${medicines.length} Drugs</span>
              </div>

              <div class="space-y-2">
                ${medicines.length > 0 ? medicines.map((m, mIdx) => {
                  const freq = formatFrequency(m.freq);
                  return `
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs relative overflow-hidden">
                      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                        <div class="flex items-center gap-2">
                          <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">${mIdx + 1}</span>
                          <strong class="text-slate-900 text-sm">${escapeHtml(m.name)}</strong>
                        </div>
                        ${m.duration ? `<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">${escapeHtml(m.duration)}</span>` : ''}
                      </div>
                      <div class="flex flex-wrap items-center gap-2 pl-7 mt-1 text-slate-600">
                        <span class="bg-white px-2 py-0.5 rounded border border-slate-200 font-bold">Dose: ${escapeHtml(m.dose || '1 Unit')}</span>
                        <span class="bg-white px-2 py-0.5 rounded border border-slate-200 font-bold flex items-center gap-1">
                          <i class="${freq.icon}"></i> ${escapeHtml(freq.full)}
                        </span>
                      </div>
                      ${m.note ? `<div class="mt-1.5 ml-7 text-[11px] text-amber-900 bg-amber-50 p-1.5 rounded border border-amber-200 font-medium"><strong>Instructions:</strong> ${escapeHtml(m.note)}</div>` : ''}
                    </div>
                  `;
                }).join('') : '<div class="text-xs text-slate-400 italic py-2 text-center">No prescriptions on file for this visit.</div>'}
              </div>
            </div>

            <!-- Ordered Diagnostic Tests -->
            ${testsOrdered.length > 0 ? `
              <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200 shadow-2xs">
                <span class="text-xs font-black uppercase tracking-wider text-amber-800 block mb-1.5 flex items-center gap-1.5">
                  <i class="fa-solid fa-flask-vial text-amber-600"></i> Diagnostic Investigations &amp; Lab Tests Ordered
                </span>
                <div class="flex flex-wrap gap-1.5">
                  ${testsOrdered.map(t => `<span class="bg-white text-amber-900 border border-amber-300 font-bold text-xs px-2.5 py-1 rounded-xl shadow-2xs">${escapeHtml(t)}</span>`).join('')}
                </div>
              </div>
            ` : ''}

            <!-- Doctor's Advice & Timeline -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Clinical Advice -->
              <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1 flex items-center gap-1.5">
                  <i class="fa-solid fa-notes-medical text-indigo-600"></i> Doctor's Clinical Notes
                </span>
                <div class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 font-medium leading-relaxed">
                  ${escapeHtml(appt.doctor_notes) || '<span class="italic text-slate-400">Standard home care and rest.</span>'}
                </div>
              </div>

              <!-- Activity Timeline -->
              <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-1.5">
                  <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Activity Timeline
                </span>
                <div class="space-y-2 text-xs">
                  ${timeline.length > 0 ? timeline.map(t => `
                    <div class="flex items-start gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                      <div>
                        <span class="text-[10px] font-mono font-bold text-slate-400">${escapeHtml(t.time)}</span>
                        <div class="text-slate-700 font-medium">${escapeHtml(t.event)}</div>
                      </div>
                    </div>
                  `).join('') : '<span class="text-xs text-slate-400 italic">No timeline entries</span>'}
                </div>
              </div>
            </div>

          </div>
        </div>
      `;
    }).join('');
  }

  function togglePopupApptDetails(idx) {
    const content = document.getElementById(`pop-appt-content-${idx}`);
    const icon = document.getElementById(`pop-appt-icon-${idx}`);
    if (content) {
      const isHidden = content.classList.contains('hidden');
      content.classList.toggle('hidden', !isHidden);
      if (icon) icon.classList.toggle('rotate-180', isHidden);
    }
  }

  // --- Render Medical Files inside Popup ---
  function renderPopupFiles(files) {
    const grid = document.getElementById('pop-files-grid');
    if (!files || files.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-12 text-center text-xs text-slate-400 bg-white rounded-3xl border border-dashed border-slate-200">
          <i class="fa-solid fa-images text-2xl text-slate-300 block mb-2"></i>
          No medical scans, X-rays or laboratory files uploaded for this patient yet.
        </div>
      `;
      return;
    }

    grid.innerHTML = files.map(f => {
      const fileName = f.file_name || f.file_path || '';
      const ext = (fileName.includes('.') ? fileName.split('?')[0].split('.').pop() : '').toLowerCase();
      const isImg = (f.mime_type && f.mime_type.startsWith('image/')) || ['jpg','jpeg','png','gif','webp','svg'].includes(ext);

      return `
        <div class="bg-white rounded-2xl border border-slate-200 p-3 hover:shadow-md transition group">
          <div class="aspect-square bg-slate-50 rounded-xl overflow-hidden mb-2.5 flex items-center justify-center cursor-pointer relative" onclick="${isImg ? `openImagePreview('${f.file_path}', '${escapeHtml(f.title)}')` : `window.open('${f.file_path}', '_blank')`}">
            ${isImg ? `
              <img src="${f.file_path}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="Medical Scan">
              <span class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-lg transition"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
            ` : `
              <i class="fa-solid fa-file-pdf text-3xl text-rose-500"></i>
            `}
          </div>
          <h5 class="text-xs font-bold text-slate-900 truncate" title="${escapeHtml(f.title)}">${escapeHtml(f.title)}</h5>
          <p class="text-[10px] text-slate-400 mt-0.5 uppercase">${f.file_date || 'Scan'}</p>
        </div>
      `;
    }).join('');
  }

  // --- Render Inpatient Stay Tab inside Popup ---
  function renderPopupStayDetails(patient) {
    const bedObj = allBeds.find(b => b.patient_id === patient.id);
    const bedNum = currentViewingBedNum || (bedObj ? bedObj.bed_number : 'None');
    const wardType = bedObj ? `${bedObj.type} Observation / Care` : 'Inpatient Ward';
    const wing = bedObj ? (bedObj.wing || 'Floor') : 'General Ward';
    const admitTime = bedObj && bedObj.admitted_at ? bedObj.admitted_at : (patient.date || 'Today');
    const doctor = bedObj && bedObj.doctor_name ? `Dr. ${bedObj.doctor_name}` : (patient.doctor || 'Doctor Not Assigned');

    document.getElementById('pop-stay-bed-num').textContent = bedNum;
    document.getElementById('pop-stay-ward').textContent = wardType;
    document.getElementById('pop-stay-wing').textContent = wing;
    document.getElementById('pop-stay-admit-time').textContent = admitTime;
    document.getElementById('pop-stay-doctor').textContent = doctor;

    const transferBtn = document.getElementById('pop-stay-transfer-btn');
    const dischargeBtn = document.getElementById('pop-stay-discharge-btn');

    if (bedNum && bedNum !== 'None') {
      if (transferBtn) transferBtn.classList.remove('hidden');
      if (dischargeBtn) dischargeBtn.classList.remove('hidden');
    } else {
      if (transferBtn) transferBtn.classList.add('hidden');
      if (dischargeBtn) dischargeBtn.classList.add('hidden');
    }
  }

  function openTransferModalFromDossier() {
    const fullName = document.getElementById('pop-patient-name').textContent;
    openTransferModal(currentViewingBedNum, fullName);
  }

  function openDischargeModalFromDossier() {
    const fullName = document.getElementById('pop-patient-name').textContent;
    promptDischarge(currentViewingBedNum, fullName);
  }

  // =========================================================================
  // ================= PRINTABLE PRESCRIPTION LOGIC ==========================
  // =========================================================================
  function printCurrentPatientSlip() {
    if (!currentViewingAppointments || currentViewingAppointments.length === 0) {
      showToast('Notice', 'No consultations available to print for this patient.', 'info');
      return;
    }
    printSpecificAppointmentPrescription(0);
  }

  function printSpecificAppointmentPrescription(apptIdx) {
    const p = currentViewingDossier;
    if (!p) return;
    const appt = currentViewingAppointments[apptIdx] || currentViewingAppointments[0] || {};

    const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient Record';
    document.getElementById('print-date').textContent = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    document.getElementById('print-patient-name').textContent = fullName;
    document.getElementById('print-patient-mrn').textContent = `MRN: ${p.id}`;

    let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
    if ((!age || age === '--') && p.demographics) {
      const parts = p.demographics.split(',').map(s => s.trim());
      parts.forEach(part => {
        if (part.endsWith('Y') || part.endsWith('y') || !isNaN(part)) age = part;
        if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
        if (part.includes('+') || part.includes('-')) blood = part;
      });
    }
    document.getElementById('print-demographics').textContent = `${age} • ${gender} • Blood: ${blood}`;
    document.getElementById('print-father').textContent = p.father_name || '--';

    const docName = appt.doctor_name ? `Dr. ${appt.doctor_name}` : (p.doctor ? `Dr. ${p.doctor}` : 'Dr. Medical Officer');
    document.getElementById('print-doctor-name').textContent = docName;
    document.getElementById('print-sign-doctor').textContent = docName;
    document.getElementById('print-doctor-dept').textContent = appt.dept || p.dept || 'General Medicine';

    document.getElementById('print-appt-code').textContent = appt.appointment_code || ('APP-' + String(appt.id || '0').padStart(4, '0'));
    document.getElementById('print-appt-type').textContent = appt.type || 'Consultation';

    // Bed section
    const bedSec = document.getElementById('print-bed-section');
    const bedNum = appt.bed_number || currentViewingBedNum;
    if (bedNum) {
      bedSec.classList.remove('hidden');
      document.getElementById('print-bed-text').textContent = `Admitted to Hospital Bed #${bedNum}`;
    } else {
      bedSec.classList.add('hidden');
    }

    // Symptoms
    document.getElementById('print-symptoms').textContent = appt.symptoms || p.symptoms || 'None recorded';

    // Diagnoses
    const diagEl = document.getElementById('print-diagnoses-list');
    const diagnoses = appt.diagnoses || p.latest_diagnoses || [];
    if (diagnoses.length > 0) {
      diagEl.innerHTML = diagnoses.map(d => `<span class="bg-rose-50 text-rose-800 border border-rose-200 text-xs px-2 py-0.5 rounded font-bold">${escapeHtml(d)}</span>`).join('');
    } else {
      diagEl.innerHTML = `<span class="text-xs text-slate-400 italic">None recorded</span>`;
    }

    // Medicines Table
    const medsTbody = document.getElementById('print-medicines-tbody');
    const medicines = appt.medicines || [];
    if (medicines.length > 0) {
      medsTbody.innerHTML = medicines.map((m, idx) => {
        const freq = formatFrequency(m.freq);
        return `
          <tr>
            <td class="p-3 font-bold text-slate-400">${idx + 1}</td>
            <td class="p-3 font-bold text-slate-900">${escapeHtml(m.name)}</td>
            <td class="p-3">${escapeHtml(m.dose || '1 Unit')}</td>
            <td class="p-3">${escapeHtml(freq.full)}</td>
            <td class="p-3">${escapeHtml(m.duration || '--')}</td>
            <td class="p-3 text-slate-500 italic">${escapeHtml(m.note || '--')}</td>
          </tr>
        `;
      }).join('');
    } else {
      medsTbody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-slate-400 italic">No medications prescribed.</td></tr>`;
    }

    // Tests Ordered
    const testsSec = document.getElementById('print-tests-section');
    const testsList = document.getElementById('print-tests-list');
    const tests = appt.tests_ordered ? appt.tests_ordered.split(',').map(s => s.trim()).filter(Boolean) : [];
    if (tests.length > 0) {
      testsSec.classList.remove('hidden');
      testsList.innerHTML = tests.map(t => `<span class="bg-amber-100 text-amber-900 border border-amber-300 font-bold px-2 py-0.5 rounded text-xs">${escapeHtml(t)}</span>`).join('');
    } else {
      testsSec.classList.add('hidden');
    }

    // Advice
    document.getElementById('print-advice-text').textContent = appt.doctor_notes || 'Standard hospital bed rest and follow prescribed instructions.';

    // Open print modal
    document.getElementById('modal-print-prescription').classList.remove('hidden');
  }

  function closePrintModal() {
    document.getElementById('modal-print-prescription').classList.add('hidden');
  }

  // =========================================================================
  // ================= IMAGE PREVIEW LIGHTBOX ================================
  // =========================================================================
  function openImagePreview(src, title) {
    document.getElementById('image-preview-element').src = src;
    document.getElementById('image-preview-title').textContent = title || 'Medical Scan';
    document.getElementById('modal-image-preview').classList.remove('hidden');
  }

  function closeImagePreview() {
    document.getElementById('modal-image-preview').classList.add('hidden');
  }

  // =========================================================================
  // ================= QUICK ADMIT PATIENT ===================================
  // =========================================================================
  async function loadDirectoryPatients() {
    try {
      const res = await fetch('api/patients.php?action=get_all');
      const data = await res.json();
      if (data.status === 'success') {
        allPatients = data.patients || [];
      }
    } catch(e) {}
  }

  async function loadHospitalDoctors() {
    try {
      const res = await fetch('api/doctors.php?action=get_hospital_doctors');
      const data = await res.json();
      if (data.status === 'success') {
        allDoctors = data.doctors || [];
      }
    } catch(e) {}
  }

  function openQuickAdmitModal(preSelectedBed = '') {
    // Populate Patients dropdown
    const patSelect = document.getElementById('admit-patient-select');
    patSelect.innerHTML = '<option value="">-- Choose Patient --</option>';
    allPatients.forEach(p => {
      const name = `${p.name || ''} ${p.surname || ''} (${p.id})`;
      patSelect.innerHTML += `<option value="${p.id}">${escapeHtml(name)}</option>`;
    });

    // Populate Vacant Beds dropdown
    const bedSelect = document.getElementById('admit-bed-select');
    bedSelect.innerHTML = '<option value="">-- Choose Vacant Bed --</option>';
    const vacantBeds = allBeds.filter(b => b.status === 'Available');
    vacantBeds.forEach(b => {
      const opt = document.createElement('option');
      opt.value = b.bed_number;
      opt.textContent = `${b.bed_number} (${b.type} Ward - ${b.wing || 'Floor'})`;
      if (preSelectedBed && b.bed_number === preSelectedBed) opt.selected = true;
      bedSelect.appendChild(opt);
    });

    // Populate Doctors dropdown
    const docSelect = document.getElementById('admit-doctor-select');
    docSelect.innerHTML = '<option value="">-- Unassigned / General Duty --</option>';
    allDoctors.forEach(d => {
      docSelect.innerHTML += `<option value="${d.id}">Dr. ${escapeHtml(d.name)}</option>`;
    });

    document.getElementById('admit-reason').value = '';
    document.getElementById('modal-admit-patient').classList.remove('hidden');
  }

  function closeQuickAdmitModal() {
    document.getElementById('modal-admit-patient').classList.add('hidden');
  }

  async function handleAdmitPatient(e) {
    e.preventDefault();
    const bed = document.getElementById('admit-bed-select').value;
    const pat = document.getElementById('admit-patient-select').value;
    const doc = document.getElementById('admit-doctor-select').value;
    const reason = document.getElementById('admit-reason').value;

    if (!bed || !pat) {
      showToast('Missing details', 'Please choose both a bed and a patient.', 'error');
      return;
    }

    try {
      const res = await fetch('api/beds.php?action=admit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bed_number: bed, patient_id: pat, doctor_id: doc, reason: reason })
      });
      const data = await res.json();
      if (data.status === 'success') {
        closeQuickAdmitModal();
        fetchBeds();
        showToast('Admitted', data.message || `Patient allocated to bed ${bed}.`, 'success');
      } else {
        showToast('Error', data.message || 'Failed to admit patient.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server communication error.', 'error');
    }
  }

  // =========================================================================
  // ================= TRANSFER BED LOGIC ====================================
  // =========================================================================
  function openTransferModal(currentBed, patientName) {
    document.getElementById('transfer-current-bed-val').value = currentBed;
    document.getElementById('transfer-current-label').textContent = `${patientName} • In Bed #${currentBed}`;

    // Fill target vacant beds
    const select = document.getElementById('transfer-new-bed-select');
    select.innerHTML = '<option value="">-- Choose New Bed --</option>';
    allBeds.filter(b => b.status === 'Available' && b.bed_number !== currentBed).forEach(b => {
      select.innerHTML += `<option value="${b.bed_number}">${b.bed_number} (${b.type} Ward - ${b.wing || 'Floor'})</option>`;
    });

    document.getElementById('transfer-reason').value = '';
    document.getElementById('modal-transfer-bed').classList.remove('hidden');
  }

  function closeTransferModal() {
    document.getElementById('modal-transfer-bed').classList.add('hidden');
  }

  async function handleTransferBed(e) {
    e.preventDefault();
    const curBed = document.getElementById('transfer-current-bed-val').value;
    const newBed = document.getElementById('transfer-new-bed-select').value;
    const reason = document.getElementById('transfer-reason').value;

    if (!newBed) {
      showToast('Error', 'Please select a destination bed.', 'error');
      return;
    }

    try {
      const res = await fetch('api/beds.php?action=transfer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_bed: curBed, new_bed: newBed, reason: reason })
      });
      const data = await res.json();
      if (data.status === 'success') {
        closeTransferModal();
        closePatientReportModal();
        fetchBeds();
        showToast('Relocated', data.message || `Patient successfully transferred to ${newBed}.`, 'success');
      } else {
        showToast('Error', data.message || 'Failed to transfer patient.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server error.', 'error');
    }
  }

  // =========================================================================
  // ================= DISCHARGE LOGIC =======================================
  // =========================================================================
  function promptDischarge(bedNumber, patientName) {
    document.getElementById('discharge-bed-val').value = bedNumber;
    document.getElementById('discharge-patient-name').textContent = patientName;
    document.getElementById('discharge-bed-num').textContent = `Hospital Bed #${bedNumber}`;
    document.getElementById('discharge-notes').value = 'Discharged from inpatient bed. Ready for home rest.';
    document.getElementById('modal-discharge-patient').classList.remove('hidden');
  }

  function closeDischargeModal() {
    document.getElementById('modal-discharge-patient').classList.add('hidden');
  }

  async function handleConfirmDischarge(e) {
    e.preventDefault();
    const bedNumber = document.getElementById('discharge-bed-val').value;
    const notes = document.getElementById('discharge-notes').value;

    try {
      const res = await fetch('api/beds.php?action=discharge', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ number: bedNumber, notes: notes })
      });
      const data = await res.json();
      if (data.status === 'success') {
        closeDischargeModal();
        closePatientReportModal();
        fetchBeds();
        showToast('Discharged', data.message || `Bed ${bedNumber} is now sanitized and available.`, 'success');
      } else {
        showToast('Error', data.message || 'Failed to discharge patient.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server error during discharge.', 'error');
    }
  }

  // =========================================================================
  // ================= ADD / DELETE BED LOGIC ================================
  // =========================================================================
  function openAddBedModal() {
    autoSuggestBedNumber();
    document.getElementById('modal-add-bed').classList.remove('hidden');
  }

  function closeAddBedModal() {
    document.getElementById('modal-add-bed').classList.add('hidden');
  }

  function autoSuggestBedNumber() {
    const type = document.getElementById('new-bed-type').value;
    const currentCount = allBeds.filter(b => b.type === type).length;
    const nextNum = String(currentCount + 1).padStart(2, '0');
    document.getElementById('new-bed-number').value = `${type}-${nextNum}`;
    document.getElementById('new-bed-wing').value = type === 'ICU' ? 'Critical Care Wing, 3rd Floor' : 'North Wing, 2nd Floor';
  }

  async function handleCreateBed(e) {
    e.preventDefault();
    try {
      const res = await fetch('api/beds.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type: document.getElementById('new-bed-type').value,
          number: document.getElementById('new-bed-number').value,
          wing: document.getElementById('new-bed-wing').value
        })
      });
      const data = await res.json();
      if (data.status === 'success') {
        closeAddBedModal();
        fetchBeds();
        showToast('Success', data.message || 'New bed added to inventory.', 'success');
      } else {
        showToast('Error', data.message || 'Failed to add bed.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server error.', 'error');
    }
  }

  async function deleteBed(num) {
    if (!confirm(`Are you sure you want to remove Bed ${num} from hospital inventory?`)) return;
    try {
      const res = await fetch('api/beds.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ number: num })
      });
      const data = await res.json();
      if (data.status === 'success') {
        fetchBeds();
        showToast('Bed Removed', data.message || `Bed ${num} deleted.`, 'success');
      } else {
        showToast('Error', data.message || 'Bed is occupied or not found.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Failed to delete bed.', 'error');
    }
  }

  // --- Helper: Format Frequency ---
  function formatFrequency(freq) {
    if (!freq || !freq.trim()) {
      return { label: 'As directed', full: 'As instructed by doctor', icon: 'fa-regular fa-clock text-slate-400' };
    }
    const clean = freq.trim().toUpperCase();
    if (clean === 'OD' || clean === '1-0-0' || clean === '0-0-1') {
      return { label: freq, full: 'Once Daily (1 Time a Day)', icon: 'fa-solid fa-sun text-amber-500' };
    } else if (clean === 'BID' || clean === '1-0-1' || clean === 'TWICE DAILY') {
      return { label: freq, full: 'Twice Daily (Morning & Night)', icon: 'fa-solid fa-clock text-blue-500' };
    } else if (clean === 'TID' || clean === '1-1-1' || clean === 'THRICE DAILY') {
      return { label: freq, full: '3 Times Daily (Morning, Afternoon & Night)', icon: 'fa-solid fa-repeat text-indigo-500' };
    } else if (clean === 'QID' || clean === '1-1-1-1') {
      return { label: freq, full: '4 Times Daily (Every 6 Hours)', icon: 'fa-solid fa-rotate text-purple-500' };
    } else if (clean === 'SOS' || clean.includes('AS NEEDED') || clean.includes('WHEN NEEDED')) {
      return { label: freq, full: 'As Needed (Only during pain or fever)', icon: 'fa-solid fa-triangle-exclamation text-rose-500' };
    } else if (clean === 'HS' || clean.includes('BEDTIME')) {
      return { label: freq, full: 'At Bedtime (Before sleep)', icon: 'fa-solid fa-moon text-indigo-600' };
    } else if (clean === 'STAT') {
      return { label: freq, full: 'Immediate / Single Dose', icon: 'fa-solid fa-bolt text-red-600' };
    }
    return { label: freq, full: freq, icon: 'fa-regular fa-clock text-slate-400' };
  }

  // --- Helper: Month Short ---
  function getMonthShort(dateStr) {
    if (!dateStr) return 'MTH';
    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    const d = new Date(dateStr);
    return isNaN(d.getMonth()) ? 'MTH' : months[d.getMonth()];
  }

  // --- Helper: Escape HTML ---
  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // --- Close modals on Escape key or backdrop click ---
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closePatientReportModal();
      closePrintModal();
      closeQuickAdmitModal();
      closeTransferModal();
      closeDischargeModal();
      closeAddBedModal();
      closeImagePreview();
    }
  });

  ['modal-patient-report', 'modal-print-prescription', 'modal-admit-patient', 'modal-transfer-bed', 'modal-discharge-patient', 'modal-add-bed'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('click', (e) => {
        if (e.target === el) {
          el.classList.add('hidden');
        }
      });
    }
  });
</script>

<?php include 'includes/footer.php'; ?>

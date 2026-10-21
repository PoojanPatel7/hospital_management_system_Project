<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- Print & Custom Responsive Smooth Scroll Styles -->
<style>
@media print {
    body * { visibility: hidden !important; }
    #printable-prescription-container, #printable-prescription-container * { visibility: visible !important; }
    #printable-prescription-container {
        position: fixed !important; left: 0 !important; top: 0 !important;
        width: 100vw !important; height: auto !important; margin: 0 !important;
        padding: 24px !important; background: #ffffff !important; color: #0f172a !important;
        display: block !important; z-index: 999999 !important;
    }
    .no-print { display: none !important; }
}

/* Touch-Friendly Smooth Horizontal Scrollbar */
.touch-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.touch-scroll::-webkit-scrollbar {
    height: 5px;
}
.touch-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.touch-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 9999px;
}
.touch-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* True Fullscreen Bed Box Mode for Desktop, Tablet & Phone */
#bed-container-slider.bed-box-fullscreen,
#bed-container-slider.mobile-fullscreen-active {
  position: fixed !important;
  inset: 0 !important;
  z-index: 99999 !important;
  width: 100vw !important;
  height: 100vh !important;
  height: 100dvh !important;
  max-width: 100vw !important;
  border-radius: 0 !important;
  border: none !important;
  overflow-y: auto !important;
  background: #f8fafc !important;
  padding: 0 !important;
  margin: 0 !important;
  box-shadow: none !important;
}

#bed-container-slider.bed-box-fullscreen #slider-deck-card-content,
#bed-container-slider.mobile-fullscreen-active #slider-deck-card-content {
  max-width: 1600px;
  margin-left: auto;
  margin-right: auto;
  padding: 16px 16px 48px 16px;
}
</style>

<div class="space-y-3.5 sm:space-y-4 max-w-[1680px] mx-auto pb-16 px-2.5 sm:px-4 lg:px-6">

  <!-- ================= UNIFIED SLEEK TOP BAR (SEARCH, FILTERS & 3-DOTS MENU) ================= -->
  <div class="bg-white rounded-2xl border border-slate-200/80 p-2.5 sm:p-3 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 sm:gap-3">
    
    <!-- Left: Bed Station Icon & Fast Search Bar -->
    <div class="flex items-center gap-2.5 flex-1 min-w-0">
      <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base sm:text-lg font-black border border-rose-100 shadow-2xs shrink-0" title="Hospital Inpatient Bed Stations">
        <i class="fa-solid fa-bed-pulse"></i>
      </div>
      <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
        <input type="text" id="bed-search-input" oninput="applyFilters()" placeholder="Search patient, MRN, bed #, doctor, diagnosis..." class="w-full text-xs font-semibold pl-9 pr-8 py-2 sm:py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition placeholder-slate-400">
        <button onclick="clearBedSearch()" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 w-5 h-5 rounded-full flex items-center justify-center">
          <i class="fa-solid fa-xmark text-[11px]"></i>
        </button>
      </div>
    </div>

    <!-- Right: Ward Filter, Status Filter, View Switcher & 3-Dots Options Menu -->
    <div class="flex items-center gap-2 justify-between md:justify-end flex-wrap sm:flex-nowrap">
      
      <!-- Ward Filter Buttons -->
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200/80 text-xs font-bold text-slate-600 shrink-0">
        <button id="filter-ward-all" onclick="setWardFilter('all')" class="px-2.5 sm:px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition">All</button>
        <button id="filter-ward-opd" onclick="setWardFilter('OPD')" class="px-2.5 sm:px-3 py-1.5 rounded-lg hover:text-slate-900 transition">OPD</button>
        <button id="filter-ward-icu" onclick="setWardFilter('ICU')" class="px-2.5 sm:px-3 py-1.5 rounded-lg hover:text-slate-900 transition">ICU</button>
      </div>

      <!-- Status Filter Dropdown -->
      <select id="bed-status-filter" onchange="applyFilters()" class="text-xs font-bold border border-slate-200 px-2.5 sm:px-3 py-2 rounded-xl bg-white text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/20 shrink-0">
        <option value="all">All Status</option>
        <option value="Occupied">Occupied</option>
        <option value="Available">Available</option>
      </select>

      <!-- View Switcher (Slider Deck, All Cards, Table) -->
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200/80 text-xs font-bold text-slate-600 shrink-0">
        <button id="btn-bed-view-slider" onclick="setBedDisplayMode('slider')" class="px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 flex items-center gap-1.5 transition" title="Big Box Slider Deck">
          <i class="fa-solid fa-layer-group text-indigo-600"></i> <span class="hidden sm:inline">Slider</span>
        </button>
        <button id="btn-bed-view-grid" onclick="setBedDisplayMode('grid')" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition" title="All Cards View">
          <i class="fa-solid fa-table-cells-large"></i> <span class="hidden sm:inline">Cards</span>
        </button>
        <button id="btn-bed-view-table" onclick="setBedDisplayMode('table')" class="px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition" title="Registry Table">
          <i class="fa-solid fa-table-list"></i> <span class="hidden sm:inline">Table</span>
        </button>
      </div>

      <!-- 3-Dots Menu Dropdown Button & Popup -->
      <div class="relative shrink-0">
        <button onclick="toggleBedOptionsMenu(event)" id="btn-bed-options-menu" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold flex items-center justify-center transition shadow-2xs active:scale-95" title="Bed Station Actions">
          <i class="fa-solid fa-ellipsis-vertical text-slate-700"></i>
        </button>

        <!-- 3-Dots Dropdown Popup -->
        <div id="bed-options-dropdown" class="hidden absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 z-50 animate-in fade-in zoom-in-95 duration-150">
          <div class="px-3.5 py-2 border-b border-slate-100 flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Bed Operations</span>
            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Sync
            </span>
          </div>

          <!-- + Admit Patient -->
          <button onclick="openQuickAdmitModal(); closeBedOptionsMenu();" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2.5 transition">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shrink-0 border border-indigo-100">
              <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
              <span class="block">+ Admit Patient</span>
              <span class="text-[10px] text-slate-400 font-normal">Assign patient to a vacant bed</span>
            </div>
          </button>

          <!-- Add Bed -->
          <button onclick="openAddBedModal(); closeBedOptionsMenu();" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2.5 transition">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs shrink-0 border border-slate-200">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div>
              <span class="block">+ Add Bed</span>
              <span class="text-[10px] text-slate-400 font-normal">Create new ICU or OPD bed</span>
            </div>
          </button>

          <div class="my-1 border-t border-slate-100"></div>

          <!-- Refresh Live Data -->
          <button onclick="fetchBeds(true); closeBedOptionsMenu();" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 flex items-center gap-2.5 transition">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shrink-0 border border-emerald-100">
              <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
            </div>
            <div>
              <span class="block">Refresh Live Data</span>
              <span class="text-[10px] text-slate-400 font-normal">Sync vitals &amp; monitor snapshots</span>
            </div>
          </button>

          <!-- Toggle Fullscreen Bed Box -->
          <button onclick="toggleBedBoxFullScreen(); closeBedOptionsMenu();" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2.5 transition">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs shrink-0 border border-slate-200">
              <i class="fa-solid fa-expand"></i>
            </div>
            <div>
              <span class="block">Fullscreen Bed Box</span>
              <span class="text-[10px] text-slate-400 font-normal">Expand bed station full screen</span>
            </div>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Hidden global inputs for 1-tap direct camera capture from bed cards -->
  <input type="file" id="direct-card-camera-input" accept="image/*" capture="environment" class="hidden" onchange="handleDirectCardFileSelection(this)">
  <input type="file" id="direct-card-gallery-input" accept="image/*,application/pdf" multiple class="hidden" onchange="handleDirectCardFileSelection(this)">

  <!-- ========================================================================= -->
  <!-- ================= HERO BIG BOX BED STATION SLIDER DECK ================== -->
  <!-- ========================================================================= -->
  <div id="bed-container-slider" class="relative bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden transition-all duration-300">
    
    <!-- Slider Control Header -->
    <div class="bg-white border-b border-slate-200 px-3.5 sm:px-6 py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3 sticky top-0 z-20 shadow-xs">
      
      <!-- Left: Bed Badge & Floor / Counter -->
      <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
        <span id="slider-bed-badge" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white font-mono font-black text-sm tracking-wide shadow-xs shrink-0">
          ICU-01
        </span>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span id="slider-ward-title" class="text-sm sm:text-base font-black text-slate-900 truncate">ICU Critical Care Ward</span>
            <span id="slider-status-pill" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-50 text-rose-700 border border-rose-200 shrink-0">
              <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
              <span>Occupied</span>
            </span>
          </div>
          <p id="slider-wing-sub" class="text-[11px] text-slate-400 font-semibold truncate">Critical Care Wing, 3rd Floor</p>
        </div>
      </div>

      <!-- Center: < and > Slider Controls & Counter -->
      <div class="flex items-center justify-between sm:justify-center gap-2 shrink-0">
        <!-- < Prev Bed Button -->
        <button onclick="navigateSlider(-1)" class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-black text-base flex items-center justify-center transition active:scale-90 border border-slate-200 shadow-2xs" title="Previous Bed (Left Arrow)">
          <i class="fa-solid fa-chevron-left"></i>
        </button>

        <!-- Counter Badge -->
        <div class="px-3 sm:px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200/80 text-center min-w-[110px]">
          <div id="slider-counter" class="text-xs sm:text-sm font-black text-slate-900 font-mono">Bed 1 of 11</div>
          <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Swipe / Arrows</div>
        </div>

        <!-- > Next Bed Button -->
        <button onclick="navigateSlider(1)" class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-black text-base flex items-center justify-center transition active:scale-90 border border-slate-200 shadow-2xs" title="Next Bed (Right Arrow)">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>

      <!-- Right: Mobile Fullscreen & Header Actions -->
      <div class="flex items-center gap-2 justify-end shrink-0">
        <!-- Bed Box Fullscreen Button -->
        <button onclick="toggleBedBoxFullScreen()" id="btn-mobile-fs" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200" title="Toggle Fullscreen Bed Station">
          <i class="fa-solid fa-expand text-xs" id="icon-mobile-fs"></i>
          <span class="text-xs" id="text-mobile-fs">Fullscreen</span>
        </button>

        <!-- Dynamic Header Action Slot -->
        <div id="slider-header-action-slot"></div>
      </div>
    </div>

    <!-- Bed Quick-Track Pills Strip (Jump directly to any bed) -->
    <div class="bg-slate-50/80 border-b border-slate-200/80 px-3 sm:px-6 py-2 touch-scroll flex items-center gap-2" id="slider-track-strip">
      <!-- Dynamic bed pills rendered here -->
    </div>

    <!-- Big Box Card Body (Slide Container) -->
    <div id="slider-deck-card-content" class="p-3.5 sm:p-6 transition-all duration-300">
      <!-- Active Bed Full Content dynamically rendered here -->
    </div>

  </div>

  <!-- ================= HORIZON BEDS STATION MATRIX (ALL CARDS VIEW) ================= -->
  <div id="bed-container-grid" class="hidden space-y-5 sm:space-y-6">

    <!-- ICU Critical Care Ward Section -->
    <div id="section-ward-icu" class="bg-white rounded-3xl border border-rose-200/90 p-3.5 sm:p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 border-b border-rose-100 mb-3.5 sm:mb-4">
        <div class="flex items-center gap-2 sm:gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-black border border-rose-200">
            <i class="fa-solid fa-heart-pulse text-sm"></i>
          </div>
          <div>
            <h2 class="font-black text-sm sm:text-base text-slate-900 flex items-center gap-1.5 sm:gap-2">
              <span>ICU Critical Care Ward</span>
              <span id="badge-icu-count" class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200">0 Beds</span>
            </h2>
          </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2 text-xs">
          <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-[11px]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span id="icu-avail-count-sub">0 Vacant</span>
          </span>
          <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-bold border border-rose-200 text-[11px]">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
            <span id="icu-occ-count-sub">0 Occupied</span>
          </span>
        </div>
      </div>

      <div id="icu-bed-matrix" class="space-y-3.5 sm:space-y-4">
        <!-- Dynamic ICU bed station cards inserted here -->
      </div>
    </div>

    <!-- OPD Ward Section -->
    <div id="section-ward-opd" class="bg-white rounded-3xl border border-slate-200/90 p-3.5 sm:p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3.5 sm:mb-4">
        <div class="flex items-center gap-2 sm:gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black border border-blue-100">
            <i class="fa-solid fa-bed text-sm"></i>
          </div>
          <div>
            <h2 class="font-black text-sm sm:text-base text-slate-900 flex items-center gap-1.5 sm:gap-2">
              <span>OPD Observation Ward</span>
              <span id="badge-opd-count" class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">0 Beds</span>
            </h2>
          </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2 text-xs">
          <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-[11px]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span id="opd-avail-count-sub">0 Vacant</span>
          </span>
          <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-100 text-[11px]">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
            <span id="opd-occ-count-sub">0 Occupied</span>
          </span>
        </div>
      </div>

      <div id="opd-bed-matrix" class="space-y-3.5 sm:space-y-4">
        <!-- Dynamic OPD bed station cards inserted here -->
      </div>
    </div>

  </div>

  <!-- ================= TABLE VIEW: DETAILED REGISTRY ================= -->
  <div id="bed-container-table" class="hidden bg-white rounded-2xl sm:rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="touch-scroll">
      <table class="w-full text-left text-xs min-w-[700px]">
        <thead class="bg-slate-50 font-black border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[10px]">
          <tr>
            <th class="p-3.5">Bed Designation</th>
            <th class="p-3.5">Status</th>
            <th class="p-3.5">Patient Details</th>
            <th class="p-3.5">Attending Doctor</th>
            <th class="p-3.5">Machine Monitor Preview</th>
            <th class="p-3.5">Admitted Time</th>
            <th class="p-3.5 text-right">Actions</th>
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
<!-- ================= REDESIGNED FULL VIEW: WHITE THEME WORKSTATION ========= -->
<!-- ========================================================================= -->
<div id="modal-fullscreen-bedstation" class="hidden fixed inset-0 z-[160] flex flex-col bg-slate-100 text-slate-800 overflow-hidden">
  
  <!-- Top Navigation Bar (Crisp White Theme & Touch-Friendly) -->
  <div class="bg-white border-b border-slate-200 px-3 sm:px-6 py-3 sm:py-3.5 flex items-center justify-between shrink-0 shadow-xs z-10">
    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 pr-2">
      <span id="fs-bed-number-badge" class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl bg-rose-600 text-white font-mono font-black text-xs sm:text-sm tracking-wide shadow-xs shrink-0">
        ICU-01
      </span>
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap sm:flex-nowrap">
          <h2 id="fs-patient-name-title" class="text-sm sm:text-lg font-black text-slate-900 leading-tight truncate">Patient Full Name</h2>
          <span id="fs-ward-badge" class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase bg-slate-100 text-slate-700 border border-slate-200 shrink-0">ICU Ward</span>
        </div>
        <p id="fs-patient-subtitle" class="text-[10px] sm:text-xs text-slate-500 font-medium mt-0.5 truncate">MRN-000 • 34Y Male • AB+</p>
      </div>
    </div>

    <!-- Switcher Controls & Close -->
    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
      <button onclick="navigateFullscreenBed(-1)" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 border border-slate-200 min-h-[36px]" title="Previous Occupied Bed">
        <i class="fa-solid fa-chevron-left text-xs"></i>
        <span class="hidden md:inline">Prev Bed</span>
      </button>
      <button onclick="navigateFullscreenBed(1)" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 border border-slate-200 min-h-[36px]" title="Next Occupied Bed">
        <span class="hidden md:inline">Next Bed</span>
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </button>
      <button onclick="closeFullscreenBedStation()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200 flex items-center justify-center transition ml-0.5 sm:ml-1" title="Exit Full View">
        <i class="fa-solid fa-xmark text-sm font-black"></i>
      </button>
    </div>
  </div>

  <!-- Fullscreen Main Body: Left (Patient & Vitals) / Right (Monitors & Reports) in White Theme -->
  <div class="flex-1 overflow-y-auto p-3 sm:p-5 flex flex-col lg:flex-row gap-4 bg-slate-100">
    
    <!-- LEFT SIDE: Patient Profile, Highlighted Vitals, Attending & Actions -->
    <div class="w-full lg:w-4/12 xl:w-4/12 flex flex-col gap-4 overflow-y-auto custom-scrollbar lg:pr-1 shrink-0">
      
      <!-- Patient Information & Highlighted Vitals Card -->
      <div class="bg-white border border-slate-200/90 rounded-3xl p-4 sm:p-5 space-y-4 shadow-sm">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 text-xs">
          <span class="text-slate-400 font-extrabold uppercase text-[10px] tracking-wider">Bed Wing / Floor:</span>
          <span id="fs-bed-wing" class="font-bold text-slate-800">Critical Care Wing, 3rd Floor</span>
        </div>

        <!-- Highlighted Vitals Metric Grid (Responsive 2x2 on Mobile, 4 Cols on Tablet/PC) -->
        <div>
          <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block mb-2">Latest Logged Vitals</span>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
            <!-- BP Tile -->
            <div class="bg-rose-50/60 p-2.5 sm:p-3 rounded-2xl border border-rose-200">
              <span class="text-[9px] sm:text-[10px] text-rose-600 block font-extrabold uppercase truncate">Blood Pressure</span>
              <span id="fs-vital-bp" class="text-sm sm:text-base font-black text-rose-700 mt-0.5 block">--</span>
            </div>
            <!-- Pulse Tile -->
            <div class="bg-emerald-50/60 p-2.5 sm:p-3 rounded-2xl border border-emerald-200">
              <span class="text-[9px] sm:text-[10px] text-emerald-600 block font-extrabold uppercase truncate">Pulse</span>
              <span id="fs-vital-pulse" class="text-sm sm:text-base font-black text-emerald-700 mt-0.5 block">-- bpm</span>
            </div>
            <!-- SpO2 Tile -->
            <div class="bg-blue-50/60 p-2.5 sm:p-3 rounded-2xl border border-blue-200">
              <span class="text-[9px] sm:text-[10px] text-blue-600 block font-extrabold uppercase truncate">SpO2</span>
              <span id="fs-vital-spo2" class="text-sm sm:text-base font-black text-blue-700 mt-0.5 block">--%</span>
            </div>
            <!-- Temp Tile -->
            <div class="bg-amber-50/60 p-2.5 sm:p-3 rounded-2xl border border-amber-200">
              <span class="text-[9px] sm:text-[10px] text-amber-600 block font-extrabold uppercase truncate">Temp</span>
              <span id="fs-vital-temp" class="text-sm sm:text-base font-black text-amber-700 mt-0.5 block">--°F</span>
            </div>
          </div>
        </div>

        <!-- Highlighted Clinical & Doctor Details -->
        <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-bold">Attending Doctor:</span>
            <span id="fs-doctor-name" class="font-bold text-indigo-700">Dr. --</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-bold">Admitted Date:</span>
            <span id="fs-admit-date" class="font-mono text-slate-700 font-bold">--</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-bold">Contact Phone:</span>
            <span id="fs-phone" class="font-mono text-slate-700">--</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-400 font-bold">Emergency Contact:</span>
            <span id="fs-emergency" class="font-mono text-slate-700">--</span>
          </div>
        </div>

        <!-- Admission Diagnosis & Symptoms Highlight -->
        <div class="space-y-1 text-xs">
          <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block">Admission Diagnosis &amp; Symptoms</span>
          <div id="fs-diagnosis" class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-slate-800 font-medium">
            --
          </div>
        </div>

        <!-- Fast Actions Strip -->
        <div class="pt-2 grid grid-cols-2 gap-2">
          <button id="fs-btn-camera" onclick="triggerFullscreenCamera()" class="p-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs flex items-center justify-center gap-2 shadow-sm shadow-indigo-600/20 transition active:scale-95 col-span-2 min-h-[44px]">
            <i class="fa-solid fa-camera text-base"></i>
            <span>Snap Machine Monitor Photo</span>
          </button>
          
          <button id="fs-btn-transfer" onclick="handleFsTransfer()" class="p-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs flex items-center justify-center gap-1.5 transition min-h-[40px]">
            <i class="fa-solid fa-arrows-turn-to-dots text-indigo-600"></i>
            <span>Transfer Bed</span>
          </button>

          <button id="fs-btn-discharge" onclick="handleFsDischarge()" class="p-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs flex items-center justify-center gap-1.5 transition min-h-[40px]">
            <i class="fa-solid fa-person-walking-arrow-right"></i>
            <span>Discharge</span>
          </button>
        </div>
      </div>

    </div>

    <!-- RIGHT SIDE: Machine Monitor Feed & Reports Timeline in White Theme -->
    <div class="w-full lg:w-8/12 xl:w-8/12 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
      
      <!-- Real-Time Machine Monitor Feed Preview Card -->
      <div class="bg-white border border-slate-200/90 rounded-3xl p-3.5 sm:p-5 flex flex-col shadow-sm">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-black uppercase tracking-wider text-slate-800 truncate">Real-Time Machine Monitor Screen</span>
          </div>
          <div class="flex items-center gap-1.5 sm:gap-2">
            <span id="fs-latest-timestamp" class="px-2 sm:px-2.5 py-1 rounded-lg bg-slate-100 font-mono text-[11px] sm:text-xs text-slate-700 font-bold border border-slate-200">Latest Photo</span>
            <button id="fs-btn-zoom-main" onclick="zoomFullscreenMainImage()" class="p-1 px-2 sm:p-1.5 sm:px-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 border border-slate-200" title="Zoom In Preview">
              <i class="fa-solid fa-magnifying-glass-plus"></i>
              <span class="hidden sm:inline">Zoom</span>
            </button>
          </div>
        </div>

        <div id="fs-main-monitor-container" class="relative aspect-video sm:aspect-[21/9] rounded-2xl bg-slate-950 border border-slate-200 overflow-hidden flex items-center justify-center group cursor-pointer" onclick="zoomFullscreenMainImage()">
          <img id="fs-main-monitor-img" src="" class="w-full h-full object-contain" alt="Machine Monitor Display">
          <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-2">
            <i class="fa-solid fa-magnifying-glass-plus text-base"></i> Click to Zoom Full Resolution
          </div>
        </div>
      </div>

      <!-- Hourly Photos & Medical Reports/Scans (White Theme Tabs) -->
      <div class="bg-white border border-slate-200/90 rounded-3xl p-3.5 sm:p-5 flex-1 flex flex-col shadow-sm">
        <!-- Tabs Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 flex-wrap gap-2">
          <div class="flex items-center gap-1.5 sm:gap-2">
            <button onclick="switchFsTab('photos')" id="fstab-btn-photos" class="px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-rose-600 text-white text-xs font-black transition flex items-center gap-1.5 shadow-xs">
              <i class="fa-solid fa-camera"></i>
              <span>Monitor Photos (<span id="fs-photos-count">0</span>)</span>
            </button>
            <button onclick="switchFsTab('reports')" id="fstab-btn-reports" class="px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200">
              <i class="fa-solid fa-file-waveform text-indigo-600"></i>
              <span>Reports &amp; Scans (<span id="fs-reports-count">0</span>)</span>
            </button>
          </div>

          <button onclick="triggerFullscreenCamera()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200">
            <i class="fa-solid fa-plus text-rose-600"></i>
            <span>Add Photo</span>
          </button>
        </div>

        <!-- Tab 1: Photos Grid with Rich Image Previews -->
        <div id="fstab-content-photos" class="space-y-3">
          <div id="fs-photos-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3">
            <!-- Dynamic thumbnails rendered here -->
          </div>
        </div>

        <!-- Tab 2: Reports Grid with Rich Image & Document Previews -->
        <div id="fstab-content-reports" class="hidden space-y-3">
          <div id="fs-reports-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            <!-- Dynamic reports rendered here -->
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: QUICK SAVE HOURLY MONITOR OBSERVATION ========== -->
<!-- ========================================================================= -->
<div id="modal-add-observation" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-6 relative my-auto shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
    <!-- Close Button -->
    <button onclick="closeObservationModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition">
      <i class="fa-solid fa-xmark text-sm font-bold"></i>
    </button>

    <!-- Modal Header -->
    <div class="flex items-center gap-3 mb-3">
      <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold border border-rose-100 shrink-0">
        <i class="fa-solid fa-camera"></i>
      </div>
      <div>
        <h3 class="font-black text-slate-900 text-base leading-tight">Save Machine Monitor Photo</h3>
        <p class="text-xs text-slate-500 font-medium">Snapshot of machine vitals for inpatient admission record</p>
      </div>
    </div>

    <!-- Active Bed/Patient Info Banner -->
    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 mb-3 flex items-center justify-between text-xs">
      <div>
        <span class="text-[10px] uppercase font-bold text-slate-400 block">Patient</span>
        <strong id="obs-patient-name" class="text-slate-900 text-sm font-black">Patient Name</strong>
        <span id="obs-patient-id" class="text-[11px] font-mono text-slate-500 ml-1">MRN-000</span>
      </div>
      <div class="text-right">
        <span id="obs-bed-badge" class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-mono font-black text-xs border border-indigo-200">Bed #--</span>
      </div>
    </div>

    <form onsubmit="handleSaveObservation(event)" class="space-y-3">
      <input type="hidden" id="obs-bed-number-val">
      <input type="hidden" id="obs-patient-id-val">

      <!-- Check Interval / Hour Label -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Check Interval *</label>
        <input type="text" id="obs-hour-label" required placeholder="e.g. Hour 1 (08:00 AM), Hour 2 (09:00 AM)..." class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
      </div>

      <!-- ================= CAMERA & PHOTO STAGING BOX ================= -->
      <div class="border border-slate-200 rounded-2xl p-3 bg-slate-50/80 space-y-2.5">
        <div class="flex items-center justify-between text-xs">
          <label class="font-extrabold text-slate-800 flex items-center gap-1.5">
            <i class="fa-solid fa-camera text-rose-600"></i>
            <span>Machine Monitor Snapshots</span>
          </label>
          <span class="text-[10px] text-slate-500 font-bold" id="obs-selected-count">0 photos staged</span>
        </div>

        <!-- Hidden File Inputs within modal -->
        <input type="file" id="modal-camera-input" accept="image/*" capture="environment" class="hidden" onchange="handleModalFileSelection(this)">
        <input type="file" id="modal-gallery-input" accept="image/*" multiple class="hidden" onchange="handleModalFileSelection(this)">

        <!-- Action Buttons to Snap More or Pick from Gallery -->
        <div class="grid grid-cols-2 gap-2">
          <button type="button" onclick="document.getElementById('modal-camera-input').click()" class="p-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition active:scale-95 min-h-[42px]">
            <i class="fa-solid fa-camera text-sm"></i>
            <span>+ Snap Camera</span>
          </button>

          <button type="button" onclick="document.getElementById('modal-gallery-input').click()" class="p-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition active:scale-95 min-h-[42px]">
            <i class="fa-solid fa-images text-sm text-indigo-600"></i>
            <span>Add from Device</span>
          </button>
        </div>

        <!-- Staged Photos Preview Container -->
        <div id="obs-preview-container" class="hidden mt-2 p-2 rounded-xl bg-white border border-slate-200">
          <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 mb-2 px-1">
            <span>Staged Photos (<span id="obs-staged-count-num">0</span>):</span>
            <button type="button" onclick="clearObsImages()" class="text-rose-600 hover:underline text-[10px] font-bold">Clear all</button>
          </div>
          <div id="obs-preview-list" class="grid grid-cols-3 gap-2 max-h-36 overflow-y-auto custom-scrollbar p-1">
            <!-- Dynamic thumbnails rendered here -->
          </div>
        </div>

        <!-- Empty prompt if no photos yet -->
        <div id="obs-empty-preview-prompt" class="p-3 text-center rounded-xl border border-dashed border-slate-300 text-xs text-slate-400">
          <i class="fa-solid fa-camera text-base text-slate-300 mb-1 block"></i>
          Tap <strong>+ Snap Camera</strong> or <strong>Add from Device</strong> above to capture monitor photo.
        </div>
      </div>

      <!-- Quick Optional Vitals Dropdown/Accordion -->
      <details class="bg-slate-50 border border-slate-200 rounded-2xl p-2.5 text-xs text-slate-700">
        <summary class="font-bold cursor-pointer select-none text-slate-700 hover:text-slate-900 flex items-center justify-between">
          <span>Optional: Log Vitals (BP, Pulse, SpO2, Temp)</span>
          <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
        </summary>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2.5">
          <div>
            <span class="text-[10px] text-slate-400 font-bold block mb-0.5">BP</span>
            <input type="text" id="obs-vital-bp" placeholder="e.g. 120/80" class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-2.5 py-1.5 bg-white outline-none">
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold block mb-0.5">Pulse (bpm)</span>
            <input type="text" id="obs-vital-pulse" placeholder="e.g. 78" class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-2.5 py-1.5 bg-white outline-none">
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold block mb-0.5">SpO2 (%)</span>
            <input type="text" id="obs-vital-spo2" placeholder="e.g. 98%" class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-2.5 py-1.5 bg-white outline-none">
          </div>
          <div>
            <span class="text-[10px] text-slate-400 font-bold block mb-0.5">Temp (°F)</span>
            <input type="text" id="obs-vital-temp" placeholder="e.g. 98.6" class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-2.5 py-1.5 bg-white outline-none">
          </div>
        </div>
        <div class="mt-2">
          <span class="text-[10px] text-slate-400 font-bold block mb-0.5">Clinical Notes</span>
          <input type="text" id="obs-notes" placeholder="e.g. Patient resting comfortably, regular sinus rhythm..." class="w-full text-xs font-medium border border-slate-200 rounded-xl px-2.5 py-1.5 bg-white outline-none">
        </div>
      </details>

      <!-- Primary Action Buttons -->
      <div class="pt-2 flex items-center justify-end gap-2">
        <button type="button" onclick="closeObservationModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
          Cancel
        </button>
        <button type="submit" id="btn-submit-obs" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2 active:scale-95 min-h-[42px]">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Save Monitor Photo</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: HOURLY OBSERVATION HISTORY & TIMELINE ========== -->
<!-- ========================================================================= -->
<div id="modal-obs-history" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-3xl w-full p-4 sm:p-6 relative my-auto shadow-2xl border border-slate-200 max-h-[92vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">
    <!-- Top Bar -->
    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 shrink-0">
      <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold border border-indigo-100 shrink-0">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="min-w-0">
          <h3 class="font-extrabold text-slate-900 text-sm sm:text-base truncate" id="hist-title">Hourly Machine Monitor History</h3>
          <p class="text-xs text-slate-500 font-medium truncate" id="hist-subtitle">Patient Bed Timeline &amp; Snapshots</p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openObsFromHistory()" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-1">
          <i class="fa-solid fa-camera"></i>
          <span class="hidden sm:inline">+ Log New Hour</span>
          <span class="sm:hidden">+ Log</span>
        </button>
        <button onclick="closeObsHistoryModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
          <i class="fa-solid fa-xmark text-sm font-bold"></i>
        </button>
      </div>
    </div>

    <!-- Scrollable Body with Observation Entries -->
    <div id="hist-observations-list" class="overflow-y-auto custom-scrollbar flex-1 py-4 space-y-3.5">
      <!-- Dynamic list inserted here -->
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: QUICK ADMIT PATIENT (VALIDATED) ================ -->
<!-- ========================================================================= -->
<div id="modal-admit-patient" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-7 relative my-auto shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
    <button onclick="closeQuickAdmitModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm font-bold"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold border border-indigo-100 shrink-0">
        <i class="fa-solid fa-user-plus"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-base">Inpatient Bed Admission</h3>
        <p class="text-xs text-slate-500 font-medium">Assign patient to a vacant bed.</p>
      </div>
    </div>

    <form onsubmit="handleAdmitPatient(event)" class="space-y-3.5">
      <!-- Select Patient with Active Bed Exclusion/Warning -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Select Registered Patient *</label>
        <select id="admit-patient-select" onchange="checkSelectedPatientBed(this.value)" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- Choose Patient --</option>
        </select>
        <!-- Validation Alert if already admitted -->
        <div id="admit-patient-warning" class="hidden mt-1.5 p-2 rounded-xl bg-rose-50 border border-rose-200 text-[11px] font-bold text-rose-700 flex items-center gap-1.5">
          <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
          <span id="admit-patient-warning-text">This patient is already admitted to another bed!</span>
        </div>
      </div>

      <!-- Select Bed -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Assign Vacant Bed *</label>
        <select id="admit-bed-select" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- Choose Vacant Bed --</option>
        </select>
      </div>

      <!-- Select Attending Doctor -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Attending Doctor</label>
        <select id="admit-doctor-select" class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none">
          <option value="">-- General Duty / Unassigned --</option>
        </select>
      </div>

      <!-- Reason / Chief Complaint -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Admission / Diagnosis</label>
        <textarea id="admit-reason" rows="2" placeholder="e.g. Post-operative observation, continuous vitals monitoring..." class="w-full text-xs font-medium border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none"></textarea>
      </div>

      <div class="pt-2">
        <button type="submit" id="btn-submit-admit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-xs font-black shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-1.5 min-h-[42px]">
          <i class="fa-solid fa-check"></i>
          <span>Confirm Admission &amp; Allocate Bed</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: TRANSFER BED =================================== -->
<!-- ========================================================================= -->
<div id="modal-transfer-bed" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-4 sm:p-6 relative my-auto shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
    <button onclick="closeTransferModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm font-bold"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold border border-blue-100 shrink-0">
        <i class="fa-solid fa-arrows-turn-to-dots"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-base">Transfer Inpatient</h3>
        <p class="text-xs text-slate-500 font-medium">Relocate patient to another vacant bed.</p>
      </div>
    </div>

    <form onsubmit="handleTransferBed(event)" class="space-y-3.5">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
        <span class="text-[10px] uppercase font-bold text-slate-400 block">Current Bed &amp; Patient</span>
        <div class="text-xs font-black text-slate-900" id="transfer-current-label">Bed --</div>
        <input type="hidden" id="transfer-current-bed-val">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Destination Vacant Bed *</label>
        <select id="transfer-new-bed-select" required class="w-full text-xs font-semibold border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none">
          <option value="">-- Choose New Bed --</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Transfer</label>
        <input type="text" id="transfer-reason" placeholder="e.g. Upgraded to ICU, Shifted to Ward..." class="w-full text-xs font-medium border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none">
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 text-xs font-black shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-1.5 min-h-[42px]">
          <i class="fa-solid fa-arrow-right-arrow-left"></i>
          <span>Complete Bed Relocation</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: DISCHARGE PATIENT ============================== -->
<!-- ========================================================================= -->
<div id="modal-discharge-patient" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-4 sm:p-6 relative my-auto shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
    <button onclick="closeDischargeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm font-bold"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold border border-rose-100 shrink-0">
        <i class="fa-solid fa-person-walking-arrow-right"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-base">Discharge Inpatient</h3>
        <p class="text-xs text-slate-500 font-medium">Free bed and conclude inpatient stay.</p>
      </div>
    </div>

    <form onsubmit="handleConfirmDischarge(event)" class="space-y-3.5">
      <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200">
        <div class="text-[10px] uppercase font-bold text-rose-600 block mb-0.5">Discharge Patient</div>
        <div class="text-sm font-black text-slate-900" id="discharge-patient-name">Patient Name</div>
        <div class="text-xs text-slate-600 mt-0.5 font-mono font-bold" id="discharge-bed-num">Bed #--</div>
        <input type="hidden" id="discharge-bed-val">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Discharge Advice / Clinical Summary</label>
        <textarea id="discharge-notes" rows="2" class="w-full text-xs font-medium border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white outline-none" placeholder="e.g. Patient recovered well. Discharged with home medication."></textarea>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button type="button" onclick="closeDischargeModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl py-2.5 text-xs font-bold transition min-h-[42px]">Cancel</button>
        <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-2.5 text-xs font-black shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-1.5 min-h-[42px]">
          <i class="fa-solid fa-check"></i>
          <span>Discharge &amp; Free</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: ADD NEW BED TO INVENTORY ======================= -->
<!-- ========================================================================= -->
<div id="modal-add-bed" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-sm w-full p-4 sm:p-6 relative my-auto shadow-2xl border border-slate-200" onclick="event.stopPropagation()">
    <button onclick="closeAddBedModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm font-bold"></i>
    </button>
    <div class="flex items-center gap-3 mb-4">
      <div class="w-9 h-9 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-base font-bold shrink-0">
        <i class="fa-solid fa-bed-pulse"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-base">Add New Bed</h3>
        <p class="text-[11px] text-slate-500 font-medium">Configure ward floor and designation.</p>
      </div>
    </div>
    <form onsubmit="handleCreateBed(event)" class="space-y-3">
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
      <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-2.5 text-xs font-black shadow-md shadow-rose-600/20 transition min-h-[42px]">Add Bed to Inventory</button>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: PATIENT CLINICAL DOSSIER ======================= -->
<!-- ========================================================================= -->
<div id="modal-patient-report" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-2.5 sm:p-5 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-200 relative my-auto flex flex-col max-h-[94vh] overflow-hidden" onclick="event.stopPropagation()">
    <!-- Top Bar -->
    <div class="p-3.5 sm:px-6 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2 sm:gap-2.5 min-w-0 pr-2">
        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold border border-indigo-100 shrink-0">
          <i class="fa-solid fa-file-medical"></i>
        </span>
        <div class="min-w-0">
          <h2 class="text-xs sm:text-sm font-black text-slate-900 leading-none flex items-center gap-1.5 sm:gap-2 truncate">
            <span>Patient Inpatient Dossier</span>
            <span id="pop-header-mrn" class="font-mono text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded shrink-0">MRN-000</span>
          </h2>
          <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 truncate">Clinical summary &amp; admission records</p>
        </div>
      </div>

      <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
        <a id="pop-btn-open-full" href="#" target="_blank" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition border border-slate-200">
          <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
          <span class="hidden sm:inline">Profile</span>
        </a>
        <button onclick="closePatientReportModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
          <i class="fa-solid fa-xmark text-sm font-bold"></i>
        </button>
      </div>
    </div>

    <!-- Scrollable Body -->
    <div class="overflow-y-auto custom-scrollbar flex-1 p-3.5 sm:p-6 space-y-4 bg-slate-50">
      <!-- Patient Banner -->
      <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-3">
          <div id="pop-avatar" class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg sm:text-xl font-black shadow-xs shrink-0">
            PT
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span id="pop-bed-text" class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">Bed #--</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
            </div>
            <h1 id="pop-patient-name" class="text-base sm:text-lg font-black text-slate-900 truncate">Patient Name</h1>
            <div class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5 truncate">
              Phone: <strong id="pop-phone" class="text-slate-800">--</strong> • Emergency: <strong id="pop-emergency" class="text-slate-800">--</strong>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 p-2 sm:p-2.5 rounded-2xl border border-slate-200 text-center text-xs self-start sm:self-auto">
          <div>
            <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold block">Age / Gender</span>
            <span id="pop-age-gender" class="font-black text-slate-900">--</span>
          </div>
          <div class="border-l border-slate-200 pl-3">
            <span class="text-[9px] uppercase tracking-wider text-rose-500 font-bold block">Blood</span>
            <span id="pop-blood" class="font-black text-rose-600 font-mono">--</span>
          </div>
        </div>
      </div>

      <!-- Dossier Tabs (Touch-Scrollable without clipping) -->
      <div class="bg-white rounded-2xl border border-slate-200 p-1 flex gap-1 shadow-2xs text-xs font-bold touch-scroll">
        <button onclick="switchPopupTab('observations')" id="poptab-btn-observations" class="poptab shrink-0 sm:flex-1 py-2 px-3 rounded-xl text-rose-700 bg-rose-50 flex items-center justify-center gap-1.5 transition">
          <i class="fa-solid fa-camera"></i>
          <span>Monitor Logs (<span id="pop-obs-count">0</span>)</span>
        </button>
        <button onclick="switchPopupTab('consultations')" id="poptab-btn-consultations" class="poptab shrink-0 sm:flex-1 py-2 px-3 rounded-xl text-slate-500 hover:bg-slate-50 flex items-center justify-center gap-1.5 transition">
          <i class="fa-solid fa-stethoscope"></i>
          <span>Prescriptions &amp; Notes</span>
        </button>
        <button onclick="switchPopupTab('files')" id="poptab-btn-files" class="poptab shrink-0 sm:flex-1 py-2 px-3 rounded-xl text-slate-500 hover:bg-slate-50 flex items-center justify-center gap-1.5 transition">
          <i class="fa-solid fa-file-waveform"></i>
          <span>Reports &amp; Scans (<span id="pop-files-count">0</span>)</span>
        </button>
      </div>

      <!-- Tab 1: Observations -->
      <div id="poptab-content-observations" class="poptab-view space-y-3">
        <div id="pop-obs-container" class="space-y-3"></div>
      </div>

      <!-- Tab 2: Consultations -->
      <div id="poptab-content-consultations" class="poptab-view hidden space-y-3">
        <div id="pop-consultations-list" class="space-y-3"></div>
      </div>

      <!-- Tab 3: Files with Rich Image Previews -->
      <div id="poptab-content-files" class="poptab-view hidden space-y-3">
        <div id="pop-files-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ================= MODAL: UNIVERSAL IMAGE & REPORT PREVIEW LIGHTBOX ====== -->
<!-- ========================================================================= -->
<div id="modal-image-preview" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-2.5 sm:p-4 bg-slate-950/85 backdrop-blur-sm" onclick="closeImagePreview()">
  <div class="relative w-full max-w-5xl max-h-[96vh] sm:max-h-[94vh] bg-white rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl p-2.5 sm:p-4 flex flex-col items-center justify-between border border-slate-200" onclick="event.stopPropagation()">
    
    <!-- Top Bar with Details and Close -->
    <div class="w-full flex items-center justify-between pb-2 mb-2 border-b border-slate-100 shrink-0 px-1 sm:px-2">
      <div class="min-w-0 pr-2">
        <h4 id="image-preview-title" class="text-xs sm:text-sm font-black text-slate-900 truncate">Preview Document</h4>
        <p id="image-preview-sub" class="text-[10px] sm:text-[11px] text-slate-400 font-mono mt-0.5 truncate">Patient Inpatient Record</p>
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
        <a id="image-preview-download-btn" href="#" target="_blank" download class="p-1.5 px-2.5 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200" title="Open Full in New Window">
          <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
          <span class="hidden sm:inline">Open Full</span>
        </a>
        <button onclick="closeImagePreview()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 flex items-center justify-center transition">
          <i class="fa-solid fa-xmark text-sm font-bold"></i>
        </button>
      </div>
    </div>

    <!-- Image Display Container -->
    <div class="w-full flex-1 flex items-center justify-center overflow-auto rounded-xl sm:rounded-2xl bg-slate-950/95 max-h-[80vh] min-h-[220px] p-1 sm:p-2">
      <img id="image-preview-element" src="" class="max-w-full max-h-[78vh] object-contain rounded-lg sm:rounded-xl transition duration-200" alt="Preview">
      <iframe id="image-preview-iframe" src="" class="hidden w-full h-[78vh] rounded-lg sm:rounded-xl border-none"></iframe>
    </div>
  </div>
</div>

<!-- ================= JAVASCRIPT ENGINE ================= -->
<script>
  let allBeds = [];
  let currentFilteredBeds = [];
  let currentSliderIndex = 0;
  let allPatients = [];
  let allDoctors = [];
  let currentFilterWard = 'all';
  let displayMode = 'slider'; // Default to the Big Box Slider Deck
  let autoRefreshTimer = null;
  let stagedObsImages = [];
  let currentObsTargetBed = null;
  let currentViewingPatientId = null;
  let currentFsBedIndex = -1;

  window.addEventListener('DOMContentLoaded', () => {
    fetchBeds();
    loadDirectoryPatients();
    loadHospitalDoctors();

    // Start 10-Second Auto Refresh Poll
    startAutoRefreshEngine();

    // Set up touch swipe gesture on Big Box Slider Deck
    setupSliderTouchGestures();
  });

  // --- Auto-Refresh Engine (Every 10 Seconds) ---
  function startAutoRefreshEngine() {
    if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    autoRefreshTimer = setInterval(() => {
      if (!isAnyModalOpen()) {
        silentFetchBeds();
      }
    }, 10000);
  }

  function isAnyModalOpen() {
    const modals = [
      'modal-add-observation',
      'modal-obs-history',
      'modal-admit-patient',
      'modal-transfer-bed',
      'modal-discharge-patient',
      'modal-add-bed',
      'modal-patient-report',
      'modal-image-preview'
    ];
    return modals.some(id => {
      const el = document.getElementById(id);
      return el && !el.classList.contains('hidden');
    });
  }

  async function silentFetchBeds() {
    try {
      const res = await fetch('api/beds.php?action=get_all');
      const data = await res.json();
      if (data.status === 'success') {
        allBeds = data.beds || [];
        updateStatistics(allBeds);
        applyFilters(true);

        if (currentFsBedIndex >= 0 && allBeds[currentFsBedIndex]) {
          renderFullscreenBedStation(allBeds[currentFsBedIndex]);
        }
      }
    } catch(e) {
      console.warn("Silent sync poll notice:", e);
    }
  }

  async function fetchBeds(showSpinner = false) {
    const icon = document.getElementById('refresh-icon');
    if (showSpinner && icon) icon.classList.add('animate-spin');

    try {
      const res = await fetch('api/beds.php?action=get_all');
      const data = await res.json();
      if (data.status === 'success') {
        allBeds = data.beds || [];
        updateStatistics(allBeds);
        applyFilters();
      }
    } catch (e) {
      console.error(e);
    } finally {
      if (icon) setTimeout(() => icon.classList.remove('animate-spin'), 400);
    }
  }

  // --- Statistics Calculation ---
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

    const setIf = (id, text) => {
      const el = document.getElementById(id);
      if (el) el.textContent = text;
    };

    setIf('stat-total-beds', total);
    setIf('stat-total-avail', avail);
    setIf('stat-total-occupied', occupied);

    setIf('stat-opd-total', opdBeds.length);
    setIf('stat-opd-avail', opdAvail);
    setIf('badge-opd-count', `${opdBeds.length} Beds`);
    setIf('opd-avail-count-sub', `${opdAvail} Vacant`);
    setIf('opd-occ-count-sub', `${opdOcc} Occupied`);
    const opdPct = opdBeds.length ? Math.round((opdOcc / opdBeds.length) * 100) : 0;
    setIf('stat-opd-occupancy-desc', `${opdPct}% Occupied`);

    setIf('stat-icu-total', icuBeds.length);
    setIf('stat-icu-avail', icuAvail);
    setIf('badge-icu-count', `${icuBeds.length} Beds`);
    setIf('icu-avail-count-sub', `${icuAvail} Vacant`);
    setIf('icu-occ-count-sub', `${icuOcc} Occupied`);
    const icuPct = icuBeds.length ? Math.round((icuOcc / icuBeds.length) * 100) : 0;
    setIf('stat-icu-occupancy-desc', `${icuPct}% Occupied`);
  }

  // --- Filter & Search ---
  function applyFilters(silent = false) {
    const query = document.getElementById('bed-search-input').value.trim().toLowerCase();
    const statusFilter = document.getElementById('bed-status-filter').value;
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);

    currentFilteredBeds = allBeds.filter(b => {
      if (currentFilterWard !== 'all' && b.type.toLowerCase() !== currentFilterWard.toLowerCase()) {
        return false;
      }
      if (statusFilter !== 'all' && b.status !== statusFilter) {
        return false;
      }
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

    if (currentSliderIndex >= currentFilteredBeds.length) {
      currentSliderIndex = Math.max(0, currentFilteredBeds.length - 1);
    }

    renderSliderBed();
    renderStationMatrix(currentFilteredBeds);
    renderTable(currentFilteredBeds);
  }

  function clearBedSearch() {
    document.getElementById('bed-search-input').value = '';
    applyFilters();
  }

  function setWardFilter(ward) {
    currentFilterWard = ward;
    ['all', 'opd', 'icu'].forEach(w => {
      const btn = document.getElementById(`filter-ward-${w}`);
      if (btn) {
        btn.className = w.toLowerCase() === ward.toLowerCase() ? "px-2.5 sm:px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 font-bold transition" : "px-2.5 sm:px-3 py-1.5 rounded-lg hover:text-slate-900 transition";
      }
    });

    const opdSec = document.getElementById('section-ward-opd');
    const icuSec = document.getElementById('section-ward-icu');
    if (opdSec) opdSec.classList.toggle('hidden', ward === 'ICU');
    if (icuSec) icuSec.classList.toggle('hidden', ward === 'OPD');

    applyFilters();
  }

  function setBedDisplayMode(mode) {
    displayMode = mode;
    ['slider', 'grid', 'table'].forEach(m => {
      const btn = document.getElementById(`btn-bed-view-${m}`);
      const cont = document.getElementById(`bed-container-${m}`);
      if (btn) {
        btn.className = m === mode ? "px-2.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 font-bold flex items-center gap-1.5 transition" : "px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 font-bold flex items-center gap-1.5 transition";
      }
      if (cont) {
        cont.classList.toggle('hidden', m !== mode);
      }
    });

    if (mode === 'slider') {
      renderSliderBed();
    }
  }

  // =========================================================================
  // ================= BIG BOX SLIDER CONTROLS & GESTURES ====================
  // =========================================================================
  function navigateSlider(delta) {
    if (currentFilteredBeds.length === 0) return;
    currentSliderIndex = (currentSliderIndex + delta + currentFilteredBeds.length) % currentFilteredBeds.length;
    renderSliderBed();
  }

  function jumpToSliderBed(idx) {
    if (idx >= 0 && idx < currentFilteredBeds.length) {
      currentSliderIndex = idx;
      renderSliderBed();
    }
  }

  function toggleBedBoxFullScreen() {
    const slider = document.getElementById('bed-container-slider');
    const icon = document.getElementById('icon-mobile-fs');
    const text = document.getElementById('text-mobile-fs');
    if (!slider) return;

    const isFs = slider.classList.toggle('bed-box-fullscreen');
    slider.classList.toggle('mobile-fullscreen-active', isFs);

    if (icon) icon.className = isFs ? 'fa-solid fa-compress text-xs' : 'fa-solid fa-expand text-xs';
    if (text) text.textContent = isFs ? 'Exit Fullscreen' : 'Fullscreen';

    const fsBtn = document.getElementById('btn-mobile-fs');
    if (fsBtn) {
      if (isFs) {
        fsBtn.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-300');
        fsBtn.classList.remove('bg-slate-100', 'text-slate-700');
        document.body.classList.add('overflow-hidden');
      } else {
        fsBtn.classList.remove('bg-rose-50', 'text-rose-700', 'border-rose-300');
        fsBtn.classList.add('bg-slate-100', 'text-slate-700');
        document.body.classList.remove('overflow-hidden');
      }
    }

    // Try HTML5 Fullscreen API
    if (isFs) {
      if (!document.fullscreenElement && slider.requestFullscreen) {
        slider.requestFullscreen().catch(() => {});
      }
    } else {
      if (document.fullscreenElement && document.exitFullscreen) {
        document.exitFullscreen().catch(() => {});
      }
    }
  }

  // Alias for backward compatibility
  window.toggleMobileFullScreen = toggleBedBoxFullScreen;

  // Listen to native exit or Escape
  document.addEventListener('fullscreenchange', () => {
    const slider = document.getElementById('bed-container-slider');
    if (!slider) return;
    if (!document.fullscreenElement && slider.classList.contains('bed-box-fullscreen')) {
      slider.classList.remove('bed-box-fullscreen', 'mobile-fullscreen-active');
      document.body.classList.remove('overflow-hidden');
      const icon = document.getElementById('icon-mobile-fs');
      const text = document.getElementById('text-mobile-fs');
      if (icon) icon.className = 'fa-solid fa-expand text-xs';
      if (text) text.textContent = 'Fullscreen';
      const fsBtn = document.getElementById('btn-mobile-fs');
      if (fsBtn) {
        fsBtn.classList.remove('bg-rose-50', 'text-rose-700', 'border-rose-300');
        fsBtn.classList.add('bg-slate-100', 'text-slate-700');
      }
    }
  });

  // 3-Dots Menu Dropdown Controls
  function toggleBedOptionsMenu(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('bed-options-dropdown');
    if (menu) menu.classList.toggle('hidden');
  }

  function closeBedOptionsMenu() {
    const menu = document.getElementById('bed-options-dropdown');
    if (menu) menu.classList.add('hidden');
  }

  document.addEventListener('click', (e) => {
    const menu = document.getElementById('bed-options-dropdown');
    const btn = document.getElementById('btn-bed-options-menu');
    if (menu && !menu.classList.contains('hidden')) {
      if (!menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
        menu.classList.add('hidden');
      }
    }
  });

  function setupSliderTouchGestures() {
    let touchStartX = 0;
    let touchStartY = 0;
    const sliderEl = document.getElementById('bed-container-slider');
    if (!sliderEl) return;

    sliderEl.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    sliderEl.addEventListener('touchend', (e) => {
      const touchEndX = e.changedTouches[0].screenX;
      const touchEndY = e.changedTouches[0].screenY;
      const diffX = touchEndX - touchStartX;
      const diffY = touchEndY - touchStartY;

      // Detect horizontal swipe > 45px
      if (Math.abs(diffX) > 45 && Math.abs(diffX) > Math.abs(diffY)) {
        if (diffX < 0) {
          navigateSlider(1); // Swipe left -> Next bed
        } else {
          navigateSlider(-1); // Swipe right -> Prev bed
        }
      }
    }, { passive: true });
  }

  // Keyboard navigation for slider (< and > arrow keys)
  window.addEventListener('keydown', (e) => {
    if (!isAnyModalOpen() && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      if (e.key === 'ArrowLeft') {
        navigateSlider(-1);
      } else if (e.key === 'ArrowRight') {
        navigateSlider(1);
      }
    }
  });

  // =========================================================================
  // ================= RENDER THE BIG BOX BED STATION SLIDER =================
  // =========================================================================
  function renderSliderBed() {
    const container = document.getElementById('slider-deck-card-content');
    const counterEl = document.getElementById('slider-counter');
    const badgeEl = document.getElementById('slider-bed-badge');
    const wardEl = document.getElementById('slider-ward-title');
    const statusPill = document.getElementById('slider-status-pill');
    const wingSub = document.getElementById('slider-wing-sub');
    const trackStrip = document.getElementById('slider-track-strip');
    const actionSlot = document.getElementById('slider-header-action-slot');

    if (!container) return;

    if (currentFilteredBeds.length === 0) {
      counterEl.textContent = '0 Beds';
      badgeEl.textContent = '--';
      wardEl.textContent = 'No Beds Found';
      statusPill.className = 'hidden';
      wingSub.textContent = 'Adjust search query or ward filter';
      container.innerHTML = `
        <div class="py-16 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
          <i class="fa-solid fa-bed text-3xl text-slate-300 block mb-2"></i>
          No inpatient beds found matching criteria.
        </div>
      `;
      trackStrip.innerHTML = '';
      if (actionSlot) actionSlot.innerHTML = '';
      return;
    }

    const bed = currentFilteredBeds[currentSliderIndex];
    const isOcc = bed.status === 'Occupied';
    const isIcu = bed.type === 'ICU';
    const fullName = isOcc ? `${bed.name || ''} ${bed.surname || ''}`.trim() : 'Vacant Bed';
    const initials = isOcc ? ((bed.name ? bed.name.charAt(0) : '') + (bed.surname ? bed.surname.charAt(0) : 'P')).toUpperCase() : '';

    let age = bed.age || '', gender = bed.gender || '', blood = bed.blood_group || '';
    if ((!age || age === '--') && bed.demographics) {
      const parts = bed.demographics.split(',').map(s => s.trim());
      parts.forEach(p => {
        if (p.endsWith('Y') || p.endsWith('y') || !isNaN(p)) age = p;
        if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
        if (p.includes('+') || p.includes('-')) blood = p;
      });
    }

    const docDisplay = bed.doctor_name ? `Dr. ${bed.doctor_name}` : 'General Duty';
    const latestImg = bed.latest_image;
    const latestObs = bed.latest_observation;
    const recentImages = bed.recent_images || [];
    const recentFiles = bed.recent_files || [];
    const imgCount = bed.images_count || recentImages.length || 0;

    // Update Slider Header
    counterEl.textContent = `Bed ${currentSliderIndex + 1} of ${currentFilteredBeds.length}`;
    badgeEl.textContent = bed.bed_number;
    badgeEl.className = `px-3 py-1.5 rounded-xl text-white font-mono font-black text-sm tracking-wide shadow-xs shrink-0 ${isIcu ? 'bg-rose-600' : 'bg-blue-600'}`;
    wardEl.textContent = `${bed.type} Ward - ${bed.bed_number}`;
    wingSub.textContent = bed.wing || (isIcu ? 'Critical Care Wing, 3rd Floor' : 'Observation Ward, 2nd Floor');

    statusPill.classList.remove('hidden');
    if (isOcc) {
      statusPill.className = `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${isIcu ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-100 text-blue-800 border border-blue-200'} shrink-0`;
      statusPill.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${isIcu ? 'bg-rose-600' : 'bg-blue-600'} animate-pulse"></span><span>Occupied</span>`;
      if (actionSlot) {
        actionSlot.innerHTML = `
          <button onclick="quickSnapBedCamera('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black px-3.5 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5 active:scale-95" title="Snap Monitor">
            <i class="fa-solid fa-camera text-xs"></i>
            <span class="hidden sm:inline">Snap Monitor</span>
          </button>
        `;
      }
    } else {
      statusPill.className = `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0`;
      statusPill.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span><span>Available</span>`;
      if (actionSlot) {
        actionSlot.innerHTML = `
          <button onclick="openQuickAdmitModal('${bed.bed_number}')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black px-3.5 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5 active:scale-95" title="Admit Patient">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span class="hidden sm:inline">Admit Patient</span>
          </button>
        `;
      }
    }

    // Render Quick-Track Bed Pills
    trackStrip.innerHTML = currentFilteredBeds.map((b, idx) => {
      const bOcc = b.status === 'Occupied';
      const bIcu = b.type === 'ICU';
      const isActive = idx === currentSliderIndex;

      let cls = "shrink-0 px-3 py-1 rounded-xl text-xs font-mono font-bold transition flex items-center gap-1.5 cursor-pointer ";
      if (isActive) {
        cls += "bg-slate-900 text-white shadow-xs ring-2 ring-indigo-400";
      } else if (bOcc) {
        cls += bIcu ? "bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100" : "bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100";
      } else {
        cls += "bg-white text-slate-600 border border-slate-200 hover:border-emerald-300";
      }

      return `
        <button onclick="jumpToSliderBed(${idx})" id="slider-pill-${idx}" class="${cls}" title="Jump to Bed ${escapeHtml(b.bed_number)}">
          <span class="w-1.5 h-1.5 rounded-full ${bOcc ? (bIcu ? 'bg-rose-600' : 'bg-blue-600') : 'bg-emerald-500'}"></span>
          <span>${escapeHtml(b.bed_number)}</span>
        </button>
      `;
    }).join('');

    // Smoothly scroll active pill into view
    const activePill = document.getElementById(`slider-pill-${currentSliderIndex}`);
    if (activePill) {
      activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    // Render Full Bed Details Inside the Big Box
    if (!isOcc) {
      // Clean Vacant Bed inside Big Box
      container.innerHTML = `
        <div class="py-12 sm:py-20 text-center flex flex-col items-center justify-center max-w-md mx-auto space-y-4">
          <div class="w-20 h-20 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl font-black border border-emerald-200 shadow-sm animate-bounce duration-1000">
            <i class="fa-solid fa-bed"></i>
          </div>
          <div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900">Bed ${escapeHtml(bed.bed_number)} is Vacant</h3>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">This bed in ${escapeHtml(bed.type)} Ward (${escapeHtml(bed.wing || 'Floor')}) is sanitized and ready for immediate inpatient admission.</p>
          </div>
          <div class="pt-2 flex items-center gap-3 w-full justify-center">
            <button onclick="openQuickAdmitModal('${bed.bed_number}')" class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 active:scale-95">
              <i class="fa-solid fa-user-plus"></i>
              <span>Admit Patient Here</span>
            </button>
            <button onclick="deleteBed('${bed.bed_number}')" class="p-3 rounded-2xl border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Bed">
              <i class="fa-solid fa-trash text-sm"></i>
            </button>
          </div>
        </div>
      `;
      return;
    }

    // Occupied Bed inside Big Box: Highlighted Patient & Bed Header, and Bigger Highlighted Image in Phone View
    container.innerHTML = `
      <!-- ================= 1. PROMINENT HIGHLIGHTED BED & PATIENT BANNER ================= -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-3 sm:p-4 rounded-2xl flex items-center justify-between gap-3 shadow-sm border border-slate-700/60 mb-3.5 sm:mb-5">
        <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0 flex-1">
          <!-- Large High-Contrast Bed Number Badge -->
          <div class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl ${isIcu ? 'bg-rose-600' : 'bg-blue-600'} text-white font-mono font-black text-base sm:text-xl tracking-wider shadow-sm shrink-0 border border-white/20">
            ${escapeHtml(bed.bed_number)}
          </div>
          
          <!-- Large Highlighted Patient Name & Vital Demographics -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <button onclick="openPatientDossier('${bed.patient_id}', '${bed.bed_number}')" class="text-base sm:text-xl font-black text-white hover:text-indigo-300 transition truncate text-left" title="Click to view full dossier">
                ${escapeHtml(fullName)}
              </button>
              <span class="font-mono text-[11px] sm:text-xs text-slate-200 bg-white/10 px-2 py-0.5 rounded border border-white/10 font-bold shrink-0">
                ${escapeHtml(bed.patient_id || 'IPD')}
              </span>
              ${blood ? `<span class="px-2 py-0.5 rounded bg-rose-500/30 text-rose-200 text-[11px] font-mono font-black border border-rose-500/40 shrink-0">🩸 ${escapeHtml(blood)}</span>` : ''}
            </div>
            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-slate-300 font-semibold mt-0.5 truncate flex-wrap">
              <span>${escapeHtml(bed.type)} Ward</span>
              <span>•</span>
              <span>${age ? age + 'Y' : ''} ${gender || ''}</span>
              <span>•</span>
              <span class="text-indigo-300"><i class="fa-solid fa-user-doctor mr-1"></i>${escapeHtml(docDisplay)}</span>
            </div>
          </div>
        </div>

        <!-- Quick Patient Dossier Button -->
        <button onclick="openPatientDossier('${bed.patient_id}', '${bed.bed_number}')" class="shrink-0 p-2 sm:px-3 sm:py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition border border-white/15 flex items-center gap-1.5" title="View Patient Dossier">
          <i class="fa-solid fa-folder-open text-xs"></i>
          <span class="hidden sm:inline">Dossier</span>
        </button>
      </div>

      <!-- ================= 2. MAIN HORIZON LAYOUT (OPTIMIZED FOR PHONE & DESKTOP) ================= -->
      <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
        
        <!-- ================= RIGHT COLUMN / ON PHONE ORDER-1: BIG HIGHLIGHTED MONITOR IMAGE & REPORTS ================= -->
        <div class="w-full lg:w-7/12 xl:w-8/12 flex flex-col justify-between space-y-4 order-1 lg:order-2">
          
          <!-- Machine Monitor Feed Preview Box (Highlighted & Bigger in Phone View) -->
          <div>
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-wider text-slate-800">Bedside Machine Monitor Snapshot</span>
              </div>
              <div class="flex items-center gap-2 text-xs shrink-0">
                ${latestImg ? `
                  <span class="font-mono text-[11px] text-slate-500 font-bold">🕒 ${escapeHtml(latestImg.formatted_time || 'Latest Photo')}</span>
                  <button onclick="openImagePreview('${latestImg.image_path}', 'Bed ${escapeHtml(bed.bed_number)}: ${escapeHtml(latestImg.title)}')" class="p-1 px-2.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition flex items-center gap-1 font-bold text-xs" title="Zoom In Preview">
                    <i class="fa-solid fa-magnifying-glass-plus"></i> Zoom Full
                  </button>
                ` : `
                  <span class="text-[11px] text-slate-400 italic">No snapshot recorded</span>
                `}
              </div>
            </div>

            <!-- BIG Highlighted Monitor Screen Box (Large on mobile & desktop) -->
            <div class="rounded-2xl overflow-hidden border-2 border-indigo-200/90 shadow-md bg-slate-950 relative group/img">
              ${latestImg ? `
                <div class="relative w-full h-64 sm:h-80 lg:h-[340px] xl:h-[380px] cursor-pointer bg-slate-950 flex items-center justify-center overflow-hidden"
                     onclick="openImagePreview('${latestImg.image_path}', 'Bed ${escapeHtml(bed.bed_number)}: ${escapeHtml(latestImg.title)}')">
                  <img src="${latestImg.image_path}" class="w-full h-full object-contain group-hover/img:scale-102 transition duration-300" alt="Machine Monitor Screen">
                  <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover/img:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-2">
                    <div class="px-4 py-2 rounded-xl bg-indigo-600/95 backdrop-blur-xs flex items-center gap-2 shadow-lg">
                      <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                      <span>Tap to Zoom Full Screen</span>
                    </div>
                  </div>
                  <!-- Overlay Badges -->
                  <div class="absolute bottom-2.5 left-2.5 px-3 py-1 rounded-xl bg-black/85 backdrop-blur-xs text-[11px] font-mono text-white flex items-center gap-2 max-w-[85%] truncate border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="truncate">${escapeHtml(latestImg.title || 'Bedside Monitor Screen')}</span>
                  </div>
                  <div class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-black/85 text-[11px] font-bold text-amber-300 border border-white/10 flex items-center gap-1.5">
                    <span>📸 ${imgCount} Photos</span>
                  </div>
                </div>
              ` : `
                <div onclick="quickSnapBedCamera('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="w-full h-52 sm:h-64 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/70 hover:bg-white flex flex-col items-center justify-center text-center p-4 cursor-pointer transition">
                  <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-2.5 shadow-sm border border-indigo-100">
                    <i class="fa-solid fa-camera"></i>
                  </div>
                  <span class="text-sm font-black text-slate-800">No Machine Monitor Photo Yet</span>
                  <span class="text-xs text-slate-400 mt-1 max-w-sm">Tap here or 'Snap Monitor' to upload phone camera photo of bedside monitor</span>
                </div>
              `}
            </div>
          </div>

          <!-- Bottom Section: Hourly Snapshots & Medical Reports Strip -->
          <div class="space-y-3 pt-2 border-t border-slate-100">
            <!-- Hourly Snapshots Strip -->
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-extrabold text-slate-700 flex items-center gap-1.5">
                  <i class="fa-solid fa-camera text-rose-600 text-xs"></i>
                  <span>Hourly Monitor History (${recentImages.length})</span>
                </span>
                <button onclick="openObsHistoryModal('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="text-[11px] text-indigo-600 hover:underline font-bold">
                  View Full Timeline →
                </button>
              </div>

              ${recentImages.length > 0 ? `
                <div class="touch-scroll flex items-center gap-2 pb-1.5">
                  ${recentImages.map(img => `
                    <div class="w-28 sm:w-32 shrink-0 relative rounded-xl overflow-hidden aspect-video bg-slate-950 cursor-pointer group border border-slate-200 shadow-2xs"
                         onclick="openImagePreview('${escapeHtml(img.image_path)}', '${escapeHtml(img.title)}')">
                      <img src="${escapeHtml(img.thumbnail_path || img.image_path)}" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" alt="Snapshot">
                      <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px]">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                      </div>
                      <span class="absolute bottom-1 right-1 px-1 rounded bg-black/75 text-[9px] text-white font-mono">${escapeHtml(img.formatted_time || '')}</span>
                    </div>
                  `).join('')}
                </div>
              ` : `
                <p class="text-[11px] text-slate-400 italic">No previous hourly photos recorded for this stay.</p>
              `}
            </div>

            <!-- Attached Reports & Scans Strip WITH IMAGE PREVIEWS -->
            ${recentFiles.length > 0 ? `
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1.5">Inpatient Diagnostic Reports &amp; Scans (${recentFiles.length}):</span>
                <div class="touch-scroll flex items-center gap-2 pb-1.5">
                  ${recentFiles.map(f => {
                    const fileUrl = f.url || f.file_path || `api/file.php?id=${f.id}`;
                    const thumbUrl = f.thumb_url || f.thumbnail_path || fileUrl;
                    const isImg = f.is_image !== false;

                    return `
                      <div onclick="openImagePreview('${fileUrl}', '${escapeHtml(f.title)}', ${isImg})" class="shrink-0 flex items-center gap-2 sm:gap-2.5 p-1.5 pr-2.5 sm:pr-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-indigo-300 cursor-pointer transition text-xs shadow-2xs group">
                        <div class="w-11 h-11 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200 relative">
                          ${isImg ? `
                            <img src="${thumbUrl}" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="Preview">
                            <div class="hidden w-full h-full items-center justify-center bg-indigo-50 text-indigo-600 text-xs">
                              <i class="fa-solid fa-file-waveform"></i>
                            </div>
                          ` : `
                            <div class="w-full h-full flex items-center justify-center bg-rose-50 text-rose-600 text-xs">
                              <i class="fa-solid fa-file-pdf"></i>
                            </div>
                          `}
                        </div>
                        <div class="max-w-[120px] sm:max-w-[140px] truncate">
                          <span class="font-bold text-slate-800 block truncate text-[11px] group-hover:text-indigo-600 transition">${escapeHtml(f.title)}</span>
                          <span class="text-[9px] text-slate-400 font-mono">${escapeHtml(f.formatted_date || f.file_date || '')}</span>
                        </div>
                      </div>
                    `;
                  }).join('')}
                </div>
              </div>
            ` : ''}

          </div>

        </div>

        <!-- ================= LEFT COLUMN / ON PHONE ORDER-2: Patient Vitals, Diagnosis & Fast Actions ================= -->
        <div class="w-full lg:w-5/12 xl:w-4/12 flex flex-col justify-between space-y-4 order-2 lg:order-1">
          
          <div class="space-y-3.5 sm:space-y-4">
            
            <!-- Highlighted Vitals Grid (2x2 on Mobile, 4 Cols on Tablet/PC) -->
            <div>
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1.5">Current Vital Signs</span>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                <!-- BP -->
                <div class="bg-rose-50/60 p-2.5 rounded-2xl border border-rose-200 shadow-2xs">
                  <span class="text-[9px] text-rose-600 font-extrabold block uppercase tracking-wider">BP</span>
                  <span class="font-black text-rose-700 text-sm sm:text-base mt-0.5 block">${latestObs && latestObs.vital_bp ? escapeHtml(latestObs.vital_bp) : '--'}</span>
                </div>
                <!-- Pulse -->
                <div class="bg-emerald-50/60 p-2.5 rounded-2xl border border-emerald-200 shadow-2xs">
                  <span class="text-[9px] text-emerald-600 font-extrabold block uppercase tracking-wider">Pulse</span>
                  <span class="font-black text-emerald-700 text-sm sm:text-base mt-0.5 block">${latestObs && latestObs.vital_pulse ? escapeHtml(latestObs.vital_pulse) : '--'}</span>
                </div>
                <!-- SpO2 -->
                <div class="bg-blue-50/60 p-2.5 rounded-2xl border border-blue-200 shadow-2xs">
                  <span class="text-[9px] text-blue-600 font-extrabold block uppercase tracking-wider">SpO2</span>
                  <span class="font-black text-blue-700 text-sm sm:text-base mt-0.5 block">${latestObs && latestObs.vital_spo2 ? escapeHtml(latestObs.vital_spo2) : '--'}</span>
                </div>
                <!-- Temp -->
                <div class="bg-amber-50/60 p-2.5 rounded-2xl border border-amber-200 shadow-2xs">
                  <span class="text-[9px] text-amber-600 font-extrabold block uppercase tracking-wider">Temp</span>
                  <span class="font-black text-amber-700 text-sm sm:text-base mt-0.5 block">${latestObs && latestObs.vital_temp ? escapeHtml(latestObs.vital_temp) + '°' : '--'}</span>
                </div>
              </div>
            </div>

            <!-- Attending Doctor & Admission Date (Highlighted) -->
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 space-y-2 text-xs text-slate-700 shadow-2xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-bold">Attending Doctor:</span>
                <span class="font-bold text-indigo-700 truncate max-w-[200px]"><i class="fa-solid fa-user-doctor text-indigo-500 mr-1"></i>${escapeHtml(docDisplay)}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-bold">Admission Date:</span>
                <span class="font-mono text-slate-700 font-bold">${bed.admit_date || 'Today'}</span>
              </div>
              ${bed.diagnoses_list || bed.symptoms ? `
                <div class="pt-2 border-t border-slate-100 text-[11px]">
                  <span class="text-slate-400 font-bold block mb-0.5">Clinical Diagnosis &amp; Symptoms:</span>
                  <div class="bg-slate-50 p-2 rounded-xl text-slate-900 font-semibold border border-slate-200/70">
                    ${escapeHtml(bed.diagnoses_list || bed.symptoms)}
                  </div>
                </div>
              ` : ''}
            </div>

          </div>

          <!-- Fast Action Buttons Strip -->
          <div class="pt-2 border-t border-slate-200/80 flex items-center gap-2">
            <button onclick="quickSnapBedCamera('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="flex-1 min-h-[42px] bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-2.5 px-3.5 rounded-xl shadow-xs transition flex items-center justify-center gap-2 active:scale-95" title="Take Instant Monitor Photo">
              <i class="fa-solid fa-camera text-sm"></i>
              <span>Snap Monitor</span>
            </button>

            <button onclick="openObsHistoryModal('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="min-h-[42px] min-w-[42px] p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Hourly History &amp; Snapshots">
              <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
            </button>

            <button onclick="openTransferModal('${bed.bed_number}', '${escapeHtml(fullName)}')" class="min-h-[42px] min-w-[42px] p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Transfer Bed">
              <i class="fa-solid fa-arrows-turn-to-dots text-slate-500"></i>
            </button>

            <button onclick="promptDischarge('${bed.bed_number}', '${escapeHtml(fullName)}')" class="min-h-[42px] min-w-[42px] p-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Discharge Inpatient">
              <i class="fa-solid fa-person-walking-arrow-right"></i>
            </button>
          </div>

        </div>

      </div>
    `;
  }

  // =========================================================================
  // ============ ALL BEDS CARDS MATRIX RENDERER =============================
  // =========================================================================
  function renderStationMatrix(beds) {
    const opdBeds = beds.filter(b => b.type === 'OPD');
    const icuBeds = beds.filter(b => b.type === 'ICU');

    const opdMatrix = document.getElementById('opd-bed-matrix');
    const icuMatrix = document.getElementById('icu-bed-matrix');

    if (opdMatrix) {
      opdMatrix.innerHTML = '';
      if (opdBeds.length === 0) {
        opdMatrix.innerHTML = `<div class="py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">No OPD beds matching criteria.</div>`;
      } else {
        opdBeds.forEach(bed => opdMatrix.appendChild(createStationBedCard(bed)));
      }
    }

    if (icuMatrix) {
      icuMatrix.innerHTML = '';
      if (icuBeds.length === 0) {
        icuMatrix.innerHTML = `<div class="py-8 text-center text-xs text-rose-400 bg-rose-50/50 rounded-2xl border border-dashed border-rose-200">No ICU beds matching criteria.</div>`;
      } else {
        icuBeds.forEach(bed => icuMatrix.appendChild(createStationBedCard(bed)));
      }
    }
  }

  function createStationBedCard(bed) {
    const isOcc = bed.status === 'Occupied';
    const isIcu = bed.type === 'ICU';
    const card = document.createElement('div');

    const fullName = isOcc ? `${bed.name || ''} ${bed.surname || ''}`.trim() : 'Vacant Bed';
    const initials = isOcc ? ((bed.name ? bed.name.charAt(0) : '') + (bed.surname ? bed.surname.charAt(0) : 'P')).toUpperCase() : '';

    let age = bed.age || '', gender = bed.gender || '', blood = bed.blood_group || '';
    if ((!age || age === '--') && bed.demographics) {
      const parts = bed.demographics.split(',').map(s => s.trim());
      parts.forEach(p => {
        if (p.endsWith('Y') || p.endsWith('y') || !isNaN(p)) age = p;
        if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
        if (p.includes('+') || p.includes('-')) blood = p;
      });
    }

    const docDisplay = bed.doctor_name ? `Dr. ${bed.doctor_name}` : 'General Duty';
    const latestImg = bed.latest_image;
    const latestObs = bed.latest_observation;
    const recentImages = bed.recent_images || [];
    const recentFiles = bed.recent_files || [];
    const imgCount = bed.images_count || recentImages.length || 0;

    if (!isOcc) {
      card.className = "rounded-3xl p-3.5 sm:p-5 border border-slate-200 bg-white hover:border-emerald-300 shadow-xs transition flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4";
      card.innerHTML = `
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm shrink-0 border border-emerald-100">
            <i class="fa-solid fa-bed"></i>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono font-black text-sm text-slate-900">${escapeHtml(bed.bed_number)}</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">Available</span>
              <span class="text-[11px] text-slate-400 font-bold truncate">${escapeHtml(bed.wing || (isIcu ? 'ICU Ward' : 'OPD Ward'))}</span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5 truncate">Sanitized &amp; ready for immediate admission.</p>
          </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <button onclick="openQuickAdmitModal('${bed.bed_number}')" class="flex-1 sm:flex-none px-4 sm:px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm shadow-emerald-600/20 transition flex items-center justify-center gap-2 active:scale-95 min-h-[40px]">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Admit Patient</span>
          </button>
          <button onclick="deleteBed('${bed.bed_number}')" class="min-h-[40px] min-w-[40px] p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-100 flex items-center justify-center" title="Delete Bed">
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        </div>
      `;
      return card;
    }

    const borderCls = isIcu ? 'border-rose-200/90 bg-white hover:border-rose-300' : 'border-slate-200/90 bg-white hover:border-blue-300';
    card.className = `rounded-3xl border shadow-xs transition overflow-hidden ${borderCls}`;

    card.innerHTML = `
      <div class="flex flex-col lg:flex-row">
        
        <!-- LEFT SIDE: Highlighted Patient Profile, Vitals & Fast Actions -->
        <div class="w-full lg:w-5/12 xl:w-5/12 p-3.5 sm:p-5 ${isIcu ? 'bg-rose-50/25' : 'bg-slate-50/50'} border-b lg:border-b-0 lg:border-r ${isIcu ? 'border-rose-100' : 'border-slate-100'} flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b ${isIcu ? 'border-rose-100' : 'border-slate-200/80'}">
              <div class="flex items-center gap-2 min-w-0">
                <span class="font-mono font-black text-xs px-2.5 py-1 rounded-xl ${isIcu ? 'bg-rose-600 text-white shadow-xs' : 'bg-blue-600 text-white shadow-xs'} shrink-0">
                  ${escapeHtml(bed.bed_number)}
                </span>
                <span class="text-[11px] text-slate-500 font-bold truncate max-w-[150px]">${escapeHtml(bed.wing || (isIcu ? 'ICU Floor' : 'OPD Ward'))}</span>
              </div>

              <div class="flex items-center gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${isIcu ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-100 text-blue-800 border border-blue-200'}">
                  <span class="w-1.5 h-1.5 rounded-full ${isIcu ? 'bg-rose-600' : 'bg-blue-600'} animate-pulse"></span>
                  <span>Occupied</span>
                </span>
                <button onclick="openFullscreenBedStation('${bed.bed_number}')" class="p-1.5 px-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs transition" title="Open Full View">
                  <i class="fa-solid fa-expand"></i>
                </button>
              </div>
            </div>

            <div class="flex items-start gap-3 mb-3">
              <div class="w-11 h-11 rounded-2xl ${isIcu ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700'} flex items-center justify-center font-black text-sm shrink-0 border ${isIcu ? 'border-rose-200' : 'border-indigo-200'}">
                ${initials || 'PT'}
              </div>
              <div class="min-w-0 flex-1">
                <button onclick="openPatientDossier('${bed.patient_id}', '${bed.bed_number}')" class="text-sm sm:text-base font-black text-slate-900 hover:text-indigo-600 transition truncate block text-left w-full" title="Click to view patient profile">
                  ${escapeHtml(fullName)}
                </button>
                <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 font-bold flex-wrap">
                  <span class="font-mono text-slate-600 bg-white px-1.5 py-0.5 rounded border border-slate-200">${escapeHtml(bed.patient_id)}</span>
                  <span>${age ? age + 'Y' : ''} ${gender || ''}</span>
                  ${blood ? `<span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 font-mono font-black border border-rose-100">🩸 ${escapeHtml(blood)}</span>` : ''}
                </div>
              </div>
            </div>

            <div class="bg-white p-2.5 rounded-2xl border border-slate-200/80 space-y-1.5 text-xs text-slate-700 mb-3 shadow-2xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 font-bold">Attending:</span>
                <span class="font-bold text-indigo-700 truncate max-w-[190px]"><i class="fa-solid fa-user-doctor text-indigo-500 mr-1"></i>${escapeHtml(docDisplay)}</span>
              </div>
              ${bed.diagnoses_list || bed.symptoms ? `
                <div class="pt-1 border-t border-slate-100 text-[11px] flex items-start gap-1">
                  <span class="text-slate-400 font-bold shrink-0">Diagnosis:</span>
                  <span class="text-slate-900 font-semibold truncate block">${escapeHtml(bed.diagnoses_list || bed.symptoms)}</span>
                </div>
              ` : ''}
            </div>

            <div class="mb-3">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-1.5 text-center text-xs">
                <div class="bg-white p-2 rounded-xl border border-rose-100 shadow-2xs">
                  <span class="text-[9px] text-rose-500 font-bold block uppercase tracking-wider">BP</span>
                  <span class="font-black text-rose-700 text-xs sm:text-sm">${latestObs && latestObs.vital_bp ? escapeHtml(latestObs.vital_bp) : '--'}</span>
                </div>
                <div class="bg-white p-2 rounded-xl border border-emerald-100 shadow-2xs">
                  <span class="text-[9px] text-emerald-500 font-bold block uppercase tracking-wider">Pulse</span>
                  <span class="font-black text-emerald-700 text-xs sm:text-sm">${latestObs && latestObs.vital_pulse ? escapeHtml(latestObs.vital_pulse) : '--'}</span>
                </div>
                <div class="bg-white p-2 rounded-xl border border-blue-100 shadow-2xs">
                  <span class="text-[9px] text-blue-500 font-bold block uppercase tracking-wider">SpO2</span>
                  <span class="font-black text-blue-700 text-xs sm:text-sm">${latestObs && latestObs.vital_spo2 ? escapeHtml(latestObs.vital_spo2) : '--'}</span>
                </div>
                <div class="bg-white p-2 rounded-xl border border-amber-100 shadow-2xs">
                  <span class="text-[9px] text-amber-500 font-bold block uppercase tracking-wider">Temp</span>
                  <span class="font-black text-amber-700 text-xs sm:text-sm">${latestObs && latestObs.vital_temp ? escapeHtml(latestObs.vital_temp) + '°' : '--'}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-2.5 border-t border-slate-200/80 flex items-center gap-1.5 sm:gap-2">
            <button onclick="quickSnapBedCamera('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="flex-1 min-h-[40px] bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-2 px-3 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 active:scale-95" title="Take Instant Monitor Photo">
              <i class="fa-solid fa-camera text-xs"></i>
              <span class="truncate">Snap Monitor</span>
            </button>

            <button onclick="openFullscreenBedStation('${bed.bed_number}')" class="min-h-[40px] min-w-[40px] p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Open Full View">
              <i class="fa-solid fa-expand text-slate-600"></i>
            </button>

            <button onclick="openObsHistoryModal('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="min-h-[40px] min-w-[40px] p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Hourly History &amp; Snapshots">
              <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
            </button>

            <button onclick="openTransferModal('${bed.bed_number}', '${escapeHtml(fullName)}')" class="min-h-[40px] min-w-[40px] p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Transfer Bed">
              <i class="fa-solid fa-arrows-turn-to-dots text-slate-500"></i>
            </button>

            <button onclick="promptDischarge('${bed.bed_number}', '${escapeHtml(fullName)}')" class="min-h-[40px] min-w-[40px] p-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shadow-2xs flex items-center justify-center" title="Discharge Inpatient">
              <i class="fa-solid fa-person-walking-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- RIGHT SIDE: Machine Monitor Feed & Reports WITH PREVIEWS -->
        <div class="w-full lg:w-7/12 xl:w-7/12 p-3.5 sm:p-5 flex flex-col justify-between space-y-3.5">
          <div>
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-wider text-slate-800 truncate">Machine Monitor Display</span>
              </div>
              <div class="flex items-center gap-2 text-xs shrink-0">
                ${latestImg ? `
                  <span class="font-mono text-[11px] text-slate-500 font-bold">🕒 ${latestImg.formatted_time || 'Latest Photo'}</span>
                  <button onclick="openImagePreview('${latestImg.image_path}', 'Bed ${escapeHtml(bed.bed_number)}: ${escapeHtml(latestImg.title)}')" class="p-1 rounded text-slate-400 hover:text-slate-800 transition" title="Zoom in">
                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                  </button>
                ` : `
                  <span class="text-[11px] text-slate-400 italic">No snapshot recorded</span>
                `}
              </div>
            </div>

            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-900/5 relative">
              ${latestImg ? `
                <div class="relative aspect-video sm:aspect-[21/9] rounded-2xl overflow-hidden group/img cursor-pointer bg-slate-950"
                     onclick="openImagePreview('${latestImg.image_path}', 'Bed ${escapeHtml(bed.bed_number)}: ${escapeHtml(latestImg.title)}')">
                  <img src="${latestImg.image_path}" class="w-full h-full object-contain group-hover/img:scale-102 transition duration-300" alt="Machine Monitor Screen">
                  <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover/img:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5">
                    <i class="fa-solid fa-magnifying-glass-plus text-sm"></i> Click to Zoom Full Resolution
                  </div>
                  <div class="absolute bottom-2 left-2 px-2.5 py-1 rounded-xl bg-slate-900/80 backdrop-blur-xs text-[11px] font-mono text-white flex items-center gap-1.5 max-w-[80%] truncate">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="truncate">${latestImg.title || 'Monitor Screen'}</span>
                  </div>
                  <div class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-slate-900/80 text-[10px] font-bold text-amber-300">
                    📸 ${imgCount} Photos
                  </div>
                </div>
              ` : `
                <div onclick="quickSnapBedCamera('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="aspect-video sm:aspect-[21/9] rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/70 hover:bg-white flex flex-col items-center justify-center text-center p-3 sm:p-4 cursor-pointer transition">
                  <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-1.5 shadow-2xs">
                    <i class="fa-solid fa-camera"></i>
                  </div>
                  <span class="text-xs sm:text-sm font-black text-slate-800">No Monitor Photo Yet</span>
                  <span class="text-[11px] text-slate-400 mt-0.5 max-w-xs">Click here or tap 'Snap Monitor' to upload phone camera photo of vitals monitor</span>
                </div>
              `}
            </div>
          </div>

          <div class="space-y-3 pt-2 border-t border-slate-100">
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-extrabold text-slate-700 flex items-center gap-1.5">
                  <i class="fa-solid fa-camera text-rose-600 text-xs"></i>
                  <span>Hourly Monitor History (${recentImages.length})</span>
                </span>
                <button onclick="openObsHistoryModal('${bed.bed_number}', '${bed.patient_id}', '${escapeHtml(fullName)}')" class="text-[11px] text-indigo-600 hover:underline font-bold">
                  View Full Timeline →
                </button>
              </div>

              ${recentImages.length > 0 ? `
                <div class="touch-scroll flex items-center gap-2 pb-1.5">
                  ${recentImages.map(img => `
                    <div class="w-24 sm:w-28 shrink-0 relative rounded-xl overflow-hidden aspect-video bg-slate-950 cursor-pointer group border border-slate-200"
                         onclick="openImagePreview('${escapeHtml(img.image_path)}', '${escapeHtml(img.title)}')">
                      <img src="${escapeHtml(img.thumbnail_path || img.image_path)}" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" alt="Snapshot">
                      <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px]">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                      </div>
                      <span class="absolute bottom-1 right-1 px-1 rounded bg-black/75 text-[9px] text-white font-mono">${escapeHtml(img.formatted_time || '')}</span>
                    </div>
                  `).join('')}
                </div>
              ` : `
                <p class="text-[11px] text-slate-400 italic">No previous hourly photos recorded for this stay.</p>
              `}
            </div>

            ${recentFiles.length > 0 ? `
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1.5">Inpatient Reports &amp; Scans (${recentFiles.length}):</span>
                <div class="touch-scroll flex items-center gap-2 pb-1.5">
                  ${recentFiles.map(f => {
                    const fileUrl = f.url || f.file_path || `api/file.php?id=${f.id}`;
                    const thumbUrl = f.thumb_url || f.thumbnail_path || fileUrl;
                    const isImg = f.is_image !== false;

                    return `
                      <div onclick="openImagePreview('${fileUrl}', '${escapeHtml(f.title)}', ${isImg})" class="shrink-0 flex items-center gap-2 sm:gap-2.5 p-1.5 pr-2.5 sm:pr-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-indigo-300 cursor-pointer transition text-xs shadow-2xs group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200 relative">
                          ${isImg ? `
                            <img src="${thumbUrl}" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="Preview">
                            <div class="hidden w-full h-full items-center justify-center bg-indigo-50 text-indigo-600 text-xs">
                              <i class="fa-solid fa-file-waveform"></i>
                            </div>
                          ` : `
                            <div class="w-full h-full flex items-center justify-center bg-rose-50 text-rose-600 text-xs">
                              <i class="fa-solid fa-file-pdf"></i>
                            </div>
                          `}
                        </div>
                        <div class="max-w-[120px] sm:max-w-[140px] truncate">
                          <span class="font-bold text-slate-800 block truncate text-[11px] group-hover:text-indigo-600 transition">${escapeHtml(f.title)}</span>
                          <span class="text-[9px] text-slate-400 font-mono">${escapeHtml(f.formatted_date || f.file_date || '')}</span>
                        </div>
                      </div>
                    `;
                  }).join('')}
                </div>
              </div>
            ` : ''}

          </div>

        </div>

      </div>
    `;

    return card;
  }

  // =========================================================================
  // ============ REGISTRY TABLE RENDERER ====================================
  // =========================================================================
  function renderTable(beds) {
    const tbody = document.getElementById('bed-table-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (beds.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-xs text-slate-400">No beds found matching filters.</td></tr>`;
      return;
    }

    beds.forEach(b => {
      const isOcc = b.status === 'Occupied';
      const fullName = isOcc ? `${b.name || ''} ${b.surname || ''}`.trim() : '-';
      const doc = b.doctor_name ? `Dr. ${b.doctor_name}` : (isOcc ? 'General Duty' : '-');
      const latestImg = b.latest_image;

      tbody.innerHTML += `
        <tr class="hover:bg-slate-50/70 transition">
          <td class="p-3.5 font-mono font-black text-slate-900 whitespace-nowrap">
            ${escapeHtml(b.bed_number)}
            <span class="block text-[10px] font-bold ${b.type === 'ICU' ? 'text-rose-600' : 'text-blue-600'}">${b.type} Ward</span>
          </td>
          <td class="p-3.5 whitespace-nowrap">
            ${isOcc ? `
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-50 text-rose-700 border border-rose-200">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> Occupied
              </span>
            ` : `
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
              </span>
            `}
          </td>
          <td class="p-3.5">
            ${isOcc ? `
              <button onclick="openPatientDossier('${b.patient_id}', '${b.bed_number}')" class="font-extrabold text-slate-900 hover:text-indigo-600 transition text-left block">
                ${escapeHtml(fullName)}
              </button>
              <div class="text-[10px] text-slate-400 font-mono">${escapeHtml(b.patient_id)} • ${b.age ? b.age + 'Y' : ''} ${b.gender || ''}</div>
            ` : `<span class="text-slate-400 italic">Unassigned</span>`}
          </td>
          <td class="p-3.5 whitespace-nowrap">
            <span class="text-xs font-bold text-indigo-700">${escapeHtml(doc)}</span>
          </td>
          <td class="p-3.5 whitespace-nowrap">
            ${latestImg ? `
              <div class="flex items-center gap-2 cursor-pointer" onclick="openImagePreview('${latestImg.image_path}', 'Bed ${escapeHtml(b.bed_number)}')">
                <img src="${latestImg.thumbnail_path || latestImg.image_path}" class="w-12 h-8 object-cover rounded-lg border border-slate-200 shadow-2xs" alt="Monitor">
                <div class="text-[10px] text-slate-500 font-mono">${latestImg.formatted_time || 'Latest'}</div>
              </div>
            ` : (isOcc ? `<button onclick="quickSnapBedCamera('${b.bed_number}', '${b.patient_id}', '${escapeHtml(fullName)}')" class="text-[10px] font-bold text-rose-600 hover:underline"><i class="fa-solid fa-camera mr-0.5"></i> Snap Photo</button>` : '-')}
          </td>
          <td class="p-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
            ${isOcc ? (b.admit_date || 'Today') : '-'}
          </td>
          <td class="p-3.5 text-right whitespace-nowrap">
            ${isOcc ? `
              <div class="flex items-center justify-end gap-1">
                <button onclick="openFullscreenBedStation('${b.bed_number}')" class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition" title="Full View">
                  <i class="fa-solid fa-expand"></i>
                </button>
                <button onclick="quickSnapBedCamera('${b.bed_number}', '${b.patient_id}', '${escapeHtml(fullName)}')" class="p-2 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition" title="Snap Monitor Photo">
                  <i class="fa-solid fa-camera"></i>
                </button>
                <button onclick="openObsHistoryModal('${b.bed_number}', '${b.patient_id}', '${escapeHtml(fullName)}')" class="p-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition" title="History">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
                <button onclick="promptDischarge('${b.bed_number}', '${escapeHtml(fullName)}')" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" title="Discharge">
                  <i class="fa-solid fa-person-walking-arrow-right"></i>
                </button>
              </div>
            ` : `
              <button onclick="openQuickAdmitModal('${b.bed_number}')" class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold px-3 py-1.5 rounded-xl border border-emerald-200 transition">
                + Admit
              </button>
            `}
          </td>
        </tr>
      `;
    });
  }

  // =========================================================================
  // ================= FULL VIEW WORKSTATION (WHITE THEME) ====================
  // =========================================================================
  function openFullscreenBedStation(bedNumber) {
    if (bedNumber) {
      const idx = currentFilteredBeds.findIndex(b => b.bed_number == bedNumber);
      if (idx !== -1) {
        jumpToSliderBed(idx);
      }
    }
    setBedDisplayMode('slider');
    const slider = document.getElementById('bed-container-slider');
    if (slider && !slider.classList.contains('bed-box-fullscreen')) {
      toggleBedBoxFullScreen();
    }
  }

  function closeFullscreenBedStation() {
    currentFsBedIndex = -1;
    document.getElementById('modal-fullscreen-bedstation').classList.add('hidden');
  }

  function navigateFullscreenBed(delta) {
    const occupiedBeds = allBeds.filter(b => b.status === 'Occupied');
    if (occupiedBeds.length === 0) return;

    const currentBed = allBeds[currentFsBedIndex];
    let curOccIdx = occupiedBeds.findIndex(b => b.bed_number === (currentBed ? currentBed.bed_number : ''));
    if (curOccIdx < 0) curOccIdx = 0;

    let nextOccIdx = curOccIdx + delta;
    if (nextOccIdx < 0) nextOccIdx = occupiedBeds.length - 1;
    if (nextOccIdx >= occupiedBeds.length) nextOccIdx = 0;

    const targetBed = occupiedBeds[nextOccIdx];
    const targetMainIdx = allBeds.findIndex(b => b.bed_number === targetBed.bed_number);
    if (targetMainIdx >= 0) {
      currentFsBedIndex = targetMainIdx;
      renderFullscreenBedStation(allBeds[targetMainIdx]);
    }
  }

  function renderFullscreenBedStation(bed) {
    if (!bed) return;
    const isIcu = bed.type === 'ICU';
    const fullName = `${bed.name || ''} ${bed.surname || ''}`.trim() || 'Inpatient';
    let age = bed.age || '', gender = bed.gender || '', blood = bed.blood_group || '';
    if ((!age || age === '--') && bed.demographics) {
      const parts = bed.demographics.split(',').map(s => s.trim());
      parts.forEach(p => {
        if (p.endsWith('Y') || p.endsWith('y') || !isNaN(p)) age = p;
        if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
        if (p.includes('+') || p.includes('-')) blood = p;
      });
    }

    document.getElementById('fs-bed-number-badge').textContent = bed.bed_number;
    document.getElementById('fs-bed-number-badge').className = `px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl text-white font-mono font-black text-xs sm:text-sm tracking-wide shadow-xs shrink-0 ${isIcu ? 'bg-rose-600' : 'bg-blue-600'}`;
    document.getElementById('fs-patient-name-title').textContent = fullName;
    document.getElementById('fs-ward-badge').textContent = `${bed.type} Ward`;
    document.getElementById('fs-patient-subtitle').textContent = `${bed.patient_id} • ${age ? age + 'Y ' : ''}${gender || ''}${blood ? ' • 🩸 ' + blood : ''}`;
    document.getElementById('fs-bed-wing').textContent = bed.wing || (isIcu ? 'Critical Care Floor' : 'Observation Ward');

    const latestObs = bed.latest_observation;
    document.getElementById('fs-vital-bp').textContent = latestObs && latestObs.vital_bp ? latestObs.vital_bp : '--';
    document.getElementById('fs-vital-pulse').textContent = latestObs && latestObs.vital_pulse ? `${latestObs.vital_pulse} bpm` : '--';
    document.getElementById('fs-vital-spo2').textContent = latestObs && latestObs.vital_spo2 ? `${latestObs.vital_spo2}%` : '--';
    document.getElementById('fs-vital-temp').textContent = latestObs && latestObs.vital_temp ? `${latestObs.vital_temp}°F` : '--';

    document.getElementById('fs-doctor-name').textContent = bed.doctor_name ? `Dr. ${bed.doctor_name}` : 'General Duty';
    document.getElementById('fs-admit-date').textContent = bed.admit_date || 'Today';
    document.getElementById('fs-phone').textContent = bed.phone || '--';
    document.getElementById('fs-emergency').textContent = bed.emergency_contact_phone ? `${bed.emergency_contact_name || ''} (${bed.emergency_contact_phone})` : '--';
    document.getElementById('fs-diagnosis').textContent = bed.diagnoses_list || bed.symptoms || 'No specific clinical diagnosis logged.';

    // Main Monitor Photo
    const latestImg = bed.latest_image;
    const mainImgEl = document.getElementById('fs-main-monitor-img');
    const tsEl = document.getElementById('fs-latest-timestamp');

    if (latestImg && latestImg.image_path) {
      mainImgEl.src = latestImg.image_path;
      mainImgEl.classList.remove('hidden');
      tsEl.textContent = latestImg.formatted_time || 'Latest Photo';
    } else {
      mainImgEl.src = '';
      mainImgEl.classList.add('hidden');
      tsEl.textContent = 'No Snapshot Yet';
    }

    // Render Photos Grid
    const recentImages = bed.recent_images || [];
    document.getElementById('fs-photos-count').textContent = recentImages.length;
    const photosGrid = document.getElementById('fs-photos-grid');
    if (recentImages.length > 0) {
      photosGrid.innerHTML = recentImages.map(img => `
        <div class="relative rounded-2xl overflow-hidden aspect-video bg-slate-950 cursor-pointer group border border-slate-200"
             onclick="openImagePreview('${escapeHtml(img.image_path)}', '${escapeHtml(img.title)}')">
          <img src="${escapeHtml(img.thumbnail_path || img.image_path)}" class="w-full h-full object-cover group-hover:scale-105 transition" alt="Snapshot">
          <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
            <i class="fa-solid fa-magnifying-glass-plus"></i>
          </div>
          <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/80 text-[10px] text-white font-mono">${escapeHtml(img.formatted_time || '')}</span>
        </div>
      `).join('');
    } else {
      photosGrid.innerHTML = `<div class="col-span-full py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">No machine monitor photos captured yet for this stay.</div>`;
    }

    // Render Reports Grid with VISUAL PREVIEWS
    const recentFiles = bed.recent_files || [];
    document.getElementById('fs-reports-count').textContent = recentFiles.length;
    const reportsGrid = document.getElementById('fs-reports-grid');
    if (recentFiles.length > 0) {
      reportsGrid.innerHTML = recentFiles.map(f => {
        const fileUrl = f.url || f.file_path || `api/file.php?id=${f.id}`;
        const thumbUrl = f.thumb_url || f.thumbnail_path || fileUrl;
        const isImg = f.is_image !== false;

        return `
          <div onclick="openImagePreview('${fileUrl}', '${escapeHtml(f.title)}', ${isImg})" class="p-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-indigo-300 cursor-pointer transition text-xs flex items-center gap-3 shadow-2xs group">
            <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
              ${isImg ? `
                <img src="${thumbUrl}" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="Report">
                <div class="hidden w-full h-full items-center justify-center bg-indigo-50 text-indigo-600 text-sm">
                  <i class="fa-solid fa-file-waveform"></i>
                </div>
              ` : `
                <div class="w-full h-full flex items-center justify-center bg-rose-50 text-rose-600 text-sm">
                  <i class="fa-solid fa-file-pdf"></i>
                </div>
              `}
            </div>
            <div class="min-w-0 flex-1">
              <span class="font-bold text-slate-900 block truncate text-xs group-hover:text-indigo-600 transition">${escapeHtml(f.title)}</span>
              <span class="text-[10px] text-slate-400 font-mono">${escapeHtml(f.formatted_date || '')}</span>
            </div>
          </div>
        `;
      }).join('');
    } else {
      reportsGrid.innerHTML = `<div class="col-span-full py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">No medical reports or admission files uploaded yet.</div>`;
    }
  }

  function zoomFullscreenMainImage() {
    if (currentFsBedIndex >= 0 && allBeds[currentFsBedIndex]) {
      const b = allBeds[currentFsBedIndex];
      if (b.latest_image && b.latest_image.image_path) {
        openImagePreview(b.latest_image.image_path, `Bed ${b.bed_number}: ${b.latest_image.title || 'Monitor Screen'}`);
      }
    }
  }

  function triggerFullscreenCamera() {
    if (currentFsBedIndex >= 0 && allBeds[currentFsBedIndex]) {
      const b = allBeds[currentFsBedIndex];
      quickSnapBedCamera(b.bed_number, b.patient_id, `${b.name || ''} ${b.surname || ''}`.trim());
    }
  }

  function handleFsTransfer() {
    if (currentFsBedIndex >= 0 && allBeds[currentFsBedIndex]) {
      const b = allBeds[currentFsBedIndex];
      openTransferModal(b.bed_number, `${b.name || ''} ${b.surname || ''}`.trim());
    }
  }

  function handleFsDischarge() {
    if (currentFsBedIndex >= 0 && allBeds[currentFsBedIndex]) {
      const b = allBeds[currentFsBedIndex];
      promptDischarge(b.bed_number, `${b.name || ''} ${b.surname || ''}`.trim());
    }
  }

  function switchFsTab(tab) {
    document.getElementById('fstab-btn-photos').className = tab === 'photos' ? "px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-rose-600 text-white text-xs font-black transition flex items-center gap-1.5 shadow-xs" : "px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200";
    document.getElementById('fstab-btn-reports').className = tab === 'reports' ? "px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-indigo-600 text-white text-xs font-black transition flex items-center gap-1.5 shadow-xs" : "px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200";
    document.getElementById('fstab-content-photos').classList.toggle('hidden', tab !== 'photos');
    document.getElementById('fstab-content-reports').classList.toggle('hidden', tab !== 'reports');
  }

  // =========================================================================
  // ================= 1-TAP CAMERA SNAP & IMAGE COMPRESSION =================
  // =========================================================================
  async function compressImageFile(file, maxWidth = 1600, maxHeight = 1600, quality = 0.82) {
    if (!file.type || !file.type.startsWith('image/')) {
      return {
        file: file,
        blob: file,
        base64: null,
        previewUrl: URL.createObjectURL(file),
        size: file.size
      };
    }

    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
          let width = img.width;
          let height = img.height;
          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.imageSmoothingEnabled = true;
          ctx.imageSmoothingQuality = 'high';
          ctx.drawImage(img, 0, 0, width, height);

          const base64 = canvas.toDataURL('image/jpeg', quality);
          canvas.toBlob((blob) => {
            const compressedBlob = blob || file;
            const newFile = new File([compressedBlob], (file.name || 'monitor.jpg').replace(/\.[^/.]+$/, ".jpg"), { type: 'image/jpeg' });
            resolve({
              file: newFile,
              blob: compressedBlob,
              base64: base64,
              previewUrl: base64,
              width: width,
              height: height,
              size: compressedBlob.size
            });
          }, 'image/jpeg', quality);
        };
        img.onerror = () => {
          resolve({
            file: file,
            blob: file,
            base64: null,
            previewUrl: URL.createObjectURL(file),
            size: file.size
          });
        };
        img.src = e.target.result;
      };
      reader.onerror = () => {
        resolve({
          file: file,
          blob: file,
          base64: null,
          previewUrl: URL.createObjectURL(file),
          size: file.size
        });
      };
      reader.readAsDataURL(file);
    });
  }

  function quickSnapBedCamera(bedNumber, patientId, patientName) {
    currentObsTargetBed = { bedNumber, patientId, patientName };
    const input = document.getElementById('direct-card-camera-input');
    if (input) input.click();
  }

  function quickGalleryForBed(bedNumber, patientId, patientName) {
    currentObsTargetBed = { bedNumber, patientId, patientName };
    const input = document.getElementById('direct-card-gallery-input');
    if (input) input.click();
  }

  async function handleDirectCardFileSelection(input) {
    if (!input.files || input.files.length === 0) return;
    if (!currentObsTargetBed) return;

    openObservationModal(currentObsTargetBed.bedNumber, currentObsTargetBed.patientId, currentObsTargetBed.patientName);

    for (let i = 0; i < input.files.length; i++) {
      const item = await compressImageFile(input.files[i]);
      stagedObsImages.push(item);
    }
    input.value = '';
    renderObsImagePreviews();
  }

  async function handleModalFileSelection(input) {
    if (!input.files || input.files.length === 0) return;
    for (let i = 0; i < input.files.length; i++) {
      const item = await compressImageFile(input.files[i]);
      stagedObsImages.push(item);
    }
    input.value = '';
    renderObsImagePreviews();
  }

  function openObservationModal(bedNumber, patientId, patientName) {
    currentObsTargetBed = { bedNumber, patientId, patientName };
    document.getElementById('obs-bed-number-val').value = bedNumber;
    document.getElementById('obs-patient-id-val').value = patientId;
    document.getElementById('obs-bed-badge').textContent = `Bed #${bedNumber}`;
    document.getElementById('obs-patient-name').textContent = patientName;
    document.getElementById('obs-patient-id').textContent = patientId;

    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('obs-hour-label').value = `Observation Check (${timeStr})`;

    document.getElementById('obs-vital-pulse').value = '';
    document.getElementById('obs-vital-bp').value = '';
    document.getElementById('obs-vital-spo2').value = '';
    document.getElementById('obs-vital-temp').value = '';
    document.getElementById('obs-notes').value = '';

    clearObsImages();
    document.getElementById('modal-add-observation').classList.remove('hidden');
  }

  function closeObservationModal() {
    document.getElementById('modal-add-observation').classList.add('hidden');
  }

  function renderObsImagePreviews() {
    const container = document.getElementById('obs-preview-container');
    const emptyPrompt = document.getElementById('obs-empty-preview-prompt');
    const list = document.getElementById('obs-preview-list');
    const countEl = document.getElementById('obs-selected-count');
    const countNum = document.getElementById('obs-staged-count-num');

    if (!container || !list) return;

    if (stagedObsImages.length === 0) {
      container.classList.add('hidden');
      if (emptyPrompt) emptyPrompt.classList.remove('hidden');
      list.innerHTML = '';
      if (countEl) countEl.textContent = '0 photos staged';
      if (countNum) countNum.textContent = '0';
      return;
    }

    container.classList.remove('hidden');
    if (emptyPrompt) emptyPrompt.classList.add('hidden');
    if (countEl) countEl.textContent = `${stagedObsImages.length} photo${stagedObsImages.length === 1 ? '' : 's'} staged`;
    if (countNum) countNum.textContent = stagedObsImages.length;

    list.innerHTML = '';
    stagedObsImages.forEach((item, idx) => {
      const div = document.createElement('div');
      div.className = "relative rounded-xl overflow-hidden aspect-video bg-slate-900 border border-slate-200 group";
      div.innerHTML = `
        <img src="${item.previewUrl}" class="w-full h-full object-cover" alt="Preview">
        <button type="button" onclick="removeObsStagedImage(${idx})" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] shadow-sm hover:bg-rose-700">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <span class="absolute bottom-1 left-1 px-1 rounded bg-black/75 text-[9px] text-white font-mono">#${idx + 1}</span>
      `;
      list.appendChild(div);
    });
  }

  function removeObsStagedImage(idx) {
    if (stagedObsImages[idx]) {
      if (stagedObsImages[idx].previewUrl && stagedObsImages[idx].previewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(stagedObsImages[idx].previewUrl);
      }
      stagedObsImages.splice(idx, 1);
      renderObsImagePreviews();
    }
  }

  function clearObsImages() {
    stagedObsImages.forEach(item => {
      if (item.previewUrl && item.previewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(item.previewUrl);
      }
    });
    stagedObsImages = [];
    renderObsImagePreviews();
  }

  async function handleSaveObservation(e) {
    e.preventDefault();
    const bed = document.getElementById('obs-bed-number-val').value;
    const pat = document.getElementById('obs-patient-id-val').value;
    const hourLabel = document.getElementById('obs-hour-label').value.trim();
    const pulse = document.getElementById('obs-vital-pulse').value.trim();
    const bp = document.getElementById('obs-vital-bp').value.trim();
    const spo2 = document.getElementById('obs-vital-spo2').value.trim();
    const temp = document.getElementById('obs-vital-temp').value.trim();
    const notes = document.getElementById('obs-notes').value.trim();

    const submitBtn = document.getElementById('btn-submit-obs');
    const ogText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('bed_number', bed);
    formData.append('patient_id', pat);
    formData.append('observation_hour', hourLabel);
    formData.append('vital_pulse', pulse);
    formData.append('vital_bp', bp);
    formData.append('vital_spo2', spo2);
    formData.append('vital_temp', temp);
    formData.append('notes', notes);

    stagedObsImages.forEach(item => {
      if (item.file) {
        formData.append('images[]', item.file, item.file.name || 'monitor.jpg');
      }
    });

    try {
      const res = await fetch('api/beds.php?action=add_observation', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Monitor Saved', data.message || 'Observation and machine monitor photo saved.', 'success');
        closeObservationModal();
        fetchBeds();
      } else {
        showToast('Error', data.message || 'Failed to save observation.', 'error');
      }
    } catch(err) {
      console.error(err);
      showToast('Error', 'Server communication error.', 'error');
    } finally {
      submitBtn.innerHTML = ogText;
      submitBtn.disabled = false;
    }
  }

  // =========================================================================
  // ================= HOURLY OBSERVATION TIMELINE HISTORY MODAL =============
  // =========================================================================
  async function openObsHistoryModal(bedNumber, patientId, patientName) {
    currentObsTargetBed = { bedNumber, patientId, patientName };
    document.getElementById('hist-title').textContent = `Bed #${bedNumber} Timeline`;
    document.getElementById('hist-subtitle').textContent = `Inpatient: ${patientName} (${patientId})`;

    const list = document.getElementById('hist-observations-list');
    list.innerHTML = `<div class="py-10 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner fa-spin text-lg block mb-1"></i>Loading observation history...</div>`;
    document.getElementById('modal-obs-history').classList.remove('hidden');

    try {
      const res = await fetch(`api/beds.php?action=get_observations&bed_number=${encodeURIComponent(bedNumber)}&patient_id=${encodeURIComponent(patientId)}`);
      const data = await res.json();

      if (data.status === 'success' && data.observations && data.observations.length > 0) {
        list.innerHTML = data.observations.map((obs, idx) => {
          const hasImages = obs.images && obs.images.length > 0;
          const vitals = [];
          if (obs.vital_bp) vitals.push(`BP: <strong>${escapeHtml(obs.vital_bp)}</strong>`);
          if (obs.vital_pulse) vitals.push(`Pulse: <strong>${escapeHtml(obs.vital_pulse)} bpm</strong>`);
          if (obs.vital_spo2) vitals.push(`SpO2: <strong>${escapeHtml(obs.vital_spo2)}</strong>`);
          if (obs.vital_temp) vitals.push(`Temp: <strong>${escapeHtml(obs.vital_temp)}°F</strong>`);

          return `
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs space-y-2.5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 font-black text-xs flex items-center justify-center shrink-0 border border-indigo-200">
                    #${data.observations.length - idx}
                  </span>
                  <div>
                    <span class="text-xs font-black text-slate-900">${escapeHtml(obs.observation_hour || 'Observation Check')}</span>
                    <span class="text-[10px] text-slate-400 ml-1.5 font-bold">🕒 ${escapeHtml(obs.observed_time_12h || '')} • ${escapeHtml(obs.observed_date || '')}</span>
                  </div>
                </div>
                ${vitals.length > 0 ? `
                  <div class="flex items-center gap-2 text-[11px] bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/80 text-slate-700 flex-wrap">
                    ${vitals.join(' • ')}
                  </div>
                ` : ''}
              </div>

              ${obs.notes ? `
                <div class="text-xs text-slate-700 bg-amber-50/60 p-2 rounded-xl border border-amber-200 font-medium">
                  <strong>Notes:</strong> ${escapeHtml(obs.notes)}
                </div>
              ` : ''}

              <!-- Image Grid with Previews -->
              ${hasImages ? `
                <div>
                  <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Machine Monitor Snapshots (${obs.images.length}):</span>
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    ${obs.images.map(img => `
                      <div class="relative rounded-xl overflow-hidden aspect-video bg-slate-950 cursor-pointer group border border-slate-200"
                           onclick="openImagePreview('${escapeHtml(img.image_path)}', '${escapeHtml(img.title)}')">
                        <img src="${escapeHtml(img.thumbnail_path || img.image_path)}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="Monitor">
                        <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[11px] font-bold">
                          <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </div>
                        <span class="absolute bottom-1 right-1 px-1 rounded bg-black/70 text-[9px] text-white font-mono">${escapeHtml(img.captured_time || '')}</span>
                      </div>
                    `).join('')}
                  </div>
                </div>
              ` : `
                <p class="text-[11px] text-slate-400 italic">No monitor photos attached for this hour.</p>
              `}

              <div class="text-[10px] text-slate-400 pt-1 flex items-center justify-between">
                <span>Recorded by: <strong>${escapeHtml(obs.recorded_by_name || 'Staff')}</strong></span>
                <span class="font-mono text-slate-300">ID #${obs.id}</span>
              </div>
            </div>
          `;
        }).join('');
      } else {
        list.innerHTML = `
          <div class="py-12 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
            <i class="fa-solid fa-camera text-2xl text-slate-300 block mb-2"></i>
            No hourly machine monitor observations logged yet for this stay.
          </div>
        `;
      }
    } catch(err) {
      list.innerHTML = `<div class="py-8 text-center text-xs text-rose-500">Failed to load observation history.</div>`;
    }
  }

  function closeObsHistoryModal() {
    document.getElementById('modal-obs-history').classList.add('hidden');
  }

  function openObsFromHistory() {
    closeObsHistoryModal();
    if (currentObsTargetBed) {
      openObservationModal(currentObsTargetBed.bedNumber, currentObsTargetBed.patientId, currentObsTargetBed.patientName);
    }
  }

  // =========================================================================
  // ================= QUICK ADMIT (ZERO DUPLICATE VALIDATION) ===============
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
    const patSelect = document.getElementById('admit-patient-select');
    patSelect.innerHTML = '<option value="">-- Choose Patient --</option>';

    allPatients.forEach(p => {
      const activeBed = allBeds.find(b => b.patient_id === p.id && b.status === 'Occupied');
      const opt = document.createElement('option');
      opt.value = p.id;
      if (activeBed) {
        opt.textContent = `❌ ${p.name || ''} ${p.surname || ''} (${p.id}) [In Bed #${activeBed.bed_number}]`;
        opt.disabled = true;
      } else {
        opt.textContent = `${p.name || ''} ${p.surname || ''} (${p.id})`;
      }
      patSelect.appendChild(opt);
    });

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

    const docSelect = document.getElementById('admit-doctor-select');
    docSelect.innerHTML = '<option value="">-- General Duty / Unassigned --</option>';
    allDoctors.forEach(d => {
      docSelect.innerHTML += `<option value="${d.id}">Dr. ${escapeHtml(d.name)}</option>`;
    });

    document.getElementById('admit-patient-warning').classList.add('hidden');
    document.getElementById('admit-reason').value = '';
    document.getElementById('modal-admit-patient').classList.remove('hidden');
  }

  function checkSelectedPatientBed(patientId) {
    const warning = document.getElementById('admit-patient-warning');
    const warningText = document.getElementById('admit-patient-warning-text');
    const submitBtn = document.getElementById('btn-submit-admit');

    const activeBed = allBeds.find(b => b.patient_id === patientId && b.status === 'Occupied');
    if (activeBed) {
      warningText.textContent = `Validation Error: This patient is already admitted in Bed #${activeBed.bed_number} (${activeBed.type} Ward)! Multiple admissions are not allowed.`;
      warning.classList.remove('hidden');
      submitBtn.disabled = true;
    } else {
      warning.classList.add('hidden');
      submitBtn.disabled = false;
    }
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
      showToast('Missing Details', 'Please choose both a bed and a patient.', 'error');
      return;
    }

    const activeBed = allBeds.find(b => b.patient_id === pat && b.status === 'Occupied');
    if (activeBed) {
      showToast('Validation Error', `Patient is already admitted in Bed ${activeBed.bed_number}!`, 'error');
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
        showToast('Admitted', data.message || `Patient allocated to Bed ${bed}.`, 'success');
      } else {
        showToast('Admission Blocked', data.message || 'Failed to admit patient.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server communication error.', 'error');
    }
  }

  // =========================================================================
  // ================= TRANSFER & DISCHARGE LOGIC ============================
  // =========================================================================
  function openTransferModal(currentBed, patientName) {
    document.getElementById('transfer-current-bed-val').value = currentBed;
    document.getElementById('transfer-current-label').textContent = `${patientName} • Current Bed #${currentBed}`;

    const select = document.getElementById('transfer-new-bed-select');
    select.innerHTML = '<option value="">-- Choose Destination Bed --</option>';
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
        if (currentFsBedIndex >= 0) closeFullscreenBedStation();
        fetchBeds();
        showToast('Relocated', data.message || `Patient transferred to Bed ${newBed}.`, 'success');
      } else {
        showToast('Error', data.message || 'Failed to transfer patient.', 'error');
      }
    } catch(err) {
      showToast('Error', 'Server error.', 'error');
    }
  }

  function promptDischarge(bedNumber, patientName) {
    document.getElementById('discharge-bed-val').value = bedNumber;
    document.getElementById('discharge-patient-name').textContent = patientName;
    document.getElementById('discharge-bed-num').textContent = `Hospital Bed #${bedNumber}`;
    document.getElementById('discharge-notes').value = 'Discharged from inpatient bed. Inpatient care completed.';
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
        if (currentFsBedIndex >= 0) closeFullscreenBedStation();
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
    if (!confirm(`Are you sure you want to remove Bed ${num} from inventory?`)) return;
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

  // =========================================================================
  // ================= PATIENT DOSSIER & REPORTS WITH PREVIEWS ===============
  // =========================================================================
  async function openPatientDossier(patient_id, bed_number = null) {
    if (!patient_id) return;
    currentViewingPatientId = patient_id;

    try {
      const res = await fetch(`api/history.php?action=get_dossier&patient_id=${encodeURIComponent(patient_id)}`);
      const data = await res.json();

      if (data.status === 'success') {
        const p = data.dossier;
        const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient Record';
        document.getElementById('pop-patient-name').textContent = fullName;
        document.getElementById('pop-header-mrn').textContent = p.id;
        document.getElementById('pop-avatar').textContent = ((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : 'P')).toUpperCase();
        document.getElementById('pop-phone').textContent = p.phone || 'None';
        document.getElementById('pop-emergency').textContent = p.emergency_contact_phone ? `${p.emergency_contact_name || ''} (${p.emergency_contact_phone})` : 'None';

        let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
        document.getElementById('pop-age-gender').textContent = `${age} Y • ${gender}`;
        document.getElementById('pop-blood').textContent = blood;
        document.getElementById('pop-bed-text').textContent = bed_number ? `Bed #${bed_number}` : 'Admitted';
        document.getElementById('pop-btn-open-full').href = `patient_profile.php?id=${encodeURIComponent(p.id)}`;

        const obsList = p.bed_observations || [];
        document.getElementById('pop-obs-count').textContent = obsList.length;
        document.getElementById('pop-files-count').textContent = (p.files || []).length;

        renderPopupObservations(obsList);
        renderPopupConsultations(p.appointments || []);
        renderPopupFiles(p.files || []);

        switchPopupTab('observations');
        document.getElementById('modal-patient-report').classList.remove('hidden');
      } else {
        showToast('Error', data.message || 'Failed to load dossier.', 'error');
      }
    } catch(e) {
      console.error(e);
      showToast('Error', 'Unable to retrieve dossier.', 'error');
    }
  }

  function closePatientReportModal() {
    document.getElementById('modal-patient-report').classList.add('hidden');
  }

  function switchPopupTab(tab) {
    document.querySelectorAll('.poptab').forEach(el => {
      el.classList.remove('text-rose-700', 'bg-rose-50', 'text-indigo-700', 'bg-indigo-50');
      el.classList.add('text-slate-500', 'hover:bg-slate-50');
    });
    document.querySelectorAll('.poptab-view').forEach(el => el.classList.add('hidden'));

    const btn = document.getElementById(`poptab-btn-${tab}`);
    if (btn) {
      btn.classList.add(tab === 'observations' ? 'text-rose-700' : 'text-indigo-700');
      btn.classList.add(tab === 'observations' ? 'bg-rose-50' : 'bg-indigo-50');
      btn.classList.remove('text-slate-500', 'hover:bg-slate-50');
    }
    const view = document.getElementById(`poptab-content-${tab}`);
    if (view) view.classList.remove('hidden');
  }

  function renderPopupObservations(observations) {
    const cont = document.getElementById('pop-obs-container');
    if (!observations || observations.length === 0) {
      cont.innerHTML = `<div class="py-10 text-center text-xs text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">No machine monitor observations logged for this patient yet.</div>`;
      return;
    }

    cont.innerHTML = observations.map(obs => {
      const hasImages = obs.images && obs.images.length > 0;
      return `
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200 shadow-2xs space-y-2">
          <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-100 flex-wrap gap-1">
            <div>
              <strong class="text-slate-900">${escapeHtml(obs.observation_hour || 'Observation')}</strong>
              <span class="text-[10px] text-slate-400 ml-1.5 font-bold">🕒 ${escapeHtml(obs.observed_time_12h || '')}</span>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-mono">Bed #${escapeHtml(obs.bed_number)}</span>
          </div>
          ${obs.notes ? `<p class="text-xs text-slate-600 bg-slate-50 p-2 rounded-xl"><strong>Notes:</strong> ${escapeHtml(obs.notes)}</p>` : ''}
          ${hasImages ? `
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
              ${obs.images.map(img => `
                <div class="relative rounded-xl overflow-hidden aspect-video bg-slate-950 cursor-pointer group border border-slate-200" onclick="openImagePreview('${escapeHtml(img.image_path)}', '${escapeHtml(img.title)}')">
                  <img src="${escapeHtml(img.thumbnail_path || img.image_path)}" class="w-full h-full object-cover group-hover:scale-105 transition" alt="Monitor">
                  <span class="absolute bottom-1 right-1 px-1 rounded bg-black/75 text-[9px] text-white font-mono">${escapeHtml(img.captured_time || '')}</span>
                </div>
              `).join('')}
            </div>
          ` : ''}
        </div>
      `;
    }).join('');
  }

  function renderPopupConsultations(appts) {
    const cont = document.getElementById('pop-consultations-list');
    if (!appts || appts.length === 0) {
      cont.innerHTML = `<div class="py-10 text-center text-xs text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">No consultation appointments recorded.</div>`;
      return;
    }
    cont.innerHTML = appts.map(a => `
      <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200 shadow-2xs space-y-2">
        <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-100 flex-wrap gap-1">
          <div>
            <span class="font-black text-slate-900">Dr. ${escapeHtml(a.doctor_name || 'Medical Officer')}</span>
            <span class="text-[10px] text-indigo-600 ml-1 font-bold">(${escapeHtml(a.dept || 'General')})</span>
          </div>
          <span class="text-[10px] font-mono text-slate-400 font-bold">${a.date || ''}</span>
        </div>
        ${a.symptoms ? `<div class="text-xs text-slate-600"><strong>Symptoms:</strong> ${escapeHtml(a.symptoms)}</div>` : ''}
        ${a.medicines && a.medicines.length > 0 ? `
          <div class="text-[11px] bg-slate-50 p-2 rounded-xl border border-slate-100">
            <span class="font-bold text-slate-700 block mb-1">Prescribed Medicines:</span>
            <div class="space-y-1">
              ${a.medicines.map(m => `<div>💊 <strong>${escapeHtml(m.name)}</strong> - ${escapeHtml(m.dose || '')} (${escapeHtml(m.freq || '')})</div>`).join('')}
            </div>
          </div>
        ` : ''}
      </div>
    `).join('');
  }

  function renderPopupFiles(files) {
    const cont = document.getElementById('pop-files-grid');
    if (!files || files.length === 0) {
      cont.innerHTML = `<div class="col-span-full py-10 text-center text-xs text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">No medical files or scans uploaded.</div>`;
      return;
    }
    cont.innerHTML = files.map(f => {
      const fileUrl = f.url || f.file_path || `api/file.php?id=${f.id}`;
      const thumbUrl = f.thumb_url || f.thumbnail_path || fileUrl;
      const isImg = f.is_image !== false && (!f.mime_type || f.mime_type.startsWith('image/'));

      return `
        <div class="bg-white rounded-2xl border border-slate-200 p-2 text-center group cursor-pointer hover:border-indigo-300 transition shadow-2xs" onclick="openImagePreview('${fileUrl}', '${escapeHtml(f.title)}', ${isImg})">
          <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-1.5 flex items-center justify-center border border-slate-100">
            ${isImg ? `
              <img src="${thumbUrl}" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="File">
              <div class="hidden w-full h-full items-center justify-center bg-indigo-50 text-indigo-600 text-lg">
                <i class="fa-solid fa-file-waveform"></i>
              </div>
            ` : `
              <div class="w-full h-full flex items-center justify-center bg-rose-50 text-rose-600 text-lg">
                <i class="fa-solid fa-file-pdf"></i>
              </div>
            `}
          </div>
          <div class="text-[11px] font-bold text-slate-800 truncate" title="${escapeHtml(f.title)}">${escapeHtml(f.title)}</div>
          <div class="text-[9px] text-slate-400 font-mono uppercase">${escapeHtml(f.formatted_date || f.file_date || '')}</div>
        </div>
      `;
    }).join('');
  }

  // =========================================================================
  // ================= UNIVERSAL IMAGE & REPORT PREVIEW LIGHTBOX =============
  // =========================================================================
  function openImagePreview(src, title, isImage = true) {
    const imgEl = document.getElementById('image-preview-element');
    const iframeEl = document.getElementById('image-preview-iframe');
    const titleEl = document.getElementById('image-preview-title');
    const dlBtn = document.getElementById('image-preview-download-btn');

    titleEl.textContent = title || 'Document Preview';
    dlBtn.href = src;

    if (isImage) {
      iframeEl.classList.add('hidden');
      iframeEl.src = '';
      imgEl.src = src;
      imgEl.classList.remove('hidden');
    } else {
      imgEl.classList.add('hidden');
      imgEl.src = '';
      iframeEl.src = src;
      iframeEl.classList.remove('hidden');
    }

    document.getElementById('modal-image-preview').classList.remove('hidden');
  }

  function closeImagePreview() {
    document.getElementById('modal-image-preview').classList.add('hidden');
    document.getElementById('image-preview-iframe').src = '';
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

  // Close modals on Escape key or backdrop click
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeObservationModal();
      closeObsHistoryModal();
      closePatientReportModal();
      closeQuickAdmitModal();
      closeTransferModal();
      closeDischargeModal();
      closeAddBedModal();
      closeFullscreenBedStation();
      closeImagePreview();
    }
  });

  [
    'modal-add-observation',
    'modal-obs-history',
    'modal-patient-report',
    'modal-admit-patient',
    'modal-transfer-bed',
    'modal-discharge-patient',
    'modal-add-bed',
    'modal-image-preview'
  ].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('click', (e) => {
        if (e.target === el) el.classList.add('hidden');
      });
    }
  });
</script>

<?php include 'includes/footer.php'; ?>

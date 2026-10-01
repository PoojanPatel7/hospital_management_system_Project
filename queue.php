<?php require_once 'auth.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="space-y-6">
  <!-- Header bar with line controls -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <h2 class="text-xl font-bold text-slate-900">Hospital Pipeline</h2>
        <span id="line-status-pill" class="text-xs px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span> Line Idle
        </span>
      </div>
      <p class="text-xs text-slate-500 mt-1">Real-time status tracking from Desk Check-In to Doctor Consultation.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto justify-start lg:justify-end">
                  <!-- Filter by Department -->
      <div class="relative w-40 sm:w-48" id="dept-filter-container">
          <input type="hidden" id="queue-dept-filter" value="All">
          <div onclick="toggleQueueDropdown('dept')" class="flex items-center justify-between w-full px-3 py-2 bg-white border border-slate-300 rounded-xl cursor-pointer shadow-sm hover:border-slate-400 transition">
              <span id="dept-display" class="text-xs font-bold text-slate-700 truncate">All Departments</span>
              <i id="dept-icon" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"></i>
          </div>
          <div id="dept-dropdown" class="hidden absolute top-full right-0 mt-1.5 w-full min-w-[12rem] bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-64 overflow-y-auto py-1 opacity-0 scale-95 transition-all duration-200 origin-top-right">
              <div onclick="selectQueueFilter('dept', 'All', 'All Departments')" class="px-3 py-2 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer text-xs font-bold text-slate-700 transition">All Departments</div>
          </div>
      </div>
      
      <!-- Filter by Doctor -->
      <div class="relative w-40 sm:w-48" id="doc-filter-container">
          <input type="hidden" id="queue-doctor-filter" value="All">
          <div onclick="toggleQueueDropdown('doc')" class="flex items-center justify-between w-full px-3 py-2 bg-white border border-slate-300 rounded-xl cursor-pointer shadow-sm hover:border-slate-400 transition">
              <span id="doc-display" class="text-xs font-bold text-slate-700 truncate">All Doctors</span>
              <i id="doc-icon" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"></i>
          </div>
          <div id="doc-dropdown" class="hidden absolute top-full right-0 mt-1.5 w-full min-w-[12rem] bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-64 overflow-y-auto py-1 opacity-0 scale-95 transition-all duration-200 origin-top-right">
              <div onclick="selectQueueFilter('doc', 'All', 'All Doctors')" class="px-3 py-2 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer text-xs font-bold text-slate-700 transition">All Doctors</div>
          </div>
      </div>

      <div class="relative flex-1 sm:flex-initial">
        <input type="text" id="queue-patient-search" placeholder="Search patient by MRN or Name..." class="w-full sm:w-60 text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" onkeyup="handlePatientSearch()">
        <div id="patient-search-dropdown" class="hidden absolute top-full mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-64 overflow-y-auto divide-y divide-slate-100">
        </div>
      </div>

      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-semibold">
        <button id="btn-queue-view-kanban" onclick="setQueueDisplayMode('kanban')" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold">Kanban</button>
        <button id="btn-queue-view-table" onclick="setQueueDisplayMode('table')" class="px-3 py-1.5 rounded-lg text-slate-600">Table</button>
      </div>

      <!-- Pre-Book Appointment Button -->
      <button onclick="openDirectBook()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow transition flex items-center gap-1.5">
        <i class="fa-solid fa-calendar-plus"></i> <span>Pre-Book</span>
      </button>

      <!-- Staff/Doctor Start Line Button -->
      <button id="btn-toggle-line" onclick="toggleDoctorLine()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition flex items-center gap-2">
        <i class="fa-solid fa-play"></i> <span id="line-btn-text">Start Line</span>
      </button>
    </div>
  </div>

  <!-- Advance & Pre-Booked Appointments Collapsible Banner -->
  <div class="bg-white rounded-2xl border border-indigo-100 p-4 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shadow-sm">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
          <h4 class="font-bold text-slate-900 text-sm">Advance & Pre-Booked Appointments</h4>
          <p class="text-[11px] text-slate-500">Upcoming scheduled appointments with doctors by date and time slot.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="toggleAdvanceScheduleView()" id="btn-toggle-advance-view" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
          <i class="fa-solid fa-chevron-down text-[10px]" id="advance-chevron"></i>
          <span id="advance-view-label">View Pre-Booked (<span id="advance-count-badge">0</span>)</span>
        </button>
        <button onclick="openDirectBook()" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-xl transition border border-indigo-200">
          + Schedule New
        </button>
      </div>
    </div>

    <!-- Pre-Booked Appointments List (Collapsible) -->
    <div id="advance-schedule-container" class="hidden mt-4 pt-3 border-t border-slate-100">
      <div id="advance-schedule-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        <!-- Loaded via JS -->
      </div>
    </div>
  </div>

  <!-- Kanban View -->
  <div id="queue-container-kanban" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <!-- Stage 1 -->
    <div class="bg-slate-100/70 rounded-2xl p-4 border border-slate-200 flex flex-col min-h-[380px]">
      <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-blue-500"></span>
          <h3 class="font-bold text-sm text-slate-800">1. Checked-In</h3>
        </div>
        <div class="flex items-center gap-1.5">
          <span id="slider-indicator-stage-1" class="hidden text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-full border border-slate-200 shadow-sm">1/1</span>
          <button onclick="slideStage(1, -1)" id="btn-prev-stage-1" class="hidden w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-[10px] text-slate-600 shadow-sm transition"><i class="fa-solid fa-chevron-left"></i></button>
          <button onclick="slideStage(1, 1)" id="btn-next-stage-1" class="hidden w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-[10px] text-slate-600 shadow-sm transition"><i class="fa-solid fa-chevron-right"></i></button>
          <button onclick="toggleLaneMode(1)" id="btn-mode-stage-1" title="Toggle Slider / Stack View" class="w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-[10px] text-blue-600 shadow-sm transition"><i class="fa-solid fa-sliders"></i></button>
          <span id="count-stage-1" class="text-xs font-mono font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 shadow-sm">0</span>
        </div>
      </div>

      <!-- Stage 1 Section Image -->
      <div class="relative w-full h-36 rounded-xl overflow-hidden mb-3.5 border border-slate-200/90 bg-slate-900/10 shadow-sm flex items-center justify-center group">
        <img src="images/Checked-In.png" alt="Checked-In Section" class="absolute inset-0 w-full h-full object-cover blur-md opacity-35 scale-110 pointer-events-none" />
        <img src="images/Checked-In.png" alt="Checked-In Section" class="relative max-h-full max-w-full object-contain p-1 z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105" />
        <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1 rounded-lg bg-slate-900/75 backdrop-blur-md text-[11px] text-white font-bold flex items-center justify-between z-20 shadow-sm border border-white/10">
          <span class="flex items-center gap-1.5 truncate">
            <i class="fa-solid fa-hospital-user text-blue-300 text-xs"></i>
            <span>Desk Check-In & MRN</span>
          </span>
          <span class="text-[9px] uppercase tracking-wider bg-blue-500/80 px-1.5 py-0.5 rounded text-white font-extrabold shrink-0">Stage 1</span>
        </div>
      </div>

      <div id="lane-stage-1" class="flex-1 flex flex-col justify-between"></div>
    </div>

    <!-- Stage 2 -->
    <div class="bg-teal-50/60 rounded-2xl p-4 border border-teal-200 flex flex-col min-h-[380px]">
      <div class="flex items-center justify-between pb-3 border-b border-teal-200 mb-3">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-teal-500"></span>
          <h3 class="font-bold text-sm text-slate-800">2. Available</h3>
        </div>
        <div class="flex items-center gap-1.5">
          <span id="slider-indicator-stage-2" class="hidden text-[10px] font-bold text-teal-800 bg-white px-2 py-0.5 rounded-full border border-teal-200 shadow-sm">1/1</span>
          <button onclick="slideStage(2, -1)" id="btn-prev-stage-2" class="hidden w-6 h-6 rounded-md bg-white border border-teal-200 hover:bg-teal-50 flex items-center justify-center text-[10px] text-teal-700 shadow-sm transition"><i class="fa-solid fa-chevron-left"></i></button>
          <button onclick="slideStage(2, 1)" id="btn-next-stage-2" class="hidden w-6 h-6 rounded-md bg-white border border-teal-200 hover:bg-teal-50 flex items-center justify-center text-[10px] text-teal-700 shadow-sm transition"><i class="fa-solid fa-chevron-right"></i></button>
          <button onclick="toggleLaneMode(2)" id="btn-mode-stage-2" title="Toggle Slider / Stack View" class="w-6 h-6 rounded-md bg-white border border-teal-200 hover:bg-teal-50 flex items-center justify-center text-[10px] text-teal-700 shadow-sm transition"><i class="fa-solid fa-sliders"></i></button>
          <span id="count-stage-2" class="text-xs font-mono font-bold bg-teal-200/70 px-2 py-0.5 rounded-full text-teal-900 shadow-sm">0</span>
        </div>
      </div>

      <!-- Stage 2 Section Image -->
      <div class="relative w-full h-36 rounded-xl overflow-hidden mb-3.5 border border-teal-200/90 bg-teal-900/10 shadow-sm flex items-center justify-center group">
        <img src="images/Available.png" alt="Available Section" class="absolute inset-0 w-full h-full object-cover blur-md opacity-35 scale-110 pointer-events-none" />
        <img src="images/Available.png" alt="Available Section" class="relative max-h-full max-w-full object-contain p-1 z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105" />
        <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1 rounded-lg bg-slate-900/75 backdrop-blur-md text-[11px] text-white font-bold flex items-center justify-between z-20 shadow-sm border border-white/10">
          <span class="flex items-center gap-1.5 truncate">
            <i class="fa-solid fa-bell-concierge text-teal-300 text-xs"></i>
            <span>Reception & Arrived</span>
          </span>
          <span class="text-[9px] uppercase tracking-wider bg-teal-500/80 px-1.5 py-0.5 rounded text-white font-extrabold shrink-0">Stage 2</span>
        </div>
      </div>

      <div id="lane-stage-2" class="flex-1 flex flex-col justify-between"></div>
    </div>

    <!-- Stage 3 -->
    <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-200 flex flex-col min-h-[380px]">
      <div class="flex items-center justify-between pb-3 border-b border-amber-200 mb-3">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-amber-500"></span>
          <h3 class="font-bold text-sm text-slate-800">3. Waiting Lounge</h3>
        </div>
        <div class="flex items-center gap-1.5">
          <span id="slider-indicator-stage-3" class="hidden text-[10px] font-bold text-amber-800 bg-white px-2 py-0.5 rounded-full border border-amber-200 shadow-sm">1/1</span>
          <button onclick="slideStage(3, -1)" id="btn-prev-stage-3" class="hidden w-6 h-6 rounded-md bg-white border border-amber-200 hover:bg-amber-50 flex items-center justify-center text-[10px] text-amber-700 shadow-sm transition"><i class="fa-solid fa-chevron-left"></i></button>
          <button onclick="slideStage(3, 1)" id="btn-next-stage-3" class="hidden w-6 h-6 rounded-md bg-white border border-amber-200 hover:bg-amber-50 flex items-center justify-center text-[10px] text-amber-700 shadow-sm transition"><i class="fa-solid fa-chevron-right"></i></button>
          <button onclick="toggleLaneMode(3)" id="btn-mode-stage-3" title="Toggle Slider / Stack View" class="w-6 h-6 rounded-md bg-white border border-amber-200 hover:bg-amber-50 flex items-center justify-center text-[10px] text-amber-700 shadow-sm transition"><i class="fa-solid fa-sliders"></i></button>
          <span id="count-stage-3" class="text-xs font-mono font-bold bg-amber-200/70 px-2 py-0.5 rounded-full text-amber-900 shadow-sm">0</span>
        </div>
      </div>

      <!-- Stage 3 Section Image -->
      <div class="relative w-full h-36 rounded-xl overflow-hidden mb-3.5 border border-amber-200/90 bg-amber-900/10 shadow-sm flex items-center justify-center group">
        <img src="images/Waiting Lounge.png" alt="Waiting Lounge Section" class="absolute inset-0 w-full h-full object-cover blur-md opacity-35 scale-110 pointer-events-none" />
        <img src="images/Waiting Lounge.png" alt="Waiting Lounge Section" class="relative max-h-full max-w-full object-contain p-1 z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105" />
        <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1 rounded-lg bg-slate-900/75 backdrop-blur-md text-[11px] text-white font-bold flex items-center justify-between z-20 shadow-sm border border-white/10">
          <span class="flex items-center gap-1.5 truncate">
            <i class="fa-solid fa-couch text-amber-300 text-xs"></i>
            <span>Waiting Lounge Area</span>
          </span>
          <span class="text-[9px] uppercase tracking-wider bg-amber-500/80 px-1.5 py-0.5 rounded text-white font-extrabold shrink-0">Stage 3</span>
        </div>
      </div>

      <div id="lane-stage-3" class="flex-1 flex flex-col justify-between"></div>
    </div>

    <!-- Stage 4 -->
    <div class="bg-purple-50/60 rounded-2xl p-4 border border-purple-200 flex flex-col min-h-[380px]">
      <div class="flex items-center justify-between pb-3 border-b border-purple-200 mb-3">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-purple-600 animate-pulse"></span>
          <h3 class="font-bold text-sm text-slate-800">4. Consulting</h3>
        </div>
        <div class="flex items-center gap-1.5">
          <span id="slider-indicator-stage-4" class="hidden text-[10px] font-bold text-purple-800 bg-white px-2 py-0.5 rounded-full border border-purple-200 shadow-sm">1/1</span>
          <button onclick="slideStage(4, -1)" id="btn-prev-stage-4" class="hidden w-6 h-6 rounded-md bg-white border border-purple-200 hover:bg-purple-50 flex items-center justify-center text-[10px] text-purple-700 shadow-sm transition"><i class="fa-solid fa-chevron-left"></i></button>
          <button onclick="slideStage(4, 1)" id="btn-next-stage-4" class="hidden w-6 h-6 rounded-md bg-white border border-purple-200 hover:bg-purple-50 flex items-center justify-center text-[10px] text-purple-700 shadow-sm transition"><i class="fa-solid fa-chevron-right"></i></button>
          <button onclick="toggleLaneMode(4)" id="btn-mode-stage-4" title="Toggle Slider / Stack View" class="w-6 h-6 rounded-md bg-white border border-purple-200 hover:bg-purple-50 flex items-center justify-center text-[10px] text-purple-700 shadow-sm transition"><i class="fa-solid fa-sliders"></i></button>
          <span id="count-stage-4" class="text-xs font-mono font-bold bg-purple-200/70 px-2 py-0.5 rounded-full text-purple-900 shadow-sm">0</span>
        </div>
      </div>

      <!-- Stage 4 Section Image -->
      <div class="relative w-full h-36 rounded-xl overflow-hidden mb-3.5 border border-purple-200/90 bg-purple-900/10 shadow-sm flex items-center justify-center group">
        <img src="images/Consulting.png" alt="Consulting Section" class="absolute inset-0 w-full h-full object-cover blur-md opacity-35 scale-110 pointer-events-none" />
        <img src="images/Consulting.png" alt="Consulting Section" class="relative max-h-full max-w-full object-contain p-1 z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105" />
        <div class="absolute bottom-2 left-2 right-2 px-2.5 py-1 rounded-lg bg-slate-900/75 backdrop-blur-md text-[11px] text-white font-bold flex items-center justify-between z-20 shadow-sm border border-white/10">
          <span class="flex items-center gap-1.5 truncate">
            <i class="fa-solid fa-user-doctor text-purple-300 text-xs"></i>
            <span>Doctor Consulting Room</span>
          </span>
          <span class="text-[9px] uppercase tracking-wider bg-purple-500/80 px-1.5 py-0.5 rounded text-white font-extrabold shrink-0">Stage 4</span>
        </div>
      </div>

      <div id="lane-stage-4" class="flex-1 flex flex-col justify-between"></div>
    </div>
  </div>

  <!-- Table View -->
  <div id="queue-container-table" class="hidden bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Token #</th>
            <th class="py-3 px-4">Stage</th>
            <th class="py-3 px-4">Patient</th>
            <th class="py-3 px-4">Priority</th>
            <th class="py-3 px-4">Doctor / Dept</th>
            <th class="py-3 px-4">Symptoms</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="queue-table-tbody" class="divide-y divide-slate-100"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ================= MODALS ================= -->

<!-- CONFIRM STAGE MOVE MODAL -->
<div id="modal-confirm-move" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-2xl lg:max-w-3xl w-full p-5 sm:p-7 shadow-2xl relative border border-slate-200 my-8 max-h-[92vh] overflow-y-auto">
    <button onclick="closeConfirmMoveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition"><i class="fa-solid fa-xmark text-sm"></i></button>

    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
      <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 shadow-sm">
        <i class="fa-solid fa-people-arrows"></i>
      </div>
      <div>
        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Confirm Stage Transfer</h3>
        <p class="text-xs text-slate-500 font-medium">Please verify complete patient information before moving them.</p>
      </div>
    </div>

    <!-- Patient Header Card -->
    <div class="bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-slate-50 border border-blue-100 rounded-2xl p-3.5 sm:p-4 mb-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div id="confirm-move-avatar" class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-md shrink-0">
            --
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h4 id="confirm-move-name" class="font-extrabold text-slate-900 text-base leading-tight">Patient Name</h4>
              <span id="confirm-move-token" class="text-[10px] font-black px-2 py-0.5 rounded-md bg-blue-600 text-white font-mono shadow-sm">Token #1</span>
              <span id="confirm-move-app-code" class="text-[10px] font-black px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono shadow-sm">APP-0000</span>
              <span id="confirm-move-priority" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Regular</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
              <span class="font-mono font-bold text-slate-600" id="confirm-move-mrn">MRN: -</span>
              <span>•</span>
              <span id="confirm-move-blood" class="text-[11px] font-bold text-rose-600">🩸 -</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Full Patient Info Grid -->
    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 mb-4 space-y-2.5 text-xs">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pb-2.5 border-b border-slate-200/60">
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Father's Name</span>
          <span id="confirm-move-father" class="font-bold text-slate-800">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Contact Phone</span>
          <span id="confirm-move-phone" class="font-bold text-slate-800">-</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pb-2.5 border-b border-slate-200/60">
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Gender & Age</span>
          <span id="confirm-move-gender-age" class="font-bold text-slate-800">-</span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Demographics / City</span>
          <span id="confirm-move-demographics" class="font-semibold text-slate-800 truncate block">-</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pb-2.5 border-b border-slate-200/60">
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Assigned Doctor</span>
          <span class="font-extrabold text-slate-900 flex items-center gap-1.5 mt-0.5"><i class="fa-solid fa-user-doctor text-indigo-500"></i> <span id="confirm-move-doctor">-</span></span>
        </div>
        <div>
          <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Department</span>
          <span id="confirm-move-dept" class="font-bold text-slate-800">-</span>
        </div>
      </div>

      <div class="pb-2.5 border-b border-slate-200/60">
        <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">Symptoms / Reason</span>
        <p id="confirm-move-symptoms" class="text-slate-800 italic mt-0.5 font-medium">-</p>
      </div>

      <!-- Stage Transfer Visual Banner -->
      <div class="pt-0.5">
        <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider mb-1.5">Pipeline Stage Movement</span>
        <div class="flex items-center justify-between gap-2 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
          <div class="text-center flex-1">
            <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Current Stage</span>
            <span id="confirm-move-current-stage" class="text-xs font-extrabold text-slate-700">-</span>
          </div>
          <div class="text-blue-500 text-sm px-2 animate-pulse">
            <i class="fa-solid fa-arrow-right-long"></i>
          </div>
          <div class="text-center flex-1">
            <span class="block text-[9px] text-blue-500 font-bold uppercase tracking-wider">Target Stage</span>
            <span id="confirm-move-target" class="text-xs font-black text-blue-700">-</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Link to Open Patient Dossier Popup -->
    <div class="mb-5 flex justify-end">
      <button type="button" id="confirm-move-dossier-btn" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-sm">
        <i class="fa-solid fa-folder-medical text-emerald-600"></i> View Full Clinical Dossier & Records
      </button>
    </div>

    <div class="flex gap-3">
      <button onclick="closeConfirmMoveModal()" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
      <button id="btn-execute-move" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/30 transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-check"></i> Confirm Move
      </button>
    </div>
  </div>
</div>

<!-- EDIT APPOINTMENT MODAL -->
<div id="modal-edit-appointment" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-2xl lg:max-w-3xl w-full p-5 sm:p-7 shadow-2xl relative border border-slate-200 my-8 max-h-[92vh] overflow-y-auto">
    <button onclick="closeEditAppointmentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition"><i class="fa-solid fa-xmark text-sm"></i></button>

    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
      <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0 shadow-sm border border-indigo-100">
        <i class="fa-solid fa-pen-to-square"></i>
      </div>
      <div>
        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Edit Appointment</h3>
        <p class="text-xs text-slate-500 font-medium">Update doctor assignment, consultation priority, or reported symptoms.</p>
      </div>
    </div>

    <!-- Patient & Appointment Summary Card -->
    <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-3.5 mb-4 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-2">
          <span id="edit-app-code" class="text-xs font-mono font-black bg-indigo-600 text-white px-2 py-0.5 rounded shadow-sm">APP-0000</span>
          <h4 id="edit-app-patient-name" class="font-bold text-slate-900 text-sm">Patient Name</h4>
        </div>
        <p class="text-[11px] text-slate-500 font-mono mt-0.5" id="edit-app-patient-mrn">MRN: CP-0000</p>
      </div>
      <span id="edit-app-current-stage" class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-white text-slate-700 border border-indigo-200 shadow-sm">Stage 1</span>
    </div>

    <form onsubmit="handleEditAppointmentSubmit(event)" class="space-y-3.5">
      <input type="hidden" id="edit-app-id">

      <!-- Doctor Selection -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Assigned Doctor *</label>
        <select id="edit-app-doctor-id" required class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
          <option value="">Select doctor...</option>
        </select>
      </div>

      <!-- Priority / Case Type -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Consultation Priority / Type *</label>
        <select id="edit-app-type" required class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
          <option value="General Consultation">General Consultation (Regular)</option>
          <option value="Emergency Case">🚨 Emergency Case (Urgent Priority)</option>
          <option value="Routine Checkup">Routine Checkup</option>
          <option value="Follow-up Consultation">Follow-up Consultation</option>
          <option value="Specialist Examination">Specialist Examination</option>
        </select>
      </div>

      <!-- Date & Slot -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Appointment Date</label>
          <input type="date" id="edit-app-date" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Time Slot</label>
          <input type="text" id="edit-app-slot" placeholder="e.g. 10:30 AM or Immediate" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
        </div>
      </div>

      <!-- Reported Symptoms -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Symptoms / Reason for Visit *</label>
        <textarea id="edit-app-symptoms" required rows="2" placeholder="Patient symptoms or complaints..." class="w-full text-xs border border-slate-300 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none"></textarea>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeEditAppointmentModal()" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
        <button type="submit" id="btn-save-edit-app" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/30 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE APPOINTMENT CONFIRMATION MODAL -->
<div id="modal-delete-appointment" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl relative border border-slate-200 my-8">
    <div class="flex items-center gap-3.5 mb-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 border border-rose-200">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div>
        <h3 class="text-base font-extrabold text-slate-900">Delete Appointment</h3>
        <p class="text-xs text-slate-500">Confirm cancellation of this appointment.</p>
      </div>
    </div>

    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 mb-5 text-xs space-y-1 text-slate-700">
      <p class="font-bold text-rose-900">Are you sure you want to delete this appointment?</p>
      <div class="flex items-center gap-2 pt-1 font-semibold text-slate-800">
        <span id="delete-app-code" class="font-mono font-black text-rose-700 bg-white px-2 py-0.5 rounded border border-rose-200">APP-0000</span>
        <span id="delete-app-patient">-</span>
      </div>
      <p class="text-[11px] text-rose-700/90 pt-1">This will permanently remove the patient from today's active pipeline and clear their current session.</p>
    </div>

    <input type="hidden" id="delete-app-id">

    <div class="flex gap-3">
      <button onclick="closeDeleteAppointmentModal()" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
      <button onclick="executeDeleteAppointment()" id="btn-confirm-delete-app" class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/30 transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-trash-can"></i> Delete Appointment
      </button>
    </div>
  </div>
</div>

<!-- DOCTOR CONSULTATION & DISPOSITION MODAL — Guided Step-by-Step Clinical Workstation -->
<div id="modal-consultation" class="hidden fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-4xl xl:max-w-5xl w-full shadow-2xl border border-slate-200 relative my-auto sm:my-6 max-h-[94vh] flex flex-col overflow-hidden">

    <!-- Datalist: 300+ Most Common Clinical Diagnoses & ICD Conditions -->
    <datalist id="common-diagnoses-list">
      <option value="Acute Upper Respiratory Tract Infection (URTI)"></option>
      <option value="Acute Viral Fever / Pyrexia of Unknown Origin"></option>
      <option value="Acute Tonsillopharyngitis"></option>
      <option value="Acute Bronchitis"></option>
      <option value="Acute Gastroenteritis / Infectious Diarrhea"></option>
      <option value="Essential Hypertension"></option>
      <option value="Type 2 Diabetes Mellitus"></option>
      <option value="Tension-Type Headache"></option>
      <option value="Migraine with / without Aura"></option>
      <option value="Allergic Rhinitis / Sinusitis"></option>
      <option value="Urinary Tract Infection (UTI)"></option>
      <option value="Gastroesophageal Reflux Disease (GERD)"></option>
      <option value="Acid Peptic Disease / Gastritis"></option>
      <option value="Lumbar Muscle Strain / Mechanical Low Back Pain"></option>
      <option value="Bronchial Asthma"></option>
      <option value="Chronic Obstructive Pulmonary Disease (COPD)"></option>
      <option value="Community Acquired Pneumonia (CAP)"></option>
      <option value="Dyspepsia / Indigestion"></option>
      <option value="Allergic Dermatitis / Urticaria"></option>
      <option value="Tinea / Fungal Skin Infection"></option>
      <option value="Osteoarthritis (Knee / Hip)"></option>
      <option value="Cervical Spondylosis"></option>
      <option value="Iron Deficiency Anemia"></option>
      <option value="Hyperlipidemia / Dyslipidemia"></option>
      <option value="Hypothyroidism"></option>
      <option value="Dengue Fever"></option>
      <option value="Enteric / Typhoid Fever"></option>
      <option value="Benign Paroxysmal Positional Vertigo (BPPV)"></option>
      <option value="Generalized Anxiety Disorder"></option>
      <option value="Major Depressive Disorder"></option>
    </datalist>

    <!-- Datalist: 300+ Most Commonly Prescribed Medicines (Tablets, Syrups, Inhalers, Topicals) -->
    <datalist id="common-drugs-list">
      <!-- 1. Antipyretics, Analgesics & NSAIDs -->
      <option value="Paracetamol 500mg Tablet"></option>
      <option value="Paracetamol 650mg Tablet"></option>
      <option value="Paracetamol 1000mg Tablet"></option>
      <option value="Paracetamol Syrup 120mg/5ml"></option>
      <option value="Paracetamol Syrup 250mg/5ml"></option>
      <option value="Paracetamol IV Infusion 1000mg/100ml"></option>
      <option value="Ibuprofen 200mg Tablet"></option>
      <option value="Ibuprofen 400mg Tablet"></option>
      <option value="Ibuprofen 600mg Tablet"></option>
      <option value="Ibuprofen Syrup 100mg/5ml"></option>
      <option value="Ibuprofen + Paracetamol (Combiflam)"></option>
      <option value="Aceclofenac 100mg Tablet"></option>
      <option value="Aceclofenac + Paracetamol (Zerodol-P)"></option>
      <option value="Aceclofenac + Paracetamol + Serratiopeptidase (Zerodol-SP)"></option>
      <option value="Diclofenac Sodium 50mg Tablet"></option>
      <option value="Diclofenac Sodium 75mg Injection"></option>
      <option value="Diclofenac Gel 1% Topical"></option>
      <option value="Tramadol 50mg Capsule"></option>
      <option value="Tramadol + Paracetamol Tablet (Ultracet)"></option>
      <option value="Naproxen 250mg Tablet"></option>
      <option value="Naproxen 500mg Tablet"></option>
      <option value="Mefenamic Acid 250mg Tablet"></option>
      <option value="Mefenamic Acid 500mg Tablet"></option>
      <option value="Mefenamic Acid + Dicyclomine (Meftal-Spas)"></option>
      <option value="Ketorolac Tromethamine 10mg Tablet"></option>
      <option value="Piroxicam 20mg Capsule"></option>
      <option value="Etoricoxib 60mg Tablet"></option>
      <option value="Etoricoxib 90mg Tablet"></option>
      <option value="Etoricoxib 120mg Tablet"></option>
      <option value="Celecoxib 100mg Capsule"></option>
      <option value="Celecoxib 200mg Capsule"></option>
      <option value="Indomethacin 25mg Capsule"></option>
      <option value="Aspirin 75mg Gastro-resistant Tablet"></option>
      <option value="Aspirin 150mg Tablet"></option>
      <option value="Aspirin 300mg Soluble Tablet"></option>

      <!-- 2. Antibiotics, Antimicrobials & Antifungals -->
      <option value="Amoxicillin 250mg Capsule"></option>
      <option value="Amoxicillin 500mg Capsule"></option>
      <option value="Amoxicillin Syrup 125mg/5ml"></option>
      <option value="Amoxicillin + Clavulanate 375mg Tablet"></option>
      <option value="Amoxicillin + Clavulanate 625mg (Augmentin)"></option>
      <option value="Amoxicillin + Clavulanate 1000mg Tablet"></option>
      <option value="Amoxicillin + Clavulanate Syrup 228.5mg/5ml"></option>
      <option value="Azithromycin 250mg Tablet"></option>
      <option value="Azithromycin 500mg Tablet"></option>
      <option value="Azithromycin Suspension 200mg/5ml"></option>
      <option value="Cefixime 100mg Tablet"></option>
      <option value="Cefixime 200mg Tablet"></option>
      <option value="Cefixime Syrup 50mg/5ml"></option>
      <option value="Cefuroxime Axetil 250mg Tablet"></option>
      <option value="Cefuroxime Axetil 500mg Tablet"></option>
      <option value="Cephalexin 250mg Capsule"></option>
      <option value="Cephalexin 500mg Capsule"></option>
      <option value="Ciprofloxacin 250mg Tablet"></option>
      <option value="Ciprofloxacin 500mg Tablet"></option>
      <option value="Ciprofloxacin Eye/Ear Drops 0.3%"></option>
      <option value="Ofloxacin 200mg Tablet"></option>
      <option value="Ofloxacin 400mg Tablet"></option>
      <option value="Ofloxacin + Ornidazole Tablet"></option>
      <option value="Levofloxacin 250mg Tablet"></option>
      <option value="Levofloxacin 500mg Tablet"></option>
      <option value="Levofloxacin 750mg Tablet"></option>
      <option value="Doxycycline 100mg Capsule"></option>
      <option value="Minocycline 50mg Tablet"></option>
      <option value="Minocycline 100mg Tablet"></option>
      <option value="Metronidazole 200mg Tablet"></option>
      <option value="Metronidazole 400mg Tablet"></option>
      <option value="Metronidazole IV Infusion 500mg/100ml"></option>
      <option value="Clindamycin 150mg Capsule"></option>
      <option value="Clindamycin 300mg Capsule"></option>
      <option value="Clindamycin Topical Gel 1%"></option>
      <option value="Nitrofurantoin 100mg SR Tablet"></option>
      <option value="Trimethoprim + Sulfamethoxazole (Bactrim DS)"></option>
      <option value="Linezolid 600mg Tablet"></option>
      <option value="Faropenem 200mg Tablet"></option>
      <option value="Cefpodoxime Proxetil 100mg Tablet"></option>
      <option value="Cefpodoxime Proxetil 200mg Tablet"></option>
      <option value="Clarithromycin 250mg Tablet"></option>
      <option value="Clarithromycin 500mg Tablet"></option>
      <option value="Erythromycin 250mg Tablet"></option>
      <option value="Ceftriaxone 1g Injection"></option>
      <option value="Cefotaxime 1g Injection"></option>
      <option value="Meropenem 1g Injection"></option>
      <option value="Fluconazole 150mg Tablet"></option>
      <option value="Fluconazole 200mg Tablet"></option>
      <option value="Itraconazole 100mg Capsule"></option>
      <option value="Itraconazole 200mg Capsule"></option>
      <option value="Terbinafine 250mg Tablet"></option>
      <option value="Voriconazole 200mg Tablet"></option>
      <option value="Griseofulvin 250mg Tablet"></option>
      <option value="Albendazole 400mg Chewable Tablet"></option>
      <option value="Ivermectin 6mg Tablet"></option>
      <option value="Ivermectin 12mg Tablet"></option>

      <!-- 3. Gastrointestinal, Antacids, PPIs, Antiemetics & Laxatives -->
      <option value="Pantoprazole 40mg Tablet"></option>
      <option value="Pantoprazole 20mg Tablet"></option>
      <option value="Pantoprazole 40mg IV Injection"></option>
      <option value="Pantoprazole + Domperidone (Pan-D)"></option>
      <option value="Omeprazole 20mg Capsule"></option>
      <option value="Omeprazole 40mg Capsule"></option>
      <option value="Omeprazole + Domperidone (Omez-D)"></option>
      <option value="Rabeprazole 20mg Tablet"></option>
      <option value="Rabeprazole + Domperidone (Rablet-D)"></option>
      <option value="Esomeprazole 20mg Tablet"></option>
      <option value="Esomeprazole 40mg Tablet"></option>
      <option value="Lansoprazole 30mg Capsule"></option>
      <option value="Ranitidine 150mg Tablet"></option>
      <option value="Famotidine 20mg Tablet"></option>
      <option value="Famotidine 40mg Tablet"></option>
      <option value="Sucralfate Syrup 1000mg/10ml"></option>
      <option value="Sucralfate + Oxetacaine Suspension"></option>
      <option value="Antacid Gel (Aluminium + Magnesium + Simethicone)"></option>
      <option value="Domperidone 10mg Tablet"></option>
      <option value="Domperidone Syrup 5mg/5ml"></option>
      <option value="Ondansetron 4mg Tablet"></option>
      <option value="Ondansetron 8mg Tablet"></option>
      <option value="Ondansetron Syrup 2mg/5ml"></option>
      <option value="Ondansetron 4mg/2ml Injection"></option>
      <option value="Metoclopramide 10mg Tablet"></option>
      <option value="Drotaverine 40mg Tablet (Drotin)"></option>
      <option value="Drotaverine 80mg Tablet"></option>
      <option value="Dicyclomine 10mg Tablet"></option>
      <option value="Hyoscine Butylbromide 10mg (Buscopan)"></option>
      <option value="Loperamide 2mg Capsule"></option>
      <option value="Racecadotril 100mg Capsule"></option>
      <option value="Oral Rehydration Salts (ORS Sachet 21.8g)"></option>
      <option value="Lactulose Solution 10g/15ml"></option>
      <option value="Bisacodyl 5mg Tablet (Dulcolax)"></option>
      <option value="Ispaghula Husk (Psyllium 5g Sachet)"></option>
      <option value="Cremaffin Liquid 15ml"></option>
      <option value="Polyethylene Glycol (PEG) Powder 17g"></option>
      <option value="Ursodeoxycholic Acid 300mg Tablet"></option>
      <option value="Chlordiazepoxide + Clidinium (Librax)"></option>

      <!-- 4. Respiratory, Cold, Cough & Antihistamines -->
      <option value="Cetirizine 10mg Tablet"></option>
      <option value="Cetirizine Syrup 5mg/5ml"></option>
      <option value="Levocetirizine 5mg Tablet"></option>
      <option value="Levocetirizine Syrup 2.5mg/5ml"></option>
      <option value="Levocetirizine + Montelukast Tablet (Montair-LC)"></option>
      <option value="Montelukast 10mg Tablet"></option>
      <option value="Montelukast 4mg Chewable Tablet"></option>
      <option value="Fexofenadine 120mg Tablet (Allegra)"></option>
      <option value="Fexofenadine 180mg Tablet"></option>
      <option value="Loratadine 10mg Tablet"></option>
      <option value="Desloratadine 5mg Tablet"></option>
      <option value="Bilastine 20mg Tablet"></option>
      <option value="Chlorpheniramine Maleate 4mg Tablet"></option>
      <option value="Pheniramine Maleate 25mg (Avil)"></option>
      <option value="Dextromethorphan Syrup 10mg/5ml"></option>
      <option value="Codeine Phosphate Syrup 15mg/5ml"></option>
      <option value="Ambroxol + Guaifenesin + Terbutaline Cough Syrup"></option>
      <option value="Levosalbutamol + Ambroxol Syrup (Ascoril-LS)"></option>
      <option value="Bromhexine 8mg Tablet"></option>
      <option value="N-Acetylcysteine 600mg Effervescent Tablet"></option>
      <option value="Salbutamol 2mg Tablet"></option>
      <option value="Salbutamol 4mg Tablet"></option>
      <option value="Salbutamol Inhaler 100mcg (Ventolin)"></option>
      <option value="Levosalbutamol Inhaler 50mcg"></option>
      <option value="Budesonide Inhaler 200mcg"></option>
      <option value="Budesonide Respules 0.5mg/2ml"></option>
      <option value="Fluticasone + Salmeterol Inhaler (Seretide)"></option>
      <option value="Formoterol + Budesonide Inhaler (Foracort 200)"></option>
      <option value="Formoterol + Budesonide Inhaler (Foracort 400)"></option>
      <option value="Ipratropium Bromide Respules 500mcg"></option>
      <option value="Tiotropium Inhaler 18mcg"></option>
      <option value="Theophylline 400mg SR Tablet"></option>
      <option value="Doxofylline 400mg Tablet"></option>
      <option value="Oxymetazoline 0.05% Nasal Spray"></option>
      <option value="Xylometazoline 0.1% Nasal Spray (Otrivin)"></option>
      <option value="Saline Nasal Drops 0.65%"></option>
      <option value="Fluticasone Furoate Nasal Spray 27.5mcg"></option>

      <!-- 5. Cardiovascular, Antihypertensives & Blood Thinners -->
      <option value="Telmisartan 20mg Tablet"></option>
      <option value="Telmisartan 40mg Tablet"></option>
      <option value="Telmisartan 80mg Tablet"></option>
      <option value="Telmisartan + Amlodipine (40mg/5mg Tablet)"></option>
      <option value="Telmisartan + Hydrochlorothiazide (40mg/12.5mg)"></option>
      <option value="Amlodipine 2.5mg Tablet"></option>
      <option value="Amlodipine 5mg Tablet"></option>
      <option value="Amlodipine 10mg Tablet"></option>
      <option value="S-Amlodipine 2.5mg Tablet"></option>
      <option value="Losartan 25mg Tablet"></option>
      <option value="Losartan 50mg Tablet"></option>
      <option value="Olmesartan 20mg Tablet"></option>
      <option value="Olmesartan 40mg Tablet"></option>
      <option value="Enalapril 2.5mg Tablet"></option>
      <option value="Enalapril 5mg Tablet"></option>
      <option value="Ramipril 2.5mg Tablet"></option>
      <option value="Ramipril 5mg Tablet"></option>
      <option value="Perindopril 4mg Tablet"></option>
      <option value="Atenolol 25mg Tablet"></option>
      <option value="Atenolol 50mg Tablet"></option>
      <option value="Metoprolol Tartrate 25mg Tablet"></option>
      <option value="Metoprolol Succinate 50mg ER Tablet"></option>
      <option value="Bisoprolol 2.5mg Tablet"></option>
      <option value="Bisoprolol 5mg Tablet"></option>
      <option value="Carvedilol 3.125mg Tablet"></option>
      <option value="Carvedilol 6.25mg Tablet"></option>
      <option value="Carvedilol 12.5mg Tablet"></option>
      <option value="Nebivolol 5mg Tablet"></option>
      <option value="Hydrochlorothiazide 12.5mg Tablet"></option>
      <option value="Hydrochlorothiazide 25mg Tablet"></option>
      <option value="Chlorthalidone 12.5mg Tablet"></option>
      <option value="Indapamide 1.5mg SR Tablet"></option>
      <option value="Furosemide 40mg Tablet (Lasix)"></option>
      <option value="Torsemide 10mg Tablet"></option>
      <option value="Torsemide 20mg Tablet"></option>
      <option value="Spironolactone 25mg Tablet"></option>
      <option value="Spironolactone 50mg Tablet"></option>
      <option value="Atorvastatin 10mg Tablet"></option>
      <option value="Atorvastatin 20mg Tablet"></option>
      <option value="Atorvastatin 40mg Tablet"></option>
      <option value="Rosuvastatin 5mg Tablet"></option>
      <option value="Rosuvastatin 10mg Tablet"></option>
      <option value="Rosuvastatin 20mg Tablet"></option>
      <option value="Fenofibrate 145mg Tablet"></option>
      <option value="Clopidogrel 75mg Tablet"></option>
      <option value="Clopidogrel + Aspirin 75mg/75mg Tablet"></option>
      <option value="Ticagrelor 90mg Tablet"></option>
      <option value="Prasugrel 10mg Tablet"></option>
      <option value="Dabigatran 110mg Capsule"></option>
      <option value="Rivaroxaban 10mg Tablet"></option>
      <option value="Rivaroxaban 15mg Tablet"></option>
      <option value="Apixaban 2.5mg Tablet"></option>
      <option value="Apixaban 5mg Tablet"></option>
      <option value="Warfarin 2mg Tablet"></option>
      <option value="Warfarin 5mg Tablet"></option>
      <option value="Digoxin 0.25mg Tablet"></option>
      <option value="Amiodarone 100mg Tablet"></option>
      <option value="Amiodarone 200mg Tablet"></option>
      <option value="Isosorbide Mononitrate 30mg SR Tablet"></option>
      <option value="Nitroglycerin 2.6mg CR Tablet"></option>
      <option value="Ivabradine 5mg Tablet"></option>

      <!-- 6. Endocrine, Diabetes & Thyroid -->
      <option value="Metformin 500mg Tablet"></option>
      <option value="Metformin 850mg Tablet"></option>
      <option value="Metformin 1000mg SR Tablet"></option>
      <option value="Glimepiride 1mg Tablet"></option>
      <option value="Glimepiride 2mg Tablet"></option>
      <option value="Glimepiride 3mg Tablet"></option>
      <option value="Glimepiride + Metformin (1mg/500mg Tablet)"></option>
      <option value="Glimepiride + Metformin (2mg/500mg Tablet)"></option>
      <option value="Gliclazide 40mg Tablet"></option>
      <option value="Gliclazide 80mg Tablet"></option>
      <option value="Gliclazide 60mg MR Tablet"></option>
      <option value="Vildagliptin 50mg Tablet"></option>
      <option value="Vildagliptin + Metformin (50mg/500mg Tablet)"></option>
      <option value="Sitagliptin 50mg Tablet"></option>
      <option value="Sitagliptin 100mg Tablet"></option>
      <option value="Sitagliptin + Metformin (50mg/500mg Tablet)"></option>
      <option value="Teneligliptin 20mg Tablet"></option>
      <option value="Linagliptin 5mg Tablet"></option>
      <option value="Dapagliflozin 5mg Tablet"></option>
      <option value="Dapagliflozin 10mg Tablet"></option>
      <option value="Empagliflozin 10mg Tablet"></option>
      <option value="Empagliflozin 25mg Tablet"></option>
      <option value="Pioglitazone 15mg Tablet"></option>
      <option value="Voglibose 0.2mg Tablet"></option>
      <option value="Voglibose 0.3mg Tablet"></option>
      <option value="Insulin Regular 100IU/ml"></option>
      <option value="Insulin NPH (Isophane) 100IU/ml"></option>
      <option value="Insulin Glargine 100IU/ml Pen (Lantus)"></option>
      <option value="Insulin Aspart 100IU/ml (Novorapid)"></option>
      <option value="Levothyroxine 25mcg Tablet"></option>
      <option value="Levothyroxine 50mcg Tablet"></option>
      <option value="Levothyroxine 75mcg Tablet"></option>
      <option value="Levothyroxine 100mcg Tablet"></option>
      <option value="Carbimazole 5mg Tablet"></option>
      <option value="Propylthiouracil 50mg Tablet"></option>

      <!-- 7. Vitamins, Minerals, Blood & Supplements -->
      <option value="Vitamin D3 60,000 IU Capsule"></option>
      <option value="Vitamin D3 Drops 400 IU/ml"></option>
      <option value="Calcium Carbonate 500mg + Vitamin D3 Tablet"></option>
      <option value="Calcium Citrate Malate + Vitamin D3 Tablet"></option>
      <option value="Methylcobalamin 1500mcg (Vitamin B12) Tablet"></option>
      <option value="Vitamin B-Complex (Becosules) Capsule"></option>
      <option value="Folic Acid 5mg Tablet"></option>
      <option value="Ferrous Ascorbate + Folic Acid Tablet (Orofer-XT)"></option>
      <option value="Ferrous Fumarate 200mg Tablet"></option>
      <option value="Vitamin C (Ascorbic Acid) 500mg Chewable"></option>
      <option value="Zinc Sulphate 50mg Tablet"></option>
      <option value="Multivitamin + Multimineral Daily Capsule"></option>
      <option value="Coenzyme Q10 100mg Capsule"></option>
      <option value="Omega-3 Fish Oil 1000mg Capsule"></option>
      <option value="Alpha Lipoic Acid 100mg Capsule"></option>
      <option value="Biotin 5mg Tablet"></option>
      <option value="Potassium Chloride 10mEq Tablet"></option>
      <option value="Magnesium Glycinate 250mg Tablet"></option>
      <option value="Glucosamine 500mg + Chondroitin Tablet"></option>
      <option value="Collagen Peptide Sachet 10g"></option>

      <!-- 8. Neurology, Psychiatry & Musculoskeletal -->
      <option value="Pregabalin 75mg Capsule"></option>
      <option value="Pregabalin 150mg Capsule"></option>
      <option value="Pregabalin + Methylcobalamin Capsule"></option>
      <option value="Gabapentin 100mg Capsule"></option>
      <option value="Gabapentin 300mg Capsule"></option>
      <option value="Baclofen 10mg Tablet"></option>
      <option value="Thiocolchicoside 4mg Capsule"></option>
      <option value="Thiocolchicoside 8mg Capsule"></option>
      <option value="Chlorzoxazone + Paracetamol Tablet"></option>
      <option value="Tizanidine 2mg Tablet"></option>
      <option value="Paroxetine 20mg Tablet"></option>
      <option value="Escitalopram 10mg Tablet"></option>
      <option value="Escitalopram 20mg Tablet"></option>
      <option value="Sertraline 50mg Tablet"></option>
      <option value="Fluoxetine 20mg Capsule"></option>
      <option value="Duloxetine 30mg Capsule"></option>
      <option value="Amitriptyline 10mg Tablet"></option>
      <option value="Amitriptyline 25mg Tablet"></option>
      <option value="Nortriptyline 25mg Tablet"></option>
      <option value="Clonazepam 0.25mg Tablet"></option>
      <option value="Clonazepam 0.5mg Tablet"></option>
      <option value="Alprazolam 0.25mg Tablet"></option>
      <option value="Alprazolam 0.5mg Tablet"></option>
      <option value="Lorazepam 1mg Tablet"></option>
      <option value="Zolpidem 5mg Tablet"></option>
      <option value="Zolpidem 10mg Tablet"></option>
      <option value="Betahistine 8mg Tablet"></option>
      <option value="Betahistine 16mg Tablet"></option>
      <option value="Cinnarizine 25mg Tablet"></option>
      <option value="Flunarizine 5mg Tablet"></option>
      <option value="Flunarizine 10mg Tablet"></option>
      <option value="Levetiracetam 500mg Tablet"></option>
      <option value="Sodium Valproate 200mg CR Tablet"></option>
      <option value="Sodium Valproate 500mg CR Tablet"></option>
      <option value="Carbamazepine 200mg Tablet"></option>
      <option value="Topiramate 25mg Tablet"></option>
      <option value="Donepezil 5mg Tablet"></option>

      <!-- 9. Dermatology, ENT, Ophthalmology & Topicals -->
      <option value="Clotrimazole Cream 1% Topical"></option>
      <option value="Clotrimazole Dusting Powder 1%"></option>
      <option value="Ketoconazole 2% Cream"></option>
      <option value="Ketoconazole 2% Shampoo"></option>
      <option value="Miconazole Oral Gel 2%"></option>
      <option value="Terbinafine Cream 1%"></option>
      <option value="Luliconazole Cream 1%"></option>
      <option value="Clobetasol Propionate 0.05% Ointment"></option>
      <option value="Betamethasone Dipropionate 0.05% Cream"></option>
      <option value="Hydrocortisone Cream 1%"></option>
      <option value="Mupirocin 2% Ointment (T-Bact)"></option>
      <option value="Fusidic Acid 2% Cream"></option>
      <option value="Neomycin + Polymyxin B + Bacitracin (Neosporin)"></option>
      <option value="Silver Sulfadiazine 1% Burn Cream"></option>
      <option value="Permethrin 5% Anti-Scabies Lotion"></option>
      <option value="Calamine Soothing Lotion 8%"></option>
      <option value="Povidone Iodine 5% Solution (Betadine)"></option>
      <option value="Povidone Iodine 5% Ointment"></option>
      <option value="Carboxymethylcellulose 0.5% Lubricant Eye Drops"></option>
      <option value="Moxifloxacin 0.5% Eye Drops"></option>
      <option value="Tobramycin 0.3% Eye Drops"></option>
      <option value="Olopatadine 0.1% Eye Drops"></option>
      <option value="Wax Dissolvent Ear Drops (Paradichlorobenzene)"></option>
      <option value="Clotrimazole 1% Ear Drops"></option>
    </datalist>

    <!-- Unified Compact Header Section -->
    <div class="shrink-0 bg-white border-b border-slate-200 px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
          <i class="fa-solid fa-stethoscope text-sm"></i>
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate" id="consult-patient-name">Patient Name</h3>
            <span id="consult-father-name" class="text-slate-500 font-medium text-xs hidden"></span>
            <span id="consult-patient-id" class="font-mono text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">MRN: -</span>
            <span id="consult-patient-vitals" class="text-xs text-slate-500">Age & Gender</span>
            <span id="consult-patient-blood" class="text-xs font-bold text-rose-600">Blood: -</span>
          </div>
          <div class="text-[11px] text-slate-500 truncate mt-0.5">
            Complaint: <span id="consult-reported-symptoms" class="text-slate-700 font-medium">-</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button type="button" onclick="openPatientDossier(activeConsultPatientId)" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
          <i class="fa-solid fa-folder-medical text-indigo-600"></i> Dossier
        </button>
        <button type="button" onclick="closeConsultModal()" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Sleek Step Progress Tabs -->
    <div class="shrink-0 bg-slate-50/80 border-b border-slate-200 px-4 sm:px-6 py-2 flex items-center justify-between gap-2 overflow-x-auto">
      <div class="flex items-center gap-1.5 sm:gap-2 min-w-max">
        <button type="button" onclick="goToConsultStep(1)" id="step-btn-1" class="consult-step-nav active-step flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition">
          <span class="step-num w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-black bg-indigo-600 text-white">1</span>
          <span>Diagnosis</span>
        </button>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>

        <button type="button" onclick="goToConsultStep(2)" id="step-btn-2" class="consult-step-nav flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
          <span class="step-num w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-black bg-slate-200 text-slate-700">2</span>
          <span>Prescriptions (Rx)</span>
          <span id="prescriptions-count-badge" class="hidden text-[10px] px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-mono font-bold leading-none">0</span>
        </button>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>

        <button type="button" onclick="goToConsultStep(3)" id="step-btn-3" class="consult-step-nav flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
          <span class="step-num w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-black bg-slate-200 text-slate-700">3</span>
          <span>Disposition & Orders</span>
        </button>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>

        <button type="button" onclick="goToConsultStep(4)" id="step-btn-4" class="consult-step-nav flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
          <span class="step-num w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-black bg-slate-200 text-slate-700">4</span>
          <span>Advice & Reports</span>
        </button>
      </div>

      <div class="text-xs text-slate-400 hidden md:block">
        Step <span id="current-step-indicator" class="text-slate-700 font-bold">1</span> of 4
      </div>
    </div>

    <!-- Main Form & Wizard Step Panels -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-5 bg-slate-50/50">
      <form onsubmit="handleConsultationSave(event)" id="consult-form" class="space-y-4">

        <!-- Restored Consultation Banner -->
        <div id="consult-restored-banner" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-sm">
          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center shrink-0 text-xs"><i class="fa-solid fa-flask-vial"></i></span>
            <div>
              <span class="font-bold block text-slate-900" id="restored-banner-title">Diagnostic Reports Received — Reviewing Previous Consultation</span>
              <span class="text-slate-600 block text-[11px]" id="restored-banner-subtitle">Pre-filled with previously saved diagnoses, prescriptions, and lab test orders.</span>
            </div>
          </div>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-200/80 text-amber-900 border border-amber-300 shrink-0 self-start sm:self-auto flex items-center gap-1">
            <i class="fa-solid fa-clock-rotate-left"></i> Restored Form
          </span>
        </div>

        <!-- ================= STEP 1: CLINICAL DIAGNOSIS ================= -->
        <div id="consult-step-panel-1" class="consult-step-panel space-y-4">
          <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3.5">
            
            <!-- Search & Add Bar -->
            <div>
              <div class="flex items-center gap-2">
                <div class="relative flex-1">
                  <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                  <input type="text" id="custom-diagnosis-input" list="common-diagnoses-list" placeholder="Search or type diagnosis (e.g. Viral Fever, Hypertension, Diabetes, Bronchitis)..." class="w-full h-10 pl-9 pr-3 text-xs sm:text-sm font-medium border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition" onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomDiagnosis();}">
                </div>
                <button type="button" onclick="addCustomDiagnosis()" class="h-10 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm shrink-0">
                  <i class="fa-solid fa-plus text-xs"></i> Add
                </button>
              </div>
            </div>

            <!-- Quick Suggestions -->
            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
              <span class="text-xs text-slate-400 font-medium mr-1">Quick:</span>
              <button type="button" onclick="addQuickDiag('Acute Viral Fever')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Viral Fever</button>
              <button type="button" onclick="addQuickDiag('Essential Hypertension')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Hypertension</button>
              <button type="button" onclick="addQuickDiag('Type 2 Diabetes Mellitus')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Diabetes T2</button>
              <button type="button" onclick="addQuickDiag('Acute Tonsillopharyngitis')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Pharyngitis</button>
              <button type="button" onclick="addQuickDiag('Acute Bronchitis')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Bronchitis</button>
              <button type="button" onclick="addQuickDiag('Acute Gastroenteritis')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Gastroenteritis</button>
              <button type="button" onclick="addQuickDiag('Urinary Tract Infection (UTI)')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ UTI</button>
              <button type="button" onclick="addQuickDiag('Migraine / Tension Headache')" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition">+ Migraine</button>
            </div>

            <!-- Selected Diagnoses Display -->
            <div class="pt-2 border-t border-slate-100">
              <div class="text-xs font-bold text-slate-700 mb-2">Diagnosed Conditions:</div>
              <div id="selected-diagnoses-display" class="flex flex-wrap gap-2 min-h-[38px] p-2.5 rounded-lg bg-slate-50 border border-slate-200"></div>
            </div>
          </div>

          <!-- Step 1 Navigation Buttons -->
          <div class="flex items-center justify-between pt-1">
            <button type="button" onclick="closeConsultModal()" class="h-9 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
              Cancel
            </button>
            <button type="button" onclick="validateStepAndProceed(1, 2)" class="h-9 px-5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
              <span>Next: Prescriptions (Rx)</span>
              <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </button>
          </div>
        </div>

        <!-- ================= STEP 2: MEDICAL PRESCRIPTIONS (Rx) ================= -->
        <div id="consult-step-panel-2" class="consult-step-panel hidden space-y-4">
          <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3.5">
            
            <!-- Category Tabs & Add Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
              <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 custom-scrollbar">
                <button type="button" onclick="filterDrugCategory('fever', this)" class="drug-cat-btn text-xs font-bold px-2.5 py-1 rounded-md border border-indigo-600 bg-indigo-600 text-white transition shrink-0" data-category="fever">Fever & Pain</button>
                <button type="button" onclick="filterDrugCategory('antibiotics', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="antibiotics">Antibiotics</button>
                <button type="button" onclick="filterDrugCategory('antacids', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="antacids">Antacids / PPI</button>
                <button type="button" onclick="filterDrugCategory('cold', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="cold">Cold & Cough</button>
                <button type="button" onclick="filterDrugCategory('heart', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="heart">BP & Heart</button>
                <button type="button" onclick="filterDrugCategory('diabetes', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="diabetes">Diabetes</button>
                <button type="button" onclick="filterDrugCategory('vitamins', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="vitamins">Vitamins</button>
                <button type="button" onclick="filterDrugCategory('topical', this)" class="drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0" data-category="topical">Topicals & Drops</button>
              </div>

              <button type="button" onclick="addMedicineInputRow()" class="h-8 px-3 rounded-lg border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                <i class="fa-solid fa-plus text-[11px]"></i> Add Row
              </button>
            </div>

            <!-- Quick Drug Chips Bar -->
            <div id="category-quick-chips" class="flex flex-wrap gap-1.5 p-2 bg-slate-50 border border-slate-200 rounded-lg min-h-[36px] max-h-24 overflow-y-auto custom-scrollbar"></div>

            <!-- Column Headers for Desktop -->
            <div class="hidden sm:grid grid-cols-12 gap-2 text-[11px] font-bold text-slate-400 uppercase px-3 pt-1">
              <div class="col-span-5">Medicine Name & Strength</div>
              <div class="col-span-2">Dose</div>
              <div class="col-span-2">Frequency</div>
              <div class="col-span-2">Duration</div>
              <div class="col-span-1 text-right">Action</div>
            </div>

            <!-- Medication Rows Container -->
            <div id="medicines-input-container" class="space-y-2.5"></div>

            <button type="button" onclick="addMedicineInputRow()" class="w-full h-9 rounded-lg border border-dashed border-slate-300 hover:border-indigo-400 bg-white hover:bg-indigo-50/40 text-slate-600 hover:text-indigo-700 font-bold text-xs transition flex items-center justify-center gap-1.5">
              <i class="fa-solid fa-plus text-indigo-600 text-xs"></i> Add Another Medicine
            </button>
          </div>

          <!-- Step 2 Navigation Buttons -->
          <div class="flex items-center justify-between pt-1">
            <button type="button" onclick="goToConsultStep(1)" class="h-9 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
              <i class="fa-solid fa-arrow-left text-[11px]"></i>
              <span>Back: Diagnosis</span>
            </button>
            <button type="button" onclick="validateStepAndProceed(2, 3)" class="h-9 px-5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
              <span>Next: Disposition & Orders</span>
              <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </button>
          </div>
        </div>

        <!-- ================= STEP 3: PATIENT DISPOSITION & CARE ORDERS ================= -->
        <div id="consult-step-panel-3" class="consult-step-panel hidden space-y-4">
          <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-4">
            
            <div class="text-xs font-bold text-slate-700">Select Care Destination:</div>

            <!-- 4 Clean Disposition Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="disposition-cards-grid">
              
              <!-- Option 1: Discharge with Medication -->
              <label onclick="handleDispositionChange('Normal Medicine')" class="disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 bg-white border-slate-200 hover:border-slate-300 shadow-sm" id="label-disposition-medicine">
                <input type="radio" name="dispositionOutcome" value="Normal Medicine" checked class="text-emerald-600 focus:ring-emerald-500">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-house-medical text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">Discharge & Home Care</span>
                  <span class="text-[11px] text-slate-500 block truncate">Prescribe medication & discharge</span>
                </div>
              </label>

              <!-- Option 2: Waiting for Lab / Diagnostic Reports -->
              <label onclick="handleDispositionChange('Waiting for Reports')" class="disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 bg-white border-slate-200 hover:border-slate-300 shadow-sm" id="label-disposition-reports">
                <input type="radio" name="dispositionOutcome" value="Waiting for Reports" class="text-amber-600 focus:ring-amber-500">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-flask-vial text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">Waiting for Lab Reports</span>
                  <span class="text-[11px] text-slate-500 block truncate">Order diagnostic tests & hold</span>
                </div>
              </label>

              <!-- Option 3: Admit to General Ward / OPD -->
              <label onclick="handleDispositionChange('OPD')" class="disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 bg-white border-slate-200 hover:border-slate-300 shadow-sm" id="label-disposition-opd">
                <input type="radio" name="dispositionOutcome" value="OPD" class="text-blue-600 focus:ring-blue-500">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-bed text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">Admit to Ward</span>
                  <span class="text-[11px] text-slate-500 block truncate">Hospital bed for observation/IV</span>
                </div>
              </label>

              <!-- Option 4: Critical Care / ICU Admission -->
              <label onclick="handleDispositionChange('ICU')" class="disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 bg-white border-slate-200 hover:border-slate-300 shadow-sm" id="label-disposition-icu">
                <input type="radio" name="dispositionOutcome" value="ICU" class="text-rose-600 focus:ring-rose-500">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-heart-pulse text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">Admit to ICU</span>
                  <span class="text-[11px] text-slate-500 block truncate">Intensive critical care</span>
                </div>
              </label>
            </div>

            <!-- Conditional Section A: Lab Tests Ordered Checklist (When 'Waiting for Reports' is chosen) -->
            <div id="tests-ordering-section" class="hidden bg-amber-50/60 border border-amber-200 rounded-xl p-3.5 space-y-2.5">
              <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                <i class="fa-solid fa-flask-vial text-amber-600"></i> Select Ordered Diagnostic Tests & Scans:
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Complete Blood Count (CBC)" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">CBC / Hemogram</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Urine Routine / Microscopy" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Urine Routine</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Chest X-Ray (PA View)" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Chest X-Ray</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="USG Abdomen & Pelvis" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Ultrasound (USG)</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Blood Sugar Fasting / PP" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Blood Sugar (FBS/PP)</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="12-Lead ECG" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">12-Lead ECG</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Serum Electrolytes (Na/K/Cl)" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Electrolytes</span>
                </label>
                <label class="flex items-center gap-1.5 bg-white p-2 rounded-lg border border-amber-200/80 cursor-pointer hover:bg-amber-50/40">
                  <input type="checkbox" value="Liver Function Test (LFT)" class="test-checkbox text-amber-600 rounded">
                  <span class="font-medium text-slate-800">Liver Profile (LFT)</span>
                </label>
              </div>
              <input type="text" id="custom-ordered-tests" placeholder="Other specific tests (e.g. Dengue Serology, CT Brain, Thyroid Panel)..." class="w-full h-9 px-3 text-xs border border-amber-300 rounded-lg bg-white focus:outline-none focus:border-amber-600">
            </div>

            <!-- Conditional Section B: Bed Allocation (When OPD or ICU is chosen) -->
            <div id="bed-allotment-section" class="hidden bg-blue-50/60 border border-blue-200 rounded-xl p-3.5 space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-blue-900 flex items-center gap-1.5">
                  <i class="fa-solid fa-bed text-blue-600"></i> Assign Hospital Bed *
                </label>
                <span id="bed-selection-type-badge" class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-200 text-blue-800">OPD Ward Bed</span>
              </div>
              <select id="allotment-bed-select" class="w-full h-10 px-3 text-xs sm:text-sm font-semibold border border-blue-300 rounded-lg bg-white focus:outline-none focus:border-blue-600 transition"></select>
            </div>
          </div>

          <!-- Step 3 Navigation Buttons -->
          <div class="flex items-center justify-between pt-1">
            <button type="button" onclick="goToConsultStep(2)" class="h-9 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
              <i class="fa-solid fa-arrow-left text-[11px]"></i>
              <span>Back: Prescriptions</span>
            </button>
            <button type="button" onclick="validateStepAndProceed(3, 4)" class="h-9 px-5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
              <span>Next: Advice & Summary</span>
              <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </button>
          </div>
        </div>

        <!-- ================= STEP 4: ADVICE, FILES & SUMMARY ================= -->
        <div id="consult-step-panel-4" class="consult-step-panel hidden space-y-4">
          <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-4">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
              <!-- Clinical Advice Textarea -->
              <div class="lg:col-span-7 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Doctor's Advice & Patient Instructions</label>
                <textarea id="consult-doctor-notes" rows="5" placeholder="e.g. Bed rest for 3 days. Adequate oral hydration. Avoid oily/cold foods. Review in OPD after 5 days with reports if symptoms persist..." class="w-full text-xs sm:text-sm font-medium border border-slate-300 rounded-lg p-3 bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition resize-none"></textarea>
              </div>

              <!-- Attach Files & Reports -->
              <div class="lg:col-span-5 space-y-2 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-slate-700">Attachments & Reports</label>
                    <button type="button" onclick="addFileInputRow()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                      <i class="fa-solid fa-plus text-[10px]"></i> Attach File
                    </button>
                  </div>
                  <div id="files-input-container" class="space-y-2 overflow-y-auto max-h-[120px] pr-1"></div>
                  <div id="files-empty-state" class="text-xs text-slate-400 italic text-center py-3 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    No files attached (Optional).
                  </div>
                </div>

                <!-- Previously Attached Files from earlier visit/reports -->
                <div id="previous-files-section" class="hidden space-y-1.5 pt-2 border-t border-slate-200">
                  <span class="text-[11px] font-bold text-slate-600 block flex items-center gap-1.5">
                    <i class="fa-solid fa-file-medical text-indigo-600"></i> Reports for this Visit:
                  </span>
                  <div id="previous-files-list" class="space-y-1.5 max-h-[100px] overflow-y-auto custom-scrollbar"></div>
                </div>
              </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 text-xs space-y-1">
              <span class="font-bold text-indigo-900 block text-xs">Consultation Summary:</span>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-0.5 text-slate-700">
                <div><strong>Diagnosis:</strong> <span id="summary-diagnoses-text" class="text-indigo-700 font-medium">-</span></div>
                <div><strong>Rx:</strong> <span id="summary-medications-text" class="text-indigo-700 font-medium">-</span></div>
                <div><strong>Disposition:</strong> <span id="summary-disposition-text" class="text-indigo-700 font-medium">-</span></div>
              </div>
            </div>
          </div>

          <!-- Step 4 Navigation & Final Submit Buttons -->
          <div class="flex items-center justify-between pt-1">
            <button type="button" onclick="goToConsultStep(3)" class="h-9 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
              <i class="fa-solid fa-arrow-left text-[11px]"></i>
              <span>Back: Disposition</span>
            </button>
            
            <button type="submit" id="btn-finalize-consult" class="h-10 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
              <i class="fa-solid fa-check-circle text-base"></i>
              <span>Finalize Consultation & Save</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
    function escapeJsQueue(str) { 
      return (str || '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;')
        .replace(/[\r\n]+/g, ' '); 
    }

  
    function toggleQueueDropdown(id) {
        const dropdown = document.getElementById(id + '-dropdown');
        const icon = document.getElementById(id + '-icon');
        if (dropdown.classList.contains('hidden')) {
            // Close others
            if (id !== 'dept') closeQueueDropdown('dept');
            if (id !== 'doc') closeQueueDropdown('doc');
            
            dropdown.classList.remove('hidden');
            setTimeout(() => {
                dropdown.classList.remove('opacity-0', 'scale-95');
                icon.classList.add('rotate-180');
            }, 10);
        } else {
            closeQueueDropdown(id);
        }
    }

    function closeQueueDropdown(id) {
        const dropdown = document.getElementById(id + '-dropdown');
        const icon = document.getElementById(id + '-icon');
        if (!dropdown) return;
        dropdown.classList.add('opacity-0', 'scale-95');
        if (icon) icon.classList.remove('rotate-180');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 200);
    }

    function selectQueueFilter(id, value, displayHtml) {
        document.getElementById('queue-' + id + '-filter').value = value;
        document.getElementById(id + '-display').innerHTML = displayHtml;
        closeQueueDropdown(id);
        renderQueuePipeline(currentQueue);
    }

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#dept-filter-container')) closeQueueDropdown('dept');
        if (!e.target.closest('#doc-filter-container')) closeQueueDropdown('doc');
    });

    let lineRunning = true;
  let currentQueue = [];
  let displayMode = 'kanban';
  let allDirectoryPatients = [];
  let allHospitalDoctors = [];
  let advanceAppointments = [];
  let advanceViewOpen = false;

  // Stage sliders and view mode states
  let stageSliderIndices = { 1: 0, 2: 0, 3: 0, 4: 0 };
  let stageModes = { 1: 'slider', 2: 'slider', 3: 'slider', 4: 'slider' };
  
  let pendingMoveArgs = null; // Stores arguments for the confirmation modal

  let activeConsultPatientId = null;
  let activeConsultAppId = null;
  let allBeds = [];
  let selectedDiagnoses = new Set();
  let currentConsultStep = 1;
  let activeConsultCategory = 'fever';

  window.addEventListener('DOMContentLoaded', () => {
    loadDepartments();
    fetchQueuePipeline();
    fetchBedsForModal();
    fetchAllDirectoryPatients();
    fetchAdvanceAppointments();
    loadHospitalDoctorsForAdvance();
    setupTouchSwipeForLanes();
    setInterval(fetchQueuePipeline, 10000);
  });
  
  async function fetchAllDirectoryPatients() {
      try {
          const res = await fetch('api/patients.php?action=get_all');
          const data = await res.json();
          if (data.status === 'success') {
              allDirectoryPatients = data.patients;
              populateAdvancePatientDropdown();
          }
      } catch (e) {}
  }

  function populateAdvancePatientDropdown() {
      const sel = document.getElementById('adv-patient-id');
      if (!sel) return;
      sel.innerHTML = '<option value="">Select registered patient...</option>';
      allDirectoryPatients.forEach(p => {
          sel.innerHTML += `<option value="${p.id}">${p.name} ${p.surname} (${p.id}) - 📞 ${p.phone || 'No phone'}</option>`;
      });
  }

    async function loadHospitalDoctorsForAdvance() {
      try {
          const res = await fetch('api/doctors.php?action=get_hospital_doctors');
          const data = await res.json();
          if (data.status === 'success') {
              allHospitalDoctors = data.doctors;
              const sel = document.getElementById('adv-doctor-id');
              if (sel) sel.innerHTML = '<option value="">Choose doctor...</option>';
              const filterDropdown = document.getElementById('doc-dropdown');
              if (filterDropdown) filterDropdown.innerHTML = '<div onclick="selectQueueFilter(\'doc\', \'All\', \'All Doctors\')" class="px-3 py-2 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer text-xs font-bold text-slate-700 transition">All Doctors</div>';
              
              allHospitalDoctors.forEach(d => {
                  const spec = d.categories && d.categories.length ? ' (' + d.categories.join(', ') + ')' : '';
                  const safeName = escapeJsQueue(d.name);
                  const optHTML = '<option value="' + d.id + '">' + d.name + spec + '</option>';
                  if (sel) sel.innerHTML += optHTML;
                                      if (filterDropdown) {
                        filterDropdown.innerHTML += `<div onclick="selectQueueFilter('doc', '${d.id}', '${safeName}')" class="px-3 py-2 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer text-xs font-bold text-slate-700 transition">${d.name}${spec}</div>`;
                    }
              });
          }
      } catch (e) {}
  }
  
  async function fetchAdvanceAppointments() {
      try {
          const res = await fetch('api/queue.php?action=get_advance_appointments');
          const data = await res.json();
          if (data.status === 'success') {
              advanceAppointments = data.appointments;
              document.getElementById('advance-count-badge').textContent = advanceAppointments.length;
              renderAdvanceAppointments();
          }
      } catch (e) {}
  }

  function toggleAdvanceScheduleView() {
      advanceViewOpen = !advanceViewOpen;
      const container = document.getElementById('advance-schedule-container');
      const chevron = document.getElementById('advance-chevron');
      if (advanceViewOpen) {
          container.classList.remove('hidden');
          chevron.className = "fa-solid fa-chevron-up text-[10px]";
          renderAdvanceAppointments();
      } else {
          container.classList.add('hidden');
          chevron.className = "fa-solid fa-chevron-down text-[10px]";
      }
  }

  function renderAdvanceAppointments() {
      const container = document.getElementById('advance-schedule-cards');
      if (!container) return;
      container.innerHTML = '';
      if (advanceAppointments.length === 0) {
          container.innerHTML = `<div class="col-span-full text-center py-6 text-slate-400 text-xs">No upcoming pre-booked appointments found. Click "+ Schedule New" to book ahead.</div>`;
          return;
      }
      advanceAppointments.forEach(a => {
          const isToday = a.date === new Date().toISOString().substring(0, 10);
          const apptCode = a.appointment_code || ('APP-' + String(a.id).padStart(4, '0'));
          const aStr = encodeURIComponent(JSON.stringify({
              appointment_id: a.id,
              appointment_code: apptCode,
              id: a.patient_id,
              name: a.name,
              surname: a.surname,
              doctor_id: a.doctor_id,
              doctor: a.doctor_name,
              type: a.type || 'General Consultation',
              symptoms: a.symptoms || '',
              date: a.date,
              slot: a.slot,
              stage: a.stage || 1,
              status: a.status || 'Pre-Booked'
          }));

          const card = document.createElement('div');
          card.className = "bg-slate-50 hover:bg-white rounded-xl p-3.5 border border-slate-200 transition shadow-sm flex flex-col justify-between";
          card.innerHTML = `
            <div>
              <div class="flex items-center justify-between gap-1 mb-2">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${isToday ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200'}">
                    ${isToday ? 'Today' : a.date}
                  </span>
                  <span class="text-[10px] font-mono font-black px-1.5 py-0.5 rounded bg-white text-indigo-700 border border-slate-200">
                    ${apptCode}
                  </span>
                </div>
                <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                  <i class="fa-regular fa-clock text-slate-400 mr-1"></i>${a.slot || 'N/A'}
                </span>
              </div>
              <div class="flex items-start justify-between gap-1">
                <div class="cursor-pointer group" onclick="openPatientDossier('${a.patient_id}')" title="Click to view full patient dossier">
                  <h5 class="font-bold text-slate-900 text-xs leading-tight group-hover:text-blue-600 transition flex items-center gap-1">
                    <span>${a.name} ${a.surname}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400 opacity-0 group-hover:opacity-100 transition"></i>
                  </h5>
                  <p class="text-[10px] font-mono text-slate-500 mt-0.5">${a.patient_id}</p>
                </div>
                <button type="button" onclick="openPatientDossier('${a.patient_id}')" class="text-[10px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold px-2 py-0.5 rounded-lg transition flex items-center gap-1 shadow-sm shrink-0" title="View Patient Clinical Dossier">
                  <i class="fa-solid fa-file-medical"></i> Dossier
                </button>
              </div>
              <div class="mt-2 text-[11px] text-slate-600 bg-white p-2 rounded-lg border border-slate-100 space-y-0.5">
                <div><span class="text-slate-400">Doctor:</span> <strong class="text-slate-800">${a.doctor_name || 'Unassigned'}</strong></div>
                <div class="truncate"><span class="text-slate-400">Reason:</span> ${a.symptoms || 'General Consultation'}</div>
              </div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between gap-1">
              <span class="text-[10px] font-semibold text-slate-500 truncate">${a.status}</span>
              <div class="flex items-center gap-1 shrink-0">
                <button onclick="openEditAppointmentModal('${aStr}')" class="w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 flex items-center justify-center text-[10px] transition shadow-sm" title="Edit Pre-Booked Appointment"><i class="fa-solid fa-pen"></i></button>
                <button onclick="confirmDeleteAppointment(${a.id}, '${escapeJsQueue(a.name)} ${escapeJsQueue(a.surname)}', '${apptCode}')" class="w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-rose-50 text-rose-600 flex items-center justify-center text-[10px] transition shadow-sm" title="Cancel/Delete Pre-Booked Appointment"><i class="fa-solid fa-trash-can"></i></button>
                ${a.stage === 1 ? `<button onclick="checkInAdvancePatient(${a.id})" class="text-[10px] bg-teal-600 hover:bg-teal-700 text-white font-bold px-2 py-1 rounded-lg transition shadow-sm flex items-center gap-1"><i class="fa-solid fa-check"></i> Check-In</button>` : ''}
              </div>
            </div>
          `;
          container.appendChild(card);
      });
  }

  async function checkInAdvancePatient(appointmentId) {
      try {
          const res = await fetch('api/queue.php?action=update_status', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({
                  appointment_id: appointmentId,
                  patient_id: '',
                  status: 'Available at Hospital',
                  stage: 2,
                  event_desc: 'Pre-booked patient arrived at hospital and checked in.'
              })
          });
          const data = await res.json();
          if (data.status === 'success') {
              showToast('Checked In', 'Pre-booked appointment moved to Available at Hospital.');
              fetchAdvanceAppointments();
              fetchQueuePipeline();
          }
      } catch (e) {}
  }

  let selectedAdvPatient = null;
  
  
  function handlePatientSearch() {
      const rawQ = (document.getElementById('queue-patient-search').value || '').trim();
      const dropdown = document.getElementById('patient-search-dropdown');
      dropdown.innerHTML = '';
      if (rawQ.length < 1) {
          dropdown.classList.add('hidden');
          return;
      }
      
      const terms = rawQ.toLowerCase().split(/\s+/).filter(Boolean);
      const filtered = (allDirectoryPatients || []).filter(p => {
          if (terms.length === 0) return true;
          const searchable = [
              p.id,
              p.name,
              p.surname,
              `${p.name || ''} ${p.surname || ''}`,
              `${p.surname || ''} ${p.name || ''}`,
              `${p.name || ''} ${p.father_name || ''} ${p.surname || ''}`,
              p.father_name,
              p.phone,
              p.blood_group,
              `blood ${p.blood_group || ''}`,
              p.gender,
              p.age ? `${p.age}` : '',
              p.age ? `${p.age} y` : '',
              p.demographics,
              p.emergency_contact_name,
              p.emergency_contact_phone,
              p.reg_date
          ].filter(Boolean).join(' ').toLowerCase();

          return terms.every(term => searchable.includes(term));
      });
      
      if (filtered.length === 0) {
          dropdown.innerHTML = '<div class="p-3 text-xs text-slate-500 text-center">No matching patients</div>';
      } else {
          filtered.slice(0, 10).forEach(p => {
              const div = document.createElement('div');
              div.className = "p-3 hover:bg-slate-50 cursor-pointer flex justify-between items-center transition";
              div.innerHTML = `
                  <div>
                      <div class="font-bold text-slate-900 text-xs">${p.name} ${p.surname}</div>
                      <div class="text-[10px] text-slate-500 font-mono mt-0.5">${p.id}</div>
                      <div class="text-[10px] text-slate-500 mt-0.5">📞 ${p.phone || 'N/A'} &nbsp; 🩸 ${p.blood_group || 'Unknown'} &nbsp; 👤 ${p.gender || 'Unknown'}</div>
                  </div>
                  <button class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm" onclick="openBookingModalFromSearch('${p.id}', '${escapeJsQueue(p.name)}', '${escapeJsQueue(p.surname)}')">Add to Line</button>
              `;
              dropdown.appendChild(div);
          });
      }
      dropdown.classList.remove('hidden');
  }

      async function loadDepartments() {
        try {
            const res = await fetch('api/booking.php?action=get_categories');
            const data = await res.json();
            if (data.status === 'success') {
                const dropdown = document.getElementById('dept-dropdown');
                if (dropdown) {
                    data.categories.forEach(d => {
                        const safeName = escapeJsQueue(d.name);
                                                dropdown.innerHTML += `<div onclick="selectQueueFilter('dept', '${d.id}', '${safeName}')" class="px-3 py-2 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer text-xs font-bold text-slate-700 transition">${d.name}</div>`;
                    });
                }
            }
        } catch (e) {}
    }

  // --- Custom Dropdown for Consultation Type ---
  function toggleAdvTypeDropdown(e) {
      if (e) e.stopPropagation();
      const menu = document.getElementById('adv-type-dropdown-menu');
      if (menu.classList.contains('hidden')) {
          menu.classList.remove('hidden');
      } else {
          menu.classList.add('hidden');
      }
  }

  function selectAdvTypeOption(value, iconClass, textClass, bgClass) {
      document.getElementById('adv-type').value = value;
      document.getElementById('adv-type-display').textContent = value;
      
      const icon = document.getElementById('adv-type-icon');
      icon.className = `fa-solid ${iconClass} text-[10px]`;
      
      const iconContainer = icon.parentElement;
      iconContainer.className = `absolute left-3.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-md flex items-center justify-center transition ${bgClass} ${textClass} group-hover:bg-indigo-600 group-hover:text-white`;
      
      document.getElementById('adv-type-dropdown-menu').classList.add('hidden');
  }

  // Close custom dropdown when clicking outside
  document.addEventListener('click', (e) => {
      const typeContainer = document.getElementById('adv-custom-type-dropdown-container');
      if (typeContainer && !typeContainer.contains(e.target)) {
          const menu = document.getElementById('adv-type-dropdown-menu');
          if (menu) menu.classList.add('hidden');
      }
      
      if (!e.target.closest('.relative')) {
          const dropdown = document.getElementById('patient-search-dropdown');
          if (dropdown) dropdown.classList.add('hidden');
      }
      if (!e.target.closest('#adv-patient-search-box')) {
          const advDropdown = document.getElementById('adv-patient-suggestions');
          if (advDropdown) advDropdown.classList.add('hidden');
      }
  });

  function openBookingModalFromSearch(id, name, surname) {
      document.getElementById('patient-search-dropdown').classList.add('hidden');
      document.getElementById('queue-patient-search').value = '';
      if (typeof openBookingModal === 'function') {
          openBookingModal(id, name, surname);
      }
  }
  
  function setQueueDisplayMode(mode) {
    displayMode = mode;
    document.getElementById('btn-queue-view-kanban').className = mode === 'kanban' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold" : "px-3 py-1.5 rounded-lg text-slate-600 font-semibold";
    document.getElementById('btn-queue-view-table').className = mode === 'table' ? "px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-bold" : "px-3 py-1.5 rounded-lg text-slate-600 font-semibold";
    
    document.getElementById('queue-container-kanban').classList.toggle('hidden', mode !== 'kanban');
    document.getElementById('queue-container-table').classList.toggle('hidden', mode !== 'table');
    renderQueuePipeline(currentQueue);
  }

  async function toggleDoctorLine() {
    try {
        const nextState = !lineRunning;
        await fetch('api/queue.php?action=toggle_line', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ running: nextState ? 1 : 0 })
        });
        lineRunning = nextState;
        fetchQueuePipeline();
        showToast('Line Status Updated', lineRunning ? 'Doctor has arrived. Line started!' : 'Line paused.');
    } catch (e) {}
  }

  async function fetchQueuePipeline() {
      try {
          const res = await fetch('api/queue.php?action=get_queue');
          const data = await res.json();
          if (data.status === 'success') {
              lineRunning = data.lineRunning;
              currentQueue = data.queue;
              renderQueuePipeline(currentQueue);
          }
      } catch (e) { console.error(e); }
  }

  function toggleLaneMode(stageNum) {
      stageModes[stageNum] = stageModes[stageNum] === 'slider' ? 'stack' : 'slider';
      renderQueuePipeline(currentQueue);
  }

  function slideStage(stageNum, delta) {
      const list = getStageList(stageNum);
      if (!list || list.length <= 1) return;
      let nextIdx = stageSliderIndices[stageNum] + delta;
      if (nextIdx < 0) nextIdx = list.length - 1;
      if (nextIdx >= list.length) nextIdx = 0;
      stageSliderIndices[stageNum] = nextIdx;
      renderLane(`lane-stage-${stageNum}`, list, stageNum);
  }

  function getStageList(stageNum) {
      const filterDept = document.getElementById('queue-dept-filter').value;
      const filterDoc = document.getElementById('queue-doctor-filter').value;
const filtered = currentQueue.filter(p => (filterDept === 'All' || String(p.dept) === String(filterDept)) && (filterDoc === 'All' || String(p.doctor_id) === String(filterDoc)));
      return filtered.filter(p => p.stage === stageNum);
  }

  function renderQueuePipeline(queue) {
    const filterDept = document.getElementById('queue-dept-filter').value;
      const filterDoc = document.getElementById('queue-doctor-filter').value;
const btnToggle = document.getElementById('btn-toggle-line');
    const statusPill = document.getElementById('line-status-pill');

    if (lineRunning) {
      statusPill.className = "text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1.5";
      statusPill.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live Line Active';
      btnToggle.className = "bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition flex items-center gap-2";
      btnToggle.innerHTML = '<i class="fa-solid fa-pause"></i> Pause Hospital Line';
    } else {
      statusPill.className = "text-xs px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 flex items-center gap-1.5";
      statusPill.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500"></span> Line Idle';
      btnToggle.className = "bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition flex items-center gap-2";
      btnToggle.innerHTML = '<i class="fa-solid fa-play"></i> Start Hospital Line';
    }

    const filtered = queue.filter(p => (filterDept === 'All' || String(p.dept) === String(filterDept)) && (filterDoc === 'All' || String(p.doctor_id) === String(filterDoc)));

    if (displayMode === 'kanban') {
        const stage1 = filtered.filter(p => p.stage === 1);
        const stage2 = filtered.filter(p => p.stage === 2);
        const stage3 = filtered.filter(p => p.stage === 3);
        const stage4 = filtered.filter(p => p.stage === 4);

        document.getElementById('count-stage-1').textContent = stage1.length;
        document.getElementById('count-stage-2').textContent = stage2.length;
        document.getElementById('count-stage-3').textContent = stage3.length;
        document.getElementById('count-stage-4').textContent = stage4.length;

        renderLane('lane-stage-1', stage1, 1);
        renderLane('lane-stage-2', stage2, 2);
        renderLane('lane-stage-3', stage3, 3);
        renderLane('lane-stage-4', stage4, 4);
    } else {
        const tbody = document.getElementById('queue-table-tbody');
        tbody.innerHTML = '';
        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-400 py-8 text-sm">Line is empty.</td></tr>`;
            return;
        }
        filtered.forEach(p => {
            const isEmergency = p.type === 'Emergency Case';
            const apptCode = p.appointment_code || ('APP-' + String(p.id).padStart(4, '0'));
            const pStr = encodeURIComponent(JSON.stringify(p));
            tbody.innerHTML += `
              <tr class="hover:bg-slate-50 transition border-b ${isEmergency ? 'bg-rose-50/70 border-l-4 border-l-rose-600 font-medium' : ''}">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="bg-blue-600 text-white text-xs font-black px-2 py-0.5 rounded-lg shadow-sm">Token #${p.token_no || '-'}</span>
                    <span class="font-mono font-black text-[11px] px-2 py-0.5 rounded ${isEmergency ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200'}">${apptCode}</span>
                  </div>
                <td class="py-3 px-4 font-bold text-slate-700">
                  ${p.status === 'Waiting for Reports' 
                    ? `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 w-fit shadow-sm"><i class="fa-solid fa-flask-vial text-amber-600"></i> Waiting for Reports</span>`
                    : `Stage ${p.stage}: ${p.status}`}
                </td>
                <td class="py-3 px-4">
                  <button type="button" onclick="openPatientDossier('${p.id}')" class="font-bold text-slate-900 hover:text-blue-600 text-left transition flex items-center gap-1 group">
                    <span>${p.name} ${p.surname}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400 group-hover:text-blue-600"></i>
                  </button>
                  <div class="text-[10px] text-slate-500 font-mono">${p.id}</div>
                  <div class="text-[10px] text-rose-500 font-bold mt-0.5">🩸 ${p.blood_group || 'Unknown'}</div>
                </td>
                <td class="py-3 px-4">
                  ${isEmergency 
                    ? `<span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-rose-600 text-white animate-pulse flex items-center gap-1 shadow-sm w-fit"><i class="fa-solid fa-triangle-exclamation"></i> EMERGENCY</span>` 
                    : `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">Regular</span>`}
                </td>
                <td class="py-3 px-4">${p.doctor}</td>
                <td class="py-3 px-4 truncate max-w-[150px]">${p.symptoms}</td>
                <td class="py-3 px-4 text-right flex items-center justify-end gap-1.5 flex-wrap">
                  <button type="button" onclick="openPatientDossier('${p.id}')" class="text-[10px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold px-2 py-1 rounded-lg transition flex items-center gap-1 shadow-sm" title="View Patient Dossier"><i class="fa-solid fa-folder-medical"></i> Dossier</button>
                  <button type="button" onclick="openEditAppointmentModal('${pStr}')" class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-bold px-2 py-1 rounded-lg transition shadow-sm" title="Edit Appointment"><i class="fa-solid fa-pen"></i></button>
                  <button type="button" onclick="confirmDeleteAppointment(${p.appointment_id}, '${escapeJsQueue(p.name)} ${escapeJsQueue(p.surname)}', '${apptCode}')" class="text-[10px] bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold px-2 py-1 rounded-lg transition shadow-sm" title="Delete Appointment"><i class="fa-solid fa-trash-can"></i></button>
                  ${getActionButtonsForCard(p, p.stage)}
                </td>
              </tr>
            `;
        });
    }
  }

  function renderLane(laneId, list, stageNum) {
    const container = document.getElementById(laneId);
    container.innerHTML = '';

    const ind = document.getElementById(`slider-indicator-stage-${stageNum}`);
    const btnPrev = document.getElementById(`btn-prev-stage-${stageNum}`);
    const btnNext = document.getElementById(`btn-next-stage-${stageNum}`);
    const btnMode = document.getElementById(`btn-mode-stage-${stageNum}`);

    if (list.length === 0) {
      if (ind) ind.classList.add('hidden');
      if (btnPrev) btnPrev.classList.add('hidden');
      if (btnNext) btnNext.classList.add('hidden');
      container.innerHTML = `<div class="text-slate-400 text-xs text-center py-12 flex flex-col items-center justify-center gap-1.5"><i class="fa-solid fa-inbox text-slate-300 text-xl"></i>No patients in this stage</div>`;
      return;
    }

    const mode = stageModes[stageNum];
    if (btnMode) {
      btnMode.className = mode === 'slider' 
        ? "w-6 h-6 rounded-md bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-[10px] shadow-sm transition" 
        : "w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center text-[10px] shadow-sm transition";
      btnMode.title = mode === 'slider' ? "Switch to Stack View" : "Switch to Slider View";
      btnMode.innerHTML = mode === 'slider' ? '<i class="fa-solid fa-sliders"></i>' : '<i class="fa-solid fa-bars"></i>';
    }

    if (mode === 'slider') {
      if (stageSliderIndices[stageNum] >= list.length) stageSliderIndices[stageNum] = list.length - 1;
      if (stageSliderIndices[stageNum] < 0) stageSliderIndices[stageNum] = 0;
      const currentIdx = stageSliderIndices[stageNum];

      if (ind) {
        ind.classList.remove('hidden');
        ind.textContent = `${currentIdx + 1} / ${list.length}`;
      }
      if (btnPrev) btnPrev.classList.toggle('hidden', list.length <= 1);
      if (btnNext) btnNext.classList.toggle('hidden', list.length <= 1);

      const p = list[currentIdx];
      const card = createPatientCard(p, stageNum, true);
      container.appendChild(card);

      // Pagination dots indicator
      if (list.length > 1) {
        const dots = document.createElement('div');
        dots.className = "flex items-center justify-center gap-1.5 pt-3 pb-1";
        list.forEach((_, i) => {
          const dot = document.createElement('button');
          dot.className = `h-1.5 rounded-full transition-all ${i === currentIdx ? 'w-5 bg-blue-600' : 'w-2 bg-slate-300 hover:bg-slate-400'}`;
          dot.onclick = () => { stageSliderIndices[stageNum] = i; renderLane(laneId, list, stageNum); };
          dots.appendChild(dot);
        });
        container.appendChild(dots);
      }
    } else {
      // Stack Mode
      if (ind) ind.classList.add('hidden');
      if (btnPrev) btnPrev.classList.add('hidden');
      if (btnNext) btnNext.classList.add('hidden');

      const stackDiv = document.createElement('div');
      stackDiv.className = "space-y-3 max-h-[60vh] overflow-y-auto custom-scrollbar pr-1";
      list.forEach(p => {
        stackDiv.appendChild(createPatientCard(p, stageNum, false));
      });
      container.appendChild(stackDiv);
    }
  }

  function createPatientCard(p, stageNum, isSlider = false) {
    const isEmergency = p.type === 'Emergency Case';
    const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
    const pStr = encodeURIComponent(JSON.stringify(p));
    const card = document.createElement('div');
    card.className = isEmergency 
      ? "bg-gradient-to-b from-rose-50/90 via-white to-white rounded-2xl p-4 border-2 border-rose-500 shadow-md shadow-rose-500/15 ring-2 ring-rose-400/30 space-y-3 transition hover:shadow-xl"
      : "bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3 transition hover:shadow-md";

    card.innerHTML = `
      <div class="flex items-start justify-between gap-2">
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="bg-blue-600 text-white font-black text-[11px] px-2.5 py-0.5 rounded-lg shadow-sm">Token #${p.token_no || '1'}</span>
          <span class="text-[10px] font-mono font-black px-2 py-0.5 rounded-md ${isEmergency ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200'}">${apptCode}</span>
          ${isEmergency 
            ? `<span class="bg-rose-600 text-white font-black text-[10px] px-2.5 py-0.5 rounded-md uppercase tracking-wider flex items-center gap-1 animate-pulse shadow-sm"><i class="fa-solid fa-triangle-exclamation"></i> Emergency</span>` 
            : `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">Regular</span>`}
        </div>
        <div class="flex items-center gap-1">
          <button onclick="openEditAppointmentModal('${pStr}')" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center text-[10px] transition shadow-sm" title="Edit Appointment"><i class="fa-solid fa-pen"></i></button>
          <button onclick="confirmDeleteAppointment(${p.appointment_id}, '${escapeJsQueue(p.name)} ${escapeJsQueue(p.surname)}', '${apptCode}')" class="w-6 h-6 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-[10px] transition shadow-sm" title="Delete / Cancel Appointment"><i class="fa-solid fa-trash-can"></i></button>
        </div>
      </div>

      ${isEmergency ? `
        <div class="bg-rose-100/90 border border-rose-200 text-rose-800 text-[10px] font-extrabold px-2.5 py-1 rounded-xl flex items-center gap-1.5">
          <i class="fa-solid fa-bell text-rose-600 animate-bounce"></i>
          <span>URGENT EMERGENCY CASE • IMMEDIATE CARE</span>
        </div>
      ` : ''}

      <div class="cursor-pointer group" onclick="openPatientDossier('${p.id}')" title="Click to view full patient dossier and history">
        <h4 class="font-extrabold text-slate-900 text-base leading-tight group-hover:text-blue-600 transition flex items-center gap-1.5">
          <span>${p.name} ${p.surname}</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition"></i>
        </h4>
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
          <span class="font-mono text-[11px] font-bold text-slate-600">${p.id}</span>
          <span>•</span>
          <span class="text-[10px] text-rose-500 font-bold">🩸 ${p.blood_group || 'Unknown'}</span>
          ${p.gender ? `<span>•</span><span class="text-[10px] text-slate-500">${p.gender}</span>` : ''}
          ${p.age ? `<span>•</span><span class="text-[10px] text-slate-500">${p.age}y</span>` : ''}
        </div>
      </div>

      <div class="bg-slate-50 rounded-xl p-2.5 text-[11px] space-y-1 border border-slate-100">
        <div class="text-slate-600 flex items-center gap-1.5"><i class="fa-solid fa-user-doctor text-slate-400"></i> <strong>Doctor:</strong> <span class="truncate">${p.doctor}</span></div>
        <div class="text-slate-700 flex items-start gap-1.5"><i class="fa-solid fa-stethoscope text-slate-400 mt-0.5"></i> <span class="truncate"><strong>Reason:</strong> ${p.symptoms}</span></div>
      </div>

      <!-- Quick Action: Full Patient Dossier -->
      <button type="button" onclick="openPatientDossier('${p.id}')" class="w-full bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 hover:border-emerald-300 font-bold py-1.5 px-3 rounded-xl text-[11px] transition flex items-center justify-center gap-1.5 shadow-sm" title="View complete patient clinical history, files, and dossier">
        <i class="fa-solid fa-folder-medical text-emerald-600"></i> View Patient Dossier & Info
      </button>

      <div class="flex items-center justify-between pt-1 border-t border-slate-100 gap-1.5">
        ${p.status === 'Waiting for Reports' 
          ? `<span class="text-[10px] font-bold text-amber-800 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-md flex items-center gap-1 shadow-sm"><i class="fa-solid fa-flask-vial text-amber-600"></i> Waiting for Reports</span>`
          : `<span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md truncate">${p.status}</span>`}
        <div class="flex items-center gap-1.5">${getActionButtonsForCard(p, stageNum)}</div>
      </div>
    `;
    return card;
  }

  function getActionButtonsForCard(p, stage) {
    const pStr = encodeURIComponent(JSON.stringify(p));
    let html = '';
    if (stage === 1) {
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'Available at Hospital', 2, 'Patient arrived at hospital desk.')" class="text-[10px] bg-teal-600 hover:bg-teal-700 text-white font-bold px-2 py-1 rounded">Mark Arrived</button>`;
    } else if (stage === 2) {
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'Checked-In', 1, 'Reverted to checked-in.')" class="text-[10px] bg-slate-200 text-slate-700 font-bold px-2 py-1 rounded"><i class="fa-solid fa-rotate-left"></i></button>`;
      html += `<button onclick="attemptMoveToWaiting('${pStr}')" class="text-[10px] bg-amber-600 hover:bg-amber-700 text-white font-bold px-2 py-1 rounded">Send to Waiting</button>`;
    } else if (stage === 3) {
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'Available at Hospital', 2, 'Reverted to available.')" class="text-[10px] bg-slate-200 text-slate-700 font-bold px-2 py-1 rounded"><i class="fa-solid fa-rotate-left"></i></button>`;
      if (p.status === 'Waiting for Reports') {
        html += `<button onclick="promptQueueStatusMove('${pStr}', 'In Consulting Room', 4, 'Lab reports ready. Called back into Consulting Room.')" class="text-[10px] bg-amber-600 hover:bg-amber-700 text-white font-bold px-2 py-1 rounded flex items-center gap-1 shadow-sm"><i class="fa-solid fa-file-medical"></i> Reports Ready</button>`;
        html += `<button onclick="openConsultationModal(${p.appointment_id})" class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2 py-1 rounded">Prescribe</button>`;
      } else {
        html += `<button onclick="promptQueueStatusMove('${pStr}', 'In Consulting Room', 4, 'Called into consulting room.')" class="text-[10px] bg-purple-600 hover:bg-purple-700 text-white font-bold px-2 py-1 rounded">Call to Room</button>`;
      }
    } else if (stage === 4) {
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'In Waiting Area', 3, 'Reverted to waiting room.')" class="text-[10px] bg-slate-200 text-slate-700 font-bold px-2 py-1 rounded"><i class="fa-solid fa-rotate-left"></i></button>`;
      html += `<button onclick="openConsultationModal(${p.appointment_id})" class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2 py-1 rounded">Diagnose</button>`;
    }
    return html;
  }

  function attemptMoveToWaiting(pStr) {
    if (!lineRunning) {
      showToast('Doctor Line Idle', 'Please start the Line before admitting patients to waiting area.', 'error');
      return;
    }
    promptQueueStatusMove(pStr, 'In Waiting Area', 3, 'Patient transferred to Waiting Lounge.');
  }

  // --- Confirmation Modal Logic ---
  function promptQueueStatusMove(pStr, new_status, new_stage, event_desc) {
    const p = JSON.parse(decodeURIComponent(pStr));
    
    // Cross reference with allDirectoryPatients if any field is missing
    const dirPatient = (allDirectoryPatients || []).find(d => d.id === p.id) || {};

    const initials = (((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : '')) || 'P').toUpperCase();
    document.getElementById('confirm-move-avatar').textContent = initials;
    document.getElementById('confirm-move-name').textContent = `${p.name} ${p.surname}`;
    document.getElementById('confirm-move-token').textContent = `Token #${p.token_no || '1'}`;
    const codeBadge = document.getElementById('confirm-move-app-code');
    if (codeBadge) codeBadge.textContent = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));
    
    const isEmergency = p.type === 'Emergency Case';
    const prioBadge = document.getElementById('confirm-move-priority');
    prioBadge.textContent = isEmergency ? 'Emergency' : 'Regular';
    prioBadge.className = isEmergency 
      ? 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200' 
      : 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700';

    document.getElementById('confirm-move-mrn').textContent = `MRN: ${p.id}`;
    document.getElementById('confirm-move-blood').textContent = `🩸 ${p.blood_group || dirPatient.blood_group || 'Unknown'}`;
    document.getElementById('confirm-move-father').textContent = p.father || dirPatient.father_name || 'N/A';
    document.getElementById('confirm-move-phone').textContent = p.phone || dirPatient.phone || 'N/A';

    const gender = p.gender || dirPatient.gender || '';
    const age = p.age || dirPatient.age ? `${p.age || dirPatient.age} yrs` : '';
    document.getElementById('confirm-move-gender-age').textContent = [gender, age].filter(Boolean).join(', ') || 'N/A';
    document.getElementById('confirm-move-demographics').textContent = p.demographics || dirPatient.demographics || 'N/A';

    document.getElementById('confirm-move-doctor').textContent = p.doctor || 'Unassigned';
    document.getElementById('confirm-move-dept').textContent = p.dept || 'General';
    document.getElementById('confirm-move-symptoms').textContent = p.symptoms || 'General Consultation';

    document.getElementById('confirm-move-current-stage').textContent = `Stage ${p.stage}: ${p.status}`;
    document.getElementById('confirm-move-target').textContent = `Stage ${new_stage}: ${new_status}`;

    // Link dossier button
    const dossierBtn = document.getElementById('confirm-move-dossier-btn');
    if (dossierBtn) {
      dossierBtn.onclick = () => {
        openPatientDossier(p.id);
      };
    }

    pendingMoveArgs = {
        appointment_id: p.appointment_id,
        patient_id: p.id,
        status: new_status,
        stage: new_stage,
        event_desc: event_desc
    };

    document.getElementById('modal-confirm-move').classList.remove('hidden');
  }

  function closeConfirmMoveModal() {
    pendingMoveArgs = null;
    document.getElementById('modal-confirm-move').classList.add('hidden');
  }

  document.getElementById('btn-execute-move').addEventListener('click', async () => {
    if(!pendingMoveArgs) return;
    try {
        await fetch('api/queue.php?action=update_status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(pendingMoveArgs)
        });
        showToast('Queue Updated', `Status changed to ${pendingMoveArgs.status}`);
        closeConfirmMoveModal();
        fetchQueuePipeline();
    } catch (e) {}
  });

  // --- Consultation Modal Logic — Guided Step-by-Step Clinical Workstation ---
  async function fetchBedsForModal() {
      try {
          const res = await fetch('api/beds.php?action=get_all');
          const data = await res.json();
          if (data.status === 'success') allBeds = data.beds;
      } catch (e) {}
  }

  // 300+ Pre-Configured Medicines Mapped by Clinical Categories
  const commonDrugsCategoryMap = {
    fever: [
      { name: "Paracetamol 650mg Tablet", dose: "1 Tab", freq: "TDS", dur: "3 Days", meal: "After Food" },
      { name: "Paracetamol 500mg Tablet", dose: "1 Tab", freq: "TDS", dur: "3 Days", meal: "After Food" },
      { name: "Ibuprofen 400mg Tablet", dose: "1 Tab", freq: "BD", dur: "3 Days", meal: "After Food" },
      { name: "Aceclofenac + Paracetamol (Zerodol-P)", dose: "1 Tab", freq: "BD", dur: "3 Days", meal: "After Food" },
      { name: "Aceclofenac + Paracetamol + Serratiopeptidase (Zerodol-SP)", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Mefenamic Acid + Dicyclomine (Meftal-Spas)", dose: "1 Tab", freq: "SOS", dur: "3 Days", meal: "After Food" },
      { name: "Tramadol + Paracetamol Tablet (Ultracet)", dose: "1 Tab", freq: "SOS", dur: "3 Days", meal: "After Food" },
      { name: "Etoricoxib 90mg Tablet", dose: "1 Tab", freq: "OD", dur: "5 Days", meal: "After Food" },
      { name: "Diclofenac Sodium 50mg Tablet", dose: "1 Tab", freq: "BD", dur: "3 Days", meal: "After Food" },
      { name: "Naproxen 500mg Tablet", dose: "1 Tab", freq: "BD", dur: "3 Days", meal: "After Food" }
    ],
    antibiotics: [
      { name: "Amoxicillin + Clavulanate 625mg (Augmentin)", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Azithromycin 500mg Tablet", dose: "1 Tab", freq: "OD", dur: "3 Days", meal: "Before Food" },
      { name: "Cefixime 200mg Tablet", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Cefuroxime Axetil 500mg Tablet", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Ciprofloxacin 500mg Tablet", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Ofloxacin + Ornidazole Tablet", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "After Food" },
      { name: "Levofloxacin 500mg Tablet", dose: "1 Tab", freq: "OD", dur: "5 Days", meal: "After Food" },
      { name: "Doxycycline 100mg Capsule", dose: "1 Cap", freq: "BD", dur: "7 Days", meal: "After Food" },
      { name: "Metronidazole 400mg Tablet", dose: "1 Tab", freq: "TDS", dur: "5 Days", meal: "After Food" },
      { name: "Nitrofurantoin 100mg SR Tablet", dose: "1 Tab", freq: "BD", dur: "7 Days", meal: "After Food" },
      { name: "Fluconazole 150mg Tablet", dose: "1 Tab", freq: "Single Dose", dur: "1 Day", meal: "After Food" }
    ],
    antacids: [
      { name: "Pantoprazole 40mg Tablet", dose: "1 Tab", freq: "OD", dur: "7 Days", meal: "Before Food" },
      { name: "Pantoprazole + Domperidone (Pan-D)", dose: "1 Cap", freq: "OD", dur: "7 Days", meal: "Before Food" },
      { name: "Omeprazole 20mg Capsule", dose: "1 Cap", freq: "OD", dur: "7 Days", meal: "Before Food" },
      { name: "Rabeprazole + Domperidone (Rablet-D)", dose: "1 Cap", freq: "OD", dur: "7 Days", meal: "Before Food" },
      { name: "Esomeprazole 40mg Tablet", dose: "1 Tab", freq: "OD", dur: "7 Days", meal: "Before Food" },
      { name: "Ondansetron 4mg Tablet", dose: "1 Tab", freq: "SOS", dur: "3 Days", meal: "Before Food" },
      { name: "Domperidone 10mg Tablet", dose: "1 Tab", freq: "BD", dur: "5 Days", meal: "Before Food" },
      { name: "Drotaverine 80mg Tablet (Drotin)", dose: "1 Tab", freq: "SOS", dur: "3 Days", meal: "After Food" },
      { name: "Oral Rehydration Salts (ORS Sachet 21.8g)", dose: "1 Sachet in 1L Water", freq: "SOS", dur: "3 Days", meal: "With Food" },
      { name: "Sucralfate + Oxetacaine Suspension", dose: "10 ml", freq: "TDS", dur: "7 Days", meal: "Before Food" },
      { name: "Lactulose Solution 10g/15ml", dose: "15 ml", freq: "HS", dur: "5 Days", meal: "At Bedtime" }
    ],
    cold: [
      { name: "Cetirizine 10mg Tablet", dose: "1 Tab", freq: "HS", dur: "5 Days", meal: "At Bedtime" },
      { name: "Levocetirizine + Montelukast (Montair-LC)", dose: "1 Tab", freq: "HS", dur: "7 Days", meal: "At Bedtime" },
      { name: "Fexofenadine 120mg Tablet (Allegra)", dose: "1 Tab", freq: "OD", dur: "5 Days", meal: "After Food" },
      { name: "Ambroxol + Guaifenesin Cough Syrup", dose: "10 ml", freq: "TDS", dur: "5 Days", meal: "After Food" },
      { name: "Dextromethorphan Syrup 10mg/5ml", dose: "10 ml", freq: "TDS", dur: "5 Days", meal: "After Food" },
      { name: "Salbutamol Inhaler 100mcg (Ventolin)", dose: "2 Puffs", freq: "SOS", dur: "14 Days", meal: "As Directed" },
      { name: "Formoterol + Budesonide Inhaler (Foracort 200)", dose: "1 Puff", freq: "BD", dur: "30 Days", meal: "As Directed" },
      { name: "Xylometazoline 0.1% Nasal Spray (Otrivin)", dose: "1 Spray each nostril", freq: "BD", dur: "3 Days", meal: "As Directed" },
      { name: "N-Acetylcysteine 600mg Effervescent Tablet", dose: "1 Tab in water", freq: "OD", dur: "5 Days", meal: "After Food" }
    ],
    heart: [
      { name: "Telmisartan 40mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Telmisartan + Amlodipine (40mg/5mg)", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Amlodipine 5mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Metoprolol Succinate 50mg ER Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Atorvastatin 20mg Tablet", dose: "1 Tab", freq: "HS", dur: "30 Days", meal: "At Bedtime" },
      { name: "Rosuvastatin 10mg Tablet", dose: "1 Tab", freq: "HS", dur: "30 Days", meal: "At Bedtime" },
      { name: "Clopidogrel 75mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Aspirin 75mg Gastro-resistant Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Furosemide 40mg Tablet (Lasix)", dose: "1 Tab", freq: "OD", dur: "14 Days", meal: "After Food" },
      { name: "Torsemide 10mg Tablet", dose: "1 Tab", freq: "OD", dur: "14 Days", meal: "After Food" }
    ],
    diabetes: [
      { name: "Metformin 500mg SR Tablet", dose: "1 Tab", freq: "BD", dur: "30 Days", meal: "After Food" },
      { name: "Metformin 1000mg SR Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Glimepiride + Metformin (1mg/500mg Tablet)", dose: "1 Tab", freq: "BD", dur: "30 Days", meal: "Before Food" },
      { name: "Glimepiride + Metformin (2mg/500mg Tablet)", dose: "1 Tab", freq: "BD", dur: "30 Days", meal: "Before Food" },
      { name: "Vildagliptin + Metformin (50mg/500mg Tablet)", dose: "1 Tab", freq: "BD", dur: "30 Days", meal: "After Food" },
      { name: "Sitagliptin 100mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Dapagliflozin 10mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Teneligliptin 20mg Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Insulin Glargine 100IU/ml Pen (Lantus)", dose: "10 Units", freq: "HS", dur: "30 Days", meal: "At Bedtime" }
    ],
    vitamins: [
      { name: "Vitamin D3 60,000 IU Capsule", dose: "1 Cap", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Calcium Carbonate 500mg + Vitamin D3 Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Methylcobalamin 1500mcg (Vitamin B12) Tablet", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Vitamin B-Complex (Becosules) Capsule", dose: "1 Cap", freq: "OD", dur: "15 Days", meal: "After Food" },
      { name: "Ferrous Ascorbate + Folic Acid Tablet (Orofer-XT)", dose: "1 Tab", freq: "OD", dur: "30 Days", meal: "After Food" },
      { name: "Vitamin C (Ascorbic Acid) 500mg Chewable", dose: "1 Tab", freq: "OD", dur: "15 Days", meal: "After Food" },
      { name: "Zinc Sulphate 50mg Tablet", dose: "1 Tab", freq: "OD", dur: "10 Days", meal: "After Food" },
      { name: "Multivitamin + Multimineral Daily Capsule", dose: "1 Cap", freq: "OD", dur: "30 Days", meal: "After Food" }
    ],
    topical: [
      { name: "Clotrimazole Cream 1% Topical", dose: "Apply Locally", freq: "BD", dur: "14 Days", meal: "As Directed" },
      { name: "Mupirocin 2% Ointment (T-Bact)", dose: "Apply Locally", freq: "TDS", dur: "7 Days", meal: "As Directed" },
      { name: "Betamethasone Dipropionate 0.05% Cream", dose: "Apply Thin Layer", freq: "BD", dur: "7 Days", meal: "As Directed" },
      { name: "Silver Sulfadiazine 1% Burn Cream", dose: "Apply Locally", freq: "BD", dur: "7 Days", meal: "As Directed" },
      { name: "Carboxymethylcellulose 0.5% Lubricant Eye Drops", dose: "1 Drop each eye", freq: "QID", dur: "30 Days", meal: "As Directed" },
      { name: "Moxifloxacin 0.5% Eye Drops", dose: "1 Drop each eye", freq: "TDS", dur: "7 Days", meal: "As Directed" },
      { name: "Calamine Soothing Lotion 8%", dose: "Apply Locally", freq: "TDS", dur: "5 Days", meal: "As Directed" },
      { name: "Povidone Iodine 5% Ointment (Betadine)", dose: "Apply Locally", freq: "BD", dur: "7 Days", meal: "As Directed" }
    ]
  };

  function goToConsultStep(stepNumber) {
    currentConsultStep = stepNumber;

    // Show only the target panel
    for (let i = 1; i <= 4; i++) {
      const panel = document.getElementById(`consult-step-panel-${i}`);
      const btn = document.getElementById(`step-btn-${i}`);
      if (panel) panel.classList.toggle('hidden', i !== stepNumber);

      if (btn) {
        const numSpan = btn.querySelector('.step-num');
        if (i === stepNumber) {
          btn.className = "consult-step-nav active-step flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm transition";
          if (numSpan) numSpan.className = "step-num w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black bg-indigo-600 text-white";
        } else {
          btn.className = "consult-step-nav flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
          if (numSpan) numSpan.className = "step-num w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black bg-slate-200 text-slate-700";
        }
      }
    }

    const indicator = document.getElementById('current-step-indicator');
    if (indicator) indicator.textContent = stepNumber;

    // If entering Step 4, update summary overview
    if (stepNumber === 4) {
      updateConsultationSummary();
    }
  }

  function validateStepAndProceed(fromStep, toStep) {
    if (fromStep === 1) {
      // Auto-add any typed text in custom input
      const customInput = document.getElementById('custom-diagnosis-input');
      if (customInput && customInput.value.trim()) {
        addCustomDiagnosis();
      }
      if (selectedDiagnoses.size === 0) {
        showToast('Diagnosis Required', 'Please select or add at least one clinical diagnosis before proceeding.', 'error');
        document.getElementById('custom-diagnosis-input').focus();
        return;
      }
    } else if (fromStep === 2) {
      updatePrescriptionsBadge();
    } else if (fromStep === 3) {
      const disposition = document.querySelector('input[name="dispositionOutcome"]:checked')?.value || 'Normal Medicine';
      if ((disposition === 'OPD' || disposition === 'ICU')) {
        const bedVal = document.getElementById('allotment-bed-select')?.value;
        if (!bedVal) {
          showToast('Bed Required', 'Please select an available bed for admission.', 'error');
          return;
        }
      }
    }

    goToConsultStep(toStep);
  }

  function filterDrugCategory(cat, clickedBtn) {
    activeConsultCategory = cat;
    document.querySelectorAll('.drug-cat-btn').forEach(btn => {
      btn.className = "drug-cat-btn text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shrink-0";
    });
    const targetBtn = clickedBtn || document.querySelector(`.drug-cat-btn[data-category="${cat}"]`);
    if (targetBtn) {
      targetBtn.className = "drug-cat-btn text-xs font-bold px-2.5 py-1 rounded-md border border-indigo-600 bg-indigo-600 text-white transition shrink-0";
    }

    const container = document.getElementById('category-quick-chips');
    if (!container) return;
    container.innerHTML = '';
    const drugs = commonDrugsCategoryMap[cat] || [];
    drugs.forEach((d, idx) => {
      container.innerHTML += `
        <button type="button" onclick="quickAddPredefinedDrug('${cat}', ${idx})" class="text-xs font-medium px-2.5 py-1 rounded-md border border-slate-200 bg-white text-slate-700 hover:border-indigo-400 hover:text-indigo-700 transition shadow-sm flex items-center gap-1">
          <i class="fa-solid fa-plus text-[9px] text-indigo-500"></i>
          <span>${d.name}</span>
        </button>
      `;
    });
  }

  function quickAddPredefinedDrug(cat, index) {
    const drug = (commonDrugsCategoryMap[cat] || [])[index];
    if (!drug) return;

    // Check if there is an empty medication row to reuse
    let targetRow = null;
    document.querySelectorAll('.med-item-row').forEach(row => {
      if (!targetRow && !row.querySelector('.med-name').value.trim()) {
        targetRow = row;
      }
    });

    if (!targetRow) {
      addMedicineInputRow();
      const allRows = document.querySelectorAll('.med-item-row');
      targetRow = allRows[allRows.length - 1];
    }

    if (targetRow) {
      targetRow.querySelector('.med-name').value = drug.name;
      targetRow.querySelector('.med-dose').value = drug.dose || '1 Tab';
      targetRow.querySelector('.med-freq').value = drug.freq || 'BD';
      targetRow.querySelector('.med-duration').value = drug.dur || '5 Days';
      targetRow.querySelector('.med-meal').value = drug.meal || 'After Food';
    }

    updatePrescriptionsBadge();
    showToast('Prescription Added', drug.name);
  }

  function updatePrescriptionsBadge() {
    let count = 0;
    document.querySelectorAll('.med-item-row').forEach(r => {
      if ((r.querySelector('.med-name')?.value || '').trim()) count++;
    });
    const badge = document.getElementById('prescriptions-count-badge');
    if (badge) {
      badge.textContent = count;
      badge.classList.toggle('hidden', count === 0);
    }
  }

  function updateConsultationSummary() {
    const diagEl = document.getElementById('summary-diagnoses-text');
    const medEl = document.getElementById('summary-medications-text');
    const dispEl = document.getElementById('summary-disposition-text');

    const diagArray = Array.from(selectedDiagnoses);
    if (diagEl) diagEl.textContent = diagArray.length ? `${diagArray.join(', ')} (${diagArray.length})` : 'None';

    let medNames = [];
    document.querySelectorAll('.med-item-row').forEach(r => {
      const name = (r.querySelector('.med-name')?.value || '').trim();
      if (name) medNames.push(name.split(' ')[0]);
    });
    if (medEl) medEl.textContent = medNames.length ? `${medNames.join(', ')} (${medNames.length} items)` : 'No medications prescribed';

    const disp = document.querySelector('input[name="dispositionOutcome"]:checked')?.value || 'Normal Medicine';
    if (dispEl) {
      if (disp === 'Normal Medicine') dispEl.textContent = 'Discharge & Home Care';
      else if (disp === 'Waiting for Reports') {
        const tests = getSelectedTestsString();
        dispEl.textContent = `Waiting for Reports (${tests || 'General Tests'})`;
      }
      else if (disp === 'OPD') {
        const bed = document.getElementById('allotment-bed-select')?.value || '';
        dispEl.textContent = `Admit to General Ward (Bed: ${bed || 'Pending'})`;
      } else {
        const bed = document.getElementById('allotment-bed-select')?.value || '';
        dispEl.textContent = `Admit to ICU (Bed: ${bed || 'Pending'})`;
      }
    }
  }

  function getSelectedTestsString() {
    const selected = [];
    document.querySelectorAll('.test-checkbox:checked').forEach(cb => selected.push(cb.value));
    const custom = (document.getElementById('custom-ordered-tests')?.value || '').trim();
    if (custom) selected.push(custom);
    return selected.join(', ');
  }

  async function openConsultationModal(app_id, patient_id, name, surname, father, type, symptoms) {
    // Attempt lookup in currentQueue by appointment_id or patient_id
    const qPatient = (currentQueue || []).find(x => x.appointment_id == app_id || x.id == app_id) || {};
    
    // Resolve patient details with fallback to qPatient
    patient_id = patient_id || qPatient.id || '';
    name = name || qPatient.name || '';
    surname = surname || qPatient.surname || '';
    father = father || qPatient.father || '';
    type = type || qPatient.type || 'General Consultation';
    symptoms = symptoms || qPatient.symptoms || '';

    activeConsultPatientId = patient_id;
    activeConsultAppId = app_id || qPatient.appointment_id;

    const dirPatient = (allDirectoryPatients || []).find(x => x.id === patient_id) || {};

    const fullName = `${name || ''} ${surname || ''}`.trim() || 'Patient';
    const nameEl = document.getElementById('consult-patient-name');
    if (nameEl) nameEl.textContent = fullName;
    
    const fatherEl = document.getElementById('consult-father-name');
    const fatherVal = father || dirPatient.father_name;
    if (fatherEl) {
      fatherEl.textContent = fatherVal ? `S/O ${fatherVal}` : '';
      fatherEl.classList.toggle('hidden', !fatherVal);
    }

    const pidEl = document.getElementById('consult-patient-id');
    if (pidEl) pidEl.textContent = `MRN: ${patient_id || 'N/A'}`;

    const symEl = document.getElementById('consult-reported-symptoms');
    if (symEl) symEl.textContent = symptoms || 'Routine clinical checkup.';

    const ageVal = qPatient.age || dirPatient.age ? `${qPatient.age || dirPatient.age} Yrs` : '';
    const genderVal = qPatient.gender || dirPatient.gender || '';
    const vitalsText = [ageVal, genderVal].filter(Boolean).join(' • ') || 'Age/Gender not recorded';
    const vitalsEl = document.getElementById('consult-patient-vitals');
    if (vitalsEl) vitalsEl.textContent = vitalsText;

    const bloodVal = qPatient.blood_group || dirPatient.blood_group || '';
    const bloodEl = document.getElementById('consult-patient-blood');
    if (bloodEl) bloodEl.textContent = bloodVal ? `Blood: ${bloodVal}` : 'Blood: Unknown';

    // Reset default form state
    selectedDiagnoses.clear();
    renderSelectedDiagnoses();
    const customInput = document.getElementById('custom-diagnosis-input');
    if (customInput) customInput.value = '';

    // Reset Prescriptions (Initialize with 1 clean prescription row)
    const medContainer = document.getElementById('medicines-input-container');
    if (medContainer) medContainer.innerHTML = '';
    addMedicineInputRow();
    filterDrugCategory('fever');
    updatePrescriptionsBadge();

    // Reset Files
    const filesContainer = document.getElementById('files-input-container');
    if (filesContainer) filesContainer.innerHTML = '';
    const filesEmpty = document.getElementById('files-empty-state');
    if (filesEmpty) filesEmpty.classList.remove('hidden');

    const prevFilesSection = document.getElementById('previous-files-section');
    if (prevFilesSection) prevFilesSection.classList.add('hidden');
    const prevFilesList = document.getElementById('previous-files-list');
    if (prevFilesList) prevFilesList.innerHTML = '';

    const restoredBanner = document.getElementById('consult-restored-banner');
    if (restoredBanner) restoredBanner.classList.add('hidden');

    // Reset Notes
    const notesEl = document.getElementById('consult-doctor-notes');
    if (notesEl) notesEl.value = '';

    // Reset Ordered Tests Checklist
    document.querySelectorAll('.test-checkbox').forEach(cb => cb.checked = false);
    const customTests = document.getElementById('custom-ordered-tests');
    if (customTests) customTests.value = '';

    // Reset Disposition to Normal Medicine (clean borders, no dark border bugs)
    handleDispositionChange('Normal Medicine');

    // Always start on Step 1
    goToConsultStep(1);

    // Show modal immediately
    const modal = document.getElementById('modal-consultation');
    if (modal) modal.classList.remove('hidden');

    // Fetch existing consultation data (if patient returns after lab reports or updating)
    if (activeConsultAppId) {
      try {
        const res = await fetch(`api/consultation.php?action=get_consultation&appointment_id=${activeConsultAppId}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
          populateExistingConsultation(json.data, qPatient);
        }
      } catch (err) {
        console.warn('Could not load previous consultation data:', err);
      }
    }
  }

  function populateMedicineRow(m) {
    const c = document.getElementById('medicines-input-container');
    if (!c) return;
    const r = document.createElement('div');
    r.className = "med-item-row bg-slate-50/70 border border-slate-200 rounded-xl p-2.5 sm:p-3 space-y-2 transition hover:border-slate-300";
    
    // Parse meal timing and note
    let mealVal = "After Food";
    let instructionVal = "";
    if (m.note) {
      if (m.note.includes('Before Food')) mealVal = "Before Food";
      else if (m.note.includes('With Food')) mealVal = "With Food";
      else if (m.note.includes('At Bedtime')) mealVal = "At Bedtime";
      
      const parts = m.note.split('•').map(p => p.trim());
      if (parts.length > 1) {
        instructionVal = parts.slice(1).join(' • ');
      } else if (!m.note.includes('Food') && !m.note.includes('Bedtime')) {
        instructionVal = m.note;
      }
    }

    const durationVal = m.duration || "5 Days";
    const freqVal = m.freq || "BD";
    const doseVal = m.dose || "1 Tab";

    r.innerHTML = `
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
        <!-- Medicine Name -->
        <div class="sm:col-span-5">
          <input type="text" list="common-drugs-list" placeholder="Drug name & strength (e.g. Paracetamol 650mg)" class="med-name w-full h-9 text-xs sm:text-sm font-semibold border border-slate-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition" value="${(m.name || '').replace(/"/g, '&quot;')}" required onchange="updatePrescriptionsBadge()">
        </div>
        <!-- Dose -->
        <div class="sm:col-span-2">
          <input type="text" placeholder="1 Tab / 5ml" class="med-dose w-full h-9 text-xs sm:text-sm border border-slate-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 transition" value="${(doseVal).replace(/"/g, '&quot;')}">
        </div>
        <!-- Frequency -->
        <div class="sm:col-span-2">
          <select class="med-freq w-full h-9 text-xs font-medium border border-slate-300 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="OD" ${freqVal === 'OD' ? 'selected' : ''}>OD (1-0-0) Daily</option>
            <option value="BD" ${freqVal === 'BD' ? 'selected' : ''}>BD (1-0-1) Twice</option>
            <option value="TDS" ${freqVal === 'TDS' ? 'selected' : ''}>TDS (1-1-1) 3x</option>
            <option value="QID" ${freqVal === 'QID' ? 'selected' : ''}>QID (1-1-1-1) 4x</option>
            <option value="HS" ${freqVal === 'HS' ? 'selected' : ''}>HS (0-0-1) Bedtime</option>
            <option value="SOS" ${freqVal === 'SOS' ? 'selected' : ''}>SOS (As needed)</option>
            <option value="STAT" ${freqVal === 'STAT' ? 'selected' : ''}>STAT (Once now)</option>
          </select>
        </div>
        <!-- Duration -->
        <div class="sm:col-span-2">
          <select class="med-duration w-full h-9 text-xs font-medium border border-slate-300 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="3 Days" ${durationVal === '3 Days' ? 'selected' : ''}>3 Days</option>
            <option value="5 Days" ${durationVal === '5 Days' ? 'selected' : ''}>5 Days</option>
            <option value="7 Days" ${durationVal === '7 Days' ? 'selected' : ''}>7 Days</option>
            <option value="10 Days" ${durationVal === '10 Days' ? 'selected' : ''}>10 Days</option>
            <option value="14 Days" ${durationVal === '14 Days' ? 'selected' : ''}>14 Days</option>
            <option value="30 Days" ${durationVal.includes('30') || durationVal.includes('Month') ? 'selected' : ''}>30 Days (1 Mo)</option>
            <option value="Continuous" ${durationVal === 'Continuous' ? 'selected' : ''}>Continuous</option>
          </select>
        </div>
        <!-- Delete Action -->
        <div class="sm:col-span-1 flex items-center justify-end">
          <button type="button" onclick="removeMedRow(this)" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition border border-transparent hover:border-rose-200" title="Remove medication">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Second Line: Meal Timing & Specific Instructions -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center pt-1.5 border-t border-slate-200/60">
        <div class="sm:col-span-3">
          <select class="med-meal w-full h-8 text-xs font-medium border border-slate-200 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600">
            <option value="After Food" ${mealVal === 'After Food' ? 'selected' : ''}>After Food</option>
            <option value="Before Food" ${mealVal === 'Before Food' ? 'selected' : ''}>Before Food</option>
            <option value="With Food" ${mealVal === 'With Food' ? 'selected' : ''}>With Food</option>
            <option value="At Bedtime" ${mealVal === 'At Bedtime' ? 'selected' : ''}>At Bedtime</option>
          </select>
        </div>
        <div class="sm:col-span-9">
          <input type="text" placeholder="Instructions (e.g. with warm water, avoid dairy, after meals)..." class="med-instruction w-full h-8 text-xs border border-slate-200 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 transition" value="${(instructionVal || '').replace(/"/g, '&quot;')}">
        </div>
      </div>
    `;
    c.appendChild(r);
  }

  function populateExistingConsultation(data, qPatient) {
    const hasDiagnoses = data.diagnoses && data.diagnoses.length > 0;
    const hasMedicines = data.medicines && data.medicines.length > 0;
    const hasNotes = (data.doctor_notes || '').trim().length > 0;
    const hasFiles = data.files && data.files.length > 0;
    const hasTests = (data.tests_ordered || '').trim().length > 0;
    const wasWaiting = (data.status === 'Waiting for Reports' || (qPatient && qPatient.status === 'Waiting for Reports'));

    // If there is existing consultation data for this appointment:
    if (hasDiagnoses || hasMedicines || hasNotes || hasFiles || hasTests || wasWaiting) {
      // 1. Show Restored Banner
      const banner = document.getElementById('consult-restored-banner');
      const titleEl = document.getElementById('restored-banner-title');
      const subEl = document.getElementById('restored-banner-subtitle');
      if (banner && titleEl && subEl) {
        banner.classList.remove('hidden');
        if (wasWaiting) {
          titleEl.textContent = 'Diagnostic Reports Received — Reviewing Previous Consultation';
          subEl.textContent = hasTests 
            ? `Restored initial assessment. Ordered tests: ${data.tests_ordered}. Review reports and finalize prescription & care plan.`
            : 'Restored initial clinical assessment and prescriptions. Review findings and finalize disposition.';
        } else {
          titleEl.textContent = 'Existing Consultation Record Loaded';
          subEl.textContent = 'Pre-filled with previously saved clinical notes, prescriptions, and orders for this visit.';
        }
      }

      // 2. Restore Diagnoses
      if (hasDiagnoses) {
        selectedDiagnoses.clear();
        data.diagnoses.forEach(diag => selectedDiagnoses.add(diag));
        renderSelectedDiagnoses();
      }

      // 3. Restore Prescriptions (Rx)
      if (hasMedicines) {
        const c = document.getElementById('medicines-input-container');
        if (c) {
          c.innerHTML = '';
          data.medicines.forEach(m => populateMedicineRow(m));
          updatePrescriptionsBadge();
        }
      }

      // 4. Restore Tests Ordered & Disposition
      if (hasTests) {
        const testsList = data.tests_ordered.split(',').map(s => s.trim().toLowerCase());
        const customRemaining = [];
        document.querySelectorAll('.test-checkbox').forEach(cb => {
          const match = testsList.some(t => cb.value.toLowerCase().includes(t) || t.includes(cb.value.toLowerCase()));
          if (match) {
            cb.checked = true;
          }
        });
        data.tests_ordered.split(',').forEach(s => {
          const trimmed = s.trim();
          let matched = false;
          document.querySelectorAll('.test-checkbox').forEach(cb => {
            if (cb.value.toLowerCase().includes(trimmed.toLowerCase())) matched = true;
          });
          if (!matched && trimmed) customRemaining.push(trimmed);
        });
        const customInput = document.getElementById('custom-ordered-tests');
        if (customInput && customRemaining.length > 0) {
          customInput.value = customRemaining.join(', ');
        }
      }

      // If returning after reports, default disposition to "Normal Medicine" (ready to discharge/home care) or keep bed if admitted
      if (data.status && (data.status.includes('OPD') || data.status.includes('Admitted'))) {
        handleDispositionChange('OPD');
        if (data.bed_number) {
          setTimeout(() => {
            const select = document.getElementById('allotment-bed-select');
            if (select) select.value = data.bed_number;
          }, 100);
        }
      } else if (data.status && data.status.includes('ICU')) {
        handleDispositionChange('ICU');
        if (data.bed_number) {
          setTimeout(() => {
            const select = document.getElementById('allotment-bed-select');
            if (select) select.value = data.bed_number;
          }, 100);
        }
      } else {
        handleDispositionChange('Normal Medicine');
      }

      // 5. Restore Doctor Notes
      if (hasNotes) {
        const notesEl = document.getElementById('consult-doctor-notes');
        if (notesEl) notesEl.value = data.doctor_notes;
      }

      // 6. Restore Previously Uploaded Files
      if (hasFiles) {
        const prevFilesSection = document.getElementById('previous-files-section');
        const prevFilesList = document.getElementById('previous-files-list');
        if (prevFilesSection && prevFilesList) {
          prevFilesSection.classList.remove('hidden');
          prevFilesList.innerHTML = data.files.map(f => {
            const isPdf = (f.mime_type === 'application/pdf') || (f.file_name && f.file_name.toLowerCase().endsWith('.pdf')) || (f.file_path && f.file_path.toLowerCase().includes('.pdf'));
            return `
            <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 text-xs shadow-sm">
              <div class="flex items-center gap-2 truncate pr-2">
                <i class="fa-solid ${isPdf ? 'fa-file-pdf text-rose-500' : 'fa-file-image text-blue-500'}"></i>
                <span class="font-bold text-slate-800 truncate">${f.title || 'Attachment'}</span>
                <span class="text-[10px] text-slate-400 font-mono shrink-0">(${f.file_date})</span>
              </div>
              <a href="${f.file_path}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 text-indigo-600 font-bold text-[10px] transition shrink-0 flex items-center gap-1 shadow-sm border border-slate-200">
                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i> View Report
              </a>
            </div>
            `;
          }).join('');
        }
      }
    }
  }

  function closeConsultModal() {
    const modal = document.getElementById('modal-consultation');
    if (modal) modal.classList.add('hidden');
  }

  // === DIAGNOSIS SYSTEM ===
  function addQuickDiag(diag) {
    if (diag && !selectedDiagnoses.has(diag)) {
      selectedDiagnoses.add(diag);
      renderSelectedDiagnoses();
    }
  }

  function addCustomDiagnosis() {
    const input = document.getElementById('custom-diagnosis-input');
    if (!input) return;
    const val = input.value.trim();
    if (val && !selectedDiagnoses.has(val)) {
      selectedDiagnoses.add(val);
      renderSelectedDiagnoses();
    }
    input.value = '';
    input.focus();
  }

  function removeDiagnosis(diag) {
    selectedDiagnoses.delete(diag);
    renderSelectedDiagnoses();
  }

  function renderSelectedDiagnoses() {
    const display = document.getElementById('selected-diagnoses-display');
    if (!display) return;
    if (selectedDiagnoses.size === 0) {
      display.innerHTML = '<span class="text-xs text-slate-400 italic">No diagnoses added yet. Type or click quick suggestions above.</span>';
      return;
    }
    display.innerHTML = '';
    selectedDiagnoses.forEach(d => {
      const safe = d.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
      display.innerHTML += `
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-md">
          <span>${d}</span>
          <button type="button" onclick="removeDiagnosis('${safe}')" class="text-indigo-400 hover:text-rose-600 transition ml-0.5">
            <i class="fa-solid fa-xmark text-[11px]"></i>
          </button>
        </span>
      `;
    });
  }

  // === PRESCRIPTION PAD SYSTEM ===
  function addMedicineInputRow() {
    const c = document.getElementById('medicines-input-container');
    const r = document.createElement('div');
    r.className = "med-item-row bg-slate-50/70 border border-slate-200 rounded-xl p-2.5 sm:p-3 space-y-2 transition hover:border-slate-300";
    r.innerHTML = `
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
        <!-- Medicine Name -->
        <div class="sm:col-span-5">
          <input type="text" list="common-drugs-list" placeholder="Drug name & strength (e.g. Paracetamol 650mg)" class="med-name w-full h-9 text-xs sm:text-sm font-semibold border border-slate-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition" required onchange="updatePrescriptionsBadge()">
        </div>
        <!-- Dose -->
        <div class="sm:col-span-2">
          <input type="text" placeholder="1 Tab / 5ml" class="med-dose w-full h-9 text-xs sm:text-sm border border-slate-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 transition" value="1 Tab">
        </div>
        <!-- Frequency -->
        <div class="sm:col-span-2">
          <select class="med-freq w-full h-9 text-xs font-medium border border-slate-300 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="OD">OD (1-0-0) Daily</option>
            <option value="BD" selected>BD (1-0-1) Twice</option>
            <option value="TDS">TDS (1-1-1) 3x</option>
            <option value="QID">QID (1-1-1-1) 4x</option>
            <option value="HS">HS (0-0-1) Bedtime</option>
            <option value="SOS">SOS (As needed)</option>
            <option value="STAT">STAT (Once now)</option>
          </select>
        </div>
        <!-- Duration -->
        <div class="sm:col-span-2">
          <select class="med-duration w-full h-9 text-xs font-medium border border-slate-300 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="3 Days">3 Days</option>
            <option value="5 Days" selected>5 Days</option>
            <option value="7 Days">7 Days</option>
            <option value="10 Days">10 Days</option>
            <option value="14 Days">14 Days</option>
            <option value="30 Days">30 Days (1 Mo)</option>
            <option value="Continuous">Continuous</option>
          </select>
        </div>
        <!-- Delete Action -->
        <div class="sm:col-span-1 flex items-center justify-end">
          <button type="button" onclick="removeMedRow(this)" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition border border-transparent hover:border-rose-200" title="Remove medication">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Second Line: Meal Timing & Specific Instructions -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center pt-1.5 border-t border-slate-200/60">
        <div class="sm:col-span-3">
          <select class="med-meal w-full h-8 text-xs font-medium border border-slate-200 rounded-lg px-2 bg-white focus:outline-none focus:border-indigo-600">
            <option value="After Food" selected>After Food</option>
            <option value="Before Food">Before Food</option>
            <option value="With Food">With Food</option>
            <option value="At Bedtime">At Bedtime</option>
          </select>
        </div>
        <div class="sm:col-span-9">
          <input type="text" placeholder="Instructions (e.g. with warm water, avoid dairy, after meals)..." class="med-instruction w-full h-8 text-xs border border-slate-200 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>
    `;
    c.appendChild(r);
    if (!document.getElementById('consult-step-panel-2')?.classList.contains('hidden')) {
      r.querySelector('.med-name')?.focus();
    }
    updatePrescriptionsBadge();
  }

  function removeMedRow(btn) {
    const c = document.getElementById('medicines-input-container');
    if (c.children.length > 1) {
      btn.closest('.med-item-row').remove();
    } else {
      const row = btn.closest('.med-item-row');
      row.querySelector('.med-name').value = '';
      row.querySelector('.med-instruction').value = '';
    }
    updatePrescriptionsBadge();
  }

  // === DISPOSITION SYSTEM (Zero Hardcoded Black Border Bug) ===
  function handleDispositionChange(disposition) {
    const sectionBed = document.getElementById('bed-allotment-section');
    const sectionTests = document.getElementById('tests-ordering-section');
    const select = document.getElementById('allotment-bed-select');

    // 1. Remove all active border and highlight classes from all 4 cards
    const cards = [
      { id: 'label-disposition-medicine', radioVal: 'Normal Medicine', activeClass: 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/20' },
      { id: 'label-disposition-reports', radioVal: 'Waiting for Reports', activeClass: 'border-amber-500 bg-amber-50/50 ring-1 ring-amber-500/20' },
      { id: 'label-disposition-opd', radioVal: 'OPD', activeClass: 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500/20' },
      { id: 'label-disposition-icu', radioVal: 'ICU', activeClass: 'border-rose-500 bg-rose-50/50 ring-1 ring-rose-500/20' }
    ];

    cards.forEach(c => {
      const el = document.getElementById(c.id);
      if (el) {
        el.className = "disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 bg-white border-slate-200 hover:border-slate-300 shadow-sm";
        const radio = el.querySelector('input[type="radio"]');
        if (radio) radio.checked = (c.radioVal === disposition);
      }
    });

    // 2. Add specific color highlight to the selected card
    const activeObj = cards.find(c => c.radioVal === disposition);
    if (activeObj) {
      const activeEl = document.getElementById(activeObj.id);
      if (activeEl) {
        activeEl.className = `disposition-card border-2 rounded-xl p-3 cursor-pointer transition flex items-center gap-3 ${activeObj.activeClass} shadow-sm`;
      }
    }

    // 3. Toggle conditional sub-sections
    if (disposition === 'Waiting for Reports') {
      if (sectionTests) sectionTests.classList.remove('hidden');
      if (sectionBed) sectionBed.classList.add('hidden');
    } else if (disposition === 'OPD') {
      if (sectionTests) sectionTests.classList.add('hidden');
      if (sectionBed) sectionBed.classList.remove('hidden');
      const badge = document.getElementById('bed-selection-type-badge');
      if (badge) {
        badge.textContent = 'OPD Ward Bed';
        badge.className = 'text-xs font-semibold px-2 py-0.5 rounded bg-blue-200 text-blue-800';
      }
    } else if (disposition === 'ICU') {
      if (sectionTests) sectionTests.classList.add('hidden');
      if (sectionBed) sectionBed.classList.remove('hidden');
      const badge = document.getElementById('bed-selection-type-badge');
      if (badge) {
        badge.textContent = 'Critical Care / ICU Bed';
        badge.className = 'text-xs font-semibold px-2 py-0.5 rounded bg-rose-200 text-rose-800';
      }
    } else {
      // Normal Medicine
      if (sectionTests) sectionTests.classList.add('hidden');
      if (sectionBed) sectionBed.classList.add('hidden');
    }

    // Populate bed select if OPD or ICU
    if (disposition === 'OPD' || disposition === 'ICU') {
      const avail = allBeds.filter(b => b.type === disposition && b.status === 'Available');
      select.innerHTML = avail.length === 0 ? `<option value="">No ${disposition} beds currently available!</option>` : '';
      avail.forEach(b => select.innerHTML += `<option value="${b.bed_number}">${b.bed_number} — ${b.wing}</option>`);
    }
  }

  // === FILE ATTACHMENTS ===
  function addFileInputRow() {
    const c = document.getElementById('files-input-container');
    const filesEmpty = document.getElementById('files-empty-state');
    if (filesEmpty) filesEmpty.classList.add('hidden');

    const r = document.createElement('div');
    const today = new Date().toISOString().substring(0, 16);
    r.className = "file-item-row bg-slate-50 p-2.5 rounded-xl border border-slate-200 space-y-2 relative";
    r.innerHTML = `
      <button type="button" onclick="removeFileRow(this)" class="absolute top-2 right-2 text-slate-400 hover:text-rose-500 text-xs w-6 h-6 rounded-full flex items-center justify-center bg-white border border-slate-200 shadow-sm"><i class="fa-solid fa-xmark"></i></button>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pr-6">
        <input type="text" placeholder="File Title (e.g. Chest X-Ray)" required class="file-title h-9 text-xs font-medium border border-slate-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-indigo-600">
        <input type="file" required accept="image/*,application/pdf" class="file-upload h-9 text-xs border border-slate-300 rounded-lg px-2 bg-white" onchange="previewImage(this)">
      </div>
      <input type="hidden" class="file-date" value="${today}">
      <img class="img-preview max-h-24 rounded border border-slate-200 hidden object-contain mt-1">
    `;
    c.appendChild(r);
  }

  function removeFileRow(btn) {
    btn.closest('.file-item-row').remove();
    const c = document.getElementById('files-input-container');
    const filesEmpty = document.getElementById('files-empty-state');
    if (c.children.length === 0 && filesEmpty) filesEmpty.classList.remove('hidden');
  }

  function previewImage(input) {
    const preview = input.closest('.file-item-row').querySelector('.img-preview');
    if (input.files && input.files[0]) {
      const file = input.files[0];
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
      } else {
        preview.src = "";
        preview.classList.add('hidden');
      }
    } else {
      preview.src = "";
      preview.classList.add('hidden');
    }
  }

  // === FORM SUBMIT HANDLER ===
  async function handleConsultationSave(e) {
    e.preventDefault();

    // 1. Diagnoses
    const diagnoses = Array.from(selectedDiagnoses);
    if (diagnoses.length === 0) {
      goToConsultStep(1);
      showToast('Diagnosis Required', 'Please add at least one clinical diagnosis or impression.', 'error');
      document.getElementById('custom-diagnosis-input').focus();
      return;
    }

    // 2. Medicines
    const medicines = [];
    document.querySelectorAll('.med-item-row').forEach(r => {
      const name = (r.querySelector('.med-name').value || '').trim();
      if (name) {
        const dose = (r.querySelector('.med-dose').value || '').trim();
        const freq = (r.querySelector('.med-freq').value || '').trim();
        const duration = (r.querySelector('.med-duration').value || '').trim();
        const meal = (r.querySelector('.med-meal').value || '').trim();
        const instruction = (r.querySelector('.med-instruction').value || '').trim();

        let note = meal;
        if (instruction) {
          note = note ? note + ' • ' + instruction : instruction;
        }

        medicines.push({
          name: name,
          dose: dose,
          freq: freq,
          duration: duration,
          note: note
        });
      }
    });

    // 3. Disposition
    const disposition = document.querySelector('input[name="dispositionOutcome"]:checked')?.value || 'Normal Medicine';
    const doctor_notes = document.getElementById('consult-doctor-notes').value.trim();
    let bed_number = (disposition === 'OPD' || disposition === 'ICU') ? document.getElementById('allotment-bed-select').value : null;

    if ((disposition === 'OPD' || disposition === 'ICU') && !bed_number) {
      goToConsultStep(3);
      showToast('Bed Required', 'Please select an available bed for patient admission.', 'error');
      return;
    }

    const submitBtn = document.getElementById('btn-finalize-consult');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizing...';

    const formData = new FormData();
    formData.append('appointment_id', activeConsultAppId);
    formData.append('patient_id', activeConsultPatientId);
    formData.append('diagnoses', JSON.stringify(diagnoses));
    formData.append('medicines', JSON.stringify(medicines));
    formData.append('disposition', disposition);
    formData.append('doctor_notes', doctor_notes);
    if (bed_number) formData.append('bed_number', bed_number);

    // If Waiting for Reports, pass ordered diagnostic tests
    if (disposition === 'Waiting for Reports') {
      const testsOrdered = getSelectedTestsString();
      formData.append('tests_ordered', testsOrdered);
    }

    // Files
    document.querySelectorAll('.file-item-row').forEach(r => {
      const fileInput = r.querySelector('.file-upload');
      if (fileInput.files.length > 0) {
        formData.append(`files[]`, fileInput.files[0]);
        formData.append(`file_titles[]`, r.querySelector('.file-title').value.trim() || 'Clinical Document');
        formData.append(`file_dates[]`, r.querySelector('.file-date').value);
      }
    });

    try {
        const res = await fetch('api/consultation.php?action=save_with_files', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeConsultModal();
            fetchQueuePipeline();
            const msg = disposition === 'Waiting for Reports' 
              ? 'Patient placed on hold awaiting lab reports. Moved to Waiting Lounge.'
              : data.message;
            showToast('Consultation Finalized', msg);
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch (err) {
        showToast('Error', 'Network or server error while saving consultation.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-check-circle text-base"></i> Finalize Consultation & Save';
    }
  }

  // --- Edit Appointment Modal Logic ---
  function openEditAppointmentModal(pStr) {
    const p = typeof pStr === 'string' ? JSON.parse(decodeURIComponent(pStr)) : pStr;
    const apptCode = p.appointment_code || ('APP-' + String(p.appointment_id).padStart(4, '0'));

    document.getElementById('edit-app-id').value = p.appointment_id;
    document.getElementById('edit-app-code').textContent = apptCode;
    document.getElementById('edit-app-patient-name').textContent = `${p.name} ${p.surname}`;
    document.getElementById('edit-app-patient-mrn').textContent = `MRN: ${p.id || p.patient_id}`;
    document.getElementById('edit-app-current-stage').textContent = `Stage ${p.stage || 1}: ${p.status || 'Active'}`;
    
    // Populate doctors dropdown
    const docSelect = document.getElementById('edit-app-doctor-id');
    docSelect.innerHTML = '<option value="">Select doctor...</option>';
    (allHospitalDoctors || []).forEach(d => {
      const isSel = (d.id == p.doctor_id || d.name == p.doctor) ? 'selected' : '';
      docSelect.innerHTML += `<option value="${d.id}" ${isSel}>${d.name} (${(d.categories || []).join(', ') || 'General'})</option>`;
    });

    document.getElementById('edit-app-type').value = p.type || 'General Consultation';
    document.getElementById('edit-app-date').value = p.date || new Date().toISOString().substring(0, 10);
    document.getElementById('edit-app-slot').value = p.slot || 'Walk-in';
    document.getElementById('edit-app-symptoms').value = p.symptoms || '';

    document.getElementById('modal-edit-appointment').classList.remove('hidden');
  }

  function closeEditAppointmentModal() {
    document.getElementById('modal-edit-appointment').classList.add('hidden');
  }

  async function handleEditAppointmentSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-edit-app');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const payload = {
      appointment_id: parseInt(document.getElementById('edit-app-id').value),
      doctor_id: document.getElementById('edit-app-doctor-id').value,
      type: document.getElementById('edit-app-type').value,
      date: document.getElementById('edit-app-date').value,
      slot: document.getElementById('edit-app-slot').value.trim(),
      symptoms: document.getElementById('edit-app-symptoms').value.trim()
    };

    try {
      const res = await fetch('api/queue.php?action=edit_appointment', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Updated', data.message || 'Appointment updated successfully');
        closeEditAppointmentModal();
        fetchQueuePipeline();
        fetchAdvanceAppointments();
      } else {
        showToast('Error', data.message || 'Failed to update appointment', 'error');
      }
    } catch (err) {
      showToast('Error', 'Network error occurred', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    }
  }

  // --- Delete Appointment Modal Logic ---
  let pendingDeleteApptId = null;

  function confirmDeleteAppointment(appointmentId, patientName, apptCode) {
    pendingDeleteApptId = appointmentId;
    document.getElementById('delete-app-id').value = appointmentId;
    document.getElementById('delete-app-code').textContent = apptCode;
    document.getElementById('delete-app-patient').textContent = patientName;
    document.getElementById('modal-delete-appointment').classList.remove('hidden');
  }

  function closeDeleteAppointmentModal() {
    pendingDeleteApptId = null;
    document.getElementById('modal-delete-appointment').classList.add('hidden');
  }

  async function executeDeleteAppointment() {
    if (!pendingDeleteApptId) return;
    const btn = document.getElementById('btn-confirm-delete-app');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';

    try {
      const res = await fetch('api/queue.php?action=delete_appointment', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ appointment_id: pendingDeleteApptId })
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast('Deleted', data.message || 'Appointment removed from pipeline');
        closeDeleteAppointmentModal();
        fetchQueuePipeline();
        fetchAdvanceAppointments();
      } else {
        showToast('Error', data.message || 'Failed to delete appointment', 'error');
      }
    } catch (err) {
      showToast('Error', 'Network error occurred', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Delete Appointment';
    }
  }

  // --- Mobile Touch Swipe Navigation for Queue Stages ---
  function setupTouchSwipeForLanes() {
    [1, 2, 3, 4].forEach(stageNum => {
      const lane = document.getElementById(`lane-stage-${stageNum}`);
      if (!lane) return;
      let startX = 0;
      let startY = 0;

      lane.addEventListener('touchstart', (e) => {
        if (!e.touches || e.touches.length === 0) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      }, { passive: true });

      lane.addEventListener('touchend', (e) => {
        if (!e.changedTouches || e.changedTouches.length === 0) return;
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        const diffX = endX - startX;
        const diffY = endY - startY;

        // Ensure horizontal swipe is dominant and exceeds minimum threshold (40px)
        if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
          if (diffX < 0) {
            // Swiped left with finger -> Next patient card
            slideStage(stageNum, 1);
          } else {
            // Swiped right with finger -> Previous patient card
            slideStage(stageNum, -1);
          }
        }
      }, { passive: true });
    });
  }
</script>

<?php include 'includes/booking_modal.php'; ?>
<?php include 'includes/book_popup.php'; ?>
<?php include 'includes/dossier_modal.php'; ?>
<?php include 'includes/footer.php'; ?>





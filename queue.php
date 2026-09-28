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
      <select id="queue-dept-filter" onchange="fetchQueuePipeline()" class="text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2 bg-slate-50">
        <option value="All">All Departments</option>
      </select>

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

<!-- DOCTOR CONSULTATION & DISPOSITION MODAL -->
<div id="modal-consultation" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-3xl lg:max-w-4xl xl:max-w-5xl w-full p-5 sm:p-8 shadow-2xl border border-slate-200 relative my-auto sm:my-8 max-h-[92vh] overflow-y-auto">
    <button onclick="closeConsultModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>

    <div class="flex items-start justify-between border-b border-slate-200 pb-4 mb-5">
      <div>
        <span class="text-xs font-bold uppercase tracking-wider text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full">Doctor Clinical Check</span>
        <h3 id="consult-patient-name" class="text-xl font-extrabold text-slate-900 mt-1">Patient Name</h3>
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
          <span>Father: <strong id="consult-father-name" class="text-slate-700"></strong></span>
          <span>•</span>
          <span>MRN: <strong id="consult-patient-id" class="font-mono text-slate-700"></strong></span>
          <span>•</span>
          <span>Type: <strong id="consult-patient-type" class="text-slate-700"></strong></span>
        </div>
      </div>
    </div>

    <form onsubmit="handleConsultationSave(event)" class="space-y-6">
      <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs">
        <span class="font-bold text-slate-600 block">Reported Symptoms / Complaints:</span>
        <p id="consult-reported-symptoms" class="text-slate-800 mt-0.5 italic"></p>
      </div>

      <!-- DIAGNOSES -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-bold uppercase text-slate-700 tracking-wider">Diagnosed Medical Problems</label>
          <button type="button" onclick="addDiagnosisInputRow()" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1">
            <i class="fa-solid fa-plus-circle"></i> Add
          </button>
        </div>
        <div id="diagnoses-input-container" class="space-y-2"></div>
      </div>

      <!-- DISPOSITION -->
      <div class="border-t border-slate-100 pt-4">
        <label class="block text-xs font-bold uppercase text-slate-700 tracking-wider mb-2">Clinical Outcome / Next Care Action *</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <label class="border-2 rounded-xl p-3 cursor-pointer transition border-emerald-500 bg-emerald-50/50" id="label-disposition-medicine">
            <input type="radio" name="dispositionOutcome" value="Normal Medicine" checked onchange="handleDispositionChange('Normal Medicine')" class="mr-1 text-emerald-600">
            <span class="text-xs font-bold text-slate-900 block">Normal Medicine</span>
          </label>
          <label class="border-2 rounded-xl p-3 cursor-pointer transition border-slate-200 hover:border-blue-400" id="label-disposition-opd">
            <input type="radio" name="dispositionOutcome" value="OPD" onchange="handleDispositionChange('OPD')" class="mr-1 text-blue-600">
            <span class="text-xs font-bold text-slate-900 block">Admit to OPD</span>
          </label>
          <label class="border-2 rounded-xl p-3 cursor-pointer transition border-slate-200 hover:border-rose-400" id="label-disposition-icu">
            <input type="radio" name="dispositionOutcome" value="ICU" onchange="handleDispositionChange('ICU')" class="mr-1 text-rose-600">
            <span class="text-xs font-bold text-slate-900 block">Admit to ICU</span>
          </label>
        </div>
      </div>

      <!-- BED ALLOTMENT -->
      <div id="bed-allotment-section" class="hidden bg-slate-50 border border-slate-300 rounded-xl p-4 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5"><i class="fa-solid fa-bed text-rose-600"></i> Select Bed</span>
          <span id="bed-selection-type-badge" class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">OPD Bed</span>
        </div>
        <select id="allotment-bed-select" class="w-full text-xs font-semibold border border-slate-300 rounded-xl p-2.5 bg-white"></select>
      </div>

      <!-- PRESCRIPTIONS -->
      <div class="border-t border-slate-100 pt-4">
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-bold uppercase text-slate-700 tracking-wider">Prescribe Medications</label>
          <button type="button" onclick="addMedicineInputRow()" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1">
            <i class="fa-solid fa-plus-circle"></i> Add
          </button>
        </div>
        <div id="medicines-input-container" class="space-y-3"></div>
      </div>

      <!-- FILE UPLOADS -->
      <div class="border-t border-slate-100 pt-4">
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-bold uppercase text-slate-700 tracking-wider">Medical Files & Scans</label>
          <button type="button" onclick="addFileInputRow()" class="text-xs text-blue-700 hover:text-blue-800 font-bold flex items-center gap-1">
            <i class="fa-solid fa-file-arrow-up"></i> Add File
          </button>
        </div>
        <div id="files-input-container" class="space-y-3"></div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase text-slate-700 tracking-wider mb-1">Clinical Notes</label>
        <textarea id="consult-doctor-notes" rows="2" class="w-full text-xs border border-slate-300 rounded-xl p-3"></textarea>
      </div>

      <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200">
        <button type="button" onclick="closeConsultModal()" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
        <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
          <i class="fa-solid fa-file-signature"></i> Finalize Consultation
        </button>
      </div>
    </form>
  </div>
</div>

<script>
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
              if (sel) {
                  sel.innerHTML = '<option value="">Choose doctor...</option>';
                  allHospitalDoctors.forEach(d => {
                      const spec = d.categories && d.categories.length ? ` (${d.categories.join(', ')})` : '';
                      sel.innerHTML += `<option value="${d.id}">${d.name}${spec}</option>`;
                  });
              }
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
                      <div class="text-[10px] text-slate-500 mt-0.5">📞 ${p.phone || 'N/A'} &nbsp; 🩸 ${p.blood_group || 'Unknown'} &nbsp; 🚹 ${p.gender || 'Unknown'}</div>
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
          const res = await fetch('api/booking.php?action=get_catalog');
          const data = await res.json();
          if (data.status === 'success') {
              const filter = document.getElementById('queue-dept-filter');
              data.departments.forEach(d => {
                  filter.innerHTML += `<option value="${d.id}">${d.name}</option>`;
              });
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
      const filtered = currentQueue.filter(p => filterDept === 'All' || p.dept === filterDept);
      return filtered.filter(p => p.stage === stageNum);
  }

  function renderQueuePipeline(queue) {
    const filterDept = document.getElementById('queue-dept-filter').value;
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

    const filtered = queue.filter(p => filterDept === 'All' || p.dept === filterDept);

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
                </td>
                <td class="py-3 px-4 font-bold text-slate-700">Stage ${p.stage}: ${p.status}</td>
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
        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md truncate">${p.status}</span>
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
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'In Consulting Room', 4, 'Called into consulting room.')" class="text-[10px] bg-purple-600 hover:bg-purple-700 text-white font-bold px-2 py-1 rounded">Call to Room</button>`;
    } else if (stage === 4) {
      html += `<button onclick="promptQueueStatusMove('${pStr}', 'In Waiting Area', 3, 'Reverted to waiting room.')" class="text-[10px] bg-slate-200 text-slate-700 font-bold px-2 py-1 rounded"><i class="fa-solid fa-rotate-left"></i></button>`;
      html += `<button onclick="openConsultationModal(${p.appointment_id}, '${p.id}', '${p.name}', '${p.surname}', '${p.father}', '${p.type}', '${p.symptoms}')" class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2 py-1 rounded">Diagnose</button>`;
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

  // --- Consultation Modal Logic ---
  async function fetchBedsForModal() {
      try {
          const res = await fetch('api/beds.php?action=get_all');
          const data = await res.json();
          if (data.status === 'success') allBeds = data.beds;
      } catch (e) {}
  }

  function openConsultationModal(app_id, patient_id, name, surname, father, type, symptoms) {
    activeConsultPatientId = patient_id;
    activeConsultAppId = app_id;
    document.getElementById('consult-patient-name').textContent = `${name} ${surname}`;
    document.getElementById('consult-father-name').textContent = father;
    document.getElementById('consult-patient-id').textContent = patient_id;
    document.getElementById('consult-patient-type').textContent = type;
    document.getElementById('consult-reported-symptoms').textContent = symptoms;
    
    document.getElementById('diagnoses-input-container').innerHTML = '';
    addDiagnosisInputRow();
    document.getElementById('medicines-input-container').innerHTML = '';
    addMedicineInputRow();
    document.getElementById('files-input-container').innerHTML = '';
    
    handleDispositionChange('Normal Medicine');
    document.getElementById('modal-consultation').classList.remove('hidden');
  }
  function closeConsultModal() { document.getElementById('modal-consultation').classList.add('hidden'); }
  function addDiagnosisInputRow() {
    const c = document.getElementById('diagnoses-input-container');
    const r = document.createElement('div');
    r.className = "flex items-center gap-2";
    r.innerHTML = `<input type="text" class="diag-item-input flex-1 text-xs border border-slate-300 rounded-xl px-3 py-2" placeholder="e.g. Fever"><button type="button" onclick="this.parentElement.remove()" class="text-rose-500"><i class="fa-solid fa-trash"></i></button>`;
    c.appendChild(r);
  }
  function addMedicineInputRow() {
    const c = document.getElementById('medicines-input-container');
    const r = document.createElement('div');
    r.className = "med-item-row bg-slate-50 p-2 rounded-xl grid grid-cols-2 gap-2 text-xs relative";
    r.innerHTML = `<input type="text" placeholder="Name" class="med-name border p-1.5 rounded"><input type="text" placeholder="Dose" class="med-dose border p-1.5 rounded"><input type="text" placeholder="Freq" class="med-freq border p-1.5 rounded"><input type="text" placeholder="Note" class="med-note border p-1.5 rounded"><button type="button" onclick="this.parentElement.remove()" class="text-rose-500 absolute -right-2 top-0"><i class="fa-solid fa-times-circle"></i></button>`;
    c.appendChild(r);
  }
  
  function addFileInputRow() {
    const c = document.getElementById('files-input-container');
    const r = document.createElement('div');
    const today = new Date().toISOString().substring(0,16);
    r.className = "file-item-row bg-slate-50 p-3 rounded-xl flex flex-col gap-2 text-xs relative";
    r.innerHTML = `
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <input type="text" placeholder="File Title *" required class="file-title border p-1.5 rounded w-full">
        <input type="datetime-local" class="file-date border p-1.5 rounded w-full" value="${today}">
        <input type="file" required accept="image/*,application/pdf" class="file-upload border p-1.5 rounded w-full bg-white" onchange="previewImage(this)">
      </div>
      <img class="img-preview mt-2 rounded border border-slate-200 hidden" style="max-height: 200px; width: auto; object-fit: contain; display: block;" />
      <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 absolute -right-2 -top-2 bg-white rounded-full shadow-sm"><i class="fa-solid fa-times-circle text-lg"></i></button>
    `;
    c.appendChild(r);
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

  function handleDispositionChange(disposition) {
    const section = document.getElementById('bed-allotment-section');
    const select = document.getElementById('allotment-bed-select');
    if (disposition === 'Normal Medicine') {
      section.classList.add('hidden');
    } else {
      section.classList.remove('hidden');
      const avail = allBeds.filter(b => b.type === disposition && b.status === 'Available');
      select.innerHTML = avail.length === 0 ? `<option value="">No ${disposition} beds available!</option>` : '';
      avail.forEach(b => select.innerHTML += `<option value="${b.bed_number}">${b.bed_number} - ${b.wing}</option>`);
    }
  }
  
  async function handleConsultationSave(e) {
    e.preventDefault();
    const diagnoses = []; document.querySelectorAll('.diag-item-input').forEach(i => { if(i.value.trim()) diagnoses.push(i.value.trim()); });
    const medicines = []; document.querySelectorAll('.med-item-row').forEach(r => {
      const name = r.querySelector('.med-name').value.trim();
      if(name) medicines.push({ name, dose: r.querySelector('.med-dose').value.trim(), freq: r.querySelector('.med-freq').value.trim(), duration: '', note: r.querySelector('.med-note').value.trim() });
    });
    const disposition = document.querySelector('input[name="dispositionOutcome"]:checked').value;
    const doctor_notes = document.getElementById('consult-doctor-notes').value.trim();
    let bed_number = disposition !== 'Normal Medicine' ? document.getElementById('allotment-bed-select').value : null;

    if (disposition !== 'Normal Medicine' && !bed_number) { showToast('Bed Required', 'Choose an available bed.', 'error'); return; }

    const formData = new FormData();
    formData.append('appointment_id', activeConsultAppId);
    formData.append('patient_id', activeConsultPatientId);
    formData.append('diagnoses', JSON.stringify(diagnoses));
    formData.append('medicines', JSON.stringify(medicines));
    formData.append('disposition', disposition);
    formData.append('doctor_notes', doctor_notes);
    if(bed_number) formData.append('bed_number', bed_number);

    document.querySelectorAll('.file-item-row').forEach(r => {
      const fileInput = r.querySelector('.file-upload');
      if (fileInput.files.length > 0) {
        formData.append(`files[]`, fileInput.files[0]);
        formData.append(`file_titles[]`, r.querySelector('.file-title').value.trim() || 'Untitled File');
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
            closeConsultModal(); fetchQueuePipeline(); showToast('Saved', data.message);
        } else showToast('Error', data.message, 'error');
    } catch (err) {}
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



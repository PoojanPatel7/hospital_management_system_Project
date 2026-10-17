<?php
require_once 'auth.php';
require_once 'db.php';

$hospital_id = (int)($_SESSION['hospital_id'] ?? 1);

// Initial summary queries
$totalStaffQuery = $conn->query("SELECT COUNT(*) AS total FROM staff WHERE hospital_id = $hospital_id");
$totalStaff = $totalStaffQuery ? (int)$totalStaffQuery->fetch_assoc()['total'] : 0;

$registeredFacesQuery = $conn->query("SELECT COUNT(*) AS reg FROM staff WHERE hospital_id = $hospital_id AND face_descriptor IS NOT NULL AND TRIM(face_descriptor) != '' AND face_descriptor != 'null'");
$registeredFaces = $registeredFacesQuery ? (int)$registeredFacesQuery->fetch_assoc()['reg'] : 0;

$pendingFaces = max(0, $totalStaff - $registeredFaces);

include 'includes/header.php';
?>

<div class="space-y-6 max-w-7xl mx-auto pb-12">

  <!-- ================= TOP HEADER ================= -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
    <div class="flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-indigo-500/20 shrink-0">
        <i class="fa-solid fa-face-viewfinder"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Staff Face Recognition Lab</h2>
          <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 rounded-full border border-purple-200">
            AI Test Suite
          </span>
        </div>
        <p class="text-slate-500 text-xs sm:text-sm font-medium mt-0.5">
          Register staff facial biometric profiles and test real-time camera recognition with voice announcements.
        </p>
      </div>
    </div>

    <!-- Right Quick Actions -->
    <div class="flex flex-wrap items-center gap-2.5">
      <a href="staff.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl transition flex items-center gap-2 text-xs shrink-0">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Staff Roster</span>
      </a>
      <a href="selfie_attendance.php" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold py-2.5 px-4 rounded-xl transition flex items-center gap-2 text-xs shrink-0">
        <i class="fa-solid fa-camera"></i>
        <span>Selfie Attendance</span>
      </a>
    </div>
  </div>

  <!-- ================= METRICS & STATS BAR ================= -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
        <i class="fa-solid fa-users"></i>
      </div>
      <div>
        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Staff</div>
        <div class="text-xl sm:text-2xl font-black text-slate-900" id="stat-total-staff"><?= $totalStaff ?></div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0">
        <i class="fa-solid fa-face-smile"></i>
      </div>
      <div>
        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Faces Registered</div>
        <div class="text-xl sm:text-2xl font-black text-emerald-600" id="stat-registered-faces"><?= $registeredFaces ?></div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shrink-0">
        <i class="fa-solid fa-user-clock"></i>
      </div>
      <div>
        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pending Faces</div>
        <div class="text-xl sm:text-2xl font-black text-amber-600" id="stat-pending-faces"><?= $pendingFaces ?></div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
      <div id="model-status-icon" class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold shrink-0">
        <i class="fa-solid fa-microchip animate-pulse"></i>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">AI Model Engine</div>
        <div id="model-status-text" class="text-xs sm:text-sm font-bold text-purple-700 truncate">Loading Models...</div>
      </div>
    </div>
  </div>

  <!-- ================= TABS NAVIGATION ================= -->
  <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
    <button id="tab-btn-register" onclick="switchLabTab('register')" class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition bg-slate-900 text-white shadow-xs">
      <i class="fa-solid fa-id-badge"></i>
      <span>1. Register Staff Face</span>
    </button>
    <button id="tab-btn-scanner" onclick="switchLabTab('scanner')" class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition bg-slate-100 text-slate-600 hover:text-slate-900">
      <i class="fa-solid fa-camera-rotate"></i>
      <span>2. Live Scanner &amp; Recognition Test</span>
    </button>
    <button id="tab-btn-roster" onclick="switchLabTab('roster')" class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition bg-slate-100 text-slate-600 hover:text-slate-900">
      <i class="fa-solid fa-table-list"></i>
      <span>3. Registered Staff Directory</span>
    </button>
  </div>

  <!-- ================= TAB 1: REGISTER STAFF FACE ================= -->
  <div id="tab-content-register" class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Left Panel: Staff Selection & Details -->
      <div class="lg:col-span-5 space-y-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
          <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">1</span>
            Select Staff Member
          </h3>
          
          <div class="space-y-3">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <input type="text" id="reg-staff-search" oninput="filterStaffSelect()" placeholder="Search staff by name or code..." class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition">
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Staff Roster Dropdown</label>
              <select id="reg-staff-select" onchange="onStaffSelected(this.value)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition">
                <option value="">-- Choose a Staff Member --</option>
              </select>
            </div>
          </div>

          <!-- Staff Detail Card -->
          <div id="reg-staff-card" class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200 hidden transition-all">
            <div class="flex items-start gap-4">
              <img id="reg-staff-avatar" src="" alt="Staff Avatar" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-sm shrink-0">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <h4 id="reg-staff-name" class="text-base font-bold text-slate-900 truncate">Staff Name</h4>
                  <span id="reg-staff-badge" class="px-2 py-0.5 rounded text-[10px] font-bold">Status</span>
                </div>
                <div class="text-xs text-slate-500 mt-0.5" id="reg-staff-code">STF-000</div>
                <div class="text-xs font-semibold text-slate-700 mt-1" id="reg-staff-role">Role • Department</div>
                <div class="text-[11px] text-slate-500" id="reg-staff-shift">Shift: Regular</div>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
              <div id="reg-face-status-indicator" class="text-xs font-bold text-amber-600 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>No face registered</span>
              </div>
              <button id="btn-delete-face" onclick="deleteFaceForSelectedStaff()" class="hidden text-xs text-rose-600 hover:text-rose-700 font-bold transition flex items-center gap-1">
                <i class="fa-solid fa-trash-can"></i>
                <span>Remove Face</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Registration Guidelines Card -->
        <div class="bg-indigo-50/60 rounded-2xl border border-indigo-100 p-4 text-xs text-indigo-900 space-y-2">
          <div class="font-bold flex items-center gap-1.5 text-indigo-950">
            <i class="fa-solid fa-circle-info text-indigo-600"></i>
            <span>Tips for Accurate Face Enrollment:</span>
          </div>
          <ul class="list-disc pl-5 space-y-1 text-indigo-800 text-[11px]">
            <li>Position yourself directly facing the camera with good ambient lighting.</li>
            <li>Maintain a neutral facial expression and keep glasses/hats consistent.</li>
            <li>Wait for the green detection box and checkmark before clicking Capture.</li>
            <li>The system extracts a 128-dimensional biometric vector saved securely to the hospital database.</li>
          </ul>
        </div>
      </div>

      <!-- Right Panel: Camera Viewport & Capture -->
      <div class="lg:col-span-7">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col h-full">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">2</span>
              Webcam Capture &amp; Facial Embedding
            </h3>
            <div class="flex items-center gap-2">
              <button onclick="toggleRegisterCamera()" id="btn-toggle-reg-cam" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-video"></i>
                <span id="txt-reg-cam-btn">Start Camera</span>
              </button>
            </div>
          </div>

          <!-- Video Viewport Frame -->
          <div class="relative w-full aspect-video sm:aspect-[4/3] bg-slate-950 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center border border-slate-800">
            <video id="reg-video" class="w-full h-full object-cover" playsinline muted autoplay></video>
            <canvas id="reg-canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

            <!-- Guide Reticle Overlay -->
            <div id="reg-reticle" class="absolute inset-0 pointer-events-none flex items-center justify-center">
              <div class="w-52 h-64 sm:w-60 sm:h-72 border-2 border-dashed border-white/40 rounded-[50px] shadow-2xl transition-all duration-300"></div>
            </div>

            <!-- In-Video Status Pill -->
            <div class="absolute bottom-4 left-0 right-0 text-center pointer-events-none px-4 z-10">
              <span id="reg-feedback-badge" class="bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3.5 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/10">
                <i class="fa-solid fa-camera"></i>
                <span id="reg-feedback-text">Camera Off</span>
              </span>
            </div>

            <!-- Camera Idle Placeholder -->
            <div id="reg-cam-placeholder" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
              <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-2xl text-slate-500 mb-3">
                <i class="fa-solid fa-camera"></i>
              </div>
              <h4 class="text-sm font-bold text-slate-200">Camera is Stopped</h4>
              <p class="text-xs text-slate-500 mt-1 max-w-xs">Select a staff member from the left and click "Start Camera" to begin face capture.</p>
              <button onclick="toggleRegisterCamera()" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-play"></i> Start Camera Feed
              </button>
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500 font-medium flex items-center gap-2">
              <i class="fa-solid fa-shield-halved text-slate-400"></i>
              <span>Biometric 128-D vector extracted locally in-browser</span>
            </div>

            <button id="btn-capture-save" onclick="captureAndRegisterFace()" disabled class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs sm:text-sm bg-slate-200 text-slate-400 cursor-not-allowed transition flex items-center justify-center gap-2 shadow-xs">
              <i class="fa-solid fa-floppy-disk"></i>
              <span id="btn-capture-save-text">Capture &amp; Save Face</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= TAB 2: LIVE SCANNER & ALL-DAY ATTENDANCE KIOSK ================= -->
  <div id="tab-content-scanner" class="space-y-6 hidden">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Left Column: Real-time Camera Scanner & Kiosk Viewport -->
      <div class="lg:col-span-7 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
          <!-- Terminal Header Bar -->
          <div class="flex items-center justify-between mb-3.5 flex-wrap gap-2.5 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-pulse shadow-sm shadow-emerald-500/50"></div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">All-Day Face Attendance Terminal</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    Live Auto-Punch
                  </span>
                </div>
                <div class="text-[11px] font-bold text-slate-500 mt-0.5 flex items-center gap-2">
                  <span id="kiosk-live-clock" class="text-blue-600 font-mono font-black text-xs">--:--:--</span>
                  <span class="text-slate-300">&bull;</span>
                  <span id="kiosk-live-date" class="text-slate-600"><?= date('l, d M Y') ?></span>
                </div>
              </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
              <!-- Reset Today's Test Data -->
              <button onclick="resetTodayAttendanceForTesting()" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition flex items-center gap-1.5 shadow-xs" title="Reset today's face punch records to test fresh">
                <i class="fa-solid fa-arrows-rotate text-amber-600"></i>
                <span>Reset Today (Test)</span>
              </button>

              <!-- Anti-Spoofing Liveness Toggle -->
              <label class="flex items-center gap-1.5 cursor-pointer bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition select-none" title="Prevent attendance via printed photo or phone screen (Requires live human presence)">
                <input type="checkbox" id="chk-scanner-antispoof" checked class="rounded text-emerald-600 focus:ring-0">
                <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                <span>Anti-Spoof</span>
              </label>

              <!-- Auto-Punch Toggle -->
              <label class="flex items-center gap-1.5 cursor-pointer bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition select-none" title="Automatically record attendance as soon as face & liveness is verified">
                <input type="checkbox" id="chk-scanner-autopunch" checked class="rounded text-blue-600 focus:ring-0">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Auto-Punch</span>
              </label>

              <!-- Voice Toggle -->
              <label class="flex items-center gap-1.5 cursor-pointer bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition select-none">
                <input type="checkbox" id="chk-scanner-voice" checked class="rounded text-indigo-600 focus:ring-0">
                <i class="fa-solid fa-volume-high text-indigo-600"></i>
                <span>Voice</span>
              </label>

              <!-- Scanner Toggle Button -->
              <button onclick="toggleScannerCamera()" id="btn-toggle-scan-cam" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-video"></i>
                <span id="txt-scan-cam-btn">Start All-Day Scanner</span>
              </button>
            </div>
          </div>

          <!-- Video Viewport Frame -->
          <div class="relative w-full aspect-video sm:aspect-[4/3] bg-slate-950 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center border border-slate-800">
            <video id="scan-video" class="w-full h-full object-cover" playsinline muted autoplay></video>
            <canvas id="scan-canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

            <!-- Liveness & Anti-Spoof In-Video HUD Badge (Top-Left) -->
            <div class="absolute top-4 left-4 z-20 pointer-events-none">
              <span id="scan-liveness-pill" class="bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 border border-white/10 shadow-lg">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                <span id="scan-liveness-text">Anti-Spoof Active</span>
              </span>
            </div>

            <!-- In-Video Overlay Status (Bottom) -->
            <div class="absolute bottom-4 left-0 right-0 text-center pointer-events-none px-4 z-10">
              <span id="scan-status-badge" class="bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/10">
                <i class="fa-solid fa-camera text-cyan-400"></i>
                <span id="scan-status-text">Scanner Inactive</span>
              </span>
            </div>

            <!-- Scanner Stopped Placeholder -->
            <div id="scan-placeholder" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
              <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-2xl text-cyan-400 mb-3 shadow-lg shadow-cyan-500/10">
                <i class="fa-solid fa-face-viewfinder"></i>
              </div>
              <h4 class="text-base font-bold text-slate-100">Attendance Terminal Ready</h4>
              <p class="text-xs text-slate-400 mt-1 max-w-sm">Click below to start camera. Leave this running all day — when staff members look into the camera and blink, their attendance will be automatically marked!</p>
              <button onclick="toggleScannerCamera()" class="mt-4 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-play"></i> Start All-Day Terminal
              </button>
            </div>
          </div>

          <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 font-medium flex-wrap gap-2">
            <span id="scan-registered-count-label"><i class="fa-solid fa-database text-blue-500 mr-1"></i> Loaded faces: 0</span>
            <div class="flex items-center gap-3">
              <span class="text-emerald-600 font-bold"><i class="fa-solid fa-shield-halved mr-1"></i> Anti-Photocopy Active</span>
              <span><i class="fa-solid fa-bullhorn text-indigo-500 mr-1"></i> TTS Voice Active</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Recognition Result & Today's Live Attendance Feed -->
      <div class="lg:col-span-5 space-y-4">
        
        <!-- Live Terminal Status Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all duration-300">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Terminal Attendance Status</h4>
            <span id="match-status-pill" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600">
              Awaiting Staff Face
            </span>
          </div>

          <!-- 1. EMPTY / IDLE STATE -->
          <div id="res-empty-state" class="py-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mb-3 shadow-inner">
              <i class="fa-solid fa-clock"></i>
            </div>
            <h4 class="text-base font-extrabold text-slate-800">Ready for Staff Attendance</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Stand in front of the camera and blink naturally. The AI will verify live presence and log your attendance.</p>
            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
              <i class="fa-solid fa-eye text-emerald-600"></i>
              <span>Anti-Spoofing: Natural Eye Blink Required</span>
            </div>
          </div>

          <!-- 2. SUCCESS STATE: FIRST TIME CHECK-IN TODAY -->
          <div id="res-success-card" class="mt-4 space-y-4 hidden transition-all">
            <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/15 via-emerald-50/50 to-white border-2 border-emerald-500/30 shadow-md">
              <div class="flex items-start gap-4">
                <img id="succ-avatar" src="" alt="Staff Avatar" class="w-20 h-20 rounded-2xl object-cover border-2 border-emerald-500 shadow-md shrink-0">
                <div class="min-w-0 flex-1">
                  <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-xs">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Attendance Marked Successfully!</span>
                  </div>
                  <h3 id="succ-name" class="text-xl font-black text-slate-900 truncate mt-1">Staff Full Name</h3>
                  <div class="text-xs font-bold text-slate-700" id="succ-code">STF-000</div>
                  <div class="text-xs font-semibold text-slate-600" id="succ-role">Nurse • Emergency</div>
                </div>
              </div>

              <!-- Time & Status Banner -->
              <div class="mt-4 p-3 bg-white/90 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div>
                  <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Punch Time</div>
                  <div class="text-base font-black text-slate-900" id="succ-time">09:15 AM</div>
                </div>
                <div class="text-right">
                  <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</div>
                  <span id="succ-status" class="px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800">
                    Present
                  </span>
                </div>
              </div>

              <!-- Liveness & Match metrics -->
              <div class="mt-3 flex items-center justify-between text-[11px] font-bold text-emerald-700 bg-emerald-100/60 p-2 rounded-xl">
                <span><i class="fa-solid fa-shield-check mr-1"></i> Live Human Verified (Eye Blink ✓)</span>
                <span id="succ-confidence">98% Match</span>
              </div>
            </div>
          </div>

          <!-- 3. ALREADY MARKED TODAY STATE -->
          <div id="res-already-card" class="mt-4 space-y-4 hidden transition-all">
            <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/15 via-amber-50/50 to-white border-2 border-amber-500/30 shadow-md">
              <div class="flex items-start gap-4">
                <img id="alr-avatar" src="" alt="Staff Avatar" class="w-18 h-18 rounded-2xl object-cover border-2 border-amber-500 shadow-md shrink-0">
                <div class="min-w-0 flex-1">
                  <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white shadow-xs">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Already Marked Today!</span>
                  </div>
                  <h3 id="alr-name" class="text-lg font-black text-slate-900 truncate mt-1">Staff Full Name</h3>
                  <div class="text-xs font-bold text-slate-700" id="alr-code">STF-000</div>
                  <div class="text-xs font-semibold text-slate-600" id="alr-role">Role • Department</div>
                </div>
              </div>

              <!-- Notice Box -->
              <div class="mt-3 p-3 bg-white/90 rounded-xl border border-amber-200">
                <div class="flex items-center justify-between text-xs font-bold">
                  <span class="text-slate-500">First Punch Today:</span>
                  <span class="text-amber-800 font-black text-sm" id="alr-time">09:05 AM</span>
                </div>
                <div class="flex items-center justify-between text-xs font-bold mt-1">
                  <span class="text-slate-500">Attendance Status:</span>
                  <span class="text-emerald-700 font-bold" id="alr-status">Present</span>
                </div>
                <div class="text-[11px] text-amber-700 mt-2 font-medium bg-amber-50 p-2 rounded-lg">
                  <i class="fa-solid fa-check-double mr-1"></i> Your attendance for today was already recorded. No need to punch again!
                </div>
              </div>
            </div>
          </div>

          <!-- 4. ANTI-SPOOFING REJECTED (PHONE / SCREEN / PHOTO DETECTED) -->
          <div id="res-spoof-card" class="mt-4 p-5 rounded-2xl bg-rose-50 border-2 border-rose-300 text-center space-y-3 hidden transition-all">
            <div class="w-14 h-14 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl shadow-inner animate-bounce">
              <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
              <div class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white">
                3D Anti-Spoof Shield
              </div>
              <h4 class="text-base font-black text-rose-900 mt-1">2D Phone Screen / Flat Photo Detected!</h4>
              <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                Attendance was <strong>blocked</strong> because the camera detected a flat 2D plane (smartphone screen or printed photo) with zero 3D facial depth.
                <br><span class="font-bold underline">A real living person with natural 3D depth</span> must be present in front of the camera.
              </p>
            </div>
          </div>

          <!-- 5. UNRECOGNIZED PERSON STATE -->
          <div id="res-unknown-card" class="mt-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3 hidden">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xl">
              <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900">Face Not Registered</h4>
              <p class="text-xs text-slate-500 mt-1">This face is not registered in the hospital database. Please enroll this staff member in Tab 1 first.</p>
            </div>
            <button onclick="switchLabTab('register')" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
              Go to Face Enrollment
            </button>
          </div>
        </div>

        <!-- Today's Live Attendance Feed -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
          <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
              <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Today's Live Attendance Feed</h4>
              <span id="today-punch-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                0 Punched
              </span>
            </div>
            <button onclick="fetchTodayFacePunches()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
              <i class="fa-solid fa-rotate"></i> Refresh
            </button>
          </div>
          <div id="today-punches-list" class="space-y-2 max-h-56 overflow-y-auto custom-scrollbar text-xs">
            <div class="text-slate-400 text-center py-6">Loading today's punch records...</div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ================= TAB 3: REGISTERED STAFF DIRECTORY ================= -->
  <div id="tab-content-roster" class="space-y-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-bold text-slate-900">Hospital Staff Biometric Roster</h3>
          <p class="text-xs text-slate-500">Overview of all hospital personnel and their face enrollment status.</p>
        </div>
        
        <div class="flex items-center gap-2">
          <div class="relative w-64">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="roster-search-input" oninput="filterRosterTable()" placeholder="Search staff by name/code..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white outline-none">
          </div>
          <select id="roster-status-filter" onchange="filterRosterTable()" class="text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700">
            <option value="All">All Statuses</option>
            <option value="Registered">Registered Only</option>
            <option value="Pending">Pending Only</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider bg-slate-50/60">
              <th class="py-3 px-4">Staff Member</th>
              <th class="py-3 px-4">Staff Code</th>
              <th class="py-3 px-4">Role &amp; Department</th>
              <th class="py-3 px-4">Shift</th>
              <th class="py-3 px-4">Face Biometrics</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="roster-table-body" class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr>
              <td colspan="6" class="text-center py-8 text-slate-400">Loading staff data...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- 100% Local Offline Face-API.js library -->
<script src="js/face-api.min.js"></script>

<script>
// ===================================================================
// GLOBAL STATE & VARIABLES
// ===================================================================
let allStaffList = [];
let registeredStaffList = [];
let registeredStaffMap = {};
let faceMatcher = null;
let modelsLoaded = false;

// Register Camera State
let regStream = null;
let regInterval = null;
let regCurrentDetection = null;
let selectedStaffMember = null;

// Scanner Camera & Terminal State
let scanStream = null;
let scanAnimationId = null;
let isScanning = false;
let lastMatchedStaff = null;
let lastAnnouncementTime = 0;
let lastAnnouncedStaffId = null;
const ANNOUNCEMENT_COOLDOWN_MS = 6000;

// All-Day Kiosk Terminal State & Cooldowns
let terminalDisplayLockedUntil = 0;
let currentDisplayedStaffId = null;
let autoPunchCooldown = {}; // { [staffId]: timestamp }
let isPunchRequestInProgress = false;
let clockTimerInterval = null;
let todayPunchesRefreshInterval = null;
let noFaceFramesCount = 0;

// Anti-Spoofing & Liveness State (3D Live Depth, Parallax, EAR & Natural Micro-movement)
let livenessTracker = {
  facePresent: false,
  firstSeenTime: 0,
  framesCount: 0,
  earHistory: [],
  depthHistory: [],
  vertHistory: [],
  motionHistory: [],
  baselineEAR: 0.28,
  blinkDetected: false,
  lastBlinkTime: 0,
  blinkCount: 0,
  isEyeClosed: false,
  closedFrames: 0,
  isPhotoBlocked: false,
  isPhoneScreenDetected: false,
  liveConfidence: 15,
  depthScore: 0,
  lastSpoofAlertTime: 0
};

// ===================================================================
// INITIALIZATION
// ===================================================================
document.addEventListener('DOMContentLoaded', async () => {
  initTerminalClock();
  await loadFaceModels();
  await refreshStaffData();
  await refreshRegisteredFaces();
  await fetchTodayFacePunches();
  
  // Periodic background refresh for today's punches list (every 30 seconds)
  if (todayPunchesRefreshInterval) clearInterval(todayPunchesRefreshInterval);
  todayPunchesRefreshInterval = setInterval(fetchTodayFacePunches, 30000);
});

// Load Face API Neural Net Models
async function loadFaceModels() {
  const statusIcon = document.getElementById('model-status-icon');
  const statusText = document.getElementById('model-status-text');
  
  // 100% Local models directory (Offline)
  const MODEL_URL = 'models/';
  
  try {
    statusText.innerText = "Loading AI Weights...";
    
    // Load SSD MobileNet (accurate), Tiny Face (fast), Landmark68 and RecognitionNet
    await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
    
    modelsLoaded = true;
    statusIcon.className = "w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0";
    statusIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
    statusText.className = "text-xs sm:text-sm font-bold text-emerald-600 truncate";
    statusText.innerText = "High-Speed AI Model Ready (30+ FPS)";
    console.log("FaceAPI models loaded successfully.");
  } catch (err) {
    console.error("Failed to load FaceAPI models:", err);
    statusIcon.className = "w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold shrink-0";
    statusIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
    statusText.className = "text-xs sm:text-sm font-bold text-rose-600 truncate";
    statusText.innerText = "Model Load Failed";
    alert("Could not load facial recognition AI models. Please check internet connection.");
  }
}

// Fetch all staff for registration & roster
async function refreshStaffData() {
  try {
    const res = await fetch('api/staff_face.php?action=get_all_staff');
    const json = await res.json();
    if (json.status === 'success') {
      allStaffList = json.data;
      updateStats();
      populateStaffSelect();
      renderRosterTable();
    }
  } catch (e) {
    console.error("Error fetching staff list:", e);
  }
}

// Fetch all staff who have face descriptors registered
async function refreshRegisteredFaces() {
  try {
    const res = await fetch('api/staff_face.php?action=get_registered_faces');
    const json = await res.json();
    if (json.status === 'success') {
      registeredStaffList = json.data;
      registeredStaffMap = {};
      
      const labeledDescriptors = [];
      registeredStaffList.forEach(s => {
        registeredStaffMap[s.id] = s;
        if (s.descriptor && Array.isArray(s.descriptor) && s.descriptor.length >= 64) {
          labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(
            String(s.id),
            [new Float32Array(s.descriptor)]
          ));
        }
      });
      
      if (labeledDescriptors.length > 0) {
        // 0.55 distance threshold: < 0.55 distance is considered a solid match
        faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.55);
      } else {
        faceMatcher = null;
      }
      
      document.getElementById('scan-registered-count-label').innerHTML = `<i class="fa-solid fa-database text-blue-500 mr-1"></i> Loaded faces: <strong>${registeredStaffList.length}</strong>`;
      updateStats();
    }
  } catch (e) {
    console.error("Error fetching registered faces:", e);
  }
}

function updateStats() {
  const total = allStaffList.length;
  const reg = allStaffList.filter(s => s.has_face).length;
  const pending = Math.max(0, total - reg);
  
  document.getElementById('stat-total-staff').innerText = total;
  document.getElementById('stat-registered-faces').innerText = reg;
  document.getElementById('stat-pending-faces').innerText = pending;
}

// ===================================================================
// TAB SWITCHING
// ===================================================================
function switchLabTab(tab) {
  // Stop any running camera streams when switching tabs
  stopRegisterCamera();
  stopScannerCamera();
  
  ['register', 'scanner', 'roster'].forEach(t => {
    const btn = document.getElementById(`tab-btn-${t}`);
    const content = document.getElementById(`tab-content-${t}`);
    if (t === tab) {
      btn.className = "px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition bg-slate-900 text-white shadow-xs";
      content.classList.remove('hidden');
    } else {
      btn.className = "px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition bg-slate-100 text-slate-600 hover:text-slate-900";
      content.classList.add('hidden');
    }
  });
}

// ===================================================================
// TAB 1: FACE REGISTRATION
// ===================================================================
function populateStaffSelect(filterText = '') {
  const select = document.getElementById('reg-staff-select');
  const currentVal = select.value;
  select.innerHTML = '<option value="">-- Choose a Staff Member --</option>';
  
  const query = filterText.toLowerCase().trim();
  allStaffList.forEach(s => {
    const fullName = `${s.first_name} ${s.last_name}`;
    const match = !query || fullName.toLowerCase().includes(query) || (s.staff_code && s.staff_code.toLowerCase().includes(query));
    if (match) {
      const opt = document.createElement('option');
      opt.value = s.id;
      const statusIcon = s.has_face ? '✓ [Enrolled]' : '○ [Pending]';
      opt.textContent = `${statusIcon} ${fullName} (${s.staff_code || 'ID: ' + s.id}) - ${s.role}`;
      select.appendChild(opt);
    }
  });
  
  if (currentVal && select.querySelector(`option[value="${currentVal}"]`)) {
    select.value = currentVal;
  }
}

function filterStaffSelect() {
  const query = document.getElementById('reg-staff-search').value;
  populateStaffSelect(query);
}

function onStaffSelected(staffId) {
  const card = document.getElementById('reg-staff-card');
  const captureBtn = document.getElementById('btn-capture-save');
  
  if (!staffId) {
    selectedStaffMember = null;
    card.classList.add('hidden');
    captureBtn.disabled = true;
    captureBtn.className = "w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs sm:text-sm bg-slate-200 text-slate-400 cursor-not-allowed transition flex items-center justify-center gap-2 shadow-xs";
    return;
  }
  
  selectedStaffMember = allStaffList.find(s => String(s.id) === String(staffId));
  if (!selectedStaffMember) return;
  
  card.classList.remove('hidden');
  document.getElementById('reg-staff-avatar').src = selectedStaffMember.avatar;
  document.getElementById('reg-staff-name').innerText = `${selectedStaffMember.first_name} ${selectedStaffMember.last_name}`;
  document.getElementById('reg-staff-code').innerText = selectedStaffMember.staff_code || `ID: #${selectedStaffMember.id}`;
  document.getElementById('reg-staff-role').innerText = `${selectedStaffMember.role} • ${selectedStaffMember.department || 'General'}`;
  document.getElementById('reg-staff-shift').innerText = `Shift: ${selectedStaffMember.shift || 'General (09:00 - 17:00)'}`;
  
  const badge = document.getElementById('reg-staff-badge');
  const statusIndicator = document.getElementById('reg-face-status-indicator');
  const delBtn = document.getElementById('btn-delete-face');
  
  if (selectedStaffMember.has_face) {
    badge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800";
    badge.innerText = "Face Enrolled";
    statusIndicator.className = "text-xs font-bold text-emerald-600 flex items-center gap-1.5";
    statusIndicator.innerHTML = '<i class="fa-solid fa-circle-check"></i><span>Face biometric registered</span>';
    delBtn.classList.remove('hidden');
  } else {
    badge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800";
    badge.innerText = "Pending Registration";
    statusIndicator.className = "text-xs font-bold text-amber-600 flex items-center gap-1.5";
    statusIndicator.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i><span>No face registered yet</span>';
    delBtn.classList.add('hidden');
  }
  
  checkCaptureButtonReadiness();
}

function checkCaptureButtonReadiness() {
  const captureBtn = document.getElementById('btn-capture-save');
  const btnText = document.getElementById('btn-capture-save-text');
  
  if (selectedStaffMember && regCurrentDetection && regStream) {
    captureBtn.disabled = false;
    captureBtn.className = "w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs sm:text-sm bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md transition flex items-center justify-center gap-2 cursor-pointer transform active:scale-95";
    btnText.innerText = selectedStaffMember.has_face ? "Re-Capture & Update Face" : "Capture & Save Face";
  } else {
    captureBtn.disabled = true;
    captureBtn.className = "w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs sm:text-sm bg-slate-200 text-slate-400 cursor-not-allowed transition flex items-center justify-center gap-2 shadow-xs";
    if (!selectedStaffMember) {
      btnText.innerText = "Select Staff Member First";
    } else if (!regStream) {
      btnText.innerText = "Start Camera First";
    } else {
      btnText.innerText = "Align Face in Center";
    }
  }
}

async function toggleRegisterCamera() {
  if (regStream) {
    stopRegisterCamera();
  } else {
    await startRegisterCamera();
  }
}

async function startRegisterCamera() {
  if (!modelsLoaded) {
    alert("Please wait for AI neural models to finish loading.");
    return;
  }
  
  const video = document.getElementById('reg-video');
  const placeholder = document.getElementById('reg-cam-placeholder');
  const txtBtn = document.getElementById('txt-reg-cam-btn');
  const feedbackText = document.getElementById('reg-feedback-text');
  
  try {
    feedbackText.innerText = "Opening webcam...";
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
      audio: false
    });
    
    video.srcObject = stream;
    regStream = stream;
    placeholder.classList.add('hidden');
    txtBtn.innerText = "Stop Camera";
    feedbackText.innerText = "Camera Active • Looking for face...";
    
    video.onloadedmetadata = () => {
      video.play();
      const canvas = document.getElementById('reg-canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      startRegisterDetectionLoop();
    };
  } catch (err) {
    console.error("Camera access error:", err);
    alert("Could not access camera: " + err.message + "\nPlease verify browser camera permissions.");
    stopRegisterCamera();
  }
}

function stopRegisterCamera() {
  if (regStream) {
    regStream.getTracks().forEach(t => t.stop());
    regStream = null;
  }
  if (regInterval) {
    cancelAnimationFrame(regInterval);
    regInterval = null;
  }
  
  regCurrentDetection = null;
  const placeholder = document.getElementById('reg-cam-placeholder');
  const txtBtn = document.getElementById('txt-reg-cam-btn');
  const feedbackText = document.getElementById('reg-feedback-text');
  const canvas = document.getElementById('reg-canvas');
  
  if (placeholder) placeholder.classList.remove('hidden');
  if (txtBtn) txtBtn.innerText = "Start Camera";
  if (feedbackText) feedbackText.innerText = "Camera Off";
  if (canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
  
  checkCaptureButtonReadiness();
}

async function startRegisterDetectionLoop() {
  const video = document.getElementById('reg-video');
  const canvas = document.getElementById('reg-canvas');
  const feedbackBadge = document.getElementById('reg-feedback-badge');
  const feedbackText = document.getElementById('reg-feedback-text');
  const reticle = document.getElementById('reg-reticle');
  
  if (!video || !canvas) return;
  const ctx = canvas.getContext('2d');
  
  const detectFrame = async () => {
    if (!regStream) return;
    
    try {
      // Detect single face with landmarks & descriptor using high-speed TinyFaceDetector
      const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
        .withFaceLandmarks()
        .withFaceDescriptor();
      
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      if (detection) {
        regCurrentDetection = detection;
        const box = detection.detection.box;
        
        // Draw futuristic corner brackets
        drawCornerBox(ctx, box.x, box.y, box.width, box.height, '#10b981', 4);
        
        // Draw landmarks dots subtly
        const landmarks = detection.landmarks.positions;
        ctx.fillStyle = '#34d399';
        landmarks.forEach(pt => {
          ctx.beginPath();
          ctx.arc(pt.x, pt.y, 1.5, 0, 2 * Math.PI);
          ctx.fill();
        });
        
        feedbackBadge.className = "bg-emerald-600/90 backdrop-blur-md text-white text-xs font-bold px-3.5 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
        feedbackText.innerText = `Face Detected ✓ (${Math.round(detection.detection.score * 100)}% quality)`;
        
        if (reticle) reticle.querySelector('div').style.borderColor = '#10b981';
      } else {
        regCurrentDetection = null;
        feedbackBadge.className = "bg-amber-600/90 backdrop-blur-md text-white text-xs font-bold px-3.5 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
        feedbackText.innerText = "No Face Detected • Center your face";
        if (reticle) reticle.querySelector('div').style.borderColor = 'rgba(255,255,255,0.4)';
      }
      
      checkCaptureButtonReadiness();
    } catch (e) {
      console.warn("Register detection loop error:", e);
    }
    
    if (regStream) {
      regInterval = requestAnimationFrame(detectFrame);
    }
  };
  
  regInterval = requestAnimationFrame(detectFrame);
}

// Capture current face descriptor and send to server
async function captureAndRegisterFace() {
  if (!selectedStaffMember) {
    alert("Please select a staff member first.");
    return;
  }
  if (!regCurrentDetection) {
    alert("No clear face detected in the camera frame. Please position face inside the guide.");
    return;
  }
  
  // DUPLICATE FACE ENROLLMENT PRE-CHECK (Client-Side)
  // Check if this face already belongs to another staff member
  if (faceMatcher && regCurrentDetection && regCurrentDetection.descriptor) {
    const bestMatch = faceMatcher.findBestMatch(regCurrentDetection.descriptor);
    if (bestMatch.label !== 'unknown' && String(bestMatch.label) !== String(selectedStaffMember.id) && bestMatch.distance < 0.48) {
      const otherStaff = registeredStaffMap[bestMatch.label];
      const otherName = otherStaff ? `${otherStaff.first_name} ${otherStaff.last_name}` : `Staff ID #${bestMatch.label}`;
      const otherCode = otherStaff?.staff_code ? ` (${otherStaff.staff_code})` : '';
      const similarity = Math.round((1.0 - (bestMatch.distance / 0.6)) * 100);
      alert(`⚠️ DUPLICATE FACE DETECTED!\n\nThis face is ALREADY enrolled for: ${otherName}${otherCode} (${similarity}% similarity match).\n\nThe system strictly prohibits registering the same face for multiple staff members. Each staff member must have a unique face enrolled.`);
      return;
    }
  }
  
  const video = document.getElementById('reg-video');
  const captureBtn = document.getElementById('btn-capture-save');
  const btnText = document.getElementById('btn-capture-save-text');
  
  // Snap photo snapshot from video frame
  const snapCanvas = document.createElement('canvas');
  snapCanvas.width = video.videoWidth || 640;
  snapCanvas.height = video.videoHeight || 480;
  const sCtx = snapCanvas.getContext('2d');
  sCtx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);
  const snapshotDataUrl = snapCanvas.toDataURL('image/jpeg', 0.85);
  
  // Convert 128 Float32Array to standard array
  const descriptorArray = Array.from(regCurrentDetection.descriptor);
  
  captureBtn.disabled = true;
  btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Biometrics...';
  
  try {
    const payload = {
      action: 'register_face',
      staff_id: selectedStaffMember.id,
      face_descriptor: descriptorArray,
      snapshot_image: snapshotDataUrl
    };
    
    const res = await fetch('api/staff_face.php?action=register_face', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    const result = await res.json();
    
    if (result.status === 'success') {
      playSuccessChime();
      alert(`Success! Face biometrics successfully registered for ${result.data.staff_name}!`);
      
      // Refresh local cache and list
      await refreshStaffData();
      await refreshRegisteredFaces();
      
      // Update view for this staff
      onStaffSelected(selectedStaffMember.id);
    } else {
      if (result.code === 'DUPLICATE_FACE') {
        alert("⚠️ REGISTRATION REJECTED:\n\n" + result.message);
      } else {
        alert("Registration failed: " + result.message);
      }
    }
  } catch (err) {
    console.error("Save face error:", err);
    alert("Network or server error while registering face: " + err.message);
  } finally {
    checkCaptureButtonReadiness();
  }
}

// Delete face for selected staff
async function deleteFaceForSelectedStaff() {
  if (!selectedStaffMember) return;
  if (!confirm(`Are you sure you want to remove registered face biometrics for ${selectedStaffMember.first_name} ${selectedStaffMember.last_name}?`)) {
    return;
  }
  
  try {
    const res = await fetch('api/staff_face.php?action=delete_face', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_face', staff_id: selectedStaffMember.id })
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert("Face data removed.");
      await refreshStaffData();
      await refreshRegisteredFaces();
      onStaffSelected(selectedStaffMember.id);
    } else {
      alert("Error: " + result.message);
    }
  } catch (e) {
    alert("Request error: " + e.message);
  }
}

// ===================================================================
// TAB 2: ALL-DAY ATTENDANCE TERMINAL (KIOSK SCANNER)
// ===================================================================
function initTerminalClock() {
  const clockEl = document.getElementById('kiosk-live-clock');
  const dateEl = document.getElementById('kiosk-live-date');
  
  function updateTime() {
    const now = new Date();
    if (clockEl) {
      clockEl.innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }
    if (dateEl) {
      dateEl.innerText = now.toLocaleDateString([], { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' });
    }
  }
  
  updateTime();
  if (clockTimerInterval) clearInterval(clockTimerInterval);
  clockTimerInterval = setInterval(updateTime, 1000);
}

// Fetch Today's Live Attendance Punches for the terminal feed
async function fetchTodayFacePunches() {
  const listEl = document.getElementById('today-punches-list');
  const badgeEl = document.getElementById('today-punch-badge');
  if (!listEl) return;
  
  try {
    const res = await fetch('api/staff_face.php?action=get_today_face_punches');
    const json = await res.json();
    if (json.status === 'success') {
      const punches = json.data || [];
      if (badgeEl) {
        badgeEl.innerText = `${punches.length} Punched Today`;
      }
      
      if (punches.length === 0) {
        listEl.innerHTML = `
          <div class="text-slate-400 text-center py-6">
            <i class="fa-regular fa-calendar-xmark text-lg mb-1 block text-slate-300"></i>
            No attendance punches recorded yet today.
          </div>
        `;
        return;
      }
      
      listEl.innerHTML = punches.map(p => {
        const isLate = p.status === 'Late';
        const isHalfDay = p.status === 'Half Day';
        const badgeClass = isLate 
          ? 'bg-amber-100 text-amber-800' 
          : (isHalfDay ? 'bg-orange-100 text-orange-800' : 'bg-emerald-100 text-emerald-800');
        
        return `
          <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition">
            <div class="flex items-center gap-2.5 min-w-0">
              <img src="${p.avatar}" alt="${p.first_name}" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shrink-0">
              <div class="min-w-0">
                <div class="font-bold text-slate-900 truncate">${p.first_name} ${p.last_name}</div>
                <div class="text-[10px] text-slate-400 font-semibold truncate">${p.role} • ${p.department || 'General'}</div>
              </div>
            </div>
            <div class="text-right shrink-0">
              <span class="px-2 py-0.5 rounded text-[10px] font-black ${badgeClass}">
                ${p.status}
              </span>
              <div class="text-[10px] text-slate-500 font-bold mt-0.5">${p.time_formatted || p.check_in_time}</div>
            </div>
          </div>
        `;
      }).join('');
    }
  } catch (err) {
    console.error("Error fetching today's punches:", err);
  }
}

// -------------------------------------------------------------------
// ANTI-SPOOFING & EYE ASPECT RATIO (EAR) LIVENESS ENGINE
// -------------------------------------------------------------------
function euclideanDistance(pt1, pt2) {
  return Math.hypot(pt1.x - pt2.x, pt1.y - pt2.y);
}

// Calculates Eye Aspect Ratio (EAR) using 6 landmark points
function getEyeAspectRatio(eye) {
  // eye = 6 points [p0, p1, p2, p3, p4, p5]
  const v1 = euclideanDistance(eye[1], eye[5]);
  const v2 = euclideanDistance(eye[2], eye[4]);
  const h = euclideanDistance(eye[0], eye[3]);
  if (h === 0) return 0;
  return (v1 + v2) / (2.0 * h);
}

function resetLivenessTracker() {
  livenessTracker.facePresent = false;
  livenessTracker.firstSeenTime = 0;
  livenessTracker.framesCount = 0;
  livenessTracker.earHistory = [];
  livenessTracker.depthHistory = [];
  livenessTracker.vertHistory = [];
  livenessTracker.motionHistory = [];
  livenessTracker.baselineEAR = 0.28;
  livenessTracker.blinkDetected = false;
  livenessTracker.lastBlinkTime = 0;
  livenessTracker.blinkCount = 0;
  livenessTracker.isEyeClosed = false;
  livenessTracker.closedFrames = 0;
  livenessTracker.isPhotoBlocked = false;
  livenessTracker.isPhoneScreenDetected = false;
  livenessTracker.liveConfidence = 15;
  livenessTracker.depthScore = 0;
}

// Smart Multi-Signal 3D Live Depth & Anti-Spoofing Engine
// Evaluates 3D nose-eye parallax depth relief, eye blink dynamics, and rejects 2D flat screens/photos
function evaluateLiveness(landmarks, box) {
  const now = Date.now();
  const pts = landmarks.positions || landmarks;
  
  // 68 Facial Landmarks: Left eye = points 36..41, Right eye = points 42..47
  const leftEye = pts.slice(36, 42);
  const rightEye = pts.slice(42, 48);
  
  const leftEAR = getEyeAspectRatio(leftEye);
  const rightEAR = getEyeAspectRatio(rightEye);
  const avgEAR = (leftEAR + rightEAR) / 2.0;
  
  // Centers of eyes in 2D plane
  const leftEyeCenter = {
    x: (pts[36].x + pts[39].x) / 2.0,
    y: (pts[36].y + pts[39].y) / 2.0
  };
  const rightEyeCenter = {
    x: (pts[42].x + pts[45].x) / 2.0,
    y: (pts[42].y + pts[45].y) / 2.0
  };
  const interOcularDist = Math.hypot(leftEyeCenter.x - rightEyeCenter.x, leftEyeCenter.y - rightEyeCenter.y);
  
  // Nose Tip (Point 30) - In 3D space, this projects out from the eye plane
  const noseTip = pts[30];
  const distNoseToLeft = Math.hypot(noseTip.x - leftEyeCenter.x, noseTip.y - leftEyeCenter.y);
  const distNoseToRight = Math.hypot(noseTip.x - rightEyeCenter.x, noseTip.y - rightEyeCenter.y);
  
  // Normalized 3D Horizontal Depth Parallax Ratio:
  // In a true 3D human head, micro-sway causes differential foreshortening between nose and eyes.
  // On a flat 2D phone screen or paper photo, this ratio remains rigidly constant even if tilted or moved.
  const depthParallaxRatio = (interOcularDist > 15) ? ((distNoseToLeft - distNoseToRight) / interOcularDist) : 0;
  
  // Vertical Nose-to-Eye Ratio
  const midEyeY = (leftEyeCenter.y + rightEyeCenter.y) / 2.0;
  const vertNoseRatio = (interOcularDist > 15) ? ((noseTip.y - midEyeY) / interOcularDist) : 0;
  
  const cx = box ? (box.x + box.width / 2.0) : 0;
  const cy = box ? (box.y + box.height / 2.0) : 0;
  
  if (!livenessTracker.facePresent) {
    livenessTracker.facePresent = true;
    livenessTracker.firstSeenTime = now;
    livenessTracker.framesCount = 0;
    livenessTracker.earHistory = [];
    livenessTracker.depthHistory = [];
    livenessTracker.vertHistory = [];
    livenessTracker.motionHistory = [];
    livenessTracker.baselineEAR = Math.max(0.24, avgEAR);
    livenessTracker.blinkDetected = false;
    livenessTracker.lastBlinkTime = 0;
    livenessTracker.blinkCount = 0;
    livenessTracker.isEyeClosed = false;
    livenessTracker.closedFrames = 0;
    livenessTracker.isPhotoBlocked = false;
    livenessTracker.isPhoneScreenDetected = false;
    livenessTracker.liveConfidence = 15;
    livenessTracker.depthScore = 0;
  }
  
  livenessTracker.framesCount++;
  livenessTracker.earHistory.push(avgEAR);
  if (livenessTracker.earHistory.length > 20) livenessTracker.earHistory.shift();
  
  livenessTracker.depthHistory.push(depthParallaxRatio);
  if (livenessTracker.depthHistory.length > 20) livenessTracker.depthHistory.shift();
  
  livenessTracker.vertHistory.push(vertNoseRatio);
  if (livenessTracker.vertHistory.length > 20) livenessTracker.vertHistory.shift();
  
  if (box) {
    livenessTracker.motionHistory.push({ cx, cy, t: now });
    if (livenessTracker.motionHistory.length > 20) livenessTracker.motionHistory.shift();
  }
  
  // Adaptive baseline open-eye EAR
  if (!livenessTracker.isEyeClosed && avgEAR > 0.21) {
    if (avgEAR > livenessTracker.baselineEAR) {
      livenessTracker.baselineEAR = avgEAR;
    } else {
      livenessTracker.baselineEAR = (livenessTracker.baselineEAR * 0.95) + (avgEAR * 0.05);
    }
  }
  livenessTracker.baselineEAR = Math.max(0.24, Math.min(0.42, livenessTracker.baselineEAR));
  
  // Natural Eye Blink Detection (Spontaneous ocular reflex)
  const closeThresh = Math.min(0.21, livenessTracker.baselineEAR * 0.78);
  const openThresh = Math.max(0.23, livenessTracker.baselineEAR * 0.86);
  
  if (avgEAR <= closeThresh) {
    livenessTracker.isEyeClosed = true;
    livenessTracker.closedFrames++;
  } else if (avgEAR >= openThresh && livenessTracker.isEyeClosed) {
    if (livenessTracker.closedFrames >= 1 && livenessTracker.closedFrames <= 14) {
      livenessTracker.blinkDetected = true;
      livenessTracker.lastBlinkTime = now;
      livenessTracker.liveConfidence = 100; // Instant 100% verification on blink!
      livenessTracker.isPhotoBlocked = false;
      livenessTracker.isPhoneScreenDetected = false;
    }
    livenessTracker.isEyeClosed = false;
    livenessTracker.closedFrames = 0;
  }
  
  // Maintain recent blink valid for 4 seconds
  if (livenessTracker.lastBlinkTime > 0 && (now - livenessTracker.lastBlinkTime < 4000)) {
    livenessTracker.blinkDetected = true;
    livenessTracker.liveConfidence = 100;
  }
  
  let depthStd = 0;
  let vertStd = 0;
  let earStd = 0;
  let boxMotionStd = 0;
  
  // 3D Depth & Motion Statistical Analysis (Evaluated over sliding window)
  if (livenessTracker.depthHistory.length >= 6) {
    const dHist = livenessTracker.depthHistory;
    const meanD = dHist.reduce((a, b) => a + b, 0) / dHist.length;
    depthStd = Math.sqrt(dHist.reduce((a, b) => a + Math.pow(b - meanD, 2), 0) / dHist.length);
    
    const vHist = livenessTracker.vertHistory;
    const meanV = vHist.reduce((a, b) => a + b, 0) / vHist.length;
    vertStd = Math.sqrt(vHist.reduce((a, b) => a + Math.pow(b - meanV, 2), 0) / vHist.length);
    
    const eHist = livenessTracker.earHistory;
    const meanE = eHist.reduce((a, b) => a + b, 0) / eHist.length;
    earStd = Math.sqrt(eHist.reduce((a, b) => a + Math.pow(b - meanE, 2), 0) / eHist.length);
    
    if (livenessTracker.motionHistory.length >= 6) {
      const mHist = livenessTracker.motionHistory;
      const mX = mHist.reduce((a, b) => a + b.cx, 0) / mHist.length;
      const mY = mHist.reduce((a, b) => a + b.cy, 0) / mHist.length;
      boxMotionStd = Math.sqrt(
        (mHist.reduce((a, b) => a + Math.pow(b.cx - mX, 2), 0) +
         mHist.reduce((a, b) => a + Math.pow(b.cy - mY, 2), 0)) / mHist.length
      );
    }
    
    // Live Human Signature:
    // Natural 3D head parallax variance (> 0.0022) or natural corneal micro-variation (> 0.0020)
    if (depthStd >= 0.0022 || (vertStd >= 0.0025 && earStd >= 0.0016)) {
      livenessTracker.depthScore = Math.min(100, livenessTracker.depthScore + 20);
      livenessTracker.liveConfidence = Math.min(100, livenessTracker.liveConfidence + 22);
      livenessTracker.isPhotoBlocked = false;
      livenessTracker.isPhoneScreenDetected = false;
    }
    
    // PHONE SCREEN & 2D PHOTO ATTACK DETECTION:
    // Signature 1: Hand-held phone screen wavering (box moves by hand tremor >= 0.45px, BUT 3D depth parallax is dead flat < 0.0014)
    // Signature 2: Static paper or stationary phone (depthStd < 0.0012, earStd < 0.0009, no blink)
    const timeInView = now - livenessTracker.firstSeenTime;
    const isFlatDepthPlane = (depthStd < 0.0015 && vertStd < 0.0016);
    const isEyesDeadStatic = (earStd < 0.0010 && !livenessTracker.blinkDetected);
    const isHandheldPlaneMoving = (boxMotionStd >= 0.45 && isFlatDepthPlane);
    
    if (livenessTracker.framesCount >= 10 && !livenessTracker.blinkDetected) {
      if ((isHandheldPlaneMoving && timeInView > 600) || (isFlatDepthPlane && isEyesDeadStatic && timeInView > 1000)) {
        livenessTracker.isPhotoBlocked = true;
        livenessTracker.isPhoneScreenDetected = true;
        livenessTracker.liveConfidence = 5;
        livenessTracker.depthScore = 0;
      }
    }
  }
  
  // Real human verified if:
  // (a) Blink detected, OR (b) liveConfidence >= 65% with confirmed 3D depth and not blocked
  const isVerified = !livenessTracker.isPhotoBlocked && 
                     !livenessTracker.isPhoneScreenDetected && 
                     ((livenessTracker.liveConfidence >= 65) || livenessTracker.blinkDetected);
  
  return {
    avgEAR,
    isVerified,
    blinkDetected: livenessTracker.blinkDetected,
    isPhotoBlocked: livenessTracker.isPhotoBlocked,
    isPhoneScreenDetected: livenessTracker.isPhoneScreenDetected,
    liveConfidence: livenessTracker.liveConfidence,
    depthScore: livenessTracker.depthScore,
    depthStd: (depthStd * 1000).toFixed(1)
  };
}

// -------------------------------------------------------------------
// TERMINAL CAMERA CONTROLS
// -------------------------------------------------------------------
async function toggleScannerCamera() {
  if (isScanning) {
    stopScannerCamera();
  } else {
    await startScannerCamera();
  }
}

async function startScannerCamera() {
  if (!modelsLoaded) {
    alert("Please wait for AI neural models to load.");
    return;
  }
  if (registeredStaffList.length === 0) {
    alert("No staff members have registered their faces yet! Please register at least one staff face in Tab 1 first.");
    switchLabTab('register');
    return;
  }
  
  const video = document.getElementById('scan-video');
  const placeholder = document.getElementById('scan-placeholder');
  const txtBtn = document.getElementById('txt-scan-cam-btn');
  const statusBadge = document.getElementById('scan-status-badge');
  const statusText = document.getElementById('scan-status-text');
  
  try {
    statusText.innerText = "Opening camera terminal...";
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
      audio: false
    });
    
    video.srcObject = stream;
    scanStream = stream;
    isScanning = true;
    placeholder.classList.add('hidden');
    txtBtn.innerText = "Stop Terminal";
    
    statusBadge.className = "bg-cyan-600/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
    statusText.innerText = "Terminal Active • Stand in front of camera";
    
    video.onloadedmetadata = () => {
      video.play();
      const canvas = document.getElementById('scan-canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      resetLivenessTracker();
      startScannerRecognitionLoop();
    };
  } catch (err) {
    console.error("Scanner camera error:", err);
    alert("Could not access camera for terminal: " + err.message);
    stopScannerCamera();
  }
}

function stopScannerCamera() {
  if (scanStream) {
    scanStream.getTracks().forEach(t => t.stop());
    scanStream = null;
  }
  if (scanAnimationId) {
    cancelAnimationFrame(scanAnimationId);
    scanAnimationId = null;
  }
  
  isScanning = false;
  resetLivenessTracker();
  
  const placeholder = document.getElementById('scan-placeholder');
  const txtBtn = document.getElementById('txt-scan-cam-btn');
  const statusBadge = document.getElementById('scan-status-badge');
  const statusText = document.getElementById('scan-status-text');
  const canvas = document.getElementById('scan-canvas');
  
  if (placeholder) placeholder.classList.remove('hidden');
  if (txtBtn) txtBtn.innerText = "Start All-Day Terminal";
  if (statusBadge) {
    statusBadge.className = "bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/10";
  }
  if (statusText) statusText.innerText = "Terminal Inactive";
  if (canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
}

// -------------------------------------------------------------------
// ALL-DAY RECOGNITION & AUTO-PUNCH LOOP
// -------------------------------------------------------------------
async function startScannerRecognitionLoop() {
  const video = document.getElementById('scan-video');
  const canvas = document.getElementById('scan-canvas');
  const statusBadge = document.getElementById('scan-status-badge');
  const statusText = document.getElementById('scan-status-text');
  const livenessPill = document.getElementById('scan-liveness-pill');
  const livenessText = document.getElementById('scan-liveness-text');
  
  if (!video || !canvas) return;
  const ctx = canvas.getContext('2d');
  
  const loop = async () => {
    if (!isScanning || !scanStream) return;
    const now = Date.now();
    
    try {
      const antiSpoofEnabled = document.getElementById('chk-scanner-antispoof')?.checked ?? true;
      const autoPunchEnabled = document.getElementById('chk-scanner-autopunch')?.checked ?? true;
      
      // High-speed real-time face detection with 68 landmarks & biometrics (30+ FPS)
      const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
        .withFaceLandmarks()
        .withFaceDescriptor();
      
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      if (detection) {
        noFaceFramesCount = 0;
        const box = detection.detection.box;
        
        // 1. Evaluate 3D Live Depth, Parallax & Blink Dynamics
        const liveness = evaluateLiveness(detection.landmarks, box);
        
        // 2. Match against registered staff faces
        if (faceMatcher) {
          const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
          
          if (bestMatch.label !== 'unknown') {
            const staffId = parseInt(bestMatch.label);
            const staff = registeredStaffMap[staffId];
            const distance = bestMatch.distance;
            const confidence = Math.max(0, Math.min(100, Math.round((1 - (distance / 0.7)) * 100)));
            
            // Check Anti-Spoof status:
            if (antiSpoofEnabled && !liveness.isVerified) {
              if (liveness.isPhotoBlocked || liveness.isPhoneScreenDetected) {
                // 2D Phone Screen or Static Photo Blocked
                drawCornerBox(ctx, box.x, box.y, box.width, box.height, '#f43f5e', 4);
                drawLabelBanner(ctx, box.x, box.y - 12, 'PHONE SCREEN / 2D PHOTO DETECTED • BLOCKED', '#f43f5e');
                
                livenessPill.className = "bg-rose-600/95 backdrop-blur-md text-white text-[11px] font-black px-3.5 py-1 rounded-full inline-flex items-center gap-1.5 border border-rose-300 shadow-xl animate-pulse";
                livenessText.innerText = "Anti-Spoof Alert: 2D Screen / Photo Blocked";
                
                statusBadge.className = "bg-rose-600/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
                statusText.innerText = "2D Phone Screen / Flat Photo Detected • Real Face Required";
                
                showSpoofCard();
                speakSpoofAlert(staff.first_name);
              } else {
                // Live 3D Depth & Organic Presence Verification (takes ~0.4 to 0.8s or an instant blink)
                drawCornerBox(ctx, box.x, box.y, box.width, box.height, '#f59e0b', 3);
                const bannerText = `${staff.first_name} (Analyzing 3D Depth ${liveness.liveConfidence}%...)`;
                drawLabelBanner(ctx, box.x, box.y - 12, bannerText, '#f59e0b');
                
                livenessPill.className = "bg-amber-600/90 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 border border-amber-300 shadow-lg";
                livenessText.innerText = `Analyzing 3D Depth (${liveness.liveConfidence}%)...`;
                
                statusBadge.className = "bg-amber-600/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
                statusText.innerText = `Recognized ${staff.first_name} • Verifying 3D facial depth...`;
                
                if (now > terminalDisplayLockedUntil) {
                  const pill = document.getElementById('match-status-pill');
                  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800";
                  pill.innerText = `Analyzing 3D Depth (${liveness.liveConfidence}%)`;
                  document.getElementById('res-spoof-card')?.classList.add('hidden');
                }
              }
            } else {
              // 3D Live Human Verified ✓ (or Anti-Spoof toggled off)
              drawCornerBox(ctx, box.x, box.y, box.width, box.height, '#10b981', 4);
              const bannerText = `${staff.first_name} ${staff.last_name} (${confidence}%) • 3D Live ✓`;
              drawLabelBanner(ctx, box.x, box.y - 12, bannerText, '#10b981');
              
              livenessPill.className = "bg-emerald-600/90 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 border border-emerald-300 shadow-lg";
              livenessText.innerText = "3D Live Human Verified ✓";
              
              statusBadge.className = "bg-emerald-600/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
              statusText.innerText = `Verified: ${staff.first_name} ${staff.last_name} (${confidence}%)`;
              
              if (autoPunchEnabled) {
                executeAutoPunch(staff, confidence);
              }
            }
          } else {
            // Unregistered Face
            drawCornerBox(ctx, box.x, box.y, box.width, box.height, '#64748b', 3);
            drawLabelBanner(ctx, box.x, box.y - 12, 'Unregistered Person', '#64748b');
            
            livenessPill.className = "bg-slate-800/90 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 border border-white/10";
            livenessText.innerText = "Face Unregistered";
            
            statusBadge.className = "bg-slate-700/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
            statusText.innerText = "Unregistered Person in Front of Camera";
            
            if (now > terminalDisplayLockedUntil) {
              showUnknownCard();
            }
          }
        }
      } else {
        // No face in current frame
        noFaceFramesCount++;
        if (noFaceFramesCount > 25) {
          resetLivenessTracker();
          livenessPill.className = "bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 border border-white/10 shadow-lg";
          livenessText.innerText = "Anti-Spoof Active";
        }
        
        statusBadge.className = "bg-cyan-600/90 backdrop-blur-md text-white text-xs font-bold px-4 py-1.5 rounded-full inline-flex items-center gap-2 shadow-lg border border-white/20";
        statusText.innerText = "Terminal Active • Ready for Next Staff Member";
        
        // Reset card display after lock duration expires and face has stepped away
        if (now > terminalDisplayLockedUntil && noFaceFramesCount > 20) {
          showEmptyState();
        }
      }
    } catch (e) {
      console.warn("Terminal scanner loop error:", e);
    }
    
    if (isScanning) {
      scanAnimationId = requestAnimationFrame(loop);
    }
  };
  
  scanAnimationId = requestAnimationFrame(loop);
}

// Reset today's face punch data for testing First-Time vs Already Marked punch
async function resetTodayAttendanceForTesting() {
  if (!confirm("Reset today's face attendance punch records so you can test fresh?")) return;
  
  try {
    const res = await fetch('api/staff_face.php?action=reset_test_attendance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'reset_test_attendance' })
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert("Success: " + result.message);
      autoPunchCooldown = {}; // clear cooldowns
      terminalDisplayLockedUntil = 0;
      showEmptyState();
      await fetchTodayFacePunches();
    } else {
      alert("Error: " + result.message);
    }
  } catch (err) {
    alert("Request error: " + err.message);
  }
}

// -------------------------------------------------------------------
// AUTO-PUNCH EXECUTION (WITH 15s COOLDOWN PER STAFF)
// -------------------------------------------------------------------
async function executeAutoPunch(staff, confidence) {
  const now = Date.now();
  
  // Cooldown check (15 seconds per staff member)
  if (autoPunchCooldown[staff.id] && now < autoPunchCooldown[staff.id]) {
    return;
  }
  
  if (isPunchRequestInProgress) return;
  isPunchRequestInProgress = true;
  
  // Set cooldown to prevent hammering server
  autoPunchCooldown[staff.id] = now + 15000;
  terminalDisplayLockedUntil = now + 4500; // Keep result card visible for 4.5 seconds
  currentDisplayedStaffId = staff.id;
  
  try {
    const res = await fetch('api/staff_face.php?action=auto_punch_attendance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'auto_punch_attendance',
        staff_id: staff.id,
        confidence: confidence / 100
      })
    });
    
    const result = await res.json();
    
    if (result.status === 'success') {
      // First Time Check-In Today!
      showSuccessPunchCard(result.data, confidence);
      playSuccessChime();
      speakAttendanceMarked(result.data.staff_name, result.data.status, result.data.check_in_time_formatted);
      await fetchTodayFacePunches();
    } else if (result.status === 'already_marked') {
      // Already Marked Today!
      showAlreadyMarkedCard(result.data);
      playAlreadyChime();
      speakAlreadyMarked(result.data.staff_name, result.data.check_in_time_formatted);
    } else {
      console.warn("Punch response:", result.message);
    }
  } catch (err) {
    console.error("Auto-punch request failed:", err);
  } finally {
    isPunchRequestInProgress = false;
  }
}

// -------------------------------------------------------------------
// RESULT CARDS DISPLAY HANDLERS
// -------------------------------------------------------------------
function showSuccessPunchCard(data, confidence) {
  document.getElementById('res-empty-state').classList.add('hidden');
  document.getElementById('res-already-card').classList.add('hidden');
  document.getElementById('res-spoof-card').classList.add('hidden');
  document.getElementById('res-unknown-card').classList.add('hidden');
  
  const card = document.getElementById('res-success-card');
  card.classList.remove('hidden');
  
  document.getElementById('succ-avatar').src = data.avatar;
  document.getElementById('succ-name').innerText = data.staff_name;
  document.getElementById('succ-code').innerText = data.staff_code || `Staff ID: #${data.staff_id}`;
  document.getElementById('succ-role').innerText = `${data.role} • ${data.department || 'General'}`;
  document.getElementById('succ-time').innerText = data.check_in_time_formatted || data.check_in_time;
  
  const statusEl = document.getElementById('succ-status');
  statusEl.innerText = data.status;
  if (data.status === 'Late') {
    statusEl.className = "px-2.5 py-1 rounded-lg text-xs font-black bg-amber-100 text-amber-800";
  } else if (data.status === 'Half Day') {
    statusEl.className = "px-2.5 py-1 rounded-lg text-xs font-black bg-orange-100 text-orange-800";
  } else {
    statusEl.className = "px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800";
  }
  
  document.getElementById('succ-confidence').innerText = `${confidence}% Match`;
  
  const pill = document.getElementById('match-status-pill');
  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800";
  pill.innerText = "Attendance Marked ✓";
}

function showAlreadyMarkedCard(data) {
  document.getElementById('res-empty-state').classList.add('hidden');
  document.getElementById('res-success-card').classList.add('hidden');
  document.getElementById('res-spoof-card').classList.add('hidden');
  document.getElementById('res-unknown-card').classList.add('hidden');
  
  const card = document.getElementById('res-already-card');
  card.classList.remove('hidden');
  
  document.getElementById('alr-avatar').src = data.avatar;
  document.getElementById('alr-name').innerText = data.staff_name;
  document.getElementById('alr-code').innerText = data.staff_code || `Staff ID: #${data.staff_id}`;
  document.getElementById('alr-role').innerText = `${data.role} • ${data.department || 'General'}`;
  document.getElementById('alr-time').innerText = data.check_in_time_formatted || data.check_in_time;
  document.getElementById('alr-status').innerText = data.status;
  
  const pill = document.getElementById('match-status-pill');
  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800";
  pill.innerText = "Already Marked Today";
}

function showSpoofCard() {
  document.getElementById('res-empty-state').classList.add('hidden');
  document.getElementById('res-success-card').classList.add('hidden');
  document.getElementById('res-already-card').classList.add('hidden');
  document.getElementById('res-unknown-card').classList.add('hidden');
  
  const card = document.getElementById('res-spoof-card');
  card.classList.remove('hidden');
  
  const pill = document.getElementById('match-status-pill');
  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800";
  pill.innerText = "Spoof Blocked: 2D Screen / Photo";
}

function showUnknownCard() {
  document.getElementById('res-empty-state').classList.add('hidden');
  document.getElementById('res-success-card').classList.add('hidden');
  document.getElementById('res-already-card').classList.add('hidden');
  document.getElementById('res-spoof-card').classList.add('hidden');
  
  const card = document.getElementById('res-unknown-card');
  card.classList.remove('hidden');
  
  const pill = document.getElementById('match-status-pill');
  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-200 text-slate-700";
  pill.innerText = "Unregistered Face";
}

function showEmptyState() {
  document.getElementById('res-success-card').classList.add('hidden');
  document.getElementById('res-already-card').classList.add('hidden');
  document.getElementById('res-spoof-card').classList.add('hidden');
  document.getElementById('res-unknown-card').classList.add('hidden');
  
  document.getElementById('res-empty-state').classList.remove('hidden');
  
  const pill = document.getElementById('match-status-pill');
  pill.className = "px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600";
  pill.innerText = "Awaiting Staff Face";
}

// -------------------------------------------------------------------
// VOICE SYNTHESIS (TTS)
// -------------------------------------------------------------------
function speakAttendanceMarked(fullName, status, checkInTime) {
  const chkVoice = document.getElementById('chk-scanner-voice');
  if (!chkVoice || !chkVoice.checked) return;
  if (!('speechSynthesis' in window)) return;
  
  window.speechSynthesis.cancel();
  const phrase = `Attendance marked for ${fullName}. Status: ${status}. Check-in time: ${checkInTime}.`;
  const utterance = new SpeechSynthesisUtterance(phrase);
  utterance.rate = 1.0;
  utterance.pitch = 1.05;
  utterance.lang = 'en-US';
  window.speechSynthesis.speak(utterance);
}

function speakAlreadyMarked(fullName, checkInTime) {
  const chkVoice = document.getElementById('chk-scanner-voice');
  if (!chkVoice || !chkVoice.checked) return;
  if (!('speechSynthesis' in window)) return;
  
  window.speechSynthesis.cancel();
  const phrase = `${fullName}, your attendance was already marked today at ${checkInTime}. Have a great day!`;
  const utterance = new SpeechSynthesisUtterance(phrase);
  utterance.rate = 1.0;
  utterance.pitch = 1.0;
  utterance.lang = 'en-US';
  window.speechSynthesis.speak(utterance);
}

let lastSpoofSpeechTime = 0;
function speakSpoofAlert(staffName = '') {
  const now = Date.now();
  if (now - lastSpoofSpeechTime < 8000) return;
  lastSpoofSpeechTime = now;
  
  const chkVoice = document.getElementById('chk-scanner-voice');
  if (!chkVoice || !chkVoice.checked) return;
  if (!('speechSynthesis' in window)) return;
  
  playWarningBeep();
  window.speechSynthesis.cancel();
  const phrase = staffName ? 
    `2D phone screen or photo detected for ${staffName}. Attendance blocked. Real living face with 3D depth required.` : 
    "2D phone screen or photo detected. Attendance blocked. Real living face with 3D depth required.";
  const utterance = new SpeechSynthesisUtterance(phrase);
  utterance.rate = 1.0;
  utterance.pitch = 1.0;
  utterance.lang = 'en-US';
  window.speechSynthesis.speak(utterance);
}

// -------------------------------------------------------------------
// WEB AUDIO SOUND EFFECTS
// -------------------------------------------------------------------
function playSuccessChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = 'sine';
    const now = ctx.currentTime;
    osc.frequency.setValueAtTime(587.33, now); // D5
    osc.frequency.setValueAtTime(880.00, now + 0.09); // A5
    gain.gain.setValueAtTime(0.15, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
    osc.start(now);
    osc.stop(now + 0.35);
  } catch(e) {}
}

function playAlreadyChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = 'triangle';
    const now = ctx.currentTime;
    osc.frequency.setValueAtTime(740.00, now); // F#5
    osc.frequency.setValueAtTime(740.00, now + 0.12);
    gain.gain.setValueAtTime(0.12, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
    osc.start(now);
    osc.stop(now + 0.3);
  } catch(e) {}
}

function playWarningBeep() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = 'sawtooth';
    const now = ctx.currentTime;
    osc.frequency.setValueAtTime(220, now);
    osc.frequency.setValueAtTime(180, now + 0.15);
    gain.gain.setValueAtTime(0.2, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
    osc.start(now);
    osc.stop(now + 0.4);
  } catch(e) {}
}

// ===================================================================
// TAB 3: STAFF ROSTER DIRECTORY
// ===================================================================
function renderRosterTable() {
  const tbody = document.getElementById('roster-table-body');
  if (!allStaffList || allStaffList.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-400">No staff records found.</td></tr>';
    return;
  }
  
  const search = (document.getElementById('roster-search-input')?.value || '').toLowerCase().trim();
  const filter = document.getElementById('roster-status-filter')?.value || 'All';
  
  const filtered = allStaffList.filter(s => {
    const fullName = `${s.first_name} ${s.last_name}`.toLowerCase();
    const code = (s.staff_code || '').toLowerCase();
    const matchSearch = !search || fullName.includes(search) || code.includes(search);
    
    let matchStatus = true;
    if (filter === 'Registered') matchStatus = s.has_face;
    if (filter === 'Pending') matchStatus = !s.has_face;
    
    return matchSearch && matchStatus;
  });
  
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-400">No staff matching criteria.</td></tr>';
    return;
  }
  
  tbody.innerHTML = filtered.map(s => {
    const fullName = `${s.first_name} ${s.last_name}`;
    const faceBadge = s.has_face 
      ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-circle-check"></i> Enrolled (128-D)</span>'
      : '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fa-solid fa-circle-exclamation"></i> Not Enrolled</span>';
      
    return `
      <tr class="hover:bg-slate-50/80 transition">
        <td class="py-3 px-4">
          <div class="flex items-center gap-3">
            <img src="${s.avatar}" alt="Avatar" class="w-9 h-9 rounded-xl object-cover border border-slate-200">
            <div>
              <div class="font-bold text-slate-900">${fullName}</div>
              <div class="text-[10px] text-slate-400 font-semibold">${s.status || 'Active'}</div>
            </div>
          </div>
        </td>
        <td class="py-3 px-4 font-bold text-slate-700">${s.staff_code || 'ID: #' + s.id}</td>
        <td class="py-3 px-4">
          <div class="font-semibold text-slate-800">${s.role}</div>
          <div class="text-[10px] text-slate-400">${s.department || 'General'}</div>
        </td>
        <td class="py-3 px-4 text-slate-600">${s.shift || 'General'}</td>
        <td class="py-3 px-4">${faceBadge}</td>
        <td class="py-3 px-4 text-right">
          <div class="inline-flex items-center gap-1.5">
            <button onclick="quickEnrollFromTable(${s.id})" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition" title="Enroll Face">
              <i class="fa-solid fa-camera mr-1"></i> ${s.has_face ? 'Re-Enroll' : 'Enroll Face'}
            </button>
            ${s.has_face ? `
              <button onclick="quickTestFromTable()" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition" title="Test Scanner">
                <i class="fa-solid fa-play"></i> Test
              </button>
              <button onclick="quickDeleteFromTable(${s.id}, '${fullName.replace(/'/g, "\\'")}')" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 font-bold text-xs transition" title="Delete Face Biometrics">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function filterRosterTable() {
  renderRosterTable();
}

function quickEnrollFromTable(staffId) {
  switchLabTab('register');
  document.getElementById('reg-staff-select').value = staffId;
  onStaffSelected(staffId);
  startRegisterCamera();
}

function quickTestFromTable() {
  switchLabTab('scanner');
  startScannerCamera();
}

async function quickDeleteFromTable(staffId, staffName) {
  if (!confirm(`Are you sure you want to remove registered face biometrics for ${staffName}?`)) return;
  try {
    const res = await fetch('api/staff_face.php?action=delete_face', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_face', staff_id: staffId })
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert("Face data removed.");
      await refreshStaffData();
      await refreshRegisteredFaces();
    } else {
      alert("Error: " + result.message);
    }
  } catch (e) {
    alert("Error: " + e.message);
  }
}

// ===================================================================
// CANVAS DRAWING HELPERS
// ===================================================================
function drawCornerBox(ctx, x, y, width, height, color, lineWidth = 3) {
  const lineLength = Math.min(width, height) * 0.25;
  ctx.save();
  ctx.strokeStyle = color;
  ctx.lineWidth = lineWidth;
  ctx.shadowColor = color;
  ctx.shadowBlur = 10;
  
  // Top-left
  ctx.beginPath();
  ctx.moveTo(x, y + lineLength);
  ctx.lineTo(x, y);
  ctx.lineTo(x + lineLength, y);
  ctx.stroke();
  
  // Top-right
  ctx.beginPath();
  ctx.moveTo(x + width - lineLength, y);
  ctx.lineTo(x + width, y);
  ctx.lineTo(x + width, y + lineLength);
  ctx.stroke();
  
  // Bottom-left
  ctx.beginPath();
  ctx.moveTo(x, y + height - lineLength);
  ctx.lineTo(x, y + height);
  ctx.lineTo(x + lineLength, y + height);
  ctx.stroke();
  
  // Bottom-right
  ctx.beginPath();
  ctx.moveTo(x + width - lineLength, y + height);
  ctx.lineTo(x + width, y + height);
  ctx.lineTo(x + width, y + height - lineLength);
  ctx.stroke();
  
  ctx.restore();
}

function drawLabelBanner(ctx, x, y, text, bgColor) {
  ctx.save();
  ctx.font = 'bold 12px sans-serif';
  const textWidth = ctx.measureText(text).width;
  const padding = 8;
  const h = 22;
  
  ctx.fillStyle = bgColor;
  ctx.shadowColor = 'rgba(0,0,0,0.4)';
  ctx.shadowBlur = 6;
  
  // Rounded rectangle for label
  const r = 6;
  const bx = x;
  const by = Math.max(10, y - h);
  const bw = textWidth + (padding * 2);
  
  ctx.beginPath();
  ctx.moveTo(bx + r, by);
  ctx.arcTo(bx + bw, by, bx + bw, by + h, r);
  ctx.arcTo(bx + bw, by + h, bx, by + h, r);
  ctx.arcTo(bx, by + h, bx, by, r);
  ctx.arcTo(bx, by, bx + bw, by, r);
  ctx.closePath();
  ctx.fill();
  
  ctx.fillStyle = '#ffffff';
  ctx.fillText(text, bx + padding, by + 15);
  ctx.restore();
}

// Cleanup cameras on page unload
window.addEventListener('beforeunload', () => {
  stopRegisterCamera();
  stopScannerCamera();
});
</script>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'auth.php';
require_once 'db.php';

$hospital_id = (int)($_SESSION['hospital_id'] ?? 1);
$hospital_name = $_SESSION['hospital_name'] ?? 'CarePulse Health';

// Quick stats for top pill
$totalStaffQuery = $conn->query("SELECT COUNT(*) AS total FROM staff WHERE hospital_id = $hospital_id AND status = 'Active'");
$totalStaff = $totalStaffQuery ? (int)$totalStaffQuery->fetch_assoc()['total'] : 0;

$registeredFacesQuery = $conn->query("SELECT COUNT(*) AS reg FROM staff WHERE hospital_id = $hospital_id AND face_descriptor IS NOT NULL AND TRIM(face_descriptor) != '' AND face_descriptor != 'null'");
$registeredFaces = $registeredFacesQuery ? (int)$registeredFacesQuery->fetch_assoc()['reg'] : 0;

$today = date('Y-m-d');
$todayPunchesQuery = $conn->query("SELECT COUNT(*) AS punches FROM staff_attendance WHERE hospital_id = $hospital_id AND date = '$today' AND (check_in_time IS NOT NULL AND status != 'Absent')");
$todayPunches = $todayPunchesQuery ? (int)$todayPunchesQuery->fetch_assoc()['punches'] : 0;
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Smart AI Face Attendance Kiosk | <?= htmlspecialchars($hospital_name) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @keyframes scanBeam {
      0% { top: 0%; opacity: 0; }
      15% { opacity: 0.85; }
      85% { opacity: 0.85; }
      100% { top: 100%; opacity: 0; }
    }
    .animate-scan {
      position: absolute;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, rgba(168,85,247,0) 0%, rgba(168,85,247,0.8) 25%, rgba(59,130,246,1) 50%, rgba(168,85,247,0.8) 75%, rgba(168,85,247,0) 100%);
      box-shadow: 0 0 16px 3px rgba(147, 51, 234, 0.6);
      animation: scanBeam 2.4s ease-in-out infinite;
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans select-none overflow-x-hidden antialiased">

  <!-- ================= TOP KIOSK HEADER ================= -->
  <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md px-4 sm:px-6 py-3 shrink-0 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 flex-wrap">
      
      <!-- Brand & Title -->
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-purple-500/30">
          <i class="fa-solid fa-face-viewfinder"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-1.5">
              <span><?= htmlspecialchars($hospital_name) ?></span>
              <span class="text-slate-400 font-normal">|</span>
              <span class="text-purple-400 font-extrabold text-sm sm:text-base">Smart AI Attendance</span>
            </h1>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 animate-pulse">
              Live Terminal
            </span>
          </div>
          <p class="text-[11px] text-slate-400">Position face in the frame to record touchless check-in attendance.</p>
        </div>
      </div>

      <!-- Live Clock & Status Strip -->
      <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
        
        <!-- Live Clock Pill -->
        <div class="bg-slate-800/80 border border-slate-700/60 rounded-xl px-3.5 py-1.5 flex items-center gap-2 text-xs">
          <i class="fa-regular fa-clock text-purple-400"></i>
          <span id="live-time" class="font-mono font-bold text-white text-sm">--:--:-- --</span>
          <span class="text-slate-500 text-[10px]">|</span>
          <span id="live-date" class="text-slate-300 text-[11px] font-medium">---</span>
        </div>

        <!-- Voice Announcement Toggle (DEFAULT: DISABLED) -->
        <button id="btn-voice-toggle" onclick="toggleVoiceAudio()" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold transition flex items-center gap-2 text-slate-300" title="Voice greeting is muted by default. Click to enable.">
          <i id="voice-icon" class="fa-solid fa-volume-xmark text-rose-400 text-sm"></i>
          <span id="voice-status-text">Voice: Muted (Default)</span>
        </button>

        <!-- Camera Switcher -->
        <button onclick="switchCamera()" id="btn-camera-switch" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl p-2 sm:px-3 sm:py-1.5 text-xs font-bold transition flex items-center gap-1.5 text-slate-300" title="Switch Video Camera">
          <i class="fa-solid fa-camera-rotate"></i>
          <span class="hidden sm:inline">Flip</span>
        </button>

        <!-- Fullscreen Kiosk Mode -->
        <button onclick="toggleFullscreen()" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl p-2 sm:px-3 sm:py-1.5 text-xs font-bold transition flex items-center gap-1.5 text-slate-300" title="Toggle Fullscreen Kiosk View">
          <i id="fullscreen-icon" class="fa-solid fa-expand"></i>
          <span class="hidden sm:inline">Kiosk</span>
        </button>

        <!-- Back to Staff Roster -->
        <a href="staff.php" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl px-3.5 py-1.5 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Exit Kiosk</span>
        </a>

      </div>
    </div>
  </header>

  <!-- ================= MAIN SCANNER WORKSPACE ================= -->
  <main class="flex-1 max-w-7xl w-full mx-auto p-3 sm:p-5 flex flex-col lg:flex-row gap-5 items-stretch">
    
    <!-- Left Stage: Main Camera Viewport -->
    <div class="flex-1 flex flex-col bg-slate-900 rounded-3xl border border-slate-800 shadow-2xl overflow-hidden relative min-h-[460px] sm:min-h-[540px]">
      
      <!-- Top Viewport Control & AI Status Bar -->
      <div class="p-3 sm:p-4 border-b border-slate-800/80 bg-slate-900/60 flex items-center justify-between text-xs gap-2 flex-wrap">
        <div class="flex items-center gap-2">
          <!-- Camera Status -->
          <span id="cam-status-pill" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 font-bold border border-slate-700/80 text-[11px]">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span id="cam-status-text">Starting Camera...</span>
          </span>

          <!-- AI Model Engine Status -->
          <span id="ai-status-pill" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 font-bold border border-slate-700/80 text-[11px]">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span id="ai-status-text">Loading Face Models...</span>
          </span>
        </div>

        <div class="flex items-center gap-2 text-slate-400 text-xs font-semibold">
          <span class="inline-flex items-center gap-1">
            <i class="fa-solid fa-users text-purple-400"></i>
            <span id="stat-registered-count" class="font-bold text-white"><?= $registeredFaces ?></span> Enrolled
          </span>
          <span>&bull;</span>
          <span class="inline-flex items-center gap-1">
            <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
            <span id="stat-today-punches" class="font-bold text-white"><?= $todayPunches ?></span> Punched Today
          </span>
        </div>
      </div>

      <!-- Video Feed Container -->
      <div class="flex-1 relative bg-black flex items-center justify-center overflow-hidden">
        
        <!-- Camera Video Feed (Direct Auto-Play) -->
        <video id="video" autoplay playsinline muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
        
        <!-- Live Face-API Bounding Box Canvas -->
        <canvas id="canvas-overlay" class="absolute inset-0 w-full h-full pointer-events-none transform scale-x-[-1]"></canvas>

        <!-- Animated Scanner Beam -->
        <div id="scanner-laser" class="animate-scan pointer-events-none"></div>

        <!-- Futuristic Scanner Target Reticle / Corner Brackets -->
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
          <div id="reticle-box" class="w-64 h-80 sm:w-72 sm:h-92 border-2 border-dashed border-white/30 rounded-3xl relative transition-all duration-300 flex items-center justify-center">
            <!-- Top Left Bracket -->
            <div class="absolute -top-1.5 -left-1.5 w-7 h-7 border-t-4 border-l-4 border-purple-500 rounded-tl-2xl"></div>
            <!-- Top Right Bracket -->
            <div class="absolute -top-1.5 -right-1.5 w-7 h-7 border-t-4 border-r-4 border-purple-500 rounded-tr-2xl"></div>
            <!-- Bottom Left Bracket -->
            <div class="absolute -bottom-1.5 -left-1.5 w-7 h-7 border-b-4 border-l-4 border-purple-500 rounded-bl-2xl"></div>
            <!-- Bottom Right Bracket -->
            <div class="absolute -bottom-1.5 -right-1.5 w-7 h-7 border-b-4 border-r-4 border-purple-500 rounded-br-2xl"></div>

            <!-- Central subtle target cross -->
            <div class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center">
              <div class="w-1.5 h-1.5 rounded-full bg-purple-400"></div>
            </div>
          </div>
        </div>

        <!-- Floating Live Guidance HUD -->
        <div class="absolute bottom-6 inset-x-4 text-center pointer-events-none flex flex-col items-center">
          <div id="hud-status-badge" class="px-4 py-2 rounded-2xl bg-slate-900/80 backdrop-blur-md text-white text-xs sm:text-sm font-bold border border-white/10 shadow-2xl flex items-center gap-2.5 transition-all">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-400 animate-pulse"></span>
            <span id="hud-status-text">Position your face inside the target frame...</span>
          </div>
        </div>

        <!-- Camera Off / Permission Required Fallback Notice -->
        <div id="cam-error-box" class="hidden absolute inset-0 bg-slate-950 flex flex-col items-center justify-center p-6 text-center z-20">
          <div class="w-16 h-16 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl font-bold mb-4 border border-rose-500/30">
            <i class="fa-solid fa-camera-slash"></i>
          </div>
          <h3 class="text-lg font-bold text-white mb-1">Camera Access Required</h3>
          <p id="cam-error-msg" class="text-xs text-slate-400 max-w-sm mb-5">Please allow webcam camera permissions in your browser to enable live facial recognition attendance.</p>
          <button onclick="initCamera()" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-500/30 transition flex items-center gap-2">
            <i class="fa-solid fa-rotate-right"></i>
            <span>Retry Camera</span>
          </button>
        </div>

        <!-- ================= ATTENDANCE SUCCESS: THANK YOU OVERLAY ================= -->
        <div id="thankyou-overlay" class="hidden absolute inset-0 bg-slate-950/92 backdrop-blur-xl flex flex-col items-center justify-center p-4 sm:p-6 z-30 transition-all duration-300">
          
          <div class="max-w-md w-full bg-gradient-to-b from-slate-900 to-slate-950 border border-emerald-500/40 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden">
            
            <!-- Glowing background aura -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Big Checkmark Celebration Icon -->
            <div class="relative w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center text-4xl shadow-xl shadow-emerald-500/30 mb-4 animate-bounce">
              <i class="fa-solid fa-check"></i>
            </div>

            <!-- Large THANK YOU Banner -->
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white uppercase mb-1">
              Thank You!
            </h2>
            <p id="ty-subtitle" class="text-xs sm:text-sm font-bold text-emerald-400 tracking-wide uppercase mb-5">
              Attendance Marked Successfully
            </p>

            <!-- Staff Photo & Profile Card -->
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 mb-5 flex items-center gap-4 text-left shadow-inner">
              <img id="ty-staff-avatar" src="" alt="Staff Face Photo" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-emerald-400 shadow-md shrink-0 bg-slate-700">
              <div class="min-w-0 flex-1">
                <h3 id="ty-staff-name" class="text-base sm:text-lg font-black text-white truncate">Staff Name</h3>
                <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                  <span id="ty-staff-code" class="font-mono text-[11px] font-bold bg-slate-900 text-slate-300 px-2 py-0.5 rounded-lg border border-slate-700">STF-000</span>
                  <span id="ty-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Present</span>
                  <span id="ty-updated-badge" class="hidden px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 flex items-center gap-1"><i class="fa-solid fa-arrow-right-arrow-left text-[9px]"></i> Status Changed</span>
                </div>
                <div id="ty-staff-role" class="text-xs text-slate-300 truncate mt-1">Role • Department</div>
                <div id="ty-staff-shift" class="text-[11px] text-slate-400">Shift: General</div>
              </div>
            </div>

            <!-- Time Stamp Grid -->
            <div class="grid grid-cols-2 gap-3 text-xs mb-3">
              <div class="bg-slate-900/90 p-2.5 rounded-xl border border-slate-800 text-left">
                <span class="text-slate-500 text-[10px] uppercase font-bold block">Punch Time</span>
                <span id="ty-punch-time" class="font-mono font-black text-white text-sm">--:--:--</span>
              </div>
              <div class="bg-slate-900/90 p-2.5 rounded-xl border border-slate-800 text-left">
                <span class="text-slate-500 text-[10px] uppercase font-bold block">Verification Method</span>
                <span class="text-purple-400 font-bold text-xs flex items-center gap-1">
                  <i class="fa-solid fa-shield-check"></i> Live AI Face
                </span>
              </div>
            </div>

            <!-- Status Update Note Box (Displayed when previously marked Absent) -->
            <div id="ty-note-box" class="hidden mb-4 p-3 bg-purple-950/40 border border-purple-800/40 rounded-2xl text-left text-xs text-purple-200 shadow-inner">
              <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center shrink-0 mt-0.5">
                  <i class="fa-solid fa-rotate text-xs"></i>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="font-extrabold text-white text-xs">Status Changed: Absent &rarr; Present</div>
                  <p id="ty-note-text" class="text-[11px] text-purple-300/90 mt-0.5 leading-snug"></p>
                </div>
              </div>
            </div>

            <!-- Friendly Message -->
            <p class="text-xs text-slate-400 mb-4 font-medium italic">
              "Have a safe, successful and wonderful shift!"
            </p>

            <!-- Reset Countdown Progress Bar -->
            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden mb-2">
              <div id="ty-progress-bar" class="bg-emerald-500 h-full w-full transition-all duration-[3500ms] ease-linear"></div>
            </div>
            <div class="text-[11px] text-slate-500 font-semibold flex items-center justify-between">
              <span>Ready for next staff member...</span>
              <span id="ty-countdown-text">3s</span>
            </div>

          </div>
        </div>

        <!-- ================= ATTENDANCE NOTICE: ALREADY MARKED TODAY ================= -->
        <div id="alreadymarked-overlay" class="hidden absolute inset-0 bg-slate-950/92 backdrop-blur-xl flex flex-col items-center justify-center p-4 sm:p-6 z-30 transition-all duration-300">
          <div class="max-w-md w-full bg-gradient-to-b from-slate-900 to-slate-950 border border-blue-500/40 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden">
            
            <div class="w-16 h-16 mx-auto rounded-3xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-3xl shadow-lg border border-blue-500/30 mb-4">
              <i class="fa-solid fa-clock-rotate-left"></i>
            </div>

            <h2 class="text-2xl sm:text-3xl font-black text-white mb-1">
              Welcome Back!
            </h2>
            <p class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-5">
              Attendance Already Recorded Today
            </p>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 mb-4 flex items-center gap-4 text-left shadow-inner">
              <img id="am-staff-avatar" src="" alt="Staff Photo" class="w-16 h-16 rounded-2xl object-cover border-2 border-blue-400 shadow-md shrink-0 bg-slate-700">
              <div class="min-w-0 flex-1">
                <h3 id="am-staff-name" class="text-base font-black text-white truncate">Staff Name</h3>
                <div class="text-xs text-slate-300 mt-0.5" id="am-staff-code">STF-000</div>
                <div class="text-[11px] text-slate-400" id="am-staff-role">Role</div>
              </div>
            </div>

            <div class="p-3 bg-blue-950/40 rounded-2xl border border-blue-800/40 text-xs text-blue-200 mb-5">
              <span id="am-message" class="font-medium">Your attendance was already recorded today at 08:30 AM.</span>
            </div>

            <button onclick="dismissAlreadyMarkedOverlay()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
              Resume Scanner
            </button>
          </div>
        </div>

      </div>

      <!-- Bottom Guidance Instructions -->
      <div class="p-3 sm:p-4 border-t border-slate-800/80 bg-slate-900/60 flex items-center justify-between text-xs text-slate-400 flex-wrap gap-2">
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i> Stand within 2–3 feet</span>
          <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i> Keep eyes open</span>
          <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i> Ensure adequate light</span>
        </div>
        <div class="text-slate-500 text-[11px]">
          Hospital Facial Biometric Terminal v2.5
        </div>
      </div>

    </div>

    <!-- Right Sidebar: Today's Recent Face Punches -->
    <div class="w-full lg:w-96 flex flex-col bg-slate-900 rounded-3xl border border-slate-800 shadow-2xl p-4 sm:p-5 shrink-0">
      
      <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
          <h3 class="font-extrabold text-sm sm:text-base text-white flex items-center gap-2">
            <i class="fa-solid fa-clipboard-user text-purple-400"></i>
            <span>Today's Live Punches</span>
          </h3>
          <p class="text-[11px] text-slate-400">Real-time attendance log</p>
        </div>
        <button onclick="loadTodayPunches()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs transition" title="Refresh Live Punches">
          <i class="fa-solid fa-arrows-rotate"></i>
        </button>
      </div>

      <!-- Feed Container -->
      <div id="recent-punches-list" class="flex-1 overflow-y-auto space-y-2.5 mt-3 pr-1 max-h-[460px] lg:max-h-[580px] no-scrollbar">
        <!-- Rendered via JS -->
        <div class="text-center py-12 text-slate-500 text-xs font-medium">
          <i class="fa-solid fa-spinner fa-spin text-xl mb-2 text-slate-600"></i>
          <p>Loading today's punch records...</p>
        </div>
      </div>

      <!-- Quick Action / Admin Tool Link -->
      <div class="pt-3 border-t border-slate-800 mt-auto flex items-center justify-between text-xs">
        <a href="staff.php" class="text-purple-400 hover:text-purple-300 font-bold transition flex items-center gap-1">
          <i class="fa-solid fa-table-list"></i>
          <span>Full Attendance Roster</span>
        </a>
        <button onclick="resetTestAttendance()" class="text-slate-500 hover:text-rose-400 transition text-[11px]" title="Reset today's test punch records">
          Reset Today (Test)
        </button>
      </div>

    </div>

  </main>

  <!-- ================= LOAD FACE-API SCRIPT ================= -->
  <script src="js/face-api.min.js"></script>

  <!-- ================= KIOSK ATTENDANCE LOGIC ================= -->
  <script>
    // System State
    let videoStream = null;
    let detectionAnimationId = null;
    let registeredStaffList = [];
    let registeredStaffMap = {};
    let faceMatcher = null;
    let modelsLoaded = false;
    let isProcessingMatch = false;
    let currentCameraFacingMode = 'user'; // 'user' (front) or 'environment' (back)
    
    // Voice Audio: DISABLED BY DEFAULT AS REQUESTED
    let voiceEnabled = false;

    // Cooldown tracker to prevent multiple rapid triggers for the same person
    let lastMarkedStaffId = null;
    let lastMarkedTimestamp = 0;
    const RE_PUNCH_COOLDOWN_MS = 10000; // 10s cooldown for immediate repeat on same device

    // Initialize as soon as DOM loads
    document.addEventListener("DOMContentLoaded", async () => {
      startLiveClock();
      loadTodayPunches();
      
      // Auto-start camera and load models simultaneously
      initCamera();
      initFaceApiModels();
    });

    // ------------------------------------------------------------------
    // Live Clock & Date
    // ------------------------------------------------------------------
    function startLiveClock() {
      const update = () => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const dateStr = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        
        const timeEl = document.getElementById('live-time');
        const dateEl = document.getElementById('live-date');
        if (timeEl) timeEl.innerText = timeStr;
        if (dateEl) dateEl.innerText = dateStr;
      };
      update();
      setInterval(update, 1000);
    }

    // ------------------------------------------------------------------
    // Voice Announcements Toggle (Default: OFF)
    // ------------------------------------------------------------------
    function toggleVoiceAudio() {
      voiceEnabled = !voiceEnabled;
      const btn = document.getElementById('btn-voice-toggle');
      const icon = document.getElementById('voice-icon');
      const text = document.getElementById('voice-status-text');

      if (voiceEnabled) {
        btn.className = "bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-500/50 rounded-xl px-3 py-1.5 text-xs font-bold transition flex items-center gap-2 text-emerald-300";
        icon.className = "fa-solid fa-volume-high text-emerald-400 text-sm";
        text.innerText = "Voice: Active (ON)";
        speakVoice("Voice announcements enabled.");
      } else {
        btn.className = "bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold transition flex items-center gap-2 text-slate-300";
        icon.className = "fa-solid fa-volume-xmark text-rose-400 text-sm";
        text.innerText = "Voice: Muted (Default)";
      }
    }

    function speakVoice(text) {
      if (!voiceEnabled || !('speechSynthesis' in window)) return;
      try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        window.speechSynthesis.speak(utterance);
      } catch (e) {
        console.warn("Speech error:", e);
      }
    }

    // ------------------------------------------------------------------
    // Direct Camera Access & Loop
    // ------------------------------------------------------------------
    async function initCamera() {
      const video = document.getElementById('video');
      const camPill = document.getElementById('cam-status-pill');
      const camText = document.getElementById('cam-status-text');
      const errBox = document.getElementById('cam-error-box');

      errBox.classList.add('hidden');
      camText.innerText = "Starting Camera...";
      camPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 font-bold border border-slate-700/80 text-[11px]";

      if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
        videoStream = null;
      }

      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: currentCameraFacingMode
          },
          audio: false
        });

        videoStream = stream;
        video.srcObject = stream;

        video.onloadedmetadata = () => {
          video.play();
          camPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]";
          camText.innerText = "Camera Live";
          startContinuousDetectionLoop();
        };

      } catch (err) {
        console.error("Camera access failed:", err);
        errBox.classList.remove('hidden');
        document.getElementById('cam-error-msg').innerText = err.name === 'NotAllowedError' 
          ? "Camera permission was denied. Please allow camera access in browser site settings and reload."
          : "Camera error: " + (err.message || 'Unable to open video device');
        camPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-400 font-bold border border-rose-500/30 text-[11px]";
        camText.innerText = "Camera Offline";
      }
    }

    function switchCamera() {
      currentCameraFacingMode = currentCameraFacingMode === 'user' ? 'environment' : 'user';
      initCamera();
    }

    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => console.warn(err));
        document.getElementById('fullscreen-icon').className = "fa-solid fa-compress";
      } else {
        document.exitFullscreen().catch(err => console.warn(err));
        document.getElementById('fullscreen-icon').className = "fa-solid fa-expand";
      }
    }

    // ------------------------------------------------------------------
    // Load AI Face-API Models & Registered Staff Biometrics
    // ------------------------------------------------------------------
    async function initFaceApiModels() {
      const aiPill = document.getElementById('ai-status-pill');
      const aiText = document.getElementById('ai-status-text');

      try {
        aiText.innerText = "Loading Neural Models...";
        
        // Load high-speed TinyFaceDetector, 68-point landmarks & 128-D recognition models
        await Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri('models'),
          faceapi.nets.faceLandmark68Net.loadFromUri('models'),
          faceapi.nets.faceRecognitionNet.loadFromUri('models')
        ]);

        modelsLoaded = true;
        aiPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 font-bold border border-purple-500/30 text-[11px]";
        aiText.innerText = "Loading Staff Biometrics...";

        await loadRegisteredStaffDescriptors();

        aiPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]";
        aiText.innerText = "Face AI Ready";

      } catch (err) {
        console.error("Model loading error:", err);
        aiPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-400 font-bold border border-rose-500/30 text-[11px]";
        aiText.innerText = "AI Models Failed";
      }
    }

    // Fetch registered staff vectors from server
    async function loadRegisteredStaffDescriptors() {
      try {
        const res = await fetch('api/staff_face.php?action=get_registered_faces');
        const json = await res.json();
        
        if (json.status === 'success' && Array.isArray(json.data)) {
          registeredStaffList = json.data;
          registeredStaffMap = {};
          
          const labeledDescriptors = [];
          registeredStaffList.forEach(s => {
            registeredStaffMap[String(s.id)] = s;
            if (s.descriptor && Array.isArray(s.descriptor) && s.descriptor.length >= 64) {
              labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(
                String(s.id),
                [new Float32Array(s.descriptor)]
              ));
            }
          });

          document.getElementById('stat-registered-count').innerText = registeredStaffList.length;

          if (labeledDescriptors.length > 0) {
            // Distance threshold 0.52: strictly matches human face
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.52);
          } else {
            faceMatcher = null;
          }
        }
      } catch (err) {
        console.error("Failed to load staff faces:", err);
      }
    }

    // ------------------------------------------------------------------
    // Continuous Live Facial Detection & Recognition Loop
    // ------------------------------------------------------------------
    let matchConfidenceStreak = 0;
    let candidateStaffId = null;

    async function startContinuousDetectionLoop() {
      const video = document.getElementById('video');
      const canvas = document.getElementById('canvas-overlay');
      const hudText = document.getElementById('hud-status-text');
      const reticle = document.getElementById('reticle-box');

      if (!video || !canvas) return;

      const runLoop = async () => {
        if (!videoStream || video.paused || video.ended) {
          detectionAnimationId = requestAnimationFrame(runLoop);
          return;
        }

        // Match canvas coordinate system to video
        if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
          canvas.width = video.videoWidth || 640;
          canvas.height = video.videoHeight || 480;
        }

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // If models are ready and not currently showing Thank You card
        if (modelsLoaded && !isProcessingMatch) {
          try {
            const detection = await faceapi
              .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
              .withFaceLandmarks()
              .withFaceDescriptor();

            if (detection) {
              const box = detection.detection.box;
              
              // Draw sleek bounding box
              drawFaceBox(ctx, box.x, box.y, box.width, box.height, '#a855f7');

              if (faceMatcher && detection.descriptor) {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                
                if (bestMatch.label !== 'unknown' && bestMatch.distance <= 0.52) {
                  const staff = registeredStaffMap[bestMatch.label];
                  const confidence = Math.round((1.0 - (bestMatch.distance / 0.6)) * 100);

                  // Draw name tag
                  drawNamePill(ctx, box.x, box.y, `${staff.first_name} ${staff.last_name} (${confidence}%)`, '#10b981');
                  reticle.style.borderColor = '#10b981';
                  hudText.innerText = `Recognized: ${staff.first_name} ${staff.last_name} (${confidence}%)`;

                  // Stability check: 2 consecutive frames confirms identity
                  if (candidateStaffId === staff.id) {
                    matchConfidenceStreak++;
                  } else {
                    candidateStaffId = staff.id;
                    matchConfidenceStreak = 1;
                  }

                  if (matchConfidenceStreak >= 2) {
                    // Check cooldown
                    const nowTs = Date.now();
                    if (lastMarkedStaffId === staff.id && (nowTs - lastMarkedTimestamp) < RE_PUNCH_COOLDOWN_MS) {
                      hudText.innerText = `${staff.first_name}, you just punched in! Please step aside.`;
                    } else {
                      // Trigger attendance registration
                      matchConfidenceStreak = 0;
                      candidateStaffId = null;
                      triggerAttendancePunch(staff, (confidence / 100));
                    }
                  }

                } else {
                  matchConfidenceStreak = 0;
                  candidateStaffId = null;
                  reticle.style.borderColor = '#f59e0b';
                  drawNamePill(ctx, box.x, box.y, "Face Detected (Unregistered)", '#f59e0b');
                  hudText.innerText = "Face detected • Unregistered in hospital system";
                }
              } else {
                reticle.style.borderColor = '#a855f7';
                hudText.innerText = "Face detected • Align in frame";
              }

            } else {
              matchConfidenceStreak = 0;
              candidateStaffId = null;
              reticle.style.borderColor = 'rgba(255, 255, 255, 0.3)';
              hudText.innerText = "Position your face inside the target frame...";
            }

          } catch (e) {
            // Frame skip
          }
        }

        detectionAnimationId = requestAnimationFrame(runLoop);
      };

      detectionAnimationId = requestAnimationFrame(runLoop);
    }

    // Helper: Draw Corner Brackets over Face
    function drawFaceBox(ctx, x, y, w, h, color) {
      const len = Math.min(w, h) * 0.22;
      ctx.strokeStyle = color;
      ctx.lineWidth = 3;
      ctx.lineCap = 'round';

      // Top-Left
      ctx.beginPath();
      ctx.moveTo(x, y + len); ctx.lineTo(x, y); ctx.lineTo(x + len, y);
      ctx.stroke();

      // Top-Right
      ctx.beginPath();
      ctx.moveTo(x + w - len, y); ctx.lineTo(x + w, y); ctx.lineTo(x + w, y + len);
      ctx.stroke();

      // Bottom-Left
      ctx.beginPath();
      ctx.moveTo(x, y + h - len); ctx.lineTo(x, y + h); ctx.lineTo(x + len, y + h);
      ctx.stroke();

      // Bottom-Right
      ctx.beginPath();
      ctx.moveTo(x + w - len, y + h); ctx.lineTo(x + w, y + h); ctx.lineTo(x + w, y + h - len);
      ctx.stroke();
    }

    // Helper: Draw Name Pill
    function drawNamePill(ctx, x, y, text, color) {
      ctx.font = 'bold 13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto';
      const textWidth = ctx.measureText(text).width;
      const padX = 10;
      const padY = 6;
      const pillY = Math.max(10, y - 28);

      ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
      ctx.beginPath();
      ctx.roundRect(x, pillY, textWidth + padX * 2, 24, 6);
      ctx.fill();

      ctx.strokeStyle = color;
      ctx.lineWidth = 1.5;
      ctx.stroke();

      ctx.fillStyle = color;
      ctx.fillText(text, x + padX, pillY + 16);
    }

    // ------------------------------------------------------------------
    // Mark Attendance & Show THANK YOU Screen
    // ------------------------------------------------------------------
    async function triggerAttendancePunch(staff, confidence) {
      if (isProcessingMatch) return;
      isProcessingMatch = true;

      try {
        const payload = {
          staff_id: staff.id,
          confidence: confidence,
          liveness_verified: 1
        };

        const res = await fetch('api/staff_face.php?action=auto_punch_attendance', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const json = await res.json();

        if (json.status === 'success') {
          lastMarkedStaffId = staff.id;
          lastMarkedTimestamp = Date.now();
          showThankYouScreen(json.data, staff);
          loadTodayPunches();
        } else if (json.status === 'already_marked') {
          lastMarkedStaffId = staff.id;
          lastMarkedTimestamp = Date.now();
          showAlreadyMarkedScreen(json.data, json.message);
        } else {
          console.warn("Punch error:", json.message);
          isProcessingMatch = false;
        }

      } catch (err) {
        console.error("Attendance request failed:", err);
        isProcessingMatch = false;
      }
    }

    // Show Thank You Celebration Modal
    function showThankYouScreen(data, staff) {
      const overlay = document.getElementById('thankyou-overlay');
      const avatarImg = document.getElementById('ty-staff-avatar');
      const nameEl = document.getElementById('ty-staff-name');
      const codeEl = document.getElementById('ty-staff-code');
      const roleEl = document.getElementById('ty-staff-role');
      const shiftEl = document.getElementById('ty-staff-shift');
      const timeEl = document.getElementById('ty-punch-time');
      const badgeEl = document.getElementById('ty-status-badge');
      const progressBar = document.getElementById('ty-progress-bar');
      const countdownText = document.getElementById('ty-countdown-text');
      const subtitleEl = document.getElementById('ty-subtitle');
      const updatedBadge = document.getElementById('ty-updated-badge');
      const noteBox = document.getElementById('ty-note-box');
      const noteText = document.getElementById('ty-note-text');

      const avatarSrc = data.avatar || staff.avatar || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(data.staff_name) + '&background=random');
      avatarImg.src = avatarSrc;
      nameEl.innerText = data.staff_name;
      codeEl.innerText = data.staff_code || ('STF-' + data.staff_id);
      roleEl.innerText = `${data.role || staff.role} • ${data.department || staff.department}`;
      shiftEl.innerText = `Shift: ${staff.shift || 'General'}`;
      timeEl.innerText = data.check_in_time_formatted || new Date().toLocaleTimeString();

      const st = data.status || 'Present';
      badgeEl.innerText = st;
      if (st === 'Present') {
        badgeEl.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30";
      } else if (st === 'Late') {
        badgeEl.className = "px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30";
      }

      // If previously marked Absent and now updated to Present
      if (data.was_absent) {
        if (subtitleEl) {
          subtitleEl.innerText = `Status Updated: ${data.previous_status || 'Absent'} ➔ Present`;
          subtitleEl.className = "text-xs sm:text-sm font-bold text-purple-400 tracking-wide uppercase mb-5";
        }
        if (updatedBadge) updatedBadge.classList.remove('hidden');
        if (noteBox) {
          noteBox.classList.remove('hidden');
          if (noteText) noteText.innerText = data.notes || `Previously marked as ${data.previous_status || 'Absent'}. Attendance status updated to Present at ${data.check_in_time_formatted}.`;
        }
        if (voiceEnabled) {
          speakVoice(`Welcome, ${data.staff_name}. Your attendance status has been changed from ${data.previous_status || 'Absent'} to Present.`);
        }
      } else {
        if (subtitleEl) {
          subtitleEl.innerText = "Attendance Marked Successfully";
          subtitleEl.className = "text-xs sm:text-sm font-bold text-emerald-400 tracking-wide uppercase mb-5";
        }
        if (updatedBadge) updatedBadge.classList.add('hidden');
        if (noteBox) noteBox.classList.add('hidden');
        if (voiceEnabled) {
          speakVoice(`Thank you, ${data.staff_name}. Attendance marked as ${st}.`);
        }
      }

      // Show overlay
      overlay.classList.remove('hidden');

      // Animate progress bar (3.5s countdown)
      progressBar.style.width = '100%';
      setTimeout(() => {
        progressBar.style.width = '0%';
      }, 50);

      let timeLeft = 3;
      countdownText.innerText = `${timeLeft}s`;
      const interval = setInterval(() => {
        timeLeft--;
        if (timeLeft > 0) {
          countdownText.innerText = `${timeLeft}s`;
        } else {
          clearInterval(interval);
          overlay.classList.add('hidden');
          isProcessingMatch = false;
        }
      }, 1000);
    }

    // Show Already Marked Modal
    function showAlreadyMarkedScreen(data, message) {
      const overlay = document.getElementById('alreadymarked-overlay');
      const avatarImg = document.getElementById('am-staff-avatar');
      const nameEl = document.getElementById('am-staff-name');
      const codeEl = document.getElementById('am-staff-code');
      const roleEl = document.getElementById('am-staff-role');
      const msgEl = document.getElementById('am-message');

      avatarImg.src = data.avatar || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(data.staff_name) + '&background=random');
      nameEl.innerText = data.staff_name;
      codeEl.innerText = data.staff_code;
      roleEl.innerText = `${data.role} • ${data.department}`;
      msgEl.innerText = message || `Attendance was already recorded today at ${data.check_in_time_formatted} (${data.status}).`;

      if (voiceEnabled) {
        speakVoice(`Welcome back, ${data.staff_name}. Attendance was already marked.`);
      }

      overlay.classList.remove('hidden');

      setTimeout(() => {
        dismissAlreadyMarkedOverlay();
      }, 3500);
    }

    function dismissAlreadyMarkedOverlay() {
      document.getElementById('alreadymarked-overlay').classList.add('hidden');
      isProcessingMatch = false;
    }

    // ------------------------------------------------------------------
    // Live Recent Punches Feed
    // ------------------------------------------------------------------
    async function loadTodayPunches() {
      const listEl = document.getElementById('recent-punches-list');
      try {
        const res = await fetch('api/staff_face.php?action=get_today_face_punches');
        const json = await res.json();

        if (json.status === 'success' && Array.isArray(json.data)) {
          document.getElementById('stat-today-punches').innerText = json.count;

          if (json.data.length === 0) {
            listEl.innerHTML = `
              <div class="text-center py-16 text-slate-500 text-xs">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800 text-slate-600 flex items-center justify-center text-xl mb-3">
                  <i class="fa-regular fa-clock"></i>
                </div>
                <p class="font-bold text-slate-400">No punches recorded yet today</p>
                <p class="text-[11px] text-slate-600 mt-1">Staff will appear here in real-time as they scan in.</p>
              </div>`;
            return;
          }

          let html = '';
          json.data.forEach(p => {
            let statusPill = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Present</span>';
            if (p.status === 'Late') {
              statusPill = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">Late</span>';
            } else if (p.status === 'Half Day') {
              statusPill = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-400 border border-sky-500/30">Half Day</span>';
            }

            let updatedBadge = '';
            if (p.notes && (p.notes.includes('Absent') || p.notes.includes('On Leave'))) {
              updatedBadge = '<span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">Updated</span>';
            }

            const avatarSrc = p.avatar || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(p.first_name + ' ' + p.last_name) + '&background=random');

            html += `
              <div class="p-3 bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/60 rounded-2xl transition flex items-center gap-3">
                <img src="${avatarSrc}" alt="${p.first_name}" class="w-10 h-10 rounded-xl object-cover border border-slate-600 shadow-sm shrink-0 bg-slate-700">
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-1">
                    <h4 class="font-bold text-xs text-white truncate">${p.first_name} ${p.last_name}</h4>
                    <div class="flex items-center gap-1 shrink-0">
                      ${updatedBadge}
                      ${statusPill}
                    </div>
                  </div>
                  <div class="flex items-center justify-between text-[11px] text-slate-400 mt-0.5">
                    <span class="font-mono text-slate-500">${p.staff_code}</span>
                    <span class="font-mono font-bold text-purple-300">${p.time_formatted}</span>
                  </div>
                </div>
              </div>`;
          });

          listEl.innerHTML = html;
        }
      } catch (err) {
        console.warn("Could not load today punches:", err);
      }
    }

    // Reset Today's Punches for Testing
    async function resetTestAttendance() {
      if (!confirm("Reset today's test face punches? This will remove today's kiosk check-ins so you can test again.")) return;
      try {
        const res = await fetch('api/staff_face.php?action=reset_test_attendance', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ staff_id: 0 })
        });
        const json = await res.json();
        alert(json.message);
        loadTodayPunches();
      } catch (e) {
        alert("Reset failed: " + e.message);
      }
    }
  </script>
</body>
</html>

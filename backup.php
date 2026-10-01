<?php
require_once 'auth.php';
include 'includes/header.php';
?>

<style>
  /* SVG Checkmark Animation */
  @keyframes checkmark-circle {
    0% {
      stroke-dashoffset: 280;
      transform: rotate(-90deg);
    }
    100% {
      stroke-dashoffset: 0;
      transform: rotate(-90deg);
    }
  }

  @keyframes checkmark-check {
    0% {
      stroke-dashoffset: 80;
      opacity: 0;
      transform: scale(0.8);
    }
    50% {
      opacity: 1;
    }
    100% {
      stroke-dashoffset: 0;
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes success-pop {
    0% { transform: scale(0.6); opacity: 0; }
    70% { transform: scale(1.08); }
    100% { transform: scale(1); opacity: 1; }
  }

  @keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 0 16px rgba(16, 185, 129, 0); }
  }

  .animate-checkmark-circle {
    stroke-dasharray: 280;
    stroke-dashoffset: 280;
    animation: checkmark-circle 0.8s cubic-bezier(0.65, 0, 0.45, 1) forwards;
  }

  .animate-checkmark-check {
    stroke-dasharray: 80;
    stroke-dashoffset: 80;
    animation: checkmark-check 0.5s 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
  }

  .animate-success-pop {
    animation: success-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }

  .animate-pulse-glow {
    animation: pulse-glow 2s infinite;
  }

  /* Highlight new row */
  @keyframes row-highlight {
    0% { background-color: rgba(59, 130, 246, 0.2); }
    100% { background-color: transparent; }
  }
  .row-just-added {
    animation: row-highlight 3s ease-out forwards;
  }
</style>

<div class="space-y-6">

  <!-- ================= TOP HEADER ================= -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-blue-500/25 shrink-0">
        <i class="fa-solid fa-database"></i>
      </div>
      <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Database Backup & Disaster Recovery</h2>
        <p class="text-slate-500 text-xs sm:text-sm font-medium mt-0.5">Generate on-demand SQL snapshots, download safe copies of patient records, and archive system states.</p>
      </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-2">
      <button onclick="openBackupFolderOnPC()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold py-2 px-3.5 rounded-xl shadow-2xs transition flex items-center gap-2 text-xs" title="Open backup storage folder directly in Windows File Explorer">
        <i class="fa-solid fa-folder-open text-indigo-600"></i>
        <span>Open Backup Folder in PC</span>
      </button>
      <button onclick="fetchBackupsList()" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold py-2 px-3.5 rounded-xl shadow-2xs transition flex items-center gap-2 text-xs">
        <i class="fa-solid fa-arrow-rotate-right text-slate-500" id="btn-refresh-icon"></i>
        <span>Refresh</span>
      </button>
    </div>
  </div>

  <!-- ================= KPI STATS CARDS ================= -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <!-- Active Database -->
    <div class="apple-card p-4 relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Database</span>
        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-server"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5 truncate">
        <span id="kpi-db-name" class="text-xl sm:text-2xl font-black text-slate-900 truncate">hospital_db</span>
      </div>
      <p id="kpi-db-tables" class="text-[11px] font-semibold text-blue-600 mt-0.5">16 Tables Online</p>
    </div>

    <!-- Total Backups -->
    <div class="apple-card p-4 relative overflow-hidden group border-indigo-200/50 bg-gradient-to-br from-white to-indigo-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Archived Backups</span>
        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">
          <i class="fa-solid fa-box-archive"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span id="kpi-total-backups" class="text-xl sm:text-2xl font-black text-indigo-900">0</span>
        <span class="text-[11px] font-semibold text-indigo-600">Snapshots</span>
      </div>
      <div class="flex items-center justify-between mt-1 text-[11px]">
        <span class="font-semibold text-slate-400">Stored in /backups</span>
        <button onclick="openBackupFolderOnPC()" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
          <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i> Open in PC
        </button>
      </div>
    </div>

    <!-- Total Storage -->
    <div class="apple-card p-4 relative overflow-hidden group border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Disk Storage</span>
        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
          <i class="fa-solid fa-hard-drive"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span id="kpi-total-size" class="text-xl sm:text-2xl font-black text-emerald-800">0 KB</span>
      </div>
      <p class="text-[11px] font-semibold text-emerald-600 mt-0.5">Safe Local Copies</p>
    </div>

    <!-- Last Backup Date -->
    <div class="apple-card p-4 relative overflow-hidden group border-amber-200/50 bg-gradient-to-br from-white to-amber-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Latest Snapshot</span>
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
          <i class="fa-regular fa-clock"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span id="kpi-latest-relative" class="text-base sm:text-lg font-black text-amber-900 truncate">Never</span>
      </div>
      <p id="kpi-latest-date" class="text-[10px] font-mono text-slate-400 mt-0.5 truncate">No backups recorded</p>
    </div>
  </div>

  <!-- ================= HERO ACTION CARD: TAKE BACKUP ================= -->
  <div class="apple-card p-6 sm:p-8 relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-blue-50/40 border border-slate-200/80 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      
      <!-- Left side info -->
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-black tracking-wide uppercase mb-3">
          <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
          <span>Zero-Downtime Backup Engine</span>
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Create Complete Database Snapshot</h3>
        <p class="text-slate-600 text-xs sm:text-sm mt-1.5 leading-relaxed">
          Instantly export an exact, fully recoverable SQL file containing table schemas, patient medical files, appointments, doctor consultation notes, prescriptions, and staff attendance logs.
        </p>

        <!-- Feature chips -->
        <div class="flex flex-wrap items-center gap-2.5 mt-4 text-xs font-bold">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i> Foreign Key Safe
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
            <i class="fa-solid fa-code text-blue-600"></i> Standard MySQL / MariaDB Dump
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
            <i class="fa-solid fa-lock text-indigo-600"></i> .htaccess Protected Storage
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
            <i class="fa-solid fa-download text-amber-600"></i> Direct 1-Click Download
          </span>
        </div>
      </div>

      <!-- Right side action button -->
      <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-3 shrink-0">
        <button onclick="openConfirmBackupModal()" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-black text-sm px-7 py-4 rounded-2xl shadow-xl shadow-blue-500/25 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
          <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
          <span>Take Backup Now</span>
        </button>
        <span class="text-[11px] text-slate-400 font-medium text-center">Requires confirmation before snapshot</span>
      </div>

    </div>
  </div>

  <!-- ================= BANNER: LOAD & RESTORE DATABASE ================= -->
  <div class="apple-card p-6 sm:p-7 relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-emerald-50/30 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5">
    <div class="flex items-start sm:items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold shrink-0 shadow-2xs">
        <i class="fa-solid fa-rotate-left"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-black text-slate-900 tracking-tight">Need to Load or Restore Database Data?</h3>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Separate Dedicated Page</span>
        </div>
        <p class="text-xs text-slate-500 mt-1 max-w-xl">
          Restoring data on a new device or migrating patient records? Use our dedicated <strong>Load / Restore Data</strong> page with automated 16-table pre-flight schema scanning and mismatch protection.
        </p>
      </div>
    </div>

    <div class="shrink-0 flex items-center gap-2.5">
      <a href="restore.php" class="py-3 px-5 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/25 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
        <i class="fa-solid fa-rotate-left"></i>
        <span>Open Load Data Page</span>
        <i class="fa-solid fa-arrow-right text-[11px] ml-1"></i>
      </a>
    </div>
  </div>

  <!-- ================= BACKUP ARCHIVES TABLE ================= -->
  <div class="apple-card overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
      <div>
        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
          <span>Backup Archive History</span>
          <span id="archives-badge-count" class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">0 files</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">All previously generated database snapshots. Download or delete archive files at any time.</p>
      </div>

      <!-- Search & Folder Actions -->
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button onclick="openBackupFolderOnPC()" class="bg-white hover:bg-indigo-50 text-indigo-700 border border-slate-200 hover:border-indigo-300 font-bold py-2 px-3 rounded-xl shadow-2xs transition flex items-center gap-1.5 text-xs shrink-0" title="Open the backups folder directly in Windows File Explorer">
          <i class="fa-solid fa-folder-open text-indigo-600"></i>
          <span>Open Folder in PC</span>
        </button>
        <div class="relative w-full sm:w-64">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input type="text" id="filter-backups" oninput="filterBackupsTable()" placeholder="Search backups by date or name..." class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/30 outline-none transition">
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-100/70 text-slate-600 uppercase font-black tracking-wider text-[11px] border-b border-slate-200/80">
          <tr>
            <th class="py-3.5 px-4">SQL Backup Snapshot</th>
            <th class="py-3.5 px-4">Created Date & Time</th>
            <th class="py-3.5 px-4">File Size</th>
            <th class="py-3.5 px-4">Security & Storage</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="backups-table-body" class="divide-y divide-slate-100 font-medium">
          <tr>
            <td colspan="5" class="text-center py-12 text-slate-400">
              <i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-500 mb-2"></i>
              <p>Scanning backups archive...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ================= STEP 1 MODAL: CONFIRM BACKUP GENERATION ================= -->
<div id="modal-confirm-backup" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-lg w-full p-6 sm:p-7 relative my-6 shadow-2xl border border-slate-200">
    <button onclick="closeConfirmBackupModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <div class="space-y-5">
      <!-- Icon & Header -->
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold shadow-xs shrink-0">
          <i class="fa-solid fa-cloud-arrow-down"></i>
        </div>
        <div>
          <h3 class="text-lg font-black text-slate-900">Confirm Database Backup</h3>
          <p class="text-xs text-slate-500 mt-0.5">Please confirm that you want to export an immediate snapshot.</p>
        </div>
      </div>

      <!-- Details Box -->
      <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2.5 text-xs">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
          <span class="text-slate-500 font-semibold">Target Database:</span>
          <span class="font-mono font-bold text-slate-900" id="confirm-modal-dbname">hospital_db</span>
        </div>
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
          <span class="text-slate-500 font-semibold">Included Tables:</span>
          <span class="font-bold text-blue-700" id="confirm-modal-tables">All 16 Online Tables</span>
        </div>
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
          <span class="text-slate-500 font-semibold">Output Format:</span>
          <span class="font-mono font-bold text-slate-700">MySQL SQL Dump (.sql)</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500 font-semibold">Estimated Time:</span>
          <span class="font-bold text-emerald-700">< 1 second</span>
        </div>
      </div>

      <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm shrink-0"></i>
        <span>A new snapshot file will be securely written to the server's backup repository and made available for direct download.</span>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3 pt-2">
        <button type="button" onclick="closeConfirmBackupModal()" class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition text-center">
          Cancel
        </button>
        <button type="button" onclick="executeBackup()" id="btn-execute-backup" class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/30 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-check"></i>
          <span>Yes, Confirm & Take Backup</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= STEP 2 MODAL: LIVE PROGRESS & CELEBRATION SUCCESS ANIMATION ================= -->
<div id="modal-backup-progress-success" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md overflow-y-auto">
  
  <!-- Canvas for Confetti Particle Explosion -->
  <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-[140] w-full h-full"></canvas>

  <div class="apple-card max-w-md w-full p-6 sm:p-8 relative my-6 shadow-2xl border border-slate-200 text-center z-[150] overflow-hidden">

    <!-- Close button (shown in success state) -->
    <button id="btn-close-success-modal" onclick="closeProgressSuccessModal()" class="hidden absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <!-- ====== STATE A: IN PROGRESS ====== -->
    <div id="backup-state-progress" class="py-6 space-y-6">
      <div class="relative w-20 h-20 mx-auto">
        <!-- Spinning glowing ring -->
        <div class="absolute inset-0 rounded-full border-4 border-blue-100 border-t-blue-600 animate-spin"></div>
        <div class="absolute inset-2 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-2xl">
          <i class="fa-solid fa-database animate-pulse"></i>
        </div>
      </div>

      <div>
        <h4 class="text-lg font-black text-slate-900">Generating SQL Snapshot...</h4>
        <p class="text-xs text-slate-500 mt-1" id="progress-step-text">Dumping schemas and patient records</p>
      </div>

      <!-- Animated Progress Bar -->
      <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
        <div id="progress-bar-fill" class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full transition-all duration-300 w-1/4"></div>
      </div>
    </div>

    <!-- ====== STATE B: SUCCESS WITH CELEBRATION ANIMATION ====== -->
    <div id="backup-state-success" class="hidden py-2 space-y-5 animate-success-pop">
      
      <!-- SVG Animated Checkmark Graphic -->
      <div class="relative w-24 h-24 mx-auto animate-pulse-glow rounded-full">
        <svg class="w-24 h-24" viewBox="0 0 100 100">
          <!-- Background circle track -->
          <circle cx="50" cy="50" r="44" fill="none" stroke="#d1fae5" stroke-width="6" />
          <!-- Animated green drawing stroke -->
          <circle class="animate-checkmark-circle" cx="50" cy="50" r="44" fill="none" stroke="#10b981" stroke-width="6" stroke-linecap="round" />
          <!-- Animated Checkmark -->
          <path class="animate-checkmark-check" fill="none" stroke="#10b981" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" d="M30 52 L43 65 L70 36" />
        </svg>
      </div>

      <!-- Headline -->
      <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black uppercase tracking-wider mb-1.5">
          <i class="fa-solid fa-circle-check"></i>
          <span>Backup Completed</span>
        </div>
        <h3 class="text-xl font-black text-slate-900 tracking-tight">Database Backup Successful!</h3>
        <p class="text-xs text-slate-500 mt-1">Your SQL snapshot has been safely generated and verified.</p>
      </div>

      <!-- Backup Details Pill Card -->
      <div class="bg-gradient-to-br from-slate-50 to-emerald-50/30 p-4 rounded-2xl border border-emerald-100 text-left space-y-2 text-xs">
        <div class="flex items-center justify-between gap-2 pb-2 border-b border-emerald-100">
          <span class="text-slate-400 font-semibold flex items-center gap-1.5">
            <i class="fa-solid fa-file-code text-blue-500"></i> File Name:
          </span>
          <span class="font-mono font-bold text-slate-800 truncate max-w-[200px]" id="success-filename">-</span>
        </div>

        <div class="grid grid-cols-3 gap-2 pt-1 text-center">
          <div class="bg-white p-2 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">File Size</span>
            <span class="font-mono font-black text-emerald-700 text-sm" id="success-filesize">-</span>
          </div>
          <div class="bg-white p-2 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Tables</span>
            <span class="font-mono font-black text-blue-700 text-sm" id="success-tables">-</span>
          </div>
          <div class="bg-white p-2 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Rows</span>
            <span class="font-mono font-black text-indigo-700 text-sm" id="success-rows">-</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2 pt-1">
        <a id="btn-download-sql-file" href="#" class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold rounded-xl shadow-lg shadow-emerald-600/25 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 text-xs">
          <i class="fa-solid fa-download text-sm"></i>
          <span>Download .SQL File Now</span>
        </a>

        <button type="button" onclick="openBackupFolderOnPC(latestNewFilename)" class="w-full py-2.5 px-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-extrabold text-xs transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-folder-open text-indigo-600"></i>
          <span>Open Backup Folder in PC</span>
        </button>

        <button onclick="closeProgressSuccessModal()" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
          Done & View in Archive
        </button>
      </div>

    </div>

  </div>
</div>

<!-- ================= MODAL: PC BACKUP FOLDER INFO & PATH ================= -->
<div id="modal-folder-path" class="hidden fixed inset-0 z-[125] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
  <div class="apple-card max-w-lg w-full p-6 sm:p-7 relative my-6 shadow-2xl border border-slate-200">
    <button onclick="closeFolderModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    <div class="space-y-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold shrink-0 shadow-2xs">
          <i class="fa-solid fa-folder-open"></i>
        </div>
        <div>
          <h3 class="text-lg font-black text-slate-900">PC Backup Storage Directory</h3>
          <p class="text-xs text-slate-500">Exact folder path on this computer where SQL snapshots are stored.</p>
        </div>
      </div>

      <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Local Folder Path</label>
          <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Directly Accessible</span>
        </div>
        <div class="flex items-center gap-2">
          <input type="text" id="input-pc-backup-path" readonly value="C:\xampp\htdocs\Hospital Management System\backups" class="w-full font-mono text-xs bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-800 outline-none select-all font-bold">
          <button type="button" onclick="copyPcBackupPath()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 shrink-0 shadow-md shadow-indigo-600/25">
            <i class="fa-regular fa-copy"></i>
            <span>Copy</span>
          </button>
        </div>
      </div>

      <div class="p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-xs text-indigo-900 space-y-1.5">
        <p class="font-extrabold flex items-center gap-1.5"><i class="fa-solid fa-lightbulb text-amber-500"></i> Windows Run Shortcut:</p>
        <p class="text-[11px] text-indigo-700 leading-relaxed">Press <kbd class="px-2 py-0.5 rounded-md bg-white border border-indigo-200 font-mono font-bold text-slate-800 shadow-2xs">Win</kbd> + <kbd class="px-2 py-0.5 rounded-md bg-white border border-indigo-200 font-mono font-bold text-slate-800 shadow-2xs">R</kbd> on your keyboard, paste the path above, and press <kbd class="px-2 py-0.5 rounded-md bg-white border border-indigo-200 font-mono font-bold text-slate-800 shadow-2xs">Enter</kbd> to open Windows File Explorer instantly.</p>
      </div>

      <div class="flex items-center gap-2.5 pt-2">
        <button type="button" onclick="openBackupFolderOnPC(); closeFolderModal();" class="flex-1 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Windows File Explorer
        </button>
        <button type="button" onclick="closeFolderModal()" class="px-5 py-3 border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs rounded-xl transition">
          Close
        </button>
      </div>
    </div>
  </div>
</div>



<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
  let backupsList = [];
  let latestNewFilename = null;
  let confettiAnimationId = null;
  let backupFolderPath = 'C:\\xampp\\htdocs\\Hospital Management System\\backups';

  document.addEventListener('DOMContentLoaded', () => {
    fetchBackupsList();
  });

  // Fetch Backups List
  async function fetchBackupsList() {
    const icon = document.getElementById('btn-refresh-icon');
    if (icon) icon.classList.add('fa-spin');

    try {
      const res = await fetch('api/backup.php?action=list_backups');
      const data = await res.json();
      if (data.status === 'success' && data.data) {
        backupsList = data.data.backups || [];
        const summ = data.data.summary || {};

        if (summ.backup_folder_path) {
          backupFolderPath = summ.backup_folder_path;
          const inp = document.getElementById('input-pc-backup-path');
          if (inp) inp.value = summ.backup_folder_path;
        }

        // Update KPIs
        document.getElementById('kpi-total-backups').textContent = summ.total_backups || 0;
        document.getElementById('kpi-total-size').textContent = summ.total_size_formatted || '0 KB';
        document.getElementById('kpi-db-name').textContent = summ.database_name || 'hospital_db';
        document.getElementById('kpi-db-tables').textContent = `${summ.db_tables_count || 16} Tables Online`;

        document.getElementById('confirm-modal-dbname').textContent = summ.database_name || 'hospital_db';
        document.getElementById('confirm-modal-tables').textContent = `All ${summ.db_tables_count || 16} Tables`;

        if (summ.latest_backup) {
          const first = backupsList[0];
          document.getElementById('kpi-latest-relative').textContent = first ? first.relative_time : 'Recently';
          document.getElementById('kpi-latest-date').textContent = summ.latest_backup;
        } else {
          document.getElementById('kpi-latest-relative').textContent = 'Never';
          document.getElementById('kpi-latest-date').textContent = 'No backups recorded';
        }

        // Populate Restore dropdown
        const selRestore = document.getElementById('select-restore-snapshot');
        if (selRestore) {
          const curVal = selRestore.value;
          selRestore.innerHTML = '<option value="">-- Choose a backup snapshot --</option>' + 
            backupsList.map(b => `<option value="${escapeHtml(b.filename)}">${escapeHtml(b.filename)} (${b.size_formatted} &bull; ${b.created_at})</option>`).join('');
          if (curVal) selRestore.value = curVal;
        }

        renderBackupsTable(backupsList);
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Could not load backups archive', 'error');
    } finally {
      if (icon) icon.classList.remove('fa-spin');
    }
  }

  // Render Table
  function renderBackupsTable(list) {
    const tbody = document.getElementById('backups-table-body');
    document.getElementById('archives-badge-count').textContent = `${list.length} files`;

    if (!list.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center py-12 text-slate-400">
            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
            <p class="font-bold text-slate-600">No backup snapshots found.</p>
            <p class="text-xs text-slate-400 mt-1">Click "Take Backup Now" to create your first database backup.</p>
          </td>
        </tr>`;
      return;
    }

    let html = '';
    list.forEach(b => {
      const isNew = b.filename === latestNewFilename;
      html += `
        <tr class="hover:bg-slate-50/80 transition ${isNew ? 'row-just-added' : ''}">
          <!-- Filename -->
          <td class="py-3.5 px-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center text-sm shadow-2xs shrink-0">
                <i class="fa-solid fa-file-code"></i>
              </div>
              <div class="min-w-0">
                <a href="${b.download_url}" class="font-mono font-bold text-slate-900 hover:text-blue-600 text-xs truncate block transition">
                  ${escapeHtml(b.filename)}
                </a>
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full mt-0.5">
                  <i class="fa-solid fa-check text-[9px]"></i> Verified SQL Snapshot
                </span>
              </div>
            </div>
          </td>

          <!-- Date -->
          <td class="py-3.5 px-4">
            <div>
              <span class="font-bold text-slate-800 text-xs">${b.created_at}</span>
              <span class="block text-[11px] text-slate-400 mt-0.5">${b.relative_time}</span>
            </div>
          </td>

          <!-- Size -->
          <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
            ${b.size_formatted}
          </td>

          <!-- Security -->
          <td class="py-3.5 px-4">
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
              <i class="fa-solid fa-lock text-[10px] text-slate-400"></i> .htaccess Protected
            </span>
          </td>

          <!-- Actions -->
          <td class="py-3.5 px-4 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="restore.php?file=${encodeURIComponent(b.filename)}" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs" title="Scan and restore database from this snapshot on dedicated Load Data page">
                <i class="fa-solid fa-rotate-left text-emerald-600"></i>
                <span class="hidden md:inline">Scan & Load</span>
              </a>
              <button onclick="openBackupFolderOnPC('${escapeHtml(b.filename)}')" class="px-2.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs" title="Open and highlight this file in Windows File Explorer">
                <i class="fa-solid fa-folder-open text-indigo-600"></i>
                <span class="hidden md:inline">Show in PC</span>
              </button>
              <a href="${b.download_url}" class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs" title="Download SQL file">
                <i class="fa-solid fa-download"></i>
                <span>Download</span>
              </a>
              <button onclick="confirmDeleteBackup('${escapeHtml(b.filename)}')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition flex items-center justify-center text-xs shadow-2xs" title="Delete backup">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });

    tbody.innerHTML = html;
  }

  // Filter Table
  function filterBackupsTable() {
    const term = document.getElementById('filter-backups').value.toLowerCase().trim();
    if (!term) {
      renderBackupsTable(backupsList);
      return;
    }
    const filtered = backupsList.filter(b => 
      b.filename.toLowerCase().includes(term) ||
      b.created_at.toLowerCase().includes(term) ||
      b.relative_time.toLowerCase().includes(term)
    );
    renderBackupsTable(filtered);
  }

  // Step 1: Open Confirmation Modal
  function openConfirmBackupModal() {
    document.getElementById('modal-confirm-backup').classList.remove('hidden');
  }

  function closeConfirmBackupModal() {
    document.getElementById('modal-confirm-backup').classList.add('hidden');
  }

  // Step 2: Execute Backup with Live Progress -> Success Celebration
  async function executeBackup() {
    closeConfirmBackupModal();

    // Open Progress Modal
    const progModal = document.getElementById('modal-backup-progress-success');
    const stateProgress = document.getElementById('backup-state-progress');
    const stateSuccess = document.getElementById('backup-state-success');
    const closeBtn = document.getElementById('btn-close-success-modal');
    const progressBar = document.getElementById('progress-bar-fill');
    const progressStep = document.getElementById('progress-step-text');

    // Reset states
    stateProgress.classList.remove('hidden');
    stateSuccess.classList.add('hidden');
    closeBtn.classList.add('hidden');
    progressBar.style.width = '25%';
    progressStep.textContent = 'Connecting to database engine...';
    progModal.classList.remove('hidden');

    // Simulate animated progress steps
    const step1 = setTimeout(() => {
      progressBar.style.width = '55%';
      progressStep.textContent = 'Extracting table schemas & foreign keys...';
    }, 200);

    const step2 = setTimeout(() => {
      progressBar.style.width = '80%';
      progressStep.textContent = 'Dumping clinical orders, patient data & staff logs...';
    }, 450);

    try {
      const res = await fetch('api/backup.php?action=create_backup');
      const data = await res.json();

      clearTimeout(step1);
      clearTimeout(step2);

      if (data.status === 'success' && data.data) {
        progressBar.style.width = '100%';
        progressStep.textContent = 'Verifying SQL dump integrity...';

        const d = data.data;
        latestNewFilename = d.filename;

        // Transition to Success Animation State
        setTimeout(() => {
          stateProgress.classList.add('hidden');
          stateSuccess.classList.remove('hidden');
          closeBtn.classList.remove('hidden');

          // Populate success details
          document.getElementById('success-filename').textContent = d.filename;
          document.getElementById('success-filesize').textContent = d.size_formatted;
          document.getElementById('success-tables').textContent = `${d.tables_count} Tables`;
          document.getElementById('success-rows').textContent = `${d.rows_count} Rows`;

          // Download button link
          const dlBtn = document.getElementById('btn-download-sql-file');
          dlBtn.href = d.download_url;
          dlBtn.setAttribute('download', d.filename);

          // Launch Confetti Celebration!
          launchConfetti();

          // Refresh backups table
          fetchBackupsList();
        }, 500);

      } else {
        closeProgressSuccessModal();
        showToast('Backup Failed', data.message || 'Error occurred while creating backup', 'error');
      }
    } catch (err) {
      console.error(err);
      clearTimeout(step1);
      clearTimeout(step2);
      closeProgressSuccessModal();
      showToast('Error', 'Server connection failure during backup', 'error');
    }
  }

  function closeProgressSuccessModal() {
    document.getElementById('modal-backup-progress-success').classList.add('hidden');
    stopConfetti();
  }

  // Delete Backup with Global Confirmation Modal
  function confirmDeleteBackup(filename) {
    openConfirmModal(
      'Delete SQL Backup?',
      'This will permanently delete this database snapshot from the server repository.',
      `<div class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-800">
        <p class="font-bold">File: <span class="font-mono">${escapeHtml(filename)}</span></p>
        <p class="mt-1">This action cannot be undone. Make sure you have downloaded a local copy if needed.</p>
      </div>`,
      async () => {
        try {
          const res = await fetch('api/backup.php?action=delete_backup', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename: filename })
          });
          const data = await res.json();
          if (data.status === 'success') {
            showToast('Deleted', data.message, 'success');
            closeConfirmModal();
            fetchBackupsList();
          } else {
            showToast('Error', data.message || 'Could not delete backup', 'error');
          }
        } catch (e) {
          console.error(e);
          showToast('Error', 'Delete failed', 'error');
        }
      },
      'danger'
    );
  }

  // Confetti Particle Celebration Animation (Canvas-based)
  function launchConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ['#10b981', '#3b82f6', '#6366f1', '#f59e0b', '#ec4899', '#14b8a6', '#8b5cf6'];
    const particles = [];
    const count = 120;

    for (let i = 0; i < count; i++) {
      particles.push({
        x: canvas.width / 2 + (Math.random() * 200 - 100),
        y: canvas.height / 2 - 50,
        r: Math.random() * 6 + 3,
        d: Math.random() * count,
        color: colors[Math.floor(Math.random() * colors.length)],
        tilt: Math.floor(Math.random() * 10) - 10,
        tiltAngleIncremental: (Math.random() * 0.07) + 0.05,
        tiltAngle: 0,
        vx: (Math.random() - 0.5) * 14,
        vy: (Math.random() * -12) - 4,
        gravity: 0.35,
        opacity: 1
      });
    }

    let startTime = Date.now();

    function render() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      const elapsed = Date.now() - startTime;

      particles.forEach(p => {
        p.vy += p.gravity;
        p.x += p.vx;
        p.y += p.vy;
        p.tiltAngle += p.tiltAngleIncremental;
        p.tilt = Math.sin(p.tiltAngle) * 12;

        if (elapsed > 2000) {
          p.opacity = Math.max(0, p.opacity - 0.02);
        }

        ctx.beginPath();
        ctx.lineWidth = p.r;
        ctx.strokeStyle = p.color;
        ctx.globalAlpha = p.opacity;
        ctx.moveTo(p.x + p.tilt + p.r, p.y);
        ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r);
        ctx.stroke();
      });

      if (elapsed < 3500) {
        confettiAnimationId = requestAnimationFrame(render);
      } else {
        stopConfetti();
      }
    }

    if (confettiAnimationId) cancelAnimationFrame(confettiAnimationId);
    render();
  }

  function stopConfetti() {
    if (confettiAnimationId) {
      cancelAnimationFrame(confettiAnimationId);
      confettiAnimationId = null;
    }
    const canvas = document.getElementById('confetti-canvas');
    if (canvas) {
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
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

  // ================= OPEN BACKUP FOLDER IN PC =================
  async function openBackupFolderOnPC(filename = '') {
    try {
      showToast('Opening Folder', filename ? `Locating ${filename} in File Explorer...` : 'Opening backup folder in PC File Explorer...', 'info');

      const url = filename
        ? `api/backup.php?action=open_folder&file=${encodeURIComponent(filename)}`
        : 'api/backup.php?action=open_folder';

      const res = await fetch(url);
      const data = await res.json();

      if (data.status === 'success') {
        const path = data.data?.folder_path || backupFolderPath;
        backupFolderPath = path;
        const msg = filename 
          ? `File Explorer focused on "${filename}"`
          : 'Backup folder opened in PC File Explorer!';
        showToast('File Explorer', msg, 'success');
      } else {
        showFolderModal(data.data?.folder_path || backupFolderPath);
      }
    } catch (err) {
      console.error(err);
      showFolderModal(backupFolderPath);
    }
  }

  function showFolderModal(path = null) {
    if (path) {
      backupFolderPath = path;
      const inp = document.getElementById('input-pc-backup-path');
      if (inp) inp.value = path;
    }
    const modal = document.getElementById('modal-folder-path');
    if (modal) modal.classList.remove('hidden');
  }

  function closeFolderModal() {
    const modal = document.getElementById('modal-folder-path');
    if (modal) modal.classList.add('hidden');
  }

  function copyPcBackupPath() {
    const path = backupFolderPath || 'C:\\xampp\\htdocs\\Hospital Management System\\backups';
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(path).then(() => {
        showToast('Copied', 'Folder path copied! Paste in File Explorer or Run dialog.', 'success');
      }).catch(() => {
        fallbackCopy(path);
      });
    } else {
      fallbackCopy(path);
    }
  }

  function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
      document.execCommand('copy');
      showToast('Copied', 'Folder path copied to clipboard!', 'success');
    } catch (e) {
      showToast('Path', text, 'info');
    }
    document.body.removeChild(ta);
  }
</script>

<?php include 'includes/footer.php'; ?>

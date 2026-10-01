<?php
require_once 'auth.php';
include 'includes/header.php';
?>

<style>
  /* SVG Checkmark Drawing Animation */
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
    70% { transform: scale(1.06); }
    100% { transform: scale(1); opacity: 1; }
  }

  @keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 0 18px rgba(16, 185, 129, 0); }
  }

  @keyframes scan-glow {
    0%, 100% { border-color: rgba(16, 185, 129, 0.3); }
    50% { border-color: rgba(16, 185, 129, 0.8); }
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

  .scanning-card-active {
    animation: scan-glow 1s infinite alternate;
  }
</style>

<!-- Canvas for Confetti Celebration -->
<canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-[200] w-full h-full"></canvas>

<div class="space-y-6">

  <!-- ================= TOP HEADER ================= -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-600 to-emerald-700 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-emerald-500/25 shrink-0">
        <i class="fa-solid fa-rotate-left"></i>
      </div>
      <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Load Data & Database Restore</h2>
        <p class="text-slate-500 text-xs sm:text-sm font-medium mt-0.5">Load database snapshots on a new device or recover clinical records with real-time pre-flight schema scanning.</p>
      </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-2 flex-wrap">
      <a href="backup.php" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold py-2 px-3.5 rounded-xl shadow-2xs transition flex items-center gap-2 text-xs" title="Generate new database backup snapshot">
        <i class="fa-solid fa-database text-blue-600"></i>
        <span>Create Backup</span>
      </a>
      <button onclick="openBackupFolderOnPC()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold py-2 px-3.5 rounded-xl shadow-2xs transition flex items-center gap-2 text-xs" title="Open backup storage folder directly in Windows File Explorer">
        <i class="fa-solid fa-folder-open text-indigo-600"></i>
        <span>Open Backup Folder</span>
      </button>
      <button onclick="fetchRestoreInitialData()" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold py-2 px-3.5 rounded-xl shadow-2xs transition flex items-center gap-2 text-xs">
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
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Target Database</span>
        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-server"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5 truncate">
        <span id="kpi-target-db" class="text-xl sm:text-2xl font-black text-slate-900 truncate">hospital_db</span>
      </div>
      <p class="text-[11px] font-semibold text-blue-600 mt-0.5">16 Tables Online</p>
    </div>

    <!-- Available Snapshots -->
    <div class="apple-card p-4 relative overflow-hidden group border-indigo-200/50 bg-gradient-to-br from-white to-indigo-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Available Snapshots</span>
        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">
          <i class="fa-solid fa-box-archive"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span id="kpi-available-snapshots" class="text-xl sm:text-2xl font-black text-indigo-900">0</span>
        <span class="text-[11px] font-semibold text-indigo-600">Files</span>
      </div>
      <div class="flex items-center justify-between mt-1 text-[11px]">
        <span class="font-semibold text-slate-400">Stored in /backups</span>
        <button onclick="openBackupFolderOnPC()" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
          <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i> View in PC
        </button>
      </div>
    </div>

    <!-- Pre-Flight Safety Enforcement -->
    <div class="apple-card p-4 relative overflow-hidden group border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Pre-flight Safety</span>
        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span class="text-xl sm:text-2xl font-black text-emerald-800">Enforced</span>
      </div>
      <p class="text-[11px] font-semibold text-emerald-600 mt-0.5">All 16 Tables & Fields</p>
    </div>

    <!-- Engine Speed -->
    <div class="apple-card p-4 relative overflow-hidden group border-amber-200/50 bg-gradient-to-br from-white to-amber-50/20">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Restore Engine</span>
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
          <i class="fa-solid fa-bolt"></i>
        </div>
      </div>
      <div class="mt-2 flex items-baseline gap-1.5">
        <span class="text-xl sm:text-2xl font-black text-amber-800">Native CLI</span>
      </div>
      <p class="text-[11px] font-semibold text-amber-600 mt-0.5">&lt; 1s High Performance</p>
    </div>
  </div>

  <!-- ================= SOURCE SELECTION CARD ================= -->
  <div class="apple-card p-6 sm:p-8 relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-emerald-50/40 border border-slate-200/80 shadow-sm">
    <div class="max-w-3xl">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black tracking-wide uppercase mb-3">
        <i class="fa-solid fa-cloud-arrow-down"></i>
        <span>Step 1: Choose Database SQL File</span>
      </div>
      <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Select or Upload SQL Snapshot</h3>
      <p class="text-slate-600 text-xs sm:text-sm mt-1.5 leading-relaxed">
        Restoring on a new device or recovering patient records? You can upload a <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-800 font-mono text-xs font-bold">.sql</code> file directly from your computer, or pick an existing snapshot from the server's backup repository.
      </p>
    </div>

    <!-- Dual Source Selector -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Option A: Upload from PC / New Device -->
      <div class="bg-white p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-300 transition shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-700 flex items-center gap-2">
              <i class="fa-solid fa-laptop text-emerald-600"></i> Option A: Upload from PC / New Device
            </span>
            <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-full border border-emerald-100">Recommended for New PC</span>
          </div>

          <!-- Drag and Drop Zone -->
          <div id="sql-dropzone" onclick="document.getElementById('input-sql-upload-file').click()" class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/30 rounded-2xl p-6 text-center cursor-pointer transition group">
            <input type="file" id="input-sql-upload-file" accept=".sql" onchange="handleSqlFileSelected(this.files)" class="hidden">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform shadow-2xs">
              <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
            </div>
            <p class="text-sm font-extrabold text-slate-800" id="dropzone-text">Click to browse or drop .SQL backup here</p>
            <p class="text-xs text-slate-400 mt-1" id="dropzone-subtext">Supports database snapshots up to 40MB</p>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100">
          <button type="button" id="btn-upload-and-scan" onclick="triggerUploadAndScan()" disabled class="w-full py-3 bg-slate-200 text-slate-400 font-extrabold text-xs rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed">
            <i class="fa-solid fa-upload"></i>
            <span>Upload & Scan Compatibility</span>
          </button>
        </div>
      </div>

      <!-- Option B: Select from Server / Backups Folder -->
      <div class="bg-white p-5 rounded-2xl border-2 border-slate-200 hover:border-indigo-300 transition shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-black uppercase tracking-wider text-indigo-700 flex items-center gap-2">
              <i class="fa-solid fa-server text-indigo-600"></i> Option B: Server Backups Folder
            </span>
            <span class="text-[10px] bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded-full border border-indigo-100">Stored Locally</span>
          </div>

          <p class="text-xs text-slate-500 mb-3 leading-relaxed">
            Pick from existing snapshots already stored in the application's local <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700 font-mono text-[11px]">/backups</code> folder.
          </p>

          <div class="space-y-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Select Snapshot to Load</label>
              <select id="select-restore-snapshot" onchange="handleSelectSnapshotChanged()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                <option value="">-- Choose a backup snapshot --</option>
              </select>
            </div>

            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400 font-medium">Looking for a specific file?</span>
              <button onclick="openBackupFolderOnPC()" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-folder-open text-[11px]"></i> Open in PC Explorer
              </button>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100">
          <button type="button" id="btn-server-scan" onclick="startScanSelectedSnapshot()" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-magnifying-glass-chart"></i>
            <span>Scan Selected Snapshot</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= SCANNER & VERIFICATION SECTION ================= -->
  <div id="section-scanner" class="hidden apple-card p-6 sm:p-8 relative overflow-hidden bg-white border border-slate-200 shadow-sm transition-all duration-300">
    
    <!-- Scanner Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <div id="scanner-status-icon-box" class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold shadow-2xs shrink-0">
          <i id="scanner-status-icon" class="fa-solid fa-radar fa-spin"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-xl font-black text-slate-900 tracking-tight" id="scanner-title">Database Schema & Compatibility Scanner</h3>
            <span id="scanner-verdict-badge" class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 uppercase tracking-wider">Scanning</span>
          </div>
          <p class="text-xs text-slate-500 font-mono mt-0.5 truncate max-w-xl" id="scanner-selected-filename">-</p>
        </div>
      </div>

      <!-- Action Button in Header (Enabled when 100% matched) -->
      <div id="scanner-header-actions" class="shrink-0">
        <button type="button" id="btn-quick-execute" disabled class="px-6 py-3 rounded-xl bg-slate-200 text-slate-400 font-extrabold text-xs transition flex items-center gap-2 cursor-not-allowed">
          <i class="fa-solid fa-clock"></i>
          <span>Verification in Progress...</span>
        </button>
      </div>
    </div>

    <!-- Scanner Body Content -->
    <div class="py-5 space-y-5">

      <!-- Status Banner -->
      <div id="scanner-state-banner" class="p-4 sm:p-5 rounded-2xl border transition-all duration-300 bg-blue-50/70 border-blue-200 text-blue-900 flex items-start gap-3.5 shadow-2xs">
        <div id="scanner-state-icon" class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-base shrink-0 mt-0.5">
          <i class="fa-solid fa-circle-notch fa-spin"></i>
        </div>
        <div class="flex-1 min-w-0">
          <h4 id="scanner-state-title" class="font-extrabold text-sm text-blue-900">Analyzing Database Schema & Table Fields...</h4>
          <p id="scanner-state-desc" class="text-xs text-blue-700 mt-0.5">Checking structure of all 16 hospital tables and fields against the selected snapshot.</p>
        </div>
      </div>

      <!-- Animated Progress Bar -->
      <div id="scanner-progress-section" class="space-y-2">
        <div class="flex items-center justify-between text-xs font-bold text-slate-600">
          <span id="scanner-progress-label" class="flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-blue-500"></i>
            <span>Verifying table structures...</span>
          </span>
          <span id="scanner-progress-percent" class="font-mono text-blue-700">0%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200">
          <div id="scanner-progress-fill" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500 h-full rounded-full transition-all duration-200 w-0"></div>
        </div>
      </div>

      <!-- Live Verification Table Grid (16 Tables) -->
      <div id="scanner-grid-container" class="space-y-3">
        <div class="flex items-center justify-between text-xs font-bold text-slate-700 px-1">
          <span class="flex items-center gap-2">
            <i class="fa-solid fa-table-cells text-slate-500"></i>
            <span>Table Compatibility Checks (16 Tables)</span>
          </span>
          <span id="scanner-count-badge" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-mono font-bold">0 / 16 Checked</span>
        </div>
        
        <div id="scanner-tables-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
          <!-- Dynamically populated table cards -->
        </div>
      </div>

      <!-- Error / Warnings Details Box (shown if mismatch) -->
      <div id="scanner-issues-box" class="hidden p-5 rounded-2xl bg-rose-50 border-2 border-rose-300 text-xs text-rose-900 space-y-3 shadow-sm">
        <div class="font-black flex items-center gap-2 text-rose-800 text-base">
          <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
          <span>Schema Incompatibility Detected — Restore Blocked</span>
        </div>
        <p class="text-rose-700 leading-relaxed font-semibold">
          This SQL snapshot file cannot be loaded because it does not match the active database schema. Loading mismatched data would corrupt hospital records or cause system crashes.
        </p>
        <div class="bg-white p-3.5 rounded-xl border border-rose-200 space-y-1.5">
          <span class="text-[11px] font-black uppercase text-rose-700 block">Specific Inconsistencies:</span>
          <ul id="scanner-issues-list" class="list-disc list-inside space-y-1 text-rose-800 font-mono text-[11px]">
            <!-- populated dynamically -->
          </ul>
        </div>
      </div>

      <!-- Success Details Box (shown if 100% matched) -->
      <div id="scanner-success-box" class="hidden p-5 rounded-2xl bg-emerald-50/90 border-2 border-emerald-300 text-xs text-emerald-900 space-y-3 shadow-sm">
        <div class="font-black flex items-center gap-2 text-emerald-800 text-base">
          <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
          <span>100% Schema & Fields Matched Successfully</span>
        </div>
        <p class="text-emerald-700 leading-relaxed font-semibold">
          Every table and required field in this snapshot matches the active database structure. Data will be safely imported into <strong class="font-mono text-emerald-900">hospital_db</strong>.
        </p>
        <div class="flex flex-wrap items-center gap-2 pt-1 text-xs font-bold text-emerald-800">
          <span class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
            <i class="fa-solid fa-database text-emerald-600"></i> <span id="success-tables-count">16</span> Tables Online
          </span>
          <span class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
            <i class="fa-solid fa-list-check text-emerald-600"></i> <span id="success-records-count">0</span> Total Records Found
          </span>
          <span class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i> Foreign Key Safe
          </span>
          <span class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
            <i class="fa-solid fa-bolt text-amber-500"></i> High-Speed Engine
          </span>
        </div>
      </div>

      <!-- Restoring Execution State Box (shown while executing restore) -->
      <div id="scanner-executing-box" class="hidden py-12 text-center space-y-5">
        <div class="relative w-20 h-20 mx-auto">
          <div class="absolute inset-0 rounded-full border-4 border-emerald-200 border-t-emerald-600 animate-spin"></div>
          <div class="absolute inset-2 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl">
            <i class="fa-solid fa-rotate animate-pulse"></i>
          </div>
        </div>
        <div>
          <h4 class="text-lg font-black text-slate-900">Restoring Database from Snapshot...</h4>
          <p class="text-xs text-slate-500 mt-1 font-medium" id="executing-step-text">Loading tables and records into hospital_db</p>
        </div>
        <div class="w-full max-w-md mx-auto bg-slate-100 rounded-full h-2 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-full w-3/4 animate-pulse"></div>
        </div>
      </div>

    </div>

    <!-- Scanner Bottom Actions -->
    <div id="scanner-bottom-actions" class="pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
      <button type="button" onclick="cancelScanView()" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition text-center">
        Cancel / Choose Another File
      </button>

      <div id="scanner-footer-actions" class="w-full sm:w-auto flex justify-end">
        <button type="button" id="btn-bottom-execute" disabled class="w-full sm:w-auto px-7 py-3 rounded-xl bg-slate-200 text-slate-400 font-extrabold text-xs transition flex items-center justify-center gap-2 cursor-not-allowed">
          <i class="fa-solid fa-clock"></i>
          <span>Verification in Progress...</span>
        </button>
      </div>
    </div>

  </div>

  <!-- ================= INFORMATION & INSTRUCTIONS CARD ================= -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="apple-card p-5 bg-white border border-slate-200 shadow-2xs space-y-2">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
        <i class="fa-solid fa-laptop-medical"></i>
      </div>
      <h4 class="font-extrabold text-sm text-slate-900">New Device Migration</h4>
      <p class="text-xs text-slate-500 leading-relaxed">
        Setting up the hospital system on a new PC or server? Simply take a backup on the old machine, copy the <code class="text-emerald-700 font-mono font-bold">.sql</code> file, and upload it here.
      </p>
    </div>

    <div class="apple-card p-5 bg-white border border-slate-200 shadow-2xs space-y-2">
      <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <h4 class="font-extrabold text-sm text-slate-900">Pre-Flight Schema Scan</h4>
      <p class="text-xs text-slate-500 leading-relaxed">
        Our scanner checks all 16 tables and fields before running any queries. Incompatible dumps or outdated backups are blocked to guarantee data integrity.
      </p>
    </div>

    <div class="apple-card p-5 bg-white border border-slate-200 shadow-2xs space-y-2">
      <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
        <i class="fa-solid fa-images"></i>
      </div>
      <h4 class="font-extrabold text-sm text-slate-900">Binary Image & File Safe</h4>
      <p class="text-xs text-slate-500 leading-relaxed">
        Patient files, photos, lab reports, and prescriptions stored as binary BLOBs are fully preserved and restored seamlessly across devices without missing file paths.
      </p>
    </div>
  </div>

</div>

<!-- ================= CELEBRATION MODAL: RESTORE SUCCESS ================= -->
<div id="modal-restore-success" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md overflow-y-auto">
  <div class="apple-card max-w-md w-full p-6 sm:p-8 relative my-6 shadow-2xl border border-slate-200 text-center z-[160] overflow-hidden">

    <button onclick="closeSuccessModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <!-- Success pop animation container -->
    <div class="py-2 space-y-5 animate-success-pop">
      
      <!-- SVG Animated Checkmark Graphic -->
      <div class="relative w-24 h-24 mx-auto animate-pulse-glow rounded-full">
        <svg class="w-24 h-24" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="44" fill="none" stroke="#d1fae5" stroke-width="6" />
          <circle class="animate-checkmark-circle" cx="50" cy="50" r="44" fill="none" stroke="#10b981" stroke-width="6" stroke-linecap="round" />
          <path class="animate-checkmark-check" fill="none" stroke="#10b981" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" d="M30 52 L43 65 L70 36" />
        </svg>
      </div>

      <!-- Headline -->
      <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black uppercase tracking-wider mb-1.5">
          <i class="fa-solid fa-circle-check"></i>
          <span>Data Loaded Successfully</span>
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Database Restored!</h3>
        <p class="text-xs text-slate-500 mt-1">All tables, clinical patient records, and binary files are active in hospital_db.</p>
      </div>

      <!-- Summary Pill Card -->
      <div class="bg-gradient-to-br from-slate-50 to-emerald-50/40 p-4 rounded-2xl border border-emerald-100 text-left space-y-2.5 text-xs">
        <div class="flex items-center justify-between gap-2 pb-2 border-b border-emerald-100">
          <span class="text-slate-400 font-semibold flex items-center gap-1.5">
            <i class="fa-solid fa-file-code text-emerald-600"></i> Loaded File:
          </span>
          <span class="font-mono font-bold text-slate-800 truncate max-w-[200px]" id="restore-success-filename">-</span>
        </div>

        <div class="grid grid-cols-3 gap-2 pt-1 text-center">
          <div class="bg-white p-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Tables</span>
            <span class="font-mono font-black text-emerald-700 text-sm" id="restore-success-tables">16</span>
          </div>
          <div class="bg-white p-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Records</span>
            <span class="font-mono font-black text-blue-700 text-sm" id="restore-success-records">-</span>
          </div>
          <div class="bg-white p-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Duration</span>
            <span class="font-mono font-black text-indigo-700 text-sm" id="restore-success-time">&lt; 1s</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2 pt-1">
        <a href="dashboard.php" class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-extrabold rounded-xl shadow-lg shadow-blue-500/25 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 text-xs">
          <i class="fa-solid fa-gauge-high"></i>
          <span>Go to Hospital Dashboard</span>
        </a>

        <div class="grid grid-cols-2 gap-2">
          <a href="patients.php" class="py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-users text-blue-600"></i>
            <span>View Patients</span>
          </a>
          <button onclick="closeSuccessModal()" class="py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-check text-emerald-600"></i>
            <span>Stay on Page</span>
          </button>
        </div>
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
  let backupFolderPath = 'C:\\xampp\\htdocs\\Hospital Management System\\backups';
  let selectedUploadSqlFile = null;
  let currentScanData = null;
  let scanAnimationTimer = null;
  let confettiAnimationId = null;

  const EXPECTED_HOSPITAL_TABLES = [
    'appointments', 'beds', 'departments', 'diagnoses', 
    'doctor_categories', 'doctor_day_schedules', 'doctor_slots', 'doctors', 
    'hospitals', 'patient_files', 'patients', 'prescriptions', 
    'staff', 'staff_attendance', 'system_state', 'timeline_events'
  ];

  document.addEventListener('DOMContentLoaded', () => {
    fetchRestoreInitialData();
    setupDropzone();

    // Check if URL has ?file= query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const paramFile = urlParams.get('file');
    if (paramFile) {
      setTimeout(() => {
        const sel = document.getElementById('select-restore-snapshot');
        if (sel) {
          sel.value = paramFile;
          startScanSelectedSnapshot();
        }
      }, 400);
    }
  });

  // Fetch Backups List & Summary
  async function fetchRestoreInitialData() {
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
        document.getElementById('kpi-available-snapshots').textContent = summ.total_backups || 0;
        document.getElementById('kpi-target-db').textContent = summ.database_name || 'hospital_db';

        // Populate Snapshot dropdown
        const sel = document.getElementById('select-restore-snapshot');
        if (sel) {
          const curVal = sel.value;
          sel.innerHTML = '<option value="">-- Choose a backup snapshot --</option>' + 
            backupsList.map(b => `<option value="${escapeHtml(b.filename)}">${escapeHtml(b.filename)} (${b.size_formatted} &bull; ${b.created_at})</option>`).join('');
          if (curVal) sel.value = curVal;
        }
      }
    } catch (err) {
      console.error(err);
      showToast('Error', 'Could not load snapshots list', 'error');
    } finally {
      if (icon) icon.classList.remove('fa-spin');
    }
  }

  // Setup Dropzone Drag and Drop
  function setupDropzone() {
    const dropzone = document.getElementById('sql-dropzone');
    if (!dropzone) return;

    ['dragenter', 'dragover'].forEach(name => {
      dropzone.addEventListener(name, (e) => {
        e.preventDefault();
        dropzone.classList.add('border-emerald-500', 'bg-emerald-50/50');
      });
    });

    ['dragleave', 'drop'].forEach(name => {
      dropzone.addEventListener(name, (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-emerald-500', 'bg-emerald-50/50');
      });
    });

    dropzone.addEventListener('drop', (e) => {
      const files = e.dataTransfer.files;
      if (files && files.length) {
        handleSqlFileSelected(files);
      }
    });
  }

  function handleSqlFileSelected(files) {
    if (!files || !files.length) return;
    const file = files[0];
    if (!file.name.toLowerCase().endsWith('.sql')) {
      showToast('Invalid File', 'Please select a valid MySQL database backup ending in .sql', 'error');
      return;
    }

    selectedUploadSqlFile = file;
    document.getElementById('dropzone-text').innerHTML = `<span class="text-emerald-700 font-extrabold truncate block">${escapeHtml(file.name)}</span>`;
    document.getElementById('dropzone-subtext').textContent = `File ready (${(file.size / 1024 / 1024).toFixed(2)} MB). Click "Upload & Scan" to verify.`;

    const btn = document.getElementById('btn-upload-and-scan');
    btn.disabled = false;
    btn.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
    btn.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-teal-600', 'hover:from-emerald-700', 'hover:to-teal-700', 'text-white', 'shadow-md', 'shadow-emerald-600/25', 'cursor-pointer');
  }

  function handleSelectSnapshotChanged() {
    const sel = document.getElementById('select-restore-snapshot');
    const btn = document.getElementById('btn-server-scan');
    if (!sel || !btn) return;
  }

  function startScanSelectedSnapshot() {
    const sel = document.getElementById('select-restore-snapshot');
    const filename = sel ? sel.value.trim() : '';
    if (!filename) {
      showToast('Select Snapshot', 'Please choose a database backup file from the list first.', 'warning');
      return;
    }
    initiateServerFileScan(filename);
  }

  // Trigger Upload and Scan from Dropzone
  async function triggerUploadAndScan() {
    if (!selectedUploadSqlFile) {
      showToast('Select File', 'Please select or drop a .sql file first.', 'warning');
      return;
    }

    const btn = document.getElementById('btn-upload-and-scan');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Uploading & Scanning...</span>';

    showScannerSection(selectedUploadSqlFile.name, (selectedUploadSqlFile.size / 1024 / 1024).toFixed(2) + ' MB');

    try {
      const formData = new FormData();
      formData.append('sql_file', selectedUploadSqlFile);

      const res = await fetch('api/backup.php?action=upload_sql_file', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.status === 'success' && data.data) {
        runTableScannerAnimation(data.data);
        fetchRestoreInitialData();
      } else {
        showScannerError(data.message || 'Upload failed or incompatible SQL file.');
      }
    } catch (err) {
      console.error(err);
      showScannerError('Error uploading SQL backup file to server.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-upload"></i><span>Upload & Scan Compatibility</span>';
    }
  }

  // Initiate Scan on Server File
  async function initiateServerFileScan(filename) {
    if (!filename) return;

    const bMeta = backupsList.find(b => b.filename === filename);
    const sizeStr = bMeta ? bMeta.size_formatted : '';

    showScannerSection(filename, sizeStr);

    try {
      const res = await fetch(`api/backup.php?action=scan_sql_file&file=${encodeURIComponent(filename)}`);
      const data = await res.json();

      if (data.status === 'success' && data.data) {
        runTableScannerAnimation(data.data);
      } else {
        showScannerError(data.message || 'Failed to scan backup file schema.');
      }
    } catch (err) {
      console.error(err);
      showScannerError('Error communicating with schema scanner API.');
    }
  }

  // Show Scanner Section & Reset States
  function showScannerSection(filename, sizeStr) {
    currentScanData = null;
    if (scanAnimationTimer) clearTimeout(scanAnimationTimer);

    const section = document.getElementById('section-scanner');
    section.classList.remove('hidden');
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });

    document.getElementById('scanner-selected-filename').textContent = `${filename} ${sizeStr ? '(' + sizeStr + ')' : ''}`;

    // Reset Top Status Icon
    const topIconBox = document.getElementById('scanner-status-icon-box');
    topIconBox.className = 'w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold shadow-2xs shrink-0';
    document.getElementById('scanner-status-icon').className = 'fa-solid fa-radar fa-spin';
    document.getElementById('scanner-verdict-badge').className = 'text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 uppercase tracking-wider';
    document.getElementById('scanner-verdict-badge').textContent = 'Scanning';

    // Reset Banner
    const banner = document.getElementById('scanner-state-banner');
    banner.className = 'p-4 sm:p-5 rounded-2xl border transition-all duration-300 bg-blue-50/70 border-blue-200 text-blue-900 flex items-start gap-3.5 shadow-2xs';
    document.getElementById('scanner-state-icon').className = 'w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-base shrink-0 mt-0.5';
    document.getElementById('scanner-state-icon').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
    document.getElementById('scanner-state-title').textContent = 'Analyzing Database Schema & Table Fields...';
    document.getElementById('scanner-state-desc').textContent = 'Checking table definitions, column types, and data batches against active hospital_db.';

    // Reset Progress Bar
    document.getElementById('scanner-progress-section').classList.remove('hidden');
    document.getElementById('scanner-progress-fill').style.width = '5%';
    document.getElementById('scanner-progress-label').innerHTML = '<i class="fa-solid fa-magnifying-glass text-blue-500"></i><span>Initializing schema scanner...</span>';
    document.getElementById('scanner-progress-percent').textContent = '5%';
    document.getElementById('scanner-count-badge').textContent = '0 / 16 Checked';

    // Populate Initial Queued Table Cards
    const grid = document.getElementById('scanner-tables-grid');
    grid.innerHTML = EXPECTED_HOSPITAL_TABLES.map(t => `
      <div id="sc-card-${t}" class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between transition-all duration-300 shadow-2xs">
        <div class="flex items-center gap-2.5 min-w-0 pr-2">
          <div id="sc-icon-${t}" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center text-xs shrink-0 shadow-2xs">
            <i class="fa-regular fa-clock"></i>
          </div>
          <div class="truncate">
            <span class="font-mono font-bold text-slate-800 text-xs block truncate">${t}</span>
            <span id="sc-sub-${t}" class="text-[10px] text-slate-400 font-medium block truncate">Pending check</span>
          </div>
        </div>
        <span id="sc-badge-${t}" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200/70 text-slate-500 shrink-0">Queued</span>
      </div>
    `).join('');

    // Reset state boxes
    document.getElementById('scanner-issues-box').classList.add('hidden');
    document.getElementById('scanner-success-box').classList.add('hidden');
    document.getElementById('scanner-executing-box').classList.add('hidden');
    document.getElementById('scanner-grid-container').classList.remove('hidden');
    document.getElementById('scanner-bottom-actions').classList.remove('hidden');

    // Reset Buttons
    updateScannerActionButtons('scanning');
  }

  function cancelScanView() {
    if (scanAnimationTimer) clearTimeout(scanAnimationTimer);
    document.getElementById('section-scanner').classList.add('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Table-by-Table Sequential Scanning Animation
  function runTableScannerAnimation(scanData) {
    currentScanData = scanData;
    const checks = scanData.table_checks || [];
    const checkMap = {};
    checks.forEach(c => { checkMap[c.table] = c; });

    let currentIndex = 0;
    const total = EXPECTED_HOSPITAL_TABLES.length;

    function stepNextTable() {
      if (currentIndex < total) {
        const tbl = EXPECTED_HOSPITAL_TABLES[currentIndex];
        const card = document.getElementById(`sc-card-${tbl}`);
        const icon = document.getElementById(`sc-icon-${tbl}`);
        const sub = document.getElementById(`sc-sub-${tbl}`);
        const badge = document.getElementById(`sc-badge-${tbl}`);

        // Update Progress Bar
        const pct = Math.round(((currentIndex + 1) / total) * 100);
        document.getElementById('scanner-progress-fill').style.width = `${pct}%`;
        document.getElementById('scanner-progress-percent').textContent = `${pct}%`;
        document.getElementById('scanner-progress-label').innerHTML = `<i class="fa-solid fa-magnifying-glass text-blue-500 animate-bounce"></i><span>Verifying table ${currentIndex + 1}/${total}: <strong class="font-mono text-slate-800">${tbl}</strong>...</span>`;
        document.getElementById('scanner-count-badge').textContent = `${currentIndex + 1} / ${total} Checked`;

        const chk = checkMap[tbl];
        if (chk && chk.status === 'matched') {
          if (card) {
            card.className = 'p-3 rounded-xl border border-emerald-200 bg-emerald-50/30 flex items-center justify-between transition-all duration-300 shadow-2xs transform hover:scale-[1.01]';
          }
          if (icon) {
            icon.className = 'w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shrink-0 shadow-2xs font-bold';
            icon.innerHTML = '<i class="fa-solid fa-check"></i>';
          }
          if (sub) {
            sub.className = 'text-[10px] text-emerald-700 font-semibold block truncate';
            sub.textContent = `${chk.columns_found}/${chk.columns_expected} fields • ${chk.records_count} rows`;
          }
          if (badge) {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 shrink-0';
            badge.textContent = 'Matched';
          }
        } else {
          if (card) {
            card.className = 'p-3 rounded-xl border-2 border-rose-300 bg-rose-50/50 flex items-center justify-between transition-all duration-300 shadow-2xs';
          }
          if (icon) {
            icon.className = 'w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs shrink-0 shadow-2xs font-bold';
            icon.innerHTML = '<i class="fa-solid fa-xmark"></i>';
          }
          if (sub) {
            sub.className = 'text-[10px] text-rose-700 font-bold block truncate';
            sub.textContent = chk ? chk.message : 'Missing table definition';
          }
          if (badge) {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 shrink-0';
            badge.textContent = 'Mismatch';
          }
        }

        currentIndex++;
        scanAnimationTimer = setTimeout(stepNextTable, 85);
      } else {
        finishScanVerdict(scanData);
      }
    }

    stepNextTable();
  }

  // Scan Completed Verdict
  function finishScanVerdict(scanData) {
    const banner = document.getElementById('scanner-state-banner');
    const bannerIcon = document.getElementById('scanner-state-icon');
    const bannerTitle = document.getElementById('scanner-state-title');
    const bannerDesc = document.getElementById('scanner-state-desc');
    const topIconBox = document.getElementById('scanner-status-icon-box');
    const topBadge = document.getElementById('scanner-verdict-badge');

    if (!scanData.valid) {
      // Incompatible Schema
      topIconBox.className = 'w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl font-bold shadow-2xs shrink-0';
      document.getElementById('scanner-status-icon').className = 'fa-solid fa-triangle-exclamation';
      topBadge.className = 'text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 uppercase tracking-wider';
      topBadge.textContent = 'Blocked';

      banner.className = 'p-4 sm:p-5 rounded-2xl border-2 transition-all duration-300 bg-rose-50 border-rose-300 text-rose-900 flex items-start gap-3.5 shadow-xs';
      bannerIcon.className = 'w-9 h-9 rounded-xl bg-rose-200 text-rose-800 flex items-center justify-center text-base shrink-0 mt-0.5';
      bannerIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
      bannerTitle.className = 'font-black text-sm text-rose-900';
      bannerTitle.textContent = 'Schema Incompatibility Warning — Data Restore Blocked';
      bannerDesc.className = 'text-xs text-rose-700 mt-0.5';
      bannerDesc.textContent = 'This backup file is missing required tables or fields. Restoring is blocked to protect database integrity.';

      const issuesBox = document.getElementById('scanner-issues-box');
      const issuesList = document.getElementById('scanner-issues-list');
      const allErrors = (scanData.fatal_errors || []).concat(scanData.warnings || []);
      issuesList.innerHTML = allErrors.map(e => `<li>${escapeHtml(e)}</li>`).join('');
      issuesBox.classList.remove('hidden');

      updateScannerActionButtons('blocked');
      showToast('Schema Mismatch', 'Backup file does not match active database schema. Restore blocked.', 'error');
    } else {
      // 100% Matched
      topIconBox.className = 'w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold shadow-2xs shrink-0';
      document.getElementById('scanner-status-icon').className = 'fa-solid fa-circle-check';
      topBadge.className = 'text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider';
      topBadge.textContent = '100% Verified';

      banner.className = 'p-4 sm:p-5 rounded-2xl border-2 transition-all duration-300 bg-emerald-50 border-emerald-300 text-emerald-900 flex items-start gap-3.5 shadow-xs';
      bannerIcon.className = 'w-9 h-9 rounded-xl bg-emerald-200 text-emerald-800 flex items-center justify-center text-base shrink-0 mt-0.5';
      bannerIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
      bannerTitle.className = 'font-black text-sm text-emerald-900';
      bannerTitle.textContent = '100% Compatibility Verified — Safe to Load';
      bannerDesc.className = 'text-xs text-emerald-700 mt-0.5';
      bannerDesc.textContent = `All 16 tables and fields match hospital_db. Found ${scanData.total_records || 0} clinical records ready to load.`;

      document.getElementById('success-tables-count').textContent = scanData.tables_found || 16;
      document.getElementById('success-records-count').textContent = scanData.total_records || 0;
      document.getElementById('scanner-success-box').classList.remove('hidden');

      updateScannerActionButtons('ready', scanData.file_name);
      showToast('Scan Complete', 'All 16 tables and fields verified 100% compatible!', 'success');
    }
  }

  function showScannerError(msg) {
    const banner = document.getElementById('scanner-state-banner');
    banner.className = 'p-4 sm:p-5 rounded-2xl border bg-rose-50 border-rose-300 text-rose-900 flex items-start gap-3.5';
    document.getElementById('scanner-state-icon').className = 'w-9 h-9 rounded-xl bg-rose-200 text-rose-800 flex items-center justify-center text-base shrink-0 mt-0.5';
    document.getElementById('scanner-state-icon').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
    document.getElementById('scanner-state-title').textContent = 'Error Scanning SQL File';
    document.getElementById('scanner-state-desc').textContent = msg;

    const issuesBox = document.getElementById('scanner-issues-box');
    const issuesList = document.getElementById('scanner-issues-list');
    issuesList.innerHTML = `<li>${escapeHtml(msg)}</li>`;
    issuesBox.classList.remove('hidden');

    updateScannerActionButtons('blocked');
    showToast('Error', msg, 'error');
  }

  function updateScannerActionButtons(state, filename = '') {
    const topContainer = document.getElementById('scanner-header-actions');
    const bottomContainer = document.getElementById('scanner-footer-actions');

    if (state === 'scanning') {
      const scanningBtn = `
        <button type="button" disabled class="px-6 py-3 rounded-xl bg-slate-200 text-slate-400 font-extrabold text-xs transition flex items-center justify-center gap-2 cursor-not-allowed">
          <i class="fa-solid fa-clock"></i>
          <span>Verification in Progress...</span>
        </button>
      `;
      topContainer.innerHTML = scanningBtn;
      bottomContainer.innerHTML = scanningBtn;
    } else if (state === 'blocked') {
      const blockedBtn = `
        <button type="button" disabled class="px-6 py-3 rounded-xl bg-rose-100 text-rose-500 font-extrabold text-xs transition flex items-center justify-center gap-2 cursor-not-allowed">
          <i class="fa-solid fa-ban"></i>
          <span>Restore Blocked (Schema Mismatch)</span>
        </button>
      `;
      topContainer.innerHTML = blockedBtn;
      bottomContainer.innerHTML = blockedBtn;
    } else if (state === 'ready') {
      const readyBtn = `
        <button type="button" onclick="confirmExecuteRestore('${escapeHtml(filename)}')" class="px-7 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 text-white font-black text-xs shadow-lg shadow-emerald-600/30 transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
          <i class="fa-solid fa-rotate-left text-sm"></i>
          <span>Confirm & Load Database Now</span>
        </button>
      `;
      topContainer.innerHTML = readyBtn;
      bottomContainer.innerHTML = readyBtn;
    }
  }

  // Execute Database Restore
  async function confirmExecuteRestore(filename) {
    if (!confirm(`Warning: Restoring "${filename}" will replace current tables and patient records in hospital_db with this snapshot.\n\nDo you want to proceed?`)) {
      return;
    }

    // Hide grids and show executing state
    document.getElementById('scanner-grid-container').classList.add('hidden');
    document.getElementById('scanner-progress-section').classList.add('hidden');
    document.getElementById('scanner-success-box').classList.add('hidden');
    document.getElementById('scanner-issues-box').classList.add('hidden');
    document.getElementById('scanner-state-banner').classList.add('hidden');
    document.getElementById('scanner-bottom-actions').classList.add('hidden');
    document.getElementById('scanner-executing-box').classList.remove('hidden');

    const executingBtn = `
      <button type="button" disabled class="px-6 py-3 rounded-xl bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center gap-2">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
        <span>Restoring database...</span>
      </button>
    `;
    document.getElementById('scanner-header-actions').innerHTML = executingBtn;

    const t1 = setTimeout(() => {
      document.getElementById('executing-step-text').textContent = 'Disabling foreign key constraints & preparing 16 tables...';
    }, 250);
    const t2 = setTimeout(() => {
      document.getElementById('executing-step-text').textContent = 'Loading schemas, binary files, and clinical patient records...';
    }, 600);

    try {
      const res = await fetch('api/backup.php?action=restore_backup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ filename: filename })
      });
      const data = await res.json();

      clearTimeout(t1);
      clearTimeout(t2);

      if (data.status === 'success') {
        document.getElementById('section-scanner').classList.add('hidden');
        triggerRestoreSuccessCelebration(data.data || {}, filename);
      } else {
        alert('Restore Failed: ' + (data.message || 'Unknown database error'));
        cancelScanView();
      }
    } catch (err) {
      console.error(err);
      alert('Error during database restore execution.');
      cancelScanView();
    }
  }

  // Restore Success Celebration & Confetti
  function triggerRestoreSuccessCelebration(data, filename) {
    const modal = document.getElementById('modal-restore-success');
    document.getElementById('restore-success-filename').textContent = data.file_name || filename || 'Database Snapshot';
    document.getElementById('restore-success-tables').textContent = data.tables_restored || '16';
    document.getElementById('restore-success-records').textContent = data.total_records_restored || '113';
    document.getElementById('restore-success-time').textContent = data.duration || '< 1s';

    launchConfetti();
    modal.classList.remove('hidden');
    showToast('Success', 'Database restored successfully!', 'success');
  }

  function closeSuccessModal() {
    document.getElementById('modal-restore-success').classList.add('hidden');
    stopConfetti();
  }

  // Confetti Particle Explosion
  function launchConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ['#10b981', '#3b82f6', '#6366f1', '#f59e0b', '#ec4899', '#14b8a6', '#8b5cf6'];
    const particles = [];
    const count = 130;

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

  // Windows File Explorer Folder Helpers
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
        showToast('File Explorer', filename ? `File Explorer focused on "${filename}"` : 'Backup folder opened in PC File Explorer!', 'success');
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

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
</script>

<?php include 'includes/footer.php'; ?>

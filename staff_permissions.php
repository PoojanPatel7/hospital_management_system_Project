<?php
require_once 'db.php';
require_once 'auth.php';

// Only admins can access this page
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'staff') {
    header("Location: dashboard.php");
    exit;
}

$pageTitle = "Staff Accounts & Access Management";
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-16 space-y-6">
  
  <!-- ================= TOP HERO / HEADER BAR ================= -->
  <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-[2rem] shadow-xl border border-indigo-500/30 overflow-hidden relative p-6 sm:p-8 text-white">
    <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
      <div class="space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-xs font-bold uppercase tracking-wider">
          <i class="fa-solid fa-user-shield"></i> Security &amp; Access Control
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Staff Accounts &amp; Permissions</h1>
        <p class="text-xs sm:text-sm text-indigo-200/90 max-w-2xl font-medium">
          Manage staff login accounts, view login credentials (ID &amp; Password), configure custom permissions (Upload-Only, View-Only), and instantly block or activate accounts.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3 shrink-0 flex-wrap">
        <a href="staff.php" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm transition flex items-center gap-2 border border-white/20 shadow-sm" title="Go to Staff Directory">
          <i class="fa-solid fa-id-card-clip text-indigo-300"></i>
          <span>Staff Directory</span>
        </a>
        <button onclick="openNewStaffUserModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
          <i class="fa-solid fa-user-plus"></i>
          <span>New Staff Account</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ================= KPI STATS CARDS ================= -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Staff Accounts -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Staff Accounts</p>
        <h3 id="stat-total-staff" class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">--</h3>
        <p class="text-[11px] text-slate-500 font-medium mt-0.5"><span id="stat-portal-users" class="font-bold text-indigo-600">--</span> with portal login</p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-users"></i>
      </div>
    </div>

    <!-- Active Accounts -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Active Logins</p>
        <h3 id="stat-active-staff" class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">--</h3>
        <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Can login to system</p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-circle-check"></i>
      </div>
    </div>

    <!-- Upload-Only Restricted Staff -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">Upload-Only Staff</p>
        <h3 id="stat-upload-only" class="text-2xl sm:text-3xl font-black text-amber-700 mt-1">--</h3>
        <p class="text-[11px] text-amber-700 font-medium mt-0.5">Cannot view patient files</p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-cloud-arrow-up"></i>
      </div>
    </div>

    <!-- Blocked Accounts -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Blocked / Suspended</p>
        <h3 id="stat-blocked-staff" class="text-2xl sm:text-3xl font-black text-rose-700 mt-1">--</h3>
        <p class="text-[11px] text-rose-600 font-medium mt-0.5">Access prohibited</p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
        <i class="fa-solid fa-user-slash"></i>
      </div>
    </div>
  </div>

  <!-- ================= SEARCH & FILTER CONTROLS ================= -->
  <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
    <!-- Search Bar -->
    <div class="relative w-full md:w-80">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input 
        type="text" 
        id="search-staff-input" 
        oninput="filterStaffCards()" 
        placeholder="Search staff, code, username..." 
        class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 outline-none transition"
      />
    </div>

    <!-- Filter Dropdowns -->
    <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
      <!-- Role Filter -->
      <select id="filter-role" onchange="filterStaffCards()" class="px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 bg-white text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/30">
        <option value="">All Roles</option>
        <option value="Nurse">Nurses</option>
        <option value="Technician">Lab / Radiology</option>
        <option value="Receptionist">Receptionists</option>
        <option value="Pharmacist">Pharmacists</option>
        <option value="Doctor">Doctors</option>
      </select>

      <!-- Status Filter -->
      <select id="filter-status" onchange="filterStaffCards()" class="px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 bg-white text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/30">
        <option value="">All Statuses</option>
        <option value="Active">Active Only</option>
        <option value="Blocked">Blocked Only</option>
        <option value="Suspended">Suspended Only</option>
      </select>

      <!-- Access Type Filter -->
      <select id="filter-access" onchange="filterStaffCards()" class="px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 bg-white text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/30">
        <option value="">All Access Types</option>
        <option value="upload_only">Upload-Only (Restricted)</option>
        <option value="view_only">View-Only</option>
        <option value="full_access">Full Access</option>
      </select>

      <button onclick="loadAllStaffAccounts()" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition" title="Refresh">
        <i class="fa-solid fa-arrows-rotate"></i>
      </button>
    </div>
  </div>

  <!-- ================= STAFF ACCOUNTS GRID ================= -->
  <div id="staff-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <!-- Skeleton loader -->
    <div class="animate-pulse bg-white rounded-3xl p-6 border border-slate-200 h-80"></div>
    <div class="animate-pulse bg-white rounded-3xl p-6 border border-slate-200 h-80"></div>
    <div class="animate-pulse bg-white rounded-3xl p-6 border border-slate-200 h-80"></div>
  </div>

</div>

<!-- ================= QUICK CREDENTIAL EDIT MODAL ================= -->
<div id="modal-edit-credentials" class="hidden fixed inset-0 z-[140] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 relative shadow-2xl border border-slate-200">
    <button onclick="closeEditCredentialsModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    
    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
      <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
        <i class="fa-solid fa-key"></i>
      </div>
      <div>
        <h3 class="text-base font-black text-slate-900">Manage Login Credentials</h3>
        <p id="cred-modal-staff-name" class="text-xs text-slate-500 font-medium">Staff Member</p>
      </div>
    </div>

    <form id="form-edit-credentials" onsubmit="saveStaffCredentials(event)" class="space-y-4">
      <input type="hidden" id="cred-staff-id" value="">
      
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Username / Login ID *</label>
        <div class="relative">
          <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input type="text" id="cred-username" required class="w-full pl-9 pr-3 py-2.5 text-xs font-mono font-medium rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/30 outline-none">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
        <div class="relative">
          <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input type="text" id="cred-password" required placeholder="Leave unchanged or enter new" class="w-full pl-9 pr-20 py-2.5 text-xs font-mono font-bold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/30 outline-none">
          <button type="button" onclick="generateRandomPassword()" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-lg transition">
            Generate
          </button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Account Login Status</label>
        <select id="cred-status" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500/30 outline-none">
          <option value="Active">Active (Can log in)</option>
          <option value="Suspended">Suspended (Temporarily restricted)</option>
          <option value="Blocked">Blocked (Login completely disabled)</option>
        </select>
      </div>

      <div class="pt-2 flex gap-2">
        <button type="button" onclick="closeEditCredentialsModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
          Cancel
        </button>
        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-floppy-disk"></i> Save Credentials
        </button>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/permissions_modal.php'; ?>

<script src="js/permissions.js"></script>
<script>
let allStaffAccounts = [];

async function loadAllStaffAccounts() {
    try {
        const res = await fetch('api/permissions.php?action=get_all_staff_permissions');
        const data = await res.json();
        
        if (data.status === 'success') {
            allStaffAccounts = data.data || [];
            updateKPICards(allStaffAccounts);
            renderStaffCards(allStaffAccounts);
        } else {
            console.error('Error fetching staff permissions:', data.message);
        }
    } catch (err) {
        console.error('Failed to load staff accounts:', err);
    }
}

function updateKPICards(list) {
    document.getElementById('stat-total-staff').textContent = list.length;
    const portalUsers = list.filter(s => parseInt(s.is_user) === 1);
    document.getElementById('stat-portal-users').textContent = `${portalUsers.length} active logins`;

    const activeCount = list.filter(s => (s.account_status || 'Active').toLowerCase() === 'active').length;
    document.getElementById('stat-active-staff').textContent = activeCount;

    // Upload only = can_upload === 1 and can_view_files === 0
    const uploadOnlyCount = list.filter(s => {
        const p = s.permissions || {};
        return parseInt(p.can_upload || 0) === 1 && parseInt(p.can_view_files || 0) === 0;
    }).length;
    document.getElementById('stat-upload-only').textContent = uploadOnlyCount;

    const blockedCount = list.filter(s => (s.account_status || '').toLowerCase() === 'blocked' || (s.account_status || '').toLowerCase() === 'suspended').length;
    document.getElementById('stat-blocked-staff').textContent = blockedCount;
}

function renderStaffCards(list) {
    const grid = document.getElementById('staff-cards-grid');
    if (!list.length) {
        grid.innerHTML = `
            <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-slate-200">
                <i class="fa-solid fa-user-slash text-4xl text-slate-300 mb-3"></i>
                <p class="text-sm font-bold text-slate-600">No staff members found matching criteria.</p>
            </div>
        `;
        return;
    }

    let html = '';
    list.forEach(s => {
        const initials = ((s.first_name ? s.first_name[0] : 'S') + (s.last_name ? s.last_name[0] : 'T')).toUpperCase();
        const p = s.permissions || {};
        const isBlocked = (s.account_status || '').toLowerCase() === 'blocked';
        const isSuspended = (s.account_status || '').toLowerCase() === 'suspended';
        
        // Status Badge
        let statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>';
        if (isBlocked) {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Blocked</span>';
        } else if (isSuspended) {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Suspended</span>';
        }

        // Credentials
        const username = s.username || 'No login set';
        const plainPwd = s.plain_password || 'staff123';
        const hasLogin = parseInt(s.is_user) === 1;

        // Permission Badges
        const canUpload = parseInt(p.can_upload || 0) === 1;
        const canView = parseInt(p.can_view_files || 0) === 1;
        const canTokens = parseInt(p.can_assign_tokens || 0) === 1;
        const canQR = parseInt(p.can_generate_qr || 0) === 1;
        const canPDF = parseInt(p.can_download_pdf || 0) === 1;
        const canConsult = parseInt(p.can_consult_online || 0) === 1;

        html += `
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden">
            <!-- Box Header -->
            <div class="p-5 pb-3 border-b border-slate-100">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-slate-900 text-white font-black flex items-center justify-center text-sm shadow-md shrink-0">
                            ${initials}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-extrabold text-slate-900 text-sm truncate" title="${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}">
                                ${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}
                            </h4>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="font-mono text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded">${escapeHtml(s.staff_code)}</span>
                                <span class="text-[10px] font-medium text-slate-500 truncate max-w-[120px]">${escapeHtml(s.role)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0">
                        ${statusBadge}
                    </div>
                </div>
            </div>

            <!-- Credentials Box (ID & Password) -->
            <div class="p-5 space-y-4 flex-1">
                <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/70 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-key text-indigo-500"></i> Portal Login Access
                        </span>
                        <button onclick="openEditCredentialsModal(${s.id}, '${escapeJs(s.first_name + ' ' + s.last_name)}', '${escapeJs(s.username || '')}', '${escapeJs(plainPwd)}', '${escapeJs(s.account_status || 'Active')}')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                            <i class="fa-solid fa-pen"></i> Edit Login
                        </button>
                    </div>

                    <!-- ID / Username -->
                    <div class="flex items-center justify-between bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 font-semibold text-[11px]">Login ID:</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-slate-900">${escapeHtml(username)}</span>
                            <button onclick="copyToClipboard('${escapeJs(username)}')" class="text-slate-400 hover:text-slate-600 transition" title="Copy Username">
                                <i class="fa-regular fa-copy text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password with Eye Toggle -->
                    <div class="flex items-center justify-between bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 font-semibold text-[11px]">Password:</span>
                        <div class="flex items-center gap-2">
                            <span id="pwd-display-${s.id}" class="font-mono font-bold text-slate-900">••••••••</span>
                            <button onclick="togglePasswordVisibility(${s.id}, '${escapeJs(plainPwd)}')" class="text-slate-400 hover:text-slate-600 transition" title="Show / Hide Password">
                                <i id="pwd-icon-${s.id}" class="fa-regular fa-eye text-xs"></i>
                            </button>
                            <button onclick="copyToClipboard('${escapeJs(plainPwd)}')" class="text-slate-400 hover:text-slate-600 transition" title="Copy Password">
                                <i class="fa-regular fa-copy text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Current Permissions Pills -->
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-2">
                        Active File &amp; Role Permissions
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        ${canUpload ? 
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Scans</span>' : 
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-medium"><i class="fa-solid fa-cloud-arrow-up"></i> No Upload</span>'
                        }
                        
                        ${canView ? 
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold"><i class="fa-solid fa-eye"></i> View Files</span>' : 
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold"><i class="fa-solid fa-eye-slash"></i> View BLOCKED</span>'
                        }

                        ${canTokens ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold"><i class="fa-solid fa-ticket"></i> Assign Tokens</span>' : ''}
                        ${canQR ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold"><i class="fa-solid fa-qrcode"></i> Lifetime QR</span>' : ''}
                        ${canPDF ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold"><i class="fa-solid fa-file-pdf"></i> PDF Export</span>' : ''}
                        ${canConsult ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-teal-50 text-teal-700 border border-teal-200 text-[10px] font-bold"><i class="fa-solid fa-video"></i> Consult</span>' : ''}
                    </div>
                </div>

                <!-- 1-Click Quick Role Presets -->
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1.5">
                        Quick Preset (1-Click)
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <button onclick="applyQuickPreset(${s.id}, 'upload_only')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition" title="Upload files, but CANNOT view any patient files">
                            🚀 Upload Only
                        </button>
                        <button onclick="applyQuickPreset(${s.id}, 'view_only')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            👁️ View Only
                        </button>
                        <button onclick="applyQuickPreset(${s.id}, 'reception')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition">
                            🏥 Reception
                        </button>
                        <button onclick="applyQuickPreset(${s.id}, 'lab_tech')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 transition">
                            🧪 Lab Tech
                        </button>
                        <button onclick="applyQuickPreset(${s.id}, 'doctor')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition">
                            👨‍⚕️ Doctor
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Action Controls -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                <button onclick="openPermissionsModal(${s.id}, '${escapeJs(s.first_name + ' ' + s.last_name)}', '${escapeJs(s.staff_code)}')" class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-sliders"></i> Full Permissions
                </button>
                <button onclick="toggleStaffBlock(${s.id}, '${escapeJs(s.account_status || 'Active')}')" class="py-2 px-3 rounded-xl font-bold text-xs transition flex items-center gap-1.5 ${isBlocked ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'}">
                    ${isBlocked ? '<i class="fa-solid fa-lock-open"></i> Unblock' : '<i class="fa-solid fa-ban"></i> Block'}
                </button>
            </div>
        </div>
        `;
    });

    grid.innerHTML = html;
}

function filterStaffCards() {
    const q = document.getElementById('search-staff-input').value.toLowerCase().trim();
    const roleF = document.getElementById('filter-role').value.toLowerCase();
    const statusF = document.getElementById('filter-status').value.toLowerCase();
    const accessF = document.getElementById('filter-access').value;

    const filtered = allStaffAccounts.filter(s => {
        const name = `${s.first_name || ''} ${s.last_name || ''}`.toLowerCase();
        const code = (s.staff_code || '').toLowerCase();
        const user = (s.username || '').toLowerCase();
        const role = (s.role || '').toLowerCase();
        const dept = (s.department || '').toLowerCase();
        const status = (s.account_status || 'Active').toLowerCase();
        const p = s.permissions || {};

        const matchQ = !q || name.includes(q) || code.includes(q) || user.includes(q) || role.includes(q) || dept.includes(q);
        const matchRole = !roleF || role.includes(roleF);
        const matchStatus = !statusF || status === statusF;

        let matchAccess = true;
        if (accessF === 'upload_only') {
            matchAccess = (parseInt(p.can_upload || 0) === 1 && parseInt(p.can_view_files || 0) === 0);
        } else if (accessF === 'view_only') {
            matchAccess = (parseInt(p.can_view_files || 0) === 1 && parseInt(p.can_upload || 0) === 0);
        } else if (accessF === 'full_access') {
            matchAccess = (parseInt(p.can_upload || 0) === 1 && parseInt(p.can_view_files || 0) === 1);
        }

        return matchQ && matchRole && matchStatus && matchAccess;
    });

    renderStaffCards(filtered);
}

function togglePasswordVisibility(staffId, plainPwd) {
    const span = document.getElementById(`pwd-display-${staffId}`);
    const icon = document.getElementById(`pwd-icon-${staffId}`);
    if (span.textContent === '••••••••') {
        span.textContent = plainPwd;
        icon.className = 'fa-regular fa-eye-slash text-xs text-indigo-600';
    } else {
        span.textContent = '••••••••';
        icon.className = 'fa-regular fa-eye text-xs text-slate-400';
    }
}

function copyToClipboard(text) {
    if (!text || text === 'No login set') {
        alert('No credential set to copy.');
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        alert(`Copied "${text}" to clipboard!`);
    }).catch(() => {
        prompt('Copy text:', text);
    });
}

async function applyQuickPreset(staffId, preset) {
    try {
        const res = await fetch('api/permissions.php?action=quick_preset', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ staff_id: staffId, preset: preset })
        });
        const data = await res.json();
        if (data.status === 'success') {
            loadAllStaffAccounts();
        } else {
            alert(data.message || 'Failed to apply preset.');
        }
    } catch (err) {
        alert('Network error while applying preset.');
    }
}

async function toggleStaffBlock(staffId, currentStatus) {
    const newStatus = (currentStatus.toLowerCase() === 'blocked') ? 'Active' : 'Blocked';
    if (!confirm(`Are you sure you want to change account status to ${newStatus}?`)) return;

    try {
        const res = await fetch('api/permissions.php?action=toggle_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ staff_id: staffId, account_status: newStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
            loadAllStaffAccounts();
        } else {
            alert(data.message || 'Failed to toggle status.');
        }
    } catch (err) {
        alert('Network error while toggling status.');
    }
}

function openEditCredentialsModal(id, name, username, password, status) {
    document.getElementById('cred-staff-id').value = id;
    document.getElementById('cred-modal-staff-name').textContent = name;
    document.getElementById('cred-username').value = (username !== 'No login set') ? username : '';
    document.getElementById('cred-password').value = password || 'staff123';
    document.getElementById('cred-status').value = status || 'Active';

    document.getElementById('modal-edit-credentials').classList.remove('hidden');
}

function closeEditCredentialsModal() {
    document.getElementById('modal-edit-credentials').classList.add('hidden');
}

function generateRandomPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#%';
    let pwd = '';
    for (let i = 0; i < 9; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('cred-password').value = pwd;
}

async function saveStaffCredentials(e) {
    e.preventDefault();
    const id = document.getElementById('cred-staff-id').value;
    const username = document.getElementById('cred-username').value.trim();
    const password = document.getElementById('cred-password').value.trim();
    const status = document.getElementById('cred-status').value;

    try {
        const res = await fetch('api/permissions.php?action=update_credentials', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                staff_id: id,
                username: username,
                password: password,
                account_status: status,
                is_user: 1
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeEditCredentialsModal();
            loadAllStaffAccounts();
        } else {
            alert(data.message || 'Error updating credentials.');
        }
    } catch (err) {
        alert('Network error.');
    }
}

function openNewStaffUserModal() {
    window.location.href = 'staff.php';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function escapeJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

window.addEventListener('DOMContentLoaded', loadAllStaffAccounts);
</script>

<?php include 'includes/footer.php'; ?>

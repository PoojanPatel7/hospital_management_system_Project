<?php
// includes/permissions_modal.php
?>
<!-- ================= MODAL: STAFF PERMISSIONS (ENHANCED MODERN DESIGN) ================= -->
<div id="modal-staff-permissions" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto">
  <div class="apple-card max-w-3xl w-full p-5 sm:p-7 relative my-6 max-h-[92vh] overflow-y-auto flex flex-col justify-between shadow-2xl border border-slate-200/90 bg-white rounded-3xl">
    
    <!-- Close Button -->
    <button type="button" onclick="closePermissionsModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center z-10" title="Close Modal">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <div class="space-y-5">
      
      <!-- Staff Profile Summary Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
        <div class="flex items-center gap-3.5">
          <div id="perm-staff-avatar-wrap" class="w-12 h-12 rounded-2xl overflow-hidden bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center text-lg font-black shadow-md shadow-indigo-500/20 shrink-0 border-2 border-white">
            <img id="perm-staff-avatar-img" src="" class="w-full h-full object-cover hidden" alt="Staff Avatar">
            <span id="perm-staff-avatar-initials"><i class="fa-solid fa-shield-halved"></i></span>
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight" id="perm-staff-name">Staff Member</h3>
              <span id="perm-staff-code" class="font-mono text-[11px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md border border-slate-200">STF-000</span>
              <span id="perm-status-badge" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5" id="perm-staff-role-dept">
              Configure system module access and role-based permissions
            </p>
          </div>
        </div>
      </div>

      <input type="hidden" id="perm-staff-id" value="0">

      <!-- Account Login Status Pill Selector -->
      <div class="bg-gradient-to-r from-slate-50 to-indigo-50/30 p-3.5 rounded-2xl border border-slate-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <label class="block text-xs font-extrabold text-slate-800">Account Login Access</label>
          <p class="text-[11px] text-slate-500 mt-0.5">Controls whether this user account is permitted to sign in to the portal.</p>
        </div>
        
        <div class="flex items-center gap-1.5 bg-white p-1 rounded-xl border border-slate-200 shadow-2xs self-start sm:self-center">
          <button type="button" onclick="setAccountStatus('Active')" id="status-pill-Active" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
          </button>
          <button type="button" onclick="setAccountStatus('Suspended')" id="status-pill-Suspended" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-50">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Suspended
          </button>
          <button type="button" onclick="setAccountStatus('Blocked')" id="status-pill-Blocked" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-50">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Blocked
          </button>
        </div>

        <!-- Hidden select to keep existing backend logic 100% compatible -->
        <select id="perm-account-status" class="hidden">
          <option value="Active">Active</option>
          <option value="Suspended">Suspended</option>
          <option value="Blocked">Blocked</option>
        </select>
      </div>

      <!-- Quick Role Presets Bar -->
      <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/80 space-y-2.5">
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-1.5">
            <i class="fa-solid fa-wand-magic-sparkles text-indigo-600 text-xs"></i>
            <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Quick Role Presets</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" onclick="selectAllPermissions(true)" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
              <i class="fa-solid fa-check-double text-[10px]"></i> Grant All
            </button>
            <span class="text-slate-300">&bull;</span>
            <button type="button" onclick="selectAllPermissions(false)" class="text-[11px] font-bold text-slate-500 hover:text-rose-600 hover:underline flex items-center gap-1">
              <i class="fa-solid fa-rotate-left text-[10px]"></i> Clear All
            </button>
          </div>
        </div>

        <!-- Role Chips -->
        <div class="flex flex-wrap items-center gap-1.5" id="preset-chips-container">
          <button type="button" onclick="setPreset('full_access')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-crown text-amber-500 text-[11px]"></i> Full Admin
          </button>
          <button type="button" onclick="setPreset('doctor')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-blue-50 hover:text-blue-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-user-doctor text-blue-600 text-[11px]"></i> Doctor
          </button>
          <button type="button" onclick="setPreset('reception')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-headset text-emerald-600 text-[11px]"></i> Receptionist
          </button>
          <button type="button" onclick="setPreset('lab_tech')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-cyan-50 hover:text-cyan-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-flask-vial text-cyan-600 text-[11px]"></i> Lab Tech
          </button>
          <button type="button" onclick="setPreset('upload_view')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-sky-50 hover:text-sky-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-folder-open text-sky-600 text-[11px]"></i> Upload &amp; View
          </button>
          <button type="button" onclick="setPreset('view_only')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 hover:text-slate-900 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-eye text-slate-500 text-[11px]"></i> View Only
          </button>
          <button type="button" onclick="setPreset('blocked')" class="preset-chip px-2.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-ban text-rose-500 text-[11px]"></i> Blocked
          </button>
        </div>

        <!-- Hidden select to keep existing backend logic 100% compatible -->
        <select id="perm-preset" class="hidden">
          <option value="">Select a preset...</option>
          <option value="full_access">Full Admin Access</option>
          <option value="reception">Receptionist</option>
          <option value="doctor">Doctor</option>
          <option value="lab_tech">Lab Technician</option>
          <option value="upload_view">Upload & View Files</option>
          <option value="view_only">View Only</option>
          <option value="upload_only">Upload Only</option>
          <option value="blocked">Blocked (No Access)</option>
        </select>
      </div>

      <!-- Categorized Permissions Container -->
      <div>
        <div class="flex items-center justify-between mb-2.5">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
            <i class="fa-solid fa-sliders text-indigo-500"></i> Module Access Matrix
          </h4>
          <span class="text-xs font-bold text-slate-500">
            <span id="perm-active-count" class="font-extrabold text-indigo-600">0</span> of 11 active
          </span>
        </div>

        <div id="permissions-toggles-container" class="space-y-4">
          <!-- Rendered dynamically by js/permissions.js -->
        </div>
      </div>

      <!-- Action Buttons Footer -->
      <div class="flex items-center justify-between pt-3.5 border-t border-slate-100 flex-wrap gap-2">
        <div class="text-[11px] text-slate-400 font-medium hidden sm:block">
          Changes take effect immediately on next user action.
        </div>
        <div class="flex items-center gap-2.5 ml-auto">
          <button type="button" onclick="closePermissionsModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition">
            Cancel
          </button>
          <button type="button" onclick="savePermissions()" id="btn-save-permissions" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md shadow-indigo-500/25 transition flex items-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Save Permissions</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

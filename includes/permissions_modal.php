<?php
// includes/permissions_modal.php
?>
<!-- ================= MODAL: STAFF PERMISSIONS ================= -->
<div id="modal-staff-permissions" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="apple-card max-w-2xl w-full p-5 sm:p-7 relative my-6 max-h-[94vh] overflow-y-auto flex flex-col justify-between shadow-2xl border border-slate-200">
    <button onclick="closePermissionsModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-indigo-500/20 shrink-0">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <h3 class="text-lg sm:text-xl font-black text-slate-900">Access Permissions</h3>
          <p class="text-xs text-slate-500 mt-0.5">
            <span id="perm-staff-name" class="font-bold text-slate-700">Name</span> 
            <span id="perm-staff-code" class="ml-1 text-[10px] font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">STF-000</span>
          </p>
        </div>
      </div>

      <input type="hidden" id="perm-staff-id" value="0">

      <!-- Preset Dropdown -->
      <div class="flex items-center justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
        <div class="flex-1">
          <label class="block text-xs font-bold text-slate-700 mb-1">Quick Apply Preset Role</label>
          <div class="flex gap-2">
            <select id="perm-preset" class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
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
            <button onclick="applyPermissionPreset()" class="px-4 py-2 rounded-xl bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold text-xs transition whitespace-nowrap">
              Apply
            </button>
          </div>
        </div>
      </div>

      <!-- Account Status (Optional extra control) -->
      <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
          <label class="block text-xs font-bold text-slate-700 mb-1">Account Login Status</label>
          <select id="perm-account-status" class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
            <option value="Active">Active (Can Login)</option>
            <option value="Suspended">Suspended (Temporarily disabled)</option>
            <option value="Blocked">Blocked (Permanent ban)</option>
          </select>
          <p class="text-[10px] text-slate-500 mt-1">If set to Suspended or Blocked, the user will be unable to log in, regardless of permissions.</p>
      </div>

      <!-- Permissions Toggles -->
      <div>
        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
          <i class="fa-solid fa-sliders text-indigo-500"></i> Individual Permissions
        </h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="permissions-toggles-container">
          <!-- Toggles injected here via JS -->
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <button type="button" onclick="closePermissionsModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
          Cancel
        </button>
        <button type="button" onclick="savePermissions()" id="btn-save-permissions" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/30 transition flex items-center gap-2">
          <i class="fa-solid fa-floppy-disk"></i>
          <span>Save Permissions</span>
        </button>
      </div>

    </div>
  </div>
</div>

// js/permissions.js
const PERMISSION_KEYS = [
    { id: 'can_upload', label: 'Upload Files', icon: 'fa-upload' },
    { id: 'can_view_files', label: 'View Files', icon: 'fa-eye' },
    { id: 'can_edit_files', label: 'Edit Files', icon: 'fa-pen' },
    { id: 'can_delete_files', label: 'Delete Files', icon: 'fa-trash' },
    { id: 'can_view_patients', label: 'View Patients', icon: 'fa-users' },
    { id: 'can_edit_patients', label: 'Edit Patients', icon: 'fa-user-pen' },
    { id: 'can_manage_appointments', label: 'Manage Appointments', icon: 'fa-calendar' },
    { id: 'can_assign_tokens', label: 'Assign Tokens', icon: 'fa-ticket' },
    { id: 'can_generate_qr', label: 'Generate QR Codes', icon: 'fa-qrcode' },
    { id: 'can_download_pdf', label: 'Download PDFs', icon: 'fa-file-pdf' },
    { id: 'can_consult_online', label: 'Online Consultations', icon: 'fa-video' }
];

const PRESETS = {
    full_access: { can_upload: 1, can_view_files: 1, can_edit_files: 1, can_delete_files: 1, can_view_patients: 1, can_edit_patients: 1, can_manage_appointments: 1, can_assign_tokens: 1, can_generate_qr: 1, can_download_pdf: 1, can_consult_online: 1 },
    upload_only: { can_upload: 1, can_view_files: 0, can_edit_files: 0, can_delete_files: 0, can_view_patients: 0, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 0, can_consult_online: 0 },
    view_only: { can_upload: 0, can_view_files: 1, can_edit_files: 0, can_delete_files: 0, can_view_patients: 1, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 0, can_consult_online: 0 },
    upload_view: { can_upload: 1, can_view_files: 1, can_edit_files: 0, can_delete_files: 0, can_view_patients: 1, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 0, can_consult_online: 0 },
    reception: { can_upload: 1, can_view_files: 0, can_edit_files: 0, can_delete_files: 0, can_view_patients: 1, can_edit_patients: 0, can_manage_appointments: 1, can_assign_tokens: 1, can_generate_qr: 1, can_download_pdf: 0, can_consult_online: 0 },
    lab_tech: { can_upload: 1, can_view_files: 1, can_edit_files: 0, can_delete_files: 0, can_view_patients: 0, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 0, can_consult_online: 0 },
    doctor: { can_upload: 1, can_view_files: 1, can_edit_files: 1, can_delete_files: 1, can_view_patients: 0, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 1, can_consult_online: 1 },
    blocked: { can_upload: 0, can_view_files: 0, can_edit_files: 0, can_delete_files: 0, can_view_patients: 0, can_edit_patients: 0, can_manage_appointments: 0, can_assign_tokens: 0, can_generate_qr: 0, can_download_pdf: 0, can_consult_online: 0 }
};

document.addEventListener('DOMContentLoaded', () => {
    // Generate toggles
    const container = document.getElementById('permissions-toggles-container');
    if (container) {
        let html = '';
        PERMISSION_KEYS.forEach(p => {
            html += `
            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-white shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs">
                        <i class="fa-solid ${p.icon}"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-700">${p.label}</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggle-${p.id}" class="sr-only peer perm-toggle">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>`;
        });
        container.innerHTML = html;
    }
});

function openPermissionsModal(staffId, staffName, staffCode) {
    document.getElementById('perm-staff-id').value = staffId;
    document.getElementById('perm-staff-name').textContent = staffName;
    document.getElementById('perm-staff-code').textContent = staffCode;
    
    document.getElementById('modal-staff-permissions').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Reset UI
    document.getElementById('perm-preset').value = '';
    document.getElementById('perm-account-status').value = 'Active';
    document.querySelectorAll('.perm-toggle').forEach(t => t.checked = false);

    // Fetch existing
    fetch(`api/permissions.php?action=get_permissions&staff_id=${staffId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const perms = data.permissions;
                PERMISSION_KEYS.forEach(p => {
                    const toggle = document.getElementById(`toggle-${p.id}`);
                    if (toggle) {
                        toggle.checked = parseInt(perms[p.id] || 0) === 1;
                    }
                });
                
                if (data.account_status) {
                    document.getElementById('perm-account-status').value = data.account_status;
                }
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error', 'Failed to load permissions.', 'error');
        });
}

function closePermissionsModal() {
    document.getElementById('modal-staff-permissions').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function applyPermissionPreset() {
    const presetKey = document.getElementById('perm-preset').value;
    if (!presetKey || !PRESETS[presetKey]) return;
    
    const preset = PRESETS[presetKey];
    PERMISSION_KEYS.forEach(p => {
        const toggle = document.getElementById(`toggle-${p.id}`);
        if (toggle) {
            toggle.checked = preset[p.id] === 1;
        }
    });
}

function savePermissions() {
    const staffId = document.getElementById('perm-staff-id').value;
    const accountStatus = document.getElementById('perm-account-status').value;
    
    const perms = {};
    PERMISSION_KEYS.forEach(p => {
        const toggle = document.getElementById(`toggle-${p.id}`);
        perms[p.id] = (toggle && toggle.checked) ? 1 : 0;
    });

    const btn = document.getElementById('btn-save-permissions');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving...</span>';

    fetch('api/permissions.php?action=set_permissions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            staff_id: staffId,
            permissions: perms,
            account_status: accountStatus
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Success', data.message, 'success');
            closePermissionsModal();
            // Refresh staff list if we are on staff.php
            if (typeof fetchStaffList === 'function') {
                fetchStaffList();
            }
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error', 'Failed to save permissions.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> <span>Save Permissions</span>';
    });
}

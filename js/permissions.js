// js/permissions.js
const PERMISSION_KEYS = [
    // Group 1: Patient Care & Telehealth
    {
        id: 'can_view_patients',
        label: 'View Patients',
        desc: 'Browse patient directory, health metrics & profile info',
        icon: 'fa-users',
        color: 'text-blue-600 bg-blue-50 border-blue-200',
        group: 'Patient Care & Operations'
    },
    {
        id: 'can_edit_patients',
        label: 'Edit Patients',
        desc: 'Register new patients & modify medical records',
        icon: 'fa-user-pen',
        color: 'text-indigo-600 bg-indigo-50 border-indigo-200',
        group: 'Patient Care & Operations'
    },
    {
        id: 'can_assign_tokens',
        label: 'Assign OPD Tokens',
        desc: 'Generate live queue tokens & manage waiting room slips',
        icon: 'fa-ticket',
        color: 'text-purple-600 bg-purple-50 border-purple-200',
        group: 'Patient Care & Operations'
    },
    {
        id: 'can_consult_online',
        label: 'Online Telehealth',
        desc: 'Conduct remote video visits & virtual consultations',
        icon: 'fa-video',
        color: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        group: 'Patient Care & Operations'
    },

    // Group 2: Appointments & Front Desk
    {
        id: 'can_manage_appointments',
        label: 'Manage Appointments',
        desc: 'Schedule, reschedule, book & cancel patient doctor visits',
        icon: 'fa-calendar-check',
        color: 'text-amber-600 bg-amber-50 border-amber-200',
        group: 'Appointments & Scheduling'
    },
    {
        id: 'can_generate_qr',
        label: 'Generate QR Health Cards',
        desc: 'Issue physical & digital QR identity cards for patients',
        icon: 'fa-qrcode',
        color: 'text-teal-600 bg-teal-50 border-teal-200',
        group: 'Appointments & Scheduling'
    },
    {
        id: 'can_download_pdf',
        label: 'Export Invoices & PDFs',
        desc: 'Download clinical summaries, discharge slips & receipts',
        icon: 'fa-file-pdf',
        color: 'text-rose-600 bg-rose-50 border-rose-200',
        group: 'Appointments & Scheduling'
    },

    // Group 3: Medical Records & Files
    {
        id: 'can_view_files',
        label: 'Browse Medical Files',
        desc: 'Inspect lab attachments, radiology scans & test documents',
        icon: 'fa-eye',
        color: 'text-sky-600 bg-sky-50 border-sky-200',
        group: 'Clinical Records & File Storage'
    },
    {
        id: 'can_upload',
        label: 'Upload File Attachments',
        desc: 'Upload laboratory reports, prescriptions & documents',
        icon: 'fa-cloud-arrow-up',
        color: 'text-blue-600 bg-blue-50 border-blue-200',
        group: 'Clinical Records & File Storage'
    },
    {
        id: 'can_edit_files',
        label: 'Edit Document Metadata',
        desc: 'Update document tags, rename records & write file remarks',
        icon: 'fa-pen-to-square',
        color: 'text-violet-600 bg-violet-50 border-violet-200',
        group: 'Clinical Records & File Storage'
    },
    {
        id: 'can_delete_files',
        label: 'Delete Records',
        desc: 'Permanently remove files & attachments from disk',
        icon: 'fa-trash-can',
        color: 'text-rose-600 bg-rose-50 border-rose-200',
        group: 'Clinical Records & File Storage',
        danger: true
    }
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
    initPermissionsToggles();
});

function initPermissionsToggles() {
    const container = document.getElementById('permissions-toggles-container');
    if (!container) return;

    // Group items by category
    const groups = {};
    PERMISSION_KEYS.forEach(p => {
        const g = p.group || 'General Permissions';
        if (!groups[g]) groups[g] = [];
        groups[g].push(p);
    });

    let html = '';
    Object.keys(groups).forEach(groupName => {
        const items = groups[groupName];
        
        let iconClass = 'fa-cubes';
        if (groupName.includes('Patient')) iconClass = 'fa-user-shield';
        else if (groupName.includes('Appointment')) iconClass = 'fa-calendar-days';
        else if (groupName.includes('File') || groupName.includes('Record')) iconClass = 'fa-folder-tree';

        html += `
        <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-2xs">
            <div class="flex items-center gap-2 mb-2.5 pb-2 border-b border-slate-100">
                <i class="fa-solid ${iconClass} text-indigo-600 text-xs"></i>
                <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider">${groupName}</h5>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.2 rounded-full bg-slate-100 text-slate-500">${items.length} options</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">`;

        items.forEach(p => {
            const dangerTag = p.danger ? '<span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 border border-rose-200 uppercase ml-1">High Risk</span>' : '';
            html += `
                <div id="card-toggle-${p.id}" onclick="togglePermissionItem('${p.id}')" class="perm-card flex items-center justify-between p-2.5 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-300 transition cursor-pointer select-none">
                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                        <div class="w-8 h-8 rounded-lg ${p.color || 'bg-slate-100 text-slate-600'} border flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid ${p.icon}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1 truncate">
                                <span>${p.label}</span>
                                ${dangerTag}
                            </div>
                            <p class="text-[10px] text-slate-500 truncate mt-0.2 leading-tight">${p.desc || ''}</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0" onclick="event.stopPropagation()">
                        <input type="checkbox" id="toggle-${p.id}" onchange="onPermissionToggleChanged('${p.id}')" class="sr-only peer perm-toggle">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>`;
        });

        html += `
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

function togglePermissionItem(id) {
    const toggle = document.getElementById(`toggle-${id}`);
    if (toggle) {
        toggle.checked = !toggle.checked;
        onPermissionToggleChanged(id);
    }
}

function onPermissionToggleChanged(id) {
    const toggle = document.getElementById(`toggle-${id}`);
    const card = document.getElementById(`card-toggle-${id}`);
    if (toggle && card) {
        if (toggle.checked) {
            card.classList.remove('bg-slate-50/50', 'border-slate-200/90');
            card.classList.add('bg-indigo-50/50', 'border-indigo-200/90');
        } else {
            card.classList.remove('bg-indigo-50/50', 'border-indigo-200/90');
            card.classList.add('bg-slate-50/50', 'border-slate-200/90');
        }
    }
    updateActivePermissionCount();
}

function updateActivePermissionCount() {
    let count = 0;
    PERMISSION_KEYS.forEach(p => {
        const toggle = document.getElementById(`toggle-${p.id}`);
        const card = document.getElementById(`card-toggle-${p.id}`);
        if (toggle && toggle.checked) {
            count++;
            if (card) {
                card.classList.remove('bg-slate-50/50', 'border-slate-200/90');
                card.classList.add('bg-indigo-50/50', 'border-indigo-200/90');
            }
        } else if (card) {
            card.classList.remove('bg-indigo-50/50', 'border-indigo-200/90');
            card.classList.add('bg-slate-50/50', 'border-slate-200/90');
        }
    });

    const countEl = document.getElementById('perm-active-count');
    if (countEl) countEl.textContent = count;
}

function setAccountStatus(status) {
    const sel = document.getElementById('perm-account-status');
    if (sel) sel.value = status;

    // Update pill highlights
    ['Active', 'Suspended', 'Blocked'].forEach(s => {
        const btn = document.getElementById(`status-pill-${s}`);
        if (!btn) return;
        if (s === status) {
            if (s === 'Active') {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs";
            } else if (s === 'Suspended') {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs";
            } else if (s === 'Blocked') {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs";
            }
        } else {
            btn.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-50";
        }
    });

    // Update top header badge
    const badge = document.getElementById('perm-status-badge');
    if (badge) {
        badge.textContent = status;
        if (status === 'Active') {
            badge.className = "text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200";
        } else if (status === 'Suspended') {
            badge.className = "text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200";
        } else {
            badge.className = "text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200";
        }
    }
}

function setPreset(presetKey) {
    if (!presetKey || !PRESETS[presetKey]) return;

    // Highlight active chip
    document.querySelectorAll('.preset-chip').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50', 'text-indigo-700');
    });
    const clickedBtn = event ? event.currentTarget : null;
    if (clickedBtn) {
        clickedBtn.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-50', 'text-indigo-700');
    }

    const sel = document.getElementById('perm-preset');
    if (sel) sel.value = presetKey;

    const preset = PRESETS[presetKey];
    PERMISSION_KEYS.forEach(p => {
        const toggle = document.getElementById(`toggle-${p.id}`);
        if (toggle) {
            toggle.checked = preset[p.id] === 1;
        }
    });

    updateActivePermissionCount();

    if (presetKey === 'blocked') {
        setAccountStatus('Blocked');
    } else if (presetKey === 'full_access') {
        setAccountStatus('Active');
    }
}

function applyPermissionPreset() {
    const sel = document.getElementById('perm-preset');
    if (sel && sel.value) {
        setPreset(sel.value);
    }
}

function selectAllPermissions(grantAll = true) {
    PERMISSION_KEYS.forEach(p => {
        const toggle = document.getElementById(`toggle-${p.id}`);
        if (toggle) {
            toggle.checked = grantAll;
        }
    });
    updateActivePermissionCount();
}

function openPermissionsModal(staffId, staffName, staffCode) {
    document.getElementById('perm-staff-id').value = staffId;
    document.getElementById('perm-staff-name').textContent = staffName;
    document.getElementById('perm-staff-code').textContent = staffCode;
    
    // Attempt to pull richer details from local caches if present
    let staffObj = null;
    if (typeof staffList !== 'undefined' && Array.isArray(staffList)) {
        staffObj = staffList.find(s => parseInt(s.id) === parseInt(staffId));
    } else if (typeof allStaffAccounts !== 'undefined' && Array.isArray(allStaffAccounts)) {
        staffObj = allStaffAccounts.find(s => parseInt(s.id) === parseInt(staffId));
    }

    const avatarWrap = document.getElementById('perm-staff-avatar-wrap');
    const avatarImg = document.getElementById('perm-staff-avatar-img');
    const avatarInit = document.getElementById('perm-staff-avatar-initials');
    const roleDeptEl = document.getElementById('perm-staff-role-dept');

    if (staffObj) {
        if (roleDeptEl) {
            roleDeptEl.textContent = `${staffObj.role || 'Staff Member'} • ${staffObj.department || 'General'}`;
        }
        if (staffObj.avatar && avatarImg && avatarInit) {
            avatarImg.src = staffObj.avatar;
            avatarImg.classList.remove('hidden');
            avatarInit.classList.add('hidden');
        } else if (avatarImg && avatarInit) {
            avatarImg.classList.add('hidden');
            avatarInit.classList.remove('hidden');
            const inits = (((staffObj.first_name ? staffObj.first_name[0] : '') + (staffObj.last_name ? staffObj.last_name[0] : '')) || 'ST').toUpperCase();
            avatarInit.textContent = inits;
        }
    } else {
        if (roleDeptEl) roleDeptEl.textContent = 'Configure system module access and role-based permissions';
        if (avatarImg) avatarImg.classList.add('hidden');
        if (avatarInit) avatarInit.innerHTML = '<i class="fa-solid fa-shield-halved"></i>';
    }

    document.getElementById('modal-staff-permissions').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Reset UI
    setAccountStatus('Active');
    document.querySelectorAll('.preset-chip').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50', 'text-indigo-700');
    });
    document.querySelectorAll('.perm-toggle').forEach(t => t.checked = false);
    updateActivePermissionCount();

    // Fetch existing permissions
    fetch(`api/permissions.php?action=get_permissions&staff_id=${staffId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const perms = data.permissions || {};
                PERMISSION_KEYS.forEach(p => {
                    const toggle = document.getElementById(`toggle-${p.id}`);
                    if (toggle) {
                        toggle.checked = parseInt(perms[p.id] || 0) === 1;
                    }
                });
                
                if (data.account_status) {
                    setAccountStatus(data.account_status);
                }

                updateActivePermissionCount();
            } else {
                showToast('Error', data.message || 'Could not load staff permissions.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error', 'Failed to load permissions from server.', 'error');
        });
}

function closePermissionsModal() {
    const modal = document.getElementById('modal-staff-permissions');
    if (modal) modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
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
            showToast('Permissions Saved', data.message || 'Permissions updated successfully.', 'success');
            closePermissionsModal();
            // Refresh lists if caller page has refresh hooks
            if (typeof fetchStaffList === 'function') {
                fetchStaffList();
            }
            if (typeof loadAllStaffAccounts === 'function') {
                loadAllStaffAccounts();
            }
        } else {
            showToast('Error', data.message || 'Failed to update permissions.', 'error');
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

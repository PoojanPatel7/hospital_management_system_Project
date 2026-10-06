<?php
require_once 'auth.php';
require_once 'db.php';
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Consultation Charges</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Manage consultation and PDF download charges.</p>
        </div>
        <button onclick="openAddModal()" class="bg-black hover:bg-neutral-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm flex items-center gap-2 transition">
            <i class="fa-solid fa-plus"></i> Add Charge
        </button>
    </div>

    <div class="apple-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-black">Charge Type</th>
                        <th class="px-6 py-4 font-black">Name</th>
                        <th class="px-6 py-4 font-black">Amount (₹)</th>
                        <th class="px-6 py-4 font-black text-center">Status</th>
                        <th class="px-6 py-4 font-black text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="chargesTableBody" class="divide-y divide-slate-100">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Charge Modal -->
<div id="addChargeModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300" id="addChargeModalContent">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-black text-lg text-slate-900">Add New Charge</h3>
            <button onclick="closeAddModal()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-black flex items-center justify-center transition shadow-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="addChargeForm" onsubmit="submitAddCharge(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Charge Type (Key)</label>
                <input type="text" id="add_charge_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="e.g. online_consultation">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Display Name</label>
                <input type="text" id="add_charge_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="e.g. Online Consultation Fee">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Amount (₹)</label>
                <input type="number" id="add_amount" required min="0" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="0.00">
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-black hover:bg-neutral-800 transition">Add Charge</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadCharges);

async function loadCharges() {
    try {
        const res = await fetch('api/charges.php?action=get_all');
        const data = await res.json();
        
        if(data.status === 'success') {
            const tbody = document.getElementById('chargesTableBody');
            tbody.innerHTML = '';
            
            data.charges.forEach(charge => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50/50 transition group";
                
                tr.innerHTML = `
                    <td class="px-6 py-4 font-mono text-xs text-slate-500">${charge.charge_type}</td>
                    <td class="px-6 py-4 font-bold text-slate-900">${charge.charge_name}</td>
                    <td class="px-6 py-4">
                        <input type="number" min="0" step="0.01" value="${charge.amount}" 
                            onchange="updateChargeAmount(${charge.id}, this.value)"
                            class="w-24 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-black focus:border-black transition">
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="toggleStatus(${charge.id}, ${charge.is_active})" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors ${charge.is_active ? 'bg-black' : 'bg-slate-300'}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition ${charge.is_active ? 'translate-x-4' : 'translate-x-1'}"></span>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="deleteCharge(${charge.id})" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch(err) {
        showToast('Error', 'Failed to load charges', 'error');
    }
}

async function updateChargeAmount(id, amount) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', id);
    formData.append('amount', amount);
    
    try {
        const res = await fetch('api/charges.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            showToast('Success', 'Charge updated');
        } else {
            showToast('Error', data.message, 'error');
            loadCharges();
        }
    } catch(err) {
        showToast('Error', 'Failed to update charge', 'error');
    }
}

async function toggleStatus(id, currentStatus) {
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);
    formData.append('is_active', currentStatus ? 0 : 1);
    
    try {
        const res = await fetch('api/charges.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            loadCharges();
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch(err) {
        showToast('Error', 'Failed to toggle status', 'error');
    }
}

async function deleteCharge(id) {
    if(!confirm('Are you sure you want to delete this charge?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const res = await fetch('api/charges.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            showToast('Success', 'Charge deleted');
            loadCharges();
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch(err) {
        showToast('Error', 'Failed to delete charge', 'error');
    }
}

function openAddModal() {
    const modal = document.getElementById('addChargeModal');
    const content = document.getElementById('addChargeModalContent');
    modal.classList.remove('hidden');
    // slight delay for transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }, 10);
}

function closeAddModal() {
    const modal = document.getElementById('addChargeModal');
    const content = document.getElementById('addChargeModalContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('addChargeForm').reset();
    }, 300);
}

async function submitAddCharge(e) {
    e.preventDefault();
    
    const type = document.getElementById('add_charge_type').value;
    const name = document.getElementById('add_charge_name').value;
    const amount = document.getElementById('add_amount').value;
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('charge_type', type);
    formData.append('charge_name', name);
    formData.append('amount', amount);
    
    try {
        const res = await fetch('api/charges.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            showToast('Success', 'Charge added');
            closeAddModal();
            loadCharges();
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch(err) {
        showToast('Error', 'Failed to add charge', 'error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>

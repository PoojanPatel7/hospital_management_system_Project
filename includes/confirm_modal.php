<!-- Global Confirmation Modal -->
<div id="modal-confirm-global" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="apple-card max-w-xl w-full p-5 sm:p-8 relative my-8 max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <button onclick="closeConfirmModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
        <div class="flex items-center gap-4 mb-4">
            <div id="confirm-icon-bg" class="w-12 h-12 rounded-full flex items-center justify-center text-xl bg-blue-50 text-blue-600 shrink-0">
                <i id="confirm-icon" class="fa-solid fa-circle-info"></i>
            </div>
            <div>
                <h3 id="confirm-title" class="text-xl font-bold text-slate-900">Confirm Action</h3>
                <p id="confirm-subtitle" class="text-sm text-slate-500">Please review the details below.</p>
            </div>
        </div>

        <div id="confirm-details" class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm text-slate-700 mb-6 space-y-2">
            <!-- Dynamic Details Go Here -->
        </div>

        <div class="flex gap-3">
            <button onclick="closeConfirmModal()" class="flex-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-3 rounded-xl transition shadow-sm">
                Cancel
            </button>
            <button id="confirm-btn-execute" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    let globalConfirmCallback = null;
    let globalCancelCallback = null;

    function openConfirmModal(title, subtitle, detailsHtml, callback, type = 'info', cancelCallback = null) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-subtitle').textContent = subtitle;
        document.getElementById('confirm-details').innerHTML = detailsHtml;
        
        globalConfirmCallback = callback;
        globalCancelCallback = cancelCallback;
        
        const btn = document.getElementById('confirm-btn-execute');
        const iconBg = document.getElementById('confirm-icon-bg');
        const icon = document.getElementById('confirm-icon');
        
        if (type === 'danger') {
            btn.className = 'flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl shadow-lg transition';
            btn.innerHTML = '<i class="fa-solid fa-trash mr-2"></i> Delete';
            iconBg.className = 'w-12 h-12 rounded-full flex items-center justify-center text-xl bg-rose-50 text-rose-600';
            icon.className = 'fa-solid fa-triangle-exclamation';
        } else if (type === 'success') {
            btn.className = 'flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition';
            btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Confirm';
            iconBg.className = 'w-12 h-12 rounded-full flex items-center justify-center text-xl bg-emerald-50 text-emerald-600';
            icon.className = 'fa-solid fa-circle-check';
        } else {
            btn.className = 'flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition';
            btn.innerHTML = 'Confirm';
            iconBg.className = 'w-12 h-12 rounded-full flex items-center justify-center text-xl bg-blue-50 text-blue-600';
            icon.className = 'fa-solid fa-circle-info';
        }
        
        document.getElementById('modal-confirm-global').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('modal-confirm-global').classList.add('hidden');
        if (globalCancelCallback) {
            globalCancelCallback();
        }
        globalConfirmCallback = null;
        globalCancelCallback = null;
    }

    document.getElementById('confirm-btn-execute').addEventListener('click', () => {
        if (globalConfirmCallback) {
            globalConfirmCallback();
        }
        document.getElementById('modal-confirm-global').classList.add('hidden');
        globalConfirmCallback = null;
        globalCancelCallback = null;
    });
</script>

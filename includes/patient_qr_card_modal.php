<!-- ================= PATIENT LIFETIME QR CODE MODAL ================= -->
<div id="patient-qr-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 relative my-8 shadow-2xl border border-slate-200">
        <!-- Close Button -->
        <button onclick="closePatientQRModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <!-- Printable Card Container -->
        <div id="printable-qr-card" class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-indigo-500/30 text-center relative overflow-hidden">
            <!-- Background Watermark/Accent -->
            <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Hospital Branding Header -->
            <div class="flex items-center justify-center gap-2 mb-3 pb-3 border-b border-indigo-500/20">
                <i class="fa-solid fa-hospital text-indigo-400 text-lg"></i>
                <span class="font-black text-sm tracking-wider uppercase text-white">CarePulse Hospital</span>
            </div>

            <div class="text-[10px] uppercase font-bold tracking-widest text-indigo-300 mb-3">
                Lifetime Patient Health Card
            </div>

            <!-- Crisp QR Code Image -->
            <div class="bg-white p-3 rounded-2xl inline-block shadow-xl my-1 border-2 border-indigo-200/50">
                <img id="qr-modal-image" src="" alt="Patient QR Code" class="w-48 h-48 object-contain mx-auto transition-transform hover:scale-105 duration-200" />
            </div>

            <!-- Patient Info -->
            <div class="mt-4 space-y-1">
                <h3 id="qr-modal-patient-name" class="text-xl font-black text-white truncate leading-tight">Patient Name</h3>
                <div class="flex items-center justify-center gap-2">
                    <span id="qr-modal-patient-id" class="px-2.5 py-0.5 rounded-lg bg-indigo-500/30 text-indigo-200 font-mono text-xs font-bold border border-indigo-400/30">CP-2026-XXX</span>
                    <span id="qr-modal-blood" class="px-2 py-0.5 rounded-lg bg-rose-500/30 text-rose-200 text-xs font-bold border border-rose-400/30">Blood: --</span>
                </div>
                <p id="qr-modal-demographics" class="text-xs text-indigo-200/80 pt-1">Age: -- • Gender: --</p>
                <p id="qr-modal-phone" class="text-[11px] text-indigo-300 font-mono">Phone: --</p>
            </div>

            <div class="mt-4 pt-3 border-t border-indigo-500/20 text-[10px] text-indigo-300 flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-lock text-[9px] text-emerald-400"></i>
                <span>Permanent lifetime QR key • Scannable by clinical staff</span>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="grid grid-cols-2 gap-2.5 mt-5">
            <button onclick="printPatientQRCard()" class="bg-slate-900 hover:bg-black text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2 shadow">
                <i class="fa-solid fa-print"></i> Print Card
            </button>
            <button onclick="downloadQRImage()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2 shadow">
                <i class="fa-solid fa-download"></i> Save Image
            </button>
            <button onclick="copyQRProfileUrl()" class="col-span-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-link"></i> Copy Link
            </button>
            <a id="qr-modal-open-link" href="#" target="_blank" class="col-span-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center justify-center gap-2 border border-blue-200">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Profile
            </a>
        </div>
    </div>
</div>

<script>
let currentQRData = null;

async function showPatientQRCode(patientId) {
    try {
        const res = await fetch(`api/qr.php?action=generate&patient_id=${encodeURIComponent(patientId)}`);
        const data = await res.json();
        if (data.status !== 'success') {
            alert(data.message || 'Failed to generate QR code.');
            return;
        }

        currentQRData = data;
        const p = data.patient || {};
        
        let age = (p.age && p.age !== '0') ? p.age : '';
        let gender = (p.gender && p.gender.toLowerCase() !== 'other') ? p.gender : '';
        let blood = p.blood_group || '';
        const demo = p.demographics || '';

        if (!age && demo) {
            const m = demo.match(/(\d+)\s*(Y|y|Years?)/i);
            if (m) age = m[1];
        }
        if (!gender && demo) {
            const m = demo.match(/\b(Male|Female)\b/i);
            if (m) gender = m[1];
        }
        if (!blood && demo) {
            const m = demo.match(/\b(A|B|AB|O)[+-]\b/i);
            if (m) blood = m[0];
        }

        document.getElementById('qr-modal-image').src = data.qr_image_url;
        document.getElementById('qr-modal-patient-name').textContent = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient';
        document.getElementById('qr-modal-patient-id').textContent = p.id || patientId;
        document.getElementById('qr-modal-blood').textContent = blood ? `🩸 ${blood}` : 'Blood: N/A';
        document.getElementById('qr-modal-demographics').textContent = `Age: ${age || '--'} • Sex: ${gender || '--'}`;
        document.getElementById('qr-modal-phone').textContent = p.phone ? `Phone: ${p.phone}` : 'Phone: N/A';
        document.getElementById('qr-modal-open-link').href = data.url;

        document.getElementById('patient-qr-modal').classList.remove('hidden');
    } catch (err) {
        console.error('QR Load error:', err);
        alert('Could not load QR code. Please try again.');
    }
}

function closePatientQRModal() {
    document.getElementById('patient-qr-modal').classList.add('hidden');
}

function printPatientQRCard() {
    const printContent = document.getElementById('printable-qr-card');
    const win = window.open('', '', 'width=650,height=650');
    win.document.write(`
        <html>
            <head>
                <title>Patient Health Card - CarePulse Hospital</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f8fafc; }
                    .card { width: 340px; background: #0f172a; color: white; border-radius: 16px; padding: 24px; text-align: center; border: 2px solid #6366f1; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
                    .qr-box { background: white; padding: 12px; border-radius: 12px; display: inline-block; margin: 12px auto; }
                    .qr-box img { width: 180px; height: 180px; display: block; }
                    .header { font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; color: #a5b4fc; }
                    h2 { margin: 8px 0 4px; font-size: 18px; color: #fff; }
                    .id-pill { background: #312e81; color: #c7d2fe; padding: 2px 10px; border-radius: 6px; font-family: monospace; font-size: 12px; font-weight: bold; display: inline-block; margin-bottom: 6px; }
                    .meta { font-size: 12px; color: #94a3b8; margin: 4px 0; }
                    .footer { font-size: 10px; color: #64748b; margin-top: 14px; border-top: 1px solid #1e293b; padding-top: 8px; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="header">CarePulse Hospital • Health Card</div>
                    <div class="qr-box">
                        <img src="${document.getElementById('qr-modal-image').src}" alt="QR Code" />
                    </div>
                    <h2>${document.getElementById('qr-modal-patient-name').textContent}</h2>
                    <div class="id-pill">${document.getElementById('qr-modal-patient-id').textContent}</div>
                    <div class="meta">${document.getElementById('qr-modal-blood').textContent} • ${document.getElementById('qr-modal-demographics').textContent}</div>
                    <div class="meta">${document.getElementById('qr-modal-phone').textContent}</div>
                    <div class="footer">Permanent Patient Lifetime QR Key</div>
                </div>
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
        </html>
    `);
    win.document.close();
}

function downloadQRImage() {
    if (!currentQRData || !currentQRData.qr_image_url) return;
    const a = document.createElement('a');
    a.href = currentQRData.qr_image_url;
    a.download = `QR_${currentQRData.token || 'patient'}.png`;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function copyQRProfileUrl() {
    if (!currentQRData || !currentQRData.url) return;
    navigator.clipboard.writeText(currentQRData.url).then(() => {
        alert('Permanent patient profile URL copied to clipboard!');
    }).catch(() => {
        prompt('Copy patient profile URL:', currentQRData.url);
    });
}
</script>

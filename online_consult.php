<?php
require_once 'auth.php';
require_once 'db.php';

$hospital_id = $_SESSION['hospital_id'] ?? 1;

// Retrieve patient by token or patient_id
$patient_id = $_GET['patient_id'] ?? '';
$token = $_GET['token'] ?? '';

$patient = null;
if ($token) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE qr_token = ? AND (hospital_id = ? OR hospital_id IS NULL)");
    $stmt->bind_param("si", $token, $hospital_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    if ($patient) {
        $patient_id = $patient['id'];
    }
} elseif ($patient_id) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ? AND (hospital_id = ? OR hospital_id IS NULL)");
    $stmt->bind_param("si", $patient_id, $hospital_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
}

if (!$patient) {
    die("Patient not found or invalid token.");
}

// Get consultation fee
$stmtFee = $conn->prepare("SELECT amount FROM consultation_charges WHERE charge_type = 'online_consultation' AND is_active = 1 AND hospital_id = ?");
$stmtFee->bind_param("i", $hospital_id);
$stmtFee->execute();
$feeRow = $stmtFee->get_result()->fetch_assoc();
$consultation_fee = $feeRow ? (float)$feeRow['amount'] : 500.00;

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Online Consultation</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Consulting with patient: <?php echo htmlspecialchars($patient['name']); ?></p>
        </div>
        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm">
            Consultation Fee: ₹<?php echo number_format($consultation_fee, 2); ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Patient Profile & Files -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Profile Summary -->
            <div class="apple-card p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-2xl text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900"><?php echo htmlspecialchars($patient['name']); ?></h2>
                        <p class="text-xs font-bold text-slate-500"><?php echo htmlspecialchars($patient['id']); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mt-6">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase">Age/Gender</span>
                        <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($patient['age'] ?: '-'); ?> / <?php echo htmlspecialchars($patient['gender'] ?: '-'); ?></span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase">Blood Group</span>
                        <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($patient['blood_group'] ?: '-'); ?></span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase">Phone</span>
                        <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($patient['phone'] ?: '-'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Previous Files Gallery -->
            <div class="apple-card p-6">
                <h3 class="font-black text-slate-900 mb-4">Patient Files Gallery</h3>
                <div class="grid grid-cols-2 gap-3" id="filesGallery">
                    <!-- Loaded via JS -->
                </div>
                <button onclick="loadFiles()" class="w-full mt-4 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold py-2 rounded-xl text-xs transition border border-slate-200">
                    Refresh Files
                </button>
            </div>
        </div>

        <!-- Right Column: Consultation Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="apple-card p-6">
                <h3 class="text-lg font-black text-slate-900 mb-6 border-b border-slate-100 pb-4">Consultation Details</h3>
                
                <form id="consultationForm" onsubmit="submitConsultation(event)" class="space-y-5">
                    <input type="hidden" id="consultation_id" value="">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Diagnosis</label>
                        <textarea id="diagnosis" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="Enter patient diagnosis"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Prescription Notes</label>
                        <textarea id="prescription_notes" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="Enter medicines and dosages"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Upload Prescription Images (Optional)</label>
                        <input type="file" id="prescription_files" multiple accept="image/*,application/pdf" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-neutral-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Doctor Notes (Private)</label>
                        <textarea id="doctor_notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition" placeholder="Internal notes"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="saveConsultation()" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-5 py-2.5 rounded-xl font-bold transition shadow-sm border border-slate-200">
                            Save Draft
                        </button>
                        <button type="submit" class="bg-black hover:bg-neutral-800 text-white px-6 py-2.5 rounded-xl font-bold transition shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-check"></i> Complete Consultation
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Previous Consultations -->
            <div class="apple-card p-6">
                <h3 class="font-black text-slate-900 mb-4 border-b border-slate-100 pb-4">Previous Consultations</h3>
                <div class="space-y-4" id="previousConsultations">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Simple Lightbox Modal -->
<div id="lightbox" class="hidden fixed inset-0 z-[200] bg-black/90 flex items-center justify-center" onclick="closeLightbox()">
    <img id="lightboxImg" src="" class="max-w-full max-h-full object-contain p-4" onclick="event.stopPropagation()">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl"><i class="fa-solid fa-xmark"></i></button>
</div>

<script>
const patientId = "<?php echo htmlspecialchars($patient_id); ?>";
const fee = <?php echo $consultation_fee; ?>;
let currentConsultationId = null;

document.addEventListener('DOMContentLoaded', () => {
    initiateConsultation();
    loadFiles();
    loadPreviousConsultations();
});

async function initiateConsultation() {
    try {
        const res = await fetch('api/online_consultation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'initiate', patient_id: patientId })
        });
        const data = await res.json();
        if(data.status === 'success') {
            currentConsultationId = data.consultation_id;
            document.getElementById('consultation_id').value = currentConsultationId;
        } else {
            showToast('Error', 'Failed to initiate session', 'error');
        }
    } catch(err) {
        console.error(err);
    }
}

async function loadFiles() {
    try {
        const res = await fetch(`api/pdf.php?action=get_pdf_data&patient_id=${encodeURIComponent(patientId)}`);
        const data = await res.json();
        
        if (data.status === 'success' && data.files) {
            const gallery = document.getElementById('filesGallery');
            gallery.innerHTML = '';
            
            if (data.files.length === 0) {
                gallery.innerHTML = '<div class="col-span-2 text-sm text-slate-500 text-center py-4">No files found</div>';
                return;
            }

            data.files.forEach(f => {
                const isImg = f.file_url.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/i) || f.file_url.includes('api/file');
                const thumb = isImg ? f.file_url : 'images/doc_placeholder.png'; // fallback
                
                const div = document.createElement('div');
                div.className = "relative group rounded-xl overflow-hidden border border-slate-200 cursor-pointer bg-slate-50 aspect-square flex items-center justify-center";
                div.innerHTML = `
                    <img src="${thumb}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiNlMmU4ZjAiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2NDc0OGIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GaWxlPC90ZXh0Pjwvc3ZnPg=='" />
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2 text-white">
                        <p class="text-xs font-bold truncate">${f.title}</p>
                        <p class="text-[10px]">${f.record_date}</p>
                    </div>
                `;
                div.onclick = () => openLightbox(f.file_url);
                gallery.appendChild(div);
            });
        }
    } catch(err) {
        console.error(err);
    }
}

function openLightbox(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightbox').classList.remove('hidden');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.getElementById('lightboxImg').src = '';
}

async function loadPreviousConsultations() {
    try {
        const res = await fetch(`api/online_consultation.php?action=get_patient_consultations&patient_id=${encodeURIComponent(patientId)}`);
        const data = await res.json();
        const container = document.getElementById('previousConsultations');
        container.innerHTML = '';
        
        if (data.status === 'success' && data.data.length > 0) {
            data.data.forEach(c => {
                // Skip the current one if it's new
                if (c.id == currentConsultationId) return;
                
                const div = document.createElement('div');
                div.className = "p-4 rounded-xl border border-slate-200 bg-slate-50";
                div.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-1 rounded">Dr. ${c.doctor_name || c.doctor_id}</span>
                        <span class="text-xs text-slate-500 font-semibold">${c.started_at}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-900 mt-2">Diagnosis: <span class="font-normal">${c.diagnosis || 'N/A'}</span></p>
                    <p class="text-sm font-bold text-slate-900 mt-1">Prescription: <span class="font-normal text-slate-600">${c.prescription_notes || 'N/A'}</span></p>
                `;
                container.appendChild(div);
            });
            if (container.children.length === 0) {
                 container.innerHTML = '<p class="text-sm text-slate-500">No previous completed consultations.</p>';
            }
        } else {
            container.innerHTML = '<p class="text-sm text-slate-500">No previous consultations.</p>';
        }
    } catch(err) {
        console.error(err);
    }
}

async function saveConsultation() {
    if (!currentConsultationId) return;
    
    const formData = new FormData();
    formData.append('action', 'save_consultation');
    formData.append('id', currentConsultationId);
    formData.append('patient_id', patientId);
    formData.append('diagnosis', document.getElementById('diagnosis').value);
    formData.append('prescription_notes', document.getElementById('prescription_notes').value);
    formData.append('doctor_notes', document.getElementById('doctor_notes').value);
    formData.append('consultation_fee', fee);
    
    const filesInput = document.getElementById('prescription_files');
    if (filesInput.files.length > 0) {
        for (let i = 0; i < filesInput.files.length; i++) {
            formData.append('prescription_files[]', filesInput.files[i]);
        }
    }
    
    try {
        const res = await fetch('api/online_consultation.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            showToast('Success', 'Draft saved successfully');
            loadFiles(); // Refresh gallery in case prescriptions were uploaded
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch(err) {
        showToast('Error', 'Failed to save', 'error');
    }
}

async function submitConsultation(e) {
    e.preventDefault();
    if (!currentConsultationId) return;
    
    // First save
    await saveConsultation();
    
    // Then complete
    const formData = new FormData();
    formData.append('action', 'complete');
    formData.append('id', currentConsultationId);
    formData.append('patient_id', patientId);
    
    try {
        const res = await fetch('api/online_consultation.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            showToast('Success', 'Consultation marked as complete');
            setTimeout(() => {
                window.location.href = 'patients.php';
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch(err) {
        showToast('Error', 'Failed to complete', 'error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>

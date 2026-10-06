let uploadFiles = [];
const categories = ['X-Ray', 'Blood Report', 'MRI', 'CT Scan', 'Prescription', 'Ultrasound', 'ECG', 'Other'];
let stream = null;

function openUploadModal(patientId, patientName, appointmentId = null) {
    document.getElementById('uploadPatientId').value = patientId;
    document.getElementById('uploadPatientInfo').textContent = `${patientName} (ID: ${patientId})`;
    
    // Reset list
    uploadFiles = [];
    renderUploadList();
    
    // Load appointments (mock fetch or real fetch depending on API)
    // For now, assume appointmentId is passed
    const select = document.getElementById('uploadAppointmentSelect');
    select.innerHTML = '<option value="">-- No Appointment (Standalone File) --</option>';
    if (appointmentId) {
        select.innerHTML += `<option value="${appointmentId}" selected>Appointment #${appointmentId}</option>`;
    }
    
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    closeCamera();
    uploadFiles = [];
}

const dropZone = document.getElementById('dropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('bg-slate-800/50', 'border-blue-500');
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-slate-800/50', 'border-blue-500');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-slate-800/50', 'border-blue-500');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            handleFiles(e.dataTransfer.files);
        }
    });
}

function handleFilesSelected(event) {
    if (event.target.files && event.target.files.length > 0) {
        handleFiles(event.target.files);
    }
    event.target.value = ''; // Reset input
}

function handleFiles(files) {
    Array.from(files).forEach(file => {
        addFileToUploadList(file);
    });
}

function addFileToUploadList(file) {
    const fileObj = {
        id: Date.now() + Math.random().toString(36).substr(2, 9),
        file: file,
        title: file.name.split('.').slice(0, -1).join('.'),
        category: 'Other',
        highlight: '',
        description: '',
        record_date: new Date().toISOString().slice(0, 16),
        tags: '',
        previewUrl: ''
    };
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            fileObj.previewUrl = e.target.result;
            renderUploadList();
        };
        reader.readAsDataURL(file);
    }
    
    uploadFiles.push(fileObj);
    renderUploadList();
}

function removeFileFromUploadList(id) {
    uploadFiles = uploadFiles.filter(f => f.id !== id);
    renderUploadList();
}

function updateFileData(id, field, value) {
    const file = uploadFiles.find(f => f.id === id);
    if (file) {
        file[field] = value;
    }
}

function renderUploadList() {
    const listEl = document.getElementById('uploadFileList');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    
    if (uploadFiles.length === 0) {
        document.getElementById('btnSubmitUpload').disabled = true;
        return;
    }
    
    document.getElementById('btnSubmitUpload').disabled = false;
    
    uploadFiles.forEach(item => {
        const catOptions = categories.map(c => `<option value="${c}" ${item.category === c ? 'selected' : ''}>${c}</option>`).join('');
        
        const previewEl = item.previewUrl 
            ? `<img src="${item.previewUrl}" class="w-16 h-16 object-cover rounded-lg">`
            : `<div class="w-16 h-16 bg-slate-800 rounded-lg flex items-center justify-center"><i class="fa-solid fa-file-pdf text-2xl text-red-400"></i></div>`;
            
        const html = `
            <div class="bg-[#1a1a2e] p-4 rounded-xl border border-slate-700 relative">
                <button type="button" class="absolute top-2 right-2 text-slate-400 hover:text-red-500 transition" onclick="removeFileFromUploadList('${item.id}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <div class="flex gap-4">
                    <div class="shrink-0 mt-1">${previewEl}</div>
                    <div class="flex-1 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Title</label>
                                <input type="text" class="w-full bg-[#16213e] border border-slate-600 rounded-lg px-3 py-1.5 text-sm" value="${item.title}" onchange="updateFileData('${item.id}', 'title', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Category</label>
                                <select class="w-full bg-[#16213e] border border-slate-600 rounded-lg px-3 py-1.5 text-sm" onchange="updateFileData('${item.id}', 'category', this.value)">
                                    ${catOptions}
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Highlight / Finding</label>
                                <input type="text" class="w-full bg-[#16213e] border border-slate-600 rounded-lg px-3 py-1.5 text-sm" value="${item.highlight}" onchange="updateFileData('${item.id}', 'highlight', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Date & Time</label>
                                <input type="datetime-local" class="w-full bg-[#16213e] border border-slate-600 rounded-lg px-3 py-1.5 text-sm" value="${item.record_date}" onchange="updateFileData('${item.id}', 'record_date', this.value)">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Description</label>
                            <textarea class="w-full bg-[#16213e] border border-slate-600 rounded-lg px-3 py-1.5 text-sm h-12" onchange="updateFileData('${item.id}', 'description', this.value)">${item.description}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
        listEl.insertAdjacentHTML('beforeend', html);
    });
}

function submitUpload() {
    if (uploadFiles.length === 0) return;
    
    if (!confirm('Are you sure you want to upload these files?')) return;
    
    const btn = document.getElementById('btnSubmitUpload');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Uploading...';
    
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('patient_id', document.getElementById('uploadPatientId').value);
    
    const apptId = document.getElementById('uploadAppointmentSelect').value;
    if (apptId) formData.append('appointment_id', apptId);
    
    uploadFiles.forEach(item => {
        formData.append('files[]', item.file);
        formData.append('titles[]', item.title);
        formData.append('categories[]', item.category);
        formData.append('highlights[]', item.highlight);
        formData.append('descriptions[]', item.description);
        formData.append('record_dates[]', item.record_date);
        formData.append('tags[]', item.tags);
    });
    
    fetch('api/files.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof showToast !== 'undefined') showToast('Success', 'Files uploaded successfully!');
            closeUploadModal();
            // Trigger an event or call a function to refresh the file list on the parent page
            if (typeof loadPatientFiles === 'function') {
                loadPatientFiles();
            }
        } else {
            if (typeof showToast !== 'undefined') showToast('Error', data.message || 'Upload failed', 'error');
            alert('Upload failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Upload failed due to network error.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-upload mr-2"></i> Confirm & Upload';
    });
}

// Camera Functions
async function openCameraCapture() {
    const container = document.getElementById('cameraContainer');
    const video = document.getElementById('cameraVideo');
    document.getElementById('dropZone').classList.add('hidden');
    container.classList.remove('hidden');
    
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        video.srcObject = stream;
    } catch (err) {
        alert('Camera access denied or unavailable.');
        closeCamera();
    }
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    canvas.toBlob((blob) => {
        const file = new File([blob], `capture_${Date.now()}.jpg`, { type: 'image/jpeg' });
        addFileToUploadList(file);
        closeCamera();
    }, 'image/jpeg', 0.85);
}

function closeCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('cameraContainer').classList.add('hidden');
    document.getElementById('dropZone').classList.remove('hidden');
}

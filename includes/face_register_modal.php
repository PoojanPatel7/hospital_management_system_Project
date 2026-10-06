<!-- Face Registration Modal -->
<div id="faceRegisterModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#16213e] text-white p-4 flex items-center justify-between shrink-0">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-face-viewfinder text-blue-400"></i> Register Face
            </h3>
            <button type="button" onclick="closeFaceRegisterModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-5 overflow-y-auto flex-1">
            <input type="hidden" id="faceRegStaffId" value="">
            <div class="text-sm text-slate-500 mb-4 text-center">
                Position face inside the frame. Wait for the green box to appear.
            </div>

            <div class="relative w-full bg-black rounded-xl overflow-hidden aspect-video shadow-inner mb-4 flex items-center justify-center">
                <video id="regVideoPreview" class="w-full h-full object-cover" playsinline muted></video>
                <canvas id="regOverlayCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                
                <div id="regScanOverlay" class="absolute inset-0 border-2 border-dashed border-white/30 m-8 rounded-full pointer-events-none"></div>
                
                <div id="regStatusBadge" class="absolute bottom-4 bg-black/70 backdrop-blur text-white text-xs font-bold px-3 py-1 rounded-full pointer-events-none">
                    Initializing...
                </div>
            </div>

            <div class="flex justify-between items-center gap-3 mt-4">
                <button type="button" onclick="closeFaceRegisterModal()" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" id="btnCaptureFace" onclick="captureAndRegisterFace()" disabled class="flex-1 px-4 py-2 bg-black text-white rounded-xl font-bold hover:bg-neutral-800 transition disabled:opacity-50 flex justify-center items-center gap-2">
                    <i class="fa-solid fa-camera"></i> Capture
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.min.js"></script>
<script>
    let regIsProcessing = false;
    let regStream = null;
    let currentDescriptor = null;
    let regFaceApiLoaded = false;

    async function loadRegFaceApiModels() {
        if (regFaceApiLoaded) return;
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        regFaceApiLoaded = true;
    }

    async function openFaceRegisterModal(staffId) {
        document.getElementById('faceRegStaffId').value = staffId;
        document.getElementById('faceRegisterModal').classList.remove('hidden');
        document.getElementById('regStatusBadge').innerText = 'Loading Models...';
        document.getElementById('btnCaptureFace').disabled = true;
        currentDescriptor = null;

        try {
            await loadRegFaceApiModels();
            document.getElementById('regStatusBadge').innerText = 'Starting Camera...';
            
            const video = document.getElementById('regVideoPreview');
            regStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false });
            video.srcObject = regStream;
            
            video.onloadedmetadata = () => {
                video.play();
                const canvas = document.getElementById('regOverlayCanvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                regIsProcessing = true;
                regDetectionLoop();
            };
        } catch (e) {
            console.error(e);
            alert("Camera error: " + e.message);
        }
    }

    function closeFaceRegisterModal() {
        regIsProcessing = false;
        if (regStream) {
            regStream.getTracks().forEach(track => track.stop());
            regStream = null;
        }
        document.getElementById('faceRegisterModal').classList.add('hidden');
    }

    async function regDetectionLoop() {
        if (!regIsProcessing) return;
        
        const video = document.getElementById('regVideoPreview');
        const canvas = document.getElementById('regOverlayCanvas');
        
        try {
            if (regFaceApiLoaded && video.videoWidth > 0) {
                const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                const btn = document.getElementById('btnCaptureFace');
                const badge = document.getElementById('regStatusBadge');
                
                if (detection) {
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#10b981'; // emerald
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    
                    if (detection.detection.score > 0.8) {
                        badge.innerText = 'Face Detected ✓';
                        badge.className = "absolute bottom-4 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md pointer-events-none";
                        currentDescriptor = Array.from(detection.descriptor);
                        btn.disabled = false;
                    } else {
                        badge.innerText = 'Move closer / brighter light';
                        badge.className = "absolute bottom-4 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md pointer-events-none";
                        btn.disabled = true;
                    }
                } else {
                    badge.innerText = 'No face detected';
                    badge.className = "absolute bottom-4 bg-black/70 backdrop-blur text-white text-xs font-bold px-3 py-1 rounded-full pointer-events-none";
                    btn.disabled = true;
                }
            }
        } catch (e) {
            console.error("Detection loop error", e);
        }
        
        if (regIsProcessing) {
            requestAnimationFrame(regDetectionLoop);
        }
    }

    async function captureAndRegisterFace() {
        if (!currentDescriptor) return;
        
        const staffId = document.getElementById('faceRegStaffId').value;
        const btn = document.getElementById('btnCaptureFace');
        const ogText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        try {
            const res = await fetch('api/attendance_selfie.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'register_face',
                    staff_id: staffId,
                    face_descriptor: currentDescriptor
                })
            });
            const data = await res.json();
            if (data.status === 'success') {
                if (typeof showToast === 'function') {
                    showToast('Success', 'Face registered successfully', 'success');
                } else {
                    alert('Face registered successfully');
                }
                closeFaceRegisterModal();
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = ogText;
                btn.disabled = false;
            }
        } catch (e) {
            alert('Error: ' + e.message);
            btn.innerHTML = ogText;
            btn.disabled = false;
        }
    }
</script>

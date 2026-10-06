<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'auth.php';

// Ensure the logged-in user is a staff member
if (empty($_SESSION['staff_id'])) {
    header("Location: dashboard.php");
    exit;
}

$hospital_id = $_SESSION['hospital_id'];
$staff_id = $_SESSION['staff_id'];

// Get Staff Info
$stmt = $conn->prepare("SELECT first_name, last_name, staff_code, role, shift, avatar FROM staff WHERE id = ? AND hospital_id = ?");
$stmt->bind_param("ii", $staff_id, $hospital_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
if (!$staff) {
    die("Staff record not found.");
}

$avatarUrl = $staff['avatar'] ? $staff['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($staff['first_name'].' '.$staff['last_name']).'&background=random';
$fullName = htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']);

include 'includes/header.php';
?>

<div class="max-w-md mx-auto w-full pt-4 pb-12 px-4 h-full flex flex-col">
    <!-- Header Card -->
    <div class="bg-[#16213e] rounded-2xl p-5 shadow-lg border border-slate-700/50 text-white mb-4 shrink-0">
        <div class="flex items-center gap-4">
            <img src="<?= $avatarUrl ?>" class="w-16 h-16 rounded-full border-2 border-slate-600 object-cover shadow-sm" alt="Avatar">
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold truncate"><?= $fullName ?></h2>
                <div class="text-xs text-slate-300"><?= htmlspecialchars($staff['staff_code']) ?></div>
                <div class="text-xs text-slate-300 truncate"><?= htmlspecialchars($staff['role']) ?></div>
                <div class="text-[10px] bg-slate-800 text-slate-300 mt-1.5 px-2 py-0.5 rounded inline-block">
                    Shift: <?= htmlspecialchars($staff['shift']) ?>
                </div>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-slate-700 flex justify-between items-center text-sm">
            <span class="text-slate-400"><i class="fa-regular fa-calendar mr-1"></i> <?= date('M d, Y') ?></span>
            <span id="attendanceStatus" class="font-bold py-1 px-3 rounded-full bg-slate-800 text-slate-400">⏳ Checking...</span>
        </div>
    </div>

    <!-- Main Action Area -->
    <div id="actionArea" class="flex-1 flex flex-col items-center justify-center bg-white rounded-2xl border border-slate-200 shadow-sm p-4 relative overflow-hidden">
        
        <div id="initialState" class="w-full text-center py-8">
            <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                <i class="fa-solid fa-camera text-4xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Mark Your Attendance</h3>
            <p class="text-sm text-slate-500 mb-6">You will need to verify your location and face to mark attendance for today.</p>
            
            <button onclick="startAttendanceProcess()" class="w-full bg-black hover:bg-neutral-800 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-camera-retro"></i> Mark Attendance
            </button>
        </div>

        <div id="cameraState" class="w-full h-full flex flex-col hidden">
            <div class="relative flex-1 bg-black rounded-xl overflow-hidden mb-4 min-h-[300px] shadow-inner">
                <video id="videoPreview" class="w-full h-full object-cover" playsinline muted></video>
                <canvas id="overlayCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                
                <div id="scanOverlay" class="absolute inset-0 border-4 border-dashed border-white/50 m-6 rounded-lg pointer-events-none"></div>
                
                <div class="absolute bottom-4 left-0 right-0 text-center pointer-events-none px-4">
                    <span id="faceStatus" class="bg-black/60 backdrop-blur-sm text-white text-xs font-bold px-3 py-1.5 rounded-full inline-block shadow-md">
                        Initializing Camera...
                    </span>
                </div>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-2">
                <div class="flex items-center gap-3 text-sm font-medium text-slate-700">
                    <div id="locIcon" class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot animate-pulse"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-500">Location Status</div>
                        <div id="locStatus" class="leading-tight">Checking GPS...</div>
                    </div>
                </div>
            </div>
            
            <button onclick="cancelProcess()" class="w-full bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold py-3 px-4 rounded-xl transition mt-auto">
                Cancel
            </button>
        </div>
        
        <div id="successState" class="w-full h-full flex flex-col items-center justify-center hidden text-center">
            <div class="w-24 h-24 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-5xl mb-6 shadow-sm border border-emerald-200">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Verified!</h3>
            <p class="text-slate-500 mb-6">Your attendance has been marked.</p>
            
            <div class="bg-slate-50 rounded-xl p-4 w-full border border-slate-200 mb-6 text-left space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Time:</span>
                    <span id="resultTime" class="font-bold text-slate-800"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Status:</span>
                    <span id="resultStatus" class="font-bold text-slate-800"></span>
                </div>
            </div>
            
            <a href="dashboard.php" class="w-full bg-black text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition block">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.min.js"></script>
<script src="js/selfie-attendance.js"></script>
<script>
    let isProcessing = false;
    let detectionInterval = null;
    let locData = null;
    let geoSettings = null;

    document.addEventListener("DOMContentLoaded", async () => {
        // Load initial status
        await fetchStatus();
    });

    async function fetchStatus() {
        try {
            const res = await fetch('api/attendance_selfie.php?action=get_status');
            const data = await res.json();
            if (data.status === 'success' && data.data) {
                const badge = document.getElementById('attendanceStatus');
                badge.className = "font-bold py-1 px-3 rounded-full text-xs ";
                if(data.data.status === 'Present') {
                    badge.classList.add('bg-emerald-500/20', 'text-emerald-400');
                    badge.innerHTML = `✅ Present (${data.data.check_in_time})`;
                } else if(data.data.status === 'Late') {
                    badge.classList.add('bg-amber-500/20', 'text-amber-400');
                    badge.innerHTML = `⚠️ Late (${data.data.check_in_time})`;
                } else if(data.data.status === 'Half Day') {
                    badge.classList.add('bg-orange-500/20', 'text-orange-400');
                    badge.innerHTML = `🌗 Half Day (${data.data.check_in_time})`;
                } else {
                    badge.classList.add('bg-rose-500/20', 'text-rose-400');
                    badge.innerHTML = `❌ ${data.data.status}`;
                }
                
                // Show success state since already marked
                showState('successState');
                document.getElementById('resultTime').innerText = data.data.check_in_time;
                document.getElementById('resultStatus').innerText = data.data.status;
            } else {
                document.getElementById('attendanceStatus').innerText = "⏳ Not Marked Yet";
                document.getElementById('attendanceStatus').className = "font-bold py-1 px-3 rounded-full bg-slate-800 text-slate-400 text-xs";
            }
        } catch (e) {
            console.error("Error fetching status:", e);
        }
    }

    function showState(stateId) {
        ['initialState', 'cameraState', 'successState'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });
        document.getElementById(stateId).classList.remove('hidden');
    }

    async function startAttendanceProcess() {
        showState('cameraState');
        document.getElementById('faceStatus').innerText = 'Loading AI Models...';
        
        try {
            // 1. Fetch GeoFence
            const geoRes = await fetch('api/attendance_selfie.php?action=get_geofence');
            const geoJson = await geoRes.json();
            if (geoJson.status === 'success') {
                geoSettings = geoJson.data;
            } else {
                throw new Error("Could not load geofence settings");
            }

            // 2. Fetch Face Descriptor
            const faceRes = await fetch('api/attendance_selfie.php?action=get_face_descriptor');
            const faceJson = await faceRes.json();
            if (faceJson.status === 'success' && faceJson.data) {
                registeredDescriptor = faceJson.data;
            } else {
                alert("You have not registered your face yet. Please contact admin.");
                cancelProcess();
                return;
            }

            // 3. Start Location Check in parallel
            startLocationCheck();

            // 4. Load Models & Start Camera
            await loadFaceApiModels();
            document.getElementById('faceStatus').innerText = 'Starting Camera...';
            
            const video = document.getElementById('videoPreview');
            await startCamera(video);
            
            // Wait for video dimensions to be set
            video.addEventListener('loadedmetadata', () => {
                const canvas = document.getElementById('overlayCanvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
            });
            
            document.getElementById('faceStatus').innerText = 'Scanning Face...';
            isProcessing = true;
            detectionLoop();
            
        } catch (e) {
            alert("Error: " + e.message);
            cancelProcess();
        }
    }

    async function startLocationCheck() {
        const locIcon = document.getElementById('locIcon');
        const locStatus = document.getElementById('locStatus');
        try {
            locData = await checkLocation(geoSettings.latitude, geoSettings.longitude, geoSettings.radius_meters);
            if (locData.withinRange) {
                locIcon.className = "w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0";
                locIcon.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
                locStatus.innerText = "Location Verified";
                locStatus.className = "text-emerald-600 font-bold";
            } else {
                locIcon.className = "w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0";
                locIcon.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
                locStatus.innerText = `Too far (${locData.distance}m away)`;
                locStatus.className = "text-rose-600 font-bold";
            }
        } catch (e) {
            locIcon.className = "w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0";
            locIcon.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
            locStatus.innerText = "Location Error: " + e.message;
            locStatus.className = "text-rose-600 font-bold";
            locData = null;
        }
    }

    async function detectionLoop() {
        if (!isProcessing) return;
        
        const video = document.getElementById('videoPreview');
        const canvas = document.getElementById('overlayCanvas');
        
        try {
            const result = await detectAndMatchFace(video);
            
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            if (result.detected) {
                const displaySize = { width: video.videoWidth, height: video.videoHeight };
                const box = result.box;
                
                ctx.lineWidth = 4;
                if (result.matched) {
                    ctx.strokeStyle = '#10b981'; // emerald
                    document.getElementById('faceStatus').innerText = 'Face Verified ✓';
                    document.getElementById('faceStatus').className = "bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-full inline-block shadow-md";
                    
                    // IF Location is also verified, submit!
                    if (locData && locData.withinRange) {
                        isProcessing = false; // Stop loop
                        await submitAttendance(result.confidence);
                        return; // Exit loop
                    }
                } else {
                    ctx.strokeStyle = '#ef4444'; // rose
                    document.getElementById('faceStatus').innerText = 'Face mismatch';
                    document.getElementById('faceStatus').className = "bg-rose-500 text-white text-xs font-bold px-3 py-1.5 rounded-full inline-block shadow-md";
                }
                
                ctx.strokeRect(box.x, box.y, box.width, box.height);
            } else {
                document.getElementById('faceStatus').innerText = 'Position face in frame';
                document.getElementById('faceStatus').className = "bg-black/60 backdrop-blur-sm text-white text-xs font-bold px-3 py-1.5 rounded-full inline-block shadow-md";
            }
            
        } catch (e) {
            console.error(e);
        }
        
        if (isProcessing) {
            requestAnimationFrame(detectionLoop);
        }
    }

    async function submitAttendance(confidence) {
        document.getElementById('faceStatus').innerText = 'Submitting...';
        stopCamera();
        
        try {
            const payload = {
                action: 'mark',
                face_verified: true,
                face_confidence: confidence,
                location_verified: true,
                latitude: locData.lat,
                longitude: locData.lng
            };
            
            const res = await fetch('api/attendance_selfie.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            
            if (json.status === 'success') {
                showState('successState');
                document.getElementById('resultTime').innerText = json.data.check_in_time;
                document.getElementById('resultStatus').innerText = json.data.status;
                fetchStatus(); // update header
            } else {
                alert("Error: " + json.message);
                cancelProcess();
            }
        } catch (e) {
            alert("Submission error: " + e.message);
            cancelProcess();
        }
    }

    function cancelProcess() {
        isProcessing = false;
        stopCamera();
        showState('initialState');
    }
    
    // Add cleanup on page unload
    window.addEventListener('beforeunload', stopCamera);
</script>

<?php include 'includes/footer.php'; ?>

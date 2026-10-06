// ================= HIGH-TECH PATIENT QR SCANNER CONTROLLER =================
let html5QrScannerInstance = null;
let currentFacingMode = "environment";
let isScannerActive = false;
let availableCameras = [];
let activeCameraIndex = 0;

// Web Audio API high-tech scanner chirp sound
function playScanBeep() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(950, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1450, ctx.currentTime + 0.12);

        gain.gain.setValueAtTime(0.25, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.14);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 0.14);
    } catch (e) {
        // Audio might be muted or blocked by policy
    }
}

function openQRScannerModal() {
    const modal = document.getElementById('qr-scanner-modal');
    if (!modal) return;
    modal.classList.remove('hidden');

    const successOverlay = document.getElementById('scanner-success-overlay');
    if (successOverlay) successOverlay.classList.add('hidden');

    const laser = document.getElementById('scanner-laser');
    if (laser) laser.classList.remove('hidden');

    const box = document.getElementById('scanner-viewport-box');
    if (box) box.className = "relative bg-slate-950 rounded-2xl overflow-hidden border-2 border-indigo-500/40 shadow-inner flex flex-col items-center justify-center min-h-[250px] sm:min-h-[270px]";

    // Auto-focus manual MRN input for immediate keyboard / USB scanner readiness
    const manualInput = document.getElementById('manual-qr-input');
    if (manualInput) {
        setTimeout(() => manualInput.focus(), 250);
    }

    startCameraScanner();
}

function closeQRScannerModal() {
    const modal = document.getElementById('qr-scanner-modal');
    if (modal) modal.classList.add('hidden');
    stopCameraScanner();
}

async function startCameraScanner() {
    const statusEl = document.getElementById('qr-scanner-status');
    const readerEl = document.getElementById('qr-reader');
    if (!readerEl) return;

    if (statusEl) {
        statusEl.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-1 text-cyan-300"></i> Initializing camera...';
        statusEl.className = "absolute bottom-2 left-2 right-2 text-center text-[11px] font-bold text-white bg-black/70 backdrop-blur-xs py-1.5 px-3 rounded-xl border border-white/10 truncate z-20";
    }

    try {
        await stopCameraScanner();

        if (typeof Html5Qrcode === 'undefined') {
            throw new Error('Html5Qrcode scanner library is not loaded');
        }

        html5QrScannerInstance = new Html5Qrcode("qr-reader");

        const qrSuccessCallback = (decodedText, decodedResult) => {
            console.log("QR decoded:", decodedText);
            handleDecodedQR(decodedText);
        };

        const config = {
            fps: 15,
            qrbox: { width: 230, height: 230 },
            aspectRatio: 1.0,
            showTorchButtonIfSupported: true
        };

        // 1. Try querying device cameras first
        let started = false;
        try {
            availableCameras = await Html5Qrcode.getCameras();
            if (availableCameras && availableCameras.length > 0) {
                // Find back / environment camera if possible
                let chosenId = availableCameras[0].id;
                const backCam = availableCameras.find(c => /(back|rear|environment)/i.test(c.label));
                if (backCam) {
                    chosenId = backCam.id;
                } else if (availableCameras.length > 1) {
                    // On mobile, the rear camera is usually the last listed device
                    chosenId = availableCameras[availableCameras.length - 1].id;
                }

                activeCameraIndex = availableCameras.findIndex(c => c.id === chosenId);
                await html5QrScannerInstance.start(chosenId, config, qrSuccessCallback, () => {});
                started = true;
            }
        } catch (camListErr) {
            console.log("Device camera list query note:", camListErr);
        }

        // 2. If getCameras direct start didn't trigger, try standard facingMode
        if (!started) {
            try {
                await html5QrScannerInstance.start(
                    { facingMode: currentFacingMode },
                    config,
                    qrSuccessCallback,
                    () => {}
                );
                started = true;
            } catch (facingErr) {
                // Fallback to front camera if environment camera is unavailable
                await html5QrScannerInstance.start(
                    { facingMode: "user" },
                    config,
                    qrSuccessCallback,
                    () => {}
                );
                started = true;
            }
        }

        isScannerActive = true;
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-cyan-400 font-extrabold"><i class="fa-solid fa-qrcode mr-1"></i> Live Viewfinder</span> • Align QR Code in box';
        }
    } catch (err) {
        console.warn("Live camera start notice:", err);
        isScannerActive = false;
        
        // Check if on mobile over insecure HTTP (e.g. port 8080)
        const isMobile = /Android|iPhone|iPad|iPod|Mobi/i.test(navigator.userAgent);
        const isInsecure = window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1';

        if (statusEl) {
            statusEl.innerHTML = '<span class="text-rose-400 font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Camera unavailable.</span> Check permissions or use MRN.';
        }
    }
}

async function stopCameraScanner() {
    if (html5QrScannerInstance && isScannerActive) {
        try {
            await html5QrScannerInstance.stop();
            html5QrScannerInstance.clear();
        } catch (e) {
            console.log("Scanner cleanup note:", e);
        }
        isScannerActive = false;
    }
}

async function toggleCameraFacing() {
    if (availableCameras && availableCameras.length > 1) {
        activeCameraIndex = (activeCameraIndex + 1) % availableCameras.length;
        const nextId = availableCameras[activeCameraIndex].id;
        try {
            await stopCameraScanner();
            html5QrScannerInstance = new Html5Qrcode("qr-reader");
            await html5QrScannerInstance.start(
                nextId,
                { fps: 15, qrbox: { width: 230, height: 230 } },
                (decodedText) => handleDecodedQR(decodedText),
                () => {}
            );
            isScannerActive = true;
            return;
        } catch (e) {
            console.warn("Switch camera error, falling back:", e);
        }
    }
    
    currentFacingMode = (currentFacingMode === "environment") ? "user" : "environment";
    await startCameraScanner();
}

// Trigger Phone Native Camera full-screen snapshot (Bypasses all HTTP / port 8080 restrictions)
function triggerMobileCameraSnap() {
    const fileInput = document.getElementById('qr-camera-file-input');
    if (fileInput) {
        fileInput.value = '';
        fileInput.click();
    }
}

async function handleQRFileSelected(files) {
    if (!files || !files.length) return;
    const file = files[0];

    const statusEl = document.getElementById('qr-scanner-status');
    if (statusEl) {
        statusEl.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-1 text-cyan-300"></i> Decoding captured photo...';
    }

    try {
        if (!html5QrScannerInstance) {
            html5QrScannerInstance = new Html5Qrcode("qr-reader");
        }
        const decodedText = await html5QrScannerInstance.scanFile(file, true);
        handleDecodedQR(decodedText);
    } catch (err) {
        console.error("Photo QR decode error:", err);
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-rose-400 font-bold"><i class="fa-solid fa-circle-xmark mr-1"></i> QR not detected.</span> Please hold steady and try again.';
        }
    }
}

function handleDecodedQR(rawText) {
    if (!rawText) return;

    // 1. Play high-tech physical scanner beep
    playScanBeep();

    // 2. Trigger high-tech visual HUD animation
    const laser = document.getElementById('scanner-laser');
    if (laser) laser.classList.add('hidden');

    const box = document.getElementById('scanner-viewport-box');
    if (box) {
        box.className = "relative bg-slate-950 rounded-2xl overflow-hidden border-2 border-emerald-400 ring-4 ring-emerald-400/40 shadow-inner flex flex-col items-center justify-center min-h-[250px] sm:min-h-[270px] transition-all";
    }

    const overlay = document.getElementById('scanner-success-overlay');
    const subtitle = document.getElementById('scanner-success-subtitle');
    if (overlay) {
        overlay.classList.remove('hidden');
    }

    stopCameraScanner();

    // 3. Resolve target URL
    let targetUrl = '';
    const clean = rawText.trim();

    if (clean.includes('token=')) {
        try {
            const urlObj = new URL(clean, window.location.origin);
            const token = urlObj.searchParams.get('token');
            if (token) {
                targetUrl = `patient_profile_qr.php?token=${encodeURIComponent(token)}`;
            } else {
                targetUrl = clean;
            }
        } catch (e) {
            const match = clean.match(/token=([a-zA-Z0-9-]+)/i);
            targetUrl = match ? `patient_profile_qr.php?token=${encodeURIComponent(match[1])}` : `patient_profile_qr.php?token=${encodeURIComponent(clean)}`;
        }
    } else if (clean.includes('/p/')) {
        const parts = clean.split('/p/');
        const token = parts[1] ? parts[1].split('?')[0].split('#')[0] : '';
        if (token) {
            targetUrl = `patient_profile_qr.php?token=${encodeURIComponent(token)}`;
        }
    } else if (/^(CP-\d{4}-\d+|PAT-\d+)$/i.test(clean)) {
        targetUrl = `patient_profile_qr.php?patient_id=${encodeURIComponent(clean.toUpperCase())}`;
    } else if (clean.length >= 20) {
        targetUrl = `patient_profile_qr.php?token=${encodeURIComponent(clean)}`;
    } else {
        targetUrl = `patient_profile_qr.php?patient_id=${encodeURIComponent(clean)}`;
    }

    if (subtitle) {
        subtitle.textContent = `Redirecting to Patient Dossier...`;
    }

    // 4. Smooth transition redirect
    setTimeout(() => {
        window.location.href = targetUrl;
    }, 600);
}

function handleManualQRLookup(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('manual-qr-input');
    if (!input || !input.value.trim()) return;

    const val = input.value.trim();
    handleDecodedQR(val);
}

// Global USB Barcode Scanner Gun Fast Keystroke Listener
let barcodeBuffer = '';
let lastKeyTime = Date.now();

document.addEventListener('keydown', (e) => {
    // Escape closes modal
    if (e.key === 'Escape') {
        const modal = document.getElementById('qr-scanner-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeQRScannerModal();
        }
        return;
    }

    // Do not intercept if typing normally in inputs other than scanner input
    const activeEl = document.activeElement;
    if (activeEl && activeEl.tagName === 'INPUT' && activeEl.id !== 'manual-qr-input') {
        return;
    }

    const now = Date.now();
    const timeDiff = now - lastKeyTime;
    lastKeyTime = now;

    // USB scanners transmit characters in very rapid bursts (< 45ms apart)
    if (timeDiff > 55) {
        barcodeBuffer = '';
    }

    if (e.key === 'Enter') {
        if (barcodeBuffer.length >= 4) {
            e.preventDefault();
            console.log("USB Barcode Gun scanned:", barcodeBuffer);
            openQRScannerModal();
            handleDecodedQR(barcodeBuffer);
            barcodeBuffer = '';
        }
    } else if (e.key.length === 1) {
        barcodeBuffer += e.key;
    }
});

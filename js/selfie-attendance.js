let faceApiLoaded = false;
let registeredDescriptor = null;
let currentStream = null;

async function loadFaceApiModels() {
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
    try {
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        faceApiLoaded = true;
        console.log("Face API Models loaded.");
    } catch (e) {
        console.error("Error loading face models:", e);
        throw e;
    }
}

async function startCamera(videoElement) {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        });
        videoElement.srcObject = stream;
        currentStream = stream;
        return new Promise((resolve) => {
            videoElement.onloadedmetadata = () => {
                videoElement.play();
                resolve(true);
            };
        });
    } catch (e) {
        console.error("Camera error:", e);
        throw e;
    }
}

function stopCamera() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
}

async function detectAndMatchFace(videoElement) {
    if (!faceApiLoaded) throw new Error("Models not loaded yet");
    
    const detection = await faceapi
        .detectSingleFace(videoElement)
        .withFaceLandmarks()
        .withFaceDescriptor();
    
    if (!detection) return { detected: false };
    
    if (!registeredDescriptor) return { detected: true, matched: false, reason: 'no_registered_face', box: detection.detection.box };
    
    const floatDescriptor = new Float32Array(registeredDescriptor);
    const distance = faceapi.euclideanDistance(detection.descriptor, floatDescriptor);
    const confidence = Math.max(0, 1 - distance);
    
    return {
        detected: true,
        matched: distance < 0.6,
        distance: distance,
        confidence: confidence,
        box: detection.detection.box,
        descriptor: Array.from(detection.descriptor)
    };
}

function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in meters
    const toRad = (deg) => deg * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

async function checkLocation(hospitalLat, hospitalLng, radiusMeters) {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation not supported'));
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const dist = haversineDistance(pos.coords.latitude, pos.coords.longitude, hospitalLat, hospitalLng);
                resolve({
                    withinRange: dist <= radiusMeters,
                    distance: Math.round(dist),
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    accuracy: pos.coords.accuracy
                });
            },
            (err) => reject(err),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
}

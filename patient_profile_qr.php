<?php
require_once 'db.php';

$token = trim($_GET['token'] ?? '');
$patient_id_param = trim($_GET['patient_id'] ?? $_GET['id'] ?? '');

$patient = null;
if (!empty($token)) {
    // Lookup patient directly by unique lifetime QR token or ID
    $stmt = $conn->prepare("SELECT * FROM patients WHERE qr_token = ? OR id = ?");
    $stmt->bind_param("ss", $token, $token);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
}

if (!$patient && !empty($patient_id_param)) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("s", $patient_id_param);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
}

if (!$patient) {
    ?>
    <?php include 'includes/header.php'; ?>
    <div class="max-w-xl mx-auto my-12 bg-white rounded-3xl p-8 shadow-xl border border-slate-200 text-center space-y-4">
        <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-3xl mx-auto shadow-inner">
            <i class="fa-solid fa-qrcode"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900">Patient QR Code Not Found</h2>
        <p class="text-xs text-slate-500 leading-relaxed">No patient record in the system matches the scanned QR token or MRN. Please verify the code or search the patient directory.</p>
        <div class="pt-2 flex flex-col sm:flex-row gap-2 justify-center">
            <button onclick="openQRScannerModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 shadow">
                <i class="fa-solid fa-camera"></i> Scan Another QR
            </button>
            <a href="patients.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-address-book"></i> Patient Directory
            </a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <?php
    exit;
}

$patient_id = $patient['id'];

// Ensure lifetime QR token is generated if missing
if (empty($patient['qr_token'])) {
    if (!function_exists('generateUUIDv4')) {
        function generateUUIDv4() {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }
    $newToken = generateUUIDv4();
    $upd = $conn->prepare("UPDATE patients SET qr_token = ?, qr_generated_at = NOW() WHERE id = ?");
    $upd->bind_param("ss", $newToken, $patient['id']);
    $upd->execute();
    $token = $newToken;
    $patient['qr_token'] = $newToken;
} else {
    $token = $patient['qr_token'];
}

// Normalize demographics accurately
$age = trim((string)($patient['age'] ?? ''));
$gender = trim((string)($patient['gender'] ?? ''));
$blood = trim((string)($patient['blood_group'] ?? ''));
$demo = trim((string)($patient['demographics'] ?? ''));

if (($age === '' || $age === '0') && !empty($demo)) {
    if (preg_match('/(\d+)\s*(Y|y|Years?)/i', $demo, $m)) $age = $m[1];
}
if (($gender === '' || strtolower($gender) === 'other') && !empty($demo)) {
    if (preg_match('/\b(Male|Female)\b/i', $demo, $m)) $gender = ucfirst(strtolower($m[1]));
}
if (empty($blood) && !empty($demo)) {
    if (preg_match('/\b(A|B|AB|O)[+-]\b/i', $demo, $m)) $blood = strtoupper($m[0]);
}

$fullName = trim(($patient['name'] ?? '') . ' ' . ($patient['surname'] ?? ''));
if (empty($fullName)) $fullName = 'Patient Record';
$initials = strtoupper(substr($patient['name'] ?? 'P', 0, 1) . substr($patient['surname'] ?? 'R', 0, 1));
$isStaffLoggedIn = isset($_SESSION['hospital_id']) || isset($_SESSION['staff_id']);

// Check if patient already has an active appointment / token today or active in queue
$today = date('Y-m-d');
$todayApptStmt = $conn->prepare("
    SELECT a.*, d.name AS doctor_name, d.degree,
           GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') AS doctor_specialties
    FROM appointments a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
    LEFT JOIN departments dep ON dc.department_id = dep.id
    WHERE a.patient_id = ? AND (a.date = ? OR a.stage >= 1) AND a.status NOT IN ('Cancelled')
    GROUP BY a.id
    ORDER BY (a.token_number IS NOT NULL AND a.token_number > 0) DESC, a.id DESC 
    LIMIT 1
");
$todayApptStmt->bind_param("ss", $patient_id, $today);
$todayApptStmt->execute();
$existingTodayAppt = $todayApptStmt->get_result()->fetch_assoc();

$activeTokenNum = null;
if (!empty($existingTodayAppt['token_number']) && (int)$existingTodayAppt['token_number'] > 0) {
    $activeTokenNum = (int)$existingTodayAppt['token_number'];
}
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto pb-12 space-y-6">
    <!-- Header / Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-[2rem] shadow-xl border border-indigo-500/30 overflow-hidden relative p-6 sm:p-8 text-white">
        <div class="flex flex-col md:flex-row gap-6 items-center">
            <!-- Patient Avatar Badge -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white/10 backdrop-blur-md rounded-2xl shadow-lg border-2 border-indigo-400/30 flex items-center justify-center text-3xl sm:text-4xl font-black text-indigo-300 shrink-0">
                <?php echo htmlspecialchars($initials); ?>
            </div>
            
            <div class="flex-1 text-center md:text-left min-w-0">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-2">
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                        <i class="fa-solid fa-qrcode text-[11px]"></i> Lifetime QR Verified
                    </span>
                    <span class="font-mono text-indigo-200 text-xs sm:text-sm font-bold bg-indigo-500/20 px-2.5 py-0.5 rounded-lg border border-indigo-400/30">
                        MRN: <?php echo htmlspecialchars($patient_id); ?>
                    </span>
                    <!-- Active Token Badge Pill (If Token is already assigned) -->
                    <span id="top-token-badge-pill" class="<?php echo $activeTokenNum ? 'inline-flex' : 'hidden'; ?> font-mono text-slate-950 text-xs sm:text-sm font-black bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 px-3 py-0.5 rounded-lg border-2 border-amber-300 shadow-md items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-ticket text-slate-950"></i>
                        <span id="top-token-badge-text">TOKEN #<?php echo $activeTokenNum ? str_pad($activeTokenNum, 2, '0', STR_PAD_LEFT) : '--'; ?></span>
                    </span>
                </div>
                
                <h1 class="text-2xl sm:text-3xl font-black text-white mb-2 truncate">
                    <?php echo htmlspecialchars($fullName); ?>
                </h1>
                
                <!-- Patient Demographics Ribbon -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 sm:gap-4 text-xs sm:text-sm text-indigo-200/90 font-medium">
                    <span class="bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                        <i class="fa-solid fa-calendar-days text-indigo-400 mr-1.5"></i> Age: <strong class="text-white"><?php echo htmlspecialchars($age ?: '--'); ?></strong>
                    </span>
                    <span class="bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                        <i class="fa-solid fa-venus-mars text-indigo-400 mr-1.5"></i> Sex: <strong class="text-white"><?php echo htmlspecialchars($gender ?: '--'); ?></strong>
                    </span>
                    <span class="bg-rose-500/20 px-2.5 py-1 rounded-lg border border-rose-400/30 text-rose-200 font-bold">
                        <i class="fa-solid fa-droplet text-rose-400 mr-1.5"></i> Blood: <?php echo htmlspecialchars($blood ?: '--'); ?>
                    </span>
                    <span class="bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                        <i class="fa-solid fa-phone text-indigo-400 mr-1.5"></i> <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $patient['phone'] ?? '')); ?>" class="hover:text-white"><?php echo htmlspecialchars($patient['phone'] ?? 'Not provided'); ?></a>
                    </span>
                </div>

                <!-- Prominent Hero Active Token Box if already assigned -->
                <div id="top-active-token-banner" class="<?php echo $activeTokenNum ? 'block' : 'hidden'; ?> mt-4 p-4 rounded-2xl bg-gradient-to-r from-amber-500/25 via-indigo-950 to-slate-900 border-2 border-amber-400/60 shadow-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                <i class="fa-solid fa-ticket"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 px-2 py-0.5 rounded-full font-black">
                                        Today's Assigned OPD Token
                                    </span>
                                    <span id="top-banner-token-status" class="text-xs font-bold text-emerald-300 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <?php echo htmlspecialchars($existingTodayAppt['status'] ?? 'Checked-In'); ?>
                                    </span>
                                </div>
                                <div class="text-xl sm:text-2xl font-black text-white mt-0.5">
                                    <span id="top-banner-token-num" class="text-amber-300">TOKEN #<?php echo $activeTokenNum ? str_pad($activeTokenNum, 2, '0', STR_PAD_LEFT) : '--'; ?></span>
                                    <?php if (!empty($existingTodayAppt['slot'])): ?>
                                    <span id="top-banner-token-slot" class="text-xs font-mono font-medium text-indigo-200 ml-2">(Slot: <?php echo htmlspecialchars($existingTodayAppt['slot']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <div id="top-banner-token-doc" class="text-xs text-indigo-200 mt-0.5 font-medium">
                                    <?php if (!empty($existingTodayAppt['doctor_name'])): ?>
                                    Consulting Doctor: <strong class="text-white">Dr. <?php echo htmlspecialchars($existingTodayAppt['doctor_name']); ?></strong>
                                    <?php if (!empty($existingTodayAppt['doctor_specialties'])): ?>
                                    &bull; <?php echo htmlspecialchars($existingTodayAppt['doctor_specialties']); ?>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="queue.php" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs transition shadow-md flex items-center justify-center gap-2">
                                <i class="fa-solid fa-bars-staggered"></i>
                                <span>Track in Live Pipeline</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex-shrink-0 flex flex-wrap md:flex-col gap-2.5 w-full md:w-auto justify-center">
                <a href="patient_profile.php?id=<?php echo urlencode($patient_id); ?>" class="bg-white hover:bg-slate-100 text-slate-900 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md">
                    <i class="fa-solid fa-folder-open text-indigo-600"></i> Full Clinical Dossier
                </a>
                <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition">
                    <i class="fa-solid fa-print"></i> Print QR Summary
                </button>
            </div>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Patient Card & Today's Visit Status -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Today's Visit Status (Module 6 Target) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-indigo-600"></i>
                        <span>Today's Visit &amp; Token</span>
                    </h3>
                    <span id="today-visit-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Checking...</span>
                </div>
                <div id="today-visit-container" class="space-y-3">
                    <div class="animate-pulse space-y-2 py-4">
                        <div class="h-4 bg-slate-100 rounded w-3/4"></div>
                        <div class="h-10 bg-slate-100 rounded-xl"></div>
                        <div class="h-4 bg-slate-100 rounded w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Patient Identification Details Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                    <i class="fa-solid fa-id-card text-indigo-600"></i>
                    <span>Patient Identification</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">MRN / ID</span>
                        <span class="font-mono font-bold text-slate-800"><?php echo htmlspecialchars($patient_id); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">Full Name</span>
                        <span class="font-bold text-slate-900"><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                    <?php if (!empty($patient['father_name'])): ?>
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">Father's Name</span>
                        <span class="font-bold text-slate-800"><?php echo htmlspecialchars($patient['father_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">Age & Gender</span>
                        <span class="font-bold text-slate-800"><?php echo htmlspecialchars($age ?: '--'); ?> • <?php echo htmlspecialchars($gender ?: '--'); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">Blood Group</span>
                        <span class="font-bold text-rose-600">🩸 <?php echo htmlspecialchars($blood ?: '--'); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-50">
                        <span class="text-slate-400 font-bold">Contact Phone</span>
                        <span class="font-bold text-blue-600 font-mono"><?php echo htmlspecialchars($patient['phone'] ?: 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($patient['emergency_contact_name']) || !empty($patient['emergency_contact_phone'])): ?>
                    <div class="pt-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Emergency Contact</span>
                        <div class="font-bold text-slate-800 mt-0.5"><?php echo htmlspecialchars($patient['emergency_contact_name'] ?: 'Not recorded'); ?></div>
                        <?php if (!empty($patient['emergency_contact_phone'])): ?>
                        <div class="text-[11px] font-mono text-rose-600 font-bold"><?php echo htmlspecialchars($patient['emergency_contact_phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Recent Appointments & Medical Files -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Recent Appointments List -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                        <span>Recent Visits &amp; Consultations</span>
                    </h3>
                    <span id="appt-count-badge" class="px-2 py-0.5 rounded-full text-[10px] bg-blue-100 text-blue-800 font-extrabold">...</span>
                </div>
                <div id="history-container" class="space-y-3">
                    <div class="text-center text-slate-400 py-6 text-xs font-medium">
                        <i class="fa-solid fa-circle-notch fa-spin text-base text-indigo-500 mb-2 block"></i>
                        Loading visit history...
                    </div>
                </div>
            </div>

            <!-- Medical Files & Doctor Prescriptions Gallery -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-file-prescription text-teal-600 text-sm"></i>
                            <span>Patient Medical Files &amp; Doctor Prescriptions</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Handwritten letterhead pads, prescriptions, diagnostic lab reports &amp; medical scans</p>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                        <span id="files-count-badge" class="px-2 py-0.5 rounded-full text-[10px] bg-teal-100 text-teal-800 font-extrabold">0</span>
                        <button type="button" onclick="openDirectUploadModal()" class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Add / Upload Files</span>
                        </button>
                        <a href="patient_profile.php?id=<?php echo urlencode($patient_id); ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <i class="fa-solid fa-folder-open"></i>
                            <span class="hidden sm:inline">Gallery</span>
                        </a>
                    </div>
                </div>

                <div id="files-container" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="col-span-full text-center text-slate-400 py-6 text-xs font-medium">
                        <i class="fa-solid fa-circle-notch fa-spin text-base text-teal-500 mb-2 block"></i>
                        Loading medical files...
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= DIRECT MULTI-IMAGE UPLOAD MODAL ================= -->
<div id="direct-upload-modal" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 relative my-8 shadow-2xl border border-slate-200">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-black shadow-xs">
          <i class="fa-solid fa-cloud-arrow-up"></i>
        </div>
        <div>
          <h3 class="text-sm font-black text-slate-900 leading-tight">Add Medical Files &amp; Doctor Prescriptions</h3>
          <p class="text-[11px] text-slate-500 font-medium">Patient: <?php echo htmlspecialchars($fullName); ?> (<?php echo htmlspecialchars($patient_id); ?>)</p>
        </div>
      </div>
      <button type="button" onclick="closeDirectUploadModal()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <form id="direct-upload-form" onsubmit="submitDirectUpload(event)" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>" />

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Document Type / Category <span class="text-rose-500">*</span></label>
        <select name="categories[]" id="upload-category" required class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 outline-none">
          <option value="Doctor Letterhead Pad / Prescription" selected>Doctor Letterhead Pad / Prescription</option>
          <option value="Diagnostic Lab Report">Diagnostic Lab Report</option>
          <option value="Medical Scan / X-Ray">Medical Scan / X-Ray / CT</option>
          <option value="Discharge Summary">Discharge Summary</option>
          <option value="Other Medical Record">Other Medical Record</option>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Date on Document <span class="text-rose-500">*</span></label>
          <input type="date" name="record_dates[]" id="upload-date" value="<?php echo date('Y-m-d'); ?>" required class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 outline-none" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Title / Note (Optional)</label>
          <input type="text" name="titles[]" id="upload-title" placeholder="e.g. Doctor Letterhead Pad" class="w-full text-xs font-medium border border-slate-300 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 outline-none" />
        </div>
      </div>

      <!-- Multiple Image Selector / Mobile Camera Picker -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Select Images or Snap Photos (Multiple allowed) <span class="text-rose-500">*</span></label>
        <div class="border-2 border-dashed border-teal-300 hover:border-teal-500 rounded-2xl p-5 text-center bg-teal-50/40 hover:bg-teal-50 transition cursor-pointer relative" onclick="document.getElementById('direct-file-input').click()">
          <input type="file" id="direct-file-input" name="files[]" multiple accept="image/*,.pdf" capture="environment" onchange="handleDirectFileSelect(this)" class="hidden" required />
          <div class="w-12 h-12 rounded-2xl bg-white text-teal-600 flex items-center justify-center text-xl mx-auto shadow-xs border border-teal-100 mb-2">
            <i class="fa-solid fa-camera-rotate"></i>
          </div>
          <div class="text-xs font-extrabold text-slate-800">Tap to Snap Photo or Choose Multiple Images</div>
          <p class="text-[11px] text-slate-500 mt-1">Supports doctor pads, prescriptions, reports &amp; scans (JPG, PNG, PDF)</p>
          <div id="file-selection-preview" class="mt-2 text-xs font-black text-teal-700 hidden"></div>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" id="btn-submit-direct-upload" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-extrabold text-xs sm:text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-md cursor-pointer">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Upload Files Now</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ================= QUICK WALK-IN APPOINTMENT MODAL ================= -->
<div id="walkin-modal" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 relative my-8 shadow-2xl border border-slate-200">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black shadow-xs">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
          <h3 class="text-sm font-black text-slate-900 leading-tight">Create Walk-in Appointment</h3>
          <p class="text-[11px] text-slate-500 font-medium">For: <?php echo htmlspecialchars($fullName); ?> (<?php echo htmlspecialchars($patient_id); ?>)</p>
        </div>
      </div>
      <button type="button" onclick="closeWalkinModal()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <form onsubmit="submitWalkinAppointment(event)" class="space-y-3.5">
      <div>
        <label for="walkin-doc-select" class="block text-xs font-bold text-slate-700 mb-1">Consulting Doctor <span class="text-rose-500">*</span></label>
        <select id="walkin-doc-select" required onchange="onWalkinDocChange(this.value)" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
          <option value="">Loading active doctors...</option>
        </select>
      </div>

      <div>
        <label for="walkin-type-select" class="block text-xs font-bold text-slate-700 mb-1">Consultation Type</label>
        <select id="walkin-type-select" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
          <option value="General Consultation" selected>General Consultation</option>
          <option value="Emergency OPD">Emergency OPD</option>
          <option value="Follow-up Consultation">Follow-up Consultation</option>
          <option value="Routine Health Checkup">Routine Health Checkup</option>
        </select>
      </div>

      <div>
        <label for="walkin-symptoms-input" class="block text-xs font-bold text-slate-700 mb-1">Primary Symptoms / Reason</label>
        <input 
          type="text" 
          id="walkin-symptoms-input" 
          placeholder="e.g. Fever, body ache, dressing change" 
          class="w-full text-xs font-medium border border-slate-300 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none"
        >
      </div>

      <!-- Typed Token Input Field with Live Check -->
      <div class="bg-indigo-50/70 border-2 border-indigo-200 rounded-2xl p-3.5 shadow-xs space-y-2">
        <div class="flex items-center justify-between gap-3">
          <div>
            <label for="walkin-token-input" class="text-xs font-black uppercase tracking-wider text-slate-800 block">
              <i class="fa-solid fa-keyboard text-indigo-600 mr-1"></i> Give Token by Typing: <span class="text-rose-500">*</span>
            </label>
            <span class="text-[10px] text-slate-500 font-medium">Type token number given on patient slip</span>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <span class="text-sm font-black text-indigo-700">#</span>
            <input 
              type="number" 
              id="walkin-token-input" 
              min="1" 
              placeholder="Type #" 
              value="" 
              oninput="liveCheckModalWalkinToken(this.value)"
              class="w-24 text-center font-black text-xl text-indigo-700 bg-white border-2 border-indigo-400 rounded-xl py-1 px-2 focus:ring-2 focus:ring-indigo-500 outline-none transition"
              autocomplete="off"
            />
          </div>
        </div>
        <div id="walkin-token-feedback" class="text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1">
          <i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" id="btn-submit-walkin" disabled class="w-full py-3 bg-slate-200 text-slate-400 font-extrabold text-xs sm:text-sm rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed">
          <i class="fa-solid fa-ticket-simple"></i>
          <span>Book Appointment &amp; Assign Token</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const patientId = "<?php echo htmlspecialchars($patient_id); ?>";
const qrToken = "<?php echo htmlspecialchars($token); ?>";

// Format date into client requested highlight format: 6-May-2020
function formatHighlightDate(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const day = d.getDate();
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    } catch(e) {
        return dateStr;
    }
}

async function loadPatientData() {
    try {
        const [dossierRes, bookingRes] = await Promise.all([
            fetch(`api/history.php?action=get_dossier&patient_id=${encodeURIComponent(patientId)}&token=${encodeURIComponent(qrToken)}`),
            fetch(`api/booking.php?action=get_patient_today_booking&patient_id=${encodeURIComponent(patientId)}`)
        ]);
        const dossierData = await dossierRes.json();
        const bookingData = await bookingRes.json();
        
        const dossier = (dossierData.status === 'success' && dossierData.dossier) ? dossierData.dossier : { appointments: [], files: [] };
        const bookings = (bookingData.status === 'success') ? bookingData : { bookings: [], all_online_bookings: [], default_suggested_token: 1 };

        renderTodayStatus(dossier, bookings);
        renderHistory(dossier.appointments || []);
        if (document.getElementById('files-container')) {
            renderFiles(dossier.files || []);
        }
    } catch (err) {
        console.error('Error loading QR dossier:', err);
    }
}

// Web Audio API beep sound for check-in confirmation
function playCheckinBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(660, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.16);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.16);
    } catch(e) {}
}

let activeDoctorsList = [];
async function getDoctorsList() {
    if (activeDoctorsList.length) return activeDoctorsList;
    try {
        const res = await fetch('api/booking.php?action=get_all_doctors_with_schedules');
        const data = await res.json();
        if (data.status === 'success' && data.doctors) {
            activeDoctorsList = data.doctors;
        }
    } catch(e) {}
    return activeDoctorsList;
}

// Live Token Collision Validation Handlers
const tokenCheckDebounceTimers = {};

async function liveCheckToken(apptId, doctorId, val) {
    const tokenVal = parseInt(val);
    const feedback = document.getElementById(`token-feedback-${apptId}`);
    const input = document.getElementById(`manual-token-${apptId}`);
    const btn = document.getElementById(`btn-assign-token-${apptId}`);

    if (tokenCheckDebounceTimers[`token_${apptId}`]) {
        clearTimeout(tokenCheckDebounceTimers[`token_${apptId}`]);
    }

    if (!val || isNaN(tokenVal) || tokenVal <= 0) {
        if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability';
            feedback.className = 'text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1';
        }
        if (input) {
            input.classList.remove('border-rose-500', 'border-emerald-500', 'bg-rose-50', 'bg-emerald-50');
            input.classList.add('border-indigo-400', 'bg-indigo-50/50');
        }
        if (btn) {
            btn.disabled = true;
            btn.className = 'w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
        }
        return;
    }

    if (feedback) {
        feedback.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-indigo-500"></i> Checking Token #${tokenVal}...`;
        feedback.className = 'text-[11px] font-bold text-indigo-600 pt-1 border-t border-indigo-100 flex items-center gap-1';
    }

    tokenCheckDebounceTimers[`token_${apptId}`] = setTimeout(async () => {
        try {
            const today = new Date().toISOString().split('T')[0];
            const url = `api/booking.php?action=check_token&doctor_id=${encodeURIComponent(doctorId)}&date=${today}&token_number=${tokenVal}&exclude_appointment_id=${apptId}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.is_available) {
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600"></i> <span class="text-emerald-700 font-bold">✓ Token #${tokenVal} is Available</span>`;
                    feedback.className = 'text-[11px] font-bold text-emerald-700 pt-1 border-t border-emerald-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-rose-500', 'border-indigo-400', 'bg-rose-50', 'bg-indigo-50/50');
                    input.classList.add('border-emerald-500', 'bg-emerald-50');
                }
                if (btn) {
                    btn.disabled = false;
                    btn.className = 'w-full bg-gradient-to-r from-indigo-600 to-teal-600 hover:from-indigo-700 hover:to-teal-700 text-white font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer';
                }
            } else {
                const warnMsg = data.message || `⚠️ Token #${tokenVal} is ALREADY GIVEN to someone today!`;
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-600"></i> <span class="text-rose-600 font-bold">${warnMsg}</span>`;
                    feedback.className = 'text-[11px] font-bold text-rose-600 pt-1 border-t border-rose-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-emerald-500', 'border-indigo-400', 'bg-emerald-50', 'bg-indigo-50/50');
                    input.classList.add('border-rose-500', 'bg-rose-50');
                }
                if (btn) {
                    btn.disabled = true;
                    btn.className = 'w-full bg-rose-100 text-rose-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
                }
            }
        } catch (e) {
            console.error('Error checking token:', e);
        }
    }, 200);
}

function onInlineDocChange(docId) {
    const tokenInput = document.getElementById('inline-walkin-token');
    if (tokenInput && tokenInput.value) {
        liveCheckWalkinToken(tokenInput.value);
    }
}

async function liveCheckWalkinToken(val) {
    const docSelect = document.getElementById('inline-walkin-doc');
    const doctorId = docSelect ? docSelect.value : '';
    const feedback = document.getElementById('inline-walkin-token-feedback');
    const input = document.getElementById('inline-walkin-token');
    const btn = document.getElementById('btn-submit-inline-walkin');
    const tokenVal = parseInt(val);

    if (tokenCheckDebounceTimers['inline_walkin']) {
        clearTimeout(tokenCheckDebounceTimers['inline_walkin']);
    }

    if (!doctorId) {
        if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-user-doctor text-amber-500"></i> Please choose a consulting doctor first';
            feedback.className = 'text-[11px] font-bold text-amber-700 pt-1 border-t border-amber-100 flex items-center gap-1';
        }
        if (btn) {
            btn.disabled = true;
            btn.className = 'w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
        }
        return;
    }

    if (!val || isNaN(tokenVal) || tokenVal <= 0) {
        if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability';
            feedback.className = 'text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1';
        }
        if (input) {
            input.classList.remove('border-rose-500', 'border-emerald-500', 'bg-rose-50', 'bg-emerald-50');
            input.classList.add('border-indigo-400', 'bg-indigo-50/50');
        }
        if (btn) {
            btn.disabled = true;
            btn.className = 'w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
        }
        return;
    }

    if (feedback) {
        feedback.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-indigo-500"></i> Checking Token #${tokenVal}...`;
        feedback.className = 'text-[11px] font-bold text-indigo-600 pt-1 border-t border-indigo-100 flex items-center gap-1';
    }

    tokenCheckDebounceTimers['inline_walkin'] = setTimeout(async () => {
        try {
            const today = new Date().toISOString().split('T')[0];
            const url = `api/booking.php?action=check_token&doctor_id=${encodeURIComponent(doctorId)}&date=${today}&token_number=${tokenVal}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.is_available) {
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600"></i> <span class="text-emerald-700 font-bold">✓ Token #${tokenVal} is Available</span>`;
                    feedback.className = 'text-[11px] font-bold text-emerald-700 pt-1 border-t border-emerald-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-rose-500', 'border-indigo-400', 'bg-rose-50', 'bg-indigo-50/50');
                    input.classList.add('border-emerald-500', 'bg-emerald-50');
                }
                if (btn) {
                    btn.disabled = false;
                    btn.className = 'w-full bg-gradient-to-r from-indigo-600 to-teal-600 hover:from-indigo-700 hover:to-teal-700 text-white font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer';
                }
            } else {
                const warnMsg = data.message || `⚠️ Token #${tokenVal} is ALREADY GIVEN to someone today!`;
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-600"></i> <span class="text-rose-600 font-bold">${warnMsg}</span>`;
                    feedback.className = 'text-[11px] font-bold text-rose-600 pt-1 border-t border-rose-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-emerald-500', 'border-indigo-400', 'bg-emerald-50', 'bg-indigo-50/50');
                    input.classList.add('border-rose-500', 'bg-rose-50');
                }
                if (btn) {
                    btn.disabled = true;
                    btn.className = 'w-full bg-rose-100 text-rose-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
                }
            }
        } catch (e) {
            console.error('Error checking walkin token:', e);
        }
    }, 200);
}

function onWalkinDocChange(docId) {
    const tokenInput = document.getElementById('walkin-token-input');
    if (tokenInput && tokenInput.value) {
        liveCheckModalWalkinToken(tokenInput.value);
    }
}

async function liveCheckModalWalkinToken(val) {
    const docSelect = document.getElementById('walkin-doc-select');
    const doctorId = docSelect ? docSelect.value : '';
    const feedback = document.getElementById('walkin-token-feedback');
    const input = document.getElementById('walkin-token-input');
    const btn = document.getElementById('btn-submit-walkin');
    const tokenVal = parseInt(val);

    if (tokenCheckDebounceTimers['modal_walkin']) {
        clearTimeout(tokenCheckDebounceTimers['modal_walkin']);
    }

    if (!doctorId) {
        if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-user-doctor text-amber-500"></i> Please choose a consulting doctor first';
            feedback.className = 'text-[11px] font-bold text-amber-700 pt-1 border-t border-amber-100 flex items-center gap-1';
        }
        if (btn) {
            btn.disabled = true;
            btn.className = 'w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
        }
        return;
    }

    if (!val || isNaN(tokenVal) || tokenVal <= 0) {
        if (feedback) {
            feedback.innerHTML = '<i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability';
            feedback.className = 'text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1';
        }
        if (input) {
            input.classList.remove('border-rose-500', 'border-emerald-500', 'bg-rose-50', 'bg-emerald-50');
            input.classList.add('border-indigo-400', 'bg-indigo-50/50');
        }
        if (btn) {
            btn.disabled = true;
            btn.className = 'w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
        }
        return;
    }

    if (feedback) {
        feedback.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-indigo-500"></i> Checking Token #${tokenVal}...`;
        feedback.className = 'text-[11px] font-bold text-indigo-600 pt-1 border-t border-indigo-100 flex items-center gap-1';
    }

    tokenCheckDebounceTimers['modal_walkin'] = setTimeout(async () => {
        try {
            const today = new Date().toISOString().split('T')[0];
            const url = `api/booking.php?action=check_token&doctor_id=${encodeURIComponent(doctorId)}&date=${today}&token_number=${tokenVal}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.is_available) {
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600"></i> <span class="text-emerald-700 font-bold">✓ Token #${tokenVal} is Available</span>`;
                    feedback.className = 'text-[11px] font-bold text-emerald-700 pt-1 border-t border-emerald-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-rose-500', 'border-indigo-400', 'bg-rose-50', 'bg-indigo-50/50');
                    input.classList.add('border-emerald-500', 'bg-emerald-50');
                }
                if (btn) {
                    btn.disabled = false;
                    btn.className = 'w-full bg-gradient-to-r from-indigo-600 to-teal-600 hover:from-indigo-700 hover:to-teal-700 text-white font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer';
                }
            } else {
                const warnMsg = data.message || `⚠️ Token #${tokenVal} is ALREADY GIVEN to someone today!`;
                if (feedback) {
                    feedback.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-600"></i> <span class="text-rose-600 font-bold">${warnMsg}</span>`;
                    feedback.className = 'text-[11px] font-bold text-rose-600 pt-1 border-t border-rose-100 flex items-center gap-1';
                }
                if (input) {
                    input.classList.remove('border-emerald-500', 'border-indigo-400', 'bg-emerald-50', 'bg-indigo-50/50');
                    input.classList.add('border-rose-500', 'bg-rose-50');
                }
                if (btn) {
                    btn.disabled = true;
                    btn.className = 'w-full bg-rose-100 text-rose-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed';
                }
            }
        } catch (e) {
            console.error('Error checking modal walkin token:', e);
        }
    }, 200);
}

function updateTopTokenBanner(hasToken, tokenVal, appt) {
    const pill = document.getElementById('top-token-badge-pill');
    const pillText = document.getElementById('top-token-badge-text');
    const banner = document.getElementById('top-active-token-banner');
    const bannerNum = document.getElementById('top-banner-token-num');
    const bannerStatus = document.getElementById('top-banner-token-status');
    const bannerSlot = document.getElementById('top-banner-token-slot');
    const bannerDoc = document.getElementById('top-banner-token-doc');

    if (hasToken && tokenVal > 0) {
        const tokenStr = `TOKEN #${String(tokenVal).padStart(2, '0')}`;
        if (pill) {
            pill.classList.remove('hidden');
            pill.classList.add('inline-flex');
        }
        if (pillText) pillText.textContent = tokenStr;

        if (banner) {
            banner.classList.remove('hidden');
            banner.classList.add('block');
        }
        if (bannerNum) bannerNum.textContent = tokenStr;
        if (bannerStatus && appt) {
            bannerStatus.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> ${escapeHtml(appt.status || 'Checked-In')}`;
        }
        if (bannerSlot && appt) {
            bannerSlot.textContent = appt.slot ? `(Slot: ${appt.slot})` : '';
        }
        if (bannerDoc && appt) {
            const docName = appt.doctor_name || appt.doctor || '';
            const depts = appt.doctor_specialties || appt.dept_name || appt.dept || '';
            bannerDoc.innerHTML = docName ? `Consulting Doctor: <strong class="text-white">Dr. ${escapeHtml(docName)}</strong> ${depts ? '&bull; ' + escapeHtml(depts) : ''}` : '';
        }
    } else {
        if (pill) {
            pill.classList.add('hidden');
            pill.classList.remove('inline-flex');
        }
        if (banner) {
            banner.classList.add('hidden');
            banner.classList.remove('block');
        }
    }
}

function toggleEditTokenBox(apptId) {
    const box = document.getElementById(`edit-token-box-${apptId}`);
    if (box) {
        box.classList.toggle('hidden');
        const input = document.getElementById(`manual-token-${apptId}`);
        if (!box.classList.contains('hidden') && input) {
            input.focus();
            input.select();
        }
    }
}

function renderTodayStatus(dossier, bookingData) {
    const cont = document.getElementById('today-visit-container');
    const badge = document.getElementById('today-visit-badge');
    const today = new Date().toISOString().split('T')[0];

    // Check today's appointments from both bookingData and dossier
    let todayBookings = (bookingData && bookingData.bookings && bookingData.bookings.length) 
        ? bookingData.bookings 
        : (dossier.appointments ? dossier.appointments.filter(a => a.date === today && a.status !== 'Cancelled') : []);

    // Also check if any active appointment in dossier matches today or is in progress (stage 1-3)
    if (!todayBookings.length && dossier.appointments) {
        todayBookings = dossier.appointments.filter(a => (a.date === today || (parseInt(a.stage) >= 1 && parseInt(a.stage) < 4)) && a.status !== 'Cancelled' && !String(a.status).includes('Discharged'));
    }

    // Prioritize appointment that has a token number already
    todayBookings.sort((a, b) => {
        const tokA = parseInt(a.token_number) || 0;
        const tokB = parseInt(b.token_number) || 0;
        return tokB - tokA;
    });

    const todayAppt = todayBookings.length ? todayBookings[0] : null;
    const hasToken = todayAppt && todayAppt.token_number && parseInt(todayAppt.token_number) > 0;
    const tokenVal = hasToken ? parseInt(todayAppt.token_number) : 0;
    const tokenDisplay = hasToken ? `TOKEN #${String(tokenVal).padStart(2, '0')}` : '';

    // Update top header banners
    updateTopTokenBanner(hasToken, tokenVal, todayAppt);

    if (todayAppt) {
        const docName = todayAppt.doctor_name || todayAppt.doctor || 'Attending Doctor';
        const deptName = todayAppt.doctor_specialties || todayAppt.dept_name || todayAppt.dept || 'General OPD';

        // ================= CASE 1: PATIENT ALREADY HAS A TOKEN ASSIGNED =================
        if (hasToken) {
            badge.textContent = `Token #${tokenVal}`;
            badge.className = 'text-[10px] font-black px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 border border-amber-300 shadow-2xs';

            cont.innerHTML = `
                <div class="bg-gradient-to-br from-amber-50/80 via-emerald-50/50 to-indigo-50/80 border-2 border-amber-300 rounded-2xl p-4 sm:p-5 text-slate-900 space-y-3.5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-slate-950 bg-amber-300 border border-amber-400 px-2.5 py-1 rounded-full shadow-2xs">
                            <i class="fa-solid fa-circle-check text-emerald-700"></i> Token Already Assigned
                        </span>
                        <span class="font-mono text-xs font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                            ${escapeHtml(todayAppt.slot || 'Today')}
                        </span>
                    </div>

                    <!-- Hero Token Card -->
                    <div class="bg-white rounded-2xl p-4 border-2 border-amber-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-extrabold text-slate-400 block tracking-wider">Active Token Number</span>
                            <div class="text-3xl sm:text-4xl font-black text-indigo-800 tracking-tight mt-0.5">${tokenDisplay}</div>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-2xl font-bold shadow-sm">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                    </div>

                    <div class="text-xs text-slate-700 space-y-1.5 bg-white/80 rounded-xl p-3 border border-amber-200/60">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Doctor:</span>
                            <strong class="text-slate-900 font-bold">Dr. ${escapeHtml(docName)}</strong>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Specialty:</span>
                            <span class="font-medium text-slate-700">${escapeHtml(deptName)}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Queue Status:</span>
                            <span class="font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">${escapeHtml(todayAppt.status)} (Stage ${todayAppt.stage || 1})</span>
                        </div>
                        ${todayAppt.booking_ref ? `
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Booking Ref:</span>
                            <span class="font-mono font-bold text-indigo-700">${escapeHtml(todayAppt.booking_ref)}</span>
                        </div>` : ''}
                    </div>

                    <div class="space-y-2 pt-1">
                        <a href="queue.php" class="w-full bg-slate-900 hover:bg-black text-white font-extrabold py-3 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bars-staggered text-amber-400"></i>
                            <span>Track in Live Pipeline</span>
                        </a>
                        <button type="button" onclick="toggleEditTokenBox(${todayAppt.id})" class="w-full bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-bold py-2.5 px-3 rounded-xl text-xs transition border border-slate-200 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
                            <span>Change / Reassign Token Number</span>
                        </button>
                    </div>

                    <!-- Collapsible Change Token Form -->
                    <div id="edit-token-box-${todayAppt.id}" class="hidden bg-white border-2 border-indigo-200 rounded-2xl p-3.5 space-y-2.5 mt-2">
                        <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-keyboard text-indigo-600"></i>
                            <span>Type New Token Number:</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-400 text-lg">#</span>
                            <input 
                                type="number" 
                                id="manual-token-${todayAppt.id}" 
                                min="1" 
                                placeholder="Type #" 
                                value="${tokenVal}" 
                                oninput="liveCheckToken(${todayAppt.id}, '${todayAppt.doctor_id}', this.value)"
                                class="w-full text-center font-black text-lg text-indigo-700 bg-indigo-50/50 border-2 border-indigo-300 rounded-xl py-1 px-2 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" 
                                autocomplete="off"
                            />
                        </div>
                        <div id="token-feedback-${todayAppt.id}" class="text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1">
                            Current Token #${tokenVal} assigned. Type new number to check availability.
                        </div>
                        <button onclick="assignTokenForToday(${todayAppt.id})" id="btn-assign-token-${todayAppt.id}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-check"></i>
                            <span>Update Token</span>
                        </button>
                    </div>
                </div>
            `;
        }
        // ================= CASE 2: ONLINE BOOKING FOUND BUT NO TOKEN YET =================
        else if (todayAppt.status === 'Online-Booked' || (todayAppt.stage === 0 && todayAppt.booking_type === 'online')) {
            badge.textContent = 'Slot Booked';
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200';
            
            cont.innerHTML = `
                <div class="bg-indigo-50/80 border-2 border-indigo-200 rounded-2xl p-4 sm:p-5 text-indigo-950 space-y-3.5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online Booking Found
                        </span>
                        <span class="font-mono text-xs font-black text-indigo-700 bg-white px-2.5 py-1 rounded-lg border border-indigo-200 shadow-2xs">
                            ${escapeHtml(todayAppt.slot || '--')}
                        </span>
                    </div>
                    
                    <div class="bg-white/80 rounded-xl p-3 border border-indigo-100">
                        <div class="font-black text-sm sm:text-base text-slate-900">Dr. ${escapeHtml(docName)}</div>
                        <div class="text-xs text-indigo-900/80 font-medium">${escapeHtml(deptName)} &bull; ${escapeHtml(todayAppt.type || 'Consultation')}</div>
                        ${todayAppt.booking_ref ? `<div class="text-[11px] font-mono text-slate-500 mt-1">Ref: <strong class="text-indigo-700 font-bold">${escapeHtml(todayAppt.booking_ref)}</strong></div>` : ''}
                        ${todayAppt.symptoms ? `<div class="text-xs text-slate-600 mt-1"><span class="font-bold text-slate-700">Symptoms:</span> ${escapeHtml(todayAppt.symptoms)}</div>` : ''}
                    </div>

                    <!-- Manual Typed Token Number Input Box with Live Check -->
                    <div class="bg-white border-2 border-indigo-300 rounded-2xl p-3.5 shadow-xs space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <label for="manual-token-${todayAppt.id}" class="text-xs font-black uppercase tracking-wider text-slate-800 block">
                                    <i class="fa-solid fa-keyboard text-indigo-600 mr-1"></i> Give Token by Typing: <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-slate-500 font-medium">Type token number given on patient slip</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-base font-black text-indigo-700">#</span>
                                <input 
                                    type="number" 
                                    id="manual-token-${todayAppt.id}" 
                                    min="1" 
                                    placeholder="Type #" 
                                    value="" 
                                    oninput="liveCheckToken(${todayAppt.id}, '${todayAppt.doctor_id}', this.value)"
                                    class="w-24 text-center font-black text-xl text-indigo-700 bg-indigo-50/50 border-2 border-indigo-400 rounded-xl py-1 px-2 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" 
                                    autocomplete="off"
                                />
                            </div>
                        </div>
                        <div id="token-feedback-${todayAppt.id}" class="text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1">
                            <i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability
                        </div>
                    </div>

                    <div>
                        <button onclick="assignTokenForToday(${todayAppt.id})" id="btn-assign-token-${todayAppt.id}" disabled class="w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed">
                            <i class="fa-solid fa-ticket-simple text-sm"></i>
                            <span>Assign Token &amp; Check-In</span>
                        </button>
                    </div>
                </div>
            `;
        }
        // ================= CASE 3: OTHER STATUS (Discharged, Cancelled, etc.) =================
        else {
            badge.textContent = todayAppt.status;
            cont.innerHTML = `
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center space-y-2">
                    <span class="text-xs font-bold text-slate-700">Today's Visit: ${escapeHtml(todayAppt.status)}</span>
                    <p class="text-[11px] text-slate-400">Doctor: Dr. ${escapeHtml(docName)}</p>
                </div>
            `;
        }
    } else {
        // No appointment booked for today -> Direct Walk-in Booking Box
        badge.textContent = 'No Booking';
        badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200';

        cont.innerHTML = `
            <div class="bg-gradient-to-br from-indigo-50/70 to-slate-50 border-2 border-indigo-200 rounded-2xl p-4 sm:p-5 text-slate-900 space-y-3.5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-300 px-2.5 py-0.5 rounded-full">
                        Walk-in Patient &bull; No Prior Booking
                    </span>
                    <span class="text-[11px] font-bold text-slate-500">Today's OPD</span>
                </div>

                <div class="space-y-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Consulting Doctor <span class="text-rose-500">*</span></label>
                        <select id="inline-walkin-doc" onchange="onInlineDocChange(this.value)" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Loading doctors...</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Visit Type</label>
                            <select id="inline-walkin-type" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="General Consultation" selected>General Consultation</option>
                                <option value="Emergency OPD">Emergency OPD</option>
                                <option value="Follow-up Consultation">Follow-up Consultation</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Symptoms / Reason</label>
                            <input type="text" id="inline-walkin-symptoms" placeholder="e.g. Fever, body ache" class="w-full text-xs font-medium border border-slate-300 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>

                    <!-- Manual Typed Token Number for Walkin with Live Check -->
                    <div class="bg-white border-2 border-indigo-300 rounded-2xl p-3.5 shadow-xs space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <label for="inline-walkin-token" class="text-xs font-black uppercase tracking-wider text-slate-800 block">
                                    <i class="fa-solid fa-keyboard text-indigo-600 mr-1"></i> Give Token by Typing: <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-slate-500 font-medium">Type token number given on patient slip</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-base font-black text-indigo-700">#</span>
                                <input 
                                    type="number" 
                                    id="inline-walkin-token" 
                                    min="1" 
                                    placeholder="Type #" 
                                    value="" 
                                    oninput="liveCheckWalkinToken(this.value)"
                                    class="w-24 text-center font-black text-xl text-indigo-700 bg-indigo-50/50 border-2 border-indigo-400 rounded-xl py-1 px-2 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" 
                                    autocomplete="off"
                                />
                            </div>
                        </div>
                        <div id="inline-walkin-token-feedback" class="text-[11px] font-bold text-slate-500 pt-1 border-t border-indigo-100 flex items-center gap-1">
                            <i class="fa-solid fa-keyboard text-slate-400"></i> Please type token number to check availability
                        </div>
                    </div>

                    <button onclick="submitInlineWalkin()" id="btn-submit-inline-walkin" disabled class="w-full bg-slate-200 text-slate-400 font-extrabold py-3.5 px-4 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-ticket-simple text-sm"></i>
                        <span>Book Appointment &amp; Assign Token</span>
                    </button>
                </div>
            </div>
        `;

        // Populate inline walkin doctors dropdown
        getDoctorsList().then(docs => {
            const sel = document.getElementById('inline-walkin-doc');
            if (sel) {
                sel.innerHTML = '<option value="">-- Choose Consulting Doctor --</option>';
                docs.forEach(d => {
                    const depts = d.categories && d.categories.length ? ` (${d.categories.join(', ')})` : '';
                    sel.innerHTML += `<option value="${d.id}">Dr. ${escapeHtml(d.name)}${escapeHtml(depts)}</option>`;
                });
            }
        });
    }

    // Also render any upcoming/other online bookings if available
    renderUpcomingOnlineBookings(bookingData.all_online_bookings || [], today);
}

function renderUpcomingOnlineBookings(allOnlineBookings, todayDate) {
    let existingSec = document.getElementById('upcoming-online-bookings-sec');
    const upcoming = allOnlineBookings.filter(b => b.date > todayDate && b.status !== 'Cancelled');
    
    if (!upcoming.length) {
        if (existingSec) existingSec.remove();
        return;
    }

    if (!existingSec) {
        existingSec = document.createElement('div');
        existingSec.id = 'upcoming-online-bookings-sec';
        existingSec.className = 'bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-3 mt-6';
        const parent = document.getElementById('today-visit-container').closest('.lg\\:col-span-1');
        if (parent) parent.appendChild(existingSec);
    }

    let itemsHtml = '';
    upcoming.forEach(b => {
        itemsHtml += `
            <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-center justify-between text-xs">
                <div>
                    <div class="font-bold text-slate-900">Dr. ${escapeHtml(b.doctor_name || 'Doctor')}</div>
                    <div class="text-[11px] text-indigo-700 font-medium">📅 ${formatHighlightDate(b.date)} &bull; ${escapeHtml(b.slot)}</div>
                </div>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">Upcoming</span>
            </div>
        `;
    });

    existingSec.innerHTML = `
        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5 pb-2 border-b border-slate-100">
            <i class="fa-solid fa-calendar-days text-indigo-600"></i> Upcoming Online Bookings (${upcoming.length})
        </h4>
        <div class="space-y-2">${itemsHtml}</div>
    `;
}

function renderHistory(appts) {
    const cont = document.getElementById('history-container');
    const badge = document.getElementById('appt-count-badge');
    badge.textContent = appts.length;

    if (!appts.length) {
        cont.innerHTML = '<div class="text-center text-slate-400 py-6 text-xs font-medium">No previous visits recorded.</div>';
        return;
    }
    
    let html = '';
    appts.slice(0, 5).forEach(a => {
        const docName = a.doctor_name || a.doctor || 'Consultant';
        const deptName = a.dept_name || a.dept || a.department || 'General';
        const formattedDate = formatHighlightDate(a.date);
        html += `
            <div class="flex items-center justify-between p-3.5 bg-slate-50 hover:bg-slate-100/80 rounded-2xl border border-slate-200/60 transition">
                <div class="space-y-0.5">
                    <div class="text-xs font-black text-slate-900 flex items-center gap-2">
                        <span class="inline-block px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold border border-indigo-200/60 text-[11px]">📅 ${formattedDate}</span>
                        <span class="text-[11px] font-medium text-slate-400">&bull; ${escapeHtml(a.slot || '')}</span>
                    </div>
                    <div class="text-xs text-slate-600 font-medium">Dr. ${escapeHtml(docName)} &bull; <span class="text-indigo-600">${escapeHtml(deptName)}</span></div>
                </div>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 shadow-sm">${escapeHtml(a.status || 'Completed')}</span>
            </div>
        `;
    });
    cont.innerHTML = html;
}

function renderFiles(files) {
    const cont = document.getElementById('files-container');
    const badge = document.getElementById('files-count-badge');
    if (badge) badge.textContent = files ? files.length : 0;

    if (!files || !files.length) {
        cont.innerHTML = '<div class="col-span-full text-center text-slate-400 py-6 text-xs font-medium">No doctor pad images or reports attached yet.</div>';
        return;
    }
    
    let html = '';
    files.forEach(f => {
        const viewUrl = `patient_file_view.php?id=${f.id}&token=${encodeURIComponent(qrToken)}`;
        const isLetterhead = (f.category || '').toLowerCase().includes('letterhead') || (f.category || '').toLowerCase().includes('prescription');
        const formattedDate = formatHighlightDate(f.file_date || f.record_date || f.date);
        html += `
            <a href="${viewUrl}" target="_blank" class="block bg-slate-50 hover:bg-white border border-slate-200 hover:border-teal-400 rounded-2xl p-3 text-center transition group hover:shadow-md">
                <div class="w-10 h-10 mx-auto bg-white rounded-xl shadow-xs border border-slate-100 flex items-center justify-center text-lg ${isLetterhead ? 'text-indigo-600' : 'text-teal-600'} mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid ${isLetterhead ? 'fa-file-signature' : 'fa-file-waveform'}"></i>
                </div>
                <div class="text-xs font-bold text-slate-800 truncate" title="${escapeHtml(f.title || f.name || 'Document')}">${escapeHtml(f.title || f.name || 'Document')}</div>
                <div class="inline-block mt-1 px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono text-[11px] font-extrabold shadow-2xs">${formattedDate}</div>
            </a>
        `;
    });
    cont.innerHTML = html;
}

async function assignTokenForToday(appointmentId) {
    const input = document.getElementById(`manual-token-${appointmentId}`);
    const customToken = input ? parseInt(input.value) : 0;
    const btn = document.getElementById(`btn-assign-token-${appointmentId}`);

    if (!customToken || isNaN(customToken) || customToken <= 0) {
        alert('Please enter a valid token number.');
        if (input) input.focus();
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Assigning Token...</span>';
    }

    try {
        const payload = { 
            appointment_id: appointmentId,
            token_number: customToken
        };

        const res = await fetch('api/booking.php?action=assign_token', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            playCheckinBeep();
            if (typeof showToast === 'function') {
                showToast('Token Assigned', data.message || 'Token assigned successfully!');
            } else {
                alert(data.message || 'Token assigned successfully!');
            }
            loadPatientData();
        } else {
            alert(data.message || 'Failed to assign token.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-ticket-simple text-sm"></i> <span>Assign Token &amp; Check-In</span>';
            }
        }
    } catch (err) {
        alert('Network error while assigning token.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-ticket-simple text-sm"></i> <span>Assign Token &amp; Check-In</span>';
        }
    }
}

// Inline Walk-in Booking Submission
async function submitInlineWalkin() {
    const docId = document.getElementById('inline-walkin-doc').value;
    const type = document.getElementById('inline-walkin-type').value;
    const symptoms = document.getElementById('inline-walkin-symptoms').value.trim() || 'Walk-in consultation';
    const tokenInput = document.getElementById('inline-walkin-token');
    const customToken = tokenInput ? parseInt(tokenInput.value) : 0;
    const btn = document.getElementById('btn-submit-inline-walkin');

    if (!docId) {
        alert('Please select a consulting doctor.');
        return;
    }

    if (!customToken || isNaN(customToken) || customToken <= 0) {
        alert('Please type a valid token number given on patient slip.');
        if (tokenInput) tokenInput.focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Assigning Token...</span>';

    try {
        const payload = {
            patient_id: patientId,
            doctor_id: docId,
            type: type,
            symptoms: symptoms,
            token_number: customToken
        };

        const res = await fetch('api/booking.php?action=create_walkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            playCheckinBeep();
            if (typeof showToast === 'function') {
                showToast('Walk-in Checked-In', data.message || 'Token assigned!');
            } else {
                alert(data.message || 'Walk-in created and token assigned!');
            }
            loadPatientData();
        } else {
            alert(data.message || 'Failed to create walk-in appointment.');
        }
    } catch(e) {
        alert('Network error creating walk-in appointment.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-ticket-simple text-sm"></i> <span>Book Appointment &amp; Assign Token</span>';
        }
    }
}

// Walk-in Modal Handlers
let walkinDoctorsLoaded = false;
async function openWalkinModal() {
    const modal = document.getElementById('walkin-modal');
    if (!modal) return;
    modal.classList.remove('hidden');

    if (!walkinDoctorsLoaded) {
        const docs = await getDoctorsList();
        const select = document.getElementById('walkin-doc-select');
        select.innerHTML = '<option value="">-- Choose Consulting Doctor --</option>';
        if (docs.length) {
            docs.forEach(d => {
                const depts = d.categories && d.categories.length ? ` (${d.categories.join(', ')})` : '';
                select.innerHTML += `<option value="${d.id}">Dr. ${d.name}${depts}</option>`;
            });
            walkinDoctorsLoaded = true;
        } else {
            select.innerHTML = '<option value="">No doctors available</option>';
        }
    }
}

function closeWalkinModal() {
    const modal = document.getElementById('walkin-modal');
    if (modal) modal.classList.add('hidden');
}

async function submitWalkinAppointment(e) {
    if (e) e.preventDefault();
    const docId = document.getElementById('walkin-doc-select').value;
    const type = document.getElementById('walkin-type-select').value;
    const symptoms = document.getElementById('walkin-symptoms-input').value.trim() || 'Walk-in consultation';
    const tokenInput = document.getElementById('walkin-token-input');
    const customToken = tokenInput ? parseInt(tokenInput.value) : 0;
    const submitBtn = document.getElementById('btn-submit-walkin');

    if (!docId) {
        alert('Please select a consulting doctor.');
        return;
    }

    if (!customToken || isNaN(customToken) || customToken <= 0) {
        alert('Please type a valid token number given on patient slip.');
        if (tokenInput) tokenInput.focus();
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Creating Walk-in...</span>';

    try {
        const payload = {
            patient_id: patientId,
            doctor_id: docId,
            type: type,
            symptoms: symptoms,
            token_number: customToken
        };

        const res = await fetch('api/booking.php?action=create_walkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            playCheckinBeep();
            closeWalkinModal();
            if (typeof showToast === 'function') {
                showToast('Walk-in Checked-In', data.message || 'Token assigned!');
            } else {
                alert(data.message || 'Walk-in created and token assigned!');
            }
            loadPatientData();
        } else {
            alert(data.message || 'Failed to create walk-in appointment.');
        }
    } catch(err) {
        alert('Network error creating walk-in appointment.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-ticket-simple"></i> <span>Assign Token &amp; Check-In</span>';
    }
}

// Direct Multi-Image Upload Handlers
function openDirectUploadModal() {
    const modal = document.getElementById('direct-upload-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeDirectUploadModal() {
    const modal = document.getElementById('direct-upload-modal');
    if (modal) modal.classList.add('hidden');
    const preview = document.getElementById('file-selection-preview');
    if (preview) {
        preview.textContent = '';
        preview.classList.add('hidden');
    }
    const form = document.getElementById('direct-upload-form');
    if (form) form.reset();
}

function handleDirectFileSelect(input) {
    const preview = document.getElementById('file-selection-preview');
    if (!preview) return;
    if (input.files && input.files.length > 0) {
        preview.textContent = `✓ ${input.files.length} file(s) selected: ` + Array.from(input.files).map(f => f.name).slice(0, 3).join(', ') + (input.files.length > 3 ? ` +${input.files.length - 3} more` : '');
        preview.classList.remove('hidden');
    } else {
        preview.textContent = '';
        preview.classList.add('hidden');
    }
}

async function submitDirectUpload(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('direct-upload-form');
    const btn = document.getElementById('btn-submit-direct-upload');
    const fileInput = document.getElementById('direct-file-input');

    if (!fileInput.files || !fileInput.files.length) {
        alert('Please select at least one file or snap a photo.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Uploading...</span>';

    try {
        const formData = new FormData(form);
        const res = await fetch('api/files.php?action=upload', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeDirectUploadModal();
            if (typeof showToast === 'function') {
                showToast('Files Uploaded', data.message || `${fileInput.files.length} file(s) attached successfully!`);
            } else {
                alert(data.message || 'Files uploaded successfully!');
            }
            loadPatientData();
        } else {
            alert(data.message || 'Failed to upload files.');
        }
    } catch(err) {
        alert('Network error uploading files.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> <span>Upload Files Now</span>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.addEventListener('DOMContentLoaded', loadPatientData);
</script>

<?php include 'includes/footer.php'; ?>

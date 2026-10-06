<?php
require_once 'auth.php';
require_once 'db.php';

$file_id = (int)($_GET['id'] ?? 0);
if (!$file_id) {
    header("Location: patients.php");
    exit;
}

// Fetch target file along with patient demographics
$stmt = $conn->prepare("
    SELECT pf.*, 
           p.id as patient_mrn, p.name as patient_name, p.surname as patient_surname, 
           p.gender as patient_gender, p.age as patient_age, p.blood_group as patient_blood,
           p.phone as patient_phone, p.emergency_contact_name, p.emergency_contact_phone, p.qr_token
    FROM patient_files pf
    LEFT JOIN patients p ON pf.patient_id = p.id
    WHERE pf.id = ?
");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    die("Error: Medical file #$file_id was not found.");
}

$patient_id = $file['patient_id'];

// Permissions check
$is_admin = $_SESSION['is_admin'] ?? false;
$staff_id = $_SESSION['staff_id'] ?? 0;
$can_view = $is_admin || 
            checkStaffPermission($conn, $staff_id, 'can_view_files') || 
            checkStaffPermission($conn, $staff_id, 'can_upload') || 
            checkStaffPermission($conn, $staff_id, 'can_upload_files');

$can_upload = $is_admin || 
              checkStaffPermission($conn, $staff_id, 'can_upload') || 
              checkStaffPermission($conn, $staff_id, 'can_upload_files');

// Token fallback (for patient QR token)
$token = $_GET['token'] ?? '';
if (!$can_view && !empty($token)) {
    if (!empty($file['qr_token']) && $token === $file['qr_token']) {
        $can_view = true;
    }
}

if (!$can_view) {
    die("Access Denied: You do not have authorization to view this medical file.");
}

// Helper function for highlighted date (e.g. 6-May-2020)
function formatHighlightDate($dateStr) {
    if (empty($dateStr)) return '';
    $ts = strtotime($dateStr);
    if ($ts === false) return $dateStr;
    return date('j-M-Y', $ts);
}

// Fetch all files for this patient for the bottom filmstrip navigation
$stmtAll = $conn->prepare("
    SELECT id, title, category, file_name, mime_type, file_size, record_date, created_at,
           DATE_FORMAT(COALESCE(record_date, created_at), '%e-%b-%Y') as formatted_date
    FROM patient_files 
    WHERE patient_id = ? 
    ORDER BY record_date DESC, id DESC
");
$stmtAll->bind_param("s", $patient_id);
$stmtAll->execute();
$allFiles = $stmtAll->get_result()->fetch_all(MYSQLI_ASSOC);

$isPdf = ($file['mime_type'] === 'application/pdf') || 
         (strtolower(pathinfo($file['file_name'] ?? '', PATHINFO_EXTENSION)) === 'pdf');

$fileUrl = "api/file.php?id=" . $file['id'] . "&file=" . urlencode($file['file_name'] ?: 'file');
$downloadUrl = "api/file.php?id=" . $file['id'] . "&download=1";
$patientFullName = trim(($file['patient_name'] ?? '') . ' ' . ($file['patient_surname'] ?? '')) ?: 'Patient';

$currentFileDateRaw = !empty($file['record_date']) ? $file['record_date'] : $file['created_at'];
$highlightDate = formatHighlightDate($currentFileDateRaw);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title><?= htmlspecialchars($patientFullName) ?> — <?= htmlspecialchars($file['title'] ?: 'Medical File') ?> (<?= htmlspecialchars($highlightDate) ?>)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #0b0f19;
            color: #f8fafc;
            user-select: none;
            -webkit-font-smoothing: antialiased;
        }
        #viewport-container {
            touch-action: none;
            cursor: grab;
        }
        #viewport-container.grabbing {
            cursor: grabbing;
        }
        #viewer-img {
            transition: transform 0.05s ease-out;
            transform-origin: center center;
            max-width: none;
            will-change: transform;
        }
        .filmstrip-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x;
        }
        .filmstrip-scroll::-webkit-scrollbar {
            height: 5px;
        }
        .filmstrip-scroll::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .filmstrip-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
                height: auto !important;
            }
            .no-print {
                display: none !important;
            }
            #printable-area {
                display: block !important;
                position: static !important;
                width: 100% !important;
                height: auto !important;
            }
            #printable-area img {
                max-width: 100% !important;
                height: auto !important;
                display: block !important;
                margin: 0 auto !important;
            }
        }
    </style>
</head>
<body class="h-[100dvh] min-h-[100dvh] max-h-[100dvh] w-full flex flex-col antialiased select-none overflow-hidden">

    <!-- ================= TOP CONTROLS & HEADER BAR ================= -->
    <header class="no-print shrink-0 bg-slate-900/95 border-b border-slate-800 backdrop-blur px-3 sm:px-5 py-2.5 flex items-center justify-between gap-2.5 z-30 shadow-lg">
        <!-- Left: Back link and Clean Patient / File Details -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <a href="patient_profile.php?id=<?= urlencode($patient_id) ?>" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white flex items-center justify-center transition border border-slate-700 shadow-sm shrink-0" title="Back to Patient Profile">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xs sm:text-sm md:text-base font-black text-white truncate"><?= htmlspecialchars($patientFullName) ?></h1>
                    
                    <!-- Highlighted Date Tag (e.g. 6-May-2020) -->
                    <span class="px-2 py-0.5 rounded-lg bg-amber-400/15 border border-amber-400/40 text-amber-300 font-extrabold text-[10px] sm:text-xs flex items-center gap-1 shrink-0">
                        <i class="fa-regular fa-calendar-check text-amber-400"></i>
                        <span><?= htmlspecialchars($highlightDate) ?></span>
                    </span>
                </div>
                
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 truncate mt-0.5">
                    <span class="text-slate-300 font-medium truncate"><?= htmlspecialchars($file['title'] ?: 'Doctor Letterhead Pad') ?></span>
                    <span class="text-slate-600">•</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 shrink-0">
                        <?= htmlspecialchars($file['category'] ?: 'Medical File') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Toolbar Actions -->
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
            <!-- Add Images Button (Multiple upload) -->
            <?php if ($can_upload): ?>
            <button onclick="openUploadModal()" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white flex items-center gap-1.5 transition text-xs font-bold shadow-md shadow-teal-600/30 shrink-0" title="Upload more images / doctor pads">
                <i class="fa-solid fa-camera sm:text-xs"></i>
                <span class="hidden xs:inline sm:inline">+ Add Images</span>
            </button>
            <?php endif; ?>

            <?php if (!$isPdf): ?>
            <!-- Desktop Zoom & Pan Controls (Images only) -->
            <div class="hidden md:flex items-center bg-slate-800/90 rounded-xl p-1 border border-slate-700">
                <button onclick="zoomStep(-0.25)" class="w-8 h-8 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition" title="Zoom Out (-)">
                    <i class="fa-solid fa-magnifying-glass-minus text-xs"></i>
                </button>
                <span id="zoom-level-text" class="text-xs font-mono font-bold text-slate-300 w-12 text-center">100%</span>
                <button onclick="zoomStep(0.25)" class="w-8 h-8 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition" title="Zoom In (+)">
                    <i class="fa-solid fa-magnifying-glass-plus text-xs"></i>
                </button>
                <button onclick="resetZoomPan()" class="w-8 h-8 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition ml-1" title="Reset View (Fit)">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                </button>
                <button onclick="rotateImage()" class="w-8 h-8 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700 flex items-center justify-center transition" title="Rotate 90°">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Action buttons -->
            <button onclick="triggerPrint()" class="w-8 h-8 sm:w-auto sm:px-3 sm:py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white flex items-center justify-center gap-1.5 transition border border-slate-700 text-xs font-bold shadow-sm" title="Print File">
                <i class="fa-solid fa-print text-xs"></i>
                <span class="hidden sm:inline">Print</span>
            </button>

            <a href="<?= htmlspecialchars($downloadUrl) ?>" download class="w-8 h-8 sm:w-auto sm:px-3 sm:py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center gap-1.5 transition text-xs font-bold shadow-md shadow-indigo-600/30" title="Download Original File">
                <i class="fa-solid fa-download text-xs"></i>
                <span class="hidden sm:inline">Download</span>
            </a>

            <!-- Info Modal Toggle -->
            <button onclick="openInfoModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white flex items-center justify-center transition border border-slate-700 text-xs sm:text-sm shadow-sm" title="View Clear Patient & File Info">
                <i class="fa-solid fa-circle-info"></i>
            </button>
        </div>
    </header>

    <!-- ================= MAIN VIEWING CANVAS ================= -->
    <main class="flex-1 min-h-0 relative overflow-hidden bg-slate-950 flex items-center justify-center">

        <?php if ($isPdf): ?>
            <!-- Embedded PDF Viewer -->
            <div class="w-full h-full p-2 sm:p-4">
                <iframe src="<?= htmlspecialchars($fileUrl) ?>#toolbar=1" class="w-full h-full rounded-xl border border-slate-800 bg-white" title="Medical PDF Viewer"></iframe>
            </div>
        <?php else: ?>
            <!-- Interactive Image Canvas with Pan & Zoom -->
            <div id="viewport-container" class="w-full h-full flex items-center justify-center overflow-hidden relative touch-none">
                <img id="viewer-img" 
                     src="<?= htmlspecialchars($fileUrl) ?>" 
                     alt="<?= htmlspecialchars($file['title'] ?: 'Medical File') ?>"
                     class="max-h-full max-w-full object-contain drop-shadow-2xl select-none pointer-events-none rounded-lg"
                     onload="centerAndInitImage()" />
            </div>

            <!-- Mobile Sleek Floating Controls (Zoom & Rotate) -->
            <div class="md:hidden absolute bottom-3 right-3 z-20 flex flex-col gap-2 no-print pointer-events-auto">
                <button onclick="zoomStep(0.3)" class="w-10 h-10 rounded-xl bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-white shadow-xl flex items-center justify-center active:scale-90 transition">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
                <button onclick="zoomStep(-0.3)" class="w-10 h-10 rounded-xl bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-white shadow-xl flex items-center justify-center active:scale-90 transition">
                    <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <button onclick="rotateImage()" class="w-10 h-10 rounded-xl bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-white shadow-xl flex items-center justify-center active:scale-90 transition">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
                <button onclick="resetZoomPan()" class="w-10 h-10 rounded-xl bg-slate-900/90 backdrop-blur-md border border-slate-700/80 text-white shadow-xl flex items-center justify-center active:scale-90 transition">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                </button>
            </div>
        <?php endif; ?>

    </main>

    <!-- ================= BOTTOM FILMSTRIP: PATIENT'S MEDICAL DOSSIER FILES ================= -->
    <footer class="no-print shrink-0 bg-slate-900 border-t border-slate-800 px-3 sm:px-5 py-2 z-20 shadow-inner pb-[max(0.6rem,env(safe-area-inset-bottom))]">
        <div class="flex items-center justify-between mb-1.5">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-photo-film text-indigo-400"></i>
                    <span>All Medical Files (<?= count($allFiles) ?>)</span>
                </span>
                <span class="text-[10px] text-slate-500 hidden sm:inline">• Tap file to switch view</span>
            </div>
            <?php if ($can_upload): ?>
            <button onclick="openUploadModal()" class="text-[11px] font-bold text-teal-400 hover:text-teal-300 flex items-center gap-1 px-2 py-0.5 rounded-lg bg-teal-500/10 border border-teal-500/20 transition">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Add More</span>
            </button>
            <?php endif; ?>
        </div>

        <!-- Touch-scrollable Filmstrip Carousel -->
        <div id="filmstrip-carousel" class="flex items-center gap-2 sm:gap-2.5 overflow-x-auto filmstrip-scroll pb-1">
            <?php foreach ($allFiles as $fItem): 
                $active = ($fItem['id'] == $file_id);
                $isItemPdf = ($fItem['mime_type'] === 'application/pdf') || (strtolower(pathinfo($fItem['file_name'] ?? '', PATHINFO_EXTENSION)) === 'pdf');
                $thumbUrl = "api/file.php?id=" . $fItem['id'] . "&thumb=1";
                $itemDate = $fItem['formatted_date'];
            ?>
                <a href="patient_file_view.php?id=<?= $fItem['id'] ?>&token=<?= urlencode($token) ?>" 
                   id="filmstrip-card-<?= $fItem['id'] ?>"
                   class="filmstrip-card flex items-center gap-2 sm:gap-2.5 p-1.5 pr-2.5 rounded-xl border transition shrink-0 <?= $active ? 'filmstrip-active-card bg-indigo-600/20 border-indigo-500 ring-2 ring-indigo-500/70 shadow-lg' : 'bg-slate-800/80 hover:bg-slate-800 border-slate-700/80 hover:border-slate-600' ?>"
                   style="min-width: 145px; max-width: 185px;">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg bg-slate-950 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0 relative">
                        <?php if ($isItemPdf): ?>
                            <i class="fa-solid fa-file-pdf text-rose-500 text-lg"></i>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="thumb" class="w-full h-full object-cover" onerror="this.outerHTML='<i class=\'fa-solid fa-file-image text-indigo-400 text-lg\'></i>'" />
                        <?php endif; ?>
                        <?php if ($active): ?>
                            <span class="absolute bottom-0 inset-x-0 bg-indigo-600 text-[8px] font-black uppercase text-center text-white leading-tight py-0.2">VIEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[11px] sm:text-xs font-bold text-white truncate <?= $active ? 'text-indigo-300' : '' ?>"><?= htmlspecialchars($fItem['title'] ?: 'File') ?></div>
                        
                        <!-- Highlighted Date Format: 6-May-2020 -->
                        <div class="text-[10px] font-bold text-amber-300 font-mono flex items-center gap-1 truncate">
                            <i class="fa-regular fa-calendar-check text-[9px] text-amber-400"></i>
                            <span><?= htmlspecialchars($itemDate) ?></span>
                        </div>
                        
                        <div class="text-[9px] text-slate-400 truncate"><?= htmlspecialchars($fItem['category'] ?: 'Other') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </footer>

    <!-- ================= CLEAN & UNCOMPLICATED INFO MODAL ================= -->
    <div id="modal-file-info" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto" onclick="closeInfoModal()">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl relative my-auto animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button onclick="closeInfoModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <!-- Header -->
            <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/30">
                    <i class="fa-solid fa-file-medical"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-white">Patient & Document Details</h3>
                    <p class="text-[11px] text-slate-400">Complete summary for clinical reference</p>
                </div>
            </div>

            <div class="mt-4 space-y-3.5 text-xs">
                <!-- Patient Info Card -->
                <div class="p-3.5 rounded-2xl bg-slate-800/60 border border-slate-700/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Patient Name</span>
                        <span class="text-xs font-mono font-bold text-indigo-400">MRN: <?= htmlspecialchars($patient_id) ?></span>
                    </div>
                    <div class="text-sm font-black text-white"><?= htmlspecialchars($patientFullName) ?></div>
                    <div class="flex items-center gap-2 flex-wrap text-[11px] text-slate-300">
                        <span><?= htmlspecialchars($file['patient_gender'] ?? 'Not specified') ?></span>
                        <span>•</span>
                        <span><?= htmlspecialchars($file['patient_age'] ? $file['patient_age'].' Years' : '--') ?></span>
                        <span>•</span>
                        <span class="font-bold text-rose-400">Blood: <?= htmlspecialchars($file['patient_blood'] ?: 'Unknown') ?></span>
                    </div>
                    <?php if (!empty($file['patient_phone'])): ?>
                    <div class="pt-1 text-[11px] text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-phone text-indigo-400 text-[10px]"></i>
                        <span><?= htmlspecialchars($file['patient_phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($file['emergency_contact_name']) || !empty($file['emergency_contact_phone'])): ?>
                    <div class="pt-1 text-[11px] text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-amber-400 text-[10px]"></i>
                        <span>Em: <?= htmlspecialchars(trim(($file['emergency_contact_name'] ?? '') . ' ' . ($file['emergency_contact_phone'] ? '('.$file['emergency_contact_phone'].')' : ''))) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Document Details Card -->
                <div class="p-3.5 rounded-2xl bg-slate-800/60 border border-slate-700/80 space-y-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-0.5">Document Title</span>
                        <div class="text-xs font-bold text-white"><?= htmlspecialchars($file['title'] ?: 'Doctor Letterhead Pad') ?></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-0.5">Category</span>
                            <span class="inline-block px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold border border-indigo-500/30 text-[10px]">
                                <?= htmlspecialchars($file['category'] ?: 'Medical File') ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-0.5">Date</span>
                            <span class="inline-block px-2 py-0.5 rounded bg-amber-400/20 text-amber-300 font-extrabold border border-amber-400/30 text-[10px] font-mono">
                                <?= htmlspecialchars($highlightDate) ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-0.5">Uploaded By</span>
                        <span class="text-slate-300 flex items-center gap-1">
                            <i class="fa-solid fa-user-doctor text-indigo-400 text-[10px]"></i>
                            <?= htmlspecialchars($file['uploaded_by_name'] ?: 'Hospital Staff') ?>
                        </span>
                    </div>

                    <?php if (!empty($file['highlight'])): ?>
                    <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300">
                        <span class="text-[10px] font-bold block mb-0.5">⭐ Clinical Highlight</span>
                        <p class="font-semibold text-[11px]"><?= htmlspecialchars($file['highlight']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($file['description'])): ?>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-0.5">Notes</span>
                        <p class="text-slate-300 bg-slate-900/60 p-2 rounded-xl border border-slate-700/60 leading-relaxed"><?= nl2br(htmlspecialchars($file['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="text-[10px] text-slate-500 font-mono pt-1">
                        File: <?= htmlspecialchars($file['file_name']) ?> (<?= number_format(($file['file_size'] ?? 0) / 1024, 1) ?> KB)
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between gap-2 flex-wrap">
                <a href="patient_profile.php?id=<?= urlencode($patient_id) ?>" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-user-injured"></i> Full Patient Profile
                </a>
                <div class="flex items-center gap-2">
                    <button onclick="generatePatientPDF(patientId)" class="px-3 py-2 rounded-xl bg-rose-600/25 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs flex items-center gap-1.5 transition border border-rose-500/30" title="Export Complete Medical File PDF">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </button>
                    <button onclick="closeInfoModal()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MULTIPLE IMAGES UPLOAD MODAL ================= -->
    <div id="modal-upload-images" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/75 backdrop-blur-sm overflow-y-auto" onclick="closeUploadModal()">
        <div class="bg-white text-slate-900 rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 relative my-auto animate-in fade-in zoom-in duration-150" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button onclick="closeUploadModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <!-- Header -->
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shadow-xs">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Upload Multiple Images / Files</h3>
                    <p class="text-xs text-slate-500">For patient <?= htmlspecialchars($patientFullName) ?> (MRN: <?= htmlspecialchars($patient_id) ?>)</p>
                </div>
            </div>

            <form id="view-upload-form" onsubmit="handleViewerMultiUpload(event)" class="space-y-3.5">
                <!-- Dropzone / Multi-file Selector -->
                <div>
                    <input type="file" id="viewer-files-input" accept="image/*,application/pdf" multiple class="hidden" onchange="handleViewerFileSelection(this)">
                    
                    <div onclick="document.getElementById('viewer-files-input').click()" class="border-2 border-dashed border-teal-300 hover:border-teal-500 bg-teal-50/40 hover:bg-teal-50/70 rounded-2xl p-4 text-center cursor-pointer transition">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mx-auto mb-1.5 text-lg shadow-xs">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-800">Tap to Select Multiple Images</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Select multiple photos from gallery or capture with camera</p>
                    </div>

                    <!-- Selected Files Preview Container -->
                    <div id="viewer-preview-container" class="hidden mt-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 px-1">
                            <span id="viewer-selected-count">0 files selected</span>
                            <button type="button" onclick="clearViewerFiles()" class="text-rose-600 hover:underline text-[11px]">Clear all</button>
                        </div>
                        <div id="viewer-preview-list" class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-1 custom-scrollbar"></div>
                    </div>
                </div>

                <!-- Title & Category Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Document Title</label>
                        <input type="text" id="viewer-file-title" value="Doctor Letterhead Pad" required class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-medium bg-slate-50 focus:bg-white focus:outline-none focus:border-teal-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category</label>
                        <select id="viewer-file-category" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-medium bg-slate-50 focus:bg-white focus:outline-none focus:border-teal-500 transition">
                            <option value="Doctor Letterhead">📝 Doctor Letterhead</option>
                            <option value="Prescription">📋 Prescription</option>
                            <option value="Lab Report">🧪 Lab Report</option>
                            <option value="Radiology Scan">🩻 Radiology Scan</option>
                            <option value="Other">📁 Other Document</option>
                        </select>
                    </div>
                </div>

                <!-- Highlight & Record Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Highlight (Optional)</label>
                        <input type="text" id="viewer-file-highlight" placeholder="e.g. Follow-up 1 week" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-medium bg-slate-50 focus:bg-white focus:outline-none focus:border-teal-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Record Date</label>
                        <input type="date" id="viewer-file-date" value="<?= date('Y-m-d') ?>" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-medium bg-slate-50 focus:bg-white focus:outline-none focus:border-teal-500 transition">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="btn-submit-viewer-upload" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-teal-600/30 transition">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Images</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Printable Version (Visible only in print mode) -->
    <div id="printable-area" class="hidden">
        <div style="padding: 24px; font-family: sans-serif;">
            <div style="border-bottom: 2px solid #334155; padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; margin: 0; color: #0f172a;"><?= htmlspecialchars($file['title'] ?: 'Medical File') ?></h2>
                    <p style="font-size: 12px; color: #64748b; margin: 4px 0 0 0;">Patient: <strong><?= htmlspecialchars($patientFullName) ?></strong> (MRN: <?= htmlspecialchars($patient_id) ?>)</p>
                </div>
                <div style="text-align: right; font-size: 11px; color: #64748b;">
                    <div>Date: <strong><?= htmlspecialchars($highlightDate) ?></strong></div>
                    <div>Staff: <?= htmlspecialchars($file['uploaded_by_name'] ?: 'Hospital Staff') ?></div>
                </div>
            </div>
            <?php if (!$isPdf): ?>
                <img src="<?= htmlspecialchars($fileUrl) ?>" alt="Medical Record" style="max-width: 100%; height: auto; display: block; margin: 0 auto;" />
            <?php else: ?>
                <p style="font-size: 14px; color: #334155;">[PDF Document: Please view digitally or print via browser PDF viewer]</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="viewer-toast" class="fixed top-4 right-4 z-50 transform transition-all duration-300 opacity-0 pointer-events-none translate-y-[-10px] max-w-sm w-full">
        <div id="viewer-toast-body" class="p-4 rounded-2xl shadow-xl flex items-center gap-3 text-white">
            <i id="viewer-toast-icon" class="fa-solid fa-circle-check text-xl"></i>
            <div class="text-xs">
                <h4 id="viewer-toast-title" class="font-bold text-sm"></h4>
                <p id="viewer-toast-msg" class="opacity-90"></p>
            </div>
        </div>
    </div>

    <!-- ================= SCRIPTS ================= -->
    <script>
        const patientId = "<?= htmlspecialchars($patient_id) ?>";
        let currentScale = 1;
        let currentRotation = 0;
        let panX = 0;
        let panY = 0;
        let isDragging = false;
        let startX = 0;
        let startY = 0;

        const viewport = document.getElementById('viewport-container');
        const img = document.getElementById('viewer-img');
        const zoomText = document.getElementById('zoom-level-text');

        function updateTransform() {
            if (!img) return;
            img.style.transform = `translate(${panX}px, ${panY}px) scale(${currentScale}) rotate(${currentRotation}deg)`;
            if (zoomText) {
                zoomText.textContent = `${Math.round(currentScale * 100)}%`;
            }
        }

        function centerAndInitImage() {
            currentScale = 1;
            currentRotation = 0;
            panX = 0;
            panY = 0;
            updateTransform();
        }

        function zoomStep(delta) {
            let nextScale = currentScale + delta;
            if (nextScale < 0.2) nextScale = 0.2;
            if (nextScale > 6) nextScale = 6;
            currentScale = Math.round(nextScale * 100) / 100;
            updateTransform();
        }

        function resetZoomPan() {
            centerAndInitImage();
        }

        function rotateImage() {
            currentRotation = (currentRotation + 90) % 360;
            updateTransform();
        }

        function triggerPrint() {
            window.print();
        }

        // Modals management
        function openInfoModal() {
            document.getElementById('modal-file-info').classList.remove('hidden');
        }

        function closeInfoModal() {
            document.getElementById('modal-file-info').classList.add('hidden');
        }

        function openUploadModal() {
            clearViewerFiles();
            document.getElementById('modal-upload-images').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('modal-upload-images').classList.add('hidden');
        }

        // Auto-scroll active card in filmstrip into view on load
        window.addEventListener('DOMContentLoaded', () => {
            const activeCard = document.querySelector('.filmstrip-active-card');
            if (activeCard) {
                activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        });

        // Touch events & Dragging for Pan/Zoom
        if (viewport && img) {
            viewport.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;
                isDragging = true;
                viewport.classList.add('grabbing');
                startX = e.clientX - panX;
                startY = e.clientY - panY;
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                panX = e.clientX - startX;
                panY = e.clientY - startY;
                updateTransform();
            });

            window.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    viewport.classList.remove('grabbing');
                }
            });

            // Wheel zoom
            viewport.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.15 : -0.15;
                zoomStep(delta);
            }, { passive: false });

            // Touch events for mobile (Pan & Pinch to zoom)
            let initialDistance = 0;
            let initialScale = 1;

            viewport.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) {
                    isDragging = true;
                    startX = e.touches[0].clientX - panX;
                    startY = e.touches[0].clientY - panY;
                } else if (e.touches.length === 2) {
                    isDragging = false;
                    initialDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    initialScale = currentScale;
                }
            });

            viewport.addEventListener('touchmove', (e) => {
                if (e.touches.length === 1 && isDragging) {
                    panX = e.touches[0].clientX - startX;
                    panY = e.touches[0].clientY - startY;
                    updateTransform();
                } else if (e.touches.length === 2 && initialDistance > 0) {
                    const currentDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    let factor = currentDist / initialDistance;
                    let nextScale = initialScale * factor;
                    if (nextScale >= 0.2 && nextScale <= 6) {
                        currentScale = Math.round(nextScale * 100) / 100;
                        updateTransform();
                    }
                }
            }, { passive: false });

            viewport.addEventListener('touchend', () => {
                isDragging = false;
                initialDistance = 0;
            });
        }

        // ================= MULTI-IMAGE UPLOAD HANDLING =================
        let viewerSelectedFiles = [];

        function handleViewerFileSelection(input) {
            if (!input.files || input.files.length === 0) return;
            for (let i = 0; i < input.files.length; i++) {
                viewerSelectedFiles.push(input.files[i]);
            }
            renderViewerFilesPreview();
            input.value = '';
        }

        function renderViewerFilesPreview() {
            const container = document.getElementById('viewer-preview-container');
            const list = document.getElementById('viewer-preview-list');
            const countEl = document.getElementById('viewer-selected-count');
            if (!container || !list) return;

            if (viewerSelectedFiles.length === 0) {
                container.classList.add('hidden');
                list.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            countEl.textContent = `${viewerSelectedFiles.length} file${viewerSelectedFiles.length === 1 ? '' : 's'} selected`;

            list.innerHTML = '';
            viewerSelectedFiles.forEach((file, idx) => {
                const item = document.createElement('div');
                item.className = "relative p-1.5 rounded-xl bg-white border border-slate-200 flex flex-col items-center text-center shadow-xs";
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = "absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] flex items-center justify-center shadow hover:bg-rose-600 transition";
                removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                removeBtn.onclick = (e) => {
                    e.stopPropagation();
                    viewerSelectedFiles.splice(idx, 1);
                    renderViewerFilesPreview();
                };

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = "w-full h-12 object-cover rounded-lg mb-1 border border-slate-100";
                    img.src = URL.createObjectURL(file);
                    item.appendChild(img);
                } else {
                    const iconDiv = document.createElement('div');
                    iconDiv.className = "w-full h-12 bg-slate-100 rounded-lg mb-1 flex items-center justify-center text-rose-500 text-lg";
                    iconDiv.innerHTML = '<i class="fa-solid fa-file-pdf"></i>';
                    item.appendChild(iconDiv);
                }

                const nameP = document.createElement('p');
                nameP.className = "text-[9px] font-bold text-slate-800 truncate w-full";
                nameP.textContent = file.name;
                item.appendChild(nameP);

                item.appendChild(removeBtn);
                list.appendChild(item);
            });
        }

        function clearViewerFiles() {
            viewerSelectedFiles = [];
            const input = document.getElementById('viewer-files-input');
            if (input) input.value = '';
            renderViewerFilesPreview();
        }

        async function handleViewerMultiUpload(e) {
            e.preventDefault();
            if (!viewerSelectedFiles || viewerSelectedFiles.length === 0) {
                showToast('Files Required', 'Please select at least one image or document.', 'warning');
                return;
            }

            const title = document.getElementById('viewer-file-title').value.trim() || 'Doctor Letterhead Pad';
            const category = document.getElementById('viewer-file-category').value;
            const highlight = document.getElementById('viewer-file-highlight').value.trim();
            const dateVal = document.getElementById('viewer-file-date').value;

            const submitBtn = document.getElementById('btn-submit-viewer-upload');
            const ogText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Uploading ' + viewerSelectedFiles.length + ' image(s)...';

            try {
                const formData = new FormData();
                formData.append('patient_id', patientId);
                formData.append('title', title);
                formData.append('category', category);
                formData.append('highlight', highlight);
                if (dateVal) {
                    formData.append('record_date', dateVal + ' 12:00:00');
                }

                viewerSelectedFiles.forEach(f => {
                    formData.append('files[]', f);
                });

                const res = await fetch('api/files.php?action=upload', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    showToast('Upload Complete', `${viewerSelectedFiles.length} file(s) saved successfully.`, 'success');
                    closeUploadModal();
                    clearViewerFiles();
                    
                    // If file_ids array was returned, navigate to the first uploaded file, otherwise reload
                    setTimeout(() => {
                        if (data.file_ids && data.file_ids.length > 0) {
                            window.location.href = `patient_file_view.php?id=${data.file_ids[0]}`;
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                } else {
                    showToast('Upload Error', data.message || 'Unable to upload files.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = ogText;
                }
            } catch (err) {
                console.error(err);
                showToast('Upload Failed', 'Network or server error during upload.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = ogText;
            }
        }

        // Toast Helper
        function showToast(title, msg, type = 'success') {
            const toast = document.getElementById('viewer-toast');
            const toastBody = document.getElementById('viewer-toast-body');
            const toastTitle = document.getElementById('viewer-toast-title');
            const toastMsg = document.getElementById('viewer-toast-msg');
            const toastIcon = document.getElementById('viewer-toast-icon');

            if (!toast) return;

            toastTitle.textContent = title;
            toastMsg.textContent = msg;

            if (type === 'success') {
                toastBody.className = 'p-4 rounded-2xl shadow-xl flex items-center gap-3 text-white bg-emerald-600 border border-emerald-500';
                toastIcon.className = 'fa-solid fa-circle-check text-xl';
            } else if (type === 'warning') {
                toastBody.className = 'p-4 rounded-2xl shadow-xl flex items-center gap-3 text-white bg-amber-600 border border-amber-500';
                toastIcon.className = 'fa-solid fa-triangle-exclamation text-xl';
            } else {
                toastBody.className = 'p-4 rounded-2xl shadow-xl flex items-center gap-3 text-white bg-rose-600 border border-rose-500';
                toastIcon.className = 'fa-solid fa-circle-xmark text-xl';
            }

            toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-[-10px]');
            toast.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-[-10px]');
                toast.classList.remove('opacity-100', 'translate-y-0');
            }, 3500);
        }
    </script>

    <!-- jsPDF & Medical Dossier PDF Generator Engine -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/pdf-generator.js?v=<?= time() ?>"></script>
</body>
</html>

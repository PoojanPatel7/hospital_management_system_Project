<?php require_once 'auth.php'; ?>
<?php 
if (!isset($_GET['id'])) {
    header("Location: patients.php");
    exit;
}
$patient_id = $_GET['id'];
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-6xl mx-auto space-y-6 pb-16">
    <!-- Top Back Bar -->
    <div class="flex items-center justify-between gap-3">
        <a href="patients.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Patients Directory</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="queue.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-xs">
                <i class="fa-solid fa-users-line text-indigo-600"></i>
                <span>Hospital Live Queue</span>
            </a>
        </div>
    </div>

    <!-- ================= SMALL PATIENT CARD (MINIMAL & CLEAN) ================= -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <!-- Left: Avatar, Name & Key Badges -->
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-indigo-100 shrink-0" id="card-patient-initials">
                    <i class="fa-solid fa-user-injured text-xl text-white/80"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight" id="card-patient-name">Loading...</h1>
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200" id="card-patient-mrn"><?= htmlspecialchars($patient_id) ?></span>
                        <span id="card-patient-status" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span>Active</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs text-slate-500 font-medium mt-1.5 flex-wrap">
                        <span id="card-patient-age-gender">Age: -- • Gender: --</span>
                        <span>•</span>
                        <span id="card-patient-blood" class="text-rose-600 font-bold">Blood: --</span>
                        <span>•</span>
                        <span id="card-patient-phone">📞 --</span>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <!-- Full Info Modal Trigger -->
                <button onclick="openFullInfoModal()" class="px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center gap-2 transition border border-indigo-200 shadow-xs">
                    <i class="fa-solid fa-circle-info text-sm"></i>
                    <span>Full Info</span>
                </button>

                <!-- Check-In & Token Profile Link -->
                <a href="patient_profile_qr.php?patient_id=<?= urlencode($patient_id) ?>" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-ticket-simple"></i>
                    <span>Token &amp; Check-In</span>
                </a>

                <!-- Lifetime QR Card Trigger -->
                <button onclick="showPatientQRCode(patientId)" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs flex items-center gap-2 transition shadow-sm" title="View & Print Lifetime QR Card">
                    <i class="fa-solid fa-qrcode text-indigo-300"></i>
                    <span>QR Card</span>
                </button>

                <!-- Export Patient Dossier PDF Trigger -->
                <button onclick="generatePatientPDF(patientId)" class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs flex items-center gap-2 transition border border-rose-200 shadow-xs" title="Download Complete Medical File PDF">
                    <i class="fa-solid fa-file-pdf text-sm text-rose-600"></i>
                    <span>Export PDF</span>
                </button>

                <!-- Online Doctor Consultation -->
                <a href="online_consult.php?patient_id=<?= urlencode($patient_id) ?>" class="px-4 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs flex items-center gap-2 transition border border-blue-200 shadow-xs" title="Open Online Doctor Consultation">
                    <i class="fa-solid fa-laptop-medical text-sm text-blue-600"></i>
                    <span>Online Consult</span>
                </a>

                <!-- Upload File / Letterhead Trigger -->
                <?php if ($canUpload): ?>
                <button onclick="openUploadFileModal()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                    <i class="fa-solid fa-camera"></i>
                    <span>+ Upload Doctor Letterhead / File</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ================= PATIENT MEDICAL FILES & DOCTOR PRESCRIPTIONS ================= -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-5">
        <!-- Section Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-folder-medical"></i>
                </div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-black text-slate-900">Medical Files & Prescriptions</h2>
                    <span id="files-count-badge" class="text-xs font-black px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-mono">0</span>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="file-search-input" oninput="filterPatientFiles()" placeholder="Search files by title, doctor..." class="w-full h-9 pl-8 pr-3 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-indigo-600 transition">
                </div>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs custom-scrollbar">
            <button onclick="setFileCategoryFilter('All', this)" class="file-cat-filter active-filter px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shrink-0">All Files</button>
            <button onclick="setFileCategoryFilter('Doctor Letterhead', this)" class="file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0">📝 Doctor Letterhead</button>
            <button onclick="setFileCategoryFilter('Prescription', this)" class="file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0">📋 Prescriptions</button>
            <button onclick="setFileCategoryFilter('Lab Report', this)" class="file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0">🧪 Lab Reports</button>
            <button onclick="setFileCategoryFilter('Radiology Scan', this)" class="file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0">🩻 Radiology Scans</button>
            <button onclick="setFileCategoryFilter('Other', this)" class="file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0">📁 Other</button>
        </div>

        <!-- Files Grid -->
        <div id="patient-files-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Loading Skeleton -->
            <div class="animate-pulse bg-slate-50 rounded-2xl h-64 border border-slate-200"></div>
            <div class="animate-pulse bg-slate-50 rounded-2xl h-64 border border-slate-200"></div>
            <div class="animate-pulse bg-slate-50 rounded-2xl h-64 border border-slate-200"></div>
        </div>
    </div>
</div>

<!-- ================= MODAL: PATIENT FULL INFORMATION POPUP ================= -->
<div id="modal-patient-full-info" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 relative shadow-2xl border border-slate-200 my-auto animate-in fade-in zoom-in duration-200" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeFullInfoModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <div class="flex items-center gap-3.5 mb-5 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-900" id="full-info-name">Patient Full Profile</h3>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" id="full-info-mrn">MRN: -</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100" id="full-info-status">Active Patient</span>
                </div>
            </div>
        </div>

        <!-- Demographics Grid -->
        <div class="space-y-4 text-xs">
            <div class="grid grid-cols-3 gap-2.5">
                <div class="bg-slate-50 rounded-2xl p-3 text-center border border-slate-100">
                    <span class="block text-[10px] text-slate-400 font-extrabold uppercase tracking-wider mb-0.5">Age</span>
                    <span id="full-info-age" class="text-sm font-black text-slate-800">--</span>
                </div>
                <div class="bg-slate-50 rounded-2xl p-3 text-center border border-slate-100">
                    <span class="block text-[10px] text-slate-400 font-extrabold uppercase tracking-wider mb-0.5">Gender</span>
                    <span id="full-info-gender" class="text-sm font-black text-slate-800">--</span>
                </div>
                <div class="bg-rose-50/80 rounded-2xl p-3 text-center border border-rose-100">
                    <span class="block text-[10px] text-rose-500 font-extrabold uppercase tracking-wider mb-0.5">Blood Group</span>
                    <span id="full-info-blood" class="text-sm font-black text-rose-700">--</span>
                </div>
            </div>

            <!-- Details List -->
            <div class="bg-slate-50/60 rounded-2xl p-4 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Contact Phone:</span>
                    <a href="#" id="full-info-phone" class="font-bold text-indigo-600 hover:text-indigo-800 transition">--</a>
                </div>
                <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Father's / Guardian Name:</span>
                    <span id="full-info-father" class="font-bold text-slate-800">--</span>
                </div>
                <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Emergency Contact:</span>
                    <div class="text-right">
                        <span id="full-info-em-name" class="font-bold text-slate-800 block">--</span>
                        <a href="#" id="full-info-em-phone" class="text-rose-600 font-bold hover:underline">--</a>
                    </div>
                </div>
                <div id="full-info-bed-row" class="hidden flex items-center justify-between gap-2">
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Admitted Bed:</span>
                    <span id="full-info-bed" class="font-black text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded border border-blue-200">Bed #--</span>
                </div>
            </div>

            <!-- QR Card Highlight Strip -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white flex items-center justify-between gap-3 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-xl p-1 shrink-0 flex items-center justify-center">
                        <img id="full-info-qr-thumb" src="" alt="QR" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <div class="font-bold text-white text-xs">Lifetime Patient Health QR</div>
                        <div class="text-[10px] text-indigo-300">Instant scan for authorized doctor access</div>
                    </div>
                </div>
                <button onclick="closeFullInfoModal(); showPatientQRCode(patientId);" class="px-3 py-2 rounded-xl bg-white text-slate-900 font-bold text-xs hover:bg-slate-100 transition shadow-sm shrink-0">
                    <i class="fa-solid fa-qrcode mr-1"></i> View Card
                </button>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between gap-3">
            <?php if ($canEditPatients): ?>
            <button onclick="closeFullInfoModal(); openEditProfile();" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-pen text-indigo-600"></i> Edit Profile
            </button>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <button onclick="closeFullInfoModal()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs transition">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ================= UPLOAD MEDICAL FILE / DOCTOR LETTERHEAD MODAL ================= -->
<div id="modal-upload-patient-file" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 relative shadow-2xl border border-slate-200 my-auto" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeUploadFileModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shadow-xs">
                <i class="fa-solid fa-camera"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 leading-tight">Upload Doctor Letterhead / File</h3>
                <p class="text-xs text-slate-500">Capture photo with phone camera or upload digital file.</p>
            </div>
        </div>

        <form id="upload-file-form" onsubmit="handleQuickFileUpload(event)" class="space-y-4">
            <!-- Camera / File Selector Dropzone -->
            <div>
                <input type="file" id="quick-file-input" accept="image/*,application/pdf" multiple class="hidden" onchange="handleQuickFileSelection(this)">
                
                <div onclick="document.getElementById('quick-file-input').click()" class="border-2 border-dashed border-teal-300 hover:border-teal-500 bg-teal-50/30 hover:bg-teal-50/60 rounded-2xl p-5 text-center cursor-pointer transition">
                    <div class="w-12 h-12 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center mx-auto mb-2 text-xl shadow-xs">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Tap to Select Multiple Images / Documents</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Select multiple images at once (Doctor letterheads, prescriptions, lab reports, X-rays)</p>
                </div>

                <!-- Instant Multi-Preview Container -->
                <div id="quick-preview-container" class="hidden mt-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 px-1">
                        <span id="quick-selected-count">0 files selected</span>
                        <button type="button" onclick="clearQuickFileSelection()" class="text-rose-600 hover:text-rose-700 font-semibold text-xs">Clear All</button>
                    </div>
                    <div id="quick-preview-list" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-44 overflow-y-auto custom-scrollbar p-1">
                    </div>
                </div>
            </div>

            <!-- Title & Category -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Document Category *</label>
                    <select id="quick-file-category" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-teal-500/30 focus:border-teal-600 outline-none bg-white">
                        <option value="Doctor Letterhead" selected>📝 Doctor Letterhead Pad</option>
                        <option value="Prescription">📋 Doctor Prescription Slip</option>
                        <option value="Lab Report">🧪 Pathology & Lab Report</option>
                        <option value="Radiology Scan">🩻 Radiology Scan (X-Ray/CT/MRI)</option>
                        <option value="Other">📁 Other Medical File</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Document Title *</label>
                    <input type="text" id="quick-file-title" value="Doctor Letterhead Pad" required placeholder="e.g. Doctor Letterhead Pad" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-teal-500/30 focus:border-teal-600 outline-none">
                </div>
            </div>

            <!-- Clinical Highlight / Note -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Clinical Note / Highlight (Optional)</label>
                <input type="text" id="quick-file-highlight" placeholder="e.g. Prescribed 5-day antibiotic course, follow-up after 1 week" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:ring-2 focus:ring-teal-500/30 focus:border-teal-600 outline-none">
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeUploadFileModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-quick-upload" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Upload to Record</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT PROFILE MODAL ================= -->
<div id="modal-edit-profile" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 relative shadow-2xl border border-slate-200 my-auto" onclick="event.stopPropagation()">
        <button onclick="document.getElementById('modal-edit-profile').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <h3 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-indigo-600"></i>
            <span>Edit Patient Profile</span>
        </h3>

        <form onsubmit="submitEditProfile(event)" class="space-y-3.5 text-xs">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">First Name *</label>
                    <input type="text" id="ep-name" required class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Surname *</label>
                    <input type="text" id="ep-surname" required class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Father's / Guardian Name</label>
                <input type="text" id="ep-father" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Age</label>
                    <input type="number" id="ep-age" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gender</label>
                    <select id="ep-gender" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium bg-white">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Blood Group</label>
                    <select id="ep-blood" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium bg-white">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                <input type="text" id="ep-phone" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Emergency Contact Name</label>
                    <input type="text" id="ep-em-name" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Emergency Phone</label>
                    <input type="text" id="ep-em-phone" class="w-full border border-slate-200 rounded-xl px-3 py-2 font-medium">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modal-edit-profile').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Cancel</button>
                <button type="submit" id="ep-submit-btn" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const patientId = "<?php echo htmlspecialchars($patient_id); ?>";
    let currentPatientData = null;
    let allPatientFiles = [];
    let currentCategoryFilter = 'All';

    window.addEventListener('DOMContentLoaded', () => {
        loadPatientProfile(patientId);
    });

    async function loadPatientProfile(id) {
        try {
            const res = await fetch(`api/history.php?action=get_dossier&patient_id=${encodeURIComponent(id)}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const p = data.dossier;
                currentPatientData = p;
                allPatientFiles = p.files || [];

                // 1. Populate Minimal Patient Card
                const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient Record';
                document.getElementById('card-patient-name').textContent = fullName;
                document.getElementById('card-patient-mrn').textContent = `MRN: ${p.id}`;
                
                const initials = ((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : 'P')).toUpperCase();
                document.getElementById('card-patient-initials').textContent = initials;

                let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
                if ((!age || age === '--') && p.demographics) {
                    const parts = p.demographics.split(',').map(s => s.trim());
                    parts.forEach(part => {
                        if (part.endsWith('Y') || part.endsWith('y') || !isNaN(part)) age = part;
                        if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
                        if (part.includes('+') || part.includes('-')) blood = part;
                    });
                }

                document.getElementById('card-patient-age-gender').textContent = `Age: ${age} • Gender: ${gender}`;
                document.getElementById('card-patient-blood').textContent = `Blood: ${blood}`;
                document.getElementById('card-patient-phone').textContent = p.phone ? `📞 ${p.phone}` : '📞 No phone';

                // Status Pill
                const statusEl = document.getElementById('card-patient-status');
                if (p.bed_number) {
                    statusEl.innerHTML = `<i class="fa-solid fa-bed text-blue-600 mr-1"></i> Admitted (Bed #${p.bed_number})`;
                    statusEl.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 border border-blue-200 flex items-center";
                } else if (p.status) {
                    statusEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1"></span> ${escapeHtml(p.status)}`;
                    statusEl.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center";
                }

                // 2. Pre-fetch QR Code for modal
                fetch(`api/qr.php?action=generate&patient_id=${encodeURIComponent(id)}`)
                    .then(r => r.json())
                    .then(q => {
                        if (q.status === 'success' && q.qr_image_url) {
                            const qrThumb = document.getElementById('full-info-qr-thumb');
                            if (qrThumb) qrThumb.src = q.qr_image_url;
                        }
                    })
                    .catch(console.error);

                // 3. Render Files Grid
                renderFilesGrid(allPatientFiles);

            } else {
                showToast('Error', data.message || 'Failed to load patient record.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'Unable to retrieve patient data.', 'error');
        }
    }

    // ================= FULL INFO MODAL =================
    function openFullInfoModal() {
        if (!currentPatientData) return;
        const p = currentPatientData;

        const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient Record';
        document.getElementById('full-info-name').textContent = fullName;
        document.getElementById('full-info-mrn').textContent = `MRN: ${p.id}`;

        let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
        if ((!age || age === '--') && p.demographics) {
            const parts = p.demographics.split(',').map(s => s.trim());
            parts.forEach(part => {
                if (part.endsWith('Y') || part.endsWith('y') || !isNaN(part)) age = part;
                if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
                if (part.includes('+') || part.includes('-')) blood = part;
            });
        }

        document.getElementById('full-info-age').textContent = age;
        document.getElementById('full-info-gender').textContent = gender;
        document.getElementById('full-info-blood').textContent = blood;

        const phoneLink = document.getElementById('full-info-phone');
        phoneLink.textContent = p.phone || 'Not provided';
        phoneLink.href = p.phone ? `tel:${p.phone.replace(/[^0-9+]/g, '')}` : '#';

        document.getElementById('full-info-father').textContent = p.father_name || 'Not provided';

        document.getElementById('full-info-em-name').textContent = p.emergency_contact_name || 'None listed';
        const emPhoneLink = document.getElementById('full-info-em-phone');
        emPhoneLink.textContent = p.emergency_contact_phone || '';
        emPhoneLink.href = p.emergency_contact_phone ? `tel:${p.emergency_contact_phone.replace(/[^0-9+]/g, '')}` : '#';

        const bedRow = document.getElementById('full-info-bed-row');
        if (p.bed_number) {
            bedRow.classList.remove('hidden');
            document.getElementById('full-info-bed').textContent = `Bed #${p.bed_number} (${p.status || 'Admitted'})`;
        } else {
            bedRow.classList.add('hidden');
        }

        document.getElementById('modal-patient-full-info').classList.remove('hidden');
    }

    function closeFullInfoModal() {
        document.getElementById('modal-patient-full-info').classList.add('hidden');
    }

    // ================= PATIENT FILES GRID =================
    function setFileCategoryFilter(cat, btn) {
        currentCategoryFilter = cat;
        document.querySelectorAll('.file-cat-filter').forEach(el => {
            el.className = "file-cat-filter px-3 py-1.5 rounded-xl font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0";
        });
        btn.className = "file-cat-filter active-filter px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shrink-0";
        filterPatientFiles();
    }

    function filterPatientFiles() {
        const query = (document.getElementById('file-search-input')?.value || '').toLowerCase().trim();
        const filtered = allPatientFiles.filter(f => {
            // Category check
            if (currentCategoryFilter !== 'All') {
                const fCat = (f.category || '').toLowerCase();
                const targetCat = currentCategoryFilter.toLowerCase();
                if (targetCat === 'doctor letterhead' && !fCat.includes('letterhead')) return false;
                else if (targetCat === 'prescription' && !fCat.includes('prescription')) return false;
                else if (targetCat === 'lab report' && !fCat.includes('lab') && !fCat.includes('pathology')) return false;
                else if (targetCat === 'radiology scan' && !fCat.includes('radiology') && !fCat.includes('scan') && !fCat.includes('x-ray')) return false;
                else if (targetCat === 'other' && (fCat.includes('letterhead') || fCat.includes('prescription') || fCat.includes('lab') || fCat.includes('radiology'))) return false;
            }
            // Query check
            if (query) {
                const matchStr = `${f.title || ''} ${f.category || ''} ${f.uploaded_by_name || ''} ${f.file_date || ''}`.toLowerCase();
                if (!matchStr.includes(query)) return false;
            }
            return true;
        });

        renderFilesGrid(filtered);
    }

    function formatHighlightDate(dateStr) {
        if (!dateStr) return '';
        if (/^\d{1,2}-[A-Za-z]{3}-\d{4}$/.test(dateStr)) return dateStr;
        const d = new Date(dateStr);
        if (!isNaN(d.getTime())) {
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return `${d.getDate()}-${months[d.getMonth()]}-${d.getFullYear()}`;
        }
        const match = String(dateStr).match(/(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return `${parseInt(match[3], 10)}-${months[parseInt(match[2], 10) - 1]}-${match[1]}`;
        }
        return dateStr;
    }

    function renderFilesGrid(files) {
        const grid = document.getElementById('patient-files-grid');
        const countBadge = document.getElementById('files-count-badge');
        if (countBadge) countBadge.textContent = files.length;

        if (!files || files.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full py-16 px-4 text-center bg-slate-50/70 rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-800 mb-1">No Medical Files or Prescriptions Found</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">No doctor letterheads or diagnostic reports match the current filter.</p>
                    <button onclick="openUploadFileModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs inline-flex items-center gap-2 transition shadow-md shadow-indigo-100">
                        <i class="fa-solid fa-camera"></i>
                        <span>Upload First Doctor Letterhead / File</span>
                    </button>
                </div>
            `;
            return;
        }

        grid.innerHTML = files.map(f => {
            const isPdf = (f.mime_type === 'application/pdf') || (f.file_name && f.file_name.toLowerCase().endsWith('.pdf'));
            const thumbUrl = `api/file.php?id=${f.id}&thumb=1`;
            const fullUrl = `api/file.php?id=${f.id}&file=${encodeURIComponent(f.file_name || 'file')}`;
            const isLetterhead = (f.category || '').toLowerCase().includes('letterhead');
            const fileDateFormatted = f.formatted_date || formatHighlightDate(f.record_date || f.file_date);

            // Category badge styling
            let catBadge = `<span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-teal-50 text-teal-800 border border-teal-200">${escapeHtml(f.category || 'File')}</span>`;
            if (isLetterhead) {
                catBadge = `<span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-800 border border-indigo-200">📝 Doctor Letterhead</span>`;
            } else if ((f.category || '').toLowerCase().includes('prescription')) {
                catBadge = `<span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 border border-blue-200">📋 Prescription</span>`;
            }

            return `
                <a href="patient_file_view.php?id=${f.id}" target="_blank" class="group bg-white rounded-2xl p-3 border border-slate-200 hover:border-indigo-400 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Thumbnail Preview Canvas with Highlighted Date -->
                        <div class="aspect-[4/3] bg-slate-900/5 rounded-xl overflow-hidden mb-3 border border-slate-100 flex items-center justify-center relative group-hover:scale-[1.02] transition duration-300">
                            <!-- Highlighted Date Overlay (e.g. 6-May-2020) -->
                            <div class="absolute top-2 left-2 px-2.5 py-1 rounded-lg bg-slate-900/85 backdrop-blur-md text-amber-300 font-black text-[11px] shadow-md border border-amber-400/30 flex items-center gap-1.5 z-10">
                                <i class="fa-regular fa-calendar-check text-amber-400"></i>
                                <span>${escapeHtml(fileDateFormatted)}</span>
                            </div>

                            ${isPdf ? `
                                <div class="flex flex-col items-center gap-1.5 text-rose-500">
                                    <i class="fa-solid fa-file-pdf text-4xl"></i>
                                    <span class="text-[9px] font-black uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">PDF Document</span>
                                </div>
                            ` : `
                                <img src="${thumbUrl}" alt="${escapeHtml(f.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='${fullUrl}'; this.onerror=null;" />
                            `}

                            <!-- Hover Overlay Indicator -->
                            <div class="absolute inset-0 bg-indigo-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-xs">
                                <span class="px-3 py-1.5 rounded-xl bg-white text-slate-900 font-extrabold text-xs shadow-lg flex items-center gap-1.5">
                                    <i class="fa-solid fa-expand text-indigo-600"></i> Open File Viewer
                                </span>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-1 flex-wrap">
                                ${catBadge}
                                <span class="text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/80 flex items-center gap-1">
                                    <i class="fa-regular fa-calendar text-amber-600"></i>
                                    <span>${escapeHtml(fileDateFormatted)}</span>
                                </span>
                            </div>

                            <h4 class="text-xs sm:text-sm font-black text-slate-900 group-hover:text-indigo-600 transition truncate" title="${escapeHtml(f.title || 'Untitled Document')}">
                                ${escapeHtml(f.title || 'Doctor Letterhead Pad')}
                            </h4>

                            ${f.highlight ? `
                                <p class="text-[11px] font-semibold text-amber-900 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/80 truncate">
                                    ⭐ ${escapeHtml(f.highlight)}
                                </p>
                            ` : ''}

                            <div class="text-[10px] text-slate-400 flex items-center gap-1 truncate pt-0.5">
                                <i class="fa-solid fa-user-doctor text-indigo-500"></i>
                                <span>${escapeHtml(f.uploaded_by_name || 'Hospital Staff')}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Strip -->
                    <div class="pt-2.5 mt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600">
                        <span class="group-hover:translate-x-1 transition-transform flex items-center gap-1 text-[11px]">
                            <span>View Full Size</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                        <span class="text-slate-400 text-[10px]">#${f.id}</span>
                    </div>
                </a>
            `;
        }).join('');
    }

    // ================= MULTIPLE FILES / DOCTOR LETTERHEAD UPLOAD =================
    let quickSelectedFiles = [];

    function openUploadFileModal() {
        clearQuickFileSelection();
        document.getElementById('quick-file-title').value = 'Doctor Letterhead Pad';
        document.getElementById('quick-file-category').value = 'Doctor Letterhead';
        document.getElementById('quick-file-highlight').value = '';
        document.getElementById('modal-upload-patient-file').classList.remove('hidden');
    }

    function closeUploadFileModal() {
        document.getElementById('modal-upload-patient-file').classList.add('hidden');
    }

    function handleQuickFileSelection(input) {
        if (!input.files || input.files.length === 0) return;
        for (let i = 0; i < input.files.length; i++) {
            quickSelectedFiles.push(input.files[i]);
        }
        renderQuickFilesPreview();
        input.value = '';
    }

    function renderQuickFilesPreview() {
        const container = document.getElementById('quick-preview-container');
        const list = document.getElementById('quick-preview-list');
        const countEl = document.getElementById('quick-selected-count');
        if (!container || !list) return;

        if (quickSelectedFiles.length === 0) {
            container.classList.add('hidden');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');
        countEl.textContent = `${quickSelectedFiles.length} file${quickSelectedFiles.length === 1 ? '' : 's'} selected`;

        list.innerHTML = '';
        quickSelectedFiles.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = "relative p-2 rounded-xl bg-white border border-slate-200 flex flex-col items-center text-center shadow-xs";
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = "absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] flex items-center justify-center shadow hover:bg-rose-600 transition";
            removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            removeBtn.onclick = (e) => {
                e.stopPropagation();
                quickSelectedFiles.splice(idx, 1);
                renderQuickFilesPreview();
            };

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = "w-full h-14 object-cover rounded-lg mb-1 border border-slate-100";
                img.src = URL.createObjectURL(file);
                item.appendChild(img);
            } else {
                const iconDiv = document.createElement('div');
                iconDiv.className = "w-full h-14 bg-slate-100 rounded-lg mb-1 flex items-center justify-center text-rose-500 text-xl";
                iconDiv.innerHTML = '<i class="fa-solid fa-file-pdf"></i>';
                item.appendChild(iconDiv);
            }

            const nameP = document.createElement('p');
            nameP.className = "text-[10px] font-bold text-slate-800 truncate w-full";
            nameP.textContent = file.name;
            item.appendChild(nameP);

            const sizeP = document.createElement('p');
            sizeP.className = "text-[9px] text-slate-400 font-mono";
            sizeP.textContent = `${(file.size / 1024).toFixed(1)} KB`;
            item.appendChild(sizeP);

            item.appendChild(removeBtn);
            list.appendChild(item);
        });
    }

    function clearQuickFileSelection() {
        quickSelectedFiles = [];
        const input = document.getElementById('quick-file-input');
        if (input) input.value = '';
        renderQuickFilesPreview();
    }

    async function handleQuickFileUpload(e) {
        e.preventDefault();
        if (!quickSelectedFiles || quickSelectedFiles.length === 0) {
            showToast('Files Required', 'Please select or capture at least one image/file.', 'warning');
            return;
        }

        const title = document.getElementById('quick-file-title').value.trim() || 'Doctor Letterhead Pad';
        const category = document.getElementById('quick-file-category').value;
        const highlight = document.getElementById('quick-file-highlight').value.trim();

        const submitBtn = document.getElementById('btn-submit-quick-upload');
        const ogText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Uploading ' + quickSelectedFiles.length + ' file(s)...';

        try {
            const formData = new FormData();
            formData.append('patient_id', patientId);
            formData.append('title', title);
            formData.append('category', category);
            formData.append('highlight', highlight);
            formData.append('record_date', new Date().toISOString());

            quickSelectedFiles.forEach(f => {
                formData.append('files[]', f);
            });

            const res = await fetch('api/files.php?action=upload', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                showToast('Upload Complete', `${quickSelectedFiles.length} file(s) saved successfully.`);
                closeUploadFileModal();
                clearQuickFileSelection();
                loadPatientProfile(patientId);
            } else {
                showToast('Upload Error', data.message || 'Unable to upload files.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Network Error', 'Failed to connect to server during upload.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = ogText;
        }
    }

    // ================= EDIT PROFILE =================
    function openEditProfile() {
        if (!currentPatientData) return;
        const p = currentPatientData;

        document.getElementById('ep-name').value = p.name || '';
        document.getElementById('ep-surname').value = p.surname || '';
        document.getElementById('ep-father').value = p.father_name || '';
        document.getElementById('ep-phone').value = p.phone || '';
        
        let age = p.age || '', gender = p.gender || '', blood = p.blood_group || '';
        if (!age && p.demographics) {
            const parts = p.demographics.split(',').map(s => s.trim());
            parts.forEach(part => {
                if (part.endsWith('Y') || part.endsWith('y')) age = part.replace(/[^0-9]/g, '');
                if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
                if (part.includes('+') || part.includes('-')) blood = part;
            });
        }
        
        document.getElementById('ep-age').value = age;
        document.getElementById('ep-gender').value = gender;
        document.getElementById('ep-blood').value = blood;
        document.getElementById('ep-em-name').value = p.emergency_contact_name || '';
        document.getElementById('ep-em-phone').value = p.emergency_contact_phone || '';

        document.getElementById('modal-edit-profile').classList.remove('hidden');
    }

    async function submitEditProfile(e) {
        e.preventDefault();
        const btn = document.getElementById('ep-submit-btn');
        const ogText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const payload = {
            id: currentPatientData.id,
            name: document.getElementById('ep-name').value.trim(),
            surname: document.getElementById('ep-surname').value.trim(),
            father: document.getElementById('ep-father').value.trim(),
            phone: document.getElementById('ep-phone').value.trim(),
            age: document.getElementById('ep-age').value.trim(),
            gender: document.getElementById('ep-gender').value.trim(),
            blood_group: document.getElementById('ep-blood').value.trim(),
            emergency_contact_name: document.getElementById('ep-em-name').value.trim(),
            emergency_contact_phone: document.getElementById('ep-em-phone').value.trim()
        };

        try {
            const res = await fetch('api/patients.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                showToast('Profile Updated', 'Patient details updated successfully.');
                document.getElementById('modal-edit-profile').classList.add('hidden');
                loadPatientProfile(currentPatientData.id);
            } else {
                showToast('Error', data.message || 'Failed to update patient profile.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'An unexpected network error occurred.', 'error');
        } finally {
            btn.innerHTML = ogText;
            btn.disabled = false;
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
</script>

<!-- jsPDF & Medical File PDF Generator Engine -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="js/pdf-generator.js?v=<?= time() ?>"></script>

<?php include 'includes/patient_qr_card_modal.php'; ?>

<?php include 'includes/footer.php'; ?>

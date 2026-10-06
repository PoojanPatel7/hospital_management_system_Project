<?php
// Dedicated Staff Operating Command Center
$staffId = (int)($_SESSION['staff_id'] ?? 0);
$staffQuery = $conn->prepare("SELECT * FROM staff WHERE id = ?");
$staffQuery->bind_param("i", $staffId);
$staffQuery->execute();
$staffProfile = $staffQuery->get_result()->fetch_assoc() ?? [];

// Check today's attendance for this staff member
$todayAttQuery = $conn->prepare("SELECT * FROM staff_attendance WHERE staff_id = ? AND date = CURDATE() ORDER BY id DESC LIMIT 1");
$todayAttQuery->bind_param("i", $staffId);
$todayAttQuery->execute();
$todayAttendance = $todayAttQuery->get_result()->fetch_assoc();

// Check recent uploads made by this staff member
$recentUploadsQuery = $conn->prepare("SELECT id, patient_id, title, category, DATE_FORMAT(record_date, '%b %d, %Y %h:%i %p') as formatted_date FROM patient_files WHERE uploaded_by_id = ? ORDER BY id DESC LIMIT 6");
$recentUploadsQuery->bind_param("i", $staffId);
$recentUploadsQuery->execute();
$recentUploads = $recentUploadsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Count total files uploaded by this staff member
$totalUploadsCount = count($recentUploads);
$countQuery = $conn->query("SELECT COUNT(*) as total FROM patient_files WHERE uploaded_by_id = $staffId");
if ($countQuery && $cRow = $countQuery->fetch_assoc()) {
    $totalUploadsCount = (int)$cRow['total'];
}
?>

<div class="space-y-6 pb-12 max-w-7xl mx-auto">

    <!-- ================= TOP HERO: STAFF COMMAND BANNER ================= -->
    <div class="relative overflow-hidden bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-200/80 shadow-[0_10px_35px_rgb(0,0,0,0.03)] group">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-gradient-to-br from-blue-100/40 via-indigo-100/30 to-purple-100/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left: Staff Greeting & Context -->
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-slate-900 text-white shadow-sm">
                        <i class="fa-solid fa-id-card-clip text-amber-400"></i>
                        <span>STAFF WORKSPACE</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Terminal Online</span>
                    </span>
                    <span id="staff-digital-clock" class="text-xs font-mono font-bold text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                        <?php echo date('h:i:s A'); ?>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 leading-tight">
                    Welcome, <?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'Staff Member'); ?>
                </h1>
                <p class="text-slate-500 mt-1.5 text-xs sm:text-sm font-medium max-w-2xl leading-relaxed">
                    <?php echo htmlspecialchars($hospital_name); ?> &bull; 
                    <span class="font-bold text-slate-700"><?php echo htmlspecialchars($staffProfile['department'] ?? 'General'); ?> Department</span> &bull; 
                    <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($staffProfile['role'] ?? 'Staff'); ?></span>
                </p>
            </div>

            <!-- Right: Today's Face Attendance Quick Status -->
            <div class="shrink-0">
                <?php if ($todayAttendance && ($todayAttendance['status'] ?? '') === 'Present'): ?>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3.5 shadow-xs">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-emerald-200">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Today's Attendance</div>
                            <div class="text-sm font-extrabold text-slate-900">Present (Face Verified)</div>
                            <div class="text-xs text-emerald-600 font-medium mt-0.5">
                                Logged at <?php echo date('h:i A', strtotime($todayAttendance['check_in_time'])); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="selfie_attendance.php" class="p-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white flex items-center gap-3.5 shadow-md hover:shadow-lg transition-all group/att">
                        <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur text-white flex items-center justify-center text-xl shrink-0 group-hover/att:scale-105 transition-transform">
                            <i class="fa-solid fa-camera animate-pulse"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-wider text-amber-100">Daily Check-In Needed</div>
                            <div class="text-sm font-extrabold">Mark Face Attendance Now</div>
                            <div class="text-xs text-amber-100 font-medium mt-0.5 flex items-center gap-1">
                                <span>Scan Camera & Geofence</span> &rarr;
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ================= QUICK RECEPTION SCAN & SEARCH HUB ================= -->
    <?php include __DIR__ . '/reception_hub.php'; ?>

    <!-- ================= PERMISSION & PRIVACY POLICY STATUS CARD ================= -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0 border border-indigo-100">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Active Terminal Access & Privacy Policy</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?php if (!$canViewFiles && $canUpload): ?>
                        <strong class="text-amber-700">🔒 Upload-Only Mode:</strong> Your account is authorized to upload diagnostic scans and reports. Past confidential files are protected.
                    <?php else: ?>
                        Your account operates under strict role-based access control. All file uploads are digitally signed with your staff ID.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5 self-start md:self-auto">
            <?php if ($canUpload): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    <i class="fa-solid fa-cloud-arrow-up text-[10px]"></i> Upload Files
                </span>
            <?php endif; ?>
            <?php if ($canViewPatients): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    <i class="fa-solid fa-users text-[10px]"></i> Patient Lookup
                </span>
            <?php endif; ?>
            <?php if ($canViewFiles): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-eye text-[10px]"></i> View Files
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <i class="fa-solid fa-lock text-[10px]"></i> Files Protected
                </span>
            <?php endif; ?>
            <?php if ($canManageAppts): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fa-solid fa-calendar-check text-[10px]"></i> Appointments
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================= FAST ACTION WORKSPACE BENTO GRID ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Card 1: Fast Patient Report & File Upload -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4 border border-blue-100 shadow-xs">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 leading-tight">Upload Patient Medical Files</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                    Attach diagnostic X-rays, blood reports, MRI/CT scans, and prescriptions directly to patient lifetime records.
                </p>

                <!-- Fast Patient Lookup Input -->
                <div class="mt-4 space-y-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="quick-upload-patient-search" placeholder="Enter Patient ID (e.g. CP-2026-001) or Name..." class="w-full text-xs font-semibold pl-9 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <button onclick="handleQuickUploadJump()" class="w-full py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        <span>Open Patient to Upload</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="patients.php" class="font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                    <span>Browse Patient Directory</span> &rarr;
                </a>
                <span class="text-slate-400 font-medium">Lifetime QR Compatible</span>
            </div>
        </div>

        <!-- Card 2: Selfie Attendance & Geofence -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-4 border border-emerald-100 shadow-xs">
                    <i class="fa-solid fa-camera-retro"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 leading-tight">Daily Selfie Face Attendance</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                    Mark your daily hospital attendance with front camera face verification and GPS location geofencing.
                </p>

                <div class="mt-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Today's Date:</span>
                        <span class="font-bold text-slate-800"><?php echo date('l, M d, Y'); ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Status:</span>
                        <?php if ($todayAttendance && ($todayAttendance['status'] ?? '') === 'Present'): ?>
                            <span class="font-bold text-emerald-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> Present (<?php echo date('h:i A', strtotime($todayAttendance['check_in_time'])); ?>)
                            </span>
                        <?php else: ?>
                            <span class="font-bold text-amber-600 flex items-center gap-1">
                                <i class="fa-solid fa-clock"></i> Not Marked Yet
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="selfie_attendance.php" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-camera"></i>
                    <span>Open Attendance Camera</span>
                </a>
            </div>
        </div>

        <!-- Card 3: Patient Directory & Lifetime QR System -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-4 border border-indigo-100 shadow-xs">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 leading-tight">Patient Lifetime QR Records</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                    View registered patients, display high-resolution printable ID cards, and access patient files instantly via permanent QR codes.
                </p>

                <div class="mt-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-400 font-black uppercase tracking-wider">My Total Uploads</div>
                        <div class="text-xl font-black text-slate-900 mt-0.5"><?php echo $totalUploadsCount; ?> file<?php echo $totalUploadsCount === 1 ? '' : 's'; ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-bold">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="patients.php" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-address-book"></i>
                    <span>Search Patients</span>
                </a>
            </div>
        </div>

    </div>

    <!-- ================= MY RECENT UPLOADS AUDIT LOG ================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> My Recent File Upload Activity
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Files uploaded to patient medical records under your authenticated staff account.</p>
            </div>
            <a href="patients.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1 self-start sm:self-auto">
                <span>Upload New File</span> &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 min-w-[700px]">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200 text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-5">Document Title</th>
                        <th class="py-3 px-5">Category</th>
                        <th class="py-3 px-5">Patient ID</th>
                        <th class="py-3 px-5">Date & Time</th>
                        <th class="py-3 px-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentUploads)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                                You have not uploaded any patient files yet. Select a patient from the directory to upload reports.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentUploads as $file): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </div>
                                        <span class="truncate max-w-[220px]" title="<?php echo htmlspecialchars($file['title']); ?>">
                                            <?php echo htmlspecialchars($file['title'] ?: 'Medical File'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <?php echo htmlspecialchars($file['category'] ?: 'Other'); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-mono font-bold text-blue-600">
                                    <?php echo htmlspecialchars($file['patient_id']); ?>
                                </td>
                                <td class="py-3.5 px-5 text-slate-500 font-medium">
                                    <?php echo htmlspecialchars($file['formatted_date']); ?>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="patient_profile.php?id=<?php echo urlencode($file['patient_id']); ?>" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-black text-white text-[11px] font-bold transition inline-flex items-center gap-1 shadow-xs">
                                        <span>Patient Dossier</span> &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Digital clock update
setInterval(() => {
    const el = document.getElementById('staff-digital-clock');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString();
    }
}, 1000);

function handleQuickUploadJump() {
    const query = (document.getElementById('quick-upload-patient-search').value || '').trim();
    if (!query) {
        alert('Please enter a Patient ID or Name to open their record.');
        return;
    }
    // If it looks like a patient ID (e.g. CP-2026-...)
    if (query.toUpperCase().startsWith('CP-') || query.toUpperCase().startsWith('PT-')) {
        window.location.href = `patient_profile.php?id=${encodeURIComponent(query.toUpperCase())}`;
    } else {
        window.location.href = `patients.php?search=${encodeURIComponent(query)}`;
    }
}
</script>

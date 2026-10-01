<?php require_once 'auth.php'; ?>
<?php 
if (!isset($_GET['id'])) {
    header("Location: patients.php");
    exit;
}
$patient_id = $_GET['id'];
?>
<?php include 'includes/header.php'; ?>

<style>
/* Clean Print Layout for Official Prescription & Medical Summary */
@media print {
    body * {
        visibility: hidden !important;
    }
    #printable-prescription-container,
    #printable-prescription-container * {
        visibility: visible !important;
    }
    #printable-prescription-container {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100vw !important;
        height: auto !important;
        margin: 0 !important;
        padding: 24px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        display: block !important;
        z-index: 999999 !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="flex flex-col lg:flex-row gap-6 max-w-7xl mx-auto pb-12">
    <!-- ================= LEFT SIDEBAR: PATIENT DOSSIER & CLINICAL STATE ================= -->
    <div class="w-full lg:w-1/3 xl:w-1/4 space-y-6 shrink-0">
        <!-- Main Patient Info Card -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 relative">
                <a href="patients.php" class="absolute top-4 left-4 bg-black/25 hover:bg-black/45 backdrop-blur text-white w-9 h-9 flex items-center justify-center rounded-xl transition shadow-inner" title="Back to Patients">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <button onclick="openEditProfile()" class="absolute top-4 right-4 bg-black/25 hover:bg-black/45 backdrop-blur text-white px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 rounded-xl transition shadow-inner">
                    <i class="fa-solid fa-user-pen"></i>
                    <span>Edit Profile</span>
                </button>
            </div>
            <div class="px-6 pb-6 relative">
                <div class="w-24 h-24 bg-white rounded-2xl shadow-lg border-[5px] border-white flex items-center justify-center text-3xl font-black text-indigo-600 absolute -top-12 z-10 shadow-indigo-100" id="dossier-initials">
                    <i class="fa-solid fa-user-injured text-slate-300"></i>
                </div>
                <div class="pt-14">
                    <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                        <span id="dossier-status" class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200">
                            Loading
                        </span>
                        <span class="text-[11px] font-bold font-mono text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200" id="dossier-mrn">...</span>
                    </div>
                    <h2 id="dossier-name" class="text-2xl font-black text-slate-900 leading-snug mb-3">Loading...</h2>
                    
                    <!-- Demographics Grid -->
                    <div class="grid grid-cols-3 gap-2 mb-5">
                        <div class="bg-slate-50 rounded-xl p-2.5 text-center border border-slate-100">
                            <span class="block text-[9px] text-slate-400 font-extrabold uppercase tracking-wider mb-0.5">Age</span>
                            <span id="dossier-age" class="text-xs sm:text-sm font-extrabold text-slate-800">--</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-2.5 text-center border border-slate-100">
                            <span class="block text-[9px] text-slate-400 font-extrabold uppercase tracking-wider mb-0.5">Gender</span>
                            <span id="dossier-gender" class="text-xs sm:text-sm font-extrabold text-slate-800">--</span>
                        </div>
                        <div class="bg-rose-50/80 rounded-xl p-2.5 text-center border border-rose-100">
                            <span class="block text-[9px] text-rose-500 font-extrabold uppercase tracking-wider mb-0.5">Blood</span>
                            <span id="dossier-blood" class="text-xs sm:text-sm font-extrabold text-rose-700">--</span>
                        </div>
                    </div>

                    <!-- Active Bed Badge (Conditional for Admitted Inpatients) -->
                    <div id="dossier-active-bed-card" class="hidden mb-5 p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-blue-900 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base shrink-0 shadow-md shadow-blue-200">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700" id="dossier-bed-title">Inpatient Bed</span>
                                </div>
                                <div class="text-sm font-black text-slate-900 truncate" id="dossier-bed-number">Bed #--</div>
                                <div class="text-[11px] text-blue-700 font-medium truncate" id="dossier-bed-dept">Admitted for Care</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-5 border-slate-100">
                    
                    <!-- Contact Details -->
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/70 text-sm">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Patient Phone</p>
                                <a href="#" id="dossier-phone-link" class="text-xs sm:text-sm font-bold text-blue-600 hover:text-blue-800 transition truncate block">...</a>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100/70 text-sm">
                                <i class="fa-solid fa-user-group"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Father's Name</p>
                                <p id="dossier-father" class="text-xs sm:text-sm font-bold text-slate-800 truncate">...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5 pt-2 border-t border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100/70 text-sm">
                                <i class="fa-solid fa-truck-medical"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Emergency Contact</p>
                                <p id="dossier-emergency-name" class="text-xs sm:text-sm font-bold text-slate-800 truncate leading-tight">...</p>
                                <a href="#" id="dossier-emergency-phone-link" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition mt-0.5 block truncate">...</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Visit & Clinical Highlights Widget -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-stethoscope text-indigo-600"></i>
                    <span>Latest Visit Summary</span>
                </h3>
                <span id="dossier-last-date" class="text-[10px] font-bold text-slate-400 font-mono">--</span>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Department & Doctor</span> 
                    <div class="text-sm font-black text-slate-900 mt-0.5" id="dossier-last-doc">...</div>
                    <div class="text-[11px] font-bold text-indigo-600" id="dossier-last-dept">...</div>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Consultation Type</span> 
                    <span id="dossier-last-type" class="inline-block mt-0.5 text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">...</span>
                </div>

                <div id="dossier-last-diag-box">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Primary Diagnosis</span> 
                    <div id="dossier-last-diag" class="mt-0.5 text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100">None recorded</div>
                </div>

                <div id="dossier-last-symptoms-box">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Chief Complaints</span> 
                    <p id="dossier-last-symptoms" class="mt-0.5 text-xs text-slate-600 italic font-medium leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">None</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= RIGHT MAIN AREA: CLINICAL DOSSIER, CONSULTATIONS & FILES ================= -->
    <div class="flex-1 min-w-0 flex flex-col h-full space-y-5">
        
        <!-- Quick Stats & Top Bar -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="grid grid-cols-3 gap-3 flex-1">
                <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 block mb-0.5">Visits</span>
                    <span class="text-lg sm:text-xl font-black text-indigo-900" id="stat-total-visits">0</span>
                </div>
                <div class="bg-emerald-50/60 border border-emerald-100 rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 block mb-0.5">Medicines (Rx)</span>
                    <span class="text-lg sm:text-xl font-black text-emerald-900" id="stat-total-medicines">0</span>
                </div>
                <div class="bg-teal-50/60 border border-teal-100 rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-600 block mb-0.5">Scans & Reports</span>
                    <span class="text-lg sm:text-xl font-black text-teal-900" id="stat-total-files">0</span>
                </div>
            </div>

            <button onclick="printLatestConsultationSlip()" class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-md shrink-0">
                <i class="fa-solid fa-print text-indigo-400"></i>
                <span>Print Latest Prescription</span>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-1.5 flex gap-1.5 overflow-x-auto shrink-0 sticky top-0 z-20">
            <button onclick="switchDossierTab('appointments')" id="tab-btn-appointments" class="dossier-tab active flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold text-blue-700 bg-blue-50 transition whitespace-nowrap">
                <i class="fa-solid fa-clipboard-prescription"></i>
                <span>Appointments & Consultations</span>
                <span id="tab-count-appointments" class="px-2 py-0.5 rounded-full text-[10px] bg-blue-200 text-blue-800 font-extrabold ml-1">0</span>
            </button>
            <button onclick="switchDossierTab('files')" id="tab-btn-files" class="dossier-tab flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition whitespace-nowrap">
                <i class="fa-solid fa-file-waveform"></i>
                <span>Medical Reports, Scans & Files</span>
                <span id="tab-count-files" class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-extrabold ml-1">0</span>
            </button>
        </div>

        <!-- ================= TAB CONTENT 1: APPOINTMENTS & FULL CONSULTATIONS ================= -->
        <div id="tab-content-appointments" class="dossier-content flex-1">
            <div id="dossier-appointments-list" class="space-y-5">
                <!-- Skeleton Loader -->
                <div class="animate-pulse space-y-4">
                    <div class="h-28 bg-slate-100 rounded-3xl border border-slate-200"></div>
                    <div class="h-28 bg-slate-100 rounded-3xl border border-slate-200"></div>
                </div>
            </div>
        </div>

        <!-- ================= TAB CONTENT 2: ALL MEDICAL FILES & SCANS ================= -->
        <div id="tab-content-files" class="dossier-content hidden flex-1">
            <div id="dossier-files-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- ================= EDIT PROFILE MODAL ================= -->
<div id="modal-edit-profile" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 relative my-8 shadow-2xl border border-slate-200">
        <button onclick="document.getElementById('modal-edit-profile').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-900 leading-tight">Edit Patient Profile</h3>
                <p class="text-xs text-slate-500">Update demographic and emergency details.</p>
            </div>
        </div>

        <form id="edit-profile-form" onsubmit="submitEditProfile(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">First Name *</label>
                <input type="text" id="ep-name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Surname *</label>
                <input type="text" id="ep-surname" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Father's Name *</label>
                <input type="text" id="ep-father" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Phone Number</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter 10 digits" id="ep-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            
            <!-- Demographics -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Age</label>
                <input type="number" id="ep-age" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Gender</label>
                <select id="ep-gender" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none bg-white">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Blood Group</label>
                <select id="ep-blood" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none bg-white">
                    <option value="">Select Blood Group</option>
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
            
            <!-- Emergency Contact -->
            <div class="md:col-span-2 pt-2 border-t border-slate-100 mt-2">
                <h4 class="text-xs font-bold text-slate-800 mb-2">Emergency Contact</h4>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Contact Name</label>
                <input type="text" id="ep-em-name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Contact Phone</label>
                <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter 10 digits" id="ep-em-phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 outline-none">
            </div>

            <div class="md:col-span-2 pt-4">
                <button type="submit" id="ep-submit-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-200 transition text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= OFFICIAL PRINTABLE PRESCRIPTION & MEDICAL SUMMARY MODAL ================= -->
<div id="modal-print-prescription" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-200 relative my-6 flex flex-col max-h-[94vh] overflow-hidden">
        
        <!-- Modal Top Bar (Non-printed) -->
        <div class="p-4 sm:px-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between shrink-0 no-print">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-file-prescription"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 leading-none">Medical Consultation & Prescription Slip</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Print or save as PDF for patient record</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-200 transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Slip</span>
                </button>
                <button onclick="closePrintModal()" class="w-8 h-8 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Printable Document Area -->
        <div class="flex-1 overflow-y-auto p-6 sm:p-10 bg-white" id="printable-prescription-container">
            <!-- Hospital Header -->
            <div class="border-b-2 border-slate-900 pb-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-md">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Hospital Management System</h1>
                        <p class="text-xs font-bold text-indigo-700 tracking-wide uppercase">Outpatient & Clinical Workstation</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Accredited Multispeciality Medical Care • 24x7 Emergency & Pharmacy</p>
                    </div>
                </div>
                <div class="text-left sm:text-right text-xs space-y-1 text-slate-600 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                    <div class="font-extrabold text-slate-900 font-mono text-sm" id="print-rx-code">APP-0000</div>
                    <div>Date: <strong id="print-rx-date" class="text-slate-900">--</strong></div>
                    <div>Phone: <span class="font-mono">+91 98765 43210</span></div>
                </div>
            </div>

            <!-- Patient & Doctor Demographics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs mb-6">
                <div class="space-y-1.5">
                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Patient Details</div>
                    <div class="text-base font-black text-slate-900" id="print-patient-name">Patient Name</div>
                    <div class="text-slate-600 flex flex-wrap gap-x-3 gap-y-1">
                        <span>MRN: <strong class="font-mono text-slate-900" id="print-patient-mrn">--</strong></span>
                        <span>Age / Sex: <strong id="print-patient-age-sex" class="text-slate-900">--</strong></span>
                        <span>Blood: <strong id="print-patient-blood" class="text-rose-700">--</strong></span>
                    </div>
                    <div class="text-slate-600">
                        <span>Father: <strong id="print-patient-father" class="text-slate-900">--</strong></span> • 
                        <span>Phone: <strong id="print-patient-phone" class="text-slate-900">--</strong></span>
                    </div>
                </div>

                <div class="space-y-1.5 sm:border-l sm:border-slate-200 sm:pl-4">
                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Consulting Specialist</div>
                    <div class="text-base font-black text-indigo-900" id="print-doctor-name">Dr. --</div>
                    <div class="text-xs font-bold text-slate-700" id="print-doctor-dept">Department of General Medicine</div>
                    <div class="text-slate-600">
                        <span>Visit Type: <strong id="print-visit-type" class="text-slate-900">General Consultation</strong></span>
                    </div>
                    <div class="text-slate-600">
                        <span>Care Disposition: <strong id="print-disposition-outcome" class="text-slate-900">Discharged (Home Care)</strong></span>
                    </div>
                </div>
            </div>

            <!-- Chief Complaints & Symptoms -->
            <div class="mb-5" id="print-symptoms-section">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-notes-medical text-indigo-600"></i>
                    <span>Chief Complaints & Presenting Symptoms:</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 leading-relaxed" id="print-symptoms-text">
                    None reported
                </div>
            </div>

            <!-- Diagnoses Section -->
            <div class="mb-5">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-stethoscope text-rose-600"></i>
                    <span>Confirmed Clinical Diagnoses:</span>
                </div>
                <div class="flex flex-wrap gap-2" id="print-diagnoses-list">
                    <span class="text-xs text-slate-400 italic">None specified</span>
                </div>
            </div>

            <!-- Prescriptions Table -->
            <div class="mb-6">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-pills text-emerald-600"></i>
                        <span>Prescribed Medicines (Rx) & Administration Schedule:</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold normal-case">Take medications strictly as instructed</span>
                </div>
                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100/80 text-slate-600 uppercase font-black text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">Medicine Name & Strength</th>
                                <th class="p-3 w-28">Dosage</th>
                                <th class="p-3 w-44">Frequency / When to Take</th>
                                <th class="p-3 w-28">Duration</th>
                                <th class="p-3">Instructions / Food Timing</th>
                            </tr>
                        </thead>
                        <tbody id="print-medicines-tbody" class="divide-y divide-slate-100 font-medium text-slate-800">
                            <!-- Populated in JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ordered Diagnostic Tests (if any) -->
            <div class="mb-6 hidden" id="print-tests-section">
                <div class="text-[11px] font-black uppercase tracking-wider text-amber-800 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-flask-vial text-amber-600"></i>
                    <span>Diagnostic Investigations & Scans Ordered:</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200">
                    <div class="flex flex-wrap gap-2 mb-2" id="print-tests-list"></div>
                    <p class="text-[11px] text-amber-800 font-medium">Please undergo the above diagnostic laboratory/radiology investigations and return for clinical review with test reports.</p>
                </div>
            </div>

            <!-- Doctor's Advice & Patient Instructions -->
            <div class="mb-8" id="print-advice-section">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-doctor text-indigo-600"></i>
                    <span>Doctor's Advice & Lifestyle Guidance:</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-800 leading-relaxed whitespace-pre-wrap" id="print-advice-text">
                    Standard home rest and adequate hydration. Re-consult if symptoms aggravate.
                </div>
            </div>

            <!-- Inpatient Bed Details (If Admitted) -->
            <div class="mb-8 hidden" id="print-bed-section">
                <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-bed text-blue-600 text-base"></i>
                        <div>
                            <span class="font-extrabold block">Inpatient Admission Record:</span>
                            <span id="print-bed-text" class="text-blue-800">Admitted to General Ward Bed #102</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-blue-200 text-blue-800">Active Inpatient</span>
                </div>
            </div>

            <!-- Footer / Signatures -->
            <div class="border-t-2 border-slate-200 pt-6 mt-8 flex flex-col sm:flex-row items-end justify-between gap-6">
                <div class="text-[11px] text-slate-400 space-y-0.5">
                    <p>• Not valid for medico-legal purposes.</p>
                    <p>• Keep medicines away from children. Store in a cool, dry place.</p>
                    <p>• In case of emergency or severe adverse reactions, report to Emergency Room immediately.</p>
                </div>
                <div class="text-center min-w-[200px] space-y-1">
                    <div class="h-12 border-b border-dashed border-slate-300"></div>
                    <div class="text-xs font-black text-slate-900" id="print-sign-doctor">Dr. --</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Authorized Signature & Seal</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= IMAGE PREVIEW MODAL ================= -->
<div id="modal-image-preview" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" onclick="closeImagePreview()">
    <div class="relative max-w-4xl max-h-[90vh] bg-black rounded-3xl overflow-hidden shadow-2xl p-2 flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button onclick="closeImagePreview()" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="image-preview-element" src="" class="max-w-full max-h-[80vh] object-contain rounded-2xl" alt="Medical Scan Preview">
        <div class="text-white text-xs font-bold mt-2 px-4 py-1 truncate max-w-full text-center" id="image-preview-title">Report Preview</div>
    </div>
</div>

<script>
    let currentPatientData = null;
    let currentAppointments = [];
    const patientId = "<?php echo htmlspecialchars($patient_id); ?>";

    window.addEventListener('DOMContentLoaded', () => {
        loadPatientProfile(patientId);
    });

    function switchDossierTab(tabId) {
        document.querySelectorAll('.dossier-tab').forEach(el => {
            el.classList.remove('text-blue-700', 'bg-blue-50');
            el.classList.add('text-slate-500', 'hover:bg-slate-50', 'hover:text-slate-700');
        });
        document.querySelectorAll('.dossier-content').forEach(el => el.classList.add('hidden'));

        const btn = document.getElementById(`tab-btn-${tabId}`);
        if (btn) {
            btn.classList.add('text-blue-700', 'bg-blue-50');
            btn.classList.remove('text-slate-500', 'hover:bg-slate-50', 'hover:text-slate-700');
        }
        const content = document.getElementById(`tab-content-${tabId}`);
        if (content) content.classList.remove('hidden');
    }

    // Helper: Map status to friendly disposition info
    function getDispositionMeta(status, bedNumber) {
        if (!status) {
            return {
                title: 'In Queue',
                badgeCls: 'bg-slate-100 text-slate-700 border-slate-200',
                icon: 'fa-solid fa-clock text-slate-500',
                desc: 'Patient is registered and currently in queue for consultation.'
            };
        }

        const s = status.trim();
        if (s.includes('Normal Medicine') || s === 'Discharged') {
            return {
                title: 'Discharged (Home Care)',
                badgeCls: 'bg-emerald-50 text-emerald-800 border-emerald-200 shadow-sm',
                icon: 'fa-solid fa-house-medical text-emerald-600',
                desc: 'Consultation completed. Patient discharged with prescribed medications for home recovery.'
            };
        } else if (s.includes('Waiting for Reports')) {
            return {
                title: 'Waiting for Lab Reports',
                badgeCls: 'bg-amber-50 text-amber-800 border-amber-300 shadow-sm',
                icon: 'fa-solid fa-flask-vial text-amber-600',
                desc: 'Consultation is on hold. Patient sent for diagnostic investigations and tests. Awaiting reports.'
            };
        } else if (s.includes('ICU')) {
            const bed = bedNumber ? `Bed #${bedNumber}` : 'ICU Bed';
            return {
                title: `Admitted (ICU) • ${bed}`,
                badgeCls: 'bg-rose-50 text-rose-800 border-rose-200 shadow-sm',
                icon: 'fa-solid fa-heart-pulse text-rose-600',
                desc: `Patient admitted to Intensive Care Unit (ICU) under continuous monitoring. Allocated Bed: ${bed}.`
            };
        } else if (s.includes('OPD') || s.includes('Admitted') || s.includes('Ward')) {
            const bed = bedNumber ? `Bed #${bedNumber}` : 'Ward Bed';
            return {
                title: `Admitted (Ward) • ${bed}`,
                badgeCls: 'bg-blue-50 text-blue-800 border-blue-200 shadow-sm',
                icon: 'fa-solid fa-bed text-blue-600',
                desc: `Patient admitted to Inpatient General Ward for observation and treatment. Allocated Bed: ${bed}.`
            };
        } else if (s.includes('In Consulting Room')) {
            return {
                title: 'In Consulting Room',
                badgeCls: 'bg-indigo-50 text-indigo-800 border-indigo-200 shadow-sm',
                icon: 'fa-solid fa-stethoscope text-indigo-600',
                desc: 'Patient is currently in the consulting room with the doctor.'
            };
        }

        return {
            title: s,
            badgeCls: 'bg-slate-100 text-slate-700 border-slate-200',
            icon: 'fa-solid fa-clipboard-check text-slate-500',
            desc: `Current visit state: ${s}`
        };
    }

    // Helper: Friendly medication frequency translation for patients
    function formatFrequency(freq) {
        if (!freq || !freq.trim()) {
            return { label: 'As directed', full: 'As instructed by doctor', icon: 'fa-regular fa-clock text-slate-400' };
        }
        const clean = freq.trim().toUpperCase();
        if (clean === 'OD' || clean === '1-0-0' || clean === '0-0-1') {
            return { label: freq, full: 'Once Daily (1 Time a Day)', icon: 'fa-solid fa-sun text-amber-500' };
        } else if (clean === 'BID' || clean === '1-0-1' || clean === 'TWICE DAILY') {
            return { label: freq, full: 'Twice Daily (Morning & Night)', icon: 'fa-solid fa-clock text-blue-500' };
        } else if (clean === 'TID' || clean === '1-1-1' || clean === 'THRICE DAILY') {
            return { label: freq, full: '3 Times Daily (Morning, Afternoon & Night)', icon: 'fa-solid fa-repeat text-indigo-500' };
        } else if (clean === 'QID' || clean === '1-1-1-1') {
            return { label: freq, full: '4 Times Daily (Every 6 Hours)', icon: 'fa-solid fa-rotate text-purple-500' };
        } else if (clean === 'SOS' || clean.includes('AS NEEDED') || clean.includes('WHEN NEEDED')) {
            return { label: freq, full: 'As Needed (Only during pain or fever)', icon: 'fa-solid fa-triangle-exclamation text-rose-500' };
        } else if (clean === 'HS' || clean.includes('BEDTIME')) {
            return { label: freq, full: 'At Bedtime (Before sleep)', icon: 'fa-solid fa-moon text-indigo-600' };
        } else if (clean === 'STAT') {
            return { label: freq, full: 'Immediate / Single Dose', icon: 'fa-solid fa-bolt text-red-600' };
        }
        return { label: freq, full: freq, icon: 'fa-regular fa-clock text-slate-400' };
    }

    async function loadPatientProfile(patient_id) {
        try {
            const res = await fetch(`api/history.php?action=get_dossier&patient_id=${encodeURIComponent(patient_id)}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const p = data.dossier;
                currentPatientData = p;
                currentAppointments = p.appointments || [];
                
                // Header Info
                const fullName = (p.name || '') + ' ' + (p.surname || '');
                document.getElementById('dossier-name').textContent = fullName.trim() || 'Patient Record';
                document.getElementById('dossier-initials').textContent = ((p.name ? p.name.charAt(0) : '') + (p.surname ? p.surname.charAt(0) : 'P')).toUpperCase();
                document.getElementById('dossier-mrn').textContent = p.id;
                document.getElementById('dossier-father').textContent = p.father_name || 'N/A';
                
                // Parse demographics
                let age = p.age || '--', gender = p.gender || '--', blood = p.blood_group || '--';
                if ((!age || age === '--') && p.demographics) {
                    const parts = p.demographics.split(',').map(s => s.trim());
                    parts.forEach(part => {
                        if (part.endsWith('Y') || part.endsWith('y') || !isNaN(part)) age = part;
                        if (part === 'Male' || part === 'Female' || part === 'Other') gender = part;
                        if (part.includes('+') || part.includes('-')) blood = part;
                    });
                }
                
                document.getElementById('dossier-age').textContent = age;
                document.getElementById('dossier-gender').textContent = gender;
                document.getElementById('dossier-blood').textContent = blood;
                
                // Phones with direct tel links
                const phoneLink = document.getElementById('dossier-phone-link');
                phoneLink.textContent = p.phone || 'Not provided';
                phoneLink.href = p.phone ? `tel:${p.phone.replace(/[^0-9+]/g, '')}` : '#';
                
                document.getElementById('dossier-emergency-name').textContent = p.emergency_contact_name || 'Not provided';
                const emPhoneLink = document.getElementById('dossier-emergency-phone-link');
                emPhoneLink.textContent = p.emergency_contact_phone || 'No phone';
                emPhoneLink.href = p.emergency_contact_phone ? `tel:${p.emergency_contact_phone.replace(/[^0-9+]/g, '')}` : '#';
                
                // Active Disposition Status
                const dispMeta = getDispositionMeta(p.status, p.bed_number);
                const statusEl = document.getElementById('dossier-status');
                statusEl.innerHTML = `<i class="${dispMeta.icon} mr-1"></i> ${dispMeta.title}`;
                statusEl.className = `text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider border ${dispMeta.badgeCls}`;

                // Inpatient Bed Banner in sidebar
                const bedCard = document.getElementById('dossier-active-bed-card');
                if (p.bed_number && p.status && (p.status.includes('Admit') || p.status.includes('OPD') || p.status.includes('ICU'))) {
                    bedCard.classList.remove('hidden');
                    document.getElementById('dossier-bed-title').textContent = p.status.includes('ICU') ? 'ICU Critical Care' : 'General Ward';
                    document.getElementById('dossier-bed-number').textContent = `Hospital Bed #${p.bed_number}`;
                    document.getElementById('dossier-bed-dept').textContent = p.dept && p.dept !== '-' ? `Department: ${p.dept}` : 'Inpatient Care Active';
                } else {
                    bedCard.classList.add('hidden');
                }
                
                // Latest Visit Widget
                document.getElementById('dossier-last-date').textContent = p.date || '--';
                document.getElementById('dossier-last-dept').textContent = p.dept && p.dept !== '-' ? p.dept : 'General OPD';
                document.getElementById('dossier-last-doc').textContent = p.doctor && p.doctor !== '-' ? `Dr. ${p.doctor}` : 'Doctor Unassigned';
                document.getElementById('dossier-last-type').textContent = p.type && p.type !== '-' ? p.type : 'General Consultation';
                
                const latestDiag = (p.latest_diagnoses && p.latest_diagnoses.length > 0) ? p.latest_diagnoses[0] : (p.diagnoses && p.diagnoses.length > 0 ? p.diagnoses[0] : '');
                const diagEl = document.getElementById('dossier-last-diag');
                if (latestDiag) {
                    diagEl.textContent = latestDiag;
                    diagEl.className = "mt-0.5 text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100";
                } else {
                    diagEl.textContent = "No specific diagnosis recorded";
                    diagEl.className = "mt-0.5 text-xs text-slate-400 italic bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100";
                }

                const symptomsEl = document.getElementById('dossier-last-symptoms');
                symptomsEl.textContent = p.symptoms ? `"${p.symptoms}"` : 'No chief complaints recorded.';

                // Top Quick Stats
                const totalVisits = currentAppointments.length;
                let totalMeds = 0;
                currentAppointments.forEach(a => {
                    if (a.medicines && a.medicines.length) totalMeds += a.medicines.length;
                });
                const totalFiles = (p.files && p.files.length) ? p.files.length : 0;

                document.getElementById('stat-total-visits').textContent = totalVisits;
                document.getElementById('stat-total-medicines').textContent = totalMeds;
                document.getElementById('stat-total-files').textContent = totalFiles;
                document.getElementById('tab-count-appointments').textContent = totalVisits;
                document.getElementById('tab-count-files').textContent = totalFiles;

                // Render Appointments & Full Consultations
                renderAppointments(currentAppointments, p);

                // Render All Files
                renderAllFiles(p.files || []);

                switchDossierTab('appointments');
            } else {
                showToast('Error', data.message || 'Failed to load patient dossier.', 'error');
            }
        } catch (err) { 
            console.error(err); 
            showToast('Error', 'Unable to retrieve patient profile details.', 'error');
        }
    }

    // ================= RENDER APPOINTMENTS & FULL CONSULTATION WORKSTATION =================
    function renderAppointments(appointments, patient) {
        const apptsList = document.getElementById('dossier-appointments-list');
        if (!appointments || appointments.length === 0) {
            apptsList.innerHTML = `
                <div class="text-center py-16 px-4 bg-white rounded-3xl border border-dashed border-slate-200">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-900 mb-1">No Consultations Found</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">This patient has no registered appointment history or clinical consultations yet.</p>
                </div>
            `;
            return;
        }

        apptsList.innerHTML = appointments.map((appt, idx) => {
            const dateParts = appt.date ? appt.date.split(' ')[0].split('-') : [];
            const year = dateParts.length === 3 ? dateParts[0] : '--';
            const day = dateParts.length === 3 ? dateParts[2] : '--';
            const monthShort = appt.date ? getMonthShort(appt.date) : 'MTH';
            
            const dispMeta = getDispositionMeta(appt.status, appt.bed_number);
            const diagnoses = appt.diagnoses || [];
            const medicines = appt.medicines || [];
            const files = appt.files || [];
            const timeline = appt.timeline || [];
            const symptoms = appt.symptoms || '';
            const doctorNotes = appt.doctor_notes || '';
            const testsOrdered = appt.tests_ordered ? appt.tests_ordered.split(',').map(s => s.trim()).filter(Boolean) : [];

            // Auto-expand the latest/first consultation for immediate readability
            const isFirst = idx === 0;

            return `
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition duration-300">
                <!-- Accordion Card Header -->
                <div onclick="toggleAppointmentDetails(${idx})" class="w-full p-4 sm:p-6 bg-white hover:bg-slate-50/70 transition cursor-pointer group">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        
                        <div class="flex items-start sm:items-center gap-4">
                            <!-- Date Badge -->
                            <div class="flex flex-col items-center justify-center bg-indigo-50 text-indigo-700 w-16 h-16 rounded-2xl shrink-0 border border-indigo-100 relative shadow-sm">
                                <span class="text-xl font-black leading-none">${day}</span>
                                <span class="text-[9px] font-extrabold uppercase tracking-wider mt-0.5">${monthShort}</span>
                                <span class="absolute -bottom-2.5 bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-sm">${year}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold font-mono bg-slate-100 text-slate-700 border border-slate-200">
                                        ${appt.appointment_code || ('APP-' + String(appt.id).padStart(4, '0'))}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-stethoscope text-[9px]"></i> ${appt.type || 'General Consultation'}
                                    </span>
                                    ${appt.slot ? `<span class="text-[10px] font-bold text-slate-400 font-mono"><i class="fa-regular fa-clock mr-0.5"></i> ${appt.slot}</span>` : ''}
                                </div>

                                <h4 class="text-base sm:text-lg font-black text-slate-900 group-hover:text-indigo-600 transition truncate">
                                    Consultation with Dr. ${appt.doctor_name || 'Medical Officer'}
                                    <span class="text-xs font-semibold text-slate-500 font-normal">(${appt.dept || 'General Medicine'})</span>
                                </h4>

                                <!-- Symptoms Preview -->
                                ${symptoms ? `
                                    <p class="text-xs text-slate-500 mt-0.5 truncate flex items-center gap-1.5 font-medium">
                                        <i class="fa-solid fa-clipboard-question text-indigo-400"></i>
                                        <span><strong>Reason:</strong> ${escapeHtml(symptoms)}</span>
                                    </p>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Right: Disposition Pill, Print Rx Button, Toggle Icon -->
                        <div class="flex items-center justify-between sm:justify-end gap-2.5 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <!-- Disposition Badge -->
                            <span class="text-[11px] font-extrabold px-3 py-1.5 rounded-xl border flex items-center gap-1.5 shrink-0 ${dispMeta.badgeCls}">
                                <i class="${dispMeta.icon}"></i>
                                <span>${dispMeta.title}</span>
                            </span>

                            <!-- Print Prescription Button -->
                            <button type="button" onclick="event.stopPropagation(); printSpecificAppointmentPrescription(${idx})" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-indigo-50 hover:border-indigo-300 text-indigo-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm shrink-0" title="Print Prescription Slip">
                                <i class="fa-solid fa-print"></i>
                                <span class="hidden md:inline">Print Rx</span>
                            </button>

                            <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-indigo-100 text-slate-600 group-hover:text-indigo-700 flex items-center justify-center transition shrink-0">
                                <i id="icon-appt-${idx}" class="fa-solid fa-chevron-down transition-transform duration-300 ${isFirst ? 'rotate-180' : ''}"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= EXPANDED FULL CONSULTATION WORKSTATION ================= -->
                <div id="content-appt-${idx}" class="${isFirst ? '' : 'hidden'} border-t border-slate-100 bg-slate-50/40">
                    <div class="p-4 sm:p-6 sm:pt-4 space-y-6">

                        <!-- Disposition & Care State Alert Banner -->
                        <div class="p-3.5 sm:p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${dispMeta.badgeCls}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-lg shrink-0">
                                    <i class="${dispMeta.icon}"></i>
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-black text-slate-900">${dispMeta.title}</div>
                                    <p class="text-xs opacity-90 leading-tight mt-0.5">${dispMeta.desc}</p>
                                </div>
                            </div>
                            ${appt.bed_number ? `
                                <div class="px-3 py-1.5 rounded-xl bg-white shadow-sm text-xs font-black text-slate-900 flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                                    <i class="fa-solid fa-bed text-blue-600"></i>
                                    <span>Hospital Bed #${appt.bed_number}</span>
                                </div>
                            ` : ''}
                        </div>

                        <!-- 2-COLUMN CLINICAL WORKSTATION -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                            <!-- LEFT COLUMN (Span 7): Diagnoses & Prescriptions (Rx) -->
                            <div class="lg:col-span-7 space-y-5">
                                
                                <!-- STEP 1: Reason for Visit & Diagnoses -->
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-4">
                                    ${symptoms ? `
                                        <div>
                                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-clipboard-question text-indigo-600"></i>
                                                <span>Chief Complaints / Presenting Symptoms</span>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-semibold text-slate-800 leading-relaxed">
                                                ${escapeHtml(symptoms)}
                                            </div>
                                        </div>
                                    ` : ''}

                                    <div>
                                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                                            <i class="fa-solid fa-stethoscope text-rose-600"></i>
                                            <span>Clinical Diagnoses (Confirmed Conditions)</span>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            ${diagnoses.length > 0 ? diagnoses.map(d => `
                                                <span class="inline-flex items-center gap-2 bg-rose-50 text-rose-800 text-xs font-extrabold px-3 py-2 rounded-xl border border-rose-100 shadow-sm">
                                                    <i class="fa-solid fa-circle-check text-rose-500 text-[10px]"></i>
                                                    <span>${escapeHtml(d)}</span>
                                                </span>
                                            `).join('') : '<span class="text-xs text-slate-400 italic">No specific diagnoses recorded for this visit.</span>'}
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2: Prescriptions (Rx) & Medicine Administration -->
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3.5">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-pills"></i>
                                            </div>
                                            <div>
                                                <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider">Prescribed Medicines (Rx)</h5>
                                                <p class="text-[10px] text-slate-400">Clear instructions on when & how to take each medication</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ${medicines.length} ${medicines.length === 1 ? 'Drug' : 'Drugs'}
                                        </span>
                                    </div>

                                    <div class="space-y-3">
                                        ${medicines.length > 0 ? medicines.map((m, mIdx) => {
                                            const freqInfo = formatFrequency(m.freq);
                                            return `
                                            <div class="bg-slate-50/70 hover:bg-emerald-50/30 border border-slate-200 hover:border-emerald-200 rounded-2xl p-4 transition shadow-sm relative overflow-hidden group">
                                                <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
                                                
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pl-1 mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">${mIdx + 1}</span>
                                                        <span class="font-black text-slate-900 text-sm sm:text-base">${escapeHtml(m.name)}</span>
                                                    </div>
                                                    ${m.duration ? `
                                                        <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 self-start sm:self-auto flex items-center gap-1 shadow-xs">
                                                            <i class="fa-regular fa-calendar-days text-[11px]"></i>
                                                            <span>${escapeHtml(m.duration)}</span>
                                                        </span>
                                                    ` : ''}
                                                </div>

                                                <!-- Dosage & Frequency Badges -->
                                                <div class="flex flex-wrap items-center gap-2 pl-1 text-xs">
                                                    <span class="font-extrabold px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 shadow-xs flex items-center gap-1.5">
                                                        <i class="fa-solid fa-capsules text-emerald-600"></i>
                                                        <span>Dose: <strong>${escapeHtml(m.dose || '1 Unit')}</strong></span>
                                                    </span>

                                                    <span class="font-extrabold px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 shadow-xs flex items-center gap-1.5" title="${freqInfo.full}">
                                                        <i class="${freqInfo.icon}"></i>
                                                        <span>Timing: <strong>${escapeHtml(freqInfo.full)}</strong></span>
                                                    </span>
                                                </div>

                                                <!-- Instructions / Food Warning -->
                                                ${m.note ? `
                                                    <div class="mt-2.5 ml-1 p-2 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs font-medium flex items-center gap-2">
                                                        <i class="fa-solid fa-utensils text-amber-600 text-xs shrink-0"></i>
                                                        <span><strong>Instructions:</strong> ${escapeHtml(m.note)}</span>
                                                    </div>
                                                ` : ''}
                                            </div>
                                            `;
                                        }).join('') : '<div class="text-xs text-slate-400 italic py-4 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">No prescriptions recorded for this visit.</div>'}
                                    </div>
                                </div>

                                <!-- Ordered Diagnostic Tests Section (If Any) -->
                                ${testsOrdered.length > 0 ? `
                                    <div class="bg-amber-50/60 rounded-2xl p-4 sm:p-5 border border-amber-200 shadow-sm space-y-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-flask-vial"></i>
                                            </div>
                                            <div>
                                                <h5 class="text-xs font-black text-amber-950 uppercase tracking-wider">Ordered Diagnostic Investigations & Scans</h5>
                                                <p class="text-[10px] text-amber-700">Lab tests requested by doctor for this clinical evaluation</p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2 pt-1">
                                            ${testsOrdered.map(t => `
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-white text-amber-900 border border-amber-300 shadow-xs">
                                                    <i class="fa-solid fa-vial text-amber-600 text-[11px]"></i>
                                                    <span>${escapeHtml(t)}</span>
                                                </span>
                                            `).join('')}
                                        </div>
                                        <p class="text-[11px] text-amber-800 font-medium pt-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-info text-amber-600"></i>
                                            <span>Please visit the Pathology Laboratory or Radiology department for these tests.</span>
                                        </p>
                                    </div>
                                ` : ''}

                            </div>

                            <!-- RIGHT COLUMN (Span 5): Doctor Advice, Files & Timeline -->
                            <div class="lg:col-span-5 space-y-5">
                                
                                <!-- STEP 4: Doctor's Advice & Care Plan -->
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-2">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-user-doctor"></i>
                                        </div>
                                        <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider">Doctor's Advice & Instructions</h5>
                                    </div>
                                    <div class="p-3.5 rounded-xl bg-indigo-50/40 border border-indigo-100 text-xs sm:text-sm font-medium text-slate-800 leading-relaxed whitespace-pre-wrap">
                                        ${doctorNotes ? escapeHtml(doctorNotes) : '<span class="italic text-slate-400">Standard home care and rest advice given.</span>'}
                                    </div>
                                </div>

                                <!-- Diagnostic Reports & Medical Scans (Files attached to this visit) -->
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-images"></i>
                                            </div>
                                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider">Reports & Scans for this Visit</h5>
                                        </div>
                                        <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">${files.length}</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        ${files.length > 0 ? files.map(f => {
                                            const fileName = f.file_name || f.file_path || '';
                                            const ext = (fileName.includes('.') ? fileName.split('?')[0].split('.').pop() : '').toLowerCase();
                                            const isImg = (f.mime_type && f.mime_type.startsWith('image/')) || ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
                                            return `
                                                <div class="border border-slate-200 rounded-xl p-2.5 bg-slate-50/50 hover:border-teal-400 hover:bg-teal-50/20 transition flex flex-col justify-between group">
                                                    <div class="flex items-center gap-2.5 mb-2">
                                                        ${isImg ? `
                                                            <div onclick="openImagePreview('${f.file_path}', '${escapeJsStr(f.title)}')" class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden shrink-0 cursor-pointer shadow-xs">
                                                                <img src="${f.file_path}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                                            </div>
                                                        ` : `
                                                            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-500 border border-rose-100 flex items-center justify-center shrink-0 text-lg">
                                                                <i class="fa-solid fa-file-pdf"></i>
                                                            </div>
                                                        `}
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-xs font-extrabold text-slate-800 truncate" title="${escapeHtml(f.title)}">${escapeHtml(f.title)}</div>
                                                            <div class="text-[10px] text-slate-400 font-medium">${f.file_date || 'Attached'}</div>
                                                        </div>
                                                    </div>
                                                    <a href="${f.file_path}" target="_blank" class="w-full text-center py-1 rounded-lg bg-white border border-slate-200 hover:border-teal-500 hover:text-teal-700 text-[11px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                        <span>View Full Report</span>
                                                    </a>
                                                </div>
                                            `;
                                        }).join('') : '<div class="col-span-full text-xs text-slate-400 italic py-3 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">No medical scans or reports attached to this visit.</div>'}
                                    </div>
                                </div>

                                <!-- Activity Timeline for this Appointment -->
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-3">
                                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </div>
                                        <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider">Visit Activity Timeline</h5>
                                    </div>

                                    <div class="space-y-3 relative before:absolute before:inset-y-0 before:left-[11px] before:w-[2px] before:bg-slate-100 z-0 pl-1">
                                        ${timeline.length > 0 ? timeline.map(t => `
                                            <div class="flex items-start gap-3">
                                                <div class="w-4 h-4 rounded-full bg-white border-4 border-indigo-500 shrink-0 mt-0.5 shadow-xs"></div>
                                                <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 flex-1 text-xs">
                                                    <span class="text-[10px] text-indigo-600 font-extrabold block mb-0.5 font-mono">${escapeHtml(t.time || '')}</span>
                                                    <span class="text-xs font-semibold text-slate-700 leading-snug">${escapeHtml(t.event || '')}</span>
                                                </div>
                                            </div>
                                        `).join('') : '<span class="text-xs text-slate-400 italic pl-5">No activity events logged for this visit.</span>'}
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="pt-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-xs text-slate-500 font-medium">
                                Consultation ID: <strong class="font-mono text-slate-800">${appt.appointment_code || appt.id}</strong>
                            </div>
                            <button type="button" onclick="printSpecificAppointmentPrescription(${idx})" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-100">
                                <i class="fa-solid fa-print"></i>
                                <span>Print Official Prescription Slip</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    // ================= RENDER ALL FILES & SCANS =================
    function renderAllFiles(files) {
        const filesList = document.getElementById('dossier-files-list');
        if (!files || files.length === 0) {
            filesList.innerHTML = `
                <div class="col-span-full text-center py-20 px-4 bg-white rounded-3xl border border-dashed border-slate-200">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-500 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-900 mb-1">No Medical Scans or Reports</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">No lab reports, diagnostic scans, or clinical files have been uploaded for this patient yet.</p>
                </div>
            `;
            return;
        }

        filesList.innerHTML = files.map(f => {
            const fileName = f.file_name || f.file_path || '';
            const ext = (fileName.includes('.') ? fileName.split('?')[0].split('.').pop() : '').toLowerCase();
            const isImg = (f.mime_type && f.mime_type.startsWith('image/')) || ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
            return `
                <div class="border border-slate-200 rounded-3xl p-4 bg-white hover:border-indigo-400 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-2xl mb-3.5 flex items-center justify-center overflow-hidden border border-slate-100 group-hover:bg-indigo-50/30 transition">
                            ${isImg ? `
                                <img src="${f.file_path}" onclick="openImagePreview('${f.file_path}', '${escapeJsStr(f.title)}')" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer" alt="${escapeHtml(f.title)}">
                            ` : `
                                <div class="flex flex-col items-center gap-2 text-rose-500">
                                    <i class="fa-solid fa-file-pdf text-4xl group-hover:scale-110 transition duration-300"></i>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">PDF Report</span>
                                </div>
                            `}
                        </div>
                        <h5 class="text-xs sm:text-sm font-black text-slate-800 truncate mb-1" title="${escapeHtml(f.title)}">${escapeHtml(f.title)}</h5>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider font-mono">${f.file_date || 'Document'}</p>
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-100 flex items-center gap-2">
                        <a href="${f.file_path}" target="_blank" class="flex-1 text-center py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>Open</span>
                        </a>
                        <a href="${f.file_path}" download class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Download">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ================= TOGGLE ACCORDION =================
    function toggleAppointmentDetails(idx) {
        const content = document.getElementById(`content-appt-${idx}`);
        const icon = document.getElementById(`icon-appt-${idx}`);
        if (!content || !icon) return;
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // ================= PRINT PRESCRIPTION SLIP LOGIC =================
    function printLatestConsultationSlip() {
        if (!currentAppointments || currentAppointments.length === 0) {
            showToast('No Consultations', 'No appointments recorded for this patient to print.', 'info');
            return;
        }
        printSpecificAppointmentPrescription(0);
    }

    function printSpecificAppointmentPrescription(idx) {
        if (!currentAppointments[idx] || !currentPatientData) return;
        const appt = currentAppointments[idx];
        const p = currentPatientData;

        // Populate Printable Slip
        document.getElementById('print-rx-code').textContent = appt.appointment_code || `APP-${String(appt.id).padStart(4, '0')}`;
        document.getElementById('print-rx-date').textContent = appt.date || new Date().toISOString().substring(0, 10);
        
        const fullName = `${p.name || ''} ${p.surname || ''}`.trim() || 'Patient';
        document.getElementById('print-patient-name').textContent = fullName;
        document.getElementById('print-patient-mrn').textContent = p.id;
        
        let ageSex = `${p.age || '--'} / ${p.gender || '--'}`;
        document.getElementById('print-patient-age-sex').textContent = ageSex;
        document.getElementById('print-patient-blood').textContent = p.blood_group ? `🩸 ${p.blood_group}` : '--';
        document.getElementById('print-patient-father').textContent = p.father_name || '--';
        document.getElementById('print-patient-phone').textContent = p.phone || '--';
        
        document.getElementById('print-doctor-name').textContent = `Dr. ${appt.doctor_name || 'Medical Officer'}`;
        document.getElementById('print-doctor-dept').textContent = `Department of ${appt.dept || 'General Medicine'}`;
        document.getElementById('print-visit-type').textContent = appt.type || 'General Consultation';
        
        const dispMeta = getDispositionMeta(appt.status, appt.bed_number);
        document.getElementById('print-disposition-outcome').textContent = dispMeta.title;

        // Symptoms
        const symptomsSection = document.getElementById('print-symptoms-section');
        if (appt.symptoms) {
            symptomsSection.classList.remove('hidden');
            document.getElementById('print-symptoms-text').textContent = appt.symptoms;
        } else {
            symptomsSection.classList.add('hidden');
        }

        // Diagnoses
        const diagContainer = document.getElementById('print-diagnoses-list');
        const diags = appt.diagnoses || [];
        if (diags.length > 0) {
            diagContainer.innerHTML = diags.map(d => `
                <span class="inline-block bg-rose-50 border border-rose-200 text-rose-800 text-xs font-black px-3 py-1 rounded-lg">
                    • ${escapeHtml(d)}
                </span>
            `).join('');
        } else {
            diagContainer.innerHTML = '<span class="text-xs text-slate-400 italic">No specific diagnoses recorded</span>';
        }

        // Medicines Table
        const medsTbody = document.getElementById('print-medicines-tbody');
        const meds = appt.medicines || [];
        if (meds.length > 0) {
            medsTbody.innerHTML = meds.map((m, i) => {
                const freqInfo = formatFrequency(m.freq);
                return `
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 text-center font-bold text-slate-400">${i + 1}</td>
                        <td class="p-3 font-extrabold text-slate-900">${escapeHtml(m.name)}</td>
                        <td class="p-3 font-bold">${escapeHtml(m.dose || '1 Unit')}</td>
                        <td class="p-3 font-bold text-indigo-700">${escapeHtml(freqInfo.full)}</td>
                        <td class="p-3 font-bold">${escapeHtml(m.duration || 'As directed')}</td>
                        <td class="p-3 text-slate-600 italic">${escapeHtml(m.note || 'After meals')}</td>
                    </tr>
                `;
            }).join('');
        } else {
            medsTbody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-slate-400 italic">No medications prescribed for this visit.</td></tr>`;
        }

        // Diagnostic Tests Ordered
        const testsSection = document.getElementById('print-tests-section');
        const testsList = document.getElementById('print-tests-list');
        const tests = appt.tests_ordered ? appt.tests_ordered.split(',').map(s => s.trim()).filter(Boolean) : [];
        if (tests.length > 0) {
            testsSection.classList.remove('hidden');
            testsList.innerHTML = tests.map(t => `
                <span class="inline-block bg-white border border-amber-300 text-amber-900 text-xs font-black px-2.5 py-1 rounded-md">
                    🔬 ${escapeHtml(t)}
                </span>
            `).join('');
        } else {
            testsSection.classList.add('hidden');
        }

        // Advice
        document.getElementById('print-advice-text').textContent = appt.doctor_notes || 'Standard medical guidance provided. Take adequate oral hydration, proper rest, and follow healthy dietary practices.';

        // Inpatient Bed Section
        const bedSection = document.getElementById('print-bed-section');
        if (appt.bed_number) {
            bedSection.classList.remove('hidden');
            document.getElementById('print-bed-text').textContent = `Patient admitted to ${appt.status.includes('ICU') ? 'ICU Critical Care' : 'General Ward'} • Assigned Bed #${appt.bed_number}`;
        } else {
            bedSection.classList.add('hidden');
        }

        document.getElementById('print-sign-doctor').textContent = `Dr. ${appt.doctor_name || 'Medical Officer'}`;

        // Show Modal
        document.getElementById('modal-print-prescription').classList.remove('hidden');
    }

    function closePrintModal() {
        document.getElementById('modal-print-prescription').classList.add('hidden');
    }

    // ================= IMAGE PREVIEW =================
    function openImagePreview(src, title) {
        document.getElementById('image-preview-element').src = src;
        document.getElementById('image-preview-title').textContent = title || 'Medical Scan';
        document.getElementById('modal-image-preview').classList.remove('hidden');
    }

    function closeImagePreview() {
        document.getElementById('modal-image-preview').classList.add('hidden');
    }

    // ================= EDIT PROFILE =================
    function openEditProfile() {
        if (!currentPatientData) return;
        
        document.getElementById('ep-name').value = currentPatientData.name || '';
        document.getElementById('ep-surname').value = currentPatientData.surname || '';
        document.getElementById('ep-father').value = currentPatientData.father_name || '';
        document.getElementById('ep-phone').value = currentPatientData.phone || '';
        
        let age = currentPatientData.age || '', gender = currentPatientData.gender || '', blood = currentPatientData.blood_group || '';
        if (!age && currentPatientData.demographics) {
            const parts = currentPatientData.demographics.split(',').map(s => s.trim());
            parts.forEach(p => {
                if (p.endsWith('Y') || p.endsWith('y')) age = p.replace(/[^0-9]/g, '');
                if (p === 'Male' || p === 'Female' || p === 'Other') gender = p;
                if (p.includes('+') || p.includes('-')) blood = p;
            });
        }
        
        document.getElementById('ep-age').value = age;
        document.getElementById('ep-gender').value = gender;
        document.getElementById('ep-blood').value = blood;
        document.getElementById('ep-em-name').value = currentPatientData.emergency_contact_name || '';
        document.getElementById('ep-em-phone').value = currentPatientData.emergency_contact_phone || '';
        
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
                showToast('Profile Updated', 'Patient details have been updated successfully.');
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

    // Helper: Month short name
    function getMonthShort(dateString) {
        const d = new Date(dateString);
        if (isNaN(d)) return 'MTH';
        return d.toLocaleString('default', { month: 'short' });
    }

    // Helper: Escape HTML strings safely
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: Escape JS single quote string
    function escapeJsStr(str) {
        if (!str) return '';
        return String(str)
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '&quot;');
    }
</script>

<?php include 'includes/footer.php'; ?>

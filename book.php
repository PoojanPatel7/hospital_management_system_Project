<?php 
require_once 'auth.php'; 
require_once 'db.php';
include 'includes/header.php'; 

// Dynamic patient self-booking portal URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$patientBookUrl = $protocol . $host . $dir . '/patient_book.php';

$preset_doctor_id = $_GET['doctor_id'] ?? '';
$preset_date = $_GET['date'] ?? date('Y-m-d');
?>

<!-- ================= TOP HEADER ================= -->
<div class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
    <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Book Appointment</span>
        </h1>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium">Schedule a patient consultation with live doctor slots.</p>
    </div>
    
    <div class="flex items-center gap-2 w-full sm:w-auto">
        <!-- Quick Copy Patient Portal Link -->
        <button type="button" onclick="copyPatientPortalUrl()" class="flex-1 sm:flex-initial justify-center bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold py-2.5 px-3 sm:px-3.5 rounded-xl border border-emerald-200 text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer" title="Copy public link to send to patients">
            <i class="fa-solid fa-share-nodes text-emerald-600"></i> <span id="copy-portal-btn-text">Share Patient Link</span>
        </button>
        <a href="appointments.php" class="flex-1 sm:flex-initial justify-center bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-3.5 sm:px-4 rounded-xl border border-slate-200 text-xs sm:text-sm shadow-2xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-calendar-check text-purple-600"></i> <span>View Appointments</span>
        </a>
    </div>
</div>

<!-- ================= BOOKING WORKSPACE (2 COLUMNS ON PC) ================= -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start mb-8">
    
    <!-- LEFT: MAIN BOOKING FORM (lg:col-span-8) -->
    <div class="lg:col-span-8 space-y-5">
        
        <form id="booking-form" onsubmit="handleBookingSubmit(event)" class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl border-2 border-slate-200/90 shadow-sm space-y-5 sm:space-y-6">
            
            <!-- STEP 1: SELECT DOCTOR (CUSTOM DESIGNED DROPDOWN) -->
            <div class="relative" id="doctor-picker-component">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-purple-600 text-white text-[11px] font-black flex items-center justify-center">1</span>
                        <span>Select Doctor *</span>
                    </span>
                    <span id="doc-count-badge" class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-lg border border-purple-200">
                        Loading...
                    </span>
                </label>

                <!-- Hidden native select for form validation & fallback -->
                <select id="book-doctor" onchange="onDoctorOrDateChanged()" class="sr-only" required>
                    <option value="">-- Choose Doctor --</option>
                </select>

                <!-- Custom Dropdown Trigger Button -->
                <button 
                    type="button" 
                    id="doc-dropdown-trigger" 
                    onclick="toggleDoctorDropdown()" 
                    class="w-full min-h-[58px] sm:min-h-[64px] px-3.5 sm:px-4 py-2.5 bg-slate-50 hover:bg-white border-2 border-slate-200 hover:border-purple-300 focus:border-purple-600 focus:bg-white rounded-2xl transition-all flex items-center justify-between gap-3 text-left cursor-pointer shadow-2xs group"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                >
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <!-- Doctor Avatar / Icon -->
                        <div id="trigger-doc-avatar" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-black text-sm shrink-0 border border-purple-200 group-hover:scale-105 transition">
                            <i class="fa-solid fa-user-doctor text-base"></i>
                        </div>
                        
                        <!-- Doctor Details Text -->
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="trigger-doc-name" class="font-black text-sm sm:text-base text-slate-900 truncate">
                                    Choose Doctor
                                </span>
                                <span id="trigger-doc-cat" class="hidden sm:inline-flex text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-md truncate max-w-[180px]">
                                    Specialty
                                </span>
                            </div>
                            <p id="trigger-doc-sub" class="text-xs text-slate-500 font-medium truncate mt-0.5">
                                Click to select consulting specialist
                            </p>
                        </div>
                    </div>

                    <!-- Right Controls: Status & Chevron -->
                    <div class="flex items-center gap-2 shrink-0">
                        <span id="trigger-doc-status" class="hidden sm:inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Selected</span>
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-400 group-hover:text-purple-600 flex items-center justify-center transition" id="trigger-chevron-box">
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" id="trigger-chevron-icon"></i>
                        </div>
                    </div>
                </button>

                <!-- Custom Dropdown Menu Panel -->
                <div 
                    id="doc-dropdown-menu" 
                    class="hidden absolute left-0 right-0 top-full mt-2 bg-white border-2 border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden transition-all"
                    role="listbox"
                >
                    <!-- Search & Filter Bar inside Dropdown -->
                    <div class="p-3 bg-slate-50/80 border-b border-slate-200 space-y-2.5">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                type="text" 
                                id="doc-dropdown-search" 
                                oninput="filterDoctorDropdown(this.value)" 
                                placeholder="Search by doctor name or specialty..." 
                                class="w-full h-10 pl-9 pr-8 text-xs sm:text-sm font-semibold text-slate-900 bg-white border border-slate-200 rounded-xl focus:border-purple-600 outline-none transition"
                                autocomplete="off"
                            >
                            <button 
                                type="button" 
                                id="btn-clear-doc-search" 
                                onclick="clearDoctorSearch()" 
                                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Department Category Pills -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-[11px]" id="doc-category-pills">
                            <button type="button" onclick="filterDoctorsByDept('all')" class="doc-cat-pill px-2.5 py-1 rounded-lg font-bold bg-purple-600 text-white shrink-0 transition" data-dept="all">
                                All
                            </button>
                        </div>
                    </div>

                    <!-- Doctors List Scroll Area -->
                    <div id="doc-dropdown-list" class="max-h-72 overflow-y-auto custom-scrollbar p-2 space-y-1.5 divide-y-0">
                        <div class="p-4 text-center text-xs text-slate-400">
                            <i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-base mb-1"></i>
                            <p>Loading doctors...</p>
                        </div>
                    </div>

                    <!-- Bottom Quick Info Footer -->
                    <div class="px-3.5 py-2 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                        <span><i class="fa-solid fa-circle-info text-purple-500 mr-1"></i> Click any doctor to select</span>
                        <span id="doc-dropdown-footer-count">-- Doctors</span>
                    </div>
                </div>

            </div>

            <!-- STEP 2: CHOOSE DATE -->
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-purple-600 text-white text-[11px] font-black flex items-center justify-center">2</span>
                    <span>Consultation Date *</span>
                </label>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <div class="relative flex-1">
                        <input 
                            type="date" 
                            id="book-date" 
                            value="<?php echo htmlspecialchars($preset_date); ?>" 
                            onchange="onDoctorOrDateChanged()" 
                            class="w-full h-12 sm:h-14 px-3.5 sm:px-4 text-sm sm:text-base font-bold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-purple-600 focus:bg-white outline-none transition cursor-pointer shadow-2xs" required>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="setDate('today')" class="flex-1 sm:flex-initial h-12 sm:h-14 px-4 rounded-2xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-black text-xs sm:text-sm border-2 border-purple-200 transition cursor-pointer">
                            Today
                        </button>
                        <button type="button" onclick="setDate('tomorrow')" class="flex-1 sm:flex-initial h-12 sm:h-14 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs sm:text-sm border-2 border-slate-200 transition cursor-pointer">
                            Tomorrow
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PICK TIME SLOT -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-purple-600 text-white text-[11px] font-black flex items-center justify-center">3</span>
                        <span>Available Time Slot *</span>
                    </label>
                    <span id="slots-status-badge" class="text-xs font-black px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">
                        Select Doctor &amp; Date
                    </span>
                </div>

                <input type="hidden" id="selected-slot-input" required>

                <!-- Slots Container -->
                <div id="slots-container" class="p-3 sm:p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 min-h-[90px] flex flex-wrap gap-2 items-center">
                    <span class="text-xs font-bold text-slate-400 p-2">Select a doctor to load available time slots.</span>
                </div>
            </div>

            <!-- STEP 4: SELECT PATIENT -->
            <div class="pt-4 border-t-2 border-slate-100">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-purple-600 text-white text-[11px] font-black flex items-center justify-center">4</span>
                        <span>Patient Details *</span>
                    </label>
                    <button type="button" onclick="openNewPatientModal()" class="text-xs font-black text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition cursor-pointer flex items-center gap-1">
                        <i class="fa-solid fa-user-plus text-xs"></i> <span>+ New Patient</span>
                    </button>
                </div>

                <input type="hidden" id="selected-patient-id" required>

                <!-- Patient Search Box -->
                <div id="patient-search-wrapper" class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                    <input 
                        type="text" 
                        id="patient-search-input" 
                        oninput="onPatientSearch(this.value)" 
                        onfocus="onPatientSearch(this.value)"
                        placeholder="Search patient by name, phone, or MRN..." 
                        autocomplete="off"
                        class="w-full h-12 sm:h-14 pl-12 pr-10 text-sm sm:text-base font-bold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-purple-600 focus:bg-white outline-none transition shadow-2xs">
                    
                    <button type="button" id="btn-clear-patient-search" onclick="clearPatientSearch()" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>

                    <!-- Suggestions Dropdown -->
                    <div id="patient-suggestions" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border-2 border-slate-200 rounded-2xl shadow-2xl z-50 max-h-64 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Selected Patient Display Card -->
                <div id="selected-patient-card" class="hidden mt-2 p-3 sm:p-3.5 rounded-2xl bg-purple-50/70 border-2 border-purple-200 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-2xs" id="selected-patient-initials">
                            P
                        </div>
                        <div class="min-w-0">
                            <h4 id="selected-patient-name" class="font-black text-sm sm:text-base text-slate-900 truncate">Patient Name</h4>
                            <p id="selected-patient-meta" class="text-xs font-bold text-slate-500 font-mono truncate">CP-2026-001 • 9876543210</p>
                        </div>
                    </div>
                    <button type="button" onclick="clearSelectedPatient()" class="text-xs font-black text-rose-600 hover:text-rose-800 bg-white px-3 py-1.5 rounded-xl border border-rose-200 transition cursor-pointer shrink-0">
                        Change
                    </button>
                </div>
            </div>

            <!-- STEP 5: TYPE & SYMPTOMS (OPTIONAL) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-4 border-t-2 border-slate-100">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Consultation Type</label>
                    <select id="book-type" class="w-full h-11 sm:h-12 px-3.5 text-xs sm:text-sm font-bold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-purple-600 focus:bg-white outline-none cursor-pointer">
                        <option value="General Consultation">General Consultation</option>
                        <option value="Follow-up">Follow-up Visit</option>
                        <option value="Emergency Case">Emergency Case</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1.5">Reason / Symptoms (Optional)</label>
                    <input 
                        type="text" 
                        id="book-symptoms" 
                        placeholder="e.g. Fever, routine checkup, headache..." 
                        class="w-full h-11 sm:h-12 px-3.5 text-xs sm:text-sm font-bold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-purple-600 focus:bg-white outline-none">
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" id="btn-submit-booking" class="w-full h-13 sm:h-16 rounded-2xl bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-black text-base sm:text-lg shadow-xl shadow-purple-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-calendar-check text-lg"></i> <span>Confirm &amp; Book Appointment</span>
            </button>

        </form>

    </div>

    <!-- RIGHT: DOCTOR SUMMARY & DIRECT PORTAL LINK (lg:col-span-4) -->
    <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
        
        <!-- Doctor Live Schedule Summary Card -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl sm:rounded-3xl border-2 border-slate-200/90 shadow-sm space-y-4">
            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                <i class="fa-solid fa-stethoscope text-purple-600"></i> Doctor Schedule
            </h3>

            <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-black text-lg shrink-0" id="doc-card-initials">
                    DR
                </div>
                <div class="min-w-0">
                    <h4 id="doc-card-name" class="font-black text-base text-slate-900 truncate">Select Doctor</h4>
                    <p id="doc-card-specialties" class="text-xs font-bold text-purple-700 truncate">--</p>
                </div>
            </div>

            <div class="space-y-2 text-xs font-bold text-slate-700">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">Working Hours:</span>
                    <span id="doc-card-hours" class="font-black text-slate-900">--</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">Available Slots:</span>
                    <span id="doc-card-open-slots" class="font-black text-emerald-600">--</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">Booked Today:</span>
                    <span id="doc-card-booked-slots" class="font-black text-purple-700">--</span>
                </div>
            </div>
        </div>

        <!-- Shareable Patient Booking Portal Box -->
        <div class="bg-gradient-to-br from-purple-50 via-white to-indigo-50 p-5 rounded-2xl sm:rounded-3xl border-2 border-purple-200 shadow-sm space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-globe"></i>
                </span>
                <div>
                    <h4 class="font-black text-sm text-slate-900">Patient Booking Portal</h4>
                    <p class="text-[11px] font-bold text-slate-500">Allow patients to book online directly</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="copyPatientPortalUrl()" class="flex-1 py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-copy"></i> <span>Copy Portal Link</span>
                </button>
                <a href="<?php echo htmlspecialchars($patientBookUrl); ?>" target="_blank" class="py-2.5 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-black text-xs border border-slate-200 transition flex items-center justify-center gap-1" title="Open patient self-booking page in new tab">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>

    </div>

</div>

<!-- ================= QUICK REGISTER PATIENT MODAL ================= -->
<div id="modal-quick-patient" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
    <div class="bg-white max-w-md w-full p-5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-2xl border-2 border-slate-200 relative my-6 max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeNewPatientModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition cursor-pointer">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h4 class="font-black text-slate-900 text-base sm:text-lg">New Patient</h4>
                <p class="text-xs font-medium text-slate-500">Quickly register patient for appointment</p>
            </div>
        </div>

        <form id="quick-patient-form" onsubmit="handleQuickPatientSubmit(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-slate-600 mb-1">First Name *</label>
                    <input type="text" id="new-patient-name" required placeholder="First name" class="w-full h-11 px-3 text-sm font-bold border-2 border-slate-200 rounded-xl outline-none focus:border-purple-600">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-slate-600 mb-1">Surname</label>
                    <input type="text" id="new-patient-surname" placeholder="Surname" class="w-full h-11 px-3 text-sm font-bold border-2 border-slate-200 rounded-xl outline-none focus:border-purple-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-slate-600 mb-1">Phone Number *</label>
                <input type="tel" id="new-patient-phone" required placeholder="e.g. 9876543210" class="w-full h-11 px-3 text-sm font-bold border-2 border-slate-200 rounded-xl outline-none focus:border-purple-600 font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-slate-600 mb-1">Gender</label>
                    <select id="new-patient-gender" class="w-full h-11 px-3 text-sm font-bold border-2 border-slate-200 rounded-xl outline-none focus:border-purple-600 bg-white">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-slate-600 mb-1">Age</label>
                    <input type="number" id="new-patient-age" placeholder="e.g. 32" class="w-full h-11 px-3 text-sm font-bold border-2 border-slate-200 rounded-xl outline-none focus:border-purple-600">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeNewPatientModal()" class="px-4 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-save-new-patient" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-md cursor-pointer">
                    Save &amp; Select Patient
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    let allDoctors = [];
    let allPatients = [];
    let selectedPatient = null;
    let selectedSlot = '';
    let currentDeptFilter = 'all';

    const presetDocId = "<?php echo htmlspecialchars($preset_doctor_id); ?>";
    const patientPortalUrl = "<?php echo htmlspecialchars($patientBookUrl); ?>";

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctorsList();
        loadAllPatients();

        // Close search suggestions & doctor dropdown on click outside
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('patient-search-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                const sugg = document.getElementById('patient-suggestions');
                if (sugg) sugg.classList.add('hidden');
            }

            const docPicker = document.getElementById('doctor-picker-component');
            if (docPicker && !docPicker.contains(e.target)) {
                closeDoctorDropdown();
            }
        });

        // Close dropdowns on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDoctorDropdown();
                const sugg = document.getElementById('patient-suggestions');
                if (sugg) sugg.classList.add('hidden');
            }
        });
    });

    // ================= 1. DOCTOR & DATE =================
    async function loadDoctorsList() {
        try {
            const res = await fetch('api/doctors.php?action=get_hospital_doctors');
            const data = await res.json();
            if (data.status === 'success') {
                allDoctors = data.doctors || [];

                // Update count badges
                const countBadge = document.getElementById('doc-count-badge');
                if (countBadge) countBadge.textContent = `${allDoctors.length} Doctors Available`;
                const footerCount = document.getElementById('doc-dropdown-footer-count');
                if (footerCount) footerCount.textContent = `${allDoctors.length} Doctors`;

                // Populate backup hidden select
                const select = document.getElementById('book-doctor');
                select.innerHTML = '<option value="">-- Choose Doctor --</option>';

                const depts = new Set();
                allDoctors.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id;
                    const cats = (doc.categories || []).join(', ') || 'General';
                    opt.textContent = `${doc.name} (${cats})`;
                    select.appendChild(opt);

                    (doc.categories || []).forEach(c => {
                        if (c) depts.add(c.trim());
                    });
                });

                // Populate Category Filter Pills
                const pillsContainer = document.getElementById('doc-category-pills');
                if (pillsContainer) {
                    pillsContainer.innerHTML = `
                        <button type="button" onclick="filterDoctorsByDept('all')" class="doc-cat-pill px-2.5 py-1 rounded-lg font-bold bg-purple-600 text-white shrink-0 transition cursor-pointer" data-dept="all">
                            All (${allDoctors.length})
                        </button>
                    `;
                    depts.forEach(dept => {
                        const count = allDoctors.filter(d => (d.categories || []).includes(dept)).length;
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'doc-cat-pill px-2.5 py-1 rounded-lg font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shrink-0 transition cursor-pointer';
                        btn.dataset.dept = dept;
                        btn.textContent = `${dept} (${count})`;
                        btn.onclick = () => filterDoctorsByDept(dept);
                        pillsContainer.appendChild(btn);
                    });
                }

                // Initial selection
                let selectedId = '';
                if (presetDocId && allDoctors.some(d => d.id === presetDocId)) {
                    selectedId = presetDocId;
                } else if (allDoctors.length > 0) {
                    selectedId = allDoctors[0].id;
                }

                if (selectedId) {
                    selectDoctorFromDropdown(selectedId);
                } else {
                    renderDoctorDropdownOptions(allDoctors);
                }
            }
        } catch (e) {
            console.error('Failed to load doctors list', e);
        }
    }

    // ================= CUSTOM DOCTOR DROPDOWN HANDLERS =================
    function toggleDoctorDropdown() {
        const menu = document.getElementById('doc-dropdown-menu');
        if (!menu) return;

        if (menu.classList.contains('hidden')) {
            openDoctorDropdown();
        } else {
            closeDoctorDropdown();
        }
    }

    function openDoctorDropdown() {
        const menu = document.getElementById('doc-dropdown-menu');
        const trigger = document.getElementById('doc-dropdown-trigger');
        const chevron = document.getElementById('trigger-chevron-icon');

        if (!menu) return;
        menu.classList.remove('hidden');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.add('border-purple-600', 'bg-white', 'ring-4', 'ring-purple-100');
        }
        if (chevron) chevron.classList.add('rotate-180');

        const searchInput = document.getElementById('doc-dropdown-search');
        if (searchInput) {
            searchInput.focus();
        }

        renderDoctorDropdownOptions(getFilteredDoctors());
    }

    function closeDoctorDropdown() {
        const menu = document.getElementById('doc-dropdown-menu');
        const trigger = document.getElementById('doc-dropdown-trigger');
        const chevron = document.getElementById('trigger-chevron-icon');

        if (!menu) return;
        menu.classList.add('hidden');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
            trigger.classList.remove('border-purple-600', 'bg-white', 'ring-4', 'ring-purple-100');
        }
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function getFilteredDoctors() {
        const query = (document.getElementById('doc-dropdown-search')?.value || '').toLowerCase().trim();
        return allDoctors.filter(doc => {
            const matchesDept = (currentDeptFilter === 'all') || (doc.categories || []).includes(currentDeptFilter);
            if (!matchesDept) return false;
            if (!query) return true;

            const name = (doc.name || '').toLowerCase();
            const cats = (doc.categories || []).join(' ').toLowerCase();
            const phone = (doc.phone || '').toLowerCase();
            return name.includes(query) || cats.includes(query) || phone.includes(query);
        });
    }

    function filterDoctorDropdown(query) {
        const clearBtn = document.getElementById('btn-clear-doc-search');
        if (clearBtn) clearBtn.classList.toggle('hidden', !query);
        renderDoctorDropdownOptions(getFilteredDoctors());
    }

    function clearDoctorSearch() {
        const searchInput = document.getElementById('doc-dropdown-search');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        filterDoctorDropdown('');
    }

    function filterDoctorsByDept(deptName) {
        currentDeptFilter = deptName;
        document.querySelectorAll('.doc-cat-pill').forEach(btn => {
            if (btn.dataset.dept === deptName) {
                btn.className = 'doc-cat-pill px-2.5 py-1 rounded-lg font-bold bg-purple-600 text-white shrink-0 transition cursor-pointer';
            } else {
                btn.className = 'doc-cat-pill px-2.5 py-1 rounded-lg font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shrink-0 transition cursor-pointer';
            }
        });
        renderDoctorDropdownOptions(getFilteredDoctors());
    }

    function renderDoctorDropdownOptions(doctorsToRender) {
        const listEl = document.getElementById('doc-dropdown-list');
        if (!listEl) return;

        const currentSelectedId = document.getElementById('book-doctor').value;

        if (doctorsToRender.length === 0) {
            listEl.innerHTML = `
                <div class="py-6 px-4 text-center text-xs font-bold text-slate-400">
                    <i class="fa-solid fa-user-doctor text-2xl text-slate-300 mb-2 block"></i>
                    <span>No doctors found matching your search.</span>
                </div>
            `;
            return;
        }

        listEl.innerHTML = doctorsToRender.map(doc => {
            const isSelected = (doc.id === currentSelectedId);
            const initials = doc.name.replace(/^(Dr\.?|Doctor)\s*/i, '').trim().split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'DR';
            const categories = doc.categories || [];
            const catBadges = categories.length > 0 
                ? categories.map(c => `<span class="inline-block text-[10px] font-semibold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-md border border-purple-100">${c}</span>`).join(' ')
                : `<span class="inline-block text-[10px] font-medium text-slate-400">General OPD</span>`;

            return `
                <div 
                    onclick="selectDoctorFromDropdown('${doc.id}')"
                    class="p-2.5 sm:p-3 rounded-xl transition-all cursor-pointer flex items-center justify-between gap-3 ${isSelected ? 'bg-purple-100/70 border-2 border-purple-500 shadow-2xs' : 'hover:bg-slate-50 border-2 border-transparent'}"
                    role="option"
                    aria-selected="${isSelected ? 'true' : 'false'}"
                >
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <!-- Avatar -->
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl ${isSelected ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-700'} flex items-center justify-center font-black text-xs shrink-0">
                            ${initials}
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <h4 class="font-black text-xs sm:text-sm text-slate-900 truncate leading-tight">${doc.name}</h4>
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                ${catBadges}
                                ${doc.phone ? `<span class="text-[10px] font-mono text-slate-400 hidden sm:inline">• 📞 ${doc.phone}</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Selected Indicator -->
                    <div class="shrink-0 flex items-center">
                        ${isSelected ? `
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-purple-600 text-white text-xs flex items-center justify-center shadow-2xs">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        ` : `
                            <span class="text-[11px] font-bold text-slate-400 hover:text-purple-600 px-2 py-1 rounded-lg bg-slate-100 hover:bg-purple-50 transition">
                                Select
                            </span>
                        `}
                    </div>
                </div>
            `;
        }).join('');
    }

    function selectDoctorFromDropdown(docId) {
        const select = document.getElementById('book-doctor');
        select.value = docId;

        const doc = allDoctors.find(d => d.id === docId);
        if (doc) {
            const initials = doc.name.replace(/^(Dr\.?|Doctor)\s*/i, '').trim().split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'DR';
            document.getElementById('trigger-doc-avatar').innerHTML = `<span class="font-black">${initials}</span>`;
            document.getElementById('trigger-doc-name').textContent = doc.name;
            
            const cats = (doc.categories || []).join(', ') || 'General OPD';
            const catBadge = document.getElementById('trigger-doc-cat');
            if (catBadge) {
                catBadge.textContent = cats;
                catBadge.classList.remove('hidden');
            }
            document.getElementById('trigger-doc-sub').textContent = 'Click to switch doctor';
            document.getElementById('trigger-doc-status').classList.remove('hidden');
        }

        closeDoctorDropdown();
        onDoctorOrDateChanged();

        renderDoctorDropdownOptions(getFilteredDoctors());
    }

    function setDate(type) {
        const input = document.getElementById('book-date');
        const now = new Date();
        if (type === 'today') {
            input.value = now.toISOString().substring(0, 10);
        } else if (type === 'tomorrow') {
            now.setDate(now.getDate() + 1);
            input.value = now.toISOString().substring(0, 10);
        }
        onDoctorOrDateChanged();
    }

    async function onDoctorOrDateChanged() {
        const docId = document.getElementById('book-doctor').value;
        const date = document.getElementById('book-date').value;

        updateDoctorSummaryCard(docId);

        if (!docId || !date) {
            document.getElementById('slots-container').innerHTML = '<span class="text-xs font-bold text-slate-400 p-2">Select a doctor to load available time slots.</span>';
            document.getElementById('slots-status-badge').textContent = 'Select Doctor';
            document.getElementById('slots-status-badge').className = 'text-xs font-black px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600';
            return;
        }

        await loadAvailableSlots(docId, date);
    }

    function updateDoctorSummaryCard(docId) {
        const doc = allDoctors.find(d => d.id === docId);
        if (!doc) {
            document.getElementById('doc-card-name').textContent = 'Select Doctor';
            document.getElementById('doc-card-specialties').textContent = '--';
            document.getElementById('doc-card-hours').textContent = '--';
            document.getElementById('doc-card-open-slots').textContent = '--';
            document.getElementById('doc-card-booked-slots').textContent = '--';
            return;
        }

        document.getElementById('doc-card-name').textContent = doc.name;
        document.getElementById('doc-card-specialties').textContent = (doc.categories || []).join(', ') || 'General Medicine';

        const initials = doc.name.replace(/^(Dr\.?|Doctor)\s*/i, '').trim().split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'DR';
        document.getElementById('doc-card-initials').textContent = initials;
    }

    // ================= 2. LIVE SLOTS =================
    async function loadAvailableSlots(docId, date) {
        const container = document.getElementById('slots-container');
        const badge = document.getElementById('slots-status-badge');

        container.innerHTML = '<span class="text-xs font-bold text-slate-400 p-2"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading slots...</span>';

        try {
            const res = await fetch(`api/booking.php?action=get_doctor_slots&doctor_id=${encodeURIComponent(docId)}&date=${encodeURIComponent(date)}`);
            const data = await res.json();

            if (data.status === 'success') {
                if (!data.is_available) {
                    badge.textContent = 'Day Off (Closed)';
                    badge.className = 'text-xs font-black px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800';
                    container.innerHTML = `<span class="text-xs font-black text-amber-800 p-3 bg-amber-50 rounded-xl border border-amber-200 w-full text-center">Doctor is not available on ${data.day_of_week}s. Please choose another date.</span>`;
                    document.getElementById('doc-card-hours').textContent = 'Day Off';
                    document.getElementById('doc-card-open-slots').textContent = '0 Open';
                    document.getElementById('doc-card-booked-slots').textContent = '0 Booked';
                    return;
                }

                // Update Doctor Info
                document.getElementById('doc-card-hours').textContent = `${data.start_time} - ${data.end_time}`;
                document.getElementById('doc-card-open-slots').textContent = `${data.available_count} Open`;
                document.getElementById('doc-card-booked-slots').textContent = `${data.booked_count} Booked`;

                badge.textContent = `${data.available_count} Open Slots`;
                badge.className = 'text-xs font-black px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800';

                const slots = data.slots || [];
                if (slots.length === 0) {
                    container.innerHTML = '<span class="text-xs font-bold text-slate-400 p-2">No slots configured for this day.</span>';
                    return;
                }

                container.innerHTML = '';
                selectedSlot = '';
                document.getElementById('selected-slot-input').value = '';

                slots.forEach(s => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    
                    if (s.available) {
                        btn.className = 'slot-btn py-2.5 px-3.5 rounded-xl border-2 border-slate-200 bg-white hover:border-purple-500 font-mono text-sm font-black text-slate-800 transition cursor-pointer shadow-2xs';
                        btn.textContent = s.slot;
                        btn.onclick = () => selectSlot(s.slot, btn);
                    } else {
                        btn.className = 'py-2.5 px-3.5 rounded-xl border-2 border-slate-200 bg-slate-100 font-mono text-sm font-bold text-slate-400 line-through cursor-not-allowed opacity-60';
                        btn.textContent = s.slot;
                        btn.disabled = true;
                        btn.title = 'Already booked';
                    }
                    container.appendChild(btn);
                });

                // Auto-select first available slot
                const firstAvail = slots.find(s => s.available);
                if (firstAvail) {
                    const firstBtn = container.querySelector('.slot-btn');
                    if (firstBtn) selectSlot(firstAvail.slot, firstBtn);
                }

            } else {
                container.innerHTML = `<span class="text-xs font-bold text-rose-500 p-2">${data.message || 'Error loading slots'}</span>`;
            }
        } catch (e) {
            console.error(e);
            container.innerHTML = '<span class="text-xs font-bold text-rose-500 p-2">Error connecting to server.</span>';
        }
    }

    function selectSlot(slotTime, btnEl) {
        selectedSlot = slotTime;
        document.getElementById('selected-slot-input').value = slotTime;

        document.querySelectorAll('.slot-btn').forEach(b => {
            b.className = 'slot-btn py-2.5 px-3.5 rounded-xl border-2 border-slate-200 bg-white hover:border-purple-500 font-mono text-sm font-black text-slate-800 transition cursor-pointer shadow-2xs';
        });

        if (btnEl) {
            btnEl.className = 'slot-btn py-2.5 px-3.5 rounded-xl border-2 border-purple-600 bg-purple-600 text-white font-mono text-sm font-black shadow-md transition cursor-pointer';
        }
    }

    // ================= 3. PATIENTS =================
    async function loadAllPatients() {
        try {
            const res = await fetch('api/patients.php?action=get_all');
            const data = await res.json();
            if (data.status === 'success') {
                allPatients = data.patients || [];
            }
        } catch (e) {
            console.error(e);
        }
    }

    function onPatientSearch(query) {
        query = (query || '').toLowerCase().trim();
        const suggestions = document.getElementById('patient-suggestions');
        const clearBtn = document.getElementById('btn-clear-patient-search');

        clearBtn.classList.toggle('hidden', query.length === 0);

        if (query.length === 0) {
            suggestions.classList.add('hidden');
            return;
        }

        const matches = allPatients.filter(p => {
            const name = `${p.name || ''} ${p.surname || ''}`.toLowerCase();
            const phone = (p.phone || '').toLowerCase();
            const id = (p.id || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || id.includes(query);
        }).slice(0, 8);

        if (matches.length === 0) {
            suggestions.innerHTML = `
                <div class="p-3.5 text-center text-xs font-bold text-slate-400">
                    No patients match "${query}". 
                    <button type="button" onclick="openNewPatientModal()" class="text-purple-700 underline font-black ml-1">Create New Patient</button>
                </div>
            `;
            suggestions.classList.remove('hidden');
            return;
        }

        suggestions.innerHTML = matches.map(p => `
            <div onclick="selectPatient('${p.id}')" class="p-3 hover:bg-purple-50 transition cursor-pointer flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="block font-black text-sm text-slate-900 truncate">${p.name} ${p.surname || ''}</span>
                    <span class="block text-xs font-bold text-slate-500 font-mono truncate">${p.id} • 📞 ${p.phone || 'No phone'}</span>
                </div>
                <span class="text-xs font-black text-purple-700 bg-purple-100 px-2 py-0.5 rounded-lg shrink-0">Select</span>
            </div>
        `).join('');

        suggestions.classList.remove('hidden');
    }

    function selectPatient(patientId) {
        const p = allPatients.find(x => x.id === patientId);
        if (!p) return;

        selectedPatient = p;
        document.getElementById('selected-patient-id').value = p.id;
        document.getElementById('selected-patient-name').textContent = `${p.name} ${p.surname || ''}`;
        document.getElementById('selected-patient-meta').textContent = `${p.id} • 📞 ${p.phone || 'N/A'}`;

        const initials = ((p.name ? p.name[0] : '') + (p.surname ? p.surname[0] : '')).toUpperCase() || 'P';
        document.getElementById('selected-patient-initials').textContent = initials;

        document.getElementById('patient-search-wrapper').classList.add('hidden');
        document.getElementById('selected-patient-card').classList.remove('hidden');
        document.getElementById('patient-suggestions').classList.add('hidden');
    }

    function clearSelectedPatient() {
        selectedPatient = null;
        document.getElementById('selected-patient-id').value = '';
        document.getElementById('patient-search-input').value = '';
        document.getElementById('patient-search-wrapper').classList.remove('hidden');
        document.getElementById('selected-patient-card').classList.add('hidden');
        document.getElementById('patient-search-input').focus();
    }

    function clearPatientSearch() {
        document.getElementById('patient-search-input').value = '';
        document.getElementById('btn-clear-patient-search').classList.add('hidden');
        document.getElementById('patient-suggestions').classList.add('hidden');
    }

    // ================= 4. QUICK NEW PATIENT MODAL =================
    function openNewPatientModal() {
        document.getElementById('quick-patient-form').reset();
        document.getElementById('modal-quick-patient').classList.remove('hidden');
        document.getElementById('new-patient-name').focus();
    }

    function closeNewPatientModal() {
        document.getElementById('modal-quick-patient').classList.add('hidden');
    }

    async function handleQuickPatientSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-new-patient');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';

        const payload = {
            name: document.getElementById('new-patient-name').value.trim(),
            surname: document.getElementById('new-patient-surname').value.trim(),
            phone: document.getElementById('new-patient-phone').value.trim(),
            gender: document.getElementById('new-patient-gender').value,
            age: document.getElementById('new-patient-age').value.trim()
        };

        try {
            const res = await fetch('api/patients.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.status === 'success') {
                const newId = data.id || ('CP-' + Date.now());
                const newP = { ...payload, id: newId };
                allPatients.unshift(newP);

                closeNewPatientModal();
                selectPatient(newId);
                showToast('Patient Registered', `${payload.name} created and selected!`, 'success');
            } else {
                showToast('Error', data.message || 'Could not register patient', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Connection failed', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save & Select Patient';
        }
    }

    // ================= 5. BOOKING SUBMISSION =================
    async function handleBookingSubmit(e) {
        e.preventDefault();

        const doctorId = document.getElementById('book-doctor').value;
        const date = document.getElementById('book-date').value;
        const slot = document.getElementById('selected-slot-input').value;
        const patientId = document.getElementById('selected-patient-id').value;
        const type = document.getElementById('book-type').value;
        const symptoms = document.getElementById('book-symptoms').value.trim();

        if (!doctorId) {
            showToast('Doctor Required', 'Please select a doctor.', 'error');
            return;
        }
        if (!date) {
            showToast('Date Required', 'Please choose an appointment date.', 'error');
            return;
        }
        if (!slot) {
            showToast('Time Slot Required', 'Please pick an open time slot.', 'error');
            return;
        }
        if (!patientId) {
            showToast('Patient Required', 'Please search and select a patient.', 'error');
            document.getElementById('patient-search-input').focus();
            return;
        }

        const submitBtn = document.getElementById('btn-submit-booking');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Booking Consultation...';

        try {
            const payload = {
                patient_id: patientId,
                doctor_id: doctorId,
                date: date,
                slot: slot,
                type: type,
                symptoms: symptoms
            };

            const res = await fetch('api/queue.php?action=book_existing', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.status === 'success') {
                showToast('Success!', 'Appointment booked successfully!', 'success');
                setTimeout(() => {
                    window.location.href = `appointments.php?date=${encodeURIComponent(date)}&doctor_id=${encodeURIComponent(doctorId)}`;
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to book appointment', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check text-lg"></i> <span>Confirm & Book Appointment</span>';
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Server connection error', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check text-lg"></i> <span>Confirm & Book Appointment</span>';
        }
    }

    // ================= 6. COPY PORTAL URL =================
    function copyPatientPortalUrl() {
        navigator.clipboard.writeText(patientPortalUrl).then(() => {
            const textEl = document.getElementById('copy-portal-btn-text');
            if (textEl) textEl.textContent = 'Copied! ✓';
            showToast('Link Copied', 'Patient Self-Booking link copied to clipboard.', 'success');
            setTimeout(() => {
                if (textEl) textEl.textContent = 'Share Patient Link';
            }, 3000);
        }).catch(() => {
            showToast('Link Copied', 'Patient Self-Booking link copied to clipboard.', 'success');
        });
    }
</script>

<?php include 'includes/footer.php'; ?>

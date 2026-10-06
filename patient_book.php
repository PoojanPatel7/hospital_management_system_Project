<?php 
require_once 'auth.php'; 
require_once 'db.php';

$isAdmin = isset($_SESSION['hospital_id']) && !empty($_SESSION['hospital_id']);
$hospital_id = $_SESSION['hospital_id'] ?? 1;
$hospital_full_name = 'Bhooma Medicare Hospital & I.C.U';

// Base64 encode logo for instant, 100% reliable html2canvas capture without CORS taint
$logoPath = __DIR__ . '/images/Logo.png';
$logoDataUri = '';
if (file_exists($logoPath)) {
    $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
} else {
    $logoDataUri = 'images/Logo.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Online Appointment Booking | <?php echo htmlspecialchars($hospital_full_name); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- html2canvas for High-Definition Appointment Image Download -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script>
    if (typeof html2canvas === 'undefined') {
      document.write('<script src="js/html2canvas.min.js"><\/script>');
    }
  </script>

  <style>
    body { 
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
      background-color: #f8fafc; 
    }
    .solemn-card {
      background: #ffffff;
      border-radius: 20px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
      transition: all 0.2s ease-in-out;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

    /* Anti-clipping rules for html2canvas capture */
    #printable-slip, #printable-slip * {
      box-sizing: border-box !important;
      overflow: visible !important;
    }
    #printable-slip {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
    }
    #printable-slip .field-title {
      font-size: 10px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.05em !important;
      color: #64748b !important;
      font-weight: 700 !important;
      line-height: 1.4 !important;
      margin-bottom: 3px !important;
      display: block !important;
      overflow: visible !important;
      white-space: normal !important;
    }
    #printable-slip .field-val {
      font-size: 13px !important;
      font-weight: 700 !important;
      color: #0f172a !important;
      line-height: 1.6 !important;
      display: block !important;
      word-break: break-word !important;
      overflow: visible !important;
      white-space: normal !important;
      min-height: 22px !important;
      padding-bottom: 4px !important;
    }

    @media print {
      body * { visibility: hidden; }
      #confirmation-modal, #confirmation-modal * { visibility: visible; }
      #confirmation-modal { position: absolute; left: 0; top: 0; width: 100%; height: auto; background: none; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body class="text-slate-800 min-h-screen bg-slate-50 flex flex-col justify-between">

  <!-- ================= TOP HEADER (HOSPITAL LOGO ONLY) ================= -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
      
      <!-- Hospital Logo ONLY in top navbar -->
      <a href="patient_book.php" class="flex items-center hover:opacity-95 transition" title="Patient Online Booking">
        <img src="<?php echo $logoDataUri; ?>" alt="Hospital Logo" class="h-11 sm:h-13 w-auto object-contain max-w-[220px] sm:max-w-[260px]" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="hidden items-center gap-2 font-bold text-slate-800 text-lg">
          <i class="fa-solid fa-hospital text-indigo-600"></i>
          <span>Hospital Portal</span>
        </div>
      </a>

      <!-- Right Action / Helpline -->
      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 bg-emerald-50 border border-emerald-200/80 px-3.5 py-1.5 rounded-full text-emerald-800 text-xs font-semibold">
          <i class="fa-solid fa-phone-volume text-emerald-600"></i>
          <span>Helpline: +91 98201 12345</span>
        </div>
        
        <?php if ($isAdmin): ?>
        <a href="book.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 px-3.5 py-2 rounded-xl transition">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Admin Desk</span>
        </a>
        <?php else: ?>
        <a href="login.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition">
          <i class="fa-solid fa-lock text-xs"></i>
          <span>Staff Login</span>
        </a>
        <?php endif; ?>
      </div>

    </div>
  </header>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-8 flex-1">

    <!-- Hero / Introduction -->
    <div class="mb-8 text-center max-w-2xl mx-auto">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-3">
        <i class="fa-solid fa-calendar-check text-indigo-600"></i> Easy 4-Step Patient Booking
      </span>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
        Schedule Your Doctor Consultation
      </h1>
      <p class="text-slate-600 text-xs sm:text-sm mt-2 font-normal leading-relaxed">
        Enter your mobile number first. If you are already registered in our system, your details will be recognized automatically with no need to fill the form again!
      </p>
    </div>

    <!-- ================= STEPPER PROGRESS BAR (4 STEPS) ================= -->
    <div class="mb-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">
        
        <!-- Step 1 Tab -->
        <button type="button" onclick="goToStep(1)" id="step-tab-1" class="step-tab p-3.5 rounded-2xl bg-indigo-600 text-white transition-all flex items-center gap-3 shadow-sm text-left cursor-pointer">
          <div id="step-tab-num-1" class="w-8 h-8 rounded-xl bg-white/25 text-white font-bold text-xs flex items-center justify-center shrink-0">
            1
          </div>
          <div class="min-w-0">
            <span class="text-[10px] uppercase font-bold text-indigo-200 block tracking-wider">Step 1</span>
            <span class="text-xs sm:text-sm font-bold block truncate">Mobile &amp; Patient</span>
          </div>
        </button>

        <!-- Step 2 Tab -->
        <button type="button" onclick="goToStep(2)" id="step-tab-2" class="step-tab p-3.5 rounded-2xl bg-white text-slate-400 border border-slate-200 transition-all flex items-center gap-3 text-left cursor-not-allowed">
          <div id="step-tab-num-2" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center shrink-0">
            2
          </div>
          <div class="min-w-0">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Step 2</span>
            <span class="text-xs sm:text-sm font-bold text-slate-600 block truncate">Select Doctor</span>
          </div>
        </button>

        <!-- Step 3 Tab -->
        <button type="button" onclick="goToStep(3)" id="step-tab-3" class="step-tab p-3.5 rounded-2xl bg-white text-slate-400 border border-slate-200 transition-all flex items-center gap-3 text-left cursor-not-allowed">
          <div id="step-tab-num-3" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center shrink-0">
            3
          </div>
          <div class="min-w-0">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Step 3</span>
            <span class="text-xs sm:text-sm font-bold text-slate-600 block truncate">Date &amp; Time Slot</span>
          </div>
        </button>

        <!-- Step 4 Tab -->
        <button type="button" onclick="goToStep(4)" id="step-tab-4" class="step-tab p-3.5 rounded-2xl bg-white text-slate-400 border border-slate-200 transition-all flex items-center gap-3 text-left cursor-not-allowed">
          <div id="step-tab-num-4" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center shrink-0">
            4
          </div>
          <div class="min-w-0">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Step 4</span>
            <span class="text-xs sm:text-sm font-bold text-slate-600 block truncate">Not-AI Test &amp; Book</span>
          </div>
        </button>

      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STEP 1: MOBILE & PATIENT DETAILS                                          -->
    <!-- ========================================================================= -->
    <div id="step-section-1" class="solemn-card p-6 sm:p-8 relative">
      
      <!-- Section Header -->
      <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
          <i class="fa-solid fa-mobile-screen"></i>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Step 1 of 4</span>
          <h2 class="text-lg sm:text-xl font-bold text-slate-900">Mobile &amp; Patient Verification</h2>
        </div>
      </div>

      <!-- Mobile Input Box -->
      <div class="max-w-xl mx-auto mb-6">
        <label class="block text-xs font-bold text-slate-700 mb-2">
          Patient Mobile Number <span class="text-rose-500">*</span>
        </label>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div class="relative flex-1">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-slate-400 pointer-events-none">
              <span class="text-xs font-bold text-slate-600">+91</span>
              <div class="w-px h-4 bg-slate-300"></div>
              <i class="fa-solid fa-phone text-xs"></i>
            </div>
            <input 
              type="tel" 
              id="patient-phone-input" 
              maxlength="15"
              placeholder="Enter 10-digit mobile number" 
              oninput="handlePhoneInput(this.value)"
              onkeydown="if(event.key==='Enter'){ event.preventDefault(); checkPatientPhone(); }"
              class="w-full pl-20 pr-10 py-3.5 text-base font-semibold text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition"
              autocomplete="tel"
            >
            <div id="phone-search-spinner" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2">
              <i class="fa-solid fa-circle-notch fa-spin text-indigo-600"></i>
            </div>
          </div>
          
          <button 
            type="button" 
            onclick="checkPatientPhone()" 
            id="btn-check-phone"
            class="px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2 shrink-0 cursor-pointer"
          >
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Check Mobile</span>
          </button>
        </div>
        <p class="text-[11px] text-slate-500 mt-2 font-normal">
          <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
          System automatically checks when 10 digits are typed.
        </p>
      </div>

      <!-- CASE A: PATIENT ALREADY IN SYSTEM -->
      <div id="existing-patient-card" class="hidden bg-emerald-50/70 border border-emerald-300/80 rounded-2xl p-5 sm:p-6 mb-4 transition-all">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-emerald-200/70">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold shadow-xs shrink-0">
              <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full mb-1">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> Recognized Patient
              </span>
              <h3 id="ex-patient-name" class="text-lg sm:text-xl font-extrabold text-slate-900">Patient Name</h3>
              <p class="text-xs text-emerald-800 font-medium">
                Welcome back! Your details are in our system. <strong>No need to fill the form again.</strong>
              </p>
            </div>
          </div>
          <button type="button" onclick="resetPhoneLookup()" class="text-xs font-semibold text-slate-600 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 px-3.5 py-1.5 rounded-xl transition">
            <i class="fa-solid fa-rotate-left mr-1"></i> Use Different Number
          </button>
        </div>

        <!-- Demographics Quick Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3.5 rounded-xl border border-emerald-100 mb-5 text-xs">
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Patient MRN</span>
            <strong id="ex-patient-mrn" class="font-mono text-slate-800">--</strong>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Gender &amp; Age</span>
            <strong id="ex-patient-gender-age" class="text-slate-800">--</strong>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Blood Group</span>
            <strong id="ex-patient-blood" class="text-rose-600">--</strong>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Last Visit</span>
            <strong id="ex-patient-last-visit" class="text-slate-800">--</strong>
          </div>
        </div>

        <!-- Action to Step 2 -->
        <div class="flex items-center justify-end">
          <button 
            type="button" 
            onclick="proceedFromStep1()" 
            class="w-full sm:w-auto px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>Continue to Select Doctor</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- CASE B: PATIENT NOT FOUND -> REGISTER FIRST TIME -->
      <div id="new-patient-form-section" class="hidden bg-slate-50 border border-slate-200 rounded-2xl p-5 sm:p-6 mb-4 transition-all">
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
          <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-base font-bold shrink-0">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">New Patient Registration</h3>
            <p class="text-xs text-slate-500 font-normal">
              This mobile number is not yet registered. Please enter your name and details to register.
            </p>
          </div>
        </div>

        <form id="new-patient-form" onsubmit="handleNewPatientSubmit(event)" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">First Name <span class="text-rose-500">*</span></label>
              <input type="text" id="reg-name" required placeholder="e.g. Ramesh" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Father / Spouse Name</label>
              <input type="text" id="reg-father" placeholder="e.g. Suresh" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Last Name / Surname <span class="text-rose-500">*</span></label>
              <input type="text" id="reg-surname" required placeholder="e.g. Patel" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Age (Years) <span class="text-rose-500">*</span></label>
              <input type="number" id="reg-age" required min="1" max="120" placeholder="e.g. 35" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Gender <span class="text-rose-500">*</span></label>
              <select id="reg-gender" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Blood Group</label>
              <select id="reg-blood" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+" selected>O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Address / City</label>
              <input type="text" id="reg-address" placeholder="e.g. Ahmedabad, Gujarat" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Emergency Contact Phone (Optional)</label>
              <input type="tel" id="reg-em-phone" placeholder="e.g. 9825099999" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
            </div>
          </div>

          <div class="flex items-center justify-end pt-3">
            <button 
              type="submit" 
              class="w-full sm:w-auto px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Save &amp; Continue to Select Doctor</span>
              <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </form>
      </div>

    </div>

    <!-- ========================================================================= -->
    <!-- STEP 2: SELECT DOCTOR                                                     -->
    <!-- ========================================================================= -->
    <div id="step-section-2" class="hidden solemn-card p-6 sm:p-8 relative">
      
      <!-- Top Patient Summary Bar -->
      <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
            <i class="fa-solid fa-user"></i>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Consultation For</span>
            <strong id="step2-patient-name" class="text-xs sm:text-sm font-bold text-slate-900">Patient Name</strong>
            <span id="step2-patient-info" class="text-xs text-slate-500 ml-1"></span>
          </div>
        </div>
        <button type="button" onclick="goToStep(1)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-white border border-slate-200 px-3 py-1 rounded-lg transition">
          <i class="fa-solid fa-pen text-[10px] mr-1"></i> Change Patient
        </button>
      </div>

      <!-- Section Header & Filter -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
            <i class="fa-solid fa-user-doctor"></i>
          </div>
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Step 2 of 4</span>
            <h2 class="text-lg sm:text-xl font-bold text-slate-900">Select Doctor</h2>
          </div>
        </div>

        <!-- Search Box -->
        <div class="w-full sm:w-64 relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
          <input 
            type="text" 
            id="doc-search-query" 
            oninput="filterDoctorCards()" 
            placeholder="Search doctor or specialty..." 
            class="w-full pl-9 pr-3.5 py-2 text-xs font-medium border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none transition"
          >
        </div>
      </div>

      <!-- Department Category Pills -->
      <div class="mb-5 flex gap-2 overflow-x-auto pb-2 custom-scrollbar" id="dept-filter-pills">
        <button type="button" onclick="selectDepartmentFilter('all')" class="dept-pill px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shrink-0 transition" data-dept="all">
          All Specialists
        </button>
      </div>

      <!-- Doctors Grid -->
      <div id="doctors-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="col-span-full text-center py-10 text-slate-400 text-xs">
          <i class="fa-solid fa-circle-notch fa-spin text-indigo-500 text-base mb-1"></i>
          <p>Loading available doctors...</p>
        </div>
      </div>

      <!-- Step Navigation -->
      <div class="flex items-center justify-between pt-5 border-t border-slate-100">
        <button 
          type="button" 
          onclick="goToStep(1)" 
          class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-pointer"
        >
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back to Step 1</span>
        </button>

        <button 
          type="button" 
          id="btn-next-to-step3" 
          onclick="proceedFromStep2()" 
          disabled
          class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-not-allowed"
        >
          <span>Next: Date &amp; Time Slot</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>

    </div>

    <!-- ========================================================================= -->
    <!-- STEP 3: DATE & TIME SLOT                                                  -->
    <!-- ========================================================================= -->
    <div id="step-section-3" class="hidden solemn-card p-6 sm:p-8 relative">
      
      <!-- Summary Bar -->
      <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-4 text-xs">
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400 uppercase font-bold text-[10px]">Patient:</span>
            <strong id="step3-patient-name" class="font-bold text-slate-800">--</strong>
          </div>
          <div class="h-3 w-px bg-slate-300 hidden sm:block"></div>
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400 uppercase font-bold text-[10px]">Doctor:</span>
            <strong id="step3-doctor-name" class="font-bold text-indigo-600">--</strong>
          </div>
        </div>
        <button type="button" onclick="goToStep(2)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-white border border-slate-200 px-3 py-1 rounded-lg transition">
          <i class="fa-solid fa-user-doctor text-[10px] mr-1"></i> Change Doctor
        </button>
      </div>

      <!-- Section Header -->
      <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
          <i class="fa-regular fa-clock"></i>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Step 3 of 4</span>
          <h2 class="text-lg sm:text-xl font-bold text-slate-900">Date &amp; Time Slot</h2>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        <!-- Left: Date Selection & Symptoms -->
        <div class="lg:col-span-5 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">
              Consultation Date <span class="text-rose-500">*</span>
            </label>
            <input 
              type="date" 
              id="appt-date-input" 
              min="<?php echo date('Y-m-d'); ?>" 
              value="<?php echo date('Y-m-d'); ?>" 
              onchange="handleDateChange(this.value)" 
              class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-semibold border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none"
            >
            <!-- Quick Date Chips -->
            <div class="flex gap-2 mt-2">
              <button type="button" onclick="setQuickDate(0)" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 transition">Today</button>
              <button type="button" onclick="setQuickDate(1)" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 transition">Tomorrow</button>
              <button type="button" onclick="setQuickDate(2)" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 transition">In 2 Days</button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">
              Consultation Type
            </label>
            <select id="appt-type-select" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none">
              <option value="General Consultation" selected>General Consultation</option>
              <option value="Follow-up Consultation">Follow-up Consultation</option>
              <option value="Routine Health Checkup">Routine Health Checkup</option>
              <option value="Urgent OPD">Urgent OPD</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">
              Reason / Symptoms (Optional)
            </label>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <button type="button" onclick="addSymptomChip('Fever')" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 transition">+ Fever</button>
              <button type="button" onclick="addSymptomChip('Cough')" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 transition">+ Cough</button>
              <button type="button" onclick="addSymptomChip('Chest Pain')" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 transition">+ Chest Pain</button>
              <button type="button" onclick="addSymptomChip('Headache')" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 transition">+ Headache</button>
            </div>
            <textarea 
              id="appt-symptoms-input" 
              rows="3" 
              placeholder="Briefly describe your symptoms or reason for visit..." 
              class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none"
            ></textarea>
          </div>
        </div>

        <!-- Right: Time Slots Matrix -->
        <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5">
          <div class="flex items-center justify-between mb-4 pb-2.5 border-b border-slate-200">
            <div>
              <h3 class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-day text-indigo-600"></i> Available Slots for <span id="slots-header-date" class="text-indigo-600">--</span>
              </h3>
              <p id="slots-header-status" class="text-[11px] text-slate-500 mt-0.5 font-normal">Click any available time slot below to choose your time.</p>
            </div>
            <span id="slots-counts-badge" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700">
              0 Available
            </span>
          </div>

          <!-- Doctor Off Alert -->
          <div id="slots-day-off-alert" class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-center gap-2 mb-4">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 shrink-0"></i>
            <span id="slots-day-off-msg">Doctor is not consulting on this date. Please pick another date.</span>
          </div>

          <!-- Slots Container -->
          <div id="slots-matrix-container" class="space-y-4">
            <!-- Morning Slots -->
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-1.5">
                <i class="fa-regular fa-sun text-amber-500"></i> Morning (09:00 AM - 12:00 PM)
              </span>
              <div id="slots-morning-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
            </div>

            <!-- Afternoon Slots -->
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-cloud-sun text-sky-500"></i> Afternoon (12:00 PM - 05:00 PM)
              </span>
              <div id="slots-afternoon-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
            </div>

            <!-- Evening Slots -->
            <div id="slots-evening-wrapper" class="hidden">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-moon text-indigo-500"></i> Evening (05:00 PM Onwards)
              </span>
              <div id="slots-evening-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
            </div>
          </div>

          <!-- Selected Slot Banner -->
          <div id="selected-slot-strip" class="hidden mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-slate-600">Selected Slot:</span>
              <span id="selected-slot-label" class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-600 text-white">--</span>
            </div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md flex items-center gap-1">Time Selected <i class="fa-solid fa-check text-[10px]"></i></span>
          </div>
        </div>

      </div>

      <!-- Step Navigation -->
      <div class="flex items-center justify-between pt-5 border-t border-slate-100">
        <button 
          type="button" 
          onclick="goToStep(2)" 
          class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-pointer"
        >
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back to Step 2 (Doctor)</span>
        </button>

        <button 
          type="button" 
          id="btn-next-to-step4" 
          onclick="proceedFromStep3()" 
          disabled
          class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-not-allowed"
        >
          <span>Next: Not-AI Test &amp; Book</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>

    </div>

    <!-- ========================================================================= -->
    <!-- STEP 4: NOT-AI TEST & BOOK                                                -->
    <!-- ========================================================================= -->
    <div id="step-section-4" class="hidden solemn-card p-6 sm:p-8 relative">
      
      <!-- Section Header -->
      <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
          <i class="fa-solid fa-shield-check"></i>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Step 4 of 4</span>
          <h2 class="text-lg sm:text-xl font-bold text-slate-900">Not-AI Test &amp; Book</h2>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mb-6">
        
        <!-- Anti-Bot Verification Box -->
        <div class="lg:col-span-6 bg-slate-50 border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-100 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
              <i class="fa-solid fa-user-shield"></i> Not-AI Human Check
            </span>
            <button type="button" onclick="loadAntiBotCaptcha()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
              <i class="fa-solid fa-rotate text-xs"></i> Refresh
            </button>
          </div>

          <div>
            <h3 class="text-base font-bold text-slate-900">Are you a human patient?</h3>
            <p class="text-xs text-slate-500 font-normal mt-0.5">Solve this simple math puzzle to confirm you are not an automated bot.</p>
          </div>

          <!-- Math Puzzle Box -->
          <div class="bg-white border-2 border-dashed border-indigo-200 rounded-xl p-4 flex items-center justify-center gap-3">
            <div id="captcha-num1" class="w-12 h-12 rounded-xl bg-indigo-600 text-white font-extrabold text-xl flex items-center justify-center">
              ?
            </div>
            <span class="text-xl font-bold text-slate-600">+</span>
            <div id="captcha-num2" class="w-12 h-12 rounded-xl bg-indigo-700 text-white font-extrabold text-xl flex items-center justify-center">
              ?
            </div>
            <span class="text-xl font-bold text-slate-600">=</span>
            <div class="w-20">
              <input 
                type="number" 
                id="captcha-answer-input" 
                oninput="validateAntiBotAnswer()" 
                placeholder="?" 
                class="w-full text-center py-2.5 text-lg font-bold text-slate-900 border-2 border-indigo-300 rounded-xl bg-indigo-50/40 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              >
            </div>
          </div>

          <!-- Human Checkbox -->
          <label class="flex items-start gap-2.5 bg-white p-3 rounded-xl border border-slate-200 cursor-pointer select-none">
            <input type="checkbox" id="human-verify-checkbox" onchange="validateAntiBotAnswer()" class="mt-0.5 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
            <span class="text-xs font-medium text-slate-700 leading-tight">
              I verify that I am a human patient booking a genuine doctor consultation.
            </span>
          </label>

          <!-- Feedback Status -->
          <div id="antibot-status-banner" class="hidden p-3 rounded-xl text-xs font-bold flex items-center gap-2"></div>
        </div>

        <!-- Appointment Summary & Confirm Button -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-4">
          <h3 class="font-bold text-slate-900 text-sm pb-2.5 border-b border-slate-100 flex items-center gap-1.5">
            <i class="fa-solid fa-receipt text-indigo-600"></i> Consultation Summary
          </h3>

          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Patient Name:</span>
              <strong id="summary-patient-name" class="font-bold text-slate-800">--</strong>
            </div>
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Mobile Number:</span>
              <strong id="summary-patient-phone" class="font-mono font-bold text-slate-800">--</strong>
            </div>
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Doctor:</span>
              <strong id="summary-doctor-name" class="font-bold text-indigo-600">--</strong>
            </div>
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Specialty:</span>
              <strong id="summary-doctor-dept" class="font-medium text-slate-700">--</strong>
            </div>
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Date &amp; Time:</span>
              <strong id="summary-slot-time" class="font-bold text-emerald-600">--</strong>
            </div>
            <div class="flex items-center justify-between py-1 border-b border-slate-50">
              <span class="text-slate-400 font-medium">Consultation Type:</span>
              <span id="summary-consult-type" class="font-medium text-slate-700">General Consultation</span>
            </div>
          </div>

          <div class="pt-2">
            <button 
              type="button" 
              id="btn-submit-appointment" 
              onclick="submitFinalAppointment()" 
              disabled 
              class="w-full py-3.5 bg-slate-200 text-slate-400 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed"
            >
              <i class="fa-solid fa-calendar-check"></i>
              <span id="btn-submit-text">Complete Not-AI Test to Book</span>
            </button>
            <p class="text-[11px] text-center text-slate-500 mt-2 font-normal">
              Time slot will be reserved. Token will be generated at hospital visit 15 min before time.
            </p>
          </div>
        </div>

      </div>

      <!-- Step Navigation -->
      <div class="flex items-center justify-between pt-5 border-t border-slate-100">
        <button 
          type="button" 
          onclick="goToStep(3)" 
          class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-pointer"
        >
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back to Step 3 (Date &amp; Time)</span>
        </button>
      </div>

    </div>

  </main>

  <!-- ========================================================================= -->
  <!-- OFFICIAL OPD CONSULTATION PASS MODAL (PROFESSIONAL WITH DOWNLOAD IMAGE)   -->
  <!-- ========================================================================= -->
  <div id="confirmation-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
    <div class="solemn-card max-w-2xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden my-8 p-4 sm:p-7 border border-slate-200">
      
      <!-- Top Success & Instant Download Notice Banner -->
      <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-2xl p-3 sm:p-3.5 flex items-center justify-between gap-3 text-xs text-emerald-900 no-print">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div>
            <h4 class="font-bold text-slate-900">Appointment Booked Successfully!</h4>
            <p class="text-emerald-800 text-[11px]">Your official consultation card is ready. You can download it as an HD image or print it.</p>
          </div>
        </div>
        
        <button 
          type="button" 
          onclick="downloadAppointmentImage()" 
          class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0"
        >
          <i class="fa-solid fa-download text-xs"></i>
          <span class="hidden sm:inline">Save Image</span>
        </button>
      </div>

      <!-- =================================================================== -->
      <!-- THE OFFICIAL PRINTABLE / DOWNLOADABLE APPOINTMENT CARD (HD IMAGE)   -->
      <!-- =================================================================== -->
      <div id="printable-slip" class="bg-white rounded-2xl border border-slate-300 shadow-xs" style="max-width: 660px; margin: 0 auto; color: #1e293b; overflow: visible;">
        
        <!-- Top Security Header Ribbon -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white px-5 py-2 flex items-center justify-between text-[11px] font-semibold tracking-wider uppercase border-b border-indigo-500/30">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
            <span>Official Outpatient Consultation Pass (OPD)</span>
          </div>
          <span class="text-indigo-200" id="slip-issue-date"><?php echo date('d M Y'); ?></span>
        </div>

        <!-- Hospital Brand Header with Logo AND Full Hospital Name -->
        <div class="p-5 sm:p-6 bg-gradient-to-b from-slate-50/90 to-white border-b border-slate-200">
          <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4 text-center sm:text-left">
            
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
              <!-- Hospital Logo (Large, prominent, and crystal-clear) -->
              <div class="p-2 sm:p-2.5 bg-white rounded-2xl border border-slate-200 shadow-xs flex items-center justify-center shrink-0">
                <img src="<?php echo $logoDataUri; ?>" alt="Bhooma Medicare Logo" class="h-16 sm:h-20 w-auto max-w-[240px] sm:max-w-[280px] object-contain">
              </div>
              
              <!-- Full Name & Hospital Details -->
              <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                  <?php echo htmlspecialchars($hospital_full_name); ?>
                </h1>
                <p class="text-xs font-bold text-indigo-700 tracking-wide mt-0.5">
                  Multi-Speciality, Critical Care &amp; Emergency Hospital
                </p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 text-xs text-slate-600 font-semibold mt-1">
                  <span class="text-indigo-800 font-bold"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> Palanpur, Gujarat</span>
                  <span>•</span>
                  <span><i class="fa-solid fa-phone text-emerald-600 mr-1"></i> Helpline: +91 98201 12345</span>
                  <span>•</span>
                  <span class="text-rose-600 font-bold"><i class="fa-solid fa-truck-medical mr-1"></i> 24x7 Emergency: 108</span>
                </div>
              </div>
            </div>

            <!-- Verified Status Badge -->
            <div class="shrink-0 flex flex-col items-center sm:items-end">
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> Confirmed &amp; Scheduled
              </span>
              <span class="text-[10px] text-slate-400 mt-1 font-mono font-bold" id="slip-booking-ref-badge">BK-REF</span>
            </div>

          </div>
        </div>

        <!-- Slot Confirmation Hero Card (Token generated at hospital) -->
        <div class="p-5 sm:p-6 bg-white">
          <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-teal-600 text-white rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left flex-1">
              <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-xs border border-white/30 text-[10px] font-black uppercase tracking-wider text-amber-200 mb-1">
                <i class="fa-solid fa-calendar-check"></i> Time Slot Booked • Token Assigned at Hospital
              </div>
              <div class="text-2xl sm:text-3xl font-black tracking-tight mt-0.5" id="slip-token">SLOT CONFIRMED</div>
              <p class="text-xs text-indigo-100 mt-1 font-medium leading-relaxed" id="slip-reporting-note">
                <i class="fa-solid fa-clock mr-1 text-amber-300"></i> Token will be generated at hospital visit 15 min before time. Please arrive by <span id="slip-arrive-time" class="font-bold underline text-white">--:-- AM</span>.
              </p>
            </div>
            
            <div class="bg-white/10 backdrop-blur-xs rounded-xl p-3 border border-white/20 text-center shrink-0 min-w-[170px]">
              <span class="text-[10px] uppercase font-bold text-indigo-200 block tracking-wider">Scheduled Slot</span>
              <div class="text-lg font-black text-white mt-0.5" id="slip-slot">--:-- AM</div>
              <div class="text-[11px] font-bold text-indigo-100 mt-0.5" id="slip-date">-- --- ----</div>
              <div class="mt-1.5 text-[10px] font-bold bg-amber-400 text-amber-950 px-2 py-0.5 rounded-md inline-block shadow-xs">
                <i class="fa-solid fa-ticket-simple mr-1"></i> Token at OPD Reception
              </div>
            </div>
          </div>
        </div>

        <!-- Full Information 2-Column Grid (Unclipped, Crisp Text Layout) -->
        <div class="px-5 sm:px-6 pb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            
            <!-- Column 1: Patient Information -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
              <div class="flex items-center gap-2 pb-2 mb-3 border-b border-slate-200">
                <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                  <i class="fa-solid fa-user"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Patient Information</h3>
              </div>

              <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                <div>
                  <div class="field-title">Patient Name</div>
                  <div class="field-val" id="slip-patient-name">--</div>
                </div>
                <div>
                  <div class="field-title">Patient MRN / ID</div>
                  <div class="field-val font-mono text-indigo-700" id="slip-patient-mrn">--</div>
                </div>
                <div>
                  <div class="field-title">Contact Phone</div>
                  <div class="field-val font-mono" id="slip-patient-phone">--</div>
                </div>
                <div>
                  <div class="field-title">Age &amp; Gender</div>
                  <div class="field-val" id="slip-patient-gender-age">--</div>
                </div>
                <div>
                  <div class="field-title">Blood Group</div>
                  <div class="field-val text-rose-600 font-extrabold" id="slip-patient-blood">--</div>
                </div>
                <div>
                  <div class="field-title">Booking Reference</div>
                  <div class="field-val font-mono" id="slip-booking-ref">--</div>
                </div>
              </div>
            </div>

            <!-- Column 2: Doctor & Consultation Information -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
              <div class="flex items-center gap-2 pb-2 mb-3 border-b border-slate-200">
                <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                  <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Doctor &amp; Consultation</h3>
              </div>

              <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                <div class="col-span-2">
                  <div class="field-title">Consulting Doctor</div>
                  <div class="field-val text-indigo-700 text-sm font-black" id="slip-doctor-name">--</div>
                </div>
                <div class="col-span-2">
                  <div class="field-title">Specialty / Department</div>
                  <div class="field-val font-semibold text-slate-800" id="slip-doctor-dept">--</div>
                </div>
                <div>
                  <div class="field-title">Consultation Type</div>
                  <div class="field-val" id="slip-consult-type">General OPD</div>
                </div>
                <div>
                  <div class="field-title">Status</div>
                  <div class="pt-0.5">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">
                      <i class="fa-solid fa-check text-[9px]"></i> Scheduled
                    </span>
                  </div>
                </div>
                <div class="col-span-2">
                  <div class="field-title">Reason / Symptoms</div>
                  <div class="field-val text-slate-600 font-medium text-xs" id="slip-symptoms">--</div>
                </div>
              </div>
            </div>

          </div>

          <!-- Reporting Guidelines Notice -->
          <div class="mt-3.5 bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5 text-xs text-amber-900">
            <i class="fa-solid fa-circle-exclamation text-amber-600 text-sm shrink-0 mt-0.5"></i>
            <div class="leading-relaxed text-[11px]">
              <strong>Hospital Visit Instructions:</strong> Your time slot is reserved. Token will be generated at hospital visit 15 min before time upon scanning your QR Code or providing your Booking Reference (<strong class="font-mono text-indigo-800" id="slip-booking-ref-note">#APP</strong>). Please carry any prior medical reports or doctor prescriptions.
            </div>
          </div>

          <!-- Bottom Security Barcode & Seal Strip -->
          <div class="mt-3.5 pt-3 border-t border-dashed border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400">
            <div class="flex items-center gap-2">
              <!-- Stylized Barcode SVG Representation -->
              <svg class="h-6 w-36 text-slate-700" viewBox="0 0 100 24" fill="currentColor">
                <rect x="0" y="0" width="3" height="24"/>
                <rect x="5" y="0" width="1" height="24"/>
                <rect x="8" y="0" width="4" height="24"/>
                <rect x="14" y="0" width="2" height="24"/>
                <rect x="18" y="0" width="1" height="24"/>
                <rect x="21" y="0" width="3" height="24"/>
                <rect x="26" y="0" width="2" height="24"/>
                <rect x="30" y="0" width="4" height="24"/>
                <rect x="36" y="0" width="1" height="24"/>
                <rect x="39" y="0" width="3" height="24"/>
                <rect x="44" y="0" width="2" height="24"/>
                <rect x="48" y="0" width="1" height="24"/>
                <rect x="51" y="0" width="4" height="24"/>
                <rect x="57" y="0" width="2" height="24"/>
                <rect x="61" y="0" width="3" height="24"/>
                <rect x="66" y="0" width="1" height="24"/>
                <rect x="69" y="0" width="4" height="24"/>
                <rect x="75" y="0" width="2" height="24"/>
                <rect x="79" y="0" width="3" height="24"/>
                <rect x="84" y="0" width="1" height="24"/>
                <rect x="87" y="0" width="4" height="24"/>
                <rect x="93" y="0" width="2" height="24"/>
                <rect x="97" y="0" width="3" height="24"/>
              </svg>
              <span class="font-mono text-[10px] text-slate-500 font-bold" id="slip-barcode-text">BK-00000</span>
            </div>

            <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-semibold">
              <i class="fa-solid fa-stamp text-indigo-600"></i>
              <span>Official System-Generated Medical Consultation Pass</span>
            </div>
          </div>

        </div>

      </div>

      <!-- =================================================================== -->
      <!-- MODAL ACTION BUTTONS                                                -->
      <!-- =================================================================== -->
      <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 no-print">
        
        <!-- Primary Action: Download Appointment Image -->
        <button 
          type="button" 
          id="btn-download-img"
          onclick="downloadAppointmentImage()" 
          class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2 cursor-pointer shadow-md shadow-emerald-600/20"
        >
          <i class="fa-solid fa-download"></i>
          <span>Download Appointment Image</span>
        </button>

        <!-- Secondary Actions -->
        <div class="flex items-center gap-2">
          <button 
            type="button" 
            onclick="window.print()" 
            class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <i class="fa-solid fa-print"></i>
            <span>Print Slip</span>
          </button>

          <button 
            type="button" 
            onclick="resetFullBookingWorkflow()" 
            class="px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition"
          >
            <i class="fa-solid fa-plus mr-1"></i> Book Another
          </button>

          <button 
            type="button" 
            onclick="closeConfirmationModal()" 
            class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
          >
            Close
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= JAVASCRIPT STATE ENGINE ================= -->
  <script>
  const bookingState = {
      currentStep: 1,

      // Step 1: Patient
      phone: '',
      isExistingPatient: false,
      patientId: '',
      patientName: '',
      patientDetails: null,
      newPatientData: null,
      step1Completed: false,
      
      // Step 2: Doctor
      selectedDoctorId: '',
      selectedDoctorName: '',
      selectedDoctorDept: '',
      selectedDoctorTiming: '',
      allDoctors: [],
      step2Completed: false,
      
      // Step 3: Date & Slot
      selectedDate: '<?php echo date('Y-m-d'); ?>',
      selectedSlot: '',
      selectedSlotNumber: 1,
      totalSlots: 12,
      consultationType: 'General Consultation',
      symptoms: '',
      step3Completed: false,

      // Step 4: Anti-Bot Captcha
      captchaAnswer: null,
      captchaToken: '',
      userCaptchaAnswer: '',
      isHumanVerified: false
  };

  let phoneDebounceTimer = null;

  document.addEventListener('DOMContentLoaded', () => {
      loadDepartments();
      loadDoctors();
      loadAntiBotCaptcha();

      const urlParams = new URLSearchParams(window.location.search);
      const prefillPhone = urlParams.get('phone');
      if (prefillPhone) {
          document.getElementById('patient-phone-input').value = prefillPhone;
          checkPatientPhone();
      }
  });

  function goToStep(step) {
      if (step === 2 && !bookingState.step1Completed) {
          alert('Please enter and verify your mobile number in Step 1 first.');
          return;
      }
      if (step === 3 && !bookingState.selectedDoctorId) {
          alert('Please select a doctor in Step 2 first.');
          return;
      }
      if (step === 4 && !bookingState.selectedSlot) {
          alert('Please select an available time slot in Step 3 first.');
          return;
      }

      for (let i = 1; i <= 4; i++) {
          const sec = document.getElementById(`step-section-${i}`);
          if (sec) sec.classList.add('hidden');
      }

      const activeSec = document.getElementById(`step-section-${step}`);
      if (activeSec) {
          activeSec.classList.remove('hidden');
          activeSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      bookingState.currentStep = step;
      updateStepTrackerUI(step);

      if (step === 2) {
          document.getElementById('step2-patient-name').textContent = bookingState.patientName || 'Patient';
          document.getElementById('step2-patient-info').textContent = bookingState.patientId ? `(MRN: ${bookingState.patientId})` : `(Mobile: ${bookingState.phone})`;
      } else if (step === 3) {
          document.getElementById('step3-patient-name').textContent = bookingState.patientName || 'Patient';
          document.getElementById('step3-doctor-name').textContent = bookingState.selectedDoctorName;
          loadDoctorSlotsForDate();
      } else if (step === 4) {
          loadAntiBotCaptcha();
      }
  }

  function updateStepTrackerUI(currentStep) {
      for (let i = 1; i <= 4; i++) {
          const tab = document.getElementById(`step-tab-${i}`);
          const numBadge = document.getElementById(`step-tab-num-${i}`);
          if (!tab || !numBadge) continue;

          if (i < currentStep) {
              tab.className = 'step-tab p-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 transition-all flex items-center gap-3 text-left cursor-pointer';
              numBadge.className = 'w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0';
              numBadge.innerHTML = '<i class="fa-solid fa-check"></i>';
          } else if (i === currentStep) {
              tab.className = 'step-tab p-3.5 rounded-2xl bg-indigo-600 text-white transition-all flex items-center gap-3 text-left shadow-sm cursor-pointer';
              numBadge.className = 'w-8 h-8 rounded-xl bg-white/25 text-white font-bold text-xs flex items-center justify-center shrink-0';
              numBadge.textContent = i;
          } else {
              tab.className = 'step-tab p-3.5 rounded-2xl bg-white text-slate-400 border border-slate-200 transition-all flex items-center gap-3 text-left cursor-not-allowed';
              numBadge.className = 'w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center shrink-0';
              numBadge.textContent = i;
          }
      }
  }

  function handlePhoneInput(val) {
      clearTimeout(phoneDebounceTimer);
      const cleaned = val.replace(/\D/g, '');
      if (cleaned.length === 10) {
          phoneDebounceTimer = setTimeout(() => {
              checkPatientPhone();
          }, 300);
      }
  }

  async function checkPatientPhone() {
      const input = document.getElementById('patient-phone-input');
      const rawVal = (input.value || '').trim();
      const digits = rawVal.replace(/\D/g, '');

      if (digits.length < 5) {
          alert('Please enter a valid 10-digit mobile number.');
          return;
      }

      const spinner = document.getElementById('phone-search-spinner');
      if (spinner) spinner.classList.remove('hidden');

      try {
          const res = await fetch(`api/booking.php?action=lookup_patient&phone=${encodeURIComponent(rawVal)}`);
          const data = await res.json();

          bookingState.phone = rawVal;

          if (data.status === 'success' && data.found && data.patient) {
              const p = data.patient;
              bookingState.isExistingPatient = true;
              bookingState.patientId = p.id;
              bookingState.patientName = `${p.name || ''} ${p.surname || ''}`.trim();
              bookingState.patientDetails = p;
              bookingState.step1Completed = true;

              document.getElementById('ex-patient-mrn').textContent = p.id;
              document.getElementById('ex-patient-name').textContent = `${p.name || ''} ${p.surname || ''}`.trim();
              document.getElementById('ex-patient-gender-age').textContent = `${p.gender || 'Unknown'}, ${p.age ? p.age + ' Yrs' : '--'}`;
              document.getElementById('ex-patient-blood').textContent = p.blood_group ? p.blood_group : '--';
              document.getElementById('ex-patient-last-visit').textContent = p.last_visit || 'First Visit';

              document.getElementById('existing-patient-card').classList.remove('hidden');
              document.getElementById('new-patient-form-section').classList.add('hidden');
          } else {
              bookingState.isExistingPatient = false;
              bookingState.patientId = '';
              bookingState.patientName = '';
              bookingState.patientDetails = null;
              bookingState.step1Completed = false;

              document.getElementById('existing-patient-card').classList.add('hidden');
              document.getElementById('new-patient-form-section').classList.remove('hidden');
          }
      } catch (err) {
          console.error('Phone lookup failed', err);
          alert('Network error while checking phone number. Please try again.');
      } finally {
          if (spinner) spinner.classList.add('hidden');
      }
  }

  function resetPhoneLookup() {
      bookingState.isExistingPatient = false;
      bookingState.patientId = '';
      bookingState.patientName = '';
      bookingState.patientDetails = null;
      bookingState.step1Completed = false;

      document.getElementById('patient-phone-input').value = '';
      document.getElementById('existing-patient-card').classList.add('hidden');
      document.getElementById('new-patient-form-section').classList.add('hidden');
      document.getElementById('patient-phone-input').focus();
  }

  function handleNewPatientSubmit(e) {
      e.preventDefault();
      const name = document.getElementById('reg-name').value.trim();
      const surname = document.getElementById('reg-surname').value.trim();
      const father = document.getElementById('reg-father').value.trim();
      const age = document.getElementById('reg-age').value.trim();
      const gender = document.getElementById('reg-gender').value;
      const blood = document.getElementById('reg-blood').value;
      const emPhone = document.getElementById('reg-em-phone').value.trim();
      const address = document.getElementById('reg-address').value.trim();

      if (!name || !surname || !age) {
          alert('Please enter your First Name, Last Name, and Age.');
          return;
      }

      bookingState.isExistingPatient = false;
      bookingState.patientName = `${name} ${surname}`;
      bookingState.newPatientData = {
          name,
          surname,
          father_name: father,
          age,
          gender,
          blood_group: blood,
          emergency_contact_phone: emPhone,
          address
      };
      bookingState.step1Completed = true;

      proceedFromStep1();
  }

  function proceedFromStep1() {
      if (!bookingState.step1Completed) {
          alert('Please verify your mobile number first.');
          return;
      }
      goToStep(2);
  }

  async function loadDepartments() {
      try {
          const res = await fetch('api/booking.php?action=get_categories');
          const data = await res.json();
          if (data.status === 'success' && data.categories) {
              const container = document.getElementById('dept-filter-pills');
              data.categories.forEach(cat => {
                  const btn = document.createElement('button');
                  btn.type = 'button';
                  btn.className = 'dept-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 shrink-0 transition';
                  btn.dataset.dept = cat.name;
                  btn.textContent = cat.name;
                  btn.onclick = () => selectDepartmentFilter(cat.name);
                  container.appendChild(btn);
              });
          }
      } catch (e) { console.error('Failed to load departments', e); }
  }

  async function loadDoctors() {
      try {
          const res = await fetch('api/booking.php?action=get_doctors_by_category');
          const data = await res.json();
          if (data.status === 'success' && data.doctors) {
              bookingState.allDoctors = data.doctors;
              renderDoctorsGrid(data.doctors);
          }
      } catch (e) { console.error('Failed to load doctors', e); }
  }

  function selectDepartmentFilter(dept) {
      document.querySelectorAll('.dept-pill').forEach(btn => {
          if (btn.dataset.dept === dept) {
              btn.className = 'dept-pill px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shrink-0 transition';
          } else {
              btn.className = 'dept-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 shrink-0 transition';
          }
      });

      if (dept === 'all') {
          renderDoctorsGrid(bookingState.allDoctors);
      } else {
          const filtered = bookingState.allDoctors.filter(d => {
              return (d.categories || []).includes(dept);
          });
          renderDoctorsGrid(filtered);
      }
  }

  function filterDoctorCards() {
      const q = (document.getElementById('doc-search-query').value || '').toLowerCase().trim();
      if (!q) {
          renderDoctorsGrid(bookingState.allDoctors);
          return;
      }
      const filtered = bookingState.allDoctors.filter(d => {
          const text = `${d.name} ${(d.categories || []).join(' ')} ${d.degree || ''}`.toLowerCase();
          return text.includes(q);
      });
      renderDoctorsGrid(filtered);
  }

  function renderDoctorsGrid(docs) {
      const container = document.getElementById('doctors-grid');
      if (!container) return;
      container.innerHTML = '';

      if (docs.length === 0) {
          container.innerHTML = `
              <div class="col-span-full text-center py-8 bg-slate-50 border border-slate-200 rounded-xl text-slate-400 text-xs">
                  <i class="fa-solid fa-user-doctor text-xl mb-1 text-slate-300"></i>
                  <p>No doctors found matching your search.</p>
              </div>
          `;
          return;
      }

      docs.forEach(doc => {
          const card = document.createElement('div');
          const isSelected = bookingState.selectedDoctorId === doc.id;
          const isAvail = doc.is_available !== false;
          const availCount = doc.available_count ?? 10;
          const totalCount = doc.total_slots ?? 12;
          const initials = (doc.name || 'Dr').replace(/^Dr\.?\s*/i, '').substring(0, 2).toUpperCase() || 'DR';
          const cats = (doc.categories || []).map(c => `<span class="inline-block bg-slate-100 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded-md mr-1 mb-1">${c}</span>`).join('') || '<span class="text-[10px] text-slate-400 italic">General OPD</span>';

          card.className = `p-4 sm:p-5 rounded-2xl border-2 transition-all flex flex-col justify-between ${isSelected ? 'border-indigo-600 bg-indigo-50/30 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300'}`;
          card.innerHTML = `
              <div>
                  <div class="flex items-start justify-between gap-3 mb-2.5">
                      <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                              ${initials}
                          </div>
                          <div class="min-w-0">
                              <h4 class="font-bold text-slate-900 text-sm leading-tight truncate">${doc.name}</h4>
                              <p class="text-[11px] text-slate-500 font-normal truncate">${doc.degree || 'Specialist Consultant'}</p>
                          </div>
                      </div>
                      <span class="w-2.5 h-2.5 rounded-full ${isAvail ? 'bg-emerald-500' : 'bg-slate-300'} ring-2 ring-white shrink-0" title="${isAvail ? 'Available' : 'Day Off'}"></span>
                  </div>

                  <div class="mb-3">
                      ${cats}
                  </div>

                  <div class="space-y-1 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100 mb-3.5">
                      <div class="flex items-center justify-between text-slate-600">
                          <span class="font-normal">Hours:</span>
                          <strong class="font-semibold text-slate-800">${doc.today_timing || '09:00 AM - 05:00 PM'}</strong>
                      </div>
                      <div class="flex items-center justify-between text-slate-600">
                          <span class="font-normal">Slots Today:</span>
                          <strong class="font-semibold ${isAvail ? 'text-emerald-700' : 'text-slate-400'}">${isAvail ? `${availCount} free` : 'Day Off'}</strong>
                      </div>
                  </div>
              </div>

              <button 
                  type="button" 
                  onclick="chooseDoctor('${doc.id}', '${escapeJs(doc.name)}', '${escapeJs((doc.categories || []).join(', '))}', '${escapeJs(doc.today_timing || '09:00 AM - 05:00 PM')}')" 
                  class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer ${isSelected ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700'}"
              >
                  <i class="fa-solid ${isSelected ? 'fa-check' : 'fa-calendar-plus'}"></i>
                  <span>${isSelected ? 'Doctor Selected' : 'Select Doctor'}</span>
              </button>
          `;
          container.appendChild(card);
      });
  }

  function chooseDoctor(id, name, specialty, timing) {
      bookingState.selectedDoctorId = id;
      bookingState.selectedDoctorName = name;
      bookingState.selectedDoctorDept = specialty || 'Specialist Consultant';
      bookingState.selectedDoctorTiming = timing;
      bookingState.step2Completed = true;

      const nextBtn = document.getElementById('btn-next-to-step3');
      if (nextBtn) {
          nextBtn.disabled = false;
          nextBtn.className = 'px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-pointer shadow-xs';
      }

      renderDoctorsGrid(bookingState.allDoctors);
  }

  function proceedFromStep2() {
      if (!bookingState.selectedDoctorId) {
          alert('Please choose a doctor first.');
          return;
      }
      goToStep(3);
  }

  function handleDateChange(newDate) {
      bookingState.selectedDate = newDate;
      bookingState.selectedSlot = '';
      bookingState.step3Completed = false;
      document.getElementById('selected-slot-strip').classList.add('hidden');

      const nextBtn = document.getElementById('btn-next-to-step4');
      if (nextBtn) {
          nextBtn.disabled = true;
          nextBtn.className = 'px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-not-allowed';
      }

      loadDoctorSlotsForDate();
  }

  function setQuickDate(daysAhead) {
      const d = new Date();
      d.setDate(d.getDate() + daysAhead);
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      const formatted = `${yyyy}-${mm}-${dd}`;
      document.getElementById('appt-date-input').value = formatted;
      handleDateChange(formatted);
  }

  async function loadDoctorSlotsForDate() {
      if (!bookingState.selectedDoctorId) return;

      const dateVal = document.getElementById('appt-date-input').value || bookingState.selectedDate;
      const headerDate = document.getElementById('slots-header-date');
      const headerStatus = document.getElementById('slots-header-status');
      const countsBadge = document.getElementById('slots-counts-badge');
      const dayOffAlert = document.getElementById('slots-day-off-alert');
      const slotsMatrix = document.getElementById('slots-matrix-container');

      const dObj = new Date(dateVal + 'T00:00:00');
      const dateFormatted = dObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
      if (headerDate) headerDate.textContent = dateFormatted;

      try {
          const res = await fetch(`api/booking.php?action=get_doctor_slots&doctor_id=${encodeURIComponent(bookingState.selectedDoctorId)}&date=${encodeURIComponent(dateVal)}`);
          const data = await res.json();

          if (data.status === 'success') {
              if (!data.is_available) {
                  if (dayOffAlert) {
                      document.getElementById('slots-day-off-msg').textContent = data.message || 'Doctor is off on this date.';
                      dayOffAlert.classList.remove('hidden');
                  }
                  if (slotsMatrix) slotsMatrix.classList.add('hidden');
                  if (countsBadge) countsBadge.textContent = 'Day Off';
                  return;
              }

              if (dayOffAlert) dayOffAlert.classList.add('hidden');
              if (slotsMatrix) slotsMatrix.classList.remove('hidden');

              const free = data.available_count ?? 0;
              if (countsBadge) countsBadge.textContent = `${free} Available`;
              if (headerStatus) headerStatus.textContent = `${data.day_of_week} consultation hours: ${data.start_time} - ${data.end_time}`;

              renderSlotButtons(data.slots || []);
          }
      } catch (e) {
          console.error('Failed to load slots', e);
      }
  }

  function renderSlotButtons(slots) {
      const morningGrid = document.getElementById('slots-morning-grid');
      const afternoonGrid = document.getElementById('slots-afternoon-grid');
      const eveningGrid = document.getElementById('slots-evening-grid');
      const eveningWrapper = document.getElementById('slots-evening-wrapper');

      morningGrid.innerHTML = '';
      afternoonGrid.innerHTML = '';
      eveningGrid.innerHTML = '';

      let hasEvening = false;
      const totalSlots = slots.length;

      slots.forEach((item, index) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          const isSelected = bookingState.selectedSlot === item.slot;
          const slotNum = item.slot_number || (index + 1);
          const total = item.total_slots || totalSlots;

          if (item.available) {
              btn.className = `p-2 rounded-xl text-xs font-semibold transition border flex items-center justify-center gap-1 cursor-pointer ${isSelected ? 'border-indigo-600 bg-indigo-600 text-white shadow-xs' : 'border-slate-200 bg-white hover:border-indigo-400 text-slate-800'}`;
              btn.innerHTML = `<span>${item.slot}</span> ${isSelected ? '<i class="fa-solid fa-check text-[10px]"></i>' : ''}`;
              btn.onclick = () => selectSlot(item.slot, slotNum, total);
          } else {
              btn.disabled = true;
              btn.className = 'p-2 rounded-xl text-xs font-normal border border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center justify-center gap-1 line-through opacity-70';
              btn.innerHTML = `<span>${item.slot}</span>`;
          }

          if (item.period === 'Morning') {
              morningGrid.appendChild(btn);
          } else if (item.period === 'Afternoon') {
              afternoonGrid.appendChild(btn);
          } else {
              hasEvening = true;
              eveningGrid.appendChild(btn);
          }
      });

      if (eveningWrapper) {
          if (hasEvening) eveningWrapper.classList.remove('hidden');
          else eveningWrapper.classList.add('hidden');
      }
  }

  function selectSlot(slot, slotNum, totalSlots) {
      bookingState.selectedSlot = slot;
      if (slotNum) bookingState.selectedSlotNumber = slotNum;
      if (totalSlots) bookingState.totalSlots = totalSlots;
      bookingState.step3Completed = true;

      const slotNumStr = String(bookingState.selectedSlotNumber || 1).padStart(2, '0');
      const totalStr = bookingState.totalSlots || 12;

      document.getElementById('selected-slot-label').textContent = `${slot} (Slot #${slotNumStr} of ${totalStr}) on ${bookingState.selectedDate}`;
      document.getElementById('selected-slot-strip').classList.remove('hidden');

      const nextBtn = document.getElementById('btn-next-to-step4');
      if (nextBtn) {
          nextBtn.disabled = false;
          nextBtn.className = 'px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition flex items-center gap-1.5 cursor-pointer shadow-xs';
      }

      loadDoctorSlotsForDate();
  }

  function addSymptomChip(text) {
      const area = document.getElementById('appt-symptoms-input');
      const current = (area.value || '').trim();
      if (!current) {
          area.value = text;
      } else if (!current.includes(text)) {
          area.value = `${current}, ${text}`;
      }
  }

  function proceedFromStep3() {
      if (!bookingState.selectedSlot) {
          alert('Please choose an available time slot.');
          return;
      }

      bookingState.consultationType = document.getElementById('appt-type-select').value;
      bookingState.symptoms = document.getElementById('appt-symptoms-input').value.trim() || 'General Consultation';

      const slotNumStr = String(bookingState.selectedSlotNumber || 1).padStart(2, '0');
      const totalStr = bookingState.totalSlots || 12;

      document.getElementById('summary-patient-name').textContent = bookingState.patientName || 'Patient';
      document.getElementById('summary-patient-phone').textContent = bookingState.phone;
      document.getElementById('summary-doctor-name').textContent = bookingState.selectedDoctorName;
      document.getElementById('summary-doctor-dept').textContent = bookingState.selectedDoctorDept;
      document.getElementById('summary-slot-time').textContent = `${bookingState.selectedSlot} (Slot #${slotNumStr} of ${totalStr}) on ${bookingState.selectedDate}`;
      document.getElementById('summary-consult-type').textContent = bookingState.consultationType;

      goToStep(4);
  }

  async function loadAntiBotCaptcha() {
      try {
          const res = await fetch('api/booking.php?action=get_captcha');
          const data = await res.json();
          if (data.status === 'success') {
              bookingState.captchaAnswer = data.num1 + data.num2;
              bookingState.captchaToken = data.token;
              bookingState.isHumanVerified = false;

              document.getElementById('captcha-num1').textContent = data.num1;
              document.getElementById('captcha-num2').textContent = data.num2;
              document.getElementById('captcha-answer-input').value = '';
              document.getElementById('human-verify-checkbox').checked = false;

              updateAntiBotUI(false);
          }
      } catch (e) {
          console.error('Failed to load captcha', e);
      }
  }

  function validateAntiBotAnswer() {
      const userAns = parseInt(document.getElementById('captcha-answer-input').value, 10);
      const checkbox = document.getElementById('human-verify-checkbox').checked;

      if (!isNaN(userAns) && userAns === bookingState.captchaAnswer && checkbox) {
          bookingState.isHumanVerified = true;
          bookingState.userCaptchaAnswer = userAns;
          updateAntiBotUI(true);
      } else {
          bookingState.isHumanVerified = false;
          updateAntiBotUI(false);
      }
  }

  function updateAntiBotUI(isValid) {
      const banner = document.getElementById('antibot-status-banner');
      const submitBtn = document.getElementById('btn-submit-appointment');
      const submitText = document.getElementById('btn-submit-text');

      if (isValid) {
          banner.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5';
          banner.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600"></i> <span>Human Verification Passed</span>';
          banner.classList.remove('hidden');

          submitBtn.disabled = false;
          submitBtn.className = 'w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow-xs flex items-center justify-center gap-2 cursor-pointer';
          submitText.textContent = 'Confirm & Book Appointment';
      } else {
          banner.classList.add('hidden');
          submitBtn.disabled = true;
          submitBtn.className = 'w-full py-3.5 bg-slate-200 text-slate-400 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed';
          submitText.textContent = 'Complete Not-AI Test to Book';
      }
  }

  async function submitFinalAppointment() {
      if (!bookingState.isHumanVerified) {
          alert('Please solve the math question and check the verification box.');
          return;
      }

      const submitBtn = document.getElementById('btn-submit-appointment');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> <span>Booking Consultation...</span>';

      const payload = {
          require_antibot: true,
          anti_bot_answer: bookingState.userCaptchaAnswer,
          anti_bot_token: bookingState.captchaToken,
          doctor_id: bookingState.selectedDoctorId,
          date: bookingState.selectedDate,
          slot: bookingState.selectedSlot,
          slot_number: bookingState.selectedSlotNumber,
          total_slots: bookingState.totalSlots,
          type: bookingState.consultationType,
          symptoms: bookingState.symptoms,
          phone: bookingState.phone
      };

      if (bookingState.isExistingPatient) {
          payload.patient_id = bookingState.patientId;
      } else {
          Object.assign(payload, bookingState.newPatientData || {});
      }

      try {
          const res = await fetch('api/booking.php?action=book', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });
          const data = await res.json();

          if (data.status === 'success' && data.appointment) {
              displayConfirmationModal(data.appointment);
          } else {
              alert(data.message || 'Failed to book appointment. Please try again.');
              loadAntiBotCaptcha();
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> <span>Confirm & Book Appointment</span>';
          }
      } catch (err) {
          console.error('Booking submission error', err);
          alert('Connection error while booking. Please try again.');
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> <span>Confirm & Book Appointment</span>';
      }
  }

  function displayConfirmationModal(appt) {
      const slotTime = appt.slot || '--:-- AM';
      let arriveTime = appt.arrive_by || '';
      if (!arriveTime && slotTime && slotTime.includes(':')) {
          try {
              const [timePart, modifier] = slotTime.split(' ');
              let [hours, minutes] = timePart.split(':').map(Number);
              if (modifier === 'PM' && hours < 12) hours += 12;
              if (modifier === 'AM' && hours === 12) hours = 0;
              const dateObj = new Date(2000, 0, 1, hours, minutes);
              dateObj.setMinutes(dateObj.getMinutes() - 15);
              let h = dateObj.getHours();
              const m = String(dateObj.getMinutes()).padStart(2, '0');
              const ampm = h >= 12 ? 'PM' : 'AM';
              h = h % 12;
              h = h ? h : 12;
              arriveTime = `${String(h).padStart(2, '0')}:${m} ${ampm}`;
          } catch(e) {
              arriveTime = '15 min before slot';
          }
      }

      const tokenEl = document.getElementById('slip-token');
      if (tokenEl) tokenEl.textContent = 'SLOT CONFIRMED';

      const arriveEl = document.getElementById('slip-arrive-time');
      if (arriveEl) arriveEl.textContent = arriveTime || '15 min before slot';

      const noteEl = document.getElementById('slip-reporting-note');
      if (noteEl) {
          noteEl.innerHTML = `<i class="fa-solid fa-clock mr-1 text-amber-300"></i> Token will be generated at hospital visit 15 min before time. Please arrive by <span class="font-bold underline text-white">${arriveTime}</span>.`;
      }

      const bRef = appt.booking_ref || `#APP-${appt.id}`;
      document.getElementById('slip-booking-ref').textContent = bRef;
      document.getElementById('slip-booking-ref-badge').textContent = bRef;
      document.getElementById('slip-barcode-text').textContent = bRef;
      const refNoteEl = document.getElementById('slip-booking-ref-note');
      if (refNoteEl) refNoteEl.textContent = bRef;
      
      document.getElementById('slip-patient-mrn').textContent = appt.patient_id;
      document.getElementById('slip-patient-name').textContent = appt.patient_name;
      document.getElementById('slip-patient-phone').textContent = appt.phone;
      
      const genderAge = `${appt.gender || 'Patient'}${appt.age ? ', ' + appt.age + ' Yrs' : ''}`;
      document.getElementById('slip-patient-gender-age').textContent = genderAge;
      document.getElementById('slip-patient-blood').textContent = appt.blood_group ? appt.blood_group : '--';

      document.getElementById('slip-doctor-name').textContent = appt.doctor_name;
      document.getElementById('slip-doctor-dept').textContent = appt.doctor_specialty;
      document.getElementById('slip-date').textContent = appt.formatted_date || appt.date;
      document.getElementById('slip-slot').textContent = appt.slot;
      document.getElementById('slip-consult-type').textContent = appt.type || 'General OPD';
      document.getElementById('slip-symptoms').textContent = appt.symptoms || 'General Consultation';

      document.getElementById('confirmation-modal').classList.remove('hidden');

      // Auto-download HD appointment image after brief delay so patient receives card instantly
      setTimeout(() => {
          downloadAppointmentImage();
      }, 700);
  }

  async function downloadAppointmentImage() {
      const card = document.getElementById('printable-slip');
      if (!card) return;

      const downloadBtn = document.getElementById('btn-download-img');
      const origHtml = downloadBtn ? downloadBtn.innerHTML : '';
      if (downloadBtn) {
          downloadBtn.disabled = true;
          downloadBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xs"></i> <span>Preparing HD Image...</span>';
      }

      try {
          if (typeof html2canvas === 'undefined') {
              throw new Error('html2canvas library is not loaded');
          }

          // Use html2canvas with scale 2 for high-resolution retina rendering
          const canvas = await html2canvas(card, {
              scale: 2,
              useCORS: true,
              allowTaint: true,
              backgroundColor: '#ffffff',
              logging: false,
              onclone: function(clonedDoc) {
                  const clonedCard = clonedDoc.getElementById('printable-slip');
                  if (clonedCard) {
                      clonedCard.style.width = '680px';
                      clonedCard.style.maxWidth = '680px';
                      clonedCard.style.minWidth = '680px';
                      clonedCard.style.overflow = 'visible';
                      clonedCard.style.boxSizing = 'border-box';
                      
                      const allElements = clonedCard.querySelectorAll('*');
                      allElements.forEach(el => {
                          el.style.overflow = 'visible';
                          el.style.clip = 'auto';
                          el.style.clipPath = 'none';
                      });

                      const fieldVals = clonedCard.querySelectorAll('.field-val');
                      fieldVals.forEach(el => {
                          el.style.display = 'block';
                          el.style.overflow = 'visible';
                          el.style.lineHeight = '1.6';
                          el.style.paddingBottom = '4px';
                          el.style.whiteSpace = 'normal';
                          el.style.wordBreak = 'break-word';
                      });
                  }
              }
          });

          const dataUrl = canvas.toDataURL('image/png', 1.0);
          const rawRef = (document.getElementById('slip-booking-ref').textContent || 'SLOT').replace(/[^a-zA-Z0-9]/g, '_');
          const rawName = (document.getElementById('slip-patient-name').textContent || 'Patient').trim().replace(/[^a-zA-Z0-9]/g, '_');
          const fileName = `Appointment_Slot_${rawRef}_${rawName}.png`;

          const link = document.createElement('a');
          link.download = fileName;
          link.href = dataUrl;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          if (downloadBtn) {
              downloadBtn.innerHTML = '<i class="fa-solid fa-circle-check text-xs"></i> <span>Image Downloaded!</span>';
              setTimeout(() => {
                  downloadBtn.disabled = false;
                  downloadBtn.innerHTML = origHtml;
              }, 2500);
          }
      } catch (err) {
          console.error('Failed to generate appointment image', err);
          if (downloadBtn) {
              downloadBtn.disabled = false;
              downloadBtn.innerHTML = origHtml;
          }
          alert('Could not download image directly. You can use the "Print Slip" button to save as PDF.');
      }
  }

  function closeConfirmationModal() {
      document.getElementById('confirmation-modal').classList.add('hidden');
  }

  function resetFullBookingWorkflow() {
      closeConfirmationModal();
      window.location.reload();
  }

  function escapeJs(str) {
      return (str || '').replace(/'/g, "\\'");
  }
  </script>

  <!-- ================= FOOTER ================= -->
  <footer class="bg-white border-t border-slate-200 mt-12 py-6 text-center text-xs text-slate-500">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <img src="<?php echo $logoDataUri; ?>" alt="Hospital Logo" class="h-6 w-auto object-contain opacity-80" onerror="this.style.display='none';">
        <span class="font-normal text-slate-500">Online Patient Appointment System</span>
      </div>
      <p class="font-normal">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($hospital_full_name); ?>. All rights reserved.
      </p>
    </div>
  </footer>

</body>
</html>

<?php 
require_once 'auth.php'; 
require_once 'db.php';
include 'includes/header.php'; 

// Generate dynamic patient booking portal URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$patientBookUrl = $protocol . $host . $dir . '/patient_book.php';
?>

<!-- ================= SHAREABLE PATIENT PORTAL LINK CARD ================= -->
<div class="mb-6 bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white p-5 sm:p-6 rounded-3xl shadow-md border border-indigo-700/50 relative overflow-hidden">
  <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
    <div class="max-w-2xl">
      <div class="flex items-center gap-2 mb-2">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
          <i class="fa-solid fa-globe"></i> Public Patient Portal Active
        </span>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/10 text-indigo-200 border border-white/15">
          Step-by-Step Patient Booking
        </span>
      </div>
      <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">Patient Online Booking Portal Link</h3>
      <p class="text-indigo-200 mt-1 text-xs sm:text-sm font-medium">
        Share this direct link with patients via SMS, WhatsApp, or embed it on your hospital website so patients can self-book appointments online without front-desk staff.
      </p>
    </div>

    <!-- Copy & Open Actions -->
    <div class="w-full lg:w-auto shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
      <div class="relative flex-1 sm:w-80">
        <input 
          type="text" 
          id="patient-portal-url" 
          readonly 
          value="<?php echo htmlspecialchars($patientBookUrl); ?>" 
          class="w-full pl-3.5 pr-10 py-2.5 bg-indigo-950/60 border border-indigo-400/40 rounded-xl text-xs font-mono font-bold text-indigo-100 select-all focus:outline-none"
        >
        <button 
          type="button" 
          onclick="copyPatientBookingUrl()" 
          title="Copy Link to Clipboard" 
          class="absolute right-2 top-1/2 -translate-y-1/2 text-indigo-300 hover:text-white p-1 text-xs"
        >
          <i class="fa-regular fa-clone"></i>
        </button>
      </div>

      <button 
        type="button" 
        id="btn-copy-portal" 
        onclick="copyPatientBookingUrl()" 
        class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
      >
        <i class="fa-solid fa-copy"></i>
        <span id="copy-btn-text">Copy Link</span>
      </button>

      <a 
        href="<?php echo htmlspecialchars($patientBookUrl); ?>" 
        target="_blank" 
        class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-extrabold text-xs rounded-xl border border-white/20 transition flex items-center justify-center gap-1.5 shrink-0 backdrop-blur-sm"
        title="Open Patient Portal in New Tab"
      >
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        <span>Open Portal ↗</span>
      </a>
    </div>
  </div>

  <!-- Decorative glow -->
  <div class="absolute -right-8 -bottom-8 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
</div>

<!-- ================= ADMIN HEADER (DOCTOR SEARCH) ================= -->
<div class="mb-6 sm:mb-8 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-6 items-center justify-between">
    <div class="flex-1">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 uppercase tracking-wider">OPD Reception Hub</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Admin Appointment Booking Desk</h2>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm font-medium mb-4">Select any doctor below to open the direct front-desk consultation scheduler, live pipeline, and quick patient assignment.</p>
        <div class="w-full max-w-md relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
            <input type="text" id="doc-filter-query" oninput="filterDoctorsList()" placeholder="Filter doctor or specialty..." class="w-full text-xs sm:text-sm font-bold border border-slate-200 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white shadow-2xs transition">
        </div>
    </div>
    <div class="w-full md:w-1/3 flex justify-center md:justify-end shrink-0">
        <img src="images/Book Appointment.jpg" class="h-36 sm:h-40 w-auto object-contain drop-shadow-sm rounded-2xl border border-slate-100 p-1 bg-white" alt="Book Appointment">
    </div>
</div>

<!-- ================= AVAILABLE DOCTORS SLIDER ================= -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-3.5">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-doctor text-indigo-600"></i> Available Specialist Doctors
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Showing live OPD availability, consultation hours, and open appointment slots.</p>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="scrollDoctorSlider('book-page-slider', -1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200 shadow-2xs cursor-pointer" title="Previous Doctors">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" onclick="scrollDoctorSlider('book-page-slider', 1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200 shadow-2xs cursor-pointer" title="Next Doctors">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Slider Track -->
    <div id="book-page-slider" class="flex gap-4 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x custom-scrollbar">
        <!-- Loaded via JS in includes/book_popup.php -->
    </div>
</div>

<!-- ================= ALL SPECIALIST DIRECTORY GRID ================= -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-3.5">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-table-cells text-indigo-600"></i> All Hospital Consultants
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive directory of doctors and their departmental schedules.</p>
        </div>
        <span id="book-docs-count-badge" class="px-2.5 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-100">
            Loading...
        </span>
    </div>

    <div id="book-docs-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Loaded via JS in includes/book_popup.php -->
    </div>
</div>

<!-- Include the Complete Direct Booking Popup Modal & Scripts -->
<?php include 'includes/book_popup.php'; ?>

<!-- Script for Copying Patient Portal Link -->
<script>
function copyPatientBookingUrl() {
    const input = document.getElementById('patient-portal-url');
    const btnText = document.getElementById('copy-btn-text');
    const copyBtn = document.getElementById('btn-copy-portal');

    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999); // Mobile devices

    navigator.clipboard.writeText(input.value).then(() => {
        if (btnText) btnText.textContent = 'Copied! ✓';
        if (copyBtn) {
            copyBtn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
            copyBtn.classList.add('bg-indigo-600');
        }

        if (typeof showToast === 'function') {
            showToast('Link Copied!', 'Patient Booking Portal link copied to clipboard. You can now send it to patients.', 'success');
        }

        setTimeout(() => {
            if (btnText) btnText.textContent = 'Copy Link';
            if (copyBtn) {
                copyBtn.classList.remove('bg-indigo-600');
                copyBtn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
            }
        }, 3000);
    }).catch(err => {
        console.error('Failed to copy', err);
        // Fallback
        document.execCommand('copy');
        if (btnText) btnText.textContent = 'Copied! ✓';
    });
}
</script>

<?php include 'includes/footer.php'; ?>

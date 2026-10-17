<?php require_once 'auth.php'; ?>
<?php include 'includes/header.php'; ?>

<style>
  /* ================= DESKTOP FULL-PAGE ZERO-SCROLL ================= */
  @media (min-width: 1024px) {
    html, body {
      height: 100vh;
      overflow: hidden !important;
    }
    main {
      padding-top: 1rem !important;
      padding-bottom: 1rem !important;
      padding-left: 1.5rem !important;
      padding-right: 1.5rem !important;
      height: calc(100vh - 70px) !important;
      max-height: calc(100vh - 70px) !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
      align-items: center !important;
      overflow: hidden !important;
    }
    .fullpage-card {
      height: 100% !important;
      max-height: 630px !important;
    }
  }

  /* Remove default number input spinner arrows */
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
  input[type=number] {
    -moz-appearance: textfield;
  }

  /* Custom dropdown scrollbar */
  .custom-dropdown-scroll::-webkit-scrollbar {
    width: 5px;
  }
  .custom-dropdown-scroll::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-dropdown-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 999px;
  }
  .custom-dropdown-scroll::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
  }
</style>

<div class="w-full max-w-5xl xl:max-w-6xl mx-auto fullpage-card">
  <div class="bg-white rounded-2xl lg:rounded-3xl shadow-sm border border-slate-200 flex flex-col lg:flex-row h-full overflow-hidden">
    
    <!-- ================= LEFT COLUMN: CLEAN IMAGE DISPLAY ONLY ================= -->
    <div class="w-full lg:w-5/12 bg-slate-50 border-b lg:border-b-0 lg:border-r border-slate-200/80 flex items-center justify-center p-6 lg:p-8 shrink-0">
      <div class="w-full max-w-[340px] xl:max-w-[380px] bg-white rounded-2xl p-3 shadow-2xs border border-slate-200/80">
        <img 
          src="images/Patient Registration.jpg" 
          alt="Patient Registration" 
          class="w-full h-auto max-h-[220px] lg:max-h-[260px] object-contain rounded-xl mx-auto"
        >
      </div>
    </div>

    <!-- ================= RIGHT COLUMN: MAIN TITLE & BIG FONT REGISTRATION FORM ================= -->
    <div class="w-full lg:w-7/12 p-6 sm:p-7 lg:p-8 flex flex-col justify-between bg-white relative overflow-y-auto lg:overflow-visible custom-dropdown-scroll">
      
      <!-- Main Title (No extra text) -->
      <div class="pb-3 border-b border-slate-100 shrink-0">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
          Patient Registration
        </h1>
      </div>

      <!-- Registration Form -->
      <form id="register-form" onsubmit="handleRegistrationSubmit(event)" class="space-y-3 sm:space-y-3.5 pt-3 sm:pt-4 flex-1 flex flex-col justify-between">
        
        <!-- Row 1: First Name & Surname -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label for="reg-name" class="block text-sm font-bold text-slate-700 mb-1">
              First Name <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              id="reg-name" 
              required 
              autocomplete="off" 
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition"
            >
          </div>

          <div>
            <label for="reg-surname" class="block text-sm font-bold text-slate-700 mb-1">
              Surname <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              id="reg-surname" 
              required 
              autocomplete="off" 
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition"
            >
          </div>
        </div>

        <!-- Row 2: Father's Name & Phone Number -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label for="reg-father" class="block text-sm font-bold text-slate-700 mb-1">
              Father's Full Name <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              id="reg-father" 
              required 
              autocomplete="off" 
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition"
            >
          </div>

          <div>
            <label for="reg-phone" class="block text-sm font-bold text-slate-700 mb-1">
              Phone Number
            </label>
            <input 
              type="tel" 
              pattern="[0-9]{10}" 
              maxlength="10" 
              inputmode="numeric" 
              title="Please enter exactly 10 digits" 
              id="reg-phone" 
              autocomplete="off" 
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition font-mono"
            >
          </div>
        </div>

        <!-- Row 3: Demographics (Gender, Blood Group, Age) in 3 Columns -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
          
          <!-- Gender Dropdown -->
          <div class="relative" id="gender-container">
            <label class="block text-sm font-bold text-slate-700 mb-1">
              Gender <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="reg-gender" required class="opacity-0 absolute w-0 h-0 pointer-events-none">
            <button 
              type="button" 
              onclick="toggleDropdown('gender')" 
              id="gender-btn"
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition flex items-center justify-between text-left"
            >
              <span id="gender-display" class="text-slate-400 truncate font-medium">Select</span>
              <i id="gender-icon" class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200 shrink-0 ml-1"></i>
            </button>

            <div id="gender-dropdown" class="hidden absolute top-full left-0 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-1.5 transform opacity-0 scale-95 transition-all duration-150 origin-top">
              <div onclick="selectOption('gender', 'Male', 'Male')" class="px-4 py-2.5 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-semibold transition flex items-center gap-2.5">
                <i class="fa-solid fa-mars text-blue-500 text-sm w-4"></i> Male
              </div>
              <div onclick="selectOption('gender', 'Female', 'Female')" class="px-4 py-2.5 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-semibold transition flex items-center gap-2.5">
                <i class="fa-solid fa-venus text-rose-500 text-sm w-4"></i> Female
              </div>
              <div onclick="selectOption('gender', 'Other', 'Other')" class="px-4 py-2.5 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-semibold transition flex items-center gap-2.5">
                <i class="fa-solid fa-transgender text-purple-500 text-sm w-4"></i> Other
              </div>
            </div>
          </div>

          <!-- Blood Group Dropdown -->
          <div class="relative" id="blood-container">
            <label class="block text-sm font-bold text-slate-700 mb-1">
              Blood Group <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="reg-blood" required class="opacity-0 absolute w-0 h-0 pointer-events-none">
            <button 
              type="button" 
              onclick="toggleDropdown('blood')" 
              id="blood-btn"
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition flex items-center justify-between text-left"
            >
              <span id="blood-display" class="text-slate-400 truncate font-medium">Select</span>
              <i id="blood-icon" class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200 shrink-0 ml-1"></i>
            </button>

            <div id="blood-dropdown" class="hidden absolute top-full left-0 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-1.5 transform opacity-0 scale-95 transition-all duration-150 origin-top max-h-52 overflow-y-auto custom-dropdown-scroll">
              <div onclick="selectOption('blood', 'A+', 'A+')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>A+</span> <span class="text-xs text-slate-400 font-normal">Positive</span></div>
              <div onclick="selectOption('blood', 'A-', 'A-')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>A-</span> <span class="text-xs text-slate-400 font-normal">Negative</span></div>
              <div onclick="selectOption('blood', 'B+', 'B+')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>B+</span> <span class="text-xs text-slate-400 font-normal">Positive</span></div>
              <div onclick="selectOption('blood', 'B-', 'B-')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>B-</span> <span class="text-xs text-slate-400 font-normal">Negative</span></div>
              <div onclick="selectOption('blood', 'AB+', 'AB+')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>AB+</span> <span class="text-xs text-slate-400 font-normal">Positive</span></div>
              <div onclick="selectOption('blood', 'AB-', 'AB-')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>AB-</span> <span class="text-xs text-slate-400 font-normal">Negative</span></div>
              <div onclick="selectOption('blood', 'O+', 'O+')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>O+</span> <span class="text-xs text-slate-400 font-normal">Positive</span></div>
              <div onclick="selectOption('blood', 'O-', 'O-')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>O-</span> <span class="text-xs text-slate-400 font-normal">Negative</span></div>
              <div onclick="selectOption('blood', 'Unknown', 'Unknown')" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-800 text-base font-bold transition flex items-center justify-between"><span>Unknown</span> <span class="text-xs text-slate-400 font-normal">N/A</span></div>
            </div>
          </div>

          <!-- Age Input -->
          <div>
            <label for="reg-age" class="block text-sm font-bold text-slate-700 mb-1">
              Age (Years) <span class="text-rose-500">*</span>
            </label>
            <input 
              type="number" 
              id="reg-age" 
              min="0" 
              max="150" 
              required 
              autocomplete="off" 
              class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition font-mono"
            >
          </div>

        </div>

        <!-- Row 4: Emergency Contact -->
        <div class="pt-1">
          <div class="flex items-center gap-3 mb-2">
            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Emergency Contact</span>
            <div class="h-px bg-slate-200 flex-1"></div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label for="reg-em-name" class="block text-sm font-bold text-slate-700 mb-1">
                Emergency Contact Name <span class="text-rose-500">*</span>
              </label>
              <input 
                type="text" 
                id="reg-em-name" 
                required 
                autocomplete="off" 
                class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition"
              >
            </div>

            <div>
              <label for="reg-em-phone" class="block text-sm font-bold text-slate-700 mb-1">
                Emergency Mobile <span class="text-rose-500">*</span>
              </label>
              <input 
                type="tel" 
                pattern="[0-9]{10}" 
                maxlength="10" 
                inputmode="numeric" 
                title="Please enter exactly 10 digits" 
                id="reg-em-phone" 
                required 
                autocomplete="off" 
                class="w-full px-4 py-2.5 sm:py-3 bg-slate-50/50 hover:bg-slate-50 focus:bg-white border border-slate-300 focus:border-slate-900 rounded-xl text-base font-semibold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition font-mono"
              >
            </div>
          </div>
        </div>

        <!-- Action Bar: Bigger Font Buttons -->
        <div class="pt-2 sm:pt-3 flex items-center gap-3">
          <button 
            type="submit" 
            id="btn-register-submit" 
            class="flex-1 bg-slate-900 hover:bg-black active:scale-[0.99] text-white font-extrabold text-base sm:text-lg py-3 sm:py-3.5 px-6 rounded-xl shadow-sm transition flex items-center justify-center gap-2.5 cursor-pointer"
          >
            <i class="fa-solid fa-check text-base"></i>
            <span>Register Patient</span>
          </button>
          
          <button 
            type="button" 
            onclick="resetForm()" 
            class="px-5 py-3 sm:py-3.5 border border-slate-300 hover:border-slate-400 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 active:scale-95 transition text-base font-bold shrink-0 cursor-pointer" 
            title="Clear Form"
          >
            Clear
          </button>
        </div>

      </form>
    </div>

  </div>
</div>

<!-- ================= DUPLICATE WARNING MODAL ================= -->
<div id="modal-duplicate" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-7 shadow-xl relative border border-slate-200 my-8">
    <div class="mb-4">
      <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3 border border-amber-200/60">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <h3 class="text-xl font-black text-slate-900 tracking-tight">Patient Already Registered</h3>
      <p class="text-sm text-slate-500 mt-1">A patient with identical name, surname, and father's name already exists.</p>
    </div>
    
    <div id="duplicate-profile-card" class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 mb-5 text-sm">
      <!-- Injected via JS -->
    </div>

    <div class="flex items-center gap-3">
      <button 
        type="button" 
        onclick="document.getElementById('modal-duplicate').classList.add('hidden')" 
        class="flex-1 px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition"
      >
        Cancel
      </button>
      <button 
        type="button" 
        onclick="showConfirmationModal()" 
        class="flex-1 px-4 py-3 bg-slate-900 hover:bg-black text-white rounded-xl text-sm font-black transition"
      >
        Register Anyway
      </button>
    </div>
  </div>
</div>

<script>
  let currentPayload = null;

  function toggleDropdown(id) {
      const container = document.getElementById(id + '-container');
      const dropdown = document.getElementById(id + '-dropdown');
      const icon = document.getElementById(id + '-icon');
      if (dropdown.classList.contains('hidden')) {
          ['gender', 'blood'].forEach(otherId => {
              if (otherId !== id) closeDropdown(otherId);
          });
          if (container) container.classList.add('z-30');
          dropdown.classList.remove('hidden');
          setTimeout(() => {
              dropdown.classList.remove('opacity-0', 'scale-95');
              if (icon) icon.classList.add('rotate-180');
          }, 10);
      } else {
          closeDropdown(id);
      }
  }

  function closeDropdown(id) {
      const container = document.getElementById(id + '-container');
      const dropdown = document.getElementById(id + '-dropdown');
      const icon = document.getElementById(id + '-icon');
      if (!dropdown) return;
      dropdown.classList.add('opacity-0', 'scale-95');
      if (icon) icon.classList.remove('rotate-180');
      setTimeout(() => {
          dropdown.classList.add('hidden');
          if (container) container.classList.remove('z-30');
      }, 150);
  }

  function selectOption(id, value, labelText) {
      document.getElementById('reg-' + id).value = value;
      const display = document.getElementById(id + '-display');
      if (display) {
          display.textContent = labelText;
          display.className = 'text-slate-900 font-bold truncate';
      }
      closeDropdown(id);
  }

  function resetForm() {
      document.getElementById('register-form').reset();
      
      const genderInp = document.getElementById('reg-gender');
      if (genderInp) genderInp.value = '';
      const genderDisp = document.getElementById('gender-display');
      if (genderDisp) {
          genderDisp.textContent = 'Select';
          genderDisp.className = 'text-slate-400 truncate font-medium';
      }

      const bloodInp = document.getElementById('reg-blood');
      if (bloodInp) bloodInp.value = '';
      const bloodDisp = document.getElementById('blood-display');
      if (bloodDisp) {
          bloodDisp.textContent = 'Select';
          bloodDisp.className = 'text-slate-400 truncate font-medium';
      }
  }

  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
      if (!e.target.closest('#gender-container')) {
          const gd = document.getElementById('gender-dropdown');
          if (gd && !gd.classList.contains('hidden')) closeDropdown('gender');
      }
      if (!e.target.closest('#blood-container')) {
          const bd = document.getElementById('blood-dropdown');
          if (bd && !bd.classList.contains('hidden')) closeDropdown('blood');
      }
  });

  async function handleRegistrationSubmit(e) {
    e.preventDefault();
    
    currentPayload = {
        name: document.getElementById('reg-name').value.trim(),
        surname: document.getElementById('reg-surname').value.trim(),
        father: document.getElementById('reg-father').value.trim(),
        phone: document.getElementById('reg-phone').value.trim(),
        gender: document.getElementById('reg-gender').value,
        blood_group: document.getElementById('reg-blood').value,
        age: document.getElementById('reg-age').value,
        emergency_contact_name: document.getElementById('reg-em-name').value.trim(),
        emergency_contact_phone: document.getElementById('reg-em-phone').value.trim()
    };

    if (!currentPayload.gender) {
      showToast('Validation', 'Please select a gender.', 'error');
      toggleDropdown('gender');
      return;
    }

    if (!currentPayload.blood_group) {
      showToast('Validation', 'Please select a blood group.', 'error');
      toggleDropdown('blood');
      return;
    }

    const btn = document.getElementById('btn-register-submit');
    const originalBtnContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Checking...</span>';
    
    try {
        const res = await fetch('api/patients.php?action=check_duplicate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(currentPayload)
        });
        const data = await res.json();
        
        if (data.status === 'success' && data.duplicates && data.duplicates.length > 0) {
            const p = data.duplicates[0];
            document.getElementById('duplicate-profile-card').innerHTML = `
              <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-200">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center font-black text-sm border border-slate-200">
                  ${(p.name || '').charAt(0)}${(p.surname || '').charAt(0)}
                </div>
                <div>
                  <h4 class="font-extrabold text-slate-900 text-sm">${p.name} ${p.surname}</h4>
                  <p class="text-xs text-slate-500 font-mono font-bold">${p.id}</p>
                </div>
              </div>
              <div class="space-y-1.5 text-sm text-slate-600 font-medium">
                <div class="flex justify-between"><span>Father:</span> <span class="text-slate-900 font-bold">${p.father_name}</span></div>
                <div class="flex justify-between"><span>Phone:</span> <span class="text-slate-900 font-mono">${p.phone || 'N/A'}</span></div>
                <div class="flex justify-between"><span>Blood Group:</span> <span class="text-slate-900 font-bold">${p.blood_group || 'Unknown'}</span></div>
                <div class="flex justify-between"><span>Registered:</span> <span class="text-slate-900">${p.reg_date || 'N/A'}</span></div>
              </div>
            `;
            document.getElementById('modal-duplicate').classList.remove('hidden');
        } else {
            showConfirmationModal();
        }
    } catch (err) { 
        console.error(err);
        showToast('Error', 'Unable to check duplicate profiles.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
    }
  }

  function showConfirmationModal() {
      document.getElementById('modal-duplicate').classList.add('hidden');
      
      const detailsHtml = `
        <div class="bg-slate-50 p-4 rounded-xl space-y-2.5 text-sm">
            <div class="flex justify-between border-b border-slate-200/70 pb-2"><strong class="text-slate-500">Name:</strong> <span class="font-bold text-slate-900">${currentPayload.name} ${currentPayload.surname}</span></div>
            <div class="flex justify-between border-b border-slate-200/70 pb-2"><strong class="text-slate-500">Father's Name:</strong> <span class="font-bold text-slate-900">${currentPayload.father}</span></div>
            <div class="flex justify-between border-b border-slate-200/70 pb-2"><strong class="text-slate-500">Phone:</strong> <span class="font-bold text-slate-900 font-mono">${currentPayload.phone || 'N/A'}</span></div>
            <div class="flex justify-between border-b border-slate-200/70 pb-2"><strong class="text-slate-500">Demographics:</strong> <span class="font-bold text-slate-900">${currentPayload.age} Y, ${currentPayload.gender}, ${currentPayload.blood_group}</span></div>
            <div class="flex justify-between"><strong class="text-slate-500">Emergency Contact:</strong> <span class="font-bold text-slate-900">${currentPayload.emergency_contact_name} (${currentPayload.emergency_contact_phone})</span></div>
        </div>
      `;

      openConfirmModal(
          'Confirm Registration', 
          'Please verify the patient details before saving.', 
          detailsHtml, 
          executeRegistration, 
          'success'
      );
  }

  async function executeRegistration() {
      const btn = document.getElementById('btn-register-submit');
      if (btn) btn.disabled = true;

      try {
          const res = await fetch('api/patients.php?action=create', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify(currentPayload)
          });
          const data = await res.json();
          if (data.status === 'success') {
              resetForm();
              showToast('Success', `Patient registered. MRN: ${data.mrn}`, 'success');
              setTimeout(() => { window.location.href = 'patients.php'; }, 1500);
          } else {
              showToast('Error', data.message || 'Unable to register patient', 'error');
              if (btn) btn.disabled = false;
          }
      } catch (err) { 
          console.error(err); 
          showToast('Error', 'Network error while registering', 'error');
          if (btn) btn.disabled = false;
      }
  }
</script>

<?php include 'includes/footer.php'; ?>

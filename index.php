<?php require_once 'auth.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-slate-200 flex flex-col lg:flex-row">
        
        <!-- Left Side: Image without cutting -->
        <div class="w-full lg:w-1/2 bg-slate-50 flex items-center justify-center p-8 lg:p-12 border-b lg:border-b-0 lg:border-r border-slate-100">
            <img src="images/Patient Registration.jpg" alt="Patient Registration" class="w-full h-auto max-h-[80vh] object-contain drop-shadow-sm rounded-xl">
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-1/2 p-8 sm:p-10 lg:p-14 flex flex-col justify-center bg-white">
            <div class="mb-10">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold mb-4 shadow-sm border border-blue-100">
                    <i class="fa-solid fa-address-card"></i>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">Patient Registration</h2>
                <p class="text-slate-500 font-medium text-sm sm:text-base">Create a new patient account in the hospital system. Please fill in all required details.</p>
            </div>

            <form id="register-form" onsubmit="handleRegistrationSubmit(event)" class="space-y-6">
                <!-- Inputs with Floating labels -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="relative group">
                        <input type="text" id="reg-name" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="First Name">
                        <label for="reg-name" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">First Name *</label>
                    </div>
                    <div class="relative group">
                        <input type="text" id="reg-surname" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Surname">
                        <label for="reg-surname" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Surname *</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="relative group">
                        <input type="text" id="reg-father" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Father's Full Name">
                        <label for="reg-father" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Father's Full Name *</label>
                    </div>
                    <div class="relative group">
                        <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="reg-phone" class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Phone Number">
                        <label for="reg-phone" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Phone Number</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <!-- Custom Gender Dropdown -->
                    <div class="relative group" id="gender-container">
                        <input type="text" id="reg-gender" required class="opacity-0 absolute w-0 h-0 top-1/2 left-1/2">
                        <button type="button" onclick="toggleDropdown('gender')" class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-4 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all text-left relative z-10" id="gender-btn">
                            <span id="gender-display" class="block truncate opacity-0 transition-opacity">Select</span>
                        </button>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-slate-400 z-20"><i class="fa-solid fa-chevron-down text-sm transition-transform duration-300" id="gender-icon"></i></div>
                        <label id="gender-label" class="absolute left-5 top-4 text-sm font-medium tracking-wider text-slate-400 transition-all pointer-events-none z-20" style="transition: all 0.2s ease-out;">Gender *</label>

                        <div id="gender-dropdown" class="hidden absolute top-[calc(100%+0.5rem)] left-0 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2 transform opacity-0 scale-95 transition-all duration-200 origin-top">
                            <div onclick="selectOption('gender', 'Male', this.innerHTML)" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><i class="fa-solid fa-mars text-blue-500 mr-2 w-4"></i> Male</div>
                            <div onclick="selectOption('gender', 'Female', this.innerHTML)" class="px-4 py-2.5 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><i class="fa-solid fa-venus text-rose-500 mr-2 w-4"></i> Female</div>
                            <div onclick="selectOption('gender', 'Other', this.innerHTML)" class="px-4 py-2.5 hover:bg-purple-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><i class="fa-solid fa-transgender text-purple-500 mr-2 w-4"></i> Other</div>
                        </div>
                    </div>
                    
                    <!-- Custom Blood Group Dropdown -->
                    <div class="relative group" id="blood-container">
                        <input type="text" id="reg-blood" required class="opacity-0 absolute w-0 h-0 top-1/2 left-1/2">
                        <button type="button" onclick="toggleDropdown('blood')" class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-4 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all text-left relative z-10" id="blood-btn">
                            <span id="blood-display" class="block truncate opacity-0 transition-opacity">Select</span>
                        </button>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-slate-400 z-20"><i class="fa-solid fa-chevron-down text-sm transition-transform duration-300" id="blood-icon"></i></div>
                        <label id="blood-label" class="absolute left-5 top-4 text-sm font-medium tracking-wider text-slate-400 transition-all pointer-events-none z-20" style="transition: all 0.2s ease-out;">Blood Group *</label>

                        <div id="blood-dropdown" class="hidden absolute top-[calc(100%+0.5rem)] left-0 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2 transform opacity-0 scale-95 transition-all duration-200 origin-top max-h-56 overflow-y-auto custom-scrollbar">
                            <div onclick="selectOption('blood', 'A+', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">A+</span> Positive</div>
                            <div onclick="selectOption('blood', 'A-', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">A-</span> Negative</div>
                            <div onclick="selectOption('blood', 'B+', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">B+</span> Positive</div>
                            <div onclick="selectOption('blood', 'B-', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">B-</span> Negative</div>
                            <div onclick="selectOption('blood', 'AB+', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">AB+</span> Positive</div>
                            <div onclick="selectOption('blood', 'AB-', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">AB-</span> Negative</div>
                            <div onclick="selectOption('blood', 'O+', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">O+</span> Positive</div>
                            <div onclick="selectOption('blood', 'O-', this.innerHTML)" class="px-4 py-2 hover:bg-rose-50 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><span class="w-6 text-center text-rose-600 font-black mr-2">O-</span> Negative</div>
                            <div onclick="selectOption('blood', 'Unknown', this.innerHTML)" class="px-4 py-2 hover:bg-slate-100 cursor-pointer text-slate-700 font-bold rounded-xl mx-2 transition-colors flex items-center"><i class="fa-solid fa-circle-question text-slate-400 mr-2 w-6 text-center"></i> Unknown</div>
                        </div>
                    </div>

                    <div class="relative group">
                        <input type="number" id="reg-age" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Age">
                        <label for="reg-age" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Age *</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-5 border-t border-slate-100">
                    <div class="relative group">
                        <input type="text" id="reg-em-name" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Emergency Contact Name">
                        <label for="reg-em-name" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Emerg. Contact Name *</label>
                    </div>
                    <div class="relative group">
                        <input type="tel" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" id="reg-em-phone" required class="peer w-full h-[3.5rem] bg-slate-50 border border-slate-200 text-slate-900 text-base font-semibold rounded-2xl px-5 pt-5 pb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all placeholder-transparent" placeholder="Emergency Contact Mobile">
                        <label for="reg-em-phone" class="absolute left-5 top-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:text-[10px] peer-focus:font-bold peer-focus:uppercase peer-focus:text-blue-600 cursor-text">Emerg. Contact Mobile *</label>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-lg px-8 py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex justify-center items-center gap-3 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-save"></i> Register Patient Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duplicate Warning Modal -->
<div id="modal-duplicate" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative border border-slate-200 my-8 max-h-[90vh] overflow-y-auto">
    <div class="mb-5 text-center">
      <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-4 border border-amber-100 shadow-inner"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="text-2xl font-black text-slate-900 tracking-tight">Patient Already Exists</h3>
      <p class="text-sm text-slate-500 mt-2 font-medium">A patient with the same name, surname, and father's name is already registered. Please review the existing profile below.</p>
    </div>
    
    <div id="duplicate-profile-card" class="bg-slate-50 rounded-2xl p-5 border border-slate-200 mb-6 shadow-sm">
      <!-- Injected via JS -->
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
      <button onclick="document.getElementById('modal-duplicate').classList.add('hidden')" class="flex-1 px-4 py-3.5 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Cancel Registration</button>
      <button onclick="showConfirmationModal()" class="flex-1 px-4 py-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-amber-200 transition">Register Anyway</button>
    </div>
  </div>
</div>

<script>
  let currentPayload = null;

  function toggleDropdown(id) {
      const dropdown = document.getElementById(id + '-dropdown');
      const icon = document.getElementById(id + '-icon');
      if (dropdown.classList.contains('hidden')) {
          dropdown.classList.remove('hidden');
          setTimeout(() => {
              dropdown.classList.remove('opacity-0', 'scale-95');
              icon.classList.add('rotate-180');
          }, 10);
      } else {
          closeDropdown(id);
      }
  }

  function closeDropdown(id) {
      const dropdown = document.getElementById(id + '-dropdown');
      const icon = document.getElementById(id + '-icon');
      dropdown.classList.add('opacity-0', 'scale-95');
      icon.classList.remove('rotate-180');
      setTimeout(() => {
          dropdown.classList.add('hidden');
      }, 200);
  }

  function selectOption(id, value, html) {
      document.getElementById('reg-' + id).value = value;
      const display = document.getElementById(id + '-display');
      display.innerHTML = html;
      display.classList.remove('opacity-0');
      
      const label = document.getElementById(id + '-label');
      label.style.top = '0.5rem';
      label.style.fontSize = '10px';
      label.style.fontWeight = 'bold';
      label.style.textTransform = 'uppercase';
      
      closeDropdown(id);
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
    
    try {
        const res = await fetch('api/patients.php?action=check_duplicate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(currentPayload)
        });
        const data = await res.json();
        
        if (data.status === 'success' && data.duplicates.length > 0) {
            const p = data.duplicates[0];
            document.getElementById('duplicate-profile-card').innerHTML = `
              <div class="flex items-center gap-4 mb-4 pb-4 border-b border-slate-200">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-lg border border-blue-200/50">
                  ${p.name.charAt(0)}${p.surname.charAt(0)}
                </div>
                <div>
                  <h4 class="font-extrabold text-slate-900 text-base">${p.name} ${p.surname}</h4>
                  <p class="text-xs text-slate-500 font-mono font-bold bg-white px-2 py-0.5 rounded border border-slate-100 mt-1 inline-block">${p.id}</p>
                </div>
              </div>
              <div class="space-y-2 text-sm text-slate-600 font-medium">
                <div class="flex justify-between"><strong class="text-slate-400 uppercase tracking-wider text-[10px]">Father</strong> <span class="text-slate-800">${p.father_name}</span></div>
                <div class="flex justify-between"><strong class="text-slate-400 uppercase tracking-wider text-[10px]">Phone</strong> <span class="text-slate-800">${p.phone || 'N/A'}</span></div>
                <div class="flex justify-between"><strong class="text-slate-400 uppercase tracking-wider text-[10px]">Blood Group</strong> <span class="text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded">🩸 ${p.blood_group || 'Unknown'}</span></div>
                <div class="flex justify-between"><strong class="text-slate-400 uppercase tracking-wider text-[10px]">Registered</strong> <span class="text-slate-800">${p.reg_date}</span></div>
              </div>
            `;
            document.getElementById('modal-duplicate').classList.remove('hidden');
        } else {
            showConfirmationModal();
        }
    } catch (err) { console.error(err); }
  }

  function showConfirmationModal() {
      document.getElementById('modal-duplicate').classList.add('hidden');
      
      const detailsHtml = `
        <div class="bg-slate-50 p-4 rounded-xl space-y-3 text-sm">
            <div class="flex justify-between border-b border-slate-100 pb-2"><strong class="text-slate-500">Name:</strong> <span class="font-bold text-slate-900">${currentPayload.name} ${currentPayload.surname}</span></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><strong class="text-slate-500">Father:</strong> <span class="font-bold text-slate-900">${currentPayload.father}</span></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><strong class="text-slate-500">Phone:</strong> <span class="font-bold text-slate-900">${currentPayload.phone || 'N/A'}</span></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><strong class="text-slate-500">Demographics:</strong> <span class="font-bold text-slate-900">${currentPayload.age} Y, ${currentPayload.gender}, 🩸 ${currentPayload.blood_group}</span></div>
            <div class="flex justify-between"><strong class="text-slate-500">Em. Contact:</strong> <span class="font-bold text-slate-900">${currentPayload.emergency_contact_name} (${currentPayload.emergency_contact_phone})</span></div>
        </div>
      `;

      openConfirmModal(
          'Confirm Registration', 
          'Please verify the details below before saving the patient record.', 
          detailsHtml, 
          executeRegistration, 
          'success'
      );
  }

  async function executeRegistration() {
      try {
          const res = await fetch('api/patients.php?action=create', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify(currentPayload)
          });
          const data = await res.json();
          if (data.status === 'success') {
              document.getElementById('register-form').reset();
              showToast('Registered', `Patient account created. MRN: ${data.mrn}`);
              setTimeout(() => { window.location.href = 'patients.php'; }, 2000);
          } else {
              showToast('Error', data.message, 'error');
          }
      } catch (err) { console.error(err); }
  }
</script>

<?php include 'includes/footer.php'; ?>

<script>
  // --- Booking Modal Logic ---
  let bookingPatientId = null;
  let allBookingDoctors = [];
  let selectedBookingDoctorId = null;

  window.addEventListener('DOMContentLoaded', async () => {
    try {
      const res = await fetch('api/booking.php?action=get_categories');
      const data = await res.json();
      if (data.status === 'success') {
        const catSelect = document.getElementById('book-category');
        if (catSelect) {
            catSelect.innerHTML = '<option value="">All Specialties / Departments</option>';
            data.categories.forEach(d => {
                catSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
        }
      }
    } catch (e) {}
  });

  async function fetchCategoryDoctors(categoryId = '') {
      const slider = document.getElementById('book-doc-slider');
      if (!slider) return;
      slider.innerHTML = '<div class="py-6 text-center text-xs text-slate-400 w-full flex items-center justify-center gap-2"><i class="fa-solid fa-spinner fa-spin text-indigo-500"></i> Loading available doctors...</div>';
      
      try {
          const url = categoryId 
              ? `api/booking.php?action=get_doctors_by_category&category_id=${categoryId}`
              : `api/booking.php?action=get_doctors_by_category`;
          const res = await fetch(url);
          const data = await res.json();
          if (data.status === 'success') {
              allBookingDoctors = data.doctors || [];
              renderBookingDoctorSlider(allBookingDoctors);
          } else {
              slider.innerHTML = `<div class="py-6 text-center text-xs text-rose-500 w-full">${data.message || 'Error loading doctors'}</div>`;
          }
      } catch (e) {
          slider.innerHTML = '<div class="py-6 text-center text-xs text-rose-500 w-full">Error connecting to server.</div>';
      }
  }

  function renderBookingDoctorSlider(doctors) {
      const slider = document.getElementById('book-doc-slider');
      if (!slider) return;
      slider.innerHTML = '';

      if (doctors.length === 0) {
          slider.innerHTML = `
              <div class="py-8 text-center text-slate-400 w-full bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                  <i class="fa-solid fa-user-doctor text-3xl text-slate-300 mb-2 block"></i>
                  <p class="text-xs font-semibold">No doctors available in this specialty.</p>
              </div>
          `;
          selectBookingDoctor('', '', '');
          return;
      }

      doctors.forEach(d => {
          const isSelected = (d.id === selectedBookingDoctorId);
          const initials = (d.name || 'Dr').replace(/^Dr\.?\s*/i, '').substring(0, 2).toUpperCase() || 'DR';
          const cats = (d.categories || []).map(c => `<span class="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-indigo-100">${c}</span>`).join(' ') || '<span class="text-[10px] text-slate-400">General</span>';
          const isAvail = (d.is_available !== false && d.is_available !== 0);
          const availSlots = d.available_count ?? (d.slots || []).length;
          const totalSlots = d.total_slots ?? (d.slots || []).length;
          const timingText = d.today_timing || (isAvail ? '09:00 AM - 05:00 PM' : 'Day Off');

          const card = document.createElement('div');
          card.id = `book-doc-card-${d.id}`;
          card.className = `w-[260px] sm:w-[280px] shrink-0 snap-start p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between select-none ${
              isSelected 
                  ? 'border-indigo-600 ring-2 ring-indigo-600 bg-indigo-50/70 shadow-md' 
                  : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'
          }`;
          
          card.innerHTML = `
              <div>
                  <div class="flex items-start justify-between gap-2 mb-2.5">
                      <div class="flex items-center gap-2.5 min-w-0">
                          <div class="relative shrink-0">
                              <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shadow-inner">
                                  ${initials}
                              </div>
                              <span class="w-2.5 h-2.5 rounded-full ${isAvail ? 'bg-emerald-500' : 'bg-slate-300'} absolute -bottom-0.5 -right-0.5 ring-2 ring-white"></span>
                          </div>
                          <div class="min-w-0">
                              <h4 class="font-extrabold text-slate-900 text-xs truncate" title="${d.name}">${d.name}</h4>
                              <p class="text-[11px] text-slate-500 truncate"><i class="fa-solid fa-phone text-[9px] mr-1 text-slate-400"></i>${d.phone || 'No phone'}</p>
                          </div>
                      </div>
                      <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md ${
                          isSelected ? 'bg-indigo-600 text-white' : 'hidden'
                      }" id="book-doc-check-${d.id}">✓ Selected</span>
                  </div>

                  <div class="mb-2.5 flex flex-wrap gap-1">
                      ${cats}
                  </div>

                  <div class="space-y-1 text-[11px] bg-slate-50/90 rounded-xl p-2 border border-slate-100">
                      <div class="flex items-center justify-between text-slate-600">
                          <span class="font-medium flex items-center gap-1"><i class="fa-regular fa-clock text-slate-400"></i> Hours:</span>
                          <strong class="font-bold ${isAvail ? 'text-slate-800' : 'text-amber-600'}">${timingText}</strong>
                      </div>
                      <div class="flex items-center justify-between text-slate-600">
                          <span class="font-medium flex items-center gap-1"><i class="fa-solid fa-ticket text-slate-400"></i> Free Slots:</span>
                          <strong class="font-bold ${availSlots > 0 ? 'text-emerald-700' : 'text-rose-600'}">${availSlots} / ${totalSlots} available</strong>
                      </div>
                  </div>
              </div>

              <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                  <span class="text-[10px] font-bold ${isAvail ? 'text-emerald-700' : 'text-slate-400'}">
                      <i class="fa-solid fa-circle text-[7px] mr-1"></i>${isAvail ? 'Accepting Patients' : 'Day Off'}
                  </span>
                  <button type="button" class="px-2.5 py-1 rounded-lg text-xs font-bold transition ${
                      isSelected 
                          ? 'bg-indigo-600 text-white shadow-xs' 
                          : 'bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700'
                  }">
                      ${isSelected ? 'Selected' : 'Select'}
                  </button>
              </div>
          `;

          card.onclick = () => selectBookingDoctor(d.id, d.name, `${timingText} • ${availSlots} slots free`);
          slider.appendChild(card);
      });

      // If already a selected doctor in list, make sure it stays active, otherwise default select first available
      if (selectedBookingDoctorId) {
          const found = doctors.find(d => d.id === selectedBookingDoctorId);
          if (found) {
              selectBookingDoctor(found.id, found.name, `${found.today_timing || '09:00 AM - 05:00 PM'} • ${found.available_count ?? 0} slots`);
              return;
          }
      }
      
      if (doctors.length > 0) {
          const first = doctors[0];
          selectBookingDoctor(first.id, first.name, `${first.today_timing || '09:00 AM - 05:00 PM'} • ${first.available_count ?? 0} slots`);
      }
  }

  function selectBookingDoctor(docId, docName, docMeta) {
      selectedBookingDoctorId = docId;
      const input = document.getElementById('book-doctor');
      if (input) input.value = docId;

      // Update cards highlight
      allBookingDoctors.forEach(d => {
          const card = document.getElementById(`book-doc-card-${d.id}`);
          const check = document.getElementById(`book-doc-check-${d.id}`);
          if (card) {
              const isCurr = (d.id === docId);
              card.className = `w-[260px] sm:w-[280px] shrink-0 snap-start p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between select-none ${
                  isCurr 
                      ? 'border-indigo-600 ring-2 ring-indigo-600 bg-indigo-50/70 shadow-md' 
                      : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'
              }`;
              const btn = card.querySelector('button');
              if (btn) {
                  btn.className = isCurr ? 'px-2.5 py-1 rounded-lg text-xs font-bold transition bg-indigo-600 text-white shadow-xs' : 'px-2.5 py-1 rounded-lg text-xs font-bold transition bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700';
                  btn.textContent = isCurr ? 'Selected' : 'Select';
              }
          }
          if (check) {
              check.classList.toggle('hidden', d.id !== docId);
          }
      });

      // Update selected doctor banner
      const banner = document.getElementById('book-selected-doctor-info');
      const nameEl = document.getElementById('book-selected-doctor-name');
      const metaEl = document.getElementById('book-selected-doctor-meta');
      if (banner && nameEl) {
          if (docId) {
              nameEl.textContent = docName;
              if (metaEl) metaEl.textContent = docMeta || '';
              banner.classList.remove('hidden');
          } else {
              banner.classList.add('hidden');
          }
      }
  }

  function scrollDoctorSlider(sliderId, direction) {
      const el = document.getElementById(sliderId);
      if (el) {
          const scrollAmount = 280 * direction;
          el.scrollBy({ left: scrollAmount, behavior: 'smooth' });
      }
  }

  function openBookingModal(id, name, surname) {
    bookingPatientId = id;
    document.getElementById('book-patient-name').textContent = `${name} ${surname} (${id})`;
    document.getElementById('modal-book').classList.remove('hidden');
    // Load available doctors immediately
    const catVal = document.getElementById('book-category') ? document.getElementById('book-category').value : '';
    fetchCategoryDoctors(catVal);
  }

  function closeBookingModal() {
    document.getElementById('modal-book').classList.add('hidden');
  }

  async function submitBooking(e) {
    e.preventDefault();
    const type = document.querySelector('input[name="book-type"]:checked').value;
    const category_id = document.getElementById('book-category').value;
    const doctor_id = document.getElementById('book-doctor').value;
    const symptoms = document.getElementById('book-symptoms').value.trim();
    
    if (!doctor_id) { 
        showToast('Doctor Required', 'Please select a doctor from the doctor cards.', 'error'); 
        return; 
    }

    try {
        const res = await fetch('api/queue.php?action=book_existing', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ patient_id: bookingPatientId, type, dept: category_id, symptoms, doctor_id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeBookingModal();
            document.getElementById('book-symptoms').value = '';
            showToast('Booked', 'Patient added to live queue successfully!');
            if (typeof fetchQueuePipeline === 'function') {
                fetchQueuePipeline(); // update kanban
            }
        } else {
            showToast('Error', data.message, 'error');
        }
    } catch (err) {
        showToast('Error', 'Network error occurred', 'error');
    }
  }
</script>

<!-- BOOKING MODAL (Wider on PC view with interactive Doctor Slider) -->
<div id="modal-book" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
  <div class="bg-white rounded-2xl max-w-3xl lg:max-w-4xl w-full p-5 sm:p-7 shadow-2xl relative border border-slate-200 my-8 max-h-[92vh] overflow-y-auto">
    <button onclick="closeBookingModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition"><i class="fa-solid fa-xmark text-sm"></i></button>
    
    <div class="flex items-center gap-3.5 mb-5 pb-3 border-b border-slate-100">
      <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0 shadow-sm">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div>
        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900">Book Appointment</h3>
        <p class="text-xs text-slate-500 mt-0.5" id="book-patient-name"></p>
      </div>
    </div>
    
    <form onsubmit="submitBooking(event)" class="space-y-4">
      
      <!-- Priority and Specialty Filter Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Consultation Priority *</label>
          <div class="grid grid-cols-2 gap-2.5">
            <label class="border rounded-xl p-2.5 cursor-pointer border-slate-200 bg-slate-50 hover:border-emerald-400 flex items-center gap-2 transition">
              <input type="radio" name="book-type" value="Regular" checked class="text-emerald-600">
              <span class="text-xs font-bold text-slate-800">Regular</span>
            </label>
            <label class="border rounded-xl p-2.5 cursor-pointer border-slate-200 bg-rose-50/40 hover:border-rose-400 flex items-center gap-2 transition">
              <input type="radio" name="book-type" value="Emergency Case" class="text-rose-600">
              <span class="text-xs font-bold text-rose-800">Emergency</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Filter Specialty / Department</label>
          <select id="book-category" onchange="fetchCategoryDoctors(this.value)" class="w-full text-xs font-semibold border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500/50 bg-slate-50 focus:bg-white outline-none shadow-xs">
            <option value="">All Specialties / Departments</option>
          </select>
        </div>
      </div>

      <!-- Doctor Carousel Slider with < and > arrows -->
      <div class="pt-1">
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
              <i class="fa-solid fa-user-doctor text-indigo-600"></i> Available Specialist Doctors *
            </label>
            <p class="text-[11px] text-slate-500">Showing doctor specialty, phone, consultation hours, and free slots.</p>
          </div>
          <div class="flex items-center gap-1.5">
            <button type="button" onclick="scrollDoctorSlider('book-doc-slider', -1)" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200/80 shadow-2xs" title="Previous Doctors">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" onclick="scrollDoctorSlider('book-doc-slider', 1)" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200/80 shadow-2xs" title="Next Doctors">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- Horizontal Scrollable Track -->
        <div id="book-doc-slider" class="flex gap-3 overflow-x-auto pb-2 pt-1 scroll-smooth snap-x custom-scrollbar">
          <!-- Loaded via JS -->
        </div>

        <!-- Hidden input storing selected doctor ID -->
        <input type="hidden" id="book-doctor" required>

        <!-- Selected Doctor Confirmation Banner -->
        <div id="book-selected-doctor-info" class="hidden mt-2 p-3 bg-indigo-50/80 border border-indigo-200 rounded-xl flex items-center justify-between text-xs">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-indigo-600 text-sm"></i>
            <span class="text-slate-700 font-medium">Selected Doctor: <strong id="book-selected-doctor-name" class="text-indigo-900 font-extrabold">Dr. Name</strong></span>
          </div>
          <span id="book-selected-doctor-meta" class="text-indigo-700 text-[11px] font-bold"></span>
        </div>
      </div>

      <!-- Symptoms / Complaints -->
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Reported Symptoms / Complaints *</label>
        <textarea id="book-symptoms" required rows="2" class="w-full text-xs font-medium border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500/50 outline-none" placeholder="Describe current patient symptoms or reason for visit..."></textarea>
      </div>

      <!-- Submit Action -->
      <div class="pt-2">
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-3 text-sm font-black shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-plus-circle"></i> <span>Confirm & Add to Live Queue</span>
        </button>
      </div>
    </form>
  </div>
</div>

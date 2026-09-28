<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 
?>

<!-- Header -->
<div class="mb-6 sm:mb-8 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm flex flex-col md:flex-row gap-6 items-center justify-between">
    <div class="flex-1">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Book Appointment</h2>
        <p class="text-slate-500 mt-1 text-sm font-medium mb-4">Select a specialist below to schedule a new consultation.</p>
        <div class="w-full max-w-md relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
            <input type="text" id="doc-filter-query" oninput="filterDoctorsList()" placeholder="Filter doctor or specialty..." class="w-full text-sm font-semibold border border-slate-200 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white shadow-sm transition">
        </div>
    </div>
    <div class="w-full md:w-1/3 flex justify-center md:justify-end shrink-0">
        <img src="images/Book Appointment.jpg" class="h-40 w-auto object-contain drop-shadow-sm rounded-xl" alt="Book Appointment">
    </div>
</div>

<!-- Available Doctors Slider with < and > Arrows -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-doctor text-indigo-600"></i> Available Specialist Doctors
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Showing live availability, consultation timings, and free appointment slots.</p>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="scrollDoctorSlider('book-page-slider', -1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200 shadow-2xs" title="Previous Doctors">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" onclick="scrollDoctorSlider('book-page-slider', 1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs transition border border-slate-200 shadow-2xs" title="Next Doctors">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Slider Track -->
    <div id="book-page-slider" class="flex gap-4 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x custom-scrollbar">
        <!-- Loaded via JS -->
    </div>
</div>



<?php include 'includes/book_popup.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchDoctorsForBooking();
    });

    function scrollDoctorSlider(sliderId, direction) {
        const el = document.getElementById(sliderId);
        if (el) {
            el.scrollBy({ left: 300 * direction, behavior: 'smooth' });
        }
    }

    async function fetchDoctorsForBooking() {
        try {
            const res = await fetch('api/booking.php?action=get_doctors_by_category');
            const data = await res.json();
            if (data.status === 'success') {
                allAvailableDoctors = data.doctors || [];
                renderDoctorsCards(allAvailableDoctors);
            }
        } catch (e) { console.error(e); }
    }

    function filterDoctorsList() {
        const q = (document.getElementById('doc-filter-query').value || '').toLowerCase().trim();
        if (!q) {
            renderDoctorsCards(allAvailableDoctors);
            return;
        }
        const filtered = allAvailableDoctors.filter(d => {
            const str = \\ \ \\.toLowerCase();
            return str.includes(q);
        });
        renderDoctorsCards(filtered);
    }

    function renderDoctorsCards(docs) {
        const slider = document.getElementById('book-page-slider');
        const container = document.getElementById('book-docs-container');
        const countBadge = document.getElementById('book-docs-count-badge');
        
        if (countBadge) countBadge.textContent = \\ \\;
        if (container) container.innerHTML = '';
        if (slider) slider.innerHTML = '';

        if (docs.length === 0) {
            if (container) container.innerHTML = \<div class="col-span-full text-center text-slate-400 py-12 text-sm">No doctors match your search.</div>\;
            if (slider) slider.innerHTML = \<div class="text-center text-slate-400 py-6 text-xs w-full">No doctors match your search.</div>\;
            return;
        }

        docs.forEach(d => {
            const initials = (d.name || 'Dr').replace(/^Dr\.?\s*/i, '').substring(0, 2).toUpperCase() || 'DR';
            const cats = (d.categories || []).map(c => \<span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded mr-1 mb-1 border border-indigo-100">\</span>\).join('') || '<span class="text-xs text-slate-400 italic">General</span>';
            const isAvail = (d.is_available !== false && d.is_available !== 0);
            const availSlots = d.available_count ?? (d.slots || []).length;
            const totalSlots = d.total_slots ?? (d.slots || []).length;
            const timingText = d.today_timing || (isAvail ? '09:00 AM - 05:00 PM' : 'Day Off');

            if (slider) {
                const sliderCard = document.createElement('div');
                sliderCard.className = "w-[280px] sm:w-[300px] shrink-0 snap-start apple-card p-4 sm:p-5 flex flex-col justify-between border border-slate-200/80 bg-white hover:border-indigo-300 hover:shadow-md transition";
                sliderCard.innerHTML = \
                    <div>
                        <div class="flex items-start gap-3 mb-3">
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black shadow-inner">
                                    \
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full \ absolute -bottom-0.5 -right-0.5 ring-2 ring-white"></span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-extrabold text-slate-900 text-sm truncate" title="\">\</h4>
                                <p class="text-xs text-slate-500 mt-0.5 truncate"><i class="fa-solid fa-phone text-[10px] mr-1 text-slate-400"></i>\</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="flex flex-wrap gap-1">\</div>
                        </div>

                        <div class="space-y-1 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100 mb-3">
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="font-medium flex items-center gap-1"><i class="fa-regular fa-clock text-slate-400"></i> Hours:</span>
                                <strong class="font-bold \">\</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="font-medium flex items-center gap-1"><i class="fa-solid fa-ticket text-slate-400"></i> Free Slots:</span>
                                <strong class="font-bold \">\ / \ available</strong>
                            </div>
                        </div>
                    </div>

                    <button onclick="openDirectBook('\', '\')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-indigo-600/20 transition text-xs flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-calendar-check"></i> Book Consultation
                    </button>
                \;
                slider.appendChild(sliderCard);
            }

            if (container) {
                container.innerHTML += \
                    <div class="apple-card p-5 sm:p-6 flex flex-col justify-between h-full border border-slate-200/80 bg-white hover:border-indigo-300 hover:shadow-md transition">
                        <div>
                            <div class="flex items-start gap-3.5 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner shrink-0 font-bold">
                                    \
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-extrabold text-slate-900 text-base leading-tight truncate">\</h3>
                                    <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> \</p>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Specialties</div>
                                <div>\</div>
                            </div>
                            <div class="text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 mb-4 space-y-1">
                                <div class="flex justify-between text-slate-600">
                                    <span>Hours Today:</span>
                                    <strong class="\">\</strong>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Free Slots:</span>
                                    <strong class="\">\ available</strong>
                                </div>
                            </div>
                        </div>
                        <button onclick="openDirectBook('\', '\')" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl shadow-md transition text-xs sm:text-sm flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-calendar-check"></i> Book Consultation
                        </button>
                    </div>
                \;
            }
        });
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\\\'");
    }
</script>

<?php include 'includes/footer.php'; ?>



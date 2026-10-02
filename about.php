<?php 
require_once 'auth.php'; 
include 'includes/header.php'; 

$hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital & I.C.U';
$user_name = $_SESSION['username'] ?? 'Administrator';
?>

<div class="space-y-8 pb-12 max-w-7xl mx-auto">

    <!-- ========================================================================= -->
    <!-- 1. HERO HEADER: ABOUT BHOOMA MEDICARE HOSPITAL & CLINICAL OS              -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/80 shadow-[0_10px_35px_rgb(0,0,0,0.03)]">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-gradient-to-br from-blue-100/60 via-indigo-100/40 to-teal-100/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-gradient-to-tr from-purple-100/40 to-pink-100/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-2xs">
                        <i class="fa-solid fa-hospital-user text-indigo-600"></i>
                        <span>About Our Institution</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>NABH Accredited &bull; 24/7 Care</span>
                    </span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                    Compassionate Care Powered by Modern Clinical Technology
                </h1>
                
                <p class="text-slate-600 mt-4 text-base sm:text-lg font-normal leading-relaxed">
                    <strong class="font-extrabold text-slate-900"><?php echo htmlspecialchars($hospital_name); ?></strong> is a premier multi-specialty healthcare institution dedicated to providing comprehensive inpatient, outpatient, and intensive emergency clinical services. Integrated with our paperless <strong>Clinical OS</strong>, we deliver synchronized patient triage, real-time queues, and secure electronic health records.
                </p>

                <div class="flex flex-wrap items-center gap-3 mt-6">
                    <a href="guide.php" class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Explore System User Guide</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="dashboard.php" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition-all flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Live Hospital Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Visual Badge Card -->
            <div class="bg-gradient-to-br from-indigo-500 via-indigo-600 to-blue-700 p-7 rounded-3xl text-white shadow-xl shadow-indigo-600/20 max-w-sm w-full shrink-0 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl mb-4 border border-white/30 shadow-inner">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider bg-white/20 px-2.5 py-1 rounded-md text-indigo-100">Care Commitment</span>
                    <h3 class="text-2xl font-black mt-2 leading-tight">24 Hours Emergency &amp; Intensive Care</h3>
                    <p class="text-indigo-100 text-xs mt-2 leading-relaxed">
                        Round-the-clock intensive trauma care, high dependency beds, cardiac monitoring, and dedicated medical specialists on standby.
                    </p>
                </div>
                <div class="relative z-10 pt-5 mt-5 border-t border-white/20 flex items-center justify-between text-xs font-mono font-bold">
                    <span>EMERGENCY HELPLINE</span>
                    <span class="bg-white text-indigo-900 px-2.5 py-1 rounded-lg shadow-sm">108 / +91-9898989898</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. CORE STATS STRIP                                                       -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="apple-card p-6 bg-white border border-slate-200/80 text-center">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div class="text-3xl font-black text-slate-900">15+</div>
            <div class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wider">Expert Consultants</div>
            <p class="text-[11px] text-slate-400 mt-1">Multi-specialty physicians</p>
        </div>

        <div class="apple-card p-6 bg-white border border-slate-200/80 text-center">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                <i class="fa-solid fa-bed-pulse"></i>
            </div>
            <div class="text-3xl font-black text-slate-900">50+</div>
            <div class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wider">Inpatient Ward Beds</div>
            <p class="text-[11px] text-slate-400 mt-1">ICU, HDU &amp; General Wards</p>
        </div>

        <div class="apple-card p-6 bg-white border border-slate-200/80 text-center">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div class="text-3xl font-black text-slate-900">24/7</div>
            <div class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wider">Trauma &amp; ICU Care</div>
            <p class="text-[11px] text-slate-400 mt-1">Uninterrupted medical triage</p>
        </div>

        <div class="apple-card p-6 bg-white border border-slate-200/80 text-center">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mx-auto mb-3 shadow-inner">
                <i class="fa-solid fa-award"></i>
            </div>
            <div class="text-3xl font-black text-slate-900">100%</div>
            <div class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wider">Digital &amp; Paperless</div>
            <p class="text-[11px] text-slate-400 mt-1">Real-time Clinical OS</p>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. MISSION, VISION & ETHICAL PILLARS                                      -->
    <!-- ========================================================================= -->
    <div class="apple-card p-6 sm:p-10 bg-white border border-slate-200/90 shadow-sm space-y-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-bullseye"></i>
                </span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Our Mission &amp; Guiding Principles</h2>
            </div>
            <p class="text-sm text-slate-500 max-w-2xl">
                The foundational healthcare values that guide every consultation, surgery, patient encounter, and technological advancement.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Mission -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-hand-holding-medical"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Clinical Mission</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    To deliver empathetic, high-precision clinical care accessible to everyone, ensuring minimal waiting periods through intelligent digital triage and transparent medical communications.
                </p>
            </div>

            <!-- Vision -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Technological Vision</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    To pioneer healthcare automation by integrating real-time live queues, instant digital prescriptions, and cloud-synced patient medical dossiers that empower physicians and safeguard patient privacy.
                </p>
            </div>

            <!-- Ethical Standard -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Quality &amp; Ethics</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Upholding the strictest clinical safety protocols, NABH-aligned diagnostic hygiene, strict data encryption, and zero medical errors across our pharmacy and ward networks.
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. CLINICAL DEPARTMENTS & SPECIALTIES                                      -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-stethoscope"></i>
                </span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Specialized Clinical Departments</h2>
            </div>
            <p class="text-sm text-slate-500">Comprehensive medical specialties housed within our integrated hospital complex.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Cardiology -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-rose-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Cardiology &amp; CCU</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Advanced ECG, echocardiography, cardiac intensive monitoring, and emergency intervention.</p>
            </div>

            <!-- General Medicine -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">General Medicine</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Comprehensive outpatient consultations, internal medicine, fever diagnostics, and preventive check-ups.</p>
            </div>

            <!-- Emergency & Trauma -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-amber-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Emergency &amp; Trauma</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">24/7 rapid triage desk, acute emergency care, accident trauma resuscitation, and critical stabilization.</p>
            </div>

            <!-- Neurology -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-purple-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Neurology</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Diagnosis and therapeutic management for stroke, neuropathy, epilepsy, and neurological rehabilitation.</p>
            </div>

            <!-- Orthopedics -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-teal-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-bone"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Orthopedics</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Fracture care, bone trauma, joint replacement surgery, spine health, and physical rehabilitation.</p>
            </div>

            <!-- Pediatrics -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-pink-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-baby"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Pediatrics</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Child health wellness, pediatric immunization, neonatal observation, and adolescent medicine.</p>
            </div>

            <!-- Pulmonology -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-cyan-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-lungs"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Pulmonology &amp; Chest</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Respiratory care, asthma management, acute lung infections, and ventilator oxygen therapy.</p>
            </div>

            <!-- Dermatology -->
            <div class="apple-card p-5 bg-white border border-slate-200/80 hover:border-indigo-300 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hand-dots"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Dermatology</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Clinical dermatology, skin infection treatments, allergy testing, and therapeutic skincare.</p>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. ABOUT THE CLINICAL OS: SYSTEM ARCHITECTURE & FEATURES                   -->
    <!-- ========================================================================= -->
    <div class="apple-card p-6 sm:p-10 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl space-y-6 relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-indigo-300 bg-indigo-900/60 px-3 py-1 rounded-full border border-indigo-700/60">
                Hospital Operating System
            </span>
            <h2 class="text-2xl sm:text-3xl font-black mt-3 tracking-tight text-white">
                Engineered for Modern Clinical Workflow &amp; Operational Excellence
            </h2>
            <p class="text-slate-300 text-sm sm:text-base mt-2 leading-relaxed">
                The BHOOMA Hospital Management System is designed to replace cumbersome paper logs with an ultra-responsive, real-time operating workspace. Designed for doctors, nurses, receptionists, and hospital administrators.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 pt-4 relative z-10">
            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-indigo-400 text-xl font-bold"><i class="fa-solid fa-bolt"></i></div>
                <h4 class="font-bold text-white text-base">Live 4-Stage Kanban Pipeline</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Visual token progression from Reception Check-In to Waiting Lounge, Doctor Consulting Room, and Final Discharge.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-emerald-400 text-xl font-bold"><i class="fa-solid fa-chart-line"></i></div>
                <h4 class="font-bold text-white text-base">Interactive Intelligence &amp; Analytics</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Filter appointment trends by doctor and timeline using interactive Chart.js graphs, status doughnuts, and department load charts.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-purple-400 text-xl font-bold"><i class="fa-solid fa-folder-medical"></i></div>
                <h4 class="font-bold text-white text-base">Electronic Medical Vault</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Perpetual patient histories, digital prescriptions, laboratory test files, diagnostic records, and timeline event logs.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-amber-400 text-xl font-bold"><i class="fa-solid fa-clipboard-user"></i></div>
                <h4 class="font-bold text-white text-base">Workforce &amp; Attendance</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Automatic shift tracking, daily attendance rates, duty rosters, and role-based staff authentication.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-rose-400 text-xl font-bold"><i class="fa-solid fa-bed-pulse"></i></div>
                <h4 class="font-bold text-white text-base">Ward &amp; Bed Occupancy</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Real-time bed availability tracking across ICU, High Dependency Units, and General Wards with one-click admissions.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 space-y-2">
                <div class="text-sky-400 text-xl font-bold"><i class="fa-solid fa-database"></i></div>
                <h4 class="font-bold text-white text-base">Disaster Recovery Backups</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Instant one-click SQL database snapshot generation, archive downloads, and safe data restoration protection.</p>
            </div>
        </div>

        <div class="pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400 relative z-10">
            <span>Powered by PHP 8.2 &bull; MySQL &bull; Tailwind CSS &bull; Chart.js</span>
            <a href="guide.php" class="text-indigo-300 hover:text-white font-bold flex items-center gap-1.5 transition">
                <span>View Complete Beginner System Guide</span> <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. CONTACT, LOCATION & EMERGENCY ACCESS                                   -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Emergency Triage Contact -->
        <div class="apple-card p-6 bg-white border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Emergency &amp; Ambulance</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Direct hotline for casualty admission, ICU bed reservation, and emergency ambulance dispatch.
                </p>
                <div class="mt-4 p-3 bg-rose-50/70 border border-rose-200 rounded-xl space-y-1">
                    <div class="text-xs font-bold text-rose-800">24/7 Hotline:</div>
                    <div class="text-lg font-mono font-black text-rose-700">+91 98989 89898 / 108</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Staffed round the clock by emergency coordinators.
            </div>
        </div>

        <!-- OPD & Consultation Desk -->
        <div class="apple-card p-6 bg-white border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">OPD Consultation Hours</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    General Outpatient and specialist consultation timings across morning and evening slots.
                </p>
                <div class="mt-4 p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-1 text-xs text-indigo-900">
                    <div><strong>Morning Slot:</strong> 09:00 AM &ndash; 01:00 PM</div>
                    <div><strong>Evening Slot:</strong> 02:00 PM &ndash; 07:00 PM</div>
                    <div><strong>Emergency Trauma:</strong> Open 24 Hours (All 7 Days)</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Prior booking recommended via the portal.
            </div>
        </div>

        <!-- Campus Location & Address -->
        <div class="apple-card p-6 bg-white border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Hospital Campus</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Centrally located healthcare campus with dedicated ambulance parking and visitor reception.
                </p>
                <div class="mt-4 p-3 bg-teal-50/70 border border-teal-200 rounded-xl space-y-1 text-xs text-teal-900">
                    <div class="font-bold">BHOOMA Medicare Hospital &amp; I.C.U Campus</div>
                    <div class="text-[11px] text-teal-800 leading-relaxed">Opp. Civil Hospital Road, Medical Hub, Ahmedabad, Gujarat, India.</div>
                    <div class="text-[11px] font-mono text-teal-700">info@bhoomamedicare.com</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Wheelchair and stretcher accessible entrance.
            </div>
        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>

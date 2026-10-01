<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['hospital_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Hospital Workspace | CarePulse OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        video::-webkit-media-controls { display: none !important; }
        
        /* Subtle grid background pattern */
        .bg-grid-pattern {
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
        
        /* Hide scrollbar for form column to keep it clean */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 bg-grid-pattern text-slate-800 flex items-center justify-center p-4 sm:p-8">

    <!-- Bento Grid Container -->
    <div class="w-full max-w-[1400px] h-full min-h-[85vh] grid grid-cols-1 lg:grid-cols-3 lg:grid-rows-3 gap-4 sm:gap-6">
        
        <!-- ================= Bento Item 1: Video Showcase (Spans 2 cols, 2 rows) ================= -->
        <div class="lg:col-span-2 lg:row-span-2 bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden relative flex items-center justify-center min-h-[300px] lg:min-h-0">
            <!-- Overlay gradients for premium feel -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute bottom-8 left-8 z-20 pointer-events-none">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-xs font-bold uppercase tracking-wider mb-3">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span> Network Expansion
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Modernize Operations</h2>
                <p class="text-slate-200 mt-1 font-medium">Join the CarePulse OS Network</p>
            </div>
            
            <video 
                id="register-intro-video"
                autoplay loop muted playsinline 
                class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none z-0"
                poster="images/Login.png"
            >
                <source src="video/intro.mp4" type="video/mp4">
            </video>
        </div>

        <!-- ================= Bento Item 2: Register Form (Spans 1 col, 3 rows) ================= -->
        <div class="lg:col-span-1 lg:row-span-3 bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8 sm:p-10 flex flex-col justify-center relative overflow-y-auto no-scrollbar">
            
            <div class="mb-8 text-center sm:text-left">
                <img src="images/Logo.png" alt="Bhooma Medicare Logo" class="h-14 sm:h-16 w-auto object-contain mb-6 mx-auto sm:mx-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 items-center justify-center mb-6 mx-auto sm:mx-0 hidden">
                    <i class="fa-solid fa-building-user text-2xl text-indigo-600"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Create Workspace</h1>
                <p class="text-slate-500 font-medium text-sm">Register your medical facility.</p>
            </div>

            <!-- Error Alert -->
            <div id="error-msg" class="hidden mb-6 bg-red-50 border border-red-100 p-4 rounded-2xl flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                <p id="error-text" class="text-sm text-red-700 font-semibold">Please correct the errors below.</p>
            </div>

            <form id="register-form" onsubmit="handleRegister(event)" class="space-y-6">
                
                <!-- Facility Name Input (Floating Label on Border) -->
                <div class="relative mt-2">
                    <input 
                        type="text" 
                        id="reg-name" 
                        required 
                        autofocus
                        class="block w-full px-4 py-4 bg-white border-2 border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:ring-0 focus:border-indigo-600 transition-all outline-none peer appearance-none shadow-sm"
                        placeholder=" "
                    >
                    <label 
                        for="reg-name" 
                        class="absolute text-sm text-slate-500 font-bold duration-300 transform -translate-y-1/2 scale-75 top-0 z-10 origin-[0] bg-white px-2 left-3 peer-focus:text-indigo-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:scale-75 peer-focus:-translate-y-1/2 peer-focus:top-0 pointer-events-none">
                        Facility Name
                    </label>
                </div>

                <!-- Admin Username Input (Floating Label on Border) -->
                <div class="relative mt-2">
                    <input 
                        type="text" 
                        id="reg-username" 
                        required 
                        class="block w-full px-4 py-4 bg-white border-2 border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:ring-0 focus:border-indigo-600 transition-all outline-none peer appearance-none shadow-sm"
                        placeholder=" "
                    >
                    <label 
                        for="reg-username" 
                        class="absolute text-sm text-slate-500 font-bold duration-300 transform -translate-y-1/2 scale-75 top-0 z-10 origin-[0] bg-white px-2 left-3 peer-focus:text-indigo-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:scale-75 peer-focus:-translate-y-1/2 peer-focus:top-0 pointer-events-none">
                        Admin Username
                    </label>
                </div>

                <!-- Password Input (Floating Label on Border) -->
                <div class="relative mt-2">
                    <input 
                        type="password" 
                        id="reg-password" 
                        required 
                        minlength="8"
                        maxlength="20"
                        class="block w-full pl-4 pr-10 py-4 bg-white border-2 border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:ring-0 focus:border-indigo-600 transition-all outline-none peer appearance-none shadow-sm"
                        placeholder=" "
                    >
                    <label 
                        for="reg-password" 
                        class="absolute text-sm text-slate-500 font-bold duration-300 transform -translate-y-1/2 scale-75 top-0 z-10 origin-[0] bg-white px-2 left-3 peer-focus:text-indigo-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:scale-75 peer-focus:-translate-y-1/2 peer-focus:top-0 pointer-events-none">
                        Password
                    </label>
                    <button type="button" onclick="togglePassword('reg-password', 'reg-eye-icon')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 outline-none">
                        <i id="reg-eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <!-- Confirm Password Input (Floating Label on Border) -->
                <div class="relative mt-2">
                    <input 
                        type="password" 
                        id="reg-confirm-password" 
                        required 
                        minlength="8"
                        maxlength="20"
                        class="block w-full pl-4 pr-10 py-4 bg-white border-2 border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:ring-0 focus:border-indigo-600 transition-all outline-none peer appearance-none shadow-sm"
                        placeholder=" "
                    >
                    <label 
                        for="reg-confirm-password" 
                        class="absolute text-sm text-slate-500 font-bold duration-300 transform -translate-y-1/2 scale-75 top-0 z-10 origin-[0] bg-white px-2 left-3 peer-focus:text-indigo-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:scale-75 peer-focus:-translate-y-1/2 peer-focus:top-0 pointer-events-none">
                        Confirm Password
                    </label>
                    <button type="button" onclick="togglePassword('reg-confirm-password', 'reg-confirm-eye-icon')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 outline-none">
                        <i id="reg-confirm-eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <div class="pt-2 mb-4">
                    <label class="flex items-start gap-2 cursor-pointer text-sm text-slate-600 font-medium select-none">
                        <input type="checkbox" required class="mt-0.5 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded-md">
                        <span>I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-700 font-bold">Terms</a> & <a href="#" class="text-indigo-600 hover:text-indigo-700 font-bold">Privacy</a>.</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="btn-reg-submit"
                    class="w-full py-4 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-lg shadow-slate-900/20 transition-all duration-200 flex justify-center items-center gap-2 active:scale-[0.98]">
                    <span>Create Account</span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm font-medium text-slate-500">
                    Already have an account? 
                    <a href="login.php" class="font-bold text-indigo-600 hover:text-indigo-700 transition-colors">Sign In</a>
                </p>
            </div>
        </div>

        <!-- ================= Bento Item 3: Info/Brand Card (Spans 1 col, 1 row) ================= -->
        <div class="lg:col-span-1 lg:row-span-1 bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8 flex flex-col justify-between">
            <div class="flex items-center gap-4">
                <img src="images/Logo.png" alt="Logo" class="h-10 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center hidden">
                    <i class="fa-solid fa-building-user text-indigo-600"></i>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <span class="text-slate-500 font-medium text-sm">Enterprise Registration</span>
            </div>
            <p class="text-slate-600 text-sm font-medium mt-4 lg:mt-0">
                Setup your administrative workspace in seconds. Begin managing beds, queueing patients, and adding doctors instantly.
            </p>
        </div>

        <!-- ================= Bento Item 4: Stats/Feature Card (Spans 1 col, 1 row) ================= -->
        <div class="lg:col-span-1 lg:row-span-1 bg-indigo-600 text-white rounded-[2rem] shadow-md shadow-indigo-500/20 p-8 flex flex-col justify-center relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold mb-1">24/7</h3>
                    <p class="text-indigo-100 font-medium text-sm">Premium Support</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <i class="fa-solid fa-headset text-xl"></i>
                </div>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const vid = document.getElementById('register-intro-video');
            if(vid) {
                vid.muted = true;
                const playVid = () => {
                    const p = vid.play();
                    if(p !== undefined) {
                        p.catch(() => { vid.muted = true; vid.play(); });
                    }
                };
                playVid();
                vid.addEventListener('ended', () => { vid.currentTime = 0; playVid(); });
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && vid.paused) playVid();
                });
            }
        });

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-slate-600';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye text-slate-400';
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-reg-submit');
            const errBox = document.getElementById('error-msg');
            const errText = document.getElementById('error-text');

            const name = document.getElementById('reg-name').value.trim();
            const username = document.getElementById('reg-username').value.trim();
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-confirm-password').value;

            if (password.length < 8 || password.length > 20) {
                errText.textContent = "Password must be between 8 and 20 characters.";
                errBox.classList.remove('hidden');
                document.getElementById('reg-password').focus();
                return;
            }

            if (password !== confirmPassword) {
                errText.textContent = "Passwords do not match. Please try again.";
                errBox.classList.remove('hidden');
                document.getElementById('reg-confirm-password').focus();
                return;
            }
            
            const originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            errBox.classList.add('hidden');

            const payload = {
                name: name,
                username: username,
                password: password
            };

            try {
                const res = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                
                const raw = await res.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (parseErr) {
                    throw new Error("Server error. Please contact support or try again later.");
                }

                if (data.status === 'success') {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Account Created!';
                    btn.classList.remove('bg-slate-900', 'hover:bg-slate-800', 'shadow-slate-900/20');
                    btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-500/30');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 500);
                } else {
                    errText.textContent = data.message || "Registration failed. Username might be taken.";
                    errBox.classList.remove('hidden');
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                }
            } catch (error) {
                errText.textContent = error.message || "Network error occurred.";
                errBox.classList.remove('hidden');
                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        }
    </script>
</body>
</html>

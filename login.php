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
    <title>Hospital Staff Portal | Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        /* Completely hide native video controls and overlays */
        video::-webkit-media-controls,
        video::-webkit-media-controls-enclosure,
        video::-webkit-media-controls-panel {
            display: none !important;
            opacity: 0 !important;
            -webkit-appearance: none !important;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col lg:flex-row antialiased text-slate-800">

    <!-- ================= LEFT HALF: Full Image Showcase without Any Cut ================= -->
    <div class="w-full lg:w-1/2 min-h-[380px] lg:min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 flex flex-col justify-center items-center p-6 sm:p-8 lg:p-12 relative overflow-hidden border-b lg:border-b-0 lg:border-r border-slate-800">
        
        <!-- Ambient lighting glows -->
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Video Container: Plays 1080p intro.mp4 without cutting/cropping -->
        <div class="relative z-10 w-full max-w-xl xl:max-w-2xl 2xl:max-w-3xl flex flex-col items-center justify-center my-auto">
            <div class="w-full bg-slate-900/90 rounded-2xl sm:rounded-3xl p-2 sm:p-3.5 border border-white/10 shadow-2xl backdrop-blur-md transition duration-300 hover:border-white/20">
                <div class="relative w-full aspect-video rounded-xl sm:rounded-2xl overflow-hidden bg-black shadow-lg flex items-center justify-center select-none" oncontextmenu="return false;">
                    <video 
                        id="login-intro-video"
                        autoplay 
                        loop 
                        muted 
                        playsinline 
                        preload="auto"
                        disablepictureinpicture
                        disableremoteplayback
                        class="w-full h-full object-contain mx-auto block pointer-events-none select-none"
                        poster="images/Login.png"
                    >
                        <source src="video/intro.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= RIGHT HALF: Clean Modern Login Interface ================= -->
    <div class="w-full lg:w-1/2 min-h-screen bg-white flex flex-col justify-between p-6 sm:p-10 lg:p-14 xl:p-16 overflow-y-auto">
        
        <!-- Top Bar with Official Logo -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <img src="images/Logo.png" alt="Bhooma Hospital Logo" class="h-10 sm:h-12 w-auto max-w-[220px] object-contain">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Staff Access
            </div>
        </div>

        <!-- Center Form Area -->
        <div class="w-full max-w-md mx-auto my-8 sm:my-auto py-4">
            
            <!-- Title Header -->
            <div class="mb-8">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm border border-blue-100 mb-4">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Staff Sign In</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5">Enter your authorized credentials to access clinical dashboard, patient queue, and bed status.</p>
            </div>

            <!-- Error Alert Box -->
            <div id="error-msg" class="hidden mb-5 text-xs text-rose-700 font-bold bg-rose-50 border border-rose-200 p-3.5 rounded-2xl flex items-center gap-3 transition shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                <span id="error-text">Network error occurred.</span>
            </div>

            <!-- Form -->
            <form id="login-form" onsubmit="handleLogin(event)" class="space-y-4">
                <!-- Username Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 ml-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-hospital-user text-blue-600"></i> Hospital Username
                    </label>
                    <div class="relative">
                        <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="text" 
                            id="log-username" 
                            required 
                            autofocus
                            class="w-full bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition shadow-sm placeholder-slate-400" 
                            placeholder="Enter registered username">
                    </div>
                </div>

                <!-- Password Field with Visibility Toggle -->
                <div>
                    <div class="flex items-center justify-between mb-2 ml-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-key text-blue-600"></i> Password (Min 6 Characters)
                        </label>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="password" 
                            id="log-password" 
                            required 
                            minlength="6"
                            class="w-full bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200 rounded-2xl pl-11 pr-12 py-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition shadow-sm placeholder-slate-400" 
                            placeholder="••••••••">
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility('log-password', 'eye-icon')" 
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 p-1.5 rounded-lg transition"
                            title="Toggle password visibility">
                            <i id="eye-icon" class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Options Row -->
                <div class="flex items-center justify-between pt-1 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-medium select-none">
                        <input type="checkbox" id="remember-me" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        Remember session
                    </label>
                    <span class="text-slate-400 text-[11px] font-medium flex items-center gap-1">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i> Secure Node
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        id="btn-submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition duration-200 flex items-center justify-center gap-2 text-sm active:scale-[0.99]">
                        <span>Sign In to Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Bottom Footer Link -->
        <div class="border-t border-slate-100 pt-6 text-center">
            <p class="text-xs sm:text-sm font-medium text-slate-500">
                Register a new hospital branch? 
                <a href="register_hospital.php" class="text-blue-600 hover:text-blue-700 font-bold ml-1 inline-flex items-center gap-1">
                    Create Workspace <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </p>
            <p class="text-[11px] text-slate-400 mt-2">Bhooma Medicare Hospital & I.C.U • Version 2.6</p>
        </div>

    </div>

    <script>
        // Ensure seamless continuous video looping without stopping or showing controls
        window.addEventListener('DOMContentLoaded', () => {
            const introVid = document.getElementById('login-intro-video');
            if (introVid) {
                introVid.muted = true;
                introVid.defaultMuted = true;
                const playPromise = () => {
                    const p = introVid.play();
                    if (p !== undefined) {
                        p.catch(() => {
                            introVid.muted = true;
                            introVid.play();
                        });
                    }
                };
                playPromise();

                // Explicit loop guarantee on ended
                introVid.addEventListener('ended', () => {
                    introVid.currentTime = 0;
                    playPromise();
                });

                // Resume if paused or tab switched back
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && introVid.paused) {
                        playPromise();
                    }
                });

                window.addEventListener('click', () => {
                    if (introVid.paused) playPromise();
                }, { once: true });
            }
        });

        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-sm text-blue-600';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye text-sm text-slate-400';
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit');
            const errBox = document.getElementById('error-msg');
            const errText = document.getElementById('error-text');
            const password = document.getElementById('log-password').value;

            if (password.length < 6) {
                errText.textContent = "Password must be at least 6 characters long.";
                errBox.classList.remove('hidden');
                return;
            }
            
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Authenticating...';
            btn.disabled = true;
            errBox.classList.add('hidden');

            const payload = {
                username: document.getElementById('log-username').value.trim(),
                password: password
            };

            try {
                const res = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                
                const raw = await res.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (parseErr) {
                    console.error("Auth Raw Response:", raw);
                    throw new Error("Invalid response from server. Please check database connection.");
                }

                if (data.status === 'success') {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Success! Redirecting...';
                    btn.className = 'w-full bg-emerald-600 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-emerald-500/25 transition duration-200 flex items-center justify-center gap-2 text-sm';
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 350);
                } else {
                    errText.textContent = data.message || "Invalid username or password.";
                    errBox.classList.remove('hidden');
                    btn.innerHTML = '<span>Sign In to Dashboard</span> <i class="fa-solid fa-arrow-right text-xs"></i>';
                    btn.disabled = false;
                }
            } catch (error) {
                errText.textContent = error.message || "Network error occurred. Please try again.";
                errBox.classList.remove('hidden');
                btn.innerHTML = '<span>Sign In to Dashboard</span> <i class="fa-solid fa-arrow-right text-xs"></i>';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>

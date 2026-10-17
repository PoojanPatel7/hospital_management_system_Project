<?php
// includes/ai_dashboard_control.php
// Interactive AI Model & Ollama Engine Controller Card for Admin Dashboard
?>
<div class="apple-card p-6 sm:p-8 bg-white border border-slate-200/90 rounded-[32px] shadow-sm relative overflow-hidden group transition-all duration-300">
    <!-- Ambient Subtle Background Glow -->
    <div class="absolute -right-24 -top-24 w-80 h-80 bg-gradient-to-br from-indigo-100/40 via-purple-100/30 to-blue-100/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-emerald-100/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 space-y-6">
        <!-- ================= TOP HEADER & MASTER CONTROLS ================= -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-brain text-[11px]"></i> Neural AI Subsystem
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-microchip text-indigo-500"></i> NVIDIA RTX 4060 (8GB VRAM)
                    </span>
                    <span id="ai-master-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Checking Telemetry...
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight flex items-center gap-2.5">
                    <span>AI Model &amp; Ollama Service Hub</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-2xl mt-1">
                    Control local Ollama inference service, warm up the fine-tuned hospital model into GPU VRAM, and trigger high-speed queries.
                </p>
            </div>

            <!-- Master Action Controls -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- MASTER BUTTON: START BOTH -->
                <button 
                    type="button" 
                    id="btn-start-both" 
                    onclick="handleStartBothEngines()" 
                    class="px-5 py-3 rounded-2xl bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 active:scale-95 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-indigo-600/30 flex items-center gap-2.5 transition-all cursor-pointer group"
                    title="Start both Ollama daemon and pre-load trained hospital AI model into GPU memory"
                >
                    <i class="fa-solid fa-bolt-lightning text-amber-300 text-sm group-hover:rotate-12 transition-transform"></i>
                    <span id="btn-start-both-text">Start Both (LLM + Trained Model)</span>
                </button>

                <!-- AI LAB LINK -->
                <a 
                    href="ai_training_dashboard.php" 
                    class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold text-xs flex items-center gap-2 shadow-xs transition cursor-pointer"
                    title="Open full AI Training Lab & Loss Curves"
                >
                    <i class="fa-solid fa-chart-line text-emerald-400"></i>
                    <span>AI Lab</span>
                </a>

                <!-- REFRESH BUTTON -->
                <button 
                    type="button" 
                    onclick="refreshAiTelemetry(true)" 
                    class="p-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    title="Refresh AI Status"
                >
                    <i class="fa-solid fa-arrows-rotate text-sm" id="ai-refresh-icon"></i>
                </button>
            </div>
        </div>

        <!-- ================= TWO ENGINE CONTROLLERS (GRID) ================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            
            <!-- ENGINE 1: OLLAMA LLM SERVICE DAEMON -->
            <div class="p-5 sm:p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl flex flex-col justify-between space-y-4 hover:border-indigo-300 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold shrink-0">
                                <i class="fa-solid fa-server"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Ollama LLM Engine</h3>
                                <p class="text-[12px] text-slate-500 font-medium">Local Daemon Service (Port 11434)</p>
                            </div>
                        </div>
                        <span id="ollama-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Checking...
                        </span>
                    </div>

                    <!-- Telemetry Details -->
                    <div class="grid grid-cols-3 gap-2 py-3 px-3.5 bg-white rounded-xl border border-slate-200/60 text-xs">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</div>
                            <div id="ollama-telemetry-state" class="font-extrabold text-slate-800 text-[13px] mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Latency</div>
                            <div id="ollama-telemetry-latency" class="font-mono font-bold text-slate-800 text-[13px] mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Installed</div>
                            <div id="ollama-telemetry-count" class="font-bold text-slate-800 text-[13px] mt-0.5">--</div>
                        </div>
                    </div>
                </div>

                <!-- Ollama Action Buttons -->
                <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60">
                    <button 
                        type="button" 
                        id="btn-ollama-start" 
                        onclick="handleStartOllama()" 
                        class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Power ON Ollama</span>
                    </button>
                    <button 
                        type="button" 
                        id="btn-ollama-stop" 
                        onclick="handleStopOllama()" 
                        class="py-2.5 px-4 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer"
                        title="Stop Ollama background daemon"
                    >
                        <i class="fa-solid fa-power-off text-xs"></i>
                        <span>Stop</span>
                    </button>
                </div>
            </div>

            <!-- ENGINE 2: TRAINED HOSPITAL AI MODEL (hms-ai:latest) -->
            <div class="p-5 sm:p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl flex flex-col justify-between space-y-4 hover:border-purple-300 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl font-bold shrink-0">
                                <i class="fa-solid fa-brain"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Trained Model: <span class="text-indigo-600 font-mono">hms-ai</span></h3>
                                <p class="text-[12px] text-slate-500 font-medium">Fine-Tuned Hospital Clinical Engine</p>
                            </div>
                        </div>
                        <span id="model-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Checking...
                        </span>
                    </div>

                    <!-- Telemetry Details -->
                    <div class="grid grid-cols-3 gap-2 py-3 px-3.5 bg-white rounded-xl border border-slate-200/60 text-xs">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">GPU VRAM</div>
                            <div id="model-telemetry-vram" class="font-extrabold text-purple-700 text-[13px] mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Base LLM</div>
                            <div class="font-bold text-slate-800 text-[13px] mt-0.5">Qwen 3.1B</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Domain</div>
                            <div class="font-bold text-slate-800 text-[13px] mt-0.5">34 Tables</div>
                        </div>
                    </div>
                </div>

                <!-- Model Action Buttons -->
                <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60">
                    <button 
                        type="button" 
                        id="btn-model-load" 
                        onclick="handleLoadModel()" 
                        class="flex-1 py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-fire text-amber-300 text-xs"></i>
                        <span>Load &amp; Warmup Model</span>
                    </button>
                    <button 
                        type="button" 
                        id="btn-model-unload" 
                        onclick="handleUnloadModel()" 
                        class="py-2.5 px-4 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer"
                        title="Unload model from GPU memory"
                    >
                        <i class="fa-solid fa-snowflake text-xs text-sky-500"></i>
                        <span>Unload</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="if(window.BhoomaAI) BhoomaAI.toggle();" 
                        class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer"
                        title="Open AI Chatbot popup"
                    >
                        <i class="fa-solid fa-comments text-xs text-teal-400"></i>
                        <span>Chat</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- ================= LIVE INFERENCE QUICK-TEST BAR ================= -->
        <div class="pt-2">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-slate-50 p-2 sm:p-2.5 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-2 pl-2 text-indigo-600 shrink-0">
                    <i class="fa-solid fa-vial-virus text-base"></i>
                    <span class="text-xs font-extrabold text-slate-700 hidden sm:inline">Live Inference Test:</span>
                </div>
                <input 
                    type="text" 
                    id="ai-quick-test-input" 
                    placeholder="Ask a question (e.g. 'How many available beds?' or 'Discharge bed ICU-02')..." 
                    class="flex-1 bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none focus:border-indigo-500 transition"
                    onkeydown="if(event.key==='Enter') executeQuickAiTest()"
                />
                <button 
                    type="button" 
                    id="btn-ai-quick-test" 
                    onclick="executeQuickAiTest()" 
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-xs cursor-pointer shrink-0"
                >
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Execute</span>
                </button>
            </div>

            <!-- Instant Test Result Drawer (Hidden by default) -->
            <div id="ai-quick-test-drawer" class="hidden mt-3 p-4 bg-slate-900 text-white rounded-2xl text-xs space-y-2 border border-slate-800 shadow-xl transition-all">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-mono font-bold text-emerald-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> <span id="test-result-latency">0 ms latency</span>
                    </span>
                    <button onclick="document.getElementById('ai-quick-test-drawer').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="test-result-content" class="text-slate-200 font-sans leading-relaxed whitespace-pre-wrap"></div>
                <div id="test-result-sql-wrapper" class="hidden pt-2 border-t border-slate-800/80">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-teal-400 mb-1">Generated SQL Query:</div>
                    <code id="test-result-sql" class="block font-mono bg-slate-950 p-2.5 rounded-xl text-teal-300 text-[11px] overflow-x-auto border border-slate-800"></code>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Real-time AI Subsystem Management Logic
let aiTelemetryState = {
    ollama_online: false,
    model_loaded: false,
    latency_ms: null,
    vram_size: null,
    models: []
};

// Initial Fetch on Dashboard Load
document.addEventListener('DOMContentLoaded', () => {
    refreshAiTelemetry(false);
    // Periodic polling every 12 seconds
    setInterval(() => {
        refreshAiTelemetry(false);
    }, 12000);
});

async function refreshAiTelemetry(showToastNotice = false) {
    const refreshIcon = document.getElementById('ai-refresh-icon');
    if (refreshIcon) refreshIcon.classList.add('fa-spin');

    try {
        const res = await fetch('api/ai_train_api.php?action=status');
        const data = await res.json();

        if (data.success) {
            aiTelemetryState.ollama_online = Boolean(data.ollama_online);
            aiTelemetryState.model_loaded = Boolean(data.model_loaded_in_vram);
            aiTelemetryState.latency_ms = data.latency_ms;
            aiTelemetryState.vram_size = data.model_vram_size;
            aiTelemetryState.models = data.active_models || [];

            updateAiControlUI();

            if (showToastNotice && typeof showToast === 'function') {
                showToast('AI Status Updated', aiTelemetryState.ollama_online ? `Ollama Active (${data.latency_ms || 15}ms) • ${aiTelemetryState.model_loaded ? 'Model in VRAM' : 'Model Standby'}` : 'Ollama Daemon is Offline', aiTelemetryState.ollama_online ? 'success' : 'info');
            }
        }
    } catch (e) {
        console.warn('AI Telemetry Fetch Failed:', e);
    } finally {
        if (refreshIcon) refreshIcon.classList.remove('fa-spin');
    }
}

function updateAiControlUI() {
    const masterBadge = document.getElementById('ai-master-badge');
    const startBothBtn = document.getElementById('btn-start-both');
    const startBothText = document.getElementById('btn-start-both-text');

    const ollamaBadge = document.getElementById('ollama-status-badge');
    const ollamaState = document.getElementById('ollama-telemetry-state');
    const ollamaLatency = document.getElementById('ollama-telemetry-latency');
    const ollamaCount = document.getElementById('ollama-telemetry-count');
    const btnOllamaStart = document.getElementById('btn-ollama-start');
    const btnOllamaStop = document.getElementById('btn-ollama-stop');

    const modelBadge = document.getElementById('model-status-badge');
    const modelVram = document.getElementById('model-telemetry-vram');
    const btnModelLoad = document.getElementById('btn-model-load');
    const btnModelUnload = document.getElementById('btn-model-unload');

    // 1. Ollama UI State
    if (aiTelemetryState.ollama_online) {
        ollamaBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs";
        ollamaBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online';
        ollamaState.textContent = 'Active (11434)';
        ollamaState.className = 'font-extrabold text-emerald-600 text-[13px] mt-0.5';
        ollamaLatency.textContent = (aiTelemetryState.latency_ms ? aiTelemetryState.latency_ms + ' ms' : '< 10 ms');
        ollamaCount.textContent = (aiTelemetryState.models.length + ' Model' + (aiTelemetryState.models.length === 1 ? '' : 's'));

        btnOllamaStart.classList.add('opacity-50', 'pointer-events-none');
        btnOllamaStart.innerHTML = '<i class="fa-solid fa-check text-xs"></i> <span>Ollama Running</span>';
        btnOllamaStop.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        ollamaBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs";
        ollamaBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-rose-500"></span> Offline';
        ollamaState.textContent = 'Stopped';
        ollamaState.className = 'font-extrabold text-rose-600 text-[13px] mt-0.5';
        ollamaLatency.textContent = '--';
        ollamaCount.textContent = '0 Models';

        btnOllamaStart.classList.remove('opacity-50', 'pointer-events-none');
        btnOllamaStart.innerHTML = '<i class="fa-solid fa-play text-xs"></i> <span>Power ON Ollama</span>';
        btnOllamaStop.classList.add('opacity-50', 'pointer-events-none');
    }

    // 2. Model UI State
    if (aiTelemetryState.model_loaded) {
        modelBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs";
        modelBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span> Loaded in VRAM';
        modelVram.textContent = aiTelemetryState.vram_size || '2.01 GB';
        modelVram.className = 'font-extrabold text-purple-700 text-[13px] mt-0.5';

        btnModelLoad.classList.add('opacity-50', 'pointer-events-none');
        btnModelLoad.innerHTML = '<i class="fa-solid fa-check text-xs"></i> <span>Model Warm in VRAM</span>';
        btnModelUnload.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        modelBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs";
        modelBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500"></span> Standby / Unloaded';
        modelVram.textContent = 'Standby (0 GB)';
        modelVram.className = 'font-extrabold text-slate-500 text-[13px] mt-0.5';

        btnModelLoad.classList.remove('opacity-50', 'pointer-events-none');
        btnModelLoad.innerHTML = '<i class="fa-solid fa-fire text-amber-300 text-xs"></i> <span>Load &amp; Warmup Model</span>';
        btnModelUnload.classList.add('opacity-50', 'pointer-events-none');
    }

    // 3. Combined Master State
    if (aiTelemetryState.ollama_online && aiTelemetryState.model_loaded) {
        masterBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200";
        masterBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Both Engines Active & Ready';

        startBothBtn.className = "px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-emerald-600/25 flex items-center gap-2.5 transition-all cursor-pointer group";
        startBothText.textContent = "Both Active (Reload)";
    } else if (aiTelemetryState.ollama_online) {
        masterBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200";
        masterBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-indigo-500"></span> Ollama Online • Model Standby';

        startBothBtn.className = "px-5 py-3 rounded-2xl bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 active:scale-95 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-indigo-600/30 flex items-center gap-2.5 transition-all cursor-pointer group";
        startBothText.textContent = "Load Trained Model";
    } else {
        masterBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200";
        masterBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-rose-400"></span> Engines Offline';

        startBothBtn.className = "px-5 py-3 rounded-2xl bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 active:scale-95 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-indigo-600/30 flex items-center gap-2.5 transition-all cursor-pointer group";
        startBothText.textContent = "Start Both (LLM + Trained Model)";
    }
}

// Handler: Start Both Engines
async function handleStartBothEngines() {
    const btn = document.getElementById('btn-start-both');
    const text = document.getElementById('btn-start-both-text');
    const originalText = text.textContent;

    btn.disabled = true;
    text.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Starting Both Engines...';

    if (typeof showToast === 'function') {
        showToast('Starting Engines', 'Powering on Ollama server and preloading trained clinical model into RTX 4060 GPU...', 'info');
    }

    try {
        const res = await fetch('api/ai_train_api.php?action=start_both');
        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Engines Ready! 🚀', 'Ollama is online and trained model (hms-ai:latest) is loaded in GPU VRAM.', 'success');
            }
            await refreshAiTelemetry(false);
        } else {
            if (typeof showToast === 'function') {
                showToast('Startup Error', data.error || 'Could not start engines automatically.', 'error');
            }
        }
    } catch (e) {
        if (typeof showToast === 'function') {
            showToast('Connection Error', 'Network or server issue starting AI engines.', 'error');
        }
    } finally {
        btn.disabled = false;
        text.textContent = originalText;
    }
}

// Handler: Start Ollama Only
async function handleStartOllama() {
    const btn = document.getElementById('btn-ollama-start');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Starting...</span>';

    try {
        const res = await fetch('api/ai_train_api.php?action=start_ollama');
        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Ollama Started', 'Ollama daemon server is now running on port 11434.', 'success');
            }
            await refreshAiTelemetry(false);
        } else {
            if (typeof showToast === 'function') {
                showToast('Failed to Start', data.error || 'Check if Ollama is installed on this PC.', 'error');
            }
        }
    } catch (e) {
        if (typeof showToast === 'function') {
            showToast('Error', 'Unable to reach backend API.', 'error');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        updateAiControlUI();
    }
}

// Handler: Stop Ollama
async function handleStopOllama() {
    if (!confirm('Are you sure you want to stop the Ollama LLM service? This will close any active model sessions.')) return;

    const btn = document.getElementById('btn-ollama-stop');
    btn.disabled = true;

    try {
        const res = await fetch('api/ai_train_api.php?action=stop_ollama');
        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Ollama Stopped', 'Ollama LLM engine has been safely shut down.', 'info');
            }
            await refreshAiTelemetry(false);
        }
    } catch (e) {
        console.error(e);
    } finally {
        btn.disabled = false;
        updateAiControlUI();
    }
}

// Handler: Load / Warmup Model
async function handleLoadModel() {
    const btn = document.getElementById('btn-model-load');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Loading into GPU...</span>';

    if (typeof showToast === 'function') {
        showToast('Warming Up Model', 'Pinning hms-ai:latest into NVIDIA RTX 4060 VRAM for zero-latency queries...', 'info');
    }

    try {
        const res = await fetch('api/ai_train_api.php?action=start_model&model=hms-ai:latest');
        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Model Loaded! ⚡', `hms-ai:latest loaded into GPU (${data.vram_size || '2.01 GB'}) in ${Math.round(data.load_duration_ms/1000)}s.`, 'success');
            }
            await refreshAiTelemetry(false);
        } else {
            if (typeof showToast === 'function') {
                showToast('Model Load Error', data.error || 'Could not load model.', 'error');
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        updateAiControlUI();
    }
}

// Handler: Unload Model
async function handleUnloadModel() {
    const btn = document.getElementById('btn-model-unload');
    btn.disabled = true;

    try {
        const res = await fetch('api/ai_train_api.php?action=unload_model&model=hms-ai:latest');
        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Model Unloaded', 'GPU memory released. Ollama daemon remains active.', 'info');
            }
            await refreshAiTelemetry(false);
        }
    } catch (e) {
        console.error(e);
    } finally {
        btn.disabled = false;
        updateAiControlUI();
    }
}

// Handler: Live Quick Test Inference
async function executeQuickAiTest() {
    const input = document.getElementById('ai-quick-test-input');
    const prompt = (input ? input.value : '').trim();
    if (!prompt) {
        if (typeof showToast === 'function') showToast('Input Required', 'Please enter a test clinical prompt.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-ai-quick-test');
    const drawer = document.getElementById('ai-quick-test-drawer');
    const latencyEl = document.getElementById('test-result-latency');
    const contentEl = document.getElementById('test-result-content');
    const sqlWrapper = document.getElementById('test-result-sql-wrapper');
    const sqlEl = document.getElementById('test-result-sql');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Testing...</span>';

    try {
        const formData = new FormData();
        formData.append('prompt', prompt);

        const res = await fetch('api/ai_train_api.php?action=test_prompt', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        drawer.classList.remove('hidden');
        latencyEl.textContent = `${data.latency_ms || 180} ms • ${data.model || 'hms-ai:latest'}`;
        contentEl.textContent = data.response || 'No response returned.';

        if (data.sql) {
            sqlWrapper.classList.remove('hidden');
            sqlEl.textContent = data.sql;
        } else {
            sqlWrapper.classList.add('hidden');
        }
    } catch (e) {
        drawer.classList.remove('hidden');
        latencyEl.textContent = 'Error';
        contentEl.textContent = 'Error executing prompt. Please ensure Ollama and model are running.';
        sqlWrapper.classList.add('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> <span>Execute</span>';
    }
}
</script>

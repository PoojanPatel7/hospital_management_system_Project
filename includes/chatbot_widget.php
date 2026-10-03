<?php
// includes/chatbot_widget.php

// Attempt to load prompt templates for suggestions
if (file_exists(__DIR__ . '/../ai/prompt_templates.php')) {
    require_once __DIR__ . '/../ai/prompt_templates.php';
} else {
    if (!function_exists('getQuickSuggestions')) {
        function getQuickSuggestions($page) {
            return [
                "Show me today's appointments",
                "How many patients are admitted?",
                "List available doctors"
            ];
        }
    }
}

$chatbot_page = basename($_SERVER['PHP_SELF']);
$chatbot_suggestions = json_encode(function_exists('getQuickSuggestions') ? getQuickSuggestions($chatbot_page) : []);
$chatbot_hospital_name = $_SESSION['hospital_name'] ?? 'BHOOMA';
$chatbot_user_role = $_SESSION['staff_role'] ?? 'Admin';
?>

<style>
/* ======================================================== */
/* BHOOMA AI CLINICAL ASSISTANT - PROFESSIONAL ANIMATION STYLES */
/* ======================================================== */

@keyframes bhooma-panel-spring {
    0% {
        opacity: 0;
        transform: translateY(28px) scale(0.92);
    }
    60% {
        opacity: 1;
        transform: translateY(-4px) scale(1.015);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes bhooma-panel-exit {
    0% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    100% {
        opacity: 0;
        transform: translateY(18px) scale(0.94);
    }
}

@keyframes bhooma-ring-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.65), 0 10px 25px -5px rgba(79, 70, 229, 0.5);
    }
    70% {
        box-shadow: 0 0 0 14px rgba(79, 70, 229, 0), 0 10px 25px -5px rgba(79, 70, 229, 0.3);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(79, 70, 229, 0), 0 10px 25px -5px rgba(79, 70, 229, 0.3);
    }
}

@keyframes bhooma-msg-enter {
    0% {
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes bhooma-typing-wave {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.45;
    }
    30% {
        transform: translateY(-6px);
        opacity: 1;
    }
}

@keyframes bhooma-shimmer-anim {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes bhooma-mic-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
        transform: scale(1.08);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        transform: scale(1);
    }
}

.bhooma-chat-panel {
    animation: bhooma-panel-spring 0.36s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transform-origin: bottom right;
}

.bhooma-chat-panel.bhooma-closing {
    animation: bhooma-panel-exit 0.22s cubic-bezier(0.4, 0, 1, 1) forwards;
}

.bhooma-btn-pulse {
    animation: bhooma-ring-pulse 2.4s infinite;
}

.bhooma-msg-anim {
    animation: bhooma-msg-enter 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.bhooma-typing-dot {
    animation: bhooma-typing-wave 1.2s infinite ease-in-out;
}
.bhooma-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.bhooma-typing-dot:nth-child(3) { animation-delay: 0.4s; }

.bhooma-shimmer {
    background: linear-gradient(90deg, rgba(241,245,249,0) 0%, rgba(224,231,255,0.7) 50%, rgba(241,245,249,0) 100%);
    background-size: 200% 100%;
    animation: bhooma-shimmer-anim 1.8s infinite;
}

.bhooma-mic-active {
    animation: bhooma-mic-pulse 1.3s infinite ease-in-out !important;
}

/* Custom Scrollbar for Chat */
.bhooma-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.bhooma-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.bhooma-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.bhooma-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Expanded Workstation Mode */
#bhooma-ai-panel.bhooma-expanded {
    width: 920px !important;
    height: 86vh !important;
    max-width: 96vw !important;
    max-height: 94vh !important;
}

/* Mobile Responsive Adaptation */
@media (max-width: 640px) {
    #bhooma-ai-panel {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        bottom: 0 !important;
        right: 0 !important;
        left: 0 !important;
        top: 0 !important;
        border-radius: 0 !important;
    }
}

.bhooma-markdown p { margin-bottom: 0.45rem; }
.bhooma-markdown p:last-child { margin-bottom: 0; }
.bhooma-markdown strong { font-weight: 700; color: #1e293b; }
.bhooma-markdown ul { list-style-type: disc; padding-left: 1.2rem; margin-bottom: 0.4rem; }
.bhooma-markdown ol { list-style-type: decimal; padding-left: 1.2rem; margin-bottom: 0.4rem; }
.bhooma-markdown code { background: rgba(99, 102, 241, 0.08); color: #4338ca; padding: 0.15rem 0.35rem; border-radius: 0.3rem; font-family: monospace; font-size: 0.88em; }
.bhooma-markdown pre code { display: block; padding: 0.6rem; overflow-x: auto; margin-bottom: 0.5rem; background: #0f172a; color: #f8fafc; border-radius: 0.5rem; }

/* Executive Callout Style */
.bhooma-callout {
    background: linear-gradient(135deg, rgba(238, 242, 255, 0.95), rgba(240, 253, 250, 0.95));
    border-left: 4px solid #4f46e5;
    border-radius: 0 0.75rem 0.75rem 0;
    padding: 0.65rem 0.85rem;
    margin: 0.45rem 0;
    color: #1e1b4b;
    font-size: 0.83rem;
    line-height: 1.45;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
</style>

<!-- Floating AI Button Container -->
<div id="bhooma-ai-btn-container" class="fixed bottom-6 right-6" style="z-index: 999999 !important;">
    <button id="bhooma-ai-toggle" type="button" title="BHOOMA AI Clinical Assistant (Ctrl+K)" 
        class="bhooma-btn-pulse flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-teal-500 text-white shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
        <i class="fa-solid fa-robot text-2xl pointer-events-none"></i>
        <span id="bhooma-ai-badge" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
            1
        </span>
    </button>
</div>

<!-- Main Chat Panel -->
<div id="bhooma-ai-panel" class="hidden fixed bottom-24 right-6 w-[470px] h-[670px] max-h-[86vh] max-w-[calc(100vw-2rem)] rounded-2xl shadow-2xl border border-slate-200/90 bg-white/95 backdrop-blur-xl flex flex-col overflow-hidden sm:right-6 right-0 left-0 mx-auto sm:mx-0 sm:left-auto transition-all duration-300 relative" style="display: none; z-index: 999999 !important;">
    
    <!-- Top Header -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-600 to-teal-500 p-3.5 text-white flex items-center justify-between cursor-move select-none shrink-0 shadow-xs" id="bhooma-ai-header">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center relative shadow-xs">
                <i class="fa-solid fa-robot text-sm"></i>
                <div id="bhooma-ai-status-dot" class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-indigo-700 ring-1 ring-emerald-300" title="Online"></div>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <h3 class="font-extrabold text-sm tracking-tight leading-tight">BHOOMA AI</h3>
                    <span class="text-[9px] bg-white/20 font-bold px-1.5 py-0.5 rounded uppercase tracking-wider" id="bhooma-ai-model">hms-ai</span>
                </div>
                <div class="flex items-center space-x-1">
                    <span id="bhooma-ai-status-text" class="text-[11px] text-white/80 font-medium">Ready</span>
                </div>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center space-x-1">
            <button id="bhooma-ai-history-btn" type="button" class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/15 hover:bg-white/25 text-white text-xs font-semibold transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98" title="Chat History">
                <i class="fa-solid fa-clock-rotate-left text-xs pointer-events-none"></i>
                <span class="pointer-events-none">History</span>
            </button>
            <button id="bhooma-ai-sound-btn" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="Toggle Sound Chimes">
                <i id="bhooma-ai-sound-icon" class="fa-solid fa-volume-high pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-export-btn" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="Export Conversation (.md)">
                <i class="fa-solid fa-arrow-down-to-bracket pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-new" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="New Conversation">
                <i class="fa-solid fa-plus pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-expand" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="Toggle Fullscreen/Workstation Mode">
                <i id="bhooma-ai-expand-icon" class="fa-solid fa-expand pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-minimize" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="Minimize">
                <i class="fa-solid fa-minus pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-close" type="button" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-xs text-white cursor-pointer" title="Close">
                <i class="fa-solid fa-xmark pointer-events-none"></i>
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SLIDE-OUT CONVERSATION HISTORY DRAWER                    -->
    <!-- ======================================================== -->
    <div id="bhooma-ai-history-drawer" class="absolute inset-0 top-[56px] bg-slate-50 z-40 flex flex-col transition-all duration-300 transform translate-x-full hidden shadow-2xl">
        <!-- Drawer Header -->
        <div class="p-3 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <button id="bhooma-history-back" type="button" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center text-sm transition-colors cursor-pointer" title="Back to Chat">
                    <i class="fa-solid fa-arrow-left pointer-events-none"></i>
                </button>
                <div>
                    <h4 class="font-bold text-sm text-slate-800 leading-tight">Conversation History</h4>
                    <span class="text-[10px] text-slate-400">Past chats and inquiries</span>
                </div>
            </div>
            <button id="bhooma-history-new-btn" type="button" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer">
                <i class="fa-solid fa-plus text-[10px] pointer-events-none"></i> New Chat
            </button>
        </div>
        <!-- Search Input -->
        <div class="p-2.5 bg-white border-b border-slate-100 shrink-0">
            <div class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs pointer-events-none"></i>
                <input type="text" id="bhooma-history-search" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400 text-slate-700" placeholder="Search past conversations...">
            </div>
        </div>
        <!-- Conversation List -->
        <div id="bhooma-history-list" class="flex-1 overflow-y-auto bhooma-scrollbar p-3 space-y-2">
            <!-- Dynamically populated by JS -->
        </div>
    </div>

    <!-- Suggestions Bar -->
    <div id="bhooma-ai-suggestions" class="bg-slate-50 border-b border-slate-100 p-2 flex overflow-x-auto bhooma-scrollbar gap-2 hide-when-chatting shrink-0">
        <!-- populated by JS -->
    </div>

    <!-- Messages Area & Floating Scroll-To-Bottom Button -->
    <div class="flex-1 relative overflow-hidden flex flex-col">
        <div id="bhooma-ai-messages" class="flex-1 overflow-y-auto bhooma-scrollbar p-4 space-y-3.5 bg-slate-50/50">
            <!-- Initial Welcome Message populated by JS renderWelcomeMessage() -->
        </div>
        
        <!-- Floating Scroll to Bottom Button -->
        <button id="bhooma-ai-scroll-bottom" type="button" 
            class="hidden absolute bottom-3 right-4 bg-white/95 backdrop-blur-md text-indigo-600 border border-indigo-200 shadow-lg rounded-full px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 hover:bg-indigo-50 hover:scale-105 active:scale-95 transition-all z-20 cursor-pointer">
            <i class="fa-solid fa-chevron-down text-[11px] pointer-events-none"></i>
            <span class="pointer-events-none">Latest</span>
            <span id="bhooma-ai-unread-count" class="hidden bg-indigo-600 text-white text-[10px] px-1.5 py-0.2 rounded-full font-bold">1</span>
        </button>
    </div>

    <!-- Input Area -->
    <div class="border-t border-slate-200 bg-white p-3 relative shrink-0">
        <!-- Live Voice Dictation Wave Bar -->
        <div id="bhooma-ai-voice-indicator" class="hidden mb-2 px-3 py-1.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between text-xs text-rose-700 font-semibold shadow-2xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-ping"></span>
                <span>Listening... Speak your hospital request</span>
            </div>
            <button id="bhooma-ai-voice-cancel" type="button" class="text-rose-600 hover:text-rose-800 text-[11px] font-bold underline cursor-pointer">Stop</button>
        </div>

        <!-- Live As-You-Type Suggestions Floating Popup -->
        <div id="bhooma-ai-live-suggestions" class="absolute bottom-full left-3 right-3 mb-2 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-xl overflow-hidden hidden z-50 divide-y divide-slate-100 max-h-56 overflow-y-auto bhooma-scrollbar">
            <!-- populated dynamically by JS as you type -->
        </div>

        <div class="relative flex items-end bg-slate-50 border border-slate-300 rounded-xl focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all shadow-2xs">
            <textarea id="bhooma-ai-input" rows="1" 
                class="w-full bg-transparent border-0 focus:ring-0 resize-none max-h-[120px] text-sm p-3 bhooma-scrollbar text-slate-800 placeholder-slate-400" 
                placeholder="Ask BHOOMA AI anything... (Enter to send)"></textarea>
            
            <!-- Voice Dictation Mic Button -->
            <button id="bhooma-ai-mic" type="button" class="shrink-0 w-8 h-8 mb-2 mr-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-200/60 flex items-center justify-center transition-all cursor-pointer" title="Speak to BHOOMA AI (Voice Dictation)">
                <i class="fa-solid fa-microphone text-sm pointer-events-none"></i>
            </button>

            <!-- Send Button -->
            <button id="bhooma-ai-send" type="button" class="shrink-0 w-8 h-8 mb-2 mr-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer shadow-2xs" title="Send message">
                <i class="fa-solid fa-paper-plane text-xs pointer-events-none"></i>
            </button>
        </div>
        <div class="flex justify-between items-center mt-1.5 px-1">
            <div class="text-[10px] text-slate-400">Press <kbd class="bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-slate-600 font-mono text-[9px]">Enter</kbd> to send, <kbd class="bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-slate-600 font-mono text-[9px]">Shift+Enter</kbd> for newline</div>
            <div id="bhooma-ai-counter" class="text-[10px] text-slate-400 hidden">0/2000</div>
        </div>
    </div>
</div>

<script>
const BhoomaAI = {
    conversationId: null,
    isOpen: false,
    isLoading: false,
    _toggling: false,
    messages: [],
    currentPage: '<?php echo addslashes($chatbot_page); ?>',
    suggestions: <?php echo $chatbot_suggestions; ?>,
    apiUrl: 'api/chatbot.php',
    
    // Modern Feature States
    soundEnabled: localStorage.getItem('bhooma_ai_sound') !== '0',
    audioCtx: null,
    speechRecognition: null,
    isListening: false,
    unreadMessagesCount: 0,

    liveIndex: -1,
    activeSuggestions: [],
    liveDebounceTimer: null,
    liveDictionary: [
        // Staff & Attendance
        { text: "Who is present today in staff?", cat: "Staff", icon: "fa-users", color: "bg-blue-100 text-blue-700" },
        { text: "Show absent staff members today", cat: "Staff", icon: "fa-user-xmark", color: "bg-rose-100 text-rose-700" },
        { text: "Mark all staff members present today", cat: "Attendance", icon: "fa-clipboard-check", color: "bg-emerald-100 text-emerald-700" },
        { text: "Mark attendance present for staff", cat: "Attendance", icon: "fa-user-check", color: "bg-emerald-100 text-emerald-700" },
        { text: "List all staff members and designations", cat: "Staff", icon: "fa-id-badge", color: "bg-blue-100 text-blue-700" },
        { text: "Check today's staff attendance overview", cat: "Attendance", icon: "fa-calendar-day", color: "bg-teal-100 text-teal-700" },
        
        // Beds & Ward
        { text: "Show available beds by ward", cat: "Beds", icon: "fa-bed", color: "bg-indigo-100 text-indigo-700" },
        { text: "How many beds are currently occupied?", cat: "Beds", icon: "fa-bed-pulse", color: "bg-amber-100 text-amber-700" },
        { text: "List all ICU beds and status", cat: "Beds", icon: "fa-heart-pulse", color: "bg-rose-100 text-rose-700" },
        { text: "Discharge patient and free bed", cat: "Beds", icon: "fa-door-open", color: "bg-emerald-100 text-emerald-700" },
        { text: "Assign bed to patient", cat: "Beds", icon: "fa-user-plus", color: "bg-indigo-100 text-indigo-700" },

        // Appointments & Doctors
        { text: "Show appointments scheduled for today", cat: "Appointments", icon: "fa-calendar-check", color: "bg-purple-100 text-purple-700" },
        { text: "List upcoming appointments this week", cat: "Appointments", icon: "fa-calendar-days", color: "bg-purple-100 text-purple-700" },
        { text: "List active doctors and their specialties", cat: "Doctors", icon: "fa-user-doctor", color: "bg-cyan-100 text-cyan-700" },
        { text: "Which doctors are available today?", cat: "Doctors", icon: "fa-stethoscope", color: "bg-cyan-100 text-cyan-700" },
        { text: "Book an appointment for patient", cat: "Appointments", icon: "fa-calendar-plus", color: "bg-purple-100 text-purple-700" },
        { text: "Cancel appointment for patient", cat: "Appointments", icon: "fa-calendar-xmark", color: "bg-rose-100 text-rose-700" },

        // Patients & Queue
        { text: "Search patient by name or phone", cat: "Patients", icon: "fa-hospital-user", color: "bg-emerald-100 text-emerald-700" },
        { text: "How many patients are registered total?", cat: "Patients", icon: "fa-users-line", color: "bg-emerald-100 text-emerald-700" },
        { text: "Show current OPD patient queue", cat: "Queue", icon: "fa-clock", color: "bg-amber-100 text-amber-700" },
        { text: "Register a new patient", cat: "Patients", icon: "fa-user-plus", color: "bg-emerald-100 text-emerald-700" },

        // Prescriptions & Medical
        { text: "View recent prescriptions issued", cat: "Pharmacy", icon: "fa-pills", color: "bg-teal-100 text-teal-700" },
        { text: "Search diagnoses for patient", cat: "Clinical", icon: "fa-notes-medical", color: "bg-blue-100 text-blue-700" },

        // Analytics & Hospital Overview
        { text: "Hospital overview dashboard metrics", cat: "Analytics", icon: "fa-chart-pie", color: "bg-indigo-100 text-indigo-700" },
        { text: "How many patients visited today?", cat: "Analytics", icon: "fa-chart-line", color: "bg-indigo-100 text-indigo-700" }
    ],

    init() {
        this.cacheDOM();

        // Reparent button container and panel to document.body so no parent container clipping/transform affects them
        if (this.dom.btnContainer && this.dom.btnContainer.parentElement !== document.body) {
            document.body.appendChild(this.dom.btnContainer);
        }
        if (this.dom.panel && this.dom.panel.parentElement !== document.body) {
            document.body.appendChild(this.dom.panel);
        }

        // Restore workstation expansion state
        if (localStorage.getItem('bhooma_ai_expanded') === '1') {
            if (this.dom.panel) this.dom.panel.classList.add('bhooma-expanded');
            if (this.dom.expandIcon) this.dom.expandIcon.className = 'fa-solid fa-compress pointer-events-none';
            if (this.dom.expandBtn) this.dom.expandBtn.title = 'Restore Window Mode';
        }

        // Update sound icon to match saved preference
        if (this.dom.soundIcon) {
            this.dom.soundIcon.className = this.soundEnabled 
                ? 'fa-solid fa-volume-high pointer-events-none' 
                : 'fa-solid fa-volume-xmark pointer-events-none text-rose-300';
        }
        if (this.dom.soundBtn) {
            this.dom.soundBtn.title = this.soundEnabled ? 'Sound Enabled (Click to Mute)' : 'Sound Muted (Click to Unmute)';
        }

        this.bindEvents();
        this.loadConversation();
        this.renderSuggestions();
        this.checkStatus();
        if (this.dom.input) this.autoResize(this.dom.input);
    },
    
    cacheDOM() {
        this.dom = {
            btnContainer: document.getElementById('bhooma-ai-btn-container'),
            toggle: document.getElementById('bhooma-ai-toggle'),
            panel: document.getElementById('bhooma-ai-panel'),
            close: document.getElementById('bhooma-ai-close'),
            minimize: document.getElementById('bhooma-ai-minimize'),
            newBtn: document.getElementById('bhooma-ai-new'),
            expandBtn: document.getElementById('bhooma-ai-expand'),
            expandIcon: document.getElementById('bhooma-ai-expand-icon'),
            soundBtn: document.getElementById('bhooma-ai-sound-btn'),
            soundIcon: document.getElementById('bhooma-ai-sound-icon'),
            exportBtn: document.getElementById('bhooma-ai-export-btn'),
            scrollBottomBtn: document.getElementById('bhooma-ai-scroll-bottom'),
            unreadCount: document.getElementById('bhooma-ai-unread-count'),
            voiceIndicator: document.getElementById('bhooma-ai-voice-indicator'),
            voiceCancel: document.getElementById('bhooma-ai-voice-cancel'),
            micBtn: document.getElementById('bhooma-ai-mic'),
            historyBtn: document.getElementById('bhooma-ai-history-btn'),
            historyDrawer: document.getElementById('bhooma-ai-history-drawer'),
            historyBack: document.getElementById('bhooma-history-back'),
            historyNewBtn: document.getElementById('bhooma-history-new-btn'),
            historySearch: document.getElementById('bhooma-history-search'),
            historyList: document.getElementById('bhooma-history-list'),
            messages: document.getElementById('bhooma-ai-messages'),
            input: document.getElementById('bhooma-ai-input'),
            send: document.getElementById('bhooma-ai-send'),
            suggestions: document.getElementById('bhooma-ai-suggestions'),
            liveSuggestions: document.getElementById('bhooma-ai-live-suggestions'),
            badge: document.getElementById('bhooma-ai-badge'),
            statusDot: document.getElementById('bhooma-ai-status-dot'),
            statusText: document.getElementById('bhooma-ai-status-text'),
            counter: document.getElementById('bhooma-ai-counter')
        };
    },
    
    bindEvents() {
        if (this.dom.toggle) {
            this.dom.toggle.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggle();
            };
        }
        if (this.dom.close) {
            this.dom.close.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggle(false);
            };
        }
        if (this.dom.minimize) {
            this.dom.minimize.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggle(false);
            };
        }
        if (this.dom.newBtn) {
            this.dom.newBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.newConversation();
            };
        }
        if (this.dom.expandBtn) {
            this.dom.expandBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggleExpand();
            };
        }
        if (this.dom.historyBtn) {
            this.dom.historyBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggleHistoryDrawer();
            };
        }
        if (this.dom.historyBack) {
            this.dom.historyBack.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.closeHistoryDrawer();
            };
        }
        if (this.dom.historyNewBtn) {
            this.dom.historyNewBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.closeHistoryDrawer();
                this.newConversation();
            };
        }
        if (this.dom.historySearch) {
            this.dom.historySearch.oninput = (e) => {
                this.filterHistoryList(e.target.value);
            };
        }
        if (this.dom.soundBtn) {
            this.dom.soundBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggleSound();
            };
        }
        if (this.dom.exportBtn) {
            this.dom.exportBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.exportConversation();
            };
        }
        if (this.dom.scrollBottomBtn) {
            this.dom.scrollBottomBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.scrollToBottom(true);
            };
        }
        if (this.dom.micBtn) {
            this.dom.micBtn.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.toggleVoiceInput();
            };
        }
        if (this.dom.voiceCancel) {
            this.dom.voiceCancel.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.stopVoiceInput();
            };
        }
        if (this.dom.messages) {
            this.dom.messages.onscroll = () => {
                this.handleScroll();
            };
        }
        if (this.dom.send) {
            this.dom.send.onclick = (e) => {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                this.handleSend();
            };
        }
        
        if (this.dom.input) {
            this.dom.input.onkeydown = (e) => {
                // Handle live suggestions navigation with keyboard
                if (this.dom.liveSuggestions && !this.dom.liveSuggestions.classList.contains('hidden') && this.activeSuggestions.length > 0) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.liveIndex = (this.liveIndex + 1) % this.activeSuggestions.length;
                        this.highlightLiveSuggestion();
                        return;
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.liveIndex = (this.liveIndex - 1 + this.activeSuggestions.length) % this.activeSuggestions.length;
                        this.highlightLiveSuggestion();
                        return;
                    }
                    if (e.key === 'Enter' || e.key === 'Tab') {
                        if (this.liveIndex >= 0 && this.liveIndex < this.activeSuggestions.length) {
                            e.preventDefault();
                            this.selectLiveSuggestion(this.liveIndex);
                            return;
                        }
                    }
                    if (e.key === 'Escape') {
                        this.hideLiveSuggestions();
                        return;
                    }
                }

                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.hideLiveSuggestions();
                    this.handleSend();
                }
            };
            
            this.dom.input.oninput = () => {
                this.autoResize(this.dom.input);
                this.updateCounter();
                this.handleLiveInput();
            };
        }
        
        // Hide live suggestions if clicked outside
        document.addEventListener('click', (e) => {
            if (this.dom.liveSuggestions && !this.dom.liveSuggestions.contains(e.target) && e.target !== this.dom.input) {
                this.hideLiveSuggestions();
            }
        });

        // Ctrl+K or Ctrl+/ shortcut
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === '/')) {
                e.preventDefault();
                this.toggle();
            }
        });

        // Event delegation for confirm/cancel and copy buttons
        if (this.dom.messages) {
            this.dom.messages.addEventListener('click', (e) => {
                const confBtn = e.target.closest('.bhooma-confirm-btn');
                const cancBtn = e.target.closest('.bhooma-cancel-btn');
                const copyBtn = e.target.closest('.bhooma-copy-btn');
                if (confBtn) {
                    this.confirmAction(confBtn.dataset.id);
                } else if (cancBtn) {
                    this.cancelAction(cancBtn.dataset.id);
                } else if (copyBtn) {
                    this.copyMessageFromElement(copyBtn);
                }
            });
        }
    },

    // -------------------------------------------------------------
    // SOUND EFFECTS (Synthesized Web Audio API - Zero External Files)
    // -------------------------------------------------------------
    getAudioContext() {
        if (!this.audioCtx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                this.audioCtx = new AudioCtx();
            }
        }
        if (this.audioCtx && this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
        return this.audioCtx;
    },

    playSound(type = 'receive') {
        if (!this.soundEnabled) return;
        try {
            const ctx = this.getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;
            
            if (type === 'send') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(520, now);
                osc.frequency.exponentialRampToValueAtTime(740, now + 0.08);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.09);
            } else if (type === 'receive') {
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();
                osc1.type = 'triangle';
                osc2.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                osc2.frequency.setValueAtTime(880.00, now + 0.06); // A5
                gain.gain.setValueAtTime(0.07, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.1);
                osc2.start(now + 0.06);
                osc2.stop(now + 0.22);
            } else if (type === 'action') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(659.25, now);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.25);
            }
        } catch(e) {
            // Audio context not allowed or not supported; ignore
        }
    },

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        localStorage.setItem('bhooma_ai_sound', this.soundEnabled ? '1' : '0');
        if (this.dom.soundIcon) {
            this.dom.soundIcon.className = this.soundEnabled 
                ? 'fa-solid fa-volume-high pointer-events-none' 
                : 'fa-solid fa-volume-xmark pointer-events-none text-rose-300';
        }
        if (this.dom.soundBtn) {
            this.dom.soundBtn.title = this.soundEnabled ? 'Sound Enabled (Click to Mute)' : 'Sound Muted (Click to Unmute)';
        }
        if (this.soundEnabled) this.playSound('receive');
    },

    // -------------------------------------------------------------
    // VOICE DICTATION (Web Speech API)
    // -------------------------------------------------------------
    toggleVoiceInput() {
        if (this.isListening) {
            this.stopVoiceInput();
        } else {
            this.startVoiceInput();
        }
    },

    startVoiceInput() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert('Voice dictation is not supported in this browser. Please use Chrome, Edge, or Safari.');
            return;
        }
        try {
            if (!this.speechRecognition) {
                this.speechRecognition = new SpeechRecognition();
                this.speechRecognition.continuous = false;
                this.speechRecognition.interimResults = true;
                this.speechRecognition.lang = 'en-US';

                this.speechRecognition.onstart = () => {
                    this.isListening = true;
                    if (this.dom.voiceIndicator) this.dom.voiceIndicator.classList.remove('hidden');
                    if (this.dom.micBtn) {
                        this.dom.micBtn.classList.add('bhooma-mic-active', 'text-rose-600', 'bg-rose-50');
                    }
                };

                this.speechRecognition.onresult = (e) => {
                    let transcript = '';
                    for (let i = e.resultIndex; i < e.results.length; i++) {
                        transcript += e.results[i][0].transcript;
                    }
                    if (this.dom.input && transcript) {
                        this.dom.input.value = transcript;
                        this.autoResize(this.dom.input);
                        this.updateCounter();
                    }
                };

                this.speechRecognition.onerror = (e) => {
                    console.warn('Speech error:', e.error);
                    this.stopVoiceInput();
                };

                this.speechRecognition.onend = () => {
                    this.stopVoiceInput();
                    if (this.dom.input && this.dom.input.value.trim().length > 0) {
                        setTimeout(() => {
                            if (this.dom.input) this.dom.input.focus();
                        }, 100);
                    }
                };
            }
            this.speechRecognition.start();
        } catch(e) {
            console.warn('Speech start error:', e);
            this.stopVoiceInput();
        }
    },

    stopVoiceInput() {
        this.isListening = false;
        if (this.speechRecognition) {
            try { this.speechRecognition.stop(); } catch(e) {}
        }
        if (this.dom.voiceIndicator) this.dom.voiceIndicator.classList.add('hidden');
        if (this.dom.micBtn) {
            this.dom.micBtn.classList.remove('bhooma-mic-active', 'text-rose-600', 'bg-rose-50');
        }
    },

    // -------------------------------------------------------------
    // EXPORT CONVERSATION TO MARKDOWN (.md)
    // -------------------------------------------------------------
    exportConversation() {
        if (!this.dom.messages) return;
        const wrappers = this.dom.messages.children;
        if (!wrappers || wrappers.length === 0) {
            alert('No conversation history to export.');
            return;
        }

        let md = `# BHOOMA AI Clinical Assistant - Conversation Export\n`;
        md += `**Date:** ${new Date().toLocaleString()}\n`;
        md += `**Hospital:** <?php echo addslashes($chatbot_hospital_name); ?>\n`;
        md += `**User Role:** <?php echo addslashes($chatbot_user_role); ?>\n\n`;
        md += `---\n\n`;

        for (let el of wrappers) {
            if (el.id === 'bhooma-typing') continue;
            const isUser = el.classList.contains('ml-auto') || el.classList.contains('flex-row-reverse');
            const role = isUser ? 'User' : 'BHOOMA AI';
            const body = el.querySelector('.bhooma-markdown') || el.querySelector('.content-box');
            if (body) {
                const text = body.innerText.replace(/Copy/g, '').trim();
                if (text) {
                    md += `### ${role}:\n${text}\n\n`;
                }
            }
        }

        const blob = new Blob([md], { type: 'text/markdown;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `bhooma_ai_chat_${Date.now()}.md`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    },

    // -------------------------------------------------------------
    // 1-CLICK MESSAGE COPY
    // -------------------------------------------------------------
    copyMessageFromElement(btnEl) {
        const parentBox = btnEl.closest('.content-box') || btnEl.closest('.bhooma-markdown');
        if (!parentBox) return;
        const text = parentBox.innerText.replace(/Copy/g, '').trim();
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> <span class="text-[10px] text-emerald-600 font-bold">Copied!</span>';
            setTimeout(() => {
                btnEl.innerHTML = originalHtml;
            }, 1800);
        }).catch(err => {
            console.error('Clipboard copy error:', err);
        });
    },

    // -------------------------------------------------------------
    // SCROLL HANDLING & UNREAD BADGE
    // -------------------------------------------------------------
    handleScroll() {
        if (!this.dom.messages || !this.dom.scrollBottomBtn) return;
        const { scrollTop, scrollHeight, clientHeight } = this.dom.messages;
        const isNearBottom = scrollHeight - scrollTop - clientHeight < 65;

        if (isNearBottom) {
            this.dom.scrollBottomBtn.classList.add('hidden');
            this.unreadMessagesCount = 0;
            if (this.dom.unreadCount) this.dom.unreadCount.classList.add('hidden');
        } else {
            this.dom.scrollBottomBtn.classList.remove('hidden');
        }
    },
    
    toggle(forceState = null) {
        if (this._toggling) return;
        this._toggling = true;
        setTimeout(() => { this._toggling = false; }, 200);

        if (!this.dom || !this.dom.panel) this.cacheDOM();
        if (!this.dom.panel) return;

        this.isOpen = forceState !== null ? forceState : !this.isOpen;
        if (this.isOpen) {
            this.dom.panel.classList.remove('hidden');
            this.dom.panel.style.display = 'flex';
            this.dom.panel.classList.add('bhooma-chat-panel');
            if (this.dom.badge) this.dom.badge.classList.add('hidden');
            if (this.dom.toggle) this.dom.toggle.classList.remove('bhooma-btn-pulse');
            setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 120);
            this.scrollToBottom();
        } else {
            this.dom.panel.classList.add('hidden');
            this.dom.panel.style.display = 'none';
            this.dom.panel.classList.remove('bhooma-chat-panel');
        }
    },
    
    async checkStatus() {
        try {
            const res = await fetch(this.apiUrl + '?action=status');
            const data = await res.json();
            if (data.ollama || data.status === 'online') {
                if (this.dom.statusDot) this.dom.statusDot.className = 'absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-indigo-600';
                if (this.dom.statusText) this.dom.statusText.textContent = 'Online';
                const modelBadge = document.getElementById('bhooma-ai-model');
                if (modelBadge && data.model) modelBadge.textContent = data.model;
            } else {
                if (this.dom.statusDot) this.dom.statusDot.className = 'absolute bottom-0 right-0 w-2.5 h-2.5 bg-rose-400 rounded-full border-2 border-indigo-600';
                if (this.dom.statusText) this.dom.statusText.textContent = 'Offline';
            }
        } catch (e) {
            if (this.dom.statusDot) this.dom.statusDot.className = 'absolute bottom-0 right-0 w-2.5 h-2.5 bg-slate-400 rounded-full border-2 border-indigo-600';
            if (this.dom.statusText) this.dom.statusText.textContent = 'Ready';
        }
    },
    
    loadConversation() {
        const saved = localStorage.getItem('bhooma_ai_conv');
        if (saved) {
            this.selectConversation(saved);
        } else {
            this.renderWelcomeMessage();
        }
    },

    toggleExpand() {
        if (!this.dom.panel) return;
        const isExp = this.dom.panel.classList.toggle('bhooma-expanded');
        if (isExp) {
            if (this.dom.expandIcon) this.dom.expandIcon.className = 'fa-solid fa-compress pointer-events-none';
            if (this.dom.expandBtn) this.dom.expandBtn.title = 'Restore Window Mode';
            localStorage.setItem('bhooma_ai_expanded', '1');
        } else {
            if (this.dom.expandIcon) this.dom.expandIcon.className = 'fa-solid fa-expand pointer-events-none';
            if (this.dom.expandBtn) this.dom.expandBtn.title = 'Workstation Mode';
            localStorage.removeItem('bhooma_ai_expanded');
        }
        this.scrollToBottom();
    },

    allConversations: [],

    toggleHistoryDrawer() {
        if (!this.dom.historyDrawer) return;
        const isOpen = !this.dom.historyDrawer.classList.contains('translate-x-full');
        if (isOpen) {
            this.closeHistoryDrawer();
        } else {
            this.openHistoryDrawer();
        }
    },

    openHistoryDrawer() {
        if (!this.dom.historyDrawer) return;
        this.dom.historyDrawer.classList.remove('hidden');
        void this.dom.historyDrawer.offsetWidth;
        this.dom.historyDrawer.classList.remove('translate-x-full');
        this.dom.historyDrawer.classList.add('translate-x-0');
        if (this.dom.historySearch) {
            this.dom.historySearch.value = '';
            setTimeout(() => this.dom.historySearch.focus(), 150);
        }
        this.loadConversationList();
    },

    closeHistoryDrawer() {
        if (!this.dom.historyDrawer) return;
        this.dom.historyDrawer.classList.remove('translate-x-0');
        this.dom.historyDrawer.classList.add('translate-x-full');
        setTimeout(() => {
            if (this.dom.historyDrawer && this.dom.historyDrawer.classList.contains('translate-x-full')) {
                this.dom.historyDrawer.classList.add('hidden');
            }
        }, 300);
    },

    async loadConversationList() {
        if (!this.dom.historyList) return;
        this.dom.historyList.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-indigo-500 mb-2"></i>
                <span class="text-xs">Loading conversations...</span>
            </div>
        `;
        try {
            const res = await fetch(this.apiUrl + '?action=conversations');
            const data = await res.json();
            if (data.success && Array.isArray(data.conversations)) {
                this.allConversations = data.conversations;
                this.renderConversationList(this.allConversations);
            } else {
                this.dom.historyList.innerHTML = `<div class="text-center py-8 text-xs text-slate-400">No past conversations found.</div>`;
            }
        } catch (e) {
            console.error('Failed to load conversations:', e);
            this.dom.historyList.innerHTML = `<div class="text-center py-8 text-xs text-rose-500">Failed to load conversations.</div>`;
        }
    },

    filterHistoryList(term) {
        if (!this.allConversations) return;
        const q = (term || '').trim().toLowerCase();
        if (!q) {
            this.renderConversationList(this.allConversations);
            return;
        }
        const filtered = this.allConversations.filter(c => {
            const title = (c.title || '').toLowerCase();
            const first = (c.first_user_query || '').toLowerCase();
            const last = (c.last_message || '').toLowerCase();
            return title.includes(q) || first.includes(q) || last.includes(q);
        });
        this.renderConversationList(filtered, q);
    },

    renderConversationList(list, searchTerm = '') {
        if (!this.dom.historyList) return;
        if (!list || list.length === 0) {
            this.dom.historyList.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-semibold text-slate-500">${searchTerm ? 'No matching conversations' : 'No chat history yet'}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">${searchTerm ? 'Try a different search word' : 'Start a conversation to save your inquiries'}</p>
                </div>
            `;
            return;
        }

        let html = '';
        list.forEach(c => {
            const isActive = this.conversationId && parseInt(this.conversationId, 10) === parseInt(c.id, 10);
            const title = c.first_user_query || c.title || ('Conversation #' + c.id);
            const snippet = c.last_message ? c.last_message.replace(/<[^>]*>/g, '') : 'No messages yet';
            const count = c.message_count || 0;
            const timeAgo = this.formatRelativeTime(c.updated_at || c.created_at);

            html += `
                <div class="group relative flex items-start justify-between p-3 rounded-xl border transition-all cursor-pointer ${isActive ? 'bg-indigo-50/80 border-indigo-300 shadow-2xs' : 'bg-white border-slate-200 hover:border-indigo-200 hover:bg-slate-50/80'}"
                     onclick="BhoomaAI.selectConversation(${c.id})">
                    <div class="min-w-0 flex-1 pr-2">
                        <div class="flex items-center gap-1.5 mb-1">
                            ${isActive ? '<span class="w-2 h-2 rounded-full bg-indigo-600 shrink-0"></span>' : ''}
                            <h5 class="text-xs font-bold ${isActive ? 'text-indigo-900' : 'text-slate-800'} truncate leading-tight">${this.escapeHtml(title)}</h5>
                        </div>
                        <p class="text-[11px] text-slate-500 line-clamp-1 mb-1.5">${this.escapeHtml(snippet)}</p>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400">
                            <span><i class="fa-regular fa-clock text-[9px] mr-1"></i>${timeAgo}</span>
                            <span>•</span>
                            <span><i class="fa-regular fa-message text-[9px] mr-1"></i>${count} msgs</span>
                        </div>
                    </div>
                    <button type="button" 
                            class="opacity-0 group-hover:opacity-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 w-7 h-7 rounded-lg flex items-center justify-center transition-all shrink-0 cursor-pointer" 
                            title="Delete conversation"
                            onclick="BhoomaAI.deleteConversation(${c.id}, event)">
                        <i class="fa-regular fa-trash-can text-xs pointer-events-none"></i>
                    </button>
                </div>
            `;
        });
        this.dom.historyList.innerHTML = html;
    },

    formatRelativeTime(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr.replace(/-/g, '/'));
            const now = new Date();
            const diffSec = Math.floor((now - date) / 1000);
            if (diffSec < 60) return 'Just now';
            const diffMin = Math.floor(diffSec / 60);
            if (diffMin < 60) return diffMin + 'm ago';
            const diffHours = Math.floor(diffMin / 60);
            if (diffHours < 24) return diffHours + 'h ago';
            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 7) return diffDays + 'd ago';
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    },

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    },

    async selectConversation(convId) {
        this.closeHistoryDrawer();
        if (!convId) return;

        this.conversationId = convId;
        localStorage.setItem('bhooma_ai_conv', convId);

        if (this.dom.suggestions) this.dom.suggestions.classList.add('hidden');
        if (this.dom.messages) {
            this.dom.messages.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-indigo-500 mb-2"></i>
                    <span class="text-xs">Restoring conversation...</span>
                </div>
            `;
        }

        try {
            const res = await fetch(this.apiUrl + '?action=history&conversation_id=' + encodeURIComponent(convId));
            const data = await res.json();

            if (data.success && Array.isArray(data.messages) && data.messages.length > 0) {
                if (this.dom.messages) this.dom.messages.innerHTML = '';
                data.messages.forEach(msg => {
                    this.appendMessage(msg.role, msg.content);
                });

                if (data.pending_action) {
                    this.renderActionCard(data.pending_action.id, data.pending_action.description);
                }
                this.scrollToBottom();
            } else {
                this.conversationId = null;
                localStorage.removeItem('bhooma_ai_conv');
                this.renderWelcomeMessage();
            }
        } catch (e) {
            console.error('Error restoring conversation:', e);
            this.conversationId = null;
            localStorage.removeItem('bhooma_ai_conv');
            this.renderWelcomeMessage();
        }
        setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 120);
    },

    async deleteConversation(convId, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        if (!confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'delete_conversation');
            formData.append('conversation_id', convId);
            const res = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (this.conversationId && parseInt(this.conversationId, 10) === parseInt(convId, 10)) {
                    this.newConversation();
                }
                this.loadConversationList();
            } else {
                alert(data.error || 'Failed to delete conversation.');
            }
        } catch (e) {
            console.error('Delete conversation error:', e);
            alert('Error deleting conversation.');
        }
    },

    renderWelcomeMessage() {
        if (!this.dom.messages) return;
        this.dom.messages.innerHTML = `
            <div class="flex items-start max-w-[90%] bhooma-msg-anim">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-teal-400 flex items-center justify-center text-white text-xs shrink-0 mt-0.5 shadow-sm mr-2.5 ring-2 ring-indigo-100">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-white border border-slate-200/90 text-slate-700 text-sm p-4 rounded-2xl rounded-tl-sm shadow-sm space-y-3 w-full">
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="text-xs font-bold text-slate-800">Hello <?php echo htmlspecialchars($chatbot_user_role); ?>!</span>
                            <span class="text-[9px] bg-indigo-50 text-indigo-700 font-bold px-1.5 py-0.2 rounded-full border border-indigo-200">AI Assistant</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            I'm <strong class="text-indigo-600 font-extrabold">BHOOMA AI</strong>. How can I assist you with managing <strong class="text-slate-800"><?php echo htmlspecialchars($chatbot_hospital_name); ?></strong> today?
                        </p>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-1.5">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i> Quick Actions
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="BhoomaAI.renderInChatForm('book_appointment');" class="px-2.5 py-1 bg-white hover:bg-purple-50 border border-purple-200 text-purple-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98">
                                <i class="fa-solid fa-calendar-plus text-[11px] text-purple-600"></i> Book Appointment
                            </button>
                            <button type="button" onclick="BhoomaAI.renderInChatForm('admit_patient');" class="px-2.5 py-1 bg-white hover:bg-teal-50 border border-teal-200 text-teal-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98">
                                <i class="fa-solid fa-bed text-[11px] text-teal-600"></i> Admit Patient
                            </button>
                            <button type="button" onclick="BhoomaAI.quickPrompt('Hospital overview dashboard metrics');" class="px-2.5 py-1 bg-white hover:bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98">
                                <i class="fa-solid fa-chart-pie text-[11px] text-indigo-600"></i> Overview
                            </button>
                            <button type="button" onclick="BhoomaAI.quickPrompt('Show available beds by ward');" class="px-2.5 py-1 bg-white hover:bg-cyan-50 border border-cyan-200 text-cyan-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98">
                                <i class="fa-solid fa-bed-pulse text-[11px] text-cyan-600"></i> Beds Status
                            </button>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100">
                        <span><i class="fa-solid fa-shield-halved text-emerald-500 mr-1"></i> Hospital Data Protected</span>
                        <span>Press <kbd class="bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-slate-500 font-mono text-[9px]">Ctrl+K</kbd> to toggle</span>
                    </div>
                </div>
            </div>
        `;
    },

    quickPrompt(text) {
        if (!this.dom.input) return;
        this.dom.input.value = text;
        this.autoResize(this.dom.input);
        this.handleSend();
    },
    
    renderSuggestions() {
        if (!this.dom.suggestions) return;
        if (!this.suggestions || this.suggestions.length === 0) return;
        this.dom.suggestions.innerHTML = '';
        this.suggestions.forEach(text => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'shrink-0 px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-colors whitespace-nowrap shadow-sm cursor-pointer';
            btn.textContent = text;
            btn.onclick = () => {
                this.dom.input.value = text;
                this.autoResize(this.dom.input);
                this.handleSend();
            };
            this.dom.suggestions.appendChild(btn);
        });
    },

    handleLiveInput() {
        clearTimeout(this.liveDebounceTimer);
        const val = (this.dom.input ? this.dom.input.value : '').trim();
        if (!val || val.length < 1) {
            this.hideLiveSuggestions();
            return;
        }
        
        this.liveDebounceTimer = setTimeout(() => {
            this.renderLiveSuggestions(val);
        }, 120);
    },

    renderLiveSuggestions(query) {
        if (!this.dom.liveSuggestions) return;
        const q = query.toLowerCase();
        const tokens = q.split(/\s+/).filter(Boolean);
        
        const matches = this.liveDictionary.map(item => {
            const txt = item.text.toLowerCase();
            const cat = item.cat.toLowerCase();
            let score = 0;
            
            if (txt.startsWith(q)) score += 100;
            else if (txt.includes(q)) score += 60;
            else if (cat.includes(q)) score += 40;
            
            tokens.forEach(tok => {
                if (txt.includes(tok)) score += 20;
                if (cat.includes(tok)) score += 15;
            });
            
            return { item, score };
        })
        .filter(x => x.score > 0)
        .sort((a, b) => b.score - a.score)
        .slice(0, 5)
        .map(x => x.item);
        
        if (matches.length === 0) {
            this.hideLiveSuggestions();
            return;
        }
        
        this.activeSuggestions = matches;
        this.liveIndex = -1;
        
        let html = '';
        matches.forEach((sug, idx) => {
            let displayText = sug.text;
            try {
                const re = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                displayText = displayText.replace(re, '<span class="text-indigo-600 font-bold">$1</span>');
            } catch(e) {}
            
            html += `
                <div class="bhooma-live-item px-3 py-2 cursor-pointer flex items-center justify-between transition-colors group text-left hover:bg-slate-50" data-index="${idx}">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <span class="w-6 h-6 rounded-lg ${sug.color} flex items-center justify-center text-[11px] shrink-0">
                            <i class="fa-solid ${sug.icon}"></i>
                        </span>
                        <span class="text-xs text-slate-700 font-medium truncate">${displayText}</span>
                    </div>
                    <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider shrink-0 ml-2 group-hover:text-indigo-600 transition-colors">${sug.cat}</span>
                </div>
            `;
        });
        
        this.dom.liveSuggestions.innerHTML = html;
        this.dom.liveSuggestions.classList.remove('hidden');
        
        const itemEls = this.dom.liveSuggestions.querySelectorAll('.bhooma-live-item');
        itemEls.forEach(el => {
            el.onmousedown = (e) => {
                e.preventDefault(); // Prevent input blur on click
            };
            el.onclick = (e) => {
                e.stopPropagation();
                const idx = parseInt(el.dataset.index, 10);
                this.selectLiveSuggestion(idx);
            };
        });
    },

    highlightLiveSuggestion() {
        if (!this.dom.liveSuggestions) return;
        const items = this.dom.liveSuggestions.querySelectorAll('.bhooma-live-item');
        items.forEach((el, idx) => {
            if (idx === this.liveIndex) {
                el.classList.add('bg-indigo-50', 'border-l-4', 'border-indigo-600', 'pl-2');
                el.classList.remove('hover:bg-slate-50');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('bg-indigo-50', 'border-l-4', 'border-indigo-600', 'pl-2');
                el.classList.add('hover:bg-slate-50');
            }
        });
    },

    selectLiveSuggestion(index) {
        if (index >= 0 && index < this.activeSuggestions.length) {
            const item = this.activeSuggestions[index];
            this.hideLiveSuggestions();
            const lower = item.text.toLowerCase();
            if (lower.includes('booking form') || lower.includes('book an appointment') || lower.includes('book appointment')) {
                this.renderInChatForm('book_appointment');
                return;
            }
            if (lower.includes('admission form') || lower.includes('admit patient')) {
                this.renderInChatForm('admit_patient');
                return;
            }
            this.dom.input.value = item.text;
            this.autoResize(this.dom.input);
            this.handleSend();
        }
    },

    hideLiveSuggestions() {
        if (this.dom.liveSuggestions) {
            this.dom.liveSuggestions.classList.add('hidden');
            this.dom.liveSuggestions.innerHTML = '';
        }
        this.liveIndex = -1;
        this.activeSuggestions = [];
    },
    
    autoResize(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight < 100 ? el.scrollHeight : 100) + 'px';
    },
    
    updateCounter() {
        if (!this.dom.input || !this.dom.counter) return;
        const len = this.dom.input.value.length;
        if (len > 100) {
            this.dom.counter.classList.remove('hidden');
            this.dom.counter.textContent = `${len}/2000`;
            if (len >= 2000) this.dom.counter.classList.add('text-rose-500');
            else this.dom.counter.classList.remove('text-rose-500');
        } else {
            this.dom.counter.classList.add('hidden');
        }
    },
    
    scrollToBottom() {
        if (this.dom.messages) {
            this.dom.messages.scrollTop = this.dom.messages.scrollHeight;
        }
    },
    
    formatTime(date = new Date()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    
    formatMarkdown(text) {
        if (!text) return '';
        // Completely strip any leaked ```sql ... ``` code blocks
        let clean = text.replace(/```sql[\s\S]*?```/gi, '');
        
        // Render executive callouts for > quotes or direct answers
        clean = clean.replace(/^>\s*(.*?)(?=\n|$)/gm, (match, p1) => {
            return `<div class="bhooma-callout my-1.5 p-2.5 bg-indigo-50/90 border-l-4 border-indigo-600 rounded-r-xl text-slate-800 text-xs font-semibold shadow-2xs">${p1}</div>`;
        });

        // Convert markdown bullet points to clean list items
        clean = clean.replace(/^[•\-\*]\s+(.*)$/gm, '<div class="flex items-start gap-1.5 my-0.5 text-xs text-slate-700"><span class="text-indigo-500 font-bold">•</span><span>$1</span></div>');

        let formatted = clean
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/```([\s\S]*?)```/g, '<pre class="bg-slate-800 text-white p-2.5 rounded-xl text-xs overflow-x-auto my-1.5 font-mono"><code>$1</code></pre>')
            .replace(/`([^`]+)`/g, '<code class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-mono text-xs border border-indigo-100">$1</code>')
            .replace(/\n/g, '<br>');
        return formatted;
    },
    
    appendMessage(role, content) {
        const isUser = role === 'user';
        const msgId = 'msg-' + Date.now() + '-' + Math.floor(Math.random()*1000);
        
        const wrapper = document.createElement('div');
        wrapper.className = `flex items-start max-w-[88%] ${isUser ? 'ml-auto flex-row-reverse' : ''} bhooma-msg-anim`;
        wrapper.id = msgId;
        
        let html = '';
        if (isUser) {
            html = `
                <div class="ml-2 mr-0 bg-gradient-to-r from-indigo-600 via-indigo-700 to-blue-600 text-white text-sm py-2.5 px-3.5 rounded-2xl rounded-tr-sm shadow-md bhooma-markdown w-full transition-all">
                    <div class="bhooma-text-body">${this.formatMarkdown(content)}</div>
                    <div class="text-[9px] text-indigo-200/80 text-right mt-1 font-mono">${this.formatTime()}</div>
                </div>
            `;
        } else {
            html = `
                <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm ${isUser ? 'ml-2' : 'mr-2'} ring-2 ring-indigo-100">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="content-box bg-white border border-slate-200/90 text-slate-700 text-sm py-2.5 px-3.5 rounded-2xl rounded-tl-sm shadow-sm bhooma-markdown w-full overflow-x-auto relative group">
                    <div class="bhooma-text-body leading-relaxed">${this.formatMarkdown(content)}</div>
                    <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-slate-100 text-[10px] text-slate-400">
                        <span class="text-[9px] font-mono text-slate-400">${this.formatTime()}</span>
                        <button type="button" class="bhooma-copy-btn hover:text-indigo-600 flex items-center gap-1 transition-colors px-1 py-0.5 rounded cursor-pointer" onclick="BhoomaAI.copyMessageFromElement(this);" title="Copy message">
                            <i class="fa-regular fa-copy text-[10px]"></i> <span class="text-[10px]">Copy</span>
                        </button>
                    </div>
                </div>
            `;
        }
        
        wrapper.innerHTML = html;
        this.dom.messages.appendChild(wrapper);

        // Handle unread messages badge if scrolled up
        if (!isUser && this.dom.messages) {
            const { scrollTop, scrollHeight, clientHeight } = this.dom.messages;
            const isScrolledUp = scrollHeight - scrollTop - clientHeight > 80;
            if (isScrolledUp) {
                this.unreadMessagesCount++;
                if (this.dom.unreadCount) {
                    this.dom.unreadCount.textContent = this.unreadMessagesCount;
                    this.dom.unreadCount.classList.remove('hidden');
                }
                if (this.dom.scrollBottomBtn) this.dom.scrollBottomBtn.classList.remove('hidden');
            } else {
                this.scrollToBottom();
            }
        } else {
            this.scrollToBottom();
        }
        return msgId;
    },
    
    showTyping() {
        if (document.getElementById('bhooma-typing')) return;
        const wrapper = document.createElement('div');
        wrapper.id = 'bhooma-typing';
        wrapper.className = 'flex items-start max-w-[85%] bhooma-msg-anim';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2 ring-2 ring-indigo-100">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200/90 py-2.5 px-3.5 rounded-2xl rounded-tl-sm shadow-xs flex items-center space-x-2 h-[38px] relative overflow-hidden">
                <div class="flex space-x-1.5 items-center">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full bhooma-typing-dot"></div>
                    <div class="w-2 h-2 bg-indigo-600 rounded-full bhooma-typing-dot"></div>
                    <div class="w-2 h-2 bg-teal-500 rounded-full bhooma-typing-dot"></div>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">BHOOMA AI is thinking...</span>
                <div class="absolute inset-0 bhooma-shimmer pointer-events-none opacity-30"></div>
            </div>
        `;
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom(true);
    },
    
    hideTyping() {
        const el = document.getElementById('bhooma-typing');
        if (el) el.remove();
    },

    renderInlineSuggestions(suggestions) {
        if (!suggestions || !Array.isArray(suggestions) || suggestions.length === 0) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[88%] bhooma-msg-anim my-1.5';
        let html = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2 ring-1 ring-indigo-200">
                <i class="fa-solid fa-lightbulb text-[10px]"></i>
            </div>
            <div class="bg-indigo-50/70 border border-indigo-200/80 p-2.5 rounded-2xl rounded-tl-sm shadow-2xs w-full">
                <div class="text-[11px] font-bold text-indigo-900 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-600 text-[10px]"></i> Suggested Options
                </div>
                <div class="flex flex-wrap gap-1.5">
        `;
        suggestions.forEach(text => {
            const escaped = this.escapeHtml(text);
            html += `
                <button type="button" class="px-2.5 py-1 bg-white hover:bg-indigo-100/70 border border-indigo-200 text-indigo-700 rounded-lg text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer shadow-2xs hover:scale-102 active:scale-98"
                    onclick="BhoomaAI.quickPrompt('${escaped}');">
                    <i class="fa-solid fa-arrow-right text-[9px] opacity-60"></i> ${escaped}
                </button>
            `;
        });
        html += `</div></div>`;
        wrapper.innerHTML = html;
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
    },
    
    async handleSend() {
        const text = this.dom.input.value.trim();
        if (!text || this.isLoading) return;
        
        if (text.length > 2000) {
            alert('Message too long. Please limit to 2000 characters.');
            return;
        }
        
        this.dom.input.value = '';
        this.hideLiveSuggestions();
        this.autoResize(this.dom.input);
        if (this.dom.suggestions) this.dom.suggestions.classList.add('hidden');
        this.updateCounter();
        
        // Play sending chime
        this.playSound('send');
        
        this.appendMessage('user', text);
        this.showTyping();
        this.isLoading = true;
        if (this.dom.send) this.dom.send.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'chat');
            formData.append('message', text);
            formData.append('page_context', this.currentPage);
            if (this.conversationId) {
                formData.append('conversation_id', this.conversationId);
            }
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                this.hideTyping();
                throw new Error('Network response was not ok');
            }
            
            // Handle SSE Stream with robust chunk-boundary handling
            const reader = response.body.getReader();
            const decoder = new TextDecoder('utf-8');
            let aiMsgId = null;
            let aiMsgContent = '';
            let isFirstChunk = true;
            let sseBuffer = '';
            
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                
                sseBuffer += decoder.decode(value, { stream: true });
                const events = sseBuffer.split('\n');
                // Keep the last partial line in buffer (may be incomplete)
                sseBuffer = events.pop() || '';
                
                for (const line of events) {
                    const trimmed = line.trim();
                    if (!trimmed || !trimmed.startsWith('data: ')) continue;
                    const dataStr = trimmed.substring(6).trim();
                    if (!dataStr || dataStr === '[DONE]') continue;
                    
                    try {
                        const data = JSON.parse(dataStr);
                        
                        if (data.type === 'chunk') {
                            if (isFirstChunk) {
                                this.hideTyping();
                                this.playSound('receive');
                                aiMsgId = this.appendMessage('assistant', '');
                                isFirstChunk = false;
                            }
                            aiMsgContent += data.content;
                            const msgEl = document.querySelector(`#${aiMsgId} .bhooma-text-body`);
                            if (msgEl) {
                                msgEl.innerHTML = this.formatMarkdown(aiMsgContent);
                                this.scrollToBottom();
                            }
                        } 
                        else if (data.type === 'data') {
                            this.hideTyping();
                            this.playSound('action');
                            this.renderDataTable(data.results || data.content, data.summary);
                        }
                        else if (data.type === 'action') {
                            this.hideTyping();
                            this.playSound('action');
                            this.renderActionCard(data.action_id, data.content);
                        }
                        else if (data.type === 'form') {
                            this.hideTyping();
                            this.playSound('action');
                            this.renderInChatForm(data.form_type, data);
                        }
                        else if (data.type === 'suggestions') {
                            this.renderInlineSuggestions(data.suggestions);
                        }
                        else if (data.type === 'done') {
                            this.hideTyping();
                            if (data.conversation_id) {
                                this.conversationId = data.conversation_id;
                                localStorage.setItem('bhooma_ai_conv', this.conversationId);
                            }
                        }
                        else if (data.type === 'error') {
                            this.hideTyping();
                            this.appendMessage('assistant', '⚠️ ' + data.message);
                        }
                    } catch (e) {
                        console.warn('SSE parse skip:', e.message, dataStr?.substring(0, 80));
                    }
                }
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTyping();
            this.appendMessage('assistant', 'Sorry, I encountered an error. Please try again later.');
        } finally {
            this.hideTyping();
            this.isLoading = false;
            if (this.dom.send) this.dom.send.disabled = false;
            setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 100);
        }
    },
    
    renderDataTable(dataArr, summary) {
        if (!dataArr) return;
        
        // Handle empty results
        if (!dataArr.length || dataArr.length === 0) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start max-w-[95%]';
            wrapper.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-slate-400 to-slate-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="bg-slate-50 border border-slate-200 py-2.5 px-3.5 rounded-2xl rounded-tl-sm shadow-sm w-full">
                    <div class="text-xs text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-inbox text-slate-400"></i>
                        <span class="font-semibold">${summary || 'No matching records found in the hospital database.'}</span>
                    </div>
                </div>
            `;
            this.dom.messages.appendChild(wrapper);
            this.scrollToBottom();
            return;
        }
        const columns = Object.keys(dataArr[0]);
        
        let visualHTML = '';
        
        // Single metric / KPI (e.g. [{"patient_count": 2}] or [{"count": 5}])
        if (dataArr.length === 1 && columns.length === 1) {
            const key = columns[0];
            const val = dataArr[0][key];
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            visualHTML = `
                <div class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-100 rounded-xl px-4 py-2.5 my-1 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500">${label}</div>
                        <div class="text-xl font-black text-indigo-900 leading-tight">${val}</div>
                    </div>
                </div>
            `;
        } else {
            let tableHTML = `<div class="w-full overflow-x-auto mt-2 mb-1 border border-slate-200 rounded-xl shadow-xs">
                <div class="bg-slate-100/80 px-3 py-1.5 border-b border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span><i class="fa-solid fa-table-list text-indigo-500 mr-1.5"></i> Hospital Records</span>
                    <span class="bg-indigo-100 text-indigo-700 text-[10px] px-1.5 py-0.2 rounded font-semibold">${dataArr.length} items</span>
                </div>
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>`;
            columns.forEach(col => {
                const colLabel = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                tableHTML += `<th class="px-3 py-2 text-[11px] tracking-tight">${colLabel}</th>`;
            });
            tableHTML += `</tr></thead><tbody class="divide-y divide-slate-100 bg-white">`;
            
            dataArr.forEach((row, i) => {
                const bg = i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50';
                tableHTML += `<tr class="${bg} hover:bg-indigo-50/70 transition-colors">`;
                columns.forEach(col => {
                    tableHTML += `<td class="px-3 py-1.5 text-slate-700">${row[col] !== null ? row[col] : '-'}</td>`;
                });
                tableHTML += `</tr>`;
            });
            
            tableHTML += `</tbody></table></div>`;
            visualHTML = tableHTML;
        }
        
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[95%]';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid fa-database"></i>
            </div>
            <div class="bg-white border border-slate-200 py-2.5 px-3.5 rounded-2xl rounded-tl-sm shadow-sm w-full overflow-x-auto">
                ${visualHTML}
            </div>
        `;
        
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
    },
    
    renderActionCard(actionId, details) {
        const isDelete = typeof details === 'string' && (details.toLowerCase().includes('delete') || details.toLowerCase().includes('cancel'));
        const badgeColor = isDelete ? 'bg-rose-500' : 'bg-amber-400';
        const cardBg = isDelete ? 'bg-rose-50 border-2 border-rose-300' : 'bg-amber-50 border-2 border-amber-200';
        const titleText = isDelete ? '⚠️ Confirm Deletion / Cancellation' : '⚡ Confirm Action';
        const titleColor = isDelete ? 'text-rose-900' : 'text-amber-800';
        const detailColor = isDelete ? 'text-rose-800' : 'text-amber-700';
        const confirmBtnClass = isDelete ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-500 hover:bg-emerald-600';
        const confirmBtnText = isDelete ? '<i class="fa-solid fa-trash-can mr-1"></i> Confirm Delete' : '<i class="fa-solid fa-check mr-1"></i> Confirm';

        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[85%] mt-2 mb-2';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full ${badgeColor} flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid ${isDelete ? 'fa-triangle-exclamation' : 'fa-bolt'}"></i>
            </div>
            <div class="${cardBg} py-3 px-4 rounded-xl shadow-sm w-full" id="action-card-${actionId}">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="font-bold ${titleColor} text-sm">${titleText}</h4>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${isDelete ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'}">Requires Approval</span>
                </div>
                <p class="text-xs ${detailColor} mb-3 leading-relaxed">${details}</p>
                <div class="flex space-x-2">
                    <button type="button" class="bhooma-confirm-btn ${confirmBtnClass} text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm transition-colors flex-1 cursor-pointer" data-id="${actionId}">
                        ${confirmBtnText}
                    </button>
                    <button type="button" class="bhooma-cancel-btn bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm transition-colors flex-1 cursor-pointer" data-id="${actionId}">
                        Cancel
                    </button>
                </div>
            </div>
        `;
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
    },

    async renderInChatForm(formType, config = {}) {
        const formId = 'bhooma-form-' + Date.now();
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[95%] mt-2 mb-3';
        wrapper.id = formId;

        if (!this._formOptions) {
            try {
                const res = await fetch(this.apiUrl + '?action=get_form_options');
                this._formOptions = await res.json();
            } catch(e) {
                this._formOptions = { doctors: [], beds: [], types: [], slots: [] };
            }
        }
        const opts = this._formOptions || {};
        const doctors = opts.doctors || [];
        const beds = opts.beds || [];
        const types = opts.types || ['General Consultation', 'Specialist Review', 'Follow-up Consultation', 'Emergency Consultation'];
        const slots = opts.slots || ['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '02:00 PM', '02:30 PM', '03:00 PM', '04:00 PM'];
        const todayStr = new Date().toISOString().split('T')[0];
        const prefill = config.prefill || {};

        let formHtml = '';

        if (formType === 'update_doctor') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-blue-50/40 border border-blue-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-blue-100">
                        <div class="flex items-center gap-2 text-blue-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-user-doctor"></i>
                            </span>
                            <span>Edit Doctor Records</span>
                        </div>
                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full uppercase tracking-wider">Live Update</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitDoctorUpdateForm('${formId}');">
                        <input type="hidden" id="${formId}-doc-id" value="${prefill.id || ''}">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Full Doctor Name *</label>
                            <input type="text" id="${formId}-doc-name" value="${prefill.name || ''}" required
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-blue-500/50">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Degree / Qualifications</label>
                                <input type="text" id="${formId}-doc-degree" value="${prefill.degree || ''}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-blue-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Experience</label>
                                <input type="text" id="${formId}-doc-exp" value="${prefill.experience || ''}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-blue-500/50">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone Number</label>
                            <input type="text" id="${formId}-doc-phone" value="${prefill.phone || ''}"
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500/50">
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Submit Changes</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'update_patient') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-emerald-50/40 border border-emerald-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-emerald-100">
                        <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-hospital-user"></i>
                            </span>
                            <span>Edit Patient Profile</span>
                        </div>
                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full uppercase tracking-wider">${prefill.id || 'PAT'}</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitPatientUpdateForm('${formId}');">
                        <input type="hidden" id="${formId}-pat-id" value="${prefill.id || ''}">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">First Name *</label>
                                <input type="text" id="${formId}-pat-name" value="${prefill.name || ''}" required
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Surname</label>
                                <input type="text" id="${formId}-pat-surname" value="${prefill.surname || ''}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone</label>
                                <input type="text" id="${formId}-pat-phone" value="${prefill.phone || ''}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Blood Group</label>
                                <input type="text" id="${formId}-pat-bg" value="${prefill.blood_group || ''}" placeholder="e.g. B+, O-"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Submit Changes</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'update_bed') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-amber-50/40 border border-amber-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-amber-100">
                        <div class="flex items-center gap-2 text-amber-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-bed"></i>
                            </span>
                            <span>Edit Bed Settings</span>
                        </div>
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full uppercase tracking-wider">Bed ${prefill.bed_number || ''}</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitBedUpdateForm('${formId}');">
                        <input type="hidden" id="${formId}-bed-num" value="${prefill.bed_number || ''}">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Ward Type</label>
                                <input type="text" id="${formId}-bed-type" value="${prefill.type || 'General Ward'}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-amber-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Wing</label>
                                <input type="text" id="${formId}-bed-wing" value="${prefill.wing || 'Wing A'}"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-amber-500/50">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                            <select id="${formId}-bed-status" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-amber-500/50">
                                <option value="Available" ${prefill.status === 'Available' ? 'selected' : ''}>Available</option>
                                <option value="Occupied" ${prefill.status === 'Occupied' ? 'selected' : ''}>Occupied</option>
                                <option value="Maintenance" ${prefill.status === 'Maintenance' ? 'selected' : ''}>Maintenance</option>
                            </select>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Submit Changes</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'book_appointment') {
            let docOptions = '<option value="">-- Choose Specialist Doctor --</option>';
            doctors.forEach(d => {
                docOptions += `<option value="${d.id}">${d.name} (${d.department})</option>`;
            });

            let typeOptions = '';
            types.forEach(t => {
                typeOptions += `<option value="${t}">${t}</option>`;
            });

            let slotOptions = '';
            slots.forEach(s => {
                slotOptions += `<option value="${s}">${s}</option>`;
            });

            formHtml = `
                <div class="bg-gradient-to-br from-white to-indigo-50/40 border border-indigo-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-indigo-100">
                        <div class="flex items-center gap-2 text-indigo-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-calendar-plus"></i>
                            </span>
                            <span>Book Consultation Form</span>
                        </div>
                        <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full uppercase tracking-wider">Live Booking</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitBookingForm('${formId}');">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Select Doctor *</label>
                            <select id="${formId}-doctor" required class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-indigo-500/50">
                                ${docOptions}
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Patient MRN or Full Name *</label>
                            <div class="relative">
                                <input type="text" id="${formId}-patient" required placeholder="e.g. PAT-1001 or Aarav Patel" autocomplete="off"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-indigo-500/50">
                                <div id="${formId}-pat-hints" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-36 overflow-y-auto divide-y divide-slate-100 text-xs"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Date *</label>
                                <input type="date" id="${formId}-date" value="${todayStr}" min="${todayStr}" required
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Time Slot *</label>
                                <select id="${formId}-slot" required class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/50">
                                    ${slotOptions}
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Consultation Type</label>
                                <select id="${formId}-type" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/50">
                                    ${typeOptions}
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Chief Symptoms</label>
                                <input type="text" id="${formId}-symptoms" placeholder="e.g. Chest pain, Fever..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/50">
                            </div>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-check"></i> <span>Confirm & Book Appointment</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'admit_patient') {
            let bedOptions = '<option value="">-- Choose Available Bed --</option>';
            beds.forEach(b => {
                bedOptions += `<option value="${b.bed_number}">${b.bed_number} (${b.type} - ${b.wing})</option>`;
            });

            let docOptions = '<option value="">-- Attending Doctor --</option>';
            doctors.forEach(d => {
                docOptions += `<option value="${d.id}">${d.name}</option>`;
            });

            formHtml = `
                <div class="bg-gradient-to-br from-white to-rose-50/40 border border-rose-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-rose-100">
                        <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-bed-pulse"></i>
                            </span>
                            <span>Inpatient Bed Admission Form</span>
                        </div>
                        <span class="text-[10px] font-bold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full uppercase tracking-wider">Admission</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitAdmissionForm('${formId}');">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Select Available Bed *</label>
                            <select id="${formId}-bed" required class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-rose-500/50">
                                ${bedOptions}
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Patient MRN or Name *</label>
                            <input type="text" id="${formId}-patient" required placeholder="e.g. PAT-1022 or Patient Name" autocomplete="off"
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-rose-500/50">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Attending Doctor</label>
                            <select id="${formId}-doctor" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-rose-500/50">
                                ${docOptions}
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Clinical Admission Reason *</label>
                            <input type="text" id="${formId}-reason" required placeholder="e.g. Acute coronary syndrome, Pre-op femur fracture"
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-rose-500/50">
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-bed"></i> <span>Confirm Inpatient Admission</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'add_patient') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-teal-50/40 border border-teal-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-teal-100">
                        <div class="flex items-center gap-2 text-teal-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-user-plus"></i>
                            </span>
                            <span>Register New Patient</span>
                        </div>
                        <span class="text-[10px] font-bold bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full uppercase tracking-wider">New Record</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitPatientAddForm('${formId}');">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">First Name *</label>
                                <input type="text" id="${formId}-addpat-name" value="${prefill.name || ''}" required placeholder="First name"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Surname</label>
                                <input type="text" id="${formId}-addpat-surname" value="${prefill.surname || ''}" placeholder="Surname"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Gender *</label>
                                <select id="${formId}-addpat-gender" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                                    <option value="Male" ${prefill.gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${prefill.gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${prefill.gender === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Blood Group *</label>
                                <select id="${formId}-addpat-bg" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                                    <option value="A+" ${prefill.blood_group === 'A+' ? 'selected' : ''}>A+</option>
                                    <option value="A-" ${prefill.blood_group === 'A-' ? 'selected' : ''}>A-</option>
                                    <option value="B+" ${prefill.blood_group === 'B+' ? 'selected' : ''}>B+</option>
                                    <option value="B-" ${prefill.blood_group === 'B-' ? 'selected' : ''}>B-</option>
                                    <option value="AB+" ${prefill.blood_group === 'AB+' ? 'selected' : ''}>AB+</option>
                                    <option value="AB-" ${prefill.blood_group === 'AB-' ? 'selected' : ''}>AB-</option>
                                    <option value="O+" ${prefill.blood_group === 'O+' ? 'selected' : ''}>O+</option>
                                    <option value="O-" ${prefill.blood_group === 'O-' ? 'selected' : ''}>O-</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Age *</label>
                                <input type="number" id="${formId}-addpat-age" value="${prefill.age || '30'}" required min="0" max="120"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone Number</label>
                                <input type="text" id="${formId}-addpat-phone" value="${prefill.phone || ''}" placeholder="+91 98..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-teal-500/50">
                            </div>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-user-check"></i> <span>Register Patient</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'add_doctor') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-sky-50/40 border border-sky-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-sky-100">
                        <div class="flex items-center gap-2 text-sky-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-sky-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-user-doctor"></i>
                            </span>
                            <span>Onboard New Doctor</span>
                        </div>
                        <span class="text-[10px] font-bold bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full uppercase tracking-wider">New Specialist</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitDoctorAddForm('${formId}');">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Doctor Full Name *</label>
                            <input type="text" id="${formId}-adddoc-name" value="${prefill.name || 'Dr. '}" required placeholder="Dr. Firstname Lastname"
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-sky-500/50">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Department</label>
                                <input type="text" id="${formId}-adddoc-dept" value="${prefill.department || 'General Medicine'}" placeholder="Cardiology, Ortho..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-sky-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Degree / Qualification</label>
                                <input type="text" id="${formId}-adddoc-degree" value="${prefill.degree || 'MBBS, MD'}" placeholder="MBBS, MS..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-sky-500/50">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Experience</label>
                                <input type="text" id="${formId}-adddoc-exp" value="${prefill.experience || '5 Years'}" placeholder="Years of exp"
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-sky-500/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone Number</label>
                                <input type="text" id="${formId}-adddoc-phone" value="${prefill.phone || ''}" placeholder="+91 98..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-sky-500/50">
                            </div>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-stethoscope"></i> <span>Add Doctor to System</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        } else if (formType === 'add_bed') {
            formHtml = `
                <div class="bg-gradient-to-br from-white to-violet-50/40 border border-violet-200 rounded-2xl p-4 shadow-md w-full relative">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-violet-100">
                        <div class="flex items-center gap-2 text-violet-700 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-violet-600 text-white flex items-center justify-center text-xs">
                                <i class="fa-solid fa-bed"></i>
                            </span>
                            <span>Create New Bed / Room</span>
                        </div>
                        <span class="text-[10px] font-bold bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full uppercase tracking-wider">Inventory</span>
                    </div>

                    <form id="${formId}-form" class="space-y-3" onsubmit="event.preventDefault(); BhoomaAI.submitBedAddForm('${formId}');">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Bed Number *</label>
                            <input type="text" id="${formId}-addbed-num" value="${prefill.bed_number || ''}" required placeholder="e.g. ICU-11, GEN-126"
                                class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-violet-500/50">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Ward Type</label>
                                <select id="${formId}-addbed-type" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-violet-500/50">
                                    <option value="ICU">ICU</option>
                                    <option value="General Ward" selected>General Ward</option>
                                    <option value="Private">Private</option>
                                    <option value="Semi-Private">Semi-Private</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Wing</label>
                                <input type="text" id="${formId}-addbed-wing" value="${prefill.wing || 'North Wing'}" placeholder="North Wing, Critical Care..."
                                    class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-2 py-1.5 outline-none focus:ring-2 focus:ring-violet-500/50">
                            </div>
                        </div>

                        <div id="${formId}-err" class="hidden text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2 font-medium"></div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" id="${formId}-submit" class="flex-1 bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-3 rounded-xl text-xs shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i> <span>Add Bed to Inventory</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${formId}').remove();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            `;
        }

        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid fa-receipt"></i>
            </div>
            ${formHtml}
        `;

        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();

        const patInput = document.getElementById(`${formId}-patient`);
        const patHints = document.getElementById(`${formId}-pat-hints`);
        if (patInput && patHints) {
            let debounce = null;
            patInput.addEventListener('input', () => {
                clearTimeout(debounce);
                const q = patInput.value.trim();
                if (q.length < 1) {
                    patHints.classList.add('hidden');
                    return;
                }
                debounce = setTimeout(async () => {
                    try {
                        const res = await fetch(`${this.apiUrl}?action=patient_quick_search&query=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        if (data.patients && data.patients.length > 0) {
                            patHints.innerHTML = '';
                            data.patients.forEach(p => {
                                const item = document.createElement('div');
                                item.className = 'p-2 hover:bg-indigo-50 cursor-pointer flex justify-between items-center';
                                item.innerHTML = `<span><b>${p.id}</b> - ${p.name} ${p.surname||''}</span><span class="text-[10px] text-slate-400">${p.phone||''}</span>`;
                                item.onclick = () => {
                                    patInput.value = `${p.id} (${p.name} ${p.surname||''})`;
                                    patHints.classList.add('hidden');
                                };
                                patHints.appendChild(item);
                            });
                            patHints.classList.remove('hidden');
                        } else {
                            patHints.classList.add('hidden');
                        }
                    } catch(e) {}
                }, 150);
            });
        }
    },

    async submitBookingForm(formId) {
        const docEl = document.getElementById(`${formId}-doctor`);
        const patEl = document.getElementById(`${formId}-patient`);
        const dateEl = document.getElementById(`${formId}-date`);
        const slotEl = document.getElementById(`${formId}-slot`);
        const typeEl = document.getElementById(`${formId}-type`);
        const sympEl = document.getElementById(`${formId}-symptoms`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        errEl.classList.add('hidden');

        if (!docEl.value) {
            errEl.textContent = 'Please choose a specialist doctor.';
            errEl.classList.remove('hidden');
            return;
        }
        if (!patEl.value.trim()) {
            errEl.textContent = 'Please enter a patient MRN or name.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Booking...`;

        try {
            const formData = new FormData();
            formData.append('action', 'book_appointment_form');
            formData.append('doctor_id', docEl.value);
            formData.append('patient_id', patEl.value.trim());
            formData.append('date', dateEl.value);
            formData.append('slot', slotEl.value);
            formData.append('type', typeEl ? typeEl.value : 'General Consultation');
            formData.append('symptoms', sympEl ? sympEl.value : '');

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 shadow-sm w-full">
                            <div class="flex items-center justify-between pb-2 border-b border-emerald-200 mb-2">
                                <span class="font-bold text-emerald-800 text-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Appointment Confirmed #APP-${data.appointment_id}
                                </span>
                                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Pre-Booked</span>
                            </div>
                            <div class="text-xs text-slate-700 space-y-1">
                                <div><b>Patient:</b> ${data.patient_name} <span class="text-slate-400">(${data.patient_id})</span></div>
                                <div><b>Doctor:</b> ${data.doctor_name}</div>
                                <div><b>Schedule:</b> ${data.date} at <b>${data.slot}</b> (${data.type})</div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-emerald-100 flex gap-2">
                                <a href="appointments.php?date=${data.date}" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-up-right-from-square text-[10px]"></i> View in Appointments Hub
                                </a>
                            </div>
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **Appointment #APP-${data.appointment_id} confirmed** for ${data.patient_name} with ${data.doctor_name} on ${data.date} at ${data.slot}.`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-check"></i> Confirm & Book Appointment`;
                errEl.textContent = data.message || 'Failed to book appointment.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check"></i> Confirm & Book Appointment`;
            errEl.textContent = 'Server connection error. Please try again.';
            errEl.classList.remove('hidden');
        }
    },

    async submitAdmissionForm(formId) {
        const bedEl = document.getElementById(`${formId}-bed`);
        const patEl = document.getElementById(`${formId}-patient`);
        const docEl = document.getElementById(`${formId}-doctor`);
        const reasonEl = document.getElementById(`${formId}-reason`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        errEl.classList.add('hidden');

        if (!bedEl.value) {
            errEl.textContent = 'Please choose an available bed.';
            errEl.classList.remove('hidden');
            return;
        }
        if (!patEl.value.trim()) {
            errEl.textContent = 'Please provide the patient MRN or name.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Admitting...`;

        try {
            const formData = new FormData();
            formData.append('action', 'admit_patient_form');
            formData.append('bed_number', bedEl.value);
            formData.append('patient_id', patEl.value.trim());
            formData.append('doctor_id', docEl ? docEl.value : '');
            formData.append('reason', reasonEl ? reasonEl.value : 'Clinical Inpatient Care');

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-bed-pulse"></i>
                        </div>
                        <div class="bg-rose-50 border-2 border-rose-200 rounded-2xl p-4 shadow-sm w-full">
                            <div class="flex items-center justify-between pb-2 border-b border-rose-200 mb-2">
                                <span class="font-bold text-rose-800 text-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-check text-rose-600"></i> Patient Admitted to Bed ${data.bed_number}
                                </span>
                                <span class="text-[10px] bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-bold">Occupied</span>
                            </div>
                            <div class="text-xs text-slate-700 space-y-1">
                                <div><b>Patient:</b> ${data.patient_name} <span class="text-slate-400">(${data.patient_id})</span></div>
                                <div><b>Ward:</b> ${data.bed_type} Bed ${data.bed_number}</div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-rose-100 flex gap-2">
                                <a href="beds.php" class="text-xs text-rose-700 hover:text-rose-800 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-up-right-from-square text-[10px]"></i> View Inpatient Bed Board
                                </a>
                            </div>
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **Patient ${data.patient_name} successfully admitted** to ${data.bed_type} Bed **${data.bed_number}**.`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-bed"></i> Confirm Inpatient Admission`;
                errEl.textContent = data.message || 'Failed to admit patient.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-bed"></i> Confirm Inpatient Admission`;
            errEl.textContent = 'Server connection error. Please try again.';
            errEl.classList.remove('hidden');
        }
    },

    async submitDoctorUpdateForm(formId) {
        const idEl = document.getElementById(`${formId}-doc-id`);
        const nameEl = document.getElementById(`${formId}-doc-name`);
        const degEl = document.getElementById(`${formId}-doc-degree`);
        const expEl = document.getElementById(`${formId}-doc-exp`);
        const phEl = document.getElementById(`${formId}-doc-phone`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        if (!nameEl.value.trim()) {
            errEl.textContent = 'Please enter a valid doctor name.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...`;

        try {
            const formData = new FormData();
            formData.append('action', 'update_doctor_form');
            formData.append('doctor_id', idEl.value);
            formData.append('name', nameEl.value.trim());
            formData.append('degree', degEl.value.trim());
            formData.append('experience', expEl.value.trim());
            formData.append('phone', phEl.value.trim());

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **${nameEl.value.trim()}** profile updated in database.`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Submit Changes</span>`;
                errEl.textContent = data.message || 'Failed to update doctor.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error while saving changes.';
            errEl.classList.remove('hidden');
        }
    },

    async submitPatientUpdateForm(formId) {
        const idEl = document.getElementById(`${formId}-pat-id`);
        const nameEl = document.getElementById(`${formId}-pat-name`);
        const surEl = document.getElementById(`${formId}-pat-surname`);
        const phEl = document.getElementById(`${formId}-pat-phone`);
        const bgEl = document.getElementById(`${formId}-pat-bg`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        if (!nameEl.value.trim()) {
            errEl.textContent = 'First name is required.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...`;

        try {
            const formData = new FormData();
            formData.append('action', 'update_patient_form');
            formData.append('patient_id', idEl.value);
            formData.append('name', nameEl.value.trim());
            formData.append('surname', surEl.value.trim());
            formData.append('phone', phEl.value.trim());
            formData.append('blood_group', bgEl.value.trim());

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **${nameEl.value.trim()}** record updated in database.`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Submit Changes</span>`;
                errEl.textContent = data.message || 'Failed to update patient.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error.';
            errEl.classList.remove('hidden');
        }
    },

    async submitBedUpdateForm(formId) {
        const numEl = document.getElementById(`${formId}-bed-num`);
        const typeEl = document.getElementById(`${formId}-bed-type`);
        const wingEl = document.getElementById(`${formId}-bed-wing`);
        const statEl = document.getElementById(`${formId}-bed-status`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...`;

        try {
            const formData = new FormData();
            formData.append('action', 'update_bed_form');
            formData.append('bed_number', numEl.value);
            formData.append('type', typeEl.value.trim());
            formData.append('wing', wingEl.value.trim());
            formData.append('status', statEl.value);

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ Bed **${numEl.value}** updated to ${typeEl.value} (${statEl.value}).`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Submit Changes</span>`;
                errEl.textContent = data.message || 'Failed to update bed.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error.';
            errEl.classList.remove('hidden');
        }
    },

    async submitPatientAddForm(formId) {
        const nameEl = document.getElementById(`${formId}-addpat-name`);
        const surnameEl = document.getElementById(`${formId}-addpat-surname`);
        const genEl = document.getElementById(`${formId}-addpat-gender`);
        const bgEl = document.getElementById(`${formId}-addpat-bg`);
        const ageEl = document.getElementById(`${formId}-addpat-age`);
        const phEl = document.getElementById(`${formId}-addpat-phone`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        if (!nameEl.value.trim()) {
            errEl.textContent = 'Please enter patient name.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Registering...`;

        try {
            const formData = new FormData();
            formData.append('action', 'add_patient_form');
            formData.append('name', nameEl.value.trim());
            formData.append('surname', surnameEl.value.trim());
            formData.append('gender', genEl.value);
            formData.append('blood_group', bgEl.value);
            formData.append('age', ageEl.value);
            formData.append('phone', phEl.value.trim());

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="bg-teal-50 border border-teal-200 rounded-xl p-3 text-xs text-teal-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **Patient Registered:** ${nameEl.value.trim()} ${surnameEl.value.trim()} (Blood Group: ${bgEl.value}, Gender: ${genEl.value}, MRN: **${data.patient_id}**).`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Register Patient</span>`;
                errEl.textContent = data.message || 'Failed to register patient.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error.';
            errEl.classList.remove('hidden');
        }
    },

    async submitDoctorAddForm(formId) {
        const nameEl = document.getElementById(`${formId}-adddoc-name`);
        const deptEl = document.getElementById(`${formId}-adddoc-dept`);
        const degEl = document.getElementById(`${formId}-adddoc-degree`);
        const expEl = document.getElementById(`${formId}-adddoc-exp`);
        const phEl = document.getElementById(`${formId}-adddoc-phone`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        if (!nameEl.value.trim()) {
            errEl.textContent = 'Please enter doctor name.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Adding Doctor...`;

        try {
            const formData = new FormData();
            formData.append('action', 'add_doctor_form');
            formData.append('name', nameEl.value.trim());
            formData.append('department', deptEl.value.trim());
            formData.append('degree', degEl.value.trim());
            formData.append('experience', expEl.value.trim());
            formData.append('phone', phEl.value.trim());

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-sky-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 text-xs text-sky-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **Doctor Added:** ${nameEl.value.trim()} (${deptEl.value.trim()} - ${degEl.value.trim()}).`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Add Doctor to System</span>`;
                errEl.textContent = data.message || 'Failed to add doctor.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error.';
            errEl.classList.remove('hidden');
        }
    },

    async submitBedAddForm(formId) {
        const numEl = document.getElementById(`${formId}-addbed-num`);
        const typeEl = document.getElementById(`${formId}-addbed-type`);
        const wingEl = document.getElementById(`${formId}-addbed-wing`);
        const btn = document.getElementById(`${formId}-submit`);
        const errEl = document.getElementById(`${formId}-err`);

        if (!numEl.value.trim()) {
            errEl.textContent = 'Please enter bed number.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Creating Bed...`;

        try {
            const formData = new FormData();
            formData.append('action', 'add_bed_form');
            formData.append('bed_number', numEl.value.trim());
            formData.append('type', typeEl.value);
            formData.append('wing', wingEl.value.trim());

            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                const card = document.getElementById(formId);
                if (card) {
                    card.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-violet-500 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 text-xs text-violet-900 w-full font-semibold">
                            ✅ ${data.message}
                        </div>
                    `;
                }
                this.appendMessage('assistant', `✅ **Bed Created:** Bed **${numEl.value.trim()}** (${typeEl.value}, ${wingEl.value.trim()}) added as Available.`);
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span>Add Bed to Inventory</span>`;
                errEl.textContent = data.message || 'Failed to create bed.';
                errEl.classList.remove('hidden');
            }
        } catch(e) {
            btn.disabled = false;
            errEl.textContent = 'Network error.';
            errEl.classList.remove('hidden');
        }
    },
    
    renderInlineSuggestions(suggestions) {
        if (!suggestions || !suggestions.length) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-wrap gap-1.5 ml-8 mt-1 mb-2';
        suggestions.forEach(text => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-lg text-xs font-medium transition-colors shadow-2xs cursor-pointer';
            btn.innerHTML = `<i class="fa-regular fa-paper-plane mr-1 text-[10px]"></i> ${text}`;
            btn.onclick = () => {
                this.dom.input.value = text;
                this.autoResize(this.dom.input);
                this.handleSend();
            };
            wrapper.appendChild(btn);
        });
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
    },
    
    async confirmAction(actionId) {
        const card = document.getElementById(`action-card-${actionId}`);
        if (card) {
            card.innerHTML = `<div class="text-xs text-slate-500 flex items-center"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Executing...</div>`;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'confirm');
            formData.append('action_id', actionId);
            
            const res = await fetch(this.apiUrl, { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                if (card) {
                    card.className = 'bg-emerald-50 border border-emerald-200 py-2 px-3 rounded-xl shadow-sm w-full';
                    card.innerHTML = `<div class="text-xs text-emerald-700 font-semibold flex items-center"><i class="fa-solid fa-check-circle mr-1.5"></i> Action Executed Successfully</div>`;
                }
                this.appendMessage('assistant', '✅ ' + (data.message || 'Action executed successfully.'));
            } else {
                if (card) {
                    card.className = 'bg-rose-50 border border-rose-200 py-2 px-3 rounded-xl shadow-sm w-full';
                    card.innerHTML = `<div class="text-xs text-rose-700 font-semibold flex items-center"><i class="fa-solid fa-xmark-circle mr-1.5"></i> Execution Failed</div>`;
                }
                this.appendMessage('assistant', '❌ ' + (data.message || 'Failed to execute action.'));
            }
        } catch (e) {
            console.error(e);
            if (card) {
                card.innerHTML = `<div class="text-xs text-rose-700">Error connecting to server.</div>`;
            }
        }
    },
    
    async cancelAction(actionId) {
        const card = document.getElementById(`action-card-${actionId}`);
        if (card) {
            card.className = 'bg-slate-50 border border-slate-200 py-2 px-3 rounded-xl shadow-sm w-full';
            card.innerHTML = `<div class="text-xs text-slate-500 flex items-center"><i class="fa-solid fa-ban mr-1.5"></i> Action Cancelled</div>`;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('action_id', actionId);
            await fetch(this.apiUrl, { method: 'POST', body: formData });
        } catch (e) {
            console.error(e);
        }
    },
    
    newConversation() {
        this.conversationId = null;
        localStorage.removeItem('bhooma_ai_conv');
        
        if (this.dom.messages) {
            this.dom.messages.innerHTML = '';
            this.renderWelcomeMessage();
        }
        
        if (this.dom.suggestions) {
            this.dom.suggestions.classList.remove('hidden');
            this.renderSuggestions();
        }
        setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 100);
    }
};

window.bhoomaToggleAI = function(state) {
    if (window.BhoomaAI) {
        window.BhoomaAI.toggle(state);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => BhoomaAI.init());
} else {
    BhoomaAI.init();
}
</script>

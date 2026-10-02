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
/* Chatbot Animations & Styles */
@keyframes bhooma-slide-in {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes bhooma-pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
}

@keyframes bhooma-bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.bhooma-chat-panel {
    animation: bhooma-slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transform-origin: bottom right;
}

.bhooma-btn-pulse {
    animation: bhooma-pulse 2s infinite;
}

.bhooma-typing-dot {
    animation: bhooma-bounce 1s infinite;
}
.bhooma-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.bhooma-typing-dot:nth-child(3) { animation-delay: 0.4s; }

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

.bhooma-markdown p { margin-bottom: 0.5rem; }
.bhooma-markdown p:last-child { margin-bottom: 0; }
.bhooma-markdown strong { font-weight: 600; }
.bhooma-markdown ul { list-style-type: disc; padding-left: 1.2rem; margin-bottom: 0.5rem; }
.bhooma-markdown ol { list-style-type: decimal; padding-left: 1.2rem; margin-bottom: 0.5rem; }
.bhooma-markdown code { background: rgba(0,0,0,0.1); padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-family: monospace; font-size: 0.9em; }
.bhooma-markdown pre code { display: block; padding: 0.5rem; overflow-x: auto; margin-bottom: 0.5rem; }
</style>

<!-- Floating AI Button Container -->
<div id="bhooma-ai-btn-container" class="fixed bottom-6 right-6" style="z-index: 999999 !important;">
    <button id="bhooma-ai-toggle" type="button" title="BHOOMA AI Assistant (Ctrl+K)" 
        class="bhooma-btn-pulse flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-teal-500 text-white shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
        <i class="fa-solid fa-robot text-2xl pointer-events-none"></i>
        <span id="bhooma-ai-badge" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
            1
        </span>
    </button>
</div>

<!-- Chat Panel -->
<div id="bhooma-ai-panel" class="hidden fixed bottom-24 right-6 w-[420px] h-[600px] max-h-[80vh] max-w-[calc(100vw-3rem)] rounded-2xl shadow-2xl border border-slate-200 bg-white/95 backdrop-blur-md flex flex-col overflow-hidden sm:right-6 right-0 left-0 mx-auto sm:mx-0 sm:left-auto sm:w-[420px]" style="display: none; z-index: 999999 !important;">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-teal-500 p-4 text-white flex items-center justify-between cursor-move" id="bhooma-ai-header">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center relative">
                <i class="fa-solid fa-robot"></i>
                <div id="bhooma-ai-status-dot" class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-indigo-600" title="Online"></div>
            </div>
            <div>
                <h3 class="font-bold text-sm leading-tight">BHOOMA AI</h3>
                <div class="flex items-center space-x-1">
                    <span class="text-[10px] bg-white/20 px-1.5 py-0.5 rounded-md" id="bhooma-ai-model">Qwen 2.5</span>
                    <span id="bhooma-ai-status-text" class="text-[10px] text-white/80">Ready</span>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-1">
            <button id="bhooma-ai-new" type="button" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="New Chat">
                <i class="fa-solid fa-plus pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-minimize" type="button" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="Minimize">
                <i class="fa-solid fa-minus pointer-events-none"></i>
            </button>
            <button id="bhooma-ai-close" type="button" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="Close">
                <i class="fa-solid fa-xmark pointer-events-none"></i>
            </button>
        </div>
    </div>

    <!-- Suggestions Bar -->
    <div id="bhooma-ai-suggestions" class="bg-slate-50 border-b border-slate-100 p-2 flex overflow-x-auto bhooma-scrollbar gap-2 hide-when-chatting">
        <!-- populated by JS -->
    </div>

    <!-- Messages Area -->
    <div id="bhooma-ai-messages" class="flex-1 overflow-y-auto bhooma-scrollbar p-4 space-y-4 bg-slate-50/50">
        <!-- Welcome Message -->
        <div class="flex items-start max-w-[85%]">
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="ml-2 bg-white border border-slate-200 text-slate-700 text-sm py-2.5 px-3.5 rounded-2xl rounded-tl-sm shadow-sm space-y-2">
                <p>Hello <?php echo htmlspecialchars($chatbot_user_role); ?>! I'm BHOOMA AI. How can I help you with <?php echo htmlspecialchars($chatbot_hospital_name); ?> today?</p>
                <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
                    <button type="button" onclick="BhoomaAI.renderInChatForm('book_appointment');" class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-calendar-plus text-[11px]"></i> Book Appointment Form
                    </button>
                    <button type="button" onclick="BhoomaAI.renderInChatForm('admit_patient');" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-bed-pulse text-[11px]"></i> Inpatient Bed Admission
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-slate-200 bg-white p-3 relative">
        <!-- Live As-You-Type Suggestions Floating Popup -->
        <div id="bhooma-ai-live-suggestions" class="absolute bottom-full left-3 right-3 mb-2 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-xl overflow-hidden hidden z-50 divide-y divide-slate-100 max-h-56 overflow-y-auto bhooma-scrollbar">
            <!-- populated dynamically by JS as you type -->
        </div>

        <div class="relative flex items-end bg-slate-50 border border-slate-300 rounded-xl focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
            <textarea id="bhooma-ai-input" rows="1" 
                class="w-full bg-transparent border-0 focus:ring-0 resize-none max-h-[100px] text-sm p-3 bhooma-scrollbar" 
                placeholder="Ask BHOOMA AI anything... (Enter to send)"></textarea>
            <button id="bhooma-ai-send" type="button" class="shrink-0 w-8 h-8 mb-2 mr-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-paper-plane text-xs pointer-events-none"></i>
            </button>
        </div>
        <div class="flex justify-between items-center mt-1 px-1">
            <div class="text-[10px] text-slate-400">Press <kbd class="bg-slate-100 px-1 rounded border">Enter</kbd> to send</div>
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

        // Event delegation for confirm/cancel buttons
        if (this.dom.messages) {
            this.dom.messages.addEventListener('click', (e) => {
                const confBtn = e.target.closest('.bhooma-confirm-btn');
                const cancBtn = e.target.closest('.bhooma-cancel-btn');
                if (confBtn) {
                    this.confirmAction(confBtn.dataset.id);
                } else if (cancBtn) {
                    this.cancelAction(cancBtn.dataset.id);
                }
            });
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
            this.conversationId = saved;
            if (this.dom.suggestions) this.dom.suggestions.classList.add('hidden');
        }
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
            this.dom.input.value = item.text;
            this.hideLiveSuggestions();
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
        let formatted = clean
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>')
            .replace(/```([\s\S]*?)```/g, '<pre class="bg-slate-800 text-white p-2 rounded text-xs overflow-x-auto my-1"><code>$1</code></pre>')
            .replace(/`([^`]+)`/g, '<code class="bg-indigo-50 text-indigo-700 px-1 py-0.5 rounded font-mono text-xs">$1</code>');
        return formatted;
    },
    
    appendMessage(role, content) {
        const isUser = role === 'user';
        const msgId = 'msg-' + Date.now() + '-' + Math.floor(Math.random()*1000);
        
        const wrapper = document.createElement('div');
        wrapper.className = `flex items-start max-w-[85%] ${isUser ? 'ml-auto flex-row-reverse' : ''}`;
        wrapper.id = msgId;
        
        let html = '';
        if (isUser) {
            html = `
                <div class="ml-2 mr-0 bg-gradient-to-r from-indigo-600 to-blue-500 text-white text-sm py-2 px-3 rounded-2xl rounded-tr-sm shadow-sm bhooma-markdown w-full">
                    ${this.formatMarkdown(content)}
                </div>
            `;
        } else {
            html = `
                <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm ${isUser ? 'ml-2' : 'mr-2'}">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="content-box bg-white border border-slate-200 text-slate-700 text-sm py-2 px-3 rounded-2xl rounded-tl-sm shadow-sm bhooma-markdown w-full overflow-x-auto">
                    ${this.formatMarkdown(content)}
                </div>
            `;
        }
        
        wrapper.innerHTML = html;
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
        return msgId;
    },
    
    showTyping() {
        const wrapper = document.createElement('div');
        wrapper.id = 'bhooma-typing';
        wrapper.className = 'flex items-start max-w-[85%]';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200 py-3 px-4 rounded-2xl rounded-tl-sm shadow-sm flex space-x-1 items-center h-[38px]">
                <div class="w-2 h-2 bg-slate-400 rounded-full bhooma-typing-dot"></div>
                <div class="w-2 h-2 bg-slate-400 rounded-full bhooma-typing-dot"></div>
                <div class="w-2 h-2 bg-slate-400 rounded-full bhooma-typing-dot"></div>
            </div>
        `;
        this.dom.messages.appendChild(wrapper);
        this.scrollToBottom();
    },
    
    hideTyping() {
        const el = document.getElementById('bhooma-typing');
        if (el) el.remove();
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
            
            this.hideTyping();
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            // Handle SSE Stream
            const reader = response.body.getReader();
            const decoder = new TextDecoder('utf-8');
            let aiMsgId = null;
            let aiMsgContent = '';
            let isFirstChunk = true;
            
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                
                const chunk = decoder.decode(value, { stream: true });
                const lines = chunk.split('\n');
                
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const dataStr = line.replace('data: ', '').trim();
                        if (!dataStr) continue;
                        if (dataStr === '[DONE]') continue;
                        
                        try {
                            const data = JSON.parse(dataStr);
                            
                            if (data.type === 'chunk') {
                                if (isFirstChunk) {
                                    aiMsgId = this.appendMessage('assistant', '');
                                    isFirstChunk = false;
                                }
                                aiMsgContent += data.content;
                                const msgEl = document.querySelector(`#${aiMsgId} .content-box`);
                                if (msgEl) {
                                    msgEl.innerHTML = this.formatMarkdown(aiMsgContent);
                                    this.scrollToBottom();
                                }
                            } 
                            else if (data.type === 'data') {
                                this.renderDataTable(data.content || data.results, data.sql);
                            }
                            else if (data.type === 'action') {
                                this.renderActionCard(data.action_id, data.content);
                            }
                            else if (data.type === 'form') {
                                this.renderInChatForm(data.form_type, data);
                            }
                            else if (data.type === 'suggestions') {
                                this.renderInlineSuggestions(data.suggestions);
                            }
                            else if (data.type === 'done') {
                                if (data.conversation_id) {
                                    this.conversationId = data.conversation_id;
                                    localStorage.setItem('bhooma_ai_conv', this.conversationId);
                                }
                            }
                            else if (data.type === 'error') {
                                this.appendMessage('assistant', '⚠️ ' + data.message);
                            }
                        } catch (e) {
                            console.error('Error parsing SSE data:', e, dataStr);
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTyping();
            this.appendMessage('assistant', 'Sorry, I encountered an error. Please try again later.');
        } finally {
            this.isLoading = false;
            if (this.dom.send) this.dom.send.disabled = false;
            setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 100);
        }
    },
    
    renderDataTable(dataArr) {
        if (!dataArr || !dataArr.length) return;
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
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[85%] mt-2 mb-2';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-amber-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="bg-amber-50 border-2 border-amber-200 py-3 px-4 rounded-xl shadow-sm w-full" id="action-card-${actionId}">
                <h4 class="font-bold text-amber-800 text-sm mb-1">Confirm Action</h4>
                <p class="text-xs text-amber-700 mb-3">${details}</p>
                <div class="flex space-x-2">
                    <button type="button" class="bhooma-confirm-btn bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm transition-colors flex-1 cursor-pointer" data-id="${actionId}">
                        <i class="fa-solid fa-check mr-1"></i> Confirm
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

        let formHtml = '';

        if (formType === 'book_appointment') {
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
    
    renderInlineSuggestions(suggestions) {
        if (!suggestions || !suggestions.length) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-wrap gap-1.5 ml-8 mt-1 mb-2';
        suggestions.forEach(text => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-lg text-xs font-medium transition-colors shadow-2xs cursor-pointer';
            btn.innerHTML = `<i class="fa-regular fa-paper-plane mr-1 text-[10px]"></i> \${text}`;
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
            while (this.dom.messages.children.length > 1) {
                this.dom.messages.removeChild(this.dom.messages.lastChild);
            }
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

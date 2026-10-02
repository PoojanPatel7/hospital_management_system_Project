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

<!-- Floating AI Button -->
<div id="bhooma-ai-btn-container" class="fixed bottom-6 right-6 z-[9999]">
    <button id="bhooma-ai-toggle" onclick="BhoomaAI.toggle()" title="BHOOMA AI Assistant (Ctrl+K)" 
        class="bhooma-btn-pulse flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-teal-500 text-white shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 cursor-pointer">
        <i class="fa-solid fa-robot text-2xl pointer-events-none"></i>
        <span id="bhooma-ai-badge" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
            1
        </span>
    </button>
</div>

<!-- Chat Panel -->
<div id="bhooma-ai-panel" class="hidden fixed bottom-24 right-6 z-[9999] w-[420px] h-[600px] max-h-[80vh] max-w-[calc(100vw-3rem)] rounded-2xl shadow-2xl border border-slate-200 bg-white/95 backdrop-blur-md flex flex-col overflow-hidden sm:right-6 right-0 left-0 mx-auto sm:mx-0 sm:left-auto sm:w-[420px]">
    
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
            <button id="bhooma-ai-new" onclick="BhoomaAI.newConversation()" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="New Chat">
                <i class="fa-solid fa-plus"></i>
            </button>
            <button id="bhooma-ai-minimize" onclick="BhoomaAI.toggle(false)" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="Minimize">
                <i class="fa-solid fa-minus"></i>
            </button>
            <button id="bhooma-ai-close" onclick="BhoomaAI.toggle(false)" class="w-8 h-8 rounded-full hover:bg-white/20 flex items-center justify-center transition-colors text-xs" title="Close">
                <i class="fa-solid fa-xmark"></i>
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
            <div class="ml-2 bg-white border border-slate-200 text-slate-700 text-sm py-2 px-3 rounded-2xl rounded-tl-sm shadow-sm">
                <p>Hello <?php echo htmlspecialchars($chatbot_user_role); ?>! I'm BHOOMA AI. How can I help you with <?php echo htmlspecialchars($chatbot_hospital_name); ?> today?</p>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-slate-200 bg-white p-3">
        <div class="relative flex items-end bg-slate-50 border border-slate-300 rounded-xl focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
            <textarea id="bhooma-ai-input" rows="1" 
                class="w-full bg-transparent border-0 focus:ring-0 resize-none max-h-[100px] text-sm p-3 bhooma-scrollbar" 
                placeholder="Ask BHOOMA AI anything... (Enter to send)"></textarea>
            <button id="bhooma-ai-send" onclick="BhoomaAI.handleSend()" class="shrink-0 w-8 h-8 mb-2 mr-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-paper-plane text-xs"></i>
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
    messages: [],
    currentPage: '<?php echo addslashes($chatbot_page); ?>',
    suggestions: <?php echo $chatbot_suggestions; ?>,
    apiUrl: 'api/chatbot.php',
    
    init() {
        this.cacheDOM();
        this.bindEvents();
        this.loadConversation();
        this.renderSuggestions();
        this.checkStatus();
        if (this.dom.input) this.autoResize(this.dom.input);
    },
    
    cacheDOM() {
        this.dom = {
            toggle: document.getElementById('bhooma-ai-toggle'),
            panel: document.getElementById('bhooma-ai-panel'),
            close: document.getElementById('bhooma-ai-close'),
            minimize: document.getElementById('bhooma-ai-minimize'),
            newBtn: document.getElementById('bhooma-ai-new'),
            messages: document.getElementById('bhooma-ai-messages'),
            input: document.getElementById('bhooma-ai-input'),
            send: document.getElementById('bhooma-ai-send'),
            suggestions: document.getElementById('bhooma-ai-suggestions'),
            badge: document.getElementById('bhooma-ai-badge'),
            statusDot: document.getElementById('bhooma-ai-status-dot'),
            statusText: document.getElementById('bhooma-ai-status-text'),
            counter: document.getElementById('bhooma-ai-counter')
        };
    },
    
    bindEvents() {
        if (this.dom.toggle) this.dom.toggle.addEventListener('click', () => this.toggle());
        if (this.dom.close) this.dom.close.addEventListener('click', () => this.toggle(false));
        if (this.dom.minimize) this.dom.minimize.addEventListener('click', () => this.toggle(false));
        if (this.dom.newBtn) this.dom.newBtn.addEventListener('click', () => this.newConversation());
        
        if (this.dom.send) this.dom.send.addEventListener('click', () => this.handleSend());
        
        if (this.dom.input) {
            this.dom.input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.handleSend();
                }
            });
            
            this.dom.input.addEventListener('input', () => {
                this.autoResize(this.dom.input);
                this.updateCounter();
            });
        }
        
        // Ctrl+K shortcut
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === '/')) {
                e.preventDefault();
                this.toggle();
            }
        });

        // Event delegation for confirm/cancel buttons
        if (this.dom.messages) {
            this.dom.messages.addEventListener('click', (e) => {
                if (e.target.closest('.bhooma-confirm-btn')) {
                    const id = e.target.closest('.bhooma-confirm-btn').dataset.id;
                    this.confirmAction(id);
                } else if (e.target.closest('.bhooma-cancel-btn')) {
                    const id = e.target.closest('.bhooma-cancel-btn').dataset.id;
                    this.cancelAction(id);
                }
            });
        }
    },
    
    toggle(forceState = null) {
        if (!this.dom || !this.dom.panel) this.cacheDOM();
        this.isOpen = forceState !== null ? forceState : !this.isOpen;
        if (this.isOpen) {
            if (this.dom.panel) {
                this.dom.panel.classList.remove('hidden');
                this.dom.panel.classList.add('bhooma-chat-panel');
            }
            if (this.dom.badge) this.dom.badge.classList.add('hidden');
            if (this.dom.toggle) this.dom.toggle.classList.remove('bhooma-btn-pulse');
            setTimeout(() => { if (this.dom.input) this.dom.input.focus(); }, 100);
            this.scrollToBottom();
        } else {
            if (this.dom.panel) {
                this.dom.panel.classList.add('hidden');
                this.dom.panel.classList.remove('bhooma-chat-panel');
            }
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
            // Ideally fetch history here, but for now just hide suggestions if we have an ID
            this.dom.suggestions.classList.add('hidden');
        }
    },
    
    renderSuggestions() {
        if (!this.suggestions || this.suggestions.length === 0) return;
        this.dom.suggestions.innerHTML = '';
        this.suggestions.forEach(text => {
            const btn = document.createElement('button');
            btn.className = 'shrink-0 px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-colors whitespace-nowrap shadow-sm';
            btn.textContent = text;
            btn.onclick = () => {
                this.dom.input.value = text;
                this.autoResize(this.dom.input);
                this.handleSend();
            };
            this.dom.suggestions.appendChild(btn);
        });
    },
    
    autoResize(el) {
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight < 100 ? el.scrollHeight : 100) + 'px';
    },
    
    updateCounter() {
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
        this.dom.messages.scrollTop = this.dom.messages.scrollHeight;
    },
    
    formatTime(date = new Date()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    
    formatMarkdown(text) {
        if (!text) return '';
        let formatted = text
            .replace(/\\*\\*(.*?)\\*\\*/g, '<strong>$1</strong>')
            .replace(/\\*(.*?)\\*/g, '<em>$1</em>')
            .replace(/\\n/g, '<br>')
            .replace(/\`\`\`([\\s\\S]*?)\`\`\`/g, '<pre><code>$1</code></pre>')
            .replace(/\`([^\`]+)\`/g, '<code>$1</code>');
        return formatted;
    },
    
    appendMessage(role, content) {
        const isUser = role === 'user';
        const msgId = 'msg-' + Date.now();
        
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
        this.autoResize(this.dom.input);
        this.dom.suggestions.classList.add('hidden');
        this.updateCounter();
        
        this.appendMessage('user', text);
        this.showTyping();
        this.isLoading = true;
        this.dom.send.disabled = true;
        
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
                const lines = chunk.split('\\n');
                
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
                                this.renderDataTable(data.content, data.sql);
                            }
                            else if (data.type === 'action') {
                                this.renderActionCard(data.action_id, data.content);
                            }
                            else if (data.type === 'done') {
                                if (data.conversation_id) {
                                    this.conversationId = data.conversation_id;
                                    localStorage.setItem('bhooma_ai_conv', this.conversationId);
                                }
                            }
                            else if (data.type === 'error') {
                                this.appendMessage('assistant', 'âš ï¸ ' + data.message);
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
            this.dom.send.disabled = false;
            setTimeout(() => this.dom.input.focus(), 100);
        }
    },
    
    renderDataTable(dataArr, sql = null) {
        if (!dataArr || !dataArr.length) return;
        const columns = Object.keys(dataArr[0]);
        
        let tableHTML = `<div class="w-full overflow-x-auto mt-2 mb-2 border border-slate-200 rounded-lg">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>`;
        columns.forEach(col => {
            tableHTML += `<th class="px-3 py-2">${col}</th>`;
        });
        tableHTML += `</tr></thead><tbody class="divide-y divide-slate-100">`;
        
        dataArr.forEach((row, i) => {
            const bg = i % 2 === 0 ? 'bg-white' : 'bg-slate-50';
            tableHTML += `<tr class="${bg} hover:bg-indigo-50">`;
            columns.forEach(col => {
                tableHTML += `<td class="px-3 py-1.5 text-slate-700">${row[col] !== null ? row[col] : '-'}</td>`;
            });
            tableHTML += `</tr>`;
        });
        
        tableHTML += `</tbody></table></div>`;
        
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start max-w-[95%]';
        wrapper.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-sm mr-2">
                <i class="fa-solid fa-table"></i>
            </div>
            <div class="bg-white border border-slate-200 py-2 px-3 rounded-2xl rounded-tl-sm shadow-sm w-full overflow-x-auto">
                ${tableHTML}
                ${sql ? `<div class="text-[9px] text-slate-400 mt-1 font-mono cursor-pointer" onclick="this.textContent = '${sql.replace(/'/g, "\\'")}'">Show Query</div>` : ''}
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
                    <button class="bhooma-confirm-btn bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm transition-colors flex-1" data-id="${actionId}">
                        <i class="fa-solid fa-check mr-1"></i> Confirm
                    </button>
                    <button class="bhooma-cancel-btn bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm transition-colors flex-1" data-id="${actionId}">
                        Cancel
                    </button>
                </div>
            </div>
        `;
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
                this.appendMessage('assistant', 'âœ… ' + (data.message || 'Action executed successfully.'));
            } else {
                if (card) {
                    card.className = 'bg-rose-50 border border-rose-200 py-2 px-3 rounded-xl shadow-sm w-full';
                    card.innerHTML = `<div class="text-xs text-rose-700 font-semibold flex items-center"><i class="fa-solid fa-xmark-circle mr-1.5"></i> Execution Failed</div>`;
                }
                this.appendMessage('assistant', 'âŒ ' + (data.message || 'Failed to execute action.'));
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
        
        // Keep only the first welcome message
        while (this.dom.messages.children.length > 1) {
            this.dom.messages.removeChild(this.dom.messages.lastChild);
        }
        
        this.dom.suggestions.classList.remove('hidden');
        this.renderSuggestions();
        setTimeout(() => this.dom.input.focus(), 100);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => BhoomaAI.init());
} else {
    BhoomaAI.init();
}
</script>

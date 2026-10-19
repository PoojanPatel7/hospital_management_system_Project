<?php
// ai_logs.php - Gemini-Style Hospital AI Chat Studio, Live Conversations & Action Audit
require_once 'auth.php';
require_once 'db.php';
include 'includes/header.php';

$hospitalId = (int)($_SESSION['hospital_id'] ?? 1);
$hospitalName = $_SESSION['hospital_name'] ?? 'BHOOMA Medicare Hospital';
$userRole = $_SESSION['staff_role'] ?? 'Admin';
$currentUsername = $_SESSION['username'] ?? 'Admin';

// -------------------------------------------------------------
// 1. Executive Analytics & KPIs
// -------------------------------------------------------------
$resConv = $conn->query("SELECT COUNT(*) as total_conv, SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_conv FROM ai_conversations WHERE hospital_id = $hospitalId");
$rowConv = $resConv ? $resConv->fetch_assoc() : ['total_conv' => 0, 'today_conv' => 0];
$totalConversations = (int)($rowConv['total_conv'] ?? 0);
$todayConversations = (int)($rowConv['today_conv'] ?? 0);

$resMsg = $conn->query("SELECT COUNT(*) as total_msg FROM ai_chat_messages m JOIN ai_conversations c ON m.conversation_id = c.id WHERE c.hospital_id = $hospitalId");
$totalMessages = $resMsg ? (int)($resMsg->fetch_assoc()['total_msg'] ?? 0) : 0;

$resActs = $conn->query("SELECT COUNT(*) as total_acts, SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_acts FROM ai_action_log WHERE hospital_id = $hospitalId");
$rowActs = $resActs ? $resActs->fetch_assoc() : ['total_acts' => 0, 'success_acts' => 0];
$totalActions = (int)($rowActs['total_acts'] ?? 0);
$successActions = (int)($rowActs['success_acts'] ?? 0);
$actionSuccessRate = $totalActions > 0 ? round(($successActions / $totalActions) * 100, 1) : 100.0;

// -------------------------------------------------------------
// 2. Fetch Initial Conversations (for zero-latency initial load)
// -------------------------------------------------------------
$convQuery = "
    SELECT c.id, c.title, c.created_at, c.updated_at, c.user_id, c.user_role,
           COUNT(m.id) as message_count,
           (SELECT content FROM ai_chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as latest_snippet
    FROM ai_conversations c
    LEFT JOIN ai_chat_messages m ON c.id = m.conversation_id
    WHERE c.hospital_id = $hospitalId
    GROUP BY c.id
    ORDER BY c.updated_at DESC, c.id DESC
    LIMIT 50
";
$initialConversations = [];
if ($res = $conn->query($convQuery)) {
    while ($r = $res->fetch_assoc()) {
        $initialConversations[] = $r;
    }
}

// -------------------------------------------------------------
// 3. Fetch Action Audit Logs
// -------------------------------------------------------------
$auditLogs = [];
$auditQuery = "SELECT * FROM ai_action_log WHERE hospital_id = $hospitalId ORDER BY created_at DESC LIMIT 100";
if ($res = $conn->query($auditQuery)) {
    while ($r = $res->fetch_assoc()) {
        $auditLogs[] = $r;
    }
}
?>
<style>
@keyframes bhooma-heartbeat-anim {
    0% { transform: scale(1); }
    14% { transform: scale(1.18); filter: drop-shadow(0 0 8px rgba(244, 63, 94, 0.8)); }
    28% { transform: scale(1.02); }
    42% { transform: scale(1.24); filter: drop-shadow(0 0 12px rgba(244, 63, 94, 0.95)); }
    68% { transform: scale(1); filter: drop-shadow(0 0 4px rgba(244, 63, 94, 0.35)); }
    100% { transform: scale(1); }
}
.bhooma-live-heart {
    animation: bhooma-heartbeat-anim 1.4s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
    transform-origin: center center;
    will-change: transform;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

<div class="space-y-4">

  <!-- ================= TOP CONTROLS & MODE TOGGLE ================= -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 sm:p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-rose-500/25 shrink-0 bhooma-live-heart">
        <i class="fa-solid fa-heart-pulse"></i>
      </div>
      <div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight flex items-center gap-2">
          <span>Hospital AI Assistant Studio</span>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 hidden sm:inline-flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Ollama</span>
          </span>
        </h2>
        <p class="text-xs text-slate-500">Ask questions, replay past conversations, and inspect database audit actions.</p>
      </div>
    </div>

    <!-- Mode Switcher -->
    <div class="flex items-center gap-2 self-start sm:self-auto">
      <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 text-xs font-bold">
        <button type="button" id="btn-mode-chat" onclick="setDashboardMode('chat')" class="px-3.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5">
          <i class="fa-solid fa-message text-indigo-600"></i>
          <span>AI Chat</span>
        </button>
        <button type="button" id="btn-mode-audit" onclick="setDashboardMode('audit')" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5">
          <i class="fa-solid fa-clipboard-list text-slate-500"></i>
          <span>Action Logs (<?php echo count($auditLogs); ?>)</span>
        </button>
      </div>

      <button type="button" onclick="startNewChat()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>New Chat</span>
      </button>
    </div>
  </div>

  <!-- ================= MODE 1: GEMINI-STYLE AI CHAT & HISTORY ================= -->
  <div id="section-chat-mode" class="apple-card p-0 bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden flex flex-col md:flex-row h-[78vh] min-h-[550px] relative">
    
    <!-- Mobile Backdrop for Sidebar Drawer -->
    <div id="sidebar-mobile-backdrop" onclick="toggleMobileHistory(false)" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-30 md:hidden transition-opacity"></div>

    <!-- Left Sidebar: Gemini Chat History (Drawer on mobile, stationary sidebar on desktop) -->
    <div id="chat-sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-4/5 sm:w-80 md:w-80 lg:w-88 border-r border-slate-200 bg-white md:bg-slate-50/70 flex flex-col shrink-0 h-full shadow-2xl md:shadow-none transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0">
      <!-- Sidebar Header -->
      <div class="p-3.5 border-b border-slate-200/80 space-y-2.5 bg-white shrink-0">
        <div class="flex items-center justify-between md:hidden pb-1">
          <span class="text-xs font-black text-slate-800 flex items-center gap-1.5">
            <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> Past Chats
          </span>
          <button type="button" onclick="toggleMobileHistory(false)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <button type="button" onclick="startNewChat(); toggleMobileHistory(false);" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-98">
          <i class="fa-solid fa-plus text-xs"></i>
          <span>New Chat Session</span>
        </button>

        <!-- Search Input -->
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input 
            type="text" 
            id="history-search-input" 
            oninput="filterHistoryList()" 
            placeholder="Search past conversations..." 
            class="w-full text-xs font-medium pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/40 outline-none transition">
        </div>
      </div>

      <!-- History Sessions List -->
      <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" id="history-sessions-container">
        <?php if (empty($initialConversations)): ?>
          <div class="text-center py-12 px-4 text-slate-400">
            <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300"></i>
            <p class="text-xs font-bold text-slate-600">No chat history yet</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Start your first AI conversation using the button above.</p>
          </div>
        <?php else: foreach ($initialConversations as $conv): 
          $displayTitle = !empty($conv['title']) ? $conv['title'] : 'Conversation #' . $conv['id'];
          $msgCount = (int)($conv['message_count'] ?? 0);
          $timeStr = date('M d, h:i A', strtotime($conv['updated_at']));
        ?>
          <div 
            onclick="loadConversation(<?php echo $conv['id']; ?>); toggleMobileHistory(false);" 
            data-conv-id="<?php echo $conv['id']; ?>"
            data-search-text="<?php echo strtolower(htmlspecialchars($displayTitle . ' ' . ($conv['latest_snippet'] ?? ''))); ?>"
            class="history-item group relative p-3 rounded-xl transition cursor-pointer border border-transparent hover:border-slate-200 hover:bg-white flex items-start justify-between gap-2">
            
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-0.5">
                <i class="fa-regular fa-message text-[11px] text-slate-400 group-hover:text-blue-600"></i>
                <h4 class="text-xs font-bold text-slate-800 truncate leading-snug history-item-title"><?php echo htmlspecialchars($displayTitle); ?></h4>
              </div>
              <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                <span><?php echo $timeStr; ?></span>
                <span>&bull;</span>
                <span class="font-mono"><?php echo $msgCount; ?> msgs</span>
              </div>
            </div>

            <!-- Delete Button -->
            <button 
              type="button" 
              onclick="event.stopPropagation(); promptDeleteConversation(<?php echo $conv['id']; ?>)" 
              class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-xs shrink-0" 
              title="Delete conversation">
              <i class="fa-solid fa-trash text-[11px]"></i>
            </button>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- Sidebar Footer -->
      <div class="p-3 border-t border-slate-200/80 bg-white flex items-center justify-between text-xs text-slate-500 shrink-0">
        <span class="text-[11px] font-semibold flex items-center gap-1.5">
          <i class="fa-solid fa-microchip text-indigo-500"></i>
          <span>qwen2.5:3b</span>
        </span>
        <button type="button" onclick="setDashboardMode('audit'); toggleMobileHistory(false);" class="text-[11px] font-bold text-blue-600 hover:underline">
          View Logs &rarr;
        </button>
      </div>
    </div>

    <!-- Right Canvas: Active Conversation Chat Feed & Input -->
    <div class="flex-1 flex flex-col h-full bg-white min-w-0">
      
      <!-- Chat Canvas Header -->
      <div class="p-3.5 sm:px-5 border-b border-slate-200/80 flex items-center justify-between bg-white shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
          <!-- Mobile History Toggle Button -->
          <button type="button" onclick="toggleMobileHistory(true)" class="md:hidden px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shrink-0" title="View past chats">
            <i class="fa-solid fa-bars text-xs text-indigo-600"></i>
            <span class="text-[11px]">History</span>
          </button>

          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0 bhooma-live-heart">
            <i class="fa-solid fa-heart-pulse"></i>
          </div>
          <div class="min-w-0">
            <h3 id="current-chat-title" class="font-black text-slate-900 text-xs sm:text-sm truncate">New Conversation</h3>
            <span id="current-chat-status" class="text-[10px] font-semibold text-emerald-600 flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              <span>Online &bull; Ready</span>
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button type="button" onclick="exportCurrentTranscript()" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5" title="Export conversation as text">
            <i class="fa-solid fa-download text-xs text-slate-400"></i>
            <span class="hidden sm:inline">Export</span>
          </button>
          <button type="button" onclick="startNewChat()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-rotate-left text-xs"></i>
            <span class="hidden sm:inline">Reset</span>
          </button>
        </div>
      </div>

      <!-- Messages Thread Area -->
      <div id="chat-messages-thread" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 custom-scrollbar bg-slate-50/40">
        
        <!-- Welcome Screen (Shown when starting new chat) -->
        <div id="chat-welcome-screen" class="max-w-xl mx-auto py-8 sm:py-12 text-center space-y-4">
          <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-rose-500/25 mx-auto bhooma-live-heart">
            <i class="fa-solid fa-heart-pulse text-2xl"></i>
          </div>
          <div>
            <h3 class="text-lg sm:text-xl font-black text-slate-900">How can I assist you today?</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
              Ask about admitted patients, check bed availability, look up doctors, or manage clinic schedules.
            </p>
          </div>

          <!-- Suggested Starter Prompts -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-left pt-2">
            <button type="button" onclick="sendStarterPrompt('Find all male patients with blood group A+')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">🩸</span>
              <span class="truncate">Find male patients with A+ blood</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Which beds are currently available in hospital?')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">🏨</span>
              <span class="truncate">Check available beds &amp; rooms</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Who is present today in staff attendance?')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">👥</span>
              <span class="truncate">Who is present in staff today?</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Show today appointments and tokens')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">📅</span>
              <span class="truncate">Today's appointments &amp; tokens</span>
            </button>
          </div>
        </div>

        <!-- Chat messages will be injected dynamically -->
      </div>

      <!-- Quick Suggestion Pills Bar (Above Input) -->
      <div id="quick-suggestions-bar" class="px-4 py-2 border-t border-slate-100 bg-white flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Suggestions:</span>
        <div id="suggestions-chips-container" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
          <button type="button" onclick="sendStarterPrompt('Show today appointments')" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
            Today's Appointments
          </button>
          <button type="button" onclick="sendStarterPrompt('How many patients are admitted?')" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
            Admitted Patients
          </button>
          <button type="button" onclick="sendStarterPrompt('Show available beds by ward')" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
            Beds by Ward
          </button>
          <button type="button" onclick="sendStarterPrompt('Who is present today in staff?')" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
            Staff On Duty
          </button>
        </div>
      </div>

      <!-- Floating Modern Chat Input Bar -->
      <div class="p-3 sm:p-4 border-t border-slate-200 bg-white shrink-0">
        <form id="chat-input-form" onsubmit="handleChatSubmit(event)" class="relative flex items-center gap-2">
          <textarea 
            id="chat-user-input" 
            rows="1" 
            onkeydown="handleInputKeydown(event)"
            oninput="autoResizeTextarea(this)"
            placeholder="Ask anything about patients, appointments, doctors..." 
            style="overflow-y: hidden !important; resize: none !important; min-height: 44px !important;"
            class="w-full text-xs sm:text-sm font-semibold pl-4 pr-12 py-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500/40 outline-none transition max-h-32 shadow-2xs"></textarea>
          
          <button 
            type="submit" 
            id="chat-send-btn" 
            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition shadow-md shadow-blue-500/30 cursor-pointer disabled:opacity-50">
            <i class="fa-solid fa-paper-plane text-xs"></i>
          </button>
        </form>
        <div class="flex items-center justify-between mt-1.5 px-1 text-[10px] text-slate-400">
          <span>AI outputs natural answers &amp; safe database queries</span>
          <span class="hidden sm:inline">Press Shift + Enter for new line</span>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= MODE 2: ACTION AUDIT LOGS ================= -->
  <div id="section-audit-mode" class="hidden space-y-4">
    <!-- KPI Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <div class="apple-card p-4 bg-white border border-slate-200/80 rounded-2xl shadow-2xs">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Conversations</div>
        <div class="text-2xl font-black text-slate-900 mt-1"><?php echo number_format($totalConversations); ?></div>
        <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">+<?php echo $todayConversations; ?> today</div>
      </div>
      <div class="apple-card p-4 bg-white border border-slate-200/80 rounded-2xl shadow-2xs">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Messages Sent</div>
        <div class="text-2xl font-black text-slate-900 mt-1"><?php echo number_format($totalMessages); ?></div>
        <div class="text-[11px] text-slate-400 font-semibold mt-0.5">Two-way natural dialogues</div>
      </div>
      <div class="apple-card p-4 bg-white border border-slate-200/80 rounded-2xl shadow-2xs">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Action Success Rate</div>
        <div class="text-2xl font-black text-emerald-700 mt-1"><?php echo $actionSuccessRate; ?>%</div>
        <div class="text-[11px] text-slate-500 font-semibold mt-0.5"><?php echo $successActions; ?> of <?php echo $totalActions; ?> executed</div>
      </div>
      <div class="apple-card p-4 bg-white border border-slate-200/80 rounded-2xl shadow-2xs">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Audit Trail</div>
        <div class="text-2xl font-black text-slate-900 mt-1"><?php echo count($auditLogs); ?></div>
        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Recorded operations</div>
      </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="apple-card overflow-hidden bg-white border border-slate-200/90 rounded-2xl shadow-sm">
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-extrabold text-sm text-slate-900">Database Action Audit Trail</h3>
        <button type="button" onclick="exportAuditLogCSV()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
          <i class="fa-solid fa-file-excel text-emerald-600"></i>
          <span>Export CSV</span>
        </button>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs whitespace-nowrap">
          <thead class="bg-slate-50 text-slate-500 uppercase font-bold tracking-wider text-[11px] border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">Timestamp</th>
              <th class="py-3 px-4">Operator</th>
              <th class="py-3 px-4">Action</th>
              <th class="py-3 px-4">Target Table</th>
              <th class="py-3 px-4">Rows</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">IP Address</th>
              <th class="py-3 px-4 text-right">SQL Inspection</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <?php if (empty($auditLogs)): ?>
              <tr><td colspan="8" class="py-8 text-center text-slate-400">No operational audit records found.</td></tr>
            <?php else: foreach ($auditLogs as $a): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-4 text-slate-500 font-mono"><?php echo date('M d, Y h:i A', strtotime($a['created_at'])); ?></td>
                <td class="py-3 px-4 font-bold text-slate-900"><?php echo htmlspecialchars($a['user_id']); ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <?php echo htmlspecialchars($a['action_type']); ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-slate-700"><?php echo htmlspecialchars($a['target_table']); ?></td>
                <td class="py-3 px-4 font-bold"><?php echo (int)$a['rows_affected']; ?></td>
                <td class="py-3 px-4">
                  <?php if ($a['success']): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Executed</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Failed</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 font-mono text-[11px] text-slate-400"><?php echo htmlspecialchars($a['ip_address'] ?? '127.0.0.1'); ?></td>
                <td class="py-3 px-4 text-right">
                  <button type="button" onclick="showSqlModal('<?php echo htmlspecialchars(addslashes($a['sql_executed'])); ?>')" class="px-2.5 py-1 text-slate-600 hover:text-blue-600 border border-slate-200 rounded-lg text-xs font-semibold transition">
                    <i class="fa-solid fa-code mr-1"></i> View SQL
                  </button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- SQL Inspection Modal -->
<div id="modal-sql" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="apple-card max-w-xl w-full p-5 bg-white shadow-2xl border border-slate-200 rounded-2xl flex flex-col overflow-hidden space-y-3">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <div class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
        <i class="fa-solid fa-code text-indigo-600"></i> Executed SQL Query
      </div>
      <button type="button" onclick="closeSqlModal()" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="p-4 bg-slate-900 text-teal-300 rounded-xl overflow-x-auto text-xs font-mono leading-relaxed max-h-80" id="modal-sql-content">
    </div>
    <div class="flex justify-end pt-2 border-t border-slate-100">
      <button type="button" onclick="closeSqlModal()" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition">Close</button>
    </div>
  </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
let currentMode = 'chat';
let activeConversationId = null;
let isGenerating = false;
let currentChatMessages = [];

document.addEventListener('DOMContentLoaded', () => {
    // Check if URL has conversation ID
    const urlParams = new URLSearchParams(window.location.search);
    const convId = urlParams.get('id');
    if (convId) {
        loadConversation(parseInt(convId));
    }
});

// Switch Dashboard Mode (Live Chat vs Action Audit Logs)
function setDashboardMode(mode) {
    currentMode = mode;
    const btnChat = document.getElementById('btn-mode-chat');
    const btnAudit = document.getElementById('btn-mode-audit');
    const secChat = document.getElementById('section-chat-mode');
    const secAudit = document.getElementById('section-audit-mode');

    if (mode === 'chat') {
        btnChat.className = "px-3.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5 font-bold";
        btnAudit.className = "px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5";
        secChat.classList.remove('hidden');
        secAudit.classList.add('hidden');
    } else {
        btnAudit.className = "px-3.5 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition flex items-center gap-1.5 font-bold";
        btnChat.className = "px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5";
        secAudit.classList.remove('hidden');
        secChat.classList.add('hidden');
    }
}

// Start New Clean Chat
function startNewChat() {
    setDashboardMode('chat');
    activeConversationId = null;
    currentChatMessages = [];
    
    document.getElementById('current-chat-title').textContent = 'New Conversation';
    document.getElementById('current-chat-status').innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span>Online &bull; Ready</span>';
    
    const thread = document.getElementById('chat-messages-thread');
    thread.innerHTML = `
        <div id="chat-welcome-screen" class="max-w-xl mx-auto py-8 sm:py-12 text-center space-y-4">
          <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-rose-500/25 mx-auto bhooma-live-heart">
            <i class="fa-solid fa-heart-pulse text-2xl"></i>
          </div>
          <div>
            <h3 class="text-lg sm:text-xl font-black text-slate-900">How can I assist you today?</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
              Ask about admitted patients, check bed availability, look up doctors, or manage clinic schedules.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-left pt-2">
            <button type="button" onclick="sendStarterPrompt('Find all male patients with blood group A+')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">🩸</span>
              <span class="truncate">Find male patients with A+ blood</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Which beds are currently available in hospital?')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">🏨</span>
              <span class="truncate">Check available beds &amp; rooms</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Who is present today in staff attendance?')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">👥</span>
              <span class="truncate">Who is present in staff today?</span>
            </button>
            <button type="button" onclick="sendStarterPrompt('Show today appointments and tokens')" class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-xs transition text-xs font-semibold text-slate-700 flex items-center gap-2.5 text-left">
              <span class="text-base">📅</span>
              <span class="truncate">Today\'s appointments &amp; tokens</span>
            </button>
          </div>
        </div>
    `;

    // Deselect sidebar items
    document.querySelectorAll('.history-item').forEach(el => {
        el.classList.remove('bg-blue-50/80', 'border-blue-200');
    });

    const input = document.getElementById('chat-user-input');
    input.value = '';
    input.focus();
}

// Load and Replay Conversation from History
async function loadConversation(convId) {
    setDashboardMode('chat');
    activeConversationId = convId;

    // Highlight active sidebar item
    document.querySelectorAll('.history-item').forEach(el => {
        const id = parseInt(el.getAttribute('data-conv-id'));
        if (id === convId) {
            el.classList.add('bg-blue-50/80', 'border-blue-200');
        } else {
            el.classList.remove('bg-blue-50/80', 'border-blue-200');
        }
    });

    const thread = document.getElementById('chat-messages-thread');
    thread.innerHTML = `
        <div class="text-center py-16 text-slate-400">
            <i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-600 mb-2"></i>
            <p class="text-xs font-bold text-slate-600">Loading conversation replay...</p>
        </div>
    `;

    try {
        const res = await fetch(`api/chatbot.php?action=history&conversation_id=${convId}`);
        const data = await res.json();
        
        if (!data || !data.messages || data.messages.length === 0) {
            thread.innerHTML = `<div class="text-center py-16 text-slate-400 text-xs">No messages recorded in this conversation thread.</div>`;
            return;
        }

        document.getElementById('current-chat-title').textContent = data.title || `Thread #${convId}`;
        document.getElementById('current-chat-status').innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span>${data.messages.length} messages loaded</span>`;

        thread.innerHTML = '';
        currentChatMessages = data.messages;

        data.messages.forEach(msg => {
            renderMessageBubble(msg.role, msg.content, msg.sql_executed, msg.created_at);
        });

        // Scroll to bottom
        thread.scrollTop = thread.scrollHeight;
    } catch (e) {
        console.error(e);
        thread.innerHTML = `<div class="text-center py-16 text-rose-500 text-xs font-bold">Failed to load conversation history.</div>`;
    }
}

// Render Single Message Bubble
function renderMessageBubble(role, content, sql = null, time = null) {
    const thread = document.getElementById('chat-messages-thread');
    
    // Remove welcome screen if present
    const welcome = document.getElementById('chat-welcome-screen');
    if (welcome) welcome.remove();

    const isUser = role === 'user';
    const timeFormatted = time ? new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const msgEl = document.createElement('div');
    msgEl.className = `flex items-start gap-3 ${isUser ? 'justify-end' : 'justify-start'}`;

    if (isUser) {
        msgEl.innerHTML = `
            <div class="max-w-[85%] sm:max-w-[75%] space-y-1">
                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-xs sm:text-sm font-semibold shadow-sm leading-relaxed whitespace-pre-wrap">
                    ${escapeHtml(content)}
                </div>
                <div class="text-[10px] text-slate-400 text-right pr-1">${timeFormatted}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-black text-xs shrink-0 mt-0.5 shadow-2xs">
                <?php echo strtoupper(substr($currentUsername, 0, 1)); ?>
            </div>
        `;
    } else {
        const sqlSnippet = sql ? `
            <div class="mt-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="text-[10px] font-bold text-indigo-600 flex items-center gap-1 hover:underline">
                    <i class="fa-solid fa-database"></i> <span>Executed SQL Query</span>
                </button>
                <pre class="hidden mt-1.5 bg-slate-900 text-teal-300 p-2.5 rounded-xl text-[11px] font-mono overflow-x-auto">${escapeHtml(sql)}</pre>
            </div>
        ` : '';

        msgEl.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-xs bhooma-live-heart">
                <i class="fa-solid fa-heart-pulse text-xs"></i>
            </div>
            <div class="max-w-[90%] sm:max-w-[80%] space-y-1">
                <div class="bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm px-4 py-3 text-xs sm:text-sm text-slate-800 shadow-2xs leading-relaxed prose prose-sm max-w-none">
                    <div class="message-content">${formatMarkdownDisplay(content)}</div>
                    ${sqlSnippet}
                </div>
                <div class="flex items-center justify-between px-1 text-[10px] text-slate-400">
                    <button type="button" onclick="copyMessageText(this)" class="hover:text-blue-600 transition flex items-center gap-1">
                        <i class="fa-regular fa-copy"></i> <span>Copy</span>
                    </button>
                    <span>${timeFormatted}</span>
                </div>
            </div>
        `;
    }

    thread.appendChild(msgEl);
    thread.scrollTop = thread.scrollHeight;
    return msgEl;
}

// Handle User Input Submission
async function handleChatSubmit(e) {
    if (e) e.preventDefault();
    if (isGenerating) return;

    const input = document.getElementById('chat-user-input');
    const promptText = input.value.trim();
    if (!promptText) return;

    input.value = '';
    autoResizeTextarea(input);

    // 1. Render user message bubble
    renderMessageBubble('user', promptText);

    // 2. Render placeholder for AI streaming response
    const thread = document.getElementById('chat-messages-thread');
    const aiBubble = document.createElement('div');
    aiBubble.className = "flex items-start gap-3 justify-start";
    aiBubble.innerHTML = `
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 via-rose-600 to-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-xs bhooma-live-heart">
            <i class="fa-solid fa-heart-pulse text-xs"></i>
        </div>
        <div class="max-w-[90%] sm:max-w-[80%] space-y-1">
            <div class="bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm px-4 py-3 text-xs sm:text-sm text-slate-800 shadow-2xs leading-relaxed">
                <div class="ai-stream-content flex items-center gap-2 text-slate-400 font-semibold text-xs">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                    <span>Consulting hospital clinical engine...</span>
                </div>
            </div>
        </div>
    `;
    thread.appendChild(aiBubble);
    thread.scrollTop = thread.scrollHeight;

    isGenerating = true;
    document.getElementById('chat-send-btn').disabled = true;

    let fullAiResponse = '';
    const contentEl = aiBubble.querySelector('.ai-stream-content');

    try {
        const response = await fetch('api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'chat',
                message: promptText,
                conversation_id: activeConversationId,
                page_context: 'ai_logs.php'
            })
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // keep last incomplete chunk

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    const jsonStr = line.substring(6).trim();
                    if (!jsonStr) continue;
                    try {
                        const data = JSON.parse(jsonStr);
                        if (data.type === 'chunk') {
                            fullAiResponse += data.content;
                            contentEl.className = "message-content leading-relaxed";
                            contentEl.innerHTML = formatMarkdownDisplay(fullAiResponse);
                            thread.scrollTop = thread.scrollHeight;
                        } else if (data.type === 'data') {
                            renderDataTable(data.results || data.content, data.summary);
                        } else if (data.type === 'action') {
                            renderActionCard(data.action_id, data.content);
                        } else if (data.type === 'suggestions') {
                            updateSuggestions(data.suggestions);
                        } else if (data.type === 'error') {
                            contentEl.className = "message-content leading-relaxed";
                            contentEl.innerHTML = `<span class="text-rose-600 font-bold">⚠️ ${escapeHtml(data.message || 'An error occurred.')}</span>`;
                        } else if (data.type === 'done') {
                            if (data.conversation_id) {
                                activeConversationId = data.conversation_id;
                            }
                            reloadConversationsList();
                        }
                    } catch (parseErr) {
                        // ignore chunk boundaries
                    }
                }
            }
        }

        if (!fullAiResponse && !contentEl.innerHTML.trim()) {
            contentEl.innerHTML = formatMarkdownDisplay("I have processed your query, but no text response was returned.");
        }

    } catch (err) {
        console.error(err);
        contentEl.innerHTML = `<span class="text-rose-600 font-bold">Failed to communicate with AI engine.</span>`;
    } finally {
        isGenerating = false;
        document.getElementById('chat-send-btn').disabled = false;
        input.focus();
    }
}

function sendStarterPrompt(text) {
    document.getElementById('chat-user-input').value = text;
    handleChatSubmit();
}

function handleInputKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleChatSubmit();
    }
}

function autoResizeTextarea(textarea) {
    if (!textarea) return;
    textarea.style.height = 'auto';
    if (textarea.scrollHeight > 115) {
        textarea.style.height = '115px';
        textarea.style.overflowY = 'auto';
    } else {
        textarea.style.height = Math.max(44, textarea.scrollHeight) + 'px';
        textarea.style.overflowY = 'hidden';
    }
}

function filterHistoryList() {
    const q = document.getElementById('history-search-input').value.toLowerCase().trim();
    document.querySelectorAll('.history-item').forEach(item => {
        const text = item.getAttribute('data-search-text') || '';
        item.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
    });
}

async function reloadConversationsList() {
    try {
        const res = await fetch('api/chatbot.php?action=conversations');
        const data = await res.json();
        if (data.success && data.conversations) {
            renderSidebarConversations(data.conversations);
        }
    } catch (e) {
        console.error('Error reloading history:', e);
    }
}

function renderSidebarConversations(convos) {
    const container = document.getElementById('history-sessions-container');
    if (!convos.length) return;

    let html = '';
    convos.forEach(c => {
        const isActive = activeConversationId === c.id;
        const displayTitle = c.title || `Thread #${c.id}`;
        const timeStr = new Date(c.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        html += `
          <div 
            onclick="loadConversation(${c.id})" 
            data-conv-id="${c.id}"
            data-search-text="${escapeHtml(displayTitle.toLowerCase())}"
            class="history-item group relative p-3 rounded-xl transition cursor-pointer border ${isActive ? 'bg-blue-50/80 border-blue-200' : 'border-transparent hover:border-slate-200 hover:bg-white'} flex items-start justify-between gap-2">
            
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-0.5">
                <i class="fa-regular fa-message text-[11px] ${isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600'}"></i>
                <h4 class="text-xs font-bold text-slate-800 truncate leading-snug history-item-title">${escapeHtml(displayTitle)}</h4>
              </div>
              <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                <span>${timeStr}</span>
                <span>&bull;</span>
                <span class="font-mono">${c.message_count || 1} msgs</span>
              </div>
            </div>

            <button 
              type="button" 
              onclick="event.stopPropagation(); promptDeleteConversation(${c.id})" 
              class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-xs shrink-0" 
              title="Delete conversation">
              <i class="fa-solid fa-trash text-[11px]"></i>
            </button>
          </div>
        `;
    });
    container.innerHTML = html;
}

function promptDeleteConversation(id) {
    openConfirmModal(
        'Delete Chat Session',
        'Are you sure you want to delete this chat session?',
        '<p class="text-xs text-slate-500">All message records in this thread will be permanently deleted from hospital memory.</p>',
        async () => {
            try {
                const res = await fetch('api/chatbot.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_conversation', conversation_id: id })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Deleted', 'Conversation removed');
                    if (activeConversationId === id) {
                        startNewChat();
                    }
                    reloadConversationsList();
                }
            } catch (e) {
                showToast('Error', 'Failed to delete conversation', 'error');
            }
        },
        'danger'
    );
}

function copyMessageText(btn) {
    const bubble = btn.closest('.max-w-\\[90\\%\\]').querySelector('.message-content');
    if (!bubble) return;
    navigator.clipboard.writeText(bubble.innerText).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> <span class="text-emerald-600">Copied!</span>';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

function exportCurrentTranscript() {
    if (!currentChatMessages || currentChatMessages.length === 0) {
        showToast('Empty Thread', 'No messages to export.', 'warning');
        return;
    }
    let text = `BHOOMA HOSPITAL AI TRANSCRIPT\nThread #${activeConversationId || 'New'}\nDate: ${new Date().toLocaleString()}\n==========================================\n\n`;
    currentChatMessages.forEach(m => {
        text += `[${m.role.toUpperCase()}] (${m.created_at || ''}):\n${m.content}\n\n`;
    });

    const blob = new Blob([text], { type: 'text/plain;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `AI_Chat_Transcript_${activeConversationId || 'latest'}.txt`;
    link.click();
}

function showSqlModal(sql) {
    document.getElementById('modal-sql-content').textContent = sql;
    document.getElementById('modal-sql').classList.remove('hidden');
}

function closeSqlModal() {
    document.getElementById('modal-sql').classList.add('hidden');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatMarkdownDisplay(text) {
    if (!text) return '';
    let clean = text;

    // Strip any robotic Direct Answer prefixes seamlessly
    clean = clean.replace(/^>\s*(?:💡\s*)?(?:\*\*)?Direct Answer:(?:\*\*)?\s*/gmi, '');
    clean = clean.replace(/^(?:💡\s*)?(?:\*\*)?Direct Answer:(?:\*\*)?\s*/gmi, '');

    // Render executive callouts for > quotes
    clean = clean.replace(/^>\s*(.*?)(?=\n|$)/gm, (match, p1) => {
        return `<div class="my-2 p-3 bg-indigo-50/90 border-l-4 border-indigo-600 rounded-r-xl text-slate-800 text-xs font-semibold shadow-2xs">${p1}</div>`;
    });

    // Convert markdown bullet points to clean list items
    clean = clean.replace(/^[ \-\*]\s+(.*)$/gm, '<div class="flex items-start gap-2 my-1 text-xs text-slate-700"><span class="text-indigo-500 font-bold">•</span><span>$1</span></div>');

    let formatted = clean
        .replace(/```([\s\S]*?)```/g, '<pre class="bg-slate-900 text-teal-300 p-3 rounded-xl text-xs font-mono my-2 overflow-x-auto shadow-xs"><code>$1</code></pre>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`([^`]+)`/g, '<code class="bg-slate-100 text-indigo-700 px-1.5 py-0.5 rounded font-mono text-[11px] border border-slate-200/80">$1</code>')
        .replace(/\n/g, '<br>');
    return formatted;
}

// Toggle Mobile History Sidebar Drawer
function toggleMobileHistory(show) {
    const sidebar = document.getElementById('chat-sidebar');
    const backdrop = document.getElementById('sidebar-mobile-backdrop');
    if (!sidebar) return;
    
    const isHidden = sidebar.classList.contains('-translate-x-full');
    const willShow = (typeof show === 'boolean') ? show : isHidden;
    
    if (willShow) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
}

// Update Quick Suggestion Pills dynamically
function updateSuggestions(suggestions) {
    if (!suggestions || !suggestions.length) return;
    const container = document.getElementById('suggestions-chips-container');
    if (!container) return;
    container.innerHTML = suggestions.map(s => `
        <button type="button" onclick="sendStarterPrompt('${escapeHtml(s).replace(/'/g, "\\'")}')" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
            ${escapeHtml(s)}
        </button>
    `).join('');
}

// Render Hospital Records Data Table (Single KPI or Multi-row tabular view)
function renderDataTable(dataArr, summary) {
    if (!dataArr) return;
    const thread = document.getElementById('chat-messages-thread');
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-start gap-3 justify-start max-w-full';

    if (!dataArr.length || dataArr.length === 0) {
        wrapper.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center text-xs shrink-0 mt-0.5">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="bg-slate-50 border border-slate-200 py-3 px-4 rounded-2xl rounded-tl-sm text-xs text-slate-600 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-inbox text-slate-400"></i>
                <span>${escapeHtml(summary || 'No matching records found in hospital database.')}</span>
            </div>
        `;
        thread.appendChild(wrapper);
        thread.scrollTop = thread.scrollHeight;
        return;
    }

    const columns = Object.keys(dataArr[0]);
    let visualHTML = '';

    if (dataArr.length === 1 && columns.length === 1) {
        const key = columns[0];
        const val = dataArr[0][key];
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        visualHTML = `
            <div class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-100 rounded-xl px-4 py-2.5 shadow-2xs">
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
        let tableHTML = `
            <div class="w-full overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                <div class="bg-slate-100/90 px-3 py-2 border-b border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-700">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-table-list text-indigo-600"></i> Hospital Records</span>
                    <span class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-bold">${dataArr.length} records</span>
                </div>
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>`;
        columns.forEach(col => {
            const colLabel = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            tableHTML += `<th class="px-3.5 py-2 text-[11px] tracking-tight uppercase">${colLabel}</th>`;
        });
        tableHTML += `</tr></thead><tbody class="divide-y divide-slate-100 bg-white font-medium">`;
        dataArr.forEach((row, i) => {
            const bg = i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50';
            tableHTML += `<tr class="${bg} hover:bg-indigo-50/60 transition">`;
            columns.forEach(col => {
                tableHTML += `<td class="px-3.5 py-2 text-slate-700">${row[col] !== null ? escapeHtml(String(row[col])) : '-'}</td>`;
            });
            tableHTML += `</tr>`;
        });
        tableHTML += `</tbody></table></div>`;
        visualHTML = tableHTML;
    }

    wrapper.innerHTML = `
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-teal-400 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-xs">
            <i class="fa-solid fa-database"></i>
        </div>
        <div class="max-w-[95%] sm:max-w-[85%] bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm p-3.5 shadow-2xs overflow-hidden">
            ${visualHTML}
        </div>
    `;
    thread.appendChild(wrapper);
    thread.scrollTop = thread.scrollHeight;
}

// Render Action Approval Card
function renderActionCard(actionId, details) {
    const thread = document.getElementById('chat-messages-thread');
    const isDelete = typeof details === 'string' && (details.toLowerCase().includes('delete') || details.toLowerCase().includes('cancel') || details.toLowerCase().includes('remove'));
    const badgeBg = isDelete ? 'bg-rose-600 text-white' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white';
    const cardBg = isDelete 
        ? 'bg-rose-50/90 border border-rose-200' 
        : 'bg-gradient-to-br from-white via-blue-50/30 to-indigo-50/40 border border-blue-200';
    const titleText = isDelete ? 'Confirm Permanent Deletion' : 'Confirm Hospital Operation';
    const titleColor = isDelete ? 'text-rose-900' : 'text-slate-900';
    const iconClass = isDelete ? 'fa-triangle-exclamation' : 'fa-bolt-lightning';
    const confirmBtnClass = isDelete 
        ? 'bg-rose-600 hover:bg-rose-700 text-white' 
        : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-xs';
    const confirmBtnText = isDelete 
        ? '<i class="fa-solid fa-trash-can mr-1.5 text-xs"></i> Confirm Delete' 
        : '<i class="fa-solid fa-circle-check mr-1.5 text-xs"></i> Confirm & Execute';

    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-start gap-3 justify-start max-w-[95%] sm:max-w-[85%]';
    wrapper.innerHTML = `
        <div class="w-8 h-8 rounded-xl ${badgeBg} flex items-center justify-center text-xs shrink-0 mt-0.5 shadow-xs">
            <i class="fa-solid ${iconClass}"></i>
        </div>
        <div class="${cardBg} p-4 rounded-2xl shadow-sm w-full transition-all" id="action-card-${actionId}">
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/70">
                <span class="font-extrabold ${titleColor} text-xs tracking-tight">${titleText}</span>
                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider ${isDelete ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-blue-100 text-blue-700 border border-blue-200'}">
                    Requires Approval
                </span>
            </div>
            <div class="text-xs text-slate-700 mb-3 leading-relaxed font-semibold">
                ${escapeHtml(details)}
            </div>
            <div class="flex items-center gap-2 pt-1">
                <button type="button" onclick="executePendingAction(${actionId}, this)" class="${confirmBtnClass} text-xs font-bold py-2 px-3.5 rounded-xl transition-all flex-1 flex items-center justify-center cursor-pointer">
                    ${confirmBtnText}
                </button>
                <button type="button" onclick="cancelPendingAction(${actionId})" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold py-2 px-3 rounded-xl shadow-2xs transition-all flex items-center justify-center cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    `;
    thread.appendChild(wrapper);
    thread.scrollTop = thread.scrollHeight;
}

// Execute Pending Action
async function executePendingAction(actionId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Executing...';
    try {
        const res = await fetch('api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'execute_action', action_id: actionId })
        });
        const data = await res.json();
        const card = document.getElementById('action-card-' + actionId);
        if (data.success) {
            if (card) {
                card.className = "bg-emerald-50 border border-emerald-200 p-3.5 rounded-2xl";
                card.innerHTML = `<div class="text-xs font-bold text-emerald-800 flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-600"></i> Operation successfully executed and committed to hospital records.</div>`;
            }
            showToast('Success', data.message || 'Operation executed successfully');
        } else {
            if (card) {
                card.innerHTML = `<div class="text-xs font-bold text-rose-700 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-rose-600"></i> ${escapeHtml(data.error || 'Execution failed')}</div>`;
            }
            showToast('Failed', data.error || 'Action failed', 'error');
        }
    } catch (e) {
        showToast('Error', 'Connection error while executing action', 'error');
    }
}

// Cancel Pending Action
async function cancelPendingAction(actionId) {
    try {
        await fetch('api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'cancel_action', action_id: actionId })
        });
        const card = document.getElementById('action-card-' + actionId);
        if (card) {
            card.className = "bg-slate-100 border border-slate-200 p-3 rounded-2xl";
            card.innerHTML = `<div class="text-xs font-semibold text-slate-500 flex items-center gap-2"><i class="fa-solid fa-ban text-slate-400"></i> Action was cancelled by user.</div>`;
        }
    } catch (e) {}
}

function exportAuditLogCSV() {
    const rows = [["Timestamp", "Operator", "Role", "Action Type", "Target Table", "Rows Affected", "Status", "IP Address"]];
    <?php foreach ($auditLogs as $a): ?>
      rows.push([
        "<?php echo addslashes($a['created_at']); ?>",
        "<?php echo addslashes($a['user_id']); ?>",
        "<?php echo addslashes($a['user_role']); ?>",
        "<?php echo addslashes($a['action_type']); ?>",
        "<?php echo addslashes($a['target_table']); ?>",
        "<?php echo (int)$a['rows_affected']; ?>",
        "<?php echo $a['success'] ? 'Executed' : 'Failed'; ?>",
        "<?php echo addslashes($a['ip_address'] ?? '127.0.0.1'); ?>"
      ]);
    <?php endforeach; ?>
    
    let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.map(i => `"${i}"`).join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Hospital_AI_Audit_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include 'includes/footer.php'; ?>

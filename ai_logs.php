<?php
// ai_logs.php - AI Assistant Intelligence, Conversation Transcripts & Action Audit Dashboard
require_once 'auth.php';
require_once 'db.php';
include 'includes/header.php';

$hospitalId = (int)($_SESSION['hospital_id'] ?? 1);
$hospitalName = $_SESSION['hospital_name'] ?? 'CarePulse Hospital';
$userRole = $_SESSION['staff_role'] ?? 'Admin';

// -------------------------------------------------------------
// 1. Executive Analytics & KPIs
// -------------------------------------------------------------
// Total Conversations
$resConv = $conn->query("SELECT COUNT(*) as total_conv, SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_conv FROM ai_conversations WHERE hospital_id = $hospitalId");
$rowConv = $resConv ? $resConv->fetch_assoc() : ['total_conv' => 0, 'today_conv' => 0];
$totalConversations = (int)($rowConv['total_conv'] ?? 0);
$todayConversations = (int)($rowConv['today_conv'] ?? 0);

// Total Messages
$resMsg = $conn->query("SELECT COUNT(*) as total_msg FROM ai_chat_messages m JOIN ai_conversations c ON m.conversation_id = c.id WHERE c.hospital_id = $hospitalId");
$totalMessages = $resMsg ? (int)($resMsg->fetch_assoc()['total_msg'] ?? 0) : 0;

// Actions Executed & Success Rate
$resActs = $conn->query("SELECT COUNT(*) as total_acts, SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_acts FROM ai_action_log WHERE hospital_id = $hospitalId");
$rowActs = $resActs ? $resActs->fetch_assoc() : ['total_acts' => 0, 'success_acts' => 0];
$totalActions = (int)($rowActs['total_acts'] ?? 0);
$successActions = (int)($rowActs['success_acts'] ?? 0);
$actionSuccessRate = $totalActions > 0 ? round(($successActions / $totalActions) * 100, 1) : 100.0;

// Pending Actions
$resPend = $conn->query("SELECT COUNT(*) as pending_count FROM ai_pending_actions WHERE hospital_id = $hospitalId AND status = 'pending' AND expires_at > NOW()");
$pendingActionsCount = $resPend ? (int)($resPend->fetch_assoc()['pending_count'] ?? 0) : 0;

// -------------------------------------------------------------
// 2. Fetch Conversations List
// -------------------------------------------------------------
$convQuery = "
    SELECT c.*, 
           COUNT(m.id) as message_count,
           MAX(m.created_at) as last_message_at,
           (SELECT content FROM ai_chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as latest_snippet
    FROM ai_conversations c
    LEFT JOIN ai_chat_messages m ON c.id = m.conversation_id
    WHERE c.hospital_id = $hospitalId
    GROUP BY c.id
    ORDER BY c.updated_at DESC
    LIMIT 100
";
$conversations = [];
if ($res = $conn->query($convQuery)) {
    while ($r = $res->fetch_assoc()) $conversations[] = $r;
}

// -------------------------------------------------------------
// 3. Fetch Action Audit Logs
// -------------------------------------------------------------
$auditLogs = [];
$auditQuery = "SELECT * FROM ai_action_log WHERE hospital_id = $hospitalId ORDER BY created_at DESC LIMIT 100";
if ($res = $conn->query($auditQuery)) {
    while ($r = $res->fetch_assoc()) $auditLogs[] = $r;
}

// -------------------------------------------------------------
// 4. Fetch Pending Actions
// -------------------------------------------------------------
$pendingActions = [];
$pendingQuery = "SELECT * FROM ai_pending_actions WHERE hospital_id = $hospitalId ORDER BY created_at DESC LIMIT 50";
if ($res = $conn->query($pendingQuery)) {
    while ($r = $res->fetch_assoc()) $pendingActions[] = $r;
}
?>

<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 rounded-2xl text-white shadow-lg relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="relative z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-lg shadow-md">
          <i class="fa-solid fa-brain"></i>
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight">AI Assistant Intelligence &amp; Audit Logs</h1>
          <p class="text-xs sm:text-sm text-slate-300">Complete visibility into user interactions, database queries, and automated action logs.</p>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center gap-2.5 relative z-10">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Model: qwen2.5:3b (Local)
      </span>
      <button onclick="window.location.reload();" class="px-3.5 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold transition border border-white/10 flex items-center gap-1.5 cursor-pointer">
        <i class="fa-solid fa-arrows-rotate"></i> Refresh
      </button>
      <button onclick="exportAuditLogCSV();" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
        <i class="fa-solid fa-download"></i> Export CSV
      </button>
    </div>
  </div>

  <!-- KPI Statistics Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Total Conversations -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
      <div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Conversations</div>
        <div class="text-2xl font-black text-slate-800 mt-1"><?php echo number_format($totalConversations); ?></div>
        <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">+<?php echo $todayConversations; ?> started today</div>
      </div>
      <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-lg">
        <i class="fa-solid fa-comments"></i>
      </div>
    </div>

    <!-- Card 2: Total Messages -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
      <div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Messages Sent</div>
        <div class="text-2xl font-black text-slate-800 mt-1"><?php echo number_format($totalMessages); ?></div>
        <div class="text-[11px] text-slate-400 font-semibold mt-0.5">Two-way natural dialogues</div>
      </div>
      <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-lg">
        <i class="fa-solid fa-message"></i>
      </div>
    </div>

    <!-- Card 3: Action Success Rate -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
      <div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Action Success Rate</div>
        <div class="text-2xl font-black text-slate-800 mt-1"><?php echo $actionSuccessRate; ?>%</div>
        <div class="text-[11px] text-slate-500 font-semibold mt-0.5"><?php echo $successActions; ?> of <?php echo $totalActions; ?> executed</div>
      </div>
      <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-lg">
        <i class="fa-solid fa-shield-check"></i>
      </div>
    </div>

    <!-- Card 4: Pending Approvals -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
      <div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Approvals</div>
        <div class="text-2xl font-black text-slate-800 mt-1"><?php echo number_format($pendingActionsCount); ?></div>
        <div class="text-[11px] text-amber-600 font-semibold mt-0.5">Awaiting user confirmation</div>
      </div>
      <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 text-lg">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
    <div class="border-b border-slate-200 px-4 flex gap-2 overflow-x-auto bg-slate-50/50" id="ai-tabs">
      <button type="button" onclick="switchAiTab('tab-convs')" id="btn-tab-convs" class="px-4 py-3 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
        <i class="fa-solid fa-comments"></i> <span>Conversations (<?php echo count($conversations); ?>)</span>
      </button>
      <button type="button" onclick="switchAiTab('tab-audit')" id="btn-tab-audit" class="px-4 py-3 text-xs font-bold border-b-2 border-transparent text-slate-600 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
        <i class="fa-solid fa-list-check"></i> <span>Action Audit Logs (<?php echo count($auditLogs); ?>)</span>
      </button>
      <button type="button" onclick="switchAiTab('tab-pending')" id="btn-tab-pending" class="px-4 py-3 text-xs font-bold border-b-2 border-transparent text-slate-600 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
        <i class="fa-solid fa-hourglass-half"></i> <span>Pending Queue (<?php echo count($pendingActions); ?>)</span>
      </button>
      <button type="button" onclick="switchAiTab('tab-schema')" id="btn-tab-schema" class="px-4 py-3 text-xs font-bold border-b-2 border-transparent text-slate-600 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
        <i class="fa-solid fa-diagram-project"></i> <span>21 Tables &amp; Relational Map</span>
      </button>
    </div>

    <!-- TAB 1: Conversations List -->
    <div id="tab-convs" class="p-4 sm:p-6 space-y-4">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <input type="text" id="conv-search-input" oninput="filterConversations()" placeholder="Search by user, page, or title..." class="w-full text-xs pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:outline-none transition">
          <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-2.5 top-2.5"></i>
        </div>
        <div class="text-xs text-slate-500">Showing last <?php echo count($conversations); ?> conversations</div>
      </div>

      <div class="overflow-x-auto border border-slate-200 rounded-xl">
        <table class="w-full text-left text-xs whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
            <tr>
              <th class="py-3 px-4">Thread ID</th>
              <th class="py-3 px-4">User &amp; Role</th>
              <th class="py-3 px-4">Origin Page</th>
              <th class="py-3 px-4">Messages</th>
              <th class="py-3 px-4">Last Activity</th>
              <th class="py-3 px-4">Latest Snippet</th>
              <th class="py-3 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white" id="conv-table-body">
            <?php if (empty($conversations)): ?>
              <tr><td colspan="7" class="py-8 text-center text-slate-400">No chat sessions recorded yet.</td></tr>
            <?php else: foreach ($conversations as $c): ?>
              <tr class="hover:bg-slate-50/80 transition-colors conv-row" data-user="<?php echo strtolower($c['user_id'] . ' ' . $c['user_role']); ?>" data-page="<?php echo strtolower($c['page_context'] ?? ''); ?>" data-text="<?php echo strtolower($c['latest_snippet'] ?? ''); ?>">
                <td class="py-3 px-4 font-mono font-bold text-indigo-700">#<?php echo $c['id']; ?></td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800"><?php echo htmlspecialchars($c['user_id']); ?></div>
                  <div class="text-[10px] text-slate-400"><?php echo htmlspecialchars($c['user_role'] ?? 'Admin'); ?></div>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <?php echo htmlspecialchars($c['page_context'] ?: 'General'); ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-bold text-slate-700"><?php echo $c['message_count']; ?></td>
                <td class="py-3 px-4 text-slate-500"><?php echo date('M d, Y h:i A', strtotime($c['updated_at'])); ?></td>
                <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="<?php echo htmlspecialchars($c['latest_snippet'] ?? ''); ?>">
                  <?php echo htmlspecialchars($c['latest_snippet'] ?: 'Started conversation'); ?>
                </td>
                <td class="py-3 px-4 text-right">
                  <button type="button" onclick="openTranscriptModal(<?php echo $c['id']; ?>, '<?php echo addslashes($c['user_id']); ?>', '<?php echo addslashes($c['page_context'] ?? ''); ?>')" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition cursor-pointer">
                    <i class="fa-solid fa-eye mr-1"></i> View Transcript
                  </button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: Action Audit Log -->
    <div id="tab-audit" class="p-4 sm:p-6 space-y-4 hidden">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Operational Database Audit Trail</h3>
        <span class="text-xs text-slate-400"><?php echo count($auditLogs); ?> audited operations</span>
      </div>

      <div class="overflow-x-auto border border-slate-200 rounded-xl">
        <table class="w-full text-left text-xs whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
            <tr>
              <th class="py-3 px-4">Timestamp</th>
              <th class="py-3 px-4">Operator</th>
              <th class="py-3 px-4">Action Type</th>
              <th class="py-3 px-4">Target Table</th>
              <th class="py-3 px-4">Rows</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">IP Address</th>
              <th class="py-3 px-4 text-right">SQL Inspection</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <?php if (empty($auditLogs)): ?>
              <tr><td colspan="8" class="py-8 text-center text-slate-400">No action executions recorded yet.</td></tr>
            <?php else: foreach ($auditLogs as $a): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-4 text-slate-500"><?php echo date('M d, Y h:i:s A', strtotime($a['created_at'])); ?></td>
                <td class="py-3 px-4">
                  <span class="font-bold text-slate-800"><?php echo htmlspecialchars($a['user_id']); ?></span>
                  <span class="text-[10px] text-slate-400 ml-1">(<?php echo htmlspecialchars($a['user_role']); ?>)</span>
                </td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <?php echo htmlspecialchars($a['action_type']); ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-slate-700"><?php echo htmlspecialchars($a['target_table']); ?></td>
                <td class="py-3 px-4 font-bold"><?php echo (int)$a['rows_affected']; ?></td>
                <td class="py-3 px-4">
                  <?php if ($a['success']): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      <i class="fa-solid fa-check mr-0.5"></i> Executed
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                      <i class="fa-solid fa-xmark mr-0.5"></i> Failed
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 font-mono text-[11px] text-slate-500"><?php echo htmlspecialchars($a['ip_address'] ?? '127.0.0.1'); ?></td>
                <td class="py-3 px-4 text-right">
                  <button type="button" onclick="showSqlModal('<?php echo htmlspecialchars(addslashes($a['sql_executed'])); ?>')" class="px-2.5 py-1 text-slate-600 hover:text-indigo-600 border border-slate-200 hover:border-indigo-200 rounded text-xs transition cursor-pointer">
                    <i class="fa-solid fa-code"></i> View SQL
                  </button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: Pending Queue -->
    <div id="tab-pending" class="p-4 sm:p-6 space-y-4 hidden">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Pending Actions Awaiting User Confirmation</h3>
        <span class="text-xs text-slate-400"><?php echo count($pendingActions); ?> items total</span>
      </div>

      <div class="overflow-x-auto border border-slate-200 rounded-xl">
        <table class="w-full text-left text-xs whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
            <tr>
              <th class="py-3 px-4">Action ID</th>
              <th class="py-3 px-4">Operation Description</th>
              <th class="py-3 px-4">Target Table</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Created Time</th>
              <th class="py-3 px-4">Expires At</th>
              <th class="py-3 px-4 text-right">Query</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <?php if (empty($pendingActions)): ?>
              <tr><td colspan="7" class="py-8 text-center text-slate-400">No pending action items.</td></tr>
            <?php else: foreach ($pendingActions as $p): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-4 font-mono font-bold text-slate-800">#<?php echo $p['id']; ?></td>
                <td class="py-3 px-4 font-semibold text-slate-800 max-w-sm truncate" title="<?php echo htmlspecialchars($p['description']); ?>">
                  <?php echo htmlspecialchars($p['description']); ?>
                </td>
                <td class="py-3 px-4 font-mono text-indigo-700"><?php echo htmlspecialchars($p['target_table']); ?></td>
                <td class="py-3 px-4">
                  <?php
                    $statusStyles = [
                      'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                      'executed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                      'failed' => 'bg-rose-50 text-rose-700 border-rose-200'
                    ];
                    $st = $p['status'] ?? 'pending';
                    $style = $statusStyles[$st] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                  ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo $style; ?>">
                    <?php echo ucfirst($st); ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-slate-500"><?php echo date('M d, h:i A', strtotime($p['created_at'])); ?></td>
                <td class="py-3 px-4 text-slate-500"><?php echo $p['expires_at'] ? date('M d, h:i A', strtotime($p['expires_at'])) : '-'; ?></td>
                <td class="py-3 px-4 text-right">
                  <button type="button" onclick="showSqlModal('<?php echo htmlspecialchars(addslashes($p['sql_query'])); ?>')" class="px-2.5 py-1 text-slate-600 hover:text-indigo-600 border border-slate-200 hover:border-indigo-200 rounded text-xs transition cursor-pointer">
                    <i class="fa-solid fa-code"></i> Inspect
                  </button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 4: 21 Tables & Relational Map Guide -->
    <div id="tab-schema" class="p-4 sm:p-6 space-y-6 hidden">
      <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-indigo-600 mt-0.5"></i>
        <div class="text-xs text-indigo-900 leading-relaxed">
          <strong>Master Relational Architecture:</strong> Below is the comprehensive relational schema of all 21 tables utilized by BHOOMA AI. Every operational table enforces multi-tenant hospital isolation with <code class="bg-indigo-100 px-1 rounded font-mono">hospital_id</code>.
        </div>
      </div>

      <!-- Grid of Tables -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
        
        <!-- Table 1 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-hospital text-indigo-500 mr-1.5"></i> hospitals</span>
            <span class="text-[10px] text-slate-400 font-mono">Parent</span>
          </div>
          <p class="text-slate-600 text-[11px]">Master multi-hospital account table. All other tables filter by <code class="font-mono text-indigo-600">hospital_id</code>.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | Unique: username</div>
        </div>

        <!-- Table 2 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-hospital-user text-blue-500 mr-1.5"></i> patients</span>
            <span class="text-[10px] text-slate-400 font-mono">Core</span>
          </div>
          <p class="text-slate-600 text-[11px]">Patient registry. ID is unique MRN ('CP-2026-001'). Demographics, blood group, age, gender.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (varchar) | FK: hospital_id</div>
        </div>

        <!-- Table 3 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-user-doctor text-emerald-500 mr-1.5"></i> doctors</span>
            <span class="text-[10px] text-slate-400 font-mono">Core</span>
          </div>
          <p class="text-slate-600 text-[11px]">Doctors roster. ID ('doc-XXXX'). Experience, degree, phone. Specialty linked via doctor_categories.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (varchar) | FK: hospital_id</div>
        </div>

        <!-- Table 4 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-building text-amber-500 mr-1.5"></i> departments</span>
            <span class="text-[10px] text-slate-400 font-mono">Lookup</span>
          </div>
          <p class="text-slate-600 text-[11px]">Specialties &amp; hospital clinics (Cardiology, Orthopedics, Pediatrics, ICU, etc.).</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (varchar) | FK: hospital_id</div>
        </div>

        <!-- Table 5 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-link text-slate-500 mr-1.5"></i> doctor_categories</span>
            <span class="text-[10px] text-slate-400 font-mono">Junction</span>
          </div>
          <p class="text-slate-600 text-[11px]">Many-to-many junction table binding doctors to departments.</p>
          <div class="text-[10px] font-mono text-slate-500">Composite PK: (doctor_id, department_id)</div>
        </div>

        <!-- Table 6 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-calendar-check text-purple-500 mr-1.5"></i> appointments</span>
            <span class="text-[10px] text-slate-400 font-mono">Pipeline</span>
          </div>
          <p class="text-slate-600 text-[11px]">Consultations &amp; live queue. Column for date is <code class="font-mono font-bold">date</code>. Stage: 0 (Pre-booked) to 5 (Bed/Discharged).</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | FKs: patient_id, doctor_id</div>
        </div>

        <!-- Table 7 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-bed text-rose-500 mr-1.5"></i> beds</span>
            <span class="text-[10px] text-slate-400 font-mono">Inpatient</span>
          </div>
          <p class="text-slate-600 text-[11px]">Bed occupancy &amp; wards (ICU, General, Deluxe). Status: 'Available' or 'Occupied'. Linked to patient_id.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (varchar) | Unique: bed_number</div>
        </div>

        <!-- Table 8 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-users text-teal-500 mr-1.5"></i> staff</span>
            <span class="text-[10px] text-slate-400 font-mono">HR Roster</span>
          </div>
          <p class="text-slate-600 text-[11px]">Hospital staff roster (Nurses, Receptionists, RMOs, Technicians). Attendance is NEVER stored here.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | staff_code (STF-101)</div>
        </div>

        <!-- Table 9 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-clipboard-user text-cyan-500 mr-1.5"></i> staff_attendance</span>
            <span class="text-[10px] text-slate-400 font-mono">Daily Attendance</span>
          </div>
          <p class="text-slate-600 text-[11px]">Daily attendance records: status ('Present','Absent','Late','Half Day','On Leave'), check_in_time, working_hours.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id | Unique: (hospital_id, staff_id, date)</div>
        </div>

        <!-- Table 10 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-prescription-bottle-medical text-amber-500 mr-1.5"></i> prescriptions</span>
            <span class="text-[10px] text-slate-400 font-mono">Pharmacy</span>
          </div>
          <p class="text-slate-600 text-[11px]">Medications prescribed: medicine_name, dosage, frequency, duration, instructions. FK to appointment_id.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | FK: appointment_id</div>
        </div>

        <!-- Table 11 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-stethoscope text-red-500 mr-1.5"></i> diagnoses</span>
            <span class="text-[10px] text-slate-400 font-mono">Clinical</span>
          </div>
          <p class="text-slate-600 text-[11px]">Disease clinical descriptions for consultations. FK to appointment_id.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | FK: appointment_id</div>
        </div>

        <!-- Table 12 -->
        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-1.5">
          <div class="font-bold text-slate-900 flex items-center justify-between">
            <span><i class="fa-solid fa-clock-rotate-left text-blue-500 mr-1.5"></i> timeline_events</span>
            <span class="text-[10px] text-slate-400 font-mono">Audit</span>
          </div>
          <p class="text-slate-600 text-[11px]">Chronological patient care events: check-in, bed admission, discharge notes. FKs: appointment_id, patient_id.</p>
          <div class="text-[10px] font-mono text-slate-500">PK: id (int) | FKs: appointment_id, patient_id</div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- Transcript Modal -->
<div id="modal-transcript" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden animate-scale-in">
    <!-- Modal Header -->
    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
          <i class="fa-solid fa-comments"></i>
        </div>
        <div>
          <h4 class="font-bold text-slate-900 text-sm" id="modal-thread-title">Conversation Thread</h4>
          <p class="text-[11px] text-slate-500" id="modal-thread-subtitle">Loading transcript details...</p>
        </div>
      </div>
      <button type="button" onclick="closeTranscriptModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 bhooma-scrollbar" id="modal-messages-container">
      <div class="text-center py-8 text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading messages...</div>
    </div>

    <!-- Modal Footer -->
    <div class="px-5 py-3 border-t border-slate-200 bg-white flex items-center justify-between">
      <span class="text-[11px] text-slate-400" id="modal-thread-footer">End of conversation</span>
      <button type="button" onclick="closeTranscriptModal()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
        Close
      </button>
    </div>
  </div>
</div>

<!-- SQL Inspection Modal -->
<div id="modal-sql" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl flex flex-col overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
      <div class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
        <i class="fa-solid fa-code text-indigo-600"></i> Executed SQL Query
      </div>
      <button type="button" onclick="closeSqlModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="p-5 bg-slate-900 text-slate-200 overflow-x-auto text-xs font-mono leading-relaxed max-h-80" id="modal-sql-content">
    </div>
    <div class="px-5 py-2.5 bg-slate-100 border-t border-slate-200 flex justify-end">
      <button type="button" onclick="closeSqlModal()" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">Close</button>
    </div>
  </div>
</div>

<script>
function switchAiTab(tabId) {
  ['tab-convs', 'tab-audit', 'tab-pending', 'tab-schema'].forEach(id => {
    const el = document.getElementById(id);
    const btn = document.getElementById('btn-' + id);
    if (el) el.classList.toggle('hidden', id !== tabId);
    if (btn) {
      if (id === tabId) {
        btn.className = 'px-4 py-3 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2 whitespace-nowrap cursor-pointer';
      } else {
        btn.className = 'px-4 py-3 text-xs font-bold border-b-2 border-transparent text-slate-600 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap cursor-pointer';
      }
    }
  });
}

function filterConversations() {
  const q = document.getElementById('conv-search-input').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.conv-row');
  rows.forEach(r => {
    const user = r.getAttribute('data-user') || '';
    const page = r.getAttribute('data-page') || '';
    const text = r.getAttribute('data-text') || '';
    const match = !q || user.includes(q) || page.includes(q) || text.includes(q);
    r.style.display = match ? '' : 'none';
  });
}

async function openTranscriptModal(convId, user, page) {
  const modal = document.getElementById('modal-transcript');
  const title = document.getElementById('modal-thread-title');
  const sub = document.getElementById('modal-thread-subtitle');
  const container = document.getElementById('modal-messages-container');
  
  title.textContent = `Thread #${convId} — ${user}`;
  sub.textContent = `Initiated from ${page || 'General'} page`;
  container.innerHTML = `<div class="text-center py-8 text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading conversation history...</div>`;
  modal.classList.remove('hidden');

  try {
    const res = await fetch(`api/chatbot.php?action=history&conversation_id=${convId}`);
    const msgs = await res.json();
    
    if (!msgs || !msgs.length) {
      container.innerHTML = `<div class="text-center py-8 text-slate-400">No messages in this conversation.</div>`;
      return;
    }

    let html = '';
    msgs.forEach(m => {
      const isUser = m.role === 'user';
      const time = m.created_at ? new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
      
      if (isUser) {
        html += `
          <div class="flex items-start justify-end gap-2.5">
            <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-[80%] text-xs shadow-xs">
              <div class="font-medium whitespace-pre-wrap">${escapeHtml(m.content)}</div>
              <div class="text-[9px] text-indigo-200 mt-1 text-right">${time}</div>
            </div>
            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-[10px] shrink-0 mt-1 font-bold">
              U
            </div>
          </div>
        `;
      } else {
        html += `
          <div class="flex items-start gap-2.5">
            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 flex items-center justify-center text-white text-[10px] shrink-0 mt-1 shadow-xs">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-xs shadow-xs text-slate-700 space-y-2">
              <div class="whitespace-pre-wrap leading-relaxed">${formatMarkdownDisplay(m.content)}</div>
              ${m.sql_executed ? `
                <div class="bg-slate-900 text-slate-200 p-2 rounded-lg text-[10px] font-mono overflow-x-auto">
                  <div class="text-slate-400 uppercase text-[9px] font-bold mb-1">Executed SQL</div>
                  <code>${escapeHtml(m.sql_executed)}</code>
                </div>
              ` : ''}
              <div class="text-[9px] text-slate-400 text-right">${time}</div>
            </div>
          </div>
        `;
      }
    });

    container.innerHTML = html;
  } catch (err) {
    container.innerHTML = `<div class="text-center py-8 text-rose-500">Failed to load conversation history.</div>`;
  }
}

function closeTranscriptModal() {
  document.getElementById('modal-transcript').classList.add('hidden');
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
  return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function formatMarkdownDisplay(text) {
  if (!text) return '';
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>');
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
  link.setAttribute("download", `BHOOMA_AI_Action_Audit_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

<?php include 'includes/footer.php'; ?>

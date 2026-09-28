<!-- LOGOUT CONFIRMATION MODAL (Matching Reference UI) -->
<div id="modal-logout-confirm" class="hidden fixed inset-0 z-[250] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200 overflow-y-auto">
  <div class="bg-white rounded-[2.5rem] max-w-sm w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative text-center flex flex-col items-center my-auto transition-all transform scale-100">
    
    <!-- Top Character / Door Illustration -->
    <div class="w-full flex justify-center pt-2 mb-4">
      <img src="images/logout_graphic.png" alt="Logout" class="w-44 sm:w-52 h-auto object-contain pointer-events-none select-none">
    </div>

    <!-- Title -->
    <h3 class="text-2xl sm:text-[1.65rem] font-black text-slate-900 tracking-tight mb-2">Logout?</h3>

    <!-- Description -->
    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-6 px-1">
      Are you sure you want to log out of <?php echo htmlspecialchars($hospital_name ?? 'Bhooma Medicare Hospital'); ?>?<br class="hidden sm:inline"> You will need to log back in to manage clinical operations.
    </p>

    <!-- Action Buttons -->
    <div class="w-full space-y-3">
      <button 
        type="button" 
        id="btn-confirm-logout" 
        onclick="executeLogout()" 
        class="w-full py-3.5 px-6 rounded-full bg-[#ef4444] hover:bg-[#dc2626] active:bg-[#b91c1c] text-white font-bold text-sm sm:text-base shadow-lg shadow-red-500/25 transition duration-200 active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2">
        <span>Yes, Logout</span>
      </button>

      <button 
        type="button" 
        onclick="closeLogoutModal()" 
        class="w-full py-3.5 px-6 rounded-full bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-sm sm:text-base transition duration-200 cursor-pointer">
        <span>Cancel</span>
      </button>
    </div>

  </div>
</div>

<script>
  function openLogoutModal() {
    const modal = document.getElementById('modal-logout-confirm');
    if (modal) {
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
  }

  function closeLogoutModal() {
    const modal = document.getElementById('modal-logout-confirm');
    if (modal) {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  }

  async function executeLogout() {
    const btn = document.getElementById('btn-confirm-logout');
    if (btn) {
      btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Logging out...';
      btn.disabled = true;
    }
    try {
      await fetch('api/auth.php?action=logout');
      window.location.href = 'login.php';
    } catch (e) {
      console.error('Logout error:', e);
      window.location.href = 'login.php';
    }
  }

  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeLogoutModal();
    }
  });

  // Close when clicking outside the card
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal-logout-confirm');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeLogoutModal();
        }
      });
    }
  });
</script>

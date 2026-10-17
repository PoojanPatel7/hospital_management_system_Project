    </main>
  </div> <!-- End Main Content Wrapper -->

  <script>
    // Live clock if element exists
    function updateLiveClock() {
      const clockEl = document.getElementById('clock');
      if (clockEl) {
        const d = new Date();
        clockEl.textContent = d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
      }
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);
  </script>
</body>
</html>

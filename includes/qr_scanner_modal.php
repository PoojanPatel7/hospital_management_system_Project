<!-- ================= QR SCANNER MODAL (LIVE CAMERA HUD + NATIVE PHONE CAPTURE + MANUAL) ================= -->
<style>
@keyframes scanLaserSweep {
  0% { top: 10%; opacity: 0.7; }
  50% { top: 88%; opacity: 1; }
  100% { top: 10%; opacity: 0.7; }
}
.scanner-laser-anim {
  animation: scanLaserSweep 2.2s ease-in-out infinite;
}
.scanner-glow {
  box-shadow: 0 0 15px rgba(56, 189, 248, 0.6);
}
</style>

<div id="qr-scanner-modal" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-3 sm:p-4 bg-slate-900/80 backdrop-blur-sm transition-opacity overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 relative my-6 shadow-2xl border border-slate-200">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-black shadow-sm">
          <i class="fa-solid fa-qrcode"></i>
        </div>
        <div>
          <h3 class="text-sm font-black text-slate-900 leading-tight">Patient QR Scanner</h3>
          <p class="text-[11px] text-slate-500 font-medium">Scan Lifetime QR Card or search by MRN</p>
        </div>
      </div>
      <button type="button" onclick="closeQRScannerModal()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer" title="Close Scanner">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Scanner Viewfinder Box with Laser HUD Animation -->
    <div class="space-y-3">
      <div id="scanner-viewport-box" class="relative bg-slate-950 rounded-2xl overflow-hidden border-2 border-indigo-500/40 shadow-inner flex flex-col items-center justify-center min-h-[250px] sm:min-h-[270px]">
        <!-- Html5Qrcode Camera Target -->
        <div id="qr-reader" class="w-full" style="width: 100%;"></div>

        <!-- Laser Line Animation (Active during live scan) -->
        <div id="scanner-laser" class="pointer-events-none absolute inset-x-6 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent scanner-glow scanner-laser-anim z-10"></div>
        
        <!-- Reticle Corner HUD Brackets -->
        <div class="pointer-events-none absolute top-3 left-3 w-6 h-6 border-t-2 border-l-2 border-cyan-400 rounded-tl-lg z-10"></div>
        <div class="pointer-events-none absolute top-3 right-3 w-6 h-6 border-t-2 border-r-2 border-cyan-400 rounded-tr-lg z-10"></div>
        <div class="pointer-events-none absolute bottom-3 left-3 w-6 h-6 border-b-2 border-l-2 border-cyan-400 rounded-bl-lg z-10"></div>
        <div class="pointer-events-none absolute bottom-3 right-3 w-6 h-6 border-b-2 border-r-2 border-cyan-400 rounded-br-lg z-10"></div>

        <!-- Success Animation Overlay (Triggers on detection) -->
        <div id="scanner-success-overlay" class="hidden absolute inset-0 bg-emerald-950/95 backdrop-blur-xs flex flex-col items-center justify-center text-center p-6 space-y-2 z-30 transition-all">
          <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-400 text-emerald-400 flex items-center justify-center text-3xl animate-bounce shadow-[0_0_20px_#10b981]">
            <i class="fa-solid fa-check"></i>
          </div>
          <h4 class="text-white font-black text-base mt-2">QR Code Verified!</h4>
          <p id="scanner-success-subtitle" class="text-xs text-emerald-200 font-medium">Opening Patient Dossier...</p>
        </div>

        <!-- Live Status Message Overlay -->
        <div id="qr-scanner-status" class="absolute bottom-2 left-2 right-2 text-center text-[11px] font-bold text-white bg-black/70 backdrop-blur-xs py-1.5 px-3 rounded-xl border border-white/10 truncate z-20">
          <i class="fa-solid fa-camera mr-1 text-cyan-300"></i> Initializing camera...
        </div>
      </div>

      <!-- Live Controls (Switch Camera / Restart Stream) -->
      <div id="live-camera-controls" class="flex items-center justify-between gap-2 pt-0.5">
        <button type="button" id="btn-toggle-camera-facing" onclick="toggleCameraFacing()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
          <i class="fa-solid fa-camera-rotate text-[11px] text-indigo-600"></i> Switch Camera
        </button>
        <button type="button" id="btn-restart-camera" onclick="startCameraScanner()" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 border border-indigo-200 shadow-2xs cursor-pointer">
          <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Restart Video
        </button>
      </div>



      <!-- Or Separator -->
      <div class="relative flex py-1.5 items-center">
        <div class="flex-grow border-t border-slate-200"></div>
        <span class="flex-shrink mx-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">OR MANUAL LOOKUP</span>
        <div class="flex-grow border-t border-slate-200"></div>
      </div>

      <!-- Manual MRN / Token Form & USB Scanner Gun input -->
      <form onsubmit="handleManualQRLookup(event)" class="space-y-2">
        <div class="flex gap-2">
          <div class="relative flex-1">
            <i class="fa-solid fa-barcode absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input 
              type="text" 
              id="manual-qr-input" 
              placeholder="Scan or enter MRN (e.g. CP-2026-005)" 
              class="w-full pl-8 pr-3 py-2 text-xs font-semibold border border-slate-300 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/50 outline-none uppercase transition"
              autocomplete="off"
            >
          </div>
          <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 shrink-0 cursor-pointer">
            <i class="fa-solid fa-arrow-right"></i>
            <span>Open</span>
          </button>
        </div>
        <p class="text-[10px] text-slate-400 font-medium">Supports USB barcode scanners, typed MRNs, and QR tokens.</p>
      </form>
    </div>

  </div>
</div>

<!-- Load Html5Qrcode Library (Self-contained CDN) -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="js/qr-scanner.js?v=<?= time() ?>"></script>

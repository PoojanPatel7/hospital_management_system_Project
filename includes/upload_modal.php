<!-- File Upload Modal Component -->
<div class="modal fade hidden fixed inset-0 z-[100] flex items-center justify-center p-4" id="uploadModal" tabindex="-1" role="dialog">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeUploadModal()"></div>
    <div class="bg-[#16213e] rounded-2xl shadow-2xl w-full max-w-4xl relative flex flex-col max-h-[90vh] text-white border border-slate-700">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/50">
            <div>
                <h3 class="text-lg font-bold">Upload Patient Files</h3>
                <p class="text-sm text-slate-400" id="uploadPatientInfo">Loading...</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-white transition" onclick="closeUploadModal()">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
            <input type="hidden" id="uploadPatientId">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Link to Appointment (Optional)</label>
                <select id="uploadAppointmentSelect" class="w-full bg-[#1a1a2e] border border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">-- No Appointment (Standalone File) --</option>
                </select>
            </div>

            <!-- Upload Zone -->
            <div id="dropZone" class="border-2 border-dashed border-slate-600 rounded-2xl p-8 text-center hover:bg-slate-800/50 transition cursor-pointer mb-6" onclick="document.getElementById('fileInput').click()">
                <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-400 mb-3"></i>
                <h4 class="text-lg font-bold mb-1">Drag & Drop Files Here</h4>
                <p class="text-sm text-slate-400 mb-4">or click to browse from your device</p>
                <input type="file" id="fileInput" class="hidden" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.bmp" onchange="handleFilesSelected(event)">
                <div class="flex justify-center gap-3">
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition" onclick="event.stopPropagation(); document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-folder-open mr-2"></i> Browse Files
                    </button>
                    <button type="button" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition" onclick="event.stopPropagation(); openCameraCapture()">
                        <i class="fa-solid fa-camera mr-2"></i> Camera
                    </button>
                </div>
            </div>

            <!-- Upload List -->
            <div id="uploadFileList" class="space-y-4">
                <!-- Dynamically added file rows will go here -->
            </div>
            
            <!-- Camera Container (Hidden by default) -->
            <div id="cameraContainer" class="hidden flex flex-col items-center mb-6">
                <video id="cameraVideo" class="w-full max-w-md rounded-xl bg-black mb-3" autoplay playsinline></video>
                <div class="flex gap-3">
                    <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold" onclick="capturePhoto()">
                        <i class="fa-solid fa-camera mr-2"></i> Capture
                    </button>
                    <button type="button" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg font-bold" onclick="closeCamera()">
                        Cancel
                    </button>
                </div>
                <canvas id="cameraCanvas" class="hidden"></canvas>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-700/50 bg-slate-800/30 rounded-b-2xl">
            <div class="text-sm text-slate-400">
                <i class="fa-solid fa-user-circle mr-1"></i> Uploading as: <span class="font-bold text-white"><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'System'); ?></span>
            </div>
            <div class="flex gap-3">
                <button type="button" class="px-5 py-2 rounded-xl font-bold bg-slate-700 hover:bg-slate-600 transition" onclick="closeUploadModal()">Cancel</button>
                <button type="button" id="btnSubmitUpload" class="px-5 py-2 rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white transition disabled:opacity-50" onclick="submitUpload()">
                    <i class="fa-solid fa-upload mr-2"></i> Confirm & Upload
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/file-upload.js"></script>

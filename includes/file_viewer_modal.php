<!-- ================= ADVANCED FILE VIEWER MODAL ================= -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3/dist/css/glightbox.min.css" />

<style>
/* Advanced File Viewer Custom Styles */
#modal-file-viewer .modal-content {
    background: #1a1a2e;
    color: #e0e0e0;
}
#modal-file-viewer .filter-bar {
    background: #16213e;
    border-bottom: 1px solid #0f3460;
}
#modal-file-viewer .form-control, #modal-file-viewer .form-select {
    background-color: #0f3460;
    color: #e0e0e0;
    border: 1px solid #1a1a2e;
}
#modal-file-viewer .form-control:focus, #modal-file-viewer .form-select:focus {
    border-color: #533483;
    box-shadow: 0 0 0 0.2rem rgba(83, 52, 131, 0.25);
}
#modal-file-viewer .btn-outline-primary {
    color: #533483;
    border-color: #533483;
}
#modal-file-viewer .btn-outline-primary:hover, #modal-file-viewer .btn-outline-primary.active {
    background-color: #533483;
    color: #ffffff;
}

/* File Cards */
.fv-card {
    background: #16213e;
    border: 1px solid #0f3460;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.fv-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}
.fv-card-img-container {
    height: 160px;
    background: #0f3460;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.fv-card-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.fv-card:hover .fv-card-img-container img {
    transform: scale(1.05);
}
.fv-card-img-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}
.fv-card:hover .fv-card-img-overlay {
    opacity: 1;
}
.fv-card-body {
    padding: 12px;
}
.fv-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fv-meta {
    font-size: 0.75rem;
    color: #a0a0a0;
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.fv-highlight {
    font-size: 0.8rem;
    color: #e0e0e0;
    margin-bottom: 4px;
    font-style: italic;
}
.fv-desc {
    font-size: 0.75rem;
    color: #a0a0a0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Badges */
.fv-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}
.fv-badge-xray { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
.fv-badge-blood { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.fv-badge-mri { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
.fv-badge-prescription { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
.fv-badge-other { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }

/* Swiper View */
.swiper-view-container {
    height: calc(100vh - 200px);
    width: 100%;
}
.swiper-slide {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #16213e;
    border-radius: 16px;
    padding: 20px;
    box-sizing: border-box;
}
.swiper-slide .file-image-container {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 0;
    margin-bottom: 20px;
}
.swiper-slide .file-image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
    cursor: zoom-in;
}
.swiper-slide .file-info-panel {
    width: 100%;
    background: #0f3460;
    padding: 16px;
    border-radius: 12px;
}

/* Appointment Group */
.fv-appt-group {
    margin-bottom: 24px;
}
.fv-appt-header {
    background: #0f3460;
    padding: 10px 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

</style>

<div id="modal-file-viewer" class="hidden fixed inset-0 z-[250] flex items-center justify-center bg-[#0f172a]/90 backdrop-blur-md overflow-hidden">
    <div class="modal-content w-full h-full flex flex-col relative max-w-[1600px] mx-auto shadow-2xl">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-[#0f3460] bg-[#16213e] shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#533483] text-white flex items-center justify-center text-lg shadow-lg">
                    <i class="fa-solid fa-photo-film"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-white leading-tight">Advanced File Viewer</h3>
                    <p class="text-xs text-[#a0a0a0]" id="fv-patient-name">Patient Medical Records</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- View Mode Toggles -->
                <div class="flex bg-[#0f3460] rounded-lg p-1 mr-4 hidden sm:flex">
                    <button onclick="fvSetViewMode('gallery')" id="fv-btn-gallery" class="px-3 py-1.5 rounded text-sm font-medium transition active bg-[#533483] text-white" title="Gallery Grid View">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button onclick="fvSetViewMode('swipe')" id="fv-btn-swipe" class="px-3 py-1.5 rounded text-sm font-medium transition text-[#a0a0a0] hover:text-white" title="Swipe/Stack View">
                        <i class="fa-solid fa-layer-group"></i>
                    </button>
                    <button onclick="fvSetViewMode('appointment')" id="fv-btn-appointment" class="px-3 py-1.5 rounded text-sm font-medium transition text-[#a0a0a0] hover:text-white" title="Group by Appointment">
                        <i class="fa-solid fa-calendar-check"></i>
                    </button>
                </div>
                <button onclick="fvClose()" class="w-10 h-10 rounded-xl bg-[#0f3460] hover:bg-[#533483] text-white flex items-center justify-center transition shadow-md">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar p-3 shrink-0">
            <form id="fv-filter-form" class="flex flex-wrap items-center gap-3 text-sm" onsubmit="event.preventDefault(); fvApplyFilters();">
                <!-- Search -->
                <div class="flex-1 min-w-[200px] relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#a0a0a0]"></i>
                    <input type="text" id="fv-search" class="form-control w-full pl-9 pr-3 py-2 rounded-lg outline-none" placeholder="Search title, diagnosis, tags...">
                </div>
                
                <!-- Date Range -->
                <div class="flex items-center gap-2">
                    <input type="date" id="fv-date-from" class="form-control py-2 px-3 rounded-lg outline-none" title="From Date">
                    <span class="text-[#a0a0a0]">-</span>
                    <input type="date" id="fv-date-to" class="form-control py-2 px-3 rounded-lg outline-none" title="To Date">
                </div>

                <!-- Category -->
                <select id="fv-category" class="form-select py-2 px-3 rounded-lg outline-none w-32">
                    <option value="">All Categories</option>
                    <option value="X-Ray">X-Ray</option>
                    <option value="Blood Report">Blood Report</option>
                    <option value="MRI">MRI</option>
                    <option value="Prescription">Prescription</option>
                    <option value="ECG">ECG</option>
                    <option value="CT Scan">CT Scan</option>
                    <option value="Other">Other</option>
                </select>

                <!-- Sort -->
                <select id="fv-sort" class="form-select py-2 px-3 rounded-lg outline-none w-36">
                    <option value="date_desc">Newest First</option>
                    <option value="date_asc">Oldest First</option>
                    <option value="title_asc">Title A-Z</option>
                    <option value="category">Category</option>
                </select>

                <button type="submit" class="bg-[#533483] hover:bg-[#6c42a3] text-white px-4 py-2 rounded-lg font-bold transition">
                    Filter
                </button>
                <button type="button" onclick="fvResetFilters()" class="bg-[#0f3460] hover:bg-slate-700 text-[#a0a0a0] hover:text-white px-3 py-2 rounded-lg transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </form>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 relative" id="fv-main-container">
            <!-- Loading Indicator -->
            <div id="fv-loader" class="hidden absolute inset-0 bg-[#1a1a2e]/80 z-50 flex flex-col items-center justify-center">
                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-[#533483] mb-4"></i>
                <p class="text-white font-bold">Loading Files...</p>
            </div>
            
            <!-- Gallery View (Grid) -->
            <div id="fv-view-gallery" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <!-- Populated via JS -->
            </div>

            <!-- Appointment Grouped View -->
            <div id="fv-view-appointment" class="hidden">
                <!-- Populated via JS -->
            </div>

            <!-- Swipe/Stack View (Swiper) -->
            <div id="fv-view-swipe" class="hidden swiper-view-container h-full">
                <!-- Swiper main container -->
                <div class="swiper fv-swiper h-full w-full max-w-3xl mx-auto">
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper" id="fv-swiper-wrapper">
                        <!-- Slides populated via JS -->
                    </div>
                    <!-- Pagination -->
                    <div class="swiper-pagination"></div>
                    <!-- Navigation buttons -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="fv-empty-state" class="hidden h-full flex flex-col items-center justify-center text-center p-8">
                <div class="w-24 h-24 rounded-full bg-[#0f3460] flex items-center justify-center text-4xl text-[#533483] mb-4">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">No Files Found</h4>
                <p class="text-[#a0a0a0] max-w-md">There are no medical records matching your current filters. Try adjusting your search criteria.</p>
                <button onclick="fvResetFilters()" class="mt-6 bg-[#533483] hover:bg-[#6c42a3] text-white px-6 py-2.5 rounded-xl font-bold transition shadow-lg">
                    Clear Filters
                </button>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox@3/dist/js/glightbox.min.js"></script>
<script src="js/file-viewer.js"></script>

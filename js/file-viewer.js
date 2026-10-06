/**
 * Advanced File Viewer JavaScript
 * Handles gallery, swipe, and appointment views of patient medical records.
 */

let fvCurrentPatientId = null;
let fvAllFiles = [];
let fvFilteredFiles = [];
let fvCurrentMode = 'gallery';
let fvSwiperInstance = null;
let fvLightboxInstance = null;

/**
 * Initialize file viewer for a patient
 */
function initFileViewer(patientId, options = {}) {
    fvCurrentPatientId = patientId;
    
    // Optional config overrides
    if (options.patientName) {
        document.getElementById('fv-patient-name').textContent = options.patientName;
    }
    
    // Show modal
    document.getElementById('modal-file-viewer').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Reset state
    fvResetFilters(false);
    
    // Load data
    loadPatientFiles(patientId);
}

/**
 * Close File Viewer
 */
function fvClose() {
    document.getElementById('modal-file-viewer').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    
    if (fvSwiperInstance) {
        fvSwiperInstance.destroy(true, true);
        fvSwiperInstance = null;
    }
    if (fvLightboxInstance) {
        fvLightboxInstance.destroy();
        fvLightboxInstance = null;
    }
}

/**
 * Load files from API
 */
async function loadPatientFiles(patientId) {
    document.getElementById('fv-loader').classList.remove('hidden');
    
    try {
        const response = await fetch(`api/files.php?action=get_files&patient_id=${encodeURIComponent(patientId)}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            fvAllFiles = result.data || [];
            fvApplyFilters();
        } else {
            console.error('Error loading files:', result.message);
            fvAllFiles = [];
            fvApplyFilters();
        }
    } catch (error) {
        console.error('Fetch error:', error);
        fvAllFiles = [];
        fvApplyFilters();
    } finally {
        document.getElementById('fv-loader').classList.add('hidden');
    }
}

/**
 * Reset filters
 */
function fvResetFilters(apply = true) {
    document.getElementById('fv-search').value = '';
    document.getElementById('fv-date-from').value = '';
    document.getElementById('fv-date-to').value = '';
    document.getElementById('fv-category').value = '';
    document.getElementById('fv-sort').value = 'date_desc';
    
    if (apply) fvApplyFilters();
}

/**
 * Apply client-side filters and sorting
 */
function fvApplyFilters() {
    const search = document.getElementById('fv-search').value.toLowerCase();
    const dateFrom = document.getElementById('fv-date-from').value;
    const dateTo = document.getElementById('fv-date-to').value;
    const category = document.getElementById('fv-category').value;
    const sortBy = document.getElementById('fv-sort').value;
    
    fvFilteredFiles = fvAllFiles.filter(file => {
        // Search match
        if (search) {
            const searchable = `${file.title} ${file.description} ${file.highlight} ${file.tags}`.toLowerCase();
            if (!searchable.includes(search)) return false;
        }
        
        // Category match
        if (category && file.category !== category) return false;
        
        // Date match (assuming record_date is YYYY-MM-DD HH:MM:SS)
        const fileDateStr = file.record_date ? file.record_date.split(' ')[0] : '';
        if (dateFrom && fileDateStr < dateFrom) return false;
        if (dateTo && fileDateStr > dateTo) return false;
        
        return true;
    });
    
    fvFilteredFiles = sortFiles(fvFilteredFiles, sortBy);
    renderCurrentView();
}

/**
 * Sort files array
 */
function sortFiles(files, sortBy) {
    return files.sort((a, b) => {
        if (sortBy === 'date_desc') {
            return new Date(b.record_date || 0) - new Date(a.record_date || 0);
        } else if (sortBy === 'date_asc') {
            return new Date(a.record_date || 0) - new Date(b.record_date || 0);
        } else if (sortBy === 'title_asc') {
            return (a.title || '').localeCompare(b.title || '');
        } else if (sortBy === 'category') {
            const catCmp = (a.category || '').localeCompare(b.category || '');
            if (catCmp !== 0) return catCmp;
            return new Date(b.record_date || 0) - new Date(a.record_date || 0);
        }
        return 0;
    });
}

/**
 * Set View Mode
 */
function fvSetViewMode(mode) {
    fvCurrentMode = mode;
    
    // Update button states
    ['gallery', 'swipe', 'appointment'].forEach(m => {
        const btn = document.getElementById(`fv-btn-${m}`);
        if (btn) {
            if (m === mode) {
                btn.classList.add('bg-[#533483]', 'text-white');
                btn.classList.remove('text-[#a0a0a0]', 'hover:text-white');
            } else {
                btn.classList.remove('bg-[#533483]', 'text-white');
                btn.classList.add('text-[#a0a0a0]', 'hover:text-white');
            }
        }
    });
    
    renderCurrentView();
}

/**
 * Render the current view based on state
 */
function renderCurrentView() {
    // Hide all views
    document.getElementById('fv-view-gallery').classList.add('hidden');
    document.getElementById('fv-view-swipe').classList.add('hidden');
    document.getElementById('fv-view-appointment').classList.add('hidden');
    document.getElementById('fv-empty-state').classList.add('hidden');
    
    if (fvFilteredFiles.length === 0) {
        document.getElementById('fv-empty-state').classList.remove('hidden');
        return;
    }
    
    if (fvCurrentMode === 'gallery') {
        renderGalleryView(fvFilteredFiles);
    } else if (fvCurrentMode === 'swipe') {
        renderSwipeView(fvFilteredFiles);
    } else if (fvCurrentMode === 'appointment') {
        renderAppointmentView(fvFilteredFiles);
    }
}

/**
 * Render Gallery Grid
 */
function renderGalleryView(files) {
    const container = document.getElementById('fv-view-gallery');
    container.innerHTML = '';
    
    files.forEach((file, index) => {
        container.appendChild(createFileCard(file, index));
    });
    
    container.classList.remove('hidden');
    initLightbox();
}

/**
 * Create a single file card element
 */
function createFileCard(file, index) {
    const card = document.createElement('div');
    card.className = 'fv-card group relative';
    
    const badgeClass = getCategoryBadgeClass(file.category);
    const dateStr = file.record_date ? new Date(file.record_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '';
    
    // Setup image or placeholder
    const isImage = isImageFile(file.mime_type, file.file_name);
    const imgSrc = isImage && file.thumb_url ? file.thumb_url : (isImage ? file.url : null);
    
    const imgHtml = imgSrc 
        ? `<img src="${imgSrc}" loading="lazy" alt="${file.title}" class="glightbox" data-gallery="fv-gallery" data-title="${file.title}" data-description="${file.description || ''}" href="${file.url}">`
        : `<div class="text-[#a0a0a0] flex flex-col items-center"><i class="fa-solid fa-file-pdf text-4xl mb-2 text-[#f87171]"></i><span class="text-xs font-bold">Document</span></div>
           <a href="${file.url}" target="_blank" class="fv-card-img-overlay"><i class="fa-solid fa-external-link-alt text-white text-2xl"></i></a>`;
           
    if (imgSrc) {
        // We handle lightbox purely via classes for gallery
        // But for non-images we link out
    }
    
    card.innerHTML = `
        <div class="fv-card-img-container">
            ${imgHtml}
        </div>
        <div class="fv-card-body">
            <div class="fv-meta">
                <span class="fv-badge ${badgeClass}">${file.category || 'Other'}</span>
                <span>${dateStr}</span>
            </div>
            <h4 class="fv-title" title="${file.title}">${file.title}</h4>
            ${file.highlight ? `<p class="fv-highlight text-emerald-400 text-xs truncate"><i class="fa-solid fa-star text-[10px] mr-1"></i>${file.highlight}</p>` : ''}
            <div class="flex justify-between items-center mt-2 border-t border-[#0f3460] pt-2">
                <span class="text-[10px] text-[#a0a0a0] truncate flex-1">By: ${file.uploaded_by_name || 'System'}</span>
                <a href="${file.url}" download class="text-[#a0a0a0] hover:text-white p-1" title="Download">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
        </div>
    `;
    return card;
}

/**
 * Render Swipe / Stack view using Swiper.js
 */
function renderSwipeView(files) {
    const container = document.getElementById('fv-view-swipe');
    const wrapper = document.getElementById('fv-swiper-wrapper');
    wrapper.innerHTML = '';
    
    files.forEach((file) => {
        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        
        const badgeClass = getCategoryBadgeClass(file.category);
        const dateStr = file.record_date ? new Date(file.record_date).toLocaleString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
        const isImage = isImageFile(file.mime_type, file.file_name);
        
        const mediaHtml = isImage 
            ? `<img src="${file.url}" loading="lazy" alt="${file.title}" class="glightbox-swipe" data-gallery="fv-swipe-gallery" href="${file.url}">`
            : `<div class="flex flex-col items-center text-center p-8 bg-[#0f3460] rounded-xl"><i class="fa-solid fa-file-pdf text-6xl text-[#f87171] mb-4"></i><a href="${file.url}" target="_blank" class="bg-[#533483] text-white px-6 py-2 rounded-lg font-bold">Open Document</a></div>`;
        
        slide.innerHTML = `
            <div class="file-image-container">
                ${mediaHtml}
            </div>
            <div class="file-info-panel shadow-lg">
                <div class="flex justify-between items-start mb-2">
                    <h5 class="text-lg font-black text-white">${file.title}</h5>
                    <a href="${file.url}" download class="bg-[#1a1a2e] text-[#a0a0a0] hover:text-white w-8 h-8 rounded flex items-center justify-center">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
                <div class="flex items-center gap-3 mb-3 text-xs">
                    <span class="fv-badge ${badgeClass}">${file.category || 'Other'}</span>
                    <span class="text-[#a0a0a0]"><i class="fa-solid fa-calendar-alt mr-1"></i>${dateStr}</span>
                </div>
                ${file.highlight ? `<p class="text-sm text-emerald-400 font-bold mb-2"><i class="fa-solid fa-star mr-1"></i>${file.highlight}</p>` : ''}
                ${file.description ? `<p class="text-sm text-[#e0e0e0] mb-3 bg-[#1a1a2e] p-2 rounded">${file.description}</p>` : ''}
                <div class="text-xs text-[#a0a0a0] flex justify-between items-center mt-2 border-t border-[#1a1a2e] pt-2">
                    <span><i class="fa-solid fa-user-nurse mr-1"></i>${file.uploaded_by_name || 'System'}</span>
                    ${file.tags ? `<span><i class="fa-solid fa-tags mr-1"></i>${file.tags}</span>` : ''}
                </div>
            </div>
        `;
        wrapper.appendChild(slide);
    });
    
    container.classList.remove('hidden');
    
    // Initialize Swiper
    if (fvSwiperInstance) {
        fvSwiperInstance.destroy(true, true);
    }
    
    fvSwiperInstance = new Swiper('.fv-swiper', {
        effect: 'cards',
        grabCursor: true,
        pagination: {
            el: '.swiper-pagination',
            type: 'fraction',
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        keyboard: {
            enabled: true,
        }
    });
    
    initLightbox('.glightbox-swipe');
}

/**
 * Render grouped by appointment view
 */
function renderAppointmentView(files) {
    const container = document.getElementById('fv-view-appointment');
    container.innerHTML = '';
    
    // Group files by appointment ID / Date
    const grouped = {};
    const orphaned = [];
    
    files.forEach(file => {
        if (file.appointment_id) {
            if (!grouped[file.appointment_id]) grouped[file.appointment_id] = [];
            grouped[file.appointment_id].push(file);
        } else {
            orphaned.push(file);
        }
    });
    
    // Sort grouped keys (assuming higher ID = newer)
    const sortedKeys = Object.keys(grouped).sort((a,b) => parseInt(b) - parseInt(a));
    
    sortedKeys.forEach(appId => {
        const appFiles = grouped[appId];
        const dateStr = appFiles[0].record_date ? new Date(appFiles[0].record_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : 'Unknown Date';
        
        const groupEl = document.createElement('div');
        groupEl.className = 'fv-appt-group';
        
        groupEl.innerHTML = `
            <div class="fv-appt-header">
                <div>
                    <h4 class="text-white font-bold text-sm"><i class="fa-solid fa-stethoscope mr-2 text-[#533483]"></i>Consultation Visit</h4>
                    <p class="text-[10px] text-[#a0a0a0]">${dateStr}</p>
                </div>
                <span class="text-xs bg-[#1a1a2e] text-[#a0a0a0] px-2 py-1 rounded font-mono">APP-${String(appId).padStart(4,'0')}</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 px-2">
            </div>
        `;
        
        const grid = groupEl.querySelector('.grid');
        appFiles.forEach((file, index) => {
            grid.appendChild(createFileCard(file, index));
        });
        
        container.appendChild(groupEl);
    });
    
    if (orphaned.length > 0) {
        const groupEl = document.createElement('div');
        groupEl.className = 'fv-appt-group';
        groupEl.innerHTML = `
            <div class="fv-appt-header">
                <div>
                    <h4 class="text-white font-bold text-sm"><i class="fa-solid fa-folder-open mr-2 text-[#533483]"></i>Unlinked / General Files</h4>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 px-2">
            </div>
        `;
        const grid = groupEl.querySelector('.grid');
        orphaned.forEach((file, index) => {
            grid.appendChild(createFileCard(file, index));
        });
        container.appendChild(groupEl);
    }
    
    container.classList.remove('hidden');
    initLightbox();
}

/**
 * Initialize Lightbox
 */
function initLightbox(selector = '.glightbox') {
    if (fvLightboxInstance) {
        fvLightboxInstance.destroy();
    }
    fvLightboxInstance = GLightbox({
        selector: selector,
        touchNavigation: true,
        loop: true,
        zoomable: true,
        openEffect: 'zoom',
        closeEffect: 'zoom',
        slideEffect: 'slide'
    });
}

/**
 * Helpers
 */
function isImageFile(mimeType, fileName) {
    if (mimeType && mimeType.startsWith('image/')) return true;
    if (!fileName) return false;
    const ext = fileName.split('.').pop().toLowerCase();
    return ['jpg','jpeg','png','gif','webp','bmp'].includes(ext);
}

function getCategoryBadgeClass(category) {
    const c = (category || '').toLowerCase();
    if (c.includes('x-ray') || c.includes('xray')) return 'fv-badge-xray';
    if (c.includes('blood') || c.includes('lab')) return 'fv-badge-blood';
    if (c.includes('mri') || c.includes('scan') || c.includes('ct')) return 'fv-badge-mri';
    if (c.includes('prescription') || c.includes('rx')) return 'fv-badge-prescription';
    return 'fv-badge-other';
}

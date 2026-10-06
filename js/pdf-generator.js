/**
 * Hospital Management System — Patient Medical Dossier PDF Generator Engine
 * Uses jsPDF to compile patient medical history and images into a branded PDF.
 */

// Dynamically ensure jsPDF is loaded
if (typeof window.jspdf === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    document.head.appendChild(script);
}

/**
 * Loads an image from a URL and converts it to a sanitized Base64 JPEG data URL
 * along with its natural dimensions.
 */
async function loadImageAsBase64(url) {
    if (!url) return null;
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                const w = img.naturalWidth || img.width || 800;
                const h = img.naturalHeight || img.height || 600;
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                
                // Draw white background for transparent PNGs
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
                
                const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                resolve({
                    dataUrl: dataUrl,
                    width: w,
                    height: h
                });
            } catch (err) {
                console.warn("Canvas conversion failed for: " + url, err);
                resolve(null);
            }
        };
        img.onerror = () => {
            console.warn("Failed to load image from URL: " + url);
            resolve(null);
        };
        img.src = url;
    });
}

/**
 * Main PDF Generation function for a patient
 */
async function generatePatientPDF(patientId, options = {}) {
    if (!patientId) {
        alert("Patient ID is required to generate PDF.");
        return;
    }

    if (typeof window.jspdf === 'undefined') {
        alert("PDF Engine is initializing. Please tap Export PDF again in 2 seconds.");
        return;
    }

    if (typeof showToast === 'function') {
        showToast('Generating PDF', 'Compiling medical records and high-quality scans...', 'success');
    }

    try {
        let apiUrl = `api/pdf.php?action=get_pdf_data&patient_id=${encodeURIComponent(patientId)}`;
        if (options.appointment_id) apiUrl += `&appointment_id=${encodeURIComponent(options.appointment_id)}`;
        if (options.from_date) apiUrl += `&from_date=${encodeURIComponent(options.from_date)}`;
        if (options.to_date) apiUrl += `&to_date=${encodeURIComponent(options.to_date)}`;

        const res = await fetch(apiUrl);
        if (!res.ok) {
            throw new Error(`Server returned HTTP ${res.status}`);
        }
        const data = await res.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to fetch patient records from server.');
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
            compress: true
        });

        const pageWidth = Number(doc.internal.pageSize.getWidth().toFixed(2));
        const pageHeight = Number(doc.internal.pageSize.getHeight().toFixed(2));
        const margin = 20;

        // ================= PAGE 1: COVER PAGE =================
        let currentY = 20;

        // Hospital Logo
        const logoUrl = 'images/Logo.png';
        const logoObj = await loadImageAsBase64(logoUrl);
        if (logoObj && logoObj.dataUrl && logoObj.width > 0 && logoObj.height > 0) {
            try {
                const logoMaxW = 36;
                const logoMaxH = 26;
                let logoW = logoMaxW;
                let logoH = (logoObj.height * logoMaxW) / logoObj.width;
                if (logoH > logoMaxH) {
                    logoH = logoMaxH;
                    logoW = (logoObj.width * logoMaxH) / logoObj.height;
                }
                logoW = Math.max(10, Number(logoW.toFixed(2)));
                logoH = Math.max(10, Number(logoH.toFixed(2)));
                const logoX = Number(((pageWidth - logoW) / 2).toFixed(2));
                doc.addImage(logoObj.dataUrl, 'JPEG', logoX, currentY, logoW, logoH, undefined, 'FAST');
                currentY += logoH + 8;
            } catch (e) {
                console.warn("Logo addImage skipped:", e);
                currentY += 15;
            }
        } else {
            currentY += 10;
        }

        // Hospital Name & Details
        const hospName = String(data.hospital?.name || 'Bhooma Medicare Hospital').toUpperCase();
        doc.setFont("helvetica", "bold");
        doc.setFontSize(18);
        doc.setTextColor(15, 23, 42); // slate-900
        doc.text(hospName, pageWidth / 2, currentY, { align: 'center' });
        currentY += 6;

        doc.setFont("helvetica", "normal");
        doc.setFontSize(10);
        doc.setTextColor(100, 116, 139); // slate-500
        const hospAddr = String(data.hospital?.address || 'Multi-Speciality Healthcare Center');
        doc.text(hospAddr, pageWidth / 2, currentY, { align: 'center' });
        currentY += 5;
        
        const hospPhone = String(data.hospital?.phone || '');
        if (hospPhone) {
            doc.text(`Phone: ${hospPhone}`, pageWidth / 2, currentY, { align: 'center' });
            currentY += 8;
        } else {
            currentY += 5;
        }

        // Title Pill
        doc.setDrawColor(226, 232, 240); // slate-200
        doc.setLineWidth(0.5);
        doc.line(margin, currentY, pageWidth - margin, currentY);
        currentY += 9;

        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(30, 41, 59); // slate-800
        doc.text("PATIENT MEDICAL DOSSIER & PRESCRIPTIONS", pageWidth / 2, currentY, { align: 'center' });
        currentY += 10;

        // Patient Details Box
        doc.setFillColor(248, 250, 252); // slate-50
        doc.roundedRect(margin, currentY, pageWidth - (margin * 2), 52, 3, 3, 'F');
        doc.setDrawColor(203, 213, 225); // slate-300
        doc.roundedRect(margin, currentY, pageWidth - (margin * 2), 52, 3, 3, 'D');

        let boxY = currentY + 8;
        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        doc.setTextColor(15, 23, 42);
        doc.text("PATIENT INFORMATION", margin + 6, boxY);
        boxY += 8;

        doc.setFontSize(9.5);
        const pt = data.patient || {};
        const pName = String(pt.full_name || pt.name || 'Patient');
        const pId = String(pt.id || patientId);
        const pAgeGen = `${pt.age ? pt.age + ' Yrs' : 'N/A'} / ${pt.gender || 'N/A'}`;
        const pBlood = String(pt.blood_group || 'Unknown');
        const pPhone = String(pt.phone || 'N/A');

        doc.setFont("helvetica", "normal");
        doc.setTextColor(71, 85, 105); // slate-600
        doc.text(`Patient Full Name:`, margin + 6, boxY);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(15, 23, 42);
        doc.text(pName, margin + 45, boxY);
        boxY += 7;

        doc.setFont("helvetica", "normal");
        doc.setTextColor(71, 85, 105);
        doc.text(`MRN (Patient ID):`, margin + 6, boxY);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(15, 23, 42);
        doc.text(pId, margin + 45, boxY);
        boxY += 7;

        doc.setFont("helvetica", "normal");
        doc.setTextColor(71, 85, 105);
        doc.text(`Age / Gender:`, margin + 6, boxY);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(15, 23, 42);
        doc.text(pAgeGen, margin + 45, boxY);
        boxY += 7;

        doc.setFont("helvetica", "normal");
        doc.setTextColor(71, 85, 105);
        doc.text(`Blood Group:`, margin + 6, boxY);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(225, 29, 72); // rose-600
        doc.text(pBlood, margin + 45, boxY);

        doc.setFont("helvetica", "normal");
        doc.setTextColor(71, 85, 105);
        doc.text(`Contact:`, margin + 85, boxY);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(15, 23, 42);
        doc.text(pPhone, margin + 102, boxY);

        currentY += 62;

        // Attending Doctors
        if (data.doctors && data.doctors.length > 0) {
            doc.setFont("helvetica", "bold");
            doc.setFontSize(10.5);
            doc.setTextColor(15, 23, 42);
            doc.text("ATTENDING CONSULTANTS & DOCTORS", margin, currentY);
            currentY += 6;

            doc.setFont("helvetica", "normal");
            doc.setFontSize(9);
            doc.setTextColor(51, 65, 85);

            data.doctors.slice(0, 4).forEach((docInfo) => {
                const docName = `• Dr. ${docInfo.name}`;
                const docSpec = docInfo.specialty ? ` — ${docInfo.specialty}` : '';
                doc.text(docName + docSpec, margin + 4, currentY);
                currentY += 5.5;
            });
            currentY += 4;
        }

        // Summary Footnotes on Cover Page
        const totalFiles = (data.files || []).length;
        doc.setFontSize(8.5);
        doc.setTextColor(148, 163, 184); // slate-400
        doc.text(`Generated On: ${data.generated_at || new Date().toLocaleString()}`, margin, pageHeight - 25);
        doc.text(`Generated By: ${data.generated_by || 'Hospital Staff'}`, margin, pageHeight - 20);
        doc.text(`Total Medical Files / Prescriptions Compiled: ${totalFiles}`, margin, pageHeight - 15);

        // ================= PAGE 2+: INDIVIDUAL FILE & IMAGE PAGES =================
        const files = data.files || [];

        for (let i = 0; i < files.length; i++) {
            const f = files[i];

            doc.addPage();
            let pageNum = i + 2;

            // Header band
            doc.setFont("helvetica", "bold");
            doc.setFontSize(8);
            doc.setTextColor(148, 163, 184);
            doc.text(`${hospName}  |  PATIENT: ${pName} (${pId})`, margin, 12);
            doc.text(`PAGE ${pageNum}`, pageWidth - margin, 12, { align: 'right' });

            doc.setDrawColor(241, 245, 249);
            doc.setLineWidth(0.3);
            doc.line(margin, 14, pageWidth - margin, 14);

            // Fetch and render image
            let imgRendered = false;
            let imageBottomY = 20;

            if (!f.is_pdf && f.file_url) {
                const loadedImg = await loadImageAsBase64(f.file_url);

                if (loadedImg && loadedImg.dataUrl && loadedImg.width > 0 && loadedImg.height > 0) {
                    try {
                        const maxW = pageWidth - (margin * 2);
                        const maxH = pageHeight * 0.58; // Allow 58% of page for image

                        let finalW = maxW;
                        let finalH = (loadedImg.height * maxW) / loadedImg.width;

                        if (finalH > maxH) {
                            finalH = maxH;
                            finalW = (loadedImg.width * maxH) / loadedImg.height;
                        }

                        // Strictly ensure sanitized positive numbers
                        finalW = Math.max(20, Number(finalW.toFixed(2)));
                        finalH = Math.max(20, Number(finalH.toFixed(2)));
                        const xPos = Number(((pageWidth - finalW) / 2).toFixed(2));
                        const yPos = 18;

                        // Add Image to PDF
                        doc.addImage(loadedImg.dataUrl, 'JPEG', xPos, yPos, finalW, finalH, undefined, 'FAST');
                        imgRendered = true;
                        imageBottomY = yPos + finalH + 10;
                    } catch (addErr) {
                        console.warn("Failed to add image to PDF:", addErr);
                    }
                }
            }

            // If image could not be loaded or is a PDF document
            if (!imgRendered) {
                const placeholderH = 50;
                doc.setFillColor(241, 245, 249);
                doc.roundedRect(margin, 20, pageWidth - (margin * 2), placeholderH, 3, 3, 'F');
                doc.setDrawColor(203, 213, 225);
                doc.roundedRect(margin, 20, pageWidth - (margin * 2), placeholderH, 3, 3, 'D');

                doc.setFont("helvetica", "bold");
                doc.setFontSize(12);
                doc.setTextColor(225, 29, 72);
                doc.text(f.is_pdf ? "[ PDF Medical Document ]" : "[ Medical Record File ]", pageWidth / 2, 40, { align: 'center' });
                
                doc.setFont("helvetica", "normal");
                doc.setFontSize(9);
                doc.setTextColor(100, 116, 139);
                doc.text("Document stored in digital clinical archive. Please view digital file via system viewer.", pageWidth / 2, 50, { align: 'center' });

                imageBottomY = 20 + placeholderH + 12;
            }

            // Metadata card below image
            let infoY = imageBottomY;
            doc.setDrawColor(226, 232, 240);
            doc.line(margin, infoY, pageWidth - margin, infoY);
            infoY += 7;

            // Document Title
            doc.setFont("helvetica", "bold");
            doc.setFontSize(12);
            doc.setTextColor(15, 23, 42);
            doc.text(String(f.title || 'Doctor Letterhead Pad'), margin, infoY);
            infoY += 6;

            // Category & Date Badge
            doc.setFont("helvetica", "normal");
            doc.setFontSize(9);
            doc.setTextColor(71, 85, 105);
            doc.text(`Category: ${f.category || 'Medical File'}`, margin, infoY);
            
            doc.setFont("helvetica", "bold");
            doc.setTextColor(217, 119, 6); // amber-600
            doc.text(`Date: ${f.record_date || 'N/A'}`, margin + 65, infoY);
            
            doc.setFont("helvetica", "normal");
            doc.setTextColor(71, 85, 105);
            doc.text(`Uploaded By: ${f.uploaded_by_name || 'Hospital Staff'}`, margin + 115, infoY);
            infoY += 6;

            if (f.doctor_name) {
                doc.text(`Consultant: Dr. ${f.doctor_name}`, margin, infoY);
                if (f.appointment_date) {
                    doc.text(`Visit Date: ${f.appointment_date}`, margin + 65, infoY);
                }
                infoY += 6;
            }

            // Clinical Highlight
            if (f.highlight) {
                doc.setFont("helvetica", "bold");
                doc.setTextColor(225, 29, 72); // rose-600
                doc.text(`Clinical Highlight: ${f.highlight}`, margin, infoY);
                infoY += 6;
            }

            // Notes / Description
            if (f.description) {
                doc.setFont("helvetica", "normal");
                doc.setFontSize(8.5);
                doc.setTextColor(51, 65, 85);
                const splitDesc = doc.splitTextToSize(`Notes: ${f.description}`, pageWidth - (margin * 2));
                doc.text(splitDesc, margin, infoY);
            }
        }

        // ================= SAVE / DOWNLOAD =================
        const safePtName = (pt.full_name || pt.name || 'Patient').replace(/[^a-zA-Z0-9_-]/g, '_');
        const fileName = `${safePtName}_${patientId}_Medical_File.pdf`;
        doc.save(fileName);

        if (typeof showToast === 'function') {
            showToast('PDF Exported', `${fileName} downloaded successfully!`, 'success');
        }

    } catch (err) {
        console.error("PDF Generation Error:", err);
        const errMsg = err?.message || 'Unknown error occurred while generating PDF.';
        if (typeof showToast === 'function') {
            showToast('PDF Error', errMsg, 'error');
        } else {
            alert("PDF Export Error: " + errMsg);
        }
    }
}

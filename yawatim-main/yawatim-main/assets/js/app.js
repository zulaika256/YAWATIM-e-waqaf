// assets/js/app.js - YAWATIM System Frontend Interactions

document.addEventListener('DOMContentLoaded', () => {
    initLayout();
    initModals();
    initFilters();
    initExports();
    initUnifiedLogin();
});

function initUnifiedLogin() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginCard = document.querySelector('.login-card');
    const loginLogo = document.querySelector('.login-logo');
    const channelBadge = document.querySelector('.channel-badge');
    const submitBtn = document.querySelector('.login-submit-btn');

    if (!emailInput || !loginCard) return;

    const updateTheme = (email) => {
        const value = (email || '').toLowerCase();
        let channel = 'Admin';
        let accent = '#1d4ed8';
        let light = '#eff6ff';
        let dark = '#1e40af';
        let icon = 'fa-shield-halved';
        let badge = 'Admin Portal';
        let tagline = 'Secure access for administrators and channel partners';

        if (value.includes('bankrakyat') || value.includes('rakyat') || value.includes('siti')) {
            channel = 'Bank Rakyat';
            accent = '#b45309';
            light = '#fffbeb';
            dark = '#92400e';
            icon = 'fa-landmark';
            badge = 'Bank Rakyat Portal';
            tagline = 'Collaborating with Bank Kerjasama Rakyat Malaysia';
        } else if (value.includes('pos') || value.includes('posmalaysia') || value.includes('chong')) {
            channel = 'Pos Malaysia';
            accent = '#dc2626';
            light = '#fef2f2';
            dark = '#b91c1c';
            icon = 'fa-envelope-open-text';
            badge = 'Pos Malaysia Portal';
            tagline = 'Reaching every corner via the postal network';
        } else if (value.includes('ebb') || value.includes('muthu')) {
            channel = 'EBB';
            accent = '#15803d';
            light = '#f0fdf4';
            dark = '#166534';
            icon = 'fa-coins';
            badge = 'EBB Portal';
            tagline = 'Driving impact through the EBB channel';
        } else if (value.includes('bsn') || value.includes('safwan')) {
            channel = 'BSN';
            accent = '#1d4ed8';
            light = '#eff6ff';
            dark = '#1e40af';
            icon = 'fa-building-columns';
            badge = 'BSN Portal';
            tagline = 'Empowering Wakalah partners with BSN';
        } else if (value.includes('admin')) {
            channel = 'Admin';
            accent = '#1d4ed8';
            light = '#eff6ff';
            dark = '#1e40af';
            icon = 'fa-shield-halved';
            badge = 'Admin Portal';
            tagline = 'Secure access for administrators and channel partners';
        }

        document.body.style.setProperty('--login-accent', accent);
        document.body.style.setProperty('--login-accent-light', light);
        document.body.style.setProperty('--login-accent-dark', dark);

        if (loginCard) {
            loginCard.style.setProperty('--login-accent', accent);
            loginCard.style.setProperty('--login-accent-light', light);
            loginCard.style.setProperty('--login-accent-dark', dark);
        }

        if (loginLogo) {
            loginLogo.style.backgroundColor = accent;
            loginLogo.style.transition = 'all 0.3s ease';
        }
        if (channelBadge) {
            channelBadge.style.backgroundColor = light;
            channelBadge.style.color = accent;
            channelBadge.style.borderColor = accent;
            channelBadge.querySelector('i').className = 'fa-solid ' + icon;
            channelBadge.querySelector('span').textContent = badge;
        }
        const title = document.querySelector('.login-title');
        const subtitle = document.querySelector('.login-subtitle');
        if (title) title.style.color = accent;
        if (subtitle) subtitle.textContent = tagline;
        if (submitBtn) submitBtn.style.background = accent;
        if (submitBtn) submitBtn.style.setProperty('--login-accent', accent);
        const loginBody = document.querySelector('.login-body');
        if (loginBody) {
            loginBody.style.background = `linear-gradient(135deg, ${light} 0%, #f8fafc 100%)`;
        }
    };

    emailInput.addEventListener('input', () => updateTheme(emailInput.value));
    emailInput.addEventListener('focus', () => updateTheme(emailInput.value));

    document.querySelectorAll('.quick-login-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const email = chip.getAttribute('data-email');
            const password = chip.getAttribute('data-password');
            if (emailInput) emailInput.value = email;
            if (passwordInput) passwordInput.value = password;
            updateTheme(email || '');
        });
    });

    updateTheme(emailInput.value);
}

// 1. Layout & Responsive Sidebar
function initLayout() {
    const toggleBtn = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }
}

// 2. Toast Notifications
function showToast(message, type = 'info') {
    // Create container if not exists
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    let icon = '<i class="fa-solid fa-info-circle"></i>';
    if (type === 'success') icon = '<i class="fa-solid fa-circle-check"></i>';
    if (type === 'error') icon = '<i class="fa-solid fa-triangle-exclamation"></i>';

    toast.innerHTML = `
        ${icon}
        <div class="toast-content">${message}</div>
    `;

    container.appendChild(toast);

    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 50);

    // Auto-remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 350);
    }, 4000);
}

// 3. Modal Controls
function initModals() {
    // Open modal triggers
    const openBtns = document.querySelectorAll('[data-modal-open]');
    openBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-open');
            openModal(modalId);
        });
    });

    // Close modal triggers
    const closeBtns = document.querySelectorAll('.modal-close, [data-modal-close]');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const overlay = btn.closest('.modal-overlay');
            if (overlay) {
                closeModal(overlay.id);
            }
        });
    });

    // Close modal clicking outside
    const overlays = document.querySelectorAll('.modal-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay.id);
            }
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('open');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
    }
}

// 4. Client-side Real-time Search and Dropdown Filter
function initFilters() {
    const searchInput = document.querySelector('[data-search-target]');
    const filterSelects = document.querySelectorAll('[data-filter-column]');
    const table = document.querySelector('.table-yawatim');

    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) return;

    function filterTable() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        rows.forEach(row => {
            let showRow = true;

            // Search filter
            if (query) {
                let matchQuery = false;
                row.querySelectorAll('td').forEach(td => {
                    // Check if content matches, excluding actions column
                    if (!td.classList.contains('actions-td') && td.innerText.toLowerCase().includes(query)) {
                        matchQuery = true;
                    }
                });
                if (!matchQuery) showRow = false;
            }

            // Dropdown column filters
            filterSelects.forEach(select => {
                const val = select.value;
                const colIdx = parseInt(select.getAttribute('data-filter-column'));

                if (val && showRow) {
                    const td = row.querySelectorAll('td')[colIdx];
                    if (td) {
                        const cellText = td.innerText.trim();
                        // Support partial or direct match
                        if (cellText !== val && !cellText.toLowerCase().includes(val.toLowerCase())) {
                            showRow = false;
                        }
                    }
                }
            });

            row.style.display = showRow ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    filterSelects.forEach(select => {
        select.addEventListener('change', filterTable);
    });
}

// 5. Excel (CSV) and PDF Export Functionality
function initExports() {
    const csvBtns = document.querySelectorAll('[data-export-csv]');
    const pdfBtns = document.querySelectorAll('[data-export-pdf]');

    csvBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tableId = btn.getAttribute('data-export-csv');
            exportTableToCSV(tableId);
        });
    });

    pdfBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tableId = btn.getAttribute('data-export-pdf');
            exportTableToPDF(tableId);
        });
    });
}

function exportTableToPDF(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    if (!window.jspdf || !window.jspdf.jsPDF) {
        alert('PDF export not available. Required libraries are missing.');
        return;
    }

    // Get dynamic theme color
    const themePrimary = getComputedStyle(document.documentElement).getPropertyValue('--primary-blue').trim() || '#1e3a8a';

    function hexToRgb(hex) {
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function (m, r, g, b) {
            return r + r + g + g + b + b;
        });
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? [
            parseInt(result[1], 16),
            parseInt(result[2], 16),
            parseInt(result[3], 16)
        ] : [30, 58, 138];
    }
    const primaryRGB = hexToRgb(themePrimary);

    // 1. Gather Report Meta Information
    const orgName = document.querySelector('.print-header h2')?.innerText || 'YAYASAN WAKAF PENDIDIKAN ANAK YATIM ATAU MISKIN MALAYSIA';
    const systemSub = document.querySelector('.print-header p')?.innerText || 'Deployment and Donation Monitoring System (YAWATIM)';
    const reportTitle = document.querySelector('.print-header h3')?.innerText || 'Donation Collection Report';
    const metaText = document.querySelector('.print-header p:last-of-type')?.innerText || '';

    // 2. Gather Summary Stats
    const stats = [];
    const statCards = document.querySelectorAll('#report-summary-cards .stat-card');
    statCards.forEach(card => {
        const label = card.querySelector('.stat-label')?.innerText || '';
        const val = card.querySelector('.stat-val')?.innerText || '';
        stats.push({ label, val });
    });

    // 3. Extract visible rows from the table
    const headers = [];
    const body = [];

    const headerCells = table.querySelectorAll('thead tr th');
    const headerRow = [];
    headerCells.forEach(cell => {
        // Exclude actions column if any
        if (cell.classList.contains('actions-td') || cell.querySelector('.action-group')) {
            return;
        }
        headerRow.push(cell.innerText.trim());
    });
    headers.push(headerRow);

    const bodyRows = table.querySelectorAll('tbody tr');
    bodyRows.forEach(row => {
        if (row.style.display === 'none') {
            return; // skip filtered-out rows
        }
        if (row.querySelector('td[colspan]')) {
            return; // skip informational/no-records row
        }
        const rowData = [];
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            // Exclude actions column if any
            if (cell.classList.contains('actions-td') || cell.querySelector('.action-group')) {
                return;
            }
            rowData.push(cell.innerText.trim());
        });
        body.push(rowData);
    });

    // 4. Initialize jsPDF
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

    // Draw header section on the first page
    pdf.setFont("Helvetica", "bold");
    pdf.setFontSize(13);
    pdf.setTextColor(primaryRGB[0], primaryRGB[1], primaryRGB[2]);
    pdf.text(orgName, 421, 40, { align: "center" });

    pdf.setFont("Helvetica", "normal");
    pdf.setFontSize(8.5);
    pdf.setTextColor(71, 85, 105);
    pdf.text(systemSub, 421, 52, { align: "center" });

    pdf.setDrawColor(primaryRGB[0], primaryRGB[1], primaryRGB[2]);
    pdf.setLineWidth(1.5);
    pdf.line(30, 60, 812, 60);

    pdf.setFont("Helvetica", "bold");
    pdf.setFontSize(11);
    pdf.setTextColor(15, 23, 42);
    pdf.text(reportTitle, 421, 75, { align: "center" });

    pdf.setFont("Helvetica", "normal");
    pdf.setFontSize(8);
    pdf.setTextColor(100, 116, 139);
    pdf.text(metaText, 421, 87, { align: "center" });

    let currentY = 100;

    // Draw stats cards
    if (stats.length > 0) {
        const gap = 15;
        const totalWidth = 782;
        const cardWidth = (totalWidth - (stats.length - 1) * gap) / stats.length;
        const cardHeight = 42;

        stats.forEach((stat, idx) => {
            const x = 30 + idx * (cardWidth + gap);
            pdf.setFillColor(248, 250, 252);
            pdf.setDrawColor(226, 232, 240);
            pdf.setLineWidth(1);
            pdf.roundedRect(x, currentY, cardWidth, cardHeight, 4, 4, "FD");

            pdf.setFont("Helvetica", "bold");
            pdf.setFontSize(7.5);
            pdf.setTextColor(148, 163, 184);
            pdf.text(stat.label.toUpperCase(), x + 10, currentY + 14);

            pdf.setFont("Helvetica", "bold");
            pdf.setFontSize(12);
            pdf.setTextColor(15, 23, 42);
            pdf.text(stat.val, x + 10, currentY + 30);
        });
        currentY += cardHeight + 15;
    }

    // 5. Render Table using AutoTable
    pdf.autoTable({
        head: headers,
        body: body,
        startY: currentY,
        margin: { left: 30, right: 30, bottom: 40 },
        styles: {
            font: 'helvetica',
            fontSize: 8.5,
            cellPadding: 5
        },
        headStyles: {
            fillColor: primaryRGB,
            textColor: 255,
            fontStyle: 'bold',
            halign: 'left'
        },
        alternateRowStyles: {
            fillColor: [248, 250, 252]
        },
        didParseCell: function (data) {
            const headerText = headers[0][data.column.index] ? headers[0][data.column.index].toLowerCase() : '';
            if (headerText.includes('amount') || headerText.includes('total') || headerText.includes('raised') || headerText.includes('rm')) {
                data.cell.styles.halign = 'right';
            }
            if (headerText.includes('count') || headerText.includes('transactions') || headerText.includes('quantity')) {
                data.cell.styles.halign = 'center';
            }
        }
    });

    // 6. Draw page numbers on all pages in post-processing
    const totalPages = pdf.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
        pdf.setPage(i);
        pdf.setFont("Helvetica", "normal");
        pdf.setFontSize(8);
        pdf.setTextColor(148, 163, 184);
        pdf.text(`Page ${i} of ${totalPages}`, 812, 575, { align: "right" });
    }

    // Save the generated document
    const reportFilename = reportTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_');
    pdf.save(`yawatim_${reportFilename}_${Date.now()}.pdf`);
}

function exportTableToCSV(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csvContent = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        // Only get rows that are currently visible (supports filtered tables)
        if (row.style.display === 'none') return;

        const rowData = [];
        const cols = row.querySelectorAll('th, td');

        cols.forEach((col, idx) => {
            // Ignore action columns
            if (col.classList.contains('actions-td') || col.querySelector('.action-group')) {
                return;
            }

            let data = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
            // Wrap in quotes
            rowData.push('"' + data + '"');
        });

        if (rowData.length > 0) {
            csvContent.push(rowData.join(','));
        }
    });

    if (csvContent.length === 0) return;

    const csvString = csvContent.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, 'yawatim_export.csv');
    } else {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `yawatim_report_${Date.now()}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}




// 7. Setup Analytical Charts
function renderDashboardCharts(chartData) {
    if (!chartData) return;

    // A. Chart: Donations by State
    const ctxState = document.getElementById('chartDonationsByState');
    if (ctxState && chartData.byState) {
        new Chart(ctxState, {
            type: 'bar',
            data: {
                labels: chartData.byState.labels,
                datasets: [{
                    label: 'Donations (RM)',
                    data: chartData.byState.data,
                    backgroundColor: '#2563eb', // Light Blue
                    borderColor: '#1e3a8a', // Dark Blue
                    borderWidth: 1.5,
                    borderRadius: 6,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'RM ' + context.raw.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) { return 'RM' + value; }
                        }
                    }
                }
            }
        });
    }

    // B. Chart: Donations by Collection Channel
    const ctxChannel = document.getElementById('chartDonationsByChannel');
    if (ctxChannel && chartData.byChannel) {
        new Chart(ctxChannel, {
            type: 'doughnut',
            data: {
                labels: chartData.byChannel.labels,
                datasets: [{
                    data: chartData.byChannel.data,
                    backgroundColor: [
                        '#f59e0b', // Amber BSN
                        '#7c3aed', // Purple Bank Rakyat
                        '#dc2626', // Red Pos Malaysia
                        '#0284c7'  // Sky EBB
                    ],
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const val = context.raw;
                                const label = context.label;
                                return ' ' + label + ': RM ' + val.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // C. Chart: Monthly Donation Trends
    const ctxTrends = document.getElementById('chartMonthlyTrends');
    if (ctxTrends && chartData.trends) {
        new Chart(ctxTrends, {
            type: 'line',
            data: {
                labels: chartData.trends.labels,
                datasets: [{
                    label: 'Donations Collected (RM)',
                    data: chartData.trends.data,
                    borderColor: '#10b981', // Green metric accent
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'RM ' + context.raw.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) { return 'RM' + value; }
                        }
                    }
                }
            }
        });
    }

    // D. Chart: Corporate Partner Donations by State
    const ctxCorpStates = document.getElementById('chartCorporateStates');
    if (ctxCorpStates && chartData.corpStates) {
        new Chart(ctxCorpStates, {
            type: 'doughnut',
            data: {
                labels: chartData.corpStates.labels,
                datasets: [{
                    data: chartData.corpStates.data,
                    backgroundColor: [
                        '#2563eb', // Blue
                        '#10b981', // Green
                        '#f59e0b', // Amber
                        '#7c3aed', // Purple
                        '#dc2626', // Red
                        '#0284c7', // Sky
                        '#ec4899', // Pink
                        '#14b8a6', // Teal
                        '#8b5cf6', // Indigo
                        '#f97316'  // Orange
                    ],
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const val = context.raw;
                                const label = context.label;
                                return ' ' + label + ': RM ' + val.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
}
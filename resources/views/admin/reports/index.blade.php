@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports Dashboard')

@section('admin-content')
<style>
.report-card {
    border-radius: 16px;
    border: none;
    transition: all 0.3s ease;
    height: 100%;
}
.report-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.report-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 15px 20px;
}
.report-card .card-header h6 {
    font-weight: 600;
    margin: 0;
}
.report-card .card-body {
    padding: 20px;
}

.filter-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
}

.stats-summary-card {
    border-radius: 12px;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.stats-summary-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79,70,229,0.08);
}
.stats-summary-card .number {
    font-size: 1.8rem;
    font-weight: 700;
}
.stats-summary-card .label {
    font-size: 0.8rem;
    color: #64748b;
}
</style>

<!-- ============================================ -->
<!-- FILTER SECTION -->
<!-- ============================================ -->
<div class="filter-section">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Report Type</label>
            <select id="reportType" class="form-select form-select-sm">
                <option value="inventory">Inventory</option>
                <option value="assignments">Assignments</option>
                <option value="requests">Asset Requests</option>
                <option value="maintenance">Maintenance</option>
                <option value="purchase_orders">Purchase Orders</option>
                <option value="procurements">Procurements</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100" id="applyFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- STATS SUMMARY -->
<!-- ============================================ -->
<div class="row g-3 mb-4" id="statsSummary">
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalRecords">0</div>
            <div class="label">Total Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="activeRecords">0</div>
            <div class="label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="pendingRecords">0</div>
            <div class="label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="cancelledRecords">0</div>
            <div class="label">Cancelled/Rejected</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS SECTION -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card report-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-chart-bar text-primary me-2"></i>Trend Analysis</h6>
                <span class="badge bg-light text-muted" id="trendPeriod">Last 12 Months</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card report-card">
            <div class="card-header">
                <h6><i class="fas fa-chart-pie text-success me-2"></i>Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DATA TABLE -->
<!-- ============================================ -->
<div class="card report-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="fas fa-table text-primary me-2"></i>Report Data</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="exportExcel">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </button>
            <button class="btn btn-secondary btn-sm" id="exportCSV">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-info btn-sm" id="printReport">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
            <table class="table table-hover table-striped mb-0" id="reportTable">
                <thead class="table-light sticky-top">
                    <tr id="tableHeader">
                        <th>#</th>
                        <th>Name/Title</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                            <p class="text-muted">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TOAST CONTAINER -->
<!-- ============================================ -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- SheetJS (XLSX) for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    // ============================================
    // CHART INSTANCES
    // ============================================
    let trendChartInstance = null;
    let statusChartInstance = null;
    let currentData = [];

    // ============================================
    // LOAD REPORT DATA
    // ============================================
    function loadReportData() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const reportType = $('#reportType').val();
        const status = $('#statusFilter').val();

        // Show loading state
        $('#tableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        // Build query parameters
        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            type: reportType,
            status: status
        });

        // Fetch data from server
        $.ajax({
            url: '{{ route("admin.reports.export") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    renderReportData(response);
                    updateStats(response);
                    updateCharts(response);
                } else {
                    showToast('error', 'No data found for the selected filters.');
                }
            },
            error: function(xhr) {
                console.error('Error loading report data:', xhr);
                showToast('error', 'Failed to load report data. Please try again.');
                $('#tableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <p>Failed to load data. Please try again.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ============================================
    // RENDER REPORT DATA
    // ============================================
    function renderReportData(response) {
        const data = response.data || [];
        const headers = response.headers || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No data found for the selected filters.</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.forEach(function(row, index) {
            // Determine status badge color
            const status = row[2] || '';
            let badgeClass = 'bg-secondary';
            if (status.toLowerCase().includes('active') || status.toLowerCase().includes('available')) {
                badgeClass = 'bg-success';
            } else if (status.toLowerCase().includes('pending')) {
                badgeClass = 'bg-warning text-dark';
            } else if (status.toLowerCase().includes('cancelled') || status.toLowerCase().includes('rejected')) {
                badgeClass = 'bg-danger';
            } else if (status.toLowerCase().includes('completed') || status.toLowerCase().includes('approved')) {
                badgeClass = 'bg-primary';
            }

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${row[1] || 'N/A'}</strong></td>
                    <td><span class="badge ${badgeClass}">${status || 'N/A'}</span></td>
                    <td>${row[3] || 'N/A'}</td>
                    <td>${row[4] || 'N/A'}</td>
                    <td>${row[5] || '0.00'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-row" data-index="${index}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        body.html(html);
    }

    // ============================================
    // UPDATE STATS
    // ============================================
    function updateStats(response) {
        const data = response.data || [];
        const stats = response.stats || {};

        $('#totalRecords').text(stats.total || data.length);
        $('#activeRecords').text(stats.active || 0);
        $('#pendingRecords').text(stats.pending || 0);
        $('#cancelledRecords').text(stats.cancelled || 0);
    }

    // ============================================
    // UPDATE CHARTS
    // ============================================
    function updateCharts(response) {
        const data = response.data || [];
        const chartData = response.chart_data || {};

        // Update Trend Chart
        updateTrendChart(chartData.trend || getDefaultTrendData(data));
        
        // Update Status Chart
        updateStatusChart(chartData.status || getDefaultStatusData(data));
    }

    function getDefaultTrendData(data) {
        // Group by month
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const counts = new Array(12).fill(0);
        
        data.forEach(function(row) {
            const date = row[4];
            if (date) {
                const month = new Date(date).getMonth();
                counts[month]++;
            }
        });

        return {
            labels: months,
            values: counts
        };
    }

    function getDefaultStatusData(data) {
        const statusMap = {};
        data.forEach(function(row) {
            const status = row[2] || 'Unknown';
            statusMap[status] = (statusMap[status] || 0) + 1;
        });

        return {
            labels: Object.keys(statusMap),
            values: Object.values(statusMap)
        };
    }

    function updateTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        
        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        trendChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: 'Records',
                    data: data.values || [],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    function updateStatusChart(data) {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        const colors = ['#4f46e5', '#22c55e', '#f59e0b', '#ef4444', '#6b7280', '#8b5cf6', '#ec4899', '#14b8a6'];

        statusChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels || [],
                datasets: [{
                    data: data.values || [],
                    backgroundColor: colors.slice(0, (data.labels || []).length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // ============================================
    // EXPORT EXCEL
    // ============================================
    function exportToExcel() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            // Prepare data for Excel
            const excelData = currentData.map(function(row, index) {
                return {
                    '#': index + 1,
                    'Name/Title': row[1] || 'N/A',
                    'Status': row[2] || 'N/A',
                    'Category': row[3] || 'N/A',
                    'Date': row[4] || 'N/A',
                    'Amount': row[5] || '0.00'
                };
            });

            // Create workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);

            // Set column widths
            ws['!cols'] = [
                { wch: 5 },
                { wch: 30 },
                { wch: 15 },
                { wch: 20 },
                { wch: 15 },
                { wch: 15 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Report');
            
            // Generate Excel file
            const fileName = `report_${$('#reportType').val()}_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, fileName);
            
            showToast('success', `Report exported as ${fileName}`);
        } catch (error) {
            console.error('Export error:', error);
            showToast('error', 'Failed to export Excel file.');
        }
    }

    // ============================================
    // EXPORT CSV
    // ============================================
    function exportToCSV() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            // Prepare CSV data
            const headers = ['#', 'Name/Title', 'Status', 'Category', 'Date', 'Amount'];
            const rows = currentData.map(function(row, index) {
                return [
                    index + 1,
                    row[1] || 'N/A',
                    row[2] || 'N/A',
                    row[3] || 'N/A',
                    row[4] || 'N/A',
                    row[5] || '0.00'
                ];
            });

            // Build CSV content
            let csv = headers.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.join(',') + '\n';
            });

            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `report_${$('#reportType').val()}_${new Date().toISOString().slice(0,10)}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            showToast('success', 'CSV file exported successfully!');
        } catch (error) {
            console.error('Export error:', error);
            showToast('error', 'Failed to export CSV file.');
        }
    }

    // ============================================
    // PRINT REPORT
    // ============================================
    function printReport() {
        const printContents = document.querySelector('.card:last-child').innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = `
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                th { background: #f5f5f5; font-weight: bold; }
                .badge { padding: 3px 8px; border-radius: 4px; color: #fff; }
                .bg-success { background: #22c55e; }
                .bg-warning { background: #f59e0b; color: #333; }
                .bg-danger { background: #ef4444; }
                .bg-primary { background: #4f46e5; }
                .bg-secondary { background: #6b7280; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h2 { margin: 0; color: #1e293b; }
                .header p { color: #64748b; margin: 5px 0; }
                .date-range { text-align: center; color: #64748b; margin-bottom: 20px; }
            </style>
            <div class="header">
                <h2>Report: ${$('#reportType').val().replace('_', ' ').toUpperCase()}</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <div class="date-range">
                Period: ${$('#dateFrom').val()} to ${$('#dateTo').val()}
            </div>
            ${printContents}
        `;

        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    function showToast(type, message) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        const colors = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };
        
        const toast = $(`
            <div class="toast show" role="alert" style="min-width:300px;">
                <div class="toast-header bg-${colors[type]} text-white">
                    <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
        $('#toastContainer').append(toast);
        setTimeout(function() { toast.remove(); }, 5000);
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    // Apply Filters
    $('#applyFilters').on('click', function() {
        loadReportData();
    });

    // Enter key on date inputs
    $('#dateFrom, #dateTo').on('keypress', function(e) {
        if (e.which === 13) {
            loadReportData();
        }
    });

    // Report Type Change
    $('#reportType').on('change', function() {
        // Update status filter options based on report type
        const type = $(this).val();
        const statusFilter = $('#statusFilter');
        statusFilter.empty();
        
        const statuses = {
            'inventory': ['All Status', 'available', 'assigned', 'under_repair', 'retired', 'expired'],
            'assignments': ['All Status', 'active', 'returned', 'overdue'],
            'requests': ['All Status', 'pending', 'approved', 'rejected', 'completed'],
            'maintenance': ['All Status', 'reported', 'in_progress', 'fixed', 'unrepairable'],
            'purchase_orders': ['All Status', 'draft', 'ordered', 'received', 'cancelled'],
            'procurements': ['All Status', 'pending', 'ordered', 'delivered', 'returned']
        };

        const options = statuses[type] || ['All Status'];
        options.forEach(function(status) {
            const value = status === 'All Status' ? '' : status;
            statusFilter.append(`<option value="${value}">${status}</option>`);
        });
    });

    // Export Excel
    $('#exportExcel').on('click', exportToExcel);

    // Export CSV
    $('#exportCSV').on('click', exportToCSV);

    // Print Report
    $('#printReport').on('click', printReport);

    // View Row Details
    $(document).on('click', '.view-row', function() {
        const index = $(this).data('index');
        const row = currentData[index];
        if (row) {
            showToast('info', `Viewing: ${row[1] || 'Item'} - ${row[2] || 'Status'}`);
        }
    });

    // ============================================
    // INITIAL LOAD
    // ============================================
    loadReportData();

    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    $(document).on('keydown', function(e) {
        // Ctrl+E: Export Excel
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportToExcel();
        }
        // Ctrl+P: Print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printReport();
        }
        // Ctrl+R: Refresh
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            loadReportData();
        }
    });

    console.log('Reports page loaded successfully!');
    console.log('Keyboard shortcuts: Ctrl+E (Export), Ctrl+P (Print), Ctrl+R (Refresh)');
});
</script>
@endpush
@endsection
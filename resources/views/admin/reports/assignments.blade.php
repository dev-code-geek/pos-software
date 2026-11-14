@extends('layouts.admin')

@section('title', 'Assignment Report')
@section('page-title', 'Assignment Report')

@section('admin-content')
<style>
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
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-badge-active { background: #dcfce7; color: #166534; }
.status-badge-returned { background: #e2e8f0; color: #475569; }
.status-badge-overdue { background: #fee2e2; color: #991b1b; }
.status-badge-extended { background: #dbeafe; color: #1e40af; }
</style>

<!-- ============================================ -->
<!-- FILTER SECTION -->
<!-- ============================================ -->
<div class="filter-section">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="returned">Returned</option>
                <option value="overdue">Overdue</option>
                <option value="extended">Extended</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">User</label>
            <select id="userFilter" class="form-select form-select-sm">
                <option value="">All Users</option>
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Condition</label>
            <select id="conditionFilter" class="form-select form-select-sm">
                <option value="">All Conditions</option>
                <option value="new">New</option>
                <option value="good">Good</option>
                <option value="fair">Fair</option>
                <option value="poor">Poor</option>
                <option value="damaged">Damaged</option>
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
            <div class="number text-primary" id="totalAssignments">0</div>
            <div class="label">Total Assignments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="activeAssignments">0</div>
            <div class="label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="returnedAssignments">0</div>
            <div class="label">Returned</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="overdueAssignments">0</div>
            <div class="label">Overdue</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS SECTION -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Assignment Trends</h6>
                <span class="badge bg-light text-muted" id="trendPeriod">Monthly</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:250px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-pie text-success me-2"></i>Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:230px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MAIN CARD -->
<!-- ============================================ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-handshake text-primary me-2"></i>Assignment Report Data</h6>
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
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="reportTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asset</th>
                        <th>Assigned To</th>
                        <th>Assigned By</th>
                        <th>Assigned Date</th>
                        <th>Expected Return</th>
                        <th>Actual Return</th>
                        <th>Condition Out</th>
                        <th>Condition In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                            <p class="text-muted">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="recordCount">Showing 0 records</span>
            <div>
                <button class="btn btn-sm btn-outline-secondary" id="prevPage" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="mx-2" id="pageInfo">Page 1 of 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="nextPage" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TOAST CONTAINER -->
<!-- ============================================ -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
$(document).ready(function() {
    let currentData = [];
    let currentPage = 1;
    const itemsPerPage = 15;
    let trendChartInstance = null;
    let statusChartInstance = null;

    // ============================================
    // LOAD REPORT DATA
    // ============================================
    function loadReportData() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const status = $('#statusFilter').val();
        const userId = $('#userFilter').val();
        const condition = $('#conditionFilter').val();

        $('#tableBody').html(`
            <tr>
                <td colspan="10" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            status: status,
            user_id: userId,
            condition: condition
        });

        $.ajax({
            url: '{{ route("admin.reports.assignments") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    currentPage = 1;
                    renderTable();
                    updateStats(response);
                    updateCharts(response);
                    updatePagination();
                } else {
                    showToast('error', 'No data found for the selected filters.');
                    $('#tableBody').html(`
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No assignment data found.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                showToast('error', 'Failed to load report data.');
                $('#tableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <p>Failed to load data. Please try again.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ============================================
    // RENDER TABLE WITH PAGINATION
    // ============================================
    function renderTable() {
        const body = $('#tableBody');
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageData = currentData.slice(start, end);

        if (pageData.length === 0) {
            body.html(`
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No data available.</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        pageData.forEach(function(item, index) {
            const statusClass = item.status === 'active' ? 'active' : 
                              (item.status === 'returned' ? 'returned' : 
                              (item.status === 'overdue' ? 'overdue' : ''));
            
            const statusBadge = item.status === 'active' ? 'Active' :
                               (item.status === 'returned' ? 'Returned' :
                               (item.status === 'overdue' ? 'Overdue' : 'Extended'));

            html += `
                <tr>
                    <td>${start + index + 1}</td>
                    <td><strong>${item.asset}</strong><br><small class="text-muted">${item.sku || ''}</small></td>
                    <td>${item.user}</td>
                    <td>${item.assigned_by || 'System'}</td>
                    <td>${item.assigned_date}</td>
                    <td>${item.expected_return || 'N/A'}</td>
                    <td>${item.actual_return || 'N/A'}</td>
                    <td><span class="badge bg-${item.condition_out == 'new' ? 'success' : (item.condition_out == 'good' ? 'info' : (item.condition_out == 'fair' ? 'warning' : 'danger'))}">${item.condition_out || 'N/A'}</span></td>
                    <td>${item.condition_in ? `<span class="badge bg-${item.condition_in == 'good' ? 'success' : (item.condition_in == 'fair' ? 'warning' : 'danger')}">${item.condition_in}</span>` : 'N/A'}</td>
                    <td>
                        <span class="status-badge status-badge-${statusClass}">
                            ${statusBadge}
                        </span>
                    </td>
                </tr>
            `;
        });

        body.html(html);
        $('#recordCount').text(`Showing ${pageData.length} of ${currentData.length} records`);
    }

    // ============================================
    // UPDATE STATS
    // ============================================
    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalAssignments').text(stats.total || 0);
        $('#activeAssignments').text(stats.active || 0);
        $('#returnedAssignments').text(stats.returned || 0);
        $('#overdueAssignments').text(stats.overdue || 0);
    }

    // ============================================
    // UPDATE CHARTS
    // ============================================
    function updateCharts(response) {
        const chartData = response.chart_data || {};

        // Trend Chart
        updateTrendChart(chartData.trend || getDefaultTrendData());

        // Status Chart
        updateStatusChart(chartData.status || getDefaultStatusData());
    }

    function getDefaultTrendData() {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const counts = new Array(12).fill(0);
        
        currentData.forEach(function(item) {
            const date = item.assigned_date;
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

    function getDefaultStatusData() {
        const statusMap = {};
        currentData.forEach(function(item) {
            const status = item.status || 'Unknown';
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
                    label: 'Assignments',
                    data: data.values || [],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4,
                    pointHoverRadius: 6
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

        const colors = ['#22c55e', '#3b82f6', '#ef4444', '#f59e0b', '#8b5cf6'];

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
    // PAGINATION
    // ============================================
    function updatePagination() {
        const totalPages = Math.ceil(currentData.length / itemsPerPage);
        $('#pageInfo').text(`Page ${currentPage} of ${totalPages || 1}`);
        $('#prevPage').prop('disabled', currentPage <= 1);
        $('#nextPage').prop('disabled', currentPage >= totalPages);
    }

    $('#prevPage').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
            updatePagination();
        }
    });

    $('#nextPage').on('click', function() {
        const totalPages = Math.ceil(currentData.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
            updatePagination();
        }
    });

    // ============================================
    // EXPORT EXCEL
    // ============================================
    function exportToExcel() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Asset': item.asset,
                    'SKU': item.sku || 'N/A',
                    'Assigned To': item.user,
                    'Assigned By': item.assigned_by || 'System',
                    'Assigned Date': item.assigned_date,
                    'Expected Return': item.expected_return || 'N/A',
                    'Actual Return': item.actual_return || 'N/A',
                    'Condition Out': item.condition_out || 'N/A',
                    'Condition In': item.condition_in || 'N/A',
                    'Status': item.status || 'N/A'
                };
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [
                { wch: 5 }, { wch: 25 }, { wch: 15 }, { wch: 20 },
                { wch: 20 }, { wch: 15 }, { wch: 15 }, { wch: 15 },
                { wch: 15 }, { wch: 15 }, { wch: 12 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Assignment Report');
            const fileName = `assignment_report_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, fileName);
            showToast('success', `Report exported as ${fileName}`);
        } catch (error) {
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
            const headers = ['#', 'Asset', 'SKU', 'Assigned To', 'Assigned By', 
                           'Assigned Date', 'Expected Return', 'Actual Return', 
                           'Condition Out', 'Condition In', 'Status'];
            
            const rows = currentData.map(function(item, index) {
                return [
                    index + 1,
                    item.asset,
                    item.sku || 'N/A',
                    item.user,
                    item.assigned_by || 'System',
                    item.assigned_date,
                    item.expected_return || 'N/A',
                    item.actual_return || 'N/A',
                    item.condition_out || 'N/A',
                    item.condition_in || 'N/A',
                    item.status || 'N/A'
                ];
            });

            let csv = headers.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.setAttribute('href', URL.createObjectURL(blob));
            link.setAttribute('download', `assignment_report_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('success', 'CSV file exported successfully!');
        } catch (error) {
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
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                th, td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
                th { background: #f5f5f5; font-weight: bold; }
                .badge { padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 10px; }
                .bg-success { background: #22c55e; }
                .bg-warning { background: #f59e0b; color: #333; }
                .bg-danger { background: #ef4444; }
                .bg-primary { background: #4f46e5; }
                .bg-info { background: #3b82f6; }
                .bg-secondary { background: #6b7280; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h2 { margin: 0; color: #1e293b; }
                .header p { color: #64748b; margin: 5px 0; }
                .date-range { text-align: center; color: #64748b; margin-bottom: 20px; }
                .stats-summary { display: flex; justify-content: space-around; margin-bottom: 20px; flex-wrap: wrap; }
                .stat-item { text-align: center; margin: 5px 15px; }
                .stat-item .number { font-size: 1.3rem; font-weight: 700; }
                .stat-item .label { color: #64748b; font-size: 0.8rem; }
                .status-badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
                .status-badge-active { background: #dcfce7; color: #166534; }
                .status-badge-returned { background: #e2e8f0; color: #475569; }
                .status-badge-overdue { background: #fee2e2; color: #991b1b; }
            </style>
            <div class="header">
                <h2>Assignment Report</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <div class="date-range">
                Period: ${$('#dateFrom').val()} to ${$('#dateTo').val()}
            </div>
            <div class="stats-summary">
                <div class="stat-item"><div class="number text-primary">${$('#totalAssignments').text()}</div><div class="label">Total</div></div>
                <div class="stat-item"><div class="number text-success">${$('#activeAssignments').text()}</div><div class="label">Active</div></div>
                <div class="stat-item"><div class="number text-warning">${$('#returnedAssignments').text()}</div><div class="label">Returned</div></div>
                <div class="stat-item"><div class="number text-danger">${$('#overdueAssignments').text()}</div><div class="label">Overdue</div></div>
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
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        const colors = { success: 'success', error: 'danger', warning: 'warning', info: 'info' };
        
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
    // KEYBOARD SHORTCUTS
    // ============================================
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportToExcel();
        }
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printReport();
        }
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            loadReportData();
        }
    });

    // ============================================
    // EVENT LISTENERS
    // ============================================
    $('#applyFilters').on('click', loadReportData);
    $('#dateFrom, #dateTo').on('keypress', function(e) {
        if (e.which === 13) loadReportData();
    });
    $('#exportExcel').on('click', exportToExcel);
    $('#exportCSV').on('click', exportToCSV);
    $('#printReport').on('click', printReport);

    // Quick filter buttons
    $('.quick-filter').on('click', function() {
        const status = $(this).data('status');
        $('#statusFilter').val(status);
        loadReportData();
    });

    // ============================================
    // INITIAL LOAD
    // ============================================
    loadReportData();

    console.log('Assignment Report loaded successfully!');
    console.log('Keyboard shortcuts: Ctrl+E (Export), Ctrl+P (Print), Ctrl+R (Refresh)');
});
</script>
@endpush
@endsection
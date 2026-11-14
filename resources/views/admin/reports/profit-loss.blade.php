@extends('layouts.admin')

@section('title', 'Profit & Loss Report')
@section('page-title', 'Profit & Loss Report')

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
.profit-positive { color: #22c55e; }
.profit-negative { color: #ef4444; }
.pnl-table th { background: #f8fafc; font-weight: 600; }
.pnl-table .section-header { background: #f1f5f9; font-weight: 700; }
</style>

<!-- FILTER SECTION -->
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
        <div class="col-md-3">
            <label class="form-label fw-bold small">Currency</label>
            <select id="currencyFilter" class="form-select form-select-sm">
                <option value="">All Currencies</option>
                @foreach($currencies ?? [] as $currency)
                    <option value="{{ $currency->id }}" {{ $currencyId == $currency->id ? 'selected' : '' }}>
                        {{ $currency->code }} ({{ $currency->symbol }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-sm w-100" id="applyFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- STATS SUMMARY - 5 Cards -->
<div class="row g-3 mb-4" id="statsSummary">
    <div class="col-md-2">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="totalSales">0</div>
            <div class="label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="totalSalesPayments">0</div>
            <div class="label">Sales Payments</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="totalPurchases">0</div>
            <div class="label">Total Purchases</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="totalPurchasePayments">0</div>
            <div class="label">Purchase Payments</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-summary-card text-center">
            <div class="number" id="netProfit">0</div>
            <div class="label">Net Profit / Loss</div>
        </div>
    </div>
</div>

<!-- PROFIT & LOSS TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Profit & Loss Statement</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="exportExcel"><i class="fas fa-file-excel me-1"></i> Excel</button>
            <button class="btn btn-secondary btn-sm" id="exportCSV"><i class="fas fa-file-csv me-1"></i> CSV</button>
            <button class="btn btn-info btn-sm" id="printReport"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 pnl-table">
                <tbody id="pnlBody">
                    <tr><td colspan="2" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MONTHLY TREND CHART -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Monthly Trend</h6>
    </div>
    <div class="card-body">
        <div style="height: 300px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let currentData = {};
    let trendChartInstance = null;

    function loadReportData() {
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            currency_id: $('#currencyFilter').val()
        });

        $('#pnlBody').html(`
            <tr>
                <td colspan="2" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("admin.reports.api.profit-loss") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                if (response && response.success) {
                    currentData = response;
                    renderPNL(response);
                    updateStats(response);
                    updateCharts(response);
                } else {
                    showToast('error', 'No data found for the selected filters.');
                    $('#pnlBody').html(`
                        <tr>
                            <td colspan="2" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <p>No data found for the selected period.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Error loading data:', xhr);
                var errorMsg = 'Failed to load report data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showToast('error', errorMsg);
                $('#pnlBody').html(`
                    <tr>
                        <td colspan="2" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <p>${errorMsg}</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderPNL(response) {
        const summary = response.summary || {};
        const sales = summary.sales || {};
        const salesPayments = summary.sales_payments || {};
        const purchases = summary.purchases || {};
        const purchasePayments = summary.purchase_payments || {};
        const expenses = summary.operating_expenses || {};
        const pnl = summary.profit_loss || {};
        const symbol = summary.currency_symbol || 'TSh';

        const formatCurrency = (value) => {
            return symbol + ' ' + parseFloat(value || 0).toFixed(2);
        };

        const getColor = (value) => {
            return value >= 0 ? 'profit-positive' : 'profit-negative';
        };

        let html = `
            <tr class="section-header">
                <td colspan="2" class="fw-bold">INCOME / REVENUE</td>
            </tr>
            <tr>
                <td>Total Sales</td>
                <td class="text-end">${formatCurrency(sales.total_sales)}</td>
            </tr>
            <tr>
                <td>Less: Sales Discounts</td>
                <td class="text-end text-danger">-${formatCurrency(sales.discounts)}</td>
            </tr>
            <tr>
                <td>Less: Sales Tax</td>
                <td class="text-end text-danger">-${formatCurrency(sales.tax)}</td>
            </tr>
            <tr>
                <td>Less: Shipping Cost</td>
                <td class="text-end text-danger">-${formatCurrency(sales.shipping)}</td>
            </tr>
            <tr class="fw-bold">
                <td>Net Sales</td>
                <td class="text-end text-success">${formatCurrency(sales.net_sales)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">SALES PAYMENTS RECEIVED</td>
            </tr>
            <tr>
                <td>Total Sales Payments Received</td>
                <td class="text-end text-success">${formatCurrency(salesPayments.total)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">COST OF GOODS SOLD</td>
            </tr>
            <tr>
                <td>Total Purchases</td>
                <td class="text-end text-danger">-${formatCurrency(purchases.total_purchases)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">PURCHASE PAYMENTS MADE</td>
            </tr>
            <tr>
                <td>Total Purchase Payments Made</td>
                <td class="text-end text-danger">-${formatCurrency(purchasePayments.total)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">GROSS PROFIT</td>
            </tr>
            <tr class="fw-bold">
                <td>Gross Profit</td>
                <td class="text-end ${getColor(pnl.gross_profit)}">${formatCurrency(pnl.gross_profit)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">OPERATING EXPENSES</td>
            </tr>
            <tr>
                <td>Payroll</td>
                <td class="text-end text-danger">-${formatCurrency(expenses.payroll)}</td>
            </tr>
            <tr>
                <td>Tax Paid</td>
                <td class="text-end text-danger">-${formatCurrency(expenses.tax_paid)}</td>
            </tr>
            <tr>
                <td>NSSF</td>
                <td class="text-end text-danger">-${formatCurrency(expenses.nssf)}</td>
            </tr>
            <tr class="fw-bold">
                <td>Total Operating Expenses</td>
                <td class="text-end text-danger">-${formatCurrency(expenses.total)}</td>
            </tr>
            
            <tr class="section-header">
                <td colspan="2" class="fw-bold">NET PROFIT / LOSS</td>
            </tr>
            <tr class="fw-bold">
                <td>Net Profit / Loss</td>
                <td class="text-end ${getColor(pnl.net_profit)}" style="font-size:1.2rem;">${formatCurrency(pnl.net_profit)}</td>
            </tr>
            <tr>
                <td>Profit Margin</td>
                <td class="text-end ${getColor(pnl.profit_margin)}">${parseFloat(pnl.profit_margin || 0).toFixed(2)}%</td>
            </tr>
        `;

        $('#pnlBody').html(html);
    }

    function updateStats(response) {
        const summary = response.summary || {};
        const sales = summary.sales || {};
        const salesPayments = summary.sales_payments || {};
        const purchases = summary.purchases || {};
        const purchasePayments = summary.purchase_payments || {};
        const pnl = summary.profit_loss || {};
        const symbol = summary.currency_symbol || 'TSh';

        $('#totalSales').text(symbol + ' ' + (sales.total_sales || 0).toFixed(2));
        $('#totalSalesPayments').text(symbol + ' ' + (salesPayments.total || 0).toFixed(2));
        $('#totalPurchases').text(symbol + ' ' + (purchases.total_purchases || 0).toFixed(2));
        $('#totalPurchasePayments').text(symbol + ' ' + (purchasePayments.total || 0).toFixed(2));
        
        const netProfit = pnl.net_profit || 0;
        const netProfitDisplay = $('#netProfit');
        netProfitDisplay.text(symbol + ' ' + netProfit.toFixed(2));
        netProfitDisplay.removeClass('profit-positive profit-negative');
        if (netProfit > 0) {
            netProfitDisplay.addClass('profit-positive');
        } else if (netProfit < 0) {
            netProfitDisplay.addClass('profit-negative');
        }
    }

    function updateCharts(response) {
        const monthlyData = response.monthly_data || [];
        updateTrendChart(monthlyData);
    }

    function updateTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');

        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        const labels = data.map(item => item.month || '');
        const sales = data.map(item => parseFloat(item.sales || 0));
        const salesPayments = data.map(item => parseFloat(item.sales_payments || 0));
        const purchases = data.map(item => parseFloat(item.purchases || 0));
        const purchasePayments = data.map(item => parseFloat(item.purchase_payments || 0));
        const profit = data.map(item => parseFloat(item.profit || 0));

        trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales',
                        data: sales,
                        backgroundColor: 'rgba(34,197,94,0.6)',
                        borderColor: '#22c55e',
                        borderWidth: 1,
                    },
                    {
                        label: 'Sales Payments',
                        data: salesPayments,
                        backgroundColor: 'rgba(59,130,246,0.6)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                    },
                    {
                        label: 'Purchases',
                        data: purchases,
                        backgroundColor: 'rgba(239,68,68,0.6)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                    },
                    {
                        label: 'Purchase Payments',
                        data: purchasePayments,
                        backgroundColor: 'rgba(245,158,11,0.6)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                    },
                    {
                        label: 'Profit',
                        data: profit,
                        backgroundColor: 'rgba(79,70,229,0.6)',
                        borderColor: '#4f46e5',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TSh ' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    }

    // ============================================
    // EXPORT FUNCTIONS
    // ============================================
    
    function exportToExcel() {
        if (!currentData.summary) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const summary = currentData.summary || {};
            const sales = summary.sales || {};
            const salesPayments = summary.sales_payments || {};
            const purchases = summary.purchases || {};
            const purchasePayments = summary.purchase_payments || {};
            const expenses = summary.operating_expenses || {};
            const pnl = summary.profit_loss || {};
            const symbol = summary.currency_symbol || 'TSh';

            const formatCurrency = (value) => {
                return symbol + ' ' + parseFloat(value || 0).toFixed(2);
            };

            const excelData = [
                {'Description': 'PROFIT & LOSS STATEMENT', 'Amount': ''},
                {'Description': 'Period', 'Amount': $('#dateFrom').val() + ' to ' + $('#dateTo').val()},
                {'Description': 'Generated', 'Amount': new Date().toLocaleString()},
                {'Description': '', 'Amount': ''},
                {'Description': 'INCOME / REVENUE', 'Amount': ''},
                {'Description': 'Total Sales', 'Amount': formatCurrency(sales.total_sales)},
                {'Description': 'Less: Sales Discounts', 'Amount': '-' + formatCurrency(sales.discounts)},
                {'Description': 'Less: Sales Tax', 'Amount': '-' + formatCurrency(sales.tax)},
                {'Description': 'Less: Shipping Cost', 'Amount': '-' + formatCurrency(sales.shipping)},
                {'Description': 'Net Sales', 'Amount': formatCurrency(sales.net_sales)},
                {'Description': '', 'Amount': ''},
                {'Description': 'SALES PAYMENTS RECEIVED', 'Amount': ''},
                {'Description': 'Total Sales Payments Received', 'Amount': formatCurrency(salesPayments.total)},
                {'Description': '', 'Amount': ''},
                {'Description': 'COST OF GOODS SOLD', 'Amount': ''},
                {'Description': 'Total Purchases', 'Amount': '-' + formatCurrency(purchases.total_purchases)},
                {'Description': '', 'Amount': ''},
                {'Description': 'PURCHASE PAYMENTS MADE', 'Amount': ''},
                {'Description': 'Total Purchase Payments Made', 'Amount': '-' + formatCurrency(purchasePayments.total)},
                {'Description': '', 'Amount': ''},
                {'Description': 'GROSS PROFIT', 'Amount': formatCurrency(pnl.gross_profit)},
                {'Description': '', 'Amount': ''},
                {'Description': 'OPERATING EXPENSES', 'Amount': ''},
                {'Description': 'Payroll', 'Amount': '-' + formatCurrency(expenses.payroll)},
                {'Description': 'Tax Paid', 'Amount': '-' + formatCurrency(expenses.tax_paid)},
                {'Description': 'NSSF', 'Amount': '-' + formatCurrency(expenses.nssf)},
                {'Description': 'Total Operating Expenses', 'Amount': '-' + formatCurrency(expenses.total)},
                {'Description': '', 'Amount': ''},
                {'Description': 'NET PROFIT / LOSS', 'Amount': formatCurrency(pnl.net_profit)},
                {'Description': 'Profit Margin', 'Amount': parseFloat(pnl.profit_margin || 0).toFixed(2) + '%'},
            ];

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [{ wch: 30 }, { wch: 20 }];
            XLSX.utils.book_append_sheet(wb, ws, 'Profit & Loss');
            XLSX.writeFile(wb, `profit_loss_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            console.error('Export error:', error);
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (!currentData.summary) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const summary = currentData.summary || {};
            const sales = summary.sales || {};
            const salesPayments = summary.sales_payments || {};
            const purchases = summary.purchases || {};
            const purchasePayments = summary.purchase_payments || {};
            const expenses = summary.operating_expenses || {};
            const pnl = summary.profit_loss || {};
            const symbol = summary.currency_symbol || 'TSh';

            const formatCurrency = (value) => {
                return symbol + ' ' + parseFloat(value || 0).toFixed(2);
            };

            const rows = [
                ['Profit & Loss Statement'],
                ['Period:', $('#dateFrom').val() + ' to ' + $('#dateTo').val()],
                ['Generated:', new Date().toLocaleString()],
                [],
                ['INCOME / REVENUE', ''],
                ['Total Sales', formatCurrency(sales.total_sales)],
                ['Less: Sales Discounts', '-' + formatCurrency(sales.discounts)],
                ['Less: Sales Tax', '-' + formatCurrency(sales.tax)],
                ['Less: Shipping Cost', '-' + formatCurrency(sales.shipping)],
                ['Net Sales', formatCurrency(sales.net_sales)],
                [],
                ['SALES PAYMENTS RECEIVED', ''],
                ['Total Sales Payments Received', formatCurrency(salesPayments.total)],
                [],
                ['COST OF GOODS SOLD', ''],
                ['Total Purchases', '-' + formatCurrency(purchases.total_purchases)],
                [],
                ['PURCHASE PAYMENTS MADE', ''],
                ['Total Purchase Payments Made', '-' + formatCurrency(purchasePayments.total)],
                [],
                ['GROSS PROFIT', formatCurrency(pnl.gross_profit)],
                [],
                ['OPERATING EXPENSES', ''],
                ['Payroll', '-' + formatCurrency(expenses.payroll)],
                ['Tax Paid', '-' + formatCurrency(expenses.tax_paid)],
                ['NSSF', '-' + formatCurrency(expenses.nssf)],
                ['Total Operating Expenses', '-' + formatCurrency(expenses.total)],
                [],
                ['NET PROFIT / LOSS', formatCurrency(pnl.net_profit)],
                ['Profit Margin', parseFloat(pnl.profit_margin || 0).toFixed(2) + '%']
            ];

            let csv = rows.map(row => row.join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `profit_loss_report_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            showToast('success', 'CSV exported!');
        } catch (error) {
            console.error('Export error:', error);
            showToast('error', 'Failed to export CSV.');
        }
    }

    function printReport() {
        window.print();
    }

    function showToast(type, message) {
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle' };
        const colors = { success: 'success', error: 'danger', warning: 'warning' };
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
        setTimeout(() => toast.remove(), 5000);
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    $('#applyFilters').on('click', loadReportData);
    $('#dateFrom, #dateTo').on('keypress', function(e) { if (e.which === 13) loadReportData(); });

    $('#exportExcel').on('click', exportToExcel);
    $('#exportCSV').on('click', exportToCSV);
    $('#printReport').on('click', printReport);

    // Initial load
    loadReportData();
});
</script>
@endpush
@endsection
@extends('layouts.admin')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@section('admin-content')
<style>
/* ============================================
   DASHBOARD CUSTOM STYLES
============================================ */
:root {
    --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    --success-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.dashboard-stat-card { border-radius: 16px; padding: 20px; position: relative; overflow: hidden; transition: all 0.3s ease; border: none; }
.dashboard-stat-card::before { content: ''; position: absolute; top: -50%; right: -20%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.05); pointer-events: none; }
.dashboard-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); }
.dashboard-stat-card .stat-icon { position: absolute; right: 15px; top: 15px; font-size: 2.5rem; opacity: 0.15; color: #fff; }
.dashboard-stat-card .stat-number { font-size: 2.2rem; font-weight: 700; margin: 0; color: #fff; font-variant-numeric: tabular-nums; }
.dashboard-stat-card .stat-label { font-size: 0.85rem; opacity: 0.85; color: #fff; margin: 0; }
.dashboard-stat-card .stat-sub { font-size: 0.7rem; opacity: 0.75; color: #fff; margin-top: 5px; display: inline-block; }
.dashboard-stat-card .stat-link { display: block; padding-top: 10px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.8rem; transition: all 0.2s ease; }
.dashboard-stat-card .stat-link:hover { color: #fff; }
.dashboard-stat-card .stat-link i { transition: transform 0.2s ease; }
.dashboard-stat-card:hover .stat-link i { transform: translateX(4px); }

.widget-card { border-radius: 16px; border: none; transition: all 0.3s ease; height: 100%; background: #fff; }
.widget-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
.widget-card .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 15px 20px; }
.widget-card .card-header h6 { font-weight: 600; margin: 0; }
.widget-card .card-body { padding: 20px; }

.activity-feed { max-height: 350px; overflow-y: auto; }
.activity-feed::-webkit-scrollbar { width: 4px; }
.activity-feed::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.activity-feed::-webkit-scrollbar-thumb { background: #c1c7cd; border-radius: 4px; }
.activity-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
.activity-item:last-child { border-bottom: none; }
.activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem; }
.activity-content { flex: 1; }
.activity-content .activity-time { font-size: 0.7rem; color: #94a3b8; }
.activity-content .activity-message { font-size: 0.85rem; margin: 2px 0 0; color: #1e293b; }

.quick-action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; }
.quick-action-item { text-decoration: none; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 15px; text-align: center; transition: all 0.3s ease; cursor: pointer; }
.quick-action-item:hover { transform: translateY(-4px); border-color: #4f46e5; box-shadow: 0 8px 25px rgba(79,70,229,0.10); }
.quick-action-item .qa-icon { font-size: 2rem; margin-bottom: 10px; display: block; }
.quick-action-item .qa-title { font-size: 0.8rem; font-weight: 600; color: #1e293b; margin: 0; }

.chart-container { position: relative; height: 280px; }

@media (max-width: 768px) {
    .quick-action-grid { grid-template-columns: repeat(2, 1fr); }
    .dashboard-stat-card .stat-number { font-size: 1.6rem; }
}

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.fade-in-up { animation: fadeInUp 0.6s ease forwards; }
.fade-in-up-delay-1 { animation-delay: 0.1s; } .fade-in-up-delay-2 { animation-delay: 0.2s; } .fade-in-up-delay-3 { animation-delay: 0.3s; } .fade-in-up-delay-4 { animation-delay: 0.4s; }

.card { border-radius: 16px; } .card-header { border-bottom: none; }
</style>

@php
    $totalProducts   = $stats['total_products'] ?? 0;
    $availableCount  = $stats['available_products'] ?? 0;
    $lowStockCount   = $stats['low_stock'] ?? 0;
    $totalSales      = $stats['total_sales'] ?? 0;
    $totalRevenue    = $stats['total_revenue'] ?? 0;
    $todaySales      = $stats['today_sales'] ?? 0;
    $todayRevenue    = $stats['today_revenue'] ?? 0;
    $totalCustomers  = $stats['total_customers'] ?? 0;
    $totalPOs        = $stats['total_purchase_orders'] ?? 0;
@endphp

<!-- ============================================ -->
<!-- TOP STATS ROW -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--primary-gradient);">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-number" id="stat-total-products">{{ number_format($totalProducts) }}</div>
            <div class="stat-label">{{ __('messages.total_products') }}</div>
            <span class="stat-sub">{{ number_format($availableCount) }} {{ __('messages.available') }}</span>
            <a href="{{ route('admin.products.index') }}" class="stat-link">
                {{ __('messages.view_products') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--success-gradient);">
            <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-number">{{ number_format($totalRevenue, 0) }}</div>
            <div class="stat-label">{{ __('messages.total_revenue') }}</div>
            <span class="stat-sub">{{ number_format($totalSales) }} {{ __('messages.sales') }}</span>
            <a href="{{ route('admin.sales.index') }}" class="stat-link">
                {{ __('messages.view_sales') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--warning-gradient);">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-number">{{ number_format($lowStockCount) }}</div>
            <div class="stat-label">{{ __('messages.low_stock_items') }}</div>
            <span class="stat-sub">{{ $stats['out_of_stock'] ?? 0 }} {{ __('messages.out_of_stock') }}</span>
            <a href="{{ route('admin.products.low-stock') }}" class="stat-link">
                {{ __('messages.view_low_stock') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--info-gradient);">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number">{{ number_format($totalCustomers) }}</div>
            <div class="stat-label">{{ __('messages.customers') }}</div>
            <span class="stat-sub">{{ $stats['active_customers'] ?? 0 }} {{ __('messages.active') }}</span>
            <a href="{{ route('admin.customers.index') }}" class="stat-link">
                {{ __('messages.view_customers') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECONDARY STATS ROW -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--purple-gradient);">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-number">{{ number_format($totalPOs) }}</div>
            <div class="stat-label">{{ __('messages.total_pos') }}</div>
            <a href="{{ route('admin.purchase-orders.index') }}" class="stat-link">
                {{ __('messages.view_pos') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: var(--danger-gradient);">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number">{{ number_format($stats['pending_purchase_orders'] ?? 0) }}</div>
            <div class="stat-label">{{ __('messages.pending_orders') }}</div>
            <a href="{{ route('admin.purchase-orders.index', ['status' => 'ordered']) }}" class="stat-link">
                {{ __('messages.view_pending') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number">{{ number_format($stats['paid_sales'] ?? 0) }}</div>
            <div class="stat-label">{{ __('messages.paid_invoices') }}</div>
            <a href="{{ route('admin.sales.index', ['status' => 'paid']) }}" class="stat-link">
                {{ __('messages.view_paid') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number">{{ number_format($stats['pending_sales'] ?? 0) }}</div>
            <div class="stat-label">{{ __('messages.pending_invoices') }}</div>
            <a href="{{ route('admin.sales.index', ['status' => 'pending']) }}" class="stat-link">
                {{ __('messages.view_pending') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number">{{ number_format($stats['total_users'] ?? 0) }}</div>
            <div class="stat-label">{{ __('messages.system_users') }}</div>
            <a href="{{ route('admin.users.index') }}" class="stat-link">
                {{ __('messages.view_users') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="dashboard-stat-card" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
            <div class="stat-icon"><i class="fas fa-truck"></i></div>
            <div class="stat-number">{{ number_format($stats['total_suppliers'] ?? 0) }}</div>
            <div class="stat-label">{{ __('messages.suppliers') }}</div>
            <a href="{{ route('admin.suppliers.index') }}" class="stat-link">
                {{ __('messages.view_suppliers') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TODAY'S REVENUE & TRENDS -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="card widget-card fade-in-up">
            <div class="card-header">
                <h6><i class="fas fa-calendar-day text-primary me-2"></i>{{ __('messages.todays_overview') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="d-block text-muted small">{{ __('messages.sales') }}</span>
                            <span class="fw-bold fs-3">{{ number_format($todaySales) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="d-block text-muted small">{{ __('messages.revenue') }}</span>
                            <span class="fw-bold fs-3 text-success">${{ number_format($todayRevenue, 0) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="d-block text-muted small">{{ __('messages.products_added') }}</span>
                            <span class="fw-bold fs-3">{{ $stats['new_products_today'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="d-block text-muted small">{{ __('messages.new_customers') }}</span>
                            <span class="fw-bold fs-3">{{ $stats['new_customers_today'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-8 col-lg-6">
        <div class="card widget-card fade-in-up">
            <div class="card-header">
                <h6><i class="fas fa-bolt text-primary me-2"></i>{{ __('messages.quick_actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="quick-action-grid">
                    @foreach($quickLinks as $link)
                        @if(auth()->user()->can($link['permission']))
                            @if(isset($link['modal']))
                                {{-- Trigger Modal --}}
                                <a href="javascript:void(0)" class="quick-action-item" data-bs-toggle="modal" data-bs-target="#{{ $link['modal'] }}">
                                    <span class="qa-icon text-{{ $link['color'] }}"><i class="fas {{ $link['icon'] }}"></i></span>
                                    <h6 class="qa-title">{{ $link['title'] }}</h6>
                                </a>
                            @else
                                {{-- Standard Route Link --}}
                                <a href="{{ $link['route'] }}" class="quick-action-item">
                                    <span class="qa-icon text-{{ $link['color'] }}"><i class="fas {{ $link['icon'] }}"></i></span>
                                    <h6 class="qa-title">{{ $link['title'] }}</h6>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHARTS SECTION -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="card widget-card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-chart-pie text-primary me-2"></i>{{ __('messages.product_status') }}</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary">{{ __('messages.total') }}: {{ array_sum($productStatusData ?? []) }}</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="productStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6">
        <div class="card widget-card fade-in-up fade-in-up-delay-1">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-chart-line text-success me-2"></i>{{ __('messages.monthly_sales') }}</h6>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-1 chart-period-btn" data-period="6">6M</button>
                    <button class="btn btn-sm btn-primary chart-period-btn" data-period="12">12M</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6">
        <div class="card widget-card fade-in-up fade-in-up-delay-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-clipboard-list text-warning me-2"></i>{{ __('messages.invoice_status') }}</h6>
                <span class="badge bg-warning bg-opacity-10 text-warning">{{ __('messages.total') }}: {{ array_sum($salesStatusData ?? []) }}</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECOND ROW CHARTS -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card widget-card fade-in-up fade-in-up-delay-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-exchange-alt text-info me-2"></i>{{ __('messages.stock_movement') }}</h6>
                <span class="badge bg-info bg-opacity-10 text-info">{{ __('messages.net') }}: {{ number_format($stats['available_products'] ?? 0) }}</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stockMovementChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6">
        <div class="card widget-card fade-in-up fade-in-up-delay-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-trophy text-warning me-2"></i>{{ __('messages.top_selling_products') }}</h6>
            </div>
            <div class="card-body p-0">
                @if(isset($topProducts) && count($topProducts) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topProducts as $index => $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-{{ ['danger','warning','info','primary','secondary'][$index] ?? 'secondary' }} me-2">
                                        #{{ $index + 1 }}
                                    </span>
                                    {{ $product->name ?? 'Unknown' }}
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $product->total_quantity }} {{ __('messages.units') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-box fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">{{ __('messages.no_sales_data') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RECENT ACTIVITIES -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card widget-card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-history text-info me-2"></i>{{ __('messages.recent_activities') }}</h6>
                <span class="badge bg-info bg-opacity-10 text-info">{{ count($recentActivities) }}</span>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    @forelse($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="activity-icon bg-{{ $activity['color'] }}-subtle text-{{ $activity['color'] }}">
                                <i class="fas {{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <span class="activity-message">{{ $activity['message'] }}</span>
                                    <span class="activity-time">{{ $activity['time'] }}</span>
                                </div>
                                <a href="{{ $activity['link'] ?? '#' }}" class="text-primary small text-decoration-none">
                                    {{ __('messages.view_details') }} <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">{{ __('messages.no_recent_activities') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- QUICK ACTION MODALS (COMPACT SIZE) -->
<!-- ============================================ -->

{{-- 1. Quick Sale Modal --}}
<div class="modal fade" id="quickSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-cash-register me-2"></i> {{ __('messages.new_sale') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label fw-bold small">{{ __('messages.customer') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select form-select-sm" required>
                                <option value="">{{ __('messages.select_customer') }}</option>
                                @foreach($customers ?? [] as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">{{ __('messages.product') }} <span class="text-danger">*</span></label>
                            <select name="items[0][product_id]" class="form-select form-select-sm" required>
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">{{ __('messages.quantity') }} <span class="text-danger">*</span></label>
                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm" value="1" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">{{ __('messages.unit_price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="items[0][unit_price]" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save me-1"></i> {{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Quick Customer Modal --}}
<div class="modal fade" id="quickCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus me-2"></i> {{ __('messages.add_customer') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.customer_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.email') }}</label>
                        <input type="email" name="email" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark"><i class="fas fa-save me-1"></i> {{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. Quick Product Modal --}}
<div class="modal fade" id="quickProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold text-white"><i class="fas fa-box me-2"></i> {{ __('messages.add_product') }}</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('messages.select_category') }}</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.selling_price') }} <span class="text-danger">*</span></label>
                        <input type="number" name="selling_price" class="form-control form-control-sm" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> {{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 4. Quick Purchase Order Modal --}}
<div class="modal fade" id="quickPurchaseOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title fw-bold text-white"><i class="fas fa-shopping-cart me-2"></i> {{ __('messages.new_purchase_order') }}</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchase-orders.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.supplier') }} <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('messages.select_supplier') }}</option>
                            @foreach($suppliers ?? [] as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">{{ __('messages.order_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-info text-white"><i class="fas fa-save me-1"></i> {{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // ============================================
    // CHART INSTANCE STORAGE & RESIZE HELPER
    // ============================================
    let chartInstances = [];

    function resizeAllCharts() {
        chartInstances.forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    const collapseBtn = document.getElementById('ictCollapseSidebar');
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function() {
            setTimeout(resizeAllCharts, 100);
        });
    }

    const toggleBtn = document.getElementById('ictSidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            setTimeout(resizeAllCharts, 200);
        });
    }

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resizeAllCharts, 150);
    });

    // ============================================
    // COLOR HELPER
    // ============================================
    const colors = {
        primary: '#4f46e5',
        success: '#22c55e',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#3b82f6',
        secondary: '#6b7280',
        purple: '#8b5cf6',
        indigo: '#6366f1',
    };

    function hexToRgb(hex) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `${r}, ${g}, ${b}`;
    }

    function createGradient(ctx, chartArea, hexColor, alphaTop = 0.35, alphaBottom = 0.02) {
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        const rgb = hexToRgb(hexColor);
        gradient.addColorStop(0, `rgba(${rgb}, ${alphaTop})`);
        gradient.addColorStop(1, `rgba(${rgb}, ${alphaBottom})`);
        return gradient;
    }

    // ============================================
    // 1. PRODUCT STATUS CHART
    // ============================================
    @if(isset($productStatusData) && count($productStatusData) > 0)
        const statusCtx = document.getElementById('productStatusChart').getContext('2d');
        const productStatusTotal = {{ array_sum($productStatusData) }};
        const chart1 = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($productStatusData)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($productStatusData)) !!},
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#6b7280', '#ef4444'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 15, font: { size: 11 } }
                    },
                    centerText: {
                        display: true,
                        text: productStatusTotal.toLocaleString(),
                        subtext: '{{ __('messages.total_products') }}'
                    }
                },
                cutout: '72%'
            }
        });
        chartInstances.push(chart1);
    @endif

    // ============================================
    // 2. MONTHLY SALES & REVENUE CHART
    // ============================================
    @if(isset($monthlySales) && count($monthlySales) > 0)
        const salesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const salesLabels = {!! json_encode($monthlySales->pluck('month')->toArray()) !!};
        const salesCounts = {!! json_encode($monthlySales->pluck('total_sales')->toArray()) !!};
        const salesRevenue = {!! json_encode($monthlySales->pluck('revenue')->toArray()) !!};

        const chart2 = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [
                    {
                        label: '{{ __('messages.sales_count') }}',
                        data: salesCounts,
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(79,70,229,0.2)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: colors.primary,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        order: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: '{{ __('messages.revenue') }} ($)',
                        data: salesRevenue,
                        borderColor: colors.success,
                        backgroundColor: 'rgba(34,197,94,0.2)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: colors.success,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        order: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        ticks: { font: { size: 11 } },
                        grid: { drawOnChartArea: false }
                    },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });
        chartInstances.push(chart2);
    @endif

    // ============================================
    // 3. SALES STATUS CHART
    // ============================================
    @if(isset($salesStatusData) && count($salesStatusData) > 0)
        const salesStatusCtx = document.getElementById('salesStatusChart').getContext('2d');
        const chart3 = new Chart(salesStatusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($salesStatusData)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($salesStatusData)) !!},
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 15, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
        chartInstances.push(chart3);
    @endif

    // ============================================
    // 4. STOCK MOVEMENT CHART
    // ============================================
    @if(isset($stockMovements) && count($stockMovements) > 0)
        const stockCtx = document.getElementById('stockMovementChart').getContext('2d');
        const incoming = {!! json_encode($stockMovements->pluck('incoming')->toArray()) !!};
        const outgoing = {!! json_encode($stockMovements->pluck('outgoing')->map(function($v) { return -abs($v); })->toArray()) !!};
        const chart4 = new Chart(stockCtx, {
            data: {
                labels: {!! json_encode($stockMovements->pluck('date')->toArray()) !!},
                datasets: [
                    {
                        type: 'bar',
                        label: '{{ __('messages.incoming') }}',
                        data: incoming,
                        backgroundColor: 'rgba(34,197,94,0.85)',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: false,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: '{{ __('messages.outgoing') }}',
                        data: outgoing,
                        backgroundColor: 'rgba(239,68,68,0.85)',
                        borderRadius: { bottomLeft: 4, bottomRight: 4 },
                        borderSkipped: false,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (item) => `${item.dataset.label}: ${Math.abs(item.raw)}`
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: { font: { size: 11 }, callback: (v) => Math.abs(v) },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { font: { size: 10 }, maxRotation: 45, autoSkip: true, maxTicksLimit: 15 }
                    }
                }
            }
        });
        chartInstances.push(chart4);
    @endif

    // ============================================
    // CHART PERIOD TOGGLES
    // ============================================
    $('.chart-period-btn').on('click', function() {
        const period = $(this).data('period');
        $('.chart-period-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        console.log('Period changed to:', period);
    });

    // ============================================
    // CSRF SETUP
    // ============================================
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ============================================
    // AUTO-REFRESH STATS
    // ============================================
    function refreshStats() {
        $.ajax({
            url: '{{ route("api.admin.stats") }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.total_products !== undefined) {
                    $('#stat-total-products').text(data.total_products.toLocaleString());
                }
                if (data.total_users !== undefined) {
                    $('#stat-total-users').text(data.total_users.toLocaleString());
                }
            },
            error: function() {
                // silent fail
            }
        });
    }
    setInterval(refreshStats, 30000);

    // ============================================
    // WELCOME GREETING
    // ============================================
    const hour = new Date().getHours();
    let greeting = '{{ __('messages.good_morning') }}';
    if (hour >= 12 && hour < 17) greeting = '{{ __('messages.good_afternoon') }}';
    else if (hour >= 17) greeting = '{{ __('messages.good_evening') }}';
    $('.welcome-greeting').text(greeting);

    // ============================================
    // ANIMATE PROGRESS BARS
    // ============================================
    setTimeout(function() {
        $('.stat-progress-bar').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0%');
            setTimeout(function() {
                $(this).css('width', width);
            }.bind(this), 100);
        });
    }, 300);

    // ============================================
    // REAL-TIME CLOCK
    // ============================================
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('{{ app()->getLocale() }}', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        $('.clock-display').text(timeString);
    }
    updateClock();
    setInterval(updateClock, 1000);

    console.log('Dashboard initialized with ' + chartInstances.length + ' charts.');
});
</script>
@endpush
@endsection
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\Category;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // ============================================
        // 1. STATISTICS
        // ============================================
        $stats = [
            // Products
            'total_products' => Product::count(),
            'available_products' => Product::where('status', 'available')->sum('quantity'),
            'low_stock' => Product::whereColumn('quantity', '<=', 'min_stock_level')
                ->where('quantity', '>', 0)
                ->count(),
            'out_of_stock' => Product::where('quantity', '<=', 0)->count(),
            'total_value' => Product::sum('purchase_price'),

            // Sales
            'total_sales' => Sale::count(),
            'total_revenue' => Sale::sum('total'),
            'pending_sales' => Sale::where('payment_status', 'pending')->count(),
            'paid_sales' => Sale::where('payment_status', 'paid')->count(),
            'today_sales' => Sale::whereDate('sale_date', today())->count(),
            'today_revenue' => Sale::whereDate('sale_date', today())->sum('total'),

            // Purchase Orders
            'total_purchase_orders' => PurchaseOrder::count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'ordered')->count(),
            'received_purchase_orders' => PurchaseOrder::where('status', 'received')->count(),

            // Customers
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('is_active', 1)->count(),

            // Suppliers
            'total_suppliers' => Supplier::count(),

            // Users
            'total_users' => User::count(),
            'users_by_role' => DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as total'))
                ->groupBy('roles.name')
                ->pluck('total', 'roles.name'),
        ];

        // ============================================
        // 2. RECENT ACTIVITIES (Sales & Purchase Orders)
        // ============================================
        $recentActivities = [];

        // Recent Sales
        $recentSales = Sale::with(['customer', 'user'])->latest()->take(5)->get();
        foreach ($recentSales as $sale) {
            $recentActivities[] = [
                'color' => 'success',
                'icon' => 'fa-file-invoice-dollar',
                'time' => $sale->created_at->locale(app()->getLocale())->diffForHumans(),
                'timestamp' => $sale->created_at->timestamp,
                'message' => __('messages.sale') . ' #' . $sale->invoice_number . ' - ' . ($sale->customer->name ?? __('messages.walk_in_customer')) . ' - $' . number_format($sale->total, 2),
                'link' => route('admin.sales.show', $sale->id),
            ];
        }

        // Recent Purchase Orders
        $recentPOs = PurchaseOrder::with(['supplier', 'user'])->latest()->take(5)->get();
        foreach ($recentPOs as $po) {
            $recentActivities[] = [
                'color' => 'info',
                'icon' => 'fa-shopping-cart',
                'time' => $po->created_at->locale(app()->getLocale())->diffForHumans(),
                'timestamp' => $po->created_at->timestamp,
                'message' => __('messages.purchase_order') . ' #' . $po->po_number . ' - ' . ($po->supplier->name ?? 'N/A') . ' - $' . number_format($po->total_amount ?? 0, 2),
                'link' => route('admin.purchase-orders.show', $po->id),
            ];
        }

        // Recent Stock Movements
        $recentMovements = StockMovement::with(['product', 'user'])->latest()->take(5)->get();
        foreach ($recentMovements as $movement) {
            $typeLabel = __('messages.' . strtolower($movement->type));
            $color = match ($movement->type) {
                'purchase' => 'success',
                'sale' => 'danger',
                'adjustment' => 'warning',
                'return' => 'info',
                default => 'secondary',
            };
            $icon = match ($movement->type) {
                'purchase' => 'fa-arrow-down',
                'sale' => 'fa-arrow-up',
                'adjustment' => 'fa-sliders-h',
                'return' => 'fa-undo',
                default => 'fa-circle',
            };
            $recentActivities[] = [
                'color' => $color,
                'icon' => $icon,
                'time' => $movement->created_at->locale(app()->getLocale())->diffForHumans(),
                'timestamp' => $movement->created_at->timestamp,
                'message' => $typeLabel . ' - ' . ($movement->product->name ?? 'N/A') . ' (' . ($movement->quantity_change > 0 ? '+' : '') . $movement->quantity_change . ')',
                'link' => route('admin.stock-movements.show', $movement->id),
            ];
        }

        // Sort by actual timestamp (descending)
        usort($recentActivities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        $recentActivities = array_slice($recentActivities, 0, 10);

        // ============================================
        // 3. CHART DATA
        // ============================================
        $productStatusData = Product::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();

        $monthlySales = Sale::select(
                DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'),
                DB::raw('count(*) as total_sales'),
                DB::raw('sum(total) as revenue')
            )
            ->where('sale_date', '>=', now()->subMonths(12))
            ->groupBy('month')->orderBy('month', 'asc')->get();

        $monthlyPurchaseOrders = PurchaseOrder::select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('count(*) as total')
            )
            ->where('order_date', '>=', now()->subMonths(12))
            ->groupBy('month')->orderBy('month', 'asc')->pluck('total', 'month')->toArray();

        $salesStatusData = Sale::select('payment_status', DB::raw('count(*) as total'))->groupBy('payment_status')->pluck('total', 'payment_status')->toArray();

        $productTypeData = Product::select('type', DB::raw('count(*) as total'))->whereNotNull('type')->groupBy('type')->pluck('total', 'type')->toArray();

        $stockMovements = StockMovement::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as incoming'),
                DB::raw('SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as outgoing')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('sum(sale_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')->orderBy('total_quantity', 'desc')->limit(5)->get();

        // ============================================
        // 4. QUICK LINKS (WITH MODALS)
        // ============================================
        $quickLinks = [
            [
                'title' => __('messages.new_sale'),
                'icon' => 'fa-cash-register',
                'color' => 'success',
                'modal' => 'quickSaleModal',
                'permission' => 'sale.create',
            ],
            [
                'title' => __('messages.add_product'),
                'icon' => 'fa-box',
                'color' => 'primary',
                'modal' => 'quickProductModal',
                'permission' => 'product.create',
            ],
            [
                'title' => __('messages.new_purchase_order'),
                'icon' => 'fa-shopping-cart',
                'color' => 'info',
                'modal' => 'quickPurchaseOrderModal',
                'permission' => 'purchase_order.create',
            ],
            [
                'title' => __('messages.add_customer'),
                'icon' => 'fa-user-plus',
                'color' => 'warning',
                'modal' => 'quickCustomerModal',
                'permission' => 'customer.create',
            ],
            [
                'title' => __('messages.view_reports'),
                'icon' => 'fa-chart-bar',
                'color' => 'danger',
                'route' => route('admin.reports.index'),
                'permission' => 'report.view',
            ],
            [
                'title' => __('messages.manage_users'),
                'icon' => 'fa-users',
                'color' => 'indigo',
                'route' => route('admin.users.index'),
                'permission' => 'user.view',
            ],
        ];

        // ============================================
        // 5. NOTIFICATIONS
        // ============================================
        $notifications = Notification::with('user')->where('is_read', false)->latest()->take(10)->get();
        $unreadCount = Notification::where('is_read', false)->count();

        // ============================================
        // 6. DATA FOR MODALS/DROPDOWNS
        // ============================================
        $categories = Category::all();
        $suppliers = Supplier::all();
        $products = Product::where('status', 'available')->where('quantity', '>', 0)->get();
        $customers = Customer::where('is_active', 1)->get();

        return view('admin.dashboard.index', compact(
            'stats', 'recentActivities', 'productStatusData', 'monthlySales',
            'monthlyPurchaseOrders', 'salesStatusData', 'productTypeData',
            'stockMovements', 'topProducts', 'quickLinks', 'notifications',
            'unreadCount', 'categories', 'suppliers', 'products', 'customers'
        ));
    }

    // ... [Keep the rest of your API methods exactly as they are, e.g., statistics(), chartData(), etc.] ...
    
    /**
     * Get recent activities (API).
     */
    public function recentActivities(Request $request)
    {
        $limit = $request->limit ?? 10;
        $activities = [];

        $sales = Sale::with(['customer', 'user'])->latest()->take($limit)->get()->map(function($sale) {
            return [
                'id' => $sale->id, 'type' => 'sale',
                'title' => __('messages.sale') . ' #' . $sale->invoice_number,
                'description' => ($sale->customer->name ?? __('messages.walk_in_customer')) . ' - $' . number_format($sale->total, 2),
                'time' => $sale->created_at->locale(app()->getLocale())->diffForHumans(),
                'created_at' => $sale->created_at,
                'link' => route('admin.sales.show', $sale->id),
            ];
        });

        $purchaseOrders = PurchaseOrder::with(['supplier', 'user'])->latest()->take($limit)->get()->map(function($po) {
            return [
                'id' => $po->id, 'type' => 'purchase_order',
                'title' => __('messages.purchase_order') . ' #' . $po->po_number,
                'description' => ($po->supplier->name ?? 'N/A') . ' - $' . number_format($po->total_amount ?? 0, 2),
                'time' => $po->created_at->locale(app()->getLocale())->diffForHumans(),
                'created_at' => $po->created_at,
                'link' => route('admin.purchase-orders.show', $po->id),
            ];
        });

        $stockMovements = StockMovement::with(['product', 'user'])->latest()->take($limit)->get()->map(function($movement) {
            return [
                'id' => $movement->id, 'type' => 'stock_movement',
                'title' => __('messages.' . strtolower($movement->type)) . ' - ' . ($movement->product->name ?? 'N/A'),
                'description' => ($movement->quantity_change > 0 ? '+' : '') . $movement->quantity_change . ' ' . __('messages.units'),
                'time' => $movement->created_at->locale(app()->getLocale())->diffForHumans(),
                'created_at' => $movement->created_at,
                'link' => route('admin.stock-movements.show', $movement->id),
            ];
        });

        $activities = $sales->merge($purchaseOrders)->merge($stockMovements)->sortByDesc('created_at')->take($limit)->values()->toArray();

        return response()->json($activities);
    }
}
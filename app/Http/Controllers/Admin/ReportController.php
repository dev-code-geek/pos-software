<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;  
use App\Models\Customer;
use App\Models\Invoice; 
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeOvertime;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\PurchasePayment;  
use App\Models\SalesPayment;     
use App\Models\LeaveType;
use App\Models\PayrollAdjustment;
use App\Models\PayrollCalculation;
use App\Models\PayrollPeriod;
use App\Models\PayrollRate;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $stats = [
                // Products
                'total_products' => Product::count(),
                'total_quantity' => Product::sum('quantity'),
                'available' => Product::where('status', 'available')->sum('quantity'),
                'assigned' => Product::where('status', 'assigned')->sum('quantity'),
                'under_repair' => Product::where('status', 'under_repair')->count(),
                'expired_warranties' => Product::where('warranty_end_date', '<', now())->whereNotNull('warranty_end_date')->count(),
                'low_stock' => Product::whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 0)->count(),
                'out_of_stock' => Product::where('quantity', '<=', 0)->count(),
                
                // Employees & Users
                'total_employees' => Employee::count(),
                'total_users' => User::count(),
                'active_employees' => Employee::where('employment_status', 'Active')->count(),
                'on_leave' => Employee::where('employment_status', 'On Leave')->count(),
                
                // Attendance
                'today_attendance' => Attendance::whereDate('attendance_date', today())->count(),
                'present_today' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'Present')->count(),
                'absent_today' => Attendance::whereDate('attendance_date', today())->where('attendance_status', 'Absent')->count(),
                
                // Leave
                'pending_leave_requests' => LeaveRequest::where('approval_status', 'Pending')->count(),
                'total_leave_requests' => LeaveRequest::count(),
                
                // Payroll
                'total_payroll_periods' => PayrollPeriod::count(),
                'open_payroll_periods' => PayrollPeriod::where('status', 'Open')->count(),
                'total_payroll_calculations' => PayrollCalculation::count(),
                'pending_payroll' => PayrollCalculation::where('payment_status', 'Pending')->count(),
                'active_loans' => EmployeeLoan::where('status', 'Active')->count(),
                'pending_overtime' => EmployeeOvertime::where('status', 'Pending')->count(),
                'pending_adjustments' => PayrollAdjustment::whereNull('approved_at')->count(),
                
                // Sales & Purchases
                'total_sales' => Sale::count(),
                'total_purchase_orders' => PurchaseOrder::count(),
                'total_quotations' => Quotation::count(),
                
                // Customers & Suppliers
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
                
                // Organization
                'total_companies' => Company::count(),
                'total_branches' => Branch::count(),
                'total_departments' => Department::count(),
                'total_warehouses' => Warehouse::count(),
                'total_categories' => Category::count(),
            ];

            $departments = Department::select('id', 'name')->get();
            $categories = Category::select('id', 'name')->get();
            $companies = Company::select('id', 'name')->get();
            $branches = Branch::select('id', 'name')->get();
            $users = User::select('id', 'name')->get();
            $periods = PayrollPeriod::select('id', 'period_name', 'period_code')->get();
            $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();

            return view('admin.reports.index', compact(
                'stats', 'departments', 'categories', 'companies', 
                'branches', 'users', 'periods', 'employees'
            ));
        } catch (\Throwable $e) {
            Log::error('Reports dashboard failed to load', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            abort(500, config('app.debug') ? $e->getMessage() : 'Failed to load reports dashboard.');
        }
    }

    /**
     * ============================================
     * INVENTORY REPORTS
     * ============================================
     */

    public function inventory(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Product::with(['category:id,name', 'supplier:id,name', 'warehouse:id,name'])
                ->select([
                    'id', 'name', 'sku', 'category_id', 'supplier_id', 'warehouse_id',
                    'type', 'quantity', 'status', 'purchase_price', 'selling_price',
                    'purchase_date', 'min_stock_level', 'warranty_end_date',
                    'created_at', 'updated_at'
                ]);

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            $statsQuery = clone $query;
            $stats = [
                'total' => (clone $statsQuery)->count(),
                'total_value' => (clone $statsQuery)->sum('purchase_price'),
                'total_quantity' => (clone $statsQuery)->sum('quantity'),
                'low_stock' => (clone $statsQuery)->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 0)->count(),
                'out_of_stock' => (clone $statsQuery)->where('quantity', '<=', 0)->count(),
                'hardware_count' => (clone $statsQuery)->where('type', 'hardware')->count(),
                'software_count' => (clone $statsQuery)->where('type', 'software')->count(),
                'available_quantity' => (clone $statsQuery)->where('status', 'available')->sum('quantity'),
                'assigned_quantity' => (clone $statsQuery)->where('status', 'assigned')->sum('quantity'),
                'expired_warranties' => (clone $statsQuery)->where('warranty_end_date', '<', now())->whereNotNull('warranty_end_date')->count(),
            ];

            $perPage = min((int) $request->input('per_page', 50), 200);
            $products = $query->latest()->paginate($perPage)->appends($request->query());

            $data = $products->getCollection()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'N/A',
                    'category_id' => $product->category_id,
                    'type' => $product->type ?? 'N/A',
                    'quantity' => $product->quantity,
                    'status' => $product->status,
                    'purchase_price' => $product->purchase_price ?? 0,
                    'selling_price' => $product->selling_price ?? 0,
                    'total_value' => ($product->purchase_price ?? 0) * $product->quantity,
                    'purchase_date' => $product->purchase_date ? $product->purchase_date->format('Y-m-d') : null,
                    'min_stock' => $product->min_stock_level,
                    'supplier' => $product->supplier->name ?? 'N/A',
                    'warehouse' => $product->warehouse->name ?? 'N/A',
                    'warranty_end_date' => $product->warranty_end_date ? $product->warranty_end_date->format('Y-m-d') : null,
                ];
            })->values();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ],
                ]);
            }

            $categories = Category::select('id', 'name')->get();
            $warehouses = Warehouse::select('id', 'name')->get();
            return view('admin.reports.inventory', compact('products', 'stats', 'categories', 'warehouses'));
        } catch (\Throwable $e) {
            Log::error('Inventory report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load inventory report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load inventory report.');
        }
    }

    public function lowStock(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Product::with(['category:id,name', 'supplier:id,name', 'warehouse:id,name'])
                ->whereColumn('quantity', '<=', 'min_stock_level')
                ->where('quantity', '>=', 0);

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $products = $query->orderBy('quantity', 'asc')->paginate($perPage)->appends($request->query());
            $collection = $products->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'low_stock' => (clone $statsQuery)->where('quantity', '>', 0)->count(),
                'out_of_stock' => (clone $statsQuery)->where('quantity', '<=', 0)->count(),
                'critical' => (clone $statsQuery)->where('quantity', '>', 0)->whereColumn('quantity', '<=', DB::raw('min_stock_level * 0.5'))->count(),
                'total' => $products->total(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'category' => $product->category->name ?? 'N/A',
                        'quantity' => $product->quantity,
                        'min_stock' => $product->min_stock_level,
                        'status' => $product->status,
                        'supplier' => $product->supplier->name ?? 'N/A',
                        'warehouse' => $product->warehouse->name ?? 'N/A',
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ],
                ]);
            }

            $categories = Category::select('id', 'name')->get();
            $warehouses = Warehouse::select('id', 'name')->get();
            return view('admin.reports.low-stock', compact('products', 'stats', 'categories', 'warehouses'));
        } catch (\Throwable $e) {
            Log::error('Low stock report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load low stock report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load low stock report.');
        }
    }

    public function warrantyExpiry(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Product::with(['category:id,name', 'supplier:id,name', 'warehouse:id,name'])
                ->whereNotNull('warranty_end_date');

            if ($request->filled('date_from')) {
                $query->whereDate('warranty_end_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('warranty_end_date', '<=', $request->date_to);
            }
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('warranty_status')) {
                switch ($request->warranty_status) {
                    case 'expired':
                        $query->where('warranty_end_date', '<', now());
                        break;
                    case 'expiring_30':
                        $query->whereBetween('warranty_end_date', [now(), now()->addDays(30)]);
                        break;
                    case 'expiring_60':
                        $query->whereBetween('warranty_end_date', [now()->addDays(30), now()->addDays(60)]);
                        break;
                    case 'active':
                        $query->where('warranty_end_date', '>', now()->addDays(60));
                        break;
                }
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $products = $query->orderBy('warranty_end_date', 'asc')->paginate($perPage)->appends($request->query());
            $collection = $products->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'expired' => (clone $statsQuery)->where('warranty_end_date', '<', now())->count(),
                'expiring_30' => (clone $statsQuery)->whereBetween('warranty_end_date', [now(), now()->addDays(30)])->count(),
                'expiring_60' => (clone $statsQuery)->whereBetween('warranty_end_date', [now()->addDays(30), now()->addDays(60)])->count(),
                'active' => (clone $statsQuery)->where('warranty_end_date', '>', now()->addDays(60))->count(),
                'total' => $products->total(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($product) {
                    $daysLeft = now()->diffInDays($product->warranty_end_date, false);
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'category' => $product->category->name ?? 'N/A',
                        'supplier' => $product->supplier->name ?? 'N/A',
                        'warehouse' => $product->warehouse->name ?? 'N/A',
                        'warranty_end_date' => $product->warranty_end_date->format('Y-m-d'),
                        'days_left' => (int) $daysLeft,
                        'status' => $daysLeft < 0 ? 'expired' : ($daysLeft <= 30 ? 'expiring_soon' : 'active'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ],
                ]);
            }

            $categories = Category::select('id', 'name')->get();
            $warehouses = Warehouse::select('id', 'name')->get();
            return view('admin.reports.warranty-expiry', compact('products', 'stats', 'categories', 'warehouses'));
        } catch (\Throwable $e) {
            Log::error('Warranty expiry report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load warranty expiry report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load warranty expiry report.');
        }
    }

    /**
     * ============================================
     * ORGANIZATION REPORTS
     * ============================================
     */

    public function companies(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Company::with(['currency:id,name,code,symbol']);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%")
                      ->orWhere('short_name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%");
            }

            $companies = $query->latest()->paginate(50)->appends($request->query());
            $collection = $companies->getCollection();

            $stats = [
                'total' => Company::count(),
                'active' => Company::where('is_active', 1)->count(),
                'inactive' => Company::where('is_active', 0)->count(),
                'total_branches' => Branch::count(),
                'total_employees' => Employee::count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'short_name' => $company->short_name,
                        'email' => $company->email,
                        'phone' => $company->phone,
                        'city' => $company->city,
                        'country' => $company->country,
                        'currency' => $company->currency->code ?? 'USD',
                        'is_active' => $company->is_active,
                        'created_at' => $company->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $companies->total(),
                        'per_page' => $companies->perPage(),
                        'current_page' => $companies->currentPage(),
                        'last_page' => $companies->lastPage(),
                    ],
                ]);
            }

            return view('admin.reports.companies', compact('companies', 'stats'));
        } catch (\Throwable $e) {
            Log::error('Companies report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load companies report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load companies report.');
        }
    }

    public function branches(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Branch::with(['company:id,name', 'warehouse:id,name']);

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%")
                      ->orWhere('code', 'like', "%{$request->search}%")
                      ->orWhere('city', 'like', "%{$request->search}%");
            }

            $branches = $query->latest()->paginate(50)->appends($request->query());
            $collection = $branches->getCollection();

            $stats = [
                'total' => Branch::count(),
                'active' => Branch::where('is_active', 1)->count(),
                'inactive' => Branch::where('is_active', 0)->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'code' => $branch->code,
                        'company' => $branch->company->name ?? 'N/A',
                        'warehouse' => $branch->warehouse->name ?? 'N/A',
                        'city' => $branch->city,
                        'email' => $branch->email,
                        'phone' => $branch->phone,
                        'is_active' => $branch->is_active,
                        'created_at' => $branch->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $branches->total(),
                        'per_page' => $branches->perPage(),
                        'current_page' => $branches->currentPage(),
                        'last_page' => $branches->lastPage(),
                    ],
                ]);
            }

            $companies = Company::select('id', 'name')->get();
            return view('admin.reports.branches', compact('branches', 'stats', 'companies'));
        } catch (\Throwable $e) {
            Log::error('Branches report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load branches report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load branches report.');
        }
    }

    public function departments(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Department::with(['company:id,name', 'branch:id,name']);

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $departments = $query->latest()->paginate(50)->appends($request->query());
            $collection = $departments->getCollection();

            $employeeCounts = Employee::whereIn('department_id', $collection->pluck('id'))
                ->select('department_id', DB::raw('count(*) as count'))
                ->groupBy('department_id')
                ->pluck('count', 'department_id')
                ->toArray();

            $stats = [
                'total' => Department::count(),
                'active' => Department::where('is_active', 1)->count(),
                'inactive' => Department::where('is_active', 0)->count(),
                'total_employees' => Employee::count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($dept) use ($employeeCounts) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'code' => $dept->department_code,
                        'company' => $dept->company->name ?? 'N/A',
                        'branch' => $dept->branch->name ?? 'N/A',
                        'head' => $dept->head_of_department,
                        'employee_count' => $employeeCounts[$dept->id] ?? 0,
                        'is_active' => $dept->is_active,
                        'created_at' => $dept->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $departments->total(),
                        'per_page' => $departments->perPage(),
                        'current_page' => $departments->currentPage(),
                        'last_page' => $departments->lastPage(),
                    ],
                ]);
            }

            $companies = Company::select('id', 'name')->get();
            $branches = Branch::select('id', 'name')->get();
            return view('admin.reports.departments', compact('departments', 'stats', 'companies', 'branches'));
        } catch (\Throwable $e) {
            Log::error('Departments report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load departments report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load departments report.');
        }
    }

    public function warehouses(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Warehouse::with(['manager:id,name']);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%")
                      ->orWhere('code', 'like', "%{$request->search}%")
                      ->orWhere('city', 'like', "%{$request->search}%");
            }

            $warehouses = $query->latest()->paginate(50)->appends($request->query());
            $collection = $warehouses->getCollection();

            $productCounts = Product::whereIn('warehouse_id', $collection->pluck('id'))
                ->select('warehouse_id', DB::raw('count(*) as count'), DB::raw('sum(quantity) as total_quantity'))
                ->groupBy('warehouse_id')
                ->get()
                ->keyBy('warehouse_id');

            $stats = [
                'total' => Warehouse::count(),
                'active' => Warehouse::where('is_active', 1)->count(),
                'inactive' => Warehouse::where('is_active', 0)->count(),
                'total_products' => Product::count(),
                'total_quantity' => Product::sum('quantity'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($warehouse) use ($productCounts) {
                    $productData = $productCounts[$warehouse->id] ?? null;
                    return [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'code' => $warehouse->code,
                        'manager' => $warehouse->manager->name ?? 'N/A',
                        'city' => $warehouse->city,
                        'country' => $warehouse->country,
                        'product_count' => $productData->count ?? 0,
                        'total_quantity' => $productData->total_quantity ?? 0,
                        'is_active' => $warehouse->is_active,
                        'created_at' => $warehouse->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $warehouses->total(),
                        'per_page' => $warehouses->perPage(),
                        'current_page' => $warehouses->currentPage(),
                        'last_page' => $warehouses->lastPage(),
                    ],
                ]);
            }

            return view('admin.reports.warehouses', compact('warehouses', 'stats'));
        } catch (\Throwable $e) {
            Log::error('Warehouses report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load warehouses report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load warehouses report.');
        }
    }

    public function categories(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Category::withCount(['products']);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            $categories = $query->orderBy('order', 'asc')->paginate(50)->appends($request->query());
            $collection = $categories->getCollection();

            $stats = [
                'total' => Category::count(),
                'active' => Category::where('is_active', 1)->count(),
                'inactive' => Category::where('is_active', 0)->count(),
                'total_products' => Product::count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'product_count' => $category->products_count,
                        'order' => $category->order,
                        'is_active' => $category->is_active,
                        'created_at' => $category->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $categories->total(),
                        'per_page' => $categories->perPage(),
                        'current_page' => $categories->currentPage(),
                        'last_page' => $categories->lastPage(),
                    ],
                ]);
            }

            return view('admin.reports.categories', compact('categories', 'stats'));
        } catch (\Throwable $e) {
            Log::error('Categories report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load categories report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load categories report.');
        }
    }

    /**
     * ============================================
     * EMPLOYEE & HR REPORTS
     * ============================================
     */

    public function employees(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Employee::with(['company:id,name', 'branch:id,name', 'department:id,name', 'user:id,name']);

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('employment_status')) {
                $query->where('employment_status', $request->employment_status);
            }
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $employees = $query->latest()->paginate($perPage)->appends($request->query());
            $collection = $employees->getCollection();

            $stats = [
                'total' => Employee::count(),
                'active' => Employee::where('employment_status', 'Active')->count(),
                'inactive' => Employee::where('employment_status', 'Inactive')->count(),
                'on_leave' => Employee::where('employment_status', 'On Leave')->count(),
                'terminated' => Employee::where('employment_status', 'Terminated')->count(),
                'male' => Employee::where('gender', 'Male')->count(),
                'female' => Employee::where('gender', 'Female')->count(),
                'total_salary' => Employee::sum('basic_salary'),
                'avg_salary' => Employee::avg('basic_salary'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'email' => $employee->email,
                        'phone' => $employee->phone,
                        'company' => $employee->company->name ?? 'N/A',
                        'branch' => $employee->branch->name ?? 'N/A',
                        'department' => $employee->department->name ?? 'N/A',
                        'gender' => $employee->gender,
                        'hire_date' => $employee->hire_date ? $employee->hire_date->format('Y-m-d') : null,
                        'employment_status' => $employee->employment_status,
                        'basic_salary' => $employee->basic_salary ?? 0,
                        'is_active' => $employee->is_active,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $employees->total(),
                        'per_page' => $employees->perPage(),
                        'current_page' => $employees->currentPage(),
                        'last_page' => $employees->lastPage(),
                    ],
                ]);
            }

            $companies = Company::select('id', 'name')->get();
            $branches = Branch::select('id', 'name')->get();
            $departments = Department::select('id', 'name')->get();
            $statuses = ['Active', 'Inactive', 'Terminated', 'On Leave'];
            return view('admin.reports.employees', compact('employees', 'stats', 'companies', 'branches', 'departments', 'statuses'));
        } catch (\Throwable $e) {
            Log::error('Employees report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load employees report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load employees report.');
        }
    }

      public function attendance(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Attendance::with(['employee:id,first_name,last_name,employee_code']);

            if ($request->filled('date_from')) {
                $query->whereDate('attendance_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('attendance_date', '<=', $request->date_to);
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('attendance_status')) {
                $query->where('attendance_status', $request->attendance_status);
            }
            if ($request->filled('is_approved')) {
                $query->where('is_approved', $request->is_approved);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $attendance = $query->latest('attendance_date')->paginate($perPage)->appends($request->query());
            $collection = $attendance->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'total' => $attendance->total(),
                'present' => (clone $statsQuery)->where('attendance_status', 'Present')->count(),
                'absent' => (clone $statsQuery)->where('attendance_status', 'Absent')->count(),
                'late' => (clone $statsQuery)->where('attendance_status', 'Late')->count(),
                'half_day' => (clone $statsQuery)->where('attendance_status', 'Half Day')->count(),
                'on_leave' => (clone $statsQuery)->where('attendance_status', 'On Leave')->count(),
                'on_travel' => (clone $statsQuery)->where('attendance_status', 'On Travel')->count(),
                'approved' => (clone $statsQuery)->where('is_approved', 1)->count(),
                'pending_approval' => (clone $statsQuery)->where('is_approved', 0)->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($record) {
                    // Safe date formatting - check if dates are Carbon objects or strings
                    $attendanceDate = $record->attendance_date;
                    if ($attendanceDate instanceof \Carbon\Carbon) {
                        $attendanceDate = $attendanceDate->format('Y-m-d');
                    } elseif (is_string($attendanceDate)) {
                        $attendanceDate = date('Y-m-d', strtotime($attendanceDate));
                    } else {
                        $attendanceDate = null;
                    }

                    $checkIn = $record->check_in_time;
                    if ($checkIn instanceof \Carbon\Carbon) {
                        $checkIn = $checkIn->format('H:i');
                    } elseif (is_string($checkIn)) {
                        $checkIn = date('H:i', strtotime($checkIn));
                    } else {
                        $checkIn = null;
                    }

                    $checkOut = $record->check_out_time;
                    if ($checkOut instanceof \Carbon\Carbon) {
                        $checkOut = $checkOut->format('H:i');
                    } elseif (is_string($checkOut)) {
                        $checkOut = date('H:i', strtotime($checkOut));
                    } else {
                        $checkOut = null;
                    }

                    return [
                        'id' => $record->id,
                        'employee' => $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'N/A',
                        'employee_code' => $record->employee->employee_code ?? 'N/A',
                        'attendance_date' => $attendanceDate,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'total_hours' => $record->total_worked_hours ?? 0,
                        'overtime' => $record->overtime_hours ?? 0,
                        'status' => $record->attendance_status ?? 'N/A',
                        'is_approved' => $record->is_approved ?? 0,
                        'remarks' => $record->remarks,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $attendance->total(),
                        'per_page' => $attendance->perPage(),
                        'current_page' => $attendance->currentPage(),
                        'last_page' => $attendance->lastPage(),
                    ],
                ]);
            }

            $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
            $statuses = ['Present', 'Absent', 'Late', 'Half Day', 'On Leave', 'On Travel'];
            return view('admin.reports.attendance', compact('attendance', 'stats', 'employees', 'statuses'));
        } catch (\Throwable $e) {
            Log::error('Attendance report error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load attendance report: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load attendance report.');
        }
    }

    /**
     * ============================================
     * LEAVE REPORTS
     * ============================================
     */

    public function leave(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $type = $request->type ?? 'requests';

            if ($type === 'types') {
                return $this->leaveTypes($request);
            } elseif ($type === 'balances') {
                return $this->leaveBalances($request);
            } else {
                return $this->leaveRequests($request);
            }
        } catch (\Throwable $e) {
            Log::error('Leave report error', ['message' => $e->getMessage()]);
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load leave report.');
        }
    }

    public function leaveTypes(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = LeaveType::with(['company:id,name']);

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $leaveTypes = $query->latest()->paginate(50)->appends($request->query());
            $collection = $leaveTypes->getCollection();

            $stats = [
                'total' => LeaveType::count(),
                'active' => LeaveType::where('is_active', 1)->count(),
                'inactive' => LeaveType::where('is_active', 0)->count(),
                'paid' => LeaveType::where('is_paid', 1)->count(),
                'unpaid' => LeaveType::where('is_paid', 0)->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'code' => $type->leave_code,
                        'name' => $type->leave_name,
                        'company' => $type->company->name ?? 'N/A',
                        'days_per_year' => $type->days_per_year,
                        'is_paid' => $type->is_paid,
                        'requires_approval' => $type->requires_approval,
                        'requires_medical' => $type->requires_medical_certificate,
                        'max_consecutive_days' => $type->max_consecutive_days,
                        'is_active' => $type->is_active,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $leaveTypes->total(),
                        'per_page' => $leaveTypes->perPage(),
                        'current_page' => $leaveTypes->currentPage(),
                        'last_page' => $leaveTypes->lastPage(),
                    ],
                ]);
            }

            $companies = Company::select('id', 'name')->get();
            return view('admin.reports.leave-types', compact('leaveTypes', 'stats', 'companies'));
        } catch (\Throwable $e) {
            Log::error('Leave types report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load leave types report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load leave types report.');
        }
    }

    public function leaveBalances(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = LeaveBalance::with(['employee:id,first_name,last_name,employee_code', 'leaveType:id,leave_name,leave_code']);

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            $balances = $query->latest()->paginate(50)->appends($request->query());
            $collection = $balances->getCollection();

            $stats = [
                'total_records' => LeaveBalance::count(),
                'total_employees' => $collection->pluck('employee_id')->unique()->count(),
                'total_entitled' => $collection->sum('entitled_days'),
                'total_used' => $collection->sum('used_days'),
                'total_remaining' => $collection->sum('remaining_days'),
                'total_carried_over' => $collection->sum('carried_over_days'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($balance) {
                    return [
                        'id' => $balance->id,
                        'employee' => $balance->employee ? $balance->employee->first_name . ' ' . $balance->employee->last_name : 'N/A',
                        'employee_code' => $balance->employee->employee_code ?? 'N/A',
                        'leave_type' => $balance->leaveType->leave_name ?? 'N/A',
                        'leave_code' => $balance->leaveType->leave_code ?? 'N/A',
                        'year' => $balance->year,
                        'entitled' => $balance->entitled_days,
                        'used' => $balance->used_days,
                        'carried_over' => $balance->carried_over_days,
                        'remaining' => $balance->remaining_days,
                        'last_updated' => $balance->last_updated ? $balance->last_updated->format('Y-m-d') : null,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $balances->total(),
                        'per_page' => $balances->perPage(),
                        'current_page' => $balances->currentPage(),
                        'last_page' => $balances->lastPage(),
                    ],
                ]);
            }

            $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
            $leaveTypes = LeaveType::select('id', 'leave_name', 'leave_code')->get();
            $years = LeaveBalance::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
            return view('admin.reports.leave-balances', compact('balances', 'stats', 'employees', 'leaveTypes', 'years'));
        } catch (\Throwable $e) {
            Log::error('Leave balances report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load leave balances report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load leave balances report.');
        }
    }

      public function leaveRequests(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = LeaveRequest::with([
                'employee:id,first_name,last_name,employee_code',
                'leaveType:id,leave_name,leave_code',
                'approvedBy:id,name',
                'rejectedBy:id,name',
                'requestedBy:id,name'
            ]);

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }
            if ($request->filled('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $requests = $query->latest()->paginate($perPage)->appends($request->query());
            $collection = $requests->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'total' => $requests->total(),
                'pending' => (clone $statsQuery)->where('approval_status', 'Pending')->count(),
                'approved' => (clone $statsQuery)->where('approval_status', 'Approved')->count(),
                'rejected' => (clone $statsQuery)->where('approval_status', 'Rejected')->count(),
                'cancelled' => (clone $statsQuery)->where('approval_status', 'Cancelled')->count(),
                'total_days' => (clone $statsQuery)->sum('total_days'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($request) {
                    // Safe date formatting
                    $startDate = $request->start_date;
                    if ($startDate instanceof \Carbon\Carbon) {
                        $startDate = $startDate->format('Y-m-d');
                    } elseif (is_string($startDate)) {
                        $startDate = date('Y-m-d', strtotime($startDate));
                    } else {
                        $startDate = null;
                    }

                    $endDate = $request->end_date;
                    if ($endDate instanceof \Carbon\Carbon) {
                        $endDate = $endDate->format('Y-m-d');
                    } elseif (is_string($endDate)) {
                        $endDate = date('Y-m-d', strtotime($endDate));
                    } else {
                        $endDate = null;
                    }

                    $approvedAt = $request->approved_at;
                    if ($approvedAt instanceof \Carbon\Carbon) {
                        $approvedAt = $approvedAt->format('Y-m-d');
                    } elseif (is_string($approvedAt)) {
                        $approvedAt = date('Y-m-d', strtotime($approvedAt));
                    } else {
                        $approvedAt = null;
                    }

                    $requestedAt = $request->requested_at;
                    if ($requestedAt instanceof \Carbon\Carbon) {
                        $requestedAt = $requestedAt->format('Y-m-d');
                    } elseif (is_string($requestedAt)) {
                        $requestedAt = date('Y-m-d', strtotime($requestedAt));
                    } else {
                        $requestedAt = null;
                    }

                    return [
                        'id' => $request->id,
                        'employee' => $request->employee ? $request->employee->first_name . ' ' . $request->employee->last_name : 'N/A',
                        'employee_code' => $request->employee->employee_code ?? 'N/A',
                        'leave_type' => $request->leaveType->leave_name ?? 'N/A',
                        'leave_code' => $request->leaveType->leave_code ?? 'N/A',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_days' => $request->total_days ?? 0,
                        'reason' => $request->reason,
                        'status' => $request->approval_status ?? 'Pending',
                        'approved_by' => $request->approvedBy->name ?? 'N/A',
                        'approved_at' => $approvedAt,
                        'rejected_by' => $request->rejectedBy->name ?? 'N/A',
                        'rejection_reason' => $request->rejection_reason,
                        'requested_at' => $requestedAt,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $requests->total(),
                        'per_page' => $requests->perPage(),
                        'current_page' => $requests->currentPage(),
                        'last_page' => $requests->lastPage(),
                    ],
                ]);
            }

            $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
            $leaveTypes = LeaveType::select('id', 'leave_name', 'leave_code')->get();
            $statuses = ['Pending', 'Approved', 'Rejected', 'Cancelled'];
            return view('admin.reports.leave-requests', compact('requests', 'stats', 'employees', 'leaveTypes', 'statuses'));
        } catch (\Throwable $e) {
            Log::error('Leave requests report error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load leave requests: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load leave requests report.');
        }
    }

    /**
     * ============================================
     * PAYROLL REPORTS
     * ============================================
     */

    public function payroll(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $companies = Company::select('id', 'name')->get();
            $periods = PayrollPeriod::select('id', 'period_name', 'period_code')->get();
            $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();

            return view('admin.reports.payroll', compact('companies', 'periods', 'employees'));
        } catch (\Throwable $e) {
            Log::error('Payroll report page error', ['message' => $e->getMessage()]);
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load payroll report page.');
        }
    }

    /**
     * ============================================
     * PAYROLL API METHODS (for AJAX)
     * ============================================
     */

    public function apiPayrollSummary(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $dateFrom = $request->date_from ?? now()->startOfYear()->format('Y-m-d');
            $dateTo = $request->date_to ?? now()->format('Y-m-d');

            $stats = [
                'total_periods' => PayrollPeriod::count(),
                'open_periods' => PayrollPeriod::where('status', 'Open')->where('is_locked', false)->count(),
                'processing_periods' => PayrollPeriod::where('status', 'Processing')->count(),
                'approved_periods' => PayrollPeriod::where('status', 'Approved')->count(),
                'locked_periods' => PayrollPeriod::where('is_locked', true)->count(),
                
                'total_calculations' => PayrollCalculation::count(),
                'pending_calculations' => PayrollCalculation::where('payment_status', 'Pending')->count(),
                'verified_calculations' => PayrollCalculation::where('payment_status', 'Verified')->count(),
                'paid_calculations' => PayrollCalculation::where('payment_status', 'Paid')->count(),
                
                'total_payslips' => Payslip::count(),
                'total_loans' => EmployeeLoan::count(),
                'active_loans' => EmployeeLoan::where('status', 'Active')->count(),
                'completed_loans' => EmployeeLoan::where('status', 'Completed')->count(),
                
                'total_overtime' => EmployeeOvertime::count(),
                'pending_overtime' => EmployeeOvertime::where('status', 'Pending')->count(),
                'approved_overtime' => EmployeeOvertime::where('status', 'Approved')->count(),
                
                'total_adjustments' => PayrollAdjustment::count(),
                'pending_adjustments' => PayrollAdjustment::whereNull('approved_at')->count(),
                'approved_adjustments' => PayrollAdjustment::whereNotNull('approved_at')->count(),
                
                'total_rates' => PayrollRate::count(),
                'active_rates' => PayrollRate::where('is_active', true)->count(),
            ];

            $monthlyTrend = DB::table('payroll_calculations')
                ->select(
                    DB::raw('DATE_FORMAT(calculated_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(net_salary) as total_net'),
                    DB::raw('SUM(total_earnings) as total_earnings'),
                    DB::raw('SUM(total_deductions) as total_deductions')
                )
                ->where('is_calculated', true)
                ->whereBetween('calculated_at', [$dateFrom, $dateTo])
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            $recentCalculations = PayrollCalculation::with(['employee', 'period'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($calc) {
                    return [
                        'id' => $calc->id,
                        'employee' => $calc->employee ? $calc->employee->first_name . ' ' . $calc->employee->last_name : 'N/A',
                        'period' => $calc->period->period_name ?? 'N/A',
                        'net_salary' => $calc->net_salary,
                        'status' => $calc->payment_status,
                        'calculated_at' => $calc->calculated_at ? $calc->calculated_at->format('Y-m-d') : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'monthly_trend' => $monthlyTrend,
                'recent_calculations' => $recentCalculations,
            ]);
        } catch (\Throwable $e) {
            Log::error('Payroll summary API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load summary.'], 500);
        }
    }

    public function apiPayrollPeriods(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = PayrollPeriod::with(['company:id,name'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('date_from')) {
                $query->whereDate('start_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('end_date', '<=', $request->date_to);
            }
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $data = $query->limit(100)->get()->map(function($period) {
                return [
                    'id' => $period->id,
                    'period_code' => $period->period_code,
                    'period_name' => $period->period_name,
                    'company' => $period->company->name ?? 'N/A',
                    'start_date' => $period->start_date->format('Y-m-d'),
                    'end_date' => $period->end_date->format('Y-m-d'),
                    'pay_date' => $period->pay_date->format('Y-m-d'),
                    'status' => $period->status,
                    'is_locked' => $period->is_locked,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Payroll periods API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load periods.'], 500);
        }
    }

    public function apiPayrollCalculations(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = PayrollCalculation::with(['employee', 'period'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('period_id')) {
                $query->where('period_id', $request->period_id);
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            $data = $query->limit(100)->get()->map(function($calc) {
                return [
                    'id' => $calc->id,
                    'employee' => $calc->employee ? $calc->employee->first_name . ' ' . $calc->employee->last_name : 'N/A',
                    'employee_code' => $calc->employee->employee_code ?? 'N/A',
                    'period' => $calc->period->period_name ?? 'N/A',
                    'basic_salary' => $calc->basic_salary,
                    'total_earnings' => $calc->total_earnings,
                    'total_deductions' => $calc->total_deductions,
                    'net_salary' => $calc->net_salary,
                    'payment_status' => $calc->payment_status,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Payroll calculations API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load calculations.'], 500);
        }
    }

    public function apiPayrollPayslips(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = Payslip::with(['employee', 'period'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('period_id')) {
                $query->where('period_id', $request->period_id);
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $data = $query->limit(100)->get()->map(function($payslip) {
                return [
                    'id' => $payslip->id,
                    'payslip_number' => $payslip->payslip_number,
                    'employee' => $payslip->employee ? $payslip->employee->first_name . ' ' . $payslip->employee->last_name : 'N/A',
                    'period' => $payslip->period->period_name ?? 'N/A',
                    'issue_date' => $payslip->issue_date->format('Y-m-d'),
                    'is_downloaded' => $payslip->is_downloaded,
                    'emailed' => $payslip->emailed,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Payslips API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load payslips.'], 500);
        }
    }

    public function apiPayrollLoans(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = EmployeeLoan::with(['employee'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $data = $query->limit(100)->get()->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'employee' => $loan->employee ? $loan->employee->first_name . ' ' . $loan->employee->last_name : 'N/A',
                    'loan_type' => $loan->loan_type,
                    'amount' => $loan->amount,
                    'monthly_installment' => $loan->monthly_installment,
                    'remaining_balance' => $loan->remaining_balance,
                    'status' => $loan->status,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Loans API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load loans.'], 500);
        }
    }

    public function apiPayrollOvertime(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = EmployeeOvertime::with(['employee', 'period'])
                ->orderBy('overtime_date', 'desc');

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('period_id')) {
                $query->where('period_id', $request->period_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $data = $query->limit(100)->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'employee' => $item->employee ? $item->employee->first_name . ' ' . $item->employee->last_name : 'N/A',
                    'period' => $item->period->period_name ?? 'N/A',
                    'overtime_date' => $item->overtime_date->format('Y-m-d'),
                    'hours_worked' => $item->hours_worked,
                    'rate_multiplier' => $item->rate_multiplier,
                    'amount' => $item->amount,
                    'status' => $item->status,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Overtime API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load overtime.'], 500);
        }
    }

    public function apiPayrollAdjustments(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = PayrollAdjustment::with(['employee', 'period'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('period_id')) {
                $query->where('period_id', $request->period_id);
            }
            if ($request->filled('adjustment_type')) {
                $query->where('adjustment_type', $request->adjustment_type);
            }

            $data = $query->limit(100)->get()->map(function($adj) {
                return [
                    'id' => $adj->id,
                    'employee' => $adj->employee ? $adj->employee->first_name . ' ' . $adj->employee->last_name : 'N/A',
                    'period' => $adj->period->period_name ?? 'N/A',
                    'adjustment_type' => $adj->adjustment_type,
                    'description' => $adj->description,
                    'amount' => $adj->amount,
                    'is_taxable' => $adj->is_taxable,
                    'status' => $adj->status ?? 'Pending',
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Adjustments API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load adjustments.'], 500);
        }
    }

    public function apiPayrollRates(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $query = PayrollRate::orderBy('rate_type')->orderBy('effective_from', 'desc');

            if ($request->filled('rate_type')) {
                $query->where('rate_type', $request->rate_type);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $data = $query->limit(100)->get()->map(function($rate) {
                return [
                    'id' => $rate->id,
                    'rate_type' => $rate->rate_type,
                    'rate_value' => $rate->rate_value,
                    'income_from' => $rate->income_from,
                    'income_to' => $rate->income_to,
                    'effective_from' => $rate->effective_from->format('Y-m-d'),
                    'effective_to' => $rate->effective_to ? $rate->effective_to->format('Y-m-d') : null,
                    'is_active' => $rate->is_active,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Rates API error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to load rates.'], 500);
        }
    }

    /**
     * ============================================
     * SALES REPORTS
     * ============================================
     */

    public function sales(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Sale::with([
                'customer:id,name,email,phone',
                'branch:id,name',
                'user:id,name',
                'currency:id,code,symbol'
            ]);

            if ($request->filled('date_from')) {
                $query->whereDate('sale_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('sale_date', '<=', $request->date_to);
            }
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $sales = $query->latest('sale_date')->paginate($perPage)->appends($request->query());
            $collection = $sales->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'total' => $sales->total(),
                'total_amount' => (clone $statsQuery)->sum('total'),
                'total_paid' => (clone $statsQuery)->sum('amount_paid'),
                'total_discount' => (clone $statsQuery)->sum('discount'),
                'total_tax' => (clone $statsQuery)->sum('tax'),
                'pending' => (clone $statsQuery)->where('payment_status', 'pending')->count(),
                'partial' => (clone $statsQuery)->where('payment_status', 'partial')->count(),
                'paid' => (clone $statsQuery)->where('payment_status', 'paid')->count(),
                'cancelled' => (clone $statsQuery)->where('payment_status', 'cancelled')->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'customer' => $sale->customer->name ?? 'N/A',
                        'branch' => $sale->branch->name ?? 'N/A',
                        'user' => $sale->user->name ?? 'N/A',
                        'sale_date' => $sale->sale_date->format('Y-m-d'),
                        'due_date' => $sale->due_date ? $sale->due_date->format('Y-m-d') : null,
                        'subtotal' => $sale->subtotal,
                        'discount' => $sale->discount,
                        'tax' => $sale->tax,
                        'total' => $sale->total,
                        'paid' => $sale->amount_paid,
                        'balance' => $sale->total - $sale->amount_paid,
                        'payment_status' => $sale->payment_status,
                        'payment_method' => $sale->payment_method,
                        'currency' => $sale->currency->code ?? 'USD',
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $sales->total(),
                        'per_page' => $sales->perPage(),
                        'current_page' => $sales->currentPage(),
                        'last_page' => $sales->lastPage(),
                    ],
                ]);
            }

            $customers = Customer::select('id', 'name')->get();
            $branches = Branch::select('id', 'name')->get();
            $paymentStatuses = ['pending', 'partial', 'paid', 'cancelled'];
            $paymentMethods = ['cash', 'card', 'mobile', 'credit', 'bank_transfer', 'cheque'];
            return view('admin.reports.sales', compact('sales', 'stats', 'customers', 'branches', 'paymentStatuses', 'paymentMethods'));
        } catch (\Throwable $e) {
            Log::error('Sales report error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load sales report: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load sales report.');
        }
    }

    public function customers(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Customer::withCount(['sales']);

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%");
                });
            }

            $customers = $query->latest()->paginate(50)->appends($request->query());
            $collection = $customers->getCollection();

            $stats = [
                'total' => Customer::count(),
                'active' => Customer::where('is_active', 1)->count(),
                'inactive' => Customer::where('is_active', 0)->count(),
                'individual' => Customer::where('type', 'individual')->count(),
                'company' => Customer::where('type', 'company')->count(),
                'total_credit' => Customer::sum('credit_limit'),
                'total_balance' => Customer::sum('balance'),
                'total_sales' => Sale::count(),
                'total_sales_value' => Sale::sum('total'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                        'type' => $customer->type,
                        'tax_id' => $customer->tax_id,
                        'credit_limit' => $customer->credit_limit,
                        'balance' => $customer->balance,
                        'sales_count' => $customer->sales_count,
                        'is_active' => $customer->is_active,
                        'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d') : null,
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $customers->total(),
                        'per_page' => $customers->perPage(),
                        'current_page' => $customers->currentPage(),
                        'last_page' => $customers->lastPage(),
                    ],
                ]);
            }

            return view('admin.reports.customers', compact('customers', 'stats'));
        } catch (\Throwable $e) {
            Log::error('Customers report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load customers report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load customers report.');
        }
    }

    public function quotations(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Quotation::with([
                'customer:id,name,email,phone',
                'user:id,name',
                'currency:id,code,symbol',
                'warehouse:id,name'
            ]);

            if ($request->filled('date_from')) {
                $query->whereDate('quotation_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('quotation_date', '<=', $request->date_to);
            }
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $quotations = $query->latest('quotation_date')->paginate($perPage)->appends($request->query());
            $collection = $quotations->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'total' => $quotations->total(),
                'total_amount' => (clone $statsQuery)->sum('total'),
                'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
                'sent' => (clone $statsQuery)->where('status', 'sent')->count(),
                'accepted' => (clone $statsQuery)->where('status', 'accepted')->count(),
                'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
                'expired' => (clone $statsQuery)->where('status', 'expired')->count(),
                'converted' => (clone $statsQuery)->where('status', 'converted')->count(),
                'conversion_rate' => $quotations->total() > 0 
                    ? round((clone $statsQuery)->where('status', 'converted')->count() / $quotations->total() * 100, 1) 
                    : 0,
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($quotation) {
                    $daysValid = $quotation->valid_until ? $quotation->quotation_date->diffInDays($quotation->valid_until) : null;
                    return [
                        'id' => $quotation->id,
                        'quotation_number' => $quotation->quotation_number,
                        'customer' => $quotation->customer->name ?? 'N/A',
                        'user' => $quotation->user->name ?? 'N/A',
                        'warehouse' => $quotation->warehouse->name ?? 'N/A',
                        'quotation_date' => $quotation->quotation_date->format('Y-m-d'),
                        'valid_until' => $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : null,
                        'days_valid' => $daysValid,
                        'subtotal' => $quotation->subtotal,
                        'discount' => $quotation->discount,
                        'tax' => $quotation->tax,
                        'total' => $quotation->total,
                        'status' => $quotation->status,
                        'currency' => $quotation->currency->code ?? 'USD',
                        'converted_to_sale' => $quotation->converted_to_sale_id,
                        'created_at' => $quotation->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $quotations->total(),
                        'per_page' => $quotations->perPage(),
                        'current_page' => $quotations->currentPage(),
                        'last_page' => $quotations->lastPage(),
                    ],
                ]);
            }

            $customers = Customer::select('id', 'name')->get();
            $statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'];
            return view('admin.reports.quotations', compact('quotations', 'stats', 'customers', 'statuses'));
        } catch (\Throwable $e) {
            Log::error('Quotations report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load quotations report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load quotations report.');
        }
    }

    /**
     * ============================================
     * PURCHASE REPORTS
     * ============================================
     */

    public function purchaseOrders(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = PurchaseOrder::with(['supplier:id,name', 'user:id,name', 'currency:id,code,symbol']);

            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            $perPage = min((int) $request->input('per_page', 50), 200);
            $purchaseOrders = $query->latest('order_date')->paginate($perPage)->appends($request->query());
            $collection = $purchaseOrders->getCollection();

            $statsQuery = clone $query;
            $stats = [
                'total' => $purchaseOrders->total(),
                'total_amount' => (clone $statsQuery)->sum('total_amount'),
                'paid_amount' => (clone $statsQuery)->sum('paid_amount'),
                'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
                'ordered' => (clone $statsQuery)->where('status', 'ordered')->count(),
                'received' => (clone $statsQuery)->where('status', 'received')->count(),
                'invoiced' => (clone $statsQuery)->where('status', 'invoiced')->count(),
                'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
                'cancelled' => (clone $statsQuery)->where('status', 'cancelled')->count(),
                'pending_payment' => (clone $statsQuery)->where('payment_status', 'pending')->count(),
                'partial_payment' => (clone $statsQuery)->where('payment_status', 'partial')->count(),
                'full_payment' => (clone $statsQuery)->where('payment_status', 'paid')->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'supplier' => $po->supplier->name ?? 'N/A',
                        'user' => $po->user->name ?? 'N/A',
                        'total_amount' => $po->total_amount ?? 0,
                        'paid_amount' => $po->paid_amount ?? 0,
                        'balance' => ($po->total_amount ?? 0) - ($po->paid_amount ?? 0),
                        'status' => $po->status,
                        'payment_status' => $po->payment_status,
                        'order_date' => $po->order_date->format('Y-m-d'),
                        'expected_delivery' => $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : null,
                        'actual_delivery' => $po->actual_delivery_date ? $po->actual_delivery_date->format('Y-m-d') : null,
                        'currency' => $po->currency->code ?? 'USD',
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $purchaseOrders->total(),
                        'per_page' => $purchaseOrders->perPage(),
                        'current_page' => $purchaseOrders->currentPage(),
                        'last_page' => $purchaseOrders->lastPage(),
                    ],
                ]);
            }

            $suppliers = Supplier::select('id', 'name')->get();
            $statuses = ['draft', 'ordered', 'received', 'invoiced', 'paid', 'cancelled'];
            $paymentStatuses = ['pending', 'partial', 'paid'];
            return view('admin.reports.purchase-orders', compact('purchaseOrders', 'stats', 'suppliers', 'statuses', 'paymentStatuses'));
        } catch (\Throwable $e) {
            Log::error('Purchase orders report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load purchase orders report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load purchase orders report.');
        }
    }

    public function suppliers(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = Supplier::withCount(['purchaseOrders']);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%");
                });
            }

            $suppliers = $query->latest()->paginate(50)->appends($request->query());
            $collection = $suppliers->getCollection();

            $stats = [
                'total' => Supplier::count(),
                'active' => Supplier::where('is_active', 1)->count(),
                'inactive' => Supplier::where('is_active', 0)->count(),
                'total_purchase_orders' => PurchaseOrder::count(),
                'total_purchase_value' => PurchaseOrder::sum('total_amount'),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'contact_person' => $supplier->contact_person,
                        'email' => $supplier->email,
                        'phone' => $supplier->phone,
                        'address' => $supplier->address,
                        'tax_id' => $supplier->tax_id,
                        'registration_number' => $supplier->registration_number,
                        'purchase_orders_count' => $supplier->purchase_orders_count,
                        'is_active' => $supplier->is_active,
                        'created_at' => $supplier->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $suppliers->total(),
                        'per_page' => $suppliers->perPage(),
                        'current_page' => $suppliers->currentPage(),
                        'last_page' => $suppliers->lastPage(),
                    ],
                ]);
            }

            return view('admin.reports.suppliers', compact('suppliers', 'stats'));
        } catch (\Throwable $e) {
            Log::error('Suppliers report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load suppliers report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load suppliers report.');
        }
    }

    /**
     * ============================================
     * USERS REPORT
     * ============================================
     */

    public function users(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            abort(403, 'You do not have permission to view reports.');
        }

        try {
            $query = User::with(['department:id,name']);

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('employee_id', 'like', "%{$request->search}%");
                });
            }

            $users = $query->latest()->paginate(50)->appends($request->query());
            $collection = $users->getCollection();

            $stats = [
                'total' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
                'with_department' => User::whereNotNull('department_id')->count(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                $data = $collection->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'employee_id' => $user->employee_id,
                        'department' => $user->department->name ?? 'N/A',
                        'is_verified' => $user->email_verified_at ? true : false,
                        'verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : null,
                        'created_at' => $user->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                    ],
                ]);
            }

            $departments = Department::select('id', 'name')->get();
            return view('admin.reports.users', compact('users', 'stats', 'departments'));
        } catch (\Throwable $e) {
            Log::error('Users report error', ['message' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to load users report: ' . $e->getMessage()], 500);
            }
            return redirect()->route('admin.reports.index')->with('error', 'Failed to load users report.');
        }
    }

    /**
     * ============================================
     * PROFIT & LOSS REPORT
     * ============================================
     */

     /**
 * ============================================
 * PROFIT & LOSS REPORT
 * ============================================
 */
public function profitLoss(Request $request)
{
    if (!auth()->user()->can('report.view')) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        abort(403, 'You do not have permission to view reports.');
    }

    try {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $currencyId = $request->currency_id ?? null;

        // Get all active currencies for filter
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        
        // Set default currency to TZS if no currency selected
        $defaultCurrency = Currency::where('code', 'TZS')->first();
        if (!$currencyId && $defaultCurrency) {
            $currencyId = $defaultCurrency->id;
        }

        // Get selected currency symbol
        $selectedCurrency = Currency::find($currencyId);
        $currencySymbol = $selectedCurrency ? $selectedCurrency->symbol : 'TSh';

        // ============================================
        // 1. TOTAL SALES (Income)
        // ============================================
        $salesQuery = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('payment_status', '!=', 'cancelled');

        if ($currencyId) {
            $salesQuery->where('currency_id', $currencyId);
        }

        $totalSales = (clone $salesQuery)->sum('total');
        $totalSalesCount = (clone $salesQuery)->count();
        $totalSalesDiscount = (clone $salesQuery)->sum('discount');
        $totalSalesTax = (clone $salesQuery)->sum('tax');
        $totalSalesShipping = (clone $salesQuery)->sum('shipping_cost');
        
        // Net Sales = Total Sales - Discounts - Tax - Shipping
        $netSales = $totalSales - $totalSalesDiscount - $totalSalesTax - $totalSalesShipping;

        // ============================================
        // 2. SALES PAYMENTS (Amount Received from Customers)
        // ============================================
        $salesPaymentsQuery = SalesPayment::whereBetween('payment_date', [$dateFrom, $dateTo]);
        
        if ($currencyId) {
            $salesPaymentsQuery->where('currency_id', $currencyId);
        }
        
        $totalSalesPayments = (clone $salesPaymentsQuery)->sum('amount');
        $totalSalesPaymentsCount = (clone $salesPaymentsQuery)->count();

        // ============================================
        // 3. TOTAL PURCHASES (Cost of Goods Sold)
        // ============================================
        $purchaseQuery = PurchaseOrder::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'draft');

        if ($currencyId) {
            $purchaseQuery->where('currency_id', $currencyId);
        }

        $totalPurchases = (clone $purchaseQuery)->sum('total_amount');
        $totalPurchasesCount = (clone $purchaseQuery)->count();

        // ============================================
        // 4. PURCHASE PAYMENTS (Amount Paid to Suppliers)
        // ============================================
        $purchasePaymentsQuery = PurchasePayment::whereBetween('payment_date', [$dateFrom, $dateTo]);
        
        if ($currencyId) {
            $purchasePaymentsQuery->where('currency_id', $currencyId);
        }
        
        $totalPurchasePayments = (clone $purchasePaymentsQuery)->sum('amount');
        $totalPurchasePaymentsCount = (clone $purchasePaymentsQuery)->count();

        // ============================================
        // 5. OPERATING EXPENSES
        // ============================================
        
        // Payroll Expenses
        $totalPayroll = PayrollCalculation::whereBetween('calculated_at', [$dateFrom, $dateTo])
            ->where('is_calculated', true)
            ->sum('net_salary');

        $totalTaxPaid = PayrollCalculation::whereBetween('calculated_at', [$dateFrom, $dateTo])
            ->where('is_calculated', true)
            ->sum('paye_tax');

        $totalNSSF = PayrollCalculation::whereBetween('calculated_at', [$dateFrom, $dateTo])
            ->where('is_calculated', true)
            ->sum('nssf_employee');

        $totalPayrollExpenses = $totalPayroll + $totalTaxPaid + $totalNSSF;

        // ============================================
        // 6. CALCULATE PROFIT/LOSS
        // ============================================
        
        // Gross Profit = Net Sales - Cost of Goods Sold
        $grossProfit = $netSales - $totalPurchases;
        
        // Total Operating Expenses
        $totalOperatingExpenses = $totalPayrollExpenses;
        
        // Net Profit = Gross Profit - Total Operating Expenses
        $netProfit = $grossProfit - $totalOperatingExpenses;
        
        // Profit Margin = (Net Profit / Net Sales) * 100
        $profitMargin = $netSales > 0 ? ($netProfit / $netSales) * 100 : 0;

        // ============================================
        // 7. SUMMARY
        // ============================================
        $summary = [
            'currency_symbol' => $currencySymbol,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'sales' => [
                'total_sales' => $totalSales,
                'sales_count' => $totalSalesCount,
                'discounts' => $totalSalesDiscount,
                'tax' => $totalSalesTax,
                'shipping' => $totalSalesShipping,
                'net_sales' => $netSales,
            ],
            'sales_payments' => [
                'total' => $totalSalesPayments,
                'count' => $totalSalesPaymentsCount,
            ],
            'purchases' => [
                'total_purchases' => $totalPurchases,
                'purchases_count' => $totalPurchasesCount,
            ],
            'purchase_payments' => [
                'total' => $totalPurchasePayments,
                'count' => $totalPurchasePaymentsCount,
            ],
            'operating_expenses' => [
                'payroll' => $totalPayroll,
                'tax_paid' => $totalTaxPaid,
                'nssf' => $totalNSSF,
                'total' => $totalOperatingExpenses,
            ],
            'profit_loss' => [
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
            ]
        ];

        // ============================================
        // 8. MONTHLY TREND
        // ============================================
        $monthlyData = [];
        
        // Get all months in range
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        
        while ($start <= $end) {
            $monthLabel = $start->format('M Y');
            
            // Sales for this month
            $monthSalesQuery = clone $salesQuery;
            $monthSales = $monthSalesQuery->whereYear('sale_date', $start->year)
                ->whereMonth('sale_date', $start->month)
                ->sum('total');
            
            // Sales Payments for this month
            $monthSalesPaymentsQuery = clone $salesPaymentsQuery;
            $monthSalesPayments = $monthSalesPaymentsQuery->whereYear('payment_date', $start->year)
                ->whereMonth('payment_date', $start->month)
                ->sum('amount');
            
            // Purchases for this month
            $monthPurchaseQuery = clone $purchaseQuery;
            $monthPurchases = $monthPurchaseQuery->whereYear('order_date', $start->year)
                ->whereMonth('order_date', $start->month)
                ->sum('total_amount');
            
            // Purchase Payments for this month
            $monthPurchasePaymentsQuery = clone $purchasePaymentsQuery;
            $monthPurchasePayments = $monthPurchasePaymentsQuery->whereYear('payment_date', $start->year)
                ->whereMonth('payment_date', $start->month)
                ->sum('amount');
            
            // Payroll for this month
            $monthPayroll = PayrollCalculation::whereBetween('calculated_at', [
                $start->copy()->startOfMonth(),
                $start->copy()->endOfMonth()
            ])->where('is_calculated', true)->sum('net_salary');
            
            $monthlyData[] = [
                'month' => $monthLabel,
                'sales' => $monthSales,
                'sales_payments' => $monthSalesPayments,
                'purchases' => $monthPurchases,
                'purchase_payments' => $monthPurchasePayments,
                'payroll' => $monthPayroll,
                'profit' => $monthSales - $monthPurchases - $monthPayroll,
            ];
            
            $start->addMonth();
        }

        // ============================================
        // 9. TOP CUSTOMERS
        // ============================================
        $topCustomers = (clone $salesQuery)
            ->select('customer_id', DB::raw('SUM(total) as total_amount, COUNT(*) as count'))
            ->with('customer:id,name')
            ->groupBy('customer_id')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        // ============================================
        // 10. PAYMENT METHODS
        // ============================================
        $paymentMethods = (clone $salesQuery)
            ->select('payment_method', DB::raw('SUM(total) as total_amount, COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary,
                'monthly_data' => $monthlyData,
                'top_customers' => $topCustomers,
                'payment_methods' => $paymentMethods,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'currency_id' => $currencyId,
                ]
            ]);
        }

        return view('admin.reports.profit-loss', compact(
            'summary', 'monthlyData', 'topCustomers', 'paymentMethods',
            'dateFrom', 'dateTo', 'currencies', 'currencyId', 'currencySymbol'
        ));
    } catch (\Throwable $e) {
        Log::error('Profit & Loss report error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load profit & loss report: ' . $e->getMessage()
            ], 500);
        }
        return redirect()->route('admin.reports.index')->with('error', 'Failed to load profit & loss report.');
    }
}

    /**
     * ============================================
     * EXPORT FUNCTIONS
     * ============================================
     */

    public function export(Request $request)
    {
        if (!auth()->user()->can('report.view')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'type' => 'required|in:inventory,low_stock,warranty,companies,branches,departments,warehouses,categories,employees,attendance,leave_types,leave_balances,leave_requests,payroll_periods,payroll_calculations,sales,customers,purchase_orders,suppliers,quotations,users,profit_loss',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        $exportData = [];
        $type = $request->type;
        $exportRequest = clone $request;
        $exportRequest->merge(['per_page' => 5000]);

        try {
            $methodMap = [
                'inventory' => 'inventory',
                'low_stock' => 'lowStock',
                'warranty' => 'warrantyExpiry',
                'companies' => 'companies',
                'branches' => 'branches',
                'departments' => 'departments',
                'warehouses' => 'warehouses',
                'categories' => 'categories',
                'employees' => 'employees',
                'attendance' => 'attendance',
                'leave_types' => 'leaveTypes',
                'leave_balances' => 'leaveBalances',
                'leave_requests' => 'leaveRequests',
                'payroll_periods' => 'apiPayrollPeriods',
                'payroll_calculations' => 'apiPayrollCalculations',
                'sales' => 'sales',
                'customers' => 'customers',
                'purchase_orders' => 'purchaseOrders',
                'suppliers' => 'suppliers',
                'quotations' => 'quotations',
                'users' => 'users',
                'profit_loss' => 'profitLoss',
            ];

            if (!isset($methodMap[$type])) {
                return response()->json(['success' => false, 'error' => 'Invalid export type.'], 422);
            }

            $response = $this->{$methodMap[$type]}($exportRequest);
            $data = $response->getData()->data ?? [];

            if (empty($data)) {
                return response()->json(['success' => false, 'error' => 'No data to export.'], 404);
            }

            $headers = array_keys((array) $data[0]);
            $exportRows = [$headers];

            foreach ($data as $item) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $item->{$header} ?? 'N/A';
                }
                $exportRows[] = $row;
            }

            return response()->json([
                'success' => true,
                'data' => $exportRows,
                'count' => count($exportRows) - 1,
                'type' => $type,
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Report export failed', [
                'type' => $type,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to export report.',
            ], 500);
        }
    }

    /**
     * ============================================
     * API HELPER METHODS
     * ============================================
     */

    public function getInventoryData(Request $request) { return $this->inventory($request); }
    public function getLowStockData(Request $request) { return $this->lowStock($request); }
    public function getWarrantyData(Request $request) { return $this->warrantyExpiry($request); }
    public function getCompaniesData(Request $request) { return $this->companies($request); }
    public function getBranchesData(Request $request) { return $this->branches($request); }
    public function getDepartmentsData(Request $request) { return $this->departments($request); }
    public function getWarehousesData(Request $request) { return $this->warehouses($request); }
    public function getCategoriesData(Request $request) { return $this->categories($request); }
    public function getEmployeesData(Request $request) { return $this->employees($request); }
    public function getAttendanceData(Request $request) { return $this->attendance($request); }
    public function getLeaveTypesData(Request $request) { return $this->leaveTypes($request); }
    public function getLeaveBalancesData(Request $request) { return $this->leaveBalances($request); }
    public function getLeaveRequestsData(Request $request) { return $this->leaveRequests($request); }
    public function getSalesData(Request $request) { return $this->sales($request); }
    public function getCustomersData(Request $request) { return $this->customers($request); }
    public function getQuotationsData(Request $request) { return $this->quotations($request); }
    public function getPurchaseOrdersData(Request $request) { return $this->purchaseOrders($request); }
    public function getSuppliersData(Request $request) { return $this->suppliers($request); }
    public function getUsersData(Request $request) { return $this->users($request); }
    public function getProfitLossData(Request $request) { return $this->profitLoss($request); }
}
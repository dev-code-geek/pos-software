<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SpecificationAmendmentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\LeaveBalanceController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\PurchasePaymentController;
use App\Http\Controllers\Admin\SalesPaymentController  ;
use App\Http\Controllers\Admin\CreditNoteController;
use App\Http\Controllers\Admin\ExchangeRateController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\AssetRequestController as AdminAssetRequestController;
use App\Http\Controllers\Admin\DirectRequestController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\DebitNoteController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SoftwareLicenseUsageController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\ProcurementController;
use App\Http\Controllers\Admin\InspectionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\User\AssetRequestController as UserAssetRequestController;
use App\Http\Controllers\User\ReportIssueController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\User\AssignmentController as UserAssignmentController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\AssetRequestController as ApiAssetRequestController;
use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================
// AUTHENTICATION ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';

// ============================================
// AUTHENTICATED ROUTES (All authenticated users)
// ============================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ============================================
    // DASHBOARD REDIRECT
    // ============================================
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    // ============================================
    // PROFILE ROUTES
    // ============================================
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ============================================
    // USER ROUTES (All authenticated users)
    // ============================================
    Route::prefix('user')->name('user.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        
        // Asset Requests
        Route::prefix('asset-requests')->name('asset-requests.')->group(function () {
            Route::get('/', [UserAssetRequestController::class, 'index'])->name('index');
            Route::get('/create', [UserAssetRequestController::class, 'create'])->name('create');
            Route::post('/', [UserAssetRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [UserAssetRequestController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UserAssetRequestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserAssetRequestController::class, 'update'])->name('update');
            Route::post('/{id}/cancel', [UserAssetRequestController::class, 'cancel'])->name('cancel');
        });
        
        // Assignments
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [UserAssignmentController::class, 'index'])->name('index');
            Route::get('/history', [UserAssignmentController::class, 'history'])->name('history');
            Route::get('/stats', [UserAssignmentController::class, 'getStats'])->name('stats');
            Route::get('/upcoming', [UserAssignmentController::class, 'upcomingReturns'])->name('upcoming');
            Route::get('/download', [UserAssignmentController::class, 'downloadReport'])->name('download');
            Route::get('/widget', [UserAssignmentController::class, 'widget'])->name('widget');
            Route::get('/{id}', [UserAssignmentController::class, 'show'])->name('show');
            Route::post('/{id}/return', [UserAssignmentController::class, 'returnAsset'])->name('return');
            Route::post('/{id}/extend', [UserAssignmentController::class, 'extendReturnDate'])->name('extend');
        });
        
        // My Assignments (backward compatibility)
        Route::get('my-assignments', [UserDashboardController::class, 'myAssignments'])->name('my-assignments');
        Route::get('my-assignments/history', [UserDashboardController::class, 'assignmentHistory'])->name('my-assignments.history');
        
        // Products (view only)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [UserProductController::class, 'index'])->name('index');
            Route::get('/search', [UserProductController::class, 'search'])->name('search');
            Route::get('/by-category/{categoryId}', [UserProductController::class, 'byCategory'])->name('by-category');
            Route::get('/{id}', [UserProductController::class, 'show'])->name('show');
        });

        // Also keep the simple route for backward compatibility
        Route::get('products', [UserDashboardController::class, 'products'])->name('products');
            
        // Report Issue
        Route::get('reports/create', [ReportIssueController::class, 'create'])->name('reports.create');
        Route::post('reports', [ReportIssueController::class, 'store'])->name('reports.store');
        
        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::post('/{id}/read', [UserDashboardController::class, 'markNotificationRead'])->name('read');
            Route::get('/', [UserDashboardController::class, 'notifications'])->name('index'); 
            Route::post('/mark-all-read', [UserDashboardController::class, 'markAllNotificationsRead'])->name('mark-all-read');
        });
    });

    // ============================================
    // ADMIN ROUTES (Protected by controller constructor)
    // ============================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // ============================================
        // DASHBOARD
        // ============================================
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])->name('dashboard.chart-data');
        
        // ============================================
        // DEPARTMENT MANAGEMENT
        // ============================================
        Route::resource('departments', DepartmentController::class);
        Route::get('departments/export', [DepartmentController::class, 'export'])->name('departments.export');
        Route::post('departments/import', [DepartmentController::class, 'import'])->name('departments.import');
        Route::delete('departments/bulk-delete', [DepartmentController::class, 'bulkDelete'])->name('departments.bulk-delete');
        Route::get('departments/dropdown', [DepartmentController::class, 'dropdown'])->name('departments.dropdown');
        Route::get('departments/statistics', [DepartmentController::class, 'statistics'])->name('departments.statistics');
        Route::post('departments/reorder', [DepartmentController::class, 'reorder'])->name('departments.reorder');
        Route::get('departments/my-department', [DepartmentController::class, 'myDepartment'])->name('departments.my-department');
        Route::get('departments/{id}/users', [DepartmentController::class, 'users'])->name('departments.users');
        Route::post('departments/{id}/assign-user', [DepartmentController::class, 'assignUser'])->name('departments.assign-user');
        Route::delete('departments/{id}/remove-user', [DepartmentController::class, 'removeUser'])->name('departments.remove-user');
        Route::get('departments/{id}/activity', [DepartmentController::class, 'activity'])->name('departments.activity');
        Route::get('departments/widget', [DepartmentController::class, 'widget'])->name('departments.widget');
        Route::post('departments/check-name', [DepartmentController::class, 'checkName'])->name('departments.check-name');
        
        // ============================================
        // CATEGORY MANAGEMENT
        // ============================================
        Route::resource('categories', CategoryController::class);
        Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
        Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');
        Route::delete('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::get('categories/dropdown', [CategoryController::class, 'dropdown'])->name('categories.dropdown');
        Route::get('categories/statistics', [CategoryController::class, 'statistics'])->name('categories.statistics');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::get('categories/with-products/{id?}', [CategoryController::class, 'withProducts'])->name('categories.with-products');
        Route::post('categories/check-name', [CategoryController::class, 'checkName'])->name('categories.check-name');
        Route::get('categories/widget', [CategoryController::class, 'widget'])->name('categories.widget');
        
        // ============================================
        // SUPPLIER MANAGEMENT
        // ============================================
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/bulk-delete', [SupplierController::class, 'bulkDelete'])->name('suppliers.bulk-delete');
        Route::post('suppliers/{id}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
        Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::post('suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
        Route::get('suppliers/dropdown', [SupplierController::class, 'dropdown'])->name('suppliers.dropdown');
        Route::get('suppliers/statistics', [SupplierController::class, 'statistics'])->name('suppliers.statistics');
        Route::get('suppliers/widget', [SupplierController::class, 'widget'])->name('suppliers.widget');
        Route::get('suppliers/{id}/products', [SupplierController::class, 'products'])->name('suppliers.products');
        Route::get('suppliers/{id}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('suppliers.purchase-orders');
        Route::get('suppliers/{id}/procurements', [SupplierController::class, 'procurements'])->name('suppliers.procurements');
        Route::get('suppliers/summary', [SupplierController::class, 'summary'])->name('suppliers.summary');
        Route::get('suppliers/with-products', [SupplierController::class, 'withProducts'])->name('suppliers.with-products');
        Route::post('suppliers/check-name', [SupplierController::class, 'checkName'])->name('suppliers.check-name');
        
       // Bulk Upload Routes
        Route::get('products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
        Route::post('products/bulk-upload', [ProductController::class, 'processBulkUpload'])->name('products.bulk-upload.process');
        Route::get('products/bulk-template', [ProductController::class, 'downloadTemplate'])->name('products.bulk-template');

        // Existing Routes
        Route::get('products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
        Route::get('products/by-category/{categoryId}', [ProductController::class, 'byCategory'])->name('products.by-category');
        Route::get('products/by-supplier/{supplierId}', [ProductController::class, 'bySupplier'])->name('products.by-supplier');
        Route::get('products/hardware', [ProductController::class, 'hardware'])->name('products.hardware');
        Route::get('products/software', [ProductController::class, 'software'])->name('products.software');
        Route::get('products/statistics', [ProductController::class, 'statistics'])->name('products.statistics');
        Route::get('products/widget', [ProductController::class, 'widget'])->name('products.widget');
        Route::get('products/dropdown', [ProductController::class, 'dropdown'])->name('products.dropdown');
        Route::post('products/scan-barcode', [ProductController::class, 'scanBarcode'])->name('products.scan-barcode');
        Route::get('products/{id}/history', [ProductController::class, 'history'])->name('products.history');
        Route::get('products/{id}/barcode', [ProductController::class, 'generateBarcode'])->name('products.barcode');
        Route::post('products/{id}/update-status', [ProductController::class, 'updateStatus'])->name('products.update-status');
        Route::post('products/{id}/adjust-quantity', [ProductController::class, 'adjustQuantity'])->name('products.adjust-quantity');
        Route::post('products/{id}/adjust', [ProductController::class, 'adjustQuantity'])->name('products.adjust');

        // Resource routes LAST — show/edit/update/destroy excluded and declared explicitly below
        Route::resource('products', ProductController::class)->except(['show', 'edit', 'update', 'destroy']);
        Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('products.show');
        Route::get('products/{id}/edit', [ProductController::class, 'edit'])->whereNumber('id')->name('products.edit');
        Route::put('products/{id}', [ProductController::class, 'update'])->whereNumber('id')->name('products.update');
        Route::delete('products/{id}', [ProductController::class, 'destroy'])->whereNumber('id')->name('products.destroy');
        // ============================================
        // ASSIGNMENT MANAGEMENT
        // ============================================
        Route::resource('assignments', AssignmentController::class);
        Route::post('assignments/{id}/return', [AssignmentController::class, 'returnAsset'])->name('assignments.return');
        Route::get('assignments/export', [AssignmentController::class, 'export'])->name('assignments.export');
        Route::get('assignments/report', [AssignmentController::class, 'report'])->name('assignments.report');
        Route::post('assignments/bulk-return', [AssignmentController::class, 'bulkReturn'])->name('assignments.bulk-return');
        Route::post('assignments/bulk-delete', [AssignmentController::class, 'bulkDelete'])->name('assignments.bulk-delete');
        Route::get('assignments/statistics', [AssignmentController::class, 'statistics'])->name('assignments.statistics');
        Route::get('assignments/widget', [AssignmentController::class, 'widget'])->name('assignments.widget');
        Route::get('assignments/user/{userId}', [AssignmentController::class, 'getUserAssignments'])->name('assignments.user');
        Route::get('assignments/product/{productId}/history', [AssignmentController::class, 'getProductHistory'])->name('assignments.product-history');
        Route::get('assignments/available-products', [AssignmentController::class, 'availableProducts'])->name('assignments.available-products');
        Route::post('assignments/{id}/extend', [AssignmentController::class, 'extend'])->name('assignments.extend');
        
        // ============================================
        // STOCK MOVEMENT MANAGEMENT
        // ============================================
        Route::resource('stock-movements', StockMovementController::class)->except(['edit', 'update']);
        Route::post('stock-movements/bulk-delete', [StockMovementController::class, 'bulkDelete'])->name('stock-movements.bulk-delete');
        Route::get('stock-movements/export', [StockMovementController::class, 'export'])->name('stock-movements.export');
        Route::get('stock-movements/statistics', [StockMovementController::class, 'statistics'])->name('stock-movements.statistics');
        Route::get('stock-movements/widget', [StockMovementController::class, 'widget'])->name('stock-movements.widget');
        Route::get('stock-movements/by-product/{productId}', [StockMovementController::class, 'byProduct'])->name('stock-movements.by-product');
        Route::get('stock-movements/by-type/{type}', [StockMovementController::class, 'byType'])->name('stock-movements.by-type');
        Route::get('stock-movements/summary', [StockMovementController::class, 'summary'])->name('stock-movements.summary');
        
      
        // ============================================
        // PURCHASE ORDER MANAGEMENT
        // ============================================
        Route::resource('purchase-orders', PurchaseOrderController::class);

        Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::delete('purchase-orders/bulk-delete', [PurchaseOrderController::class, 'bulkDelete'])->name('purchase-orders.bulk-delete');
        Route::post('purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');

        Route::get('purchase-orders/export', [PurchaseOrderController::class, 'export'])->name('purchase-orders.export');
        Route::get('purchase-orders/{id}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');
        Route::get('purchase-orders/{id}/stream-pdf', [PurchaseOrderController::class, 'streamPdf'])->name('purchase-orders.stream-pdf');
        Route::post('purchase-orders/bulk-pdf', [PurchaseOrderController::class, 'bulkPdf'])->name('purchase-orders.bulk-pdf');
        Route::post('purchase-orders/{id}/email-pdf', [PurchaseOrderController::class, 'emailPdf'])->name('purchase-orders.email-pdf');

        Route::get('purchase-orders/statistics', [PurchaseOrderController::class, 'statistics'])->name('purchase-orders.statistics');
        Route::get('purchase-orders/widget', [PurchaseOrderController::class, 'widget'])->name('purchase-orders.widget');

        Route::get('purchase-orders/by-supplier/{supplierId}', [PurchaseOrderController::class, 'bySupplier'])->name('purchase-orders.by-supplier');
        Route::get('purchase-orders/{id}/items', [PurchaseOrderController::class, 'items'])->name('purchase-orders.items');
        Route::get('purchase-orders/supplier-products/{supplierId}', [PurchaseOrderController::class, 'supplierProducts'])->name('purchase-orders.supplier-products');
        Route::post('purchase-orders/{id}/update-items', [PurchaseOrderController::class, 'updateItems'])->name('purchase-orders.update-items');

        // FIXED — these four were missing the "purchase-orders/" segment
        Route::get('purchase-orders/product/{id}', [PurchaseOrderController::class, 'getProductDetails'])->name('purchase-orders.product-details');
        Route::post('purchase-orders/{id}/payment', [PurchaseOrderController::class, 'recordPayment'])->name('purchase-orders.payment');
        Route::get('purchase-orders/{id}/payment-details', [PurchaseOrderController::class, 'getPaymentDetails'])->name('purchase-orders.payment-details');
        Route::post('purchase-orders/{id}/invoice', [PurchaseOrderController::class, 'markInvoiced'])->name('purchase-orders.invoice');
   
                
        // ============================================
        // MAINTENANCE MANAGEMENT
        // ============================================
        Route::resource('maintenance', MaintenanceController::class);
        Route::post('maintenance/{id}/resolve', [MaintenanceController::class, 'resolve'])->name('maintenance.resolve');
        Route::post('maintenance/{id}/reopen', [MaintenanceController::class, 'reopen'])->name('maintenance.reopen');
        Route::post('maintenance/{id}/assign', [MaintenanceController::class, 'assign'])->name('maintenance.assign');
        Route::post('maintenance/{id}/unassign', [MaintenanceController::class, 'unassign'])->name('maintenance.unassign');
        Route::post('maintenance/bulk-delete', [MaintenanceController::class, 'bulkDelete'])->name('maintenance.bulk-delete');
        Route::get('maintenance/export', [MaintenanceController::class, 'export'])->name('maintenance.export');
        Route::get('maintenance/report', [MaintenanceController::class, 'report'])->name('maintenance.report');
        Route::post('maintenance/{id}/unrepairable', [MaintenanceController::class, 'markUnrepairable'])->name('maintenance.unrepairable');
        Route::post('maintenance/{id}/add-note', [MaintenanceController::class, 'addNote'])->name('maintenance.add-note');
        Route::get('maintenance/statistics', [MaintenanceController::class, 'statistics'])->name('maintenance.statistics');
        Route::get('maintenance/widget', [MaintenanceController::class, 'widget'])->name('maintenance.widget');
        Route::get('maintenance/by-product/{productId}', [MaintenanceController::class, 'byProduct'])->name('maintenance.by-product');
        Route::get('maintenance/unresolved', [MaintenanceController::class, 'unresolved'])->name('maintenance.unresolved');
        Route::get('maintenance/summary', [MaintenanceController::class, 'summary'])->name('maintenance.summary');
        Route::get('maintenance/by-priority/{priority}', [MaintenanceController::class, 'byPriority'])->name('maintenance.by-priority');
        
        // ============================================
        // USER MANAGEMENT
        // ============================================
        Route::resource('users', UserController::class);
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/{id}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::post('users/{id}/remove-role', [UserController::class, 'removeRole'])->name('users.remove-role');
        Route::get('users/{id}/permissions', [UserController::class, 'managePermissions'])->name('users.permissions');
        Route::post('users/{id}/sync-permissions', [UserController::class, 'syncPermissions'])->name('users.sync-permissions');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::post('users/{id}/verify-email', [UserController::class, 'verifyEmail'])->name('users.verify-email');
        Route::post('users/{id}/unverify-email', [UserController::class, 'unverifyEmail'])->name('users.unverify-email');
        Route::get('users/dropdown', [UserController::class, 'dropdown'])->name('users.dropdown');
        Route::get('users/{id}/activity', [UserController::class, 'activity'])->name('users.activity');
        Route::get('users/statistics', [UserController::class, 'statistics'])->name('users.statistics');
        Route::get('users/widget', [UserController::class, 'widget'])->name('users.widget');
        Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');
        Route::put('users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
        
        // ============================================
        // ROLE MANAGEMENT
        // ============================================
        Route::resource('roles', RoleController::class);
        Route::post('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
        Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
        Route::post('roles/{id}/clone', [RoleController::class, 'clone'])->name('roles.clone');
        Route::get('roles/{id}/users', [RoleController::class, 'users'])->name('roles.users');
        Route::get('roles/{id}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{id}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
        Route::get('roles/dropdown', [RoleController::class, 'dropdown'])->name('roles.dropdown');
        Route::post('roles/assign-to-user', [RoleController::class, 'assignToUser'])->name('roles.assign-to-user');
        Route::delete('roles/remove-from-user', [RoleController::class, 'removeFromUser'])->name('roles.remove-from-user');
        Route::get('roles/statistics', [RoleController::class, 'statistics'])->name('roles.statistics');
        Route::get('roles/widget', [RoleController::class, 'widget'])->name('roles.widget');
        Route::post('roles/check-name', [RoleController::class, 'checkName'])->name('roles.check-name');
        
        // ============================================
        // PERMISSION MANAGEMENT
        // ============================================
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/export', [PermissionController::class, 'export'])->name('export');
            Route::post('/import', [PermissionController::class, 'import'])->name('import');
            Route::get('/grouped', [PermissionController::class, 'grouped'])->name('grouped');
            Route::get('/groups', [PermissionController::class, 'groups'])->name('groups');
            Route::get('/for-role/{roleId}', [PermissionController::class, 'forRole'])->name('for-role');
            Route::post('/sync-role/{roleId}', [PermissionController::class, 'syncRole'])->name('sync-role');
            Route::get('/statistics', [PermissionController::class, 'statistics'])->name('statistics');
            Route::get('/widget', [PermissionController::class, 'widget'])->name('widget');
            Route::post('/check-name', [PermissionController::class, 'checkName'])->name('check-name');
            Route::post('/refresh-cache', [PermissionController::class, 'refreshCache'])->name('refresh-cache');
            Route::post('/create-default-groups', [PermissionController::class, 'createDefaultGroups'])->name('create-default-groups');
            Route::get('/by-group/{group}', [PermissionController::class, 'byGroup'])->name('by-group');
        });

        // ============================================
        // SPECIFICATION AMENDMENT MANAGEMENT
        // ============================================
        Route::prefix('specification-amendments')->name('specification-amendments.')->group(function () {
            Route::get('/', [SpecificationAmendmentController::class, 'index'])->name('index');
            Route::get('/create', [SpecificationAmendmentController::class, 'create'])->name('create');
            Route::post('/', [SpecificationAmendmentController::class, 'store'])->name('store');
            Route::get('/{id}', [SpecificationAmendmentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SpecificationAmendmentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SpecificationAmendmentController::class, 'update'])->name('update');
            Route::delete('/{id}', [SpecificationAmendmentController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [SpecificationAmendmentController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [SpecificationAmendmentController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [SpecificationAmendmentController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/export', [SpecificationAmendmentController::class, 'export'])->name('export');
            Route::get('/by-asset-request/{assetRequestId}', [SpecificationAmendmentController::class, 'byAssetRequest'])->name('by-asset-request');
            Route::get('/statistics', [SpecificationAmendmentController::class, 'statistics'])->name('statistics');
            Route::get('/widget', [SpecificationAmendmentController::class, 'widget'])->name('widget');
            Route::get('/pending', [SpecificationAmendmentController::class, 'pending'])->name('pending');
            Route::get('/approved', [SpecificationAmendmentController::class, 'approved'])->name('approved');
            Route::get('/rejected', [SpecificationAmendmentController::class, 'rejected'])->name('rejected');
        });

        // ============================================
        // NOTIFICATION MANAGEMENT
        // ============================================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [NotificationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NotificationController::class, 'update'])->name('update');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
            Route::post('/{id}/mark-unread', [NotificationController::class, 'markUnread'])->name('mark-unread');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
            Route::post('/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/export', [NotificationController::class, 'export'])->name('export');
            Route::get('/statistics', [NotificationController::class, 'statistics'])->name('statistics');
            Route::get('/widget', [NotificationController::class, 'widget'])->name('widget');
            Route::get('/my-notifications', [NotificationController::class, 'myNotifications'])->name('my-notifications');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::get('/by-type/{type}', [NotificationController::class, 'byType'])->name('by-type');
            Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
            Route::put('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');
            Route::post('/send', [NotificationController::class, 'send'])->name('send');
            Route::post('/send-bulk', [NotificationController::class, 'sendBulk'])->name('send-bulk');
            Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
            Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
            Route::put('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        });

        // ============================================
        // ACTIVITY LOG MANAGEMENT
        // ============================================
        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/{id}', [ActivityLogController::class, 'show'])->name('show');
            Route::delete('/clear', [ActivityLogController::class, 'clear'])->name('clear');
            Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
            Route::get('/statistics', [ActivityLogController::class, 'statistics'])->name('statistics');
        });

        
        
// ============================================
// REPORTS
// ============================================
Route::prefix('reports')->name('reports.')->group(function () {
    // Dashboard
    Route::get('/', [ReportController::class, 'index'])->name('index');
    
    // ============================================
    // API ENDPOINTS (AJAX) - Using 'api' prefix
    // ============================================
    Route::prefix('api')->name('api.')->group(function () {
        // Inventory Reports
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/inventory/data', [ReportController::class, 'inventory'])->name('inventory.data');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/low-stock/data', [ReportController::class, 'lowStock'])->name('low-stock.data');
        Route::get('/warranty', [ReportController::class, 'warrantyExpiry'])->name('warranty');
        Route::get('/warranty/data', [ReportController::class, 'warrantyExpiry'])->name('warranty.data');
        
        // Organization Reports
        Route::get('/companies', [ReportController::class, 'companies'])->name('companies');
        Route::get('/companies/data', [ReportController::class, 'companies'])->name('companies.data');
        Route::get('/branches', [ReportController::class, 'branches'])->name('branches');
        Route::get('/branches/data', [ReportController::class, 'branches'])->name('branches.data');
        Route::get('/departments', [ReportController::class, 'departments'])->name('departments');
        Route::get('/departments/data', [ReportController::class, 'departments'])->name('departments.data');
        Route::get('/warehouses', [ReportController::class, 'warehouses'])->name('warehouses');
        Route::get('/warehouses/data', [ReportController::class, 'warehouses'])->name('warehouses.data');
        Route::get('/categories', [ReportController::class, 'categories'])->name('categories');
        Route::get('/categories/data', [ReportController::class, 'categories'])->name('categories.data');
        
        // HR Reports
        Route::get('/employees', [ReportController::class, 'employees'])->name('employees');
        Route::get('/employees/data', [ReportController::class, 'employees'])->name('employees.data');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/attendance/data', [ReportController::class, 'attendance'])->name('attendance.data');
        
        // Leave Reports
        Route::get('/leave-types', [ReportController::class, 'leaveTypes'])->name('leave-types');
        Route::get('/leave-types/data', [ReportController::class, 'leaveTypes'])->name('leave-types.data');
        Route::get('/leave-balances', [ReportController::class, 'leaveBalances'])->name('leave-balances');
        Route::get('/leave-balances/data', [ReportController::class, 'leaveBalances'])->name('leave-balances.data');
        Route::get('/leave-requests', [ReportController::class, 'leaveRequests'])->name('leave-requests');
        Route::get('/leave-requests/data', [ReportController::class, 'leaveRequests'])->name('leave-requests.data');
        
        // Payroll Reports (AJAX endpoints)
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/summary', [ReportController::class, 'apiPayrollSummary'])->name('summary');
            Route::get('/periods', [ReportController::class, 'apiPayrollPeriods'])->name('periods');
            Route::get('/calculations', [ReportController::class, 'apiPayrollCalculations'])->name('calculations');
            Route::get('/payslips', [ReportController::class, 'apiPayrollPayslips'])->name('payslips');
            Route::get('/loans', [ReportController::class, 'apiPayrollLoans'])->name('loans');
            Route::get('/overtime', [ReportController::class, 'apiPayrollOvertime'])->name('overtime');
            Route::get('/adjustments', [ReportController::class, 'apiPayrollAdjustments'])->name('adjustments');
            Route::get('/rates', [ReportController::class, 'apiPayrollRates'])->name('rates');
        });
        
        // Sales Reports
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/data', [ReportController::class, 'sales'])->name('sales.data');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/customers/data', [ReportController::class, 'customers'])->name('customers.data');
        Route::get('/quotations', [ReportController::class, 'quotations'])->name('quotations');
        Route::get('/quotations/data', [ReportController::class, 'quotations'])->name('quotations.data');
        
        // Purchase Reports
        Route::get('/purchase-orders', [ReportController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/purchase-orders/data', [ReportController::class, 'purchaseOrders'])->name('purchase-orders.data');
        Route::get('/suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/suppliers/data', [ReportController::class, 'suppliers'])->name('suppliers.data');
        
        // Users
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/users/data', [ReportController::class, 'users'])->name('users.data');
        
        // Profit & Loss
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/data', [ReportController::class, 'profitLoss'])->name('profit-loss.data');
    });
    
    // ============================================
    // PAGE VIEWS (Blade templates)
    // ============================================
    
    // Inventory Reports
    Route::view('/inventory', 'admin.reports.inventory')->name('inventory');
    Route::view('/low-stock', 'admin.reports.low-stock')->name('low-stock');
    Route::view('/warranty-expiry', 'admin.reports.warranty-expiry')->name('warranty-expiry');
    
    // Organization Reports
    Route::view('/companies', 'admin.reports.companies')->name('companies');
    Route::view('/branches', 'admin.reports.branches')->name('branches');
    Route::view('/departments', 'admin.reports.departments')->name('departments');
    Route::view('/warehouses', 'admin.reports.warehouses')->name('warehouses');
    Route::view('/categories', 'admin.reports.categories')->name('categories');
    
    // HR Reports
    Route::view('/employees', 'admin.reports.employees')->name('employees');
    Route::view('/attendance', 'admin.reports.attendance')->name('attendance');
    Route::get('/leave', [ReportController::class, 'leave'])->name('leave');
    
    // Payroll Report (Single page with tabs)
    Route::view('/payroll', 'admin.reports.payroll')->name('payroll');
    
    // Sales Reports
    Route::view('/sales', 'admin.reports.sales')->name('sales');
    Route::view('/customers', 'admin.reports.customers')->name('customers');
    Route::view('/quotations', 'admin.reports.quotations')->name('quotations');
    
    // Purchase Reports
    Route::view('/purchase-orders', 'admin.reports.purchase-orders')->name('purchase-orders');
    Route::view('/suppliers', 'admin.reports.suppliers')->name('suppliers');
    
    // Users
    Route::view('/users', 'admin.reports.users')->name('users');
    
    // Profit & Loss
    Route::view('/profit-loss', 'admin.reports.profit-loss')->name('profit-loss');
    
    // ============================================
    // EXPORT
    // ============================================
    Route::post('/export', [ReportController::class, 'export'])->name('export');
});

        // ============================================
        // SETTINGS
        // ============================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');

            // General
            Route::get('general', [SettingController::class, 'general'])->name('general');
            Route::put('general', [SettingController::class, 'updateGeneral'])->name('general.update');

            // Backup
            Route::get('backup', [SettingController::class, 'backup'])->name('backup');
            Route::post('backup/run', [SettingController::class, 'runBackup'])->name('backup.run');
            Route::get('backup/download/{filename}', [SettingController::class, 'downloadBackup'])->name('backup.download');
            Route::delete('backup/delete/{filename}', [SettingController::class, 'deleteBackup'])->name('backup.delete');
            Route::post('backup/schedule', [SettingController::class, 'updateBackupSchedule'])->name('backup.schedule');

            // Logs
            Route::get('logs', [SettingController::class, 'logs'])->name('logs');
            Route::delete('logs/clear', [SettingController::class, 'clearLogs'])->name('logs.clear');
            Route::get('logs/download', [SettingController::class, 'downloadLogs'])->name('logs.download');

            // Email
            Route::get('email', [SettingController::class, 'email'])->name('email');
            Route::put('email', [SettingController::class, 'updateEmail'])->name('email.update');
            Route::post('email/test', [SettingController::class, 'testEmail'])->name('email.test');

            // Security
            Route::get('security', [SettingController::class, 'security'])->name('security');
            Route::put('security', [SettingController::class, 'updateSecurity'])->name('security.update');

            // System Info
            Route::get('system-info', [SettingController::class, 'systemInfo'])->name('system-info');

            // Cache
            Route::get('cache', [SettingController::class, 'cache'])->name('cache');
            Route::post('cache/clear', [SettingController::class, 'clearCache'])->name('cache.clear');

            // Maintenance
            Route::get('maintenance', [SettingController::class, 'maintenance'])->name('maintenance');
            Route::put('maintenance/toggle', [SettingController::class, 'toggleMaintenance'])->name('maintenance.toggle');
        });

        // ============================================
        // COMPANIES MANAGEMENT
        // ============================================
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{id}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{id}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [CompanyController::class, 'export'])->name('export');
            Route::get('/dropdown', [CompanyController::class, 'dropdown'])->name('dropdown');
        });

        // ============================================
        // BRANCHES MANAGEMENT
        // ============================================
        Route::prefix('branches')->name('branches.')->group(function () {
            Route::get('/', [BranchController::class, 'index'])->name('index');
            Route::get('/create', [BranchController::class, 'create'])->name('create');
            Route::post('/', [BranchController::class, 'store'])->name('store');
            Route::get('/{id}', [BranchController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [BranchController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BranchController::class, 'update'])->name('update');
            Route::delete('/{id}', [BranchController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [BranchController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [BranchController::class, 'export'])->name('export');
            Route::delete('/bulk-delete', [BranchController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/dropdown', [BranchController::class, 'dropdown'])->name('dropdown');
            Route::get('/statistics', [BranchController::class, 'statistics'])->name('statistics');
            Route::get('/get-managers', [BranchController::class, 'getManagers'])->name('get-managers');
        });

        // ============================================
        // EMPLOYEES MANAGEMENT
        // ============================================
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/create', [EmployeeController::class, 'create'])->name('create');
            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [EmployeeController::class, 'export'])->name('export');
            Route::get('/dropdown', [EmployeeController::class, 'dropdown'])->name('dropdown');
        });

        
        // ============================================
        // LEAVE TYPES
        // ============================================
        Route::prefix('leave-types')->name('leave-types.')->group(function () {
            Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
            Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
            Route::get('/{id}', [LeaveTypeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LeaveTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [LeaveTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [LeaveTypeController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [LeaveTypeController::class, 'export'])->name('export');
        });

        // ============================================
        // LEAVE BALANCES
        // ============================================
        Route::prefix('/leave/balances')->name('leave.balances.')->group(function () {
            Route::post('/initialize', [LeaveBalanceController::class, 'initializeYear'])->name('initialize');
            Route::get('/', [LeaveBalanceController::class, 'index'])->name('index');
            Route::get('/export', [LeaveBalanceController::class, 'export'])->name('export');
            Route::put('/{id}', [LeaveBalanceController::class, 'update'])->name('update');
            Route::post('/initialize', [LeaveBalanceController::class, 'initializeYear'])->name('initialize');
            Route::get('/employee/{employeeId}', [LeaveBalanceController::class, 'employeeBalance'])->name('employee');
        });

         // ============================================
        // LEAVE REQUESTS
        // ============================================
        Route::prefix('leave/requests')->name('leave.requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
            Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [LeaveRequestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('update');
            Route::delete('/{id}', [LeaveRequestController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
            Route::post('/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
            Route::post('/bulk-approve', [LeaveRequestController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [LeaveRequestController::class, 'bulkReject'])->name('bulk-reject');
            Route::post('/bulk-delete', [LeaveRequestController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/{id}/certificate', [LeaveRequestController::class, 'downloadCertificate'])->name('certificate');
            Route::get('/employee/{employeeId}/balance', [LeaveRequestController::class, 'getEmployeeBalance'])->name('employee-balance');
        });

        // ============================================
        // ATTENDANCE MANAGEMENT
        // ============================================
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/create', [AttendanceController::class, 'create'])->name('create');
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::post('/bulk', [AttendanceController::class, 'bulkStore'])->name('bulk-store');
            Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
            Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [AttendanceController::class, 'approve'])->name('approve');
            Route::get('/export', [AttendanceController::class, 'export'])->name('export');
            Route::post('/bulk-approve', [AttendanceController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // ============================================
        // PAYROLL MANAGEMENT (All Tabs)
        // ============================================
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // Main index with tabs
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/summary', [PayrollController::class, 'getPayrollSummary'])->name('summary');

            // ---- PERIODS ----
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::post('/', [PayrollController::class, 'storePeriod'])->name('store');
                Route::get('/{id}/edit', [PayrollController::class, 'editPeriod'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updatePeriod'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyPeriod'])->name('destroy');
                Route::post('/{id}/lock', [PayrollController::class, 'lockPeriod'])->name('lock');
                Route::post('/{id}/unlock', [PayrollController::class, 'unlockPeriod'])->name('unlock');
                Route::post('/{id}/approve', [PayrollController::class, 'approvePeriod'])->name('approve');
                Route::post('/calculate', [PayrollController::class, 'calculatePeriod'])->name('calculate');
            });

            // ---- CALCULATIONS ----
            Route::prefix('calculations')->name('calculations.')->group(function () {
                Route::post('/calculate', [PayrollController::class, 'calculate'])->name('calculate');
                Route::post('/', [PayrollController::class, 'storeCalculation'])->name('store');
                Route::get('/{id}', [PayrollController::class, 'showCalculation'])->name('show');
                Route::get('/{id}/edit', [PayrollController::class, 'editCalculation'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updateCalculation'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyCalculation'])->name('destroy');
                Route::post('/{id}/verify', [PayrollController::class, 'verifyCalculation'])->name('verify');
                Route::post('/{id}/pay', [PayrollController::class, 'processPayment'])->name('pay');
            });

            // ---- PAYSLIPS ----
            Route::prefix('payslips')->name('payslips.')->group(function () {
                Route::post('/generate', [PayrollController::class, 'generatePayslips'])->name('generate');
                Route::get('/{id}/preview', [PayrollController::class, 'previewPayslip'])->name('preview');
                Route::get('/{id}/download', [PayrollController::class, 'downloadPayslip'])->name('download');
                Route::post('/{id}/email', [PayrollController::class, 'emailPayslip'])->name('email');
                Route::post('/{id}/regenerate', [PayrollController::class, 'regeneratePayslip'])->name('regenerate');
                Route::delete('/{id}', [PayrollController::class, 'destroyPayslip'])->name('destroy');
                Route::get('/{id}/print', [PayrollController::class, 'printPayslip'])->name('print');
            });

            // ---- LOANS ----
            Route::prefix('loans')->name('loans.')->group(function () {
                Route::post('/', [PayrollController::class, 'storeLoan'])->name('store');
                Route::get('/{id}/edit', [PayrollController::class, 'editLoan'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updateLoan'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyLoan'])->name('destroy');
                Route::post('/{id}/approve', [PayrollController::class, 'approveLoan'])->name('approve');
                Route::post('/{id}/reject', [PayrollController::class, 'rejectLoan'])->name('reject');
                Route::post('/{id}/disburse', [PayrollController::class, 'disburseLoan'])->name('disburse');
                Route::post('/{id}/suspend', [PayrollController::class, 'suspendLoan'])->name('suspend');
                Route::post('/{id}/resume', [PayrollController::class, 'resumeLoan'])->name('resume');
                Route::post('/{id}/complete', [PayrollController::class, 'completeLoan'])->name('complete');
                Route::get('/{id}/view', [PayrollController::class, 'viewLoan'])->name('view');
            });

            // ---- OVERTIME ----
            Route::prefix('overtime')->name('overtime.')->group(function () {
                Route::post('/', [PayrollController::class, 'storeOvertime'])->name('store');
                Route::get('/{id}/edit', [PayrollController::class, 'editOvertime'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updateOvertime'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyOvertime'])->name('destroy');
                Route::post('/{id}/approve', [PayrollController::class, 'approveOvertime'])->name('approve');
                Route::post('/{id}/reject', [PayrollController::class, 'rejectOvertime'])->name('reject');
            });

            // ---- ADJUSTMENTS ----
            Route::prefix('adjustments')->name('adjustments.')->group(function () {
                Route::post('/', [PayrollController::class, 'storeAdjustment'])->name('store');
                Route::get('/{id}/edit', [PayrollController::class, 'editAdjustment'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updateAdjustment'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyAdjustment'])->name('destroy');
                Route::post('/{id}/approve', [PayrollController::class, 'approveAdjustment'])->name('approve');
                Route::post('/{id}/reject', [PayrollController::class, 'rejectAdjustment'])->name('reject');
            });

            // ---- RATES ----
            Route::prefix('rates')->name('rates.')->group(function () {
                Route::post('/', [PayrollController::class, 'storeRate'])->name('store');
                Route::get('/{id}/edit', [PayrollController::class, 'editRate'])->name('edit');
                Route::put('/{id}', [PayrollController::class, 'updateRate'])->name('update');
                Route::delete('/{id}', [PayrollController::class, 'destroyRate'])->name('destroy');
            });
        });

        // Stock Levels (read-only)
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::get('/{id}', [StockController::class, 'show'])->name('show');
            Route::get('/export', [StockController::class, 'export'])->name('export');
            Route::get('/populate', [StockController::class, 'populateStock'])->name('populate');
        });

         Route::prefix('sales-payments')->name('sales-payments.')->group(function () {
        Route::get('/', [SalesPaymentController::class, 'index'])->name('index');
        Route::post('/', [SalesPaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [SalesPaymentController::class, 'show'])->name('show');
        Route::delete('/{id}', [SalesPaymentController::class, 'destroy'])->name('destroy');
        Route::get('/export', [SalesPaymentController::class, 'export'])->name('export');
    });

    // Add to Invoice routes: payment recording
    Route::post('invoices/{id}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');

    // ============================================
    // PURCHASE PAYMENTS
    // ============================================
    Route::prefix('purchase-payments')->name('purchase-payments.')->group(function () {
        Route::get('/', [PurchasePaymentController::class, 'index'])->name('index');
        Route::get('/create', [PurchasePaymentController::class, 'create'])->name('create');
        Route::post('/', [PurchasePaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PurchasePaymentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PurchasePaymentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PurchasePaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PurchasePaymentController::class, 'destroy'])->name('destroy');
        Route::get('/export', [PurchasePaymentController::class, 'export'])->name('export');
        Route::post('/get-po-details', [PurchasePaymentController::class, 'getPurchaseOrderDetails'])->name('get-po-details');
    });
        // ============================================
        // CURRENCIES MANAGEMENT
        // ============================================
        Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::get('/{id}', [CurrencyController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [CurrencyController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/set-base', [CurrencyController::class, 'setBase'])->name('set-base');
        Route::get('/dropdown', [CurrencyController::class, 'dropdown'])->name('dropdown');
        Route::get('/export', [CurrencyController::class, 'export'])->name('export');
        Route::post('/import', [CurrencyController::class, 'import'])->name('import');
    });
        // ============================================
        // EXCHANGE RATES
        // ============================================
        Route::prefix('exchange-rates')->name('exchange-rates.')->group(function () {
            Route::get('/', [ExchangeRateController::class, 'index'])->name('index');
            Route::get('/create', [ExchangeRateController::class, 'create'])->name('create');
            Route::post('/', [ExchangeRateController::class, 'store'])->name('store');
            Route::get('/{id}', [ExchangeRateController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ExchangeRateController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ExchangeRateController::class, 'update'])->name('update');
            Route::delete('/{id}', [ExchangeRateController::class, 'destroy'])->name('destroy');
            Route::get('/export', [ExchangeRateController::class, 'export'])->name('export');
            Route::post('/fetch-latest', [ExchangeRateController::class, 'fetchLatest'])->name('fetch-latest');
        });

       // Warehouses
        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');

            // Static segments MUST come before the {id} wildcard routes
            Route::get('/dropdown', [WarehouseController::class, 'dropdown'])->name('dropdown');
            Route::get('/export', [WarehouseController::class, 'export'])->name('export');

            Route::get('/{id}', [WarehouseController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{id}', [WarehouseController::class, 'update'])->name('update');
            Route::delete('/{id}', [WarehouseController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('toggle-status');
        });
                        
       
        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\QuotationController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\QuotationController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\QuotationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\QuotationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\QuotationController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\QuotationController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/send', [App\Http\Controllers\Admin\QuotationController::class, 'send'])->name('send');
            Route::post('/{id}/accept', [App\Http\Controllers\Admin\QuotationController::class, 'accept'])->name('accept');
            Route::post('/{id}/reject', [App\Http\Controllers\Admin\QuotationController::class, 'reject'])->name('reject');
            Route::post('/{id}/convert-to-sale', [App\Http\Controllers\Admin\QuotationController::class, 'convertToSale'])->name('convert-to-sale');
            Route::get('/{id}/print', [App\Http\Controllers\Admin\QuotationController::class, 'print'])->name('print');
            Route::get('/{id}/pdf', [App\Http\Controllers\Admin\QuotationController::class, 'pdf'])->name('pdf');
            Route::get('/export', [App\Http\Controllers\Admin\QuotationController::class, 'export'])->name('export');
        });

        // ============================================
        // INVOICES
        // ============================================
        Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');          // List
        Route::post('/', [InvoiceController::class, 'store'])->name('store');        // <-- ADD THIS LINE
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');       // View
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');  // Edit (modal)
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');   // Update
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy'); // Delete
        Route::post('/{id}/mark-paid', [InvoiceController::class, 'markPaid'])->name('mark-paid');
        Route::post('/{id}/send-reminder', [InvoiceController::class, 'sendReminder'])->name('send-reminder');
        Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
        Route::get('/{id}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
        Route::get('/export', [InvoiceController::class, 'export'])->name('export');
    });

        // ============================================
        // DEBIT NOTES (Supplier Returns)
        // ============================================
        Route::prefix('debit-notes')->name('debit-notes.')->group(function () {
        Route::get('/', [DebitNoteController::class, 'index'])->name('index');
        Route::post('/', [DebitNoteController::class, 'store'])->name('store');
        Route::get('/{id}', [DebitNoteController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DebitNoteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DebitNoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [DebitNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [DebitNoteController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [DebitNoteController::class, 'reject'])->name('reject');
        Route::get('/{id}/print', [DebitNoteController::class, 'print'])->name('print');
        Route::get('/{id}/pdf', [DebitNoteController::class, 'pdf'])->name('pdf');
        Route::get('/export', [DebitNoteController::class, 'export'])->name('export');
    });
        // ============================================
        // SALES (Enhanced)
        // ============================================
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/create', [SaleController::class, 'create'])->name('create');
            Route::post('/', [SaleController::class, 'store'])->name('store');
            Route::get('/{id}', [SaleController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SaleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SaleController::class, 'update'])->name('update');
            Route::delete('/{id}', [SaleController::class, 'destroy'])->name('destroy');
            Route::get('/export', [SaleController::class, 'export'])->name('export');
            Route::post('/bulk-delete', [SaleController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/{id}/invoice', [SaleController::class, 'invoice'])->name('invoice');
            Route::get('/{id}/print', [SaleController::class, 'print'])->name('print');
            Route::get('/{id}/pdf', [SaleController::class, 'pdf'])->name('pdf');
            Route::post('/{id}/pay', [SaleController::class, 'pay'])->name('pay');
            Route::post('/{id}/cancel', [SaleController::class, 'cancel'])->name('cancel');
            Route::get('/product/{id}', [SaleController::class, 'getProductDetails'])->name('product.details');

        });

        // ============================================
        // CREDIT NOTES (Customer Returns)
        // ============================================
        Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
        Route::get('/', [CreditNoteController::class, 'index'])->name('index');
        Route::post('/', [CreditNoteController::class, 'store'])->name('store');
        Route::get('/{id}', [CreditNoteController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CreditNoteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CreditNoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [CreditNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [CreditNoteController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [CreditNoteController::class, 'reject'])->name('reject');
        Route::get('/{id}/print', [CreditNoteController::class, 'print'])->name('print');
        Route::get('/{id}/pdf', [CreditNoteController::class, 'pdf'])->name('pdf');
        Route::get('/export', [CreditNoteController::class, 'export'])->name('export');
    });
        // ============================================
        // STOCK ADJUSTMENTS
        // ============================================
        Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'cancel'])->name('cancel');
        Route::get('/export', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'export'])->name('export');
    });
        
       Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StockTransferController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\StockTransferController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\StockTransferController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\StockTransferController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\StockTransferController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\StockTransferController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\StockTransferController::class, 'approve'])->name('approve');
        Route::post('/{id}/complete', [App\Http\Controllers\Admin\StockTransferController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [App\Http\Controllers\Admin\StockTransferController::class, 'cancel'])->name('cancel');
        Route::get('/export', [App\Http\Controllers\Admin\StockTransferController::class, 'export'])->name('export');
    });
         // ============================================
        // POINT OF SALE (POS)
        // ============================================
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\POSController::class, 'index'])->name('index');
            Route::post('/add-to-cart', [App\Http\Controllers\Admin\POSController::class, 'addToCart'])->name('add-to-cart');
            Route::post('/update-cart', [App\Http\Controllers\Admin\POSController::class, 'updateCart'])->name('update-cart');
            Route::post('/remove-from-cart', [App\Http\Controllers\Admin\POSController::class, 'removeFromCart'])->name('remove-from-cart');
            Route::post('/clear-cart', [App\Http\Controllers\Admin\POSController::class, 'clearCart'])->name('clear-cart');
            Route::get('/search-products', [App\Http\Controllers\Admin\POSController::class, 'searchProducts'])->name('search-products');
            Route::get('/scan-barcode', [App\Http\Controllers\Admin\POSController::class, 'scanBarcode'])->name('scan-barcode');
            Route::post('/checkout', [App\Http\Controllers\Admin\POSController::class, 'checkout'])->name('checkout');
        });
        Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

        // Optional extras (like export, bulk actions, etc.)
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('bulk-delete');
      });
    });
});

// ============================================
// API ROUTES (AJAX calls)
// ============================================
Route::middleware(['auth', 'verified'])
    ->prefix('api')
    ->name('api.')
    ->group(function () {
        
        // ============================================
        // PRODUCTS API
        // ============================================
        Route::get('products/by-category/{category}', [ApiProductController::class, 'byCategory'])->name('products.by-category');
        Route::get('products/available', [ApiProductController::class, 'available'])->name('products.available');
        Route::get('products/search', [ApiProductController::class, 'search'])->name('products.search');
        
        // ============================================
        // USERS API
        // ============================================
        Route::get('users/search', [ApiUserController::class, 'search'])->name('users.search');
        Route::get('users/by-role/{role}', [ApiUserController::class, 'byRole'])->name('users.by-role');
        
        // ============================================
        // ASSET REQUESTS API
        // ============================================
        Route::get('asset-requests/{id}/status', [ApiAssetRequestController::class, 'status'])->name('asset-requests.status');
        Route::get('asset-requests/{id}/timeline', [ApiAssetRequestController::class, 'timeline'])->name('asset-requests.timeline');

          // Attendance
    Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/data', [ReportController::class, 'attendance'])->name('attendance.data');
    
    // Leave Requests
    Route::get('/leave-requests', [ReportController::class, 'leaveRequests'])->name('leave-requests');
    Route::get('/leave-requests/data', [ReportController::class, 'leaveRequests'])->name('leave-requests.data');
    
    // Sales
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/sales/data', [ReportController::class, 'sales'])->name('sales.data');
        
        // ============================================
        // USER COUNTS API
        // ============================================
        Route::get('user/assignments/count', function () {
            $count = \App\Models\Assignment::where('user_id', auth()->id())
                ->whereNull('actual_return_date')
                ->count();
            return response()->json(['count' => $count]);
        })->name('user.assignments.count');
        
        Route::get('user/requests/count', function () {
            $count = \App\Models\AssetRequest::where('user_id', auth()->id())
                ->whereIn('status', ['pending_ict_review', 'ict_reviewing'])
                ->count();
            return response()->json(['count' => $count]);
        })->name('user.requests.count');
        
        Route::get('user/overdue/count', function () {
            $count = \App\Models\Assignment::where('user_id', auth()->id())
                ->whereNull('actual_return_date')
                ->where('expected_return_date', '<', now())
                ->count();
            return response()->json(['count' => $count]);
        })->name('user.overdue.count');
        
        // ============================================
        // ADMIN COUNTS API
        // ============================================
        Route::get('admin/pending-requests/count', function () {
            $count = \App\Models\AssetRequest::whereIn('status', ['pending_ict_review', 'ict_reviewing'])->count();
            return response()->json(['count' => $count]);
        })->name('admin.pending-requests.count');
        
        Route::get('admin/active-assignments/count', function () {
            $count = \App\Models\Assignment::whereNull('actual_return_date')->count();
            return response()->json(['count' => $count]);
        })->name('admin.active-assignments.count');
        
        Route::get('admin/pending-maintenance/count', function () {
            $count = \App\Models\MaintenanceLog::whereIn('status', ['reported', 'in_progress'])->count();
            return response()->json(['count' => $count]);
        })->name('admin.pending-maintenance.count');
        
        Route::get('admin/pending-inspections/count', function () {
            $count = \App\Models\Inspection::where('result', 'partial')->count();
            return response()->json(['count' => $count]);
        })->name('admin.pending-inspections.count');
        
        Route::get('admin/pending-amendments/count', function () {
            $count = \App\Models\SpecificationAmendment::where('status', 'pending')->count();
            return response()->json(['count' => $count]);
        })->name('admin.pending-amendments.count');
        
        Route::get('admin/expiring-licenses/count', function () {
            $count = \App\Models\Product::where('type', 'software')
                ->where('license_expiry_date', '<=', now()->addDays(30))
                ->where('license_expiry_date', '>=', now())
                ->count();
            return response()->json(['count' => $count]);
        })->name('admin.expiring-licenses.count');
        
        Route::get('admin/pending-direct-requests/count', function () {
            $count = \App\Models\DirectRequest::where('status', 'pending_director')->count();
            return response()->json(['count' => $count]);
        })->name('admin.pending-direct-requests.count');
        
        Route::get('admin/low-stock/count', function () {
            $count = \App\Models\Product::whereColumn('quantity', '<=', 'min_stock_level')
                ->where('quantity', '>', 0)
                ->count();
            return response()->json(['count' => $count]);
        })->name('admin.low-stock.count');
        
        Route::get('admin/expired-warranties/count', function () {
            $count = \App\Models\Product::where('warranty_end_date', '<', now())
                ->whereNotNull('warranty_end_date')
                ->count();
            return response()->json(['count' => $count]);
        })->name('admin.expired-warranties.count');
        
        Route::get('admin/overdue-assignments/count', function () {
            $count = \App\Models\Assignment::whereNull('actual_return_date')
                ->where('expected_return_date', '<', now())
                ->count();
            return response()->json(['count' => $count]);
        })->name('admin.overdue-assignments.count');
        
        Route::get('admin/activity-logs/today/count', function () {
            $count = \App\Models\ActivityLog::whereDate('performed_at', today())->count();
            return response()->json(['count' => $count]);
        })->name('admin.activity-logs.today.count');
        
        // ============================================
        // ADMIN STATS API
        // ============================================
        Route::get('admin/stats', function () {
            $stats = [
                'total_products' => \App\Models\Product::count(),
                'available_products' => \App\Models\Product::where('status', 'available')->sum('quantity'),
                'pending_requests' => \App\Models\AssetRequest::where('status', 'pending_ict_review')->count(),
                'under_repair' => \App\Models\Product::where('status', 'under_repair')->count(),
                'low_stock' => \App\Models\Product::whereColumn('quantity', '<=', 'min_stock_level')
                    ->where('quantity', '>', 0)
                    ->count(),
                'active_assignments' => \App\Models\Assignment::whereNull('actual_return_date')->count(),
                'pending_maintenance' => \App\Models\MaintenanceLog::whereIn('status', ['reported', 'in_progress'])->count(),
                'total_users' => \App\Models\User::count(),
                'expired_warranties' => \App\Models\Product::where('warranty_end_date', '<', now())
                    ->whereNotNull('warranty_end_date')
                    ->count(),
            ];
            return response()->json($stats);
        })->name('admin.stats');
        
        // ============================================
        // NOTIFICATIONS API
        // ============================================
        Route::get('notifications/count', function () {
            $count = \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count();
            return response()->json(['count' => $count]);
        })->name('notifications.count');
        
        Route::post('notifications/mark-all-read', function () {
            \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
            return response()->json(['success' => true]);
        })->name('notifications.mark-all-read');
    });

    // routes/web.php
Route::get('/lang/{locale}', function ($locale) {
    $available = array_keys(config('app.available_locales'));
    
    if (in_array($locale, $available)) {
        session(['locale' => $locale]);
    }
    
    return redirect()->back();
})->name('lang.switch');

// ============================================
// FALLBACK ROUTE (404 handling)
// ============================================
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
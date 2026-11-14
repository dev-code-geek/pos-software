<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permission groups
        $permissionGroups = [
            // Dashboard
            'dashboard' => [
                'dashboard.view',
                'dashboard.edit',
            ],
            
            // User Management
            'user' => [
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                'user.approve',
                'user.suspend',
                'user.reactivate',
                'user.block',
                'user.reset_password',
                'user.export',
                'user.import',
                'user.assign_role',
                'user.remove_role',
                'user.manage_permissions',
            ],
            
            // Role Management
            'role' => [
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'role.assign_permissions',
            ],
            
            // Department Management
            'department' => [
                'department.view',
                'department.create',
                'department.edit',
                'department.delete',
            ],
            
            // Category Management
            'category' => [
                'category.view',
                'category.create',
                'category.edit',
                'category.delete',
                'category.export',
                'category.import',
            ],
            
            // Supplier Management
            'supplier' => [
                'supplier.view',
                'supplier.create',
                'supplier.edit',
                'supplier.delete',
                'supplier.export',
                'supplier.import',
                'supplier.approve',
            ],
            
            // Product Management
            'product' => [
                'product.view',
                'product.create',
                'product.edit',
                'product.delete',
                'product.export',
                'product.import',
                'product.bulk_delete',
                'product.bulk_export',
                'product.view_hardware',
                'product.view_software',
                'product.manage_hardware',
                'product.manage_software',
            ],
            
            // Assignment Management
            'assignment' => [
                'assignment.view',
                'assignment.create',
                'assignment.edit',
                'assignment.delete',
                'assignment.return',
                'assignment.bulk_create',
                'assignment.bulk_return',
                'assignment.view_history',
                'assignment.export',
            ],
            
            // Stock Management
            'stock' => [
                'stock.view',
                'stock.create',
                'stock.adjust',
                'stock.export',
                'stock.report',
                'stock.audit',
            ],
            
            // Purchase Order Management
            'purchase_order' => [
                'purchase_order.view',
                'purchase_order.create',
                'purchase_order.edit',
                'purchase_order.delete',
                'purchase_order.submit',
                'purchase_order.approve',
                'purchase_order.reject',
                'purchase_order.receive',
                'purchase_order.export',
                'purchase_order.import',
                'purchase_order.bulk_delete',
                'purchase_order.bulk_approve',
                'purchase_order.download_pdf',
                'purchase_order.print',
            ],
            
            // Maintenance Management
            'maintenance' => [
                'maintenance.view',
                'maintenance.create',
                'maintenance.edit',
                'maintenance.delete',
                'maintenance.approve',
                'maintenance.reject',
                'maintenance.repair',
                'maintenance.complete',
                'maintenance.export',
                'maintenance.import',
                'maintenance.download',
            ],
            
            // Asset Request Management
            'asset_request' => [
                'asset_request.view',
                'asset_request.create',
                'asset_request.edit',
                'asset_request.delete',
                'asset_request.review',
                'asset_request.approve',
                'asset_request.reject',
                'asset_request.forward_to_director',
                'asset_request.forward_to_procurement',
                'asset_request.specify',
                'asset_request.cancel',
                'asset_request.export',
                'asset_request.import',
                'asset_request.bulk_delete',
                'asset_request.bulk_approve',
                'asset_request.bulk_reject',
                'asset_request.view_my_requests',
                'asset_request.view_all_requests',
            ],
            
            // Procurement Management
            'procurement' => [
                'procurement.view',
                'procurement.create',
                'procurement.edit',
                'procurement.delete',
                'procurement.approve',
                'procurement.reject',
                'procurement.receive',
                'procurement.export',
                'procurement.import',
                'procurement.bulk_delete',
                'procurement.bulk_approve',
                'procurement.download_pdf',
            ],
            
            // Inspection Management
            'inspection' => [
                'inspection.view',
                'inspection.create',
                'inspection.edit',
                'inspection.delete',
                'inspection.pass',
                'inspection.fail',
                'inspection.export',
                'inspection.import',
                'inspection.return_to_supplier',
                'inspection.bulk_pass',
                'inspection.bulk_fail',
            ],
            
            // Direct Request Management
            'direct_request' => [
                'direct_request.view',
                'direct_request.create',
                'direct_request.edit',
                'direct_request.delete',
                'direct_request.approve',
                'direct_request.reject',
                'direct_request.forward_to_ict',
                'direct_request.forward_to_procurement',
                'direct_request.merge_to_request',
                'direct_request.export',
            ],
            
            // Notification Management
            'notification' => [
                'notification.view',
                'notification.create',
                'notification.mark_read',
                'notification.mark_unread',
                'notification.mark_all_read',
                'notification.delete',
                'notification.bulk_delete',
                'notification.export',
                'notification.settings',
                'notification.configure',
            ],
            
            // Report Management
            'report' => [
                'report.view',
                'report.generate',
                'report.export',
                'report.print',
                'report.schedule',
                'report.inventory',
                'report.assignment',
                'report.request',
                'report.procurement',
                'report.maintenance',
                'report.financial',
                'report.custom',
            ],
            
            // Settings Management
            'settings' => [
                'settings.view',
                'settings.edit',
                'settings.manage',
                'settings.system_config',
                'settings.email_config',
                'settings.backup',
                'settings.audit_logs',
            ],
            
            // Software License Management
            'license' => [
                'license.view',
                'license.create',
                'license.edit',
                'license.delete',
                'license.assign',
                'license.revoke',
                'license.export',
                'license.import',
                'license.renew',
                'license.view_expired',
                'license.view_available',
            ],
            
            // Activity Log Management
            'activity_log' => [
                'activity_log.view',
                'activity_log.export',
                'activity_log.delete',
                'activity_log.clear',
                'activity_log.filter',
            ],
            
            // API Management
            'api' => [
                'api.access',
                'api.manage_tokens',
                'api.revoke_tokens',
            ],

            'activity_log' => [
                'activity_log.view',
                'activity_log.delete',
                'activity_log.export',
            ],
              // Backup Management (NEW - Separate group for backup)
            'backup' => [
                'backup.view',
                'backup.create',
                'backup.delete',
                'backup.download',
                'backup.restore',
                'backup.schedule',
                'backup.configure',
            ],
            // Facility Management
            'facility' => [
                'facility.view',
                'facility.create',
                'facility.edit',
                'facility.delete',
                'facility.export',
                'facility.import',
                'facility.verify',
            ],

            // Sponsor Management
            'sponsor' => [
                'sponsor.view',
                'sponsor.create',
                'sponsor.edit',
                'sponsor.delete',
                'sponsor.export',
                'sponsor.import',
                'sponsor.verify',
            ],

            // Currency Management
            'currency' => [
                'currency.view',
                'currency.create',
                'currency.edit',
                'currency.delete',
                'currency.export',
                'currency.import',
                'currency.toggle_status',
                'currency.set_base',
            ],

            // Exchange Rates
            'exchange_rate' => [
                'exchange_rate.view',
                'exchange_rate.create',
                'exchange_rate.edit',
                'exchange_rate.delete',
                'exchange_rate.export',
                'exchange_rate.fetch_latest',
            ],

            // Warehouse Management
            'warehouse' => [
                'warehouse.view',
                'warehouse.create',
                'warehouse.edit',
                'warehouse.delete',
                'warehouse.export',
                'warehouse.import',
                'warehouse.toggle_status',
            ],

            // Product Variants
            'variant' => [
                'variant.view',
                'variant.create',
                'variant.edit',
                'variant.delete',
                'variant.toggle_status',
            ],

            // Quotations
            'quotation' => [
                'quotation.view',
                'quotation.create',
                'quotation.edit',
                'quotation.delete',
                'quotation.export',
                'quotation.send',
                'quotation.accept',
                'quotation.reject',
                'quotation.convert_to_sale',
                'quotation.print',
                'quotation.pdf',
            ],

            // Sales (Enhanced)
            'sale' => [
                'sale.view',
                'sale.create',
                'sale.edit',
                'sale.delete',
                'sale.export',
                'sale.bulk_delete',
                'sale.pay',
                'sale.cancel',
                'sale.print',
                'sale.pdf',
            ],

            // Invoices
            'invoice' => [
                'invoice.view',
                'invoice.create',
                'invoice.print',
                'invoice.pdf',
                'invoice.export',
                'invoice.mark_paid',
                'invoice.send_reminder',
            ],

            // Debit Notes (Supplier Returns)
            'debit_note' => [
                'debit_note.view',
                'debit_note.create',
                'debit_note.edit',
                'debit_note.delete',
                'debit_note.export',
                'debit_note.approve',
                'debit_note.reject',
                'debit_note.print',
                'debit_note.pdf',
            ],

            // Credit Notes (Customer Returns)
            'credit_note' => [
                'credit_note.view',
                'credit_note.create',
                'credit_note.edit',
                'credit_note.delete',
                'credit_note.export',
                'credit_note.approve',
                'credit_note.reject',
                'credit_note.print',
                'credit_note.pdf',
            ],

            // Stock Adjustments
            'stock_adjustment' => [
                'stock_adjustment.view',
                'stock_adjustment.create',
                'stock_adjustment.edit',
                'stock_adjustment.delete',
                'stock_adjustment.export',
                'stock_adjustment.approve',
                'stock_adjustment.cancel',
            ],

            // Stock Transfers
            'stock_transfer' => [
                'stock_transfer.view',
                'stock_transfer.create',
                'stock_transfer.edit',
                'stock_transfer.delete',
                'stock_transfer.export',
                'stock_transfer.approve',
                'stock_transfer.complete',
                'stock_transfer.cancel',
            ],

            // Customer Management
                'customer' => [
                    'customer.view',
                    'customer.create',
                    'customer.edit',
                    'customer.delete',
                    'customer.export',
                    'customer.import',
                    'customer.toggle_status',
                    'customer.bulk_delete',
                ],
                 
                'sales_payment' => [
                    'sales_payment.view',
                    'sales_payment.create',
                    'sales_payment.edit',
                    'sales_payment.delete',
                    'sales_payment.export',
                ],

                'purchase_payment' => [
                    'purchase_payment.view',
                    'purchase_payment.create',
                    'purchase_payment.edit',
                    'purchase_payment.delete',
                    'purchase_payment.export',
                ],

                'branch' => [
                    'branch.view',
                    'branch.create',
                    'branch.edit',
                    'branch.delete',
                    'branch.export',
                    'branch.import',
                    'branch.toggle_status',
                    'branch.bulk_delete',
                ],

                'tax_rate' => [
                    'tax_rate.view',
                    'tax_rate.create',
                    'tax_rate.edit',
                    'tax_rate.delete',
                    'tax_rate.export',
                    'tax_rate.import',
                    'tax_rate.toggle_status',
                    'tax_rate.bulk_delete',
                ],
                // Stock
                'stock' => [
                    'stock.view',
                    'stock.export',
                    // 'stock.adjust' – handled by adjustments module
                ],

                // ============================================
                // COMPANY MANAGEMENT
                // ============================================
                'company' => [
                    'company.view',
                    'company.create',
                    'company.edit',
                    'company.delete',
                    'company.export',
                    'company.toggle_status',
                ],

                // ============================================
                // BRANCH MANAGEMENT
                // ============================================
                'branch' => [
                    'branch.view',
                    'branch.create',
                    'branch.edit',
                    'branch.delete',
                    'branch.export',
                    'branch.toggle_status',
                ],

                // ============================================
                // EMPLOYEE MANAGEMENT
                // ============================================
                'employee' => [
                    'employee.view',
                    'employee.create',
                    'employee.edit',
                    'employee.delete',
                    'employee.export',
                    'employee.toggle_status',
                ],

                // ============================================
                // LEAVE TYPE MANAGEMENT
                // ============================================
                'leave_type' => [
                    'leave_type.view',
                    'leave_type.create',
                    'leave_type.edit',
                    'leave_type.delete',
                    'leave_type.export',
                    'leave_type.toggle_status',
                ],

                // ============================================
                // LEAVE BALANCE MANAGEMENT
                // ============================================
                'leave_balance' => [
                    'leave_balance.view',
                    'leave_balance.edit',           // update individual balances
                    'leave_balance.initialize',     // initialize yearly balances
                    'leave_balance.export',
                ],

                // ============================================
                // LEAVE REQUEST MANAGEMENT
                // ============================================
                'leave_request' => [
                    'leave_request.view',
                    'leave_request.create',
                    'leave_request.edit',
                    'leave_request.delete',
                    'leave_request.approve',
                    'leave_request.reject',
                    'leave_request.cancel',
                    'leave_request.bulk_approve',
                    'leave_request.bulk_reject',
                    'leave_request.bulk_delete',
                ],

                // ============================================
                // ATTENDANCE MANAGEMENT
                // ============================================
                'attendance' => [
                    'attendance.view',
                    'attendance.create',
                    'attendance.edit',
                    'attendance.delete',
                    'attendance.approve',
                    'attendance.export',
                    'attendance.bulk_create',       // bulk store attendance
                ],

                // ============================================
                // PAYROLL PERIOD MANAGEMENT
                // ============================================
                'payroll_period' => [
                    'payroll_period.view',
                    'payroll_period.create',
                    'payroll_period.edit',
                    'payroll_period.delete',
                    'payroll_period.lock',
                    'payroll_period.unlock',
                    'payroll_period.approve',
                    'payroll_period.export',
                ],

                // ============================================
                // PAYROLL CALCULATION MANAGEMENT
                // ============================================
                'payroll_calculation' => [
                    'payroll_calculation.view',
                    'payroll_calculation.create',   // manual calculation
                    'payroll_calculation.calculate', // calculate payroll
                    'payroll_calculation.edit',
                    'payroll_calculation.delete',
                    'payroll_calculation.verify',   // verify calculation
                    'payroll_calculation.pay',      // process payment
                    'payroll_calculation.export',
                ],

                // ============================================
                // PAYSLIP MANAGEMENT
                // ============================================
                'payslip' => [
                    'payslip.view',
                    'payslip.generate',
                    'payslip.download',
                    'payslip.email',
                    'payslip.print',
                    'payslip.regenerate',
                    'payslip.delete',
                ],

                // ============================================
                // EMPLOYEE LOAN MANAGEMENT
                // ============================================
                'employee_loan' => [
                    'employee_loan.view',
                    'employee_loan.create',
                    'employee_loan.edit',
                    'employee_loan.delete',
                    'employee_loan.approve',
                    'employee_loan.reject',
                    'employee_loan.disburse',
                    'employee_loan.suspend',
                    'employee_loan.resume',
                    'employee_loan.complete',
                    'employee_loan.export',
                ],

                // ============================================
                // OVERTIME MANAGEMENT
                // ============================================
                'overtime' => [
                    'overtime.view',
                    'overtime.create',
                    'overtime.edit',
                    'overtime.delete',
                    'overtime.approve',
                    'overtime.reject',
                    'overtime.export',
                ],

                // ============================================
                // PAYROLL ADJUSTMENT MANAGEMENT (Allowances/Deductions)
                // ============================================
                'payroll_adjustment' => [
                    'payroll_adjustment.view',
                    'payroll_adjustment.create',
                    'payroll_adjustment.edit',
                    'payroll_adjustment.delete',
                    'payroll_adjustment.approve',
                    'payroll_adjustment.reject',
                    'payroll_adjustment.export',
                ],

                // ============================================
                // PAYROLL RATE MANAGEMENT (PAYE, NSSF, Allowance rates)
                // ============================================
                'payroll_rate' => [
                    'payroll_rate.view',
                    'payroll_rate.create',
                    'payroll_rate.edit',
                    'payroll_rate.delete',
                    'payroll_rate.export',
                ],
        ];

        // Create all permissions
        $allPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permName) {
                $permission = Permission::firstOrCreate([
                    'name' => $permName,
                    'guard_name' => 'web'
                ]);
                $allPermissions[] = $permission;
            }
        }

        // ============================================
        // 1. SUPER_ADMIN - Gets ALL permissions
        // ============================================
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web'
        ]);
        $superAdminRole->syncPermissions($allPermissions);

        // ============================================
        // 2. OTHER ROLES - NO permissions by default
        //    (Admin will assign permissions later)
        // ============================================
        $otherRoles = [
            'admin',
            'ict_officer',
            'director',
            'procurement_officer',
            'manager',
            'user'
        ];
        
        foreach ($otherRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            
            // IMPORTANT: Sync with NO permissions
            // This ensures the role exists but has no permissions
            $role->syncPermissions([]);
        }

        // ============================================
        // 3. (Optional) Pre-assign some permissions 
        //    for faster setup - Comment this out if you
        //    want to assign everything manually
        // ============================================
        // Uncomment the following if you want to pre-assign basic permissions
        /*
        $basicPermissions = [
            'dashboard.view',
            'product.view',
            'assignment.view',
            'asset_request.create',
            'asset_request.view',
            'notification.view',
        ];
        
        $userRole = Role::where('name', 'user')->first();
        $userRole->syncPermissions($basicPermissions);
        */
    }
}
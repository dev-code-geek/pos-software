<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log authenticated users and specific routes
        if (auth()->check() && $this->shouldLog($request)) {
            $routeName = $request->route() ? $request->route()->getName() : '';
            $module = $this->getModuleFromRoute($routeName);
            $action = $this->getActionFromRoute($routeName, $request->method());
            
            // Skip logging for ajax requests that are just fetching data
            if ($request->ajax() && $request->method() === 'GET') {
                return $response;
            }

            // Log the activity if we can determine the action and it's not an index/view
            if ($action && $module && $action !== 'viewed') {
                ActivityLogger::log(
                    $action,
                    $module,
                    "User performed {$action} on {$module}",
                    [
                        'route' => $routeName,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'data' => $request->except(['password', 'password_confirmation']),
                    ]
                );
            }
        }

        return $response;
    }

    /**
     * Determine if the request should be logged.
     */
    private function shouldLog(Request $request)
    {
        // Skip logging for certain paths
        $skipPaths = [
            'api/*',
            '_debugbar/*',
            'horizon/*',
            'telescope/*',
            'livewire/*',
        ];

        foreach ($skipPaths as $path) {
            if ($request->is($path)) {
                return false;
            }
        }

        // Skip logging for GET requests that are just loading pages
        if ($request->method() === 'GET' && !$request->ajax()) {
            // Only log GET requests for specific actions (not just page loads)
            $actions = ['show', 'edit', 'export', 'print', 'download'];
            $routeName = $request->route() ? $request->route()->getName() : '';
            
            foreach ($actions as $action) {
                if (str_contains($routeName, $action)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Get module from route name.
     */
    private function getModuleFromRoute($routeName)
    {
        $parts = explode('.', $routeName);
        
        // Check if it's an admin route
        if (isset($parts[0]) && $parts[0] === 'admin') {
            return $parts[1] ?? 'unknown';
        }
        
        return count($parts) > 1 ? $parts[0] : 'unknown';
    }

    /**
     * Get action from route name and method.
     */
    private function getActionFromRoute($routeName, $method)
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        
        // Map HTTP methods to actions
        $methodMap = [
            'POST' => 'created',
            'PUT' => 'updated',
            'PATCH' => 'updated',
            'DELETE' => 'deleted',
        ];

        // Check if the route name indicates a specific action
        $actionMap = [
            'store' => 'created',
            'update' => 'updated',
            'destroy' => 'deleted',
            'approve' => 'approved',
            'reject' => 'rejected',
            'assign' => 'assigned',
            'return' => 'returned',
            'resolve' => 'resolved',
            'export' => 'exported',
            'import' => 'imported',
            'backup' => 'backup',
            'restore' => 'restored',
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
            'verify' => 'verified',
        ];

        // Check if the action exists in the action map
        if (isset($actionMap[$action])) {
            return $actionMap[$action];
        }

        // Use HTTP method mapping for CRUD operations
        if (in_array($action, ['index', 'show', 'edit', 'create'])) {
            return 'viewed';
        }

        return $methodMap[$method] ?? 'viewed';
    }
}
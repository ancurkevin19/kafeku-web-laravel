<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Traits\HasRoles;

class AttendanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ... $permissions)
    {
        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if (! auth()->user()->hasPermissionTo($permission)) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
                ], 403);
            }
        }

        return $next($request);
    }
}
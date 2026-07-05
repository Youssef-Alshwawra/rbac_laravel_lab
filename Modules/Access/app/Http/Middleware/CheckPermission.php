<?php

namespace Modules\Access\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        if(!$user || !$user->hasPermission($permission)) { 
            abort(403, 'You do not have permission to do this action');
        }
        
        return $next($request);
    }
}

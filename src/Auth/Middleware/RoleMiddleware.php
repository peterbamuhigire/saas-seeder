<?php
namespace App\Auth\Middleware;

class RoleMiddleware 
{
    public function handle($request, $next, ...$roles)
    {
        $user = $request->getUser();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Insufficient role permissions'
        ], 403);
    }
}

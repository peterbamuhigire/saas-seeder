<?php
namespace App\Auth\Middleware;

class PermissionMiddleware 
{
    public function handle($request, $next, ...$permissions)
    {
        $user = $request->getUser();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient permissions'
                ], 403);
            }
        }

        return $next($request);
    }

    public function validateModuleAccess($request, $module)
    {
        $user = $request->getUser();
        $franchise = $request->getFranchise();
        
        if (!$franchise || !$user) {
            return false;
        }

        return $user->hasModuleAccess($module, $franchise->getId());
    }
}

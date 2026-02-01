<?php
namespace App\Auth\Middleware;

class AuthMiddleware 
{
    private $authService;
    
    public function __construct($authService) 
    {
        $this->authService = $authService;
    }

    public function handle($request, $next)
    {
        $token = $request->getBearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'No authentication token provided'
            ], 401);
        }

        $session = $this->authService->validateSession($token);
        
        if (!$session) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Invalid or expired session'
            ], 401);
        }

        $request->setUser($session->getUser());
        $request->setFranchise($session->getFranchise());
        
        return $next($request);
    }
}

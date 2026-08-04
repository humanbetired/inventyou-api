<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowedRoles = array_map(fn (string $role) => UserRole::from($role), $roles);

        if (! in_array($user->role, $allowedRoles, strict: true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk aksi ini.'], 403);
        }

        return $next($request);
    }
}
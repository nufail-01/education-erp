<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

  
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId($user ? ($user->institute_id ?? 0) : null);

        return $next($request);
    }
}
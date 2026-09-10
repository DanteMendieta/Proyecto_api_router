<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('No tienes permisos para acceder a esta sección', 403);
        }

        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->success([
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'last_page' => $users->lastPage(),
            'data' => $users->items(),
        ], 'Usuarios administradores obtenidos correctamente');
    }
}

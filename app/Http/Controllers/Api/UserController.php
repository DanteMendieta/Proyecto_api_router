<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        return $this->success($users, 'Usuarios obtenidos correctamente');
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if ($user->id !== $request->user()->id) {
            return $this->error('No tienes permisos para ver este usuario', 403);
        }

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ], 'Usuario obtenido correctamente');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->id !== $request->user()->id) {
            return $this->error('No tienes permisos para actualizar este usuario', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->fill($validated);
        $user->save();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at,
        ], 'Usuario actualizado correctamente');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id !== $request->user()->id) {
            return $this->error('No tienes permisos para eliminar este usuario', 403);
        }

        $user->delete();

        return $this->success(null, 'Usuario eliminado correctamente');
    }
}

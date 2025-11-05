<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manageUsers', Admin::class);

        $users = User::paginate(10);
        return $this->paginatedResponse($users, $users, 'Users retrieved successfully');
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('manageUsers', Admin::class);

        return $this->successResponse($user, 'User retrieved successfully');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('manageUsers', Admin::class);

        $validated = $request->validate([
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'status' => 'in:Actif,Inactif',
            'role' => 'in:Admin,Client',
        ]);

        $user->update($validated);
        return $this->successResponse($user, 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('manageUsers', Admin::class);

        $user->delete();
        return $this->successResponse(null, 'User deleted successfully');
    }
}
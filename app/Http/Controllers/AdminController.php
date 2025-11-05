<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    use ApiResponseTrait;

    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('view', Admin::class);

        $stats = [
            'total_users' => User::count(),
            'total_admins' => Admin::count(),
            'total_clients' => User::where('role', 'Client')->count(),
            'active_accounts' => User::where('status', 'Actif')->count(),
        ];

        return $this->successResponse($stats, 'Dashboard data retrieved successfully');
    }
}
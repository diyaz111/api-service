<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexUsersRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\AccountCreatedMail;
use App\Mail\NewUserNotificationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        Mail::to($user->email)->send(new AccountCreatedMail($user));

        $administrators = User::where('role', 'administrator')->where('active', true)->get();
        foreach ($administrators as $administrator) {
            Mail::to($administrator->email)->send(new NewUserNotificationMail($user));
        }

        return ApiResponse::success([
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at->toIso8601String(),
        ], 'User created successfully.', 201);
    }

    public function index(IndexUsersRequest $request): JsonResponse
    {
        $query = User::query()
            ->where('active', true)
            ->withCount('orders');

        $search = $request->getSearch();
        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($request->getSortBy());

        $paginator = $query->paginate(15, ['*'], 'page', $request->getPage());
        $currentUser = $request->user('sanctum');

        $users = $paginator->getCollection()->map(function (User $user) use ($currentUser) {
            return [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'created_at' => $user->created_at->toIso8601String(),
                'orders_count' => (int) $user->orders_count,
                'can_edit' => $this->canEdit($currentUser, $user),
            ];
        });

        return ApiResponse::success([
            'page' => (int) $paginator->currentPage(),
            'users' => $users->values()->all(),
        ], 'Users fetched successfully.');
    }

    private function canEdit(?User $currentUser, User $targetUser): bool
    {
        if ($currentUser === null) {
            return false;
        }

        if ($currentUser->role === 'administrator') {
            return true;
        }

        if ($currentUser->role === 'manager') {
            return $targetUser->role === 'user';
        }

        return $currentUser->id === $targetUser->id;
    }
}

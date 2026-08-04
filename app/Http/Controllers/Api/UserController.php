<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::with('branch')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'data' => $user->load('branch'),
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'data' => $user->load('branch'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $newRole = $validated['role'] ?? $user->role->value;
        $newBranchId = $validated['branch_id'] ?? $user->branch_id;

        if ($newRole !== 'super_admin' && ! $newBranchId) {
            return response()->json([
                'message' => 'Cabang wajib dipilih untuk role ini.',
                'errors' => ['branch_id' => ['Cabang wajib dipilih untuk role ini.']],
            ], 422);
        }

        $user->update($validated);

        return response()->json([
            'data' => $user->load('branch'),
        ]);
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'User tidak bisa dihapus karena masih memiliki data terkait (misal pengajuan atau approval).',
            ], 422);
        }

        return response()->json(null, 204);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Branch::all(),
        ]);
    }

    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'data' => $branch,
        ], 201);
    }

    public function show(Branch $branch)
    {
        return response()->json([
            'data' => $branch,
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return response()->json([
            'data' => $branch,
        ]);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json(null, 204);
    }
}
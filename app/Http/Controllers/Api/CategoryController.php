<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Category::all(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'data' => $category,
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'data' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json([
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena masih memiliki produk terkait. Pindahkan atau hapus produk tersebut terlebih dahulu.',
            ], 422);
        }

        return response()->json(null, 204);
    }
}
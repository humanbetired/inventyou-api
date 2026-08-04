<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Product::with('category')->get(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'data' => $product->load('category'),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'data' => $product->load('category'),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'data' => $product->load('category'),
        ]);
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Produk tidak bisa dihapus karena masih memiliki data stok terkait.',
            ], 422);
        }

        return response()->json(null, 204);
    }
}
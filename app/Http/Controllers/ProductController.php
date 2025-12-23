<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected function index() {
        return response()->json(Product::select('id', 'name', 'price', 'quantity')->get());
    }

    protected function create(CreateProductRequest $req) {
        Product::create($req->validated());

        return response()->json(
            ['message' => 'Success']
        );
    }

    protected function show(Product $product) {
        return response()->json($product);
    }

    protected function destroy(Product $product) {
        $product->delete();
        return response()->json(['message' => 'Success']);
    }

    protected function update(Product $product, UpdateProductRequest $req) {
        $product->update($req->validated());
        return response()->json(['message' => 'Success']);
    }
}

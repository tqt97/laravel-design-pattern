<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Services\Products\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    public function create()
    {
        return view('products.create-service');
    }
    public function store(StoreProductRequest $request)
    {
        // $data = $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        // ]);

        try {
            $product = $this->productService->createProduct($request->all());
            return response()->json(['product' => $product, 'message' => 'Sản phẩm đã được tạo thành công'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

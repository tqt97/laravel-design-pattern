<?php

namespace App\Http\Controllers\Repositories;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\Products\ProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $products = $this->productRepository->all();

        return view('products.index', ['products' => $products]);
    }

    //  Add a new method to search for a product by name
    public function search(Request $request)
    {
        $products = $this->productRepository->search($request->name);

        return response()->json($products);
    }

    public function show(int $id)
    {
        $product = $this->productRepository->find($id);

        return view('products.show', ['product' => $product]);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->productRepository->create($request->all());

        return view('products.show', ['product' => $product]);
    }

    public function edit(int $id)
    {
        $product = $this->productRepository->find($id);

        return view('products.edit', ['product' => $product]);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productRepository->update($id, $request->all());

        return view('products.show', ['product' => $product]);
    }

    public function destroy(int $id)
    {
        $this->productRepository->delete($id);
        // return response()->json(null, 204);
        return redirect()->route('products.index');
    }
}

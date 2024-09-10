<?php

namespace App\Repositories\Products;

use App\Models\Product;
use App\Repositories\Products\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{

    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        // paginate the results
        return $this->model
            ->select('id', 'name', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function find(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->model->findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id): void
    {
        $product = $this->find($id);
        $product->delete();
    }

    public function search(string $name)
    {
        return $this->model->where('name', 'LIKE', "%$name%")->get();
    }
}

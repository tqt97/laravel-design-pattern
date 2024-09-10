<?php

namespace App\Repositories\Products;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function all();

    public function find(int $id): Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): void;

    // Add a new method to search for a product by name
    public function search(string $text);
}

<?php

namespace App\Services\Products;

use App\Events\ProductCreated;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function createProduct(array $data)
    {
        DB::beginTransaction();

        try {
            // Tạo sản phẩm
            $product = Product::create($data);

            // Gửi thông báo xác nhận
            // Notification::send($product->user, new ProductCreatedNotification($product));
            event(new ProductCreated($product));

            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error when creating product');
        }
    }
}

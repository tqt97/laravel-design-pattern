# Repository Pattern

> **Repository Pattern** trong Laravel giúp tách biệt logic truy cập dữ liệu ra khỏi phần còn lại của ứng dụng, làm cho code dễ bảo trì, mở rộng và kiểm thử hơn. Nó cung cấp một lớp trung gian giữa tầng ứng dụng và tầng dữ liệu.

## Lợi ích của Repository Pattern

- Tách biệt logic truy cập dữ liệu: Giúp tập trung logic truy cập dữ liệu vào một lớp riêng biệt.
- Dễ bảo trì và mở rộng: Việc thay đổi cách thức lưu trữ dữ liệu (ví dụ từ MySQL sang MongoDB) dễ dàng hơn vì chỉ cần thay đổi trong Repository.
- Dễ dàng kiểm thử: Vì Repository có thể được mock trong quá trình kiểm thử.ách chi tiết.

## Triển khai Repository Pattern trong Laravel

### Bước 1: Tạo Interface cho Repository

Tạo một interface định nghĩa các phương thức chính để truy cập dữ liệu.

```php
<?php

namespace App\Repositories;

interface ProductRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
```

### Bước 2: Tạo lớp Repository triển khai Interface

Lớp Repository triển khai các phương thức trong Interface, làm việc với model Eloquent để truy xuất và thao tác với dữ liệu.

```php
<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $product = $this->findById($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id)
    {
        $product = $this->findById($id);
        return $product->delete();
    }
}
```

### Bước 3: Đăng ký Repository trong Service Provider

Trong Laravel 11, vẫn cần bind Interface và Repository cụ thể trong một Service Provider để Laravel biết phải sử dụng Repository nào khi cần.

Tạo một Service Provider mới nếu chưa có:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    public function boot()
    {
        //
    }
}
```

Sau đó thêm vào file `config/app.php` trong phần `providers`:

```php
'providers' => [
    // Các service providers khác...
    App\Providers\RepositoryServiceProvider::class,
],
```

### Bước 4: Sử dụng Repository trong Controller

Controller sẽ sử dụng Dependency Injection để lấy Repository thông qua Interface đã bind.

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepositoryInterface;
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
        $products = $this->productRepository->getAll();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = $this->productRepository->findById($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $product = $this->productRepository->create($request->all());
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = $this->productRepository->update($id, $request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->productRepository->delete($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
```

### Bước 5: Kiểm thử và triển khai

Nhờ có Interface, Repository dễ dàng mock để kiểm thử mà không cần phụ thuộc vào cơ sở dữ liệu thực tế:

```php
public function test_it_returns_all_products()
{
    $productRepoMock = Mockery::mock(ProductRepositoryInterface::class);
    $productRepoMock->shouldReceive('all')->andReturn([
            ['name' => 'Organce', 'description' => 'A sweet fruit'],
            ['name' => 'Banana', 'description' => 'A long yellow fruit'],
            ['name' => 'Apple', 'description' => 'A round red fruit'],
        ]);

    $this->app->instance(ProductRepositoryInterface::class, $productRepoMock);

    $response = $this->get('/products');
    $response->assertSee('Orange');
}
```

Run test:
```php artisan test --filter ProductRepositoriesTest```

### Mở rộng Repository

Với **Repository Pattern**, ta có thể dễ dàng mở rộng logic truy xuất dữ liệu mà không ảnh hưởng đến controller.

Giả sử ta cần thêm tính năng lọc sản phẩm theo giá. Chỉ cần thêm phương thức getByPriceRange() vào repository.

```php
public function getByPriceRange($min, $max)
{
    return $this->model->whereBetween('price', [$min, $max])->get();
}
```

Sau đó, sử dụng phương thức này trong controller:

```php
public function filterByPrice(Request $request)
{
    $min = $request->query('min_price');
    $max = $request->query('max_price');

    $products = $this->productRepository->getByPriceRange($min, $max);
    return response()->json($products);
}
```
### Những điểm mới có thể xuất hiện trong Laravel 11

- **Typed Properties and Methods**: Laravel 11 có thể yêu cầu sử dụng các thuộc tính và phương thức có kiểu cụ thể, điều này giúp cho code an toàn và rõ ràng hơn.
- **Strict Types**: Laravel 11 có thể hỗ trợ strict typing nhiều hơn, vì vậy có thể cần thêm chú thích kiểu cho các phương thức và thuộc tính.

#### Giải thích

- **ProductRepositoryInterface:** Định nghĩa các phương thức cần có cho Repository, giúp việc tuân thủ nguyên tắc Dependency Inversion Principle (D trong SOLID).
- **ProductRepository:** Triển khai chi tiết các phương thức từ Interface, sử dụng Eloquent để tương tác với database.
- **AppServiceProvider:** Đăng ký bind giữa Interface và Repository cụ thể, đảm bảo khi inject ProductRepositoryInterface, Laravel sẽ cung cấp đối tượng ProductRepository.
- **Controller:** Sử dụng Repository thay vì trực tiếp gọi Eloquent Model, giúp code dễ kiểm thử và tuân thủ nguyên tắc phân chia trách nhiệm.

### Tóm tắt

**Repository Pattern** giúp quản lý logic truy cập dữ liệu hiệu quả hơn, tách biệt logic của ứng dụng và thao tác cơ sở dữ liệu, dễ bảo trì và mở rộng.

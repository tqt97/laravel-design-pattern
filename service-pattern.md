# Service Pattern

**Service Pattern trong Laravel** là một mẫu thiết kế phổ biến, tách biệt logic nghiệp vụ ra khỏi controller, giúp code dễ đọc, bảo trì và tái sử dụng hơn. Trong một dự án thực tế, Service Pattern thường được sử dụng để xử lý các logic phức tạp, giảm sự phụ thuộc của controller vào các chi tiết cụ thể, từ đó tuân thủ nguyên tắc Single Responsibility trong SOLID.

### Khi nào nên sử dụng Service Pattern?

- Khi logic nghiệp vụ quá phức tạp để đặt trong controller.
- Khi bạn cần tái sử dụng logic nghiệp vụ ở nhiều nơi trong ứng dụng.
- Khi muốn dễ dàng kiểm thử các logic này mà không phụ thuộc vào controller hoặc database.

### Triển khai Service Pattern trong Laravel

#### Bước 1: Tạo Service class

Tạo một lớp Service chứa các logic nghiệp vụ chính.

Ví dụ: Ta có một hệ thống quản lý sản phẩm và cần xử lý nghiệp vụ khi tạo sản phẩm mới (bao gồm lưu dữ liệu vào database và gửi email xác nhận).

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Support\Facades\Notification;
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
            Notification::send($product->user, new ProductCreatedNotification($product));

            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Có lỗi xảy ra khi tạo sản phẩm');
        }
    }
}
```

**Giải thích**:

- `DB::beginTransaction()` và `DB::commit()`: Sử dụng transaction để đảm bảo tính toàn vẹn của dữ liệu. Nếu có lỗi trong quá trình tạo sản phẩm hoặc gửi thông báo, hệ thống sẽ rollback lại.
- `Notification::send()`: Gửi thông báo qua email, SMS, hoặc bất kỳ kênh nào khác, giúp đảm bảo nghiệp vụ hoàn chỉnh sau khi sản phẩm được tạo.

#### Bước 2: Sử dụng Service trong Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            $product = $this->productService->createProduct($data);
            return response()->json(['product' => $product, 'message' => 'Sản phẩm đã được tạo thành công'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
```

**Giải thích**:

- **ProductService**: Được inject vào controller qua constructor, controller chỉ cần gọi đến service mà không phải quan tâm đến chi tiết logic của việc tạo sản phẩm.
- **Validation**: Dữ liệu từ request được xác thực trước khi gửi tới service.
- **Exception Handling**: Controller xử lý lỗi từ service và trả về response phù hợp.

#### Bước 3: Kiểm thử Service

Khi sử dụng Service Pattern, logic nghiệp vụ được tách ra dễ kiểm thử hơn mà không cần quan tâm đến controller. Ta có thể tạo các unit test cho `ProductService`.

```php
<?php

namespace Tests\Unit;

use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_create_product_successfully()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        Notification::fake();

        $productService = new ProductService();
        $productData = [
            'name' => 'Product A',
            'price' => 100,
            'description' => 'Sample product',
        ];

        $product = $productService->createProduct($productData);

        $this->assertInstanceOf(Product::class, $product);
        Notification::assertSentTo($product->user, ProductCreatedNotification::class);
    }

    public function test_create_product_failed_rollback()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $productService = new ProductService();
        $this->expectException(\Exception::class);

        // Tạo lỗi bằng cách truyền dữ liệu không hợp lệ
        $productService->createProduct([]);
    }
}
```

**Giải thích**:

- **Notification::fake()**: Giả lập việc gửi thông báo để không thực sự gửi email trong khi kiểm thử.
- **DB::shouldReceive()**: Mock transaction để không can thiệp vào database thực tế.
- **Unit test**: Dễ dàng kiểm tra logic của service mà không cần phụ thuộc vào controller hay các hệ thống khác.

### Bước 4: Refactor và mở rộng

Một trong những lợi ích lớn nhất của Service Pattern là khả năng mở rộng. Giả sử ta cần thêm nghiệp vụ tính chiết khấu khi tạo sản phẩm, chỉ cần mở rộng `ProductService` mà không cần thay đổi controller.

```php
public function createProduct(array $data)
{
    DB::beginTransaction();

    try {
        // Tính chiết khấu nếu có
        if (isset($data['discount'])) {
            $data['price'] -= $data['price'] * ($data['discount'] / 100);
        }

        $product = Product::create($data);

        Notification::send($product->user, new ProductCreatedNotification($product));

        DB::commit();

        return $product;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new \Exception('Có lỗi xảy ra khi tạo sản phẩm');
    }
}
```

### Tóm tắt

- **Service Pattern** giúp tách biệt logic nghiệp vụ ra khỏi controller, giúp code dễ bảo trì, kiểm thử và mở rộng.
- **Tái sử dụng**: Khi cần sử dụng lại logic, bạn chỉ cần gọi service trong các thành phần khác mà không cần viết lại code.
- **Kiểm thử**: Việc kiểm thử dễ dàng hơn khi logic được đặt trong các lớp service riêng biệt.

Trong các dự án thực tế, Service Pattern thường được áp dụng khi cần xử lý các nghiệp vụ phức tạp, đồng thời giữ cho controller đơn giản và dễ bảo trì.

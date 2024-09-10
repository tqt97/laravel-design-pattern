# Event Listener

Trong Laravel, **Event** và **Listener** là một cách hiệu quả để xử lý các hành động cụ thể một cách bất đồng bộ, ví dụ như gửi thông báo sau khi một sản phẩm mới được tạo ra mà không ảnh hưởng đến luồng chính của ứng dụng.

### Bước 1: Tạo Event

Ta sẽ tạo một event gọi là `ProductCreated`, sự kiện này sẽ được kích hoạt khi một sản phẩm mới được tạo.

Chạy lệnh sau để tạo event:

```bash
php artisan make:event ProductCreated
```

Sau khi chạy lệnh, Laravel sẽ tạo ra một file event tại `app/Events/ProductCreated.php`:

```php
<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
```

**Giải thích**:
- **Dispatchable**: Giúp event dễ dàng được dispatch (gọi) ở bất kỳ đâu trong ứng dụng.
- **InteractsWithSockets**: Được sử dụng khi bạn cần tích hợp WebSockets.
- **SerializesModels**: Đảm bảo rằng model có thể được tuần tự hóa (serialize) khi được truyền qua queue.
- **$product**: Sự kiện nhận đối tượng `Product` và truyền nó cho listener hoặc nơi cần sử dụng.

### Bước 2: Tạo Listener

Listener sẽ xử lý công việc khi event `ProductCreated` được kích hoạt. Trong ví dụ này, ta sẽ tạo listener gửi email thông báo khi sản phẩm mới được tạo ra.

Chạy lệnh để tạo listener:

```bash
php artisan make:listener SendProductCreatedNotification --event=ProductCreated
```

Laravel sẽ tạo một file listener tại `app/Listeners/SendProductCreatedNotification.php`:

```php
<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendProductCreatedNotification
{
    public function __construct()
    {
        //
    }

    public function handle(ProductCreated $event)
    {
        $product = $event->product;

        // Gửi thông báo qua email đến tất cả người dùng liên quan
        Notification::send($product->user, new ProductCreatedNotification($product));
    }
}
```

**Giải thích**:
- **handle()**: Hàm này xử lý logic khi sự kiện được kích hoạt, trong trường hợp này là gửi thông báo cho người dùng thông qua email.

### Bước 3: Tạo Notification

Laravel có hệ thống thông báo tích hợp, ta sẽ tạo một notification để gửi email khi sản phẩm được tạo.

Chạy lệnh để tạo notification:

```bash
php artisan make:notification ProductCreatedNotification
```

File notification sẽ được tạo tại `app/Notifications/ProductCreatedNotification.php`:

```php
<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification
{
    use Queueable;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Một sản phẩm mới đã được tạo: ' . $this->product->name)
                    ->action('Xem chi tiết', url('/products/' . $this->product->id))
                    ->line('Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!');
    }
}
```

**Giải thích**:
- **via()**: Xác định các kênh thông báo. Ở đây ta chỉ gửi thông qua email (`mail`), nhưng bạn có thể thêm các kênh khác như SMS, Slack, etc.
- **toMail()**: Định nghĩa nội dung email, bao gồm tên sản phẩm và một liên kết đến trang chi tiết sản phẩm.

### Bước 4: Đăng ký Event và Listener

Ta cần đăng ký sự kiện và listener trong file `EventServiceProvider`.

Mở file `app/Providers/EventServiceProvider.php` và thêm vào:

```php
<?php

namespace App\Providers;

use App\Events\ProductCreated;
use App\Listeners\SendProductCreatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProductCreated::class => [
            SendProductCreatedNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
```

**Giải thích**:
- **$listen**: Ta chỉ định event `ProductCreated` và listener tương ứng `SendProductCreatedNotification`.

### Bước 5: Gọi Event trong Service hoặc Controller

Bây giờ, ta sẽ dispatch (kích hoạt) sự kiện `ProductCreated` khi sản phẩm được tạo thành công trong `ProductService` hoặc `ProductController`.

Ví dụ: Thêm đoạn mã sau vào `ProductService`:

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Events\ProductCreated;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function createProduct(array $data)
    {
        DB::beginTransaction();

        try {
            $product = Product::create($data);

            // Kích hoạt event sau khi tạo sản phẩm
            event(new ProductCreated($product));

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
- **event(new ProductCreated($product))**: Khi một sản phẩm mới được tạo thành công, ta kích hoạt event `ProductCreated`. Laravel sẽ tự động tìm kiếm listener và xử lý công việc tương ứng.

### Bước 6: Kiểm thử Event và Listener

Cuối cùng, ta có thể kiểm thử event và listener bằng cách tạo một sản phẩm mới và kiểm tra xem email thông báo đã được gửi hay chưa.

#### Kiểm thử Listener:

```php
<?php

namespace Tests\Unit;

use App\Events\ProductCreated;
use App\Listeners\SendProductCreatedNotification;
use App\Models\Product;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendProductCreatedNotificationTest extends TestCase
{
    public function test_it_sends_notification_when_product_is_created()
    {
        Notification::fake();

        $product = Product::factory()->create();

        // Kích hoạt event
        event(new ProductCreated($product));

        // Kiểm tra thông báo được gửi
        Notification::assertSentTo($product->user, ProductCreatedNotification::class);
    }
}
```

### Tóm tắt

- **Event** và **Listener** trong Laravel giúp xử lý các công việc không đồng bộ, đặc biệt là những tác vụ như gửi email hoặc thông báo sau khi một sự kiện nhất định xảy ra.
- **Notification** cung cấp một cơ chế đơn giản để gửi email, tin nhắn SMS, Slack, hoặc các kênh khác.
- Bằng cách sử dụng event, bạn có thể giữ cho controller và service đơn giản, tập trung vào nghiệp vụ chính và để các tác vụ phụ được xử lý bởi các listener.

Với cách triển khai này, khi sản phẩm mới được tạo ra, hệ thống sẽ tự động gửi email thông báo cho người dùng mà không làm ảnh hưởng đến luồng chính của ứng dụng.

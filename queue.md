# Queue


Để gửi email cho người dùng khi có sản phẩm mới được thêm vào hệ thống bằng cách sử dụng **Queue** trong Laravel, bạn có thể làm theo các bước dưới đây. Queue giúp xử lý các công việc nặng nề (như gửi email) một cách bất đồng bộ, giúp cải thiện hiệu suất và tốc độ phản hồi của ứng dụng.

### Bước 1: Cấu hình Queue

Đầu tiên, bạn cần cấu hình queue trong Laravel. Laravel hỗ trợ nhiều driver queue, chẳng hạn như database, Redis, SQS, và nhiều hơn nữa. Trong hướng dẫn này, tôi sẽ sử dụng driver database.

#### Cấu hình Queue Driver

Mở file cấu hình queue `config/queue.php` và chọn driver mà bạn muốn sử dụng. Để sử dụng database:

```php
'connection' => env('QUEUE_CONNECTION', 'database'),
```

### Bước 2: Tạo Migration và Queue Table

Chạy lệnh sau để tạo migration cho bảng queue jobs:

```bash
php artisan queue:table
```

Sau đó, chạy migration để tạo bảng:

```bash
php artisan migrate
```

### Bước 3: Tạo Event và Listener

Chúng ta sẽ sử dụng event và listener như trong phần trước, nhưng với một số thay đổi để sử dụng queue.

#### Tạo Event

Chạy lệnh tạo event:

```bash
php artisan make:event ProductCreated
```

File event sẽ nằm tại `app/Events/ProductCreated.php`:

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

#### Tạo Listener

Chạy lệnh tạo listener:

```bash
php artisan make:listener SendProductCreatedNotification --event=ProductCreated
```

File listener sẽ nằm tại `app/Listeners/SendProductCreatedNotification.php`:

```php
<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendProductCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(ProductCreated $event)
    {
        $product = $event->product;
        $users = \App\Models\User::all(); // Lấy tất cả người dùng

        // Gửi thông báo cho tất cả người dùng
        Notification::send($users, new ProductCreatedNotification($product));
    }
}
```

**Giải thích**:
- **ShouldQueue**: Đánh dấu listener này sẽ được xử lý trong hàng đợi.
- **InteractsWithQueue**: Cung cấp các phương thức để tương tác với hàng đợi, chẳng hạn như retry, delete, v.v.

### Bước 4: Tạo Notification

Chạy lệnh tạo notification:

```bash
php artisan make:notification ProductCreatedNotification
```

File notification sẽ nằm tại `app/Notifications/ProductCreatedNotification.php`:

```php
<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification implements ShouldQueue
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

### Bước 5: Đăng ký Event và Listener

Thêm vào file `EventServiceProvider` để đăng ký event và listener:

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

### Bước 6: Kích hoạt Event trong Service hoặc Controller

Thay đổi mã trong controller hoặc service nơi sản phẩm được tạo:

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

### Bước 7: Xử lý Hàng Đợi

Để xử lý các công việc trong hàng đợi, bạn cần chạy worker. Chạy lệnh sau để bắt đầu worker:

```bash
php artisan queue:work
```

Bạn có thể chạy lệnh này trong một tiến trình nền (background process) trên server sản xuất hoặc sử dụng dịch vụ quản lý hàng đợi như Supervisor để giám sát và tự động khởi động lại worker khi cần.

### Bước 8: Kiểm thử

Để kiểm thử, tạo một sản phẩm mới và kiểm tra xem tất cả người dùng có nhận được email thông báo hay không.

#### Kiểm thử Listener

```php
<?php

namespace Tests\Unit;

use App\Events\ProductCreated;
use App\Listeners\SendProductCreatedNotification;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendProductCreatedNotificationTest extends TestCase
{
    public function test_it_sends_notification_to_all_users_when_product_is_created()
    {
        Notification::fake();

        $product = Product::factory()->create();
        $users = User::factory()->count(3)->create(); // Tạo người dùng giả

        // Kích hoạt event
        event(new ProductCreated($product));

        // Kiểm tra thông báo được gửi cho tất cả người dùng
        Notification::assertSentTo($users, ProductCreatedNotification::class);
    }
}
```

### Tóm tắt

- **Queue** giúp xử lý các tác vụ nặng nề như gửi email một cách bất đồng bộ, cải thiện hiệu suất của ứng dụng.
- Sử dụng **Event** và **Listener** để tách biệt các tác vụ phụ ra khỏi luồng chính của ứng dụng.
- Đảm bảo bạn chạy queue worker để xử lý các công việc trong hàng đợi.

Với cách triển khai này, khi một sản phẩm mới được tạo ra, email thông báo sẽ được gửi cho tất cả người dùng một cách hiệu quả và không làm giảm hiệu suất của ứng dụng.

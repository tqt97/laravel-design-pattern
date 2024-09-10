<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\User;
use App\Notifications\ProductCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendProductCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        $product = $event->product;
        $users = User::all();

        // Gửi thông báo qua email đến tất cả người dùng liên quan
        // $product->user->notify(new ProductCreatedNotification($product));
        Notification::send($users, new ProductCreatedNotification($product));
    }
}

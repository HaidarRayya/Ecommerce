<?php

namespace App\Console\Commands;

use App\Http\Controllers\NotificationController;
use App\Http\Resources\SellerProductResource;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class checkDelivearyOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checkDelivearyOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::where('status', '=', 4)->orderBy('created_at')->get();
        $now = now();

        foreach ($orders as $order) {
            if ($now->diffInDays($order->created_at) >= 30) {
                $orderproducts = OrderProducts::where('order_id', '=', $order->id)->get();
                foreach ($orderproducts as $orderproduct) {
                    $orderproduct->delete();
                }
                $order->delete();
            }
        }
        $orders = Order::where('status', '>=', 3)->orderBy('created_at')->get();
        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                if ($now->diffInDays($order->expectedArrivalDate) >= 3) {
                    $orderproducts = OrderProducts::where('order_id', '=', $order->id)->get();
                    foreach ($orderproducts as $orderproduct) {
                        $product = Product::where('id', '=', $orderproduct->product_id)->first();
                        $product->update([
                            'count' => $orderproduct->quantity
                        ]);
                        $orderproduct->delete();
                    }
                    $customer = User::find($order->customer_id);
                    $seller = User::find($order->seller_id);

                    $customer->update([
                        'points' => $customer->points + $order->totalprice
                    ]);
                    $notification = [
                        'user_id' => $customer->id,
                        "descripation" =>  " تم الغاء طلبيتك لدى البائع" . " " . $seller->firstName . " "  . $seller->lastName . " "  . ' واعادة جيع النقاط وهي ' . $order->totalprice,
                        'date' => now(),
                        'redirection' => '',
                    ];
                    NotificationController::store($notification);

                    $notification = [
                        'user_id' => $seller->id,
                        "descripation" =>  " تم الغاء طلبية الزبون" . " " . $customer->firstName . " "  . $customer->lastName . " "  . 'لتجاوز وقت التوصيل المتوقع ب ثلاث ايام ',
                        'date' => now(),
                        'redirection' => '',
                    ];
                    NotificationController::store($notification);
                    $order->delete();
                }
            }
        }
        $this->info('done');
    }
}

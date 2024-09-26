<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\OrderProducts;
use App\Models\Product;
use Illuminate\Console\Command;

class checkUnnecessaryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checkUnnecessaryData';

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
        $carts = Cart::orderBy('created_at')->get();
        $products = Product::onlyTrashed()->get();
        $ordersproducts = OrderProducts::all();
        $now = now();

        foreach ($carts as $cart) {
            if ($now->diffInHours($cart->created_at) >= 6) {
                $product = Product::where('id', '=', $cart->product_id)->first();
                $product->update([
                    'count' => $product->count + $cart->quantity
                ]);
                $cart->delete();
            }
        }

        $del = true;

        foreach ($products as   $product) {
            foreach ($ordersproducts as $i) {
                if ($product->id == $i->product_id) {
                    $del = false;
                    break;
                }
            }
            if ($del) {
                $product->forceDelete();
            }
        }
        $this->info('done');
    }
}

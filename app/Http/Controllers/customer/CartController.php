<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartProductResource;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{

    public function index()
    {
        $cartProducts = Cart::where('customer_id', '=', Auth::user()->id)->get();
        $cartProducts = CartProductResource::collection($cartProducts);
        return  response()->json([
            'cartItmes' => $cartProducts
        ]);
    }
    public function addToCart($product_id)
    {
        $product = Product::where('id', '=', $product_id)->first();
        if ($product->count < 1) {
            throw ValidationException::withMessages([
                'message' => "عدد المنتجات المدخلة اكبر من كمية المتوفرة "
            ]);
        }
        $cartItem = Cart::where('customer_id', '=', Auth::user()->id)->where('product_id', '=', $product->id)->first();
        if ($cartItem != null) {
            $cartItem->update([
                'quantity' =>  $cartItem->quantity + 1
            ]);
            $product->update([
                'count' => $product->count - 1
            ]);
            return  response()->json([
                'message' => "تمت اضافة العنصر الى السلة بنجاح"
            ]);
        }
        $product->update([
            'count' => $product->count - 1
        ]);
        $cartItem = [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'customer_id' => Auth::user()->id
        ];
        Cart::create($cartItem);
        return  response()->json([
            'message' => "تمت اضافة العنصر الى السلة بنجاح"
        ]);
    }


    public  function destroy(Cart $cart)
    {
        $product = Product::where('id', '=', $cart->product_id)->first();
        $product->update([
            'count' => $product->count + $cart->quantity
        ]);

        $cart->delete();
        return  response()->json([
            'message' => "تم حذف منتج بنجاح"
        ]);
    }
    public function saveCart(Request $request)
    {
        $cartItems = $request->toArray();
        foreach ($cartItems  as  $cartItem) {
            $cart = Cart::where('customer_id', '=', Auth::user()->id)->where('id', '=', $cartItem['cartId'])->first();
            $product = Product::where('id', '=', $cart->product_id)->first();

            if ($cartItem['quantity'] > $cart->quantity) {
                if (($cartItem['quantity'] - $cart->quantity) > $product->count) {
                    throw ValidationException::withMessages([
                        'message' =>   $product->count  .   " الكمية المتوفرة هي " .  $product->name . " لا يتوفر كمية كافية من المنتج"
                    ]);
                }
                $product->update([
                    'count' => $product->count - ($cartItem['quantity'] - $cart->quantity)
                ]);
                $cart->update([
                    'quantity' => $cart->quantity + ($cartItem['quantity'] - $cart->quantity)
                ]);
            } else if ($cartItem['quantity'] < $cart->quantity) {
                $product->update([
                    'count' => $product->count + ($cart->quantity - $cartItem['quantity'])
                ]);
                $cart->update([
                    'quantity' => $cart->quantity - ($cart->quantity - $cartItem['quantity'])
                ]);
            } else {
                continue;
            }
            return  response()->json([
                'message' => "تم الحفظ بنجاح"
            ]);
        }
    }

    public function confirmOrder()
    {
        $Alltotalprice = 0;
        $cartProducts = Cart::where('customer_id', '=', Auth::user()->id)->get();
        $user = User::find(Auth::user()->id);
        $orders = [];
        foreach ($cartProducts as $cartProduct) {
            $product = Product::where('id', '=', $cartProduct->product_id)->first();
            if (!array_key_exists($product->seller_id, $orders))
                $orders[$product->seller_id] = [];
            $x = $cartProduct->quantity * $cartProduct->price;
            $Alltotalprice += $x;
            array_push($orders[$product->seller_id], ["totalprice" => $x, "product" => $cartProduct]);
        }
        if ($user->points < $Alltotalprice) {
            throw ValidationException::withMessages([
                'message' => "ليس لديك رصيد كافي للقيام بعملية الشراء"
            ]);
        }
        $totalprice = 0;
        foreach ($orders as $key => $order) {
            foreach ($order as $o) {
                $totalprice  += $o['totalprice'];
            }
            $newOrder = Order::create([
                'totalprice' =>  $totalprice,
                'customer_id' => Auth::user()->id,
                'seller_id' => $key,
            ]);
            $seller = User::where('id', '=', $key)->first();
            /*
            $seller->update([
                'points' => $user->points + $totalprice
            ]);
            */
            foreach ($order as $o) {
                OrderProducts::create([
                    'quantity' => $o['product']->quantity,
                    'price' => $o['product']->price,
                    'order_id' => $newOrder->id,
                    'product_id' => $o['product']->product_id
                ]);
                $cartItem = Cart::where('id', '=', $o['product']->id)->first();
                $cartItem->delete();
            }
        }
        $user->update([
            'points' => $user->points - $Alltotalprice
        ]);

        return  response()->json([
            'message' => 'تم اضافة طلبية بنجاح  '
        ]);
    }
}
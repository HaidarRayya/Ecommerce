<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Resources\productOrderResourse;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    /*
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $orders = Order::where('customer_id', '=', Auth::user()->id)
            ->get();
        $x = [];
        $x1 = [];
        $x2 = [];
        $x3 = [];
        $x4 = [];

        foreach ($orders as $order) {
            if ($order->status == 1) {
                array_push($x1, $order);
            } else if ($order->status == 2) {
                array_push($x2, $order);
            } else if ($order->status == 3) {
                array_push($x3, $order);
            } else if ($order->status == 4) {
                array_push($x4, $order);
            }
        }
        // $x = $x3 + $x2 + $x1 + $x4;
        $x = array_merge($x, $x3);
        $x = array_merge($x, $x2);
        $x = array_merge($x, $x1);
        $x = array_merge($x, $x4);


        $orders = CustomerOrderResource::collection($x);

        return  response()->json([
            'orders' => $orders
        ]);
    }

    /*
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /*
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*

    */
    public function show($id)
    {
        $Orderproducts = OrderProducts::where('order_id', '=', $id)->get();


        $Orderproducts = productOrderResourse::collection($Orderproducts);
        return  response()->json([
            'Orderproducts' => $Orderproducts
        ]);
    }

    /*
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::where('id', '=', $id)->first();
        $orderProducts = OrderProducts::where('order_id', '=', $order->id)->get();
        $customer = User::find(Auth::user()->id);
        $seller = User::where('id', '=', $order->seller_id)->first();

        foreach ($orderProducts as $op) {
            $product = Product::where('id', '=', $op->product_id)
                ->withTrashed()->first();
            if ($product->delete_at != null) {
                $product->update([
                    'count' => $product->count + $op->quantity
                ]);
            }
            $op->delete();
        }

        /*
        $seller->update([
            'points' => $customer->points - $order->totalprice
        ]);
        */
        $customer->update([
            'points' => $customer->points + $order->totalprice
        ]);
        /* if ($order->status == 0) {
            $seller->update([
                'points' => $customer->points - $order->totalprice
            ]);
            $customer->update([
                'points' => $customer->points + $order->totalprice
            ]);
        } else {
            $seller->update([
                'points' => $customer->points -  ($order->totalprice) * 0.8
            ]);
            $customer->update([
                'points' => $customer->points + ($order->totalprice) * 0.8
            ]);
        }*/
        $order->delete();
        return  response()->json([
            'message' => 'تم الحذف بنجاح'
        ]);
    }
}

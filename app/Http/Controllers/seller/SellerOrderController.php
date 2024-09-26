<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\productOrderResourse;
use App\Http\Resources\SellerOrderResource;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerOrderController extends Controller
{

    public function wiatingOrders()
    {
        $orders = Order::where('seller_id', '=', Auth::user()->id)->where('status', '=', 1)->get();
        $orders = SellerOrderResource::collection($orders);

        return  response()->json([
            'orders' => $orders
        ]);
    }

    public function acceptedOrders()
    {
        $orders = Order::where('seller_id', '=', Auth::user()->id)
            ->where('status', '=', 2)
            ->orWhere('status', '=', 3)
            ->orWhere('status', '=', 4)
            ->orderBy('status')
            ->get();
        $orders = SellerOrderResource::collection($orders);

        return  response()->json([
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $Orderproducts = OrderProducts::where('order_id', '=', $id)->get();

        $Orderproducts = productOrderResourse::collection($Orderproducts);
        return  response()->json([
            'Orderproducts' => $Orderproducts
        ]);
    }


    public function update(Request $request, $order_id)
    {
        $validator = Validator::make($request->all(), [
            'deliveryDriver_id' => 'required',
            'expectedArrivalDate' => 'required'
        ], [
            'deliveryDriver_id.required' => 'يجب ادخال سائق التوصيل',
            'expectedArrivalDate.min' => 'يجب ادخال الوقت المتوقع للوصول',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $expectedArrivalDate = Carbon::create($request->expectedArrivalDate);
        if ($expectedArrivalDate->lessThanOrEqualTo(now())) {
            return response()->json([
                'message' => 'الوقت المدخل خاطئ'
            ], 422);
        }
        $order = Order::where('id', '=', $order_id)->first();
        $customer = User::where('id', '=', $order->customer_id)->first();

        $order->update(
            [
                'deliveryDriver_id' => $request->deliveryDriver_id,
                'expectedArrivalDate' => $request->expectedArrivalDate,
                'status' => 2
            ]
        );
        /*
        $data = [
            'subject' => " قبول الطلبية  ",
            "body" =>  "مرحبا " . " " . $customer->firstname . " "  . $customer->lastname . " " .  " تم قبول طلبيتك الوقت المتوقع ل الوصول هو " . $request->expectedArrivalDate
        ];
        EmailController::acceptOrder($data, $customer->email);
        */
        // اشعار
        return  response()->json([
            'message' => 'تم القبول بنجاح'
        ]);
    }




    public function reject($id)
    {
        $order = Order::where('id', '=', $id)->first();
        $orderProducts = OrderProducts::where('order_id', '=', $order->id)->get();
        $customer = User::where('id', '=', $order->customer_id)->first();
        $seller = User::where('id', '=', $order->seller_id)->first();

        foreach ($orderProducts as $op) {
            $product = Product::where('id', '=', $op->product_id)->first();
            $product->update([
                'count' => $product->count + $op->quantity
            ]);
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
        $order->delete();


        $data = [
            'subject' => " رفض الطلبية  ",
            "body" =>  "مرحبا " . " " . $customer->firstName . " "  . $customer->lastName . " " .  " تم قبول طلبيتك  من قبل  البائع " . $seller->firstName . " "  . $seller->lastName,
        ];
        EmailController::rejectOrder($data, $customer->email);

        $notification = [
            'user_id' => $customer->id,
            "descripation" =>  "مرحبا " . " " . $customer->firstName . " "  . $customer->lastName . " " .  " تم قبول طلبيتك  من قبل  البائع " . $seller->firstName . " "  . $seller->lastName,
            'date' => now(),
            'redirection' => '/customer_orders',
        ];
        NotificationController::store($notification);
        return  response()->json([
            'message' => 'تم الرفض بنجاح'
        ]);
    }
    public function accept(Request $request, $order_id)
    {
        $validator = Validator::make($request->all(), [
            'deliveryDriver_id' => 'required',
            'expectedArrivalDate' => 'required'
        ], [
            'deliveryDriver_id.required' => 'يجب ادخال سائق التوصيل',
            'expectedArrivalDate.min' => 'يجب ادخال الوقت المتوقع للوصول',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $expectedArrivalDate = Carbon::create($request->expectedArrivalDate);
        if ($expectedArrivalDate->lessThanOrEqualTo(now())) {
            return response()->json([
                'message' => 'الوقت المدخل خاطئ'
            ], 422);
        }
        $order = Order::where('id', '=', $order_id)->first();
        $customer = User::where('id', '=', $order->customer_id)->first();
        $seller = User::where('id', '=', $order->seller_id)->first();

        $order->update(
            [
                'deliveryDriver_id' => $request->deliveryDriver_id,
                'expectedArrivalDate' => $request->expectedArrivalDate,
                'status' => 2
            ]
        );
        $data = [
            'subject' => " قبول الطلبية  ",
            "body" =>  "مرحبا " . " " . $customer->firstName . " "  . $customer->lastName . " " .  " تم قبول طلبيتك  من قبل  البائع " . $seller->firstName . " "  . $seller->lastName,
        ];
        $notification = [
            'user_id' => $customer->id,
            "descripation" =>  "مرحبا " . " " . $customer->firstName . " "  . $customer->lastName . " " .  " تم قبول طلبيتك  من قبل  البائع " . $seller->firstName . " "  . $seller->lastName,
            'date' => now(),
            'redirection' => '/customer_orders',
        ];
        NotificationController::store($notification);

        EmailController::acceptOrder($data, $customer->email);
        // اشعار
        return  response()->json([
            'message' => 'تم القبول بنجاح'
        ]);
    }
}
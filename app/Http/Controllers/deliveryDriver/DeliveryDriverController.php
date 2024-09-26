<?php

namespace App\Http\Controllers\DeliveryDriver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Resources\DeliveryOrderResource;
use App\Http\Resources\SellerResource;
use App\Models\ConfirmationCode;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\PurchaseHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeliveryDriverController extends Controller
{
    public function wiatingOders()
    {
        $orders = Order::where('deliveryDriver_id', '=', Auth::user()->id)
            ->where('status', '=', 2)->get();
        $orders = DeliveryOrderResource::collection($orders);

        return  response()->json([
            'orders' => $orders
        ]);
    }

    public function startedDelivery()
    {
        $orders = Order::where('deliveryDriver_id', '=', Auth::user()->id)->where('status', '=', 3)->get();
        $orders = DeliveryOrderResource::collection($orders);

        return  response()->json([
            'orders' => $orders
        ]);
    }
    public function startDelivery($id)
    {
        $order = Order::where('id', '=', $id)->first();

        $order->update([
            'status' => 3
        ]);

        return  response()->json([
            'message' => 'تم بدء توصيل الطلبية'
        ]);
    }
    public function createConfirmOrderCode($id)
    {
        $order = Order::where('id', '=', $id)->first();
        $user = User::where('id', '=', $order->customer_id)->first();
        $code = random_int(100000, 999999);
        ConfirmationCode::create([
            'user_id' => $user->id,
            'code' =>  $code,
            'type' => 2,
        ]);

        $notification = [
            'user_id' => $user->id,
            'descripation' => ' رمز عملية تأكيد الطلب هو  ' . $code . ' , هذا الرمز متاح لساعة فقط ',
            'date' => now(),
            'redirection' => ''
        ];
        NotificationController::store($notification);

        $data = [
            'subject' => "رمز تأكيد التوصيل",
            "body" =>  ' رمز عملية تأكيد التوصيل هو  ' . $code . ' , هذا الرمز متاح لساعة فقط ',
        ];
        EmailController::sendConfirmationCode($data, $user->email);
        return  response(200);
    }
    public function delivered($order_id, Request $request)
    {
        $order = Order::where('id', '=', $order_id)->first();
        $customer = User::where('id', '=', $order->customer_id)->first();
        $seller = User::where('id', '=', $order->seller_id)->first();

        $confirmOrder = ConfirmationCode::where('user_id', '=',  $customer->id)
            ->where('type', '=', 2)
            ->orderByDesc('created_at')->first();
        if ($confirmOrder->code == $request->code) {
            $order->update(['status' => 4]);
            $seller->update([
                'points' => $seller->points + $order->totalprice
            ]);
            $confirmOrder->delete();
        } else {
            throw ValidationException::withMessages([
                'message' => 'الرمز الذي ادخلته خاطئ يرجى التأكد واعادة المحاولة'
            ]);
        }

        $orderProducts = OrderProducts::where('order_id', '=', $order->id)->get();
        foreach ($orderProducts as $op) {
            PurchaseHistory::create([
                'user_id' => $customer->id,
                'product_id' => $op->product_id
            ]);
        }

        $notification = [
            'user_id' => $customer->id,
            "descripation" =>  "لقد قمت بعملية شراء من البائع " . " " . $seller->firstName . " "  . $seller->lastName . " "  . 'يمكن الضغط على هذا الاشعار لتقييم البائع',
            'date' => now(),
            'redirection' => '/seller_page',
            'data' => SellerResource::make($seller)->toJson()
        ];
        NotificationController::store($notification);

        $notification = [
            'user_id' => $customer->id,
            "descripation" =>  "لقد تم توصيل الطلبية رقم " . " " . $order_id . " "    . 'يمكنك الضغط على هذا الاشعار لتقييم المنتجات ',
            'date' => now(),
            'redirection' => '/customer_order_page',
            'data' => CustomerOrderResource::make($order)->toJson()

        ];
        NotificationController::store($notification);

        return  response()->json([
            'message' => 'تم توصيل الطلبية'
        ]);
    }
}

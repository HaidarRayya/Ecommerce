<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function udateProfile(Request $request)
    {
        $activeUser = User::find(Auth::user()->id);
        $userAddress = $activeUser->address;

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|min:3',
            'lastName' => 'required|min:3',
            'address' => 'required',
            'phoneNumber' => 'required',
        ], [
            'firstName.required' => 'يجب ادخال الاسم الاول',
            'lastName.required' => 'يجب ادخال الاسم الاخير',
            'address.required' => 'يجب ادخال العنوان',
            'phoneNumber.required' => 'يجب ادخال رقم الموبايل',
            'firstName.min' => 'الاسم الاول المدخل يجب ان تحتوي على 3 محارف',
            'lastName.min' => 'الاسم الاخير المدخل يجب ان تحتوي على 3 محارف'
        ]);


        $user = User::where('phoneNumber', '=', $request->phoneNumber)->where('id', '!=', Auth::user()->id)->first();
        if ($user) {
            return  response()->json([
                'message' => ' الرقم الهاتف مكرر يرجى التأكد واعادة المحاولة'
            ], 422);
        }
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        if ($request->hasFile('image')) {
            if ($request->file('image') == substr($activeUser->image, 6)) {
                Storage::disk('public')->delete($activeUser->image);
            }
            $image = $request->file('image')->store('profileImages', 'public');
            $activeUser->update([
                'image' => $image,
            ]);
        }

        if ($request->newPassword != null) {
            if (!Hash::check($request->oldPassword, $activeUser->password)) {
                $activeUser->update([
                    'firstName' => $request->firstName,
                    'lastName' => $request->lastName,
                    'address' => $request->address,
                    'phoneNumber' => $request->phoneNumber,
                ]);
                throw ValidationException::withMessages([
                    'message' => "كلمة السر القديمة غير صحيحية يرجى التأكد منها واعدة المحاولة",
                ]);
            }
            $password = Hash::make($request->newPassword);
            $activeUser->update([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'address' => $request->address,
                'phoneNumber' => $request->phoneNumber,
                'password' => $password
            ]);
        } else {
            $activeUser->update([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'address' => $request->address,
                'phoneNumber' => $request->phoneNumber,
            ]);
        }
        $products = Product::where('seller_id', '=', $activeUser->id)->get();
        if (!empty($products)) {
            foreach ($products as $product) {
                $product->update(['seller_name' => $activeUser->firstName . " " . $activeUser->lastName]);
            }
        }
        if ($userAddress != $request->address) {
            $orders = Order::where('customer_id', '=', Auth::user()->id)->get();
            foreach ($orders as $order) {
                $customer = User::find($order->customer_id);

                $seller = User::find($order->seller_id);
                $notification = [
                    'user_id' => $seller->id,
                    "descripation" =>  "قام الزبون " . " " . $customer->firstName . " "  . $customer->lastName . " " .  ' صاحب الطلبية رقم ' . $order->id .   ' بتغير عنوانه ',
                    'date' => now(),
                    'redirection' => '/seller_orders'
                ];
                NotificationController::store($notification);

                $deliveryDriver = User::find($order->deliveryDriver_id);

                $notification = [
                    'user_id' => $deliveryDriver->id,
                    "descripation" =>  "قام الزبون " . " " . $customer->firstName . " "  . $customer->lastName . " " .  ' صاحب الطلبية رقم ' . $order->id .   ' بتغير عنوانه ',
                    'date' => now(),
                    'redirection' => '/seller_orders'
                ];
                NotificationController::store($notification);
            }
        }

        return response()->json([
            "message" => "تم تحديث المعلومات بنجاح",
            'image' =>  asset('storage/' .  $activeUser->image),
            'user' => UserResource::make(Auth::user())
        ]);
    }
    public function updateData(Request $request)
    {
        return response()->json([
            'user' => UserResource::make(Auth::user())
        ]);
    }
}

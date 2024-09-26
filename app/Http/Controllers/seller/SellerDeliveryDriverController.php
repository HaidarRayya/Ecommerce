<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\SellerDeliveryDriver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class SellerDeliveryDriverController extends Controller
{

    public function index()
    {
        $deliveryDrivers_id = SellerDeliveryDriver::where('seller_id', '=', Auth::user()->id)->get();
        $deliveryDrivers = [];
        foreach ($deliveryDrivers_id as $i) {
            array_push(
                $deliveryDrivers,
                UserResource::make(User::where('id', '=', $i->deliveryDriver_id)->first())
            );
        }
        return response()->json([
            'deliveryDrivers' => $deliveryDrivers
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|min:3',
            'lastName' => 'required|min:3',
            'address' => 'required',
            'phoneNumber' => ['required', Rule::unique('users', 'phoneNumber')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|min:8'
        ], [
            'firstName.required' => 'يجب ادخال الاسم الاول',
            'lastName.required' => 'يجب ادخال الاسم الاخير',
            'address.required' => 'يجب ادخال العنوان',
            'phoneNumber.required' => 'يجب ادخال رقم الموبايل',
            'email.required' => 'يجب ادخال الايميل',
            'password.required' =>  'يجب ادخال كلمة السر',
            'email.email' => 'الايميل الذي قمت بادخاله غير صحيح',
            'password.min' => 'كلمة السر المدخلة يجب ان تحتوي على 8 محارف',
            'firstName.min' => 'الاسم الاول المدخل يجب ان تحتوي على 3 محارف',
            'lastName.min' => 'الاسم الاخير المدخل يجب ان تحتوي على 3 محارف'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $registerData = $request->all();
        $registerData['role'] = 'deliveryDriver';
        $registerData['password'] = Hash::make($request->password);

        $user = User::create($registerData);
        SellerDeliveryDriver::create([
            'seller_id' => Auth::user()->id,
            'deliveryDriver_id' => $user->id,
        ]);
        return response()->json([
            'message' => 'تم اضافة سائق توصيل  بنجاح',
            'deliveryDriver' => $user
        ]);
    }

    public function show($id)
    {
        //
    }

    /**
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
        $user = User::find($id);
        $sellerDeliveryDriver = SellerDeliveryDriver::where('seller_id', '=', Auth::user()->id)
            ->where('deliveryDriver_id', '=', $user->id)
            ->first();
        $orders = Order::where('deliveryDriver_id', '=', $user->id)->get();
        if (!$orders->isEmpty()) {
            return response()->json([
                'message' => 'لا يمكنك ازالة هذا السائق لديه طلبيات موكلة له'
            ]);
        }
        $sellerDeliveryDriver->delete();
        $user->delete();
        return response()->json([
            'message' => 'تمت الازالة بنجاح',
        ]);
    }
}
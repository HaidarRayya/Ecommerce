<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], [
            'email.required' => 'يجب ادخال الايميل',
            'password.required' =>  'يجب ادخال كلمة السر',
            'email.email' => 'الايميل الذي قمت بادخاله غير صحيح',
            'password.min' => 'كلمة السر المدخلة يجب ان تحتوي على 8 محارف'
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $user = User::where('email',  $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة يرجى التأكد واعادة المحاولة"
            ]);
        }

        if ($user->role === 'pointRetalier' && $user->role == 'waiting') {
            return response()->json([
                "message" => "طلبك قيد المراجعة سوف يتم ارسال ايميل عند معالجة الحالة"
            ], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $user = UserResource::make($user);
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "message" => "تم تسجيل الخروج"
        ]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|min:3',
            'lastName' => 'required|min:3',
            'address' => 'required',
            'phoneNumber' => 'required',
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'max:21', Password::min(8)->letters()->numbers()],
            'role' => 'required'
        ], [
            'firstName.required' => 'يجب ادخال الاسم الاول',
            'lastName.required' => 'يجب ادخال الاسم الاخير',
            'address.required' => 'يجب ادخال العنوان',
            'phoneNumber.required' => 'يجب ادخال رقم الموبايل',
            'phoneNumber.unique' => 'الرقم الذي ادخلته مكرر يرجى التأكد منه ',
            'email.unique' => 'الايميل الذي ادخلته مكرر يرجى التأكد منه ',
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
            ]);
        }
        $user = User::where('email', '=', $request->email)->first();
        if ($user) {
            return  response()->json([
                'message' => 'هذا الايميل مكرر يرجى التأكد واعادة المحاولة'
            ], 422);
        }

        $user = User::where('phoneNumber', '=', $request->phoneNumber)->first();
        if ($user) {
            return  response()->json([
                'message' => ' الرقم الهاتف مكرر يرجى التأكد واعادة المحاولة'
            ], 422);
        }

        $registerData = $request->all();
        $user = User::where('email', '=', $registerData['email'])->first();
        if ($user) {
            throw ValidationException::withMessages([
                'message' => "هذا الحساب مسجل في الموقع"
            ]);
        }
        $registerData['password'] = Hash::make($request->password);
        if ($registerData['role'] === 'pointRetalier') {
            $registerData['status'] = 'waiting';
        }
        $user = User::create($registerData);
        if ($registerData['role'] === 'pointRetalier') {
            return response()->json([
                "message" => "تم تسجيل طلبك بنجاح سوف يتم ارسال ايميل عند الموافقة على الطلب"
            ], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $user = UserResource::make(User::where('id', '=', $user->id)->first());
        return response()->json([
            "user" => $user,
            "token" => $token,
            "message" => "تم التسجيل بنجاح"
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Models\ConfirmationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ForgetPasswordController extends Controller
{
    public function checkEmail(Request $request)
    {
        $user = User::where('email',  $request->email)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'message' => "الايميل الذي ادخلته غير صحيح"
            ]);
        }
        $code = random_int(100000, 999999);
        ConfirmationCode::create([
            'user_id' => $user->id,
            'code' =>  $code,
            'type' => 1,
        ]);
        $data = [
            'subject' => "رمز اعادة تعيين كلمة المرور ",
            "body" =>   "ان رمز تعينن كلمة مرور هو " . $code . "حيث هذا الرمز متاح للاستخدام لمدة ساعة فقط"
        ];
        EmailController::changePassword($data, $user->email);
        return response(200);
    }
    public function checkCode(Request $request)
    {
        $user = User::where('email',  $request->email)->first();
        $confirmOrder = ConfirmationCode::where('user_id', '=',  $user->id)->where('type', '=', 1)->orderByDesc('created_at')->first();

        $code = $confirmOrder->code;
        if ($code ==  $request->code) {
            return response(200);
        } else {
            throw ValidationException::withMessages([
                'message' => "الكود الذي ادخلته خاطئ يرجى التأكد منه واعادة المحاولة"
            ]);
        }
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8'
        ], [
            'password.required' =>  'يجب ادخال كلمة السر',
            'password.min' => 'كلمة السر المدخلة يجب ان تحتوي على 8 محارف'
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                // 'errors' => $validator->errors()
            ]);
        }
        $user = User::where('email',  $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);
        ConfirmationCode::where('user_id', '=', $user->id)->delete();
        return response()->json([
            "message" => "تم تحديث كلمة مرور بنجاح"
        ]);
    }
}
<?php

namespace App\Http\Controllers\pointRetailer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\OperationResource;
use App\Models\ConfirmationCode;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class OperationsController extends Controller
{
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'amount' => 'required'
        ], [
            'email.required' => 'يجب ادخال الايميل',
            'amount.required' =>  'يجب ادخال قيمة العملية',
            'email.email' => 'الايميل الذي قمت بادخاله غير صحيح',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $user = User::where('email', '=', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => "الحساب الذي ادخلته غير صحيح"
            ]);
        }
        if ($user->role != "customer") {
            throw ValidationException::withMessages([
                'message' => "هذه العملية غير متاحة لهذا المستخدم يرجى التأكد من الحساب المدخل"
            ]);
        }
        if ($request->amount <= 0) {
            throw ValidationException::withMessages([
                'message' => "القيمة مدخلة خاطئة"
            ]);
        }
        $user->update([
            'points' => $user->points + $request->amount
        ]);

        Operation::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'date' => now(),
            'amount' => $request->amount,
            'pointretailer_id' => Auth::user()->id
        ]);

        $pointPetailer = User::find(Auth::user()->id);

        $notification = [
            'user_id' => $user->id,
            "descripation" =>  "قام موزع النقاط " . " " . $pointPetailer->firstName . " "  . $pointPetailer->lastName . " " .  ' بتعبئة  مبلغ   ' . $request->amount  . " " .  ' من حسابك اضغط على الرمز الي بجانب النقاط لتحديث القيمة',
            'date' => now(),
            'redirection' => '',
        ];
        NotificationController::store($notification);
        return response()->json([
            'message' => 'تمت عملية التحويل بنجاح'
        ]);
    }
    public function confirmationCodeWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ], [
            'email.required' => 'يجب ادخال الايميل',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' =>  'يجب ادخال الايميل',
            ]);
        }
        $user = User::where('email', '=', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => "الحساب الذي ادخلته غير صحيح"
            ]);
        }
        if ($user->role != "seller") {
            throw ValidationException::withMessages([
                'message' => "هذه العملية غير متاحة لزبون"
            ]);
        }
        $code = random_int(100000, 999999);
        ConfirmationCode::create([
            'user_id' => $user->id,
            'code' =>  $code,
            'type' => 3,
        ]);


        $notification = [
            'user_id' => $user->id,
            'descripation' => ' رمز عملية السحب هو ' . $code . ' , هذا الرمز متاح لساعة فقط ',
            'date' => now(),
            'redirection' => ''
        ];
        NotificationController::store($notification);

        $data = [
            'subject' => "رمز تأكيد السحب",
            "body" =>  ' رمز عملية السحب هو ' . $code . ' , هذا الرمز متاح لساعة فقط ',
        ];
        EmailController::sendConfirmationCode($data, $user->email);
        return response()->json([
            'message' => "لقد تم ارسال الرمز الي اشعارات الزبون يرجى ادخاله لاتمام عملية السحب"
        ]);
    }
    public function withdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'amount' => 'required',
            'code' => 'required'
        ], [
            'email.required' => 'يجب ادخال الايميل',
            'amount.required' =>  'يجب ادخال قيمة العملية',
            'email.email' => 'الايميل الذي قمت بادخاله غير صحيح',
            'code.required' =>  'يجب ادخال رمز السحب ',

        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $user = User::where('email', '=', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => "الحساب الذي ادخلته غير صحيح"
            ]);
        }

        $confirmOrder = ConfirmationCode::where('user_id', '=',  $user->id)->where('type', '=', 3)->orderByDesc('created_at')->first();
        if ($confirmOrder->code !=  $request->code) {
            throw ValidationException::withMessages([
                'message' => "الكود الذي ادخلته خاطئ يرجى التأكد منه واعادة المحاولة"
            ]);
        }

        if ($user->points < $request->amount) {
            throw ValidationException::withMessages([
                'message' => "ليس لديك رصيد كافي يرجى التحقق من العملية"
            ]);
        }
        if ($request->amount <= 0) {
            throw ValidationException::withMessages([
                'message' => "القيمة مدخلة خاطئة"
            ]);
        }
        $user->update([
            'points' => $user->points - $request->amount
        ]);
        Operation::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'date' => now(),
            'amount' => $request->amount,
            'pointretailer_id' => Auth::user()->id
        ]);
        $pointPetailer = User::find(Auth::user()->id);

        $notification = [
            'user_id' => $user->id,
            "descripation" =>  "قام موزع النقاط " . " " . $pointPetailer->firstName . " "  . $pointPetailer->lastName . " " .  ' بسحب  مبلغ   ' . $request->amount  . " " .  ' من حسابك اضغط على الرمز الي بجانب النقاط لتحديث القيمة',
            'date' => now(),
            'redirection' => '',
        ];
        NotificationController::store($notification);

        $confirmOrder->delete();
        return response()->json([
            'message' => 'تمت عملية السحب بنجاح'
        ]);
    }


    public function getAllOperations()
    {
        $operations = Operation::where('pointretailer_id', '=', Auth::user()->id)->orderByDesc('date')->get();
        $operations = OperationResource::collection($operations);
        return response()->json([
            'operations' => $operations
        ]);
    }
}
